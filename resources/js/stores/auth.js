import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const isAuthenticated = computed(() => !!user.value);

    /**
     * Récupère l'utilisateur courant.
     * Axios est configuré globalement avec withCredentials + withXSRFToken,
     * donc le cookie de session et le CSRF sont gérés automatiquement.
     */
    async function fetchUser() {
        try {
            const { data } = await axios.get('/api/me');
            user.value = data.user;
        } catch {
            user.value = null;
        }
    }

    /**
     * Demande un nouveau cookie CSRF.
     * Indispensable avant toute requête mutante (login, register, logout).
     */
    async function csrf() {
        await axios.get('/sanctum/csrf-cookie');
    }

    async function register(payload) {
        loading.value = true;
        error.value = null;
        try {
            await csrf();
            const { data } = await axios.post('/api/register', payload);
            user.value = data.user;
            return data.user;
        } catch (e) {
            error.value = e.response?.data?.message || 'Erreur lors de l’inscription';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function login(payload) {
        loading.value = true;
        error.value = null;
        try {
            await csrf();
            const { data } = await axios.post('/api/login', payload);
            user.value = data.user;
            return data.user;
        } catch (e) {
            error.value = e.response?.data?.message || 'Identifiants incorrects';
            throw e;
        } finally {
            loading.value = false;
        }
    }

    async function logout() {
        await csrf();
        await axios.post('/api/logout');
        user.value = null;
    }

    return { user, loading, error, isAuthenticated, fetchUser, register, login, logout };
});