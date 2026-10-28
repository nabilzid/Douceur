<!-- Suivi de demande -->
<template>
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 md:py-16">
        <!-- En-tête -->
        <header v-if="!order" class="mb-8 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-caramel/10 text-3xl">
                🎂
            </div>

            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-caramel-dark">
                L'univers Douceur
            </p>

            <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                Suivre ma demande sur mesure
            </h1>

            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-muted sm:text-base">
                <template v-if="!isAuthenticated">
                    Entrez votre référence et l'email ou le téléphone utilisé lors de votre demande.
                </template>
                <template v-else>
                    Retrouvez les détails de votre demande personnalisée grâce à sa référence.
                </template>
            </p>
        </header>

        <!-- Formulaire de recherche -->
        <form
            v-if="!order"
            class="mx-auto max-w-2xl space-y-5 rounded-3xl bg-white p-6 shadow-card sm:p-10"
            @submit.prevent="track"
        >
            <div>
                <label for="reference" class="mb-2 block text-sm font-semibold text-chocolate">
                    Référence de la demande
                </label>

                <input
                    id="reference"
                    v-model.trim="form.reference"
                    type="text"
                    placeholder="SUR-20261025-XXXX"
                    autocomplete="off"
                    required
                    class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white focus:ring-4 focus:ring-caramel/10"
                />
            </div>

            <div v-if="!isAuthenticated">
                <label for="contact" class="mb-2 block text-sm font-semibold text-chocolate">
                    Email ou téléphone
                </label>

                <input
                    id="contact"
                    v-model.trim="form.contact"
                    type="text"
                    placeholder="email@example.com ou 06..."
                    autocomplete="email"
                    required
                    class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white focus:ring-4 focus:ring-caramel/10"
                />

                <p class="mt-2 text-xs leading-5 text-muted">
                    Utilisez les coordonnées associées à votre demande pour protéger vos informations.
                </p>
            </div>

            <div
                v-if="error"
                role="alert"
                class="rounded-xl border border-raspberry/20 bg-raspberry/10 px-4 py-3 text-sm text-raspberry"
            >
                {{ error }}
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-caramel-dark focus:outline-none focus:ring-4 focus:ring-caramel/20 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg
                    v-if="loading"
                    class="h-5 w-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-opacity=".25"
                        stroke-width="3"
                    />
                    <path
                        d="M21 12a9 9 0 0 0-9-9"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-linecap="round"
                    />
                </svg>

                <span>{{ loading ? 'Recherche en cours…' : 'Suivre ma demande' }}</span>
            </button>
        </form>

        <!-- Détails de la demande -->
        <div v-else class="space-y-6">
            <!-- En-tête de commande -->
            <section class="overflow-hidden rounded-3xl bg-white shadow-card">
                <div class="bg-chocolate p-6 text-white sm:p-8">
                    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-cream/80">
                                Votre demande personnalisée
                            </p>

                            <h1 class="font-display text-2xl font-bold sm:text-3xl">
                                {{ order.reference }}
                            </h1>

                            <p class="mt-3 text-sm leading-6 text-white/80">
                                🎂 {{ order.event_label }}
                                <span v-if="order.event_date">
                                    · 📅 {{ formatDate(order.event_date) }}
                                </span>
                            </p>
                        </div>

                        <span
                            :class="[
                                'inline-flex w-fit items-center rounded-full px-4 py-2 text-xs font-semibold',
                                {
                                    'bg-caramel/20 text-cream': order.status === 'quoted',
                                    'bg-emerald-100 text-emerald-800':
                                        ['deposit_paid', 'completed'].includes(order.status),
                                    'bg-blue-100 text-blue-800': order.status === 'accepted',
                                    'bg-raspberry/15 text-raspberry':
                                        ['rejected', 'refused_by_merchant', 'cancelled'].includes(order.status),
                                    'bg-white/15 text-white':
                                        ['pending_quote'].includes(order.status),
                                }
                            ]"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                </div>

                <!-- Description et livraison -->
                <div class="p-5 sm:p-8">
                    <h2 class="font-display text-xl font-bold text-chocolate">
                        Votre demande
                    </h2>

                    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-chocolate">
                        <span class="rounded-full bg-cream px-3 py-1.5">
                            🎂 {{ order.event_label }}
                        </span>

                        <span
                            v-if="order.guests_count"
                            class="rounded-full bg-cream px-3 py-1.5"
                        >
                            👥 {{ order.guests_count }} personnes
                        </span>

                        <span class="rounded-full bg-cream px-3 py-1.5">
                            {{ order.delivery_type === 'delivery'
                                ? '🚚 Livraison'
                                : '🏪 Click & Collect' }}
                        </span>
                    </div>

                    <p class="mt-5 whitespace-pre-line text-sm leading-7 text-muted">
                        {{ order.description }}
                    </p>

                    <div
                        v-if="order.delivery_type === 'delivery'"
                        class="mt-5 rounded-2xl border-l-4 border-caramel bg-cream p-4 sm:p-5"
                    >
                        <h3 class="text-sm font-bold text-chocolate">
                            📍 Adresse de livraison
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-chocolate">
                            {{ order.delivery_address }}<br />
                            {{ order.delivery_postal_code }} {{ order.delivery_city }}
                        </p>

                        <p
                            v-if="order.delivery_notes"
                            class="mt-3 text-sm italic leading-6 text-muted"
                        >
                            💬 {{ order.delivery_notes }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Décision sur le devis -->
            <section
                v-if="order.status === 'quoted'"
                class="rounded-3xl border border-caramel/20 bg-white p-5 shadow-card sm:p-8"
            >
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-caramel-dark">
                        Votre prochaine étape
                    </p>

                    <h2 class="mt-2 font-display text-2xl font-bold text-chocolate">
                        {{ stepNumber('decision') }}. Votre décision
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-muted">
                        Choisissez le montant à régler pour accepter votre devis.
                    </p>
                </div>

                <!-- Choix de l'acompte -->
                <div class="grid gap-3 sm:grid-cols-3">
                    <button
                        v-for="pct in [20, 50, 100]"
                        :key="pct"
                        type="button"
                        :aria-pressed="selectedPercentage === pct"
                        :class="[
                            'flex flex-col items-center rounded-2xl border-2 p-4 text-center transition',
                            selectedPercentage === pct
                                ? 'border-caramel bg-caramel text-white shadow-soft'
                                : 'border-cream-dark bg-cream text-chocolate hover:border-caramel'
                        ]"
                        @click="selectedPercentage = pct"
                    >
                        <span class="text-sm font-semibold">
                            {{ pct === 100 ? 'Paiement total' : `Acompte ${pct} %` }}
                        </span>

                        <span class="mt-2 text-lg font-bold">
                            {{ formatPrice(order.quoted_price * pct / 100) }}
                        </span>

                        <span class="mt-1 text-xs opacity-80">
                            {{ pct === 100 ? 'Tout régler maintenant' : 'Sur le montant du devis' }}
                        </span>
                    </button>
                </div>

                <!-- Récapitulatif financier -->
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-cream p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Total du devis
                        </p>

                        <p class="mt-2 text-xl font-bold text-chocolate">
                            {{ formatPrice(order.quoted_price) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-caramel/20 bg-caramel/5 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-caramel-dark">
                            {{ selectedPercentage === 100
                                ? 'Total à régler'
                                : `Acompte (${selectedPercentage} %)` }}
                        </p>

                        <p class="mt-2 text-xl font-bold text-chocolate">
                            {{ formatPrice(depositAmount) }}
                        </p>
                    </div>

                    <div
                        v-if="selectedPercentage < 100"
                        class="rounded-2xl bg-cream p-4 sm:col-span-2"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Solde restant
                        </p>

                        <p class="mt-2 text-lg font-bold text-chocolate">
                            {{ formatPrice(balanceAmount) }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <button
                        type="button"
                        class="flex flex-1 items-center justify-center rounded-full bg-caramel px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-caramel-dark focus:outline-none focus:ring-4 focus:ring-caramel/20 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="responding"
                        @click="acceptQuote"
                    >
                        <span v-if="responding">Traitement en cours…</span>
                        <span v-else>
                            ✓ Accepter et payer {{ formatPrice(depositAmount) }}
                        </span>
                    </button>

                    <button
                        type="button"
                        class="rounded-full border-2 border-raspberry/30 px-5 py-3.5 text-sm font-semibold text-raspberry transition hover:bg-raspberry/10 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="responding"
                        @click="rejectQuote"
                    >
                        ✕ Refuser le devis
                    </button>
                </div>

                <p class="mt-4 text-xs leading-6 text-muted">
                    <template v-if="selectedPercentage === 100">
                        Vous réglez la totalité du devis en ligne. Aucun solde ne restera à payer.
                    </template>

                    <template v-else>
                        Vous réglez {{ formatPrice(depositAmount) }} en ligne.
                        Le solde de {{ formatPrice(balanceAmount) }} sera à régler auprès du pâtissier
                        le jour de la livraison ou du retrait.
                    </template>
                </p>
            </section>

            <!-- Paiement confirmé -->
            <section
                v-if="order.amount_paid > 0"
                class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-card sm:p-8"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            Récapitulatif
                        </p>

                        <h2 class="mt-2 font-display text-2xl font-bold text-chocolate">
                            {{ stepNumber('payment') }}. Votre règlement
                        </h2>
                    </div>

                    <span class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">
                        {{ remainingAmount > 0 ? '✓ Acompte versé' : '✓ Payé intégralement' }}
                    </span>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-cream p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Total du devis
                        </p>

                        <p class="mt-2 text-lg font-bold text-chocolate">
                            {{ formatPrice(order.quoted_price) }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            {{ remainingAmount > 0 ? 'Acompte versé' : 'Montant versé' }}
                        </p>

                        <p class="mt-2 text-lg font-bold text-emerald-800">
                            {{ formatPrice(order.amount_paid) }}
                        </p>
                    </div>

                    <div
                        v-if="remainingAmount > 0"
                        class="rounded-2xl bg-cream p-4"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                            Reste à payer
                        </p>

                        <p class="mt-2 text-lg font-bold text-chocolate">
                            {{ formatPrice(remainingAmount) }}
                        </p>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="mt-6">
                    <div class="mb-2 flex items-center justify-between text-xs font-semibold text-muted">
                        <span>Progression du paiement</span>
                        <span>{{ paymentProgress }} %</span>
                    </div>

                    <div
                        class="h-3 overflow-hidden rounded-full bg-cream-dark"
                        role="progressbar"
                        :aria-valuenow="paymentProgress"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        :aria-label="`Paiement effectué à ${paymentProgress} %`"
                    >
                        <div
                            class="h-full rounded-full bg-caramel transition-all duration-500"
                            :style="{ width: paymentProgress + '%' }"
                        />
                    </div>
                </div>

                <div
                    class="mt-5 rounded-2xl border-l-4 p-4"
                    :class="remainingAmount > 0
                        ? 'border-caramel bg-caramel/5'
                        : 'border-emerald-600 bg-emerald-50'"
                >
                    <p class="font-semibold text-chocolate">
                        {{
                            remainingAmount > 0
                                ? `Reste à payer : ${formatPrice(remainingAmount)}`
                                : 'Votre commande est intégralement réglée.'
                        }}
                    </p>

                    <p class="mt-1 text-sm leading-6 text-muted">
                        {{
                            remainingAmount > 0
                                ? 'Le solde sera à régler auprès du pâtissier le jour de la livraison ou du retrait.'
                                : 'Aucun montant ne reste à régler pour votre devis.'
                        }}
                    </p>
                </div>
            </section>

            <!-- Documents -->
            <section
                v-if="order.quote_pdf_url || order.deposit_invoice_url || order.balance_invoice_url"
                class="rounded-3xl bg-white p-5 shadow-card sm:p-8"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-caramel-dark">
                    Vos fichiers
                </p>

                <h2 class="mt-2 font-display text-2xl font-bold text-chocolate">
                    {{ stepNumber('documents') }}. Mes documents
                </h2>

                <div class="mt-5 space-y-3">
                    <!-- Devis -->
                    <div class="flex flex-col justify-between gap-3 rounded-2xl border border-cream-dark p-4 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="text-sm font-semibold text-chocolate">
                                Devis
                            </h3>
                            <p class="mt-1 text-xs text-muted">
                                Document commercial initial
                            </p>
                        </div>

                        <a
                            v-if="order.quote_pdf_url"
                            :href="order.quote_pdf_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white"
                        >
                            Consulter le devis ↗
                        </a>

                        <span v-else class="text-xs text-muted">
                            Non disponible
                        </span>
                    </div>

                    <!-- Facture d'acompte -->
                    <div class="flex flex-col justify-between gap-3 rounded-2xl border border-cream-dark p-4 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="text-sm font-semibold text-chocolate">
                                Facture d'acompte
                            </h3>
                            <p class="mt-1 text-xs text-muted">
                                Justificatif du premier règlement
                            </p>
                        </div>

                        <a
                            v-if="order.deposit_invoice_url"
                            :href="order.deposit_invoice_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white"
                        >
                            Consulter la facture ↗
                        </a>

                        <span v-else class="text-xs text-muted">
                            Émise après paiement
                        </span>
                    </div>

                    <!-- Facture de solde -->
                    <div class="flex flex-col justify-between gap-3 rounded-2xl border border-cream-dark p-4 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="text-sm font-semibold text-chocolate">
                                Facture de solde
                            </h3>
                            <p class="mt-1 text-xs text-muted">
                                Document final après livraison
                            </p>
                        </div>

                        <a
                            v-if="order.balance_invoice_url"
                            :href="order.balance_invoice_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white"
                        >
                            Consulter la facture ↗
                        </a>

                        <span v-else class="text-xs text-muted">
                            À venir
                        </span>
                    </div>
                </div>
            </section>

            <!-- Nouvelle recherche -->
            <div class="text-center">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full border-2 border-caramel px-6 py-3 text-sm font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white focus:outline-none focus:ring-4 focus:ring-caramel/20"
                    @click="reset"
                >
                    ← Rechercher une autre demande
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const auth = useAuthStore();

/*
 * Protection du template si le store est momentanément indéfini.
 * Si auth reste undefined, vérifie aussi l'initialisation de Pinia
 * et l'export de useAuthStore dans ../stores/auth.
 */
const isAuthenticated = computed(() => Boolean(auth?.isAuthenticated));

const form = reactive({
    reference: '',
    contact: '',
});

const order = ref(null);
const loading = ref(false);
const error = ref(null);
const responding = ref(false);

const selectedPercentage = ref(20);

const depositAmount = computed(() => {
    if (!order.value) return 0;

    return Math.round(
        Number(order.value.quoted_price || 0) * selectedPercentage.value
    ) / 100;
});

const balanceAmount = computed(() => {
    if (!order.value) return 0;

    return Math.max(
        0,
        Number(order.value.quoted_price || 0) - depositAmount.value
    );
});

const remainingAmount = computed(() => {
    if (!order.value) return 0;

    return Math.max(
        0,
        Number(order.value.quoted_price || 0) - Number(order.value.amount_paid || 0)
    );
});

const paymentProgress = computed(() => {
    if (!order.value || !Number(order.value.quoted_price)) return 0;

    return Math.min(
        100,
        Math.round(
            (Number(order.value.amount_paid || 0) /
                Number(order.value.quoted_price)) * 100
        )
    );
});

/**
 * Numérotation dynamique des étapes visibles.
 */
const visibleSteps = computed(() => {
    if (!order.value) return [];

    const steps = [];

    if (order.value.quoted_price) steps.push('quote');
    if (order.value.status === 'quoted') steps.push('decision');
    if (Number(order.value.amount_paid) > 0) steps.push('payment');

    if (
        order.value.quote_pdf_url ||
        order.value.deposit_invoice_url ||
        order.value.balance_invoice_url
    ) {
        steps.push('documents');
    }

    return steps;
});

function stepNumber(key) {
    const index = visibleSteps.value.indexOf(key);

    return index >= 0 ? index + 1 : '';
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(price ?? 0));
}

function formatDate(date) {
    if (!date) return '';

    const parsedDate = new Date(date);

    if (Number.isNaN(parsedDate.getTime())) return '';

    return parsedDate.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function statusLabel(status) {
    return {
        pending_quote: 'En attente de devis',
        quoted: 'En attente de votre décision',
        accepted: 'Devis accepté',
        deposit_paid: 'Acompte réglé',
        completed: 'Terminée',
        rejected: 'Refusée',
        refused_by_merchant: 'Refusée',
        cancelled: 'Annulée',
    }[status] || status;
}

/**
 * Recherche d'une demande personnalisée.
 */
async function track() {
    if (loading.value) return;

    loading.value = true;
    error.value = null;

    try {
        const payload = {
            reference: form.reference.trim(),
        };

        if (!isAuthenticated.value) {
            payload.contact = form.contact.trim();
        }

        const { data } = await axios.post(
            '/api/custom-orders/track',
            payload
        );

        order.value = data;
        selectedPercentage.value = 20;
    } catch (e) {
        error.value =
            e.response?.data?.message ||
            'Demande introuvable. Vérifiez votre référence et vos coordonnées.';
    } finally {
        loading.value = false;
    }
}

/**
 * Acceptation du devis et redirection vers le paiement.
 */
async function acceptQuote() {
    if (responding.value || !order.value) return;

    responding.value = true;

    try {
        const { data } = await axios.put(
            `/api/custom-orders/${encodeURIComponent(order.value.reference)}/respond-guest`,
            {
                contact: form.contact.trim(),
                decision: 'accept',
                payment_percentage: selectedPercentage.value,
            }
        );

        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        } else if (data.status) {
            order.value.status = data.status;

            if (data.amount_paid !== undefined) {
                order.value.amount_paid = data.amount_paid;
            }
        }
    } catch (e) {
        window.alert(
            e.response?.data?.message || 'Une erreur est survenue.'
        );
    } finally {
        responding.value = false;
    }
}

/**
 * Refus du devis.
 */
async function rejectQuote() {
    if (responding.value || !order.value) return;

    const confirmed = window.confirm(
        'Voulez-vous vraiment refuser ce devis ?'
    );

    if (!confirmed) return;

    responding.value = true;

    try {
        const { data } = await axios.put(
            `/api/custom-orders/${encodeURIComponent(order.value.reference)}/respond-guest`,
            {
                contact: form.contact.trim(),
                decision: 'reject',
            }
        );

        order.value.status = data.status;
    } catch (e) {
        window.alert(
            e.response?.data?.message || 'Une erreur est survenue.'
        );
    } finally {
        responding.value = false;
    }
}

/**
 * Retour au formulaire de recherche.
 */
function reset() {
    order.value = null;
    form.reference = '';
    form.contact = '';
    error.value = null;
    selectedPercentage.value = 20;
}

/**
 * Chargement de l'utilisateur et recherche automatique
 * lorsque la référence est fournie dans l'URL.
 */
onMounted(async () => {
    try {
        if (auth && !auth.user && typeof auth.fetchUser === 'function') {
            await auth.fetchUser();
        }
    } catch (e) {
        console.error('Impossible de récupérer la session utilisateur :', e);
    }

    // ✅ Priorité 1 : :reference de l'URL signée (/demande/SUR-2026-XXX)
    // ✅ Priorité 2 : ?reference= du query string (/suivi-demande?reference=SUR-...)
    const referenceFromUrl = route.params.reference || route.query.reference;

    if (referenceFromUrl) {
        form.reference = String(referenceFromUrl);
    }

    if (route.query.contact) {
        form.contact = String(route.query.contact);
    }

    if (
        form.reference &&
        (isAuthenticated.value || form.contact)
    ) {
        await track();
    }
});
</script>