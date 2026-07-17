const {
  ActionRowBuilder,
  EmbedBuilder,
  PermissionFlagsBits,
  StringSelectMenuBuilder,
  UserSelectMenuBuilder
} = require('discord.js');

const {
  deleteRankPanel,
  getRankPanel,
  saveRankPanel
} = require('./storage');

const MAX_RANK_ROLES = 25;
const MAX_MEMBER_OPTIONS = 25;
const RANK_PANEL_ID_PREFIX = 'rank-panel';
const MEMBER_CACHE_REFRESH_MS = 10 * 60 * 1000;
const memberCacheRefreshes = new Map();

function trimLabel(value, maxLength = 100) {
  return value.length > maxLength ? value.slice(0, maxLength - 3).trimEnd() + '...' : value;
}

function assertRankPanelAdmin(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('You need the Manage Roles permission to configure rank panels.');
  }
}

async function assertBotCanManageRole(interaction, role) {
  if (role.id === interaction.guild.id) {
    throw new Error('The @everyone role cannot be managed by a rank panel.');
  }

  if (role.managed) {
    throw new Error('Managed integration roles cannot be assigned manually.');
  }

  const botMember = interaction.guild.members.me ?? await interaction.guild.members.fetchMe();
  if (!botMember.permissions.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('I need the Manage Roles permission before I can update ranks.');
  }

  if (botMember.roles.highest.comparePositionTo(role) <= 0) {
    throw new Error(`Move my highest role above ${role.name} before adding it to a rank panel.`);
  }
}

function hasRole(member, roleId) {
  return member.roles.cache.has(roleId);
}

function isServerAdmin(member) {
  return member.permissions.has(PermissionFlagsBits.ManageRoles);
}

function formatRankEntry(entry, index) {
  const extraRole = entry.extraRoleId ? ` + <@&${entry.extraRoleId}>` : '';
  return `${index + 1}. <@&${entry.roleId}>${extraRole}`;
}

function managedRoleIds(panel) {
  return new Set(panel.rankRoles.flatMap((entry) => [entry.roleId, entry.extraRoleId].filter(Boolean)));
}

async function ensureMemberCache(guild, allowFetch = false) {
  if (!guild) {
    return false;
  }

  const lastRefresh = memberCacheRefreshes.get(guild.id) ?? 0;
  const cacheLooksComplete = guild.memberCount ? guild.members.cache.size >= guild.memberCount : false;
  if (cacheLooksComplete || !allowFetch || Date.now() - lastRefresh < MEMBER_CACHE_REFRESH_MS) {
    return cacheLooksComplete;
  }

  try {
    await guild.members.fetch();
    memberCacheRefreshes.set(guild.id, Date.now());
    return true;
  } catch (error) {
    console.error('Failed to refresh member cache for rank panel:', error);
    memberCacheRefreshes.set(guild.id, Date.now());
    return false;
  }
}

function assertLeaderCanUsePanel(panel, leader) {
  if (isServerAdmin(leader)) {
    return;
  }

  if (!hasRole(leader, panel.leadershipRoleId)) {
    throw new Error(`You need <@&${panel.leadershipRoleId}> to use this panel.`);
  }

  if (!hasRole(leader, panel.teamRoleId)) {
    throw new Error(`You need <@&${panel.teamRoleId}> to manage this team.`);
  }
}

function assertLeaderCanManageTarget(panel, leader, target) {
  if (!hasRole(target, panel.teamRoleId)) {
    throw new Error(`${target} does not have the configured team role <@&${panel.teamRoleId}>.`);
  }

  if (target.id === leader.id) {
    throw new Error('You cannot change your own rank from this panel.');
  }

  if (target.user.bot) {
    throw new Error('Rank panels can only manage server members, not bots.');
  }

  if (isServerAdmin(leader)) {
    return;
  }

  const highestComparison = target.roles.highest.comparePositionTo(leader.roles.highest);
  const highestTargetRoleIsManaged = managedRoleIds(panel).has(target.roles.highest.id);

  if (highestComparison > 0 || (highestComparison === 0 && !highestTargetRoleIsManaged)) {
    throw new Error('You cannot manage someone with an equal or higher highest role than you.');
  }
}

