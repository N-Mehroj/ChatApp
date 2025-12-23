import "../css/app.css";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import type { DefineComponent } from "vue";
import { createApp, h } from "vue";
import { initializeTheme } from "./composables/useAppearance";

// Laravel Echo for real-time messaging
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

// Direct Echo initialization for immediate use
console.log("🔧 Initializing Echo directly...");

window.Echo = new Echo({
  broadcaster: "reverb",
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
  enabledTransports: ["ws", "wss"],
  auth: {
    headers: {
      "X-CSRF-TOKEN":
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
      Authorization: `Bearer ${
        document.querySelector('meta[name="api-token"]')?.getAttribute("content") || ""
      }`,
    },
  },
});

console.log("📊 Echo config:", {
  broadcaster: "reverb",
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
  key: import.meta.env.VITE_REVERB_APP_KEY?.substring(0, 8) + "...",
});

// Debug Pusher connection events
window.Echo.connector.pusher.connection.bind("connected", () => {
  console.log("✅ Pusher connected successfully");
});

window.Echo.connector.pusher.connection.bind("error", (error) => {
  console.error("🔴 Pusher connection error:", error);
});

window.Echo.connector.pusher.connection.bind("disconnected", () => {
  console.log("❌ Pusher disconnected");
});

console.log("🔧 Echo setup completed");

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  resolve: (name) =>
    resolvePageComponent(
      `./pages/${name}.vue`,
      import.meta.glob<DefineComponent>("./pages/**/*.vue")
    ),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
  progress: {
    color: "#4B5563",
  },
});

// This will set light / dark mode on page load...
initializeTheme();
