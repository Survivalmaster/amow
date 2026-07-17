const {
  ActionRowBuilder,
  ButtonBuilder,
  ButtonStyle,
  EmbedBuilder,
  PermissionFlagsBits
} = require('discord.js');

const {
  deletePanel,
  deletePanelCooldowns,
  getPanel,
  getPanelUserCooldown,
  savePanel,
  setPanelUserCooldown
} = require('./storage');

const MAX_PANEL_ROLES = 10;
const BUTTON_ID_PREFIX = 'role-panel';
const memberCacheRefreshes = new Map();
const panelUserLocks = new Map();
const MEMBER_CACHE_REFRESH_MS = 10 * 60 * 1000;
const MAX_COOLDOWN_MS = 365 * 24 * 60 * 60 * 1000;

const buttonStyles = {
  Primary: ButtonStyle.Primary,
  Secondary: ButtonStyle.Secondary,
  Success: ButtonStyle.Success,
  Danger: ButtonStyle.Danger
};

function stripWrappingQuotes(value) {
  return value.replace(/^["']+|["']+$/g, '').trim();
}

function parseDuration(value) {
  const normalizedValue = value?.trim().toLowerCase();
  if (!normalizedValue || ['0', 'off', 'none', 'disable', 'disabled'].includes(normalizedValue)) {
    return 0;
  }

  const match = normalizedValue.match(/^(\d+)\s*(m|min|mins|minute|minutes|h|hr|hrs|hour|hours|d|day|days|w|week|weeks)$/);
  if (!match) {
    throw new Error('Use a cooldown like `30m`, `24h`, `7d`, `2w`, or `0` to disable.');
  }

  const amount = Number(match[1]);
  const multipliers = {
    m: 60 * 1000,
    min: 60 * 1000,
    mins: 60 * 1000,
    minute: 60 * 1000,
    minutes: 60 * 1000,
    h: 60 * 60 * 1000,
    hr: 60 * 60 * 1000,
    hrs: 60 * 60 * 1000,
    hour: 60 * 60 * 1000,
    hours: 60 * 60 * 1000,
    d: 24 * 60 * 60 * 1000,
    day: 24 * 60 * 60 * 1000,
    days: 24 * 60 * 60 * 1000,
    w: 7 * 24 * 60 * 60 * 1000,
    week: 7 * 24 * 60 * 60 * 1000,
    weeks: 7 * 24 * 60 * 60 * 1000
  };
  const duration = amount * multipliers[match[2]];

  if (!Number.isSafeInteger(duration) || duration > MAX_COOLDOWN_MS) {
    throw new Error('Cooldown must be between 0 and 365 days.');
  }

  return duration;
}

function formatDuration(durationMs) {
  if (!durationMs) {
    return 'off';
  }

  const units = [
    ['week', 7 * 24 * 60 * 60 * 1000],
    ['day', 24 * 60 * 60 * 1000],
    ['hour', 60 * 60 * 1000],
    ['minute', 60 * 1000]
  ];

  for (const [label, unitMs] of units) {
    if (durationMs >= unitMs && durationMs % unitMs === 0) {
      const amount = durationMs / unitMs;
      return `${amount} ${label}${amount === 1 ? '' : 's'}`;
    }
  }

  const minutes = Math.ceil(durationMs / (60 * 1000));
  return `${minutes} minute${minutes === 1 ? '' : 's'}`;
}

function formatRemainingTime(durationMs) {
  const totalMinutes = Math.max(1, Math.ceil(durationMs / (60 * 1000)));
  const days = Math.floor(totalMinutes / (24 * 60));
  const hours = Math.floor((totalMinutes % (24 * 60)) / 60);
  const minutes = totalMinutes % 60;
  const parts = [];

  if (days) parts.push(`${days} day${days === 1 ? '' : 's'}`);
  if (hours) parts.push(`${hours} hour${hours === 1 ? '' : 's'}`);
  if (minutes || !parts.length) parts.push(`${minutes} minute${minutes === 1 ? '' : 's'}`);

  return parts.slice(0, 2).join(' ');
}

async function ensureMemberCache(guild, force = false) {
  if (!guild) {
    return;
  }

  const lastRefresh = memberCacheRefreshes.get(guild.id) ?? 0;
  const cacheLooksComplete = guild.memberCount ? guild.members.cache.size >= guild.memberCount : false;
  if (!force && cacheLooksComplete && Date.now() - lastRefresh < MEMBER_CACHE_REFRESH_MS) {
    return;
  }

  await guild.members.fetch();
  memberCacheRefreshes.set(guild.id, Date.now());
}

function roleCount(guild, roleId) {
  return guild?.roles.cache.get(roleId)?.members.size ?? 0;
}

function panelEmbed(panel, guild = null) {
  const roleList = panel.roles.length
    ? panel.roles.map((entry) => `<@&${entry.roleId}> - (${roleCount(guild, entry.roleId)})`).join('\n')
    : 'No roles have been added yet.';

  return new EmbedBuilder()
    .setTitle(panel.title)
    .setDescription(panel.description)
    .setColor(0x5865f2)
    .addFields(
      { name: 'Available roles', value: roleList },
      {
        name: 'Selection mode',
        value: panel.exclusive
          ? 'Choose one role from this panel. Picking another removes the previous one.'
          : 'Choose any roles from this panel. Clicking a role you already have removes it.'
      },
      {
        name: 'Cooldown',
        value: panel.cooldownMs ? `You can change roles once every ${formatDuration(panel.cooldownMs)}.` : 'No cooldown.'
      }
    )
    .setFooter({ text: 'Use the buttons below to update your roles.' });
}

function panelComponents(panel) {
  const buttons = panel.roles.map((entry) => {
    const button = new ButtonBuilder()
      .setCustomId(`${BUTTON_ID_PREFIX}:${panel.messageId}:${entry.roleId}`)
      .setLabel(entry.label)
      .setStyle(buttonStyles[entry.style] ?? ButtonStyle.Secondary);

    if (entry.emoji) {
      button.setEmoji(entry.emoji);
    }

    return button;
  });

  const rows = [];
  for (let index = 0; index < buttons.length; index += 5) {
    rows.push(new ActionRowBuilder().addComponents(buttons.slice(index, index + 5)));
  }

  return rows;
}

function roleNamesFromIds(guild, roleIds) {
  return roleIds
    .map((roleId) => guild.roles.cache.get(roleId)?.name)
    .filter(Boolean);
}

async function sendRoleLog(interaction, panel, assignedRoleNames, revokedRoleNames) {
  if (!panel.logChannelId) {
    return;
  }

  const logChannel = await interaction.guild.channels.fetch(panel.logChannelId).catch(() => null);
  if (!logChannel?.isTextBased()) {
    return;
  }

  const actionLines = [];
  if (assignedRoleNames.length) {
    actionLines.push(`Roles Assigned (${assignedRoleNames.length}): ${assignedRoleNames.join(', ')}`);
  }

  if (revokedRoleNames.length) {
    actionLines.push(`Roles Revoked (${revokedRoleNames.length}): ${revokedRoleNames.join(', ')}`);
  }

  const embed = new EmbedBuilder()
    .setTitle('ROLE SELECTION UPDATED')
    .setColor(0x57f287)
    .setDescription(`${interaction.user} updated their roles from **${panel.title}**.`)
    .addFields({
      name: 'ACTIONS',
      value: actionLines.join('\n')
    })
    .setTimestamp();

  await logChannel.send({ embeds: [embed] }).catch((error) => {
    console.error('Failed to send role panel log:', error);
  });
}

async function assertCooldownReady(interaction, panel) {
  if (!panel.cooldownMs) {
    return true;
  }

  const lastChange = await getPanelUserCooldown(panel.messageId, interaction.user.id);
  if (!lastChange?.changedAt) {
    return true;
  }

  const expiresAt = lastChange.changedAt + panel.cooldownMs;
  const remainingMs = expiresAt - Date.now();
  if (remainingMs <= 0) {
    return true;
  }

  await interaction.reply({
    content: `You can change roles from **${panel.title}** again in ${formatRemainingTime(remainingMs)}.`,
    ephemeral: true
  });
  return false;
}

async function recordCooldown(interaction, panel, roleId) {
  if (!panel.cooldownMs) {
    return;
  }

  await setPanelUserCooldown(panel.messageId, interaction.user.id, {
    changedAt: Date.now(),
    roleId
  });
}

async function runWithPanelUserLock(panel, userId, task) {
  const lockKey = `${panel.messageId}:${userId}`;
  const previousLock = panelUserLocks.get(lockKey) ?? Promise.resolve();
  let releaseLock;
  const currentLock = new Promise((resolve) => {
    releaseLock = resolve;
  });
  const queuedLock = previousLock.then(() => currentLock);

  panelUserLocks.set(lockKey, queuedLock);

  try {
    await previousLock;
    return await task();
  } finally {
    releaseLock();
    if (panelUserLocks.get(lockKey) === queuedLock) {
      panelUserLocks.delete(lockKey);
    }
  }
}

async function fetchPanelMessage(interaction, messageId) {
  const panel = await getPanel(messageId);
  if (!panel) {
    throw new Error('I could not find a saved role panel for that message ID.');
  }

  const channel = await interaction.guild.channels.fetch(panel.channelId);
  if (!channel?.isTextBased()) {
    throw new Error('The saved panel channel is missing or is not a text channel.');
  }

  const message = await channel.messages.fetch(panel.messageId);
  return { message, panel };
}

function assertAdmin(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('You need the Manage Roles permission to configure role panels.');
  }
}

async function assertBotCanManageRole(interaction, role) {
  if (role.id === interaction.guild.id) {
    throw new Error('The @everyone role cannot be assigned by a role panel.');
  }

  if (role.managed) {
    throw new Error('Managed integration roles cannot be assigned manually.');
  }

  const botMember = interaction.guild.members.me ?? await interaction.guild.members.fetchMe();
  if (!botMember.permissions.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('I need the Manage Roles permission before I can assign roles.');
  }

  if (botMember.roles.highest.comparePositionTo(role) <= 0) {
    throw new Error(`Move my highest role above ${role.name} before adding it to a panel.`);
  }
}

async function refreshPanelMessage(message, panel, forceCounts = false) {
  await ensureMemberCache(message.guild, forceCounts);

  await message.edit({
    embeds: [panelEmbed(panel, message.guild)],
    components: panelComponents(panel)
  });
}

async function refreshPanelFromInteraction(interaction, panel) {
  const channel = await interaction.guild.channels.fetch(panel.channelId).catch(() => null);
  if (!channel?.isTextBased()) {
    return;
  }

  const message = await channel.messages.fetch(panel.messageId).catch(() => null);
  if (!message) {
    return;
  }

  await refreshPanelMessage(message, panel);
}

async function createPanel(interaction) {
  assertAdmin(interaction);

  const targetChannel = interaction.options.getChannel('channel') ?? interaction.channel;
  const logChannel = interaction.options.getChannel('log-channel');
  if (!targetChannel?.isTextBased()) {
    throw new Error('Please choose a text channel for the role panel.');
  }

  if (logChannel && !logChannel.isTextBased()) {
    throw new Error('Please choose a text channel for role panel logs.');
  }

  const panel = {
    guildId: interaction.guildId,
    channelId: targetChannel.id,
    logChannelId: logChannel?.id ?? null,
    messageId: null,
    title: interaction.options.getString('title', true),
    description: interaction.options.getString('description', true),
    exclusive: interaction.options.getBoolean('exclusive') ?? false,
    cooldownMs: parseDuration(interaction.options.getString('cooldown') ?? '0'),
    roles: [],
    createdBy: interaction.user.id,
    createdAt: new Date().toISOString()
  };

  const message = await targetChannel.send({
    embeds: [panelEmbed(panel, interaction.guild)],
    components: []
  });

  panel.messageId = message.id;
  await savePanel(panel);
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: [
      `Role panel created in ${targetChannel}.`,
      logChannel ? `Role changes will be logged in ${logChannel}.` : null,
      `Message ID: \`${message.id}\``,
      'Add buttons with `/role-panel add`.'
    ].filter(Boolean).join('\n'),
    ephemeral: true
  });
}

