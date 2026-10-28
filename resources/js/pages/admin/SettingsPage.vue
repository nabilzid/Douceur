<template>
  <div class="max-w-4xl mx-auto">

    <!-- ═══ HEADER ═══ -->
    <header class="mb-8">
      <p class="text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark mb-2">
        Administration
      </p>
      <h1 class="font-display text-3xl md:text-4xl font-bold text-chocolate">
        Paramètres
      </h1>
      <p class="mt-2 text-sm text-muted">
        Configuration globale de la boutique.
      </p>
    </header>

    <!-- ═══ CHARGEMENT ═══ -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3 bg-white rounded-2xl shadow-soft">
      <div class="w-10 h-10 rounded-full border-4 border-caramel/20 border-t-caramel animate-spin"></div>
      <p class="text-sm text-muted">Chargement des paramètres…</p>
    </div>

    <!-- ═══ FORMULAIRE ═══ -->
    <form v-else @submit.prevent="save" class="space-y-4 md:space-y-5">

      <!-- Section Livraison -->
      <section class="bg-white rounded-2xl shadow-soft p-5 md:p-7">
        <div class="flex items-center gap-3 mb-5">
          <div class="grid place-items-center w-10 h-10 rounded-xl bg-caramel/10">
            <span class="text-xl">🚚</span>
          </div>
          <div>
            <h2 class="font-display text-lg font-bold text-chocolate">Livraison</h2>
            <p class="text-xs text-muted">Règles de livraison et frais associés</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Montant minimum de commande (€) <span class="text-raspberry">*</span>
            </label>
            <input
              v-model.number="form.delivery_min_order_amount"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
            <p class="mt-1 text-xs text-muted">En dessous de ce montant, la livraison n'est pas proposée.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Seuil de livraison gratuite (€) <span class="text-raspberry">*</span>
            </label>
            <input
              v-model.number="form.delivery_free_threshold"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
            <p class="mt-1 text-xs text-muted">Au-dessus, la livraison est offerte.</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Frais de livraison (€) <span class="text-raspberry">*</span>
            </label>
            <input
              v-model.number="form.delivery_default_fee"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
            <p class="mt-1 text-xs text-muted">Appliqués sous le seuil de gratuité.</p>
          </div>
        </div>
      </section>

      <!-- Section Taxes -->
      <section class="bg-white rounded-2xl shadow-soft p-5 md:p-7">
        <div class="flex items-center gap-3 mb-5">
          <div class="grid place-items-center w-10 h-10 rounded-xl bg-caramel/10">
            <span class="text-xl">💰</span>
          </div>
          <div>
            <h2 class="font-display text-lg font-bold text-chocolate">Taxes</h2>
            <p class="text-xs text-muted">TVA appliquée sur les produits</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Taux de TVA (%) <span class="text-raspberry">*</span>
            </label>
            <input
              v-model.number="form.tax_vat_rate"
              type="number"
              min="0"
              step="0.1"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
            <p class="mt-1 text-xs text-muted">Taux standard en France : 5,5 % (alimentaire).</p>
          </div>
        </div>
      </section>

      <!-- Section Sur mesure -->
      <section class="bg-white rounded-2xl shadow-soft p-5 md:p-7">
        <div class="flex items-center gap-3 mb-5">
          <div class="grid place-items-center w-10 h-10 rounded-xl bg-caramel/10">
            <span class="text-xl">🎂</span>
          </div>
          <div>
            <h2 class="font-display text-lg font-bold text-chocolate">Commandes sur mesure</h2>
            <p class="text-xs text-muted">Délai minimum avant la date de l'événement</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Délai minimum (jours) <span class="text-raspberry">*</span>
            </label>
            <input
              v-model.number="form.custom_order_min_lead_days"
              type="number"
              min="1"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
            <p class="mt-1 text-xs text-muted">Nombre de jours minimum entre la demande et l'événement.</p>
          </div>
        </div>
      </section>

      <!-- Feedback -->
      <p v-if="error" class="flex items-center gap-2 p-4 rounded-xl bg-raspberry/10 text-raspberry font-medium text-sm">
        <span>⚠️</span> {{ error }}
      </p>
      <p v-if="success" class="flex items-center gap-2 p-4 rounded-xl bg-emerald-50 text-emerald-700 font-medium text-sm">
        <span>✓</span> Paramètres enregistrés.
      </p>

      <!-- Submit -->
      <div class="flex justify-end pt-2">
        <button
          type="submit"
          :disabled="saving"
          class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
        >
          <svg
            v-if="saving"
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
          {{ saving ? 'Enregistrement…' : 'Enregistrer les paramètres' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const form = reactive({
  delivery_min_order_amount: 15,
  delivery_free_threshold: 30,
  delivery_default_fee: 5,
  tax_vat_rate: 5.5,
  custom_order_min_lead_days: 3,
});
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(false);

async function save() {
  saving.value = true;
  error.value = null;
  success.value = false;
  try {
    await axios.put('/api/admin/settings', form);
    success.value = true;
    setTimeout(() => (success.value = false), 3000);
  } catch (e) {
    error.value = e.response?.data?.message || 'Erreur.';
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/admin/settings');
    Object.assign(form, data);
  } finally {
    loading.value = false;
  }
});
</script>