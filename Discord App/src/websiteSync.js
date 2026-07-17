const SYNC_DEBOUNCE_MS = 15000;

let syncTimer = null;
let syncInFlight = false;
let syncAgain = false;

function syncConfig() {
  return {
    baseUrl: process.env.WEBSITE_BASE_URL || process.env.APP_URL,
    secret: process.env.WEBSITE_DISCORD_SYNC_SECRET || process.env.DISCORD_BOT_SYNC_SECRET,
    guildId: process.env.GUILD_ID || process.env.DISCORD_GUILD_ID
  };
}

function roleColor(role) {
  if (!role.hexColor || role.hexColor === '#000000') {
    return null;
  }

  return role.hexColor.toUpperCase();
}

function memberAvatarUrl(member) {
  return member.user.displayAvatarURL({ extension: 'png', size: 128 });
}

async function buildRoleSnapshot(guild) {
  await guild.roles.fetch();
  await guild.members.fetch();

  return guild.roles.cache
    .filter((role) => role.id !== guild.id)
    .sort((a, b) => b.position - a.position)
    .map((role) => {
      const members = role.members.map((member) => ({
        id: member.id,
        username: member.user.tag,
        display_name: member.displayName,
        avatar_url: memberAvatarUrl(member),
        joined_at: member.joinedAt ? member.joinedAt.toISOString() : null
      }));

      return {
        id: role.id,
        name: role.name,
        color: roleColor(role),
        position: role.position,
        managed: role.managed,
        members
      };
    });
}

async function syncDiscordRoles(client) {
  const { baseUrl, secret, guildId } = syncConfig();

  if (!baseUrl || !secret || !guildId) {
    return;
  }

  if (syncInFlight) {
    syncAgain = true;
    return;
  }

  syncInFlight = true;

  try {
    const guild = await client.guilds.fetch(guildId);
    const roles = await buildRoleSnapshot(guild);
    const endpoint = new URL('/api/discord/roles/sync', baseUrl);

    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Discord-Sync-Secret': secret
      },
      body: JSON.stringify({
        guild_id: guild.id,
        roles
      })
    });

    if (!response.ok) {
      const body = await response.text();
      throw new Error(`Website role sync failed with ${response.status}: ${body}`);
    }

    console.log(`Synced ${roles.length} Discord roles to the website.`);
  } finally {
    syncInFlight = false;

    if (syncAgain) {
      syncAgain = false;
      scheduleDiscordRoleSync(client);
    }
  }
}

function scheduleDiscordRoleSync(client) {
  const { baseUrl, secret, guildId } = syncConfig();

  if (!baseUrl || !secret || !guildId) {
    return;
  }

  clearTimeout(syncTimer);
  syncTimer = setTimeout(() => {
    syncDiscordRoles(client).catch((error) => {
      console.error('Failed to sync Discord roles to the website:', error);
    });
  }, SYNC_DEBOUNCE_MS);
}

module.exports = {
  scheduleDiscordRoleSync,
  syncDiscordRoles
};
