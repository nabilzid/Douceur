<template>
    <div class="track-page">
        <header v-if="!order" class="page-header">
            <h1>Suivre ma demande sur mesure</h1>
            <p v-if="!auth.isAuthenticated">
                Entrez votre référence et l'email (ou téléphone) utilisé lors de la demande.
            </p>
            <p v-else>Saisissez votre référence pour afficher les détails.</p>
        </header>

        <form v-if="!order" class="track-form" @submit.prevent="track">
            <label>
                Référence de la demande
                <input v-model="form.reference" type="text" placeholder="SUR-20261025-XXXX" required />
            </label>
            <label v-if="!auth.isAuthenticated">
                Email ou téléphone
                <input v-model="form.contact" type="text" placeholder="email@example.com ou 06..." required />
            </label>
            <p v-if="error" class="error">{{ error }}</p>
            <button type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Recherche…' : 'Suivre ma demande' }}
            </button>
        </form>

        <div v-else class="order-card fade-in-up">
            <!-- En-tête -->
            <header class="order-head">
                <div>
                    <h2>{{ order.reference }}</h2>
                    <p class="order-event">🎂 {{ order.event_label }} — 📅 {{ formatDate(order.event_date) }}</p>
                </div>
                <span :class="['order-status', order.status]">{{ statusLabel(order.status) }}</span>
            </header>

            <!-- Description du client -->
            <section class="section section-client-desc">
                <h3 class="section-title">Votre demande</h3>
                <p class="event-type">
                    🎂 {{ order.event_label }}
                    <span v-if="order.guests_count"> · {{ order.guests_count }} personnes</span>
                    <span class="delivery-tag">
                        {{ order.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
                    </span>
                </p>
                <p class="desc">{{ order.description }}</p>

                <!-- ✅ Adresse de livraison (uniquement si livraison) -->
                <div v-if="order.delivery_type === 'delivery'" class="order-address">
                    <span class="address-label">📍 Adresse de livraison</span>
                    <p>{{ order.delivery_address }}</p>
                    <p>{{ order.delivery_postal_code }} {{ order.delivery_city }}</p>
                    <p v-if="order.delivery_notes" class="delivery-notes">
                        💬 {{ order.delivery_notes }}
                    </p>
                </div>
            </section>

            <!-- ÉTAPE DYNAMIQUE : La décision (choix du pourcentage + acceptation/paiement fusionnés) -->
            <section v-if="order.status === 'quoted'" class="section section-decision">
                <h3 class="section-title">{{ stepNumber('decision') }}. Votre décision</h3>
                <p class="section-subtitle">Choisissez le montant à régler pour accepter ce devis.</p>

                <!-- Sélecteur de pourcentage -->
                <div class="percentage-choices">
                    <button
                        v-for="pct in [20, 50, 100]"
                        :key="pct"
                        type="button"
                        :class="['percentage-btn', { active: selectedPercentage === pct }]"
                        @click="selectedPercentage = pct"
                    >
                        {{ pct === 100 ? 'Payer la totalité' : `Acompte ${pct} %` }}
                        <span class="percentage-amount">{{ formatPrice(order.quoted_price * pct / 100) }}</span>
                    </button>
                </div>

                <div class="payment-grid">
                    <div class="payment-grid-item">
                        <span class="payment-grid-label">Total du devis</span>
                        <strong class="payment-grid-value">{{ formatPrice(order.quoted_price) }}</strong>
                    </div>
                    <div class="payment-grid-item remaining">
                        <span class="payment-grid-label">
                            {{ selectedPercentage === 100 ? 'Montant à régler (totalité)' : `Montant à régler (${selectedPercentage} %)` }}
                        </span>
                        <strong class="payment-grid-value">{{ formatPrice(depositAmount) }}</strong>
                    </div>
                    <div v-if="selectedPercentage < 100" class="payment-grid-item">
                        <span class="payment-grid-label">Solde après acompte</span>
                        <strong class="payment-grid-value">{{ formatPrice(balanceAmount) }}</strong>
                    </div>
                </div>

                <div class="decision-buttons">
                    <button class="btn-accept" :disabled="responding" @click="acceptQuote">
                        <template v-if="selectedPercentage === 100">
                            ✓ Accepter et payer la totalité ({{ formatPrice(order.quoted_price) }})
                        </template>
                        <template v-else>
                            ✓ Accepter et payer l'acompte de {{ formatPrice(depositAmount) }}
                        </template>
                    </button>
                    <button class="btn-reject" :disabled="responding" @click="rejectQuote">
                        ✕ Refuser le devis
                    </button>
                </div>

                <p class="section-note">
                    <template v-if="selectedPercentage === 100">
                        Vous réglerez la totalité en ligne. Aucun montant ne sera à payer le jour de la livraison.
                    </template>
                    <template v-else>
                        Seul l'acompte de {{ formatPrice(depositAmount) }} est réglé en ligne aujourd'hui.
                        Le solde de {{ formatPrice(balanceAmount) }} sera à régler le jour de la livraison
                        (ou du retrait), directement auprès du pâtissier.
                    </template>
                </p>
            </section>

            <!-- ÉTAPE DYNAMIQUE : Le paiement est confirmé -->
            <section v-if="order.amount_paid > 0" class="section section-payment-confirmed">
                <div class="section-header">
                    <h3 class="section-title">{{ stepNumber('payment') }}. Règlement</h3>
                    <span :class="['payment-badge', remainingAmount > 0 ? 'partial' : 'complete']">
                        {{ remainingAmount > 0 ? 'Acompte versé' : 'Payé intégralement' }}
                    </span>
                </div>

                <div class="payment-grid">
                    <div class="payment-grid-item">
                        <span class="payment-grid-label">Total du devis</span>
                        <strong class="payment-grid-value">{{ formatPrice(order.quoted_price) }}</strong>
                    </div>
                    <div class="payment-grid-item paid">
                        <span class="payment-grid-label">
                            {{ remainingAmount > 0 ? 'Acompte versé' : 'Montant versé' }}
                        </span>
                        <strong class="payment-grid-value">{{ formatPrice(order.amount_paid) }}</strong>
                    </div>
                    <div v-if="remainingAmount > 0" class="payment-grid-item remaining">
                        <span class="payment-grid-label">Reste à payer</span>
                        <strong class="payment-grid-value">{{ formatPrice(remainingAmount) }}</strong>
                    </div>
                </div>

                <div class="payment-bar-wrapper">
                    <div class="payment-bar"><div class="payment-bar-fill" :style="{ width: paymentProgress + '%' }"></div></div>
                    <span class="payment-bar-text">{{ paymentProgress }}%</span>
                </div>

                <div v-if="remainingAmount > 0" class="balance-note">
                    <div class="balance-note-icon">💡</div>
                    <div class="balance-note-content">
                        <strong>Reste à payer : {{ formatPrice(remainingAmount) }}</strong>
                        <p>
                            Ce montant sera à régler le jour de la livraison
                            (ou du retrait) de votre commande, directement auprès du pâtissier.
                        </p>
                    </div>
                </div>
                <div v-else class="balance-note complete">
                    <div class="balance-note-icon">✅</div>
                    <div class="balance-note-content">
                        <strong>Votre commande est intégralement réglée.</strong>
                        <p>Aucun montant ne sera à payer le jour de la livraison ou du retrait.</p>
                    </div>
                </div>
            </section>

            <!-- ÉTAPE DYNAMIQUE : Les documents -->
            <section v-if="order.quote_pdf_url || order.deposit_invoice_url || order.balance_invoice_url" class="section section-documents">
                <h3 class="section-title">{{ stepNumber('documents') }}. Mes documents</h3>
                <div class="document-list">
                    <div class="document-item">
                        <div class="document-info">
                            <strong>Devis</strong>
                            <span>Document commercial initial</span>
                        </div>
                        <a v-if="order.quote_pdf_url" :href="order.quote_pdf_url" class="btn-doc-small" target="_blank">Consulter</a>
                        <span v-else class="document-status">Non disponible</span>
                    </div>

                    <div class="document-item" :class="{ disabled: !order.deposit_invoice_url }">
                        <div class="document-info">
                            <strong>Facture d'acompte</strong>
                            <span>Justificatif du premier règlement</span>
                        </div>
                        <a v-if="order.deposit_invoice_url" :href="order.deposit_invoice_url" class="btn-doc-small" target="_blank">Consulter</a>
                        <span v-else class="document-status">Émise après paiement</span>
                    </div>

                    <div class="document-item" :class="{ disabled: !order.balance_invoice_url }">
                        <div class="document-info">
                            <strong>Facture de solde</strong>
                            <span>Document final après livraison</span>
                        </div>
                        <a v-if="order.balance_invoice_url" :href="order.balance_invoice_url" class="btn-doc-small" target="_blank">Consulter</a>
                        <span v-else class="document-status">À venir</span>
                    </div>
                </div>
            </section>

            <button class="btn-secondary" @click="reset">← Rechercher une autre demande</button>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const auth = useAuthStore();

const form = reactive({ reference: '', contact: '' });
const order = ref(null);
const loading = ref(false);
const error = ref(null);
const responding = ref(false);

const selectedPercentage = ref(20);

const depositAmount = computed(() => {
    if (!order.value) return 0;
    return Math.round(order.value.quoted_price * selectedPercentage.value) / 100;
});
const balanceAmount = computed(() => {
    if (!order.value) return 0;
    return order.value.quoted_price - depositAmount.value;
});
const remainingAmount = computed(() => {
    if (!order.value) return 0;
    return Math.max(0, order.value.quoted_price - order.value.amount_paid);
});
const paymentProgress = computed(() => {
    if (!order.value || !order.value.quoted_price) return 0;
    return Math.min(100, Math.round((order.value.amount_paid / order.value.quoted_price) * 100));
});

/**
 * Numérotation dynamique des étapes.
 * Seules les étapes réellement visibles sont comptées.
 */
const visibleSteps = computed(() => {
    if (!order.value) return [];
    const steps = [];
    if (order.value.quoted_price) steps.push('quote');
    if (order.value.status === 'quoted') steps.push('decision');
    if (order.value.amount_paid > 0) steps.push('payment');
    if (order.value.quote_pdf_url || order.value.deposit_invoice_url || order.value.balance_invoice_url) steps.push('documents');
    return steps;
});

function stepNumber(key) {
    const index = visibleSteps.value.indexOf(key);
    return index >= 0 ? index + 1 : '';
}

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}
function statusLabel(s) {
    return {
        pending_quote: 'En attente de devis',
        quoted: 'En attente de votre décision',
        accepted: 'Devis accepté',
        deposit_paid: 'Acompte réglé',
        completed: 'Terminée',
        rejected: 'Refusée',
        refused_by_merchant: 'Refusée',
        cancelled: 'Annulée',
    }[s] || s;
}

async function track() {
    loading.value = true;
    error.value = null;
    try {
        const payload = { reference: form.reference };
        if (!auth.isAuthenticated) payload.contact = form.contact;
        const { data } = await axios.post('/api/custom-orders/track', payload);
        order.value = data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Demande introuvable.';
    } finally {
        loading.value = false;
    }
}

async function acceptQuote() {
    responding.value = true;
    try {
        const { data } = await axios.put(`/api/custom-orders/${order.value.reference}/respond-guest`, {
            contact: form.contact,
            decision: 'accept',
            payment_percentage: selectedPercentage.value,
        });
        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    } finally {
        responding.value = false;
    }
}

async function rejectQuote() {
    responding.value = true;
    try {
        const { data } = await axios.put(`/api/custom-orders/${order.value.reference}/respond-guest`, {
            contact: form.contact,
            decision: 'reject',
        });
        order.value.status = data.status;
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    } finally {
        responding.value = false;
    }
}

function reset() {
    order.value = null;
    form.reference = '';
    form.contact = '';
    error.value = null;
    selectedPercentage.value = 20;
}

onMounted(async () => {
    if (!auth.user) await auth.fetchUser();
    if (route.query.reference) form.reference = route.query.reference;
    if (route.query.contact) form.contact = route.query.contact;
    if (form.reference && (auth.isAuthenticated || form.contact)) await track();
});
</script>

<style scoped>
/* ✅ Tag livraison / click & collect */
.delivery-tag {
    display: inline-block;
    margin-left: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* ✅ Bloc adresse de livraison */
.order-address {
    background: white;
    border-radius: var(--radius-sm);
    padding: 0.85rem 1rem;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: var(--chocolate-soft);
    border-left: 3px solid var(--caramel);
}
.address-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 0.35rem;
}
.order-address p {
    margin: 0;
    line-height: 1.4;
}
.order-address .delivery-notes {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    font-style: italic;
    color: var(--text-muted);
}
.track-page { max-width: 720px; margin: 0 auto; padding: 2rem 1rem; }

.page-header { text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
.page-header p { color: var(--text-muted); }

.track-form { background: white; border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); display: flex; flex-direction: column; gap: 1rem; }
label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
input { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); }
input:focus { outline: none; border-color: var(--caramel); background: white; }
.error { color: var(--raspberry); font-size: 0.9rem; }
.btn-primary { width: 100%; justify-content: center; }

