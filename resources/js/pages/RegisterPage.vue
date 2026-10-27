<template>
    <div class="auth-page">
        <div class="auth-card">
            <h1>Créer un compte</h1>
            <p class="subtitle">Rejoignez Douceur</p>
            <form @submit.prevent="submit">
                <label>Nom<input v-model="form.name" type="text" required /></label>
                <label>Email<input v-model="form.email" type="email" required /></label>
                <label>Téléphone<input v-model="form.phone" type="tel" /></label>
                <label>Mot de passe<input v-model="form.password" type="password" required minlength="8" /></label>
                <label>Confirmer<input v-model="form.password_confirmation" type="password" required /></label>
                <p v-if="error" class="error">{{ error }}</p>
                <button type="submit" class="btn-primary" :disabled="loading">
                    {{ loading ? 'Création…' : 'Créer mon compte' }}
                </button>
            </form>
            <p class="switch">Déjà inscrit ? <RouterLink to="/connexion">Se connecter</RouterLink></p>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const form = reactive({ name: '', email: '', phone: '', password: '', password_confirmation: '' });
const loading = ref(false);
const error = ref(null);

async function submit() {
    loading.value = true;
    error.value = null;
    try {
        await auth.register(form);
        router.push('/');
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
/* réutilisez le style de LoginPage */
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