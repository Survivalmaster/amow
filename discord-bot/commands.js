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
    new SlashCommandBuilder()
        .setName('amowwhois')
        .setDescription('Show a linked AMOW character profile publicly in the channel.')
        .addUserOption((option) =>
            option
                .setName('user')
                .setDescription('The Discord user whose linked AMOW profile you want to show.')
                .setRequired(true)
        ),
];

export const commandPayload = commands.map((command) => command.toJSON());
