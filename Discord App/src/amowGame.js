const {
  ActionRowBuilder,
  EmbedBuilder,
  StringSelectMenuBuilder
} = require('discord.js');

function gameConfig() {
  return {
    baseUrl: process.env.WEBSITE_BASE_URL || process.env.AMOW_API_URL || process.env.APP_URL,
    secret: process.env.WEBSITE_DISCORD_LINK_SECRET || process.env.DISCORD_LINKING_SECRET || process.env.WEBSITE_DISCORD_SYNC_SECRET || process.env.DISCORD_BOT_SYNC_SECRET
  };
}

async function websiteRequest(path, options = {}) {
  const { baseUrl, secret } = gameConfig();

  if (!baseUrl || !secret) {
    throw new Error('Website URL and Discord link secret are not configured for this bot.');
  }

  const endpoint = new URL(path, baseUrl);
  const response = await fetch(endpoint, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Discord-Link-Secret': secret,
      ...(options.headers || {})
    }
  });

  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = response.status === 404 && body.linked === false
      ? `${body.message || 'No AMOW character is linked to this Discord user.'} Generate a Discord link code from your AMOW profile, then run \`/amow-link\` with that code.`
      : body.message || `Website request failed with ${response.status}.`;
    const error = new Error(message);
    error.status = response.status;
    error.body = body;
    error.publicReply = true;
    throw error;
  }

  return body;
}

function credits(value) {
  return Number(value || 0).toLocaleString('en-GB');
}

function trimText(value, maxLength) {
  const text = String(value || '').trim();

  if (text.length <= maxLength) {
    return text;
  }

  return `${text.slice(0, Math.max(0, maxLength - 3))}...`;
}

function embedColor(character, fallback = 0x7ead59) {
  const hex = String(character?.faction_color || '').replace('#', '').trim();

  if (!/^[0-9a-f]{6}$/i.test(hex)) {
    return fallback;
  }

  return Number.parseInt(hex, 16);
}

function buildBankEmbed(body) {
  const character = body.character;

  return new EmbedBuilder()
    .setColor(embedColor(character))
    .setTitle(`${character.name}'s Bank`)
    .setDescription(`**${credits(character.credits)}** Plastic Credits`)
    .addFields(
      { name: 'Faction', value: character.faction || 'Unknown', inline: true },
      { name: 'Rank', value: character.rank || 'Unknown', inline: true }
    );
}

async function handleBankCommand(interaction) {
  await interaction.deferReply();

  const body = await websiteRequest(`/api/discord/bank/${interaction.user.id}`);

  await interaction.editReply({ embeds: [buildBankEmbed(body)] });
}

async function handleWorkCommand(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const body = await websiteRequest('/api/discord/work', {
    method: 'POST',
    body: JSON.stringify({
      discord_user_id: interaction.user.id
    })
  });

  const character = body.character;
  const embed = new EmbedBuilder()
    .setColor(embedColor(character, 0xc2a84f))
    .setTitle(`${character.name} completed a shift`)
    .setDescription(`Earned **${credits(body.earnings)}** Plastic Credits and **${body.experience_earned} XP**.`)
    .addFields(
      { name: 'Job', value: character.job || 'Unknown', inline: true },
      { name: 'Balance', value: credits(character.credits), inline: true },
      { name: 'Stamina', value: String(character.stamina_points ?? 'Unknown'), inline: true }
    );

  if (body.levels_gained > 0) {
    embed.addFields({ name: 'Level Up', value: `Reached level ${character.level}.`, inline: false });
  }

  await interaction.editReply({ embeds: [embed] });
}

function jobEntries(body) {
  return body.jobs.map((job) => {
    let status = 'Available';

    if (job.is_current) {
      status = 'Current';
    } else if (!job.is_active) {
      status = 'Unavailable';
    } else if (body.character.level < job.required_level) {
      status = `Requires level ${job.required_level}`;
    } else if (!body.character.can_change_job) {
      status = 'Switch cooldown active';
    }

    return {
      ...job,
      status
    };
  });
}

function buildJobsEmbed(body, entries) {
  const character = body.character;
  const preview = entries.slice(0, 12).map((job) =>
    `**${job.name}** - ${credits(job.min_pay)}-${credits(job.max_pay)} credits, ${job.experience_reward || 0} XP, ${job.work_cooldown_minutes}m work cooldown (${job.status})`
  );

  const embed = new EmbedBuilder()
    .setColor(embedColor(character))
    .setTitle('AMOW Jobs')
    .setDescription(preview.length ? preview.join('\n') : 'No jobs are configured yet.')
    .addFields(
      { name: 'Character', value: character.name, inline: true },
      { name: 'Current Job', value: character.current_job || 'Unknown', inline: true },
      { name: 'Level', value: String(character.level), inline: true }
    );

  if (!character.can_change_job && character.job_change_cooldown_ends_at) {
    embed.setFooter({ text: `Job switch cooldown ends ${new Date(character.job_change_cooldown_ends_at).toLocaleString('en-GB')}` });
  } else {
    embed.setFooter({ text: entries.length > 12 ? 'Showing the first 12 jobs. Use the dropdown for available jobs.' : 'Use the dropdown to change jobs.' });
  }

  return embed;
}

function buildJobsComponents(userId, entries) {
  const options = entries
    .filter((job) => job.can_choose)
    .slice(0, 25)
    .map((job) => ({
      label: trimText(job.name, 100),
      description: trimText(`${credits(job.min_pay)}-${credits(job.max_pay)} credits - level ${job.required_level}`, 100),
      value: String(job.id)
    }));

  if (options.length === 0) {
    return [];
  }

  const select = new StringSelectMenuBuilder()
    .setCustomId(`amow_job_pick:${userId}`)
    .setPlaceholder('Choose a new job')
    .addOptions(options);

  return [new ActionRowBuilder().addComponents(select)];
}

