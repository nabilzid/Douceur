<!-- Page finaliser une commande invité -->
<template>
    <main class="mx-auto min-h-screen max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <header class="mb-8 sm:mb-10">
            <RouterLink
                to="/panier"
                class="mb-5 inline-flex items-center gap-2 text-sm text-muted transition hover:text-caramel-dark"
            >
                <span>←</span>
                Retour au panier
            </RouterLink>

            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-caramel-dark">
                Douceur · Votre commande
            </p>
            <h1 class="font-serif text-3xl font-bold text-chocolate sm:text-4xl">
                Finaliser la commande
            </h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-muted sm:text-base">
                Quelques informations et vos pâtisseries seront bientôt prêtes.
            </p>
        </header>

        <!-- Commande confirmée -->
        <section
            v-if="success"
            class="mx-auto max-w-2xl rounded-3xl border border-cream-dark bg-white px-6 py-12 text-center shadow-card sm:px-10"
        >
            <div class="mb-5 text-5xl">🎉</div>
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-caramel-dark">
                Merci pour votre commande
            </p>
            <h2 class="font-serif text-3xl font-bold text-chocolate">
                Commande confirmée !
            </h2>
            <p class="mt-4 text-muted">
                Votre commande
                <strong class="text-chocolate">{{ success.reference }}</strong>
                a bien été enregistrée.
            </p>
            <p class="mt-2 text-sm text-muted">
                Un email de confirmation vous sera envoyé.
            </p>
            <RouterLink
                to="/catalogue"
                class="mt-8 inline-flex items-center justify-center rounded-full bg-caramel px-6 py-3 font-semibold text-white transition hover:bg-caramel-dark"
            >
                Continuer mes achats →
            </RouterLink>
        </section>

        <!-- Panier vide -->
        <section
            v-else-if="cart.items.length === 0"
            class="rounded-3xl border border-cream-dark bg-white px-6 py-14 text-center shadow-soft"
        >
            <div class="mb-4 text-5xl">🧺</div>
            <h2 class="font-serif text-2xl font-bold text-chocolate">
                Votre panier est vide
            </h2>
            <p class="mt-3 text-muted">
                Découvrez nos pâtisseries artisanales.
            </p>
            <RouterLink
                to="/catalogue"
                class="mt-6 inline-flex rounded-full bg-caramel px-6 py-3 font-semibold text-white transition hover:bg-caramel-dark"
            >
                Voir le catalogue →
            </RouterLink>
        </section>

        <form
            v-else
            class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1fr)_390px] lg:gap-8"
            @submit.prevent="submit"
        >
            <!-- Formulaire -->
            <div class="min-w-0 space-y-5">
                <!-- Message de synchronisation du stock -->
                <div
                    v-if="stockChanges.length > 0"
                    role="alert"
                    class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 sm:p-5"
                >
                    <p class="font-bold">Votre panier a été mis à jour</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs sm:text-sm">
                        <li v-for="(change, i) in stockChanges" :key="i">
                            <template v-if="change.type === 'removed'">
                                <strong>{{ change.name }}</strong> n'est plus disponible et a été retiré.
                            </template>
                            <template v-else-if="change.type === 'out_of_stock'">
                                <strong>{{ change.name }}</strong> est en rupture de stock et a été retiré.
                            </template>
                            <template v-else-if="change.type === 'reduced'">
                                <strong>{{ change.name }}</strong> : quantité réduite à {{ change.newQty }} (stock limité).
                            </template>
                        </li>
                    </ul>
                    <p class="mt-3 text-xs">
                        Merci de vérifier votre panier avant de confirmer.
                    </p>
                </div>

                <!-- Coordonnées -->
                <section class="rounded-3xl border border-cream-dark bg-white p-5 shadow-soft sm:p-7">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cream text-lg">
                            <span>👤</span>
                        </div>
                        <div>
                            <h2 class="font-serif text-xl font-bold text-chocolate sm:text-2xl">
                                Vos coordonnées
                            </h2>
                            <p class="mt-1 text-sm text-muted">
                                Pour vous contacter au sujet de votre commande.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="auth.isAuthenticated"
                        class="rounded-2xl border border-cream-dark bg-cream p-4"
                    >
                        <p class="text-sm text-chocolate">
                            Connecté en tant que
                            <strong>{{ auth.user.name }}</strong>
                        </p>
                        <p class="mt-2 text-sm text-muted">
                            Les informations de votre compte seront utilisées pour cette commande.
                        </p>
                        <RouterLink
                            to="/mon-compte"
                            class="mt-3 inline-flex text-sm font-semibold text-caramel-dark hover:underline"
                        >
                            Modifier mes informations →
                        </RouterLink>
                    </div>

                    <div v-else class="space-y-4">
                        <div>
                            <label for="checkout-name" class="mb-2 block text-sm font-semibold text-chocolate">
                                Nom complet <span class="text-raspberry">*</span>
                            </label>
                            <input
                                id="checkout-name"
                                v-model.trim="form.name"
                                type="text"
                                autocomplete="name"
                                placeholder="Ex. Camille Martin"
                                required
                                class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                            />
                        </div>

                        <div>
                            <label for="checkout-email" class="mb-2 block text-sm font-semibold text-chocolate">
                                Adresse email <span class="text-raspberry">*</span>
                            </label>
                            <input
                                id="checkout-email"
                                v-model.trim="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="vous@exemple.fr"
                                required
                                class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                            />
                        </div>

                        <div>
                            <label for="checkout-phone" class="mb-2 block text-sm font-semibold text-chocolate">
                                Téléphone <span class="text-raspberry">*</span>
                            </label>
                            <input
                                id="checkout-phone"
                                v-model.trim="form.phone"
                                type="tel"
                                autocomplete="tel"
                                placeholder="06 12 34 56 78"
                                required
                                class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                            />
                        </div>

                        <p class="text-xs leading-5 text-muted">
                            Les champs marqués d’un astérisque sont obligatoires.
                            Vous pouvez commander sans créer de compte.
                        </p>
                    </div>
                </section>

                <!-- Adresse de livraison -->
                <section
                    v-if="cart.isDelivery"
                    class="rounded-3xl border border-cream-dark bg-white p-5 shadow-soft sm:p-7"
                >
                    <div class="mb-6 flex items-start gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cream text-lg">
                            <span>📍</span>
                        </div>
                        <div>
                            <h2 class="font-serif text-xl font-bold text-chocolate sm:text-2xl">
                                Adresse de livraison
                            </h2>
                            <p class="mt-1 text-sm text-muted">
                                Où souhaitez-vous recevoir votre commande ?
                            </p>
                        </div>
                    </div>

                    <div v-if="addresses.length > 0" class="space-y-3">
                        <label
                            v-for="addr in addresses"
                            :key="addr.id"
                            :class="[
                                'flex cursor-pointer gap-3 rounded-2xl border p-4 transition',
                                selectedAddressId === addr.id
                                    ? 'border-caramel bg-cream'
                                    : 'border-cream-dark bg-white hover:border-caramel'
                            ]"
                        >
                            <input
                                v-model="selectedAddressId"
                                type="radio"
                                :value="addr.id"
                                name="saved-address"
                                class="mt-1 size-4 shrink-0 accent-caramel"
                                @change="selectAddress(addr)"
                            />

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-chocolate">
                                        {{ addr.label || 'Adresse' }}
                                    </span>
                                    <span
                                        v-if="addr.is_default"
                                        class="rounded-full bg-caramel px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white"
                                    >
                                        Par défaut
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-muted">
                                    {{ addr.recipient_name }}
                                </p>
                                <p class="text-sm text-muted">
                                    {{ addr.street }}<span v-if="addr.street_2">, {{ addr.street_2 }}</span>
                                </p>
                                <p class="text-sm text-muted">
                                    {{ addr.postal_code }} {{ addr.city }}
                                </p>
                            </div>
                        </label>
                    </div>

                    <div v-else class="space-y-4">
                        <div class="relative">
                            <label for="checkout-address" class="mb-2 block text-sm font-semibold text-chocolate">
                                Adresse <span class="text-raspberry">*</span>
                            </label>
                            <input
                                id="checkout-address"
                                v-model.trim="form.address"
                                type="text"
                                autocomplete="off"
                                placeholder="Numéro et nom de rue"
                                required
                                class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                                @input="onAddressInput"
                                @focus="showSuggestions = suggestions.length > 0"
                                @blur="hideSuggestions"
                            />

                            <!-- Suggestions d'adresses -->
                            <ul
                                v-if="showSuggestions && suggestions.length > 0"
                                class="absolute left-0 right-0 top-full z-20 mt-1 max-h-64 overflow-y-auto rounded-xl border border-cream-dark bg-white py-1 shadow-lg"
                            >
                                <li
                                    v-for="(s, i) in suggestions"
                                    :key="i"
                                    class="cursor-pointer border-l-2 border-transparent px-4 py-2.5 text-sm text-chocolate transition hover:border-caramel hover:bg-cream"
                                    @mousedown.prevent="selectSuggestion(s)"
                                >
                                    {{ s.label }}
                                </li>
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="checkout-postal" class="mb-2 block text-sm font-semibold text-chocolate">
                                    Code postal <span class="text-raspberry">*</span>
                                </label>
                                <input
                                    id="checkout-postal"
                                    v-model.trim="form.postalCode"
                                    type="text"
                                    autocomplete="postal-code"
                                    placeholder="13000"
                                    required
                                    class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                                />
                            </div>
                            <div>
                                <label for="checkout-city" class="mb-2 block text-sm font-semibold text-chocolate">
                                    Ville <span class="text-raspberry">*</span>
                                </label>
                                <input
                                    id="checkout-city"
                                    v-model.trim="form.city"
                                    type="text"
                                    autocomplete="address-level2"
                                    placeholder="Marseille"
                                    required
                                    class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Créneau -->
                <section class="rounded-3xl border border-cream-dark bg-white p-5 shadow-soft sm:p-7">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cream text-lg">
                            <span>🗓️</span>
                        </div>
                        <div>
                            <h2 class="font-serif text-xl font-bold text-chocolate sm:text-2xl">
                                Créneau {{ cart.isDelivery ? 'de livraison' : 'de retrait' }}
                            </h2>
                            <p class="mt-1 text-sm text-muted">
                                Choisissez le moment qui vous convient.
                            </p>
                        </div>
                    </div>

                    <label for="checkout-slot" class="mb-2 block text-sm font-semibold text-chocolate">
                        Créneau disponible
                    </label>
                    <select
                        id="checkout-slot"
                        v-model="form.slotId"
                        class="w-full rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition focus:border-caramel focus:bg-white"
                    >
                        <option :value="null">Choisir un créneau (facultatif)</option>
                        <option v-for="slot in slots" :key="slot.id" :value="slot.id">
                            {{ formatSlot(slot) }}
                        </option>
                    </select>
                </section>

                <!-- Notes -->
                <section class="rounded-3xl border border-cream-dark bg-white p-5 shadow-soft sm:p-7">
                    <div class="mb-5 flex items-start gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cream text-lg">
                            <span>💬</span>
                        </div>
                        <div>
                            <h2 class="font-serif text-xl font-bold text-chocolate sm:text-2xl">
                                Une précision ?
                            </h2>
                            <p class="mt-1 text-sm text-muted">
                                Ajoutez une note pour préparer votre commande.
                            </p>
                        </div>
                    </div>

                    <label for="checkout-notes" class="mb-2 block text-sm font-semibold text-chocolate">
                        Instructions particulières <span class="font-normal text-muted">(facultatif)</span>
                    </label>
                    <textarea
                        id="checkout-notes"
                        v-model="form.notes"
                        rows="4"
                        placeholder="Un détail à nous communiquer…"
                        class="w-full resize-y rounded-xl border border-cream-dark bg-cream px-4 py-3 text-sm text-chocolate outline-none transition placeholder:text-muted/70 focus:border-caramel focus:bg-white"
                    ></textarea>
                </section>

                <div
                    v-if="error"
                    role="alert"
                    class="rounded-2xl border border-raspberry/30 bg-raspberry/5 p-4 text-sm font-medium text-raspberry"
                >
                    {{ error }}
                </div>
            </div>

            <!-- Récapitulatif -->
            <aside class="min-w-0 lg:sticky lg:top-24">
                <section class="rounded-3xl border border-cream-dark bg-white p-5 shadow-card sm:p-7">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
                        Presque terminé
                    </p>
                    <h2 class="mb-6 font-serif text-2xl font-bold text-chocolate">
                        Votre récapitulatif
                    </h2>

                    <ul class="mb-5 space-y-4 border-b border-cream-dark pb-5">
                        <li
                            v-for="item in cart.items"
                            :key="item.id"
                            class="flex items-start gap-3"
                        >
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-cream text-xl">
                                {{ emojiFor(item.type?.slug) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="break-words text-sm font-semibold text-chocolate">
                                    {{ item.name }}
                                </p>
                                <p class="mt-1 text-xs text-muted">
                                    Quantité : {{ item.quantity }}
                                </p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-chocolate">
                                {{ formatPrice(item.price * item.quantity) }}
                            </span>
                        </li>
                    </ul>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="text-muted">Sous-total</span>
                            <span class="font-medium text-chocolate">{{ formatPrice(cart.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <span class="text-muted">
                                {{ cart.isDelivery ? 'Livraison' : 'Retrait' }}
                            </span>
                            <span class="font-medium text-chocolate">
                                {{ cart.isDelivery ? (cart.deliveryFee === 0 ? 'Offerte' : formatPrice(cart.deliveryFee)) : 'Gratuit' }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="cart.isDelivery && cart.missingForFreeDelivery > 0"
                        class="mt-5 rounded-2xl bg-cream p-4 text-sm leading-6 text-caramel-dark"
                    >
                        <span class="mr-1">💡</span>
                        Plus que <strong>{{ formatPrice(cart.missingForFreeDelivery) }}</strong>
                        pour bénéficier de la livraison gratuite.
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4 border-t-2 border-cream-dark pt-5">
                        <span class="font-serif text-xl font-bold text-chocolate">Total</span>
                        <span class="text-2xl font-bold text-chocolate">
                            {{ formatPrice(cart.total) }}
                        </span>
                    </div>

                    <!-- Bandeau d'alerte si stock invalide -->
                    <div
                        v-if="cart.hasStockIssue"
                        class="mt-5 rounded-2xl border border-raspberry/20 bg-raspberry/5 p-4 text-xs leading-5 text-raspberry"
                    >
                        Certains produits ne sont plus disponibles en quantité suffisante. Vérifiez votre panier avant de commander.
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting || !cart.canCheckout"
                        class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-caramel px-5 py-4 font-bold text-white shadow-sm transition hover:bg-caramel-dark disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span>{{ submitting ? 'Traitement en cours…' : 'Confirmer la commande' }}</span>
                        <span v-if="!submitting">→</span>
                    </button>

                    <p class="mt-4 text-center text-xs leading-5 text-muted">
                        🔒 Vous allez être redirigé vers le paiement sécurisé.
                    </p>

                    <div class="mt-5 flex items-center justify-center gap-2 border-t border-cream-dark pt-5 text-xs text-muted">
                        <span>🍰</span>
                        <span>Préparé avec soin par Douceur</span>
                    </div>
                </section>
            </aside>
        </form>
    </main>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useCartStore } from '../stores/cart';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const cart = useCartStore();

const form = reactive({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postalCode: '',
    slotId: null,
    notes: '',
});

const slots = ref([]);
const submitting = ref(false);
const error = ref(null);
const success = ref(null);
const addresses = ref([]);
const selectedAddressId = ref(null);
const stockChanges = ref([]);

/* ─────────────────────────────────────────────────────────────
   Autocomplétion d'adresse (API Adresse.data.gouv.fr)
   ───────────────────────────────────────────────────────────── */

const suggestions = ref([]);
const showSuggestions = ref(false);
let debounceTimer = null;

/**
 * Masque le dropdown après un court délai, pour laisser
 * le @mousedown de la suggestion se déclencher avant le @blur.
 */
function hideSuggestions() {
    window.setTimeout(() => {
        showSuggestions.value = false;
    }, 200);
}

/**
 * Debounce la recherche à chaque frappe sur le champ adresse.
 */
function onAddressInput() {
    clearTimeout(debounceTimer);
    const query = form.address;

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

/**
 * Remplit le formulaire avec l'adresse sélectionnée.
 */
function selectSuggestion(s) {
    form.address = s.street;
    form.postalCode = s.postalCode;
    form.city = s.city;
    suggestions.value = [];
    showSuggestions.value = false;
}

/* ─────────────────────────────────────────────────────────────
   Helpers
   ───────────────────────────────────────────────────────────── */

const emojiMap = {
    cake: '🍰',
    dry_pastry: '🍪',
    savory: '🥐',
    sweet_bite: '🧁',
    event_package: '🎁',
};

function emojiFor(slug) {
    return emojiMap[slug] || '🍮';
}

function selectAddress(addr) {
    selectedAddressId.value = addr.id;
    form.address = addr.street + (addr.street_2 ? ', ' + addr.street_2 : '');
    form.postalCode = addr.postal_code;
    form.city = addr.city;
}

function formatPrice(price) {
    const amount = Number(price);
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number.isFinite(amount) ? amount : 0);
}

function formatSlot(slot) {
    const date = new Date(slot.date).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });

    return `${date} — ${slot.start_time.slice(0, 5)} → ${slot.end_time.slice(0, 5)}`;
}

async function loadSlots() {
    try {
        const type = cart.isDelivery ? 'delivery' : 'pickup';
        const { data } = await axios.get(`/api/delivery-slots?type=${type}`);
        slots.value = data;
    } catch {
        slots.value = [];
    }
}

async function loadAddresses() {
    try {
        const { data } = await axios.get('/api/user/addresses');
        addresses.value = data;
    } catch {
        addresses.value = [];
    }
}

/**
 * Synchronise le panier avec le stock réel.
 * Retourne un message d'erreur à afficher, ou null si tout est OK.
 */
async function validateStock() {
    const changes = await cart.syncStock();

    if (changes.length > 0) {
        stockChanges.value = changes;

        const messages = changes.map((c) => {
            if (c.type === 'removed') return `"${c.name}" n'est plus disponible et a été retiré.`;
            if (c.type === 'out_of_stock') return `"${c.name}" est en rupture de stock et a été retiré.`;
            return `"${c.name}" : quantité réduite à ${c.newQty} (stock limité).`;
        });

        return `Votre panier a été mis à jour : ${messages.join(' ')} Vérifiez avant de commander.`;
    }

    stockChanges.value = [];
    return null;
}

async function submit() {
    if (submitting.value) return;

    submitting.value = true;
    error.value = null;

    try {
        if (auth.isAuthenticated) {
            form.name = auth.user.name;
            form.email = auth.user.email;
            form.phone = auth.user.phone || 'non renseigné';
        }

        if (cart.isDelivery && !form.address.trim()) {
            error.value = 'Veuillez renseigner une adresse de livraison.';
            submitting.value = false;
            return;
        }

        // ✅ Synchronisation + vérification du stock
        const stockError = await validateStock();

        if (stockError) {
            error.value = stockError;
            submitting.value = false;
            return;
        }

        // ✅ Sécurité finale côté client (le backend revalidera avec lockForUpdate)
        if (!cart.canCheckout) {
            error.value = 'Votre panier contient des quantités indisponibles. Vérifiez-le avant de commander.';
            submitting.value = false;
            return;
        }

        const order = await cart.submitOrder(form);

        // ✅ Contact obligatoire pour les invités (protection backend)
        const checkoutPayload = auth.isAuthenticated ? {} : { contact: form.email };
        const { data } = await axios.post(
            `/api/orders/${order.reference}/checkout`,
            checkoutPayload,
        );

        if (!data?.checkout_url) {
            throw new Error('Le lien de paiement est indisponible. Veuillez réessayer.');
        }

        window.location.href = data.checkout_url;
    } catch (e) {
        error.value = e.response?.data?.message || e.message || 'Une erreur est survenue.';
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    // ✅ Synchronise le stock au chargement (remplace la boucle manuelle)
    const changes = await cart.syncStock();
    if (changes.length > 0) {
        stockChanges.value = changes;
    }

    // Les adresses enregistrées sont chargées uniquement pour les comptes connectés.
    if (auth.isAuthenticated && cart.isDelivery) {
        await loadAddresses();

        const defaultAddr =
            addresses.value.find((addr) => addr.is_default) || addresses.value[0];

        if (defaultAddr) {
            selectAddress(defaultAddr);
        }
    }

    await loadSlots();
});

onUnmounted(() => {
    // Évite qu'un debounce en attente déclenche un fetch sur un composant démonté.
    clearTimeout(debounceTimer);
});
</script>