function linkConfig() {
  return {
    baseUrl: process.env.WEBSITE_BASE_URL || process.env.AMOW_API_URL || process.env.APP_URL,
    secret: process.env.WEBSITE_DISCORD_LINK_SECRET || process.env.DISCORD_LINKING_SECRET || process.env.WEBSITE_DISCORD_SYNC_SECRET || process.env.DISCORD_BOT_SYNC_SECRET
  };
}

function formatDiscordUsername(user) {
  return user.discriminator && user.discriminator !== '0'
    ? `${user.username}#${user.discriminator}`
    : user.username;
}

async function completeDiscordLink(interaction) {
  const { baseUrl, secret } = linkConfig();

  if (!baseUrl || !secret) {
    throw new Error('Website link URL and secret are not configured for this bot.');
  }

  const token = interaction.options.getString('code', true).trim().toUpperCase();

  await interaction.deferReply({ ephemeral: true });

  const endpoint = new URL('/api/discord/link/complete', baseUrl);
  const response = await fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Discord-Link-Secret': secret
    },
    body: JSON.stringify({
      token,
      discord_user_id: interaction.user.id,
      discord_username: formatDiscordUsername(interaction.user),
      discord_avatar: interaction.user.avatar
    })
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(body.message || `Website account link failed with ${response.status}.`);
  }

  await interaction.editReply(body.message || 'Discord account linked.');
}

async function handleLinkCommand(interaction) {
  return completeDiscordLink(interaction);
}

module.exports = {
  handleLinkCommand
};