async function addRole(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const label = stripWrappingQuotes(interaction.options.getString('label') ?? role.name);
  const emoji = interaction.options.getString('emoji');
  const style = interaction.options.getString('style') ?? 'Secondary';

  const { message, panel } = await fetchPanelMessage(interaction, messageId);
  await assertBotCanManageRole(interaction, role);

  if (panel.roles.length >= MAX_PANEL_ROLES) {
    throw new Error(`This role panel already has ${MAX_PANEL_ROLES} roles.`);
  }

  if (panel.roles.some((entry) => entry.roleId === role.id)) {
    throw new Error('That role is already on this panel.');
  }

  panel.roles.push({
    roleId: role.id,
    label: label.slice(0, 80),
    emoji,
    style
  });

  await savePanel(panel);
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: `Added ${role} to the panel.`,
    ephemeral: true
  });
}

async function removeRole(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const { message, panel } = await fetchPanelMessage(interaction, messageId);
  const nextRoles = panel.roles.filter((entry) => entry.roleId !== role.id);

  if (nextRoles.length === panel.roles.length) {
    throw new Error('That role is not on this panel.');
  }

  panel.roles = nextRoles;
  await savePanel(panel);
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: `Removed ${role} from the panel.`,
    ephemeral: true
  });
}

async function editRole(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const label = interaction.options.getString('label');
  const style = interaction.options.getString('style');
  const { message, panel } = await fetchPanelMessage(interaction, messageId);
  const entry = panel.roles.find((panelRole) => panelRole.roleId === role.id);

  if (!entry) {
    throw new Error('That role is not on this panel.');
  }

  if (!label && !style) {
    throw new Error('Give me a new label, style, or both.');
  }

  if (label) {
    entry.label = stripWrappingQuotes(label).slice(0, 80);
  }

  if (style) {
    entry.style = style;
  }

  await savePanel(panel);
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: `Updated the ${role} button.`,
    ephemeral: true
  });
}