function rankPanelEmbed(panel) {
  const rankList = panel.rankRoles.length
    ? panel.rankRoles.map(formatRankEntry).join('\n')
    : 'No rank roles have been added yet.';

  return new EmbedBuilder()
    .setTitle(panel.title)
    .setDescription(panel.description)
    .setColor(0xf1c40f)
    .addFields(
      { name: 'Leadership role', value: `<@&${panel.leadershipRoleId}>`, inline: true },
      { name: 'Team role', value: `<@&${panel.teamRoleId}>`, inline: true },
      { name: 'Manageable ranks', value: rankList }
    )
    .setFooter({ text: 'Select a team member below, then choose their new rank.' });
}

async function teamMemberOptions(guild, panel, allowMemberFetch = false) {
  const cacheReady = await ensureMemberCache(guild, allowMemberFetch);
  const teamRole = await guild.roles.fetch(panel.teamRoleId).catch(() => null);
  if (!teamRole) {
    return { cacheReady, members: [] };
  }

  const members = teamRole.members
    .filter((member) => !member.user.bot)
    .sort((left, right) => left.displayName.localeCompare(right.displayName))
    .map((member) => ({
      label: trimLabel(member.displayName),
      value: member.id,
      description: trimLabel(member.user.tag)
    }));

  return { cacheReady, members };
}

async function rankPanelComponents(panel, guild, allowMemberFetch = false) {
  const { cacheReady, members } = await teamMemberOptions(guild, panel, allowMemberFetch);

  if (cacheReady && members.length && members.length <= MAX_MEMBER_OPTIONS) {
    return [
      new ActionRowBuilder().addComponents(
        new StringSelectMenuBuilder()
          .setCustomId(`${RANK_PANEL_ID_PREFIX}:member:${panel.messageId}`)
          .setPlaceholder('Select a team member')
          .setMinValues(1)
          .setMaxValues(1)
          .addOptions(members)
      )
    ];
  }

  if (cacheReady && !members.length) {
    return [
      new ActionRowBuilder().addComponents(
        new StringSelectMenuBuilder()
          .setCustomId(`${RANK_PANEL_ID_PREFIX}:empty:${panel.messageId}`)
          .setPlaceholder('No team members found')
          .setMinValues(1)
          .setMaxValues(1)
          .setDisabled(true)
          .addOptions({
            label: 'No team members found',
            value: 'none',
            description: 'Add members to the configured team role.'
          })
      )
    ];
  }

  return [
    new ActionRowBuilder().addComponents(
      new UserSelectMenuBuilder()
        .setCustomId(`${RANK_PANEL_ID_PREFIX}:user:${panel.messageId}`)
        .setPlaceholder('Search a team member')
        .setMinValues(1)
        .setMaxValues(1)
    )
  ];
}

function rankSelectComponents(panel, targetId) {
  const options = panel.rankRoles.map((entry) => ({
    label: trimLabel(entry.label),
    value: entry.roleId,
    description: trimLabel(entry.extraRoleId
      ? `Set rank and also assign ${entry.extraRoleName ?? 'extra role'}`
      : `Set ${entry.label} as this member's rank`)
  }));

  return [
    new ActionRowBuilder().addComponents(
      new StringSelectMenuBuilder()
        .setCustomId(`${RANK_PANEL_ID_PREFIX}:rank:${panel.messageId}:${targetId}`)
        .setPlaceholder('Choose the new rank')
        .setMinValues(1)
        .setMaxValues(1)
        .addOptions(options)
    )
  ];
}

async function fetchRankPanelMessage(interaction, messageId) {
  const panel = await getRankPanel(messageId);
  if (!panel) {
    throw new Error('I could not find a saved rank panel for that message ID.');
  }

  const channel = await interaction.guild.channels.fetch(panel.channelId);
  if (!channel?.isTextBased()) {
    throw new Error('The saved rank panel channel is missing or is not a text channel.');
  }

  const message = await channel.messages.fetch(panel.messageId);
  return { message, panel };
}

