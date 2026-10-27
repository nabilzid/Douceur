<template>
    <div class="checkout-page">
        <header class="checkout-header">
            <RouterLink to="/panier" class="back-link">← Retour au panier</RouterLink>
            <h1>Finaliser la commande</h1>
        </header>

        <div v-if="success" class="success-card fade-in-up">
            <span class="success-emoji">🎉</span>
            <h2>Commande confirmée !</h2>
            <p>Votre commande <strong>{{ success.reference }}</strong> a bien été enregistrée.</p>
            <p class="muted">Un email de confirmation vous sera envoyé.</p>
            <RouterLink to="/catalogue" class="btn-primary">Continuer mes achats →</RouterLink>
        </div>

        <div v-else-if="cart.items.length === 0" class="empty">
            <p>Votre panier est vide.</p>
            <RouterLink to="/catalogue" class="btn-primary">Voir le catalogue</RouterLink>
        </div>

        <form v-else class="checkout-layout" @submit.prevent="submit">
            <div class="checkout-form">
                <section class="form-section">
                    <h2>Vos coordonnées</h2>

                    <div v-if="auth.isAuthenticated" class="user-summary">
                        <p>Connecté en tant que <strong>{{ auth.user.name }}</strong></p>
                        <p class="hint">Les informations de votre compte seront utilisées pour cette commande.</p>
                        <RouterLink to="/mon-compte" class="edit-link">Modifier mes informations →</RouterLink>
                    </div>

                    <div v-else class="form-grid">
                        <label>Nom complet<input v-model="form.name" type="text" required /></label>
                        <label>Email<input v-model="form.email" type="email" required /></label>
                        <label>Téléphone<input v-model="form.phone" type="tel" required /></label>
                    </div>
                </section>

                <section v-if="cart.isDelivery" class="form-section">
                    <h2>Adresse de livraison</h2>

                    <!-- Adresses enregistrées -->
                    <div v-if="addresses.length > 0" class="address-selector">
                        <label
                            v-for="addr in addresses"
                            :key="addr.id"
                            :class="['address-option', { selected: selectedAddressId === addr.id }]"
                        >
                            <input
                                type="radio"
                                :value="addr.id"
                                v-model="selectedAddressId"
                                @change="selectAddress(addr)"
                            />
                            <div class="address-option-body">
                                <div class="address-option-head">
                                    <strong>{{ addr.label || 'Adresse' }}</strong>
                                    <span v-if="addr.is_default" class="default-tag">Par défaut</span>
                                </div>
                                <p>{{ addr.recipient_name }}</p>
                                <p>{{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span></p>
                                <p>{{ addr.postal_code }} {{ addr.city }}</p>
                            </div>
                        </label>
                    </div>

                    <!-- Formulaire manuel (invité ou aucune adresse) -->
                    <div v-else class="form-grid">
                        <label class="full">
                            Adresse
                            <input v-model="form.address" type="text" required />
                        </label>
                        <label>
                            Code postal
                            <input v-model="form.postalCode" type="text" required />
                        </label>
                        <label>
                            Ville
                            <input v-model="form.city" type="text" required />
                        </label>
                    </div>
                </section>

                <section class="form-section">
                    <h2>Créneau {{ cart.isDelivery ? 'de livraison' : 'de retrait' }}</h2>
                    <select v-model="form.slotId" class="slot-select">
                        <option :value="null">— Choisir un créneau (optionnel) —</option>
                        <option v-for="slot in slots" :key="slot.id" :value="slot.id">
                            {{ formatSlot(slot) }}
                        </option>
                    </select>
                </section>

                <section class="form-section">
                    <h2>Notes (optionnel)</h2>
                    <textarea v-model="form.notes" rows="3" placeholder="Instructions particulières…"></textarea>
                </section>

                <p v-if="error" class="error">{{ error }}</p>
            </div>

            <aside class="checkout-summary">
                <h2>Récapitulatif</h2>
                <ul class="summary-items">
                    <li v-for="item in cart.items" :key="item.id">
                        <span>{{ item.name }} × {{ item.quantity }}</span>
                        <span>{{ formatPrice(item.price * item.quantity) }}</span>
                    </li>
                </ul>
                <div class="summary-line">
                    <span>Sous-total</span>
                    <span>{{ formatPrice(cart.subtotal) }}</span>
                </div>
                <div class="summary-line">
                    <span>{{ cart.isDelivery ? 'Livraison' : 'Retrait' }}</span>
                    <span>{{ cart.isDelivery ? (cart.deliveryFee === 0 ? 'Offerte' : formatPrice(cart.deliveryFee)) : 'Gratuit' }}</span>
                </div>
                <div v-if="cart.isDelivery && cart.missingForFreeDelivery > 0" class="checkout-hint">
                    💡 Plus que {{ formatPrice(cart.missingForFreeDelivery) }} pour la livraison gratuite
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>{{ formatPrice(cart.total) }}</span>
                </div>
                <button type="submit" class="btn-primary submit-btn" :disabled="submitting">
                    {{ submitting ? 'Envoi…' : 'Confirmer la commande' }}
                </button>
                <p class="secure-note">🔒 Paiement sécurisé</p>
            </aside>
        </form>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const cart = useCartStore();

const form = reactive({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postalCode: '',
    slotId: null,
    notes: '',
});

const slots = ref([]);
const submitting = ref(false);
const error = ref(null);
const success = ref(null);
const addresses = ref([]);
const selectedAddressId = ref(null);

function selectAddress(addr) {
    selectedAddressId.value = addr.id;
    form.address = addr.street + (addr.street_2 ? ', ' + addr.street_2 : '');
    form.postalCode = addr.postal_code;
    form.city = addr.city;
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
}

function formatSlot(slot) {
    const date = new Date(slot.date).toLocaleDateString('fr-FR', {
        weekday: 'long', day: 'numeric', month: 'long',
    });
    return `${date} — ${slot.start_time.slice(0, 5)} → ${slot.end_time.slice(0, 5)}`;
}

async function loadSlots() {
    try {
        const type = cart.isDelivery ? 'delivery' : 'pickup';
        const { data } = await axios.get(`/api/delivery-slots?type=${type}`);
        slots.value = data;
    } catch {
        slots.value = [];
    }
}

async function loadAddresses() {
    try {
        const { data } = await axios.get('/api/user/addresses');
        addresses.value = data;
    } catch {
        addresses.value = [];
    }
}

async function validateStock() {
    for (const item of cart.items) {
        try {
            const { data } = await axios.get(`/api/products/${item.slug}`);
            if (!data || data.stock_quantity < item.quantity) {
                return `Stock insuffisant pour ${item.name}. Quantité disponible : ${data?.stock_quantity ?? 0}.`;
            }
        } catch {
            return `Le produit ${item.name} n'est plus disponible.`;
        }
    }
    return null;
}

async function submit() {
    submitting.value = true;
    error.value = null;

    try {
        if (auth.isAuthenticated) {
            form.name = auth.user.name;
            form.email = auth.user.email;
            form.phone = auth.user.phone || 'non renseigné';
        }

        const stockError = await validateStock();
        if (stockError) {
            error.value = stockError;
            submitting.value = false;
            return;
        }

        const order = await cart.submitOrder(form);
        const { data } = await axios.post(`/api/orders/${order.reference}/checkout`);
        window.location.href = data.checkout_url;
    } catch (e) {
        error.value = e.response?.data?.message || e.message;
        submitting.value = false;
    }
}

onMounted(async () => {
    // Recharger les stocks réels pour les produits du panier
    for (const item of cart.items) {
        try {
            const { data } = await axios.get(`/api/products/${item.slug}`);
            if (data.stock_quantity < item.quantity) {
                cart.updateQuantity(item.id, data.stock_quantity);
            }
            const storeItem = cart.items.find((i) => i.id === item.id);
            if (storeItem) {
                storeItem.stock_quantity = data.stock_quantity;
            }
        } catch {
            cart.removeItem(item.id);
        }
    }

    // Pré-remplir l'adresse par défaut si connecté
    if (auth.isAuthenticated && cart.isDelivery) {
        await loadAddresses();
        const defaultAddr = addresses.value.find((a) => a.is_default) || addresses.value[0];
        if (defaultAddr) {
            selectAddress(defaultAddr);
        }
    }

    loadSlots();
});
</script>

<style scoped>
.user-summary {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
}
.user-summary p { margin: 0 0 0.5rem; font-size: 0.95rem; }
.user-summary .hint { color: var(--text-muted); font-size: 0.85rem; }
.edit-link { font-size: 0.85rem; color: var(--caramel); }

.checkout-hint {
    background: rgba(201, 123, 63, 0.08);
    color: var(--caramel-dark);
    padding: 0.6rem 0.9rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

/* Sélecteur d'adresses */
.address-selector {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.address-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    background: var(--cream);
    cursor: pointer;
    transition: all 0.2s;
}

.address-option:hover {
    border-color: var(--caramel-light);
    background: white;
}

.address-option.selected {
    border-color: var(--caramel);
    background: rgba(201, 123, 63, 0.05);
}

.address-option input[type="radio"] {
    margin-top: 0.2rem;
    width: 18px;
    height: 18px;
    accent-color: var(--caramel);
    cursor: pointer;
    flex-shrink: 0;
}

.address-option-body {
    flex: 1;
}

.address-option-head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.address-option-head strong {
    color: var(--chocolate);
    font-size: 0.95rem;
}

.address-option-body p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.default-tag {
    background: var(--caramel);
    color: white;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.checkout-page { padding: 1rem 0 4rem; }
.checkout-header { margin-bottom: 2.5rem; }
.back-link { color: var(--text-muted); font-size: 0.9rem; display: inline-block; margin-bottom: 1rem; }
.checkout-header h1 { font-size: 2.5rem; }

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}

.form-section {
    background: white;
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}
.form-section h2 { font-size: 1.2rem; margin-bottom: 1.25rem; }

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-grid label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; color: var(--text-muted); font-weight: 500; }
.form-grid label.full { grid-column: 1 / -1; }

input, textarea, select {
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 0.95rem;
    background: var(--cream);
    color: var(--text-dark);
    transition: border-color 0.2s;
}
input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--caramel);
    background: white;
}
textarea { resize: vertical; }
.slot-select { width: 100%; }

