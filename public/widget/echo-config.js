/**
 * Laravel Echo Configuration for ChatWidget
 * WebSocket integration with user identification
 */

// Echo initialization
window.initChatWidgetEcho = function (config = {}) {
    const defaultConfig = {
        broadcaster: 'reverb',
        key: document.querySelector('meta[name="reverb-app-key"]')?.content || 'app-key',
        wsHost: window.location.hostname,
        wsPort: 8080,
        wssPort: 8080,
        forceTLS: location.protocol === 'https:',
        enabledTransports: ['ws', 'wss'],
        // Authentication
        auth: {
            headers: {
                'Authorization': 'Bearer ' + (localStorage.getItem('token') || ''),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }
    };

    // Merge with provided config
    const echoConfig = Object.assign({}, defaultConfig, config);

    // Initialize Echo
    if (typeof Echo !== 'undefined') {
        window.Echo = new Echo(echoConfig);

        console.log('💬 ChatWidget Echo initialized');

        // Connection event listeners
        window.Echo.connector.socket.on('connect', () => {
            console.log('✅ ChatWidget WebSocket connected');
        });

        window.Echo.connector.socket.on('disconnect', (reason) => {
            console.warn('❌ ChatWidget WebSocket disconnected:', reason);
        });

        window.Echo.connector.socket.on('error', (error) => {
            console.error('💥 ChatWidget WebSocket error:', error);
        });

        window.Echo.connector.socket.on('reconnect', (attemptNumber) => {
            console.log('🔄 ChatWidget WebSocket reconnected, attempt:', attemptNumber);
        });

        return window.Echo;
    } else {
        console.error('❌ Laravel Echo not found. Please include Laravel Echo library.');
        return null;
    }
};

// Auto-initialize if Echo is available
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Echo !== 'undefined' && !window.Echo) {
        window.initChatWidgetEcho();
    }
});

// WebSocket utility functions for ChatWidget
window.ChatWidgetWebSocket = {
    // Subscribe to chat channel
    subscribeToChat: function (chatId, callbacks = {}) {
        if (!window.Echo) {
            console.error('❌ Echo not initialized');
            return null;
        }

        console.log(`🔊 Subscribing to chat.${chatId}`);

        return window.Echo.private(`chat.${chatId}`)
            .listen('.message.sent', (data) => {
                console.log('📩 New message received:', data);
                if (callbacks.onMessage) {
                    callbacks.onMessage(data.message);
                }
            })
            .listen('.message.read', (data) => {
                console.log('👀 Message read:', data);
                if (callbacks.onMessageRead) {
                    callbacks.onMessageRead(data);
                }
            })
            .listen('.user.typing', (data) => {
                console.log('⌨️ User typing:', data);
                if (callbacks.onTyping) {
                    callbacks.onTyping(data);
                }
            })
            .subscribed(() => {
                console.log(`✅ Successfully subscribed to chat.${chatId}`);
                if (callbacks.onSubscribed) {
                    callbacks.onSubscribed();
                }
            })
            .error((error) => {
                console.error(`❌ Subscription error for chat.${chatId}:`, error);
                if (callbacks.onError) {
                    callbacks.onError(error);
                }
            });
    },

    // Subscribe to user status channel
    subscribeToUserStatus: function (userId, callbacks = {}) {
        if (!window.Echo) {
            console.error('❌ Echo not initialized');
            return null;
        }

        console.log(`👤 Subscribing to user-status.${userId}`);

        return window.Echo.private(`user-status.${userId}`)
            .listen('.user.online', (data) => {
                console.log('🟢 User online:', data);
                if (callbacks.onOnline) {
                    callbacks.onOnline(data);
                }
            })
            .listen('.user.offline', (data) => {
                console.log('🔴 User offline:', data);
                if (callbacks.onOffline) {
                    callbacks.onOffline(data);
                }
            })
            .subscribed(() => {
                console.log(`✅ Successfully subscribed to user-status.${userId}`);
            })
            .error((error) => {
                console.error(`❌ User status subscription error:`, error);
            });
    },

    // Subscribe to operator status
    subscribeToOperatorStatus: function (callbacks = {}) {
        if (!window.Echo) {
            console.error('❌ Echo not initialized');
            return null;
        }

        console.log('🎯 Subscribing to operator status');

        return window.Echo.channel('operators')
            .listen('.operator.online', (data) => {
                console.log('🟢 Operator online:', data);
                if (callbacks.onOperatorOnline) {
                    callbacks.onOperatorOnline(data);
                }
            })
            .listen('.operator.offline', (data) => {
                console.log('🔴 Operator offline:', data);
                if (callbacks.onOperatorOffline) {
                    callbacks.onOperatorOffline(data);
                }
            });
    },

    // Leave channel
    leave: function (channelName) {
        if (window.Echo && window.Echo.connector.channels[channelName]) {
            window.Echo.leave(channelName);
            console.log(`👋 Left channel: ${channelName}`);
        }
    },

    // Check connection status
    isConnected: function () {
        return window.Echo &&
            window.Echo.connector.socket &&
            window.Echo.connector.socket.connected;
    },

    // Get connection info
    getConnectionInfo: function () {
        if (!window.Echo) {
            return { connected: false, error: 'Echo not initialized' };
        }

        const socket = window.Echo.connector.socket;
        return {
            connected: socket ? socket.connected : false,
            id: socket ? socket.id : null,
            transport: socket ? socket.io.engine.transport.name : null,
            channels: Object.keys(window.Echo.connector.channels || {})
        };
    }
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initChatWidgetEcho: window.initChatWidgetEcho,
        ChatWidgetWebSocket: window.ChatWidgetWebSocket
    };
}