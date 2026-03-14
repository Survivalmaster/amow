import axios from 'axios';
import { config } from './config.js';

export async function postWpnnAnnouncement({ headline, announcement, imageUrl, authorTag }) {
    if (!config.wpnnWebhookUrl) {
        throw new Error('DISCORD_WPNN_WEBHOOK_URL is not configured.');
    }

    const embed = {
        title: headline,
        description: announcement,
        color: 0xc65b3f,
        footer: {
            text: `WPNN desk | Posted by ${authorTag}`,
        },
        timestamp: new Date().toISOString(),
    };

    if (imageUrl) {
        embed.image = { url: imageUrl };
    }

    await axios.post(config.wpnnWebhookUrl, {
        embeds: [embed],
    }, {
        headers: {
            'Content-Type': 'application/json',
        },
        timeout: 10000,
    });
}
