const {
  AttachmentBuilder,
  PermissionFlagsBits
} = require('discord.js');

const DEFAULT_LIMIT = 1000;
const MAX_LIMIT = 2500;
const MAX_ATTACHMENT_BYTES = 24 * 1024 * 1024;

function assertExportAdmin(interaction) {
  if (!interaction.memberPermissions?.has(PermissionFlagsBits.Administrator)) {
    throw new Error('You need the Administrator permission to export channel transcripts.');
  }
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function formatTimestamp(date) {
  return new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'UTC'
  }).format(date);
}

function sanitizeFilePart(value) {
  return String(value ?? 'channel')
    .toLowerCase()
    .replace(/[^a-z0-9_-]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 60) || 'channel';
}

function replaceMentions(content, guild, channel) {
  return content
    .replace(/&lt;@!?(\d+)&gt;/g, (match, userId) => {
      const member = guild.members.cache.get(userId);
      const user = guild.client.users.cache.get(userId);
      return `<span class="mention">@${escapeHtml(member?.displayName ?? user?.username ?? userId)}</span>`;
    })
    .replace(/&lt;@&amp;(\d+)&gt;/g, (match, roleId) => {
      const role = guild.roles.cache.get(roleId);
      return `<span class="mention">@${escapeHtml(role?.name ?? roleId)}</span>`;
    })
    .replace(/&lt;#(\d+)&gt;/g, (match, channelId) => {
      const mentionedChannel = guild.channels.cache.get(channelId);
      return `<span class="mention">#${escapeHtml(mentionedChannel?.name ?? channel?.name ?? channelId)}</span>`;
    });
}

function linkify(content) {
  return content.replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noreferrer noopener">$1</a>');
}

function formatMessageContent(message, channel) {
  const escaped = escapeHtml(message.content || '');
  const withMentions = replaceMentions(escaped, message.guild, channel);
  return linkify(withMentions).replace(/\n/g, '<br>');
}

function renderAttachment(attachment) {
  const name = escapeHtml(attachment.name || attachment.url);
  const url = escapeHtml(attachment.url);
  const contentType = attachment.contentType || '';

  if (contentType.startsWith('image/')) {
    return `
      <a class="attachment image" href="${url}" target="_blank" rel="noreferrer noopener">
        <img src="${url}" alt="${name}">
      </a>
    `;
  }

  return `
    <a class="attachment file" href="${url}" target="_blank" rel="noreferrer noopener">
      ${name}
    </a>
  `;
}

function renderEmbed(embed) {
  const fields = embed.fields?.map((field) => `
    <div class="embed-field">
      <div class="embed-field-name">${escapeHtml(field.name)}</div>
      <div>${escapeHtml(field.value).replace(/\n/g, '<br>')}</div>
    </div>
  `).join('') || '';

  return `
    <div class="embed">
      ${embed.author?.name ? `<div class="embed-author">${escapeHtml(embed.author.name)}</div>` : ''}
      ${embed.title ? `<div class="embed-title">${escapeHtml(embed.title)}</div>` : ''}
      ${embed.description ? `<div class="embed-description">${escapeHtml(embed.description).replace(/\n/g, '<br>')}</div>` : ''}
      ${fields}
      ${embed.thumbnail?.url ? `<img class="embed-thumbnail" src="${escapeHtml(embed.thumbnail.url)}" alt="">` : ''}
      ${embed.image?.url ? `<img class="embed-image" src="${escapeHtml(embed.image.url)}" alt="">` : ''}
      ${embed.footer?.text ? `<div class="embed-footer">${escapeHtml(embed.footer.text)}</div>` : ''}
    </div>
  `;
}

function renderMessage(message, channel) {
  const author = message.author;
  const member = message.member ?? message.guild.members.cache.get(author.id);
  const displayName = member?.displayName ?? author.globalName ?? author.username;
  const avatarUrl = author.displayAvatarURL({ extension: 'png', size: 64 });
  const timestamp = formatTimestamp(message.createdAt);
  const content = formatMessageContent(message, channel);
  const attachments = [...message.attachments.values()].map(renderAttachment).join('');
  const embeds = message.embeds.map(renderEmbed).join('');
  const reply = message.reference?.messageId
    ? `<div class="reply">reply to message (${escapeHtml(message.reference.messageId)})</div>`
    : '';

  return `
    <article class="message" id="message-${escapeHtml(message.id)}">
      <img class="avatar" src="${escapeHtml(avatarUrl)}" alt="">
      <div class="message-body">
        ${reply}
        <div class="meta">
          <span class="author">${escapeHtml(displayName)}</span>
          ${author.bot ? '<span class="bot">BOT</span>' : ''}
          <span class="timestamp">${escapeHtml(timestamp)} UTC</span>
        </div>
        ${content ? `<div class="content">${content}</div>` : ''}
        ${embeds}
        ${attachments ? `<div class="attachments">${attachments}</div>` : ''}
      </div>
    </article>
  `;
}

async function fetchMessages(channel, limit) {
  const messages = [];
  let before;

  while (messages.length < limit) {
    const batch = await channel.messages.fetch({
      limit: Math.min(100, limit - messages.length),
      before
    });

    if (!batch.size) {
      break;
    }

    messages.push(...batch.values());
    before = batch.last().id;
  }

  return messages.sort((a, b) => a.createdTimestamp - b.createdTimestamp);
}

