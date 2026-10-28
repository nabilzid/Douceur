<template>
  <div class="min-h-screen bg-cream px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">

      <!-- ═══ HEADER ═══ -->
      <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="font-display text-3xl font-bold text-chocolate sm:text-4xl">
            Produits & stock
          </h1>
          <p class="mt-2 text-sm text-muted sm:text-base">
            Gérez votre catalogue, ajustez les stocks et activez vos produits.
          </p>
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-cream-dark bg-white px-4 py-2.5 text-sm font-semibold text-chocolate shadow-sm transition hover:bg-cream-dark disabled:cursor-not-allowed disabled:opacity-60"
            :disabled="loading || refreshing"
            @click="load()"
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

          <button
            v-if="isAdmin"
            type="button"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-caramel px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-caramel-dark"
            @click="openCreateModal"
          >
            + Nouveau produit
          </button>
        </div>
      </header>

      <!-- ═══ STATS ═══ -->
      <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
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
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.5fr)_minmax(180px,1fr)_minmax(180px,1fr)_auto]">
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
                placeholder="Nom, type..."
                class="w-full rounded-xl border border-cream-dark bg-cream/40 py-2.5 pl-9 pr-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:ring-2 focus:ring-caramel/20"
              />
            </div>
          </label>

          <label class="block">
            <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted">
              Type
            </span>
            <select
              v-model="typeFilter"
              class="w-full rounded-xl border border-cream-dark bg-white px-3 py-2.5 text-sm text-chocolate outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20"
            >
              <option value="all">Tous les types</option>
              <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
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
              <option value="name">Nom (A → Z)</option>
              <option value="price_asc">Prix croissant</option>
              <option value="price_desc">Prix décroissant</option>
              <option value="stock_asc">Stock croissant</option>
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
            <h2 class="font-semibold text-chocolate">Tous les produits</h2>
            <p class="mt-1 text-sm text-muted">
              {{ filteredProducts.length }} produit{{ filteredProducts.length > 1 ? 's' : '' }}
              affiché{{ filteredProducts.length > 1 ? 's' : '' }}
              sur {{ products.length }}
            </p>
          </div>
          <p class="text-xs text-muted">
            Les produits en stock faible sont signalés.
          </p>
        </div>

        <!-- Chargement -->
        <div v-if="loading && !products.length" class="space-y-3 p-5">
          <div v-for="n in 5" :key="n" class="h-16 animate-pulse rounded-xl bg-cream" />
        </div>

        <!-- Vide -->
        <div v-else-if="!filteredProducts.length" class="px-6 py-16 text-center">
          <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream text-caramel-dark">
            <span class="text-2xl">🍰</span>
          </div>
          <h3 class="mt-4 font-semibold text-chocolate">Aucun produit trouvé</h3>
          <p class="mt-1 text-sm text-muted">
            Modifie les filtres ou crée un nouveau produit.
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
          <div class="hidden grid-cols-[minmax(160px,1.5fr)_minmax(120px,1fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_32px] gap-4 border-b border-cream-dark bg-cream/50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted xl:grid">
            <span>Produit</span>
            <span>Type</span>
            <span>Prix</span>
            <span>Stock</span>
            <span>Seuil</span>
            <span>Statut</span>
            <span aria-hidden="true"></span>
          </div>

          <!-- Lignes -->
          <div class="divide-y divide-cream-dark/80">
            <button
              v-for="product in filteredProducts"
              :key="product.id"
              type="button"
              class="group relative block w-full cursor-pointer px-4 py-5 text-left transition hover:bg-cream/60 focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-caramel sm:px-5 xl:grid xl:grid-cols-[minmax(160px,1.5fr)_minmax(120px,1fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_minmax(100px,0.8fr)_32px] xl:items-center xl:gap-4"
              @click="openPanel(product)"
            >
              <!-- Mobile -->
              <div class="flex items-start justify-between gap-3 xl:hidden">
                <div class="min-w-0">
                  <p class="font-semibold text-chocolate">{{ product.name }}</p>
                  <p class="mt-1 text-xs text-caramel-dark font-semibold uppercase tracking-wider">
                    {{ product.type?.name }}
                  </p>
                  <p class="mt-1 text-sm font-bold text-chocolate">
                    {{ formatPrice(product.price) }}
                  </p>
                </div>
                <svg class="mt-1 h-4 w-4 shrink-0 text-muted transition group-hover:translate-x-0.5 group-hover:text-caramel-dark"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="m9 18 6-6-6-6" />
                </svg>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-2 xl:hidden">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="stockClasses(product)">
                  {{ product.stock_quantity }} en stock
                </span>
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="product.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                  {{ product.is_active ? 'Actif' : 'Inactif' }}
                </span>
              </div>

              <!-- Desktop -->
              <div class="hidden min-w-0 xl:block">
                <p class="font-semibold text-chocolate truncate">{{ product.name }}</p>
                <p v-if="product.is_customizable" class="mt-0.5 text-xs text-muted">Personnalisable</p>
              </div>

              <div class="hidden min-w-0 xl:block">
                <span class="inline-flex rounded-full bg-caramel/10 px-2.5 py-1 text-xs font-semibold text-caramel-dark">
                  {{ product.type?.name }}
                </span>
              </div>

              <div class="hidden xl:block">
                <p class="font-semibold text-chocolate">{{ formatPrice(product.price) }}</p>
              </div>

              <div class="hidden xl:block">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="stockClasses(product)">
                  {{ product.stock_quantity }}
                </span>
              </div>

              <div class="hidden xl:block">
                <span class="text-sm text-muted">{{ product.low_stock_threshold }}</span>
              </div>

              <div class="hidden xl:block">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="product.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                  {{ product.is_active ? 'Actif' : 'Inactif' }}
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
          v-if="panelOpen && activeProduct"
          class="fixed inset-y-0 right-0 z-[100] flex w-full max-w-2xl flex-col bg-cream shadow-2xl"
          role="dialog"
          aria-modal="true"
          :aria-label="`Détail du produit ${activeProduct.name}`"
        >
          <!-- En-tête panneau -->
          <div class="flex items-start justify-between gap-4 border-b border-cream-dark bg-white px-5 py-5 sm:px-7">
            <div class="min-w-0">
              <p class="text-xs font-semibold uppercase tracking-wide text-caramel-dark">
                Produit
              </p>
              <h2 class="mt-1 truncate font-display text-2xl font-bold text-chocolate">
                {{ activeProduct.name }}
              </h2>
              <p class="mt-1 text-sm text-muted">
                {{ activeProduct.type?.name }}
              </p>
            </div>
            <button
              type="button"
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-muted transition hover:bg-cream hover:text-chocolate"
              aria-label="Fermer"
              @click="closePanel"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m18 6-12 12M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Contenu panneau -->
          <div class="flex-1 space-y-6 overflow-y-auto px-5 py-6 sm:px-7">
            <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
              {{ error }}
            </div>

            <!-- Statut + prix -->
            <section class="rounded-2xl border border-cream-dark bg-white p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Statut</p>
                  <button
                    type="button"
                    @click="toggleActive(activeProduct)"
                    class="mt-2 inline-flex rounded-full px-3 py-1.5 text-xs font-semibold transition"
                    :class="activeProduct.is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                  >
                    {{ activeProduct.is_active ? '✓ Actif' : 'Inactif' }}
                  </button>
                </div>
                <div class="text-right">
                  <p class="text-xs font-semibold uppercase tracking-wide text-muted">Prix</p>
                  <p class="mt-2 font-display text-xl font-bold text-chocolate">
                    {{ formatPrice(activeProduct.price) }}
                  </p>
                </div>
              </div>
            </section>

            <!-- Description -->
            <section v-if="activeProduct.description">
              <h3 class="mb-3 font-semibold text-chocolate">Description</h3>
              <div class="whitespace-pre-line rounded-2xl border border-cream-dark bg-white p-4 text-sm leading-6 text-chocolate">
                {{ activeProduct.description }}
              </div>
            </section>

            <!-- Allergènes -->
            <section v-if="activeProduct.allergens?.length">
              <h3 class="mb-3 font-semibold text-chocolate">Allergènes</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="a in activeProduct.allergens"
                    :key="a"
                    class="px-3 py-1 rounded-full bg-raspberry/10 text-raspberry text-xs font-medium"
                  >
                    {{ a }}
                  </span>
                </div>
              </div>
            </section>

            <!-- Stock -->
            <section>
              <h3 class="mb-3 font-semibold text-chocolate">Stock</h3>
              <div class="rounded-2xl border border-cream-dark bg-white p-4">
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div class="p-3 rounded-lg bg-cream">
                    <p class="text-xs text-muted mb-1">Quantité actuelle</p>
                    <p class="font-display text-xl font-bold text-chocolate">{{ activeProduct.stock_quantity }}</p>
                  </div>
                  <div class="p-3 rounded-lg bg-cream">
                    <p class="text-xs text-muted mb-1">Seuil d'alerte</p>
                    <p class="font-display text-xl font-bold text-chocolate">{{ activeProduct.low_stock_threshold }}</p>
                  </div>
                </div>

                <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold" :class="stockClasses(activeProduct)">
                  {{ activeProduct.stock_quantity <= activeProduct.low_stock_threshold ? '⚠️ Stock faible' : '✓ Stock OK' }}
                </span>

                <div class="mt-4 pt-4 border-t border-cream-dark">
                  <label class="block text-sm font-medium text-chocolate mb-2">
                    Ajuster le stock
                  </label>
                  <div class="flex gap-2">
                    <input
                      v-model.number="adjustments[activeProduct.id]"
                      type="number"
                      placeholder="Ex: +10 ou -5"
                      class="flex-1 px-3 py-2.5 rounded-xl border border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                    />
                    <button
                      type="button"
                      @click="adjustStock(activeProduct)"
                      class="px-4 py-2.5 rounded-xl bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm transition-colors"
                    >
                      Ajuster
                    </button>
                  </div>
                </div>
              </div>
            </section>

            <!-- Actions admin -->
            <section v-if="isAdmin">
              <h3 class="mb-3 font-semibold text-chocolate">Actions</h3>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button
                  type="button"
                  @click="openEditModal(activeProduct)"
                  class="rounded-xl border border-cream-dark bg-white px-4 py-3 text-sm font-semibold text-chocolate hover:bg-cream transition-colors"
                >
                  ✏️ Modifier
                </button>
                <button
                  type="button"
                  @click="confirmDelete(activeProduct)"
                  class="rounded-xl border border-raspberry/30 bg-white px-4 py-3 text-sm font-semibold text-raspberry hover:bg-raspberry/10 transition-colors"
                >
                  🗑️ Supprimer
                </button>
              </div>
            </section>
          </div>
        </aside>
      </Transition>
    </Teleport>

    <!-- ═══ MODAL CRÉATION / ÉDITION ═══ -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showModal && isAdmin"
          class="fixed inset-0 z-[110] flex items-center justify-center bg-chocolate/50 p-4 overflow-y-auto"
          @click.self="closeModal"
        >
          <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl my-8">
            <div class="flex items-start justify-between gap-3 mb-5">
              <h2 class="font-display text-xl font-bold text-chocolate">
                {{ editingProduct ? 'Modifier le produit' : 'Nouveau produit' }}
              </h2>
              <button
                type="button"
                @click="closeModal"
                class="grid place-items-center w-9 h-9 rounded-full bg-cream hover:bg-cream-dark transition-colors shrink-0"
              >
                <svg class="w-4 h-4 text-chocolate" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m18 6-12 12M6 6l12 12" stroke-linecap="round" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="saveProduct" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                  Type <span class="text-raspberry">*</span>
                </label>
                <select
                  v-model.number="form.product_type_id"
                  required
                  class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                >
                  <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                  Nom <span class="text-raspberry">*</span>
                </label>
                <input
                  v-model="form.name"
                  required
                  class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                  Description
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors resize-y"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                    Prix (€) <span class="text-raspberry">*</span>
                  </label>
                  <input
                    v-model.number="form.price"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                    Stock <span class="text-raspberry">*</span>
                  </label>
                  <input
                    v-model.number="form.stock_quantity"
                    type="number"
                    min="0"
                    required
                    class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                  Seuil d'alerte <span class="text-raspberry">*</span>
                </label>
                <input
                  v-model.number="form.low_stock_threshold"
                  type="number"
                  min="0"
                  required
                  class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                  Allergènes <span class="text-xs text-muted">(séparés par virgule)</span>
                </label>
                <input
                  v-model="allergensInput"
                  placeholder="gluten, lait, œufs"
                  class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
                />
              </div>

              <label class="flex items-center gap-3 p-3 rounded-xl bg-cream hover:bg-cream-dark cursor-pointer transition-colors">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  class="w-4 h-4 accent-caramel cursor-pointer shrink-0"
                />
                <span class="text-sm font-medium text-chocolate-soft">Produit actif</span>
              </label>

              <p v-if="error" class="flex items-center gap-2 p-3 rounded-xl bg-raspberry/10 text-raspberry text-sm font-medium">
                <span>⚠️</span> {{ error }}
              </p>

              <button
                type="submit"
                :disabled="saving"
                class="w-full px-6 py-3.5 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {{ saving ? 'Enregistrement…' : (editingProduct ? 'Enregistrer' : 'Créer le produit') }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const isAdmin = computed(() => auth.user?.role === 'admin');

