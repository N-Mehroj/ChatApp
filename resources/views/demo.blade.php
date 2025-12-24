<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App Vue.js Demo - User Identification</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Vue 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Axios -->
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button.active {
            background-color: #3B82F6;
            color: white;
        }
        .message-animation {
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Demo Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Chat App Vue.js Demo</h1>
            <p class="text-gray-600 mb-4">Vue.js bilan user identifikatsiya tizimi - kim yozganligini aniqlab berish</p>
            @auth
                <div class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-full">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">{{ auth()->user()->name }} ({{ auth()->user()->role }}) sifatida kirgansiz</span>
                </div>
            @else
                <div class="inline-flex items-center space-x-2 bg-orange-100 text-orange-800 px-4 py-2 rounded-full">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">Demo foydalanuvchi sifatida ko'ringansiz</span>
                </div>
            @endauth
        </div>
        <!-- Tabs -->
        <div class="mb-6">
            <div class="flex space-x-1 bg-white rounded-lg p-1 shadow-md">
                <button class="tab-button active px-6 py-2 rounded-md font-medium text-sm transition-colors" onclick="showTab('demo')">
                    Live Demo
                </button>
                <button class="tab-button px-6 py-2 rounded-md font-medium text-sm transition-colors" onclick="showTab('vue-code')">
                    Vue.js Code
                </button>
                <button class="tab-button px-6 py-2 rounded-md font-medium text-sm transition-colors" onclick="showTab('backend')">
                    Backend Integration
                </button>
                <button class="tab-button px-6 py-2 rounded-md font-medium text-sm transition-colors" onclick="showTab('websocket')">
                    WebSocket Events
                </button>
            </div>
        </div>

        <!-- Tab Contents -->
        
        <!-- Live Demo Tab -->
        <div id="demo" class="tab-content active">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Live Chat Demo with User Identification</h2>
                <p class="text-gray-600 mb-6">Haqiqiy chat interfeysi - kim yozganligini aniqlab ko'rsatadi</p>
                
                <div id="chat-app" class="border rounded-lg">
                    <!-- Chat Header -->
                    <div class="p-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white border-b rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg">Support Chat</h3>
                                <p class="text-blue-100 text-sm">Haqiqiy vaqtda xabar almashish</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-sm text-blue-100">Online</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current User Info -->
                    <div class="p-3 bg-blue-50 border-b text-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">{{ auth()->user()->name ?? 'Demo User' }}</span>
                                <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
                                    {{ ucfirst(auth()->user()->role ?? 'user') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Container -->
                    <div class="h-96 overflow-y-auto p-4 bg-gray-50" ref="messagesContainer">
                        <div v-for="message in messages" :key="message.id" class="mb-4 message-animation">
                            <div 
                                :class="[
                                    'max-w-xs lg:max-w-md p-3 rounded-lg relative',
                                    message.sender_id === currentUser.id ? 
                                    'bg-blue-500 text-white ml-auto rounded-br-none' : 
                                    'bg-white text-gray-800 shadow-md border border-gray-200 rounded-bl-none'
                                ]"
                            >
                                <!-- User Name & Role (only for received messages) -->
                                <div v-if="message.sender_id !== currentUser.id" class="text-xs mb-2 pb-2 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-5 h-5 bg-gray-300 rounded-full flex items-center justify-center text-gray-600 font-bold text-xs">
                                                {{ message.sender_name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="font-medium text-gray-600">{{ message.sender_name }}</span>
                                        </div>
                                        <span 
                                            v-if="message.from_operator"
                                            class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            Support
                                        </span>
                                        <span 
                                            v-else
                                            class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs capitalize"
                                        >
                                            {{ message.sender_role }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Message Content -->
                                <div class="text-sm leading-relaxed">{{ message.content }}</div>
                                
                                <!-- Time & Status -->
                                <div 
                                    :class="[
                                        'text-xs mt-2 flex items-center',
                                        message.sender_id === currentUser.id ? 
                                        'text-blue-100 justify-end' : 
                                        'text-gray-400 justify-start'
                                    ]"
                                >
                                    <span>{{ formatTime(message.created_at) }}</span>
                                    <!-- Read receipt for sent messages -->
                                    <div v-if="message.sender_id === currentUser.id" class="ml-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Typing Indicator -->
                        <div v-if="isTyping" class="mb-4 flex items-center space-x-2 text-gray-500">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                            <span class="text-sm">{{ typingUser }} yozmoqda...</span>
                        </div>
                    </div>
                    
                    <!-- Message Input -->
                    <div class="p-4 border-t bg-white rounded-b-lg">
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1 flex space-x-2">
                                <input 
                                    v-model="newMessage"
                                    @keyup.enter="sendMessage"
                                    @input="handleTyping"
                                    type="text" 
                                    placeholder="Xabar yozing... (Enter = yuborish)"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                <button 
                                    @click="sendMessage"
                                    :disabled="!newMessage.trim() || isSending"
                                    class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center space-x-2"
                                >
                                    <svg v-if="!isSending" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    <span>{{ isSending ? 'Yuborilmoqda...' : 'Yuborish' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Demo Controls -->
                <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-3">Demo Controls</h4>
                    <div class="flex flex-wrap gap-2">
                        <button @click="simulateOperatorMessage" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm">
                            Support javobini simulatsiya qiling
                        </button>
                        <button @click="simulateTyping" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm">
                            Yozish jarayonini ko'rsating
                        </button>
                        <button @click="clearMessages" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm">
                            Xabarlarni tozalash
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        .message-content {
            max-width: 70%;
        }
        
        .message-bubble {
            padding: 8px 12px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message.own .message-bubble {
            background: #3b82f6;
            color: white;
        }
        
        .message-info {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .message-input {
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
        }
        
        .message-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 20px;
            outline: none;
        }
        
        .message-input input:focus {
            border-color: #3b82f6;
        }
        
        .send-btn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .send-btn:hover {
            background: #2563eb;
        }
        
        .code-block {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 16px;
            margin: 16px 0;
            overflow-x: auto;
        }
        
        .code-block pre {
            margin: 0;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            margin-left: 4px;
        }
        
        .offline {
            background: #6b7280;
        }

        .loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container">
            <h1>Vue.js Chat Demo - User Identification</h1>
            
            <div class="tabs">
                <div 
                    class="tab" 
                    :class="{ active: activeTab === 'demo' }"
                    @click="activeTab = 'demo'"
                >
                    Live Demo
                </div>
                <div 
                    class="tab" 
                    :class="{ active: activeTab === 'vue' }"
                    @click="activeTab = 'vue'"
                >
                    Vue.js Code
                </div>
                <div 
                    class="tab" 
                    :class="{ active: activeTab === 'backend' }"
                    @click="activeTab = 'backend'"
                >
                    Backend Integration
                </div>
            </div>
            
            <div class="tab-content">
                <!-- Live Demo Tab -->
                <div v-show="activeTab === 'demo'">
                    <h2>Real-time Chat with User Identification</h2>
                    <p><strong>Current User:</strong> {{ currentUser.name }} ({{ currentUser.role }})</p>
                    
                    <div class="chat-container">
                        <div class="user-list">
                            <h3 style="margin: 0 0 16px 0; font-size: 14px;">Online Users</h3>
                            <div 
                                v-for="user in users" 
                                :key="user.id"
                                class="user-item"
                                :class="{ active: selectedUser?.id === user.id }"
                                @click="selectUser(user)"
                            >
                                <div class="user-avatar">{{ user.name.charAt(0) }}</div>
                                <div>
                                    <div style="font-size: 13px; font-weight: 500;">{{ user.name }}</div>
                                    <div style="font-size: 11px; opacity: 0.7;">{{ user.role }}</div>
                                </div>
                                <span class="status-indicator" :class="{ offline: !user.online }"></span>
                            </div>
                        </div>
                        
                        <div class="chat-area">
                            <div class="chat-header" v-if="selectedUser">
                                <h3 style="margin: 0; font-size: 16px;">
                                    Chat with {{ selectedUser.name }}
                                    <span class="status-indicator" :class="{ offline: !selectedUser.online }"></span>
                                </h3>
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                                    Role: {{ selectedUser.role }} • 
                                    {{ selectedUser.online ? 'Online' : 'Last seen: ' + formatTime(selectedUser.last_seen) }}
                                </p>
                            </div>
                            
                            <div class="messages" ref="messagesContainer" v-if="selectedUser">
                                <div v-if="loading" class="loading" style="justify-content: center; margin: 20px 0;">
                                    <div class="spinner"></div>
                                    <span>Loading messages...</span>
                                </div>
                                
                                <div v-else>
                                    <div 
                                        v-for="message in messages" 
                                        :key="message.id"
                                        class="message"
                                        :class="{ own: message.user_id === currentUser.id }"
                                    >
                                        <div class="message-avatar">
                                            {{ getUserById(message.user_id)?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div class="message-content">
                                            <div class="message-bubble">
                                                {{ message.message }}
                                            </div>
                                            <div class="message-info">
                                                {{ getUserById(message.user_id)?.name || 'Unknown' }} • 
                                                {{ formatTime(message.created_at) }}
                                                <span v-if="message.from_operator" style="color: #3b82f6;">• Operator</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="message-input" v-if="selectedUser">
                                <input 
                                    v-model="newMessage"
                                    @keyup.enter="sendMessage"
                                    placeholder="Type your message..."
                                    :disabled="sending"
                                />
                                <button 
                                    class="send-btn" 
                                    @click="sendMessage"
                                    :disabled="!newMessage.trim() || sending"
                                >
                                    <div v-if="sending" class="spinner" style="width: 16px; height: 16px;"></div>
                                    <span v-else>→</span>
                                </button>
                            </div>
                            
                            <div v-else style="display: flex; align-items: center; justify-content: center; height: 100%; color: #6b7280;">
                                Select a user to start chatting
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vue.js Code Tab -->
                <div v-show="activeTab === 'vue'">
                    <h2>Vue.js Implementation</h2>
                    
                    <h3>1. User Identification Setup</h3>
                    <div class="code-block">
                        <pre><code>// Vue.js Component Setup
const { createApp, ref, onMounted } = Vue;

createApp({
  setup() {
    // Current user from Laravel backend
    const currentUser = ref(@json(auth()->user()));
    const users = ref([]);
    const messages = ref([]);
    const selectedUser = ref(null);
    const newMessage = ref('');
    const loading = ref(false);
    const sending = ref(false);

    // Get user by ID helper
    const getUserById = (userId) => {
      return [...users.value, currentUser.value].find(u => u.id === userId);
    };

    return {
      currentUser,
      users,
      messages,
      selectedUser,
      newMessage,
      loading,
      sending,
      getUserById
    };
  }
}).mount('#app');</code></pre>
                    </div>

                    <h3>2. Message Sending with User Info</h3>
                    <div class="code-block">
                        <pre><code>// Send Message Function
const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return;

  sending.value = true;
  const messageText = newMessage.value;
  newMessage.value = '';

  try {
    const response = await axios.post('/api/chat/send', {
      recipient_id: selectedUser.value.id,
      message: messageText,
    }, {
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('token'),
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });

    // Add message to UI immediately with user info
    const messageData = response.data.message;
    messages.value.push({
      ...messageData,
      user_id: currentUser.value.id,
      from_operator: currentUser.value.role === 'support'
    });

    scrollToBottom();
  } catch (error) {
    console.error('Error sending message:', error);
    newMessage.value = messageText; // Restore message
  } finally {
    sending.value = false;
  }
};</code></pre>
                    </div>

                    <h3>3. Real-time Message Receiving</h3>
                    <div class="code-block">
                        <pre><code>// WebSocket Setup for Real-time Messages
const setupWebSocket = () => {
  if (window.Echo) {
    window.Echo.private(`chat.${selectedUser.value.id}`)
      .listen('.message.sent', (event) => {
        const messageData = event.message;
        
        // Add sender information
        const messageWithUser = {
          ...messageData,
          sender_name: getUserById(messageData.user_id)?.name || 'Unknown',
          sender_role: getUserById(messageData.user_id)?.role || 'user'
        };
        
        messages.value.push(messageWithUser);
        scrollToBottom();
      });
  }
};

// Format timestamp
const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  
  if (diff < 60000) return 'now';
  if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
  if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
  return date.toLocaleDateString();
};</code></pre>
                    </div>
                </div>
                
                <!-- Backend Integration Tab -->
                <div v-show="activeTab === 'backend'">
                    <h2>Backend Integration</h2>
                    
                    <h3>1. Message Controller with User Tracking</h3>
                    <div class="code-block">
                        <pre><code>// ChatController.php
public function sendMessage(Request $request, Chat $chat): JsonResponse
{
    $request->validate([
        'message' => 'required|string|max:1000',
    ]);

    $user = Auth::user();

    // Create message with user identification
    $message = ChatMessage::create([
        'chat_id' => $chat->id,
        'user_id' => $user->id,
        'message' => $request->message,
        'from_operator' => $user->isSupport(), // Track if from operator
    ]);

    // Load user relationship
    $message->load('user');

    // Broadcast with complete user info
    broadcast(new MessageSent($message));

    return response()->json([
        'message' => $message,
        'sender' => [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'avatar' => $user->avatar_url
        ]
    ]);
}</code></pre>
                    </div>

                    <h3>2. User Model Enhancement</h3>
                    <div class="code-block">
                        <pre><code>// User.php Model
class User extends Authenticatable
{
    // Add user identification helpers
    public function isSupport(): bool
    {
        return in_array($this->role, [UserRole::Support, UserRole::Admin]);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->first_name && $this->last_name 
            ? "{$this->first_name} {$this->last_name}"
            : $this->name ?? $this->email;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? Storage::url($this->avatar)
            : "https://ui-avatars.com/api/?name={$this->display_name}&background=3b82f6&color=fff";
    }

    public function getOnlineStatusAttribute(): bool
    {
        return $this->last_activity && 
               $this->last_activity->gt(now()->subMinutes(5));
    }
}</code></pre>
                    </div>

                    <h3>3. API Route with User Context</h3>
                    <div class="code-block">
                        <pre><code>// api.php Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Get current user with role info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load(['roles']),
            'permissions' => $request->user()->getAllPermissions()
        ]);
    });

    // Chat routes with user identification
    Route::prefix('chat')->group(function () {
        Route::get('/users', [ChatController::class, 'getUsers']);
        Route::post('/send', [ChatController::class, 'sendMessage']);
        Route::get('/{chat}/messages', [ChatController::class, 'getMessages']);
    });
});</code></pre>
                    </div>

                    <h3>4. WebSocket Event with User Data</h3>
                    <div class="code-block">
                        <pre><code>// MessageSent.php Event
class MessageSent implements ShouldBroadcastNow
{
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'message' => $this->message->message,
                'created_at' => $this->message->created_at,
                'user_id' => $this->message->user_id,
                'from_operator' => $this->message->from_operator,
                
                // Include complete user info
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->display_name,
                    'role' => $this->message->user->role,
                    'avatar' => $this->message->user->avatar_url,
                    'online' => $this->message->user->online_status
                ]
            ]
        ];
    }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const { createApp, ref, onMounted, nextTick } = Vue;

        createApp({
            setup() {
                const activeTab = ref('demo');
                const currentUser = ref({
                    id: {{ auth()->id() ?? 1 }},
                    name: '{{ auth()->user()->name ?? "Demo User" }}',
                    role: '{{ auth()->user()->role ?? "support" }}'
                });
                
                const users = ref([
                    { id: 1, name: 'John Doe', role: 'user', online: true, last_seen: new Date() },
                    { id: 2, name: 'Jane Smith', role: 'support', online: true, last_seen: new Date() },
                    { id: 3, name: 'Bob Wilson', role: 'admin', online: false, last_seen: new Date(Date.now() - 300000) }
                ]);
                
                const selectedUser = ref(null);
                const messages = ref([]);
                const newMessage = ref('');
                const loading = ref(false);
                const sending = ref(false);
                const messagesContainer = ref(null);

                const selectUser = async (user) => {
                    selectedUser.value = user;
                    await loadMessages(user.id);
                };

                const loadMessages = async (userId) => {
                    loading.value = true;
                    try {
                        // Simulate API call
                        await new Promise(resolve => setTimeout(resolve, 500));
                        
                        // Demo messages
                        messages.value = [
                            {
                                id: 1,
                                user_id: userId,
                                message: 'Hello! How can I help you today?',
                                created_at: new Date(Date.now() - 60000),
                                from_operator: users.value.find(u => u.id === userId)?.role === 'support'
                            },
                            {
                                id: 2,
                                user_id: currentUser.value.id,
                                message: 'I have a question about my account.',
                                created_at: new Date(Date.now() - 30000),
                                from_operator: currentUser.value.role === 'support'
                            }
                        ];
                        
                        await nextTick();
                        scrollToBottom();
                    } catch (error) {
                        console.error('Error loading messages:', error);
                    } finally {
                        loading.value = false;
                    }
                };

                const sendMessage = async () => {
                    if (!newMessage.value.trim() || sending.value || !selectedUser.value) return;

                    sending.value = true;
                    const messageText = newMessage.value;
                    newMessage.value = '';

                    try {
                        // Simulate API call
                        await new Promise(resolve => setTimeout(resolve, 300));
                        
                        const newMsg = {
                            id: Date.now(),
                            user_id: currentUser.value.id,
                            message: messageText,
                            created_at: new Date(),
                            from_operator: currentUser.value.role === 'support'
                        };
                        
                        messages.value.push(newMsg);
                        await nextTick();
                        scrollToBottom();
                    } catch (error) {
                        console.error('Error sending message:', error);
                        newMessage.value = messageText;
                    } finally {
                        sending.value = false;
                    }
                };

                const getUserById = (userId) => {
                    return [...users.value, currentUser.value].find(u => u.id === userId);
                };

                const formatTime = (timestamp) => {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diff = now - date;
                    
                    if (diff < 60000) return 'now';
                    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
                    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
                    return date.toLocaleDateString();
                };

                const scrollToBottom = () => {
                    if (messagesContainer.value) {
                        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
                    }
                };

                onMounted(() => {
                    // Setup CSRF token for axios
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                });

                return {
                    activeTab,
                    currentUser,
                    users,
                    selectedUser,
                    messages,
                    newMessage,
                    loading,
                    sending,
                    messagesContainer,
                    selectUser,
                    sendMessage,
                    getUserById,
                    formatTime
                };
            }
        }).mount('#app');
    </script>
</body>
</html>