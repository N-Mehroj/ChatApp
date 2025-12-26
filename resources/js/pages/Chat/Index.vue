<template>
  <div class="h-screen flex bg-gray-100 dark:bg-gray-900 overflow-hidden">
    <!-- Left Sidebar - Chat List (Desktop only) -->
    <div
      :class="[
        'bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col',
        'w-80 relative translate-x-0',
        'hidden md:flex',
      ]"
    >
      <!-- Header -->
      <div
        class="bg-telegram-blue dark:bg-telegram-dark-blue text-white p-4 flex items-center justify-between"
      >
        <h1 class="text-lg font-medium">Chat</h1>
        <button
          v-if="user.role && ['support', 'admin'].includes(user.role.toLowerCase())"
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
        <!-- Loading indicator for chat list -->
        <div v-if="isLoading" class="p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-center space-x-2 text-sm text-gray-500">
            <div
              class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-400"
            ></div>
            <span>Chatlar yuklanmoqda...</span>
          </div>
        </div>

        <div v-if="chatList.length === 0 && !isLoading" class="p-8 text-center">
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
              <div class="flex items-center flex-1 min-w-0 mr-2">
                <h3
                  class="font-medium text-gray-900 dark:text-white text-sm truncate cursor-pointer hover:text-telegram-blue transition-colors"
                  @click="openUserProfile(chat)"
                >
                  {{ getChatDisplayName(chat) }}
                </h3>
                <!-- Widget Chat Badge -->
                <span
                  v-if="chat.is_widget_chat"
                  class="ml-2 px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full"
                >
                  Widget
                </span>
              </div>
              <div class="flex items-center ml-2">
                <!-- Unread Message Count Badge -->
                <span
                  v-if="chat.unread_count && chat.unread_count > 0"
                  class="bg-red-500 text-white text-xs font-medium px-1.5 py-0.5 rounded-full min-w-[18px] h-[18px] flex items-center justify-center mr-2"
                >
                  {{ chat.unread_count > 9 ? "9+" : chat.unread_count }}
                </span>
                <!-- Online Status Indicator -->
                <div
                  v-if="chat.user && isUserOnline(chat.user.id)"
                  class="w-2 h-2 bg-green-500 rounded-full mr-2"
                  title="Online"
                ></div>
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
            <!-- Widget user info -->
            <div
              v-if="chat.is_widget_chat && (chat.visitor_email || chat.visitor_phone)"
              class="mb-1"
            >
              <div
                class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400"
              >
                <span v-if="chat.visitor_email">{{ chat.visitor_email }}</span>
                <span v-if="chat.visitor_phone">{{ chat.visitor_phone }}</span>
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
      <!-- Mobile Chat List (when no chat selected) -->
      <div v-if="!selectedChat" class="md:hidden flex flex-col h-full">
        <!-- Mobile Header for Chat List -->
        <div
          class="bg-telegram-blue dark:bg-telegram-dark-blue text-white p-4 flex items-center justify-between"
        >
          <h1 class="text-lg font-medium">Chat</h1>
          <button
            v-if="user.role && ['support', 'admin'].includes(user.role.toLowerCase())"
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

        <!-- Mobile Search Bar -->
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

        <!-- Mobile Chat List -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="chatList.length === 0" class="p-6 text-center">
            <div
              class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
            >
              <svg
                class="w-8 h-8 text-gray-400"
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
            <p class="text-gray-500 dark:text-gray-400 mb-2">Hozircha suhbat yo'q</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm">
              Yangi suhbat boshlash uchun + tugmasini bosing
            </p>
          </div>

          <div
            v-for="chat in chatList"
            :key="chat.id"
            @click="selectChat(chat)"
            class="flex items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-700 transition-colors"
          >
            <div
              class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-medium mr-3 shrink-0"
            >
              {{ getChatDisplayName(chat).charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <p
                  class="text-sm font-medium text-gray-900 dark:text-white truncate cursor-pointer hover:text-telegram-blue transition-colors"
                  @click.stop="openUserProfile(chat)"
                >
                  {{ getChatDisplayName(chat) }}
                  <span
                    v-if="chat.user && isUserOnline(chat.user.id)"
                    class="inline-block w-2 h-2 bg-green-400 rounded-full ml-2"
                  ></span>
                </p>
                <p
                  v-if="chat.latest_message"
                  class="text-xs text-gray-500 dark:text-gray-400"
                >
                  {{ formatTime(chat.latest_message.created_at) }}
                </p>
              </div>
              <p
                v-if="chat.latest_message"
                class="text-sm text-gray-600 dark:text-gray-300 truncate"
              >
                {{ chat.latest_message.message }}
              </p>
              <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
                Hozircha xabar yo'q
              </p>
            </div>
            <div
              v-if="chat.unread_count > 0"
              class="ml-2 bg-telegram-blue text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center"
            >
              {{ chat.unread_count }}
            </div>
          </div>
        </div>
      </div>

      <!-- Desktop Empty State / Mobile Chat Interface -->
      <div
        v-if="!selectedChat"
        class="hidden md:flex flex-1 flex items-center justify-center"
      >
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
        <!-- Loading and Connection Status Bar -->
        <div
          v-if="
            loadingStage || channelStatus || isLoadingMessages || isConnectingChannels
          "
          class="bg-blue-50 dark:bg-blue-900 border-b border-blue-200 dark:border-blue-700 p-2"
        >
          <div class="flex items-center justify-center space-x-2 text-sm">
            <!-- Loading Spinner -->
            <div
              v-if="isLoadingMessages || isConnectingChannels"
              class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"
            ></div>

            <!-- Status Text -->
            <span class="text-blue-700 dark:text-blue-300">
              {{ loadingStage || channelStatus || "Yuklanmoqda..." }}
            </span>

            <!-- Connected Channels Count -->
            <span
              v-if="connectedChannels.size > 0"
              class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 px-2 py-1 rounded-full text-xs"
            >
              {{ connectedChannels.size }} kanal ulandi
            </span>
          </div>
        </div>

        <!-- Mobile Chat Header (with back button) -->
        <div
          class="md:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4 flex items-center"
        >
          <button
            @click="selectedChat = null"
            class="w-8 h-8 mr-3 flex items-center justify-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <div class="flex-1">
            <h2
              class="font-medium text-gray-900 dark:text-white cursor-pointer hover:text-telegram-blue transition-colors"
              @click="openUserProfile(selectedChat)"
            >
              {{ getChatDisplayName(selectedChat) }}
              <span
                v-if="selectedChat.user && isUserOnline(selectedChat.user.id)"
                class="inline-block w-2 h-2 bg-green-400 rounded-full ml-2"
              ></span>
            </h2>
            <p
              v-if="selectedChat.user && isUserOnline(selectedChat.user.id)"
              class="text-xs text-green-500"
            >
              Onlayn
            </p>
            <p
              v-else-if="selectedChat.user && getLastSeenText(selectedChat.user.id)"
              class="text-xs text-gray-500 dark:text-gray-400"
            >
              {{ getLastSeenText(selectedChat.user.id) }}
            </p>
          </div>
          <button
            @click="openUserProfile(selectedChat)"
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
            title="User Profile"
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

        <!-- Desktop Chat Header -->
        <div
          class="hidden md:block bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4"
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
                class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-medium mr-3 cursor-pointer hover:opacity-80 transition-all"
                @click="openUserProfile(selectedChat)"
              >
                {{ getChatDisplayName(selectedChat).charAt(0).toUpperCase() }}
              </div>
              <div class="cursor-pointer" @click="openUserProfile(selectedChat)">
                <h2
                  class="font-medium text-gray-900 dark:text-white hover:text-telegram-blue transition-colors"
                >
                  {{ getChatDisplayName(selectedChat) }}
                </h2>
                <p
                  class="text-sm"
                  :class="
                    selectedChat.user && isUserOnline(selectedChat.user.id)
                      ? 'text-green-500'
                      : 'text-gray-500 dark:text-gray-400'
                  "
                >
                  {{
                    selectedChat.user && isUserOnline(selectedChat.user.id)
                      ? "online"
                      : selectedChat.user
                      ? getLastSeenText(selectedChat.user.id)
                      : "Offline"
                  }}
                </p>
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
                @click="openUserProfile(selectedChat)"
                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                title="User Profile"
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
          class="flex-1 overflow-y-auto p-2 sm:p-4 bg-gray-50 dark:bg-gray-900 max-h-[calc(100vh-100px)] relative"
          ref="messagesContainer"
          @scroll="handleScroll"
          :style="{
            backgroundImage: `url(data:image/svg+xml,${encodeURIComponent(svgBg)})`,
            minHeight: '200px',
          }"
        >
          <!-- Loading Messages Indicator -->
          <div v-if="isLoadingMessages" class="text-center py-8">
            <div
              class="inline-flex items-center space-x-2 text-gray-500 dark:text-gray-400"
            >
              <div
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"
              ></div>
              <span class="text-sm">Xabarlar yuklanmoqda...</span>
            </div>
          </div>

          <div v-else-if="currentChatMessages.length === 0" class="text-center py-16">
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

          <div class="space-y-2 sm:space-y-3">
            <div
              v-for="message in currentChatMessages"
              :key="message.id"
              class="flex items-end gap-1 sm:gap-2"
              :class="message.user_id === user.id ? 'flex-row-reverse' : 'flex-row'"
            >
              <!-- Avatar for received messages -->
              <div
                v-if="message.user_id !== user.id"
                class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xs sm:text-sm font-medium shrink-0"
              >
                {{ getUserDisplayName(message.user).charAt(0).toUpperCase() }}
              </div>

              <!-- Message Bubble -->
              <div
                class="max-w-[250px] sm:max-w-xs lg:max-w-md rounded-2xl shadow-sm"
                :class="
                  message.user_id === user.id
                    ? 'bg-telegram-blue text-white rounded-br-md'
                    : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 rounded-bl-md'
                "
              >
                <!-- Sender Info for received messages -->
                <div
                  v-if="message.user_id !== user.id"
                  class="px-3 pt-2 pb-1 border-b border-gray-200 dark:border-gray-600"
                >
                  <div class="flex items-center justify-between text-xs">
                    <span class="font-medium text-gray-600 dark:text-gray-300">
                      {{ getUserDisplayName(message.user) }}
                    </span>
                    <span
                      v-if="message.from_operator"
                      class="bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full text-xs font-medium"
                    >
                      Support
                    </span>
                    <span
                      v-else-if="message.user?.role"
                      class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full text-xs capitalize"
                    >
                      {{ message.user.role }}
                    </span>
                  </div>
                </div>

                <div class="px-3 py-2">
                  <p class="text-sm sm:text-base leading-relaxed break-words">
                    {{ message.message }}
                  </p>
                </div>

                <div class="flex items-center justify-end px-3 pb-2 gap-1">
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

          <!-- Scroll to Bottom Button -->
          <div
            v-if="showScrollToBottom"
            class="fixed bottom-20 sm:bottom-25 right-4 sm:right-6 z-50"
          >
            <button
              @click="scrollToBottom"
              class="w-12 h-12 sm:w-10 sm:h-10 bg-gray-600 hover:bg-gray-700 text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110 opacity-90 hover:opacity-100 touch-manipulation"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 14l-7 7m0 0l-7-7m7 7V3"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Message Input - Only for Support Users -->
      <div
        v-if="user.role && ['support', 'admin'].includes(user.role.toLowerCase())"
        class="sticky bottom-0 border-t border-gray-200 dark:border-gray-700 p-2 sm:p-4 bg-white dark:bg-gray-800 mt-auto"
      >
        <form
          @submit.prevent="sendMessage"
          class="flex items-center space-x-2 sm:space-x-3"
        >
          <!-- Attachment button -->
          <button
            type="button"
            class="p-2 sm:p-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors touch-manipulation"
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
          <div class="flex-1 relative mt-1">
            <textarea
              v-model="newMessage"
              @keydown.enter.exact.prevent="sendMessage"
              placeholder="Xabar yozing..."
              rows="1"
              class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-50 dark:bg-gray-700 border-0 rounded-2xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-telegram-blue resize-none max-h-32 text-sm sm:text-base touch-manipulation"
              :disabled="sending"
              style="min-height: 40px"
            ></textarea>

            <!-- Emoji button -->
            <button
              type="button"
              class="absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1 touch-manipulation"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            class="w-10 h-10 sm:w-11 sm:h-11 bg-telegram-blue hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-full flex items-center justify-center transition-colors touch-manipulation"
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
  <!-- </div> -->

  <!-- User Profile Modal -->
  <div
    v-if="showUserProfile && selectedUser"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    @click.self="closeUserProfile"
  >
    <div
      class="bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 transform scale-100"
    >
      <!-- Header with gradient background -->
      <div
        class="bg-gradient-to-br from-telegram-blue to-blue-600 p-6 text-white relative"
      >
        <button
          @click="closeUserProfile"
          class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-full"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>

        <!-- User Avatar and Info -->
        <div class="flex flex-col items-center text-center">
          <div class="relative mb-4">
            <div
              class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-3xl font-bold border-4 border-white/30 overflow-hidden"
            >
              <img
                v-if="getCurrentProfileUser().image"
                :src="getAvatarUrl(getCurrentProfileUser())"
                class="w-full h-full object-cover"
                alt="Avatar"
              />
              <span v-else>{{ getInitials(getCurrentProfileUser()) }}</span>
            </div>
            <div
              v-if="isOnline(getCurrentProfileUser())"
              class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-4 border-white"
            ></div>
          </div>
          <h2 class="text-2xl font-bold mb-1">
            {{ getUserDisplayName(getCurrentProfileUser()) }}
          </h2>
          <p v-if="isOnline(getCurrentProfileUser())" class="text-green-200 text-sm">
            online
          </p>
          <p v-else class="text-blue-200 text-sm">
            {{ getLastSeenTextForUser(getCurrentProfileUser()) }}
          </p>
          <p v-if="getCurrentProfileUser().username" class="text-blue-200 text-sm mt-1">
            @{{ getCurrentProfileUser().username }}
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex bg-gray-50 dark:bg-gray-700">
        <button
          @click="startChatFromProfile"
          class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
        >
          <div
            class="w-10 h-10 bg-telegram-blue text-white rounded-full flex items-center justify-center mb-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
              />
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-600 dark:text-gray-300"
            >Message</span
          >
        </button>

        <button
          v-if="getCurrentProfileUser().phone"
          class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
        >
          <div
            class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mb-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Call</span>
        </button>

        <button
          class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
        >
          <div
            class="w-10 h-10 bg-gray-500 text-white rounded-full flex items-center justify-center mb-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
              />
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-600 dark:text-gray-300">More</span>
        </button>
      </div>

      <!-- User Details -->
      <div class="p-4 space-y-1 bg-white dark:bg-gray-800 max-h-96 overflow-y-auto">
        <!-- Phone -->
        <div
          v-if="getCurrentProfileUser().phone"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().phone }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mobile</p>
          </div>
        </div>

        <!-- Email -->
        <div
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().email }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
          </div>
        </div>

        <!-- Position -->
        <div
          v-if="getCurrentProfileUser().position"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().position }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Position</p>
          </div>
        </div>

        <!-- Department -->
        <div
          v-if="getCurrentProfileUser().department_id"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              Department #{{ getCurrentProfileUser().department_id }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Department</p>
          </div>
        </div>

        <!-- Organization -->
        <div
          v-if="getCurrentProfileUser().organization_id"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              Organization #{{ getCurrentProfileUser().organization_id }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Organization</p>
          </div>
        </div>

        <!-- Role Badge -->
        <div
          v-if="getCurrentProfileUser().role"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().role }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
          </div>
        </div>

        <!-- Registration Date -->
        <div
          v-if="getCurrentProfileUser().created_at"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ formatRegistrationDate(getCurrentProfileUser().created_at) }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Member since</p>
          </div>
        </div>

        <!-- Mobile App Status -->
        <div
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
            :class="
              getCurrentProfileUser().mobile_app_installed
                ? 'bg-green-100 text-green-600'
                : 'bg-red-100 text-red-600'
            "
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{
                getCurrentProfileUser().mobile_app_installed
                  ? "O'rnatilgan"
                  : "O'rnatilmagan"
              }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Mobile App</p>
          </div>
        </div>

        <!-- App Version -->
        <div
          v-if="
            getCurrentProfileUser().mobile_app_installed &&
            getCurrentProfileUser().app_version
          "
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4a1 1 0 000 2v0a1 1 0 001 1h8a1 1 0 001-1v0a1 1 0 000-2M7 4h10M5 8v12a2 2 0 002 2h10a2 2 0 002-2V8"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              v{{ getCurrentProfileUser().app_version }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">App Version</p>
          </div>
        </div>

        <!-- Company -->
        <div
          v-if="getCurrentProfileUser().company_name"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().company_name }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Company</p>
          </div>
        </div>

        <!-- Department Name -->
        <div
          v-if="getCurrentProfileUser().department_name"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().department_name }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Department</p>
          </div>
        </div>

        <!-- Group -->
        <div
          v-if="getCurrentProfileUser().group_name"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm-13.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().group_name }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Group</p>
          </div>
        </div>

        <!-- Merchant -->
        <div
          v-if="getCurrentProfileUser().merchant_name"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ getCurrentProfileUser().merchant_name }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Merchant</p>
          </div>
        </div>

        <!-- Last Activity -->
        <div
          v-if="getCurrentProfileUser().last_activity"
          class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors"
        >
          <div
            class="w-10 h-10 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mr-3"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-medium text-gray-900 dark:text-gray-100">
              {{ formatRegistrationDate(getCurrentProfileUser().last_activity) }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Last Activity</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
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
const showUserProfile = ref(false);
const selectedUser = ref(null);
const availableUsers = ref([]);
const allUsers = ref([]); // Store all users for filtering
const searchQuery = ref(""); // Search input value
const messagesContainer = ref(null);
const isLoading = ref(false);
const isSearching = ref(false);
const chatList = ref([...props.chats]); // Local reactive copy of chats
const showScrollToBottom = ref(false); // Show scroll to bottom button
const onlineUsers = ref(new Set()); // Track online users
const userStatuses = ref({}); // Track detailed user statuses
const activeChannel = ref(null); // Track current active channel for cleanup

// Loading and connection states
const isLoadingMessages = ref(false);
const isConnectingChannels = ref(false);
const connectedChannels = ref(new Set());
const channelStatus = ref("");
const loadingStage = ref("");

// Computed
const page = usePage();

// SVG background for messages area
const svgBg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="0.5" fill="%23e5e7eb" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>`;

// Load messages for selected chat
const loadChatMessages = async (chat) => {
  if (!chat) return;

  isLoadingMessages.value = true;
  loadingStage.value = "Xabarlar yuklanmoqda...";

  try {
    const response = await axios.get(`/chat/${chat.id}/messages`);
    currentChatMessages.value = response.data.messages;
    loadingStage.value = "Xabarlar yuklandi";
    await nextTick();
    scrollToBottom();
  } catch (error) {
    console.error("Xabarlarni yuklashda xato:", error);
    loadingStage.value = "Xabarlarni yuklashda xato!";
  } finally {
    isLoadingMessages.value = false;
    setTimeout(() => (loadingStage.value = ""), 2000);
  }
};

// Select chat
const selectChat = async (chat) => {
  loadingStage.value = "Chat tanlanmoqda...";

  // Clean up only the previous active channel (preserve global listeners)
  cleanupActiveChannel();
  selectedChat.value = chat;

  loadingStage.value = "Xabarlar yuklanmoqda...";
  await loadChatMessages(chat);

  // Clear unread count for the selected chat using helper function
  resetUnreadCount(chat.id);

  loadingStage.value = "Xabarlar o'qilgan deb belgilanmoqda...";
  // Mark messages as read
  try {
    await axios.post(`/chat/${chat.id}/mark-read`);
    console.log("✅ Messages marked as read for chat:", chat.id);
  } catch (error) {
    console.error("❌ Error marking messages as read:", error);
  }

  // Setup real-time messaging for the new chat
  loadingStage.value = "WebSocket ulanmoqda...";
  setupEcho();
};

// Send message
const sendMessage = async () => {
  if (!newMessage.value.trim() || sending.value || !selectedChat.value) return;

  // Only support users can send messages
  if (!props.user.role || !["support", "admin"].includes(props.user.role.toLowerCase())) {
    console.error("Only support users can send messages");
    return;
  }

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

    // Message will be added via WebSocket, no need to add locally
    console.log("Message sent, waiting for WebSocket update...");
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
      user_id: user.id,
    });

    const newChat = response.data.chat;

    // Check if chat already exists in list
    const existingChatIndex = chatList.value.findIndex((chat) => chat.id === newChat.id);

    if (existingChatIndex === -1) {
      // Add new chat to beginning of list
      chatList.value.unshift(newChat);

      // Subscribe to the new chat for real-time updates
      subscribeToSingleChat(newChat);
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

// Reset unread count for specific chat
const resetUnreadCount = (chatId) => {
  const chatIndex = chatList.value.findIndex((chat) => chat.id === chatId);
  if (chatIndex !== -1) {
    const oldCount = chatList.value[chatIndex].unread_count;
    chatList.value[chatIndex].unread_count = 0;
    console.log(`📖 Reset unread count for chat ${chatId}: ${oldCount} -> 0`);
  }
};
// Update chat in list when new message arrives
const updateChatInList = (chatId, message) => {
  const chatIndex = chatList.value.findIndex((chat) => chat.id === chatId);
  if (chatIndex !== -1) {
    const currentChat = chatList.value[chatIndex];

    // Update the last message and move to top
    const updatedChat = {
      ...currentChat,
      last_message: message,
      updated_at: message.created_at,
      is_new: message.user_id !== props.user.id, // Only mark as new if from another user
    };

    // Update unread count if message is from another user
    if (message.user_id !== props.user.id) {
      // If this chat is currently selected and active, don't increment unread count
      if (selectedChat.value && selectedChat.value.id === chatId) {
        // Keep unread count as 0 for active chat
        updatedChat.unread_count = 0;
        console.log(`🔄 Message for active chat ${chatId}, keeping unread count at 0`);
      } else {
        // Increment unread count for inactive chats
        const currentUnread = parseInt(currentChat.unread_count || 0);
        updatedChat.unread_count = currentUnread + 1;
        console.log(
          `🆕 Incremented unread count for chat ${chatId}: ${currentUnread} -> ${updatedChat.unread_count}`
        );
      }
    } else {
      // Message from current user, keep existing unread count
      updatedChat.unread_count = parseInt(currentChat.unread_count || 0);
    }

    // Remove from current position and add to top
    chatList.value.splice(chatIndex, 1);
    chatList.value.unshift(updatedChat);

    console.log(
      `✅ Updated chat ${chatId} in list with unread count: ${updatedChat.unread_count}`
    );
  } else {
    console.warn(`⚠️ Chat ${chatId} not found in chat list`);
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

  // Widget chat bo'lsa
  if (chat.is_widget_chat) {
    if (chat.widget_user) {
      // Login qilgan user ma'lumotlari
      return `${getUserDisplayName(chat.widget_user)} (Widget)`;
    } else if (chat.visitor_name) {
      // Visitor name berilgan bo'lsa
      return `${chat.visitor_name} (Widget)`;
    } else {
      // Anonymous visitor
      return "Anonymous Visitor (Widget)";
    }
  }

  // Oddiy chat
  if (!chat.user) return "Suhbat";

  // For support tickets, always show the customer (chat.user)
  const customer = chat.user;
  return getUserDisplayName(customer);
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
    messagesContainer.value.scrollTo({
      top: messagesContainer.value.scrollHeight,
      behavior: "smooth",
    });
    showScrollToBottom.value = false; // Hide button when scrolled to bottom
  }
};

// Handle scroll event to show/hide scroll to bottom button
const handleScroll = () => {
  if (messagesContainer.value) {
    const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;
    const isNearBottom = scrollTop + clientHeight >= scrollHeight - 100; // 100px threshold
    const hasScrolledFromTop = scrollTop > 200; // Show button when scrolled 200px from top
    showScrollToBottom.value =
      !isNearBottom && hasScrolledFromTop && currentChatMessages.value.length > 3;
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

  isConnectingChannels.value = true;
  channelStatus.value = `Kanal ulanmoqda: ${channelName}`;
  loadingStage.value = "WebSocket kanali ulanmoqda...";

  // Clean up previous active channel
  if (activeChannel.value && activeChannel.value !== channelName) {
    console.log(`🧹 Leaving previous channel: ${activeChannel.value}`);
    window.Echo.leave(activeChannel.value);
    connectedChannels.value.delete(activeChannel.value);
  }

  // Leave current channel if exists (for safety)
  window.Echo.leave(channelName);

  console.log(`📡 Setting up channel for chat: ${selectedChat.value.id}`);

  const channel = window.Echo.private(channelName);
  activeChannel.value = channelName; // Track the active channel

  // Listen specifically to message.sent
  channel
    .listen(".message.sent", function (eventData) {
      // Only process messages if this is still the active chat
      if (
        !selectedChat.value ||
        activeChannel.value !== `chat.${selectedChat.value.id}`
      ) {
        console.log("⚠️ Ignoring message for inactive chat");
        return;
      }

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
      if (
        messageData &&
        messageData.id &&
        messageData.chat_id === selectedChat.value.id
      ) {
        // Check if message already exists
        const exists = currentChatMessages.value.some((msg) => msg.id === messageData.id);
        if (!exists) {
          currentChatMessages.value.push(messageData);
          console.log(
            "🆕 Message added via WebSocket! Total messages:",
            currentChatMessages.value.length
          );
          nextTick(() => scrollToBottom());
        } else {
          console.log("🔄 Message already exists, skipping");
        }
        // Update chat list
        updateChatInList(messageData.chat_id, messageData);
      } else {
        console.error("❌ Invalid message data or wrong chat:", messageData);
      }
    })
    .listen(".message.read", function (eventData) {
      console.log("📖 Message read event received:", eventData);

      // Update read receipts for the messages
      if (eventData.message_ids && Array.isArray(eventData.message_ids)) {
        let updatedCount = 0;
        eventData.message_ids.forEach((messageId) => {
          const messageIndex = currentChatMessages.value.findIndex(
            (msg) => msg.id === messageId
          );
          if (messageIndex !== -1) {
            currentChatMessages.value[messageIndex].read_at = eventData.read_at;
            currentChatMessages.value[messageIndex].read_by = eventData.reader.id;
            updatedCount++;
            console.log(`✅ Updated read status for message ${messageId}`);
          } else {
            console.log(`⚠️ Message ${messageId} not found in current messages`);
          }
        });

        // Reset unread count for this chat when messages are marked as read
        if (eventData.chat_id && updatedCount > 0) {
          resetUnreadCount(eventData.chat_id);
        }

        console.log(
          `📖 Total messages updated: ${updatedCount} of ${eventData.message_ids.length}`
        );
      } else {
        console.error("❌ Invalid message_ids in read event:", eventData);
      }
    })
    .subscribed(() => {
      console.log(`✅ Successfully subscribed to ${channelName}`);
      connectedChannels.value.add(channelName);
      isConnectingChannels.value = false;
      channelStatus.value = `Ulandi: ${channelName}`;
      loadingStage.value = "Kanal muvaffaqiyatli ulandí";

      // Clear status after 2 seconds
      setTimeout(() => {
        channelStatus.value = "";
        loadingStage.value = "";
      }, 2000);
    })
    .error((error) => {
      console.error(`❌ Subscription error for ${channelName}:`, error);
      isConnectingChannels.value = false;
      channelStatus.value = `Xato: ${channelName}`;
      loadingStage.value = "Kanal ulanishida xato!";

      // Clear error after 3 seconds
      setTimeout(() => {
        channelStatus.value = "";
        loadingStage.value = "";
      }, 3000);
    });
};

// Cleanup only the active Echo channel (preserve global listeners)
const cleanupActiveChannel = () => {
  if (!window.Echo) return;

  // Leave only the active channel
  if (activeChannel.value) {
    console.log(`🧹 Cleaning up active channel: ${activeChannel.value}`);
    window.Echo.leave(activeChannel.value);
    connectedChannels.value.delete(activeChannel.value);
    activeChannel.value = null;
  }
};

// Cleanup all Echo channels (use only on unmount)
const cleanupAllChannels = () => {
  if (!window.Echo) return;

  // Leave active channel
  if (activeChannel.value) {
    console.log(`🧹 Cleaning up active channel: ${activeChannel.value}`);
    window.Echo.leave(activeChannel.value);
    activeChannel.value = null;
  }

  // Leave all global channels (cleanup any leaked channels)
  if (window.Echo.connector && window.Echo.connector.channels) {
    Object.keys(window.Echo.connector.channels).forEach((channelName) => {
      if (channelName.startsWith("private-chat.")) {
        console.log(`🧹 Cleaning up leaked channel: ${channelName}`);
        window.Echo.leave(channelName.replace("private-", ""));
      }
    });
  }
};

// Setup global Echo listeners for chat notifications
const setupGlobalEcho = () => {
  console.log("🌍 setupGlobalEcho called - setting up background listeners");

  channelStatus.value = "Global kanallar ulanmoqda...";

  if (!window.Echo) {
    console.error("❌ Echo not available for global setup");
    channelStatus.value = "Echo mavjud emas!";
    return;
  }

  // Subscribe to more chats for background notifications (limit to 25 to balance performance)
  const visibleChats = chatList.value.slice(0, 25);

  visibleChats.forEach((chat) => {
    const channelName = `chat.${chat.id}`;

    // Check if already subscribed to avoid duplicate subscriptions
    if (!window.Echo.connector.channels[`private-${channelName}`]) {
      window.Echo.private(channelName)
        .listen(".message.sent", function (eventData) {
          // Extract message data
          let messageData = eventData;
          if (eventData && eventData.message) {
            messageData = eventData.message;
          }

          if (messageData && messageData.id) {
            console.log(`📩 Background message received for chat ${chat.id}`);

            // Update chat in list (for unread count and last message)
            updateChatInList(chat.id, messageData);

            // Only add to current messages if this is the currently selected chat
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
          console.log(`✅ Background subscription active for chat ${chat.id}`);
          connectedChannels.value.add(`chat.${chat.id}`);

          // Update status when all chats are connected
          if (connectedChannels.value.size > 0) {
            channelStatus.value = `${connectedChannels.value.size} chat kanali ulandi`;
            setTimeout(() => (channelStatus.value = ""), 3000);
          }
        })
        .error((error) => {
          console.error(`❌ Background subscription error for chat ${chat.id}:`, error);
        });
    } else {
      console.log(`ℹ️ Already subscribed to chat ${chat.id}`);
    }
  });
};

// Subscribe to a single chat for background notifications
const subscribeToSingleChat = (chat) => {
  if (!window.Echo) {
    console.warn("⚠️ Echo not available for single chat subscription");
    return;
  }

  const channelName = `chat.${chat.id}`;

  // Check if already subscribed
  if (window.Echo.connector.channels[`private-${channelName}`]) {
    console.log(`ℹ️ Already subscribed to chat ${chat.id}`);
    return;
  }

  window.Echo.private(channelName)
    .listen(".message.sent", function (eventData) {
      let messageData = eventData;
      if (eventData && eventData.message) {
        messageData = eventData.message;
      }

      if (messageData && messageData.id) {
        console.log(`📩 Background message received for new chat ${chat.id}`);

        // Update chat in list
        updateChatInList(chat.id, messageData);

        // Only add to current messages if this is the currently selected chat
        if (selectedChat.value && selectedChat.value.id === chat.id) {
          const exists = currentChatMessages.value.some(
            (msg) => msg.id === messageData.id
          );
          if (!exists) {
            currentChatMessages.value.push(messageData);
            console.log("🆕 Message added to current chat from new subscription");
            nextTick(() => scrollToBottom());
          }
        }
      }
    })
    .subscribed(() => {
      console.log(`✅ Background subscription active for new chat ${chat.id}`);
      connectedChannels.value.add(`chat.${chat.id}`);
    })
    .error((error) => {
      console.error(`❌ Background subscription error for new chat ${chat.id}:`, error);
    });
};

// Online status functions
const isUserOnline = (userId) => {
  return onlineUsers.value.has(userId);
};

const getLastSeenText = (userId) => {
  const status = userStatuses.value[userId];
  if (!status || !status.last_seen) return "offline";

  const lastSeen = new Date(status.last_seen);
  const now = new Date();
  const diff = now - lastSeen;

  if (diff < 60000) return "just now"; // Less than 1 minute
  if (diff < 3600000) return `${Math.floor(diff / 60000)} minutes ago`; // Less than 1 hour
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`; // Less than 1 day
  return `${Math.floor(diff / 86400000)} days ago`;
};

