<template>
    <div class="cart-page">
        <header class="cart-header">
            <h1>Votre panier</h1>
            <p v-if="cart.items.length > 0">{{ cart.itemCount }} article(s)</p>
        </header>

        <div v-if="cart.items.length === 0" class="empty">
            <span class="empty-emoji">🧺</span>
            <h2>Votre panier est vide</h2>
            <p>Découvrez nos pâtisseries artisanales.</p>
            <RouterLink to="/catalogue" class="btn-primary">Voir le catalogue →</RouterLink>
        </div>

        <div v-else class="cart-layout">
            <div class="cart-items">
                <article v-for="item in cart.items" :key="item.id" :class="{ 'out-of-stock': item.stock_quantity === 0 }">
                    <span v-if="item.stock_quantity === 0" class="stock-warning">⚠️ Rupture</span>
                    <div class="item-visual">
                        <span class="item-emoji">{{ emojiFor(item.type?.slug) }}</span>
                    </div>
                    <div class="item-info">
                        <span class="item-type">{{ item.type?.name }}</span>
                        <h3 class="item-name">{{ item.name }}</h3>
                        <span class="item-price">{{ formatPrice(item.price) }}</span>
                    </div>
                    <div class="item-actions">
                        <div class="quantity">
                            <button @click="cart.updateQuantity(item.id, item.quantity - 1)">−</button>
                            <span>{{ item.quantity }}</span>
                            <button @click="cart.updateQuantity(item.id, item.quantity + 1)">+</button>
                        </div>
                        <span class="item-total">{{ formatPrice(item.price * item.quantity) }}</span>
                        <button class="remove" @click="cart.removeItem(item.id)" aria-label="Supprimer">
                            ✕
                        </button>
                    </div>
                </article>
            </div>

            <aside class="cart-summary">
                <h2>Récapitulatif</h2>

                <div class="delivery-toggle">
                    <button
                        :class="['toggle-btn', { active: cart.isDelivery }]"
                        @click="cart.setDeliveryType('delivery')"
                    >
                        🚚 Livraison
                    </button>
                    <button
                        :class="['toggle-btn', { active: !cart.isDelivery }]"
                        @click="cart.setDeliveryType('pickup')"
                    >
                        🏪 Click & Collect
                    </button>
                </div>

                <!-- Cas 1 : livraison non disponible (sous le seuil minimum) -->
                <div v-if="cart.isDelivery && cart.missingForDelivery > 0" class="warning">
                    Ajoutez {{ formatPrice(cart.missingForDelivery) }} de produits pour débloquer la livraison
                    (min. {{ formatPrice(settings.delivery.min_order_amount) }}).
                    En dessous, choisissez le Click & Collect.
                </div>

                <!-- Cas 2 : livraison disponible mais pas encore gratuite -->
                <div
                    v-else-if="cart.isDelivery && cart.missingForFreeDelivery > 0"
                    class="progress-block"
                >
                    <div class="progress-text">
                        Plus que <strong>{{ formatPrice(cart.missingForFreeDelivery) }}</strong>
                        pour la livraison gratuite 🎉
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                    </div>
                </div>

                <!-- Cas 3 : livraison gratuite atteinte -->
                <div
                    v-else-if="cart.isDelivery && cart.missingForFreeDelivery === 0 && cart.subtotal > 0"
                    class="progress-block success"
                >
                    🎉 Vous bénéficiez de la livraison gratuite !
                </div>

                <div class="summary-line">
                    <span>Sous-total</span>
                    <span>{{ formatPrice(cart.subtotal) }}</span>
                </div>
                <div class="summary-line">
                    <span>Livraison</span>
                    <span>
                        {{ cart.isDelivery
                            ? (cart.deliveryFee === 0 ? 'Offerte' : formatPrice(cart.deliveryFee))
                            : '—' }}
                    </span>
                </div>

                <div class="summary-total">
                    <span>Total</span>
                    <span>{{ formatPrice(cart.total) }}</span>
                </div>

                <button
                    class="btn-primary checkout-btn"
                    :disabled="!cart.canCheckout"
                    @click="goToCheckout"
                >
                    Passer commande →
                </button>

                <RouterLink to="/catalogue" class="continue-link">
                    ← Continuer mes achats
                </RouterLink>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useSettingsStore } from '../stores/settings';

const cart = useCartStore();
const settings = useSettingsStore();
const router = useRouter();

const progressPercent = computed(() => {
    if (!cart.isDelivery) return 0;
    const threshold = settings.delivery.free_threshold;
    if (!threshold) return 0;
    return Math.min(100, (cart.subtotal / threshold) * 100);
});

const emojiMap = {
    cake: '🍰',
    dry_pastry: '🍪',
    savory: '🥐',
    sweet_bite: '🧁',
    event_package: '🎁',
};

function emojiFor(slug) {
    return emojiMap[slug] || '🍮';
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
}

