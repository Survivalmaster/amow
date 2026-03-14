import axios from 'axios';
import { config } from './config.js';

export const api = axios.create({
    baseURL: config.apiBaseUrl,
    headers: {
        'Accept': 'application/json',
        'X-Discord-Link-Secret': config.linkSecret,
    },
    timeout: 10000,
});
