<template>
  <div class="mx-auto max-w-7xl space-y-8">

    <!-- ═══ HEADER ═══ -->
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

      <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
        <!-- Date -->
        <span class="inline-flex items-center gap-2 rounded-full border border-cream-dark/70 bg-white px-4 py-2.5 text-xs font-medium capitalize text-chocolate-soft shadow-soft transition-shadow hover:shadow-card md:text-sm">
          <svg viewBox="0 0 24 24" fill="none" class="size-4 text-caramel-dark" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3" y="5" width="18" height="16" rx="3" />
            <path d="M16 3v4M8 3v4M3 10h18" />
          </svg>
          {{ today }}
        </span>

        <!-- Bouton Actualiser -->
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-full border border-cream-dark/70 bg-white px-4 py-2.5 text-xs font-semibold text-chocolate shadow-soft transition-all hover:-translate-y-0.5 hover:bg-cream-dark hover:shadow-card disabled:cursor-not-allowed disabled:opacity-60 md:text-sm"
          :disabled="loading || refreshing"
          @click="load()"
        >
          <svg
            class="size-4"
            :class="{ 'animate-spin': refreshing }"
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
      </div>
    </header>

    <!-- ═══ CHARGEMENT ═══ -->
    <div v-if="loading" class="flex min-h-64 flex-col items-center justify-center gap-3">
      <div class="size-10 animate-spin rounded-full border-4 border-caramel/20 border-t-caramel"></div>
      <p class="text-sm text-muted">Chargement des statistiques…</p>
    </div>

    <template v-else>

      <!-- ═══════════════════════════════════════════════════════
           SECTION 1 — VUE D'ENSEMBLE
           ═══════════════════════════════════════════════════════ -->
      <section>
        <div class="mb-4 flex items-end justify-between gap-3">
          <div>
            <h2 class="font-display text-xl font-bold text-chocolate">Vue d'ensemble</h2>
            <p class="mt-1 text-sm text-muted">Le résumé de votre activité</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-5 lg:grid-cols-4">

          <!-- ADMIN : Utilisateurs -->
          <article
            v-if="isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                👥
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Utilisateurs</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ adminStats.total_users ?? 0 }}
                </span>
                <div class="mt-2 space-y-0.5 text-xs text-muted">
                  <div class="flex justify-between gap-2">
                    <span>Clients</span>
                    <strong class="text-chocolate">{{ adminStats.total_customers ?? 0 }}</strong>
                  </div>
                  <div class="flex justify-between gap-2">
                    <span>Commerçants</span>
                    <strong class="text-chocolate">{{ adminStats.total_merchants ?? 0 }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <!-- ADMIN : Commandes -->
          <article
            v-if="isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                📦
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Commandes</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ adminStats.total_orders ?? 0 }}
                </span>
                <div class="mt-2 space-y-0.5 text-xs text-muted">
                  <div class="flex justify-between gap-2">
                    <span>En attente</span>
                    <strong class="text-amber-700">{{ adminStats.pending_orders ?? 0 }}</strong>
                  </div>
                  <div class="flex justify-between gap-2">
                    <span>Payées</span>
                    <strong class="text-emerald-700">{{ stats.orders_count ?? 0 }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <!-- ADMIN : CA total -->
          <article
            v-if="isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                💰
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">CA total</span>
                <span class="mt-1 break-words font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ formatPrice(adminStats.total_revenue) }}
                </span>
                <span class="mt-2 text-xs text-muted">Commandes + sur-mesure</span>
              </div>
            </div>
          </article>

          <!-- ADMIN : À recouvrer -->
          <article
            v-if="isAdmin"
            class="group rounded-2xl border-l-4 border-amber-500 bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                💳
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">À recouvrer</span>
                <span class="mt-1 break-words font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ formatPrice(adminStats.pending_payments) }}
                </span>
                <span class="mt-2 text-xs text-muted">
                  {{ adminStats.pending_payments_count ?? 0 }} commande(s) avec solde dû
                </span>
              </div>
            </div>
          </article>

          <!-- MERCHANT : Produits -->
          <article
            v-if="!isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                🍰
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Produits</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.products_count ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">Actifs dans votre catalogue</span>
              </div>
            </div>
          </article>

          <!-- MERCHANT : Stock faible -->
          <article
            v-if="!isAdmin"
            class="group rounded-2xl border-l-4 border-raspberry bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-raspberry/10 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                ⚠️
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Stock faible</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.low_stock_count ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">Produits à réapprovisionner</span>
              </div>
            </div>
          </article>

          <!-- MERCHANT : Aujourd'hui -->
          <article
            v-if="!isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-caramel/15 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                📅
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Aujourd'hui</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.orders_today ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">{{ merchantStats.pending_orders ?? 0 }} en attente</span>
              </div>
            </div>
          </article>

          <!-- MERCHANT : En attente -->
          <article
            v-if="!isAdmin"
            class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6"
          >
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                ⏳
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">En attente</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ merchantStats.pending_orders ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">À traiter</span>
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════
           SECTION 2 — DEMANDES SUR MESURE
           ═══════════════════════════════════════════════════════ -->
      <section>
        <div class="mb-4 flex items-end justify-between gap-3">
          <div>
            <h2 class="font-display text-xl font-bold text-chocolate">Demandes sur mesure</h2>
            <p class="mt-1 text-sm text-muted">Le pipeline de vos devis</p>
          </div>
          <RouterLink
            to="/admin/sur-mesure"
            class="text-sm font-semibold text-caramel-dark hover:underline"
          >
            Voir tout →
          </RouterLink>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-5">

          <article class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6">
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                📝
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">À traiter</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ stats.custom_pending ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">Devis à préparer</span>
              </div>
            </div>
          </article>

          <article class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6">
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-sky-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                ✉️
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Devis envoyés</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ stats.custom_quoted ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">En attente de réponse</span>
              </div>
            </div>
          </article>

          <article class="group rounded-2xl border border-transparent bg-white p-5 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:shadow-card md:p-6">
            <div class="flex items-start gap-4">
              <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-2xl transition-transform duration-300 group-hover:scale-110 md:size-14">
                ✅
              </div>
              <div class="flex min-w-0 flex-1 flex-col">
                <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Acceptées</span>
                <span class="mt-1 font-display text-2xl font-bold leading-tight text-chocolate md:text-3xl">
                  {{ stats.custom_accepted ?? 0 }}
                </span>
                <span class="mt-2 text-xs text-muted">En préparation</span>
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════
           SECTION 3 — TRÉSORERIE
           ═══════════════════════════════════════════════════════ -->
      <section v-if="isAdmin">
        <div class="mb-4 flex items-end justify-between gap-3">
          <div>
            <h2 class="font-display text-xl font-bold text-chocolate">Trésorerie</h2>
            <p class="mt-1 text-sm text-muted">Vue détaillée de votre chiffre d'affaires</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:gap-5">

          <!-- CA total -->
          <article class="rounded-2xl border border-cream-dark bg-white p-5 shadow-soft md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Chiffre d'affaires total</p>
                <p class="mt-1 font-display text-4xl font-bold text-chocolate">
                  {{ formatPrice(stats.total_revenue) }}
                </p>
                <p class="mt-1 text-xs text-muted">Commandes standard + sur-mesure</p>
              </div>
              <div class="grid size-16 shrink-0 place-items-center rounded-full bg-caramel text-3xl text-white">
                💰
              </div>
            </div>

            <!-- Détail -->
            <div class="mt-5 space-y-3 border-t border-cream-dark pt-4">
              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm">
                  <span class="grid size-8 place-items-center rounded-lg bg-emerald-100 text-base">📦</span>
                  <div>
                    <p class="font-medium text-chocolate">Commandes standard</p>
                    <p class="text-xs text-muted">{{ stats.orders_count ?? 0 }} payée(s)</p>
                  </div>
                </div>
                <strong class="text-chocolate">{{ formatPrice(stats.orders_revenue) }}</strong>
              </div>

              <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm">
                  <span class="grid size-8 place-items-center rounded-lg bg-caramel/15 text-base">🎂</span>
                  <div>
                    <p class="font-medium text-chocolate">Sur-mesure</p>
                    <p class="text-xs text-muted">{{ stats.custom_accepted ?? 0 }} acceptée(s)</p>
                  </div>
                </div>
                <strong class="text-chocolate">{{ formatPrice(stats.custom_revenue) }}</strong>
              </div>
            </div>
          </article>

          <!-- Soldes à recouvrer -->
          <article
            v-if="(stats.pending_payments ?? 0) > 0"
            class="rounded-2xl border-l-4 border-amber-500 bg-white p-5 shadow-soft md:p-6"
          >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
              <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Soldes à recouvrer</p>
                <p class="mt-1 font-display text-3xl font-bold text-chocolate">
                  {{ formatPrice(stats.pending_payments) }}
                </p>
                <p class="mt-1 text-xs text-muted">
                  {{ stats.pending_payments_count ?? 0 }} commande(s) avec acompte — reste à encaisser sur place
                </p>
              </div>
              <div class="grid size-14 shrink-0 place-items-center rounded-xl bg-amber-100 text-3xl">
                💳
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════
           SECTION 4 — RACCOURCIS
           ═══════════════════════════════════════════════════════ -->
      <section>
        <div class="mb-4">
          <h2 class="font-display text-xl font-bold text-chocolate">Raccourcis</h2>
          <p class="mt-1 text-sm text-muted">Accès rapide à vos outils</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <RouterLink
            to="/admin/commandes"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">📋</span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Voir les commandes
            </span>
            <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white" stroke="currentColor" stroke-width="1.8">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            to="/admin/produits"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">🍰</span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Gérer les produits
            </span>
            <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white" stroke="currentColor" stroke-width="1.8">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            to="/admin/sur-mesure"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">🎂</span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Demandes sur mesure
            </span>
            <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white" stroke="currentColor" stroke-width="1.8">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </RouterLink>

          <RouterLink
            v-if="isAdmin"
            to="/admin/parametres"
            class="group flex items-center gap-3 rounded-2xl border border-transparent bg-white p-4 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:border-cream-dark hover:bg-caramel hover:shadow-card sm:p-5"
          >
            <span class="text-2xl transition-transform duration-300 group-hover:scale-110">⚙️</span>
            <span class="flex-1 text-sm font-medium text-chocolate-soft transition-colors group-hover:text-white">
              Paramètres
            </span>
            <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-caramel-dark transition-all group-hover:translate-x-1 group-hover:text-white" stroke="currentColor" stroke-width="1.8">
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
const stats = ref({})
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
      stats.value = {
        custom_pending: data.custom_orders?.pending_quote ?? 0,
        custom_quoted: data.custom_orders?.quoted ?? 0,
        custom_accepted: data.custom_orders?.accepted ?? 0,
        custom_revenue: data.custom_revenue ?? 0,
        orders_revenue: data.orders_revenue ?? 0,
        orders_count: data.total_orders ?? 0,
        total_revenue: data.total_revenue ?? 0,
        pending_payments: data.pending_payments ?? 0,
        pending_payments_count: data.pending_payments_count ?? 0,
      }
    } else {
    const { data } = await axios.get('/api/merchant/dashboard')
    merchantStats.value = data
    stats.value = {
      custom_pending: data.custom_orders_pending ?? 0,
      custom_quoted: data.custom_orders_quoted ?? 0,
      custom_accepted: data.custom_orders_accepted ?? 0,
      // Pas de trésorerie pour le merchant
    }
  }
  } catch {
    // Garder les données existantes
  } finally {
    loading.value = false
  }
}

function handleNotificationClick(event) {
  if (event.detail?.url?.startsWith('/admin/')) {
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