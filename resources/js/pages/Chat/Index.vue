<template>
  <div class="h-screen flex bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <!-- Left Sidebar - Chat List -->
    <div
      class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col"
    >
      <!-- Header -->
      <div
        class="bg-telegram-blue dark:bg-telegram-dark-blue text-white p-4 flex items-center justify-between"
      >
        <h1 class="text-lg font-medium">Chat</h1>
        <button
          @click="showUserList = true"
          class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
        </button>
      </div>

      <!-- Search Bar -->
      <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <div class="relative">
          <svg
            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
          <input
            type="text"
            placeholder="Qidirish..."
            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-md text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-telegram-blue"
          />
        </div>
      </div>

      <!-- Chat List -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="chatList.length === 0" class="p-8 text-center">
          <div class="text-gray-400 mb-2">
            <svg
              class="w-16 h-16 mx-auto mb-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
              />
            </svg>
          </div>
          <p class="text-gray-500 dark:text-gray-400 text-sm">Hozircha suhbatlar yo'q</p>
          <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">
            Yangi suhbat boshlash uchun + tugmasini bosing
          </p>
        </div>

        <div
          v-for="chat in chatList"
          :key="chat.id"
          @click="selectChat(chat)"
          class="flex items-center p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700/50"
          :class="{
            'bg-telegram-blue/10 dark:bg-telegram-blue/20': selectedChat?.id === chat.id,
          }"
        >
          <!-- Avatar -->
          <div
            class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-medium text-lg mr-3 shrink-0"
          >
            {{ getChatDisplayName(chat).charAt(0).toUpperCase() }}
          </div>

          <!-- Chat Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
              <h3 class="font-medium text-gray-900 dark:text-white text-sm truncate">
                {{ getChatDisplayName(chat) }}
              </h3>
              <div class="flex items-center ml-2">
                <span
                  v-if="chat.is_new"
                  class="w-2 h-2 bg-telegram-blue rounded-full mr-2"
                ></span>
                <span
                  class="text-xs text-gray-500 dark:text-gray-400"
                  v-if="chat.last_message"
                >
                  {{ formatTime(chat.last_message.created_at) }}
                </span>
              </div>
            </div>
            <p
              class="text-gray-600 dark:text-gray-300 text-sm truncate"
              v-if="chat.last_message"
            >
              {{ chat.last_message.message }}
            </p>
            <p class="text-gray-400 dark:text-gray-500 text-sm" v-else>
              Hozircha xabar yo'q
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Chat Content -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 h-screen">
      <div v-if="!selectedChat" class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <div
            class="w-32 h-32 mx-auto mb-6 rounded-full bg-gradient-to-br from-telegram-blue to-blue-600 flex items-center justify-center"
          >
            <svg
              class="w-16 h-16 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
              />
            </svg>
          </div>
          <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">Chat</h3>
          <p class="text-gray-500 dark:text-gray-400">
            Xabar almashinishni boshlash uchun suhbat tanlang
          </p>
        </div>
      </div>

      <div v-else class="flex-1 flex flex-col h-full max-h-screen overflow-hidden">
        <!-- Chat Header -->
        <div
          class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <button
                @click="selectedChat = null"
                class="mr-3 p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors lg:hidden"
              >
                <svg
                  class="w-5 h-5 text-gray-600 dark:text-gray-300"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                  />
                </svg>
              </button>
              <div
                class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-medium mr-3"
              >
                {{ getChatDisplayName(selectedChat).charAt(0).toUpperCase() }}
              </div>
              <div>
                <h2 class="font-medium text-gray-900 dark:text-white">
                  {{ getChatDisplayName(selectedChat) }}
                </h2>
                <p class="text-sm text-telegram-blue">online</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
              >
                <svg
                  class="w-5 h-5 text-gray-600 dark:text-gray-300"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </button>
              <button
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
              >
                <svg
                  class="w-5 h-5 text-gray-600 dark:text-gray-300"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div
          class="flex-1 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 max-h-[calc(100vh-200px)]"
          ref="messagesContainer"
          :style="{
            backgroundImage: `url(data:image/svg+xml,${encodeURIComponent(svgBg)})`,
            minHeight: '300px',
          }"
        >
          <div v-if="currentChatMessages.length === 0" class="text-center py-16">
            <div
              class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
            >
              <svg
                class="w-10 h-10 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                />
              </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400 mb-2">Hozircha xabar yo'q</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm">Birinchi xabar yozing!</p>
          </div>

          <div class="space-y-3">
            <div
              v-for="message in currentChatMessages"
              :key="message.id"
              class="flex items-end gap-2"
              :class="message.user_id === user.id ? 'flex-row-reverse' : 'flex-row'"
            >
              <!-- Avatar for received messages -->
              <div
                v-if="message.user_id !== user.id"
                class="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium shrink-0"
              >
                {{ getUserDisplayName(message.user).charAt(0).toUpperCase() }}
              </div>

              <!-- Message Bubble -->
              <div
                class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl shadow-sm"
                :class="
                  message.user_id === user.id
                    ? 'bg-telegram-blue text-white rounded-br-md'
                    : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 rounded-bl-md'
                "
              >
                <p class="text-sm leading-relaxed break-words">{{ message.message }}</p>
                <div class="flex items-center justify-end mt-1 gap-1">
                  <span
                    class="text-xs"
                    :class="
                      message.user_id === user.id
                        ? 'text-white/70'
                        : 'text-gray-500 dark:text-gray-400'
                    "
                  >
                    {{ formatTime(message.created_at) }}
                  </span>
                  <!-- Read Receipt Icons -->
                  <div v-if="message.user_id === user.id" class="flex items-center ml-1">
                    <!-- Single Check (Sent) -->
                    <svg
                      v-if="!message.read_at"
                      class="w-4 h-4 text-white/70"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"
                      />
                    </svg>
                    <!-- Double Check (Read) - Telegram style -->
                    <div v-else class="relative flex items-center">
                      <!-- First check (behind) -->
                      <svg
                        class="w-4 h-4 text-blue-400"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"
                        />
                      </svg>
                      <!-- Second check (overlapping) -->
                      <svg
                        class="w-4 h-4 text-blue-400 -ml-3"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"
                        />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div
          class="sticky bottom-0 border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800 mt-auto"
        >
          <form @submit.prevent="sendMessage" class="flex items-end space-x-3">
            <!-- Attachment button -->
            <button
              type="button"
              class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                />
              </svg>
            </button>

            <!-- Message input -->
            <div class="flex-1 relative">
              <textarea
                v-model="newMessage"
                @keydown.enter.exact.prevent="sendMessage"
                placeholder="Xabar yozing..."
                rows="1"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-2xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-telegram-blue resize-none max-h-32"
                :disabled="sending"
                style="min-height: 44px"
              ></textarea>

              <!-- Emoji button -->
              <button
                type="button"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              >
                <svg
                  class="w-5 h-5"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </button>
            </div>

            <!-- Send button -->
            <button
              type="submit"
              :disabled="!newMessage.trim() || sending"
              class="w-11 h-11 bg-telegram-blue hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center transition-colors"
            >
              <svg
                v-if="!sending"
                class="w-5 h-5 text-white"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
              </svg>
              <div
                v-else
                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
              ></div>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- User Selection Modal -->
    <div
      v-if="showUserList"
      class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
      @click.self="showUserList = false"
    >
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full max-h-[600px] overflow-hidden shadow-2xl"
      >
        <div class="bg-telegram-blue dark:bg-telegram-dark-blue text-white p-4">
          <div class="flex items-center justify-between">
            <h3 class="font-medium text-lg">Yangi Suhbat</h3>
            <button
              @click="showUserList = false"
              class="text-white/80 hover:text-white transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- Search -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="relative">
            <svg
              class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
            <input
              v-model="searchQuery"
              @input="handleSearchInput"
              type="text"
              placeholder="Qidirish..."
              class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border-0 rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-telegram-blue"
            />
          </div>
        </div>

        <div class="overflow-y-auto max-h-80">
          <div v-if="isSearching" class="p-8 text-center">
            <div
              class="w-8 h-8 border-2 border-telegram-blue border-t-transparent rounded-full animate-spin mx-auto mb-4"
            ></div>
            <p class="text-gray-500 dark:text-gray-400">Qidirilmoqda...</p>
          </div>

          <div
            v-else-if="availableUsers.length === 0 && searchQuery.trim()"
            class="p-8 text-center"
          >
            <div class="text-gray-400 mb-2">
              <svg
                class="w-12 h-12 mx-auto"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400">
              "{{ searchQuery }}" bo'yicha hech narsa topilmadi
            </p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
              Boshqa nom yoki email kiriting
            </p>
          </div>

          <div v-else-if="availableUsers.length === 0" class="p-8 text-center">
            <div class="text-gray-400 mb-2">
              <svg
                class="w-12 h-12 mx-auto"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm-13.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                />
              </svg>
            </div>
            <p class="text-gray-500 dark:text-gray-400">Boshqa foydalanuvchilar yo'q</p>
          </div>

          <div
            v-for="user in availableUsers"
            :key="user.id"
            @click="startChat(user)"
            class="flex items-center p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700/50"
          >
            <div
              class="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-medium mr-3"
            >
              {{ getUserDisplayName(user).charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">
                {{ getUserDisplayName(user) }}
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
            </div>
            <div class="text-telegram-blue">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";

// Props
const props = defineProps({
  chats: Array,
  user: Object,
});

// Reactive data
const selectedChat = ref(null);
const currentChatMessages = ref([]);
const newMessage = ref("");
const sending = ref(false);
const showUserList = ref(false);
const availableUsers = ref([]);
const allUsers = ref([]); // Store all users for filtering
const searchQuery = ref(""); // Search input value
const messagesContainer = ref(null);
const isLoading = ref(false);
const isSearching = ref(false);
const chatList = ref([...props.chats]); // Local reactive copy of chats

// Computed
const page = usePage();

// SVG background for messages area
const svgBg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="0.5" fill="%23e5e7eb" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>`;

// Load messages for selected chat
const loadChatMessages = async (chat) => {
  if (!chat) return;

  isLoading.value = true;
  try {
    const response = await axios.get(`/chat/${chat.id}/messages`);
    currentChatMessages.value = response.data.messages;
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error("Xabarlarni yuklashda xato:", error);
  } finally {
    isLoading.value = false;
  }
};

// Select chat
const selectChat = async (chat) => {
  selectedChat.value = chat;
  await loadChatMessages(chat);

  // Mark messages as read
  try {
    await axios.post(`/chat/${chat.id}/mark-read`);
    console.log("✅ Messages marked as read for chat:", chat.id);
  } catch (error) {
    console.error("❌ Error marking messages as read:", error);
  }

  setupEcho(); // Setup real-time messaging
};

// Send message
const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value || !selectedChat.value) return;

  const messageText = newMessage.value.trim();
  newMessage.value = "";
  sending.value = true;

  console.log("Sending message to chat:", selectedChat.value.id, "Message:", messageText);

  try {
    const response = await axios.post(`/chat/${selectedChat.value.id}/message`, {
      message: messageText,
    });

    console.log("Message sent successfully:", response.data);
    console.log("Server response message:", response.data.message);

    currentChatMessages.value.push(response.data.message);
    console.log(
      "Message added locally. Total messages:",
      currentChatMessages.value.length
    );
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error("Xabar yuborishda xato:", error);
    newMessage.value = messageText; // Restore message on error
  } finally {
    sending.value = false;
    console.log("Message sending completed");
  }
};

// Load available users
const loadUsers = async () => {
  try {
    // Get all users instead of just from chats
    const response = await axios.get("/api/users/search"); // New endpoint for all users
    allUsers.value = response.data.users.filter((user) => user.id !== props.user.id);
    availableUsers.value = allUsers.value;
  } catch (error) {
    console.error("Foydalanuvchilarni yuklashda xato:", error);
    // Fallback to chat-based users
    try {
      const response = await axios.get("/api/user/chats");
      const users = new Map();

      response.data.chats.forEach((chat) => {
        if (chat.user && chat.user.id !== props.user.id) {
          users.set(chat.user.id, chat.user);
        }
      });

      allUsers.value = Array.from(users.values());
      availableUsers.value = allUsers.value;
    } catch (fallbackError) {
      console.error("Fallback user loading failed:", fallbackError);
    }
  }
};

// Search users in database
const searchUsers = async (query) => {
  if (!query || query.trim().length < 2) {
    availableUsers.value = allUsers.value;
    return;
  }

  isSearching.value = true;

  try {
    const response = await axios.get("/api/users/search", {
      params: { q: query.trim() },
    });

    availableUsers.value = response.data.users.filter(
      (user) => user.id !== props.user.id
    );
  } catch (error) {
    console.error("Foydalanuvchi qidirishda xato:", error);
    // Fallback to local filtering
    const lowerQuery = query.toLowerCase();
    availableUsers.value = allUsers.value.filter((user) => {
      const firstName = (user.first_name || "").toLowerCase();
      const lastName = (user.last_name || "").toLowerCase();
      const email = (user.email || "").toLowerCase();
      const fullName = `${firstName} ${lastName}`.trim().toLowerCase();

      return (
        firstName.includes(lowerQuery) ||
        lastName.includes(lowerQuery) ||
        fullName.includes(lowerQuery) ||
        email.includes(lowerQuery)
      );
    });
  } finally {
    isSearching.value = false;
  }
};

// Handle search input with debouncing
let searchTimeout = null;
const handleSearchInput = (event) => {
  const query = event.target.value;
  searchQuery.value = query;

  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  console.log(query);
  // Debounce search to avoid too many requests
  searchTimeout = setTimeout(() => {
    searchUsers(query);
  }, 300);
};

// Start new chat
const startChat = async (user) => {
  showUserList.value = false;
  clearSearch(); // Clear search when starting chat

  try {
    const response = await axios.post("/chat", {
      recipient_id: user.id,
    });

    const newChat = response.data.chat;

    // Check if chat already exists in list
    const existingChatIndex = chatList.value.findIndex((chat) => chat.id === newChat.id);

    if (existingChatIndex === -1) {
      // Add new chat to beginning of list
      chatList.value.unshift(newChat);
    }

    // Select the chat immediately
    selectedChat.value = newChat;
    await loadChatMessages(newChat);
    setupEcho(); // Setup real-time for this chat
  } catch (error) {
    console.error("Yangi suhbat yaratishda xato:", error);
  }
};

// Clear search
const clearSearch = () => {
  searchQuery.value = "";
  availableUsers.value = allUsers.value;
  if (searchTimeout) {
    clearTimeout(searchTimeout);
    searchTimeout = null;
  }
};
// Update chat in list when new message arrives
const updateChatInList = (chatId, message) => {
  const chatIndex = chatList.value.findIndex((chat) => chat.id === chatId);
  if (chatIndex !== -1) {
    // Update the last message and move to top
    const updatedChat = {
      ...chatList.value[chatIndex],
      last_message: message,
      updated_at: message.created_at,
      is_new: message.user_id !== props.user.id, // Only mark as new if from another user
    };

    // Remove from current position and add to top
    chatList.value.splice(chatIndex, 1);
    chatList.value.unshift(updatedChat);
  }
};
// Helper functions
const getUserDisplayName = (user) => {
  if (!user) return "Noma'lum foydalanuvchi";

  const firstName = user.first_name || "";
  const lastName = user.last_name || "";

  if (firstName && lastName) {
    return `${firstName} ${lastName}`;
  } else if (firstName) {
    return firstName;
  } else if (lastName) {
    return lastName;
  } else if (user.name) {
    return user.name; // fallback to name field if exists
  } else {
    return "Noma'lum foydalanuvchi";
  }
};

const getChatDisplayName = (chat) => {
  if (!chat) return "Suhbat";

  // Current user
  const currentUserId = props.user.id;

  // If chat has recipient and user, find the other participant
  if (chat.recipient && chat.user) {
    const otherUser = chat.user.id === currentUserId ? chat.recipient : chat.user;
    return getUserDisplayName(otherUser);
  }

  // Fallback: use chat.user if available
  if (chat.user && chat.user.id !== currentUserId) {
    return getUserDisplayName(chat.user);
  }

  // Final fallback
  return "Suhbat";
};

const formatTime = (timestamp) => {
  if (!timestamp) return "";

  const date = new Date(timestamp);
  const now = new Date();
  const diff = now.getTime() - date.getTime();

  // Less than a minute
  if (diff < 60000) {
    return "Hozir";
  }

  // Less than an hour
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000);
    return `${minutes} daqiqa oldin`;
  }

  // Less than a day
  if (diff < 86400000) {
    return date.toLocaleTimeString("uz-UZ", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  // More than a day
  return date.toLocaleDateString("uz-UZ", {
    day: "2-digit",
    month: "2-digit",
  });
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

// Real-time messaging with Laravel Echo
const setupEcho = () => {
  if (!selectedChat.value) {
    console.warn("⚠️ No selected chat, skipping Echo setup");
    return;
  }

  if (!window.Echo) {
    console.error("❌ Echo not available");
    return;
  }

  const channelName = `chat.${selectedChat.value.id}`;
  console.log("🎯 Setting up Echo for selected chat:", channelName);
  console.log("🔍 Expected channel will be: private-" + channelName);

  // Leave any previous channels
  window.Echo.leave(channelName);

  // Create channel and add debugging
  console.log("📡 Creating private channel listener...");

  const channel = window.Echo.private(channelName);
  console.log("📻 Channel created:", typeof channel, channel);

  // Listen specifically to message.sent
  channel
    .listen(".message.sent", function (eventData) {
      let messageData;

      try {
        // Handle string data (from your WebSocket message)
        if (typeof eventData === "string") {
          const parsed = JSON.parse(eventData);
          messageData = parsed.message || parsed;
        } else if (eventData && typeof eventData === "object") {
          // Direct object or nested
          messageData = eventData.message || eventData;
        } else {
          console.error("❌ Unknown data format:", eventData);
          return;
        }
      } catch (error) {
        console.error("❌ Error parsing event data:", error, eventData);
        return;
      }

      // Validate and add message
      if (messageData && messageData.id) {
        // Check if message already exists
        const exists = currentChatMessages.value.some((msg) => msg.id === messageData.id);
        if (!exists) {
          currentChatMessages.value.push(messageData);
          console.log(
            "🆕 Message added! Total messages:",
            currentChatMessages.value.length
          );
          nextTick(() => scrollToBottom());
        } else {
          console.log("🔄 Message already exists, skipping");
        }
        // Update chat list
        updateChatInList(selectedChat.value.id, messageData);
      } else {
        console.error("❌ Invalid message data:", messageData);
      }
    })
    .subscribed(() => {
      console.log(`✅ Successfully subscribed to ${channelName}`);
    })
    .error((error) => {
      console.error(`❌ Subscription error for ${channelName}:`, error);
    });

  console.log("🎯 Echo setup completed for chat:", selectedChat.value.id);
};

// Setup global Echo listeners for all user's chats
const setupGlobalEcho = () => {
  console.log("🌍 setupGlobalEcho called");

  if (!window.Echo) {
    console.error("❌ Echo not available for global setup");
    return;
  }
  // Listen to all chats this user is part of
  chatList.value.forEach((chat, index) => {
    const channelName = `chat.${chat.id}`;

    window.Echo.private(channelName)
      .listen("message.sent", function (eventData) {
        // Extract message data
        let messageData = eventData;
        if (eventData && eventData.message) {
          messageData = eventData.message;
        }

        if (messageData && messageData.id) {
          // Update chat in list
          updateChatInList(chat.id, messageData);

          // If this is the currently selected chat, add to messages
          if (selectedChat.value && selectedChat.value.id === chat.id) {
            const exists = currentChatMessages.value.some(
              (msg) => msg.id === messageData.id
            );
            if (!exists) {
              currentChatMessages.value.push(messageData);
              console.log("🆕 Global message added to current chat");
              nextTick(() => scrollToBottom());
            }
          }
        }
      })
      .subscribed(() => {
        console.log(`✅ Global subscription successful`);
      })
      .error((error) => {
        console.error(`❌ Global subscription error for ${channelName}:`, error);
      });
  });
};

// Watch for new messages (fallback)
const pollForNewMessages = () => {
  if (selectedChat.value && !window.Echo) {
    loadChatMessages(selectedChat.value);
  }
};

// Initialize
onMounted(() => {
  loadUsers();
  // Debug Echo connection
  if (window.Echo) {
    console.log("✅ Echo initialized:", window.Echo);
    console.log("⚙️ Echo config:", {
      broadcaster: window.Echo.options.broadcaster,
      wsHost: window.Echo.options.wsHost,
      wsPort: window.Echo.options.wsPort,
    });
    setupGlobalEcho(); // Global echo setup for all chats
  } else {
    console.error("❌ Echo not available, falling back to polling");
    // Poll for new messages every 5 seconds as fallback
    setInterval(pollForNewMessages, 5000);
  }
});

// Watch for chat selection changes
watch(selectedChat, (newChat) => {
  if (newChat) {
    loadChatMessages(newChat);
    setupEcho(); // Setup real-time for new chat
  }
});

// Watch for user list modal changes
watch(showUserList, (isShown) => {
  if (!isShown) {
    clearSearch(); // Clear search when modal is closed
  }
});
</script>

<style scoped>
/* Custom Telegram colors */
.bg-telegram-blue {
  background-color: #0088cc;
}

.dark .bg-telegram-dark-blue {
  background-color: #2b5278;
}

.text-telegram-blue {
  color: #0088cc;
}

.ring-telegram-blue {
  --tw-ring-color: #0088cc;
}

.focus\:ring-telegram-blue:focus {
  --tw-ring-color: #0088cc;
}

/* Message input auto-resize */
textarea {
  resize: none;
  field-sizing: content;
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.4);
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.6);
}

/* Dark mode scrollbar */
.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.4);
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.6);
}
</style>
