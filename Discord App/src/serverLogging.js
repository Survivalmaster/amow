const {
  AuditLogEvent,
  ChannelType,
  EmbedBuilder,
  PermissionFlagsBits
} = require('discord.js');

const { getGuildSettings, saveGuildSettings } = require('./storage');

const deletedMessageIds = new Set();

function assertLoggingAdmin(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.ManageGuild)) {
    throw new Error('You need the Manage Server permission to configure logging.');
  }
}

function trimValue(value, maxLength = 1000) {
  if (!value) {
    return null;
  }

  return value.length > maxLength ? `${value.slice(0, maxLength - 3)}...` : value;
}

async function getConfiguredLogChannel(guild, settingKey) {
  const settings = await getGuildSettings(guild.id);
  const channelId = settings[settingKey];
  if (!channelId) {
    return null;
  }

  const channel = await guild.channels.fetch(channelId).catch(() => null);
  return channel?.isTextBased() ? channel : null;
}

async function sendLog(guild, embed) {
  const channel = await getConfiguredLogChannel(guild, 'logChannelId');
  if (!channel) {
    return;
  }

  await channel.send({ embeds: [embed] }).catch((error) => {
    console.error('Failed to send server log:', error);
  });
}

async function sendMemberLog(guild, settingKey, embed) {
  const channel = await getConfiguredLogChannel(guild, settingKey);
  if (!channel) {
    return;
  }

  await channel.send({ embeds: [embed] }).catch((error) => {
    console.error('Failed to send member log:', error);
  });
}

async function findAuditExecutor(guild, type, targetId) {
  if (!guild.members.me?.permissions.has(PermissionFlagsBits.ViewAuditLog)) {
    return null;
  }

  const logs = await guild.fetchAuditLogs({ type, limit: 5 }).catch(() => null);
  const entry = logs?.entries.find((auditEntry) => (
    auditEntry.target?.id === targetId && Date.now() - auditEntry.createdTimestamp < 10_000
  ));

  return entry?.executor ?? null;
}

function channelKind(channel) {
  const names = {
    [ChannelType.GuildText]: 'Text channel',
    [ChannelType.GuildAnnouncement]: 'Announcement channel',
    [ChannelType.GuildVoice]: 'Voice channel',
    [ChannelType.GuildStageVoice]: 'Stage channel',
    [ChannelType.GuildForum]: 'Forum channel',
    [ChannelType.GuildMedia]: 'Media channel',
    [ChannelType.GuildCategory]: 'Category'
  };

  return names[channel.type] ?? 'Channel';
}

function channelLabel(channel) {
  return channel.type === ChannelType.GuildCategory ? channel.name : `#${channel.name}`;
}

async function handleLoggingCommand(interaction) {
  assertLoggingAdmin(interaction);

  const subcommand = interaction.options.getSubcommand();

  if (subcommand === 'set') {
    const channel = interaction.options.getChannel('channel', true);
    if (!channel.isTextBased()) {
      throw new Error('Choose a text channel for logs.');
    }

    await saveGuildSettings(interaction.guildId, { logChannelId: channel.id });
    await interaction.reply({
      content: `Server logs will now be posted in ${channel}.`,
      ephemeral: true
    });
    return;
  }

  if (subcommand === 'off') {
    await saveGuildSettings(interaction.guildId, { logChannelId: null });
    await interaction.reply({
      content: 'Server logging has been turned off.',
      ephemeral: true
    });
    return;
  }

  if (subcommand === 'joins' || subcommand === 'leaves') {
    const channel = interaction.options.getChannel('channel');
    const clear = interaction.options.getBoolean('clear') ?? false;
    const settingKey = subcommand === 'joins' ? 'joinLogChannelId' : 'leaveLogChannelId';
    const label = subcommand === 'joins' ? 'join' : 'leave';

    if (clear) {
      await saveGuildSettings(interaction.guildId, { [settingKey]: null });
      await interaction.reply({
        content: `Cleared the ${label} log channel.`,
        ephemeral: true
      });
      return;
    }

    if (!channel?.isTextBased()) {
      throw new Error('Choose a text channel, or set `clear` to true.');
    }

    await saveGuildSettings(interaction.guildId, { [settingKey]: channel.id });
    await interaction.reply({
      content: `Member ${label} logs will now be posted in ${channel}.`,
      ephemeral: true
    });
    return;
  }

  if (subcommand === 'status') {
    const settings = await getGuildSettings(interaction.guildId);
    await interaction.reply({
      content: [
        `General logs: ${settings.logChannelId ? `<#${settings.logChannelId}>` : '`off`'}`,
        `Join logs: ${settings.joinLogChannelId ? `<#${settings.joinLogChannelId}>` : '`off`'}`,
        `Leave logs: ${settings.leaveLogChannelId ? `<#${settings.leaveLogChannelId}>` : '`off`'}`
      ].join('\n'),
      ephemeral: true
    });
    return;
  }

  throw new Error('Unknown logging command.');
}