async function refreshRankPanelMessage(message, panel, options = {}) {
  await message.edit({
    embeds: [rankPanelEmbed(panel)],
    components: await rankPanelComponents(panel, message.guild, options.allowMemberFetch ?? false)
  });
}

async function tryRefreshRankPanelMessage(message, panel, options = {}) {
  try {
    await refreshRankPanelMessage(message, panel, options);
    return true;
  } catch (error) {
    console.error('Failed to refresh rank panel message:', error);
    return false;
  }
}

async function createRankPanel(interaction) {
  assertRankPanelAdmin(interaction);

  const targetChannel = interaction.options.getChannel('channel') ?? interaction.channel;
  const logChannel = interaction.options.getChannel('log-channel');
  const leadershipRole = interaction.options.getRole('leadership-role', true);
  const teamRole = interaction.options.getRole('team-role', true);

  if (!targetChannel?.isTextBased()) {
    throw new Error('Please choose a text channel for the rank panel.');
  }

  if (logChannel && !logChannel.isTextBased()) {
    throw new Error('Please choose a text channel for rank panel logs.');
  }

  const panel = {
    guildId: interaction.guildId,
    channelId: targetChannel.id,
    logChannelId: logChannel?.id ?? null,
    messageId: null,
    title: interaction.options.getString('title', true),
    description: interaction.options.getString('description', true),
    leadershipRoleId: leadershipRole.id,
    teamRoleId: teamRole.id,
    rankRoles: [],
    createdBy: interaction.user.id,
    createdAt: new Date().toISOString()
  };

  const message = await targetChannel.send({
    embeds: [rankPanelEmbed(panel)],
    components: []
  });

  panel.messageId = message.id;
  await saveRankPanel(panel);
  const refreshed = await tryRefreshRankPanelMessage(message, panel);

  await interaction.reply({
    content: [
      `Rank panel created in ${targetChannel}.`,
      `Leadership: ${leadershipRole}`,
      `Team: ${teamRole}`,
      logChannel ? `Rank changes will be logged in ${logChannel}.` : null,
      `Message ID: \`${message.id}\``,
      refreshed ? null : 'The panel was saved, but I could not refresh the message controls yet. Try `/rank-panel refresh` in a moment.',
      'Add ranks with `/rank-panel add-rank`.'
    ].filter(Boolean).join('\n'),
    ephemeral: true
  });
}

async function addRankRole(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const extraRole = interaction.options.getRole('extra-role');
  const label = interaction.options.getString('label') ?? role.name;
  const { message, panel } = await fetchRankPanelMessage(interaction, messageId);
  if (panel.rankRoles.length >= MAX_RANK_ROLES) {
    throw new Error(`This rank panel already has ${MAX_RANK_ROLES} rank roles.`);
  }

  if (panel.rankRoles.some((entry) => entry.roleId === role.id)) {
    const refreshed = await tryRefreshRankPanelMessage(message, panel);
    await interaction.reply({
      content: refreshed
        ? `That role is already manageable on this rank panel. I refreshed the panel display.`
        : `That role is already manageable on this rank panel. The panel display may be stale; try \`/rank-panel refresh\` in a moment.`,
      ephemeral: true
    });
    return;
  }

  await assertBotCanManageRole(interaction, role);
  if (extraRole) {
    await assertBotCanManageRole(interaction, extraRole);
  }

  if (extraRole?.id === role.id) {
    throw new Error('The extra role must be different from the main rank role.');
  }

  panel.rankRoles.push({
    roleId: role.id,
    extraRoleId: extraRole?.id ?? null,
    extraRoleName: extraRole?.name ?? null,
    label: trimLabel(label)
  });

  await saveRankPanel(panel);
  const refreshed = await tryRefreshRankPanelMessage(message, panel);

  await interaction.reply({
    content: [
      extraRole
        ? `Added ${role} as a manageable rank with ${extraRole} as its extra role.`
        : `Added ${role} as a manageable rank.`,
      refreshed ? null : 'Saved successfully, but I could not refresh the panel message yet. Try `/rank-panel refresh` in a moment.'
    ].filter(Boolean).join('\n'),
    ephemeral: true
  });
}

