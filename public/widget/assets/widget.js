// Chat Widget Standalone Script
(function () {
  'use strict';

  // Check if Vue is available globally, if not load it
  if (typeof Vue === 'undefined') {
    console.warn('Vue.js is required for ChatWidget. Loading from CDN...');
    loadVue().then(() => {
      // Give Vue a moment to fully initialize
      setTimeout(() => {
        initializeWidget();
        signalReady();
      }, 100);
    }).catch(err => {
      console.error('Failed to load Vue:', err);
    });
  } else {
    // Even if Vue exists, wait a bit to ensure it's fully ready
    setTimeout(() => {
      initializeWidget();
      signalReady();
    }, 50);
  }

  function signalReady() {
    // Signal to the SDK that the widget is ready
    if (window.ChatWidget && typeof window.ChatWidget.onWidgetReady === 'function') {
      window.ChatWidget.onWidgetReady();
    }
  }

  function loadVue() {
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://unpkg.com/vue@3/dist/vue.global.js';
      script.onload = () => {
        // Wait for Vue to be properly attached to window
        const checkVue = () => {
          if (typeof window.Vue !== 'undefined') {
            resolve();
          } else {
            setTimeout(checkVue, 50);
          }
        };
        checkVue();
      };
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  function initializeWidget() {
    // Ensure Vue is available
    if (typeof Vue === 'undefined') {
      console.error('ChatWidget: Vue is still not loaded');
      return;
    }

    // Define the ChatWidget component
    const { createApp, ref, onMounted, onUnmounted, nextTick } = Vue;

    const ChatWidgetComponent = {
      props: {
        apiKey: String,
        apiUrl: { type: String, default: '/api/widget' },
        primaryColor: { type: String, default: '#3B82F6' },
        animations: { type: Object, default: () => ({}) },
        design: { type: Object, default: () => ({}) }
      },

      setup(props) {
        // Merge default configurations with provided ones
        const animationConfig = {
          enabled: true,
          openSpeed: 300,
          bounceIntensity: 'normal',
          typingAnimation: true,
          fadeIn: true,
          slideIn: true,
          ...props.animations
        };

        const designConfig = {
          theme: 'modern',
          borderRadius: 'normal',
          shadow: 'normal',
          buttonStyle: 'floating',
          chatWidth: 320,
          chatHeight: 500,
          fontSize: 'normal',
          avatarStyle: 'circle',
          messageStyle: 'bubbles',
          ...props.design
        };
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

        // Check if running on file:// protocol (demo mode)
        const isFileProtocol = window.location.protocol === 'file:';
        const isDemoMode = isFileProtocol || props.apiKey === 'demo_key';

        // Style computation functions
        const getBorderRadius = () => {
          const radii = {
            none: '0px',
            small: '4px',
            normal: '8px',
            large: '12px',
            round: '50%'
          };
          return radii[designConfig.borderRadius] || '8px';
        };

        const getBoxShadow = () => {
          const shadows = {
            none: 'none',
            subtle: '0 2px 8px rgba(0, 0, 0, 0.08)',
            normal: '0 4px 12px rgba(0, 0, 0, 0.15)',
            strong: '0 8px 30px rgba(0, 0, 0, 0.25)'
          };
          return shadows[designConfig.shadow] || '0 4px 12px rgba(0, 0, 0, 0.15)';
        };

        const getFontSize = () => {
          const sizes = {
            small: '13px',
            normal: '14px',
            large: '16px'
          };
          return sizes[designConfig.fontSize] || '14px';
        };

        const getBounceIntensity = () => {
          const intensities = {
            none: '0px',
            subtle: '-3px',
            normal: '-6px',
            strong: '-10px'
          };
          return intensities[animationConfig.bounceIntensity] || '-6px';
        };

        const getAnimationDuration = () => {
          return animationConfig.enabled ? `${animationConfig.openSpeed}ms` : '0ms';
        };

        const getChatDimensions = () => ({
          width: `${designConfig.chatWidth}px`,
          height: `${designConfig.chatHeight}px`
        });

        const getButtonStyle = () => {
          const baseStyle = {
            position: 'fixed',
            bottom: '24px',
            right: '24px',
            zIndex: '999999',
            width: '64px',
            height: '64px',
            backgroundColor: props.primaryColor,
            border: 'none',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'white',
            fontSize: '24px',
            boxShadow: getBoxShadow()
          };

          if (designConfig.buttonStyle === 'floating') {
            baseStyle.borderRadius = '50%';
            if (animationConfig.enabled) {
              baseStyle.transition = 'all ' + getAnimationDuration() + ' ease';
            }
          } else if (designConfig.buttonStyle === 'minimal') {
            baseStyle.borderRadius = getBorderRadius();
            baseStyle.boxShadow = 'none';
            baseStyle.border = `2px solid ${props.primaryColor}`;
            baseStyle.backgroundColor = 'white';
            baseStyle.color = props.primaryColor;
          } else if (designConfig.buttonStyle === 'fixed') {
            baseStyle.borderRadius = getBorderRadius();
          }

          return baseStyle;
        };

        const getMessageStyle = (isFromUser) => {
          const baseStyle = {
            maxWidth: '240px',
            padding: '12px',
            fontSize: getFontSize(),
            wordWrap: 'break-word'
          };

          if (designConfig.messageStyle === 'bubbles') {
            baseStyle.borderRadius = getBorderRadius();
            if (isFromUser) {
              baseStyle.backgroundColor = props.primaryColor;
              baseStyle.color = 'white';
              baseStyle.borderBottomRightRadius = '4px';
            } else {
              baseStyle.backgroundColor = 'white';
              baseStyle.border = '1px solid #E5E7EB';
              baseStyle.color = '#1F2937';
              baseStyle.borderBottomLeftRadius = '4px';
            }
          } else if (designConfig.messageStyle === 'flat') {
            baseStyle.borderRadius = '0px';
            baseStyle.backgroundColor = isFromUser ? props.primaryColor : '#F3F4F6';
            baseStyle.color = isFromUser ? 'white' : '#1F2937';
          } else if (designConfig.messageStyle === 'outlined') {
            baseStyle.borderRadius = getBorderRadius();
            baseStyle.border = `2px solid ${isFromUser ? props.primaryColor : '#E5E7EB'}`;
            baseStyle.backgroundColor = isFromUser ? props.primaryColor : 'white';
            baseStyle.color = isFromUser ? 'white' : '#1F2937';
          }

          return baseStyle;
        };

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
          if (isDemoMode) {
            // Demo mode - simulate a session
            sessionId.value = 'demo_session_' + Date.now();
            messages.value = [
              {
                id: 1,
                message: 'Hello! This is a demo of the chat widget. In demo mode, messages won\'t be saved.',
                is_from_user: false,
                created_at: new Date().toISOString()
              }
            ];
            supportAgent.value = { name: 'Demo Agent' };
            setTimeout(() => scrollToBottom(), 100);
            return;
          }

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
            // Fallback to demo mode on error
            sessionId.value = 'fallback_session_' + Date.now();
            messages.value = [
              {
                id: 1,
                message: 'Sorry, we\'re having trouble connecting to our servers. This is a demo mode.',
                is_from_user: false,
                created_at: new Date().toISOString()
              }
            ];
            setTimeout(() => scrollToBottom(), 100);
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

          if (isDemoMode) {
            // Demo mode - simulate a response
            setTimeout(() => {
              const demoResponses = [
                'Thanks for your message! This is a demo response.',
                'I\'m a demo bot. In production, you\'d be connected to real support.',
                `You said: "${message}". This is just a demo of the widget functionality.`,
                'For real chat functionality, please serve this page over HTTP/HTTPS.',
                'Demo mode: Your messages aren\'t being saved or sent to real support.'
              ];

              const randomResponse = demoResponses[Math.floor(Math.random() * demoResponses.length)];

              messages.value.push({
                id: Date.now() + 1,
                message: randomResponse,
                is_from_user: false,
                created_at: new Date().toISOString()
              });

              scrollToBottom();
              sending.value = false;
            }, 1000);
            return;
          }

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
            // Add error message
            messages.value.push({
              id: Date.now() + 1,
              message: 'Sorry, there was an error sending your message. Please try again.',
              is_from_user: false,
              created_at: new Date().toISOString()
            });
            scrollToBottom();
          } finally {
            sending.value = false;
          }
        };

        const handleTyping = () => {
          if (isDemoMode) return; // Skip typing indicators in demo mode

          const now = Date.now();
          lastTypingTime.value = now;

          if (typingTimeout.value) {
            clearTimeout(typingTimeout.value);
          }

          if (now - lastTypingTime.value > 2000) {
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
          handleScroll,
          // Configuration objects
          animationConfig,
          designConfig,
          // Style functions
          getBorderRadius,
          getBoxShadow,
          getFontSize,
          getBounceIntensity,
          getAnimationDuration,
          getChatDimensions,
          getButtonStyle,
          getMessageStyle
        };
      },

      template: `
        <div class="chat-widget" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
          <!-- Chat Button -->
          <div
            v-if="!isOpen"
            @click="toggleChat"
            class="chat-button"
            :style="getButtonStyle()"
            @mouseenter="$event.target.style.transform = animationConfig.enabled ? 'scale(1.05)' : 'none'"
            @mouseleave="$event.target.style.transform = 'none'"
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
            :style="{
              position: 'fixed',
              bottom: '24px',
              right: '24px',
              zIndex: '999999',
              width: getChatDimensions().width,
              height: getChatDimensions().height,
              background: 'white',
              borderRadius: getBorderRadius(),
              boxShadow: getBoxShadow(),
              display: 'flex',
              flexDirection: 'column',
              overflow: 'hidden',
              transition: animationConfig.enabled ? 'all ' + getAnimationDuration() + ' ease' : 'none',
              animation: animationConfig.enabled && animationConfig.slideIn ? 'slideInUp 0.3s ease-out' : 'none'
            }"
          >
            <!-- Header -->
            <div :style="{ backgroundColor: primaryColor, color: 'white', padding: '16px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }">
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
                  <svg :style="{ width: '24px', height: '24px', color: primaryColor }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    :style="{ width: '24px', height: '24px', background: primaryColor, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontSize: '12px', marginRight: '8px', marginTop: '4px', flexShrink: '0' }"
                  >
                    {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
                  </div>

                  <!-- Message Bubble -->
                  <div :style="getMessageStyle(message.is_from_user)">
                    <p style="margin: 0;">{{ message.message }}</p>
                    <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                      <span
                        :style="{ fontSize: '12px', color: message.is_from_user ? 'rgba(255, 255, 255, 0.7)' : '#6B7280' }"
                      >
                        {{ formatTime(message.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Typing Indicator -->
              <div v-if="isTyping" style="display: flex; align-items: center; margin-top: 12px;">
                <div :style="{ width: '24px', height: '24px', background: primaryColor, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontSize: '12px', marginRight: '8px', flexShrink: '0' }">
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
                  :style="{ width: '32px', height: '32px', background: primaryColor, border: 'none', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'background-color 0.3s', cursor: 'pointer', ...((!newMessage.trim() || sending) ? { opacity: '0.5', cursor: 'not-allowed' } : {}) }"
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
        </div>
      `
    };

    // Make component available globally
    window.ChatWidgetComponent = ChatWidgetComponent;

    // If there's already a container, mount the widget
    const container = document.getElementById('chat-widget-container');
    if (container) {
      const app = createApp({
        template: `<ChatWidgetComponent 
          :api-key="apiKey" 
          :api-url="apiUrl" 
          :primary-color="primaryColor" 
          :animations="animations"
          :design="design"
        />`,
        data() {
          return {
            apiKey: window.ChatWidget?.config?.apiKey || 'demo_key',
            apiUrl: window.ChatWidget?.config?.apiUrl || '/api/widget',
            primaryColor: window.ChatWidget?.config?.primaryColor || '#3B82F6',
            animations: window.ChatWidget?.config?.animations || {},
            design: window.ChatWidget?.config?.design || {}
          };
        }
      });

      app.component('ChatWidgetComponent', ChatWidgetComponent);
      app.mount('#chat-widget-container');
    }
  }
})();