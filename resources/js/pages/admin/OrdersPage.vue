<template>
    <div class="admin-page">
        <header class="page-header">
            <div>
                <h1>Commandes</h1>
                <p class="subtitle">{{ orders.length }} commande(s)</p>
            </div>
        </header>

        <div class="filters">
            <button
                v-for="s in statuses"
                :key="s.value ?? 'all'"
                :class="['chip', { active: filter === s.value }]"
                @click="filter = s.value"
            >
                {{ s.label }}
            </button>
        </div>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement…</p>
        </div>

        <div v-else-if="filteredOrders.length === 0" class="state">
            Aucune commande pour ce filtre.
        </div>

        <div v-else class="orders-list">
            <article v-for="order in filteredOrders" :key="order.id" class="order-card">
                <div class="order-head">
                    <div>
                        <strong class="order-ref">{{ order.reference }}</strong>
                        <span class="order-date">{{ formatDate(order.created_at) }}</span>
                    </div>
                    <span class="order-total">{{ formatPrice(order.total_amount) }}</span>
                </div>
                <ul class="order-items">
                    <li v-for="item in order.items" :key="item.id">
                        <span>{{ item.product_name }} × {{ item.quantity }}</span>
                        <span>{{ formatPrice(item.line_total) }}</span>
                    </li>
                </ul>

                <div v-if="order.delivery_type === 'delivery'" class="order-address">
                    <span class="address-label">📍 Adresse de livraison</span>
                    <p>{{ order.delivery_address }}</p>
                    <p>{{ order.delivery_postal_code }} {{ order.delivery_city }}</p>
                </div>

                <div class="order-foot">
                    <div class="order-customer">
                        <strong>{{ order.customer_name }}</strong>
                        <span>{{ order.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Retrait' }}</span>
                    </div>
                    <select :value="order.status" @change="updateStatus(order, $event.target.value)">
                        <option v-for="s in statuses.filter(x => x.value)" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
            </article>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';

const orders = ref([]);
const loading = ref(true);
const filter = ref(null);

let pollInterval = null;

const statuses = [
    { value: null, label: 'Toutes' },
    { value: 'pending', label: 'En attente' },
    { value: 'confirmed', label: 'Confirmées' },
    { value: 'preparing', label: 'En préparation' },
    { value: 'ready', label: 'Prêtes' },
    { value: 'delivering', label: 'En livraison' },
    { value: 'delivered', label: 'Livrées' },
    { value: 'cancelled', label: 'Annulées' },
];

const filteredOrders = computed(() =>
    filter.value ? orders.value.filter((o) => o.status === filter.value) : orders.value
);

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}
function formatDate(d) {
    return new Date(d).toLocaleString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

async function load() {
    try {
        const { data } = await axios.get('/api/merchant/orders');
        orders.value = data;
    } catch {
        // garder la liste existante
    } finally {
        loading.value = false;
    }
}

async function updateStatus(order, status) {
    try {
        const { data } = await axios.put(`/api/merchant/orders/${order.id}/status`, { status });
        Object.assign(order, data);
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    }
}

onMounted(() => {
    load();
    pollInterval = setInterval(load, 15 * 60 * 1000);
    window.addEventListener('notification-clicked', handleNotificationClick);
});

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
    window.removeEventListener('notification-clicked', handleNotificationClick);
});

function handleNotificationClick(event) {
    if (event.detail.url === '/admin/sur-mesure') {
        load();
    }
}
</script>

<style scoped>
.admin-page { max-width: 1400px; margin: 0 auto; }
.page-header { margin-bottom: 1.5rem; }
.page-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); margin: 0; }
.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; }
.chip { padding: 0.5rem 1rem; border-radius: 999px; background: white; border: 1.5px solid var(--cream-dark); font-size: 0.85rem; font-weight: 500; color: var(--chocolate-soft); transition: all 0.2s; }
.chip:hover { border-color: var(--caramel-light); }
.chip.active { background: var(--caramel); color: white; border-color: var(--caramel); }

.orders-list { display: flex; flex-direction: column; gap: 1rem; }
.order-card { background: white; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm); transition: all 0.2s; }
.order-card:hover { box-shadow: var(--shadow-md); }
.order-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--cream-dark); }
.order-ref { font-size: 1.05rem; color: var(--chocolate); display: block; }
.order-date { color: var(--text-muted); font-size: 0.85rem; }
.order-total { font-size: 1.25rem; font-weight: 700; color: var(--chocolate); }
.order-items { list-style: none; padding: 0; margin: 0 0 1rem; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; }
.order-items li { display: flex; justify-content: space-between; color: var(--text-muted); }

.order-address { background: var(--cream); border-radius: var(--radius-sm); padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: var(--chocolate-soft); }
.address-label { display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.35rem; }
.order-address p { margin: 0; line-height: 1.4; }

.order-foot { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--cream-dark); flex-wrap: wrap; gap: 1rem; }
.order-customer { display: flex; flex-direction: column; gap: 0.2rem; font-size: 0.9rem; }
.order-customer strong { color: var(--chocolate); }
.order-customer span { color: var(--text-muted); font-size: 0.85rem; }
select { padding: 0.55rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 500; background: var(--cream); }
select:focus { outline: none; border-color: var(--caramel); background: white; }
@media (max-width: 600px) { .order-head, .order-foot { flex-direction: column; align-items: flex-start; } }
</style>