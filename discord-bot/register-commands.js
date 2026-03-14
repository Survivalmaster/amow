import { REST, Routes } from 'discord.js';
import { commandPayload } from './commands.js';
import { config } from './config.js';

const rest = new REST({ version: '10' }).setToken(config.botToken);

await rest.put(
    Routes.applicationGuildCommands(config.applicationId, config.guildId),
    { body: commandPayload },
);

console.log(`Registered ${commandPayload.length} guild commands for ${config.guildId}.`);