function goToCheckout() {
    router.push('/commande');
}
</script>

<style scoped>
.cart-item.out-of-stock {
    opacity: 0.6;
}
.stock-warning {
    color: var(--raspberry);
    font-size: 0.8rem;
    font-weight: 600;
}
.cart-page { padding: 1rem 0 4rem; }

.cart-header { margin-bottom: 2.5rem; }
.cart-header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }
.cart-header p { color: var(--text-muted); }

.empty {
    text-align: center;
    padding: 4rem 1rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
}
.empty-emoji { font-size: 5rem; display: block; margin-bottom: 1rem; }
.empty h2 { margin-bottom: 0.5rem; }
.empty p { color: var(--text-muted); margin-bottom: 2rem; }

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}

.cart-items { display: flex; flex-direction: column; gap: 1rem; }

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 1.25rem;
    align-items: center;
    background: white;
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
}
.cart-item:hover { box-shadow: var(--shadow-md); }

.item-visual {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--cream-dark), rgba(232, 167, 106, 0.3));
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
}
.item-emoji { font-size: 3rem; }

.item-info { display: flex; flex-direction: column; gap: 0.25rem; }
.item-type {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--caramel);
}
.item-name { font-size: 1.15rem; margin: 0; }
.item-price { color: var(--text-muted); font-size: 0.9rem; }

.item-actions {
    display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;
}
.quantity {
    display: inline-flex; align-items: center; gap: 0.25rem;
    background: var(--cream); border-radius: 999px; padding: 0.25rem;
}
.quantity button {
    width: 28px; height: 28px; border-radius: 50%;
    background: white; color: var(--chocolate); font-weight: 600;
    box-shadow: var(--shadow-sm);
}
.quantity button:hover { background: var(--caramel); color: white; }
.quantity span { min-width: 28px; text-align: center; font-weight: 600; }

.item-total { font-weight: 700; color: var(--chocolate); font-size: 1.05rem; }

.remove {
    color: var(--text-muted); font-size: 0.9rem;
    width: 24px; height: 24px; border-radius: 50%;
    transition: all 0.2s;
}
.remove:hover { background: var(--raspberry); color: white; }

.cart-summary {
    background: white;
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 6rem;
}
.cart-summary h2 { font-size: 1.35rem; margin-bottom: 1.5rem; }

.delivery-toggle {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;
    background: var(--cream); padding: 0.35rem; border-radius: 999px;
    margin-bottom: 1.25rem;
}
.toggle-btn {
    padding: 0.6rem 0.5rem; border-radius: 999px;
    font-size: 0.85rem; font-weight: 600; color: var(--text-muted);
    transition: all 0.2s;
}
.toggle-btn.active {
    background: white; color: var(--caramel-dark);
    box-shadow: var(--shadow-sm);
}

.warning {
    background: rgba(201, 79, 109, 0.08);
    color: var(--raspberry);
    padding: 0.75rem 1rem; border-radius: var(--radius-sm);
    font-size: 0.85rem; font-weight: 500;
    margin-bottom: 1.25rem;
}

/* Barre de progression */
.progress-block {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 0.9rem 1rem;
    margin-bottom: 1.25rem;
}
.progress-block.success {
    background: rgba(5, 150, 105, 0.1);
    color: #059669;
    text-align: center;
    font-weight: 600;
}
.progress-text {
    font-size: 0.85rem;
    color: var(--chocolate-soft);
    margin-bottom: 0.5rem;
}
.progress-text strong {
    color: var(--caramel-dark);
}
.progress-bar {
    height: 8px;
    background: var(--cream-dark);
    border-radius: 999px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--caramel), var(--caramel-dark));
    border-radius: 999px;
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.summary-line {
    display: flex; justify-content: space-between;
    padding: 0.6rem 0; color: var(--text-muted); font-size: 0.95rem;
}

.summary-total {
    display: flex; justify-content: space-between;
    padding: 1rem 0 1.25rem; margin-top: 0.5rem;
    border-top: 2px solid var(--cream-dark);
    font-size: 1.35rem; font-weight: 700; color: var(--chocolate);
}

.checkout-btn {
    width: 100%; justify-content: center; margin-bottom: 1rem;
}
.checkout-btn:disabled {
    opacity: 0.5; cursor: not-allowed; transform: none;
}

.continue-link {
    display: block; text-align: center;
    color: var(--text-muted); font-size: 0.9rem;
}

@media (max-width: 900px) {
    .cart-layout { grid-template-columns: 1fr; }
    .cart-summary { position: static; }
    .cart-item { grid-template-columns: 80px 1fr; }
    .item-actions {
        grid-column: 1 / -1;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}
@media (max-width: 480px) {
    .cart-header h1 { font-size: 2rem; }
    .cart-item { padding: 1rem; }
    .item-emoji { font-size: 2rem; }
    .item-name { font-size: 1rem; }
    .delivery-toggle { grid-template-columns: 1fr; }
}
</style>