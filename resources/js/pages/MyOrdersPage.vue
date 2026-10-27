<template>
    <div class="my-orders-page">
        <header class="page-header">
            <h1>Mes commandes</h1>
            <p>Retrouvez l'historique de vos commandes.</p>
        </header>

        <div v-if="loading" class="state">Chargement…</div>

        <div v-else-if="orders.length === 0" class="state">
            <span class="empty-emoji">🧺</span>
            <p>Vous n'avez pas encore passé de commande.</p>
            <RouterLink to="/catalogue" class="btn-primary">Découvrir le catalogue →</RouterLink>
        </div>

        <div v-else class="orders-list">
            <article
                v-for="order in orders"
                :key="order.id"
                :class="['order-card', { open: openOrders[order.id] }]"
            >
                <!-- ✅ En-tête cliquable (toujours visible) -->
                <button class="order-header" @click="toggleOrder(order.id)">
                    <div class="header-left">
                        <strong class="ref">{{ order.reference }}</strong>
                        <span class="date">📅 {{ formatDate(order.created_at) }}</span>
                    </div>
                    <div class="header-right">
                        <span :class="['status', order.status]">{{ statusLabel(order.status) }}</span>
                        <span class="chevron" :class="{ open: openOrders[order.id] }">▾</span>
                    </div>
                </button>

                <!-- ✅ Contenu dépliable -->
                <Transition name="accordion">
                    <div v-if="openOrders[order.id]" class="order-body">
                        <div class="summary">
                            <h3>Récapitulatif</h3>
                            <ul class="items">
                                <li v-for="item in order.items" :key="item.id">
                                    <span>{{ item.product_name }} × {{ item.quantity }}</span>
                                    <span>{{ formatPrice(item.line_total) }}</span>
                                </li>
                            </ul>
                            <div class="line">
                                <span>Sous-total</span>
                                <span>{{ formatPrice(order.subtotal) }}</span>
                            </div>
                            <div class="line">
                                <span>{{ order.delivery_type === 'delivery' ? 'Livraison' : 'Retrait' }}</span>
                                <span>{{ order.delivery_fee > 0 ? formatPrice(order.delivery_fee) : 'Offerte' }}</span>
                            </div>
                            <div class="total">
                                <span>Total</span>
                                <span>{{ formatPrice(order.total_amount) }}</span>
                            </div>
                        </div>

                        <div v-if="order.delivery_type === 'delivery'" class="address">
                            <h3>Adresse de livraison</h3>
                            <p>{{ order.delivery_address }}</p>
                            <p>{{ order.delivery_postal_code }} {{ order.delivery_city }}</p>
                        </div>
                    </div>
                </Transition>
            </article>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const orders = ref([]);
const loading = ref(true);
const openOrders = reactive({}); // { [orderId]: boolean }

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

function toggleOrder(orderId) {
    openOrders[orderId] = !openOrders[orderId];
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/my-orders');
        orders.value = data;
        // Initialiser tous les accordéons à fermé
        data.forEach((o) => (openOrders[o.id] = false));
    } catch {
        orders.value = [];
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.my-orders-page { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }

.page-header { text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
.page-header p { color: var(--text-muted); }

.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.empty-emoji { font-size: 4rem; display: block; margin-bottom: 1rem; }

.orders-list { display: flex; flex-direction: column; gap: 1rem; }

.order-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.order-card:hover { box-shadow: var(--shadow-md); }
.order-card.open { box-shadow: var(--shadow-md); }

/* En-tête cliquable */
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 1.25rem 1.5rem;
    background: white;
    border: none;
    cursor: pointer;
    text-align: left;
    gap: 1rem;
    transition: background 0.15s;
}
.order-header:hover { background: var(--cream); }

.header-left { display: flex; flex-direction: column; gap: 0.25rem; }
.ref { font-size: 1.05rem; color: var(--chocolate); }
.date { color: var(--text-muted); font-size: 0.85rem; }

.header-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }

.status {
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--cream-dark);
    color: var(--chocolate-soft);
    white-space: nowrap;
}
.status.confirmed, .status.preparing, .status.ready { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.status.delivered { background: rgba(5, 150, 105, 0.1); color: #059669; }
.status.cancelled { background: rgba(201, 79, 109, 0.1); color: var(--raspberry); }

.chevron {
    font-size: 1rem;
    color: var(--text-muted);
    transition: transform 0.25s ease;
}
.chevron.open { transform: rotate(180deg); }

/* Contenu dépliable */
.order-body {
    padding: 0 1.5rem 1.5rem;
    border-top: 1px solid var(--cream-dark);
}

.summary { padding-top: 1.25rem; }
.summary h3 { font-size: 1rem; margin-bottom: 0.75rem; color: var(--chocolate); }

.items { list-style: none; padding: 0; margin: 0 0 1rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem; }
.items li { display: flex; justify-content: space-between; color: var(--chocolate-soft); }

.line { display: flex; justify-content: space-between; padding: 0.5rem 0; color: var(--text-muted); font-size: 0.9rem; }
.total {
    display: flex; justify-content: space-between;
    padding: 1rem 0 0; margin-top: 0.5rem;
    border-top: 2px solid var(--cream-dark);
    font-size: 1.2rem; font-weight: 700; color: var(--chocolate);
}

.address {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 1rem;
    margin-top: 1rem;
}
.address h3 { font-size: 0.85rem; margin-bottom: 0.5rem; text-transform: uppercase; color: var(--text-muted); }
.address p { margin: 0; font-size: 0.9rem; }

/* Animation accordéon */
.accordion-enter-active,
.accordion-leave-active {
    transition: max-height 0.3s ease, opacity 0.3s ease;
    overflow: hidden;
}
.accordion-enter-from,
.accordion-leave-to {
    max-height: 0;
    opacity: 0;
}
.accordion-enter-to,
.accordion-leave-from {
    max-height: 1000px;
    opacity: 1;
}

@media (max-width: 600px) {
    .order-header { flex-direction: column; align-items: flex-start; }
    .header-right { width: 100%; justify-content: space-between; }
}
</style>