(function () {
    'use strict';

    // ChatWidget SDK
    window.ChatWidget = {
        config: {
            apiKey: null,
            apiUrl: '/api/widget',
            widgetUrl: '/widget',
            position: 'bottom-right',
            primaryColor: '#3B82F6',
            debug: false,
            // Animation configurations
            animations: {
                enabled: true,
                openSpeed: 300,
                bounceIntensity: 'normal', // 'none', 'subtle', 'normal', 'strong'
                typingAnimation: true,
                fadeIn: true,
                slideIn: true
            },
            // Design configurations
            design: {
                theme: 'modern', // 'modern', 'classic', 'minimal', 'rounded'
                borderRadius: 'normal', // 'none', 'small', 'normal', 'large', 'round'
                shadow: 'normal', // 'none', 'subtle', 'normal', 'strong'
                buttonStyle: 'floating', // 'floating', 'fixed', 'minimal'
                chatWidth: 320,
                chatHeight: 500,
                fontSize: 'normal', // 'small', 'normal', 'large'
                avatarStyle: 'circle', // 'circle', 'square', 'none'
                messageStyle: 'bubbles' // 'bubbles', 'flat', 'outlined'
            }
        },

        // Initialize the widget
        init: function (options = {}) {
            // Merge options with defaults
            Object.assign(this.config, options);

            if (!this.config.apiKey) {
                console.error('ChatWidget: API key is required');
                return;
            }

            this.log('Initializing ChatWidget with config:', this.config);

            // Load CSS
            this.loadCSS();

            // Create widget container
            this.createContainer();

            // Load Vue app
            this.loadVueApp();
        },

        // Get absolute URL for assets
        getAssetUrl: function (path) {
            // Normalize path by removing leading slashes
            const normalizedPath = path.replace(/^\/+/, '');

            // If running locally from file://, use relative path from the widget directory
            if (window.location.protocol === 'file:') {
                // Try to get the SDK script's location
                const scripts = document.getElementsByTagName('script');
                let sdkPath = '';
                for (let script of scripts) {
                    if (script.src && script.src.includes('sdk.js')) {
                        // Get the directory of the SDK script
                        sdkPath = script.src.substring(0, script.src.lastIndexOf('/') + 1);
                        break;
                    }
                }

                if (sdkPath) {
                    return sdkPath + normalizedPath;
                }

                // Fallback to relative path
                return normalizedPath;
            }

            // For web servers, use configured widget URL
            if (this.config.widgetUrl.startsWith('http')) {
                return this.config.widgetUrl + '/' + normalizedPath;
            }

            // Relative path for same-origin
            return this.config.widgetUrl + '/' + normalizedPath;
        },

        // Load widget styles
        loadCSS: function () {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = this.getAssetUrl('/assets/widget.css');
            link.onerror = () => {
                this.log('Failed to load CSS, using inline styles...');
                this.addInlineStyles();
            };
            document.head.appendChild(link);
            this.log('Loading CSS from: ' + link.href);
        },

        // Add inline styles as fallback
        addInlineStyles: function () {
            const style = document.createElement('style');
            style.textContent = `
                .chat-widget {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    z-index: 999999;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                .chat-widget-button {
                    width: 60px;
                    height: 60px;
                    background: ${this.config.primaryColor};
                    border-radius: 50%;
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                    color: white;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    transition: transform 0.2s;
                }
                .chat-widget-button:hover {
                    transform: scale(1.1);
                }
                .chat-widget-window {
                    position: absolute;
                    bottom: 70px;
                    right: 0;
                    width: 350px;
                    height: 500px;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
                    display: none;
                    flex-direction: column;
                }
                .chat-widget-header {
                    background: ${this.config.primaryColor};
                    color: white;
                    padding: 16px;
                    border-radius: 12px 12px 0 0;
                    font-weight: 600;
                }
                .chat-widget-body {
                    flex: 1;
                    padding: 16px;
                    overflow-y: auto;
                }
                .chat-widget-input {
                    padding: 12px;
                    border-top: 1px solid #e5e5e5;
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .chat-widget-input input {
                    flex: 1;
                    border: 1px solid #e5e5e5;
                    border-radius: 20px;
                    padding: 8px 16px;
                    outline: none;
                }
                .chat-widget-input button {
                    background: ${this.config.primaryColor};
                    color: white;
                    border: none;
                    border-radius: 4px;
                    padding: 8px 12px;
                    cursor: pointer;
                    font-size: 12px;
                }
                .chat-widget-input button:hover {
                    opacity: 0.9;
                }
            `;
            document.head.appendChild(style);
            this.log('Inline styles added');
        },

        // Create widget container
        createContainer: function () {
            if (document.getElementById('chat-widget-container')) {
                return; // Already exists
            }

            const container = document.createElement('div');
            container.id = 'chat-widget-container';
            container.style.cssText = `
        position: fixed;
        z-index: 999999;
        pointer-events: none;
        ${this.getPositionCSS()}
      `;

            document.body.appendChild(container);
            this.log('Widget container created');
        },

        // Get position CSS based on config
        getPositionCSS: function () {
            switch (this.config.position) {
                case 'bottom-left':
                    return 'bottom: 0; left: 0;';
                case 'bottom-right':
                default:
                    return 'bottom: 0; right: 0;';
                case 'top-left':
                    return 'top: 0; left: 0;';
                case 'top-right':
                    return 'top: 0; right: 0;';
            }
        },

        // Load Vue application
        loadVueApp: function () {
            const script = document.createElement('script');
            script.src = this.getAssetUrl('/assets/widget.js');
            this.log('Loading JS from: ' + script.src);
            script.onload = () => {
                this.log('Vue app loaded');
                // The widget will call ChatWidget.onWidgetReady() when Vue is ready
            };
            script.onerror = (error) => {
                this.log('Failed to load Vue app, using fallback...');
                this.createFallbackWidget();
            };
            document.head.appendChild(script);
        },

        // Create fallback widget
        createFallbackWidget: function () {
            const container = document.getElementById('chat-widget-container');
            if (!container) return;

            container.innerHTML = `
                <div class="chat-widget">
                    <div id="chat-widget-window" class="chat-widget-window">
                        <div class="chat-widget-header">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span>Chat Support</span>
                                <button onclick="ChatWidget.toggleWidget()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">×</button>
                            </div>
                        </div>
                        <div class="chat-widget-body">
                            <div style="text-align: center; padding: 20px; color: #666;">
                                <p>👋 Hello! How can we help you today?</p>
                                <p><small>This is a demo widget. Send a message to test!</small></p>
                            </div>
                        </div>
                        <div class="chat-widget-input">
                            <input type="text" id="chat-input" placeholder="Type your message..." onkeypress="if(event.key==='Enter') ChatWidget.sendFallbackMessage()">
                            <button onclick="ChatWidget.sendFallbackMessage()">Send</button>
                        </div>
                    </div>
                    <button class="chat-widget-button" onclick="ChatWidget.toggleWidget()">
                        💬
                    </button>
                </div>
            `;

            container.style.pointerEvents = 'auto';
            this.log('Fallback widget created with inline functionality');
        },

        // Toggle widget visibility
        toggleWidget: function () {
            const window = document.getElementById('chat-widget-window');
            if (window) {
                window.style.display = window.style.display === 'flex' ? 'none' : 'flex';
            }
        },

        // Callback for when widget signals it's ready
        onWidgetReady: function () {
            this.log('Widget signaled ready, mounting...');
            this.mountWidget();
        },

        // Mount the widget
        mountWidget: function () {
            if (typeof Vue === 'undefined') {
                console.error('ChatWidget: Vue is not loaded');
                return;
            }

            const container = document.getElementById('chat-widget-container');
            if (!container) {
                console.error('ChatWidget: Container not found');
                return;
            }

            // Enable pointer events on the widget
            container.style.pointerEvents = 'auto';

            // Create Vue app instance
            const { createApp } = Vue;
            const app = createApp({
                template: `
              <ChatWidget 
                :api-key="apiKey" 
                :api-url="apiUrl"
                :primary-color="primaryColor"
                :animations="animations"
                :design="design"
              />
            `,
                data() {
                    return {
                        apiKey: ChatWidget.config.apiKey,
                        apiUrl: ChatWidget.config.apiUrl,
                        primaryColor: ChatWidget.config.primaryColor,
                        animations: ChatWidget.config.animations || {},
                        design: ChatWidget.config.design || {}
                    };
                }
            });

            // Register the ChatWidget component
            if (window.ChatWidgetComponent) {
                app.component('ChatWidget', window.ChatWidgetComponent);
                app.mount('#chat-widget-container');
                this.log('Widget mounted successfully');
            } else {
                console.error('ChatWidget: Component not found');
            }
        },

        // Send message in fallback mode
        sendFallbackMessage: function () {
            const input = document.getElementById('chat-input');
            const body = document.querySelector('.chat-widget-body');

            if (!input || !body || !input.value.trim()) return;

            // Add user message
            const userMessage = document.createElement('div');
            userMessage.style.cssText = 'margin: 8px 0; padding: 8px 12px; background: #f0f0f0; border-radius: 8px; margin-left: 20%; text-align: right;';
            userMessage.textContent = input.value.trim();
            body.appendChild(userMessage);

            // Clear input
            const messageText = input.value.trim();
            input.value = '';

            // Add bot response after a short delay
            setTimeout(() => {
                const botMessage = document.createElement('div');
                botMessage.style.cssText = 'margin: 8px 0; padding: 8px 12px; background: ' + this.config.primaryColor + '; color: white; border-radius: 8px; margin-right: 20%;';
                botMessage.innerHTML = `Thanks for your message: "${messageText}"<br><br>For full chat functionality, please serve this page over HTTP to connect to our support team.`;
                body.appendChild(botMessage);

                // Scroll to bottom
                body.scrollTop = body.scrollHeight;
            }, 500);

            // Scroll to bottom
            body.scrollTop = body.scrollHeight;
        },

        // Show the widget
        show: function () {
            const container = document.getElementById('chat-widget-container');
            if (container) {
                container.style.display = 'block';
            }
        },

        // Hide the widget
        hide: function () {
            const container = document.getElementById('chat-widget-container');
            if (container) {
                container.style.display = 'none';
            }
        },

        // Open the chat
        openChat: function () {
            // This would trigger the widget to open
            const event = new CustomEvent('chatwidget:open');
            document.dispatchEvent(event);
        },

        // Close the chat
        closeChat: function () {
            const event = new CustomEvent('chatwidget:close');
            document.dispatchEvent(event);
        },

        // Send a message programmatically
        sendMessage: function (message) {
            const event = new CustomEvent('chatwidget:send', {
                detail: { message }
            });
            document.dispatchEvent(event);
        },

        // Set user information
        setUser: function (userInfo) {
            const event = new CustomEvent('chatwidget:setuser', {
                detail: userInfo
            });
            document.dispatchEvent(event);
        },

        // Debug logging
        log: function (...args) {
            if (this.config.debug) {
                console.log('[ChatWidget]', ...args);
            }
        }
    };

    // Auto-initialization if window.chatWidgetConfig exists
    if (typeof window.chatWidgetConfig !== 'undefined') {
        ChatWidget.init(window.chatWidgetConfig);
    }

    // Support for deferred initialization
    if (typeof window.chatWidgetQueue !== 'undefined' && Array.isArray(window.chatWidgetQueue)) {
        window.chatWidgetQueue.forEach(command => {
            if (Array.isArray(command) && command.length >= 1) {
                const method = command[0];
                const args = command.slice(1);
                if (typeof ChatWidget[method] === 'function') {
                    ChatWidget[method].apply(ChatWidget, args);
                }
            }
        });

        // Replace queue with direct calls
        window.chatWidgetQueue = {
            push: function (command) {
                if (Array.isArray(command) && command.length >= 1) {
                    const method = command[0];
                    const args = command.slice(1);
                    if (typeof ChatWidget[method] === 'function') {
                        ChatWidget[method].apply(ChatWidget, args);
                    }
                }
            }
        };
    }
})();

// Installation snippet that users can copy
window.ChatWidgetInstaller = {
    generateSnippet: function (apiKey, options = {}) {
        const config = JSON.stringify(Object.assign({ apiKey }, options), null, 2);

        return `
<!-- Chat Widget -->
<script>
  window.chatWidgetConfig = ${config};
  
  // Queue for commands before widget loads
  window.chatWidgetQueue = window.chatWidgetQueue || [];
  
  (function() {
    var script = document.createElement('script');
    script.src = '${window.location.origin}/widget/sdk.js';
    script.async = true;
    document.head.appendChild(script);
  })();
</script>
<!-- End Chat Widget -->`.trim();
    }
};

// Export for usage examples
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.ChatWidget;
}