async function setLogChannel(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const channel = interaction.options.getChannel('channel');
  const clear = interaction.options.getBoolean('clear') ?? false;
  const panel = await getPanel(messageId);

  if (!panel) {
    throw new Error('I could not find a saved role panel for that message ID.');
  }

  if (clear) {
    panel.logChannelId = null;
    await savePanel(panel);
    await interaction.reply({
      content: 'Cleared the log channel for that panel.',
      ephemeral: true
    });
    return;
  }

  if (!channel?.isTextBased()) {
    throw new Error('Choose a text channel, or set `clear` to true.');
  }

  panel.logChannelId = channel.id;
  await savePanel(panel);

  await interaction.reply({
    content: `Role changes for that panel will now be logged in ${channel}.`,
    ephemeral: true
  });
}

async function setCooldown(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const duration = interaction.options.getString('duration', true);
  const cooldownMs = parseDuration(duration);
  const { message, panel } = await fetchPanelMessage(interaction, messageId);

  panel.cooldownMs = cooldownMs;
  await savePanel(panel);
  if (!cooldownMs) {
    await deletePanelCooldowns(messageId);
  }
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: cooldownMs
      ? `Set that panel cooldown to ${formatDuration(cooldownMs)}.`
      : 'Disabled the cooldown for that panel.',
    ephemeral: true
  });
}

