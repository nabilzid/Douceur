<!-- Page formulaire evenement plus succes damande envoyée -->
<template>
    <div class="mx-auto max-w-3xl">
        <!-- En-tête -->
        <div class="mb-8 md:mb-10">
            <RouterLink
                to="/evenements"
                class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-muted transition-colors hover:text-caramel-dark"
            >
                ← Retour aux formules
            </RouterLink>

            <h1 class="font-display text-3xl font-bold text-chocolate md:text-4xl">
                Demande sur mesure
            </h1>

            <p v-if="pkg" class="mt-2 text-sm text-muted">
                Formule :
                <strong class="text-caramel-dark">{{ pkg.name }}</strong>
            </p>

            <p v-if="!success" class="mt-3 max-w-2xl text-sm leading-6 text-muted">
                Décrivez votre événement et vos envies. Nous préparerons une proposition
                personnalisée adaptée à votre projet.
            </p>
        </div>

        <!-- Chargement de la formule -->
        <div
            v-if="loadingPackage"
            class="rounded-2xl bg-white p-8 text-center shadow-soft"
        >
            <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-cream-dark border-t-caramel"></div>
            <p class="mt-4 text-sm text-muted">Chargement de votre formule…</p>
        </div>

        <!-- Confirmation -->
        <div
            v-else-if="success"
            class="mx-auto mt-8 max-w-lg rounded-2xl bg-white p-8 text-center shadow-card animate-fade-up md:p-12"
        >
            <div class="mb-5 inline-flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                <span class="text-5xl">🎉</span>
            </div>

            <h2 class="mb-3 font-display text-2xl font-bold text-chocolate">
                Demande envoyée !
            </h2>

            <p class="mb-2 text-chocolate-soft">
                Référence :
                <strong class="text-caramel-dark">{{ success.reference }}</strong>
            </p>

            <p class="mb-1 text-sm text-muted">
                Mode de récupération :
                <strong class="text-chocolate-soft">
                    {{ success.delivery_type === 'delivery' ? 'Livraison' : 'Click & Collect' }}
                </strong>
            </p>

            <p class="mb-6 text-sm text-muted">
                Notre équipe reviendra vers vous sous 48 h.
            </p>

            <RouterLink
                to="/evenements"
                class="inline-flex items-center gap-2 rounded-full bg-caramel px-6 py-3 text-sm font-semibold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card"
            >
                Retour aux formules →
            </RouterLink>
        </div>

        <!-- Formulaire -->
        <form
            v-else-if="pkg"
            class="space-y-4 md:space-y-5"
            @submit.prevent="submit"
        >
            <!-- Coordonnées -->
            <section class="rounded-2xl bg-white p-5 shadow-soft md:p-7">
                <div class="mb-5 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-caramel/10">
                        <span class="text-xl">👤</span>
                    </div>
                    <h2 class="font-display text-lg font-bold text-chocolate">
                        Vos coordonnées
                    </h2>
                </div>

                <div
                    v-if="auth.isAuthenticated"
                    class="rounded-xl bg-cream p-4"
                >
                    <p class="text-sm text-chocolate-soft">
                        Connecté en tant que
                        <strong class="text-chocolate">{{ auth.user.name }}</strong>
                    </p>
                    <p class="mt-1 text-xs text-muted">
                        Le devis sera envoyé à
                        <strong>{{ auth.user.email }}</strong>.
                    </p>
                    <RouterLink
                        to="/mon-compte"
                        class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-caramel-dark transition-colors hover:text-caramel"
                    >
                        Modifier mes informations →
                    </RouterLink>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Nom complet <span class="text-raspberry">*</span>
                        </label>
                        <input
                            v-model="form.customer_name"
                            type="text"
                            autocomplete="name"
                            required
                            placeholder="Votre nom et prénom"
                            class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Email <span class="text-raspberry">*</span>
                        </label>
                        <input
                            v-model="form.customer_email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="vous@exemple.fr"
                            class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Téléphone <span class="text-raspberry">*</span>
                        </label>
                        <input
                            v-model="form.customer_phone"
                            type="tel"
                            autocomplete="tel"
                            required
                            placeholder="06 00 00 00 00"
                            class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        />
                    </div>
                </div>
            </section>

            <!-- Informations sur l'événement -->
            <section class="rounded-2xl bg-white p-5 shadow-soft md:p-7">
                <div class="mb-5 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-caramel/10">
                        <span class="text-xl">🎉</span>
                    </div>
                    <h2 class="font-display text-lg font-bold text-chocolate">
                        Votre événement
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Type d'événement <span class="text-raspberry">*</span>
                        </label>
                        <select
                            v-model="form.event_type"
                            required
                            class="w-full cursor-pointer rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        >
                            <option value="birthday">Anniversaire</option>
                            <option value="wedding">Mariage</option>
                            <option value="baptism">Baptême</option>
                            <option value="corporate">Entreprise</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Date <span class="text-raspberry">*</span>
                        </label>
                        <input
                            v-model="form.event_date"
                            type="date"
                            :min="minDate"
                            required
                            class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Nombre de personnes
                        </label>
                        <input
                            v-model.number="form.guests_count"
                            type="number"
                            min="1"
                            placeholder="Ex. : 30"
                            class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        />
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                        Description de votre projet
                        <span class="text-raspberry">*</span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        required
                        placeholder="Décrivez le gâteau, les saveurs, les couleurs, la décoration…"
                        class="w-full resize-y rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                    ></textarea>
                    <p class="mt-1.5 text-xs text-muted">
                        Plus votre description est précise, mieux nous pourrons répondre à vos envies.
                    </p>
                </div>
            </section>

            <!-- Mode de récupération -->
            <section class="rounded-2xl bg-white p-5 shadow-soft md:p-7">
                <div class="mb-2 flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-caramel/10">
                        <span class="text-xl">🚚</span>
                    </div>
                    <h2 class="font-display text-lg font-bold text-chocolate">
                        Mode de récupération
                    </h2>
                </div>

                <p class="mb-4 text-sm text-muted">
                    Comment souhaitez-vous recevoir votre commande ?
                </p>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <button
                        type="button"
                        :aria-pressed="form.delivery_type === 'delivery'"
                        @click="form.delivery_type = 'delivery'"
                        class="flex items-center justify-center gap-2 rounded-xl border-2 px-4 py-3 text-sm font-semibold transition-all"
                        :class="form.delivery_type === 'delivery'
                            ? 'border-caramel bg-caramel text-white shadow-card'
                            : 'border-cream-dark bg-cream text-chocolate-soft hover:border-caramel hover:bg-white hover:text-caramel-dark'"
                    >
                        🚚 Livraison
                    </button>

                    <button
                        type="button"
                        :aria-pressed="form.delivery_type === 'pickup'"
                        @click="form.delivery_type = 'pickup'"
                        class="flex items-center justify-center gap-2 rounded-xl border-2 px-4 py-3 text-sm font-semibold transition-all"
                        :class="form.delivery_type === 'pickup'
                            ? 'border-caramel bg-caramel text-white shadow-card'
                            : 'border-cream-dark bg-cream text-chocolate-soft hover:border-caramel hover:bg-white hover:text-caramel-dark'"
                    >
                        🏪 Click & Collect
                    </button>
                </div>

                <!-- Adresse de livraison -->
                <div
                    v-if="form.delivery_type === 'delivery'"
                    class="mt-6 border-t border-cream-dark pt-6"
                >
                    <h3 class="mb-4 font-display text-base font-bold text-chocolate">
                        Adresse de livraison
                    </h3>

                    <!-- Adresses enregistrées -->
                    <div
                        v-if="auth.isAuthenticated && addresses.length > 0"
                        class="space-y-2.5"
                    >
                        <label
                            v-for="addr in addresses"
                            :key="addr.id"
                            class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition-all"
                            :class="selectedAddressId === addr.id
                                ? 'border-caramel bg-caramel/5'
                                : 'border-cream-dark bg-cream hover:border-caramel-light hover:bg-white'"
                        >
                            <input
                                v-model="selectedAddressId"
                                type="radio"
                                :value="addr.id"
                                @change="selectAddress(addr)"
                                class="mt-1 h-4 w-4 shrink-0 cursor-pointer accent-caramel"
                            />

                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex flex-wrap items-center gap-2">
                                    <strong class="text-sm text-chocolate">
                                        {{ addr.label || 'Adresse' }}
                                    </strong>
                                    <span
                                        v-if="addr.is_default"
                                        class="rounded-full bg-caramel px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white"
                                    >
                                        Par défaut
                                    </span>
                                </div>

                                <p class="text-sm font-medium text-chocolate-soft">
                                    {{ addr.recipient_name }}
                                </p>
                                <p class="mt-0.5 text-xs leading-relaxed text-muted">
                                    {{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span>
                                    <br />
                                    {{ addr.postal_code }} {{ addr.city }}
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Adresse saisie manuellement -->
                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                                Adresse <span class="text-raspberry">*</span>
                            </label>
                            <input
                                v-model="form.delivery_address"
                                type="text"
                                autocomplete="street-address"
                                placeholder="Numéro et rue"
                                required
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                                Code postal <span class="text-raspberry">*</span>
                            </label>
                            <input
                                v-model="form.delivery_postal_code"
                                type="text"
                                autocomplete="postal-code"
                                maxlength="10"
                                placeholder="75002"
                                required
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                                Ville <span class="text-raspberry">*</span>
                            </label>
                            <input
                                v-model="form.delivery_city"
                                type="text"
                                autocomplete="address-level2"
                                placeholder="Votre ville"
                                required
                                class="w-full rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                            />
                        </div>
                    </div>

                    <!-- Instructions de livraison -->
                    <div
                        v-if="!(auth.isAuthenticated && addresses.length > 0)"
                        class="mt-4"
                    >
                        <label class="mb-1.5 block text-sm font-medium text-chocolate-soft">
                            Instructions de livraison
                            <span class="text-xs text-muted">(optionnel)</span>
                        </label>
                        <textarea
                            v-model="form.delivery_notes"
                            rows="3"
                            placeholder="Code d'entrée, étage, digicode…"
                            class="w-full resize-y rounded-xl border-2 border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate placeholder:text-muted/60 transition-colors focus:border-caramel focus:bg-white focus:outline-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Retrait -->
                <p
                    v-else
                    class="mt-4 rounded-xl bg-cream p-3 text-xs italic text-muted"
                >
                    Vous récupérerez votre commande directement chez le pâtissier.
                </p>
            </section>

            <!-- Erreur -->
            <div
                v-if="error"
                role="alert"
                class="flex items-start gap-2 rounded-xl bg-raspberry/10 p-4 text-sm font-medium text-raspberry"
            >
                <span>⚠️</span>
                <span>{{ error }}</span>
            </div>

            <!-- Envoi -->
            <div class="pt-1">
                <button
                    type="submit"
                    :disabled="submitting || loadingPackage || !pkg"
                    class="w-full rounded-full bg-caramel px-6 py-3.5 text-sm font-semibold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card disabled:transform-none disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ submitting ? 'Envoi en cours…' : 'Envoyer ma demande' }}
                </button>

                <p class="mt-3 text-center text-xs text-muted">
                    Votre demande sera étudiée par notre équipe.
                </p>
            </div>
        </form>

        <!-- Formule introuvable ou erreur de chargement -->
        <div
            v-if="!loadingPackage && !pkg && !success"
            class="rounded-2xl bg-white p-8 text-center shadow-soft"
        >
            <span class="text-4xl">🍰</span>
            <h2 class="mt-4 font-display text-xl font-bold text-chocolate">
                Formule indisponible
            </h2>
            <p class="mt-2 text-sm text-muted">
                {{ error || 'Cette formule est introuvable pour le moment.' }}
            </p>
            <RouterLink
                to="/evenements"
                class="mt-5 inline-flex rounded-full bg-caramel px-5 py-3 text-sm font-semibold text-white transition hover:bg-caramel-dark"
            >
                Retour aux formules →
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const route = useRoute();

const pkg = ref(null);
const loadingPackage = ref(true);
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

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
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
    } catch (e) {
        console.error('Erreur lors du chargement des adresses :', e);
        addresses.value = [];
    }
}

async function submit() {
    if (!pkg.value || submitting.value) return;

    submitting.value = true;
    error.value = null;

    try {
        const payload = {
            ...form,
            merchant_id: pkg.value.merchant_id,
        };

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

        // Attendre l'affichage de la confirmation, puis remonter en haut
        await nextTick();

        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    } catch (e) {
        error.value =
            e.response?.data?.message ||
            'Une erreur est survenue lors de l’envoi de votre demande.';
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    loadingPackage.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('/api/event-packages');
        pkg.value = data.find((p) => p.slug === route.params.slug) || null;

        if (!pkg.value) {
            error.value = 'Cette formule événementielle est introuvable.';
            return;
        }

        if (auth.isAuthenticated) {
            await loadAddresses();
        }
    } catch (e) {
        console.error('Erreur lors du chargement de la formule :', e);
        error.value = 'Impossible de charger cette formule. Veuillez réessayer.';
    } finally {
        loadingPackage.value = false;
    }
});
</script>
