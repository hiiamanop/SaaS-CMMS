<div x-data="chatWidget()" class="fixed bottom-6 right-6 z-50 font-sans">
    {{-- Chat Toggle Button --}}
    <button @click="toggle()" 
            class="w-14 h-14 bg-slate-900 text-white rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.2)] flex items-center justify-center hover:bg-slate-800 transition-all duration-300 transform hover:scale-110 active:scale-95 border border-slate-700">
        <svg x-show="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        <svg x-show="isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span x-show="!isOpen" class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></span>
    </button>

    {{-- Chat Window --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="absolute bottom-20 right-0 w-[400px] h-[600px] bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 flex flex-col overflow-hidden"
         style="display: none;">
        
        {{-- Header --}}
        <div class="bg-slate-900 p-5 flex items-center justify-between shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/20 to-transparent opacity-50"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center border border-emerald-500/20 backdrop-blur-sm shadow-[0_0_15px_rgba(16,185,129,0.1)]">
                    <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4 12H2m20 0h-2m-3-9l-1 1m-6-1l1 1m9 9l-1-1m-10 1l1-1M5 8h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2zm4 4h.01M15 12h.01M9 16h6" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base tracking-tight">Aruna Assistant</h3>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>
                        <span class="text-[11px] text-slate-400 font-medium">Sistem Cerdas Aktif</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 relative z-10">
                <button @click="clearChat()" class="text-slate-400 hover:text-red-400 transition-colors p-1" title="Hapus Percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
                <button @click="isOpen = false" class="text-slate-400 hover:text-white transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto p-5 space-y-6 bg-[#f8fafc]">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user' ? 'bg-slate-800 text-white rounded-2xl rounded-tr-none shadow-md' : 'bg-white text-slate-700 border border-slate-100 rounded-2xl rounded-tl-none shadow-sm'"
                         class="max-w-[85%] px-5 py-3.5 text-[13px] leading-relaxed relative group">
                        <div x-html="formatMessage(msg.content)"></div>
                        <span :class="msg.role === 'user' ? 'text-slate-400' : 'text-slate-300'" 
                              class="block text-[9px] mt-2 font-medium tracking-wide uppercase" x-text="formatTime(msg.created_at)"></span>
                        
                        {{-- Decorative Tail --}}
                        <div x-show="msg.role === 'user'" class="absolute -top-px -right-2 w-4 h-4 bg-slate-800 clip-path-user"></div>
                    </div>
                </div>
            </template>
            
            <div x-show="isTyping" class="flex justify-start">
                <div class="bg-white border border-slate-100 rounded-2xl rounded-tl-none shadow-sm px-5 py-3 flex items-center gap-2">
                    <div class="flex gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce [animation-duration:1s]"></span>
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce [animation-duration:1s] [animation-delay:0.2s]"></span>
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce [animation-duration:1s] [animation-delay:0.4s]"></span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium ml-1">Aruna berpikir...</span>
                </div>
            </div>

            <div x-show="errorMessage" class="flex justify-center mt-2">
                <div class="bg-red-50 text-red-600 text-[11px] px-4 py-1.5 rounded-xl border border-red-100 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span x-text="errorMessage"></span>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-5 bg-white border-t border-slate-100 shadow-[0_-10px_40px_rgba(0,0,0,0.02)]">
            <form @submit.prevent="send()" class="flex gap-3 items-center bg-slate-50 p-1.5 rounded-2xl border border-slate-200 focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10 transition-all">
                <input type="text" x-model="newMessage" placeholder="Tulis pesan Anda..." 
                       class="flex-1 bg-transparent border-none px-4 py-2.5 text-[13px] text-slate-700 placeholder-slate-400 focus:ring-0">
                <button type="submit" :disabled="!newMessage.trim() || isTyping"
                        class="bg-slate-900 text-white p-2.5 rounded-xl hover:bg-emerald-600 disabled:opacity-20 disabled:cursor-not-allowed transition-all shadow-md active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
            <div class="flex justify-center items-center gap-1.5 mt-4 opacity-30 grayscale">
                <span class="text-[10px] font-bold text-slate-900 uppercase tracking-widest">Aruna Intelligent Platform</span>
            </div>
        </div>
    </div>
</div>

<style>
    .clip-path-user {
        clip-path: polygon(0 0, 0% 100%, 100% 0);
    }
</style>

<script>
function chatWidget() {
    return {
        isOpen: false,
        isTyping: false,
        errorMessage: '',
        messages: [],
        newMessage: '',
        
        async init() {
            await this.fetchMessages();
        },

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.scrollToBottom();
            }
        },

        async fetchMessages() {
            const res = await fetch('{{ route('chat.index') }}');
            this.messages = await res.json();
            this.scrollToBottom();
        },

        async clearChat() {
            if (!confirm('Hapus seluruh riwayat percakapan?')) return;
            try {
                await fetch('/chat/clear', {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                this.messages = [];
            } catch (err) {
                console.error(err);
            }
        },

        async send() {
            if (!this.newMessage.trim()) return;
            
            const content = this.newMessage;
            this.newMessage = '';
            this.isTyping = true;
            this.errorMessage = '';
            
            // Optimistic update
            this.messages.push({
                id: Date.now(),
                role: 'user',
                content: content,
                created_at: new Date().toISOString()
            });
            this.scrollToBottom();

            try {
                const res = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: content })
                });
                
                const data = await res.json();
                if (data.status === 'success') {
                    await this.fetchMessages();
                } else {
                    this.errorMessage = data.message;
                }
            } catch (err) {
                console.error('Chat error:', err);
                this.errorMessage = 'Gagal terhubung ke server.';
            } finally {
                this.isTyping = false;
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            setTimeout(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }, 50);
        },

        formatTime(dateStr) {
            const date = new Date(dateStr);
            return date.getHours().toString().padStart(2, '0') + ':' + 
                   date.getMinutes().toString().padStart(2, '0');
        },

        formatMessage(text) {
            if (!text) return '';
            // Escape HTML
            let escaped = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            // Bold **text**
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
            // Bullet points
            escaped = escaped.replace(/^- (.*)/gm, '• $1');
            // New lines
            return escaped.replace(/\n/g, '<br>');
        }
    }
}
</script>
