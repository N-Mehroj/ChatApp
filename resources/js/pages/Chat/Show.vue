<template>
  <div class="min-h-screen bg-background">
    <div class="max-w-6xl mx-auto p-6">
      <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        <div
          class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-4 flex items-center space-x-3"
        >
          <button
            @click="goBack"
            class="text-white hover:text-blue-200 transition-colors p-1 rounded hover:bg-white/10"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
              ></path>
            </svg>
          </button>
          <div>
            <h1 class="text-xl font-semibold">
              {{ getUserDisplayName(chat.user) }}
            </h1>
            <p class="text-blue-100 text-sm">Suhbat</p>
          </div>
        </div>

        <div class="flex flex-col h-[600px]">
          <!-- Messages -->
          <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div
              v-for="message in messages"
              :key="message.id"
              class="flex"
              :class="
                message.user_id === currentUser.id ? 'justify-end' : 'justify-start'
              "
            >
              <div
                class="max-w-xs lg:max-w-md px-3 py-2 rounded-lg text-sm"
                :class="
                  message.user_id === currentUser.id
                    ? 'bg-blue-600 text-white'
                    : 'bg-muted text-foreground'
                "
              >
                <div
                  v-if="message.user_id !== currentUser.id"
                  class="text-xs font-medium mb-1 opacity-70"
                >
                  {{ getUserDisplayName(message.user) }}
                </div>
                <p>{{ message.message }}</p>
                <p class="text-xs mt-1 opacity-70">
                  {{ formatTime(message.created_at) }}
                </p>
              </div>
            </div>

            <div
              v-if="messages.length === 0"
              class="text-center text-muted-foreground py-8"
            >
              <div
                class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4"
              >
                <svg
                  class="w-8 h-8"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  ></path>
                </svg>
              </div>
              <p class="text-sm">Hali xabarlar yo'q. Birinchi xabarni yuboring!</p>
            </div>
          </div>

          <!-- Message Input -->
          <div class="p-4 border-t border-border">
            <form @submit.prevent="sendMessage" class="flex space-x-3">
              <input
                v-model="newMessage"
                type="text"
                placeholder="Xabar yozing..."
                class="flex-1 bg-background border border-input rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="sending"
                ref="messageInput"
              />
              <button
                type="submit"
                :disabled="!newMessage.trim() || sending"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium flex items-center space-x-1"
              >
                <svg
                  v-if="sending"
                  class="w-4 h-4 animate-spin"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <svg
                  v-else
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                  ></path>
                </svg>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- User list for starting new chats -->
      <div
        v-if="showUserList"
        class="mt-4 bg-card rounded-lg shadow-sm border border-border p-4"
      >
        <h3 class="text-lg font-medium mb-4 text-foreground">Boshqa foydalanuvchilar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div
            v-for="user in availableUsers"
            :key="user.id"
            @click="startNewChat(user)"
            class="p-3 border border-border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
          >
            <p class="font-medium text-foreground text-sm">
              {{ getUserDisplayName(user) }}
            </p>
            <p class="text-muted-foreground text-xs">{{ user.email }}</p>
          </div>
        </div>
        <button
          @click="showUserList = false"
          class="mt-4 w-full bg-muted text-foreground px-4 py-2 rounded-lg hover:bg-muted/80 transition-colors text-sm"
        >
          Yopish
        </button>
      </div>

      <!-- Floating action button to show users -->
      <button
        @click="showUserList = !showUserList"
        class="fixed bottom-6 right-6 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          ></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
}

interface ChatMessage {
  id: number;
  chat_id: number;
  user_id: number;
  message: string;
  from_operator: boolean;
  created_at: string;
  user: User;
}

interface Chat {
  id: number;
  user_id: number;
  is_new: boolean;
  created_at: string;
  updated_at: string;
  user: User;
  last_message?: ChatMessage;
}

interface Props {
  chat: Chat;
  messages: ChatMessage[];
  currentUser: User;
  users: User[];
}

const props = defineProps<Props>();

const messages = ref([...props.messages]);
const newMessage = ref("");
const sending = ref(false);
const showUserList = ref(false);
const messagesContainer = ref<HTMLElement>();
const messageInput = ref<HTMLInputElement>();
const echo = ref<Echo | null>(null);

const currentUser = computed(() => props.currentUser);
const chat = computed(() => props.chat);
const availableUsers = computed(() =>
  props.users.filter((u) => u.id !== currentUser.value.id)
);

onMounted(() => {
  initializeEcho();
  joinChatChannel();
  scrollToBottom();
  messageInput.value?.focus();
});

onUnmounted(() => {
  if (echo.value) {
    echo.value.disconnect();
  }
});

