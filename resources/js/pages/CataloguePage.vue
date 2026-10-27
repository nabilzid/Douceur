<template>
    <div class="catalogue">
        <header class="catalogue-header">
            <span class="catalogue-tag">Notre sélection</span>
            <h1>Le catalogue</h1>
            <p>Gâteaux, secs, salés et sucrés — tout ce qu'il faut pour se régaler.</p>
        </header>
        <p class="catalogue-info">
            🚚 Livraison offerte dès {{ formatPrice(settings.delivery.free_threshold) }} d'achat
        </p>
        <TypeFilter
            :types="store.types"
            :model-value="selectedType"
            @update:model-value="selectedType = $event"
        />
        <p v-if="selectedType === 'event_package'" class="type-hint">
            Nos offres et promotions du moment, à commander directement.
        </p>
        <div v-if="store.loading" class="state">Chargement…</div>
        <div v-else-if="store.error" class="state error">{{ store.error }}</div>
        <div v-else-if="filteredProducts.length === 0" class="state">
            Aucun produit dans cette catégorie pour l'instant.
        </div>

        <div v-else class="grid">
            <RouterLink
                v-for="(product, index) in filteredProducts"
                :key="product.id"
                :to="`/produit/${product.slug}`"
                :class="['card', 'fade-in-up', { 'out-of-stock': product.stock_quantity === 0 }]"
                :style="{ animationDelay: `${index * 60}ms` }"
            >
                <div class="card-visual">
                    <span class="card-emoji">{{ emojiFor(product.type?.slug) }}</span>
                    <!-- ✅ Badge rupture -->
                    <span v-if="product.stock_quantity === 0" class="rupture-badge">RUPTURE</span>
                </div>
                <div class="card-body">
                    <span class="card-type">{{ product.type?.name }}</span>
                    <h2 class="card-title">{{ product.name }}</h2>
                    <div class="card-footer">
                        <span class="card-price">{{ formatPrice(product.price) }}</span>
                        <span class="card-arrow">→</span>
                    </div>
                </div>
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import TypeFilter from '../components/catalogue/TypeFilter.vue';
import { useProductsStore } from '../stores/products';
import { useSettingsStore } from '../stores/settings';
const settings = useSettingsStore();
const store = useProductsStore();
const selectedType = ref(null);

const filteredProducts = computed(() => store.byType(selectedType.value));

const emojiMap = {
    cake: '🍰',
    dry_pastry: '🍪',
    savory: '🥐',
    sweet_bite: '🧁',
    event_package: '🎉',
};


function emojiFor(slug) {
    return emojiMap[slug] || '🍮';
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
}

onMounted(() => {
    store.fetchProducts();
    store.fetchTypes();
});
</script>

<style scoped>
.catalogue-header {
    text-align: center;
    padding: 2rem 0 2rem;
}

.catalogue-tag {
    display: inline-block;
    background: rgba(201, 123, 63, 0.1);
    color: var(--caramel-dark);
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.catalogue-header h1 {
    font-size: 3rem;
    margin-bottom: 0.75rem;
}

.catalogue-header p {
    color: var(--text-muted);
    font-size: 1.05rem;
}

.state {
    text-align: center;
    padding: 4rem;
    color: var(--text-muted);
}

.state.error {
    color: var(--raspberry);
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 2rem;
}

.card {
    position: relative;   /* ← AJOUT : pour positionner le badge */
    background: white;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    color: inherit;
    display: flex;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-md);
    color: inherit;
}

.card-visual {
    position: relative;   /* ← AJOUT : pour positionner le badge */
    aspect-ratio: 4 / 3;
    background: linear-gradient(135deg, var(--cream-dark), rgba(232, 167, 106, 0.3));
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;   /* ← AJOUT : pour couper le badge qui dépasse */
}

.card-emoji {
    font-size: 5rem;
    transition: transform 0.4s ease;
}

.card:hover .card-emoji {
    transform: scale(1.15) rotate(-6deg);
}

/* ✅ Badge RUPTURE */
.rupture-badge {
    position: absolute;
    top: 20px;
    right: -35px;
    background: var(--raspberry);
    color: white;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    padding: 0.35rem 2.5rem;
    transform: rotate(35deg);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
    z-index: 2;
    text-transform: uppercase;
    white-space: nowrap;
}

.card.out-of-stock {
    opacity: 0.75;
}

.card.out-of-stock .card-emoji {
    filter: grayscale(0.6);
}

.card.out-of-stock:hover {
    transform: none;
    cursor: not-allowed;
    box-shadow: var(--shadow-sm);
}

.card-body {
    padding: 1.25rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    flex: 1;
}

.card-type {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--caramel);
}

.card-title {
    font-size: 1.25rem;
    line-height: 1.3;
    margin: 0;
}

.card-footer {
    margin-top: auto;
    padding-top: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-price {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--chocolate);
}

.card-arrow {
    color: var(--caramel);
    font-size: 1.25rem;
    transition: transform 0.3s ease;
}

.card:hover .card-arrow {
    transform: translateX(4px);
}

.card.out-of-stock:hover .card-arrow {
    transform: none;
}

.type-hint {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.catalogue-info {
    display: inline-block;
    background: rgba(201, 123, 63, 0.08);
    color: var(--caramel-dark);
    padding: 0.4rem 1rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

@media (max-width: 900px) {
    .catalogue-header h1 {
        font-size: 2.25rem;
    }
    .catalogue-header p {
        font-size: 0.95rem;
    }
    .grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
    }
    .card-emoji {
        font-size: 3.5rem;
    }
    .card-body {
        padding: 0.9rem 1rem 1.1rem;
    }
    .card-title {
        font-size: 1.05rem;
    }
    .card-price {
        font-size: 1rem;
    }
    .rupture-badge {
        font-size: 0.6rem;
        padding: 0.3rem 2rem;
        top: 14px;
        right: -28px;
    }
}

@media (max-width: 480px) {
    .grid {
        grid-template-columns: 1fr;
    }
    .card-emoji {
        font-size: 4rem;
    }
    .rupture-badge {
        font-size: 0.65rem;
        padding: 0.35rem 2.5rem;
        top: 20px;
        right: -35px;
    }
}
</style>