// Send online status updates
const sendOnlineStatus = async () => {
  try {
    console.log("📡 Sending online status...");
    const response = await axios.post("/api/user/online");
    console.log("✅ Online status sent successfully:", response.data);
  } catch (error) {
    console.error("❌ Error sending online status:", error);
  }
};

const sendOfflineStatus = async () => {
  try {
    console.log("📡 Sending offline status...");
    const response = await axios.post("/api/user/offline");
    console.log("✅ Offline status sent successfully:", response.data);
  } catch (error) {
    console.error("❌ Error sending offline status:", error);
  }
};

// Set up online status listeners for all users
const setupOnlineStatusListeners = () => {
  if (!window.Echo) {
    console.error("❌ Echo not available for online status setup");
    return;
  }

  // Instead of listening to each user separately, listen to a general online users channel
  // This reduces the number of connections significantly
  const generalStatusChannel = `online-users`;
  console.log(`🔔 Setting up general status listener`);

  window.Echo.channel(generalStatusChannel)
    .listen(".user.status.updated", (eventData) => {
      if (eventData.is_online) {
        onlineUsers.value.add(eventData.user_id);
        console.log(`🟢 User ${eventData.user_id} (${eventData.name}) is now online`);
      } else {
        onlineUsers.value.delete(eventData.user_id);
        console.log(`🔴 User ${eventData.user_id} (${eventData.name}) is now offline`);
      }

      userStatuses.value[eventData.user_id] = {
        is_online: eventData.is_online,
        last_seen: eventData.last_seen,
        name: eventData.name,
      };

      console.log(`📊 Current online users:`, Array.from(onlineUsers.value));
    })
    .subscribed(() => {
      console.log(`✅ General status subscription successful`);
    })
    .error((error) => {
      console.error(`❌ General status subscription error:`, error);
    });
};