.order-card { background: white; border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); }

.order-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.order-head h2 { font-size: 1.6rem; margin: 0; color: var(--chocolate); }
.order-event { color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0 0; }
.order-status { padding: 0.4rem 0.9rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
.order-status.quoted { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.order-status.accepted { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.order-status.deposit_paid { background: rgba(5, 150, 105, 0.12); color: #059669; }
.order-status.completed { background: rgba(5, 150, 105, 0.12); color: #059669; }
.order-status.rejected, .order-status.cancelled, .order-status.refused_by_merchant { background: rgba(201, 79, 109, 0.12); color: var(--raspberry); }

/* Description du client */
.section-client-desc {
    background: var(--cream);
    border-left: 3px solid var(--chocolate-soft);
}
.section-client-desc .event-type {
    font-size: 0.95rem;
    color: var(--chocolate-soft);
    font-weight: 500;
    margin-bottom: 0.75rem;
}
.section-client-desc .desc {
    font-size: 0.95rem;
    color: var(--chocolate);
    line-height: 1.5;
    margin: 0;
    font-style: italic;
}

.section { background: var(--cream); border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1rem; }
.section-header { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--caramel); margin: 0; }
.section-price { font-size: 1.6rem; font-weight: 700; color: var(--chocolate); }
.section-subtitle { font-size: 0.9rem; color: var(--chocolate-soft); margin: 0 0 1rem; }
.section-note { font-size: 0.8rem; color: var(--text-muted); font-style: italic; margin-top: 0.75rem; }
.section-message { font-size: 0.95rem; color: var(--chocolate-soft); line-height: 1.5; margin: 0; padding: 0.75rem 1rem; background: white; border-radius: var(--radius-sm); border-left: 3px solid var(--caramel); }

.section-decision { background: white; border: 1.5px dashed var(--cream-dark); }

.percentage-choices { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; }
.percentage-btn {
    flex: 1;
    min-width: 110px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 1rem;
    border-radius: var(--radius-sm);
    border: 2px solid var(--cream-dark);
    background: var(--cream);
    color: var(--chocolate);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}
.percentage-btn:hover { border-color: var(--caramel); color: var(--caramel-dark); background: white; }
.percentage-btn.active { background: var(--caramel); border-color: var(--caramel); color: white; box-shadow: 0 4px 12px rgba(201, 123, 63, 0.3); }
.percentage-amount { font-size: 1.05rem; font-weight: 700; }

.decision-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 1rem; }
.btn-accept, .btn-reject { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.85rem 1.5rem; border-radius: 999px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
.btn-accept { background: #059669; color: white; border-color: #059669; flex: 1; }
.btn-accept:hover:not(:disabled) { background: #047857; border-color: #047857; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); }
.btn-reject { background: white; color: var(--raspberry); border-color: var(--raspberry); }
.btn-reject:hover:not(:disabled) { background: var(--raspberry); color: white; }
.btn-accept:disabled, .btn-reject:disabled { opacity: 0.5; cursor: not-allowed; }

.section-payment-confirmed { background: #f0f9f5; border-left: 4px solid #059669; }

.payment-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.payment-grid-item { display: flex; flex-direction: column; gap: 0.25rem; background: white; padding: 0.9rem 1rem; border-radius: var(--radius-sm); border-left: 3px solid var(--cream-dark); }
.payment-grid-item.paid { border-left-color: #059669; }
.payment-grid-item.remaining { border-left-color: var(--raspberry); }
.payment-grid-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 600; }
.payment-grid-value { font-size: 1.1rem; color: var(--chocolate); }
.payment-grid-item.paid .payment-grid-value { color: #059669; }
.payment-grid-item.remaining .payment-grid-value { color: var(--raspberry); }

.payment-badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.payment-badge.partial { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.payment-badge.complete { background: rgba(5, 150, 105, 0.1); color: #059669; }

.payment-bar-wrapper { display: flex; align-items: center; gap: 0.75rem; margin-top: 1rem; }
.payment-bar { flex: 1; height: 10px; background: var(--cream-dark); border-radius: 999px; overflow: hidden; }
.payment-bar-fill { height: 100%; background: linear-gradient(90deg, var(--caramel), var(--caramel-dark)); border-radius: 999px; transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
.payment-bar-text { font-size: 0.85rem; font-weight: 700; color: var(--caramel-dark); }

.balance-note {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    margin-top: 1rem;
    padding: 1rem 1.25rem;
    background: rgba(201, 123, 63, 0.08);
    border-left: 4px solid var(--caramel);
    border-radius: var(--radius-sm);
}
.balance-note.complete { background: rgba(5, 150, 105, 0.08); border-left-color: #059669; }
.balance-note-icon { font-size: 1.4rem; line-height: 1; flex-shrink: 0; }
.balance-note-content strong { display: block; color: var(--chocolate); font-size: 0.95rem; margin-bottom: 0.25rem; }
.balance-note-content p { margin: 0; font-size: 0.85rem; color: var(--chocolate-soft); line-height: 1.5; }

.section-documents { background: white; }
.document-list { display: flex; flex-direction: column; gap: 0.5rem; }
.document-item { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; border: 1px solid var(--cream-dark); border-radius: var(--radius-sm); gap: 1rem; }
.document-item.disabled { opacity: 0.6; }
.document-info { display: flex; flex-direction: column; gap: 0.15rem; }
.document-info strong { font-size: 0.9rem; color: var(--chocolate); }
.document-info span { font-size: 0.75rem; color: var(--text-muted); }
.document-status { font-size: 0.8rem; color: var(--text-muted); font-style: italic; }
.btn-doc-small { padding: 0.4rem 0.9rem; background: var(--cream); color: var(--caramel-dark); border: 1px solid var(--caramel); border-radius: 999px; font-size: 0.8rem; font-weight: 600; text-decoration: none; }
.btn-doc-small:hover { background: var(--caramel); color: white; text-decoration: none; }

.btn-secondary { margin-top: 1.5rem; padding: 0.6rem 1.2rem; border-radius: 999px; background: white; color: var(--caramel); border: 1.5px solid var(--caramel); font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 0.9rem; }
.btn-secondary:hover { background: var(--caramel); color: white; }

@media (max-width: 600px) {
    .order-head { flex-direction: column; }
    .decision-buttons { flex-direction: column; }
    .btn-accept, .btn-reject { width: 100%; }
    .percentage-choices { flex-direction: column; }
    .percentage-btn { width: 100%; }
}
</style>