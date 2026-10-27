<template>
    <div class="auth-page">
        <div class="auth-card">
            <h1>Connexion</h1>
            <p class="subtitle">Ravi de vous revoir !</p>
            <form @submit.prevent="submit">
                <label>Email<input v-model="form.email" type="email" required /></label>
                <label>Mot de passe<input v-model="form.password" type="password" required /></label>
                <p v-if="error" class="error">{{ error }}</p>
                <button type="submit" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Connexion…' : 'Se connecter' }}
                </button>
            </form>
            <p class="switch">Pas de compte ? <RouterLink to="/inscription">S'inscrire</RouterLink></p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const form = reactive({ email: '', password: '' });
const loading = ref(false);
const error = ref(null);

async function submit() {
    loading.value = true;
    error.value = null;
    try {
        const user = await auth.login(form);
        // if (['merchant', 'admin'].includes(user.role)) {
        if (user.role === 'merchant') {
            router.push('/admin/dashboard');
        } else {
            router.push(route.query.redirect || '/');
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-page { display: flex; justify-content: center; padding: 4rem 1rem; }
.auth-card { background: white; padding: 2.5rem; border-radius: var(--radius); box-shadow: var(--shadow-md); width: 100%; max-width: 420px; }
.auth-card h1 { font-size: 2rem; margin-bottom: 0.5rem; text-align: center; }
.subtitle { text-align: center; color: var(--text-muted); margin-bottom: 2rem; }
form { display: flex; flex-direction: column; gap: 1rem; }
label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
input { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); background: var(--cream); font-size: 0.95rem; }
input:focus { outline: none; border-color: var(--caramel); background: white; }
.btn-primary { width: 100%; justify-content: center; margin-top: 0.5rem; }
.error { color: var(--raspberry); font-size: 0.9rem; }
.switch { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted); }
</style>