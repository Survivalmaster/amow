import { api } from './api.js';
import { isAxiosError } from 'axios';

export async function fetchWebhookCommands() {
    try {
        const response = await api.get('/api/discord/commands');
        return response.data.commands ?? [];
    } catch (error) {
        if (isAxiosError(error)) {
            const apiMessage = error.response?.data?.message;
            throw new Error(apiMessage || `Failed to load Discord command config: ${error.message}`);
        }

        throw error;
    }
}