const products = ref([]);
const types = ref([]);
const loading = ref(true);
const refreshing = ref(false);
const adjustments = reactive({});

// Filtres
const search = ref('');
const typeFilter = ref('all');
const sortMode = ref('name');
const activeStat = ref('all');

// Panneau détail
const panelOpen = ref(false);
const activeProduct = ref(null);

// Modal
const showModal = ref(false);
const editingProduct = ref(null);
const saving = ref(false);
const error = ref(null);

const form = reactive({
  product_type_id: null,
  name: '',
  description: '',
  price: 0,
  stock_quantity: 0,
  low_stock_threshold: 5,
  is_active: true,
  is_customizable: false,
});
const allergensInput = ref('');

let pollInterval = null;

// ═══ Stats ═══
const stats = computed(() => {
  const lowStock = products.value.filter(p => p.stock_quantity <= p.low_stock_threshold);
  const inactive = products.value.filter(p => !p.is_active);

  return [
    {
      key: 'all',
      title: 'Tous',
      value: products.value.length,
      subtitle: 'Produits dans le catalogue',
    },
    {
      key: 'active',
      title: 'Actifs',
      value: products.value.filter(p => p.is_active).length,
      subtitle: 'Visibles en boutique',
    },
    {
      key: 'low_stock',
      title: 'Stock faible',
      value: lowStock.length,
      subtitle: 'À réapprovisionner',
    },
    {
      key: 'inactive',
      title: 'Inactifs',
      value: inactive.length,
      subtitle: 'Masqués en boutique',
    },
  ];
});

