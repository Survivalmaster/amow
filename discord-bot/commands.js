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

export function buildDynamicCommand(config) {
    const command = new SlashCommandBuilder()
        .setName(config.command_name)
        .setDescription(config.command_description);

    const options = Array.isArray(config.command_options) && config.command_options.length > 0
        ? config.command_options
        : defaultOptionsForHandler(config.handler_key);

    for (const optionConfig of options) {
        addOption(command, optionConfig);
    }

    return command;
}

function defaultOptionsForHandler(handlerKey) {
    if (handlerKey === 'pray_to_deity') {
        return [
            {
                name: 'deity',
                description: 'Choose the god you want to pray to.',
                type: 'string',
                required: true,
                choices: [
                    { name: 'Marble', value: 'Marble' },
                    { name: 'Obsidian', value: 'Obsidian' },
                ],
            },
        ];
    }

    return [
        {
            name: 'headline',
            description: 'The announcement headline.',
            type: 'string',
            required: true,
            max_length: 120,
        },
        {
            name: 'announcement',
            description: 'The announcement body text.',
            type: 'string',
            required: true,
            max_length: 1900,
        },
        {
            name: 'image',
            description: 'Optional image attachment for the post.',
            type: 'attachment',
            required: false,
        },
        {
            name: 'image_url',
            description: 'Optional image URL if you do not want to upload a file.',
            type: 'string',
            required: false,
        },
    ];
}

function addOption(command, optionConfig) {
    if (!optionConfig?.name || !optionConfig?.description) {
        return;
    }

    const type = optionConfig.type ?? 'string';

    if (type === 'attachment') {
        command.addAttachmentOption((option) =>
            option
                .setName(optionConfig.name)
                .setDescription(optionConfig.description)
                .setRequired(Boolean(optionConfig.required))
        );

        return;
    }

    if (type === 'user') {
        command.addUserOption((option) =>
            option
                .setName(optionConfig.name)
                .setDescription(optionConfig.description)
                .setRequired(Boolean(optionConfig.required))
        );

        return;
    }

    command.addStringOption((option) => {
        option
            .setName(optionConfig.name)
            .setDescription(optionConfig.description)
            .setRequired(Boolean(optionConfig.required));

        if (typeof optionConfig.max_length === 'number') {
            option.setMaxLength(optionConfig.max_length);
        }

        if (Array.isArray(optionConfig.choices)) {
            for (const choice of optionConfig.choices) {
                option.addChoices({
                    name: choice.name,
                    value: choice.value,
                });
            }
        }

        return option;
    });
}
