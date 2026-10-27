<template>
    <div class="admin-page">
        <header class="page-header">
            <div>
                <h1>Commandes sur mesure</h1>
                <p class="subtitle">{{ orders.length }} demande(s)</p>
            </div>
        </header>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement…</p>
        </div>

        <div v-else-if="orders.length === 0" class="state">
            Aucune demande pour l'instant.
        </div>

        <div v-else class="orders-list">
            <article
                v-for="(o, index) in orders"
                :key="o.id"
                :class="['card', { open: openOrders[o.id] }]"
            >
                <!-- En-tête cliquable -->
                <button class="card-header" @click="toggleOrder(o.id)">
                    <div class="header-left">
                        <strong class="ref">{{ o.reference }}</strong>
                        <div class="dates">
                            <span class="date">📅 Événement : {{ formatDate(o.event_date) }}</span>
                            <span class="date-requested">📝 Demandée le {{ formatDate(o.created_at) }}</span>
                        </div>
                    </div>
                    <div class="header-right">
                        <span :class="['status', o.status]">{{ statusLabel(o.status) }}</span>
                        <span class="chevron" :class="{ open: openOrders[o.id] }">▾</span>
                    </div>
                </button>

                <!-- Contenu dépliable -->
                <Transition name="accordion">
                    <div v-if="openOrders[o.id]" class="card-body">
                        <!-- Infos client -->
                        <div class="customer-box">
                            <div class="customer-row">
                                <span class="customer-label">Client</span>
                                <strong>{{ o.customer_name }}</strong>
                            </div>
                            <div class="customer-row">
                                <span class="customer-label">Email</span>
                                <a :href="`mailto:${o.customer_email}`">{{ o.customer_email }}</a>
                            </div>
                            <div class="customer-row">
                                <span class="customer-label">Téléphone</span>
                                <a :href="`tel:${o.customer_phone}`">{{ o.customer_phone }}</a>
                            </div>
                        </div>

                        <!-- Description du client -->
                        <section class="section section-client-desc">
                            <h3 class="section-title">Description du client</h3>
                            <p class="event-type">
                                🎂 {{ o.event_label }}
                                <span v-if="o.guests_count"> · {{ o.guests_count }} personnes</span>
                                <span class="delivery-tag">
                                    {{ o.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Retrait' }}
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

                        <!-- ÉTAPE : Formulaire de réponse admin (pending_quote + admin) -->
                        <section v-if="o.status === 'pending_quote' && isAdmin" class="section section-decision">
                            <h3 class="section-title">1. Réponse de l'admin</h3>
                            <p class="section-subtitle">Renseignez le prix et la réponse à envoyer au client.</p>

                            <div class="quote-form">
                                <label>
                                    Prix du devis (€)
                                    <input v-model.number="quotes[o.id].price" type="number" min="0" step="0.01" placeholder="Ex : 155.00" />
                                </label>
                                <label>
                                    Réponse au client
                                    <textarea v-model="quotes[o.id].response" rows="3" placeholder="Détail de la prestation, conditions…"></textarea>
                                </label>
                                <p v-if="!quotes[o.id].price || !quotes[o.id].response" class="hint">Renseignez un prix et une réponse.</p>
                                <p v-if="quoteError[o.id]" class="error">{{ quoteError[o.id] }}</p>
                                <p v-if="quoteSuccess[o.id]" class="success">✓ Devis envoyé.</p>
                            </div>

                            <div class="actions-row">
                                <button class="btn-accept" :disabled="!canSendQuote(o) || sending[o.id]" @click="sendQuote(o)">
                                    {{ sending[o.id] ? 'Envoi…' : 'Envoyer le devis' }}
                                </button>
                                <button class="btn-reject" @click="openRefuseModal(o)">
                                    Refuser la demande
                                </button>
                            </div>
                        </section>

                        <!-- Pâtissier : lecture seule en attente -->
                        <div v-else-if="o.status === 'pending_quote' && !isAdmin" class="section section-decision">
                            <p class="section-subtitle">⏳ En attente de la décision de l'administrateur.</p>
                        </div>

                        <!-- ÉTAPE DYNAMIQUE : Réponse de l'admin (tous statuts avec quoted_price) -->
                        <section v-if="o.quoted_price" class="section">
                            <div class="section-header">
                                <h3 class="section-title">{{ stepNumber(o, 'admin_response') }}. Réponse de l'admin</h3>
                                <span class="section-price">{{ formatPrice(o.quoted_price) }}</span>
                            </div>
                            <p class="section-message">{{ o.merchant_response }}</p>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : Décision du client (accepté ou terminé) -->
                        <section
                            v-if="['accepted', 'completed'].includes(o.status)"
                            class="section section-decision"
                        >
                            <div class="section-header">
                                <h3 class="section-title">{{ stepNumber(o, 'decision') }}. Décision du client</h3>
                                <span class="payment-badge complete">Devis accepté</span>
                            </div>
                            <p class="section-subtitle">
                                Le client a accepté le devis
                                <template v-if="o.amount_paid > 0">
                                    et réglé {{ formatPrice(o.amount_paid) }}
                                    <template v-if="o.payment_percentage">({{ o.payment_percentage }} %)</template>
                                    en ligne.
                                </template>
                            </p>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : Décision du client (refusé) -->
                        <section v-if="o.status === 'rejected'" class="section section-decision">
                            <div class="section-header">
                                <h3 class="section-title">{{ stepNumber(o, 'decision') }}. Décision du client</h3>
                                <span class="payment-badge refused">Devis refusé</span>
                            </div>
                            <p class="section-subtitle">Le client a refusé ce devis.</p>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : Règlement -->
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
                                    <span class="payment-grid-label">Reste à percevoir</span>
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
                                    <strong>Reste à percevoir : {{ formatPrice(remainingAmount(o)) }}</strong>
                                    <p>
                                        Ce montant sera réglé le jour de la livraison
                                        (ou du retrait) directement par le client.
                                    </p>
                                </div>
                            </div>
                            <div v-else class="balance-note complete">
                                <div class="balance-note-icon">✅</div>
                                <div class="balance-note-content">
                                    <strong>Commande intégralement réglée par le client.</strong>
                                    <p>Aucun montant à percevoir le jour de la livraison.</p>
                                </div>
                            </div>
                        </section>

                        <!-- ÉTAPE DYNAMIQUE : Documents -->
                        <section
                            v-if="o.quote_pdf_path || o.deposit_invoice_path || o.balance_invoice_path"
                            class="section section-documents"
                        >
                            <h3 class="section-title">{{ stepNumber(o, 'documents') }}. Documents</h3>
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

                        <!-- Refusée par l'admin (si pas de devis) -->
                        <div v-if="o.status === 'refused_by_merchant' && !o.quoted_price" class="alert warning">
                            <strong>Demande refusée</strong>
                            <p v-if="o.merchant_response">{{ o.merchant_response }}</p>
                        </div>

                        <!-- Annulée -->
                        <div v-if="o.status === 'cancelled'" class="alert warning">
                            <strong>Demande annulée</strong>
                            <p v-if="o.merchant_response">{{ o.merchant_response }}</p>
                        </div>

                        <!-- Terminée -->
                        <div v-if="o.status === 'completed'" class="alert success">
                            🎉 Commande terminée.
                        </div>

                        <!-- Actions selon le rôle et le statut -->
                        <div v-if="o.status === 'accepted'" class="status-actions">
                            <button class="btn-accept" @click="completeOrder(o)">
                                ✓ Marquer terminée
                            </button>
                            <button v-if="isAdmin" class="btn-reject" @click="cancelOrder(o)">
                                Annuler la demande
                            </button>
                        </div>
                    </div>
                </Transition>
            </article>
        </div>

        <!-- Modal de refus (admin uniquement) -->
        <div v-if="refuseModal && isAdmin" class="modal-overlay" @click.self="closeRefuseModal">
            <div class="modal">
                <header class="modal-head">
                    <h2>Refuser la demande {{ refuseModal.reference }}</h2>
                    <button class="modal-close" @click="closeRefuseModal">✕</button>
                </header>
                <textarea v-model="refuseReason" rows="3" placeholder="Motif du refus (visible par le client)…"></textarea>
                <p v-if="!refuseReason" class="hint">Le motif est obligatoire.</p>
                <p v-if="refuseError" class="error">{{ refuseError }}</p>
                <div class="modal-actions">
                    <button class="btn-secondary" @click="closeRefuseModal">Annuler</button>
                    <button class="btn-accept" :disabled="!refuseReason || refusing" @click="confirmRefuse">
                        {{ refusing ? 'Envoi…' : 'Confirmer le refus' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const isAdmin = computed(() => auth.user?.role === 'admin');

const orders = ref([]);
const loading = ref(true);
const quotes = reactive({});
const sending = reactive({});
const quoteError = reactive({});
const quoteSuccess = reactive({});
const openOrders = reactive({});

const refuseModal = ref(null);
const refuseReason = ref('');
const refuseError = ref(null);
const refusing = ref(false);

let pollInterval = null;

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p ?? 0);
}
function formatDate(d) {
    return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}
function statusLabel(s) {
    return {
        pending_quote: 'En attente',
        quoted: 'Devis envoyé',
        accepted: 'Acceptée',
        rejected: 'Refusée par le client',
        refused_by_merchant: 'Refusée',
        completed: 'Terminée',
        cancelled: 'Annulée',
    }[s] || s;
}
function canSendQuote(order) {
    const q = quotes[order.id];
    return q && q.price > 0 && q.response && q.response.trim().length > 0;
}

function remainingAmount(order) {
    return Math.max(0, (order.quoted_price ?? 0) - (order.amount_paid ?? 0));
}
function paymentProgress(order) {
    if (!order.quoted_price) return 0;
    return Math.min(100, Math.round((order.amount_paid / order.quoted_price) * 100));
}

/**
 * Numérotation dynamique des étapes par commande.
 * Seules les étapes réellement visibles sont comptées.
 */
function stepNumber(order, key) {
    const steps = [];
    if (order.quoted_price) steps.push('admin_response');
    if (['accepted', 'completed', 'rejected'].includes(order.status)) steps.push('decision');
    if (order.amount_paid > 0) steps.push('payment');
    if (order.quote_pdf_path || order.deposit_invoice_path || order.balance_invoice_path) steps.push('documents');

    const index = steps.indexOf(key);
    return index >= 0 ? index + 1 : '';
}

function toggleOrder(orderId) {
    openOrders[orderId] = !openOrders[orderId];
}

async function load() {
    try {
        const { data } = await axios.get('/api/merchant/custom-orders');
        orders.value = data;
        data.forEach((o, index) => {
            quotes[o.id] = { price: o.quoted_price || null, response: o.merchant_response || '' };
            sending[o.id] = false;
            quoteError[o.id] = null;
            quoteSuccess[o.id] = false;
            if (openOrders[o.id] === undefined) {
                openOrders[o.id] = index === 0;
            }
        });
    } catch {
        // garder la liste existante en cas d'erreur
    } finally {
        loading.value = false;
    }
}

async function sendQuote(order) {
    if (!canSendQuote(order)) return;
    sending[order.id] = true;
    quoteError[order.id] = null;
    quoteSuccess[order.id] = false;
    try {
        const { price, response } = quotes[order.id];
        const { data } = await axios.put(`/api/admin/custom-orders/${order.id}/quote`, {
            quoted_price: price,
            merchant_response: response,
        });
        Object.assign(order, data);
        quoteSuccess[order.id] = true;
        setTimeout(() => (quoteSuccess[order.id] = false), 3000);
    } catch (e) {
        quoteError[order.id] = e.response?.data?.message || 'Erreur.';
    } finally {
        sending[order.id] = false;
    }
}

function openRefuseModal(order) {
    refuseModal.value = order;
    refuseReason.value = '';
    refuseError.value = null;
}

function closeRefuseModal() {
    refuseModal.value = null;
    refuseReason.value = '';
    refuseError.value = null;
}

async function confirmRefuse() {
    if (!refuseReason.value.trim()) return;
    refusing.value = true;
    refuseError.value = null;
    try {
        const { data } = await axios.put(`/api/admin/custom-orders/${refuseModal.value.id}/refuse`, {
            reason: refuseReason.value,
        });
        Object.assign(refuseModal.value, data);
        closeRefuseModal();
    } catch (e) {
        refuseError.value = e.response?.data?.message || 'Erreur.';
    } finally {
        refusing.value = false;
    }
}

async function completeOrder(order) {
    if (!confirm(`Marquer la demande ${order.reference} comme terminée ?`)) return;
    try {
        const { data } = await axios.put(`/api/merchant/custom-orders/${order.id}/status`, { status: 'completed' });
        Object.assign(order, data);
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    }
}

async function cancelOrder(order) {
    if (!confirm(`Annuler la demande ${order.reference} ? Cette action est irréversible.`)) return;
    try {
        const { data } = await axios.put(`/api/admin/custom-orders/${order.id}/status`, { status: 'cancelled' });
        Object.assign(order, data);
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    }
}

onMounted(() => {
    load();
    pollInterval = setInterval(load, 15 * 60 * 1000);
    window.addEventListener('notification-clicked', handleNotificationClick);
});

onBeforeUnmount(() => {
    if (pollInterval) clearInterval(pollInterval);
    window.removeEventListener('notification-clicked', handleNotificationClick);
});

function handleNotificationClick(event) {
    if (event.detail.url === '/admin/sur-mesure') {
        load();
    }
}
</script>

<style scoped>
/* ✅ Tag livraison / retrait dans la ligne événement */
.delivery-tag {
    display: inline-block;
    margin-left: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* ✅ Bloc adresse — identique à OrdersPage.vue */
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
.admin-page { max-width: 1400px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); margin: 0; }
.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.orders-list { display: flex; flex-direction: column; gap: 1rem; }

/* Carte accordéon */
.card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.card:hover { box-shadow: var(--shadow-md); }
.card.open { box-shadow: var(--shadow-md); }

/* En-tête cliquable */
.card-header {
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
.card-header:hover { background: var(--cream); }

.header-left { display: flex; flex-direction: column; gap: 0.35rem; }
.ref { font-size: 1.05rem; color: var(--chocolate); }
.dates { display: flex; flex-direction: column; gap: 0.2rem; }
.date { color: var(--chocolate-soft); font-size: 0.85rem; font-weight: 500; }
.date-requested { color: var(--text-muted); font-size: 0.8rem; }

.header-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }

.status { padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; background: var(--cream-dark); color: var(--chocolate-soft); white-space: nowrap; }
.status.quoted { background: rgba(201, 123, 63, 0.15); color: var(--caramel-dark); }
.status.accepted { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.status.completed { background: rgba(5, 150, 105, 0.12); color: #059669; }
.status.rejected, .status.cancelled, .status.refused_by_merchant { background: rgba(201, 79, 109, 0.12); color: var(--raspberry); }

.chevron { font-size: 1rem; color: var(--text-muted); transition: transform 0.25s ease; }
.chevron.open { transform: rotate(180deg); }

/* Contenu dépliable */
.card-body {
    padding: 0 1.5rem 1.5rem;
    border-top: 1px solid var(--cream-dark);
}

/* Infos client */
.customer-box {
    background: var(--cream);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    margin-top: 1rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    border-left: 3px solid var(--caramel);
}
.customer-row { display: flex; gap: 1rem; font-size: 0.9rem; }
.customer-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    font-weight: 600;
    min-width: 80px;
}
.customer-row strong { color: var(--chocolate); }
.customer-row a { color: var(--caramel-dark); text-decoration: none; }
.customer-row a:hover { text-decoration: underline; }

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

/* Sections */
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

/* Formulaire devis */
.quote-form { display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1rem; }
.quote-form label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.85rem; font-weight: 600; color: var(--chocolate-soft); }
.quote-form input, .quote-form textarea {
    padding: 0.65rem 1rem;
    border: 1.5px solid var(--cream-dark);
    border-radius: var(--radius-sm);
    font-family: inherit;
    font-size: 0.9rem;
    background: white;
}
.quote-form input:focus, .quote-form textarea:focus { outline: none; border-color: var(--caramel); }
.quote-form textarea { resize: vertical; min-height: 70px; }

/* Décision */
.section-decision { background: white; border: 1.5px dashed var(--cream-dark); }

/* Boutons PDF */
.btn-doc {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.4rem;
    background: white;
    color: var(--caramel-dark);
    border: 2px solid var(--caramel);
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-doc:hover { background: var(--caramel); color: white; text-decoration: none; }

/* Actions */
.actions-row, .status-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem; }
.status-actions { padding-top: 1rem; border-top: 1px solid var(--cream-dark); }

.btn-accept, .btn-reject {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.75rem 1.25rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}
.btn-accept { background: #059669; color: white; border-color: #059669; }
.btn-accept:hover:not(:disabled) { background: #047857; border-color: #047857; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3); }
.btn-reject { background: white; color: var(--raspberry); border-color: var(--raspberry); }
.btn-reject:hover:not(:disabled) { background: var(--raspberry); color: white; }
.btn-accept:disabled, .btn-reject:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-secondary {
    background: white;
    color: var(--chocolate-soft);
    border: 1.5px solid var(--cream-dark);
    padding: 0.75rem 1.25rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary:hover { background: var(--cream); }

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
.payment-badge.refused { background: rgba(201, 79, 109, 0.12); color: var(--raspberry); }

.payment-bar-wrapper { display: flex; align-items: center; gap: 0.75rem; margin-top: 1rem; }
.payment-bar { flex: 1; height: 10px; background: var(--cream-dark); border-radius: 999px; overflow: hidden; }
.payment-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--caramel), var(--caramel-dark));
    border-radius: 999px;
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
.payment-bar-text { font-size: 0.85rem; font-weight: 700; color: var(--caramel-dark); }

/* Encadré reste à percevoir / intégralement réglé */
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
.alert strong { display: block; margin-bottom: 0.25rem; }
.alert p { margin: 0; font-size: 0.85rem; }

.hint { font-size: 0.8rem; color: var(--text-muted); font-style: italic; }
.error { color: var(--raspberry); font-size: 0.85rem; font-weight: 500; }
.success { color: #059669; font-size: 0.85rem; font-weight: 600; }

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

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(61, 40, 23, 0.5); display: flex; align-items: center; justify-content: center; z-index: 200; padding: 1rem; }
.modal { background: white; border-radius: var(--radius); padding: 2rem; max-width: 500px; width: 100%; box-shadow: var(--shadow-lg); display: flex; flex-direction: column; gap: 1rem; }
.modal-head { display: flex; justify-content: space-between; align-items: center; }
.modal-head h2 { font-size: 1.25rem; margin: 0; }
.modal-close { width: 32px; height: 32px; border-radius: 50%; background: var(--cream); color: var(--chocolate); font-size: 1rem; border: none; cursor: pointer; }
.modal-close:hover { background: var(--raspberry); color: white; }
.modal textarea { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); resize: vertical; }
.modal textarea:focus { outline: none; border-color: var(--caramel); background: white; }
.modal-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }

@media (max-width: 600px) {
    .card-header { flex-direction: column; align-items: flex-start; }
    .header-right { width: 100%; justify-content: space-between; }
    .status-actions, .actions-row { flex-direction: column; align-items: stretch; }
    .customer-row { flex-direction: column; gap: 0.15rem; }
    .customer-label { min-width: auto; }
}
</style>