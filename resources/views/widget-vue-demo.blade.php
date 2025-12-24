<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue.js ChatWidget Demo - User Identification</title>

    <!-- Meta tags -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="reverb-app-key" content="{{ config('broadcasting.connections.reverb.app_key') }}">

    <!-- Styles -->
    <link rel="stylesheet" href="/widget/vue-styles.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Vue.js 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Axios -->
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <!-- Laravel Echo -->
    <script src="//{{ request()->getHost() }}:6001/socket.io/socket.io.js"></script>
    <script>
        window.io = io;
        class Echo {
            constructor(config) {
                this.config = config;
                this.connector = {
                    socket: io(`${config.wsHost}:${config.wsPort}`, {
                        transports: config.enabledTransports || ['websocket'],
                        forceNew: true
                    }),
                    channels: {}
                };

                this.connector.socket.on('connect', () => {
                    console.log('Socket connected');
                });
            }

            private(channelName) {
                const channel = {
                    callbacks: {},
                    listen: function(event, callback) {
                        this.callbacks[event] = callback;
                        return this;
                    },
                    subscribed: function(callback) {
                        callback();
                        return this;
                    },
                    error: function(callback) {
                        return this;
                    }
                };

                this.connector.channels[channelName] = channel;
                return channel;
            }

            channel(channelName) {
                return this.private(channelName);
            }

            leave(channelName) {
                delete this.connector.channels[channelName];
            }
        }
        window.Echo = Echo;
    </script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }

        .demo-card {
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .code-block {
            background: #1a202c;
            border-radius: 8px;
            font-family: 'Fira Code', 'Monaco', monospace;
        }

        .tab-button.active {
            background: #3b82f6;
            color: white;
        }

        .feature-icon {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-50">
    <div id="app">
        <!-- Header -->
        <header class="py-8">
            <div class="container mx-auto px-6">
                <div class="text-center">
                    <h1 class="text-5xl font-bold mb-4 text-gray-900">Vue.js ChatWidget Demo</h1>
                    <p class="text-xl text-gray-600 mb-6">Real-time chat widget with user identification va WebSocket
                        integration</p>

                    <!-- Current User Info -->
                    @auth
                        <div
                            class="inline-flex items-center space-x-3 bg-green-50 rounded-full px-6 py-3 border border-green-200">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <span
                                    class="font-semibold text-sm text-green-700">{{ strtoupper(substr(auth()->user()->display_name ?? 'U', 0, 1)) }}</span>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-gray-900">
                                    {{ auth()->user()->display_name ?? (auth()->user()->name ?? 'User') }}</p>
                                <p class="text-sm text-gray-600">{{ ucfirst(auth()->user()->role?->value ?? 'user') }}
                                    sifatida kirgan</p>
                            </div>
                        </div>
                    @else
                        <div
                            class="inline-flex items-center space-x-3 bg-orange-50 rounded-full px-6 py-3 border border-orange-200">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-gray-900">Guest User</p>
                                <p class="text-sm text-gray-600">Login qiling to'liq funksionallik uchun</p>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-6">
            <!-- Features Grid -->
            <section class="mb-12">
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <div class="demo-card rounded-xl p-6 text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900">User Identification</h3>
                        <p class="text-gray-600 text-sm">Kim yozganligini aniqlab ko'rsatish</p>
                    </div>

                    <div class="demo-card rounded-xl p-6 text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900">Real-time WebSocket</h3>
                        <p class="text-gray-600 text-sm">Laravel Reverb bilan real-time messaging</p>
                    </div>

                    <div class="demo-card rounded-xl p-6 text-center">
                        <div class="feature-icon w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-gray-900">Vue.js Integration</h3>
                        <p class="text-gray-600 text-sm">Vue 3 component va Composition API</p>
                    </div>
                </div>
            </section>

            <!-- Demo Tabs -->
            <section class="demo-card rounded-xl p-8">
                <!-- Tab Navigation -->
                <div class="flex flex-wrap gap-1 mb-8 bg-gray-100 rounded-lg p-1">
                    <button
                        class="tab-button active px-6 py-3 rounded-md font-medium text-sm transition-all text-gray-700 hover:text-gray-900"
                        onclick="showTab('live-demo')">
                        Live Demo
                    </button>
                    <button
                        class="tab-button px-6 py-3 rounded-md font-medium text-sm transition-all text-gray-700 hover:text-gray-900"
                        onclick="showTab('installation')">
                        O'rnatish
                    </button>
                    <button
                        class="tab-button px-6 py-3 rounded-md font-medium text-sm transition-all text-gray-700 hover:text-gray-900"
                        onclick="showTab('code-examples')">
                        Code Examples
                    </button>
                    <button
                        class="tab-button px-6 py-3 rounded-md font-medium text-sm transition-all text-gray-700 hover:text-gray-900"
                        onclick="showTab('features')">
                        Features
                    </button>
                </div>

                <!-- Live Demo Tab -->
                <div id="live-demo" class="tab-content">
                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Demo Description -->
                        <div>
                            <h3 class="text-2xl font-bold mb-4 text-gray-900">Live Chat Demo</h3>
                            <p class="text-gray-600 mb-6">Vue.js widget bilan real-time chat - user identification va
                                WebSocket bilan</p>

                            <div class="space-y-4 mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Kim yozganligini ko'rsatadi</span>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">User role badgelari</span>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Real-time message delivery</span>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-700">Online/offline status</span>
                                </div>
                            </div>

                            @auth
                                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-green-800">✅ Siz autentifikatsiya qilgansiz! Widget to'liq ishlaydi.</p>
                                </div>
                            @else
                                <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                                    <p class="text-orange-800 mb-2">⚠️ Widget uchun autentifikatsiya kerak.</p>
                                    <a href="/login" class="text-blue-600 underline hover:text-blue-800">Login qilish
                                        →</a>
                                </div>
                            @endauth
                        </div>

                        <!-- Demo Controls -->
                        <div>
                            <h4 class="text-lg font-semibold mb-4 text-gray-900">Demo Controls</h4>
                            <div class="space-y-3">
                                <button
                                    class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-3 rounded-lg border border-gray-300 transition-all text-gray-700"
                                    onclick="simulateMessage()">
                                    📩 Support xabarini simulatsiya qilish
                                </button>
                                <button
                                    class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-3 rounded-lg border border-gray-300 transition-all text-gray-700"
                                    onclick="toggleWidget()">
                                    💬 Widget'ni ochish/yopish
                                </button>
                                <button
                                    class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-3 rounded-lg border border-gray-300 transition-all text-gray-700"
                                    onclick="clearMessages()">
                                    🧹 Xabarlarni tozalash
                                </button>
                            </div>

                            <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                                <h5 class="font-semibold mb-2 text-gray-900">Connection Status:</h5>
                                <div id="connection-status" class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm text-gray-700">Ulanmoqda...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Installation Tab -->
                <div id="installation" class="tab-content hidden">
                    <h3 class="text-2xl font-bold mb-6 text-gray-900">O'rnatish qo'llanmasi</h3>

                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">1. HTML sahifada</h4>
                            <div class="code-block p-4 text-green-400 text-sm overflow-x-auto">
                                <pre>&lt;!-- CSS va JS fayllarni ulash --&gt;
&lt;link rel="stylesheet" href="/widget/vue-styles.css"&gt;
&lt;script src="https://unpkg.com/vue@3/dist/vue.global.js"&gt;&lt;/script&gt;
&lt;script src="/widget/vue-sdk.js"&gt;&lt;/script&gt;

&lt;!-- Widget container --&gt;
&lt;div id="chat-widget"&gt;&lt;/div&gt;

&lt;script&gt;
// Widget'ni ishga tushirish
const chatApp = window.ChatWidget.create('chat-widget', {
    apiUrl: '/api/widget',
    position: 'bottom-right',
    primaryColor: '#3B82F6'
});
&lt;/script&gt;</pre>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">2. Vue.js loyihasida</h4>
                            <div class="code-block p-4 text-green-400 text-sm overflow-x-auto">
                                <pre>// main.js da
import ChatWidget from '/widget/vue-sdk.js'
import '/widget/vue-styles.css'

app.use(ChatWidget.install)

// Component'da
&lt;template&gt;
    &lt;ChatWidget :config="chatConfig" /&gt;
&lt;/template&gt;</pre>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">3. Backend sozlamalari</h4>
                            <div class="code-block p-4 text-green-400 text-sm overflow-x-auto">
                                <pre>// routes/api.php
Route::middleware('auth:sanctum')->prefix('widget')->group(function () {
    Route::get('/session', [WidgetController::class, 'session']);
    Route::get('/chats/{chat}/messages', [WidgetController::class, 'getMessages']);
    Route::post('/chats/{chat}/messages', [WidgetController::class, 'sendMessage']);
});

// WebSocket sozlamalari
php artisan reverb:start</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Examples Tab -->
                <div id="code-examples" class="tab-content hidden">
                    <h3 class="text-2xl font-bold mb-6 text-gray-900">Code Examples</h3>

                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">Vue Component Example</h4>
                            <div class="code-block p-4 text-green-400 text-sm overflow-x-auto">
                                <pre>&lt;template&gt;
    &lt;div&gt;
        &lt;h1&gt;Mening sahifam&lt;/h1&gt;
        &lt;ChatWidget 
            ref="chatWidget"
            :config="chatConfig"
            @message-sent="handleMessageSent"
            @message-received="handleMessageReceived"
        /&gt;
    &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
    data() {
        return {
            chatConfig: {
                apiUrl: '/api/widget',
                position: 'bottom-right',
                primaryColor: '#667eea'
            }
        }
    },
    methods: {
        handleMessageSent(message) {
            console.log('Xabar yuborildi:', message);
        },
        handleMessageReceived(message) {
            console.log('Xabar keldi:', message);
        }
    },
    mounted() {
        this.$refs.chatWidget.init(this.chatConfig);
    }
}
&lt;/script&gt;</pre>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">API Integration Example</h4>
                            <div class="code-block p-4 text-green-400 text-sm overflow-x-auto">
                                <pre>// User session olish
const session = await fetch('/api/widget/session', {
    headers: {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
    }
});

const data = await session.json();
console.log('User:', data.user.display_name);
console.log('Chat ID:', data.chat_id);

// Xabar yuborish
const response = await fetch(`/api/widget/chats/${chatId}/messages`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        message: 'Salom, yordam kerak!'
    })
});</pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Tab -->
                <div id="features" class="tab-content hidden">
                    <h3 class="text-2xl font-bold mb-6 text-gray-900">Widget Features</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">👤 User Identification</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>• Kim yozganligini aniq ko'rsatish</li>
                                <li>• Display name va avatar</li>
                                <li>• Role badges (Support, Admin, User)</li>
                                <li>• Online/offline status</li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">⚡ Real-time Features</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>• WebSocket orqali real-time messaging</li>
                                <li>• Typing indicators</li>
                                <li>• Read receipts</li>
                                <li>• Connection status tracking</li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">🎨 Design Options</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>• Multiple themes (modern, classic)</li>
                                <li>• Customizable colors</li>
                                <li>• Responsive design</li>
                                <li>• Position options</li>
                            </ul>
                        </div>

                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-lg font-semibold mb-3 text-gray-900">🔒 Security</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>• Laravel Sanctum authentication</li>
                                <li>• Private channels</li>
                                <li>• CSRF protection</li>
                                <li>• Role-based access</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="py-8 text-center text-gray-500">
            <p>&copy; 2025 ChatWidget Vue.js Demo. WebSocket bilan real-time messaging.</p>
            <p class="mt-2">
                <a href="/widget/VUE_INSTALLATION.md" class="underline hover:text-gray-700" target="_blank">Batafsil
                    o'rnatish qo'llanmasi →</a>
            </p>
        </footer>
    </div>

    <!-- ChatWidget Container -->
    <div id="chat-widget"></div>

    <!-- Scripts -->
    <script src="/widget/echo-config.js"></script>
    <script src="/widget/vue-sdk.js"></script>

    <script>
        const {
            createApp
        } = Vue;

        // Main app
        createApp({
            data() {
                return {
                    connectionStatus: 'Ulanmoqda...'
                }
            },
            methods: {
                updateConnectionStatus(status) {
                    this.connectionStatus = status;
                    const statusEl = document.getElementById('connection-status');
                    if (statusEl) {
                        const dot = statusEl.querySelector('div');
                        const text = statusEl.querySelector('span');

                        if (status === 'connected') {
                            dot.className = 'w-3 h-3 bg-green-500 rounded-full';
                            text.textContent = 'Ulangan';
                        } else if (status === 'disconnected') {
                            dot.className = 'w-3 h-3 bg-red-500 rounded-full';
                            text.textContent = 'Uzilgan';
                        } else {
                            dot.className = 'w-3 h-3 bg-yellow-500 rounded-full animate-pulse';
                            text.textContent = 'Ulanmoqda...';
                        }
                    }
                }
            },
            mounted() {
                // Initialize Echo
                window.initChatWidgetEcho({
                    broadcaster: 'reverb',
                    key: document.querySelector('meta[name="reverb-app-key"]')?.content || 'app-key',
                    wsHost: '{{ request()->getHost() }}',
                    wsPort: {{ config('broadcasting.connections.reverb.port', 8080) }},
                    wssPort: {{ config('broadcasting.connections.reverb.port', 8080) }},
                    forceTLS: false,
                    enabledTransports: ['ws', 'wss']
                });

                // Initialize ChatWidget
                @auth
                const chatApp = window.ChatWidget.create('chat-widget', {
                    apiUrl: '/api/widget',
                    position: 'bottom-right',
                    primaryColor: '#667eea',
                    theme: 'modern'
                });
            @endauth

            // Connection status tracking
            if (window.Echo) {
                window.Echo.connector.socket.on('connect', () => {
                    this.updateConnectionStatus('connected');
                });

                window.Echo.connector.socket.on('disconnect', () => {
                    this.updateConnectionStatus('disconnected');
                });
            }
        }
        }).mount('#app');

        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Show selected tab
            document.getElementById(tabName).classList.remove('hidden');

            // Update button states
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Demo controls
        function simulateMessage() {
            // Simulate received message
            if (window.chatWidgetApp && window.chatWidgetApp.$refs.widget) {
                const message = {
                    id: Date.now(),
                    content: 'Salom! Sizga qanday yordam bera olaman?',
                    sender_id: 999,
                    sender_name: 'Support Agent',
                    sender_role: 'support',
                    from_operator: true,
                    created_at: new Date().toISOString(),
                    user: {
                        display_name: 'Support Agent',
                        avatar_url: null,
                        role: 'support'
                    }
                };

                window.chatWidgetApp.$refs.widget.handleNewMessage(message);
            }
        }

        function toggleWidget() {
            if (window.chatWidgetApp && window.chatWidgetApp.$refs.widget) {
                window.chatWidgetApp.$refs.widget.toggleChat();
            }
        }

        function clearMessages() {
            if (window.chatWidgetApp && window.chatWidgetApp.$refs.widget) {
                window.chatWidgetApp.$refs.widget.messages = [];
            }
        }
    </script>
</body>

</html>
