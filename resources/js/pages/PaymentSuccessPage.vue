<template>
    <div class="success-page">
        <div class="card">
            <div v-if="status === 'loading'" class="state">
                <div class="spinner"></div>
                <p>Vérification du paiement…</p>
            </div>

            <div v-else-if="status === 'paid'" class="state success">
                <span class="emoji">🎉</span>
                <h1>Paiement confirmé !</h1>
                <p>Votre commande <strong>{{ orderRef }}</strong> a bien été payée.</p>
                <p class="muted">Un email de confirmation vous sera envoyé.</p>
                <RouterLink to="/mes-commandes" class="btn-primary">Voir mes commandes →</RouterLink>
            </div>

            <div v-else-if="status === 'pending'" class="state pending">
                <span class="emoji">⏳</span>
                <h1>Paiement en cours de traitement</h1>
                <p>Cela peut prendre quelques secondes. Rafraîchissez la page.</p>
                <button class="btn-primary" @click="checkStatus">Vérifier à nouveau</button>
            </div>

            <div v-else class="state error">
                <span class="emoji">⚠️</span>
                <h1>Paiement non confirmé</h1>
                <p>Si vous avez été débité, contactez-nous avec la référence <strong>{{ orderRef }}</strong>.</p>
                <RouterLink to="/panier" class="btn-primary">Retour au panier</RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useCartStore } from '../stores/cart';

const route = useRoute();
const cart = useCartStore();
const orderRef = route.query.order;
const status = ref('loading');

async function checkStatus() {
    try {
        const { data } = await axios.get(`/api/orders/${orderRef}/payment-status`);
        status.value = data.payment_status === 'paid' ? 'paid'
            : data.payment_status === 'pending' ? 'pending'
            : 'failed';

        if (status.value === 'paid') {
            await nextTick();   // laisser Vue afficher "paid"
            cart.clear();       // puis vider le panier
        }
    } catch {
        status.value = 'failed';
    }
}

onMounted(async () => {
    if (!orderRef) {
        status.value = 'failed';
        return;
    }
    await new Promise((r) => setTimeout(r, 2000));
    await checkStatus();
    if (status.value === 'pending') {
        await new Promise((r) => setTimeout(r, 3000));
        await checkStatus();
    }
});
</script>

<style scoped>
.success-page {
    display: flex;
    justify-content: center;
    padding: 4rem 1rem;
}
.card {
    background: white;
    border-radius: var(--radius);
    padding: 3rem 2rem;
    box-shadow: var(--shadow-md);
    max-width: 560px;
    width: 100%;
    text-align: center;
}
.state { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
.emoji { font-size: 4rem; }
.state h1 { font-size: 1.75rem; }
.state p { color: var(--text-muted); }
.muted { font-size: 0.85rem; }
.spinner {
    width: 48px;
    height: 48px;
    border: 4px solid var(--cream-dark);
    border-top-color: var(--caramel);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.success h1 { color: #059669; }
.pending h1 { color: var(--caramel-dark); }
.error h1 { color: var(--raspberry); }
</style>