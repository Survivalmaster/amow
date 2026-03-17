import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const getValue = (state, key) => state?.[key];

const updateCharacterStateDom = (state) => {
    document.querySelectorAll('[data-character-field]').forEach((element) => {
        const value = getValue(state, element.dataset.characterField);

        if (value !== undefined && value !== null) {
            element.textContent = value;
        }
    });

    document.querySelectorAll('[data-character-width]').forEach((element) => {
        const value = getValue(state, element.dataset.characterWidth);

        if (value !== undefined && value !== null) {
            element.style.width = `${value}%`;
        }
    });

    document.querySelectorAll('[data-character-toggle-disabled]').forEach((element) => {
        const key = element.dataset.characterToggleDisabled;
        const value = Boolean(getValue(state, key));

        element.disabled = value;
    });
};

const startCharacterStatePolling = () => {
    const stateUrl = document.body.dataset.characterStateUrl;

    if (!stateUrl) {
        return;
    }

    let isFetching = false;

    const fetchState = async () => {
        if (isFetching) {
            return;
        }

        isFetching = true;

        try {
            const response = await fetch(stateUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const state = await response.json();

            updateCharacterStateDom(state);
            window.dispatchEvent(new CustomEvent('character-state:updated', { detail: state }));
        } finally {
            isFetching = false;
        }
    };

    fetchState();
    window.setInterval(fetchState, 3000);
};

const startPresenceHeartbeat = () => {
    const presenceUrl = document.body.dataset.presenceUrl;

    if (!presenceUrl) {
        return;
    }

    let isSending = false;

    const sendHeartbeat = async () => {
        if (isSending) {
            return;
        }

        isSending = true;

        try {
            await fetch(presenceUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    current_path: document.body.dataset.currentPath,
                    current_page_name: document.body.dataset.currentPageName,
                }),
            });
        } finally {
            isSending = false;
        }
    };

    sendHeartbeat();
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            sendHeartbeat();
        }
    });
    window.addEventListener('focus', sendHeartbeat);
    window.setInterval(sendHeartbeat, 5000);
};

document.addEventListener('DOMContentLoaded', () => {
    startPresenceHeartbeat();
    startCharacterStatePolling();
});
