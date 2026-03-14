import 'dotenv/config';

function requireEnv(name) {
    const value = process.env[name];

    if (!value) {
        throw new Error(`Missing required environment variable: ${name}`);
    }

    return value;
}

function trimTrailingSlash(value) {
    return value.replace(/\/+$/, '');
}

export const config = {
    apiBaseUrl: trimTrailingSlash(process.env.AMOW_API_URL || process.env.APP_URL || 'http://127.0.0.1:8000'),
    applicationId: requireEnv('DISCORD_APPLICATION_ID'),
    botToken: requireEnv('DISCORD_BOT_TOKEN'),
    guildId: requireEnv('DISCORD_GUILD_ID'),
    linkSecret: requireEnv('DISCORD_LINKING_SECRET'),
    linkUrl: process.env.DISCORD_LINK_URL || '',
};
