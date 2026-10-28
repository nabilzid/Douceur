<!-- Page mes commandes client connecté -->
<template>
  <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">

    <!-- ═══ EN-TÊTE DE PAGE ═══ -->
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <RouterLink
          to="/mon-compte"
          class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-muted transition hover:text-chocolate"
        >
          <span aria-hidden="true">←</span>
          Retour à mon compte
        </RouterLink>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
          Espace client
        </p>
        <h1 class="text-3xl font-bold tracking-tight text-chocolate sm:text-4xl">
          Mes commandes
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-muted sm:text-base">
          Retrouvez l'historique de vos commandes, le détail des articles et le suivi de livraison.
        </p>
      </div>

      <RouterLink
        to="/catalogue"
        class="inline-flex shrink-0 items-center justify-center rounded-full bg-caramel px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2"
      >
        Nouvelle commande
        <span class="ml-2" aria-hidden="true">→</span>
      </RouterLink>
    </header>

    <!-- ═══ CHARGEMENT ═══ -->
    <section
      v-if="loading"
      class="rounded-3xl border border-cream-dark bg-white p-10 text-center shadow-soft"
      aria-live="polite"
    >
      <span class="mx-auto block h-9 w-9 animate-spin rounded-full border-4 border-cream-dark border-t-caramel" />
      <p class="mt-4 text-sm text-muted">Chargement de vos commandes…</p>
    </section>

    <!-- ═══ AUCUNE COMMANDE ═══ -->
    <section
      v-else-if="orders.length === 0"
      class="rounded-3xl border border-cream-dark bg-white px-5 py-14 text-center shadow-soft sm:px-10"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cream text-3xl" aria-hidden="true">
        🧺
      </div>
      <h2 class="mt-5 text-xl font-bold text-chocolate">Aucune commande pour le moment</h2>
      <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">
        Vos commandes et leur suivi apparaîtront ici dès votre première commande.
      </p>
      <RouterLink
        to="/catalogue"
        class="mt-6 inline-flex items-center justify-center rounded-full bg-caramel px-5 py-3 text-sm font-semibold text-white transition hover:bg-caramel-dark"
      >
        Découvrir le catalogue
        <span class="ml-2" aria-hidden="true">→</span>
      </RouterLink>
    </section>

    <!-- ═══ LISTE DES COMMANDES ═══ -->
    <section v-else class="space-y-5">
      <article
        v-for="order in orders"
        :key="order.id"
        class="rounded-3xl border border-cream-dark/80 bg-white shadow-soft transition-shadow duration-200 hover:shadow-card"
      >
        <!-- Résumé toujours visible -->
        <button
          type="button"
          class="flex w-full flex-col gap-4 p-4 text-left transition hover:bg-cream/60 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-caramel sm:flex-row sm:items-center sm:justify-between sm:p-6"
          :aria-expanded="Boolean(openOrders[order.id])"
          :aria-controls="`order-panel-${order.id}`"
          @click="toggleOrder(order.id)"
        >
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-base font-bold text-chocolate sm:text-lg">
                Commande {{ order.reference }}
              </span>
              <span
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                :class="statusClasses(order.status)"
              >
                {{ statusLabel(order.status) }}
              </span>
            </div>
            <p class="mt-2 text-sm leading-6 text-muted">
              <span aria-hidden="true">📅</span>
              {{ formatDate(order.created_at) }}
              <span v-if="order.delivery_type" class="mx-1 text-cream-dark">·</span>
              <span v-if="order.delivery_type" aria-hidden="true">
                {{ order.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
              </span>
            </p>
            <p v-if="order.total_amount != null" class="mt-2 text-sm font-semibold text-chocolate">
              Total : {{ formatPrice(order.total_amount) }}
            </p>
          </div>

          <div class="flex shrink-0 items-center justify-between gap-4 sm:justify-end">
            <span class="text-sm font-medium text-muted">
              {{ openOrders[order.id] ? 'Masquer les détails' : 'Voir les détails' }}
            </span>
            <span
              class="flex h-9 w-9 items-center justify-center rounded-full bg-cream text-chocolate transition-transform duration-200"
              :class="{ 'rotate-180': openOrders[order.id] }"
              aria-hidden="true"
            >
              ▾
            </span>
          </div>
        </button>

        <!-- Contenu dépliable -->
        <Transition name="accordion">
          <div
            v-if="openOrders[order.id]"
            :id="`order-panel-${order.id}`"
            class="space-y-5 border-t border-cream-dark/80 p-4 sm:p-6"
          >
            <!-- 1. Récapitulatif des articles -->
            <section class="overflow-hidden rounded-2xl border border-cream-dark bg-cream">
              <div class="flex items-start gap-3 border-b border-cream-dark/80 px-4 py-4 sm:px-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                  🧾
                </span>
                <div class="min-w-0">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">
                    Détail de la commande
                  </p>
                  <h2 class="mt-1 text-lg font-bold text-chocolate">Récapitulatif</h2>
                  <p class="mt-1 text-sm text-muted">
                    Référence : {{ order.reference }}
                  </p>
                </div>
              </div>

              <div class="space-y-4 p-4 sm:p-5">
                <!-- Items -->
                <ul class="space-y-2 rounded-xl border border-cream-dark/80 bg-white p-4">
                  <li
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex justify-between gap-3 text-sm text-chocolate"
                  >
                    <span class="truncate">
                      {{ item.product_name }}
                      <span class="text-muted">× {{ item.quantity }}</span>
                    </span>
                    <span class="font-semibold shrink-0">{{ formatPrice(item.line_total) }}</span>
                  </li>
                </ul>

                <!-- Détail financier -->
                <div class="rounded-xl border border-cream-dark/80 bg-white p-4 space-y-2">
                  <div class="flex justify-between text-sm text-muted">
                    <span>Sous-total</span>
                    <span class="text-chocolate-soft font-medium">{{ formatPrice(order.subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm text-muted">
                    <span>{{ order.delivery_type === 'delivery' ? 'Livraison' : 'Retrait' }}</span>
                    <span
                      class="font-medium"
                      :class="order.delivery_fee > 0 ? 'text-chocolate-soft' : 'text-emerald-600'"
                    >
                      {{ order.delivery_fee > 0 ? formatPrice(order.delivery_fee) : 'Offerte' }}
                    </span>
                  </div>
                  <div class="flex justify-between items-baseline pt-3 mt-1 border-t-2 border-cream-dark">
                    <span class="font-display text-lg font-bold text-chocolate">Total</span>
                    <span class="font-display text-xl sm:text-2xl font-bold text-chocolate">
                      {{ formatPrice(order.total_amount) }}
                    </span>
                  </div>
                </div>
              </div>
            </section>

            <!-- 2. Adresse de livraison -->
            <section
              v-if="order.delivery_type === 'delivery'"
              class="overflow-hidden rounded-2xl border border-caramel/40 bg-white"
            >
              <div class="flex items-start gap-3 border-b border-cream-dark bg-caramel/10 px-4 py-4 sm:px-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                  📍
                </span>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">
                    Livraison
                  </p>
                  <h2 class="mt-1 text-lg font-bold text-chocolate">Adresse de livraison</h2>
                </div>
              </div>

              <div class="p-4 sm:p-5">
                <div class="rounded-xl border-l-4 border-caramel bg-cream p-4">
                  <p class="text-sm font-semibold text-chocolate">
                    {{ order.delivery_address }}
                  </p>
                  <p class="text-sm text-chocolate-soft">
                    {{ order.delivery_postal_code }} {{ order.delivery_city }}
                  </p>
                  <p v-if="order.delivery_notes" class="mt-3 whitespace-pre-line text-sm leading-6 text-muted">
                    <span class="font-semibold text-chocolate">Précisions :</span>
                    {{ order.delivery_notes }}
                  </p>
                </div>
              </div>
            </section>

            <!-- 3. Retrait boutique -->
            <section
              v-else
              class="overflow-hidden rounded-2xl border border-cream-dark bg-white"
            >
              <div class="flex items-start gap-3 border-b border-cream-dark bg-cream/70 px-4 py-4 sm:px-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                  🏪
                </span>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">
                    Retrait
                  </p>
                  <h2 class="mt-1 text-lg font-bold text-chocolate">Click & Collect</h2>
                  <p class="mt-1 text-sm text-muted">
                    Votre commande sera à retirer directement en boutique.
                  </p>
                </div>
              </div>
            </section>

          </div>
        </Transition>
      </article>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const orders = ref([]);
const loading = ref(true);
const openOrders = reactive({}); // { [orderId]: boolean }

// ✅ Classes Tailwind par statut (même logique que MyCustomOrdersPage)
function statusClasses(status) {
  return {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-caramel/15 text-caramel-dark',
    preparing: 'bg-caramel/15 text-caramel-dark',
    ready: 'bg-caramel/15 text-caramel-dark',
    delivering: 'bg-blue-100 text-blue-800',
    delivered: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-rose-100 text-rose-800',
  }[status] || 'bg-cream text-chocolate-soft';
}

function formatPrice(p) {
  const amount = Number(p ?? 0);
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(Number.isFinite(amount) ? amount : 0);
}

function formatDate(d) {
  if (!d) return 'Date non renseignée';
  const date = new Date(d);
  if (Number.isNaN(date.getTime())) return 'Date non renseignée';
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function statusLabel(s) {
  return {
    pending: 'En attente',
    confirmed: 'Confirmée',
    preparing: 'En préparation',
    ready: 'Prête',
    delivering: 'En livraison',
    delivered: 'Livrée',
    cancelled: 'Annulée',
  }[s] || s;
}

function toggleOrder(orderId) {
  openOrders[orderId] = !openOrders[orderId];
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/my-orders');
    orders.value = data;
    // Initialiser tous les accordéons à fermé (logique préservée)
    data.forEach((o) => (openOrders[o.id] = false));
  } catch {
    orders.value = [];
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Transition accordéon (impossible en Tailwind pur) */
.accordion-enter-active,
.accordion-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
  overflow: hidden;
}
.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
}
.accordion-enter-to,
.accordion-leave-from {
  max-height: 1000px;
  opacity: 1;
}
</style>