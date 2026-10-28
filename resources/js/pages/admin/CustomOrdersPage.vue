<!-- Liste des sur-mesure admin -->
<template>
  <div class="min-h-screen bg-cream px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">
      <!-- En-tête -->
      <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
            Demandes sur mesure
          </h1>
          <p class="mt-2 text-sm text-muted sm:text-base">
            Gérez les demandes, préparez les devis et suivez les commandes personnalisées.
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

      <!-- Erreur de chargement -->
      <div
        v-if="loadError"
        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        role="alert"
      >
        {{ loadError }}
        <button
          type="button"
          class="ml-2 font-semibold underline"
          @click="loadOrders()"
        >
          Réessayer
        </button>
      </div>

      <!-- Indicateurs -->
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

      <!-- Filtres -->
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
              <option value="pending_quote">À traiter</option>
              <option value="quoted">Devis envoyé</option>
              <option value="accepted">Acceptée</option>
              <option value="deposit_paid">Acompte réglé</option>
              <option value="completed">Terminée</option>
              <option value="rejected">Refusée par le client</option>
              <option value="refused_by_merchant">Refusée par le pâtissier</option>
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
              <option value="event_asc">Événement le plus proche</option>
              <option value="recent">Demandes les plus récentes</option>
              <option value="oldest">Demandes les plus anciennes</option>
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

      <!-- Tableau -->
      <section class="overflow-hidden rounded-2xl border border-cream-dark bg-white shadow-card">
        <div class="flex flex-col gap-1 border-b border-cream-dark px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
          <div>
            <h2 class="font-semibold text-chocolate">Toutes les demandes</h2>
            <p class="mt-1 text-sm text-muted">
              {{ filteredOrders.length }} demande{{ filteredOrders.length > 1 ? 's' : '' }}
              affichée{{ filteredOrders.length > 1 ? 's' : '' }}
              sur {{ orders.length }}
            </p>
          </div>
          <p class="text-xs text-muted">
            Les demandes en attente de devis sont prioritaires.
          </p>
        </div>

        <div v-if="loading && !orders.length" class="space-y-3 p-5">
          <div
            v-for="n in 5"
            :key="n"
            class="h-16 animate-pulse rounded-xl bg-cream"
          />
        </div>

        <div
          v-else-if="!filteredOrders.length"
          class="px-6 py-16 text-center"
        >
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream text-caramel-dark">
            <svg
              class="h-6 w-6"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.7"
              aria-hidden="true"
            >
              <circle cx="11" cy="11" r="7" />
              <path d="m20 20-4-4" />
            </svg>
          </div>
          <h3 class="mt-4 font-semibold text-chocolate">Aucune demande trouvée</h3>
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
          <!-- En-tête du tableau desktop -->
          <div class="hidden grid-cols-[minmax(100px,0.8fr)_minmax(160px,1.25fr)_minmax(130px,1fr)_minmax(130px,0.9fr)_minmax(120px,0.8fr)_minmax(150px,1fr)_32px] gap-4 border-b border-cream-dark bg-cream/50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted xl:grid">
            <button type="button" class="text-left hover:text-chocolate" @click="setSort('reference')">
              Référence
            </button>
            <button type="button" class="text-left hover:text-chocolate" @click="setSort('customer')">
              Client
            </button>
            <button type="button" class="text-left hover:text-chocolate" @click="setSort('event')">
              Événement
            </button>
            <span>Montant</span>
            <span>Devis proposé</span>
            <span>Statut</span>
            <span aria-hidden="true"></span>
          </div>

          <!-- Lignes avec espacement mobile -->
          <div class="divide-y divide-cream-dark/80">
            <button
              v-for="order in filteredOrders"
              :key="order.id"
              type="button"
              class="group relative block w-full cursor-pointer px-4 py-5 text-left transition hover:bg-cream/60 focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-caramel sm:px-5 xl:grid xl:grid-cols-[minmax(100px,0.8fr)_minmax(160px,1.25fr)_minmax(130px,1fr)_minmax(120px,0.9fr)_minmax(120px,0.8fr)_minmax(150px,1fr)_32px] xl:items-center xl:gap-4"
              :aria-label="`Ouvrir la demande ${order.reference || order.id}`"
              @click="openPanel(order)"
            >
              <!-- Présentation mobile -->
              <div class="flex items-start justify-between gap-3 xl:hidden">
                <div class="min-w-0">
                  <p class="font-semibold text-chocolate">
                    {{ order.reference || `CMD-${order.id}` }}
                  </p>
                  <p class="mt-1 truncate text-sm text-chocolate">
                    {{ order.customer_name || 'Client non renseigné' }}
                  </p>
                  <p class="mt-1 text-xs text-muted">
                    {{ eventLabel(order.event_type) }}
                    <span aria-hidden="true"> · </span>
                    {{ formatDate(order.event_date) }}
                  </p>
                </div>
                <svg
                  class="mt-1 h-4 w-4 shrink-0 text-muted transition group-hover:translate-x-0.5 group-hover:text-caramel-dark"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  aria-hidden="true"
                >
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-2 xl:hidden">
                <span class="text-sm font-semibold text-chocolate">
                  {{ formatPrice(order.quoted_price ?? order.estimated_budget) }}
                </span>
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="quoteClasses(order)"
                >
                  {{ quoteLabel(order) }}
                </span>
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="statusClasses(order.status)"
                >
                  {{ statusLabel(order.status) }}
                </span>
              </div>

              <!-- Colonnes desktop -->
              <div class="hidden min-w-0 xl:block">
                <span class="font-semibold text-chocolate">
                  {{ order.reference || `CMD-${order.id}` }}
                </span>
                <span
                  v-if="order.status === 'pending_quote'"
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
                  {{ order.customer_email || order.email || 'E-mail non renseigné' }}
                </p>
              </div>

              <div class="hidden min-w-0 xl:block">
                <p class="truncate text-sm font-medium text-chocolate">
                  {{ eventLabel(order.event_type) }}
                </p>
                <p class="mt-1 text-xs text-muted">
                  {{ formatDate(order.event_date) }}
                </p>
              </div>

              <div class="hidden xl:block">
                <p class="font-semibold text-chocolate">
                  {{ formatPrice(order.quoted_price ?? order.estimated_budget) }}
                </p>
                <p v-if="order.amount_paid > 0" class="mt-1 text-xs text-muted">
                  {{ formatPrice(order.amount_paid) }} payé
                </p>
              </div>

              <div class="hidden xl:block">
                <p v-if="order.quoted_price != null" class="font-semibold text-chocolate">
                  {{ formatPrice(order.quoted_price) }}
                </p>
                <span v-else class="text-xs text-muted italic">À préparer</span>
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
                <svg
                  class="h-4 w-4 text-muted transition group-hover:translate-x-0.5 group-hover:text-caramel-dark"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  aria-hidden="true"
                >
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </div>
            </button>
          </div>
        </template>
      </section>
    </div>

    <!-- Panneau de détail -->
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
          :aria-label="`Détail de la demande ${activeOrder.reference || activeOrder.id}`"
        >
          <!-- En-tête panneau -->
          <div class="flex items-start justify-between gap-4 border-b border-cream-dark bg-white px-5 py-5 sm:px-7">
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-wide text-caramel-dark">
                Demande sur mesure
              </p>
              <h2 class="mt-1 truncate font-display text-2xl font-bold text-chocolate">
                {{ activeOrder.reference || `CMD-${activeOrder.id}` }}
              </h2>
              <p class="mt-1 text-sm text-muted">
                Reçue le {{ formatDate(activeOrder.created_at) }}
              </p>
            </div>
            <button
              type="button"
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-muted transition hover:bg-cream hover:text-chocolate focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-caramel"
              aria-label="Fermer le panneau"
              @click="closePanel"
            >
              <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
              >
                <path d="m18 6-12 12M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Contenu panneau -->
          <div class="flex-1 space-y-6 overflow-y-auto px-5 py-6 sm:px-7">
            <div
              v-if="actionMessage"
              class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
              role="status"
            >
              {{ actionMessage }}
            </div>

            <div
              v-if="actionError"
              class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
              role="alert"
            >
              {{ actionError }}
            </div>

            <!-- Statut -->
            <section class="rounded-2xl border border-cream-dark bg-white p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Statut</p>
                  <span
                    class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-semibold"
                    :class="statusClasses(activeOrder.status)"
                  >
                    {{ statusLabel(activeOrder.status) }}
                  </span>
                </div>
                <div class="text-right">
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Devis</p>
                  <span
                    class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-semibold"
                    :class="quoteClasses(activeOrder)"
                  >
                    {{ quoteLabel(activeOrder) }}
                  </span>
                </div>
              </div>
            </section>

            <!-- Client -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Informations client</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <p class="font-semibold text-chocolate">
                  {{ activeOrder.customer_name || 'Client non renseigné' }}
                </p>
                <a
                  v-if="activeOrder.customer_email || activeOrder.email"
                  class="mt-2 block break-all text-sm text-caramel-dark underline underline-offset-2"
                  :href="`mailto:${activeOrder.customer_email || activeOrder.email}`"
                >
                  {{ activeOrder.customer_email || activeOrder.email }}
                </a>
                <p v-if="activeOrder.customer_phone || activeOrder.phone" class="mt-2 text-sm text-muted">
                  {{ activeOrder.customer_phone || activeOrder.phone }}
                </p>
              </div>
            </section>

            <!-- Événement -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Détails de l'événement</h3>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-cream-dark bg-white p-4">
                  <p class="text-xs text-muted">Type d'événement</p>
                  <p class="mt-1 font-medium text-chocolate">
                    {{ eventLabel(activeOrder.event_type) }}
                  </p>
                </div>
                <div class="rounded-2xl border border-cream-dark bg-white p-4">
                  <p class="text-xs text-muted">Date prévue</p>
                  <p class="mt-1 font-medium text-chocolate">
                    {{ formatDate(activeOrder.event_date) }}
                  </p>
                </div>
                <div class="rounded-2xl border border-cream-dark bg-white p-4">
                  <p class="text-xs text-muted">Nombre de personnes</p>
                  <p class="mt-1 font-medium text-chocolate">
                    {{ activeOrder.guest_count || activeOrder.number_of_guests || 'Non précisé' }}
                  </p>
                </div>
                <div class="rounded-2xl border border-cream-dark bg-white p-4">
                  <p class="text-xs text-muted">Budget indicatif</p>
                  <p class="mt-1 font-medium text-chocolate">
                    {{ formatPrice(activeOrder.estimated_budget) }}
                  </p>
                </div>
              </div>
            </section>

            <!-- Description -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Description du projet</h3>
              <div class="whitespace-pre-line rounded-2xl border border-cream-dark bg-white p-4 text-sm leading-6 text-chocolate">
                {{ activeOrder.description || activeOrder.details || 'Aucune description fournie.' }}
              </div>
            </section>

            <!-- Devis (envoi uniquement, pas de mise à jour) -->
            <section
              v-if="activeOrder.status === 'pending_quote'"
              class="rounded-2xl border border-caramel/40 bg-white p-5"
            >
              <div class="mb-4">
                <h3 class="font-semibold text-chocolate">Préparer le devis</h3>
                <p class="mt-1 text-sm text-muted">
                  Indique le prix proposé et ajoute un message pour le client.
                </p>
              </div>

              <div v-if="quoteForActiveOrder" class="space-y-4">
                <label class="block">
                  <span class="mb-1.5 block text-sm font-medium text-chocolate">
                    Montant du devis (€)
                  </span>
                  <div class="relative">
                    <input
                      v-model.number="quoteForActiveOrder.price"
                      type="number"
                      min="0.01"
                      step="0.01"
                      placeholder="Ex. : 250"
                      class="w-full rounded-xl border border-cream-dark bg-white px-3 py-3 pr-10 text-sm text-chocolate outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20 disabled:bg-cream/60"
                      :disabled="savingQuote"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted">€</span>
                  </div>
                </label>

                <label class="block">
                  <span class="mb-1.5 block text-sm font-medium text-chocolate">
                    Message accompagnant le devis
                  </span>
                  <textarea
                    v-model="quoteForActiveOrder.response"
                    rows="4"
                    placeholder="Précisions sur la proposition, les parfums, la livraison..."
                    class="w-full resize-y rounded-xl border border-cream-dark bg-white px-3 py-3 text-sm text-chocolate outline-none placeholder:text-muted/70 focus:border-caramel focus:ring-2 focus:ring-caramel/20 disabled:bg-cream/60"
                    :disabled="savingQuote"
                  />
                </label>

                <button
                  type="button"
                  class="flex w-full items-center justify-center gap-2 rounded-xl bg-chocolate px-4 py-3 font-semibold text-white transition hover:bg-chocolate/90 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="savingQuote || !isQuoteValid"
                  @click="sendQuote"
                >
                  <svg
                    v-if="savingQuote"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <circle cx="12" cy="12" r="9" opacity=".25" />
                    <path d="M21 12a9 9 0 0 0-9-9" />
                  </svg>
                  {{ savingQuote ? 'Enregistrement...' : 'Envoyer le devis' }}
                </button>
              </div>
            </section>

            <!-- Devis déjà envoyé (lecture seule) -->
            <section
              v-else-if="activeOrder.quoted_price != null"
              class="rounded-2xl border border-cream-dark bg-white p-4"
            >
              <h3 class="mb-2 font-semibold text-chocolate">Devis envoyé</h3>
              <p class="text-2xl font-bold text-chocolate">{{ formatPrice(activeOrder.quoted_price) }}</p>
              <p class="mt-3 whitespace-pre-line rounded-xl bg-cream/70 p-3 text-sm leading-6 text-chocolate-soft">
                {{ activeOrder.merchant_response || 'Aucun message complémentaire.' }}
              </p>
            </section>

            <!-- Paiement -->
            <section v-if="activeOrder.quoted_price != null">
              <h3 class="mb-3 font-semibold text-chocolate">Suivi du paiement</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <div class="flex items-center justify-between gap-3 text-sm">
                  <span class="text-muted">Montant du devis</span>
                  <strong class="text-chocolate">{{ formatPrice(activeOrder.quoted_price) }}</strong>
                </div>
                <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                  <span class="text-muted">Montant payé</span>
                  <strong class="text-chocolate">{{ formatPrice(activeOrder.amount_paid) }}</strong>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-cream-dark">
                  <div
                    class="h-full rounded-full bg-caramel transition-all"
                    :style="{ width: `${paymentProgress(activeOrder)}%` }"
                  />
                </div>
                <div class="mt-2 flex items-center justify-between gap-3 text-xs text-muted">
                  <span>{{ paymentProgress(activeOrder) }} % payé</span>
                  <span>Reste {{ formatPrice(remainingAmount(activeOrder)) }}</span>
                </div>
              </div>
            </section>

            <!-- ✅ Documents -->
            <section v-if="hasAnyDocument(activeOrder)">
              <h3 class="mb-3 font-semibold text-chocolate">Documents</h3>
              <div class="space-y-2">
                <div
                  v-for="doc in documentItems(activeOrder)"
                  :key="doc.kind"
                  class="flex items-center justify-between gap-3 rounded-2xl border border-cream-dark bg-white p-4"
                  :class="{ 'opacity-60': !doc.available }"
                >
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-chocolate">{{ doc.label }}</p>
                    <p class="mt-0.5 text-xs text-muted">{{ doc.description }}</p>
                  </div>
                  <a
                    v-if="doc.available"
                    :href="documentUrl(activeOrder, doc.kind)"
                    target="_blank"
                    class="shrink-0 rounded-full border border-caramel px-3 py-1.5 text-xs font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white"
                  >
                    Consulter ↗
                  </a>
                  <span v-else class="shrink-0 text-xs italic text-muted">
                    {{ doc.pendingLabel }}
                  </span>
                </div>
              </div>
            </section>

            <!-- Actions -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Actions</h3>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button
                  v-if="canRefuse"
                  type="button"
                  class="rounded-xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-50 disabled:opacity-60"
                  :disabled="savingAction"
                  @click="refuseModalOpen = true"
                >
                  Refuser la demande
                </button>

                <button
                  v-if="['accepted', 'deposit_paid'].includes(activeOrder.status)"
                  type="button"
                  class="rounded-xl bg-caramel px-4 py-3 text-sm font-semibold text-white transition hover:bg-caramel-dark disabled:opacity-60"
                  :disabled="savingAction"
                  @click="markCompleted"
                >
                  Marquer comme terminée
                </button>

                <button
                  v-if="activeOrder.status === 'completed'"
                  type="button"
                  class="rounded-xl border border-cream-dark bg-white px-4 py-3 text-sm font-semibold text-chocolate"
                  disabled
                >
                  Commande terminée
                </button>
              </div>
            </section>
          </div>
        </aside>
      </Transition>

      <!-- Confirmation de refus -->
      <Transition name="fade">
        <div
          v-if="refuseModalOpen"
          class="fixed inset-0 z-[110] flex items-center justify-center bg-chocolate/50 p-4"
          @click.self="refuseModalOpen = false"
        >
          <div
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="refuse-title"
            aria-describedby="refuse-description"
          >
            <h3 id="refuse-title" class="font-display text-xl font-bold text-chocolate">
              Refuser cette demande ?
            </h3>
            <p id="refuse-description" class="mt-2 text-sm leading-6 text-muted">
              Cette action va enregistrer le refus de la demande.
            </p>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                type="button"
                class="rounded-xl border border-cream-dark px-4 py-2.5 text-sm font-semibold text-chocolate hover:bg-cream"
                :disabled="savingAction"
                @click="refuseModalOpen = false"
              >
                Annuler
              </button>
              <button
                type="button"
                class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                :disabled="savingAction"
                @click="confirmRefuse"
              >
                {{ savingAction ? 'En cours...' : 'Confirmer le refus' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

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
const quotes = ref({})

const savingQuote = ref(false)
const savingAction = ref(false)
const actionMessage = ref('')
const actionError = ref('')
const refuseModalOpen = ref(false)

let refreshInterval = null

const isAdmin = computed(() => auth.user?.role === 'admin')

const stats = computed(() => [
  {
    key: 'pending_quote',
    title: 'À traiter',
    value: orders.value.filter(o => o.status === 'pending_quote').length,
    subtitle: 'Devis à préparer',
  },
  {
    key: 'quoted',
    title: 'Devis envoyés',
    value: orders.value.filter(o => o.status === 'quoted').length,
    subtitle: 'En attente de réponse',
  },
  {
    key: 'accepted',
    title: 'En préparation',
    value: orders.value.filter(o => ['accepted', 'deposit_paid'].includes(o.status)).length,
    subtitle: 'Commandes acceptées',
  },
  {
    key: 'completed',
    title: 'Terminées',
    value: orders.value.filter(o => o.status === 'completed').length,
    subtitle: 'Commandes finalisées',
  },
  {
    key: 'all',
    title: 'Toutes',
    value: orders.value.length,
    subtitle: 'Toutes les demandes',
  },
])

const quoteForActiveOrder = computed(() => {
  if (!activeOrder.value) return null
  return quotes.value[activeOrder.value.id] || null
})

const canRefuse = computed(() => {
  if (!activeOrder.value) return false
  return ['pending_quote', 'quoted'].includes(activeOrder.value.status)
})

const isQuoteValid = computed(() => {
  const quote = quoteForActiveOrder.value
  return Boolean(quote && Number(quote.price) > 0)
})

const filteredOrders = computed(() => {
  let result = [...orders.value]

  if (activeStat.value !== 'all') {
    result = result.filter(order => {
      if (activeStat.value === 'accepted') {
        return ['accepted', 'deposit_paid'].includes(order.status)
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
        order.email,
        order.event_type,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return searchable.includes(query)
    })
  }

  const dateValue = value => {
    if (!value) return Number.MAX_SAFE_INTEGER
    const time = new Date(value).getTime()
    return Number.isNaN(time) ? Number.MAX_SAFE_INTEGER : time
  }

  switch (sortMode.value) {
    case 'event_asc':
      result.sort((a, b) => dateValue(a.event_date) - dateValue(b.event_date))
      break
    case 'recent':
      result.sort((a, b) => dateValue(b.created_at) - dateValue(a.created_at))
      break
    case 'oldest':
      result.sort((a, b) => dateValue(a.created_at) - dateValue(b.created_at))
      break
    case 'priority':
    default:
      result.sort((a, b) => {
        const aPriority = a.status === 'pending_quote' ? 0 : 1
        const bPriority = b.status === 'pending_quote' ? 0 : 1

        if (aPriority !== bPriority) return aPriority - bPriority

        const dateDifference = dateValue(a.event_date) - dateValue(b.event_date)
        if (dateDifference !== 0) return dateDifference

        return dateValue(b.created_at) - dateValue(a.created_at)
      })
  }

  return result
})

function ensureQuote(order) {
  if (!order) return

  if (!quotes.value[order.id]) {
    quotes.value[order.id] = {
      price: order.quoted_price ?? null,
      response: order.merchant_response ?? order.response ?? '',
    }
  } else {
    quotes.value[order.id].price = order.quoted_price ?? quotes.value[order.id].price ?? null
    quotes.value[order.id].response =
      order.merchant_response ?? order.response ?? quotes.value[order.id].response ?? ''
  }
}

async function loadOrders({ silent = false } = {}) {
  if (!silent) {
    if (orders.value.length) refreshing.value = true
    else loading.value = true
  }

  loadError.value = ''

  try {
    const response = await fetch('/api/merchant/custom-orders', {
      method: 'GET',
      headers: { Accept: 'application/json' },
      credentials: 'include',
    })

    if (!response.ok) {
      throw new Error('Impossible de récupérer les demandes.')
    }

    const data = await response.json()
    const list = Array.isArray(data) ? data : data.orders

    if (!Array.isArray(list)) {
      throw new Error('Le format des demandes reçu est invalide.')
    }

    orders.value = list

    for (const order of orders.value) {
      ensureQuote(order)
    }

    if (activeOrder.value) {
      const refreshedOrder = orders.value.find(
        order => order.id === activeOrder.value.id,
      )
      if (refreshedOrder) {
        activeOrder.value = refreshedOrder
        ensureQuote(refreshedOrder)
      }
    }
  } catch (error) {
    loadError.value = error.message || 'Une erreur est survenue lors du chargement.'
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

function openPanel(order) {
  activeOrder.value = order
  ensureQuote(order)
  panelOpen.value = true
  actionMessage.value = ''
  actionError.value = ''
  refuseModalOpen.value = false
}

function closePanel() {
  panelOpen.value = false
  refuseModalOpen.value = false
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

function setSort(key) {
  const mapping = {
    reference: 'reference',
    customer: 'customer_name',
    event: 'event_date',
  }
  const sortKey = mapping[key]
  if (sortMode.value === 'recent' && sortKey === 'reference') {
    sortMode.value = 'priority'
  }
}

function formatDate(value) {
  if (!value) return 'Date non renseignée'

  const dateOnly = typeof value === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(value)
  const date = new Date(dateOnly ? `${value}T12:00:00` : value)

  if (Number.isNaN(date.getTime())) return 'Date non renseignée'

  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

function formatPrice(value) {
  if (value === null || value === undefined || value === '') return 'À définir'

  const amount = Number(value)
  if (!Number.isFinite(amount)) return 'À définir'

  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 2,
  }).format(amount)
}

function eventLabel(type) {
  const labels = {
    wedding: 'Mariage',
    birthday: 'Anniversaire',
    baptism: 'Baptême',
    corporate: 'Événement professionnel',
    baby_shower: 'Baby shower',
    communion: 'Communion',
    other: 'Autre événement',
  }

  return labels[type] || type || 'Événement non précisé'
}

function statusLabel(status) {
  const labels = {
    pending_quote: 'À traiter',
    quoted: 'Devis envoyé',
    accepted: 'Acceptée',
    deposit_paid: 'Acompte réglé',
    completed: 'Terminée',
    rejected: 'Refusée par le client',
    refused_by_merchant: 'Refusée par le pâtissier',
    cancelled: 'Annulée',
  }

  return labels[status] || status || 'Statut inconnu'
}

function statusClasses(status) {
  const classes = {
    pending_quote: 'bg-amber-100 text-amber-800',
    quoted: 'bg-sky-100 text-sky-800',
    accepted: 'bg-emerald-100 text-emerald-800',
    deposit_paid: 'bg-emerald-100 text-emerald-800',
    completed: 'bg-stone-200 text-stone-700',
    rejected: 'bg-rose-100 text-rose-800',
    refused_by_merchant: 'bg-red-100 text-red-800',
    cancelled: 'bg-slate-100 text-slate-700',
  }

  return classes[status] || 'bg-cream-dark text-muted'
}

function quoteLabel(order) {
  if (order.quoted_price !== null && order.quoted_price !== undefined) {
    return 'Devis préparé'
  }

  if (order.status === 'pending_quote') return 'À préparer'
  if (order.status === 'rejected') return 'Devis refusé'
  if (order.status === 'refused_by_merchant') return 'Non établi'
  if (order.status === 'cancelled') return 'Annulé'

  return 'Non disponible'
}

function quoteClasses(order) {
  if (order.quoted_price !== null && order.quoted_price !== undefined) {
    return 'bg-emerald-50 text-emerald-700'
  }

  if (order.status === 'pending_quote') {
    return 'bg-amber-50 text-amber-800'
  }

  return 'bg-cream-dark text-muted'
}

function remainingAmount(order) {
  return Math.max(
    0,
    (Number(order.quoted_price) || 0) - (Number(order.amount_paid) || 0),
  )
}

function paymentProgress(order) {
  const total = Number(order.quoted_price) || 0
  if (total <= 0) return 0

  const paid = Number(order.amount_paid) || 0
  return Math.min(100, Math.max(0, Math.round((paid / total) * 100)))
}

// ✅ Documents
function hasAnyDocument(order) {
  if (!order) return false
  return Boolean(
    order.quote_pdf_path
    || order.deposit_invoice_path
    || order.balance_invoice_path
  )
}

function documentItems(order) {
  return [
    {
      kind: 'quote',
      label: 'Devis',
      description: 'Document commercial initial',
      available: Boolean(order.quote_pdf_path),
      pendingLabel: 'Non disponible',
    },
    {
      kind: 'deposit',
      label: 'Facture d\'acompte',
      description: 'Justificatif du premier règlement',
      available: Boolean(order.deposit_invoice_path),
      pendingLabel: 'Émise après paiement',
    },
    {
      kind: 'balance',
      label: 'Facture de solde',
      description: 'Document final après livraison',
      available: Boolean(order.balance_invoice_path),
      pendingLabel: 'À venir',
    },
  ]
}

function documentUrl(order, kind) {
  if (!order?.reference) return '#'
  const reference = encodeURIComponent(order.reference)

  const routes = {
    quote: `/api/custom-orders/${reference}/quote-pdf`,
    deposit: `/api/custom-orders/${reference}/invoice/deposit`,
    balance: `/api/custom-orders/${reference}/invoice/balance`,
  }

  return routes[kind] || '#'
}

// ✅ CORRECTION 422 : envoyer les noms de champs attendus par Laravel
async function sendQuote() {
  if (!activeOrder.value || !quoteForActiveOrder.value || !isQuoteValid.value) return

  savingQuote.value = true
  actionError.value = ''
  actionMessage.value = ''

  try {
    const orderId = activeOrder.value.id
    const quote = quoteForActiveOrder.value

    const response = await fetch(`/api/admin/custom-orders/${orderId}/quote`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({
        // ✅ Noms attendus par le backend Laravel
        quoted_price: Number(quote.price),
        merchant_response: quote.response || '',
      }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      // ✅ Afficher le message de validation Laravel si présent
      const validationMessage = data.errors
        ? Object.values(data.errors).flat().join(' ')
        : data.message
      throw new Error(validationMessage || 'Impossible d’enregistrer le devis.')
    }

    actionMessage.value = 'Le devis a bien été enregistré.'
    await loadOrders({ silent: true })
  } catch (error) {
    actionError.value = error.message || 'Une erreur est survenue.'
  } finally {
    savingQuote.value = false
  }
}

async function confirmRefuse() {
  if (!activeOrder.value) return

  const orderId = activeOrder.value.id
  savingAction.value = true
  actionError.value = ''
  actionMessage.value = ''

  try {
    const response = await fetch(`/api/admin/custom-orders/${orderId}/refuse`, {
      method: 'PUT',
      headers: { Accept: 'application/json' },
      credentials: 'include',
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      throw new Error(data.message || 'Impossible de refuser cette demande.')
    }

    refuseModalOpen.value = false
    actionMessage.value = 'La demande a été refusée.'
    await loadOrders({ silent: true })
  } catch (error) {
    actionError.value = error.message || 'Une erreur est survenue.'
  } finally {
    savingAction.value = false
  }
}

// ✅ CORRECTION 500 : vérifier les prérequis avant d'envoyer
async function markCompleted() {
  if (!activeOrder.value) return

  // ✅ Autoriser accepted ET deposit_paid → completed
  if (!['accepted', 'deposit_paid'].includes(activeOrder.value.status)) {
    actionError.value = 'Cette demande ne peut pas être marquée comme terminée dans son état actuel.'
    return
  }

  const orderId = activeOrder.value.id
  savingAction.value = true
  actionError.value = ''
  actionMessage.value = ''

  try {
    const response = await fetch(`/api/merchant/custom-orders/${orderId}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ status: 'completed' }),
    })

    const data = await response.json().catch(() => ({}))

    if (!response.ok) {
      const validationMessage = data.errors
        ? Object.values(data.errors).flat().join(' ')
        : data.message
      throw new Error(validationMessage || 'Impossible de terminer cette commande.')
    }

    actionMessage.value = 'La commande a été marquée comme terminée.'
    await loadOrders({ silent: true })
  } catch (error) {
    actionError.value = error.message || 'Une erreur est survenue.'
  } finally {
    savingAction.value = false
  }
}

function handleNotificationClick(event) {
  if (event.detail?.url === '/admin/sur-mesure') {
    loadOrders({ silent: true })
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    if (refuseModalOpen.value) {
      refuseModalOpen.value = false
    } else if (panelOpen.value) {
      closePanel()
    }
  }
}

watch(panelOpen, isOpen => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = isOpen ? 'hidden' : ''
  }
})

onMounted(() => {
  loadOrders()

  refreshInterval = setInterval(() => {
    loadOrders({ silent: true })
  }, 30_000)

  window.addEventListener('notification-click', handleNotificationClick)
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)

  window.removeEventListener('notification-click', handleNotificationClick)
  window.removeEventListener('keydown', handleKeydown)

  if (typeof document !== 'undefined') {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 180ms ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: transform 240ms ease;
}

.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}

@media (prefers-reduced-motion: reduce) {
  .fade-enter-active,
  .fade-leave-active,
  .drawer-enter-active,
  .drawer-leave-active {
    transition: none;
  }
}
</style>