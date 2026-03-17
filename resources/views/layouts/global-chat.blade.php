<div
    x-data="globalChatBox({
        fetchUrl: @js(route('chat.global.index')),
        sendUrl: @js(route('chat.global.store')),
        csrfToken: @js(csrf_token()),
        currentCharacter: @js($chatCharacter->name),
    })"
    x-init="init()"
    x-cloak
    class="pointer-events-none fixed inset-0 z-[85]"
>
    <section
        x-ref="panel"
        class="pointer-events-auto fixed bottom-5 right-5 flex w-[min(92vw,20rem)] max-w-[20rem] flex-col overflow-hidden rounded-[1.35rem] border border-white/10 bg-[linear-gradient(180deg,rgba(8,14,10,0.72),rgba(6,10,8,0.68))] shadow-2xl shadow-black/45 backdrop-blur-xl"
        :class="{ 'h-[28rem]': !minimized, 'h-auto': minimized }"
        :style="panelStyle"
    >
        <header
            @mousedown.prevent="startDrag($event)"
            class="flex cursor-move items-center justify-between gap-3 border-b border-white/10 bg-black/20 px-3 py-2.5"
        >
            <div>
                <p class="font-['Teko'] text-[1.9rem] uppercase leading-none tracking-[0.08em] text-[#f4ecd0]">World Chat</p>
                <p class="mt-0.5 text-[10px] uppercase tracking-[0.18em] text-white/45">Live transmission across Plastica</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="minimized = !minimized; persistPosition()" class="rounded-full border border-white/10 px-2.5 py-1 text-[9px] font-semibold uppercase tracking-[0.18em] text-white/60">
                    <span x-text="minimized ? 'Open' : 'Minimize'"></span>
                </button>
            </div>
        </header>

        <template x-if="!minimized">
            <div class="flex min-h-0 flex-1 flex-col">
                <div x-ref="messages" class="min-h-0 flex-1 space-y-2.5 overflow-y-auto px-2.5 py-2.5">
                    <template x-for="message in messages" :key="message.id">
                        <article class="rounded-[1rem] border border-white/10 bg-black/20 px-2.5 py-2.5">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <p class="font-['Teko'] text-[1.05rem] uppercase leading-none tracking-[0.05em]" :style="`color: ${message.faction_color || '#f4ecd0'}`" x-text="message.character_name"></p>
                                    <div class="flex flex-wrap items-center gap-1">
                                        <template x-for="icon in message.account_icons" :key="icon.name + icon.icon_value">
                                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-white/10 bg-black/30 text-[8px]" :style="`color: ${icon.color}`" :title="icon.tooltip">
                                                <i :class="icon.icon_value"></i>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <div class="mt-1 flex items-center justify-end gap-3 text-[9px] uppercase tracking-[0.18em] text-white/40">
                                    <span x-text="message.created_at"></span>
                                </div>
                                <p class="mt-1.5 whitespace-pre-wrap break-words text-[13px] leading-5" :class="message.message_type === 'standard' ? 'text-white/78' : 'text-[#b98cff] italic'" x-text="message.display_message"></p>
                            </div>
                        </article>
                    </template>
                    <template x-if="messages.length === 0">
                        <div class="rounded-[1rem] border border-dashed border-white/10 px-4 py-7 text-center text-[10px] uppercase tracking-[0.18em] text-white/38">
                            No messages yet.
                        </div>
                    </template>
                </div>

                <form @submit.prevent="sendMessage()" class="border-t border-white/10 bg-black/15 p-2.5">
                    <label class="sr-only" for="global-chat-message">World chat message</label>
                    <textarea
                        id="global-chat-message"
                        x-model="draft"
                        @keydown.enter.prevent="if ($event.shiftKey) { draft += '\n'; return; } sendMessage()"
                        maxlength="400"
                        rows="2"
                        class="w-full resize-none rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-[13px] text-[#f4ecd0] placeholder:text-white/28"
                        placeholder="Speak... Use /me or /do"
                    ></textarea>
                    <div class="mt-2.5 flex items-center justify-between gap-3">
                        <p class="text-[9px] uppercase tracking-[0.18em] text-white/35">
                            Signed in as <span class="text-white/55" x-text="currentCharacter"></span>
                        </p>
                        <button type="submit" class="rounded-full bg-[#7ead59] px-3.5 py-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#07100c]" :disabled="sending || !draft.trim()">
                            <span x-text="sending ? 'Sending...' : 'Send'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </section>