async function logMemberJoin(member) {
  const embed = new EmbedBuilder()
    .setAuthor({ name: member.user.tag, iconURL: member.user.displayAvatarURL() })
    .setTitle('Member joined')
    .setDescription(`${member} joined the server.`)
    .setColor(0x57f287)
    .addFields(
      { name: 'User ID', value: member.id, inline: true },
      { name: 'Account created', value: `<t:${Math.floor(member.user.createdTimestamp / 1000)}:R>`, inline: true }
    )
    .setTimestamp();

  await sendMemberLog(member.guild, 'joinLogChannelId', embed);
}

async function logMemberLeave(member) {
  const roles = member.roles.cache
    .filter((role) => role.id !== member.guild.id)
    .sort((left, right) => right.position - left.position)
    .map((role) => role.name);

  const embed = new EmbedBuilder()
    .setAuthor({ name: member.user.tag, iconURL: member.user.displayAvatarURL() })
    .setTitle('Member left')
    .setDescription(`${member.user.tag} left the server.`)
    .setColor(0xed4245)
    .addFields(
      { name: 'User ID', value: member.id, inline: true },
      { name: 'Joined', value: member.joinedTimestamp ? `<t:${Math.floor(member.joinedTimestamp / 1000)}:R>` : 'Unknown', inline: true },
      { name: 'Roles', value: trimValue(roles.join(', '), 1000) ?? 'No roles' }
    )
    .setTimestamp();

  await sendMemberLog(member.guild, 'leaveLogChannelId', embed);
}

async function logMessageDelete(message) {
  if (!message.guild || message.author?.bot || deletedMessageIds.has(message.id)) {
    return;
  }

  deletedMessageIds.add(message.id);
  setTimeout(() => deletedMessageIds.delete(message.id), 30_000);

  const executor = await findAuditExecutor(message.guild, AuditLogEvent.MessageDelete, message.author?.id);
  const description = trimValue(message.content) ?? 'No message content available.';
  const embed = new EmbedBuilder()
    .setAuthor({
      name: message.author?.tag ?? 'Unknown user',
      iconURL: message.author?.displayAvatarURL()
    })
    .setTitle(`Message deleted in #${message.channel?.name ?? 'unknown-channel'}`)
    .setDescription(description)
    .setColor(0xed4245)
    .addFields(
      { name: 'Message ID', value: message.id, inline: true },
      { name: 'Channel ID', value: message.channelId, inline: true }
    )
    .setFooter({ text: executor ? `Deleted by ${executor.tag}` : `ID: ${message.author?.id ?? 'unknown'}` })
    .setTimestamp();

  await sendLog(message.guild, embed);
}

async function logRoleChanges(oldMember, newMember) {
  const oldRoleIds = new Set(oldMember.roles.cache.keys());
  const newRoleIds = new Set(newMember.roles.cache.keys());
  const addedRoles = newMember.roles.cache.filter((role) => !oldRoleIds.has(role.id) && role.id !== newMember.guild.id);
  const removedRoles = oldMember.roles.cache.filter((role) => !newRoleIds.has(role.id) && role.id !== newMember.guild.id);

  for (const role of addedRoles.values()) {
    const embed = new EmbedBuilder()
      .setAuthor({ name: newMember.user.tag, iconURL: newMember.user.displayAvatarURL() })
      .setTitle('Role added')
      .setDescription(`${role}`)
      .setColor(0x5865f2)
      .setFooter({ text: `ID: ${newMember.id}` })
      .setTimestamp();

    await sendLog(newMember.guild, embed);
  }

  for (const role of removedRoles.values()) {
    const embed = new EmbedBuilder()
      .setAuthor({ name: newMember.user.tag, iconURL: newMember.user.displayAvatarURL() })
      .setTitle('Role removed')
      .setDescription(`${role}`)
      .setColor(0x5865f2)
      .setFooter({ text: `ID: ${newMember.id}` })
      .setTimestamp();

    await sendLog(newMember.guild, embed);
  }
}

