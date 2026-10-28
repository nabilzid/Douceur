<!-- Page Produit unique -->
<template>
  <main class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

      <!-- Chargement -->
      <div
        v-if="loading"
        class="flex min-h-[65vh] flex-col items-center justify-center gap-4"
      >
        <div class="relative grid h-16 w-16 place-items-center">
          <div class="absolute inset-0 rounded-full border border-caramel/20"></div>
          <div class="absolute inset-1 animate-spin rounded-full border-2 border-transparent border-t-caramel"></div>
          <span class="text-2xl">🥐</span>
        </div>
        <p class="text-sm tracking-wide text-muted">
          Un instant, on prépare votre découverte...
        </p>
      </div>

      <!-- Erreur -->
      <div
        v-else-if="error"
        class="mx-auto mt-10 max-w-lg rounded-[2rem] border border-caramel/10 bg-white px-6 py-14 text-center shadow-soft sm:px-10"
      >
        <div class="mx-auto mb-6 grid h-20 w-20 place-items-center rounded-3xl bg-raspberry/10 text-4xl">
          🥺
        </div>

        <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-raspberry">
          Petite mésaventure
        </p>

        <h1 class="font-display text-3xl font-bold text-chocolate">
          Ce produit reste introuvable
        </h1>

        <p class="mx-auto mt-4 max-w-sm text-sm leading-7 text-muted">
          {{ error }}
        </p>

        <RouterLink
          to="/catalogue"
          class="mt-8 inline-flex items-center gap-3 rounded-full bg-caramel px-7 py-3.5 text-sm font-semibold text-white shadow-soft transition-all duration-300 hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card"
        >
          <span aria-hidden="true">←</span>
          Explorer le catalogue
        </RouterLink>
      </div>

      <!-- Produit -->
      <div v-else-if="product">

        <!-- Navigation -->
        <div class="mb-7 flex flex-wrap items-center justify-between gap-4 border-b border-chocolate/10 pb-5">
          <RouterLink
            to="/catalogue"
            class="group inline-flex items-center gap-2 text-sm font-medium text-muted transition-colors hover:text-caramel-dark"
          >
            <span class="transition-transform duration-200 group-hover:-translate-x-1">←</span>
            Retour à la boutique
          </RouterLink>

          <div class="flex items-center gap-2 text-xs font-medium tracking-wide text-muted">
            <span class="h-1.5 w-1.5 rounded-full bg-caramel"></span>
            <span>COLLECTION GOURMANDE</span>
          </div>
        </div>

        <div class="grid items-start gap-8 lg:grid-cols-[1.08fr_0.92fr] lg:gap-14 xl:gap-20">

          <!-- Visuel -->
          <section
            class="group relative isolate flex aspect-[4/4.5] items-center justify-center overflow-hidden rounded-[2rem] bg-gradient-to-br from-cream-dark via-[#f8e8d8] to-caramel-light/40 sm:aspect-square lg:sticky lg:top-8 lg:rounded-[2.5rem]"
          >
            <!-- Éléments décoratifs -->
            <div class="absolute -left-20 -top-20 h-72 w-72 rounded-full border border-white/30"></div>
            <div class="absolute -left-10 -top-10 h-52 w-52 rounded-full border border-white/30"></div>
            <div class="absolute -bottom-24 -right-20 h-80 w-80 rounded-full bg-white/20 blur-3xl"></div>

            <!-- Label -->
            <div class="absolute left-5 top-5 z-10 sm:left-8 sm:top-8">
              <span class="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/60 px-4 py-2.5 text-[10px] font-bold tracking-[0.18em] text-chocolate backdrop-blur-md sm:text-xs">
                <span class="text-caramel">✳</span>
                NOTRE SÉLECTION
              </span>
            </div>

            <!-- Emoji -->
            <span
              class="relative z-0 select-none text-[8rem] drop-shadow-[0_24px_24px_rgba(92,53,31,0.13)] transition-transform duration-700 ease-out group-hover:scale-105 sm:text-[11rem] lg:text-[13rem] xl:text-[15rem]"
              role="img"
              :aria-label="product.name"
            >
              {{ emoji }}
            </span>

            <!-- Signature -->
            <div class="absolute bottom-5 left-5 right-5 z-10 flex items-end justify-between gap-4 sm:bottom-8 sm:left-8 sm:right-8">
              <div>
                <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.22em] text-chocolate/60">
                  Le plaisir des bonnes choses
                </p>
                <p class="font-display text-xl font-bold text-chocolate sm:text-2xl">
                  Une envie, un instant.
                </p>
              </div>

              <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full border border-white/60 bg-white/50 text-xl backdrop-blur-md">
                ✨
              </div>
            </div>

            <!-- Rupture -->
            <div
              v-if="product.stock_quantity === 0"
              class="absolute right-0 top-0 z-20 m-5 rounded-full bg-raspberry px-5 py-2.5 text-xs font-extrabold tracking-[0.15em] text-white shadow-md sm:m-7"
            >
              ÉPUISÉ
            </div>
          </section>

          <!-- Informations -->
          <section class="min-w-0 py-2 lg:py-5">

            <!-- Catégorie et disponibilité -->
            <div class="mb-5 flex flex-wrap items-center gap-3">
              <span class="text-xs font-bold uppercase tracking-[0.2em] text-caramel-dark">
                {{ product.type?.name || 'Notre gourmandise' }}
              </span>

              <span class="h-1 w-1 rounded-full bg-chocolate/30"></span>

              <span
                v-if="product.stock_quantity > 0"
                class="inline-flex items-center gap-2 text-xs font-medium text-emerald-700"
              >
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Disponible
              </span>

              <span
                v-else
                class="inline-flex items-center gap-2 text-xs font-medium text-raspberry"
              >
                <span class="h-2 w-2 rounded-full bg-raspberry"></span>
                Temporairement indisponible
              </span>
            </div>

            <!-- Titre -->
            <h1 class="max-w-xl font-display text-4xl leading-[1.1] font-bold tracking-tight text-chocolate sm:text-5xl lg:text-[3.5rem]">
              {{ product.name }}
            </h1>

            <!-- Petit accent -->
            <div class="mt-6 flex items-center gap-2">
              <span class="h-1 w-12 rounded-full bg-caramel"></span>
              <span class="h-1 w-3 rounded-full bg-caramel/30"></span>
            </div>

            <!-- Description -->
            <p class="mt-6 max-w-xl text-sm leading-7 text-muted sm:text-base sm:leading-8">
              {{ product.description || 'Une création gourmande préparée avec soin pour vous offrir un délicieux moment.' }}
            </p>

            <!-- Prix -->
            <div class="mt-8 flex flex-wrap items-baseline gap-3">
              <span class="font-display text-4xl font-bold tracking-tight text-chocolate sm:text-5xl">
                {{ formatPrice(product.price) }}
              </span>
              <span class="text-xs font-medium text-muted">
                Prix TTC
              </span>
            </div>

            <!-- Livraison -->
            <div
              v-if="cart.items.length > 0 && cart.missingForFreeDelivery > 0"
              class="mt-7 flex items-start gap-3 rounded-2xl border border-caramel/15 bg-caramel/8 p-4"
            >
              <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-lg shadow-sm">
                🚚
              </span>

              <div>
                <p class="text-sm font-bold text-chocolate">
                  La livraison offerte approche !
                </p>
                <p class="mt-1 text-xs leading-5 text-muted">
                  Plus que
                  <strong class="text-caramel-dark">
                    {{ formatPrice(cart.missingForFreeDelivery) }}
                  </strong>
                  pour en profiter.
                </p>
              </div>
            </div>

            <!-- Allergènes -->
            <div
              v-if="product.allergens?.length"
              class="mt-7 rounded-2xl border border-chocolate/10 bg-white/70 p-5 sm:p-6"
            >
              <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-caramel-dark">
                    À savoir
                  </p>
                  <h2 class="mt-1 font-display text-lg font-bold text-chocolate">
                    Allergènes
                  </h2>
                </div>

                <span class="grid h-10 w-10 place-items-center rounded-xl bg-cream text-lg">
                  ℹ️
                </span>
              </div>

              <div class="flex flex-wrap gap-2">
                <span
                  v-for="allergen in product.allergens"
                  :key="allergen"
                  class="rounded-full border border-raspberry/10 bg-raspberry/5 px-3.5 py-2 text-xs font-medium text-raspberry"
                >
                  {{ allergen }}
                </span>
              </div>
            </div>

            <!-- Rupture de stock -->
            <div
              v-if="product.stock_quantity === 0"
              class="mt-7 flex items-start gap-3 rounded-2xl border border-raspberry/20 bg-raspberry/5 p-4"
              role="status"
            >
              <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-raspberry text-sm font-bold text-white">
                !
              </span>

              <div>
                <p class="text-sm font-bold text-raspberry">
                  Victime de son succès
                </p>
                <p class="mt-1 text-xs leading-6 text-muted">
                  Ce produit n'est plus disponible pour le moment. Revenez bientôt !
                </p>
              </div>
            </div>

            <!-- Stock -->
            <div
              v-else
              class="mt-7 flex items-center justify-between gap-3 border-y border-chocolate/10 py-4"
            >
              <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-600">
                  ✓
                </span>

                <div>
                  <p class="text-sm font-semibold text-chocolate">
                    En stock
                  </p>
                  <p class="mt-0.5 text-xs text-muted">
                    Prêt à rejoindre votre panier
                  </p>
                </div>
              </div>

              <span class="text-xs font-medium text-muted">
                {{ product.stock_quantity }} disponible{{ product.stock_quantity > 1 ? 's' : '' }}
              </span>
            </div>

            <!-- Zone d'achat -->
            <div class="mt-7 rounded-3xl border border-chocolate/10 bg-white p-4 shadow-soft sm:p-5">

              <p class="mb-4 text-xs font-bold uppercase tracking-[0.15em] text-chocolate-soft">
                Votre sélection
              </p>

              <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                <!-- Quantité -->
                <div class="flex h-14 shrink-0 items-center justify-between gap-1 rounded-full border border-chocolate/10 bg-cream/60 p-1.5 sm:w-36">
                  <button
                    type="button"
                    @click="decrease"
                    :disabled="quantity <= 1 || product.stock_quantity === 0"
                    aria-label="Diminuer la quantité"
                    class="grid h-10 w-10 place-items-center rounded-full text-lg font-medium text-chocolate transition-colors hover:bg-caramel hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-chocolate"
                  >
                    −
                  </button>

                  <span
                    class="min-w-7 text-center text-sm font-bold tabular-nums text-chocolate"
                    aria-live="polite"
                  >
                    {{ quantity }}
                  </span>

                  <button
                    type="button"
                    @click="increase"
                    :disabled="quantity >= product.stock_quantity || product.stock_quantity === 0"
                    aria-label="Augmenter la quantité"
                    class="grid h-10 w-10 place-items-center rounded-full text-lg font-medium text-chocolate transition-colors hover:bg-caramel hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-chocolate"
                  >
                    +
                  </button>
                </div>

                <!-- Bouton : couleur caramel conservée -->
                <button
                  type="button"
                  @click="addToCart"
                  :disabled="product.stock_quantity === 0"
                  class="inline-flex min-h-14 flex-1 items-center justify-center gap-2 rounded-full bg-caramel px-5 py-4 text-sm font-semibold text-white shadow-soft transition-all duration-300 hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-caramel disabled:translate-y-0 disabled:cursor-not-allowed disabled:bg-cream-dark disabled:text-muted disabled:shadow-none disabled:hover:bg-cream-dark"
                >
                  <span aria-hidden="true">＋</span>

                  <span>
                    {{ product.stock_quantity === 0 ? 'Produit épuisé' : 'Ajouter au panier' }}
                  </span>

                  <span
                    v-if="product.stock_quantity > 0"
                    class="ml-auto whitespace-nowrap text-white/90"
                  >
                    {{ formatPrice(product.price * quantity) }}
                  </span>
                </button>
              </div>

              <!-- Confirmation -->
              <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div
                  v-if="added"
                  class="mt-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                  role="status"
                  aria-live="polite"
                >
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 font-bold text-emerald-700">
                    ✓
                  </span>

                  <div>
                    <p class="text-sm font-bold text-emerald-800">
                      C'est dans votre panier !
                    </p>
                    <p class="mt-1 text-xs text-emerald-700">
                      Votre gourmandise a bien été ajoutée.
                    </p>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Réassurance -->
            <div class="mt-5 grid grid-cols-2 gap-3">
              <div class="flex items-center gap-3 rounded-2xl bg-white/60 p-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cream text-lg">
                  🥐
                </span>
                <div>
                  <p class="text-xs font-bold text-chocolate">
                    Pause gourmande
                  </p>
                  <p class="mt-1 text-[10px] leading-4 text-muted">
                    Un plaisir à chaque bouchée
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3 rounded-2xl bg-white/60 p-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cream text-lg">
                  🤎
                </span>
                <div>
                  <p class="text-xs font-bold text-chocolate">
                    Choisi avec soin
                  </p>
                  <p class="mt-1 text-[10px] leading-4 text-muted">
                    Le goût avant tout
                  </p>
                </div>
              </div>
            </div>

          </section>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
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