// Set up online status tracking
const setupOnlineStatusTracking = () => {
  // Send online status immediately
  sendOnlineStatus();

  // Reduce frequency from 30s to 5 minutes for less server load
  const onlineInterval = setInterval(sendOnlineStatus, 300000); // 5 minutes instead of 30 seconds

  // Use page visibility API to send status only when needed
  let wasVisible = true;
  const handleVisibilityChange = () => {
    if (document.visibilityState === "visible" && !wasVisible) {
      // Page became visible, send online status
      sendOnlineStatus();
      wasVisible = true;
    } else if (document.visibilityState === "hidden") {
      // Page became hidden, send offline status
      sendOfflineStatus();
      wasVisible = false;
    }
  };

  document.addEventListener("visibilitychange", handleVisibilityChange);

  // Send offline status when page unloads
  window.addEventListener("beforeunload", sendOfflineStatus);

  // Clean up interval and listeners on component unmount
  return () => {
    clearInterval(onlineInterval);
    document.removeEventListener("visibilitychange", handleVisibilityChange);
    window.removeEventListener("beforeunload", sendOfflineStatus);
    sendOfflineStatus();
  };
};

// Set up chat list listeners for real-time updates
const setupChatListListeners = () => {
  if (!window.Echo || !props.user) {
    console.error("❌ Echo not available or user not found for chat list setup");
    return;
  }

  const chatListChannel = `user.chats.${props.user.id}`;

  window.Echo.private(chatListChannel)
    .listen(".chat.created", (eventData) => {
      console.log(`🆕 New chat created:`, eventData);

      // Add new chat to the beginning of chat list if it doesn't exist
      const existingChatIndex = chatList.value.findIndex(
        (chat) => chat.id === eventData.chat.id
      );
      if (existingChatIndex === -1) {
        chatList.value.unshift(eventData.chat);
        console.log(`✅ Added new chat ${eventData.chat.id} to chat list`);
      } else {
        console.log(`🔄 Chat ${eventData.chat.id} already exists in list`);
      }
    })
    .listen(".chat.updated", (eventData) => {
      console.log(`📝 Chat updated:`, eventData);

      // Find and update existing chat or add to beginning if new
      const existingChatIndex = chatList.value.findIndex(
        (chat) => chat.id === eventData.chat.id
      );
      if (existingChatIndex !== -1) {
        // Update existing chat and move to top
        chatList.value.splice(existingChatIndex, 1);
        chatList.value.unshift(eventData.chat);
        console.log(`✅ Updated and moved chat ${eventData.chat.id} to top`);
      } else {
        // Add new chat to beginning
        chatList.value.unshift(eventData.chat);
        console.log(`✅ Added new chat ${eventData.chat.id} to chat list`);
      }
    })
    .subscribed(() => {
      console.log(`✅ Chat list subscription successful for user ${props.user.id}`);
    })
    .error((error) => {
      console.error(`❌ Chat list subscription error for user ${props.user.id}:`, error);
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

  // Setup online status tracking
  const cleanupOnlineStatus = setupOnlineStatusTracking();

  // Setup online status listeners for all users
  setupOnlineStatusListeners();

  // Setup chat list listeners for real-time updates
  setupChatListListeners();

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
    console.warn("⚠️ Echo not available - real-time features disabled");
    // Only poll when specifically needed, not continuously
  }
});

