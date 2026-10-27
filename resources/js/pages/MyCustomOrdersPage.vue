<template>
    <div class="my-custom-orders-page">
        <header class="page-header">
            <h1>Mes demandes sur mesure</h1>
            <p>Retrouvez l'historique de vos demandes et vos devis.</p>
        </header>

        <div v-if="loading" class="state">Chargement…</div>

        <div v-else-if="orders.length === 0" class="state">
            <span class="empty-emoji">🎂</span>
            <p>Vous n'avez pas encore fait de demande.</p>
            <RouterLink to="/evenements" class="btn-primary">Voir les formules →</RouterLink>
        </div>

        <div v-else class="orders-list">
            <article
                v-for="(o, index) in orders"
                :key="o.id"
                :class="['order-card', { open: openOrders[o.id] }]"
            >
                <button class="order-header" @click="toggleOrder(o.id)">
                    <div class="header-left">
                        <strong class="ref">{{ o.reference }}</strong>
                        <span class="date">🎂 {{ eventLabel(o.event_type) }} — 📅 {{ formatDate(o.event_date) }}</span>
                    </div>
                    <div class="header-right">
                        <span :class="['status', o.status]">{{ statusLabel(o.status) }}</span>
                        <span class="chevron" :class="{ open: openOrders[o.id] }">▾</span>
                    </div>
                </button>

                <Transition name="accordion">
                    <div v-if="openOrders[o.id]" class="order-body">
                        <!-- ✅ Description du client (Votre demande) -->
                        <section class="section section-client-desc">
                            <h3 class="section-title">Votre demande</h3>
                            <p class="event-type">
                                🎂 {{ o.event_type ? eventLabel(o.event_type) : o.event_label }}
                                <span v-if="o.guests_count"> · {{ o.guests_count }} personnes</span>
                                <span class="delivery-tag">
                                    {{ o.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
                                </span>
                            </p>
                            <p class="desc">{{ o.description }}</p>

                            <!-- ✅ Adresse de livraison (uniquement si livraison) -->
                            <div v-if="o.delivery_type === 'delivery'" class="order-address">
                                <span class="address-label">📍 Adresse de livraison</span>
                                <p>{{ o.delivery_address }}</p>
                                <p>{{ o.delivery_postal_code }} {{ o.delivery_city }}</p>
                                <p v-if="o.delivery_notes" class="delivery-notes">
                                    💬 {{ o.delivery_notes }}
                                </p>
                            </div>
                        </section>

                        <!-- ✅ ÉTAPE DYNAMIQUE : La réponse du pâtissier -->
                        <section v-if="o.quoted_price" class="section">
                            <div class="section-header">
                                <h3 class="section-title">{{ stepNumber(o, 'quote') }}. La réponse du pâtissier</h3>
                                <span class="section-price">{{ formatPrice(o.quoted_price) }}</span>
                            </div>
                            <p class="section-message">{{ o.merchant_response }}</p>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : La décision -->
                        <section v-if="o.status === 'quoted'" class="section section-decision">
                            <h3 class="section-title">{{ stepNumber(o, 'decision') }}. Votre décision</h3>
                            <p class="section-subtitle">Choisissez le montant à régler pour accepter ce devis.</p>

                            <div class="percentage-choices">
                                <button
                                    v-for="pct in [20, 50, 100]"
                                    :key="pct"
                                    type="button"
                                    :class="['percentage-btn', { active: paymentPercentages[o.id] === pct }]"
                                    @click="paymentPercentages[o.id] = pct"
                                >
                                    {{ pct === 100 ? 'Payer la totalité' : `Acompte ${pct} %` }}
                                    <span class="percentage-amount">{{ formatPrice(o.quoted_price * pct / 100) }}</span>
                                </button>
                            </div>

                            <div class="payment-grid">
                                <div class="payment-grid-item">
                                    <span class="payment-grid-label">Total du devis</span>
                                    <strong class="payment-grid-value">{{ formatPrice(o.quoted_price) }}</strong>
                                </div>
                                <div class="payment-grid-item remaining">
                                    <span class="payment-grid-label">
                                        {{ paymentPercentages[o.id] === 100 ? 'Montant à régler (totalité)' : `Montant à régler (${paymentPercentages[o.id]} %)` }}
                                    </span>
                                    <strong class="payment-grid-value">{{ formatPrice(o.quoted_price * paymentPercentages[o.id] / 100) }}</strong>
                                </div>
                                <div v-if="paymentPercentages[o.id] < 100" class="payment-grid-item">
                                    <span class="payment-grid-label">Solde après acompte</span>
                                    <strong class="payment-grid-value">{{ formatPrice(o.quoted_price * (100 - paymentPercentages[o.id]) / 100) }}</strong>
                                </div>
                            </div>

                            <div class="decision-buttons">
                                <button class="btn-accept" :disabled="responding[o.id]" @click="respond(o, 'accept')">
                                    <template v-if="paymentPercentages[o.id] === 100">
                                        ✓ Accepter et payer la totalité ({{ formatPrice(o.quoted_price) }})
                                    </template>
                                    <template v-else>
                                        ✓ Accepter et payer l'acompte de {{ formatPrice(o.quoted_price * paymentPercentages[o.id] / 100) }}
                                    </template>
                                </button>
                                <button class="btn-reject" :disabled="responding[o.id]" @click="respond(o, 'reject')">
                                    ✕ Refuser
                                </button>
                            </div>

                            <p class="section-note">
                                <template v-if="paymentPercentages[o.id] === 100">
                                    Vous réglerez la totalité en ligne. Aucun montant ne sera à payer le jour de la livraison.
                                </template>
                                <template v-else>
                                    Seul l'acompte de {{ formatPrice(o.quoted_price * paymentPercentages[o.id] / 100) }} est réglé en ligne aujourd'hui.
                                    Le solde de {{ formatPrice(o.quoted_price * (100 - paymentPercentages[o.id]) / 100) }} sera à régler
                                    le jour de la livraison (ou du retrait), directement auprès du pâtissier.
                                </template>
                            </p>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : Le règlement -->
                        <section v-if="o.amount_paid > 0" class="section section-payment-confirmed">
                            <div class="section-header">
                                <h3 class="section-title">{{ stepNumber(o, 'payment') }}. Règlement</h3>
                                <span :class="['payment-badge', remainingAmount(o) > 0 ? 'partial' : 'complete']">
                                    {{ remainingAmount(o) > 0 ? 'Acompte versé' : 'Payé intégralement' }}
                                </span>
                            </div>

                            <div class="payment-grid">
                                <div class="payment-grid-item">
                                    <span class="payment-grid-label">Total du devis</span>
                                    <strong class="payment-grid-value">{{ formatPrice(o.quoted_price) }}</strong>
                                </div>
                                <div class="payment-grid-item paid">
                                    <span class="payment-grid-label">
                                        {{ remainingAmount(o) > 0 ? 'Acompte versé' : 'Montant versé' }}
                                    </span>
                                    <strong class="payment-grid-value">{{ formatPrice(o.amount_paid) }}</strong>
                                </div>
                                <div v-if="remainingAmount(o) > 0" class="payment-grid-item remaining">
                                    <span class="payment-grid-label">Reste à payer</span>
                                    <strong class="payment-grid-value">{{ formatPrice(remainingAmount(o)) }}</strong>
                                </div>
                            </div>

                            <div class="payment-bar-wrapper">
                                <div class="payment-bar">
                                    <div class="payment-bar-fill" :style="{ width: paymentProgress(o) + '%' }"></div>
                                </div>
                                <span class="payment-bar-text">{{ paymentProgress(o) }}%</span>
                            </div>

                            <div v-if="remainingAmount(o) > 0" class="balance-note">
                                <div class="balance-note-icon">💡</div>
                                <div class="balance-note-content">
                                    <strong>Reste à payer : {{ formatPrice(remainingAmount(o)) }}</strong>
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
                        <section
                            v-if="o.quote_pdf_path || o.deposit_invoice_path || o.balance_invoice_path"
                            class="section section-documents"
                        >
                            <h3 class="section-title">{{ stepNumber(o, 'documents') }}. Mes documents</h3>
                            <div class="document-list">
                                <div class="document-item">
                                    <div class="document-info">
                                        <strong>Devis</strong>
                                        <span>Document commercial initial</span>
                                    </div>
                                    <a
                                        v-if="o.quote_pdf_path"
                                        :href="`/api/custom-orders/${o.reference}/quote-pdf`"
                                        class="btn-doc-small"
                                        target="_blank"
                                    >Consulter</a>
                                    <span v-else class="document-status">Non disponible</span>
                                </div>

                                <div class="document-item" :class="{ disabled: !o.deposit_invoice_path }">
                                    <div class="document-info">
                                        <strong>Facture d'acompte</strong>
                                        <span>Justificatif du premier règlement</span>
                                    </div>
                                    <a
                                        v-if="o.deposit_invoice_path"
                                        :href="`/api/custom-orders/${o.reference}/invoice/deposit`"
                                        class="btn-doc-small"
                                        target="_blank"
                                    >Consulter</a>
                                    <span v-else class="document-status">Émise après paiement</span>
                                </div>

                                <div class="document-item" :class="{ disabled: !o.balance_invoice_path }">
                                    <div class="document-info">
                                        <strong>Facture de solde</strong>
                                        <span>Document final après livraison</span>
                                    </div>
                                    <a
                                        v-if="o.balance_invoice_path"
                                        :href="`/api/custom-orders/${o.reference}/invoice/balance`"
                                        class="btn-doc-small"
                                        target="_blank"
                                    >Consulter</a>
                                    <span v-else class="document-status">À venir</span>
                                </div>
                            </div>
                        </section>

                        <!-- Messages de statut -->
                        <div v-if="o.status === 'accepted'" class="alert info">
                            ✓ Vous avez accepté ce devis. Le pâtissier va réaliser votre commande.
                        </div>
                        <div v-if="o.status === 'completed'" class="alert success">
                            🎉 Commande terminée. Merci !
                        </div>
                        <div v-if="o.status === 'rejected'" class="alert warning">
                            Vous avez refusé ce devis.
                        </div>
                        <div v-if="o.status === 'refused_by_merchant'" class="alert warning">
                            Cette demande a été refusée.
                            <span v-if="o.merchant_response">Motif : {{ o.merchant_response }}</span>
                        </div>
                        <div v-if="o.status === 'cancelled'" class="alert warning">
                            Cette demande a été annulée.
                        </div>
                    </div>
                </Transition>
            </article>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const orders = ref([]);
const loading = ref(true);
const responding = reactive({});
const openOrders = reactive({});
const paymentPercentages = reactive({});

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}
function eventLabel(type) {
    return {
        birthday: 'Anniversaire',
        wedding: 'Mariage',
        baptism: 'Baptême',
        corporate: 'Entreprise',
        other: 'Autre',
    }[type] || type;
}
function statusLabel(s) {
    return {
        pending_quote: 'En attente de devis',
        quoted: 'Devis reçu',
        accepted: 'Acceptée',
        completed: 'Terminée',
        rejected: 'Refusée',
        refused_by_merchant: 'Refusée',
        cancelled: 'Annulée',
    }[s] || s;
}

function remainingAmount(order) {
    return Math.max(0, (order.quoted_price ?? 0) - (order.amount_paid ?? 0));
}
function paymentProgress(order) {
    if (!order.quoted_price) return 0;
    return Math.min(100, Math.round((order.amount_paid / order.quoted_price) * 100));
}

function toggleOrder(orderId) {
    openOrders[orderId] = !openOrders[orderId];
}

/**
 * Numérotation dynamique des étapes par commande.
 * Seules les étapes réellement visibles sont comptées.
 */
function stepNumber(order, key) {
    const steps = [];
    if (order.quoted_price) steps.push('quote');
    if (order.status === 'quoted') steps.push('decision');
    if (order.amount_paid > 0) steps.push('payment');
    if (order.quote_pdf_path || order.deposit_invoice_path || order.balance_invoice_path) steps.push('documents');

    const index = steps.indexOf(key);
    return index >= 0 ? index + 1 : '';
}

async function respond(order, decision) {
    responding[order.id] = true;
    try {
        const payload = { decision };
        if (decision === 'accept') {
            payload.payment_percentage = paymentPercentages[order.id];
        }

        const { data } = await axios.put(`/api/custom-orders/${order.reference}/respond`, payload);

        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        } else {
            Object.assign(order, data);
        }
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur lors de la réponse au devis.');
    } finally {
        responding[order.id] = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/custom-orders/mine');
        orders.value = data;

        data.forEach((o, index) => {
            openOrders[o.id] = index === 0;
            responding[o.id] = false;
            paymentPercentages[o.id] = 20;
        });
    } finally {
        loading.value = false;
    }
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
.my-custom-orders-page { max-width: 720px; margin: 0 auto; padding: 2rem 1rem; }

.page-header { text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
.page-header p { color: var(--text-muted); }

.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.empty-emoji { font-size: 4rem; display: block; margin-bottom: 1rem; }

.orders-list { display: flex; flex-direction: column; gap: 1rem; }

.order-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.order-card:hover { box-shadow: var(--shadow-md); }
.order-card.open { box-shadow: var(--shadow-md); }

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 1.25rem 1.5rem;
    background: white;
    border: none;
    cursor: pointer;
    text-align: left;
    gap: 1rem;
    transition: background 0.15s;
}
.order-header:hover { background: var(--cream); }

.header-left { display: flex; flex-direction: column; gap: 0.25rem; }
.ref { font-size: 1.05rem; color: var(--chocolate); }
.date { color: var(--text-muted); font-size: 0.85rem; }

.header-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }

.status {
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--cream-dark);
    color: var(--chocolate-soft);
    white-space: nowrap;
}
.status.quoted { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.status.accepted { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.status.completed { background: rgba(5, 150, 105, 0.12); color: #059669; }
.status.rejected,
.status.cancelled,
.status.refused_by_merchant { background: rgba(201, 79, 109, 0.12); color: var(--raspberry); }

.chevron {
    font-size: 1rem;
    color: var(--text-muted);
    transition: transform 0.25s ease;
}
.chevron.open { transform: rotate(180deg); }

.order-body {
    padding: 0 1.5rem 1.5rem;
    border-top: 1px solid var(--cream-dark);
}

/* Description du client */
.section-client-desc {
    background: var(--cream);
    border-left: 3px solid var(--chocolate-soft);
    margin-top: 1rem;
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

.section {
    background: var(--cream);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.section-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--caramel);
    margin: 0;
}
.section-price { font-size: 1.6rem; font-weight: 700; color: var(--chocolate); }
.section-subtitle { font-size: 0.9rem; color: var(--chocolate-soft); margin: 0 0 1rem; }
.section-note { font-size: 0.8rem; color: var(--text-muted); font-style: italic; margin-top: 0.75rem; }
.section-message {
    font-size: 0.95rem;
    color: var(--chocolate-soft);
    line-height: 1.5;
    margin: 0 0 1rem;
    padding: 0.75rem 1rem;
    background: white;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--caramel);
}

/* Décision — boutons identiques à TrackCustomOrderPage */
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
.btn-accept, .btn-reject {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.85rem 1.5rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}
.btn-accept { background: #059669; color: white; border-color: #059669; flex: 1; }
.btn-accept:hover:not(:disabled) { background: #047857; border-color: #047857; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); }
.btn-reject { background: white; color: var(--raspberry); border-color: var(--raspberry); }
.btn-reject:hover:not(:disabled) { background: var(--raspberry); color: white; }
.btn-accept:disabled, .btn-reject:disabled { opacity: 0.5; cursor: not-allowed; }

/* Paiement */
.section-payment-confirmed { background: #f0f9f5; border-left: 4px solid #059669; }

.payment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}
.payment-grid-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    background: white;
    padding: 0.9rem 1rem;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--cream-dark);
}
.payment-grid-item.paid { border-left-color: #059669; }
.payment-grid-item.remaining { border-left-color: var(--raspberry); }
.payment-grid-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    font-weight: 600;
}
.payment-grid-value { font-size: 1.1rem; color: var(--chocolate); }
.payment-grid-item.paid .payment-grid-value { color: #059669; }
.payment-grid-item.remaining .payment-grid-value { color: var(--raspberry); }

.payment-badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.payment-badge.partial { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.payment-badge.complete { background: rgba(5, 150, 105, 0.1); color: #059669; }

.payment-bar-wrapper { display: flex; align-items: center; gap: 0.75rem; margin-top: 1rem; }
.payment-bar { flex: 1; height: 10px; background: var(--cream-dark); border-radius: 999px; overflow: hidden; }
.payment-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--caramel), var(--caramel-dark));
    border-radius: 999px;
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.payment-bar-text { font-size: 0.85rem; font-weight: 700; color: var(--caramel-dark); }

/* Encadré reste à payer / intégralement réglé */
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

/* Documents */
.section-documents { background: white; }
.document-list { display: flex; flex-direction: column; gap: 0.5rem; }
.document-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.85rem 1rem;
    border: 1px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    gap: 1rem;
}
.document-item.disabled { opacity: 0.6; }
.document-info { display: flex; flex-direction: column; gap: 0.15rem; }
.document-info strong { font-size: 0.9rem; color: var(--chocolate); }
.document-info span { font-size: 0.75rem; color: var(--text-muted); }
.document-status { font-size: 0.8rem; color: var(--text-muted); font-style: italic; }
.btn-doc-small {
    padding: 0.4rem 0.9rem;
    background: var(--cream);
    color: var(--caramel-dark);
    border: 1px solid var(--caramel);
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
}
.btn-doc-small:hover { background: var(--caramel); color: white; text-decoration: none; }

/* Alertes */
.alert {
    padding: 0.85rem 1rem;
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 1rem;
}
.alert.success { background: rgba(5, 150, 105, 0.1); color: #059669; }
.alert.warning { background: rgba(234, 179, 8, 0.12); color: #a16207; }
.alert.info { background: rgba(59, 130, 246, 0.08); color: #2563eb; }

/* Animation accordéon */
.accordion-enter-active,
.accordion-leave-active {
    transition: max-height 0.3s ease, opacity 0.3s ease;
    overflow: hidden;
}
.accordion-enter-from,
.accordion-leave-to {
    max-height: 0;
    opacity: 0;
}
.accordion-enter-to,
.accordion-leave-from {
    max-height: 3000px;
    opacity: 1;
}

@media (max-width: 600px) {
    .order-header { flex-direction: column; align-items: flex-start; }
    .header-right { width: 100%; justify-content: space-between; }
    .decision-buttons { flex-direction: column; }
    .btn-accept, .btn-reject { width: 100%; }
    .percentage-choices { flex-direction: column; }
    .percentage-btn { width: 100%; }
}
</style>