</div>

@push('scripts')
    <script>
        function globalChatBox({ fetchUrl, sendUrl, csrfToken, currentCharacter }) {
            return {
                fetchUrl,
                sendUrl,
                csrfToken,
                currentCharacter,
                messages: [],
                draft: '',
                sending: false,
                minimized: false,
                isFetching: false,
                hasDragged: false,
                position: { x: null, y: null },
                dragOffset: { x: 0, y: 0 },
                panelStyle: '',
                pollTimer: null,
                init() {
                    this.restorePosition();
                    this.updatePanelStyle();
                    this.fetchMessages(true);
                    this.pollTimer = window.setInterval(() => this.fetchMessages(false), 2500);
                    window.addEventListener('resize', () => this.updatePanelStyle());
                },
                restorePosition() {
                    try {
                        const saved = JSON.parse(window.localStorage.getItem('amow-global-chat-position') || 'null');

                        if (saved) {
                            this.position = {
                                x: typeof saved.x === 'number' ? saved.x : null,
                                y: typeof saved.y === 'number' ? saved.y : null,
                            };
                            this.minimized = Boolean(saved.minimized);
                            this.hasDragged = this.position.x !== null && this.position.y !== null;
                        }
                    } catch (error) {
                        this.hasDragged = false;
                    }
                },
                persistPosition() {
                    window.localStorage.setItem('amow-global-chat-position', JSON.stringify({
                        x: this.position.x,
                        y: this.position.y,
                        minimized: this.minimized,
                    }));
                    this.updatePanelStyle();
                },
                updatePanelStyle() {
                    if (!this.$refs.panel) {
                        return;
                    }

                    const maxX = Math.max(12, window.innerWidth - this.$refs.panel.offsetWidth - 12);
                    const maxY = Math.max(12, window.innerHeight - this.$refs.panel.offsetHeight - 12);

                    if (this.position.x !== null) {
                        this.position.x = Math.min(Math.max(12, this.position.x), maxX);
                    }

                    if (this.position.y !== null) {
                        this.position.y = Math.min(Math.max(12, this.position.y), maxY);
                    }

                    this.panelStyle = this.position.x !== null && this.position.y !== null
                        ? `left:${this.position.x}px;top:${this.position.y}px;right:auto;bottom:auto;`
                        : '';
                },
                startDrag(event) {
                    if (window.innerWidth < 768) {
                        return;
                    }

                    const rect = this.$refs.panel.getBoundingClientRect();

                    this.position = { x: rect.left, y: rect.top };
                    this.dragOffset = {
                        x: event.clientX - rect.left,
                        y: event.clientY - rect.top,
                    };

                    const onMove = (moveEvent) => {
                        this.hasDragged = true;
                        this.position = {
                            x: moveEvent.clientX - this.dragOffset.x,
                            y: moveEvent.clientY - this.dragOffset.y,
                        };
                        this.updatePanelStyle();
                    };

                    const onUp = () => {
                        window.removeEventListener('mousemove', onMove);
                        window.removeEventListener('mouseup', onUp);
                        this.persistPosition();
                    };

                    window.addEventListener('mousemove', onMove);
                    window.addEventListener('mouseup', onUp);
                },
                async fetchMessages(scrollToBottom = false) {
                    if (this.isFetching) {
                        return;
                    }

                    this.isFetching = true;

                    try {
                        const response = await fetch(this.fetchUrl, {
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
                        const previousLastId = this.messages.length ? this.messages[this.messages.length - 1].id : null;

                        this.messages = Array.isArray(payload.messages) ? payload.messages : [];

                        this.$nextTick(() => {
                            const nextLastId = this.messages.length ? this.messages[this.messages.length - 1].id : null;

                            if (scrollToBottom || previousLastId !== nextLastId) {
                                this.scrollToBottom();
                            }
                        });
                    } finally {
                        this.isFetching = false;
                    }
                },
                async sendMessage() {
                    const message = this.draft.trim();

                    if (!message || this.sending) {
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
                            body: JSON.stringify({ message }),
                        });

                        if (!response.ok) {
                            return;
                        }

                        this.draft = '';
                        await this.fetchMessages(true);
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