// Cleanup on component unmount
onUnmounted(() => {
  cleanupAllChannels();
});

// Watch for chat selection changes
watch(selectedChat, (newChat) => {
  if (newChat) {
    showScrollToBottom.value = false; // Reset scroll button when changing chat
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

// User profile helper functions
function openUserProfile(chat) {
  if (!chat) return;

  // Get user from chat
  let user = null;
  if (chat.is_widget_chat && chat.widget_user) {
    user = chat.widget_user;
  } else if (chat.user) {
    user = chat.user;
  }

  if (user) {
    selectedUser.value = user;
    showUserProfile.value = true;
  }
}

function closeUserProfile() {
  showUserProfile.value = false;
  selectedUser.value = null;
}

function getCurrentProfileUser() {
  return selectedUser.value || {};
}

function isOnline(user) {
  if (!user || !user.last_activity) return false;
  const lastActivity = new Date(user.last_activity);
  const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
  return lastActivity > fiveMinutesAgo;
}

function getLastSeenTextForUser(user) {
  if (!user || !user.last_activity) return "last seen long time ago";
  const lastActivity = new Date(user.last_activity);
  const now = new Date();
  const diff = now.getTime() - lastActivity.getTime();
  const diffMins = Math.floor(diff / 60000);
  const diffHours = Math.floor(diff / 3600000);
  const diffDays = Math.floor(diff / 86400000);

  if (diffMins < 1) return "just now";
  if (diffMins < 60) return `${diffMins} minutes ago`;
  if (diffHours < 24) return `${diffHours} hours ago`;
  if (diffDays < 7) return `${diffDays} days ago`;
  return "last seen long time ago";
}

function getInitials(user) {
  if (!user) return "?";
  const name = getUserDisplayName(user);
  return name
    .split(" ")
    .map((n) => n.charAt(0))
    .join("")
    .substring(0, 2)
    .toUpperCase();
}

function getAvatarUrl(user) {
  if (!user) return "";
  if (user.image) {
    return user.image.startsWith("http") ? user.image : `/storage/${user.image}`;
  }
  const name = encodeURIComponent(getUserDisplayName(user));
  return `https://ui-avatars.com/api/?name=${name}&background=3b82f6&color=fff&size=96`;
}

function formatRegistrationDate(dateString) {
  if (!dateString) return "Unknown";
  const date = new Date(dateString);
  return date.toLocaleDateString("uz-UZ", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

function startChatFromProfile() {
  if (!selectedUser.value) return;

  // Close profile modal first
  closeUserProfile();

  // Check if chat already exists
  const existingChat = chatList.value.find((chat) => {
    if (chat.is_widget_chat && chat.widget_user) {
      return chat.widget_user.id === selectedUser.value.id;
    }
    return chat.user && chat.user.id === selectedUser.value.id;
  });

  if (existingChat) {
    // Select existing chat
    selectChat(existingChat);
  } else {
    // Start new chat
    startChat(selectedUser.value);
  }
}
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
