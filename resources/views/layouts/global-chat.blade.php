<div
    x-data="globalChatBox({
        stateUrl: @js(route('chat.state')),
        sendUrl: @js(route('chat.send')),
        csrfToken: @js(csrf_token()),
        currentCharacter: @js($chatCharacter->name),
        currentFaction: @js($chatCharacter->faction?->name ?? 'Nation'),
    })"
    x-init="init()"
    x-cloak
    class="pointer-events-none fixed inset-0 z-[85]"
>
    <section
        x-show="!hidden"
        class="pointer-events-auto fixed bottom-5 right-5 flex w-[min(92vw,21rem)] max-w-[21rem] flex-col overflow-hidden rounded-[1.35rem] border border-white/10 bg-[linear-gradient(180deg,rgba(8,14,10,0.72),rgba(6,10,8,0.68))] shadow-2xl shadow-black/45 backdrop-blur-xl"
        :class="{ 'h-[30rem]': !minimized, 'h-auto': minimized }"
    >
        <header
            class="flex items-center justify-between gap-3 border-b border-white/10 bg-black/20 px-3 py-2.5"
        >
            <div>
                <p class="font-['Teko'] text-[1.75rem] uppercase leading-none tracking-[0.08em] text-[#f4ecd0]">Chat</p>
                <p class="mt-0.5 text-[10px] uppercase tracking-[0.18em] text-white/45">
                    <span x-text="`${onlineCount} online`"></span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="toggleMinimized()" class="relative rounded-full border border-white/10 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.18em] text-white/60">
                    <span x-text="minimized ? 'Open' : 'Minimize'"></span>
                    <template x-if="minimized && totalUnreadCount() > 0">
                        <span class="absolute -right-1.5 -top-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-[#c65b3f] px-1 text-[9px] font-bold text-white" x-text="totalUnreadCount()"></span>
                    </template>
                </button>
                <button type="button" @click="hideChat()" class="rounded-full border border-white/10 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.18em] text-white/60">
                    Hide
                </button>
            </div>
        </header>

        <template x-if="!minimized">
            <div class="flex min-h-0 flex-1 flex-col">
                <div class="grid grid-cols-3 gap-1 border-b border-white/10 bg-black/10 p-2">
                    <button type="button" @click="setActiveTab('world')" class="relative rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em]" :class="activeTab === 'world' ? 'bg-[#7ead59] text-[#07100c]' : 'bg-white/5 text-white/60'">
                        <span>World</span>
                        <template x-if="unread.world > 0">
                            <span class="absolute -right-1 -top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-[#c65b3f] px-1 text-[9px] font-bold text-white" x-text="unread.world"></span>
                        </template>
                    </button>
                    <button type="button" @click="setActiveTab('nation')" class="relative rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em]" :class="activeTab === 'nation' ? 'bg-[#7ead59] text-[#07100c]' : 'bg-white/5 text-white/60'">
                        <span>Nation</span>
                        <template x-if="unread.nation > 0">
                            <span class="absolute -right-1 -top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-[#c65b3f] px-1 text-[9px] font-bold text-white" x-text="unread.nation"></span>
                        </template>
                    </button>
                    <button type="button" @click="setActiveTab('direct')" class="relative rounded-full px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.18em]" :class="activeTab === 'direct' ? 'bg-[#7ead59] text-[#07100c]' : 'bg-white/5 text-white/60'">
                        <span>Direct</span>
                        <template x-if="unread.direct > 0">
                            <span class="absolute -right-1 -top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-[#c65b3f] px-1 text-[9px] font-bold text-white" x-text="unread.direct"></span>
                        </template>
                    </button>
                </div>

                <div class="border-b border-white/10 bg-black/10 px-2.5 py-2">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                        <template x-for="onlineCharacter in onlineCharacters" :key="onlineCharacter.id">
                            <button
                                type="button"
                                @click="activeTab = 'direct'; selectedDirectCharacterId = onlineCharacter.id; unread.direct = 0; fetchState(true)"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.16em]"
                                :class="selectedDirectCharacterId === onlineCharacter.id && activeTab === 'direct' ? 'border-[#7ead59]/40 bg-[#7ead59]/12 text-[#d7edc7]' : 'border-white/10 bg-black/20 text-white/60'"
                            >
                                <span class="h-2 w-2 rounded-full" :style="`background:${onlineCharacter.faction_color || '#f4ecd0'}`"></span>
                                <span x-text="onlineCharacter.name"></span>
                            </button>
                        </template>
                        <template x-if="onlineCharacters.length === 0">
                            <span class="text-[9px] uppercase tracking-[0.16em] text-white/35">No one else online</span>
                        </template>
                    </div>
                </div>

                <template x-if="activeTab === 'direct'">
                    <div class="border-b border-white/10 bg-black/10 px-2.5 py-2">
                        <p class="text-[9px] uppercase tracking-[0.18em] text-white/35">Direct Target</p>
                        <p class="mt-1 font-['Teko'] text-xl uppercase tracking-[0.05em] text-[#f4ecd0]" x-text="selectedDirectCharacterName()"></p>
                    </div>
                </template>

                <div x-ref="messages" class="min-h-0 flex-1 space-y-2 overflow-y-auto px-2.5 py-2.5">
                    <template x-for="message in activeMessages()" :key="`${activeTab}-${message.id}`">
                        <article class="rounded-[1rem] border border-white/10 bg-black/20 px-2.5 py-2">
                            <div class="min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex flex-wrap items-center gap-1">
                                        <p class="font-['Teko'] text-[0.95rem] uppercase leading-none tracking-[0.04em]" :style="`color: ${message.faction_color || '#f4ecd0'}`" x-text="message.character_name"></p>
                                        <div class="flex flex-wrap items-center gap-1">
                                            <template x-for="icon in message.account_icons" :key="icon.name + icon.icon_value">
                                                <span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full border border-white/10 bg-black/30 text-[7px]" :style="`color: ${icon.color}`" :title="icon.tooltip">
                                                    <i :class="icon.icon_value"></i>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="shrink-0 pt-[1px] text-[9px] uppercase tracking-[0.16em] text-white/35" x-text="message.created_at"></span>
                                </div>
                                <div class="mt-0.5">
                                    <p class="min-w-0 whitespace-pre-wrap break-words text-[12px] leading-5" :class="message.message_type === 'standard' ? 'text-white/78' : 'text-[#b98cff] italic'" x-text="message.display_message"></p>
                                </div>
                            </div>
                        </article>
                    </template>
                    <template x-if="activeMessages().length === 0">
                        <div class="rounded-[1rem] border border-dashed border-white/10 px-4 py-7 text-center text-[10px] uppercase tracking-[0.18em] text-white/38">
                            <span x-text="activeTab === 'direct' ? 'No messages with this character yet.' : 'No messages yet.'"></span>
                        </div>
                    </template>
                </div>

                <form @submit.prevent="sendMessage()" class="border-t border-white/10 bg-black/15 p-2.5">
                    <label class="sr-only" for="global-chat-message">Chat message</label>
                    <textarea
                        id="global-chat-message"
                        x-model="draft"
                        @keydown.enter.prevent="if ($event.shiftKey) { draft += '\n'; return; } sendMessage()"
                        maxlength="400"
                        rows="2"
                        class="w-full resize-none rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-[12px] text-[#f4ecd0] placeholder:text-white/28"
                        :placeholder="activeTab === 'world' ? 'Speak to the world... Use /me or /do' : (activeTab === 'nation' ? `Speak to ${currentFaction}... Use /me or /do` : (selectedDirectCharacterId ? `Message ${selectedDirectCharacterName()}...` : 'Select an online character to message'))"
                    ></textarea>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-[9px] uppercase tracking-[0.16em] text-white/35">
                            <span x-text="activeTabLabel()"></span>
                        </p>
                        <button type="submit" class="rounded-full bg-[#7ead59] px-3.5 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#07100c]" :disabled="sending || !draft.trim() || (activeTab === 'direct' && !selectedDirectCharacterId)">
                            <span x-text="sending ? 'Sending...' : 'Send'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </section>

    <button
        x-show="hidden"
        x-cloak
        type="button"
        @click="showChat()"
        class="pointer-events-auto fixed bottom-5 right-5 inline-flex items-center gap-2 rounded-full border border-white/10 bg-[linear-gradient(180deg,rgba(8,14,10,0.9),rgba(6,10,8,0.86))] px-4 py-2.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#f4ecd0] shadow-2xl shadow-black/45 backdrop-blur-xl"
    >
        <i class="fa-solid fa-comments text-[#7ead59]"></i>
        <span>Show Chat</span>
        <template x-if="totalUnreadCount() > 0">
            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#c65b3f] px-1 text-[10px] font-bold text-white" x-text="totalUnreadCount()"></span>
        </template>
    </button>
