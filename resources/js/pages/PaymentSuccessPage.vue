<!-- Page vérification de paiement -->
<template>
    <main class="mx-auto flex min-h-[60vh] w-full max-w-5xl items-center justify-center px-4 py-12 sm:px-6 lg:py-16">
        <section
            class="w-full max-w-xl rounded-3xl bg-white p-6 text-center shadow-card sm:p-10"
            aria-live="polite"
        >
            <!-- Vérification en cours -->
            <div
                v-if="status === 'loading'"
                class="flex flex-col items-center gap-5"
            >
                <div
                    class="h-12 w-12 animate-spin rounded-full border-4 border-cream-dark border-t-caramel"
                    role="status"
                    aria-label="Vérification du paiement"
                ></div>

                <div class="space-y-2">
                    <h1 class="font-display text-2xl font-bold text-chocolate sm:text-3xl">
                        Vérification du paiement
                    </h1>
                    <p class="text-muted">
                        Nous vérifions la confirmation de votre paiement…
                    </p>
                </div>
            </div>

            <!-- Paiement confirmé -->
            <div
                v-else-if="status === 'paid'"
                class="flex flex-col items-center gap-5"
            >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-5xl">
                    🎉
                </div>

                <div class="space-y-3">
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">
                        Paiement confirmé
                    </span>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Merci pour votre commande !
                    </h1>

                    <p class="leading-7 text-muted">
                        Votre commande
                        <strong class="break-all text-chocolate">
                            {{ orderRef }}
                        </strong>
                        a bien été payée.
                    </p>

                    <p class="text-sm leading-6 text-muted">
                        Un email de confirmation vous sera envoyé.
                    </p>
                </div>

                <RouterLink
                    to="/mes-commandes"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-caramel px-6 py-3.5 font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2 sm:w-auto"
                >
                    Voir mes commandes
                    <span aria-hidden="true">→</span>
                </RouterLink>
            </div>

            <!-- Paiement en attente -->
            <div
                v-else-if="status === 'pending'"
                class="flex flex-col items-center gap-5"
            >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-amber-50 text-5xl">
                    ⏳
                </div>

                <div class="space-y-3">
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800">
                        Paiement en attente
                    </span>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Paiement en cours de traitement
                    </h1>

                    <p class="leading-7 text-muted">
                        Votre paiement n'est pas encore confirmé.
                        Cela peut prendre quelques instants.
                    </p>

                    <p class="text-sm text-muted">
                        Vous pouvez vérifier à nouveau son statut.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="checking"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-caramel px-6 py-3.5 font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                    @click="checkStatus"
                >
                    <span
                        v-if="checking"
                        class="h-5 w-5 animate-spin rounded-full border-2 border-white/40 border-t-white"
                        aria-hidden="true"
                    ></span>
                    {{ checking ? 'Vérification…' : 'Vérifier à nouveau' }}
                </button>
            </div>

            <!-- Paiement non confirmé -->
            <div
                v-else
                class="flex flex-col items-center gap-5"
            >
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-rose-50 text-5xl">
                    ⚠️
                </div>

                <div class="space-y-3">
                    <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-raspberry">
                        Confirmation indisponible
                    </span>

                    <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
                        Paiement non confirmé
                    </h1>

                    <p class="leading-7 text-muted">
                        Nous n'avons pas pu confirmer votre paiement.
                        Si vous avez été débité, contactez-nous en indiquant
                        la référence de votre commande.
                    </p>

                    <p
                        v-if="orderRef"
                        class="break-all rounded-xl bg-cream px-4 py-3 text-sm text-chocolate"
                    >
                        Référence :
                        <strong>{{ orderRef }}</strong>
                    </p>
                </div>

                <RouterLink
                    to="/panier"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-caramel px-6 py-3.5 font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2 sm:w-auto"
                >
                    Retour au panier
                    <span aria-hidden="true">→</span>
                </RouterLink>
            </div>
        </section>
    </main>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useCartStore } from '../stores/cart';

const route = useRoute();
const cart = useCartStore();

const orderRef = typeof route.query.order === 'string'
    ? route.query.order
    : '';

const status = ref('loading');
const checking = ref(false);
const cartCleared = ref(false);

async function checkStatus() {
    if (!orderRef || checking.value) {
        if (!orderRef) {
            status.value = 'failed';
        }
        return;
    }

    checking.value = true;

    try {
        const { data } = await axios.get(
            `/api/orders/${encodeURIComponent(orderRef)}/payment-status`
        );

        if (data.payment_status === 'paid') {
            status.value = 'paid';

            // Laisser Vue afficher l'état confirmé avant de vider le panier.
            await nextTick();

            // Éviter de vider le panier plusieurs fois.
            if (!cartCleared.value) {
                cart.clear();
                cartCleared.value = true;
            }
        } else if (data.payment_status === 'pending') {
            status.value = 'pending';
        } else {
            status.value = 'failed';
        }
    } catch {
        status.value = 'failed';
    } finally {
        checking.value = false;
    }
}

onMounted(async () => {
    if (!orderRef) {
        status.value = 'failed';
        return;
    }

    // Laisser le temps au prestataire de paiement de confirmer la transaction.
    await new Promise((resolve) => setTimeout(resolve, 2000));

    await checkStatus();

    // Une seconde vérification automatique si le paiement est toujours en attente.
    if (status.value === 'pending') {
        await new Promise((resolve) => setTimeout(resolve, 3000));

        if (status.value === 'pending') {
            await checkStatus();
        }
    }
});
</script>