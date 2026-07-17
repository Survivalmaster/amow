const { PermissionFlagsBits } = require('discord.js');
const { scheduleDiscordRoleSync } = require('./websiteSync');

function syncConfig() {
  return {
    baseUrl: process.env.WEBSITE_BASE_URL || process.env.AMOW_API_URL || process.env.APP_URL,
    secret: process.env.WEBSITE_DISCORD_SYNC_SECRET || process.env.DISCORD_BOT_SYNC_SECRET
  };
}

function assertBulkRankAdmin(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('You need the Manage Roles permission to bulk-rank members.');
  }
}

async function assertBotCanManageRole(interaction, role) {
  if (role.id === interaction.guild.id) {
    throw new Error('The @everyone role cannot be assigned.');
  }

  if (role.managed) {
    throw new Error('Managed integration roles cannot be assigned manually.');
  }

  const botMember = interaction.guild.members.me ?? await interaction.guild.members.fetchMe();
  if (!botMember.permissions.has(PermissionFlagsBits.ManageRoles)) {
    throw new Error('I need the Manage Roles permission before I can bulk-rank members.');
  }

  if (botMember.roles.highest.comparePositionTo(role) <= 0) {
    throw new Error(`Move my highest role above ${role.name} before I can assign it.`);
  }
}

async function fetchDefaultRankPlan({ rankRoleId = null, nationRoleId = null } = {}) {
  const { baseUrl, secret } = syncConfig();

  if (!baseUrl || !secret) {
    throw new Error('Website sync URL and secret are not configured for this bot.');
  }

  const endpoint = new URL('/api/discord/ranks/default-plan', baseUrl);
  const response = await fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Discord-Sync-Secret': secret
    },
    body: JSON.stringify({
      default_rank_role_id: rankRoleId,
      nation_role_id: nationRoleId
    })
  });

  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(body.message || `Website bulk-rank plan failed with ${response.status}.`);
  }

  return body;
}

function formatPreview(assignments) {
  if (!assignments.length) {
    return 'No members need the default rank.';
  }

  return assignments
    .slice(0, 10)
    .map((assignment) => `- ${assignment.display_name || assignment.username || assignment.member_id} (${assignment.nation_name})`)
    .join('\n');
}

async function defaultRank(interaction) {
  assertBulkRankAdmin(interaction);

  const chosenRankRole = interaction.options.getRole('rank');
  const chosenNationRole = interaction.options.getRole('nation');
  const apply = interaction.options.getBoolean('apply') ?? false;

  await interaction.deferReply({ ephemeral: true });

  const plan = await fetchDefaultRankPlan({
    rankRoleId: chosenRankRole?.id ?? null,
    nationRoleId: chosenNationRole?.id ?? null
  });

  const rankRole = await interaction.guild.roles.fetch(plan.default_rank_role.id);
  if (!rankRole) {
    await interaction.editReply('The default rank role from the website no longer exists in this server.');
    return;
  }

  await assertBotCanManageRole(interaction, rankRole);

  if (!apply) {
    await interaction.editReply([
      `Preview: ${plan.assignment_count} member(s) would receive ${rankRole}.`,
      chosenNationRole ? `Nation filter: ${chosenNationRole}.` : 'Nation filter: all configured nation roles.',
      '',
      formatPreview(plan.assignments),
      plan.assignment_count > 10 ? `...and ${plan.assignment_count - 10} more.` : '',
      '',
      'Run again with `apply:true` to assign the role.'
    ].filter(Boolean).join('\n'));
    return;
  }

  let assigned = 0;
  const failed = [];

  for (const assignment of plan.assignments) {
    try {
      const member = await interaction.guild.members.fetch(assignment.member_id);
      if (!member.roles.cache.has(rankRole.id)) {
        await member.roles.add(rankRole, `Bulk default rank by ${interaction.user.tag}`);
        assigned++;
      }
    } catch (error) {
      failed.push(assignment.display_name || assignment.username || assignment.member_id);
      console.error('Failed to assign bulk default rank:', error);
    }
  }

  scheduleDiscordRoleSync(interaction.client);

  await interaction.editReply([
    `Assigned ${rankRole} to ${assigned} member(s).`,
    failed.length ? `Failed: ${failed.slice(0, 10).join(', ')}${failed.length > 10 ? ` and ${failed.length - 10} more` : ''}.` : null
  ].filter(Boolean).join('\n'));
}

async function handleRankToolsCommand(interaction) {
  const subcommand = interaction.options.getSubcommand();

  if (subcommand === 'default-rank') {
    return defaultRank(interaction);
  }

  throw new Error('Unknown rank tools command.');
}

module.exports = {
  handleRankToolsCommand
};