</div>

@push('scripts')
    <script>
        function globalChatBox({ stateUrl, sendUrl, csrfToken, currentCharacter, currentFaction }) {
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
                minimized: false,
                hidden: false,
                isFetching: false,
                pollTimer: null,
                init() {
                    this.restoreState();
                    this.fetchState(true);
                    this.pollTimer = window.setInterval(() => this.fetchState(false), 2500);
                },
                activeMessages() {
                    if (this.activeTab === 'nation') {
                        return this.nationMessages;
                    }

                    if (this.activeTab === 'direct') {
                        return this.directMessages;
                    }

                    return this.worldMessages;
                },
                selectedDirectCharacterName() {
                    return this.onlineCharacters.find((character) => character.id === this.selectedDirectCharacterId)?.name || 'No target selected';
                },
                activeTabLabel() {
                    if (this.activeTab === 'nation') {
                        return `${this.currentFaction} channel`;
                    }

                    if (this.activeTab === 'direct') {
                        return this.selectedDirectCharacterId ? `Direct to ${this.selectedDirectCharacterName()}` : 'Choose an online user';
                    }

                    return 'World channel';
                },
                restoreState() {
                    try {
                        const saved = JSON.parse(window.localStorage.getItem('amow-global-chat-state') || 'null');

                        if (saved) {
                            this.minimized = Boolean(saved.minimized);
                            this.hidden = Boolean(saved.hidden);
                        }
                    } catch (error) {
                    }
                },
                persistState() {
                    window.localStorage.setItem('amow-global-chat-state', JSON.stringify({
                        minimized: this.minimized,
                        hidden: this.hidden,
                    }));
                },
                toggleMinimized() {
                    this.minimized = !this.minimized;
                    this.hidden = false;

                    if (!this.minimized) {
                        this.clearUnread();
                        this.scrollToBottom();
                    }

                    this.persistState();
                },
                hideChat() {
                    this.hidden = true;
                    this.persistState();
                },
                showChat() {
                    this.hidden = false;
                    this.minimized = false;
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
                    if (!previousLastId || previousLastId === nextLastId) {
                        return;
                    }

                    if (this.minimized || this.activeTab !== channel) {
                        this.unread[channel] += 1;
                    }
                },
                async fetchState(scrollToBottom = false) {
                    if (this.isFetching) {
                        return;
                    }

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

                        if (!response.ok) {
                            return;
                        }

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

                        if (!this.minimized && !this.hidden) {
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

                        if (!response.ok) {
                            return;
                        }

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
        }
    </script>
@endpush
