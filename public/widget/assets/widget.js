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
        design: { type: Object, default: () => ({}) },
        visibility: { type: Object, default: () => ({}) },
        access: { type: Object, default: () => ({}) },
        userConfig: { type: Object, default: () => ({}) },
        userInfo: { type: Object, default: () => ({}) }
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
          chatinlineSize: 320,
          chatblockSize: 500,
          fontSize: 'normal',
          avatarStyle: 'circle',
          messageStyle: 'bubbles',
          ...props.design
        };

        const accessConfig = {
          allowGuestUsers: true,
          allowLoggedUsers: true,
          restrictedRoles: [],
          requireAuth: false,
          loginUrl: '/login',
          readOnlyMode: false,
          ...props.access
        };

        const userConfig = {
          autoDetect: true,
          sendToBackend: true,
          csrfToken: null,
          authHeader: null,
          userInfoEndpoint: '/api/user/info',
          customUserData: {},
          ...props.userConfig
        };

        const isOpen = ref(false);
        const messages = ref([]);

        // WebSocket debugging helper
        const debugWebSocketMessage = (source, payload) => {
          console.log(`=== DEBUG: *** ${source.toUpperCase()} MESSAGE RECEIVED *** ===`);
          console.log('=== DEBUG: Full payload ===', JSON.stringify(payload, null, 2));
          console.log('=== DEBUG: Payload type ===', typeof payload);
          console.log('=== DEBUG: Payload keys ===', Object.keys(payload || {}));
          console.log('=== DEBUG: Current messages count ===', messages.value.length);

          if (payload && payload.message) {
            console.log('=== DEBUG: Message found ===', payload.message);
            return true;
          } else {
            console.log('=== DEBUG: ❌ NO MESSAGE OBJECT IN PAYLOAD ===');
            return false;
          }
        };

        // Debug message array changes
        const originalPush = messages.value.push;
        Object.defineProperty(messages.value, 'push', {
          value: function (...args) {
            console.log('=== DEBUG: 📝 MESSAGES ARRAY PUSH ===', args);
            console.log('=== DEBUG: Current length before push:', this.length);
            const result = originalPush.apply(this, args);
            console.log('=== DEBUG: New length after push:', this.length);
            return result;
          },
          writable: true
        });
        const newMessage = ref('');
        const sending = ref(false);
        const canSendMessages = ref(true);
        const currentUser = ref(props.userInfo || {});
        const unreadCount = ref(0);
        const isTyping = ref(false);
        const messagesContainer = ref(null);
        const supportAgent = ref({ name: 'Support Team' });
        const sessionId = ref(null);
        const lastTypingTime = ref(0);
        const typingTimeout = ref(null);

        // Polling variables
        const pollingInterval = ref(null);
        const lastMessageId = ref(0);
        const pollingEnabled = ref(true);
        const pollingIntervalMs = ref(500); // 500ms - very frequent checking
        const websocketConnected = ref(false);
        const useWebsocketFallback = ref(true);

        // Demo mode only when explicit demo key is provided
        const isDemoMode = props.apiKey === 'demo_key';

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

            // DEBUG: Test user ma'lumotlarini o'rnatish
            console.log('=== DEBUG: Setting test user for demo ===');
            if (window.ChatWidget) {
              window.ChatWidget.setUser({
                id: 1,
                name: 'admin Doe',
                email: 'admin@gmail.com',
                role: 'user'
              });
            }

            // Check user access permissions when opening chat
            checkUserAccess();
            nextTick(() => {
              scrollToBottom();
              initializeSession();
            });
          } else {
            // Stop polling when widget is closed
            stopMessagePolling();
          }
        };

        // Message polling functions
        const startMessagePolling = () => {
          if (!pollingEnabled.value) return;

          console.log('=== DEBUG: Starting message polling every', pollingIntervalMs.value, 'ms ===');

          // Update last message ID for comparison
          if (messages.value.length > 0) {
            lastMessageId.value = Math.max(...messages.value.map(m => m.id));
          }

          // Start polling as fallback if WebSocket is not connected after 3 seconds
          setTimeout(() => {
            if (!websocketConnected.value && useWebsocketFallback.value) {
              console.log('=== DEBUG: WebSocket not connected, starting polling fallback ===');
              pollingInterval.value = setInterval(async () => {
                await pollForNewMessages();
              }, pollingIntervalMs.value);
            }
          }, 3000);
        };

        const stopMessagePolling = () => {
          if (pollingInterval.value) {
            console.log('=== DEBUG: Stopping message polling ===');
            clearInterval(pollingInterval.value);
            pollingInterval.value = null;
          }
          websocketConnected.value = false;
        };

        const pollForNewMessages = async () => {
          if (!sessionId.value) return;

          try {
            console.log('=== DEBUG: Polling for new messages ===');

            // Re-initialize session to get fresh messages
            const userInfo = getCurrentUserInfo();
            const sessionPayload = {
              api_key: props.apiKey
            };

            if (userInfo && userInfo.isLoggedIn) {
              sessionPayload.user_metadata = {
                user_id: userInfo.id,
                name: userInfo.name,
                email: userInfo.email,
                role: userInfo.role
              };
            }

            const response = await fetch(`/api/widget/session`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(sessionPayload)
            });

            // if (!response.ok) {
            //   // console.warn('Failed to poll messages:', response.status);
            //   return;
            // }

            const data = await response.json();

            if (data.messages && Array.isArray(data.messages)) {
              const newMessages = data.messages.filter(msg =>
                msg && typeof msg === 'object' && msg.id && msg.id > lastMessageId.value
              );

              if (newMessages.length > 0) {
                console.log('=== DEBUG: Found', newMessages.length, 'new messages via polling ===');

                // Add new messages
                newMessages.forEach(msg => {
                  const formattedMessage = {
                    id: msg.id,
                    message: msg.message || '',
                    from_operator: Boolean(msg.from_operator),
                    created_at: msg.created_at || new Date().toISOString()
                  };

                  // Check if message doesn't already exist
                  const exists = messages.value.find(m => m.id === msg.id);
                  if (!exists) {
                    console.log('=== DEBUG: Adding new polling message ===', formattedMessage);
                    messages.value.push(formattedMessage);

                    // Update unread count if widget is closed
                    if (!isOpen.value) {
                      unreadCount.value++;
                    }
                  }
                });

                // Update last message ID
                lastMessageId.value = Math.max(...data.messages.map(m => m.id));

                // Scroll to bottom
                setTimeout(() => scrollToBottom(), 100);
              }
            }
          } catch (error) {
            console.warn('Message polling failed (non-blocking):', error);
          }
        };

        const initializeSession = async () => {
          try {
            console.log('=== DEBUG: initializeSession starting ===');

            // Get user info first to pass with session creation
            const userInfo = getCurrentUserInfo();
            console.log('=== DEBUG: Widget opening ===');
            console.log('User info detected:', userInfo);

            // Prepare session request with user information if available
            const sessionPayload = {
              api_key: props.apiKey
            };

            // Add user information to session request for better user identification
            if (userInfo && userInfo.isLoggedIn) {
              sessionPayload.user_metadata = {
                user_id: userInfo.id,
                name: userInfo.name,
                email: userInfo.email,
                role: userInfo.role
              };
              console.log('=== DEBUG: Sending user metadata ===', sessionPayload.user_metadata);
            } else {
              console.log('=== DEBUG: No user info found, opening as anonymous ===');
            }

            const headers = {
              'Content-Type': 'application/json'
            };

            console.log('=== DEBUG: Making session request to:', `${props.apiUrl}/session`);
            console.log('=== DEBUG: Session payload:', sessionPayload);

            const response = await fetch(`${props.apiUrl}/session`, {
              method: 'POST',
              headers: headers,
              credentials: 'same-origin', // Include session cookies
              body: JSON.stringify(sessionPayload)
            });

            console.log('=== DEBUG: Session response status:', response.status);
            console.log('=== DEBUG: Session response ok:', response.ok);

            if (!response.ok) {
              console.error('=== DEBUG: Session response error ===');
              throw new Error('Session init failed');
            }

            const data = await response.json();
            console.log('=== DEBUG: Full API response ===', data);

            sessionId.value = data.session_id;
            console.log('=== DEBUG: Session ID set to:', sessionId.value);

            // Validate messages before setting
            if (data.messages && Array.isArray(data.messages)) {
              const validMessages = data.messages.filter(msg => msg && typeof msg === 'object' && msg.id);
              messages.value = validMessages.map(msg => ({
                id: msg.id,
                message: msg.message || '',
                from_operator: Boolean(msg.from_operator),
                created_at: msg.created_at || new Date().toISOString()
              }));
            } else {
              messages.value = [];
            }

            supportAgent.value = data.agent || { name: 'Support Team' };

            console.log('=== DEBUG: Session created successfully ===');
            console.log('Chat ID:', data.chat_id);
            console.log('Messages received:', data.messages?.length || 0);
            console.log('Messages.value length:', messages.value.length);
            console.log('User identified:', data.user?.name || 'No user');
            if (data.messages && data.messages.length > 0) {
              console.log('First message:', data.messages[0]?.message);
              console.log('Last message:', data.messages[data.messages.length - 1]?.message);
              console.log('All messages:', data.messages.map(m => `${m.from_operator ? 'Support' : 'User'}: ${m.message}`));
            } else {
              console.log('No messages in response - checking user identification...');
            }

            // Update current user with server response
            if (data.user) {
              currentUser.value = { ...currentUser.value, ...data.user, isLoggedIn: true };
            }

            // If we have user info but session creation didn't identify user, identify now
            if (userInfo && userInfo.isLoggedIn && !data.user) {
              const identified = await identifyCurrentUser();
              // After identifying, reload session to get user's chat history
              if (identified) {
                await reloadUserChats();
              }
            } else if (userInfo && userInfo.isLoggedIn && data.user) {
              // User was already identified on server, update local state
              currentUser.value = { ...currentUser.value, ...data.user, isLoggedIn: true };
              console.log('User already identified on server, loaded ALL', data.messages?.length || 0, 'messages (complete history)');
            }

            // Initialize realtime after session established
            await initRealtime(data.config || {});

            // Start message polling as fallback
            startMessagePolling();

            setTimeout(() => scrollToBottom(), 100);
          } catch (error) {
            console.error('Failed to initialize session:', error);
            messages.value = [
              {
                id: 1,
                message: 'We could not connect right now. Please reload or try again.',
                from_operator: true,
                created_at: new Date().toISOString()
              }
            ];
            setTimeout(() => scrollToBottom(), 100);
          }
        };

        // Check user access permissions
        const checkUserAccess = () => {
          // Check if ChatWidget SDK has access checking
          if (window.ChatWidget && typeof window.ChatWidget.canUserSendMessages === 'function') {
            canSendMessages.value = window.ChatWidget.canUserSendMessages();
          } else {
            // Fallback to local access configuration
            canSendMessages.value = !accessConfig.readOnlyMode;
          }
        };

        // Handle authentication requirement
        const handleAuthRequired = () => {
          if (accessConfig.requireAuth && accessConfig.loginUrl) {
            // Redirect to login or show login modal
            window.location.href = accessConfig.loginUrl;
          }
        };

        // Update user information
        const updateUser = (userInfo) => {
          currentUser.value = { ...currentUser.value, ...userInfo };
          // Recheck access permissions when user changes
          checkUserAccess();
          // When user info changes, identify user and reload their chats
          identifyCurrentUser().then(() => {
            reloadUserChats();
          });
        };

        // Get current user information from SDK or default sources
        const getCurrentUserInfo = () => {
          console.log('=== DEBUG: getCurrentUserInfo called ===');

          // Try to get user info from ChatWidget SDK first
          if (window.ChatWidget && typeof window.ChatWidget.getCurrentUserInfo === 'function') {
            const sdkUserInfo = window.ChatWidget.getCurrentUserInfo();
            console.log('SDK User Info:', sdkUserInfo);
            if (sdkUserInfo && sdkUserInfo.isLoggedIn) {
              console.log('Using SDK user info:', sdkUserInfo.name || sdkUserInfo.email);
              return sdkUserInfo;
            }
          } else {
            console.log('ChatWidget SDK not available or no getCurrentUserInfo function');
          }

          // Check Laravel's global user object (if authenticated)
          if (window.Laravel && window.Laravel.user) {
            const laravelUser = {
              id: window.Laravel.user.id,
              name: window.Laravel.user.name,
              email: window.Laravel.user.email,
              role: window.Laravel.user.role || 'user',
              isLoggedIn: true
            };
            console.log('Using Laravel global user:', laravelUser);
            return laravelUser;
          }

          // Check meta tags for user information
          const userIdMeta = document.querySelector('meta[name="user-id"]');
          const userNameMeta = document.querySelector('meta[name="user-name"]');
          const userEmailMeta = document.querySelector('meta[name="user-email"]');

          if (userIdMeta && userIdMeta.content) {
            const metaUser = {
              id: parseInt(userIdMeta.content),
              name: userNameMeta ? userNameMeta.content : null,
              email: userEmailMeta ? userEmailMeta.content : null,
              role: 'user',
              isLoggedIn: true
            };
            console.log('Using meta tag user info:', metaUser);
            return metaUser;
          }

          // Fallback to current user value or props
          const fallback = currentUser.value.isLoggedIn ? currentUser.value : (props.userInfo || {});
          console.log('Using fallback user info:', fallback);
          return fallback;
        };

        // Reload user's chat history after user identification
        const reloadUserChats = async () => {
          if (!sessionId.value) return;

          try {
            const userInfo = getCurrentUserInfo();
            if (!userInfo || !userInfo.isLoggedIn) return;

            console.log('Reloading user chats for:', userInfo.name || userInfo.id);

            const headers = window.ChatWidget?.getSecureHeaders ?
              window.ChatWidget.getSecureHeaders() :
              { 'Content-Type': 'application/json' };

            // Call session endpoint again to get user's chat history
            const response = await fetch(`${props.apiUrl}/session`, {
              method: 'POST',
              headers: headers,
              credentials: 'same-origin',
              body: JSON.stringify({
                api_key: props.apiKey,
                session_id: sessionId.value, // Use existing session ID
                force_user_reload: true // Flag to force user chat loading
              })
            });

            if (response.ok) {
              const data = await response.json();
              if (data.messages && data.messages.length > 0) {
                messages.value = data.messages;
                console.log(`Loaded ALL ${data.messages.length} chat messages for user (complete history)`);
                setTimeout(() => scrollToBottom(), 100);
              } else {
                console.log('No previous messages found for this user');
              }
            }
          } catch (error) {
            console.warn('Failed to reload user chats (non-blocking):', error);
          }
        };

        // Update/refresh widget data
        const updateWidget = async () => {
          console.log('=== DEBUG: Updating widget data ===');

          try {
            // Stop current polling
            stopMessagePolling();

            // Re-initialize session to get fresh data
            await initializeSession();

            console.log('=== DEBUG: Widget updated successfully ===');
          } catch (error) {
            console.error('=== DEBUG: Failed to update widget ===', error);
          }
        };

        // Make updateUser and updateWidget available globally for SDK
        if (!window.ChatWidgetComponent) {
          window.ChatWidgetComponent = {};
        }
        window.ChatWidgetComponent.updateUser = updateUser;
        window.ChatWidgetComponent.updateWidget = updateWidget;

        const sendMessage = async () => {
          console.log('=== DEBUG: sendMessage called ===');
          console.log('canSendMessages:', canSendMessages.value);
          console.log('newMessage:', newMessage.value);
          console.log('sending:', sending.value);
          console.log('sessionId:', sessionId.value);

          if (!canSendMessages.value) {
            console.log('=== DEBUG: User cannot send messages ===');
            if (accessConfig.requireAuth) {
              handleAuthRequired();
              return;
            } else {
              console.warn('User does not have permission to send messages');
              return;
            }
          }

          if (!newMessage.value.trim() || sending.value) {
            console.log('=== DEBUG: Message empty or already sending ===');
            return;
          }

          if (!sessionId.value) {
            console.error('=== DEBUG: No session ID available, cannot send message ===');
            // Try to initialize session if missing
            try {
              await initializeSession();
              if (!sessionId.value) {
                console.error('Failed to create session, cannot send message');
                return;
              }
            } catch (error) {
              console.error('Failed to initialize session:', error);
              return;
            }
          }

          const message = newMessage.value.trim();
          newMessage.value = '';
          sending.value = true;

          const userMessage = {
            id: Date.now(),
            message: message,
            from_operator: false,
            created_at: new Date().toISOString()
          };

          messages.value.push(userMessage);
          scrollToBottom();

          try {
            // If we haven't identified the user yet, try now (best-effort)
            await identifyCurrentUser();

            // Prepare secure payload with user information
            const payload = {
              api_key: props.apiKey,
              session_id: sessionId.value,
              message: message,
              // Include user information if available
              user_info: currentUser.value.isLoggedIn ? {
                id: currentUser.value.id,
                name: currentUser.value.name,
                email: currentUser.value.email,
                role: currentUser.value.role
              } : null,
              timestamp: Date.now(),
              page_url: window.location.href
            };

            console.log('=== DEBUG: Sending message payload ===', payload);
            console.log('=== DEBUG: Session ID ===', sessionId.value);
            console.log('=== DEBUG: API URL ===', `${props.apiUrl}/message`);
            console.log('=== DEBUG: props.apiUrl ===', props.apiUrl);
            console.log('=== DEBUG: Full URL for request ===', `${props.apiUrl}/message`);
            console.log('=== DEBUG: window.location.origin ===', window.location.origin);

            // Get secure headers from SDK
            const headers = window.ChatWidget?.getSecureHeaders ?
              window.ChatWidget.getSecureHeaders() :
              {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              };

            // Add CSRF token if available
            if (currentUser.value.csrfToken) {
              headers['X-CSRF-TOKEN'] = currentUser.value.csrfToken;
            }

            const response = await fetch(`${props.apiUrl}/message`, {
              method: 'POST',
              headers: headers,
              credentials: 'same-origin', // Include session cookies
              body: JSON.stringify(payload)
            });

            console.log('=== DEBUG: Response received ===');
            console.log('=== DEBUG: Response status ===', response.status);
            console.log('=== DEBUG: Response statusText ===', response.statusText);
            console.log('=== DEBUG: Response ok ===', response.ok);
            console.log('=== DEBUG: Response headers ===', [...response.headers.entries()]);

            if (!response.ok) {
              const responseText = await response.text();
              console.error('=== DEBUG: Error response body ===', responseText);
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('=== DEBUG: Message sent response ===', data);

            // Check if response has proper structure
            if (!data) {
              throw new Error('Empty response from server');
            }

            if (data.error) {
              throw new Error(data.error);
            }

            // Update the temporary message with the server response
            const messageIndex = messages.value.findIndex(m => m.id === userMessage.id);
            console.log('=== DEBUG: Message update ===', {
              messageIndex,
              userMessageId: userMessage.id,
              serverResponse: data.message,
              hasMessage: !!data.message
            });

            if (messageIndex !== -1 && data.message) {
              // Ensure the server response has required fields
              const updatedMessage = {
                id: data.message.id || userMessage.id,
                message: data.message.message || userMessage.message,
                from_operator: data.message.from_operator || false,
                created_at: data.message.created_at || userMessage.created_at
              };

              console.log('=== DEBUG: Updating message at index', messageIndex, 'with:', updatedMessage);
              messages.value[messageIndex] = updatedMessage;
            } else if (messageIndex === -1) {
              console.warn('Could not find message to update in array');
            } else if (!data.message) {
              console.warn('Server response missing message data');
            }

          } catch (error) {
            console.error('=== DEBUG: Message send error ===', error);
            console.error('Error details:', {
              name: error.name,
              message: error.message,
              stack: error.stack
            });

            // Remove the failed message from array
            const failedMessageIndex = messages.value.findIndex(m => m.id === userMessage.id);
            if (failedMessageIndex !== -1) {
              messages.value.splice(failedMessageIndex, 1);
            }

            // Add error message
            messages.value.push({
              id: Date.now() + 1,
              message: `Failed to send message: ${error.message}. Please try again.`,
              from_operator: true,
              created_at: new Date().toISOString()
            });
            scrollToBottom();
          } finally {
            sending.value = false;
            console.log('=== DEBUG: Message sending finished, sending.value =', sending.value);
          }
        };

        // Identify the current user (logged-in or provided by SDK) and attach to session
        async function identifyCurrentUser() {
          if (!userConfig.sendToBackend || !sessionId.value) {
            return false;
          }

          const info = window.ChatWidget?.getCurrentUserInfo
            ? window.ChatWidget.getCurrentUserInfo()
            : currentUser.value;

          // If no useful info, skip
          if (!info || (!info.isLoggedIn && !info.email && !info.name)) {
            return false;
          }

          console.log('Identifying user:', info.name || info.email || info.id);

          const payload = {
            api_key: props.apiKey,
            session_id: sessionId.value,
            name: info.name || `${info.first_name || ''} ${info.last_name || ''}`.trim() || null,
            email: info.email || null,
            phone: info.phone || null,
            notes: info.role ? `Role: ${info.role}` : null,
            metadata: {
              user_id: info.id || null,
              role: info.role || null,
              page_url: window.location.href,
            },
          };

          try {
            const headers = window.ChatWidget?.getSecureHeaders
              ? window.ChatWidget.getSecureHeaders()
              : {
                'Content-Type': 'application/json',
                Accept: 'application/json',
              };

            const response = await fetch(`${props.apiUrl}/identify-user`, {
              method: 'POST',
              headers,
              credentials: 'same-origin',
              body: JSON.stringify(payload),
            });

            if (response.ok) {
              // Update current user info
              currentUser.value = { ...currentUser.value, ...info, isLoggedIn: true };
              console.log('User identified successfully, loading chat history...');
              return true;
            } else {
              console.warn('User identification failed:', response.status);
              return false;
            }
          } catch (error) {
            console.warn('User identification failed (non-blocking):', error);
            return false;
          }
        }

        // Realtime (Reverb) setup
        let echoInstance = null;

        const loadScript = (src) => {
          return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
              resolve();
              return;
            }
            const s = document.createElement('script');
            s.src = src;
            s.async = true;
            s.onload = () => resolve();
            s.onerror = (e) => reject(e);
            document.head.appendChild(s);
          });
        };

        const initRealtime = async (config = {}) => {
          if (echoInstance || !sessionId.value) {
            console.log('=== DEBUG: Realtime already initialized or no session ID ===');
            return;
          }

          console.log('=== DEBUG: Initializing WebSocket/Reverb ===', config);

          try {
            const wsUrl = config.ws_url || 'ws://' + window.location.hostname + ':8080';
            const appKey = config.app_key || props.pusherKey || props.reverbKey || '';
            const chatId = config.chat_id;

            console.log('=== DEBUG: WebSocket Config ===', {
              wsUrl,
              appKey,
              chatId,
              sessionId: sessionId.value
            });

            if (!appKey) {
              console.warn('Realtime disabled: missing app key');
              return;
            }

            const url = new URL(wsUrl.replace('ws://', 'http://').replace('wss://', 'https://'));
            const wsHost = url.hostname;
            const wsPort = url.port || (url.protocol === 'https:' ? 443 : 80);
            const forceTLS = url.protocol === 'https:';

            console.log('=== DEBUG: Parsed WebSocket URL ===', {
              wsHost,
              wsPort,
              forceTLS,
              protocol: url.protocol
            });

            // Load required scripts
            await loadScript('https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js');
            await loadScript('https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js');

            console.log('=== DEBUG: Scripts loaded, creating Echo instance ===');
            console.log('=== DEBUG: Available constructors ===', {
              Echo: typeof window.Echo,
              LaravelEcho: typeof window.LaravelEcho,
              Pusher: typeof window.Pusher
            });

            // Check what's actually available in window.Echo
            if (window.Echo) {
              console.log('=== DEBUG: window.Echo properties ===', Object.keys(window.Echo));
            }

            // Laravel Echo IIFE might expose the constructor differently
            let EchoConstructor;
            if (window.LaravelEcho && typeof window.LaravelEcho === 'function') {
              EchoConstructor = window.LaravelEcho;
            } else if (window.Echo && typeof window.Echo === 'function') {
              EchoConstructor = window.Echo;
            } else if (window.Echo && window.Echo.default && typeof window.Echo.default === 'function') {
              EchoConstructor = window.Echo.default;
            } else if (window.Echo && window.Echo.Echo && typeof window.Echo.Echo === 'function') {
              EchoConstructor = window.Echo.Echo;
            } else {
              throw new Error('Laravel Echo constructor not found');
            }

            console.log('=== DEBUG: Using EchoConstructor ===', typeof EchoConstructor);
            console.log('=== DEBUG: Attempting to create Echo instance with config ===', {
              broadcaster: 'reverb',
              key: appKey,
              wsHost,
              wsPort,
              wssPort: wsPort,
              forceTLS
            });

            echoInstance = new EchoConstructor({
              broadcaster: 'reverb',
              key: appKey,
              wsHost,
              wsPort,
              wssPort: wsPort,
              forceTLS,
              disableStats: true,
              enabledTransports: forceTLS ? ['ws', 'wss'] : ['ws'],
            });

            console.log('=== DEBUG: Echo instance created ===', echoInstance);

            // Set connection timeout
            let connectionTimeout = setTimeout(() => {
              if (!websocketConnected.value) {
                console.log('=== DEBUG: WebSocket connection timeout after 5 seconds ===');
                websocketConnected.value = false;
              }
            }, 5000);

            // Listen for connection events
            if (window.Pusher && echoInstance.connector && echoInstance.connector.pusher) {
              const pusher = echoInstance.connector.pusher;

              pusher.connection.bind('connected', () => {
                console.log('=== DEBUG: WebSocket connected successfully ===');
                console.log('=== DEBUG: Connection state:', pusher.connection.state);
                console.log('=== DEBUG: Socket ID:', pusher.connection.socket_id);
                websocketConnected.value = true;
                clearTimeout(connectionTimeout);
                // Stop polling if WebSocket connects
                if (pollingInterval.value) {
                  clearInterval(pollingInterval.value);
                  pollingInterval.value = null;
                  console.log('=== DEBUG: Stopped polling fallback, WebSocket is active ===');
                }
              });

              pusher.connection.bind('disconnected', () => {
                console.log('=== DEBUG: WebSocket disconnected ===');
                websocketConnected.value = false;
                clearTimeout(connectionTimeout);
                // Start polling fallback when WebSocket disconnects
                if (useWebsocketFallback.value && !pollingInterval.value) {
                  console.log('=== DEBUG: WebSocket disconnected, starting polling fallback ===');
                  pollingInterval.value = setInterval(async () => {
                    await pollForNewMessages();
                  }, pollingIntervalMs.value);
                }
              });

              pusher.connection.bind('error', (error) => {
                console.error('=== DEBUG: WebSocket connection error ===', error);
                websocketConnected.value = false;
                clearTimeout(connectionTimeout);
              });
            }

            // Listen for messages on chat channel  
            if (chatId) {
              console.log('=== DEBUG: Subscribing to chat channel ===', `chat.${chatId}`);

              const chatChannel = echoInstance.channel(`chat.${chatId}`);

              chatChannel.subscribed(() => {
                console.log('=== DEBUG: ✅ Successfully subscribed to chat channel ===');
                console.log('=== DEBUG: Chat channel object:', chatChannel);
                console.log('=== DEBUG: Listening for: message.sent ===');
              });

              chatChannel.error((error) => {
                console.error('=== DEBUG: Chat channel subscription error ===', error);
              });

              chatChannel.listen('message.sent', (payload) => {
                if (debugWebSocketMessage('CHAT CHANNEL', payload)) {
                  const newMessage = {
                    id: payload.message.id,
                    message: payload.message.message,
                    from_operator: Boolean(payload.message.from_operator),
                    created_at: payload.message.created_at
                  };

                  console.log('=== DEBUG: Formatted new message ===', newMessage);

                  // Check if message doesn't already exist
                  const exists = messages.value.find(m => m.id === newMessage.id);
                  console.log('=== DEBUG: Message exists check ===', !!exists);

                  if (!exists) {
                    console.log('=== DEBUG: ✅ ADDING NEW WEBSOCKET MESSAGE ===');
                    messages.value.push(newMessage);
                    console.log('=== DEBUG: New messages count ===', messages.value.length);

                    // Update last message ID for polling
                    if (newMessage.id > lastMessageId.value) {
                      lastMessageId.value = newMessage.id;
                    }

                    setTimeout(() => scrollToBottom(), 100);

                    // Update unread count if widget is closed
                    if (!isOpen.value) {
                      unreadCount.value++;
                    }
                  } else {
                    console.log('=== DEBUG: ❌ Message already exists, skipping ===', newMessage.id);
                  }
                }
              });
            }

            // Also listen on widget session channel for backward compatibility
            console.log('=== DEBUG: Subscribing to widget session channel ===', `widget.session.${sessionId.value}`);

            const widgetChannel = echoInstance.channel(`widget.session.${sessionId.value}`);

            widgetChannel.subscribed(() => {
              console.log('=== DEBUG: ✅ Successfully subscribed to widget session channel ===');
              console.log('=== DEBUG: Widget channel object:', widgetChannel);
              console.log('=== DEBUG: Listening for: widget.message.sent ===');
            });

            widgetChannel.error((error) => {
              console.error('=== DEBUG: Widget channel subscription error ===', error);
            });

            widgetChannel.listen('widget.message.sent', (payload) => {
              console.log('=== DEBUG: *** WIDGET MESSAGE RECEIVED *** ===');
              console.log('=== DEBUG: Event: widget.message.sent ===');
              console.log('=== DEBUG: Channel: widget.session.' + sessionId.value + ' ===');
              console.log('=== DEBUG: Full payload ===', payload);
              console.log('=== DEBUG: Payload type ===', typeof payload);
              console.log('=== DEBUG: Payload keys ===', Object.keys(payload || {}));

              if (payload && payload.message) {
                console.log('=== DEBUG: Widget message object found ===', payload.message);
                console.log('=== DEBUG: Message ID ===', payload.message.id);
                console.log('=== DEBUG: Message text ===', payload.message.message);
                console.log('=== DEBUG: From operator ===', payload.message.from_operator);
                console.log('=== DEBUG: Current messages count ===', messages.value.length);

                const newMessage = {
                  id: payload.message.id,
                  message: payload.message.message,
                  from_operator: Boolean(payload.message.from_operator),
                  created_at: payload.message.created_at
                };

                console.log('=== DEBUG: Formatted widget message ===', newMessage);

                // Check if message doesn't already exist
                const exists = messages.value.find(m => m.id === newMessage.id);
                console.log('=== DEBUG: Widget message exists check ===', !!exists);

                if (!exists) {
                  console.log('=== DEBUG: ✅ ADDING NEW WIDGET MESSAGE ===', newMessage);
                  messages.value.push(newMessage);
                  console.log('=== DEBUG: New messages count ===', messages.value.length);

                  // Update last message ID for polling
                  if (newMessage.id > lastMessageId.value) {
                    lastMessageId.value = newMessage.id;
                    console.log('=== DEBUG: Updated lastMessageId ===', lastMessageId.value);
                  }

                  setTimeout(() => scrollToBottom(), 100);

                  // Update unread count if widget is closed
                  if (!isOpen.value) {
                    unreadCount.value++;
                    console.log('=== DEBUG: Updated unread count ===', unreadCount.value);
                  }
                } else {
                  console.log('=== DEBUG: ❌ Widget message already exists, skipping ===', newMessage.id);
                }
              } else {
                console.log('=== DEBUG: ❌ NO MESSAGE OBJECT IN WIDGET PAYLOAD ===');
                console.log('=== DEBUG: Widget payload structure issue ===', payload);
              }
            });

            console.log('=== DEBUG: WebSocket/Reverb initialized successfully ===');
            console.log('=== DEBUG: Active channels:', {
              chat: chatId ? `chat.${chatId}` : 'not subscribed',
              widget: `widget.session.${sessionId.value}`
            });

            // Add global event listener to catch ALL WebSocket events for debugging
            if (window.Pusher && echoInstance.connector && echoInstance.connector.pusher) {
              const pusher = echoInstance.connector.pusher;

              // Listen to ALL events on ALL channels
              pusher.bind_global((eventName, data) => {
                console.log('=== DEBUG: 🌐 GLOBAL WEBSOCKET EVENT ===');
                console.log('=== DEBUG: Event name:', eventName);
                console.log('=== DEBUG: Event data:', data);
                console.log('=== DEBUG: All channels:', Object.keys(pusher.channels.channels));

                // Process message.sent events if they aren't handled by specific channel listeners
                if (eventName === 'message.sent' && data && data.message) {
                  console.log('=== DEBUG: 🚨 GLOBAL FALLBACK - Processing message.sent ===');

                  const newMessage = {
                    id: data.message.id,
                    message: data.message.message,
                    from_operator: Boolean(data.message.from_operator),
                    created_at: data.message.created_at
                  };

                  // Check if message doesn't already exist
                  const exists = messages.value.find(m => m.id === newMessage.id);
                  if (!exists) {
                    console.log('=== DEBUG: ✅ GLOBAL - Adding message to widget ===', newMessage);
                    messages.value.push(newMessage);

                    // Update last message ID for polling
                    if (newMessage.id > lastMessageId.value) {
                      lastMessageId.value = newMessage.id;
                    }

                    setTimeout(() => scrollToBottom(), 100);

                    // Update unread count if widget is closed
                    if (!isOpen.value && newMessage.from_operator) {
                      unreadCount.value++;
                    }
                  } else {
                    console.log('=== DEBUG: ❌ GLOBAL - Message already exists, skipping ===', newMessage.id);
                  }
                }
              });
            }

          } catch (err) {
            console.error('=== DEBUG: Realtime init failed ===', err);
            console.warn('Realtime init failed (non-blocking):', err);
            websocketConnected.value = false;

            // Start polling fallback immediately if WebSocket setup fails
            if (useWebsocketFallback.value && sessionId.value) {
              console.log('=== DEBUG: WebSocket setup failed, starting polling fallback ===');
              pollingInterval.value = setInterval(async () => {
                await pollForNewMessages();
              }, pollingIntervalMs.value);
            }
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
          if (pollingInterval.value) {
            clearInterval(pollingInterval.value);
          }
          if (echoInstance) {
            echoInstance.disconnect();
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
          canSendMessages,
          currentUser,
          websocketConnected,
          toggleChat,
          sendMessage,
          handleTyping,
          formatTime,
          handleScroll,
          checkUserAccess,
          handleAuthRequired,
          updateUser,
          updateWidget,
          // Configuration objects
          animationConfig,
          designConfig,
          accessConfig,
          userConfig,
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
                  <p style="font-size: 12px; color: rgba(255, 255, 255, 0.7); margin: 0;">
                    {{ supportAgent.name || 'Support Team' }}<span v-if="messages.length > 0"> • {{ messages.length }} messages</span>
                  </p>
                </div>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 4px;" title="WebSocket Connection Status">
                  <div :style="{ 
                    width: '8px', 
                    height: '8px', 
                    borderRadius: '50%', 
                    backgroundColor: websocketConnected ? '#10B981' : '#EF4444',
                    transition: 'background-color 0.3s'
                  }"></div>
                  <span style="font-size: 10px; color: rgba(255, 255, 255, 0.7);">
                    {{ websocketConnected ? 'WS' : 'Polling' }}
                  </span>
                </div>
                <button @click="updateWidget" style="color: rgba(255, 255, 255, 0.8); background: none; border: none; cursor: pointer; transition: color 0.3s; padding: 4px;" title="Update widget">
                  <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </button>
                <button @click="toggleChat" style="color: rgba(255, 255, 255, 0.8); background: none; border: none; cursor: pointer; transition: color 0.3s;">
                  <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
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
                <p style="color: #9CA3AF; font-size: 12px; margin: 0;">Send us a message and we'll get back to you shortly.<br>All your previous messages are loaded automatically.</p>
              </div>

              <!-- Messages -->
              <div style="display: flex; flex-direction: column; gap: 12px;">
                <div
                  v-for="message in messages"
                  :key="message.id"
                  style="display: flex;"
                  :style="!message.from_operator ? 'justify-content: flex-end' : 'justify-content: flex-start'"
                >
                  <!-- Support Avatar -->
                  <div
                    v-if="message.from_operator"
                    :style="{ width: '24px', height: '24px', background: primaryColor, borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontSize: '12px', marginRight: '8px', marginTop: '4px', flexShrink: '0' }"
                  >
                    {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
                  </div>

                  <!-- Message Bubble -->
                  <div :style="getMessageStyle(!message.from_operator)">
                    <p style="margin: 0;">{{ message.message }}</p>
                    <div style="display: flex; justify-content: flex-end; margin-top: 4px;">
                      <span
                        :style="{ fontSize: '12px', color: !message.from_operator ? 'rgba(255, 255, 255, 0.7)' : '#6B7280' }"
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
            <div v-if="canSendMessages" style="padding: 12px; background: white; border-top: 1px solid #E5E7EB;">
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
            
            <!-- Restricted Access Message -->
            <div v-else style="padding: 12px; background: #F9FAFB; border-top: 1px solid #E5E7EB; text-align: center;">
              <p style="margin: 0; color: #6B7280; font-size: 14px;">
                <template v-if="accessConfig.requireAuth">
                  <span>Please </span>
                  <button @click="handleAuthRequired" style="color: #3B82F6; background: none; border: none; cursor: pointer; text-decoration: underline;">login</button>
                  <span> to send messages</span>
                </template>
                <template v-else-if="accessConfig.readOnlyMode">
                  Chat is in read-only mode
                </template>
                <template v-else>
                  You don't have permission to send messages
                </template>
              </p>
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
          :visibility="visibility"
          :access="access"
          :user-config="userConfig"
          :user-info="userInfo"
        />`,
        data() {
          const configData = {
            apiKey: window.ChatWidget?.config?.apiKey || 'demo_key',
            apiUrl: window.ChatWidget?.config?.apiUrl || '/api/widget',
            primaryColor: window.ChatWidget?.config?.primaryColor || '#3B82F6',
            animations: window.ChatWidget?.config?.animations || {},
            design: window.ChatWidget?.config?.design || {},
            visibility: window.ChatWidget?.config?.visibility || {},
            access: window.ChatWidget?.config?.access || {},
            userConfig: window.ChatWidget?.config?.user || {},
            userInfo: window.ChatWidget?.getCurrentUserInfo ? window.ChatWidget.getCurrentUserInfo() : {}
          };

          console.log('=== DEBUG: Widget config data ===', configData);
          console.log('=== DEBUG: window.ChatWidget ===', window.ChatWidget);
          console.log('=== DEBUG: window.ChatWidget.config ===', window.ChatWidget?.config);

          return configData;
        }
      });

      app.component('ChatWidgetComponent', ChatWidgetComponent);
      app.mount('#chat-widget-container');

      // Make widget update function globally accessible
      if (!window.ChatWidget) {
        window.ChatWidget = {};
      }
      window.ChatWidget.update = () => {
        if (window.ChatWidgetComponent && window.ChatWidgetComponent.updateWidget) {
          window.ChatWidgetComponent.updateWidget();
        }
      };

    }
  }
})();