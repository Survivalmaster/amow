import { Client, Events, GatewayIntentBits, MessageFlags } from 'discord.js';
import { isAxiosError } from 'axios';
import { api } from './api.js';
import { fetchWebhookCommands } from './command-config.js';
import { config } from './config.js';
import { buildLinkHelpText, buildProfileEmbed } from './responses.js';

const client = new Client({
    intents: [GatewayIntentBits.Guilds],
});

let webhookCommands = [];

client.once(Events.ClientReady, (readyClient) => {
    console.log(`Discord bot logged in as ${readyClient.user.tag}.`);
});

client.once(Events.ClientReady, async () => {
    try {
        webhookCommands = await fetchWebhookCommands();
    } catch (error) {
        console.error('Failed to load Discord command config on startup.', error);
    }
});

client.on(Events.InteractionCreate, async (interaction) => {
    if (!interaction.isChatInputCommand()) {
        return;
    }

    try {
        if (interaction.commandName === 'amowlink') {
            await handleLink(interaction);
            return;
        }

        if (interaction.commandName === 'amowprofile') {
            await handleProfile(interaction);
            return;
        }

        if (interaction.commandName === 'amowwhois') {
            await handleWhoIs(interaction);
            return;
        }

        if (webhookCommands.length === 0) {
            webhookCommands = await fetchWebhookCommands();
        }

        const webhookCommand = webhookCommands.find((command) => command.command_name === interaction.commandName);
        if (webhookCommand) {
            await handleWebhookCommand(interaction, webhookCommand);
        }
    } catch (error) {
        if (isUnknownInteractionError(error)) {
            console.warn(`Ignored stale Discord interaction for /${interaction.commandName}.`);
            return;
        }

        console.error(`Unhandled interaction error for /${interaction.commandName}.`, error);

        if (!interaction.replied && !interaction.deferred) {
            try {
                await interaction.reply({
                    content: 'The command failed before Discord could accept the response.',
                    flags: MessageFlags.Ephemeral,
                });
            } catch (replyError) {
                if (!isUnknownInteractionError(replyError)) {
                    console.error('Failed to send fallback interaction reply.', replyError);
                }
            }
        }
    }
});

async function handleLink(interaction) {
    await interaction.deferReply({ flags: MessageFlags.Ephemeral });

    const code = interaction.options.getString('code', true).trim().toUpperCase();

    try {
        await api.post('/api/discord/link/complete', {
            token: code,
            discord_user_id: interaction.user.id,
            discord_username: interaction.user.globalName ?? interaction.user.username,
            discord_avatar: interaction.user.avatar,
        });

        await interaction.editReply('Your Discord account is now linked to AMOW.');
    } catch (error) {
        if (isAxiosError(error)) {
            if (error.response?.status === 422) {
                await interaction.editReply(`That code is invalid or expired. ${buildLinkHelpText(config.linkUrl)}`);
                return;
            }

            if (error.response?.status === 403) {
                await interaction.editReply('The bot could not authenticate with the AMOW website API.');
                return;
            }

            const apiMessage = error.response?.data?.message;
            if (typeof apiMessage === 'string' && apiMessage.length > 0) {
                await interaction.editReply(`Linking failed: ${apiMessage}`);
                return;
            }
        }

        console.error('amowlink failed', error);
        await interaction.editReply('Linking failed due to a server error.');
    }
}

async function handleProfile(interaction) {
    await interaction.deferReply({ flags: MessageFlags.Ephemeral });

    try {
        const response = await api.get(`/api/discord/profile/${interaction.user.id}`);

        await interaction.editReply({
            embeds: [buildProfileEmbed(response.data)],
        });
    } catch (error) {
        if (isAxiosError(error)) {
            if (error.response?.status === 404) {
                await interaction.editReply(`No linked AMOW account was found. ${buildLinkHelpText(config.linkUrl)}`);
                return;
            }

            if (error.response?.status === 403) {
                await interaction.editReply('The bot could not authenticate with the AMOW website API.');
                return;
            }

            const apiMessage = error.response?.data?.message;
            if (typeof apiMessage === 'string' && apiMessage.length > 0) {
                await interaction.editReply(`Profile lookup failed: ${apiMessage}`);
                return;
            }
        }

        console.error('amowprofile failed', error);
        await interaction.editReply('Profile lookup failed due to a server error.');
    }
}

async function handleWhoIs(interaction) {
    await interaction.deferReply();

    const targetUser = interaction.options.getUser('user', true);

    try {
        const response = await api.get(`/api/discord/profile/${targetUser.id}`);

        await interaction.editReply({
            content: `AMOW profile for <@${targetUser.id}>`,
            embeds: [buildProfileEmbed(response.data)],
        });
    } catch (error) {
        if (isAxiosError(error)) {
            if (error.response?.status === 404) {
                await interaction.editReply(`No linked AMOW account was found for <@${targetUser.id}>.`);
                return;
            }

            if (error.response?.status === 403) {
                await interaction.editReply('The bot could not authenticate with the AMOW website API.');
                return;
            }

            const apiMessage = error.response?.data?.message;
            if (typeof apiMessage === 'string' && apiMessage.length > 0) {
                await interaction.editReply(`Profile lookup failed: ${apiMessage}`);
                return;
            }
        }

        console.error('amowwhois failed', error);
        await interaction.editReply('Profile lookup failed due to a server error.');
    }
}

async function handleWebhookCommand(interaction, webhookCommand) {
    await interaction.deferReply({ flags: MessageFlags.Ephemeral });

    if (webhookCommand.access_mode === 'role' && !memberHasRole(interaction, webhookCommand.role_id)) {
        await interaction.editReply('You do not have permission to use this command.');
        return;
    }

    const headline = interaction.options.getString('headline', true).trim();
    const announcement = interaction.options.getString('announcement', true).trim();
    const imageAttachment = interaction.options.getAttachment('image');
    const imageUrl = imageAttachment?.url ?? interaction.options.getString('image_url')?.trim() ?? null;

    try {
        await api.post('/api/discord/wpnn', {
            headline,
            announcement,
            image_url: imageUrl,
            author_name: interaction.user.globalName ?? interaction.user.username,
            command_name: webhookCommand.command_name,
        });

        await interaction.editReply('Discord announcement posted.');
    } catch (error) {
        if (isAxiosError(error)) {
            if (error.response?.status === 403) {
                await interaction.editReply('The bot could not authenticate with the AMOW website API.');
                return;
            }

            const apiMessage = error.response?.data?.message;
            if (typeof apiMessage === 'string' && apiMessage.length > 0) {
                await interaction.editReply(`The Discord announcement could not be posted: ${apiMessage}`);
                return;
            }
        }

        console.error(`webhook command ${webhookCommand.command_name} failed`, error);
        await interaction.editReply('The Discord announcement could not be posted.');
    }
}

function memberHasRole(interaction, roleId) {
    if (!interaction.inGuild() || !roleId) {
        return false;
    }

    const { member } = interaction;

    if (!member || typeof member !== 'object' || !('roles' in member)) {
        return false;
    }

    const { roles } = member;

    if (Array.isArray(roles)) {
        return roles.includes(roleId);
    }

    if (roles?.cache) {
        return roles.cache.has(roleId);
    }

    return false;
}

function isUnknownInteractionError(error) {
    return isAxiosError(error)
        ? error.response?.data?.code === 10062
        : error?.code === 10062;
}

await client.login(config.botToken);
