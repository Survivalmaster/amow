require('./loadEnv');

const { REST, Routes } = require('discord.js');
const commands = require('./commands');

const { CLIENT_ID, DISCORD_TOKEN, GUILD_ID } = process.env;

if (!CLIENT_ID || !DISCORD_TOKEN) {
  throw new Error('CLIENT_ID and DISCORD_TOKEN must be set in your .env file.');
}

const rest = new REST({ version: '10' }).setToken(DISCORD_TOKEN);

async function main() {
  const route = GUILD_ID
    ? Routes.applicationGuildCommands(CLIENT_ID, GUILD_ID)
    : Routes.applicationCommands(CLIENT_ID);

  await rest.put(route, { body: commands });

  const scope = GUILD_ID ? `guild ${GUILD_ID}` : 'global';
  console.log(`Registered ${commands.length} slash command group(s) for ${scope}.`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