async function editRankPanel(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const title = interaction.options.getString('title');
  const description = interaction.options.getString('description');
  const { message, panel } = await fetchRankPanelMessage(interaction, messageId);

  if (!title && !description) {
    throw new Error('Give me a new title, description, or both.');
  }

  if (title) {
    panel.title = title;
  }

  if (description) {
    panel.description = description;
  }

  await saveRankPanel(panel);
  const refreshed = await tryRefreshRankPanelMessage(message, panel);

  await interaction.reply({
    content: refreshed
      ? 'Updated that rank panel.'
      : 'Updated that rank panel, but I could not refresh the panel message yet. Try `/rank-panel refresh` in a moment.',
    ephemeral: true
  });
}

async function removeRankRole(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const { message, panel } = await fetchRankPanelMessage(interaction, messageId);
  const nextRankRoles = panel.rankRoles.filter((entry) => entry.roleId !== role.id);

  if (nextRankRoles.length === panel.rankRoles.length) {
    throw new Error('That role is not on this rank panel.');
  }

  panel.rankRoles = nextRankRoles;
  await saveRankPanel(panel);
  const refreshed = await tryRefreshRankPanelMessage(message, panel);

  await interaction.reply({
    content: refreshed
      ? `Removed ${role} from the manageable ranks.`
      : `Removed ${role} from the manageable ranks. The panel display may be stale; try \`/rank-panel refresh\` in a moment.`,
    ephemeral: true
  });
}

async function editRankRole(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const role = interaction.options.getRole('role', true);
  const label = interaction.options.getString('label');
  const extraRole = interaction.options.getRole('extra-role');
  const clearExtraRole = interaction.options.getBoolean('clear-extra-role') ?? false;
  const { message, panel } = await fetchRankPanelMessage(interaction, messageId);
  const entry = panel.rankRoles.find((rankRole) => rankRole.roleId === role.id);

  if (!entry) {
    throw new Error('That role is not on this rank panel.');
  }

  if (!label && !extraRole && !clearExtraRole) {
    throw new Error('Give me a new label, extra role, or set `clear-extra-role` to true.');
  }

  if (extraRole && clearExtraRole) {
    throw new Error('Choose either `extra-role` or `clear-extra-role`, not both.');
  }

  if (extraRole?.id === role.id) {
    throw new Error('The extra role must be different from the main rank role.');
  }

  if (label) {
    entry.label = trimLabel(label);
  }

  if (extraRole) {
    await assertBotCanManageRole(interaction, extraRole);
    entry.extraRoleId = extraRole.id;
    entry.extraRoleName = extraRole.name;
  }

  if (clearExtraRole) {
    entry.extraRoleId = null;
    entry.extraRoleName = null;
  }

  await saveRankPanel(panel);
  const refreshed = await tryRefreshRankPanelMessage(message, panel);

  const extraRoleText = entry.extraRoleId ? ` Extra role: <@&${entry.extraRoleId}>.` : ' No extra role.';
  await interaction.reply({
    content: [
      `Updated ${role} on that rank panel. Label: \`${entry.label}\`.${extraRoleText}`,
      refreshed ? null : 'Saved successfully, but I could not refresh the panel message yet. Try `/rank-panel refresh` in a moment.'
    ].filter(Boolean).join('\n'),
    ephemeral: true
  });
}

async function listRankPanel(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const panel = await getRankPanel(messageId);
  if (!panel) {
    throw new Error('I could not find a saved rank panel for that message ID.');
  }

  const ranks = panel.rankRoles.length
    ? panel.rankRoles.map((entry, index) => {
      const extraRole = entry.extraRoleId ? ` + <@&${entry.extraRoleId}>` : '';
      return `${index + 1}. <@&${entry.roleId}>${extraRole} - ${entry.label}`;
    }).join('\n')
    : 'No rank roles configured.';

  await interaction.reply({
    content: [
      `Rank panel: \`${panel.messageId}\``,
      `Channel: <#${panel.channelId}>`,
      `Leadership role: <@&${panel.leadershipRoleId}>`,
      `Team role: <@&${panel.teamRoleId}>`,
      `Log channel: ${panel.logChannelId ? `<#${panel.logChannelId}>` : '`none`'}`,
      ranks
    ].join('\n'),
    ephemeral: true
  });
}