// ═══ Filtres + tri ═══
const filteredProducts = computed(() => {
  let result = [...products.value];

  // Filtre stat (actifs/inactifs/stock faible)
  if (activeStat.value === 'active') {
    result = result.filter(p => p.is_active);
  } else if (activeStat.value === 'inactive') {
    result = result.filter(p => !p.is_active);
  } else if (activeStat.value === 'low_stock') {
    result = result.filter(p => p.stock_quantity <= p.low_stock_threshold);
  }

  // Filtre type
  if (typeFilter.value !== 'all') {
    result = result.filter(p => p.product_type_id === typeFilter.value);
  }

  // Recherche
  const query = search.value.trim().toLowerCase();
  if (query) {
    result = result.filter(p => {
      const searchable = [p.name, p.type?.name].filter(Boolean).join(' ').toLowerCase();
      return searchable.includes(query);
    });
  }

  // Tri
  switch (sortMode.value) {
    case 'price_asc':
      result.sort((a, b) => (a.price ?? 0) - (b.price ?? 0));
      break;
    case 'price_desc':
      result.sort((a, b) => (b.price ?? 0) - (a.price ?? 0));
      break;
    case 'stock_asc':
      result.sort((a, b) => (a.stock_quantity ?? 0) - (b.stock_quantity ?? 0));
      break;
    case 'name':
    default:
      result.sort((a, b) => (a.name || '').localeCompare(b.name || '', 'fr'));
  }

  return result;
});

