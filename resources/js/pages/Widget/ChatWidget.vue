<template>
  <div class="chat-widget">
    <!-- Chat Button -->
    <div
      v-if="!isOpen"
      @click="toggleChat"
      class="chat-button fixed bottom-6 right-6 z-50 w-16 h-16 bg-blue-500 hover:bg-blue-600 rounded-full shadow-lg cursor-pointer flex items-center justify-center transition-all duration-300 hover:scale-110"
    >
      <svg
        class="w-8 h-8 text-white"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
        />
      </svg>
      <!-- Unread badge -->
      <div
        v-if="unreadCount > 0"
        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </div>
    </div>

    <!-- Chat Modal -->
    <div
      v-if="isOpen"
      class="chat-modal fixed bottom-6 right-6 z-50 w-80 h-96 bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden"
    >
      <!-- Header -->
      <div class="bg-blue-500 text-white p-4 flex items-center justify-between">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center mr-3">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </div>
          <div>
            <h3 class="font-medium">Support</h3>
            <p class="text-xs text-blue-100">{{ supportAgent.name || 'Support Team' }}</p>
          </div>
        </div>
        <button @click="toggleChat" class="text-white/80 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Messages Area -->
      <div
        ref="messagesContainer"
        class="flex-1 overflow-y-auto p-3 bg-gray-50"
        @scroll="handleScroll"
      >
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="text-center py-6">
          <div class="w-12 h-12 mx-auto mb-3 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <p class="text-gray-600 text-sm mb-2">Welcome to Support!</p>
          <p class="text-gray-500 text-xs">Send us a message and we'll get back to you shortly.</p>
        </div>

        <!-- Messages -->
        <div class="space-y-3">
          <div
            v-for="message in messages"
            :key="message.id"
            class="flex"
            :class="message.is_from_user ? 'justify-end' : 'justify-start'"
          >
            <!-- Support Avatar -->
            <div
              v-if="!message.is_from_user"
              class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs mr-2 mt-1 shrink-0"
            >
              {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
            </div>

            <!-- Message Bubble -->
            <div
              class="max-w-xs px-3 py-2 rounded-lg text-sm"
              :class="
                message.is_from_user
                  ? 'bg-blue-500 text-white rounded-br-sm'
                  : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm'
              "
            >
              <p>{{ message.message }}</p>
              <div class="flex justify-end mt-1">
                <span
                  class="text-xs"
                  :class="
                    message.is_from_user 
                      ? 'text-blue-100' 
                      : 'text-gray-500'
                  "
                >
                  {{ formatTime(message.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="flex items-center mt-3">
          <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs mr-2 shrink-0">
            {{ (supportAgent.name || 'S').charAt(0).toUpperCase() }}
          </div>
          <div class="bg-white border border-gray-200 rounded-lg rounded-bl-sm px-3 py-2">
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
              <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-3 bg-white border-t border-gray-200">
        <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
          <input
            v-model="newMessage"
            type="text"
            placeholder="Type your message..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :disabled="sending"
            @keydown="handleTyping"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || sending"
            class="w-8 h-8 bg-blue-500 hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center transition-colors"
          >
            <svg
              v-if="!sending"
              class="w-4 h-4 text-white"
              fill="currentColor"
              viewBox="0 0 24 24"
            >
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
            </svg>
            <div
              v-else
              class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"
            ></div>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

// Props
const props = defineProps({
  apiKey: {
    type: String,
    required: true
  },
  apiUrl: {
    type: String,
    default: '/api/widget'
  }
});

// Reactive data
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

// Toggle chat modal
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

// Initialize chat session
const initializeSession = async () => {
  try {
    const response = await axios.post(`${props.apiUrl}/session`, {
      api_key: props.apiKey
    });
        
    sessionId.value = response.data.session_id;
    messages.value = response.data.messages || [];
    supportAgent.value = response.data.agent || { name: 'Support Team' };
    
    // Set up real-time connection
    setupWebSocket();
    
    setTimeout(() => scrollToBottom(), 100);
  } catch (error) {
    console.error('Failed to initialize session:', error);
  }
};

// Send message
const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value) return;
  
  const message = newMessage.value.trim();
  newMessage.value = '';
  sending.value = true;
  
  // Add user message to UI immediately
  const userMessage = {
    id: Date.now(),
    message: message,
    is_from_user: true,
    created_at: new Date().toISOString()
  };
  
  messages.value.push(userMessage);
  scrollToBottom();
  
  try {
    const response = await axios.post(`${props.apiUrl}/message`, {
      api_key: props.apiKey,
      session_id: sessionId.value,
      message: message
    });
    
    // Update message with server response
    const messageIndex = messages.value.findIndex(m => m.id === userMessage.id);
    if (messageIndex !== -1) {
      messages.value[messageIndex] = response.data.message;
    }
    
  } catch (error) {
    console.error('Failed to send message:', error);
  } finally {
    sending.value = false;
  }
};

// Handle typing indicator
const handleTyping = () => {
  const now = Date.now();
  lastTypingTime.value = now;
  
  // Clear existing timeout
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
  
  // Send typing indicator
  if (sessionId.value) {
    axios.post(`${props.apiUrl}/typing`, {
      api_key: props.apiKey,
      session_id: sessionId.value,
      typing: true
    }).catch(console.error);
  }
  
  // Stop typing after 2 seconds
  typingTimeout.value = setTimeout(() => {
    if (sessionId.value) {
      axios.post(`${props.apiUrl}/typing`, {
        api_key: props.apiKey,
        session_id: sessionId.value,
        typing: false
      }).catch(console.error);
    }
  }, 2000);
};

// Format time
const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  return date.toLocaleTimeString('en-US', { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false 
  });
};

// Scroll to bottom
const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Handle scroll
const handleScroll = () => {
  // Could implement "mark as read" functionality here
};

// WebSocket setup
let websocketConnection = null;

const setupWebSocket = () => {
  if (!sessionId.value) return;
  
  try {
    // This would connect to your WebSocket server
    // For now, we'll simulate with periodic polling
    pollForMessages();
  } catch (error) {
    console.error('WebSocket setup failed:', error);
  }
};

// Poll for new messages (fallback for WebSocket)
const pollForMessages = () => {
  if (!sessionId.value || !isOpen.value) return;
  
  setInterval(async () => {
    try {
      const response = await axios.get(`${props.apiUrl}/messages/${sessionId.value}`, {
        params: { api_key: props.apiKey }
      });
      
      const newMessages = response.data.messages || [];
      const currentMessageIds = messages.value.map(m => m.id);
      const trulyNewMessages = newMessages.filter(m => !currentMessageIds.includes(m.id));
      
      if (trulyNewMessages.length > 0) {
        messages.value.push(...trulyNewMessages);
        scrollToBottom();
        
        // Update unread count if chat is closed
        if (!isOpen.value) {
          unreadCount.value += trulyNewMessages.filter(m => !m.is_from_user).length;
        }
      }
      
      // Update typing status
      isTyping.value = response.data.agent_typing || false;
      
    } catch (error) {
      console.error('Failed to poll messages:', error);
    }
  }, 2000); // Poll every 2 seconds
};

// Cleanup
onUnmounted(() => {
  if (websocketConnection) {
    websocketConnection.close();
  }
  if (typingTimeout.value) {
    clearTimeout(typingTimeout.value);
  }
});
</script>

<style scoped>
.chat-widget {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.chat-button {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.chat-modal {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.animate-bounce {
  animation: bounce 1.4s infinite;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-6px);
  }
}
</style>