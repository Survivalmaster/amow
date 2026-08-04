import './bootstrap';
import Alpine from 'alpinejs';
import { bootTerritoryMap } from './territory-map';

window.globalChatBox = function globalChatBox({ stateUrl, sendUrl, csrfToken, currentCharacter, currentFaction }) {
    return {
        stateUrl,
        sendUrl,
        csrfToken,
        currentCharacter,
        currentFaction,
        activeTab: 'world',
        unread: { world: 0, nation: 0, direct: 0 },
        worldMessages: [],
        nationMessages: [],
        directMessages: [],
        onlineCharacters: [],
        onlineCount: 1,
        selectedDirectCharacterId: null,
        draft: '',
        sending: false,
        hidden: false,
        isFetching: false,
        pollTimer: null,
        init() {
            this.restoreState();
            this.fetchState(true);
            this.pollTimer = window.setInterval(() => this.fetchState(false), 2500);
        },
        activeMessages() {
            if (this.activeTab === 'nation') return this.nationMessages;
            if (this.activeTab === 'direct') return this.directMessages;
            return this.worldMessages;
        },
        selectedDirectCharacterName() {
            return this.onlineCharacters.find((character) => character.id === this.selectedDirectCharacterId)?.name || 'No target selected';
        },
        activeTabLabel() {
            if (this.activeTab === 'nation') return `${this.currentFaction} channel`;
            if (this.activeTab === 'direct') return this.selectedDirectCharacterId ? `Direct to ${this.selectedDirectCharacterName()}` : 'Choose an online user';
            return 'World channel';
        },
        restoreState() {
            try {
                const saved = JSON.parse(window.localStorage.getItem('amow-global-chat-state') || 'null');
                if (saved) this.hidden = Boolean(saved.hidden);
            } catch (error) {
            }
        },
        persistState() {
            window.localStorage.setItem('amow-global-chat-state', JSON.stringify({ hidden: this.hidden }));
        },
        hideChat() {
            this.hidden = true;
            this.persistState();
        },
        showChat() {
            this.hidden = false;
            this.clearUnread();
            this.persistState();
            this.$nextTick(() => this.scrollToBottom());
        },
        setActiveTab(tab) {
            this.activeTab = tab;
            this.unread[tab] = 0;
            this.scrollToBottom();
        },
        clearUnread() {
            this.unread = { world: 0, nation: 0, direct: 0 };
        },
        totalUnreadCount() {
            return this.unread.world + this.unread.nation + this.unread.direct;
        },
        applyUnread(channel, previousLastId, nextLastId) {
            if (!previousLastId || previousLastId === nextLastId) return;
            if (this.hidden || this.activeTab !== channel) this.unread[channel] += 1;
        },
        async fetchState(scrollToBottom = false) {
            if (this.isFetching) return;

            this.isFetching = true;

            try {
                const url = new URL(this.stateUrl, window.location.origin);

                if (this.selectedDirectCharacterId) {
                    url.searchParams.set('direct_character_id', this.selectedDirectCharacterId);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) return;

                const payload = await response.json();
                const previousWorldLastId = this.worldMessages.length ? this.worldMessages[this.worldMessages.length - 1].id : null;
                const previousNationLastId = this.nationMessages.length ? this.nationMessages[this.nationMessages.length - 1].id : null;
                const previousDirectLastId = this.directMessages.length ? this.directMessages[this.directMessages.length - 1].id : null;

                this.worldMessages = Array.isArray(payload.world_messages) ? payload.world_messages : [];
                this.nationMessages = Array.isArray(payload.nation_messages) ? payload.nation_messages : [];
                this.directMessages = Array.isArray(payload.direct_messages) ? payload.direct_messages : [];
                this.onlineCharacters = Array.isArray(payload.online_characters) ? payload.online_characters : [];
                this.onlineCount = Number(payload.online_count || 1);
                this.selectedDirectCharacterId = payload.selected_direct_character_id || this.selectedDirectCharacterId;

                const nextWorldLastId = this.worldMessages.length ? this.worldMessages[this.worldMessages.length - 1].id : null;
                const nextNationLastId = this.nationMessages.length ? this.nationMessages[this.nationMessages.length - 1].id : null;
                const nextDirectLastId = this.directMessages.length ? this.directMessages[this.directMessages.length - 1].id : null;

                this.applyUnread('world', previousWorldLastId, nextWorldLastId);
                this.applyUnread('nation', previousNationLastId, nextNationLastId);
                this.applyUnread('direct', previousDirectLastId, nextDirectLastId);

                if (!this.hidden) {
                    this.unread[this.activeTab] = 0;
                }

                this.$nextTick(() => {
                    const activeMessages = this.activeMessages();
                    const previousLastId = this.activeTab === 'world'
                        ? previousWorldLastId
                        : (this.activeTab === 'nation' ? previousNationLastId : previousDirectLastId);
                    const nextLastId = activeMessages.length ? activeMessages[activeMessages.length - 1].id : null;

                    if (!this.hidden && (scrollToBottom || previousLastId !== nextLastId)) {
                        this.scrollToBottom();
                    }
                });
            } finally {
                this.isFetching = false;
            }
        },
        async sendMessage() {
            const message = this.draft.trim();

            if (!message || this.sending || (this.activeTab === 'direct' && !this.selectedDirectCharacterId)) {
                return;
            }

            this.sending = true;

            try {
                const response = await fetch(this.sendUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        channel: this.activeTab,
                        message,
                        target_character_id: this.activeTab === 'direct' ? this.selectedDirectCharacterId : null,
                    }),
                });

                if (!response.ok) return;

                this.draft = '';
                await this.fetchState(true);
            } finally {
                this.sending = false;
            }
        },
        scrollToBottom() {
            if (this.$refs.messages) {
                this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
            }
        },
    };
};

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

    const resolveCurrentActivity = () => {
        const activityElement = document.querySelector('[data-presence-activity]');

        if (activityElement) {
            return activityElement.dataset.presenceActivity ?? '';
        }

        return document.body.dataset.currentActivity ?? '';
    };

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
                    current_activity_text: resolveCurrentActivity(),
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

