@extends('layouts.app')

@section('title', 'Chat - EduChat')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');
    .chat-studio {
        font-family: 'Space Grotesk', sans-serif;
        background:
            radial-gradient(circle at 8% 8%, #dbeafe 0, transparent 30%),
            radial-gradient(circle at 92% 14%, #e9d5ff 0, transparent 24%),
            linear-gradient(160deg, #f5f3ff 0%, #ecfeff 46%, #f8fafc 100%);
    }
</style>
<div x-data="chatApp()" class="chat-studio min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur border-b border-white/60 shadow-sm sticky top-0 z-10">
        <div class="w-full py-3 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-800">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="flex items-center space-x-2">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <div>
                        <h1 class="font-bold text-xl text-gray-800">EduChat</h1>
                        <p class="text-xs text-gray-500" x-show="documentName" x-text="documentName"></p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <div class="hidden md:flex items-center space-x-2 px-3 py-2 rounded-xl bg-white border border-slate-200 text-sm text-slate-600">
                    <span>{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                    @csrf
                    <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-100 transition text-sm">
                        Logout
                    </button>
                </form>
                <!-- Summary Button -->
                <button 
                    @click="generateSummary"
                    x-show="sessionId && !isProcessing"
                    :disabled="isLoading"
                    class="hidden md:flex items-center space-x-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-4 py-2 rounded-xl hover:from-indigo-700 hover:to-violet-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Ringkasan</span>
                </button>

                <a
                    x-show="pdfUrl"
                    :href="pdfUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hidden md:flex items-center space-x-2 bg-white text-slate-700 px-4 py-2 rounded-xl hover:bg-slate-100 transition border border-slate-200"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z"/>
                    </svg>
                    <span>Lihat PDF</span>
                </a>

                <!-- Upload Button -->
                <button 
                    @click="$refs.fileInput.click()"
                    :disabled="isUploading"
                    class="flex items-center space-x-2 bg-cyan-600 text-white px-4 py-2 rounded-xl hover:bg-cyan-700 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span x-show="!isUploading">Upload PDF</span>
                    <span x-show="isUploading">Uploading...</span>
                </button>
                <input 
                    type="file" 
                    x-ref="fileInput" 
                    @change="uploadFile" 
                    accept=".pdf" 
                    class="hidden"
                >
            </div>
        </div>
    </header>

    <!-- Main Chat Area -->
    <div class="flex-1 w-full py-0 flex flex-col">
        <div class="flex-1 flex flex-col lg:flex-row items-start gap-4 min-h-0">
            <!-- Sidebar: PDF History -->
            <aside class="w-full lg:w-80 lg:max-h-[calc(100vh-5rem)] bg-white/90 rounded-2xl shadow-lg border border-white/70 flex flex-col min-h-0">
                <div class="px-4 py-4 border-b border-slate-100 bg-slate-50/80 rounded-t-2xl">
                    <h2 class="font-semibold text-gray-800">Riwayat PDF</h2>
                    <p class="text-xs text-gray-500 mt-1">Klik PDF untuk melihat chat sebelumnya</p>
                    <div class="mt-3">
                        <input
                            type="text"
                            x-model="sessionSearch"
                            placeholder="Cari PDF atau judul..."
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white/90 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                        >
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-if="filteredSessions().length === 0">
                        <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-3">
                            Tidak ada sesi yang cocok.
                        </div>
                    </template>

                    <template x-for="session in filteredSessions()" :key="session.session_id">
                        <div
                            class="rounded-xl border px-3 py-3 transition min-h-[92px]"
                            :class="session.session_id === sessionId ? 'border-cyan-500 bg-cyan-50' : 'border-slate-200 hover:border-cyan-300 hover:bg-white'"
                        >
                            <div class="flex items-center justify-between gap-2 h-full">
                                <button
                                    type="button"
                                    @click="selectSession(session)"
                                    :disabled="isLoading || isProcessing || isManagingSession"
                                    class="flex-1 text-left disabled:opacity-60 disabled:cursor-not-allowed min-w-0"
                                >
                                    <div class="font-medium text-sm text-gray-800 truncate" x-text="getSessionDisplayName(session)"></div>
                                    <div class="text-xs text-gray-500 mt-1 truncate" x-text="session.document?.original_filename || 'PDF Tanpa Nama'"></div>
                                    <div class="text-xs text-gray-500 mt-1 truncate" x-text="formatDate(session.updated_at)"></div>
                                </button>

                                <div class="flex items-center shrink-0">
                                    <button
                                        type="button"
                                        @click.stop="deleteSession(session)"
                                        :disabled="isLoading || isProcessing || isManagingSession"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-red-600 hover:bg-red-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                        title="Delete"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

            <!-- PDF Preview -->
            <aside class="hidden lg:flex lg:w-[26rem] xl:w-[30rem] lg:h-[calc(100vh-5rem)] bg-white/90 rounded-2xl shadow-lg border border-white/70 flex-col overflow-hidden min-h-0">
                <div class="px-4 py-4 border-b border-slate-100 bg-slate-50/80">
                    <h2 class="font-semibold text-gray-800">Preview Slide PDF</h2>
                    <p class="text-xs text-gray-500 mt-1" x-text="documentName || 'Pilih sesi untuk melihat slide'"></p>
                </div>
                <div class="flex-1 bg-slate-100">
                    <template x-if="pdfUrl">
                        <iframe :src="pdfUrl" class="w-full h-full border-0" title="PDF Preview"></iframe>
                    </template>
                    <template x-if="!pdfUrl">
                        <div class="h-full flex items-center justify-center text-sm text-gray-500 p-4 text-center">
                            Preview PDF akan muncul di sini.
                        </div>
                    </template>
                </div>
            </aside>

            <div class="flex-1 lg:h-[calc(100vh-5rem)] bg-white/95 rounded-2xl shadow-lg border border-white/70 flex flex-col overflow-hidden min-h-0">
            <!-- Messages Container -->
            <div 
                x-ref="messagesContainer"
                class="flex-1 overflow-y-auto p-6 space-y-4 bg-gradient-to-b from-white to-sky-50/30"
            >
                <!-- Empty State -->
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center px-4">
                        <div class="bg-blue-100 rounded-full p-6 mb-6">
                            <svg class="h-16 w-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Selamat Datang di EduChat!</h3>
                        <p class="text-gray-600 mb-6 max-w-md">
                            Upload slide PDF untuk memulai. AI akan memproses dokumen dan siap menjawab pertanyaan kamu.
                        </p>
                        <button 
                            @click="$refs.fileInput.click()"
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium"
                        >
                            Upload Slide Sekarang
                        </button>
                    </div>
                </template>

                <!-- Messages -->
                <template x-for="(message, index) in messages" :key="index">
                    <div 
                        class="flex"
                        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div 
                            class="max-w-[80%] md:max-w-[70%] rounded-2xl px-5 py-3 shadow-sm border"
                            :class="{
                                'bg-gradient-to-r from-cyan-600 to-blue-600 text-white border-cyan-500': message.role === 'user',
                                'bg-white text-slate-800 border-slate-200': message.role === 'assistant',
                                'bg-violet-50 text-violet-900 border-violet-200': message.role === 'system'
                            }"
                        >
                            <!-- Message Content -->
                            <div 
                                class="whitespace-pre-wrap break-words"
                                x-html="formatMessage(message.content)"
                            ></div>
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
                <template x-if="isLoading">
                    <div class="flex justify-start">
                        <div class="bg-gray-100 rounded-2xl px-5 py-4 shadow-sm">
                            <div class="flex space-x-2">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Processing Indicator -->
                <template x-if="isProcessing">
                    <div class="flex justify-center">
                        <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 rounded-xl px-6 py-4 shadow-sm">
                            <div class="flex items-center space-x-3">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium">Memproses dokumen... (<span x-text="processingProgress"></span>)</span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Input Area -->
            <div class="border-t border-slate-200 bg-white/95 p-4">
                <div class="flex space-x-3">
                    <input 
                        type="text" 
                        x-model="input"
                        @keyup.enter="sendMessage"
                        :disabled="!sessionId || isLoading || isProcessing"
                        placeholder="Tanya tentang materi..."
                        class="flex-1 border border-slate-300 rounded-xl px-5 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                    >
                    <button 
                        @click="sendMessage"
                        :disabled="!sessionId || isLoading || !input.trim() || isProcessing"
                        class="bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-6 py-3 rounded-xl hover:from-cyan-700 hover:to-blue-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed font-medium flex items-center space-x-2 shadow-sm"
                    >
                        <span class="hidden sm:inline">Kirim</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Helper Text -->
                <div class="mt-2 text-sm text-gray-500 text-center">
                    <span x-show="!sessionId">Upload PDF terlebih dahulu untuk memulai chat</span>
                    <span x-show="sessionId && !isProcessing">Tekan Enter untuk kirim pesan</span>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
function chatApp() {
    return {
        messages: [],
        input: '',
        sessions: [],
        sessionSearch: '',
        sessionId: null,
        pdfUrl: null,
        documentSessionId: null,
        documentId: null,
        documentName: '',
        isLoading: false,
        isManagingSession: false,
        isUploading: false,
        isProcessing: false,
        processingProgress: 'Menunggu...',

        async init() {
            await this.loadSessions();
        },

        async parseJsonResponse(response) {
            if (response.status === 401) {
                window.location.href = '{{ route('login') }}';
                throw new Error('Session berakhir. Silakan login ulang.');
            }

            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                return response.json();
            }

            await response.text();
            throw new Error(`Server returned non-JSON response (status ${response.status}).`);
        },

        extractPayload(responseBody) {
            return responseBody?.data ?? responseBody;
        },

        async createChatSession() {
            const response = await fetch('/api/chat/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    document_id: this.documentId
                })
            });

            const data = await this.parseJsonResponse(response);
            const payload = this.extractPayload(data);

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Gagal membuat chat session.');
            }

            this.sessionId = payload.session.session_id;
            this.setPdfPreview(this.sessionId);
            await this.loadSessions();
        },

        async loadSessions() {
            try {
                const response = await fetch('/api/chat/sessions', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await this.parseJsonResponse(response);
                const payload = this.extractPayload(data);

                if (response.ok && data.success) {
                    this.sessions = payload.sessions || [];
                }
            } catch (error) {
                console.error('Failed to load sessions:', error);
            }
        },

        filteredSessions() {
            const keyword = this.sessionSearch.trim().toLowerCase();
            if (!keyword) return this.sessions;

            return this.sessions.filter((session) => {
                const title = (session.title || '').toLowerCase();
                const filename = (session.document?.original_filename || '').toLowerCase();
                return title.includes(keyword) || filename.includes(keyword);
            });
        },

        getSessionDisplayName(session) {
            if (session?.title?.trim()) return session.title.trim();
            return session?.document?.original_filename || 'PDF Tanpa Nama';
        },

        async selectSession(session) {
            const selectedSessionId = typeof session === 'string' ? session : session.session_id;
            if (!selectedSessionId || this.isProcessing) return;

            this.sessionId = selectedSessionId;
            this.documentId = session.document_id ?? this.documentId;
            this.documentName = session.document?.original_filename || session.title || '';
            this.setPdfPreview(selectedSessionId);
            await this.loadMessages(selectedSessionId);
        },

        setPdfPreview(sessionId) {
            if (!sessionId) {
                this.pdfUrl = null;
                return;
            }

            this.pdfUrl = `/api/documents/file/${sessionId}?v=${Date.now()}`;
        },

        async deleteSession(session) {
            const confirmed = window.confirm(`Hapus sesi "${this.getSessionDisplayName(session)}"?`);
            if (!confirmed) return;

            this.isManagingSession = true;

            try {
                const response = await fetch(`/api/chat/${session.session_id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await this.parseJsonResponse(response);

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menghapus sesi.');
                }

                if (this.sessionId === session.session_id) {
                    this.sessionId = null;
                    this.pdfUrl = null;
                    this.documentId = null;
                    this.documentName = '';
                    this.messages = [];
                }

                await this.loadSessions();
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                this.isManagingSession = false;
            }
        },

        async loadMessages(sessionId) {
            this.isLoading = true;

            try {
                const response = await fetch(`/api/chat/messages/${sessionId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await this.parseJsonResponse(response);
                const payload = this.extractPayload(data);

                if (response.ok && data.success) {
                    this.messages = (payload.messages || []).map((message) => ({
                        ...message,
                        timestamp: message.created_at,
                    }));
                    this.scrollToBottom();
                } else {
                    this.messages = [{
                        role: 'system',
                        content: `Error: ${data.message}`,
                        timestamp: new Date()
                    }];
                }
            } catch (error) {
                this.messages = [{
                    role: 'system',
                    content: `Error: ${error.message}`,
                    timestamp: new Date()
                }];
            } finally {
                this.isLoading = false;
            }
        },

        async uploadFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.pdf')) {
                alert('Hanya file PDF yang didukung!');
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('Ukuran file maksimal 10MB!');
                return;
            }

            this.isUploading = true;
            this.documentName = file.name;
            
            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch('/api/documents/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await this.parseJsonResponse(response);
                const payload = this.extractPayload(data);

                if (response.ok && data.success) {
                    this.documentSessionId = payload.session_id;
                    this.documentId = payload.document_id;
                    this.sessionId = null;
                    this.pdfUrl = null;
                    
                    this.messages.push({
                        role: 'system',
                        content: `OK: Dokumen "${file.name}" berhasil diupload!\n\nSedang memproses dokumen...`,
                        timestamp: new Date()
                    });

                    this.scrollToBottom();
                    this.pollStatus();
                } else {
                    alert('Upload gagal: ' + data.message);
                }
            } catch (error) {
                alert('Error upload: ' + error.message);
            } finally {
                this.isUploading = false;
                event.target.value = ''; // Reset file input
            }
        },

        async pollStatus() {
            this.isProcessing = true;
            this.processingProgress = 'Memproses...';

            const interval = setInterval(async () => {
                try {
                    const response = await fetch(`/api/documents/status/${this.documentSessionId}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await this.parseJsonResponse(response);
                    const payload = this.extractPayload(data);

                    if (payload.status === 'ready') {
                        clearInterval(interval);
                        try {
                            await this.createChatSession();
                            this.isProcessing = false;
                        
                            this.messages.push({
                                role: 'system',
                            content: `OK: Dokumen berhasil diproses!\n\nTotal chunks: ${payload.num_chunks}\n\nKamu sekarang bisa mulai bertanya tentang materi.`,
                                timestamp: new Date()
                            });
                            await this.loadSessions();
                        
                            this.scrollToBottom();
                        } catch (error) {
                            this.isProcessing = false;
                            this.messages.push({
                                role: 'system',
                                content: `Error: Gagal membuat chat session: ${error.message}`,
                                timestamp: new Date()
                            });
                            this.scrollToBottom();
                        }
                    } else if (payload.status === 'failed') {
                        clearInterval(interval);
                        this.isProcessing = false;
                        
                        this.messages.push({
                            role: 'system',
                            content: `Error: Gagal memproses dokumen:\n${payload.error_message}`,
                            timestamp: new Date()
                        });
                        
                        this.scrollToBottom();
                    } else {
                        this.processingProgress = 'Memproses...';
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 3000); // Check every 3 seconds
        },

        async sendMessage() {
            if (!this.input.trim() || !this.sessionId || this.isLoading) return;

            const userMessage = this.input.trim();
            this.input = '';

            // Add user message
            this.messages.push({
                role: 'user',
                content: userMessage,
                timestamp: new Date()
            });

            this.scrollToBottom();
            this.isLoading = true;

            try {
                const response = await fetch('/api/chat/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        session_id: this.sessionId,
                        question: userMessage
                    })
                });

                const data = await this.parseJsonResponse(response);
                const payload = this.extractPayload(data);

                if (response.ok && data.success) {
                    this.messages.push({
                        role: 'assistant',
                        content: payload.answer,
                        timestamp: new Date()
                    });
                    await this.loadSessions();
                } else {
                    this.messages.push({
                        role: 'system',
                        content: `Error: ${data.message}`,
                        timestamp: new Date()
                    });
                }

                this.scrollToBottom();
            } catch (error) {
                this.messages.push({
                    role: 'system',
                    content: `Error: ${error.message}`,
                    timestamp: new Date()
                });
                this.scrollToBottom();
            } finally {
                this.isLoading = false;
            }
        },

        async generateSummary() {
            if (!this.sessionId || this.isLoading) return;

            this.isLoading = true;

            try {
                const response = await fetch('/api/chat/summary', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        session_id: this.sessionId
                    })
                });

                const data = await this.parseJsonResponse(response);
                const payload = this.extractPayload(data);

                if (response.ok && data.success) {
                    this.messages.push({
                        role: 'system',
                        content: `**Ringkasan Dokumen:**\n\n${payload.summary}`,
                        timestamp: new Date()
                    });
                    await this.loadSessions();
                    this.scrollToBottom();
                } else {
                    alert('Gagal membuat ringkasan: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                this.isLoading = false;
            }
        },

        formatMessage(content) {
            // Convert **bold** to <strong>
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Convert line breaks
            content = content.replace(/\n/g, '<br>');
            
            return content;
        },

        formatDate(timestamp) {
            return new Date(timestamp).toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                container.scrollTop = container.scrollHeight;
            });
        }
    }
}
</script>
@endsection
