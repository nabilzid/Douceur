<template>
    <div class="admin-page">
        <header class="page-header">
            <div>
                <h1>Produits & stock</h1>
                <p class="subtitle">{{ products.length }} produit(s) dans votre catalogue</p>
            </div>
            <button v-if="isAdmin" class="btn-primary" @click="openCreateModal">+ Nouveau produit</button>
        </header>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement…</p>
        </div>

        <div v-else class="products-grid">
            <article v-for="product in products" :key="product.id" class="product-card">
                <div class="product-head">
                    <div>
                        <span class="product-type">{{ product.type?.name }}</span>
                        <h3 class="product-name">{{ product.name }}</h3>
                    </div>
                    <button :class="['toggle', product.is_active ? 'active' : '']" @click="toggleActive(product)">
                        {{ product.is_active ? 'Actif' : 'Inactif' }}
                    </button>
                </div>

                <div class="product-price">{{ formatPrice(product.price) }}</div>

                <div class="product-stock">
                    <span :class="['stock-badge', product.stock_quantity <= product.low_stock_threshold ? 'low' : 'ok']">
                        {{ product.stock_quantity <= product.low_stock_threshold ? '⚠️' : '✓' }}
                        Stock : {{ product.stock_quantity }}
                    </span>
                    <span class="threshold">Seuil : {{ product.low_stock_threshold }}</span>
                </div>

                <div class="stock-adjust">
                    <input v-model.number="adjustments[product.id]" type="number" placeholder="±" />
                    <button class="btn-adjust" @click="adjustStock(product)">Ajuster</button>
                </div>

                <div v-if="isAdmin" class="product-actions">
                    <button class="btn-edit" @click="openEditModal(product)">✏️ Modifier</button>
                    <button class="btn-delete" @click="confirmDelete(product)">🗑️ Supprimer</button>
                </div>
            </article>
        </div>

        <!-- Modal création / édition (admin uniquement) -->
        <div v-if="showModal && isAdmin" class="modal-overlay" @click.self="closeModal">
            <div class="modal">
                <header class="modal-head">
                    <h2>{{ editingProduct ? 'Modifier le produit' : 'Nouveau produit' }}</h2>
                    <button class="modal-close" @click="closeModal">✕</button>
                </header>

                <form class="modal-form" @submit.prevent="saveProduct">
                    <label>Type
                        <select v-model.number="form.product_type_id" required>
                            <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                        </select>
                    </label>
                    <label>Nom<input v-model="form.name" required /></label>
                    <label>Description<textarea v-model="form.description" rows="3"></textarea></label>
                    <div class="grid-2">
                        <label>Prix (€)<input v-model.number="form.price" type="number" min="0" step="0.01" required /></label>
                        <label>Stock<input v-model.number="form.stock_quantity" type="number" min="0" required /></label>
                        <label>Seuil d'alerte<input v-model.number="form.low_stock_threshold" type="number" min="0" required /></label>
                    </div>
                    <label>Allergènes (séparés par virgule)
                        <input v-model="allergensInput" placeholder="gluten, lait, œufs" />
                    </label>
                    <label class="checkbox">
                        <input v-model="form.is_active" type="checkbox" /> Actif
                    </label>
                    <p v-if="error" class="error">{{ error }}</p>
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Enregistrement…' : (editingProduct ? 'Enregistrer' : 'Créer le produit') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const isAdmin = computed(() => auth.user?.role === 'admin');

const products = ref([]);
const types = ref([]);
const loading = ref(true);
const adjustments = reactive({});
const showModal = ref(false);
const editingProduct = ref(null);
const saving = ref(false);
const error = ref(null);

const form = reactive({
    product_type_id: null,
    name: '',
    description: '',
    price: 0,
    stock_quantity: 0,
    low_stock_threshold: 5,
    is_active: true,
    is_customizable: false,
});
const allergensInput = ref('');

let pollInterval = null;

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p);
}