async function handleJobsCommand(interaction) {
  await interaction.deferReply({ ephemeral: true });

  const body = await websiteRequest(`/api/discord/jobs/${interaction.user.id}`);
  const entries = jobEntries(body);

  await interaction.editReply({
    embeds: [buildJobsEmbed(body, entries)],
    components: buildJobsComponents(interaction.user.id, entries)
  });
}

function storeEntries(body) {
  const licenceEntries = body.licences.map((licence) => ({
    type: 'licence',
    id: licence.id,
    name: licence.name,
    description: licence.description,
    price: licence.price,
    canBuy: !licence.owned,
    status: licence.owned ? 'Owned' : 'Available'
  }));

  const itemEntries = body.items.map((item) => ({
    type: 'item',
    id: item.id,
    name: item.name,
    description: item.description,
    price: item.price,
    canBuy: item.can_purchase,
    status: item.can_purchase ? 'Available' : 'Locked'
  }));

  return [...licenceEntries, ...itemEntries];
}

function buildStoreEmbed(body, entries) {
  const character = body.character;
  const preview = entries.slice(0, 12).map((entry) =>
    `**${entry.name}** - ${credits(entry.price)} credits (${entry.status})`
  );

  return new EmbedBuilder()
    .setColor(embedColor(character))
    .setTitle('AMOW Store')
    .setDescription(preview.length ? preview.join('\n') : 'The store is empty.')
    .addFields(
      { name: 'Balance', value: credits(character.credits), inline: true },
      { name: 'Inventory', value: `${character.inventory_slots_used}/${character.inventory_slot_capacity}`, inline: true }
    )
    .setFooter({ text: entries.length > 12 ? 'Showing the first 12 entries. Use the dropdown for available purchases.' : 'Use the dropdown to buy available entries.' });
}

function buildStoreComponents(userId, entries) {
  const options = entries
    .filter((entry) => entry.canBuy)
    .slice(0, 25)
    .map((entry) => ({
      label: trimText(entry.name, 100),
      description: trimText(`${credits(entry.price)} credits - ${entry.type}`, 100),
      value: `${entry.type}:${entry.id}`
    }));

  if (options.length === 0) {
    return [];
  }

  const select = new StringSelectMenuBuilder()
    .setCustomId(`amow_store_buy:${userId}`)
    .setPlaceholder('Buy an item or licence')
    .addOptions(options);

  return [new ActionRowBuilder().addComponents(select)];
}

async function handleStoreCommand(interaction) {
  await interaction.deferReply();

  const body = await websiteRequest(`/api/discord/store/${interaction.user.id}`);
  const entries = storeEntries(body);

  await interaction.editReply({
    embeds: [buildStoreEmbed(body, entries)],
    components: buildStoreComponents(interaction.user.id, entries)
  });
}

function isStorePurchaseSelect(interaction) {
  return interaction.isStringSelectMenu() && interaction.customId.startsWith('amow_store_buy:');
}

function isJobPickSelect(interaction) {
  return interaction.isStringSelectMenu() && interaction.customId.startsWith('amow_job_pick:');
}

async function handleJobPickSelect(interaction) {
  const [, ownerId] = interaction.customId.split(':');

  if (interaction.user.id !== ownerId) {
    await interaction.reply({ content: 'This job picker belongs to another player.', ephemeral: true });
    return;
  }

  await interaction.deferUpdate();

  const body = await websiteRequest('/api/discord/jobs/change', {
    method: 'POST',
    body: JSON.stringify({
      discord_user_id: interaction.user.id,
      job_id: Number(interaction.values[0])
    })
  });

  const embed = new EmbedBuilder()
    .setColor(embedColor(body.character, 0xc2a84f))
    .setTitle(body.message)
    .setDescription(`${body.character.name} is now working as **${body.character.current_job}**.`)
    .addFields(
      { name: 'Pay Range', value: `${credits(body.job.min_pay)}-${credits(body.job.max_pay)}`, inline: true },
      { name: 'XP Reward', value: `${body.job.experience_reward || 0} XP`, inline: true },
      { name: 'Work Cooldown', value: `${body.job.work_cooldown_minutes}m`, inline: true }
    )
    .setFooter({ text: `Next job switch available ${new Date(body.job_change_cooldown_ends_at).toLocaleString('en-GB')}` });

  await interaction.editReply({ embeds: [embed], components: [] });
}

async function handleStorePurchaseSelect(interaction) {
  const [, ownerId] = interaction.customId.split(':');

  if (interaction.user.id !== ownerId) {
    await interaction.reply({ content: 'This store menu belongs to another player.', ephemeral: true });
    return;
  }

  const [purchaseType, id] = interaction.values[0].split(':');

  await interaction.deferUpdate();

  const body = await websiteRequest('/api/discord/store/purchase', {
    method: 'POST',
    body: JSON.stringify({
      discord_user_id: interaction.user.id,
      purchase_type: purchaseType,
      id: Number(id)
    })
  });

  const embed = new EmbedBuilder()
    .setColor(embedColor(body.character, 0xc2a84f))
    .setTitle(body.message)
    .setDescription(`Balance: **${credits(body.character.credits)}** Plastic Credits`)
    .addFields({
      name: 'Inventory',
      value: `${body.character.inventory_slots_used}/${body.character.inventory_slot_capacity}`,
      inline: true
    });

  await interaction.editReply({ embeds: [embed], components: [] });
}

module.exports = {
  handleBankCommand,
  handleJobPickSelect,
  handleJobsCommand,
  handleStoreCommand,
  handleStorePurchaseSelect,
  handleWorkCommand,
  isJobPickSelect,
  isStorePurchaseSelect
};
