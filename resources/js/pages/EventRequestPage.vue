<template>
    <div class="request-page">
        <header class="page-header">
            <RouterLink to="/evenements" class="back-link">← Retour aux formules</RouterLink>
            <h1>Demande sur mesure</h1>
            <p v-if="pkg">Formule : <strong>{{ pkg.name }}</strong></p>
        </header>

        <div v-if="success" class="success-card fade-in-up">
            <span class="success-emoji">🎉</span>
            <h2>Demande envoyée !</h2>
            <p>Référence : <strong>{{ success.reference }}</strong></p>
            <p class="muted">
                Mode de récupération :
                <strong>{{ success.delivery_type === 'delivery' ? 'Livraison' : 'Click & Collect' }}</strong>
            </p>
            <p class="muted">Le pâtissier vous répondra sous 48h.</p>
            <RouterLink to="/evenements" class="btn-primary">Retour aux formules →</RouterLink>
        </div>

        <form v-else class="form-card" @submit.prevent="submit">
            <section class="form-section">
                <h2>Vos coordonnées</h2>

                <div v-if="auth.isAuthenticated" class="user-summary">
                    <p>Connecté en tant que <strong>{{ auth.user.name }}</strong></p>
                    <p class="hint">
                        Le devis sera envoyé à <strong>{{ auth.user.email }}</strong>.
                    </p>
                    <RouterLink to="/mon-compte" class="edit-link">Modifier mes informations →</RouterLink>
                </div>

                <div v-else class="form-grid">
                    <label>Nom complet<input v-model="form.customer_name" required /></label>
                    <label>Email<input v-model="form.customer_email" type="email" required /></label>
                    <label>Téléphone<input v-model="form.customer_phone" type="tel" required /></label>
                </div>
            </section>

            <section class="form-section">
                <h2>Votre événement</h2>
                <div class="form-grid">
                    <label>Type
                        <select v-model="form.event_type" required>
                            <option value="birthday">Anniversaire</option>
                            <option value="wedding">Mariage</option>
                            <option value="baptism">Baptême</option>
                            <option value="corporate">Entreprise</option>
                            <option value="other">Autre</option>
                        </select>
                    </label>
                    <label>Date<input v-model="form.event_date" type="date" :min="minDate" required /></label>
                    <label>Nombre de personnes<input v-model.number="form.guests_count" type="number" min="1" /></label>
                </div>
                <div class="form-grid">
                    <label class="full">Description de votre projet
                        <textarea v-model="form.description" rows="5" required placeholder="Décrivez le gâteau, les saveurs, les couleurs, la décoration…"></textarea>
                    </label>
                </div>
            </section>

            <!-- ✅ Mode de récupération -->
            <section class="form-section">
                <h2>Mode de récupération</h2>
                <p class="section-hint">Comment souhaitez-vous recevoir votre commande ?</p>

                <div class="delivery-toggle">
                    <button
                        type="button"
                        :class="['toggle-btn', { active: form.delivery_type === 'delivery' }]"
                        @click="form.delivery_type = 'delivery'"
                    >
                        🚚 Livraison
                    </button>
                    <button
                        type="button"
                        :class="['toggle-btn', { active: form.delivery_type === 'pickup' }]"
                        @click="form.delivery_type = 'pickup'"
                    >
                        🏪 Click & Collect
                    </button>
                </div>

                <div v-if="form.delivery_type === 'delivery'">
                    <h2>Adresse de livraison</h2>

                    <!-- ✅ Client connecté avec adresses : même design que CheckoutPage -->
                    <div v-if="auth.isAuthenticated && addresses.length > 0" class="address-selector">
                        <label
                            v-for="addr in addresses"
                            :key="addr.id"
                            :class="['address-option', { selected: selectedAddressId === addr.id }]"
                        >
                            <input
                                type="radio"
                                :value="addr.id"
                                v-model="selectedAddressId"
                                @change="selectAddress(addr)"
                            />
                            <div class="address-option-body">
                                <div class="address-option-head">
                                    <strong>{{ addr.label || 'Adresse' }}</strong>
                                    <span v-if="addr.is_default" class="default-tag">Par défaut</span>
                                </div>
                                <p>{{ addr.recipient_name }}</p>
                                <p>{{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span></p>
                                <p>{{ addr.postal_code }} {{ addr.city }}</p>
                            </div>
                        </label>
                    </div>

                    <!-- ✅ Invité ou connecté sans adresse : formulaire manuel identique -->
                    <div v-else class="form-grid">
                        <label class="full">
                            Adresse
                            <input v-model="form.delivery_address" type="text" placeholder="Numéro et rue" required />
                        </label>
                        <label>
                            Code postal
                            <input v-model="form.delivery_postal_code" type="text" maxlength="10" placeholder="75002" required />
                        </label>
                        <label>
                            Ville
                            <input v-model="form.delivery_city" type="text" placeholder="Paris" required />
                        </label>
                    </div>

                    <!-- ✅ Instructions affichées uniquement si saisie manuelle d'adresse -->
                    <div v-if="!(auth.isAuthenticated && addresses.length > 0)" class="form-grid">
                        <label class="full">
                            Instructions de livraison (optionnel)
                            <textarea
                                v-model="form.delivery_notes"
                                rows="3"
                                placeholder="Code d'entrée, étage, digicode…"
                            ></textarea>
                        </label>
                    </div>
                </div>

                <p v-else class="fulfillment-note">
                    Vous récupérerez votre commande directement chez le pâtissier.
                </p>
            </section>

            <p v-if="error" class="error">{{ error }}</p>
            <button type="submit" class="btn-primary submit-btn" :disabled="submitting">
                {{ submitting ? 'Envoi…' : 'Envoyer ma demande' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const route = useRoute();
const pkg = ref(null);
const submitting = ref(false);
const error = ref(null);
const success = ref(null);

const addresses = ref([]);
const selectedAddressId = ref(null);

const form = reactive({
    merchant_id: null,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    event_type: 'birthday',
    event_date: '',
    guests_count: null,
    budget: null,
    description: '',
    delivery_type: 'pickup',
    delivery_address: '',
    delivery_postal_code: '',
    delivery_city: '',
    delivery_notes: '',
});

const minDate = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() + 3);
    return d.toISOString().slice(0, 10);
});

function selectAddress(addr) {
    selectedAddressId.value = addr.id;
    form.delivery_address = addr.street + (addr.street_2 ? ', ' + addr.street_2 : '');
    form.delivery_postal_code = addr.postal_code;
    form.delivery_city = addr.city;
}

async function loadAddresses() {
    try {
        const { data } = await axios.get('/api/user/addresses');
        addresses.value = data;

        const defaultAddr = data.find((a) => a.is_default) || data[0];
        if (defaultAddr) {
            selectAddress(defaultAddr);
        }
    } catch {
        addresses.value = [];
    }
}

async function submit() {
    submitting.value = true;
    error.value = null;
    try {
        const payload = { ...form, merchant_id: pkg.value.merchant_id };

        if (auth.isAuthenticated) {
            payload.customer_name = auth.user.name;
            payload.customer_email = auth.user.email;
            payload.customer_phone = auth.user.phone || 'non renseigné';
        }

        if (form.delivery_type === 'pickup') {
            delete payload.delivery_address;
            delete payload.delivery_postal_code;
            delete payload.delivery_city;
            delete payload.delivery_notes;
        }

        const { data } = await axios.post('/api/custom-orders', payload);
        success.value = data.custom_order;
    } catch (e) {
        error.value = e.response?.data?.message || 'Erreur lors de l’envoi.';
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    const { data } = await axios.get(`/api/event-packages`);
    pkg.value = data.find((p) => p.slug === route.params.slug) || null;

    if (auth.isAuthenticated) {
        await loadAddresses();
    }
});
</script>

<style scoped>

.request-page { max-width: 800px; margin: 0 auto; padding: 1rem 0 4rem; }
.page-header { margin-bottom: 2.5rem; }
.back-link { color: var(--text-muted); font-size: 0.9rem; display: inline-block; margin-bottom: 1rem; }
.page-header h1 { font-size: 2.25rem; margin-bottom: 0.5rem; }

.form-card {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ✅ Form-section identique à CheckoutPage */
.form-section {
    background: white;
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow-sm);
}
.form-section h2 { font-size: 1.2rem; margin-bottom: 1.25rem; }

/* ✅ form-grid identique à CheckoutPage */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-grid label {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 500;
}
.form-grid label.full { grid-column: 1 / -1; }

/* ✅ Inputs identiques à CheckoutPage */
input, select, textarea {
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 0.95rem;
    background: var(--cream);
    color: var(--text-dark);
    transition: border-color 0.2s;
}
input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: var(--caramel);
    background: white;
}
textarea { resize: vertical; }