async function listPanel(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const panel = await getPanel(messageId);
  if (!panel) {
    throw new Error('I could not find a saved role panel for that message ID.');
  }

  const roles = panel.roles.length
    ? panel.roles.map((entry, index) => `${index + 1}. <@&${entry.roleId}> - ${entry.label}`).join('\n')
    : 'No roles configured.';

  await interaction.reply({
    content: [
      `Panel: \`${panel.messageId}\``,
      `Channel: <#${panel.channelId}>`,
      `Log channel: ${panel.logChannelId ? `<#${panel.logChannelId}>` : '`none`'}`,
      `Exclusive: \`${panel.exclusive ? 'yes' : 'no'}\``,
      `Cooldown: \`${formatDuration(panel.cooldownMs ?? 0)}\``,
      roles
    ].join('\n'),
    ephemeral: true
  });
}

async function refreshPanelCommand(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const { message, panel } = await fetchPanelMessage(interaction, messageId);
  await refreshPanelMessage(message, panel, true);

  await interaction.reply({
    content: 'Refreshed that panel and its role counts.',
    ephemeral: true
  });
}

async function deletePanelCommand(interaction) {
  assertAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const { message } = await fetchPanelMessage(interaction, messageId);
  await message.delete();
  await deletePanel(messageId);
  await deletePanelCooldowns(messageId);

  await interaction.reply({
    content: 'Deleted that role panel and removed its saved config.',
    ephemeral: true
  });
}