let addedTimeout;

const emojiMap = {
  cake: '🍰',
  dry_pastry: '🍪',
  savory: '🥐',
  sweet_bite: '🧁',
  event_package: '🎉',
};

const emoji = computed(
  () => emojiMap[product.value?.type?.slug] || '🍮'
);

function formatPrice(price) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(price);
}

function increase() {
  if (
    product.value &&
    quantity.value < product.value.stock_quantity
  ) {
    quantity.value++;
  }
}

function decrease() {
  if (quantity.value > 1) {
    quantity.value--;
  }
}

function addToCart() {
  if (!product.value || product.value.stock_quantity <= 0) {
    return;
  }

  if (quantity.value > product.value.stock_quantity) {
    quantity.value = product.value.stock_quantity;
  }

  cart.addItem(product.value, quantity.value);
  added.value = true;

  clearTimeout(addedTimeout);

  addedTimeout = setTimeout(() => {
    added.value = false;
  }, 2500);
}

onMounted(async () => {
  try {
    const { data } = await axios.get(
      `/api/products/${route.params.slug}`
    );

    product.value = data;
    quantity.value = 1;
  } catch (e) {
    error.value = e.response?.data?.message
      ? `Produit introuvable : ${e.response.data.message}`
      : `Produit introuvable (${e.response?.status ?? e.message})`;
  } finally {
    loading.value = false;
  }
});

onBeforeUnmount(() => {
  clearTimeout(addedTimeout);
});
</script>
