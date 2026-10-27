<template>
    <div class="account-page">
        <h1>Mon compte</h1>

        <div v-if="!auth.isAuthenticated" class="state">
            <p>Vous devez être connecté pour accéder à cette page.</p>
            <RouterLink to="/connexion" class="btn-primary">Se connecter</RouterLink>
        </div>

        <div v-else class="account-layout">
            <!-- Informations personnelles -->
            <section class="card">
                <h2>Informations personnelles</h2>
                <form @submit.prevent="saveProfile">
                    <label>Nom<input v-model="profile.name" required /></label>
                    <label>Email<input v-model="profile.email" type="email" required /></label>
                    <label>Téléphone<input v-model="profile.phone" type="tel" /></label>
                    <p v-if="profileError" class="error">{{ profileError }}</p>
                    <p v-if="profileSuccess" class="success">✓ Profil mis à jour.</p>
                    <button type="submit" class="btn-primary" :disabled="savingProfile">
                        {{ savingProfile ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </form>
            </section>

            <!-- Adresses -->
            <section class="card">
                <div class="card-head">
                    <h2>Mes adresses</h2>
                    <button class="btn-secondary small" @click="showAddressForm = !showAddressForm">
                        {{ showAddressForm ? 'Annuler' : '+ Ajouter' }}
                    </button>
                </div>

                <form v-if="showAddressForm" class="address-form" @submit.prevent="saveAddress">
                    <label>Libellé (optionnel)<input v-model="addressForm.label" placeholder="Domicile, Bureau…" /></label>
                    <label>Destinataire<input v-model="addressForm.recipient_name" required /></label>
                    <label>Téléphone<input v-model="addressForm.phone" type="tel" /></label>

                    <label class="address-label">
                        Adresse
                        <div class="autocomplete">
                            <input
                                v-model="addressForm.street"
                                required
                                autocomplete="off"
                                @input="onAddressInput"
                                @focus="showSuggestions = suggestions.length > 0"
                                @blur="hideSuggestions"
                            />
                            <ul v-if="showSuggestions && suggestions.length > 0" class="suggestions">
                                <li
                                    v-for="(s, i) in suggestions"
                                    :key="i"
                                    @mousedown.prevent="selectSuggestion(s)"
                                >
                                    {{ s.label }}
                                </li>
                            </ul>
                        </div>
                    </label>

                    <label>Complément<input v-model="addressForm.street_2" /></label>
                    <div class="grid-2">
                        <label>Code postal<input v-model="addressForm.postal_code" required /></label>
                        <label>Ville<input v-model="addressForm.city" required /></label>
                    </div>
                    <label class="checkbox">
                        <input v-model="addressForm.is_default" type="checkbox" />
                        Définir comme adresse de livraison par défaut
                    </label>
                    <p v-if="addressError" class="error">{{ addressError }}</p>
                    <button type="submit" class="btn-primary" :disabled="savingAddress">
                        {{ savingAddress ? 'Enregistrement…' : 'Ajouter l’adresse' }}
                    </button>
                </form>

                <div v-if="addresses.length === 0" class="empty">
                    <span class="empty-emoji">📍</span>
                    <p>Aucune adresse enregistrée.</p>
                    <p class="hint">Ajoutez une adresse pour vos livraisons.</p>
                </div>

                <ul v-else class="addresses">
                    <li v-for="addr in addresses" :key="addr.id" class="address-card">
                        <div class="address-header">
                            <div class="address-title">
                                <span class="address-icon">{{ addr.label ? '🏠' : '📍' }}</span>
                                <strong>{{ addr.label || 'Adresse' }}</strong>
                                <span v-if="addr.is_default" class="default-badge">Par défaut</span>
                            </div>
                            <button class="remove" @click="deleteAddress(addr)" title="Supprimer">✕</button>
                        </div>
                        <p class="address-recipient">{{ addr.recipient_name }}</p>
                        <p class="address-line">{{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span></p>
                        <p class="address-line">{{ addr.postal_code }} {{ addr.city }}</p>
                        <p v-if="addr.phone" class="address-phone">📞 {{ addr.phone }}</p>
                        <button
                            v-if="!addr.is_default"
                            class="btn-default"
                            @click="setDefault(addr)"
                        >
                            Définir par défaut
                        </button>
                        <button
                            v-else
                            class="btn-unset"
                            @click="unsetDefault(addr)"
                        >
                            Retirer le défaut
                        </button>
                    </li>
                </ul>

                <div v-if="addresses.length > 0 && !hasDefault" class="info-fallback">
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

<style scoped>
.address-form {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--cream-dark);
}

.address-form .btn-primary {
    margin-top: 0.5rem;
}

.address-form .address-label {
    grid-column: 1 / -1;
    width: 100%;
}

.address-form .address-label .autocomplete,
.address-form .address-label input {
    width: 100%;
}

.account-page { padding: 1rem 0 4rem; }
.account-page h1 { font-size: 2.5rem; margin-bottom: 2rem; }
.account-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start; }
.card { background: white; border-radius: var(--radius); padding: 1.75rem; box-shadow: var(--shadow-sm); }
.card h2 { font-size: 1.3rem; margin-bottom: 1.25rem; }
.card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.card-head h2 { margin: 0; }
form { display: flex; flex-direction: column; gap: 1rem; }
label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
label.checkbox {
    flex-direction: row;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    background: var(--cream);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--chocolate-soft);
    transition: background 0.2s;
}

label.checkbox:hover {
    background: var(--cream-dark);
}

label.checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--caramel);
    cursor: pointer;
    flex-shrink: 0;
}
input { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); }
input:focus { outline: none; border-color: var(--caramel); background: white; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.btn-secondary.small { padding: 0.5rem 1rem; font-size: 0.85rem; }
.btn-secondary { background: white; color: var(--caramel); border: 1.5px solid var(--caramel); padding: 0.6rem 1.2rem; border-radius: 999px; font-weight: 600; }

/* Cartes d'adresses */
.addresses { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.address-card {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 1.25rem;
    border: 1.5px solid transparent;
    transition: all 0.2s;
}
.address-card:has(.default-badge) {
    border-color: var(--caramel);
    background: rgba(201, 123, 63, 0.05);
}
.address-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}
.address-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.address-icon { font-size: 1.1rem; }
.address-title strong { font-size: 1rem; color: var(--chocolate); }
.default-badge {
    background: var(--caramel);
    color: white;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.address-recipient { font-weight: 600; color: var(--chocolate); margin: 0 0 0.25rem; font-size: 0.95rem; }
.address-line { margin: 0; font-size: 0.9rem; color: var(--text-muted); }
.address-phone { margin: 0.5rem 0 0; font-size: 0.85rem; color: var(--text-muted); }
.remove {
    color: var(--text-muted);
    font-size: 0.9rem;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    transition: all 0.2s;
    flex-shrink: 0;
}
.remove:hover { background: var(--raspberry); color: white; }
.btn-default, .btn-unset {
    margin-top: 0.85rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-default {
    background: white;
    color: var(--caramel-dark);
    border: 1.5px solid var(--caramel);
}
.btn-default:hover { background: var(--caramel); color: white; }
.btn-unset {
    background: transparent;
    color: var(--text-muted);
    border: 1.5px solid var(--cream-dark);
}
.btn-unset:hover { background: white; color: var(--chocolate); }

.empty { text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); }
.empty-emoji { font-size: 2.5rem; display: block; margin-bottom: 0.75rem; }
.empty .hint { font-size: 0.85rem; font-style: italic; margin-top: 0.25rem; }
.info-fallback {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: rgba(201, 123, 63, 0.08);
    color: var(--caramel-dark);
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
}

.error { color: var(--raspberry); font-size: 0.85rem; }
.success { color: #059669; font-size: 0.85rem; font-weight: 600; }

.autocomplete { position: relative; }
.suggestions {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: white;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
    list-style: none;
    padding: 0.35rem 0;
    margin: 0;
    z-index: 10;
    max-height: 260px;
    overflow-y: auto;
}

.suggestions li {
    padding: 0.7rem 1rem;
    font-size: 0.9rem;
    color: var(--chocolate-soft);
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    border-left: 3px solid transparent;
}

.suggestions li:hover {
    background: var(--cream);
    color: var(--caramel-dark);
    border-left-color: var(--caramel);
}

.suggestions li:active {
    background: rgba(201, 123, 63, 0.1);
}

@media (max-width: 900px) {
    .account-layout { grid-template-columns: 1fr; }
    .grid-2 { grid-template-columns: 1fr; }
}
</style>