const startConstructionTimers = () => {
    const timers = document.querySelectorAll('[data-construction-timer]');

    if (!timers.length) {
        return;
    }

    const gradientForPercent = (percent) => {
        if (percent >= 100) return 'linear-gradient(90deg,#7ead59 0%,#d7edc7 100%)';
        if (percent >= 75) return 'linear-gradient(90deg,#8fbe63 0%,#d7edc7 100%)';
        if (percent >= 40) return 'linear-gradient(90deg,#c2a84f 0%,#f4ecd0 100%)';
        return 'linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%)';
    };

    const formatDuration = (totalSeconds) => {
        const safeSeconds = Math.max(0, totalSeconds);
        const hours = Math.floor(safeSeconds / 3600);
        const minutes = Math.floor((safeSeconds % 3600) / 60);
        const seconds = safeSeconds % 60;

        return [hours, minutes, seconds].map((value) => String(value).padStart(2, '0')).join(':');
    };

    const updateTimers = () => {
        const now = Date.now();

        timers.forEach((timer) => {
            const buildStart = Date.parse(timer.getAttribute('data-build-start') || '');
            const buildComplete = Date.parse(timer.getAttribute('data-build-complete') || '');

            if (!buildStart || !buildComplete || buildComplete <= buildStart) {
                return;
            }

            const durationMs = Math.max(1000, buildComplete - buildStart);
            const elapsedMs = Math.max(0, Math.min(durationMs, now - buildStart));
            const remainingSeconds = Math.max(0, Math.ceil((buildComplete - now) / 1000));
            const progressPercent = Math.max(0, Math.min(100, Math.round((elapsedMs / durationMs) * 100)));
            const isComplete = now >= buildComplete;

            const fill = timer.querySelector('[data-build-progress-fill]');
            const percentLabel = timer.querySelector('[data-build-progress-percent]');
            const remainingLabel = timer.querySelector('[data-build-progress-remaining]');
            const statusRoot = timer.closest('.rounded-2xl') || timer.parentElement;
            const statusLabel = statusRoot ? statusRoot.querySelector('[data-build-status]') : null;

            if (fill) {
                fill.style.width = `${progressPercent}%`;
                fill.style.backgroundColor = progressPercent >= 100 ? '#7ead59' : progressPercent >= 40 ? '#c2a84f' : '#c65b3f';
                fill.style.backgroundImage = gradientForPercent(progressPercent);
            }

            if (percentLabel) {
                percentLabel.textContent = `${progressPercent}%`;
            }

            if (remainingLabel) {
                remainingLabel.textContent = isComplete ? '00:00:00' : formatDuration(remainingSeconds);
            }

            if (statusLabel) {
                statusLabel.textContent = isComplete ? 'Ready' : 'Building';
                statusLabel.style.color = isComplete ? '#d7edc7' : '#f4ecd0';
            }
        });
    };

    updateTimers();
    window.setInterval(updateTimers, 1000);
};

document.addEventListener('DOMContentLoaded', () => {
    startPresenceHeartbeat();
    startCharacterStatePolling();
    startConstructionTimers();
    bootTerritoryMap(document.getElementById('territory-map-app'));
});