async function refreshRankPanelCommand(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const { message, panel } = await fetchRankPanelMessage(interaction, messageId);
  await refreshRankPanelMessage(message, panel, { allowMemberFetch: true });

  await interaction.reply({
    content: 'Refreshed that rank panel.',
    ephemeral: true
  });
}

async function deleteRankPanelCommand(interaction) {
  assertRankPanelAdmin(interaction);

  const messageId = interaction.options.getString('message-id', true);
  const { message } = await fetchRankPanelMessage(interaction, messageId);
  await message.delete();
  await deleteRankPanel(messageId);

  await interaction.reply({
    content: 'Deleted that rank panel and removed its saved config.',
    ephemeral: true
  });
}

async function handleRankPanelCommand(interaction) {
  const subcommand = interaction.options.getSubcommand();

  if (subcommand === 'create') return createRankPanel(interaction);
  if (subcommand === 'add-rank') return addRankRole(interaction);
  if (subcommand === 'edit') return editRankPanel(interaction);
  if (subcommand === 'remove-rank') return removeRankRole(interaction);
  if (subcommand === 'edit-rank') return editRankRole(interaction);
  if (subcommand === 'list') return listRankPanel(interaction);
  if (subcommand === 'refresh') return refreshRankPanelCommand(interaction);
  if (subcommand === 'delete') return deleteRankPanelCommand(interaction);

  throw new Error('Unknown rank panel command.');
}

async function promptForRank(interaction, panel, targetId) {
  const leader = await interaction.guild.members.fetch(interaction.user.id);
  assertLeaderCanUsePanel(panel, leader);

  const target = await interaction.guild.members.fetch(targetId);
  assertLeaderCanManageTarget(panel, leader, target);

  const currentRanks = panel.rankRoles
    .filter((entry) => target.roles.cache.has(entry.roleId))
    .map((entry) => `<@&${entry.roleId}>`);

  await interaction.reply({
    content: [
      `Managing ${target}.`,
      `Current panel rank: ${currentRanks.length ? currentRanks.join(', ') : '`none`'}`
    ].join('\n'),
    components: rankSelectComponents(panel, target.id),
    ephemeral: true
  });
}

async function handleRankMemberSelect(interaction) {
  const [, , messageId] = interaction.customId.split(':');
  const panel = await getRankPanel(messageId);
  if (!panel) {
    await interaction.reply({
      content: 'That rank panel is no longer configured.',
      ephemeral: true
    });
    return;
  }

  if (!panel.rankRoles.length) {
    await interaction.reply({
      content: 'No rank roles have been added to this panel yet.',
      ephemeral: true
    });
    return;
  }

  const targetId = interaction.values[0];
  await promptForRank(interaction, panel, targetId);
}

async function handleRankUserSelect(interaction) {
  const [, , messageId] = interaction.customId.split(':');
  const panel = await getRankPanel(messageId);
  if (!panel) {
    await interaction.reply({
      content: 'That rank panel is no longer configured.',
      ephemeral: true
    });
    return;
  }

  if (!panel.rankRoles.length) {
    await interaction.reply({
      content: 'No rank roles have been added to this panel yet.',
      ephemeral: true
    });
    return;
  }

  const targetId = interaction.values[0];
  await promptForRank(interaction, panel, targetId);
}

async function sendRankLog(interaction, panel, target, addedRoles, removedRoleIds) {
  if (!panel.logChannelId) {
    return;
  }

  const logChannel = await interaction.guild.channels.fetch(panel.logChannelId).catch(() => null);
  if (!logChannel?.isTextBased()) {
    return;
  }

  const removedText = removedRoleIds.length
    ? removedRoleIds.map((roleId) => `<@&${roleId}>`).join(', ')
    : 'None';
  const addedText = addedRoles.length
    ? addedRoles.map((role) => `${role}`).join(', ')
    : 'None';

  const embed = new EmbedBuilder()
    .setTitle('Rank updated')
    .setColor(0x57f287)
    .setDescription(`${interaction.user} updated ${target}'s rank.`)
    .addFields(
      { name: 'Assigned roles', value: addedText },
      { name: 'Removed managed roles', value: removedText },
      { name: 'Team', value: `<@&${panel.teamRoleId}>` }
    )
    .setTimestamp();

  await logChannel.send({ embeds: [embed] }).catch((error) => {
    console.error('Failed to send rank panel log:', error);
  });
}

