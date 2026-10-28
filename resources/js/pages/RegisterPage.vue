<!-- Page d'inscription -->
<template>
    <div class="mx-auto flex min-h-[65vh] max-w-7xl items-center justify-center px-4 py-10 sm:px-6 md:py-16">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-card lg:grid-cols-2">

            <!-- Panneau de bienvenue -->
            <div
                class="relative hidden flex-col justify-between overflow-hidden bg-chocolate p-10 text-white lg:flex xl:p-12"
            >
                <!-- Effets lumineux -->
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-caramel/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-16 h-64 w-64 rounded-full bg-raspberry/10 blur-3xl"></div>
                <div class="pointer-events-none absolute bottom-1/3 right-0 h-40 w-40 rounded-full bg-caramel/10 blur-3xl"></div>

                <div class="relative z-10">
                    <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-caramel">
                        Bienvenue chez Douceur
                    </p>

                    <h2 class="font-display text-3xl font-bold leading-tight text-caramel sm:text-4xl">
                        Votre histoire gourmande commence ici.
                    </h2>

                    <p class="mt-5 text-base leading-7 text-white/80">
                        Créez votre compte pour découvrir nos pâtisseries artisanales
                        et retrouver facilement vos gourmandises préférées.
                    </p>
                </div>

                <!-- Carte décorative -->
                <div class="relative z-10 mt-12">
                    <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-caramel/20">
                            <span class="text-2xl">🎂</span>
                        </div>

                        <div>
                            <p class="font-display font-semibold">
                                La douceur se partage.
                            </p>
                            <p class="mt-1 text-xs leading-5 text-white/65">
                                Un compte, vos envies, mille occasions de se faire plaisir.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-2 text-xs text-white/50">
                        <span class="text-caramel-light">✦</span>
                        <span>Ravi de vous accueillir dans notre univers.</span>
                    </div>
                </div>
            </div>

            <!-- Formulaire d'inscription -->
            <div class="p-6 sm:p-10 lg:p-12">
                <div class="mx-auto max-w-md">

                    <!-- Retour -->
                    <RouterLink
                        to="/"
                        class="inline-flex items-center gap-2 text-sm font-medium text-muted transition-colors hover:text-caramel-dark"
                    >
                        <span aria-hidden="true">←</span>
                        Retour à l'accueil
                    </RouterLink>

                    <!-- En-tête -->
                    <div class="mb-8 mt-8">
                        <div class="mb-5 grid h-14 w-14 place-items-center rounded-2xl bg-caramel/10">
                            <span class="text-3xl">👋</span>
                        </div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
                            Rejoignez-nous
                        </p>

                        <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                            Créer un compte
                        </h1>

                        <p class="mt-3 text-sm leading-6 text-muted">
                            Quelques informations suffisent pour rejoindre
                            l'univers Douceur et retrouver vos gourmandises.
                        </p>
                    </div>

                    <!-- Formulaire -->
                    <form class="space-y-5" @submit.prevent="submit">

                        <!-- Nom -->
                        <div>
                            <label
                                for="name"
                                class="mb-1.5 block text-sm font-medium text-chocolate-soft"
                            >
                                Nom complet
                                <span class="text-raspberry">*</span>
                            </label>

                            <input
                                id="name"
                                v-model.trim="form.name"
                                type="text"
                                name="name"
                                autocomplete="name"
                                required
                                maxlength="255"
                                placeholder="Votre nom et prénom"
                                :disabled="loading"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            />
                        </div>

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
                                maxlength="255"
                                placeholder="vous@exemple.fr"
                                :disabled="loading"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            />
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label
                                for="phone"
                                class="mb-1.5 block text-sm font-medium text-chocolate-soft"
                            >
                                Téléphone
                                <span class="text-xs font-normal text-muted">(facultatif)</span>
                            </label>

                            <input
                                id="phone"
                                v-model.trim="form.phone"
                                type="tel"
                                name="phone"
                                autocomplete="tel"
                                maxlength="30"
                                placeholder="06 12 34 56 78"
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
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder="8 caractères minimum"
                                :disabled="loading"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                            />

                            <p class="mt-2 text-xs leading-5 text-muted">
                                Choisissez un mot de passe d'au moins 8 caractères.
                            </p>
                        </div>

                        <!-- Confirmation -->
                        <div>
                            <label
                                for="password_confirmation"
                                class="mb-1.5 block text-sm font-medium text-chocolate-soft"
                            >
                                Confirmer le mot de passe
                                <span class="text-raspberry">*</span>
                            </label>

                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder="Retapez votre mot de passe"
                                :disabled="loading"
                                :aria-invalid="passwordMismatch || undefined"
                                :aria-describedby="passwordMismatch ? 'password-error' : undefined"
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                                :class="{ 'border-raspberry focus:border-raspberry': passwordMismatch }"
                            />

                            <p
                                v-if="passwordMismatch"
                                id="password-error"
                                class="mt-2 text-xs font-medium text-raspberry"
                            >
                                Les deux mots de passe ne correspondent pas.
                            </p>
                        </div>

                        <!-- Erreur serveur -->
                        <div
                            v-if="error"
                            role="alert"
                            class="flex items-start gap-3 rounded-xl bg-raspberry/10 p-4 text-sm font-medium text-raspberry"
                        >
                            <span aria-hidden="true">⚠️</span>
                            <span>{{ error }}</span>
                        </div>

                        <!-- Bouton d'inscription -->
                        <button
                            type="submit"
                            :disabled="loading || passwordMismatch"
                            class="flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-6 py-3.5 text-sm font-semibold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card disabled:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60"
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

                            {{ loading ? 'Création du compte…' : 'Créer mon compte' }}

                            <span v-if="!loading" aria-hidden="true">→</span>
                        </button>
                    </form>

                    <!-- Séparateur -->
                    <div class="my-7 flex items-center gap-4">
                        <div class="h-px flex-1 bg-cream-dark"></div>
                        <span class="text-xs text-muted">Déjà membre ?</span>
                        <div class="h-px flex-1 bg-cream-dark"></div>
                    </div>

                    <!-- Connexion -->
                    <p class="text-center text-sm text-muted">
                        Vous avez déjà un compte ?

                        <RouterLink
                            to="/connexion"
                            class="font-semibold text-caramel-dark transition-colors hover:text-caramel"
                        >
                            Se connecter
                        </RouterLink>
                    </p>

                    <!-- Pied de page -->
                    <p class="mt-8 text-center text-xs leading-5 text-muted">
                        En créant votre compte, vous rejoignez l'univers
                        gourmand de Douceur.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = reactive({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const error = ref(null);

const passwordMismatch = computed(() => {
    return (
        form.password_confirmation.length > 0 &&
        form.password !== form.password_confirmation
    );
});

async function submit() {
    if (loading.value) return;

    error.value = null;

    if (form.password !== form.password_confirmation) {
        error.value = 'Les deux mots de passe ne correspondent pas.';
        return;
    }

    if (form.password.length < 8) {
        error.value = 'Le mot de passe doit contenir au moins 8 caractères.';
        return;
    }

    loading.value = true;

    try {
        await auth.register({ ...form });
        await router.push('/');
    } catch (e) {
        const errors = e.response?.data?.errors;

        error.value =
            errors
                ? Object.values(errors).flat().join(' ')
                : e.response?.data?.message ||
                  e.message ||
                  'Impossible de créer votre compte. Veuillez réessayer.';
    } finally {
        loading.value = false;
    }
}
</script>
```