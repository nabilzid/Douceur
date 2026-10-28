<template>
    <div class="mx-auto flex min-h-[65vh] max-w-7xl items-center justify-center px-4 py-10 sm:px-6 md:py-16">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-card lg:grid-cols-2">

            <!-- Panneau de bienvenue -->
            <div
                class="relative hidden flex-col justify-between overflow-hidden bg-chocolate p-10 text-white lg:flex xl:p-12"
            >
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-caramel/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-16 h-64 w-64 rounded-full bg-raspberry/10 blur-3xl"></div>

                <div class="relative z-10">
                    <span class="text-sm font-semibold uppercase tracking-[0.2em] text-cream">
                        ✨ Bienvenue chez Douceur
                    </span>

                    <h2 class="mt-8 font-display text-4xl font-bold leading-tight text-caramel sm:text-4xl xl:text-5xl">
                        Le plaisir de vous retrouver.
                    </h2>

                    <p class="mt-5 max-w-sm text-sm leading-7 text-white/80">
                        Retrouvez vos commandes et découvrez de nouvelles
                        gourmandises à partager.
                    </p>
                </div>

                <div class="relative z-10 mt-12">
                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-caramel/20">
                            <span class="text-2xl">🍰</span>
                        </div>

                        <div>
                            <p class="font-display font-semibold">
                                Une petite envie de douceur ?
                            </p>
                            <p class="mt-1 text-xs text-white/65">
                                Vos gourmandises vous attendent.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de connexion -->
            <div class="p-6 sm:p-10 lg:p-12">
                <div class="mx-auto max-w-md">
                    <RouterLink
                        to="/"
                        class="inline-flex items-center gap-2 text-sm font-medium text-muted transition-colors hover:text-caramel-dark"
                    >
                        <span aria-hidden="true">←</span>
                        Retour à l'accueil
                    </RouterLink>

                    <div class="mb-8 mt-8 text-center lg:text-left">
                        <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl bg-caramel/10 lg:mx-0">
                            <span class="text-3xl">👋</span>
                        </div>

                        <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                            Connexion
                        </h1>

                        <p class="mt-3 text-sm leading-6 text-muted">
                            Ravi de vous revoir ! Connectez-vous à votre compte
                            pour continuer.
                        </p>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="mb-1.5 block text-sm font-medium text-chocolate-soft"
                            >
                                Adresse email
                                <span class="text-raspberry">*</span>
                            </label>

                            <input
                                id="email"
                                v-model.trim="form.email"
                                type="email"
                                name="email"
                                autocomplete="email"
                                required
                                placeholder="vous@exemple.fr"
                                :disabled="loading"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            />
                        </div>

                        <!-- Mot de passe -->
                        <div>
                            <label
                                for="password"
                                class="mb-1.5 block text-sm font-medium text-chocolate-soft"
                            >
                                Mot de passe
                                <span class="text-raspberry">*</span>
                            </label>

                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                                placeholder="Votre mot de passe"
                                :disabled="loading"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            />
                        </div>

                        <!-- Message d'erreur -->
                        <div
                            v-if="error"
                            role="alert"
                            class="flex items-start gap-2 rounded-xl bg-raspberry/10 p-4 text-sm font-medium text-raspberry"
                        >
                            <span aria-hidden="true">⚠️</span>
                            <span>{{ error }}</span>
                        </div>

                        <!-- Bouton -->
                        <button
                            type="submit"
                            :disabled="loading"
                            class="flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-6 py-3.5 text-sm font-semibold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card disabled:transform-none disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <svg
                                v-if="loading"
                                class="h-5 w-5 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                />
                            </svg>

                            {{ loading ? 'Connexion en cours…' : 'Se connecter' }}

                            <span v-if="!loading" aria-hidden="true">→</span>
                        </button>
                    </form>

                    <div class="my-7 flex items-center gap-4">
                        <div class="h-px flex-1 bg-cream-dark"></div>
                        <span class="text-xs text-muted">Nouveau chez Douceur ?</span>
                        <div class="h-px flex-1 bg-cream-dark"></div>
                    </div>

                    <p class="text-center text-sm text-muted">
                        Vous n'avez pas encore de compte ?

                        <RouterLink
                            to="/inscription"
                            class="font-semibold text-caramel-dark transition-colors hover:text-caramel"
                        >
                            Créer un compte
                        </RouterLink>
                    </p>

                    <p class="mt-8 text-center text-xs leading-5 text-muted">
                        Connectez-vous pour retrouver votre espace et vos commandes.
                    </p>
                </div>
            </div>
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

const form = reactive({
    email: '',
    password: '',
});

const loading = ref(false);
const error = ref(null);

async function submit() {
    if (loading.value) return;

    loading.value = true;
    error.value = null;

    try {
        const user = await auth.login(form);

        // ✅ admin + merchant → dashboard admin
        const isStaff = ['merchant', 'admin'].includes(user.role);

        // Récupère le redirect éventuel et sécurise
        const redirect = route.query.redirect;
        const hasValidRedirect = typeof redirect === 'string' && redirect.startsWith('/');
        const redirectIsAdmin = hasValidRedirect && redirect.startsWith('/admin');

        // Si le redirect est /admin mais user n'est pas staff → ignorer
        const safeRedirect = redirectIsAdmin && !isStaff ? null : redirect;

        if (isStaff) {
            await router.push(safeRedirect || '/admin/dashboard');
        } else {
            await router.push(safeRedirect || '/');
        }
    } catch (e) {
        error.value =
            e.response?.data?.message ||
            e.message ||
            'Connexion impossible. Vérifiez vos identifiants et réessayez.';
    } finally {
        loading.value = false;
    }
}
</script>