async function handleRankSelect(interaction) {
  const [, , messageId, targetId] = interaction.customId.split(':');
  const panel = await getRankPanel(messageId);
  if (!panel) {
    await interaction.reply({
      content: 'That rank panel is no longer configured.',
      ephemeral: true
    });
    return;
  }

  const selectedRoleId = interaction.values[0];
  const selectedEntry = panel.rankRoles.find((entry) => entry.roleId === selectedRoleId);
  if (!selectedEntry) {
    await interaction.reply({
      content: 'That role is no longer manageable from this panel.',
      ephemeral: true
    });
    return;
  }

  await interaction.deferReply({ ephemeral: true });

  const leader = await interaction.guild.members.fetch(interaction.user.id);
  const target = await interaction.guild.members.fetch(targetId);
  const selectedRole = await interaction.guild.roles.fetch(selectedRoleId);
  const extraRole = selectedEntry.extraRoleId
    ? await interaction.guild.roles.fetch(selectedEntry.extraRoleId)
    : null;

  if (!selectedRole) {
    await interaction.editReply('That rank role no longer exists on this server.');
    return;
  }

  if (selectedEntry.extraRoleId && !extraRole) {
    await interaction.editReply('The extra role for that rank no longer exists on this server.');
    return;
  }

  assertLeaderCanUsePanel(panel, leader);
  assertLeaderCanManageTarget(panel, leader, target);
  await assertBotCanManageRole(interaction, selectedRole);
  if (extraRole) {
    await assertBotCanManageRole(interaction, extraRole);
  }

  const rolesToKeep = new Set([selectedRoleId]);
  if (selectedEntry.extraRoleId) {
    rolesToKeep.add(selectedEntry.extraRoleId);
  }

  const removedRoleIds = panel.rankRoles
    .flatMap((entry) => [entry.roleId, entry.extraRoleId].filter(Boolean))
    .filter((roleId, index, roleIds) => roleIds.indexOf(roleId) === index)
    .filter((roleId) => !rolesToKeep.has(roleId) && target.roles.cache.has(roleId));

  if (removedRoleIds.length) {
    await target.roles.remove(removedRoleIds, `Rank panel update by ${interaction.user.tag}`);
  }

  const rolesToAdd = [selectedRole, extraRole].filter(Boolean);
  const missingRoleIds = rolesToAdd
    .map((role) => role.id)
    .filter((roleId) => !target.roles.cache.has(roleId));

  if (missingRoleIds.length) {
    await target.roles.add(missingRoleIds, `Rank panel update by ${interaction.user.tag}`);
  }

  const removedText = removedRoleIds.length
    ? ` Removed ${removedRoleIds.map((roleId) => `<@&${roleId}>`).join(', ')}.`
    : '';
  const addedText = rolesToAdd.map((role) => `${role}`).join(' and ');

  await interaction.editReply(`Updated ${target} to ${addedText}.${removedText}`);
  await sendRankLog(interaction, panel, target, rolesToAdd, removedRoleIds);
}

function isRankPanelUserSelect(interaction) {
  return interaction.isUserSelectMenu() && interaction.customId.startsWith(`${RANK_PANEL_ID_PREFIX}:user:`);
}

function isRankPanelMemberSelect(interaction) {
  return interaction.isStringSelectMenu() && interaction.customId.startsWith(`${RANK_PANEL_ID_PREFIX}:member:`);
}

function isRankPanelRankSelect(interaction) {
  return interaction.isStringSelectMenu() && interaction.customId.startsWith(`${RANK_PANEL_ID_PREFIX}:rank:`);
}

module.exports = {
  handleRankMemberSelect,
  handleRankPanelCommand,
  handleRankSelect,
  handleRankUserSelect,
  isRankPanelMemberSelect,
  isRankPanelRankSelect,
  isRankPanelUserSelect
};