label.full { margin-top: 1rem; }

.section-hint { font-size: 0.9rem; color: var(--text-muted); margin: 0 0 1rem; }

/* Toggle livraison / click & collect */
.delivery-toggle {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.toggle-btn {
    flex: 1;
    padding: 0.9rem 1rem;
    border-radius: var(--radius-sm);
    border: 2px solid var(--cream-dark);
    background: var(--cream);
    color: var(--chocolate);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
}
.toggle-btn:hover {
    border-color: var(--caramel);
    color: var(--caramel-dark);
    background: white;
}
.toggle-btn.active {
    background: var(--caramel);
    border-color: var(--caramel);
    color: white;
    box-shadow: 0 4px 12px rgba(201, 123, 63, 0.3);
}

/* ✅ Sélecteur d'adresses — identique à CheckoutPage */
.address-selector {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.address-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    background: var(--cream);
    cursor: pointer;
    transition: all 0.2s;
}

.address-option:hover {
    border-color: var(--caramel-light);
    background: white;
}

.address-option.selected {
    border-color: var(--caramel);
    background: rgba(201, 123, 63, 0.05);
}

.address-option input[type="radio"] {
    margin-top: 0.2rem;
    width: 18px;
    height: 18px;
    accent-color: var(--caramel);
    cursor: pointer;
    flex-shrink: 0;
}

.address-option-body {
    flex: 1;
}

.address-option-head {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.address-option-head strong {
    color: var(--chocolate);
    font-size: 0.95rem;
}

.address-option-body p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.default-tag {
    background: var(--caramel);
    color: white;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.fulfillment-note {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-style: italic;
    margin: 0;
}

/* User summary identique à CheckoutPage */
.user-summary {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
}
.user-summary p { margin: 0 0 0.5rem; font-size: 0.95rem; }
.user-summary .hint { color: var(--text-muted); font-size: 0.85rem; }
.edit-link { font-size: 0.85rem; color: var(--caramel); }

.submit-btn { width: 100%; justify-content: center; }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.error { color: var(--raspberry); font-weight: 500; margin-top: 1rem; }

/* Carte succès identique à CheckoutPage */
.success-card {
    max-width: 600px;
    margin: 4rem auto;
    text-align: center;
    background: white;
    padding: 3rem 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
}
.success-emoji { font-size: 4rem; display: block; margin-bottom: 1rem; }
.success-card h2 { margin-bottom: 1rem; }
.success-card p { margin-bottom: 1rem; color: var(--text-muted); }
.muted { font-size: 0.85rem; }

@media (max-width: 900px) {
    .form-grid { grid-template-columns: 1fr; }
    .page-header h1 { font-size: 2rem; }
}
@media (max-width: 480px) {
    .form-section { padding: 1.25rem; }
    .delivery-toggle { flex-direction: column; }
}
</style>