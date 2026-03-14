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
    new SlashCommandBuilder()
        .setName('amowwpnn')
        .setDescription('Post an AMOW news announcement through the WPNN webhook.')
        .addStringOption((option) =>
            option
                .setName('headline')
                .setDescription('The news headline.')
                .setRequired(true)
                .setMaxLength(120)
        )
        .addStringOption((option) =>
            option
                .setName('announcement')
                .setDescription('The announcement body text.')
                .setRequired(true)
                .setMaxLength(1900)
        )
        .addStringOption((option) =>
            option
                .setName('image_url')
                .setDescription('Optional image URL for the news post.')
                .setRequired(false)
        ),
];

export const commandPayload = commands.map((command) => command.toJSON());
