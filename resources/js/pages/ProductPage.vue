<template>
    <div class="product-page">
        <div v-if="loading" class="state">Chargement…</div>
        <div v-else-if="error" class="state error">{{ error }}</div>

        <div v-else-if="product" class="product-layout">
            <div class="product-visual">
                <span class="product-emoji">{{ emoji }}</span>
                <span v-if="product.stock_quantity === 0" class="rupture-badge">RUPTURE</span>
            </div>

            <div class="product-info fade-in-up">
                <RouterLink to="/catalogue" class="back-link">← Retour au catalogue</RouterLink>

                <span class="product-type">{{ product.type?.name }}</span>
                <h1 class="product-title">{{ product.name }}</h1>
                <p class="product-desc">{{ product.description }}</p>

                <div class="product-price">{{ formatPrice(product.price) }}</div>
                <p v-if="cart.items.length > 0 && cart.missingForFreeDelivery > 0" class="free-delivery-hint">
                    Plus que {{ formatPrice(cart.missingForFreeDelivery) }} pour la livraison gratuite 🚚
                </p>
                <div v-if="product.allergens?.length" class="allergens">
                    <h3 class="allergens-title">Allergènes</h3>
                    <div class="allergen-tags">
                        <span v-for="a in product.allergens" :key="a" class="allergen-tag">
                            {{ a }}
                        </span>
                    </div>
                </div>

                <div v-if="product.stock_quantity === 0" class="out-of-stock-banner">
                    <span class="out-icon">✗</span>
                    <div>
                        <strong>Produit en rupture de stock</strong>
                        <p>Ce produit n'est pas disponible pour le moment. Revenez bientôt !</p>
                    </div>
                </div>

                <div v-else class="stock-info in-stock-info">
                    <span class="in-stock">✓ En stock ({{ product.stock_quantity }} disponibles)</span>
                </div>

                <div class="actions">
                    <div class="quantity">
                        <button @click="decrease" :disabled="quantity <= 1">−</button>
                        <span>{{ quantity }}</span>
                        <button @click="increase" :disabled="quantity >= product.stock_quantity">+</button>
                    </div>
                    <button
                        class="btn-primary"
                        :disabled="product.stock_quantity === 0"
                        @click="addToCart"
                    >
                        Ajouter au panier — {{ formatPrice(product.price * quantity) }}
                    </button>
                </div>

                <p v-if="added" class="added-msg">✓ Ajouté au panier !</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useCartStore } from '../stores/cart';

const route = useRoute();
const cart = useCartStore();

const product = ref(null);
const loading = ref(true);
const error = ref(null);
const quantity = ref(1);
const added = ref(false);

const emojiMap = {
    cake: '🍰',
    dry_pastry: '🍪',
    savory: '🥐',
    sweet_bite: '🧁',
    event_package: '🎉',
};

const emoji = computed(() => emojiMap[product.value?.type?.slug] || '🍮');

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
}

function increase() {
    if (quantity.value < product.value.stock_quantity) quantity.value++;
}
function decrease() {
    if (quantity.value > 1) quantity.value--;
}

function addToCart() {
    cart.addItem(product.value, quantity.value);
    added.value = true;
    setTimeout(() => (added.value = false), 2000);
}

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/products/${route.params.slug}`);
        product.value = data;
    } catch (e) {
        error.value = e.response?.data?.message
            ? `Produit introuvable : ${e.response.data.message}`
            : `Produit introuvable (${e.response?.status ?? e.message})`;
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
/* Badge rupture sur l'image */
.product-visual {
    position: relative;   /* ← ajoutez ceci à la règle existante */
    overflow: hidden;
}

.rupture-badge {
    position: absolute;
    top: 30px;
    right: -50px;
    background: var(--raspberry);
    color: white;
    font-size: 0.85rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    padding: 0.5rem 3.5rem;
    transform: rotate(35deg);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
    z-index: 2;
    text-transform: uppercase;
    white-space: nowrap;
}

/* Bannière rupture */
.out-of-stock-banner {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(201, 79, 109, 0.08);
    border: 1.5px solid rgba(201, 79, 109, 0.3);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}

.out-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--raspberry);
    color: white;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.out-of-stock-banner strong {
    display: block;
    color: var(--raspberry);
    font-size: 0.95rem;
    margin-bottom: 0.15rem;
}

.out-of-stock-banner p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Stock disponible (info discrète) */
.in-stock-info {
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.in-stock {
    color: #059669;
}
.btn-primary:disabled {
    background: var(--cream-dark);
    color: var(--text-muted);
    cursor: not-allowed;
    box-shadow: none;
}
.free-delivery-hint {
    background: rgba(201, 123, 63, 0.08);
    color: var(--caramel-dark);
    padding: 0.6rem 1rem;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 1rem;
}


.state {
    text-align: center;
    padding: 4rem;
    color: var(--text-muted);
}
.state.error { color: var(--raspberry); }

.product-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    padding: 2rem 0;
    align-items: start;
}

.product-visual {
    aspect-ratio: 1;
    background: linear-gradient(135deg, var(--cream-dark), rgba(232, 167, 106, 0.4));
    border-radius: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 6rem;
}

.product-emoji {
    font-size: 14rem;
    animation: float 5s ease-in-out infinite;
}

.back-link {
    display: inline-block;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.back-link:hover { color: var(--caramel); }

.product-type {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--caramel);
    margin-bottom: 0.5rem;
}

.product-title {
    font-size: 2.75rem;
    margin-bottom: 1rem;
}

.product-desc {
    font-size: 1.05rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.product-price {
    font-size: 2rem;
    font-weight: 700;
    color: var(--chocolate);
    margin-bottom: 2rem;
}

.allergens {
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    background: white;
    border-radius: var(--radius-sm);
}

.allergens-title {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--chocolate-soft);
    margin-bottom: 0.75rem;
}

.allergen-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.allergen-tag {
    background: rgba(201, 79, 109, 0.1);
    color: var(--raspberry);
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
}

.stock-info {
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.in-stock { color: #059669; }
.out-of-stock { color: var(--raspberry); }

.actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.quantity {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: white;
    border-radius: 999px;
    padding: 0.35rem;
    box-shadow: var(--shadow-sm);
}

.quantity button {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--cream);
    color: var(--chocolate);
    font-size: 1.1rem;
    font-weight: 600;
    transition: background 0.2s;
}

.quantity button:hover:not(:disabled) {
    background: var(--caramel-light);
    color: white;
}

.quantity button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.quantity span {
    min-width: 32px;
    text-align: center;
    font-weight: 600;
}

.added-msg {
    margin-top: 1rem;
    color: #059669;
    font-weight: 600;
    animation: fadeInUp 0.3s ease;
}



@media (max-width: 900px) {
    .product-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .product-title {
        font-size: 2rem;
    }
    .product-visual {
        position: static;
    }
    .product-emoji {
        font-size: 7rem;
    }
    .actions {
        flex-direction: column;
        align-items: stretch;
    }
    .quantity {
        align-self: center;
    }
    .rupture-badge {
        font-size: 0.75rem;
        padding: 0.4rem 2.5rem;
        top: 20px;
        right: -40px;
    }
}
</style>