import { REST, Routes } from 'discord.js';
import { buildWebhookCommand, getBaseCommands } from './commands.js';
import { fetchWebhookCommands } from './command-config.js';
import { config } from './config.js';

const rest = new REST({ version: '10' }).setToken(config.botToken);
const dynamicConfigs = await fetchWebhookCommands();
const commandPayload = [
    ...getBaseCommands(),
    ...dynamicConfigs.map(buildWebhookCommand),
].map((command) => command.toJSON());

await rest.put(
    Routes.applicationGuildCommands(config.applicationId, config.guildId),
    { body: commandPayload },
);

console.log(`Registered ${commandPayload.length} guild commands for ${config.guildId}.`);