// ═══ Helpers ═══
function formatPrice(p) {
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}

function stockClasses(product) {
  if (product.stock_quantity <= product.low_stock_threshold) {
    return 'bg-raspberry/10 text-raspberry';
  }
  return 'bg-emerald-100 text-emerald-800';
}

// ═══ Chargement ═══
async function load() {
  if (products.value.length) refreshing.value = true;
  else loading.value = true;

  try {
    const [prodRes, typeRes] = await Promise.all([
      axios.get('/api/merchant/products'),
      axios.get('/api/product-types'),
    ]);
    products.value = prodRes.data;
    types.value = typeRes.data;
    products.value.forEach((p) => (adjustments[p.id] = adjustments[p.id] ?? null));

    // Rafraîchir le produit actif si le panneau est ouvert
    if (activeProduct.value) {
      const refreshed = products.value.find(p => p.id === activeProduct.value.id);
      if (refreshed) activeProduct.value = refreshed;
    }
  } catch {
    // garder les données existantes
  } finally {
    loading.value = false;
    refreshing.value = false;
  }
}

// ═══ Panneau ═══
function openPanel(product) {
  activeProduct.value = product;
  panelOpen.value = true;
  error.value = null;
}

function closePanel() {
  panelOpen.value = false;
}

// ═══ Filtres reset ═══
function selectStat(key) {
  activeStat.value = key;
}

