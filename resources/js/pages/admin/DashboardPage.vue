<template>
    <div class="dashboard">
        <header class="dashboard-header">
            <div>
                <h1>Tableau de bord</h1>
                <p class="subtitle">
                    {{ isAdmin ? 'Vue globale de la plateforme' : 'Vue de votre boutique' }}
                </p>
            </div>
            <span class="date-badge">{{ today }}</span>
        </header>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement des statistiques…</p>
        </div>

        <!-- ✅ ADMIN : stats globales -->
        <div v-else-if="isAdmin" class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon users">👥</div>
                <div class="stat-body">
                    <span class="stat-label">Utilisateurs</span>
                    <span class="stat-value">{{ adminStats.total_users ?? 0 }}</span>
                    <span class="stat-detail">
                        {{ adminStats.total_customers ?? 0 }} clients · {{ adminStats.total_merchants ?? 0 }} commerçants
                    </span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon orders">📦</div>
                <div class="stat-body">
                    <span class="stat-label">Commandes totales</span>
                    <span class="stat-value">{{ adminStats.total_orders ?? 0 }}</span>
                    <span class="stat-detail">{{ adminStats.pending_orders ?? 0 }} en attente</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon revenue">💰</div>
                <div class="stat-body">
                    <span class="stat-label">Chiffre d'affaires</span>
                    <span class="stat-value">{{ formatPrice(adminStats.total_revenue) }}</span>
                    <span class="stat-detail">Commandes payées uniquement</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-body">
                    <span class="stat-label">En attente</span>
                    <span class="stat-value">{{ adminStats.pending_orders ?? 0 }}</span>
                    <span class="stat-detail">À traiter</span>
                </div>
            </article>
        </div>

        <!-- ✅ PÂTISSIER : stats boutique (sans CA) -->
        <div v-else class="stats-grid">
            <article class="stat-card">
                <div class="stat-icon products">🍰</div>
                <div class="stat-body">
                    <span class="stat-label">Produits</span>
                    <span class="stat-value">{{ merchantStats.products_count ?? 0 }}</span>
                    <span class="stat-detail">Actifs dans votre catalogue</span>
                </div>
            </article>

            <article class="stat-card warning">
                <div class="stat-icon lowstock">⚠️</div>
                <div class="stat-body">
                    <span class="stat-label">Stock faible</span>
                    <span class="stat-value">{{ merchantStats.low_stock_count ?? 0 }}</span>
                    <span class="stat-detail">Produits à réapprovisionner</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon today">📅</div>
                <div class="stat-body">
                    <span class="stat-label">Commandes aujourd'hui</span>
                    <span class="stat-value">{{ merchantStats.orders_today ?? 0 }}</span>
                    <span class="stat-detail">{{ merchantStats.pending_orders ?? 0 }} en attente</span>
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-icon pending">⏳</div>
                <div class="stat-body">
                    <span class="stat-label">En attente</span>
                    <span class="stat-value">{{ merchantStats.pending_orders ?? 0 }}</span>
                    <span class="stat-detail">À traiter</span>
                </div>
            </article>
        </div>

        <!-- Raccourcis -->
        <section v-if="!loading" class="shortcuts">
            <h2>Raccourcis</h2>
            <div class="shortcuts-grid">
                <RouterLink to="/admin/commandes" class="shortcut">
                    <span class="shortcut-icon">📋</span>
                    <span>Voir les commandes</span>
                </RouterLink>
                <RouterLink to="/admin/produits" class="shortcut">
                    <span class="shortcut-icon">🍰</span>
                    <span>Gérer les produits</span>
                </RouterLink>
                <RouterLink to="/admin/sur-mesure" class="shortcut">
                    <span class="shortcut-icon">🎂</span>
                    <span>Demandes sur mesure</span>
                </RouterLink>
                <RouterLink v-if="isAdmin" to="/admin/parametres" class="shortcut">
                    <span class="shortcut-icon">⚙️</span>
                    <span>Paramètres</span>
                </RouterLink>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const isAdmin = computed(() => auth.user?.role === 'admin');

const adminStats = ref({});
const merchantStats = ref({});
const loading = ref(true);

let pollInterval = null;

const today = new Date().toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
});

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}

async function load() {
    try {
        if (isAdmin.value) {
            const { data } = await axios.get('/api/admin/dashboard');
            adminStats.value = data;
        } else {
            const { data } = await axios.get('/api/merchant/dashboard');
            merchantStats.value = data;
        }
    } catch {
        // garder les données existantes
    } finally {
        loading.value = false;
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
.dashboard { max-width: 1400px; margin: 0 auto; }

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.dashboard-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); font-size: 1rem; margin: 0; }
.date-badge {
    background: white;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--chocolate-soft);
    box-shadow: var(--shadow-sm);
    text-transform: capitalize;
}

.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}
.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1.5px solid transparent;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--cream-dark); }
.stat-card.warning { border-left: 4px solid var(--raspberry); }

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}
.stat-icon.users { background: rgba(59, 130, 246, 0.1); }
.stat-icon.orders,
.stat-icon.today { background: rgba(201, 123, 63, 0.12); }
.stat-icon.revenue { background: rgba(5, 150, 105, 0.12); }
.stat-icon.pending { background: rgba(234, 179, 8, 0.15); }
.stat-icon.products { background: rgba(201, 123, 63, 0.12); }
.stat-icon.lowstock { background: rgba(201, 79, 109, 0.12); }

.stat-body { display: flex; flex-direction: column; gap: 0.25rem; flex: 1; min-width: 0; }
.stat-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-value { font-size: 2rem; font-weight: 700; color: var(--chocolate); line-height: 1.1; }
.stat-detail { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; }

.shortcuts h2 { font-size: 1.4rem; margin-bottom: 1.25rem; color: var(--chocolate); }
.shortcuts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.shortcut {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    color: var(--chocolate-soft);
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s;
}
.shortcut:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    background: var(--caramel);
    color: white;
    text-decoration: none;
}
.shortcut-icon { font-size: 1.5rem; }

@media (max-width: 900px) {
    .dashboard-header h1 { font-size: 1.75rem; }
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 1rem; }
    .stat-card { padding: 1.25rem; }
    .stat-value { font-size: 1.5rem; }
    .stat-icon { width: 44px; height: 44px; font-size: 1.35rem; }
}
@media (max-width: 600px) {
    .stats-grid { grid-template-columns: 1fr; }
    .dashboard-header { flex-direction: column; align-items: flex-start; }
    .shortcuts-grid { grid-template-columns: 1fr; }
}
</style>