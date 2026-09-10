const POLL_INTERVAL_MS = 30000;

let pollTimer = null;
let pollInFlight = false;
let warnedMissingConfig = false;

function changelogConfig() {
  return {
    baseUrl: process.env.WEBSITE_BASE_URL || process.env.AMOW_API_URL || process.env.APP_URL,
    secret: process.env.WEBSITE_DISCORD_SYNC_SECRET || process.env.DISCORD_BOT_SYNC_SECRET || process.env.DISCORD_LINKING_SECRET
  };
}

async function websiteRequest(path, options = {}) {
  const { baseUrl, secret } = changelogConfig();

  if (!baseUrl || !secret) {
    if (!warnedMissingConfig) {
      console.warn('Changelog publisher disabled: WEBSITE_BASE_URL/AMOW_API_URL and WEBSITE_DISCORD_SYNC_SECRET/DISCORD_BOT_SYNC_SECRET are required.');
      warnedMissingConfig = true;
    }

    return null;
  }

  const endpoint = new URL(path, baseUrl);
  const response = await fetch(endpoint, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Discord-Sync-Secret': secret,
      ...(options.headers || {})
    }
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(body.message || `Website changelog request failed with ${response.status}.`);
  }

  return body;
}

async function markChangelogSent(id) {
  await websiteRequest(`/api/discord/changelogs/${id}/sent`, {
    method: 'POST',
    body: JSON.stringify({})
  });
}

async function publishPendingChangelogs(client) {
  if (pollInFlight) {
    return;
  }

  pollInFlight = true;

  try {
    const body = await websiteRequest('/api/discord/changelogs/pending');
    const changelogs = body?.changelogs || [];

    for (const changelog of changelogs) {
      const channel = await client.channels.fetch(changelog.channel_id).catch(() => null);

      if (!channel?.isTextBased()) {
        console.warn(`Changelog ${changelog.id} target channel ${changelog.channel_id} is not available.`);
        continue;
      }

      await channel.send({ embeds: [changelog.embed] });
      await markChangelogSent(changelog.id);
      console.log(`Published changelog ${changelog.id} to Discord channel ${changelog.channel_id}.`);
    }
  } finally {
    pollInFlight = false;
  }
}

function startChangelogPublisher(client) {
  const { baseUrl, secret } = changelogConfig();

  if (!baseUrl || !secret) {
    console.warn('Changelog publisher not started: WEBSITE_BASE_URL/AMOW_API_URL and WEBSITE_DISCORD_SYNC_SECRET/DISCORD_BOT_SYNC_SECRET are required.');
    return;
  }

  clearInterval(pollTimer);
  publishPendingChangelogs(client).catch((error) => {
    console.error('Failed to publish pending changelogs:', error);
  });

  pollTimer = setInterval(() => {
    publishPendingChangelogs(client).catch((error) => {
      console.error('Failed to publish pending changelogs:', error);
    });
  }, POLL_INTERVAL_MS);
}

module.exports = {
  publishPendingChangelogs,
  startChangelogPublisher
};