function initializeEcho() {
  console.log("=== DEBUG: Initializing Echo for chat ===");
  console.log("=== DEBUG: Reverb config ===", {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT,
    scheme: import.meta.env.VITE_REVERB_SCHEME,
  });

  // @ts-ignore
  window.Pusher = Pusher;

  echo.value = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
  });

  console.log("=== DEBUG: Echo initialized ===", echo.value);
}

function joinChatChannel() {
  if (echo.value && chat.value) {
    console.log("=== DEBUG: Joining chat channel ===", `chat.${chat.value.id}`);

    // Listen on both private channel (for authenticated users) and public channel (for widgets)
    echo.value
      .private(`chat.${chat.value.id}`)
      .listen("message.sent", (e: { message: ChatMessage }) => {
        console.log("=== DEBUG: Message received via private channel ===", e);
        messages.value.push(e.message);
        nextTick(() => scrollToBottom());
      })
      .error((error: any) => {
        console.error("=== DEBUG: Private channel error ===", error);
        // Fallback to public channel if private fails
        console.log("=== DEBUG: Falling back to public channel ===");
        echo.value
          ?.channel(`chat.${chat.value.id}`)
          .listen("message.sent", (e: { message: ChatMessage }) => {
            console.log("=== DEBUG: Message received via public channel ===", e);
            messages.value.push(e.message);
            nextTick(() => scrollToBottom());
          });
      });

    // Also listen on public channel for widget messages
    echo.value
      .channel(`chat.${chat.value.id}`)
      .listen("message.sent", (e: { message: ChatMessage }) => {
        console.log("=== DEBUG: Message received via public channel ===", e);
        // Check if message already exists to avoid duplicates
        const existingMessage = messages.value.find((msg) => msg.id === e.message.id);
        if (!existingMessage) {
          messages.value.push(e.message);
          nextTick(() => scrollToBottom());
        }
      });
  }
}

async function sendMessage() {
  if (!newMessage.value.trim() || sending.value) {
    return;
  }

  sending.value = true;
  const messageText = newMessage.value;
  newMessage.value = "";

  try {
    let response;

    // Check if this is a widget chat
    if (chat.value.widget_session_id) {
      // Widget chat - use widget reply endpoint
      const session = chat.value.widget_session; // Assuming we have this data
      response = await fetch(
        `/api/widget/session/${session?.session_id || "unknown"}/reply`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
              document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || "",
            Authorization: "Bearer " + (localStorage.getItem("auth_token") || ""),
          },
          body: JSON.stringify({
            message: messageText,
          }),
        }
      );
    } else {
      // Regular chat - use Inertia router for automatic CSRF handling
      router.post(
        `/chat/${chat.value.id}/message`,
        {
          message: messageText,
        },
        {
          preserveState: true,
          preserveScroll: true,
          onSuccess: (page) => {
            console.log("Message sent successfully:", page.props);
            // Message will be added via WebSocket, no need to add manually
          },
          onError: (errors) => {
            console.error("Error sending message:", errors);
            newMessage.value = messageText;
          },
        }
      );

      // Return early for Inertia request as it's handled differently
      return;
    }

    // Only handle response for widget chats (regular chats use Inertia)
    if (chat.value.widget_session_id && response) {
      const data = await response.json();

      if (response.ok) {
        // Add message immediately for sender
        messages.value.push(data.message);
        await nextTick();
        scrollToBottom();
      } else {
        console.error("Error sending message:", data);
        newMessage.value = messageText;
      }
    }
  } catch (error) {
    console.error("Error sending message:", error);
    newMessage.value = messageText;
  } finally {
    sending.value = false;
    messageInput.value?.focus();
  }
}

async function startNewChat(user: User) {
  router.post(
    "/chat",
    {
      user_id: user.id,
    },
    {
      onSuccess: (page) => {
        const chat = (page.props as any).chat;
        if (chat) {
          router.visit(`/chat/${chat.id}`);
        }
      },
      onError: (errors) => {
        console.error("Error creating chat:", errors);
      },
    }
  );
}

function goBack() {
  router.visit("/chat");
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function getUserDisplayName(user: User): string {
  if (!user) return "Noma'lum foydalanuvchi";
  return `${user.first_name || ""} ${user.last_name || ""}`.trim() || user.email;
}

function formatTime(dateString: string): string {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return "Hozir";
  if (diffMins < 60) return `${diffMins} daqiqa oldin`;
  if (diffHours < 24) return `${diffHours} soat oldin`;
  if (diffDays < 7) return `${diffDays} kun oldin`;

  return date.toLocaleDateString("uz-UZ", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}
</script>
