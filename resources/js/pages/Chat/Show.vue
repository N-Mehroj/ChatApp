<template>
  <div class="min-h-screen bg-background">
    <div class="max-w-6xl mx-auto p-6">
      <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        <div
          @click="openUserProfile(chat.user)"
          class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-4 flex items-center justify-between"
        >
          <div class="flex items-center space-x-3">
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
            <div class="cursor-pointer hover:opacity-80 transition-opacity">
              <h1 class="text-xl font-semibold">{{ getUserDisplayName(chat.user) }}</h1>
              <p class="text-blue-100 text-sm flex items-center">
                Suhbat
                <span v-if="isOnline(chat.user)" class="ml-2 flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                  Online
                </span>
                <span v-else class="ml-2 text-blue-200">{{
                  getLastSeenText(chat.user)
                }}</span>
              </p>
            </div>
          </div>

          <!-- Three dots menu button -->
          <!-- <button
            @click="openUserProfile(chat.user)"
            class="text-white hover:text-blue-200 transition-colors p-2 rounded hover:bg-white/10"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
              />
            </svg>
          </button> -->
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
                  class="text-xs font-medium mb-1 opacity-70 cursor-pointer hover:opacity-100 hover:underline transition-all"
                  @click="openUserProfile(message.user)"
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

      <!-- User Profile Modal -->
      <div
        v-if="showUserProfile"
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
                {{ getLastSeenText(getCurrentProfileUser()) }}
              </p>
              <p
                v-if="getCurrentProfileUser().username"
                class="text-blue-200 text-sm mt-1"
              >
                @{{ getCurrentProfileUser().username }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex bg-gray-50 dark:bg-gray-700">
            <button
              class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
              <div
                class="w-10 h-10 bg-telegram-blue text-white rounded-full flex items-center justify-center mb-2"
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
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                  />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300"
                >Message</span
              >
            </button>

            <button
              v-if="chat.user.phone"
              class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
              <div
                class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center mb-2"
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
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                  />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300"
                >Call</span
              >
            </button>

            <button
              class="flex-1 flex flex-col items-center py-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
              <div
                class="w-10 h-10 bg-gray-500 text-white rounded-full flex items-center justify-center mb-2"
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
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"
                  />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-600 dark:text-gray-300"
                >More</span
              >
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
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
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
            <div class="flex items-center py-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg px-2 transition-colors">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                :class="getCurrentProfileUser().mobile_app_installed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
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
                  {{ getCurrentProfileUser().mobile_app_installed ? 'O\'rnatilgan' : 'O\'rnatilmagan' }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Mobile App</p>
              </div>
            </div>

            <!-- App Version -->
            <div
              v-if="getCurrentProfileUser().mobile_app_installed && getCurrentProfileUser().app_version"
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

            <!-- Statistics Section -->
            <div class="border-t border-gray-200 dark:border-gray-600 pt-4 mt-4">
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Statistics
              </h3>

              <!-- Message count -->
              <div class="flex items-center py-2 px-2">
                <div
                  class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3"
                >
                  <svg
                    class="w-4 h-4"
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
                </div>
                <div class="flex-1">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400"
                      >Messages in this chat</span
                    >
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{
                      getUserMessageCount(getCurrentProfileUser())
                    }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
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
import { log } from "console";

interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string | null;
  position?: string | null;
  department_id?: number | null;
  organization_id?: number | null;
  image?: string | null;
  role?: string | null;
  last_activity?: string | null;
  created_at?: string;
  username?: string | null;
  mobile_app_installed?: boolean;
  app_version?: string | null;
  company_name?: string | null;
  department_name?: string | null;
  group_name?: string | null;
  merchant_name?: string | null;
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
const showUserProfile = ref(false);
const selectedUser = ref<User | null>(null);
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

function isOnline(user: User): boolean {
  if (!user.last_activity) return false;
  const lastActivity = new Date(user.last_activity);
  const fiveMinutesAgo = new Date(Date.now() - 5 * 60 * 1000);
  return lastActivity > fiveMinutesAgo;
}

function getLastSeenText(user: User): string {
  if (!user.last_activity) return "last seen long time ago";
  return `last seen ${formatTime(user.last_activity)}`;
}

function getInitials(user: User): string {
  const name = getUserDisplayName(user);
  return name
    .split(" ")
    .map((n) => n.charAt(0))
    .join("")
    .substring(0, 2)
    .toUpperCase();
}

function getAvatarUrl(user: User): string {
  if (user.image) {
    return user.image.startsWith("http") ? user.image : `/storage/${user.image}`;
  }
  const name = encodeURIComponent(getUserDisplayName(user));
  return `https://ui-avatars.com/api/?name=${name}&background=3b82f6&color=fff&size=96`;
}

function formatRegistrationDate(dateString?: string | null): string {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString("uz-UZ", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

function getUserMessageCount(user?: User): number {
  const targetUser = user || chat.value.user;
  return messages.value.filter((m) => m.user_id === targetUser.id).length;
}

function openUserProfile(user: User) {
  console.log("=== DEBUG: Opening user profile modal ===", user);
  selectedUser.value = user;
  showUserProfile.value = true;
}

function closeUserProfile() {
  console.log("=== DEBUG: Closing user profile modal ===");
  showUserProfile.value = false;
  selectedUser.value = null;
}

function getCurrentProfileUser(): User {
  return selectedUser.value || chat.value.user;
}
</script>