async function load() {
    try {
        const [prodRes, typeRes] = await Promise.all([
            axios.get('/api/merchant/products'),
            axios.get('/api/product-types'),
        ]);
        products.value = prodRes.data;
        types.value = typeRes.data;
        products.value.forEach((p) => (adjustments[p.id] = adjustments[p.id] ?? null));
    } catch {
        // garder les données existantes
    } finally {
        loading.value = false;
    }
}

// ... openCreateModal, openEditModal, closeModal, saveProduct,
// toggleActive, adjustStock, confirmDelete inchangés ...

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
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.page-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); margin: 0; }

.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.product-card { background: white; border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 1rem; transition: all 0.2s; }
.product-card:hover { box-shadow: var(--shadow-md); }
.product-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.product-type { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--caramel); }
.product-name { font-size: 1.15rem; margin: 0.25rem 0 0; }
.product-price { font-size: 1.5rem; font-weight: 700; color: var(--chocolate); }
.product-stock { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
.stock-badge { padding: 0.35rem 0.75rem; border-radius: 999px; font-weight: 600; }
.stock-badge.ok { background: rgba(5, 150, 105, 0.1); color: #059669; }
.stock-badge.low { background: rgba(201, 79, 109, 0.1); color: var(--raspberry); }
.threshold { color: var(--text-muted); font-size: 0.8rem; }
.stock-adjust { display: flex; gap: 0.5rem; }
.stock-adjust input { flex: 1; padding: 0.6rem 0.9rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; background: var(--cream); }
.stock-adjust input:focus { outline: none; border-color: var(--caramel); background: white; }
.btn-adjust { padding: 0.6rem 1.1rem; border-radius: var(--radius-sm); background: var(--caramel); color: white; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; }
.btn-adjust:hover { background: var(--caramel-dark); }
.toggle { padding: 0.4rem 0.9rem; border-radius: 999px; background: var(--cream); color: var(--text-muted); font-weight: 600; font-size: 0.8rem; transition: all 0.2s; white-space: nowrap; }
.toggle.active { background: rgba(5, 150, 105, 0.1); color: #059669; }

.product-actions { display: flex; gap: 0.5rem; padding-top: 0.75rem; border-top: 1px solid var(--cream-dark); }
.btn-edit, .btn-delete { flex: 1; padding: 0.55rem 1rem; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; transition: all 0.2s; }
.btn-edit { background: var(--cream); color: var(--chocolate-soft); }
.btn-edit:hover { background: var(--caramel); color: white; }
.btn-delete { background: rgba(201, 79, 109, 0.08); color: var(--raspberry); }
.btn-delete:hover { background: var(--raspberry); color: white; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(61, 40, 23, 0.5); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
.modal { background: white; border-radius: var(--radius); padding: 2rem; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: var(--shadow-lg); }
.modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.modal-head h2 { font-size: 1.4rem; margin: 0; }
.modal-close { width: 32px; height: 32px; border-radius: 50%; background: var(--cream); color: var(--chocolate); font-size: 1rem; transition: all 0.2s; }
.modal-close:hover { background: var(--raspberry); color: white; }
.modal-form { display: flex; flex-direction: column; gap: 1rem; }
.modal-form label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
.modal-form label.checkbox { flex-direction: row; align-items: center; gap: 0.5rem; }
.modal-form input, .modal-form select, .modal-form textarea { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); }
.modal-form input:focus, .modal-form select:focus, .modal-form textarea:focus { outline: none; border-color: var(--caramel); background: white; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.error { color: var(--raspberry); font-size: 0.85rem; font-weight: 500; }
.btn-primary { padding: 0.75rem 1.5rem; border-radius: 999px; background: var(--caramel); color: white; font-weight: 600; transition: all 0.2s; }
.btn-primary:hover { background: var(--caramel-dark); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

@media (max-width: 600px) { .products-grid { grid-template-columns: 1fr; } .grid-2 { grid-template-columns: 1fr; } }
</style>