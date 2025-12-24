# 🎯 Vue.js ChatWidget O'rnatish Qo'llanmasi

Vue.js uchun mo'ljallangan chat widget - real-time messaging va user identification bilan.

## 📋 Talablar

- Vue.js 3.0+
- Laravel Echo (WebSocket uchun)
- Autentifikatsiya tizimi

## 🚀 O'rnatish

### 1-bosqich: CSS va JavaScript fayllarini ulash

HTML sahifangizda CSS va JS fayllarni ulang:

```html
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sizning saytingiz</title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- ChatWidget CSS -->
    <link rel="stylesheet" href="/widget/vue-styles.css">
    
    <!-- Vue.js 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Axios (API so'rovlar uchun) -->
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    
    <!-- Laravel Echo (WebSocket uchun) -->
    <script src="//{{ request()->getHost() }}:6001/socket.io/socket.io.js"></script>
    <script src="/js/echo.js"></script>
</head>
<body>
    <!-- Sizning kontentingiz -->
    
    <!-- ChatWidget Container -->
    <div id="chat-widget"></div>
    
    <!-- ChatWidget Vue.js SDK -->
    <script src="/widget/vue-sdk.js"></script>
    
    <script>
        // Echo konfiguratsiyasi
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config("broadcasting.connections.reverb.app_key") }}',
            wsHost: '{{ config("broadcasting.connections.reverb.host") }}',
            wsPort: '{{ config("broadcasting.connections.reverb.port") }}',
            wssPort: '{{ config("broadcasting.connections.reverb.port") }}',
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });
        
        // ChatWidget ni ishga tushirish
        const chatApp = window.ChatWidget.create('chat-widget', {
            apiUrl: '/api/widget',
            position: 'bottom-right',
            primaryColor: '#3B82F6',
            theme: 'modern'
        });
    </script>
</body>
</html>
```

### 2-bosqich: Vue.js loyihasida komponent sifatida ishlatish

Agar Vue.js loyihangiz mavjud bo'lsa:

```javascript
// main.js yoki app.js da
import { createApp } from 'vue'
import ChatWidget from '/widget/vue-sdk.js'
import '/widget/vue-styles.css'

const app = createApp({})

// ChatWidget plugin'ni o'rnatish
app.use(ChatWidget.install)

// Echo'ni global qilish
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'your-app-key',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

app.mount('#app')
```

Vue component'da ishlatish:

```vue
<template>
    <div>
        <h1>Mening sahifam</h1>
        
        <!-- ChatWidget -->
        <ChatWidget 
            ref="chatWidget"
            :config="chatConfig"
        />
    </div>
</template>

<script>
export default {
    data() {
        return {
            chatConfig: {
                apiUrl: '/api/widget',
                position: 'bottom-right',
                primaryColor: '#3B82F6',
                theme: 'modern'
            }
        }
    },
    
    mounted() {
        // Widget'ni ishga tushirish
        this.$refs.chatWidget.init(this.chatConfig);
    }
}
</script>
```

## ⚙️ Konfiguratsiya

ChatWidget quyidagi sozlamalarni qabul qiladi:

```javascript
const config = {
    // API sozlamalari
    apiUrl: '/api/widget',              // Widget API URL
    wsUrl: 'ws://localhost:8080',       // WebSocket URL
    
    // Ko'rinish sozlamalari
    position: 'bottom-right',           // 'bottom-right', 'bottom-left', 'top-right', 'top-left'
    primaryColor: '#3B82F6',            // Asosiy rang
    theme: 'modern',                    // 'modern', 'classic', 'minimal'
    
    // Xulq-atvor sozlamalari
    allowGuests: false,                 // Guest userlar yoza oladimi
    requireAuth: true,                  // Autentifikatsiya talab qilinadimi
    
    // Animation sozlamalari
    animations: {
        enabled: true,                  // Animatsiyalar yoqilgan
        openSpeed: 300,                 // Ochilish tezligi (ms)
        bounceIntensity: 'normal'       // 'none', 'subtle', 'normal', 'strong'
    }
};
```

## 🔐 Autentifikatsiya

ChatWidget autentifikatsiya qilingan userlar bilan ishlaydi:

### Laravel Sanctum bilan

```php
// routes/api.php
Route::middleware('auth:sanctum')->prefix('widget')->group(function () {
    Route::get('/session', [WidgetController::class, 'session']);
    Route::get('/chats/{chat}/messages', [WidgetController::class, 'getMessages']);
    Route::post('/chats/{chat}/messages', [WidgetController::class, 'sendWidgetMessage']);
});
```

### Frontend'da token bilan

```javascript
// Axios konfiguratsiyasi
axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('token');
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

// ChatWidget'ni ishga tushirish
const chatApp = window.ChatWidget.create('chat-widget', {
    apiUrl: '/api/widget',
    headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('token')
    }
});
```

## 🌐 WebSocket Integration

### Laravel Reverb sozlamalari