function describeOverwriteChange(oldChannel, newChannel) {
  const oldEveryone = oldChannel.permissionOverwrites.cache.get(oldChannel.guild.id);
  const newEveryone = newChannel.permissionOverwrites.cache.get(newChannel.guild.id);
  const oldSend = oldEveryone?.deny.has(PermissionFlagsBits.SendMessages) ? 'denied' : 'not denied';
  const newSend = newEveryone?.deny.has(PermissionFlagsBits.SendMessages) ? 'denied' : 'not denied';

  if (oldSend !== newSend) {
    return `Send messages for @everyone changed from ${oldSend} to ${newSend}.`;
  }

  return null;
}

async function logChannelUpdate(oldChannel, newChannel) {
  if (!newChannel.guild) {
    return;
  }

  const changes = [];
  if (oldChannel.name !== newChannel.name) {
    changes.push(`Name: ${channelLabel(oldChannel)} -> ${channelLabel(newChannel)}`);
  }

  if ('topic' in oldChannel && oldChannel.topic !== newChannel.topic) {
    changes.push(`Topic changed.`);
  }

  if ('permissionOverwrites' in oldChannel && 'permissionOverwrites' in newChannel) {
    const overwriteChange = describeOverwriteChange(oldChannel, newChannel);
    if (overwriteChange) {
      changes.push(overwriteChange);
    }
  }

  if (!changes.length) {
    return;
  }

  const embed = new EmbedBuilder()
    .setTitle(`${channelKind(newChannel)} updated`)
    .setDescription(`${channelLabel(newChannel)} updated`)
    .setColor(0x5865f2)
    .addFields({ name: 'Changes', value: changes.join('\n') })
    .setFooter({ text: `Channel ID: ${newChannel.id}` })
    .setTimestamp();

  await sendLog(newChannel.guild, embed);
}

async function logChannelCreate(channel) {
  if (!channel.guild) {
    return;
  }

  const executor = await findAuditExecutor(channel.guild, AuditLogEvent.ChannelCreate, channel.id);
  const embed = new EmbedBuilder()
    .setTitle(`${channelKind(channel)} created`)
    .setDescription(`${channelLabel(channel)} was created.`)
    .setColor(0x57f287)
    .addFields(
      { name: 'Channel ID', value: channel.id, inline: true },
      { name: 'Type', value: channelKind(channel), inline: true }
    )
    .setFooter({ text: executor ? `Created by ${executor.tag}` : 'Creator unknown' })
    .setTimestamp();

  await sendLog(channel.guild, embed);
}

async function logChannelDelete(channel) {
  if (!channel.guild) {
    return;
  }

  const executor = await findAuditExecutor(channel.guild, AuditLogEvent.ChannelDelete, channel.id);
  const embed = new EmbedBuilder()
    .setTitle(`${channelKind(channel)} deleted`)
    .setDescription(`${channelLabel(channel)} was deleted.`)
    .setColor(0xed4245)
    .addFields(
      { name: 'Channel ID', value: channel.id, inline: true },
      { name: 'Type', value: channelKind(channel), inline: true }
    )
    .setFooter({ text: executor ? `Deleted by ${executor.tag}` : 'Deleter unknown' })
    .setTimestamp();

  await sendLog(channel.guild, embed);
}

module.exports = {
  handleLoggingCommand,
  logChannelCreate,
  logChannelDelete,
  logChannelUpdate,
  logMemberJoin,
  logMemberLeave,
  logMessageDelete,
  logRoleChanges
};
