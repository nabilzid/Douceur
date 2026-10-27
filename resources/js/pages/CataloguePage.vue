<!-- Page header du catalogue -->
<template>
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

    <!-- En-tête -->
    <header class="mb-10 text-center sm:mb-14">
      <span class="text-xs font-semibold uppercase tracking-[0.25em] text-caramel-dark">
        L'atelier gourmand · Paris
      </span>

      <h1 class="mt-4 font-display text-4xl font-bold tracking-tight text-chocolate sm:text-5xl">
        Le catalogue<span class="text-caramel">.</span>
      </h1>

      <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-chocolate-soft/70 sm:text-base">
        Des créations artisanales imaginées avec passion,
        pour toutes vos envies de gourmandise.
      </p>

      <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-gold/30 bg-cream/60 px-4 py-2 text-sm text-chocolate-soft">
        <span>🚚</span>
        Livraison offerte dès
        <strong>{{ formatPrice(settings.delivery.free_threshold) }}</strong>
        d'achat
      </div>
    </header>

    <!-- Filtres -->
    <TypeFilter
      :types="store.types"
      :model-value="selectedType"
      @update:model-value="selectedType = $event"
    />

    <p
      v-if="selectedType === 'event_package'"
      class="mb-6 text-center text-sm italic text-chocolate-soft/70"
    >
      Nos offres et promotions du moment, à commander directement.
    </p>

    <!-- États -->
    <div v-if="store.loading" class="py-20 text-center text-chocolate-soft/70">
      Chargement de vos gourmandises...
    </div>

    <div v-else-if="store.error" class="py-20 text-center text-raspberry">
      {{ store.error }}
    </div>

    <div
      v-else-if="filteredProducts.length === 0"
      class="py-20 text-center text-chocolate-soft/70"
    >
      Aucune gourmandise dans cette catégorie pour l'instant.
    </div>

    <template v-else>
      <div class="mb-6 flex items-end justify-between gap-4">
        <div>
          <h2 class="font-display text-2xl font-bold text-chocolate">
            {{ selectedType
              ? store.types.find(t => t.slug === selectedType)?.name || 'Nos créations'
              : 'Toutes nos gourmandises' }}
          </h2>
          <p class="mt-1 text-sm text-chocolate-soft/60">
            {{ filteredProducts.length }} produit(s) à découvrir
          </p>
        </div>
      </div>

      <!-- Grille produits -->
      <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4">
        <RouterLink
          v-for="product in filteredProducts"
          :key="product.id"
          :to="`/produit/${product.slug}`"
          class="group overflow-hidden rounded-2xl border border-gold/15 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel"
          :class="{ 'opacity-75': product.stock_quantity === 0 }"
        >
        <!-- Visuel -->
        <div class="relative aspect-[4/3] overflow-hidden bg-cream-dark">
            <img
                v-if="product.image_url"
                :src="product.image_url"
                :alt="product.name"
                loading="lazy"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />

            <div
                v-else
                class="grid h-full place-items-center bg-gradient-to-br from-cream-dark to-caramel/20"
            >
                <span class="text-5xl sm:text-6xl">
                {{ emojiFor(product.type?.slug) }}
                </span>
            </div>

            <!-- Catégorie -->
            <span
                class="absolute left-3 top-3 max-w-[calc(100%-1.5rem)] truncate rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-chocolate-soft backdrop-blur sm:text-xs"
            >
                {{ product.type?.name || 'Artisanal' }}
            </span>

            <!-- Épuisé : badge et barre diagonale -->
            <template v-if="product.stock_quantity === 0">
                <div class="absolute inset-0 bg-chocolate/10"></div>

                <div
                class="absolute -right-10 top-8 z-10 w-40 rotate-45 bg-raspberry py-2 text-center text-xs font-bold uppercase tracking-widest text-white shadow-md"
                >
                Épuisé
                </div>
            </template>
        </div>

        <!-- Informations -->
        <div class="flex flex-1 flex-col gap-3 p-3 sm:p-5">
        <div class="flex items-start justify-between gap-2">
            <h3
            class="font-display text-base font-bold leading-snug text-chocolate transition-colors group-hover:text-caramel-dark sm:text-lg"
            >
            {{ product.name }}
            </h3>

            <span
            class="shrink-0 pt-0.5 text-lg text-caramel transition-transform duration-300 group-hover:translate-x-1"
            aria-hidden="true"
            >
            →
            </span>
        </div>

        <div class="mt-auto">
            <span class="text-sm font-bold text-chocolate sm:text-base">
            {{ formatPrice(product.price) }}
            </span>
        </div>
        </div>
        </RouterLink>
      </div>
    </template>
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

const filteredProducts = computed(() =>
  store.byType(selectedType.value)
);

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
  const amount = Number(price);

  if (!Number.isFinite(amount)) return 'Prix indisponible';

  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount);
}

onMounted(() => {
  store.fetchProducts();
  store.fetchTypes();
});
</script>
