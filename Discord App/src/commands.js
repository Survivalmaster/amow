const {
  ChannelType,
  PermissionFlagsBits,
  SlashCommandBuilder
} = require('discord.js');

const rolePanelCommand = new SlashCommandBuilder()
  .setName('role-panel')
  .setDescription('Create and manage button-based role panels.')
  .setDefaultMemberPermissions(PermissionFlagsBits.ManageRoles)
  .addSubcommand((subcommand) =>
    subcommand
      .setName('create')
      .setDescription('Create a new role button panel.')
      .addStringOption((option) =>
        option
          .setName('title')
          .setDescription('Embed title.')
          .setRequired(true)
          .setMaxLength(256)
      )
      .addStringOption((option) =>
        option
          .setName('description')
          .setDescription('Embed description.')
          .setRequired(true)
          .setMaxLength(4000)
      )
      .addBooleanOption((option) =>
        option
          .setName('exclusive')
          .setDescription('Remove other panel roles when someone chooses a new one.')
      )
      .addStringOption((option) =>
        option
          .setName('cooldown')
          .setDescription('Optional change cooldown, e.g. 24h, 7d, 0 to disable.')
          .setMaxLength(20)
      )
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Where to send the panel. Defaults to the current channel.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
      .addChannelOption((option) =>
        option
          .setName('log-channel')
          .setDescription('Optional public channel for role assignment logs.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('cooldown')
      .setDescription('Set or clear a panel role-change cooldown.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
      .addStringOption((option) =>
        option
          .setName('duration')
          .setDescription('Cooldown such as 24h, 7d, 30m, or 0 to disable.')
          .setRequired(true)
          .setMaxLength(20)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('add')
      .setDescription('Add a role button to a panel.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Role to give when this button is clicked.')
          .setRequired(true)
      )
      .addStringOption((option) =>
        option
          .setName('label')
          .setDescription('Button label. Defaults to the role name.')
          .setMaxLength(80)
      )
      .addStringOption((option) =>
        option
          .setName('emoji')
          .setDescription('Optional button emoji.')
      )
      .addStringOption((option) =>
        option
          .setName('style')
          .setDescription('Button colour.')
          .addChoices(
            { name: 'Primary', value: 'Primary' },
            { name: 'Secondary', value: 'Secondary' },
            { name: 'Success', value: 'Success' },
            { name: 'Danger', value: 'Danger' }
          )
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('remove')
      .setDescription('Remove a role button from a panel.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Role button to remove.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('edit')
      .setDescription('Edit a role button label or style.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Role button to edit.')
          .setRequired(true)
      )
      .addStringOption((option) =>
        option
          .setName('label')
          .setDescription('New button label.')
          .setMaxLength(80)
      )
      .addStringOption((option) =>
        option
          .setName('style')
          .setDescription('New button colour.')
          .addChoices(
            { name: 'Primary', value: 'Primary' },
            { name: 'Secondary', value: 'Secondary' },
            { name: 'Success', value: 'Success' },
            { name: 'Danger', value: 'Danger' }
          )
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('log-channel')
      .setDescription('Set or clear the public log channel for a panel.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Channel where role changes should be logged.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
      .addBooleanOption((option) =>
        option
          .setName('clear')
          .setDescription('Clear the current log channel.')
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('list')
      .setDescription('Show a panel configuration.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('refresh')
      .setDescription('Refresh a panel embed and role counts.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('delete')
      .setDescription('Delete a saved panel config and remove its message.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the role panel.')
          .setRequired(true)
      )
  );

const loggingCommand = new SlashCommandBuilder()
  .setName('logging')
  .setDescription('Configure automatic server logging.')
  .setDefaultMemberPermissions(PermissionFlagsBits.ManageGuild)
  .addSubcommand((subcommand) =>
    subcommand
      .setName('set')
      .setDescription('Send automatic server logs to a channel.')
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Channel where server logs should be posted.')
          .setRequired(true)
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('off')
      .setDescription('Turn automatic server logging off.')
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('joins')
      .setDescription('Set or clear the member join log channel.')
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Channel where member join logs should be posted.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
      .addBooleanOption((option) =>
        option
          .setName('clear')
          .setDescription('Clear the current join log channel.')
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('leaves')
      .setDescription('Set or clear the member leave log channel.')
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Channel where member leave logs should be posted.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
      .addBooleanOption((option) =>
        option
          .setName('clear')
          .setDescription('Clear the current leave log channel.')
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('status')
      .setDescription('Show the current automatic logging channels.')
  );

const rankPanelCommand = new SlashCommandBuilder()
  .setName('rank-panel')
  .setDescription('Create and manage leadership rank panels.')
  .setDefaultMemberPermissions(PermissionFlagsBits.ManageRoles)
  .addSubcommand((subcommand) =>
    subcommand
      .setName('create')
      .setDescription('Create a leadership rank management panel.')
      .addStringOption((option) =>
        option
          .setName('title')
          .setDescription('Embed title.')
          .setRequired(true)
          .setMaxLength(256)
      )
      .addStringOption((option) =>
        option
          .setName('description')
          .setDescription('Embed description.')
          .setRequired(true)
          .setMaxLength(4000)
      )
      .addRoleOption((option) =>
        option
          .setName('leadership-role')
          .setDescription('Role required to use this panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('team-role')
          .setDescription('Team role leaders and managed members must share.')
          .setRequired(true)
      )
      .addChannelOption((option) =>
        option
          .setName('channel')
          .setDescription('Where to send the panel. Defaults to the current channel.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
      .addChannelOption((option) =>
        option
          .setName('log-channel')
          .setDescription('Optional channel for rank update logs.')
          .addChannelTypes(ChannelType.GuildText, ChannelType.GuildAnnouncement)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('add-rank')
      .setDescription('Add a manageable rank role to a rank panel.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Rank role leaders can assign.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('extra-role')
          .setDescription('Optional second role to assign with this rank.')
      )
      .addStringOption((option) =>
        option
          .setName('label')
          .setDescription('Dropdown label. Defaults to the role name.')
          .setMaxLength(100)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('edit')
      .setDescription('Edit a rank panel title or description.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
      .addStringOption((option) =>
        option
          .setName('title')
          .setDescription('New embed title.')
          .setMaxLength(256)
      )
      .addStringOption((option) =>
        option
          .setName('description')
          .setDescription('New embed description.')
          .setMaxLength(4000)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('remove-rank')
      .setDescription('Remove a manageable rank role from a rank panel.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Rank role to remove.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('edit-rank')
      .setDescription('Edit a manageable rank label or extra role.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
      .addRoleOption((option) =>
        option
          .setName('role')
          .setDescription('Rank role to edit.')
          .setRequired(true)
      )
      .addStringOption((option) =>
        option
          .setName('label')
          .setDescription('New dropdown label.')
          .setMaxLength(100)
      )
      .addRoleOption((option) =>
        option
          .setName('extra-role')
          .setDescription('New optional second role to assign with this rank.')
      )
      .addBooleanOption((option) =>
        option
          .setName('clear-extra-role')
          .setDescription('Remove the current extra role from this rank.')
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('list')
      .setDescription('Show a rank panel configuration.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('refresh')
      .setDescription('Refresh a rank panel embed.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
  )
  .addSubcommand((subcommand) =>
    subcommand
      .setName('delete')
      .setDescription('Delete a saved rank panel config and remove its message.')
      .addStringOption((option) =>
        option
          .setName('message-id')
          .setDescription('Message ID of the rank panel.')
          .setRequired(true)
      )
  );

const rankToolsCommand = new SlashCommandBuilder()
  .setName('rank-tools')
  .setDescription('Bulk rank helpers for nation membership.')
  .setDefaultMemberPermissions(PermissionFlagsBits.ManageRoles)
  .addSubcommand((subcommand) =>
    subcommand
      .setName('default-rank')
      .setDescription('Give the default rank to nation members who are missing a rank.')
      .addRoleOption((option) =>
        option
          .setName('rank')
          .setDescription('Rank to assign. Defaults to the website-detected Private role.')
      )
      .addRoleOption((option) =>
        option
          .setName('nation')
          .setDescription('Limit the run to one nation role.')
      )
      .addBooleanOption((option) =>
        option
          .setName('apply')
          .setDescription('Actually assign roles. If false or omitted, only preview the changes.')
      )
  );

const linkCommand = new SlashCommandBuilder()
  .setName('link')
  .setDescription('Link your Discord account to your AMOW account.')
  .addStringOption((option) =>
    option
      .setName('code')
      .setDescription('The 12-character link code from your AMOW profile.')
      .setRequired(true)
      .setMinLength(12)
      .setMaxLength(12)
  );

module.exports = [
  linkCommand.toJSON(),
  rolePanelCommand.toJSON(),
  loggingCommand.toJSON(),
  rankPanelCommand.toJSON(),
  rankToolsCommand.toJSON()
];
