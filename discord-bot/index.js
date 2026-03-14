import { Client, Events, GatewayIntentBits } from 'discord.js';
import { isAxiosError } from 'axios';
import { api } from './api.js';
import { config } from './config.js';
import { buildLinkHelpText, buildProfileEmbed } from './responses.js';

const client = new Client({
    intents: [GatewayIntentBits.Guilds],
});

client.once(Events.ClientReady, (readyClient) => {
    console.log(`Discord bot logged in as ${readyClient.user.tag}.`);
});

client.on(Events.InteractionCreate, async (interaction) => {
    if (!interaction.isChatInputCommand()) {
        return;
    }

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
    }
});

async function handleLink(interaction) {
    await interaction.deferReply({ ephemeral: true });

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
    await interaction.deferReply({ ephemeral: true });

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

await client.login(config.botToken);