function buildTranscriptHtml({ channel, guild, messages, exportedBy }) {
  const generatedAt = new Date();
  const guildIcon = guild.iconURL({ extension: 'png', size: 128 });
  const channelName = channel.name ? `#${channel.name}` : 'Direct transcript';

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${escapeHtml(guild.name)} ${escapeHtml(channelName)} transcript</title>
  <style>
    :root {
      color-scheme: dark;
      --background: #121821;
      --panel: #1b222d;
      --muted: #718096;
      --text: #f7fafc;
      --soft: #cbd5e0;
      --mention: #5b78d6;
      --embed: #232a35;
      --accent: #3ddc84;
    }

    * { box-sizing: border-box; }
    body {
      margin: 0;
      background: var(--background);
      color: var(--text);
      font-family: "Inter", "Segoe UI", Arial, sans-serif;
      font-size: 16px;
      line-height: 1.45;
    }
    header {
      display: flex;
      gap: 16px;
      align-items: center;
      padding: 28px 24px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }
    header img {
      width: 84px;
      height: 84px;
      border-radius: 12px;
      object-fit: cover;
      background: #0e131b;
    }
    h1 {
      margin: 0;
      font-size: 28px;
      line-height: 1.12;
      font-weight: 750;
    }
    .summary {
      margin-top: 4px;
      color: var(--soft);
      font-size: 18px;
    }
    main {
      max-width: 1120px;
      padding: 24px;
    }
    .notice {
      margin-bottom: 24px;
      color: var(--muted);
      font-size: 13px;
    }
    .message {
      display: grid;
      grid-template-columns: 48px minmax(0, 1fr);
      gap: 14px;
      margin: 0 0 22px;
    }
    .avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      object-fit: cover;
      background: #0e131b;
    }
    .meta {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: baseline;
      min-height: 22px;
    }
    .author {
      color: #fff;
      font-size: 18px;
      font-weight: 700;
    }
    .bot {
      border-radius: 3px;
      background: #7289da;
      color: #fff;
      padding: 1px 4px;
      font-size: 11px;
      font-weight: 700;
    }
    .timestamp,
    .reply,
    .embed-footer {
      color: var(--muted);
      font-size: 13px;
    }
    .reply {
      margin: 0 0 2px;
    }
    .content {
      color: var(--text);
      font-size: 20px;
      overflow-wrap: anywhere;
    }
    a {
      color: #8ab4ff;
    }
    .mention {
      border-radius: 4px;
      background: rgba(91, 120, 214, 0.35);
      color: #cdd9ff;
      padding: 0 3px;
    }
    .embed {
      position: relative;
      max-width: 720px;
      margin-top: 8px;
      border-left: 5px solid var(--accent);
      border-radius: 4px;
      background: var(--embed);
      padding: 12px 14px;
      color: var(--soft);
      overflow: hidden;
    }
    .embed-author,
    .embed-field-name {
      color: #d6dde7;
      font-weight: 700;
    }
    .embed-title {
      margin-top: 4px;
      color: #fff;
      font-weight: 700;
    }
    .embed-description,
    .embed-field {
      margin-top: 8px;
    }
    .embed-thumbnail {
      max-width: 96px;
      max-height: 96px;
      float: right;
      margin-left: 12px;
      border-radius: 4px;
    }
    .embed-image {
      display: block;
      max-width: 100%;
      margin-top: 10px;
      border-radius: 4px;
    }
    .attachments {
      display: grid;
      gap: 8px;
      margin-top: 8px;
      max-width: 720px;
    }
    .attachment.image img {
      display: block;
      max-width: min(100%, 560px);
      max-height: 420px;
      border-radius: 6px;
      object-fit: contain;
    }
    .attachment.file {
      display: inline-block;
      width: fit-content;
      border-radius: 4px;
      background: var(--panel);
      padding: 8px 10px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <header>
    ${guildIcon ? `<img src="${escapeHtml(guildIcon)}" alt="">` : ''}
    <div>
      <h1>${escapeHtml(guild.name)}<br>${escapeHtml(channelName)}</h1>
      <div class="summary">${messages.length} messages</div>
    </div>
  </header>
  <main>
    <div class="notice">
      Exported by ${escapeHtml(exportedBy.tag)} on ${escapeHtml(formatTimestamp(generatedAt))} UTC.
      Message IDs are preserved in each message anchor.
    </div>
    ${messages.map((message) => renderMessage(message, channel)).join('\n')}
  </main>
</body>
</html>`;
}

async function handleExportCommand(interaction) {
  assertExportAdmin(interaction);

  const channel = interaction.options.getChannel('channel') ?? interaction.channel;
  const limit = Math.min(interaction.options.getInteger('limit') ?? DEFAULT_LIMIT, MAX_LIMIT);
  const isPublic = interaction.options.getBoolean('public') ?? false;

  if (!channel?.isTextBased() || !channel.messages) {
    await interaction.reply({ content: 'That channel does not have exportable message history.', ephemeral: true });
    return;
  }

  await interaction.deferReply({ ephemeral: !isPublic });

  const messages = await fetchMessages(channel, limit);
  const html = buildTranscriptHtml({
    channel,
    guild: interaction.guild,
    messages,
    exportedBy: interaction.user
  });
  const buffer = Buffer.from(html, 'utf8');

  if (buffer.byteLength > MAX_ATTACHMENT_BYTES) {
    await interaction.editReply(`That transcript is ${(buffer.byteLength / 1024 / 1024).toFixed(1)} MB, which is too large to attach. Try a lower limit.`);
    return;
  }

  const filename = [
    'amow-transcript',
    sanitizeFilePart(interaction.guild.name),
    sanitizeFilePart(channel.name ?? channel.id),
    new Date().toISOString().slice(0, 10)
  ].join('-');

  const attachment = new AttachmentBuilder(buffer, { name: `${filename}.html` });

  await interaction.editReply({
    content: `Exported ${messages.length} messages from ${channel}.`,
    files: [attachment]
  });
}

module.exports = {
  handleExportCommand
};
