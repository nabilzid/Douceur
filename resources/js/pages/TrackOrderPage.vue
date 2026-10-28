<!-- Suivi de commande client invité -->
<template>
    <main class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6 sm:py-14">
        <!-- En-tête -->
        <header class="mb-8 text-center sm:mb-10">
            <span class="mb-4 inline-flex items-center gap-2 rounded-full bg-caramel/10 px-4 py-2 text-sm font-semibold text-caramel-dark">
                <span aria-hidden="true">📦</span>
                Suivi de commande
            </span>

            <h1 class="font-display text-3xl font-bold tracking-tight text-chocolate sm:text-4xl">
                Suivre ma commande
            </h1>

            <p
                v-if="!isAuthenticated"
                class="mx-auto mt-3 max-w-xl leading-7 text-muted"
            >
                Entrez votre référence de commande et l’email ou le téléphone utilisé lors de l’achat.
            </p>

            <p
                v-else
                class="mx-auto mt-3 max-w-xl leading-7 text-muted"
            >
                Saisissez votre référence de commande pour retrouver les détails de votre achat.
            </p>
        </header>

        <!-- Formulaire de recherche -->
        <form
            v-if="!order"
            class="space-y-5 rounded-3xl bg-white p-5 shadow-card sm:p-8"
            @submit.prevent="track"
        >
            <div class="space-y-2">
                <label
                    for="order-reference"
                    class="block text-sm font-semibold text-chocolate"
                >
                    Référence de commande
                </label>

                <input
                    id="order-reference"
                    v-model.trim="form.reference"
                    type="text"
                    name="reference"
                    placeholder="CMD-20261025-XXXX"
                    autocomplete="off"
                    required
                    class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white focus:ring-4 focus:ring-caramel/10"
                />

                <p class="text-xs leading-5 text-muted">
                    Vous trouverez cette référence dans l’email de confirmation de votre commande.
                </p>
            </div>

            <div
                v-if="!isAuthenticated"
                class="space-y-2"
            >
                <label
                    for="order-contact"
                    class="block text-sm font-semibold text-chocolate"
                >
                    Email ou téléphone
                </label>

                <input
                    id="order-contact"
                    v-model.trim="form.contact"
                    type="text"
                    name="contact"
                    placeholder="email@example.com ou 06..."
                    autocomplete="email"
                    required
                    class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white focus:ring-4 focus:ring-caramel/10"
                />

                <p class="text-xs leading-5 text-muted">
                    Utilisez les coordonnées renseignées lors de votre achat.
                </p>
            </div>

            <!-- Erreur -->
            <div
                v-if="error"
                role="alert"
                class="flex items-start gap-3 rounded-xl border border-raspberry/20 bg-rose-50 p-4 text-sm text-raspberry"
            >
                <span class="text-lg" aria-hidden="true">⚠️</span>
                <p>{{ error }}</p>
            </div>

            <button
                type="submit"
                :disabled="loading || !form.reference.trim() || (!isAuthenticated && !form.contact.trim())"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-caramel px-6 py-3.5 font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span
                    v-if="loading"
                    class="h-5 w-5 animate-spin rounded-full border-2 border-white/40 border-t-white"
                    aria-hidden="true"
                ></span>

                <span>
                    {{ loading ? 'Recherche en cours…' : 'Suivre ma commande' }}
                </span>

                <span v-if="!loading" aria-hidden="true">→</span>
            </button>

            <p class="text-center text-xs text-muted">
                <span aria-hidden="true">🔒</span>
                Vos informations servent uniquement à retrouver votre commande.
            </p>
        </form>

        <!-- Détails de la commande -->
        <section
            v-else
            class="animate-fade-up space-y-5"
            aria-label="Détails de la commande"
        >
            <div class="overflow-hidden rounded-3xl bg-white shadow-card">
                <!-- En-tête de la commande -->
                <div class="border-b border-cream-dark p-5 sm:p-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="mb-2 text-sm font-medium text-muted">
                                Référence de commande
                            </p>

                            <h2 class="break-all font-display text-2xl font-bold text-chocolate sm:text-3xl">
                                {{ order.reference }}
                            </h2>

                            <p class="mt-3 text-sm text-muted">
                                <span aria-hidden="true">📅</span>
                                Commandée le {{ formatDate(order.created_at) }}
                            </p>
                        </div>

                        <span
                            :class="statusClasses(order.status)"
                            class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-current"
                                aria-hidden="true"
                            ></span>
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div class="p-5 sm:p-8">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-caramel/10 text-xl">
                            🧁
                        </div>

                        <div>
                            <h3 class="font-display text-xl font-bold text-chocolate">
                                Récapitulatif
                            </h3>
                            <p class="text-sm text-muted">
                                Les produits de votre commande
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="order.items?.length"
                        class="divide-y divide-cream-dark"
                    >
                        <div
                            v-for="(item, i) in order.items"
                            :key="item.id ?? `${item.product_name}-${i}`"
                            class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-4"
                        >
                            <div class="min-w-0">
                                <p class="font-medium text-chocolate">
                                    {{ item.product_name }}
                                </p>
                                <p class="mt-1 text-sm text-muted">
                                    Quantité : {{ item.quantity }}
                                </p>
                            </div>

                            <p class="shrink-0 text-right font-semibold text-chocolate">
                                {{ formatPrice(item.line_total) }}
                            </p>
                        </div>
                    </div>

                    <p
                        v-else
                        class="rounded-xl bg-cream p-4 text-sm text-muted"
                    >
                        Aucun article à afficher pour cette commande.
                    </p>

                    <!-- Totaux -->
                    <div class="mt-2 space-y-3 border-t border-cream-dark pt-5">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="text-muted">Sous-total</span>
                            <span class="font-medium text-chocolate">
                                {{ formatPrice(order.subtotal) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="text-muted">
                                {{ order.delivery_type === 'delivery' ? 'Livraison' : 'Retrait' }}
                            </span>

                            <span
                                :class="Number(order.delivery_fee) > 0 ? 'font-medium text-chocolate' : 'font-semibold text-emerald-700'"
                            >
                                {{ Number(order.delivery_fee) > 0 ? formatPrice(order.delivery_fee) : 'Offerte' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 border-t-2 border-cream-dark pt-4">
                            <span class="font-display text-lg font-bold text-chocolate">
                                Total
                            </span>

                            <span class="font-display text-2xl font-bold text-caramel-dark">
                                {{ formatPrice(order.total_amount) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Adresse de livraison -->
                <div
                    v-if="order.delivery_type === 'delivery'"
                    class="px-5 pb-5 sm:px-8 sm:pb-8"
                >
                    <div class="rounded-2xl bg-cream p-5 sm:p-6">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-xl">
                                📍
                            </div>

                            <h3 class="font-display text-lg font-bold text-chocolate">
                                Adresse de livraison
                            </h3>
                        </div>

                        <p class="whitespace-pre-line text-sm leading-6 text-chocolate">
                            {{ order.delivery_address || 'Adresse non renseignée' }}
                        </p>

                        <p
                            v-if="order.delivery_postal_code || order.delivery_city"
                            class="mt-1 text-sm text-muted"
                        >
                            {{ order.delivery_postal_code }}
                            {{ order.delivery_city }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Nouvelle recherche -->
            <button
                type="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-caramel bg-white px-6 py-3.5 font-semibold text-caramel-dark transition hover:bg-caramel/5 focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2"
                @click="reset"
            >
                <span aria-hidden="true">←</span>
                Rechercher une autre commande
            </button>
        </section>
    </main>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const auth = useAuthStore();

const form = reactive({
    reference: '',
    contact: '',
});

const order = ref(null);
const loading = ref(false);
const error = ref(null);

const isAuthenticated = computed(() => Boolean(auth?.isAuthenticated));

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(price) || 0);
}

function formatDate(date) {
    if (!date) return 'Date indisponible';

    const parsedDate = new Date(date);

    if (Number.isNaN(parsedDate.getTime())) {
        return 'Date indisponible';
    }

    return parsedDate.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function statusLabel(status) {
    const labels = {
        pending: 'En attente',
        confirmed: 'Confirmée',
        preparing: 'En préparation',
        ready: 'Prête',
        delivering: 'En livraison',
        delivered: 'Livrée',
        cancelled: 'Annulée',
    };

    return labels[status] || status || 'Statut inconnu';
}

function statusClasses(status) {
    const classes = {
        pending: 'bg-amber-50 text-amber-800',
        confirmed: 'bg-caramel/10 text-caramel-dark',
        preparing: 'bg-caramel/10 text-caramel-dark',
        ready: 'bg-emerald-50 text-emerald-700',
        delivering: 'bg-sky-50 text-sky-700',
        delivered: 'bg-emerald-50 text-emerald-700',
        cancelled: 'bg-rose-50 text-raspberry',
    };

    return classes[status] || 'bg-cream-dark text-chocolate';
}

async function track() {
    if (loading.value) return;

    loading.value = true;
    error.value = null;

    try {
        const payload = {
            reference: form.reference.trim(),
        };

        // Le contact est uniquement envoyé pour les visiteurs non connectés.
        if (!isAuthenticated.value) {
            payload.contact = form.contact.trim();
        }

        const { data } = await axios.post('/api/orders/track', payload);

        order.value = data;
    } catch (e) {
        error.value =
            e.response?.data?.message ||
            'Commande introuvable. Vérifiez votre référence et vos coordonnées.';
    } finally {
        loading.value = false;
    }
}

function reset() {
    order.value = null;
    form.reference = '';
    form.contact = '';
    error.value = null;
}

onMounted(async () => {
    // Essayer de charger l'utilisateur sans bloquer la page en cas d'échec.
    if (auth && !auth.user && typeof auth.fetchUser === 'function') {
        try {
            await auth.fetchUser();
        } catch {
            // La recherche en tant que visiteur reste possible.
        }
    }

    // Préremplir la référence depuis les paramètres de l'URL.
    const reference = route.query.reference;

    if (typeof reference === 'string' && reference.trim()) {
        form.reference = reference.trim();

        // Pour un client connecté, lancer automatiquement la recherche.
        if (isAuthenticated.value) {
            await track();
        }
    }

    // Préremplir le contact si l'URL le fournit.
    const contact = route.query.contact;

    if (typeof contact === 'string') {
        form.contact = contact;
    }
});
</script>