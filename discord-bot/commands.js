import { SlashCommandBuilder } from 'discord.js';

export function getBaseCommands() {
    return [
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
}

export function buildWebhookCommand(config) {
    return new SlashCommandBuilder()
        .setName(config.command_name)
        .setDescription(config.command_description)
        .addStringOption((option) =>
            option
                .setName('headline')
                .setDescription('The announcement headline.')
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
        .addAttachmentOption((option) =>
            option
                .setName('image')
                .setDescription('Optional image attachment for the post.')
                .setRequired(false)
        )
        .addStringOption((option) =>
            option
                .setName('image_url')
                .setDescription('Optional image URL if you do not want to upload a file.')
                .setRequired(false)
        );
}
