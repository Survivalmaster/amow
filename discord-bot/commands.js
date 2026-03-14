import { SlashCommandBuilder } from 'discord.js';

export const commands = [
    new SlashCommandBuilder()
        .setName('amowlink')
        .setDescription('Link your Discord account to your AMOW account.')
        .addStringOption((option) =>
            option
                .setName('code')
                .setDescription('The AMOW link code from your profile page.')
                .setRequired(true)
        ),
    new SlashCommandBuilder()
        .setName('amowprofile')
        .setDescription('Show the linked AMOW character profile for this Discord account.'),
];

export const commandPayload = commands.map((command) => command.toJSON());