async function handleRolePanelCommand(interaction) {
  const subcommand = interaction.options.getSubcommand();

  if (subcommand === 'create') return createPanel(interaction);
  if (subcommand === 'add') return addRole(interaction);
  if (subcommand === 'remove') return removeRole(interaction);
  if (subcommand === 'edit') return editRole(interaction);
  if (subcommand === 'log-channel') return setLogChannel(interaction);
  if (subcommand === 'cooldown') return setCooldown(interaction);
  if (subcommand === 'list') return listPanel(interaction);
  if (subcommand === 'refresh') return refreshPanelCommand(interaction);
  if (subcommand === 'delete') return deletePanelCommand(interaction);

  throw new Error('Unknown role panel command.');
}

async function handleRoleButton(interaction) {
  const [, messageId, roleId] = interaction.customId.split(':');
  const panel = await getPanel(messageId);

  if (!panel) {
    await interaction.reply({
      content: 'That role panel is no longer configured.',
      ephemeral: true
    });
    return;
  }

  await runWithPanelUserLock(panel, interaction.user.id, () => handleLockedRoleButton(interaction, panel, roleId));
}

async function handleLockedRoleButton(interaction, panel, roleId) {
  if (!panel.roles.some((entry) => entry.roleId === roleId)) {
    await interaction.reply({
      content: 'That role is no longer part of this panel.',
      ephemeral: true
    });
    return;
  }

  const selectedRole = await interaction.guild.roles.fetch(roleId);
  if (!selectedRole) {
    await interaction.reply({
      content: 'That role no longer exists on this server.',
      ephemeral: true
    });
    return;
  }

  const member = await interaction.guild.members.fetch(interaction.user.id);
  const hasSelectedRole = member.roles.cache.has(roleId);
  const cooldownReady = await assertCooldownReady(interaction, panel);
  if (!cooldownReady) {
    return;
  }

  if (hasSelectedRole) {
    await member.roles.remove(roleId, 'Role panel button toggle');
    await recordCooldown(interaction, panel, null);
    await interaction.reply({
      content: `Removed ${selectedRole.name}.`,
      ephemeral: true
    });
    await refreshPanelFromInteraction(interaction, panel);
    await sendRoleLog(interaction, panel, [], [selectedRole.name]);
    return;
  }

  if (panel.exclusive) {
    const otherRoleIds = panel.roles
      .map((entry) => entry.roleId)
      .filter((panelRoleId) => panelRoleId !== roleId && member.roles.cache.has(panelRoleId));

    if (otherRoleIds.length) {
      await member.roles.remove(otherRoleIds, 'Exclusive role panel selection changed');
    }

    const removedRoleNames = roleNamesFromIds(interaction.guild, otherRoleIds);

    await member.roles.add(roleId, 'Role panel button selection');
    await recordCooldown(interaction, panel, roleId);
    await interaction.reply({
      content: removedRoleNames.length
        ? `Added ${selectedRole.name}. Removed ${removedRoleNames.join(', ')}.`
        : `Added ${selectedRole.name}.`,
      ephemeral: true
    });
    await refreshPanelFromInteraction(interaction, panel);
    await sendRoleLog(interaction, panel, [selectedRole.name], removedRoleNames);
    return;
  }

  await member.roles.add(roleId, 'Role panel button selection');
  await recordCooldown(interaction, panel, roleId);
  await interaction.reply({
    content: `Added ${selectedRole.name}.`,
    ephemeral: true
  });
  await refreshPanelFromInteraction(interaction, panel);
  await sendRoleLog(interaction, panel, [selectedRole.name], []);
}

function isRolePanelButton(interaction) {
  return interaction.isButton() && interaction.customId.startsWith(`${BUTTON_ID_PREFIX}:`);
}

module.exports = {
  handleRoleButton,
  handleRolePanelCommand,
  isRolePanelButton
};
