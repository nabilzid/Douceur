<template>
    <div class="min-h-screen bg-cream flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-lg">

            <!-- ═══ CAS : Lien invalide ═══ -->
            <template v-if="status === 'invalid'">
                <div class="text-center">
                    <div class="mx-auto mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full bg-raspberry/10">
                        <span class="text-4xl" aria-hidden="true">⚠️</span>
                    </div>

                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-raspberry">
                        Douceur · Vérification
                    </p>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Lien invalide
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-muted sm:text-base">
                        Ce lien a peut-être déjà été utilisé, ou il est incorrect.
                        Vous pouvez demander un nouveau lien de vérification.
                    </p>
                </div>

                <div class="mt-8 rounded-3xl bg-white p-6 shadow-card sm:p-8">
                    <button
                        type="button"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-caramel-dark disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="sending || cooldown > 0"
                        @click="resend"
                    >
                        <span v-if="sending">Envoi…</span>
                        <span v-else-if="cooldown > 0">Renvoyer dans {{ cooldown }}s</span>
                        <span v-else>Recevoir un nouveau lien</span>
                    </button>

                    <Transition name="fade">
                        <div v-if="message" class="mt-4 flex items-start gap-2 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800" role="status">
                            <span class="text-base" aria-hidden="true">✅</span>
                            <span>{{ message }}</span>
                        </div>
                    </Transition>

                    <Transition name="fade">
                        <div v-if="error" class="mt-4 flex items-start gap-2 rounded-xl bg-raspberry/10 p-3 text-sm text-raspberry" role="alert">
                            <span class="text-base" aria-hidden="true">⚠️</span>
                            <span>{{ error }}</span>
                        </div>
                    </Transition>

                    <div class="mt-6 border-t border-cream-dark pt-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <button
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-chocolate transition hover:bg-cream"
                                @click="logout"
                            >
                                Se déconnecter
                            </button>
                            <RouterLink
                                to="/connexion"
                                class="rounded-full px-4 py-2 text-center text-sm font-semibold text-caramel-dark transition hover:bg-cream"
                                @click="logout"
                            >
                                Se reconnecter
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ═══ CAS : Lien expiré ═══ -->
            <template v-else-if="status === 'expired'">
                <div class="text-center">
                    <div class="mx-auto mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full bg-amber-100">
                        <span class="text-4xl" aria-hidden="true">⏱️</span>
                    </div>

                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-amber-700">
                        Douceur · Vérification
                    </p>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Lien expiré
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-muted sm:text-base">
                        Ce lien de vérification a expiré (valable 60 minutes).
                        Demandez un nouveau lien pour activer votre compte.
                    </p>
                </div>

                <div class="mt-8 rounded-3xl bg-white p-6 shadow-card sm:p-8">
                    <button
                        type="button"
                        class="flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-caramel-dark disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="sending || cooldown > 0"
                        @click="resend"
                    >
                        <span v-if="sending">Envoi…</span>
                        <span v-else-if="cooldown > 0">Renvoyer dans {{ cooldown }}s</span>
                        <span v-else>Recevoir un nouveau lien</span>
                    </button>

                    <Transition name="fade">
                        <div v-if="message" class="mt-4 flex items-start gap-2 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800" role="status">
                            <span class="text-base" aria-hidden="true">✅</span>
                            <span>{{ message }}</span>
                        </div>
                    </Transition>

                    <Transition name="fade">
                        <div v-if="error" class="mt-4 flex items-start gap-2 rounded-xl bg-raspberry/10 p-3 text-sm text-raspberry" role="alert">
                            <span class="text-base" aria-hidden="true">⚠️</span>
                            <span>{{ error }}</span>
                        </div>
                    </Transition>

                    <div class="mt-6 border-t border-cream-dark pt-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <button
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-chocolate transition hover:bg-cream"
                                @click="logout"
                            >
                                Se déconnecter
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ═══ CAS : En attente (par défaut) ═══ -->
            <template v-else>
                <!-- En-tête visuel -->
                <div class="mb-8 text-center">
                    <div class="mx-auto mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full bg-caramel/10">
                        <span class="text-4xl" aria-hidden="true">📧</span>
                    </div>

                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-caramel-dark">
                        Douceur · Vérification
                    </p>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Vérifiez votre email
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-muted sm:text-base">
                        Un email de confirmation vient d'être envoyé à
                        <strong class="text-chocolate">{{ maskedEmail }}</strong>.
                        <br />
                        Cliquez sur le lien dans le mail pour activer votre compte.
                    </p>
                </div>

                <!-- Carte principale -->
                <div class="rounded-3xl bg-white p-6 shadow-card sm:p-8">

                    <!-- Bloc info -->
                    <div class="rounded-2xl bg-cream/70 p-4 text-sm leading-6 text-chocolate-soft">
                        <p class="flex items-start gap-2">
                            <span class="text-lg" aria-hidden="true">⏱️</span>
                            <span>
                                Le lien est valable <strong>60 minutes</strong> et ne peut être utilisé qu'<strong>une seule fois</strong>.
                            </span>
                        </p>
                        <p class="mt-3 flex items-start gap-2">
                            <span class="text-lg" aria-hidden="true">📬</span>
                            <span>
                                Pensez à vérifier vos spams si vous ne le voyez pas dans les 2 minutes.
                            </span>
                        </p>
                    </div>

                    <!-- Bouton renvoyer -->
                    <div class="mt-6">
                        <p class="mb-3 text-sm font-medium text-chocolate">
                            Vous n'avez pas reçu le mail ?
                        </p>

                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-full border-2 border-caramel bg-white px-5 py-3.5 text-sm font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="sending || cooldown > 0"
                            @click="resend"
                        >
                            <svg
                                v-if="sending"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9" opacity=".25" />
                                <path d="M21 12a9 9 0 0 0-9-9" />
                            </svg>
                            <span v-if="sending">Envoi en cours…</span>
                            <span v-else-if="cooldown > 0">Renvoyer dans {{ cooldown }}s</span>
                            <span v-else>Renvoyer le mail</span>
                        </button>

                        <!-- Feedback -->
                        <Transition name="fade">
                            <div
                                v-if="message"
                                class="mt-4 flex items-start gap-2 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800"
                                role="status"
                            >
                                <span class="text-base" aria-hidden="true">✅</span>
                                <span>{{ message }}</span>
                            </div>
                        </Transition>

                        <Transition name="fade">
                            <div
                                v-if="error"
                                class="mt-4 flex items-start gap-2 rounded-xl bg-raspberry/10 p-3 text-sm text-raspberry"
                                role="alert"
                            >
                                <span class="text-base" aria-hidden="true">⚠️</span>
                                <span>{{ error }}</span>
                            </div>
                        </Transition>
                    </div>

                    <!-- Séparateur -->
                    <div class="mt-6 border-t border-cream-dark pt-6">
                        <p class="mb-3 text-center text-xs text-muted">
                            Mauvaise adresse email ?
                        </p>

                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <button
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold text-chocolate transition hover:bg-cream"
                                @click="logout"
                            >
                                Se déconnecter
                            </button>
                            <RouterLink
                                to="/inscription"
                                class="rounded-full px-4 py-2 text-center text-sm font-semibold text-caramel-dark transition hover:bg-cream"
                                @click="logout"
                            >
                                Créer un autre compte
                            </RouterLink>
                        </div>
                    </div>
                </div>

                <!-- Footer discret -->
                <p class="mt-6 text-center text-xs text-muted">
                    Besoin d'aide ? <a href="mailto:contact@douceur.fr" class="underline hover:text-chocolate">contact@douceur.fr</a>
                </p>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const status = ref(route.query.status || 'pending');
