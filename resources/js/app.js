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

document.addEventListener('DOMContentLoaded', () => {
    startCharacterStatePolling();
});