```bash
# .env faylida
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Reverb serverni ishga tushirish

```bash
php artisan reverb:start
```

### Frontend'da Echo sozlamalari

```javascript
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'my-app-key',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
```

## 📱 User Identification

Widget avtomatik ravishda user ma'lumotlarini ko'rsatadi:

- **Display Name** - To'liq ism yoki username
- **Avatar** - User rasmcha yoki boshlang'ich harf
- **Role Badge** - Support, Admin, User rollarini ko'rsatish
- **Online Status** - User online/offline holati

### Backend'da user ma'lumotlari

```php
// User model'da
public function getDisplayNameAttribute(): string
{
    if ($this->first_name && $this->last_name) {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    return $this->username ?: $this->email ?: 'Unknown User';
}

public function getAvatarUrlAttribute(): string
{
    if ($this->image) {
        return asset('storage/' . $this->image);
    }
    
    $name = urlencode($this->display_name);
    return "https://ui-avatars.com/api/?name={$name}&background=3b82f6&color=fff";
}
```

## 🎨 Dizayn Customization

### CSS o'zgartirishlari

```css
/* Widget tugmasini o'zgartirish */
.widget-button {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%);
    width: 70px;
    height: 70px;
}

/* Chat oynasini o'zgartirish */
.chat-window {
    width: 400px;
    height: 600px;
    border-radius: 20px;
}

/* Header rangini o'zgartirish */
.chat-header {
    background: linear-gradient(135deg, #your-color1 0%, #your-color2 100%);
}
```

### JavaScript bilan dinamik ranglar

```javascript
const chatApp = window.ChatWidget.create('chat-widget', {
    primaryColor: '#ff6b6b',
    theme: 'modern',
    customStyles: {
        headerBackground: 'linear-gradient(135deg, #ff6b6b 0%, #ffd93d 100%)',
        buttonBackground: '#ff6b6b',
        messageBackground: '#ff6b6b'
    }
});
```

## 🔧 API Endpoints

ChatWidget quyidagi API endpointlardan foydalanadi:

### GET `/api/widget/session`
Current user session ma'lumotlari

```json
{
    "user": {
        "id": 1,
        "display_name": "John Doe",
        "avatar_url": "https://...",
        "role": "user",
        "is_online": true
    },
    "chat_id": 123,
    "operator": {
        "name": "Support Team",
        "avatar": "https://...",
        "is_online": true
    }
}
```

### GET `/api/widget/chats/{chat}/messages`
Chat xabarlarini olish

```json
{
    "messages": [
        {
            "id": 1,
            "message": "Salom!",
            "user_id": 1,
            "from_operator": false,
            "created_at": "2024-01-01T10:00:00",
            "user": {
                "display_name": "John Doe",
                "avatar_url": "https://...",
                "role": "user"
            }
        }
    ]
}
```

### POST `/api/widget/chats/{chat}/messages`
Yangi xabar yuborish

```json
{
    "message": "Salom, yordam kerak"
}
```

## 🚨 Xatoliklar va Debugging

### 1. WebSocket ulanish xatoliklari

```javascript
// Echo xatoliklarini kuzatish
window.Echo.connector.socket.on('error', (error) => {
    console.error('WebSocket xatolik:', error);
});

window.Echo.connector.socket.on('disconnect', () => {
    console.warn('WebSocket uzildi');
});
```

### 2. API xatoliklarini aniqlash

```javascript
// Vue component'da
methods: {
    async sendMessage() {
        try {
            await this.sendMessage();
        } catch (error) {
            if (error.response?.status === 401) {
                console.error('Autentifikatsiya xatolik');
                // Login sahifasiga yo'naltirish
            }
        }
    }
}
```

### 3. Debugging yoqish

```javascript
const chatApp = window.ChatWidget.create('chat-widget', {
    debug: true,  // Console'da batafsil loglar
    apiUrl: '/api/widget'
});
```

## 📋 Maslahatlar

1. **Performance**: Faqat kerakli sahifalarda widget'ni yuklang
2. **Mobile**: Widget avtomatik responsive, ammo test qilishni unutmang
3. **Notifikatsiya**: Browser notification'larini yoqishni so'rang
4. **Session**: User session'ni localStorage'da saqlang
5. **Error Handling**: Network xatoliklarni to'g'ri handle qiling

## 🔄 Update qilish

Widget'ni yangilash uchun:

```bash
# Fayllarni yangilash
cp new-vue-sdk.js /widget/vue-sdk.js
cp new-vue-styles.css /widget/vue-styles.css

# Cache'ni tozalash
php artisan cache:clear
```

## 💡 Qo'shimcha imkoniyatlar

- File yuklash support
- Voice message
- Emoji picker
- Dark/Light theme toggle
- Multi-language support
- Offline message storage

---

**Yordam kerak bo'lsa**: Widget'ning demo sahifasini `/chat/demo` dan ko'ring yoki texnik yordam uchun murojaat qiling.