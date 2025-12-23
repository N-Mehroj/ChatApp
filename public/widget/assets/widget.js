// Chat Widget Standalone Script
(function () {
    'use strict';

    // Check if Vue is available globally, if not load it
    if (typeof Vue === 'undefined') {
        console.warn('Vue.js is required for ChatWidget. Loading from CDN...');
        loadVue().then(() => {
            initializeWidget();
        });
    } else {
        initializeWidget();
    }

    function loadVue() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/vue@3/dist/vue.global.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function initializeWidget() {
        // Define the ChatWidget component
        const { createApp, ref, onMounted, onUnmounted, nextTick } = Vue;

        const ChatWidgetComponent = {
            props: {
                apiKey: String,
                apiUrl: { type: String, default: '/api/widget' },
                primaryColor: { type: String, default: '#3B82F6' }
            },

            setup(props) {
                const isOpen = ref(false);
                const messages = ref([]);
                const newMessage = ref('');
                const sending = ref(false);
                const unreadCount = ref(0);
                const isTyping = ref(false);
                const messagesContainer = ref(null);
                const supportAgent = ref({ name: 'Support Team' });
                const sessionId = ref(null);
                const lastTypingTime = ref(0);
                const typingTimeout = ref(null);

                const toggleChat = () => {
                    isOpen.value = !isOpen.value;
                    if (isOpen.value) {
                        unreadCount.value = 0;
                        nextTick(() => {
                            scrollToBottom();
                            initializeSession();
                        });
                    }
                };

                const initializeSession = async () => {
                    try {
                        const response = await fetch(`${props.apiUrl}/session`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                api_key: props.apiKey
                            })
                        });

                        const data = await response.json();
                        sessionId.value = data.session_id;
                        messages.value = data.messages || [];
                        supportAgent.value = data.agent || { name: 'Support Team' };

                        setTimeout(() => scrollToBottom(), 100);
                    } catch (error) {
                        console.error('Failed to initialize session:', error);
                    }
                };

                const sendMessage = async () => {
                    if (!newMessage.value.trim() || sending.value) return;

                    const message = newMessage.value.trim();
                    newMessage.value = '';
                    sending.value = true;

                    const userMessage = {
                        id: Date.now(),
                        message: message,
                        is_from_user: true,
                        created_at: new Date().toISOString()
                    };

                    messages.value.push(userMessage);
                    scrollToBottom();

                    try {
                        const response = await fetch(`${props.apiUrl}/message`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                api_key: props.apiKey,
                                session_id: sessionId.value,
                                message: message
                            })
                        });

                        const data = await response.json();
                        const messageIndex = messages.value.findIndex(m => m.id === userMessage.id);
                        if (messageIndex !== -1) {
                            messages.value[messageIndex] = data.message;
                        }

                    } catch (error) {
                        console.error('Failed to send message:', error);
                    } finally {
                        sending.value = false;
                    }
                };

                const handleTyping = () => {
                    const now = Date.now();
                    lastTypingTime.value = now;

                    if (typingTimeout.value) {
                        clearTimeout(typingTimeout.value);
                    }

                    if (sessionId.value) {
                        fetch(`${props.apiUrl}/typing`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                api_key: props.apiKey,
                                session_id: sessionId.value,
                                typing: true
                            })
                        }).catch(console.error);
                    }

                    typingTimeout.value = setTimeout(() => {
                        if (sessionId.value) {
                            fetch(`${props.apiUrl}/typing`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    api_key: props.apiKey,
                                    session_id: sessionId.value,
                                    typing: false
                                })
                            }).catch(console.error);
                        }
                    }, 2000);
                };

                const formatTime = (timestamp) => {
                    const date = new Date(timestamp);
                    return date.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                };

                const scrollToBottom = () => {
                    if (messagesContainer.value) {
                        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
                    }
                };

                const handleScroll = () => {
                    // Mark as read functionality
                };

                onUnmounted(() => {
                    if (typingTimeout.value) {
                        clearTimeout(typingTimeout.value);
                    }
                });

                return {
                    isOpen,
                    messages,
                    newMessage,
                    sending,
                    unreadCount,
                    isTyping,
                    messagesContainer,
                    supportAgent,
                    toggleChat,
                    sendMessage,
                    handleTyping,
                    formatTime,
                    handleScroll
                };
            },

            template: `
        <div class="chat-widget" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
          <!-- Chat Button -->
          <div
            v-if="!isOpen"
            @click="toggleChat"
            class="chat-button"
            style="position: fixed; bottom: 24px; right: 24px; z-index: 50; width: 64px; height: 64px; background-color: #3B82F6; border-radius: 50%; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;"
          >
            <svg style="width: 32px; height: 32px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <div
              v-if="unreadCount > 0"
              style="position: absolute; top: -8px; right: -8px; background-color: #EF4444; color: white; font-size: 12px; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-weight: bold;"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </div>
          </div>

          <!-- Chat Modal -->
          <div
            v-if="isOpen"
            style="position: fixed; bottom: 24px; right: 24px; z-index: 50; width: 320px; height: 384px; background: white; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); display: flex; flex-direction: column; overflow: hidden;"
          >
            <!-- Header -->
            <div style="background-color: #3B82F6; color: white; padding: 16px; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center;">
                <div style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                  <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                  </svg>
                </div>
                <div>
                  <h3 style="font-weight: 500; margin: 0;">Support</h3>
                  <p style="font-size: 12px; color: rgba(255, 255, 255, 0.7); margin: 0;">{{ supportAgent.name || 'Support Team' }}</p>
                </div>
              </div>
              <button @click="toggleChat" style="color: rgba(255, 255, 255, 0.8); background: none; border: none; cursor: pointer; transition: color 0.3s;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Messages Area -->
            <div
              ref="messagesContainer"
              @scroll="handleScroll"
              style="flex: 1; overflow-y: auto; padding: 12px; background: #F9FAFB;"
            >
              <!-- Welcome Message -->
              <div v-if="messages.length === 0" style="text-align: center; padding: 24px 0;">
                <div style="width: 48px; height: 48px; margin: 0 auto 12px; background: #DBEAFE; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                  <svg style="width: 24px; height: 24px; color: #3B82F6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
                <p style="color: #6B7280; font-size: 14px; margin: 0 0 8px;">Welcome to Support!</p>
                <p style="color: #9CA3AF; font-size: 12px; margin: 0;">Send us a message and we'll get back to you shortly.</p>
              </div>

              <!-- Messages -->
              <div style="display: flex; flex-direction: column; gap: 12px;">
                <div
                  v-for="message in messages"
                  :key="message.id"
                  style="display: flex;"
                  :style="message.is_from_user ? 'justify-content: flex-end' : 'justify-content: flex-start'"
                >
                  <!-- Support Avatar -->
                  <div
                    v-if="!message.is_from_user"
                    style="width: 24px; height: 24px; background: #3B82F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; margin-right: 8px; margin-top: 4px; flex-shrink: 0;"
                  >
                    {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
                  </div>

                  <!-- Message Bubble -->
                  <div
                    style="max-width: 240px; padding: 12px; border-radius: 8px; font-size: 14px;"
                    :style="message.is_from_user 
                      ? 'background: #3B82F6; color: white; border-bottom-right-radius: 4px;' 
                      : 'background: white; border: 1px solid #E5E7EB; color: #1F2937; border-bottom-left-radius: 4px;'"
                  >
                    <p style="margin: 0;">{{ message.message }}</p>
                    <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                      <span
                        style="font-size: 12px;"
                        :style="message.is_from_user ? 'color: rgba(255, 255, 255, 0.7);' : 'color: #6B7280;'"
                      >
                        {{ formatTime(message.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Typing Indicator -->
              <div v-if="isTyping" style="display: flex; align-items: center; margin-top: 12px;">
                <div style="width: 24px; height: 24px; background: #3B82F6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; margin-right: 8px; flex-shrink: 0;">
                  {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
                </div>
                <div style="background: white; border: 1px solid #E5E7EB; border-radius: 8px; border-bottom-left-radius: 4px; padding: 12px;">
                  <div style="display: flex; gap: 4px;">
                    <div style="width: 8px; height: 8px; background: #6B7280; border-radius: 50%; animation: bounce 1.4s infinite;"></div>
                    <div style="width: 8px; height: 8px; background: #6B7280; border-radius: 50%; animation: bounce 1.4s infinite 0.15s;"></div>
                    <div style="width: 8px; height: 8px; background: #6B7280; border-radius: 50%; animation: bounce 1.4s infinite 0.3s;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Input Area -->
            <div style="padding: 12px; background: white; border-top: 1px solid #E5E7EB;">
              <form @submit.prevent="sendMessage" style="display: flex; align-items: center; gap: 8px;">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Type your message..."
                  style="flex: 1; padding: 12px; border: 1px solid #D1D5DB; border-radius: 24px; font-size: 14px; outline: none; transition: border-color 0.3s;"
                  :disabled="sending"
                  @keydown="handleTyping"
                />
                <button
                  type="submit"
                  :disabled="!newMessage.trim() || sending"
                  style="width: 32px; height: 32px; background: #3B82F6; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s; cursor: pointer;"
                  :style="(!newMessage.trim() || sending) ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                >
                  <svg
                    v-if="!sending"
                    style="width: 16px; height: 16px; color: white;"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                  </svg>
                  <div
                    v-else
                    style="width: 12px; height: 12px; border: 2px solid rgba(255, 255, 255, 0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite;"
                  ></div>
                </button>
              </form>
            </div>
          </div>
          
          <style>
            @keyframes bounce {
              0%, 80%, 100% {
                transform: translateY(0);
              }
              40% {
                transform: translateY(-6px);
              }
            }
            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }
          </style>
        </div>
      `
        };

        // Make component available globally
        window.ChatWidgetComponent = ChatWidgetComponent;

        // If there's already a container, mount the widget
        const container = document.getElementById('chat-widget-container');
        if (container) {
            const app = createApp({
                template: `<ChatWidgetComponent :api-key="apiKey" :api-url="apiUrl" :primary-color="primaryColor" />`,
                data() {
                    return {
                        apiKey: window.ChatWidget?.config?.apiKey || 'demo_key',
                        apiUrl: window.ChatWidget?.config?.apiUrl || '/api/widget',
                        primaryColor: window.ChatWidget?.config?.primaryColor || '#3B82F6'
                    };
                }
            });

            app.component('ChatWidgetComponent', ChatWidgetComponent);
            app.mount('#chat-widget-container');
        }
    }
})();