<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Widget Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .demo-content {
            margin-bottom: 30px;
        }

        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }

        .api-key-section {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .generate-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .generate-btn:hover {
            background: #0056b3;
        }

        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Chat Widget Demo & Setup</h1>

        <div class="demo-content">
            <h2>📋 How to Use</h2>
            <p>This is a demonstration of the embedded chat widget system. Here's how to integrate it:</p>

            <div class="api-key-section">
                <h3>🔑 Step 1: Generate API Key</h3>
                <p>First, generate an API key for your merchant account:</p>
                <button id="generateApiKey" class="generate-btn">Generate API Key</button>
                <div id="apiKeyResult" style="margin-top: 10px;"></div>
            </div>

            <h3>📜 Step 2: Add Script to Your Website</h3>
            <p>Add this code to your website's HTML (before closing &lt;/body&gt; tag):</p>

            <div class="code-block" id="widgetCode">
                <pre><!-- Chat Widget -->
&lt;script&gt;
  window.chatWidgetConfig = {
    "apiKey": "your-api-key-here",
    "primaryColor": "#3B82F6",
    "position": "bottom-right",
    "debug": true
  };
  
  // Queue for commands before widget loads
  window.chatWidgetQueue = window.chatWidgetQueue || [];
  
  (function() {
    var script = document.createElement('script');
    script.src = '{{ url('/widget/sdk.js') }}';
    script.async = true;
    document.head.appendChild(script);
  })();
&lt;/script&gt;
<!-- End Chat Widget --></pre>
            </div>

            <h3>🎛️ Step 3: Configuration Options</h3>
            <div class="code-block">
                <pre>{
  "apiKey": "required - your API key",
  "apiUrl": "/api/widget - API endpoint",
  "position": "bottom-right | bottom-left | top-right | top-left",
  "primaryColor": "#3B82F6 - widget theme color",
  "debug": false - enable debug logging,
  
  // Animation Configuration
  "animations": {
    "enabled": true,
    "openSpeed": 300,
    "bounceIntensity": "normal", // none, subtle, normal, strong
    "typingAnimation": true,
    "fadeIn": true,
    "slideIn": true
  },
  
  // Design Configuration  
  "design": {
    "theme": "modern", // modern, classic, minimal, rounded
    "borderRadius": "normal", // none, small, normal, large, round
    "shadow": "normal", // none, subtle, normal, strong
    "buttonStyle": "floating", // floating, fixed, minimal
    "chatWidth": 320,
    "chatHeight": 500,
    "fontSize": "normal", // small, normal, large
    "avatarStyle": "circle", // circle, square, none
    "messageStyle": "bubbles" // bubbles, flat, outlined
  }
}</pre>
            </div>

            <!-- Live Configuration Panel -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h4>🎨 Live Configuration Demo</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div>
                        <h5>Colors & Position</h5>
                        <label>Primary Color: <input type="color" id="primaryColor" value="#3B82F6"></label><br><br>
                        <label>Position:
                            <select id="position">
                                <option value="bottom-right">Bottom Right</option>
                                <option value="bottom-left">Bottom Left</option>
                                <option value="top-right">Top Right</option>
                                <option value="top-left">Top Left</option>
                            </select>
                        </label><br><br>
                    </div>

                    <div>
                        <h5>Design Options</h5>
                        <label>Theme:
                            <select id="theme">
                                <option value="modern">Modern</option>
                                <option value="classic">Classic</option>
                                <option value="minimal">Minimal</option>
                                <option value="rounded">Rounded</option>
                            </select>
                        </label><br><br>
                        <label>Button Style:
                            <select id="buttonStyle">
                                <option value="floating">Floating</option>
                                <option value="fixed">Fixed</option>
                                <option value="minimal">Minimal</option>
                            </select>
                        </label><br><br>
                        <label>Message Style:
                            <select id="messageStyle">
                                <option value="bubbles">Bubbles</option>
                                <option value="flat">Flat</option>
                                <option value="outlined">Outlined</option>
                            </select>
                        </label><br><br>
                    </div>

                    <div>
                        <h5>Animation Settings</h5>
                        <label><input type="checkbox" id="animations" checked> Enable Animations</label><br><br>
                        <label>Bounce Intensity:
                            <select id="bounceIntensity">
                                <option value="none">None</option>
                                <option value="subtle">Subtle</option>
                                <option value="normal" selected>Normal</option>
                                <option value="strong">Strong</option>
                            </select>
                        </label><br><br>
                        <label><input type="checkbox" id="fadeIn" checked> Fade In Effect</label><br><br>
                        <label><input type="checkbox" id="slideIn" checked> Slide In Effect</label><br><br>
                    </div>
                </div>

                <button id="applyConfig"
                    style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 15px;">
                    🔄 Apply Configuration
                </button>
                <button id="resetConfig"
                    style="background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                    ↻ Reset to Default
                </button>
            </div>

            <h3>🚀 Step 4: Advanced Usage</h3>
            <p>You can control the widget programmatically:</p>
            <div class="code-block">
                <pre>// Show/hide widget
ChatWidget.show();
ChatWidget.hide();

// Open/close chat
ChatWidget.openChat();
ChatWidget.closeChat();

// Send message programmatically
ChatWidget.sendMessage('Hello from website!');

// Set user information
ChatWidget.setUser({
  name: 'John Doe',
  email: 'john@example.com'
});</pre>
            </div>

            <h3>📊 Features</h3>
            <ul>
                <li>✅ Real-time messaging</li>
                <li>✅ Typing indicators</li>
                <li>✅ Unread message badges</li>
                <li>✅ Mobile responsive</li>
                <li>✅ Customizable theme</li>
                <li>✅ Session management</li>
                <li>✅ WebSocket support</li>
                <li>✅ Easy integration</li>
            </ul>
        </div>

        <div style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px;">
            <p><strong>💡 Tip:</strong> The chat widget will appear in the bottom-right corner of this page. Try
                clicking it to test the functionality!</p>
            <p><em>Note: Make sure to run the migrations and start the WebSocket server for full functionality.</em></p>
        </div>
    </div>

    <script>
        // Generate API key functionality
        document.getElementById('generateApiKey').addEventListener('click', function() {
            // Generate a simple API key for demo
            const apiKey = 'widget_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36)
                .substring(2, 15);

            const resultDiv = document.getElementById('apiKeyResult');
            resultDiv.innerHTML = `
                <div class="status success">
                    <strong>Generated API Key:</strong><br>
                    <code style="background: white; padding: 5px; border-radius: 3px;">${apiKey}</code>
                    <br><br>
                    <small>⚠️ In production, generate API keys through your admin panel and store them securely.</small>
                </div>
            `;

            // Update the code example
            const codeBlock = document.getElementById('widgetCode');
            const updatedCode = codeBlock.innerHTML.replace('your-api-key-here', apiKey);
            codeBlock.innerHTML = updatedCode;
        });

        // Demo widget configuration
        window.chatWidgetConfig = {
            apiKey: "demo_widget_key_12345",
            primaryColor: "#3B82F6",
            position: "bottom-right",
            debug: true,
            animations: {
                enabled: true,
                openSpeed: 300,
                bounceIntensity: "normal",
                typingAnimation: true,
                fadeIn: true,
                slideIn: true
            },
            design: {
                theme: "modern",
                borderRadius: "normal",
                shadow: "normal",
                buttonStyle: "floating",
                chatWidth: 320,
                chatHeight: 500,
                fontSize: "normal",
                avatarStyle: "circle",
                messageStyle: "bubbles"
            }
        };

        // Configuration control functions
        function updateConfig() {
            const config = {
                apiKey: "demo_widget_key_12345",
                primaryColor: document.getElementById('primaryColor').value,
                position: document.getElementById('position').value,
                debug: true,
                animations: {
                    enabled: document.getElementById('animations').checked,
                    openSpeed: 300,
                    bounceIntensity: document.getElementById('bounceIntensity').value,
                    typingAnimation: true,
                    fadeIn: document.getElementById('fadeIn').checked,
                    slideIn: document.getElementById('slideIn').checked
                },
                design: {
                    theme: document.getElementById('theme').value,
                    borderRadius: "normal",
                    shadow: "normal",
                    buttonStyle: document.getElementById('buttonStyle').value,
                    chatWidth: 320,
                    chatHeight: 500,
                    fontSize: "normal",
                    avatarStyle: "circle",
                    messageStyle: document.getElementById('messageStyle').value
                }
            };

            // Update global config
            window.chatWidgetConfig = config;

            // Reinitialize widget if it exists
            if (window.ChatWidget && window.ChatWidget.init) {
                // Remove existing widget
                const container = document.getElementById('chat-widget-container');
                if (container) {
                    container.remove();
                }

                // Reinitialize with new config
                setTimeout(() => {
                    window.ChatWidget.init(config);
                }, 100);
            }
        }

        function resetConfig() {
            document.getElementById('primaryColor').value = "#3B82F6";
            document.getElementById('position').value = "bottom-right";
            document.getElementById('theme').value = "modern";
            document.getElementById('buttonStyle').value = "floating";
            document.getElementById('messageStyle').value = "bubbles";
            document.getElementById('animations').checked = true;
            document.getElementById('bounceIntensity').value = "normal";
            document.getElementById('fadeIn').checked = true;
            document.getElementById('slideIn').checked = true;

            updateConfig();
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('applyConfig').addEventListener('click', updateConfig);
            document.getElementById('resetConfig').addEventListener('click', resetConfig);
        });

        // Load widget for demo
        window.chatWidgetQueue = window.chatWidgetQueue || [];

        (function() {
            var script = document.createElement('script');
            script.src = '{{ url('/widget/sdk.js') }}';
            script.async = true;
            script.onload = function() {
                console.log('Chat widget loaded for demo!');
            };
            document.head.appendChild(script);
        })();
    </script>
</body>

</html>
