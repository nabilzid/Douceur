<template>
    <div class="track-page">
        <header class="page-header">
            <h1>Suivre ma commande</h1>
            <p v-if="!auth.isAuthenticated">
                Entrez votre référence de commande et l'email (ou téléphone) utilisé lors de l'achat.
            </p>
            <p v-else>
                Saisissez votre référence de commande pour afficher les détails.
            </p>
        </header>

        <form v-if="!order" class="track-form" @submit.prevent="track">
            <label>
                Référence de commande
                <input v-model="form.reference" type="text" placeholder="CMD-20261025-XXXX" required />
            </label>
            <label v-if="!auth.isAuthenticated">
                Email ou téléphone
                <input v-model="form.contact" type="text" placeholder="email@example.com ou 06..." required />
            </label>
            <p v-if="error" class="error">{{ error }}</p>
            <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Recherche…' : 'Suivre ma commande' }}
            </button>
        </form>

        <div v-else class="order-card fade-in-up">
            <header class="head">
                <h2>{{ order.reference }}</h2>
                <span :class="['status', order.status]">{{ statusLabel(order.status) }}</span>
            </header>

            <p class="meta">📅 Passée le {{ formatDate(order.created_at) }}</p>

            <div class="summary">
                <h3>Récapitulatif</h3>
                <ul class="items">
                    <li v-for="(item, i) in order.items" :key="i">
                        <span>{{ item.product_name }} × {{ item.quantity }}</span>
                        <span>{{ formatPrice(item.line_total) }}</span>
                    </li>
                </ul>
                <div class="line"><span>Sous-total</span><span>{{ formatPrice(order.subtotal) }}</span></div>
                <div class="line">
                    <span>{{ order.delivery_type === 'delivery' ? 'Livraison' : 'Retrait' }}</span>
                    <span>{{ order.delivery_fee > 0 ? formatPrice(order.delivery_fee) : 'Offerte' }}</span>
                </div>
                <div class="total"><span>Total</span><span>{{ formatPrice(order.total_amount) }}</span></div>
            </div>

            <div v-if="order.delivery_type === 'delivery'" class="address">
                <h3>Adresse de livraison</h3>
                <p>{{ order.delivery_address }}</p>
                <p>{{ order.delivery_postal_code }} {{ order.delivery_city }}</p>
            </div>

            <button class="btn-secondary" @click="reset">Rechercher une autre commande</button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const auth = useAuthStore();

const form = reactive({ reference: '', contact: '' });
const order = ref(null);
const loading = ref(false);
const error = ref(null);

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}
function statusLabel(s) {
    return {
        pending: 'En attente',
        confirmed: 'Confirmée',
        preparing: 'En préparation',
        ready: 'Prête',
        delivering: 'En livraison',
        delivered: 'Livrée',
        cancelled: 'Annulée',
    }[s] || s;
}

async function track() {
    loading.value = true;
    error.value = null;
    try {
        const payload = { reference: form.reference };

        // ✅ N'envoyer le contact que si le client n'est pas connecté
        if (!auth.isAuthenticated) {
            payload.contact = form.contact;
        }

        const { data } = await axios.post('/api/orders/track', payload);
        order.value = data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Commande introuvable.';
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
    // Charger l'utilisateur si ce n'est pas déjà fait
    if (!auth.user) await auth.fetchUser();

    // Pré-remplir la référence depuis l'URL
    if (route.query.reference) {
        form.reference = route.query.reference;

        // ✅ Si connecté, tenter directement (sans contact)
        if (auth.isAuthenticated) {
            track();
        }
    }
});
</script>

<style scoped>
.track-page { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }
.page-header { text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
.page-header p { color: var(--text-muted); }
.track-form { background: white; border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); display: flex; flex-direction: column; gap: 1rem; }
label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
input { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); }
input:focus { outline: none; border-color: var(--caramel); background: white; }
.error { color: var(--raspberry); font-size: 0.9rem; }
.btn-primary { width: 100%; justify-content: center; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.order-card { background: white; border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); }
.head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.head h2 { font-size: 1.5rem; margin: 0; }
.status { padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; background: var(--cream-dark); color: var(--chocolate-soft); }
.status.confirmed, .status.preparing, .status.ready { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.status.delivered { background: rgba(5, 150, 105, 0.1); color: #059669; }
.status.cancelled { background: rgba(201, 79, 109, 0.1); color: var(--raspberry); }
.meta { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; }
.summary h3 { font-size: 1rem; margin-bottom: 0.75rem; }
.items { list-style: none; padding: 0; margin: 0 0 1rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem; }
.items li { display: flex; justify-content: space-between; color: var(--chocolate-soft); }
.line { display: flex; justify-content: space-between; padding: 0.5rem 0; color: var(--text-muted); font-size: 0.9rem; }
.total { display: flex; justify-content: space-between; padding: 1rem 0 0; margin-top: 0.5rem; border-top: 2px solid var(--cream-dark); font-size: 1.2rem; font-weight: 700; color: var(--chocolate); }
.address { background: var(--cream); border-radius: var(--radius-sm); padding: 1rem; margin: 1.5rem 0; }
.address h3 { font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; color: var(--text-muted); }
.address p { margin: 0; font-size: 0.9rem; }
.btn-secondary { margin-top: 1.5rem; padding: 0.6rem 1.2rem; border-radius: 999px; background: white; color: var(--caramel); border: 1.5px solid var(--caramel); font-weight: 600; cursor: pointer; }
</style>