const sending = ref(false);
const message = ref('');
const error = ref('');
const cooldown = ref(0);

const maskedEmail = computed(() => {
    const email = auth.user?.email;
    if (!email) return 'votre adresse';
    const [local, domain] = email.split('@');
    if (!domain) return email;
    const visible = local.slice(0, 2);
    return `${visible}***@${domain}`;
});

let cooldownTimer = null;

function startCooldown(seconds = 60) {
    cooldown.value = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        cooldown.value = Math.max(0, cooldown.value - 1);
        if (cooldown.value === 0) {
            clearInterval(cooldownTimer);
            cooldownTimer = null;
        }
    }, 1000);
}

async function resend() {
    if (sending.value || cooldown.value > 0) return;

    sending.value = true;
    message.value = '';
    error.value = '';

    try {
        const { data } = await axios.post('/api/email/verification-notification');
        message.value = data.message || 'Un nouvel email a été envoyé.';
        status.value = 'pending'; // repasser en mode attente après renvoi
        startCooldown(60);
    } catch (e) {
        const httpStatus = e.response?.status;

        if (httpStatus === 429) {
            error.value = 'Trop de tentatives. Patientez une minute avant de réessayer.';
            startCooldown(60);
        } else if (httpStatus === 422) {
            error.value = e.response?.data?.message || 'Votre email est déjà vérifié.';
        } else if (httpStatus === 401) {
            error.value = 'Session expirée. Veuillez vous reconnecter.';
        } else {
            error.value = e.response?.data?.message || 'Impossible d\'envoyer le mail. Réessayez plus tard.';
        }
    } finally {
        sending.value = false;
    }
}

async function logout() {
    try {
        await axios.post('/api/logout');
    } catch {
        // ignore
    }
    auth.user = null;
    router.push('/connexion');
}

onMounted(async () => {
    // Si succès → /mon-compte
    if (route.query.status === 'success' || route.query.status === 'already-verified') {
        router.replace('/mon-compte');
        return;
    }

    // Charger l'utilisateur
    if (!auth.user) {
        await auth.fetchUser();
    }

    // Si déjà vérifié → /mon-compte
    if (auth.user?.email_verified_at) {
        router.replace('/mon-compte');
        return;
    }

    // Pas connecté → /connexion
    if (!auth.user) {
        router.replace('/connexion');
        return;
    }
});

onUnmounted(() => {
    if (cooldownTimer) clearInterval(cooldownTimer);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>