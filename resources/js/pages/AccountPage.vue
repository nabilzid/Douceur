
<!-- Page mon compte client -->
<template>
  <div class="max-w-6xl mx-auto">

    <!-- En-tête -->
    <header class="mb-8 sm:mb-10">
      <p class="text-xs font-semibold uppercase tracking-[0.2em] text-caramel-dark">
        Espace personnel
      </p>

      <h1 class="mt-2 font-display text-3xl font-bold text-chocolate sm:text-4xl">
        Mon compte<span class="text-caramel">.</span>
      </h1>

      <p class="mt-3 max-w-2xl text-sm leading-6 text-chocolate-soft/70 sm:text-base">
        Gérez vos informations personnelles et vos adresses de livraison.
      </p>
    </header>



    <!-- Non connecté -->
    <div v-if="!auth.isAuthenticated" class="max-w-md mx-auto text-center py-16 px-4 bg-white rounded-2xl shadow-soft">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cream-dark mb-4">
        <span class="text-3xl">🔒</span>
      </div>
      <h2 class="font-display text-xl font-bold text-chocolate mb-2">Accès restreint</h2>
      <p class="text-sm text-muted mb-6">Vous devez être connecté pour accéder à cette page.</p>
      <RouterLink
        to="/connexion"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all hover:-translate-y-0.5"
      >
        Se connecter
      </RouterLink>
    </div>

    <!-- Connecté -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 items-start">

      <!-- ═══ Informations personnelles ═══ -->
      <section class="bg-white rounded-2xl shadow-soft p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="grid place-items-center w-10 h-10 rounded-xl bg-caramel/10">
            <span class="text-xl">👤</span>
          </div>
          <h2 class="font-display text-xl font-bold text-chocolate">Informations personnelles</h2>
        </div>

        <form @submit.prevent="saveProfile" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Nom <span class="text-raspberry">*</span>
            </label>
            <input
              v-model="profile.name"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Email <span class="text-raspberry">*</span>
            </label>
            <input
              v-model="profile.email"
              type="email"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">Téléphone</label>
            <input
              v-model="profile.phone"
              type="tel"
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <!-- Feedback -->
          <p v-if="profileError" class="flex items-center gap-2 text-sm text-raspberry font-medium">
            <span>⚠️</span> {{ profileError }}
          </p>
          <p v-if="profileSuccess" class="flex items-center gap-2 text-sm text-emerald-600 font-medium">
            <span>✓</span> Profil mis à jour.
          </p>

          <button
            type="submit"
            :disabled="savingProfile"
            class="w-full sm:w-auto px-6 py-3 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ savingProfile ? 'Enregistrement…' : 'Enregistrer' }}
          </button>
        </form>
      </section>

      <!-- ═══ Adresses ═══ -->
      <section class="bg-white rounded-2xl shadow-soft p-6 md:p-8">
        <div class="flex items-center justify-between gap-3 mb-6">
          <div class="flex items-center gap-3">
            <div class="grid place-items-center w-10 h-10 rounded-xl bg-caramel/10">
              <span class="text-xl">📍</span>
            </div>
            <h2 class="font-display text-xl font-bold text-chocolate">Mes adresses</h2>
          </div>
          <button
            @click="showAddressForm = !showAddressForm"
            class="shrink-0 px-4 py-2 rounded-full text-xs md:text-sm font-semibold transition-all"
            :class="showAddressForm
              ? 'bg-cream-dark text-chocolate-soft hover:bg-cream'
              : 'bg-caramel/10 text-caramel-dark hover:bg-caramel hover:text-white'"
          >
            {{ showAddressForm ? '✕ Annuler' : '+ Ajouter' }}
          </button>
        </div>

        <!-- Formulaire d'ajout -->
        <form v-if="showAddressForm" @submit.prevent="saveAddress" class="space-y-4 pb-6 mb-6 border-b border-cream-dark">
          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Libellé <span class="text-xs text-muted">(optionnel)</span>
            </label>
            <input
              v-model="addressForm.label"
              placeholder="Domicile, Bureau…"
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm placeholder:text-muted/60 focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Destinataire <span class="text-raspberry">*</span>
            </label>
            <input
              v-model="addressForm.recipient_name"
              required
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">Téléphone</label>
            <input
              v-model="addressForm.phone"
              type="tel"
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <!-- Autocomplete adresse -->
          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
              Adresse <span class="text-raspberry">*</span>
            </label>
            <div class="relative">
              <input
                v-model="addressForm.street"
                required
                autocomplete="off"
                @input="onAddressInput"
                @focus="showSuggestions = suggestions.length > 0"
                @blur="hideSuggestions"
                class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
              />
              <ul
                v-if="showSuggestions && suggestions.length > 0"
                class="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-float border border-cream-dark py-1 max-h-64 overflow-y-auto z-20"
              >
                <li
                  v-for="(s, i) in suggestions"
                  :key="i"
                  @mousedown.prevent="selectSuggestion(s)"
                  class="px-4 py-2.5 text-sm text-chocolate-soft cursor-pointer border-l-3 border-transparent hover:bg-cream hover:text-caramel-dark hover:border-caramel transition-colors"
                >
                  {{ s.label }}
                </li>
              </ul>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-chocolate-soft mb-1.5">Complément</label>
            <input
              v-model="addressForm.street_2"
              class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                Code postal <span class="text-raspberry">*</span>
              </label>
              <input
                v-model="addressForm.postal_code"
                required
                class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-chocolate-soft mb-1.5">
                Ville <span class="text-raspberry">*</span>
              </label>
              <input
                v-model="addressForm.city"
                required
                class="w-full px-4 py-3 rounded-xl border-2 border-cream-dark bg-cream text-chocolate text-sm focus:outline-none focus:border-caramel focus:bg-white transition-colors"
              />
            </div>
          </div>

          <label class="flex items-center gap-3 p-3 rounded-xl bg-cream hover:bg-cream-dark cursor-pointer transition-colors">
            <input
              v-model="addressForm.is_default"
              type="checkbox"
              class="w-4 h-4 accent-caramel cursor-pointer shrink-0"
            />
            <span class="text-sm font-medium text-chocolate-soft">
              Définir comme adresse de livraison par défaut
            </span>
          </label>

          <p v-if="addressError" class="flex items-center gap-2 text-sm text-raspberry font-medium">
            <span>⚠️</span> {{ addressError }}
          </p>

          <button
            type="submit"
            :disabled="savingAddress"
            class="w-full px-6 py-3 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all disabled:opacity-60 disabled:cursor-not-allowed"
          >
            {{ savingAddress ? 'Enregistrement…' : 'Ajouter l\'adresse' }}
          </button>
        </form>

        <!-- Aucune adresse -->
        <div v-if="addresses.length === 0" class="text-center py-10">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-cream-dark mb-3">
            <span class="text-3xl">📍</span>
          </div>
          <p class="text-chocolate-soft font-medium">Aucune adresse enregistrée.</p>
          <p class="text-xs text-muted italic mt-1">Ajoutez une adresse pour vos livraisons.</p>
        </div>

        <!-- Liste des adresses -->
        <ul v-else class="space-y-3">
          <li
            v-for="addr in addresses"
            :key="addr.id"
            class="relative rounded-xl p-4 border-2 transition-colors"
            :class="addr.is_default
              ? 'bg-caramel/5 border-caramel'
              : 'bg-cream border-transparent'"
          >
            <!-- Header adresse -->
            <div class="flex items-start justify-between gap-3 mb-2">
              <div class="flex items-center gap-2 flex-wrap min-w-0">
                <span class="text-base shrink-0">{{ addr.label ? '🏠' : '📍' }}</span>
                <strong class="text-sm font-bold text-chocolate truncate">{{ addr.label || 'Adresse' }}</strong>
                <span
                  v-if="addr.is_default"
                  class="px-2 py-0.5 rounded-full bg-caramel text-white text-[10px] font-bold uppercase tracking-wider"
                >
                  Par défaut
                </span>
              </div>
              <button
                @click="deleteAddress(addr)"
                title="Supprimer"
                class="grid place-items-center w-7 h-7 rounded-full text-muted hover:bg-raspberry hover:text-white transition-colors shrink-0"
              >
                ✕
              </button>
            </div>

            <p class="text-sm font-semibold text-chocolate mb-1">{{ addr.recipient_name }}</p>
            <p class="text-sm text-muted">
              {{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span>
            </p>
            <p class="text-sm text-muted">{{ addr.postal_code }} {{ addr.city }}</p>
            <p v-if="addr.phone" class="text-xs text-muted mt-1.5">📞 {{ addr.phone }}</p>

            <button
              v-if="!addr.is_default"
              @click="setDefault(addr)"
              class="mt-3 px-3 py-1.5 rounded-full bg-white border-2 border-caramel text-caramel-dark hover:bg-caramel hover:text-white text-xs font-semibold transition-colors"
            >
              Définir par défaut
            </button>
            <button
              v-else
              @click="unsetDefault(addr)"
              class="mt-3 px-3 py-1.5 rounded-full bg-transparent border-2 border-cream-dark text-muted hover:bg-white hover:text-chocolate text-xs font-semibold transition-colors"
            >
              Retirer le défaut
            </button>
          </li>
        </ul>

        <!-- Info fallback -->
        <div
          v-if="addresses.length > 0 && !hasDefault"
          class="mt-4 p-3 rounded-xl bg-caramel/8 text-caramel-dark text-xs leading-relaxed"
        >
          ℹ️ Aucune adresse par défaut : la livraison utilisera les coordonnées de votre compte.
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const profile = reactive({ name: '', email: '', phone: '' });
const savingProfile = ref(false);
const profileError = ref(null);
const profileSuccess = ref(false);

const addresses = ref([]);
const showAddressForm = ref(false);
const savingAddress = ref(false);
const addressError = ref(null);
const addressForm = reactive({
    label: '',
    recipient_name: '',
    phone: '',
    street: '',
    street_2: '',
    postal_code: '',
    city: '',
    country: 'FR',
    is_default: false,
});

const hasDefault = computed(() => addresses.value.some((a) => a.is_default));

// --- Autocomplétion d'adresse ---
const suggestions = ref([]);
const showSuggestions = ref(false);
let debounceTimer = null;

function hideSuggestions() {
    window.setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
}

function onAddressInput() {
    clearTimeout(debounceTimer);
    const query = addressForm.street;

    if (!query || query.length < 3) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    debounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(
                `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5&citycode=13055`
            );
            const data = await res.json();
            suggestions.value = data.features.map((f) => ({
                label: f.properties.label,
                street: f.properties.name,
                postalCode: f.properties.postcode,
                city: f.properties.city,
            }));
            showSuggestions.value = true;
        } catch {
            suggestions.value = [];
        }
    }, 300);
}