.checkout-summary {
    background: white;
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 6rem;
}
.checkout-summary h2 { font-size: 1.35rem; margin-bottom: 1.25rem; }

.summary-items {
    list-style: none; padding: 0; margin: 0 0 1.25rem;
    display: flex; flex-direction: column; gap: 0.6rem;
    border-bottom: 1px solid var(--cream-dark); padding-bottom: 1.25rem;
}
.summary-items li { display: flex; justify-content: space-between; font-size: 0.9rem; }

.summary-line { display: flex; justify-content: space-between; padding: 0.5rem 0; color: var(--text-muted); font-size: 0.95rem; }

.summary-total {
    display: flex; justify-content: space-between;
    padding: 1rem 0 1.25rem; margin-top: 0.5rem;
    border-top: 2px solid var(--cream-dark);
    font-size: 1.35rem; font-weight: 700; color: var(--chocolate);
}

.submit-btn { width: 100%; justify-content: center; }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.secure-note { text-align: center; margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted); }

.error { color: var(--raspberry); font-weight: 500; margin-top: 1rem; }

.success-card {
    max-width: 600px; margin: 4rem auto; text-align: center;
    background: white; padding: 3rem 2rem;
    border-radius: var(--radius); box-shadow: var(--shadow-md);
}
.success-emoji { font-size: 4rem; display: block; margin-bottom: 1rem; }
.success-card h2 { margin-bottom: 1rem; }
.success-card p { margin-bottom: 1rem; color: var(--text-muted); }
.muted { font-size: 0.85rem; }

.empty { text-align: center; padding: 4rem 1rem; }

@media (max-width: 900px) {
    .checkout-layout { grid-template-columns: 1fr; }
    .checkout-summary { position: static; }
    .form-grid { grid-template-columns: 1fr; }
    .checkout-header h1 { font-size: 2rem; }
}
@media (max-width: 480px) {
    .form-section { padding: 1.25rem; }
}
</style>