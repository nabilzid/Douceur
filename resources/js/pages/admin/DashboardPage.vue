```vue
<template>
  <div class="mx-auto max-w-7xl space-y-8">

    <!-- HEADER -->
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
          Administration
        </p>

        <h1 class="font-display text-3xl font-bold text-chocolate md:text-4xl">
          Tableau de bord
        </h1>

        <p class="mt-2 text-sm text-muted">
          {{ isAdmin ? 'Vue globale de la plateforme' : 'Vue de votre boutique' }}
        </p>
      </div>

      <span
        class="inline-flex items-center gap-2 self-start rounded-full border border-cream-dark/70 bg-white px-4 py-2.5 text-xs font-medium capitalize text-chocolate-soft shadow-soft transition-shadow hover:shadow-card sm:self-auto md:text-sm"
      >
        <svg
          viewBox="0 0 24 24"
          fill="none"
          class="size-4 text-caramel-dark"
          stroke="currentColor"
          stroke-width="1.8"
          aria-hidden="true"
        >
          <rect x="3" y="5" width="18" height="16" rx="3" />
          <path d="M16 3v4M8 3v4M3 10h18" />
        </svg>
        {{ today }}
      </span>
    </header>

    <!-- CHARGEMENT -->
    <div
      v-if="loading"
      class="flex min-h-64 flex-col items-center justify-center gap-3"
    >
      <div
        class="size-10 animate-spin rounded-full border-4 border-caramel/20 border-t-caramel"
      ></div>
      <p class="text-sm text-muted">Chargement des statistiques…</p>
    </div>

    <template v-else>
      <!-- STATISTIQUES -->
      <section class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 lg:grid-cols-4">

        <!-- ADMIN -->
        <template v-if="isAdmin">
          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                👥
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Utilisateurs
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ adminStats.total_users ?? 0 }}
                </span>

                <span class="mt-1 text-xs leading-relaxed text-muted">
                  {{ adminStats.total_customers ?? 0 }} clients
                  <span class="mx-0.5 text-cream-dark">·</span>
                  {{ adminStats.total_merchants ?? 0 }} commerçants
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                📦
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Commandes totales
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ adminStats.total_orders ?? 0 }}
                </span>

                <span class="mt-1 flex items-center gap-1.5 text-xs text-muted">
                  <span class="size-1.5 rounded-full bg-amber-500"></span>
                  {{ adminStats.pending_orders ?? 0 }} en attente
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                💰
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Chiffre d'affaires
                </span>

                <span class="mt-1 break-words font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ formatPrice(adminStats.total_revenue) }}
                </span>

                <span class="mt-1 text-xs leading-relaxed text-muted">
                  Commandes payées uniquement
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                ⏳
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  En attente
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ adminStats.pending_orders ?? 0 }}
                </span>

                <span class="mt-1 text-xs text-muted">À traiter</span>
              </div>
            </div>
          </article>
        </template>

        <!-- COMMERÇANT -->
        <template v-else>
          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                🍰
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Produits
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.products_count ?? 0 }}
                </span>

                <span class="mt-1 text-xs leading-relaxed text-muted">
                  Actifs dans votre catalogue
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border-l-4 border-raspberry bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-raspberry/10 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                ⚠️
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Stock faible
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.low_stock_count ?? 0 }}
                </span>

                <span class="mt-1 text-xs leading-relaxed text-muted">
                  Produits à réapprovisionner
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                📅
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  Aujourd'hui
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.orders_today ?? 0 }}
                </span>

                <span class="mt-1 text-xs leading-relaxed text-muted">
                  {{ merchantStats.pending_orders ?? 0 }} en attente
                </span>
              </div>
            </div>
          </article>

          <article
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div
                class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14"
              >
                ⏳
              </div>

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">
                  En attente
                </span>

                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.pending_orders ?? 0 }}
                </span>

                <span class="mt-1 text-xs text-muted">À traiter</span>
              </div>
            </div>
          </article>
        </template>
      </section>

      <!-- RACCOURCIS -->
      <section>
        <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
              Accès rapide
            </p>

            <h2 class="font-display text-xl font-bold text-chocolate md:text-2xl">
              Raccourcis
            </h2>

            <p class="mt-1 text-sm text-muted">
              Retrouvez rapidement vos outils de gestion.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <RouterLink
            to="/admin/commandes"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">
              📋
            </span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Voir les commandes
            </span>
            <svg
              viewBox="0 0 24 24"
              fill="none"
              class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            to="/admin/produits"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">
              🍰
            </span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Gérer les produits
            </span>
            <svg
              viewBox="0 0 24 24"
              fill="none"
              class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            to="/admin/sur-mesure"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">
              🎂
            </span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Demandes sur mesure
            </span>
            <svg
              viewBox="0 0 24 24"
              fill="none"
              class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            v-if="isAdmin"
            to="/admin/parametres"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 ease-out hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">
              ⚙️
            </span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Paramètres
            </span>
            <svg
              viewBox="0 0 24 24"
              fill="none"
              class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const isAdmin = computed(() => auth.user?.role === 'admin')

const adminStats = ref({})
const merchantStats = ref({})
const loading = ref(true)

let pollInterval = null

const today = new Date().toLocaleDateString('fr-FR', {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
})

function formatPrice(p) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
  }).format(p ?? 0)
}

async function load() {
  try {
    if (isAdmin.value) {
      const { data } = await axios.get('/api/admin/dashboard')
      adminStats.value = data
    } else {
      const { data } = await axios.get('/api/merchant/dashboard')
      merchantStats.value = data
    }
  } catch {
    // Garder les données existantes en cas d'erreur.
  } finally {
    loading.value = false
  }
}

function handleNotificationClick(event) {
  if (event.detail?.url === '/admin/sur-mesure') {
    load()
  }
}

onMounted(() => {
  load()
  pollInterval = setInterval(load, 15 * 60 * 1000)
  window.addEventListener('notification-clicked', handleNotificationClick)
})

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval)
  window.removeEventListener('notification-clicked', handleNotificationClick)
})
</script>
```