function selectSuggestion(s) {
    addressForm.street = s.street;
    addressForm.postal_code = s.postalCode;
    addressForm.city = s.city;
    suggestions.value = [];
    showSuggestions.value = false;
}

// --- Chargement / sauvegarde ---
async function loadProfile() {
    if (auth.user) {
        profile.name = auth.user.name;
        profile.email = auth.user.email;
        profile.phone = auth.user.phone || '';
    }
}

async function loadAddresses() {
    const { data } = await axios.get('/api/user/addresses');
    addresses.value = data;
}

async function saveProfile() {
    savingProfile.value = true;
    profileError.value = null;
    profileSuccess.value = false;
    try {
        const { data } = await axios.put('/api/user/profile', profile);
        auth.user = data;
        profileSuccess.value = true;
        setTimeout(() => (profileSuccess.value = false), 3000);
    } catch (e) {
        profileError.value = e.response?.data?.message || 'Erreur.';
    } finally {
        savingProfile.value = false;
    }
}

async function saveAddress() {
    savingAddress.value = true;
    addressError.value = null;
    try {
        await axios.post('/api/user/addresses', addressForm);
        Object.assign(addressForm, {
            label: '', recipient_name: '', phone: '', street: '', street_2: '',
            postal_code: '', city: '', country: 'FR', is_default: false,
        });
        showAddressForm.value = false;
        await loadAddresses();
    } catch (e) {
        addressError.value = e.response?.data?.message || 'Erreur.';
    } finally {
        savingAddress.value = false;
    }
}

async function setDefault(addr) {
    await axios.put(`/api/user/addresses/${addr.id}`, { ...addr, is_default: true });
    await loadAddresses();
}

async function unsetDefault(addr) {
    await axios.put(`/api/user/addresses/${addr.id}`, { ...addr, is_default: false });
    await loadAddresses();
}

async function deleteAddress(addr) {
    if (!confirm('Supprimer cette adresse ?')) return;
    await axios.delete(`/api/user/addresses/${addr.id}`);
    await loadAddresses();
}

onMounted(async () => {
    if (!auth.user) await auth.fetchUser();
    await loadProfile();
    if (auth.isAuthenticated) await loadAddresses();
});
</script>