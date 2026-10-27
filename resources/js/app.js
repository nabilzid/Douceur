import '../css/app.css';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import { useSettingsStore } from './stores/settings';

// Configuration Axios pour Sanctum SPA
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
window.axios = axios;

// Pinia avec persistance
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

// Créer l'app
const app = createApp(App);

// Brancher les plugins
app.use(pinia);
app.use(router);

const settings = useSettingsStore();
settings.fetchDelivery().finally(() => {
    app.mount('#app');
});

// Monter
// app.mount('#app');