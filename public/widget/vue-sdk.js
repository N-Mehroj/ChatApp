/**
 * ChatWidget Vue.js SDK
 * Real-time chat widget with user authentication and WebSocket support
 * 
 * @version 1.0.0
 * @author ChatApp Team
 */

(function () {
    'use strict';

    // Vue.js ChatWidget Component
    const ChatWidgetComponent = {
        name: 'ChatWidget',
        template: `
            <div class="chat-widget" :class="widgetClasses" v-if="isVisible">
                <!-- Widget Button -->
                <div 
                    v-if="!isOpen" 
                    class="widget-button" 
                    :class="buttonClasses"
                    @click="toggleChat"
                >
                    <div class="button-icon">
                        <svg v-if="!hasUnreadMessages" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <div v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</div>
                    <div v-if="isConnecting" class="connecting-indicator">
                        <div class="spinner"></div>
                    </div>
                </div>

                <!-- Chat Window -->
                <div 
                    v-if="isOpen" 
                    class="chat-window" 
                    :class="windowClasses"
                >
                    <!-- Header -->
                    <div class="chat-header">
                        <div class="header-content">
                            <div class="operator-info">
                                <div class="operator-avatar">
                                    <img v-if="operatorInfo.avatar" :src="operatorInfo.avatar" :alt="operatorInfo.name" />
                                    <div v-else class="avatar-placeholder">
                                        {{ operatorInfo.name ? operatorInfo.name.charAt(0) : 'S' }}
                                    </div>
                                </div>
                                <div class="operator-details">
                                    <div class="operator-name">{{ operatorInfo.name || 'Support' }}</div>
                                    <div class="operator-status" :class="{ online: isOnline }">
                                        {{ connectionStatus }}
                                    </div>
                                </div>
                            </div>
                            <div class="header-actions">
                                <button class="minimize-btn" @click="toggleChat" title="Yopish">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div class="messages-container" ref="messagesContainer">
                        <!-- Welcome Message -->
                        <div v-if="messages.length === 0" class="welcome-message">
                            <div class="welcome-avatar">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                            </div>
                            <div class="welcome-text">
                                <h4>Salom! 👋</h4>
                                <p>Sizga qanday yordam bera olaman?</p>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div 
                            v-for="message in messages" 
                            :key="message.id" 
                            class="message" 
                            :class="{ 
                                'message-sent': message.user_id === currentUser?.id,
                                'message-received': message.user_id !== currentUser?.id,
                                'message-operator': message.from_operator
                            }"
                        >
                            <!-- Message Avatar (for received messages) -->
                            <div v-if="message.user_id !== currentUser?.id" class="message-avatar">
                                <img v-if="message.user?.avatar_url" :src="message.user.avatar_url" :alt="message.user.display_name" />
                                <div v-else class="avatar-placeholder">
                                    {{ (message.user?.display_name || 'S').charAt(0) }}
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div class="message-content">
                                <!-- Sender Info (for received messages) -->
                                <div v-if="message.user_id !== currentUser?.id" class="message-sender">
                                    <span class="sender-name">{{ message.user?.display_name || 'Support' }}</span>
                                    <span v-if="message.from_operator" class="sender-badge operator">Support</span>
                                    <span v-else-if="message.user?.role" class="sender-badge" :class="message.user.role">
                                        {{ message.user.role }}
                                    </span>
                                </div>

                                <!-- Message Bubble -->
                                <div class="message-bubble" :class="{ 'has-sender': message.user_id !== currentUser?.id }">
                                    <div class="message-text">{{ message.message }}</div>
                                    <div class="message-time">
                                        {{ formatTime(message.created_at) }}
                                        <span v-if="message.user_id === currentUser?.id && message.read_at" class="read-indicator">
                                            ✓✓
                                        </span>
                                        <span v-else-if="message.user_id === currentUser?.id" class="sent-indicator">
                                            ✓
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Typing Indicator -->
                        <div v-if="isTyping" class="typing-indicator">
                            <div class="typing-avatar">
                                <div class="avatar-placeholder">S</div>
                            </div>
                            <div class="typing-content">
                                <div class="typing-bubble">
                                    <div class="typing-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                                <div class="typing-text">{{ typingUser }} yozmoqda...</div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="input-container">
                        <!-- User Info (if logged in) -->
                        <div v-if="currentUser" class="current-user-info">
                            <div class="user-avatar-small">
                                <img v-if="currentUser.avatar_url" :src="currentUser.avatar_url" :alt="currentUser.display_name" />
                                <div v-else class="avatar-placeholder-small">
                                    {{ (currentUser.display_name || 'U').charAt(0) }}
                                </div>
                            </div>
                            <span class="user-name-small">{{ currentUser.display_name }}</span>
                        </div>

                        <!-- Message Input -->
                        <div class="input-wrapper">
                            <textarea
                                v-model="newMessage"
                                @keydown="handleKeydown"
                                @input="handleTyping"
                                :placeholder="inputPlaceholder"
                                :disabled="isSending || !canSendMessage"
                                class="message-input"
                                rows="1"
                                ref="messageInput"
                            ></textarea>
                            
                            <button 
                                @click="sendMessage"
                                :disabled="!canSend"
                                class="send-button"
                                :class="{ active: canSend }"
                            >
                                <div v-if="isSending" class="sending-spinner"></div>
                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"/>
                                    <polygon points="22,2 15,22 11,13 2,9 22,2"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Connection Status -->
                        <div v-if="!isOnline" class="connection-warning">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                            Ulanish yo'q
                        </div>
                    </div>
                </div>
            </div>
        `,

        data() {
            return {
                isOpen: false,
                isVisible: true,
                isConnecting: false,
                isOnline: false,
                isSending: false,
                isTyping: false,

                messages: [],
                newMessage: '',
                unreadCount: 0,

                currentUser: null,
                operatorInfo: {
                    name: 'Support Team',
                    avatar: null
                },
                typingUser: 'Support',

                echo: null,
                chatId: null,

                // Config
                config: {
                    apiUrl: '/api/widget',
                    wsUrl: null,
                    position: 'bottom-right',
                    primaryColor: '#3B82F6',
                    theme: 'modern'
                }
            };
        },

        computed: {
            widgetClasses() {
                return [
                    'position-' + this.config.position,
                    'theme-' + this.config.theme
                ];
            },

            buttonClasses() {
                return [
                    {
                        'has-unread': this.hasUnreadMessages,
                        'connecting': this.isConnecting
                    }
                ];
            },

            windowClasses() {
                return [
                    'position-' + this.config.position
                ];
            },

            hasUnreadMessages() {
                return this.unreadCount > 0;
            },

            connectionStatus() {
                if (this.isConnecting) return 'Ulanmoqda...';
                return this.isOnline ? 'Online' : 'Offline';
            },

            inputPlaceholder() {
                if (!this.canSendMessage) return 'Kirish talab qilinadi...';
                if (!this.isOnline) return 'Ulanish yo\'q...';
                return 'Xabar yozing...';
            },

            canSendMessage() {
                return this.isOnline && (this.currentUser || this.config.allowGuests);
            },

            canSend() {
                return this.canSendMessage &&
                    this.newMessage.trim().length > 0 &&
                    !this.isSending;
            }
        },

        methods: {
            async init(options = {}) {
                // Merge config
                Object.assign(this.config, options);

                try {
                    // Initialize user session
                    await this.initializeSession();

                    // Setup WebSocket connection
                    await this.setupWebSocket();

                    // Load initial messages
                    await this.loadMessages();

                    console.log('💬 ChatWidget Vue.js initialized successfully');
                } catch (error) {
                    console.error('❌ ChatWidget initialization failed:', error);
                }
            },

            async initializeSession() {
                try {
                    const response = await fetch(this.config.apiUrl + '/session', {
                        method: 'GET',
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.currentUser = data.user;
                        this.chatId = data.chat_id;
                        this.operatorInfo = data.operator || this.operatorInfo;

                        console.log('👤 User session initialized:', this.currentUser?.display_name || 'Guest');
                    }
                } catch (error) {
                    console.error('❌ Session initialization failed:', error);
                }
            },

            async setupWebSocket() {
                if (!window.Echo) {
                    console.warn('⚠️ Laravel Echo not available');
                    return;
                }

                this.isConnecting = true;

                try {
                    // Listen for connection events
                    window.Echo.connector.socket.on('connect', () => {
                        this.isOnline = true;
                        this.isConnecting = false;
                        console.log('✅ WebSocket connected');
                    });

                    window.Echo.connector.socket.on('disconnect', () => {
                        this.isOnline = false;
                        console.log('❌ WebSocket disconnected');
                    });

                    // Subscribe to chat channel
                    if (this.chatId) {
                        this.echo = window.Echo.private(`chat.${this.chatId}`)
                            .listen('.message.sent', (data) => {
                                this.handleNewMessage(data.message);
                            })
                            .listen('.message.read', (data) => {
                                this.markMessageAsRead(data.message_id);
                            });

                        console.log(`🔊 Subscribed to chat.${this.chatId}`);
                    }

                } catch (error) {
                    this.isConnecting = false;
                    console.error('❌ WebSocket setup failed:', error);
                }
            },

            async loadMessages() {
                if (!this.chatId) return;

                try {
                    const response = await fetch(this.config.apiUrl + `/chats/${this.chatId}/messages`, {
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.messages = data.messages || [];
                        this.$nextTick(() => this.scrollToBottom());
                    }
                } catch (error) {
                    console.error('❌ Failed to load messages:', error);
                }
            },

            toggleChat() {
                this.isOpen = !this.isOpen;

                if (this.isOpen) {
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (this.$refs.messageInput) {
                            this.$refs.messageInput.focus();
                        }
                    });
                    this.markAllAsRead();
                }
            },

            async sendMessage() {
                if (!this.canSend) return;

                const message = this.newMessage.trim();
                this.newMessage = '';
                this.isSending = true;

                try {
                    const response = await fetch(this.config.apiUrl + `/chats/${this.chatId}/messages`, {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ message })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Message will be added via WebSocket
                        console.log('✅ Message sent successfully');
                    } else {
                        throw new Error('Failed to send message');
                    }
                } catch (error) {
                    console.error('❌ Failed to send message:', error);
                    this.newMessage = message; // Restore message
                } finally {
                    this.isSending = false;
                }
            },

            handleNewMessage(message) {
                // Check for duplicates
                const exists = this.messages.find(m => m.id === message.id);
                if (exists) return;

                this.messages.push(message);
                this.$nextTick(() => this.scrollToBottom());

                // Update unread count if widget is closed and message is not from current user
                if (!this.isOpen && message.user_id !== this.currentUser?.id) {
                    this.unreadCount++;
                }

                // Show browser notification
                if (message.user_id !== this.currentUser?.id) {
                    this.showNotification(message);
                }
            },

            markMessageAsRead(messageId) {
                const message = this.messages.find(m => m.id === messageId);
                if (message) {
                    message.read_at = new Date().toISOString();
                }
            },

            markAllAsRead() {
                this.unreadCount = 0;
                // API call to mark messages as read could be added here
            },

            handleKeydown(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    this.sendMessage();
                }
            },

            handleTyping() {
                // Typing indicator logic can be implemented here
            },

            scrollToBottom() {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },

            formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString('uz-UZ', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            showNotification(message) {
                if (Notification.permission === 'granted') {
                    const senderName = message.user?.display_name || 'Support';
                    new Notification(`${senderName}dan xabar`, {
                        body: message.message,
                        icon: '/favicon.ico'
                    });
                }
            }
        },

        created() {
            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },

        beforeUnmount() {
            // Cleanup WebSocket connections
            if (this.echo) {
                this.echo.stopListening('.message.sent');
                this.echo.stopListening('.message.read');
            }
        }
    };

    // Global ChatWidget object for Vue.js
    window.ChatWidget = {
        Vue: null,
        app: null,

        install(vue, options = {}) {
            if (this.Vue) {
                console.warn('ChatWidget already installed');
                return;
            }

            this.Vue = vue;

            // Register global component
            vue.component('ChatWidget', ChatWidgetComponent);

            console.log('🎯 ChatWidget Vue.js plugin installed');
        },

        create(elementId = 'chat-widget', options = {}) {
            if (!this.Vue) {
                console.error('❌ Vue.js not found. Please install Vue.js first.');
                return;
            }

            const element = document.getElementById(elementId);
            if (!element) {
                console.error(`❌ Element with id "${elementId}" not found`);
                return;
            }

            // Create Vue app with ChatWidget component
            this.app = this.Vue.createApp({
                components: {
                    ChatWidget: ChatWidgetComponent
                },
                template: '<ChatWidget ref="widget" />',
                mounted() {
                    this.$refs.widget.init(options);
                }
            });

            this.app.mount(element);

            console.log('🚀 ChatWidget Vue.js app created and mounted');
            return this.app;
        },

        destroy() {
            if (this.app) {
                this.app.unmount();
                this.app = null;
            }
        }
    };

})();