function resetFilters() {
  search.value = '';
  typeFilter.value = 'all';
  sortMode.value = 'name';
  activeStat.value = 'all';
}

// ═══ Modal ═══
function openCreateModal() {
  editingProduct.value = null;
  resetForm();
  showModal.value = true;
  error.value = null;
}

function openEditModal(product) {
  editingProduct.value = product;
  form.product_type_id = product.product_type_id;
  form.name = product.name;
  form.description = product.description || '';
  form.price = product.price;
  form.stock_quantity = product.stock_quantity;
  form.low_stock_threshold = product.low_stock_threshold;
  form.is_active = product.is_active;
  form.is_customizable = product.is_customizable || false;
  allergensInput.value = (product.allergens || []).join(', ');
  showModal.value = true;
  error.value = null;
}

function closeModal() {
  showModal.value = false;
  editingProduct.value = null;
  error.value = null;
}

function resetForm() {
  form.product_type_id = types.value[0]?.id ?? null;
  form.name = '';
  form.description = '';
  form.price = 0;
  form.stock_quantity = 0;
  form.low_stock_threshold = 5;
  form.is_active = true;
  form.is_customizable = false;
  allergensInput.value = '';
}

// ═══ Actions ═══
async function saveProduct() {
  saving.value = true;
  error.value = null;

  try {
    const payload = {
      ...form,
      allergens: allergensInput.value
        .split(',')
        .map((a) => a.trim())
        .filter(Boolean),
    };

    if (editingProduct.value) {
      const { data } = await axios.put(`/api/admin/products/${editingProduct.value.id}`, payload);
      Object.assign(editingProduct.value, data);
    } else {
      const { data } = await axios.post('/api/admin/products', payload);
      products.value.push(data);
    }

    await load();
    closeModal();
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur lors de l\'enregistrement.';
  } finally {
    saving.value = false;
  }
}

async function toggleActive(product) {
  try {
    const { data } = await axios.put(`/api/merchant/products/${product.id}`, {
      ...product,
      is_active: !product.is_active,
    });
    Object.assign(product, data);
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur.');
  }
}

async function adjustStock(product) {
  const adjustment = adjustments[product.id];
  if (!adjustment || Number(adjustment) === 0) return;

  try {
    const { data } = await axios.post(`/api/merchant/products/${product.id}/stock`, {
      quantity: Number(adjustment),  // ← était "adjustment", devient "quantity"
    });
    Object.assign(product, data);
    adjustments[product.id] = null;
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur.');
  }
}

async function confirmDelete(product) {
  if (!confirm(`Supprimer définitivement "${product.name}" ? Cette action est irréversible.`)) return;

  try {
    await axios.delete(`/api/admin/products/${product.id}`);
    products.value = products.value.filter((p) => p.id !== product.id);
    closePanel();
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur.');
  }
}

// ═══ Lifecycle ═══
function handleNotificationClick(event) {
  if (event.detail?.url === '/admin/produits') {
    load();
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    if (showModal.value) closeModal();
    else if (panelOpen.value) closePanel();
  }
}

watch(panelOpen, isOpen => {
  document.body.style.overflow = isOpen ? 'hidden' : '';
});

onMounted(() => {
  load();
  pollInterval = setInterval(load, 15 * 60 * 1000);
  window.addEventListener('notification-clicked', handleNotificationClick);
  window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
  if (pollInterval) clearInterval(pollInterval);
  window.removeEventListener('notification-clicked', handleNotificationClick);
  window.removeEventListener('keydown', handleKeydown);
  document.body.style.overflow = '';
});
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