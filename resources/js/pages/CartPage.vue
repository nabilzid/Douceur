<!-- Page panier avec la liste des produits -->
<template>
  <main class="mx-auto min-h-screen max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
    <!-- En-tête -->
    <header class="mb-8 sm:mb-10">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-caramel-dark">
        Vos gourmandises
      </p>

      <h1 class="mt-2 font-display text-3xl font-bold text-chocolate sm:text-4xl">
        Votre panier<span class="text-caramel">.</span>
      </h1>

      <p v-if="cart.items.length > 0" class="mt-2 text-sm text-muted sm:text-base">
        {{ cart.itemCount }} article(s) dans votre panier
      </p>
    </header>

    <!-- Panier vide -->
    <section
      v-if="cart.items.length === 0"
      class="rounded-2xl bg-white px-5 py-14 text-center shadow-soft sm:px-8 sm:py-20"
    >
      <div class="mx-auto grid size-20 place-items-center rounded-full bg-cream text-5xl sm:size-24">
        🧺
      </div>

      <h2 class="mt-6 font-display text-2xl font-bold text-chocolate sm:text-3xl">
        Votre panier est vide
      </h2>

      <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted sm:text-base">
        Vos prochaines gourmandises préférées vous attendent.
        Découvrez nos pâtisseries artisanales.
      </p>

      <RouterLink
        to="/catalogue"
        class="mt-7 inline-flex min-h-12 items-center justify-center rounded-full bg-caramel px-7 py-3 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card"
      >
        Voir le catalogue <span class="ml-2">→</span>
      </RouterLink>
    </section>

    <!-- Panier avec articles -->
    <div v-else class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-8 xl:grid-cols-[minmax(0,1fr)_390px]">

      <!-- Liste des produits -->
      <section class="min-w-0">
        <div class="mb-4 flex items-center justify-between gap-3">
          <h2 class="font-display text-xl font-bold text-chocolate sm:text-2xl">
            Vos articles
          </h2>

          <RouterLink
            to="/catalogue"
            class="shrink-0 text-sm font-semibold text-caramel-dark transition hover:text-caramel"
          >
            + Ajouter des produits
          </RouterLink>
        </div>

        <div class="space-y-4">
          <article
            v-for="item in cart.items"
            :key="item.id"
            class="relative grid min-w-0 grid-cols-[76px_minmax(0,1fr)] gap-3 rounded-2xl bg-white p-3 shadow-soft transition hover:shadow-card sm:grid-cols-[104px_minmax(0,1fr)] sm:gap-5 sm:p-5"
            :class="{ 'opacity-70': item.stock_quantity === 0 }"
          >
            <!-- Visuel produit -->
            <div class="relative grid aspect-square place-items-center overflow-hidden rounded-xl bg-gradient-to-br from-cream-dark to-caramel/20 sm:rounded-2xl">
              <span class="text-4xl sm:text-5xl">
                {{ emojiFor(item.type?.slug) }}
              </span>

              <span
                v-if="item.stock_quantity === 0"
                class="absolute -right-7 top-4 w-24 rotate-45 bg-raspberry py-1 text-center text-[9px] font-bold uppercase tracking-wider text-white shadow-sm sm:-right-8 sm:top-5 sm:w-28 sm:text-[10px]"
              >
                Épuisé
              </span>
            </div>

            <!-- Informations produit -->
            <div class="flex min-w-0 flex-col justify-between gap-3 py-0.5 sm:py-1">
              <div class="min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <span class="text-[10px] font-semibold uppercase tracking-[0.12em] text-caramel-dark sm:text-xs">
                    {{ item.type?.name || 'Artisanal' }}
                  </span>

                  <button
                    type="button"
                    class="grid size-8 shrink-0 place-items-center rounded-full text-muted transition hover:bg-raspberry/10 hover:text-raspberry focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-raspberry"
                    :aria-label="`Supprimer ${item.name} du panier`"
                    @click="cart.removeItem(item.id)"
                  >
                    ✕
                  </button>
                </div>

                <h3 class="mt-1 break-words font-display text-base font-bold leading-snug text-chocolate sm:text-lg">
                  {{ item.name }}
                </h3>

                <p class="mt-1 text-xs text-muted sm:text-sm">
                  {{ formatPrice(item.price) }} / unité
                </p>

                <p
                  v-if="item.stock_quantity === 0"
                  class="mt-2 text-xs font-semibold text-raspberry"
                >
                  ⚠️ Produit en rupture de stock
                </p>
              </div>

              <!-- Quantité et sous-total -->
              <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                  <span class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wider text-muted sm:text-xs">
                    Quantité
                  </span>

                  <div class="inline-flex items-center gap-1 rounded-full bg-cream p-1">
                    <button
                      type="button"
                      :disabled="item.quantity <= 1"
                      :aria-label="`Diminuer la quantité de ${item.name}`"
                      class="grid size-8 place-items-center rounded-full bg-white text-lg font-semibold text-chocolate shadow-sm transition hover:bg-caramel hover:text-white disabled:cursor-not-allowed disabled:opacity-40 sm:size-9"
                      @click="cart.updateQuantity(item.id, item.quantity - 1)"
                    >
                      −
                    </button>

                    <span class="min-w-7 text-center text-sm font-bold text-chocolate sm:min-w-8">
                      {{ item.quantity }}
                    </span>

                    <button
                      type="button"
                      :disabled="item.quantity >= item.stock_quantity"
                      :aria-label="`Augmenter la quantité de ${item.name}`"
                      class="grid size-8 place-items-center rounded-full bg-white text-lg font-semibold text-chocolate shadow-sm transition hover:bg-caramel hover:text-white disabled:cursor-not-allowed disabled:opacity-40 sm:size-9"
                      @click="cart.updateQuantity(item.id, item.quantity + 1)"
                    >
                      +
                    </button>
                  </div>
                </div>

                <div class="text-right">
                  <span class="block text-[10px] font-medium uppercase tracking-wider text-muted sm:text-xs">
                    Sous-total
                  </span>
                  <span class="mt-1 block text-sm font-bold text-chocolate sm:text-base">
                    {{ formatPrice(item.price * item.quantity) }}
                  </span>
                </div>
              </div>
            </div>
          </article>
        </div>

        <!-- Continuer les achats -->
        <RouterLink
          to="/catalogue"
          class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-full px-2 py-2 text-sm font-semibold text-caramel-dark transition hover:text-caramel"
        >
          <span>←</span> Continuer mes achats
        </RouterLink>
      </section>

      <!-- Récapitulatif -->
      <aside class="min-w-0 rounded-2xl bg-white p-5 shadow-soft sm:p-7 lg:sticky lg:top-24">
        <div class="mb-6 flex items-center gap-3">
          <div class="grid size-10 place-items-center rounded-xl bg-caramel/10">
            <span class="text-xl">🧾</span>
          </div>

          <h2 class="font-display text-xl font-bold text-chocolate">
            Récapitulatif
          </h2>
        </div>

        <!-- Mode de réception -->
        <div class="mb-6 rounded-2xl bg-cream p-1.5">
          <div class="grid grid-cols-2 gap-1.5">
            <button
              type="button"
              :aria-pressed="cart.isDelivery"
              class="flex min-h-12 items-center justify-center gap-1.5 rounded-xl px-2 py-2 text-xs font-semibold transition sm:text-sm"
              :class="cart.isDelivery
                ? 'bg-white text-caramel-dark shadow-sm'
                : 'text-muted hover:text-chocolate'"
              @click="cart.setDeliveryType('delivery')"
            >
              <span>🚚</span> Livraison
            </button>

            <button
              type="button"
              :aria-pressed="!cart.isDelivery"
              class="flex min-h-12 items-center justify-center gap-1.5 rounded-xl px-2 py-2 text-xs font-semibold transition sm:text-sm"
              :class="!cart.isDelivery
                ? 'bg-white text-caramel-dark shadow-sm'
                : 'text-muted hover:text-chocolate'"
              @click="cart.setDeliveryType('pickup')"
            >
              <span>🏪</span> Click & Collect
            </button>
          </div>
        </div>

        <!-- Livraison sous le minimum -->
        <div
          v-if="cart.isDelivery && cart.missingForDelivery > 0"
          class="mb-5 rounded-xl border border-raspberry/20 bg-raspberry/5 p-4"
        >
          <p class="text-sm font-semibold text-raspberry">
            Livraison non disponible
          </p>

          <p class="mt-1 text-xs leading-5 text-chocolate-soft">
            Ajoutez
            <strong>{{ formatPrice(cart.missingForDelivery) }}</strong>
            de produits pour atteindre le minimum de
            {{ formatPrice(settings.delivery.min_order_amount) }}.
            Vous pouvez choisir le Click & Collect.
          </p>
        </div>

        <!-- Progression vers la livraison gratuite -->
        <div
          v-else-if="cart.isDelivery && cart.missingForFreeDelivery > 0"
          class="mb-5 rounded-xl bg-cream p-4"
        >
          <p class="text-sm leading-5 text-chocolate-soft">
            Plus que
            <strong class="text-caramel-dark">
              {{ formatPrice(cart.missingForFreeDelivery) }}
            </strong>
            pour profiter de la livraison gratuite !
          </p>

          <div
            class="mt-3 h-2 overflow-hidden rounded-full bg-cream-dark"
            role="progressbar"
            :aria-valuenow="Math.round(progressPercent)"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-label="Progression vers la livraison gratuite"
          >
            <div
              class="h-full rounded-full bg-gradient-to-r from-caramel to-caramel-dark transition-[width] duration-500"
              :style="{ width: `${progressPercent}%` }"
            ></div>
          </div>
        </div>

        <!-- Livraison gratuite -->
        <div
          v-else-if="cart.isDelivery && cart.missingForFreeDelivery === 0 && cart.subtotal > 0"
          class="mb-5 flex items-start gap-3 rounded-xl bg-emerald-50 p-4 text-emerald-700"
        >
          <span class="text-lg">🎉</span>
          <div>
            <p class="text-sm font-semibold">Livraison gratuite !</p>
            <p class="mt-1 text-xs leading-5">
              Votre commande bénéficie de la livraison offerte.
            </p>
          </div>
        </div>

        <!-- Détail des montants -->
        <div class="space-y-4">
          <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-muted">Sous-total</span>
            <span class="font-semibold text-chocolate">
              {{ formatPrice(cart.subtotal) }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-muted">Livraison</span>
            <span
              class="text-right font-semibold"
              :class="cart.isDelivery && cart.deliveryFee === 0
                ? 'text-emerald-600'
                : 'text-chocolate'"
            >
              {{
                cart.isDelivery
                  ? (cart.deliveryFee === 0 ? 'Offerte' : formatPrice(cart.deliveryFee))
                  : 'Retrait gratuit'
              }}
            </span>
          </div>
        </div>

        <div class="my-5 border-t border-dashed border-cream-dark"></div>

        <div class="flex items-center justify-between gap-3">
          <span class="font-display text-lg font-bold text-chocolate">
            Total
          </span>
          <span class="font-display text-2xl font-bold text-caramel-dark">
            {{ formatPrice(cart.total) }}
          </span>
        </div>

        <p class="mt-2 text-right text-xs text-muted">
          TTC
        </p>

        <!-- Validation -->
        <div
          v-if="cart.items.some(item => item.stock_quantity === 0)"
          class="mt-5 rounded-xl bg-raspberry/5 p-3 text-xs leading-5 text-raspberry"
        >
          Certains produits sont épuisés. Vérifiez votre panier avant de commander.
        </div>

        <button
          type="button"
          :disabled="!cart.canCheckout"
          class="mt-6 flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-caramel px-5 py-3 text-sm font-bold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card disabled:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
          @click="goToCheckout"
        >
          Passer commande <span aria-hidden="true">→</span>
        </button>

        <p
          v-if="!cart.canCheckout"
          class="mt-3 text-center text-xs leading-5 text-muted"
        >
          Vérifiez la disponibilité des produits et les conditions de livraison.
        </p>

        <div class="mt-5 flex items-center justify-center gap-2 text-xs text-muted">
          <span aria-hidden="true">🔒</span>
          <span>Commande en toute sérénité</span>
        </div>
      </aside>
    </div>
  </main>
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

  const threshold = Number(settings.delivery.free_threshold);

  if (!Number.isFinite(threshold) || threshold <= 0) return 0;

  return Math.min(100, (Number(cart.subtotal) / threshold) * 100);
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
  const amount = Number(price);

  if (!Number.isFinite(amount)) return 'Prix indisponible';

  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount);
}

function goToCheckout() {
  if (!cart.canCheckout) return;

  router.push('/commande');
}
</script>
