require('./loadEnv');

const { Client, Events, GatewayIntentBits, Partials } = require('discord.js');
const { handleRankToolsCommand } = require('./bulkRank');
const { startChangelogPublisher } = require('./changelogPublisher');
const { handleLinkCommand } = require('./linking');
const {
  handleBankCommand,
  handleJobPickSelect,
  handleJobsCommand,
  handleStoreCommand,
  handleStorePurchaseSelect,
  handleWorkCommand,
  isJobPickSelect,
  isStorePurchaseSelect
} = require('./amowGame');
const { handleExportCommand } = require('./amowExport');
const {
  handleRoleButton,
  handleRolePanelCommand,
  isRolePanelButton
} = require('./rolePanels');
const {
  handleRankMemberSelect,
  handleRankPanelCommand,
  handleRankSelect,
  handleRankUserSelect,
  isRankPanelMemberSelect,
  isRankPanelRankSelect,
  isRankPanelUserSelect,
  refreshRankPanelsForTeamRoles
} = require('./rankPanels');
const {
  handleLoggingCommand,
  logChannelCreate,
  logChannelDelete,
  logChannelUpdate,
  logMemberJoin,
  logMemberLeave,
  logMessageDelete,
  logRoleChanges
} = require('./serverLogging');
const {
  scheduleDiscordRoleSync,
  syncDiscordRoles
} = require('./websiteSync');

const { DISCORD_TOKEN } = process.env;

if (!DISCORD_TOKEN) {
  throw new Error('DISCORD_TOKEN must be set in your .env file.');
}

const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMembers,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.MessageContent
  ],
  partials: [Partials.Channel, Partials.Message]
});

client.once(Events.ClientReady, (readyClient) => {
  console.log(`Logged in as ${readyClient.user.tag}.`);
  syncDiscordRoles(readyClient).catch((error) => {
    console.error('Failed to sync Discord roles to the website:', error);
  });
  startChangelogPublisher(readyClient);
});

client.on(Events.InteractionCreate, async (interaction) => {
  try {
    if (interaction.isChatInputCommand() && interaction.commandName === 'role-panel') {
      await handleRolePanelCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-link') {
      await handleLinkCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-bank') {
      await handleBankCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-work') {
      await handleWorkCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-jobs') {
      await handleJobsCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-store') {
      await handleStoreCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'amow-export') {
      await handleExportCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'logging') {
      await handleLoggingCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'rank-panel') {
      await handleRankPanelCommand(interaction);
      return;
    }

    if (interaction.isChatInputCommand() && interaction.commandName === 'rank-tools') {
      await handleRankToolsCommand(interaction);
      return;
    }

    if (isRolePanelButton(interaction)) {
      await handleRoleButton(interaction);
      return;
    }

    if (isStorePurchaseSelect(interaction)) {
      await handleStorePurchaseSelect(interaction);
      return;
    }

    if (isJobPickSelect(interaction)) {
      await handleJobPickSelect(interaction);
      return;
    }

    if (isRankPanelUserSelect(interaction)) {
      await handleRankUserSelect(interaction);
      return;
    }

    if (isRankPanelMemberSelect(interaction)) {
      await handleRankMemberSelect(interaction);
      return;
    }

    if (isRankPanelRankSelect(interaction)) {
      await handleRankSelect(interaction);
    }
  } catch (error) {
    console.error(error);

    const content = error.message || 'Something went wrong while handling that interaction.';
    if (error.publicReply && interaction.deferred && !interaction.replied) {
      await interaction.editReply({ content, embeds: [], components: [] });
    } else if (interaction.replied || interaction.deferred) {
      await interaction.followUp({ content, ephemeral: true });
    } else {
      await interaction.reply({ content, ephemeral: true });
    }
  }
});

client.on(Events.MessageDelete, async (message) => {
  try {
    await logMessageDelete(message);
  } catch (error) {
    console.error('Failed to process message delete log:', error);
  }
});

client.on(Events.GuildMemberUpdate, async (oldMember, newMember) => {
  try {
    await logRoleChanges(oldMember, newMember);
    const changedRoleIds = [
      ...oldMember.roles.cache
        .filter((role) => !newMember.roles.cache.has(role.id))
        .keys(),
      ...newMember.roles.cache
        .filter((role) => !oldMember.roles.cache.has(role.id))
        .keys()
    ];

    await refreshRankPanelsForTeamRoles(newMember.client, newMember.guild.id, changedRoleIds);
    scheduleDiscordRoleSync(newMember.client);
  } catch (error) {
    console.error('Failed to process member update log:', error);
  }
});

client.on(Events.GuildMemberAdd, async (member) => {
  try {
    await logMemberJoin(member);
    scheduleDiscordRoleSync(member.client);
  } catch (error) {
    console.error('Failed to process member join log:', error);
  }
});

client.on(Events.GuildMemberRemove, async (member) => {
  try {
    await logMemberLeave(member);
    scheduleDiscordRoleSync(member.client);
  } catch (error) {
    console.error('Failed to process member leave log:', error);
  }
});

client.on(Events.GuildRoleCreate, async (role) => {
  scheduleDiscordRoleSync(role.client);
});

client.on(Events.GuildRoleUpdate, async (oldRole, newRole) => {
  scheduleDiscordRoleSync(newRole.client);
});

client.on(Events.GuildRoleDelete, async (role) => {
  scheduleDiscordRoleSync(role.client);
});

client.on(Events.ChannelUpdate, async (oldChannel, newChannel) => {
  try {
    await logChannelUpdate(oldChannel, newChannel);
  } catch (error) {
    console.error('Failed to process channel update log:', error);
  }
});

client.on(Events.ChannelCreate, async (channel) => {
  try {
    await logChannelCreate(channel);
  } catch (error) {
    console.error('Failed to process channel create log:', error);
  }
});

client.on(Events.ChannelDelete, async (channel) => {
  try {
    await logChannelDelete(channel);
  } catch (error) {
    console.error('Failed to process channel delete log:', error);
  }
});

client.login(DISCORD_TOKEN);
