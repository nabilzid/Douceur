<template>
  <div class="min-h-screen bg-cream px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">

      <!-- ═══ HEADER ═══ -->
      <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
            Commandes
          </h1>
          <p class="mt-2 text-sm text-muted sm:text-base">
            Suivez les commandes, changez les statuts et gérez les livraisons.
          </p>
        </div>

        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-cream-dark bg-white px-4 py-2.5 text-sm font-semibold text-chocolate shadow-sm transition hover:bg-cream-dark disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="loading || refreshing"
          @click="loadOrders()"
        >
          <svg
            class="h-4 w-4"
            :class="{ 'animate-spin': loading || refreshing }"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
          >
            <path d="M20 7v5h-5" />
            <path d="M4 17v-5h5" />
            <path d="M5.6 9A7 7 0 0 1 17 5l3 2" />
            <path d="M18.4 15A7 7 0 0 1 7 19l-3-2" />
          </svg>
          Actualiser
        </button>
      </header>

      <!-- ═══ ERREUR ═══ -->
      <div
        v-if="loadError"
        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        role="alert"
      >
        {{ loadError }}
        <button type="button" class="ml-2 font-semibold underline" @click="loadOrders()">
          Réessayer
        </button>
      </div>

      <!-- ═══ STATS ═══ -->
      <section class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <button
          v-for="stat in stats"
          :key="stat.key"
          type="button"
          class="rounded-2xl border p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
          :class="activeStat === stat.key
            ? 'border-caramel bg-white ring-2 ring-caramel/20'
            : 'border-cream-dark bg-white'"
          @click="selectStat(stat.key)"
        >
          <span class="block text-sm text-muted">{{ stat.title }}</span>
          <span class="mt-2 block text-2xl font-bold text-chocolate">
            {{ stat.value }}
          </span>
          <span class="mt-1 block text-xs text-muted">{{ stat.subtitle }}</span>
        </button>
      </section>

      <!-- ═══ FILTRES ═══ -->
      <section class="rounded-2xl border border-cream-dark bg-white p-4 shadow-sm sm:p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.5fr)_minmax(180px,1fr)_minmax(220px,1fr)_auto]">
          <label class="block">
            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted">
              Rechercher
            </span>
            <div class="relative">
              <svg
                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
              >
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-4-4" />
              </svg>
              <input
                v-model="search"
                type="search"
                placeholder="Référence, client, e-mail..."
                class="w-full rounded-xl border border-cream-dark bg-cream/40 py-2.5 pl-9 pr-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:ring-2 focus:ring-caramel/20"
              />
            </div>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted">
              Statut
            </span>
            <select
              v-model="statusFilter"
              class="w-full rounded-xl border border-cream-dark bg-white px-3 py-2.5 text-sm text-chocolate outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20"
            >
              <option value="all">Tous les statuts</option>
              <option value="pending">En attente</option>
              <option value="confirmed">Confirmée</option>
              <option value="preparing">En préparation</option>
              <option value="ready">Prête</option>
              <option value="delivering">En livraison</option>
              <option value="delivered">Livrée</option>
              <option value="cancelled">Annulée</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted">
              Trier par
            </span>
            <select
              v-model="sortMode"
              class="w-full rounded-xl border border-cream-dark bg-white px-3 py-2.5 text-sm text-chocolate outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20"
            >
              <option value="priority">Priorité de traitement</option>
              <option value="recent">Commandes les plus récentes</option>
              <option value="oldest">Commandes les plus anciennes</option>
              <option value="amount_desc">Montant décroissant</option>
            </select>
          </label>

          <div class="flex items-end">
            <button
              type="button"
              class="w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-caramel-dark transition hover:bg-cream sm:w-auto"
              @click="resetFilters"
            >
              Réinitialiser
            </button>
          </div>
        </div>
      </section>

      <!-- ═══ TABLEAU ═══ -->
      <section class="overflow-hidden rounded-2xl border border-cream-dark bg-white shadow-card">
        <div class="flex flex-col gap-1 border-b border-cream-dark px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
          <div>
            <h2 class="font-semibold text-chocolate">Toutes les commandes</h2>
            <p class="mt-1 text-sm text-muted">
              {{ filteredOrders.length }} commande{{ filteredOrders.length > 1 ? 's' : '' }}
              affichée{{ filteredOrders.length > 1 ? 's' : '' }}
              sur {{ orders.length }}
            </p>
          </div>
          <p class="text-xs text-muted">
            Les commandes en attente sont prioritaires.
          </p>
        </div>

        <div v-if="loading && !orders.length" class="space-y-3 p-5">
          <div v-for="n in 5" :key="n" class="h-16 animate-pulse rounded-xl bg-cream" />
        </div>

        <div v-else-if="!filteredOrders.length" class="px-6 py-16 text-center">
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream text-caramel-dark">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-4-4" />
            </svg>
          </div>
          <h3 class="mt-4 font-semibold text-chocolate">Aucune commande trouvée</h3>
          <p class="mt-1 text-sm text-muted">
            Modifie les filtres ou lance une nouvelle recherche.
          </p>
          <button
            type="button"
            class="mt-4 text-sm font-semibold text-caramel-dark underline"
            @click="resetFilters"
          >
            Réinitialiser les filtres
          </button>
        </div>

        <template v-else>
          <!-- En-tête tableau desktop -->
          <div class="hidden grid-cols-[minmax(100px,0.8fr)_minmax(160px,1.25fr)_minmax(130px,1fr)_minmax(130px,0.9fr)_minmax(120px,0.8fr)_minmax(150px,1fr)_32px] gap-4 border-b border-cream-dark bg-cream/50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted xl:grid">
            <span>Référence</span>
            <span>Client</span>
            <span>Date</span>
            <span>Montant</span>
            <span>Récupération</span>
            <span>Statut</span>
            <span aria-hidden="true"></span>
          </div>

          <!-- Lignes -->
          <div class="divide-y divide-cream-dark/80">
            <button
              v-for="order in filteredOrders"
              :key="order.id"
              type="button"
              class="group relative block w-full cursor-pointer px-4 py-5 text-left transition hover:bg-cream/60 focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-caramel sm:px-5 xl:grid xl:grid-cols-[minmax(100px,0.8fr)_minmax(160px,1.25fr)_minmax(130px,1fr)_minmax(130px,0.9fr)_minmax(120px,0.8fr)_minmax(150px,1fr)_32px] xl:items-center xl:gap-4"
              :aria-label="`Ouvrir la commande ${order.reference || order.id}`"
              @click="openPanel(order)"
            >
              <!-- Mobile -->
              <div class="flex items-start justify-between gap-3 xl:hidden">
                <div class="min-w-0">
                  <p class="font-semibold text-chocolate">
                    {{ order.reference || `CMD-${order.id}` }}
                  </p>
                  <p class="mt-1 truncate text-sm text-chocolate">
                    {{ order.customer_name || 'Client non renseigné' }}
                  </p>
                  <p class="mt-1 text-xs text-muted">
                    {{ formatDate(order.created_at) }}
                  </p>
                </div>
                <svg class="mt-1 h-4 w-4 shrink-0 text-muted transition group-hover:translate-x-0.5 group-hover:text-caramel-dark"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-2 xl:hidden">
                <span class="text-sm font-semibold text-chocolate">
                  {{ formatPrice(order.total_amount) }}
                </span>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(order.status)">
                  {{ statusLabel(order.status) }}
                </span>
                <span class="text-xs text-muted">
                  {{ order.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Retrait' }}
                </span>
              </div>

              <!-- Desktop -->
              <div class="hidden min-w-0 xl:block">
                <span class="font-semibold text-chocolate">
                  {{ order.reference || `CMD-${order.id}` }}
                </span>
                <span
                  v-if="order.status === 'pending'"
                  class="mt-1 block text-xs font-medium text-caramel-dark"
                >
                  Prioritaire
                </span>
              </div>

              <div class="hidden min-w-0 xl:block">
                <p class="truncate font-medium text-chocolate">
                  {{ order.customer_name || 'Client non renseigné' }}
                </p>
                <p class="mt-1 truncate text-xs text-muted">
                  {{ order.customer_email || 'E-mail non renseigné' }}
                </p>
              </div>

              <div class="hidden min-w-0 xl:block">
                <p class="text-sm text-chocolate-soft">
                  {{ formatDate(order.created_at) }}
                </p>
              </div>

              <div class="hidden xl:block">
                <p class="font-semibold text-chocolate">
                  {{ formatPrice(order.total_amount) }}
                </p>
              </div>

              <div class="hidden xl:block">
                <span class="text-sm text-chocolate-soft">
                  {{ order.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Retrait' }}
                </span>
              </div>

              <div class="hidden xl:block">
                <span
                  class="inline-flex max-w-full rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusClasses(order.status)"
                >
                  {{ statusLabel(order.status) }}
                </span>
              </div>

              <div class="hidden justify-end xl:flex">
                <svg class="h-4 w-4 text-muted transition group-hover:translate-x-0.5 group-hover:text-caramel-dark"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </div>
            </button>
          </div>
        </template>
      </section>
    </div>

    <!-- ═══ PANNEAU LATÉRAL ═══ -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="panelOpen"
          class="fixed inset-0 z-[90] bg-chocolate/40 backdrop-blur-[2px]"
          aria-hidden="true"
          @click="closePanel"
        />
      </Transition>

      <Transition name="drawer">
        <aside
          v-if="panelOpen && activeOrder"
          class="fixed inset-y-0 right-0 z-[100] flex w-full max-w-2xl flex-col bg-cream shadow-2xl"
          role="dialog"
          aria-modal="true"
          :aria-label="`Détail de la commande ${activeOrder.reference || activeOrder.id}`"
        >
          <!-- En-tête panneau -->
          <div class="flex items-start justify-between gap-4 border-b border-cream-dark bg-white px-5 py-5 sm:px-7">
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-wide text-caramel-dark">
                Commande
              </p>
              <h2 class="mt-1 truncate font-display text-2xl font-bold text-chocolate">
                {{ activeOrder.reference || `CMD-${activeOrder.id}` }}
              </h2>
              <p class="mt-1 text-sm text-muted">
                Passée le {{ formatDate(activeOrder.created_at) }}
              </p>
            </div>
            <button
              type="button"
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-muted transition hover:bg-cream hover:text-chocolate focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel"
              aria-label="Fermer le panneau"
              @click="closePanel"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m18 6-12 12M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Contenu panneau -->
          <div class="flex-1 space-y-6 overflow-y-auto px-5 py-6 sm:px-7">
            <div v-if="actionMessage" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
              {{ actionMessage }}
            </div>
            <div v-if="actionError" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
              {{ actionError }}
            </div>

            <!-- Statut -->
            <section class="rounded-2xl border border-cream-dark bg-white p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Statut</p>
                  <span class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-semibold" :class="statusClasses(activeOrder.status)">
                    {{ statusLabel(activeOrder.status) }}
                  </span>
                </div>
                <div class="text-right">
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Total</p>
                  <p class="mt-2 font-display text-xl font-bold text-chocolate">
                    {{ formatPrice(activeOrder.total_amount) }}
                  </p>
                </div>
              </div>
            </section>

            <!-- Client + mode -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Informations client</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <p class="font-semibold text-chocolate">
                  {{ activeOrder.customer_name || 'Client non renseigné' }}
                </p>
                <a
                  v-if="activeOrder.customer_email"
                  class="mt-2 block break-all text-sm text-caramel-dark underline underline-offset-2"
                  :href="`mailto:${activeOrder.customer_email}`"
                >
                  {{ activeOrder.customer_email }}
                </a>
                <p v-if="activeOrder.customer_phone" class="mt-2 text-sm text-muted">
                  {{ activeOrder.customer_phone }}
                </p>

                <!-- Mode de récupération -->
                <div class="mt-3 pt-3 border-t border-cream-dark">
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted mb-1.5">
                    Mode de récupération
                  </p>
                  <span class="inline-flex items-center gap-1.5 rounded-full bg-cream px-3 py-1.5 text-sm font-medium text-chocolate">
                    {{ activeOrder.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
                  </span>
                </div>
              </div>
            </section>

            <!-- Articles -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Articles</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <ul class="space-y-2">
                  <li v-for="item in activeOrder.items" :key="item.id" class="flex justify-between gap-3 text-sm">
                    <span class="truncate">
                      {{ item.product_name }}
                      <span class="text-muted">× {{ item.quantity }}</span>
                    </span>
                    <span class="font-semibold text-chocolate shrink-0">{{ formatPrice(item.line_total) }}</span>
                  </li>
                </ul>
                <div class="mt-4 pt-3 border-t border-cream-dark space-y-1.5">
                  <div class="flex justify-between text-sm text-muted">
                    <span>Sous-total</span>
                    <span class="text-chocolate-soft font-medium">{{ formatPrice(activeOrder.subtotal) }}</span>
                  </div>
                  <div class="flex justify-between text-sm text-muted">
                    <span>{{ activeOrder.delivery_type === 'delivery' ? 'Livraison' : 'Retrait' }}</span>
                    <span class="font-medium" :class="activeOrder.delivery_fee > 0 ? 'text-chocolate-soft' : 'text-emerald-600'">
                      {{ activeOrder.delivery_fee > 0 ? formatPrice(activeOrder.delivery_fee) : 'Offerte' }}
                    </span>
                  </div>
                  <div class="flex justify-between items-baseline pt-2 border-t-2 border-cream-dark">
                    <span class="font-display text-base font-bold text-chocolate">Total</span>
                    <span class="font-display text-xl font-bold text-chocolate">{{ formatPrice(activeOrder.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </section>

            <!-- Adresse -->
            <section v-if="activeOrder.delivery_type === 'delivery'">
              <h3 class="mb-3 font-semibold text-chocolate">Adresse de livraison</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4 border-l-4 border-caramel">
                <p class="text-sm text-chocolate-soft">{{ activeOrder.delivery_address }}</p>
                <p class="text-sm text-chocolate-soft">{{ activeOrder.delivery_postal_code }} {{ activeOrder.delivery_city }}</p>
              </div>
            </section>

            <!-- Actions -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Changer le statut</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <select
                  :value="activeOrder.status"
                  @change="updateStatus(activeOrder, $event.target.value)"
                  :disabled="savingAction"
                  class="w-full rounded-xl border border-cream-dark bg-cream px-3 py-3 text-sm font-medium text-chocolate outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20 disabled:opacity-60"
                >
                  <option value="pending">En attente</option>
                  <option value="confirmed">Confirmée</option>
                  <option value="preparing">En préparation</option>
                  <option value="ready">Prête</option>
                  <option value="delivering">En livraison</option>
                  <option value="delivered">Livrée</option>
                  <option value="cancelled">Annulée</option>
                </select>
                <p class="mt-2 text-xs text-muted">
                  Le changement de statut est immédiat et notifie le client.
                </p>
              </div>
            </section>
          </div>
        </aside>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import axios from 'axios'

const orders = ref([])
const loading = ref(true)
const refreshing = ref(false)
const loadError = ref('')

const search = ref('')
const statusFilter = ref('all')
const sortMode = ref('priority')
const activeStat = ref('all')

const panelOpen = ref(false)
const activeOrder = ref(null)

const savingAction = ref(false)
const actionMessage = ref('')
const actionError = ref('')

let refreshInterval = null

const stats = computed(() => [
  {
    key: 'pending',
    title: 'En attente',
    value: orders.value.filter(o => o.status === 'pending').length,
    subtitle: 'À confirmer',
  },
  {
    key: 'confirmed',
    title: 'Confirmées',
    value: orders.value.filter(o => o.status === 'confirmed').length,
    subtitle: 'À préparer',
  },
  {
    key: 'preparing',
    title: 'En préparation',
    value: orders.value.filter(o => ['preparing', 'ready'].includes(o.status)).length,
    subtitle: 'En cours',
  },
  {
    key: 'delivered',
    title: 'Livrées',
    value: orders.value.filter(o => o.status === 'delivered').length,
    subtitle: 'Terminées',
  },
  {
    key: 'all',
    title: 'Toutes',
    value: orders.value.length,
    subtitle: 'Toutes les commandes',
  },
])

const filteredOrders = computed(() => {
  let result = [...orders.value]

  if (activeStat.value !== 'all') {
    result = result.filter(order => {
      if (activeStat.value === 'preparing') {
        return ['preparing', 'ready'].includes(order.status)
      }
      return order.status === activeStat.value
    })
  }

  if (statusFilter.value !== 'all') {
    result = result.filter(order => order.status === statusFilter.value)
  }

  const query = search.value.trim().toLowerCase()
  if (query) {
    result = result.filter(order => {
      const searchable = [
        order.reference,
        order.customer_name,
        order.customer_email,
      ].filter(Boolean).join(' ').toLowerCase()
      return searchable.includes(query)
    })
  }

  const dateValue = value => {
    if (!value) return Number.MAX_SAFE_INTEGER
    const time = new Date(value).getTime()
    return Number.isNaN(time) ? Number.MAX_SAFE_INTEGER : time
  }

  switch (sortMode.value) {
    case 'recent':
      result.sort((a, b) => dateValue(b.created_at) - dateValue(a.created_at))
      break
    case 'oldest':
      result.sort((a, b) => dateValue(a.created_at) - dateValue(b.created_at))
      break
    case 'amount_desc':
      result.sort((a, b) => (b.total_amount ?? 0) - (a.total_amount ?? 0))
      break
    case 'priority':
    default:
      result.sort((a, b) => {
        const aPriority = a.status === 'pending' ? 0 : 1
        const bPriority = b.status === 'pending' ? 0 : 1
        if (aPriority !== bPriority) return aPriority - bPriority
        return dateValue(b.created_at) - dateValue(a.created_at)
      })
  }

  return result
})

async function loadOrders({ silent = false } = {}) {
  if (!silent) {
    if (orders.value.length) refreshing.value = true
    else loading.value = true
  }
  loadError.value = ''

  try {
    const { data } = await axios.get('/api/merchant/orders')
    orders.value = Array.isArray(data) ? data : []

    if (activeOrder.value) {
      const refreshed = orders.value.find(o => o.id === activeOrder.value.id)
      if (refreshed) activeOrder.value = refreshed
    }
  } catch (error) {
    loadError.value = error.response?.data?.message || 'Impossible de charger les commandes.'
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

function openPanel(order) {
  activeOrder.value = order
  panelOpen.value = true
  actionMessage.value = ''
  actionError.value = ''
}

function closePanel() {
  panelOpen.value = false
}

function selectStat(key) {
  activeStat.value = key
  statusFilter.value = 'all'
}

function resetFilters() {
  search.value = ''
  statusFilter.value = 'all'
  sortMode.value = 'priority'
  activeStat.value = 'all'
}

function formatDate(value) {
  if (!value) return 'Date inconnue'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return 'Date inconnue'
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  }).format(date)
}

function formatPrice(value) {
  const amount = Number(value ?? 0)
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount)
}

function statusLabel(status) {
  const labels = {
    pending: 'En attente',
    confirmed: 'Confirmée',
    preparing: 'En préparation',
    ready: 'Prête',
    delivering: 'En livraison',
    delivered: 'Livrée',
    cancelled: 'Annulée',
  }
  return labels[status] || status
}

function statusClasses(status) {
  const classes = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-caramel/15 text-caramel-dark',
    preparing: 'bg-caramel/15 text-caramel-dark',
    ready: 'bg-sky-100 text-sky-800',
    delivering: 'bg-blue-100 text-blue-800',
    delivered: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-rose-100 text-rose-800',
  }
  return classes[status] || 'bg-cream-dark text-muted'
}

async function updateStatus(order, status) {
  if (savingAction.value || order.status === status) return

  savingAction.value = true
  actionError.value = ''
  actionMessage.value = ''

  try {
    const { data } = await axios.put(`/api/merchant/orders/${order.id}/status`, { status })
    Object.assign(order, data)
    actionMessage.value = `Statut mis à jour : ${statusLabel(status)}.`
  } catch (error) {
    actionError.value = error.response?.data?.message || 'Impossible de mettre à jour le statut.'
  } finally {
    savingAction.value = false
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape' && panelOpen.value) closePanel()
}

watch(panelOpen, isOpen => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
})

onMounted(() => {
  loadOrders()
  refreshInterval = setInterval(() => {
    if (!savingAction.value) loadOrders({ silent: true })
  }, 30_000)
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 180ms ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.drawer-enter-active, .drawer-leave-active { transition: transform 240ms ease; }
.drawer-enter-from, .drawer-leave-to { transform: translateX(100%); }

@media (prefers-reduced-motion: reduce) {
  .fade-enter-active, .fade-leave-active,
  .drawer-enter-active, .drawer-leave-active { transition: none; }
}
</style>