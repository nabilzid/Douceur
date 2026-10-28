<!-- Page Mes demandes pour client connecté -->
<template>
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- En-tête de page -->
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <RouterLink
                    to="/mon-compte"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-muted transition hover:text-chocolate"
                >
                    <span aria-hidden="true">←</span>
                    Retour à mon compte
                </RouterLink>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark">
                    Espace client
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-chocolate sm:text-4xl">
                    Mes demandes sur mesure
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-muted sm:text-base">
                    Retrouvez votre demande initiale, la réponse du pâtissier, vos paiements et tous les documents associés.
                </p>
            </div>

            <RouterLink
                to="/evenements"
                class="inline-flex shrink-0 items-center justify-center rounded-full bg-caramel px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2"
            >
                Nouvelle demande
                <span class="ml-2" aria-hidden="true">→</span>
            </RouterLink>
        </header>

        <!-- Chargement -->
        <section
            v-if="loading"
            class="rounded-3xl border border-cream-dark bg-white p-10 text-center shadow-soft"
            aria-live="polite"
        >
            <span class="mx-auto block h-9 w-9 animate-spin rounded-full border-4 border-cream-dark border-t-caramel" />
            <p class="mt-4 text-sm text-muted">Chargement de vos demandes…</p>
        </section>

        <!-- Erreur de chargement -->
        <section
            v-else-if="loadError"
            class="rounded-3xl border border-rose-200 bg-rose-50 p-6 text-center sm:p-8"
            role="alert"
        >
            <p class="text-base font-semibold text-rose-800">Vos demandes n’ont pas pu être chargées.</p>
            <p class="mt-2 text-sm text-rose-700">{{ loadError }}</p>
            <button
                type="button"
                class="mt-5 inline-flex items-center justify-center rounded-full bg-chocolate px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"
                @click="loadOrders"
            >
                Réessayer
            </button>
        </section>

        <!-- Aucune demande -->
        <section
            v-else-if="orders.length === 0"
            class="rounded-3xl border border-cream-dark bg-white px-5 py-14 text-center shadow-soft sm:px-10"
        >
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cream text-3xl" aria-hidden="true">
                🎂
            </div>
            <h2 class="mt-5 text-xl font-bold text-chocolate">Aucune demande pour le moment</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">
                Vos demandes personnalisées et les devis des pâtissiers apparaîtront ici.
            </p>
            <RouterLink
                to="/evenements"
                class="mt-6 inline-flex items-center justify-center rounded-full bg-caramel px-5 py-3 text-sm font-semibold text-white transition hover:bg-caramel-dark"
            >
                Découvrir les formules
                <span class="ml-2" aria-hidden="true">→</span>
            </RouterLink>
        </section>

        <!-- Liste des commandes -->
        <section v-else class="space-y-5">
            <article
                v-for="o in orders"
                :key="o.id"
                class="rounded-3xl border border-cream-dark/80 bg-white shadow-soft transition-shadow duration-200 hover:shadow-card"
            >
                <!-- Résumé toujours visible -->
                <button
                    type="button"
                    class="flex w-full flex-col gap-4 p-4 text-left transition hover:bg-cream/60 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-caramel sm:flex-row sm:items-center sm:justify-between sm:p-6"
                    :aria-expanded="Boolean(openOrders[o.id])"
                    :aria-controls="`order-panel-${o.id}`"
                    @click="toggleOrder(o.id)"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-bold text-chocolate sm:text-lg">
                                Demande {{ o.reference }}
                            </span>
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClasses(o.status)"
                            >
                                {{ statusLabel(o.status) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-muted">
                            <span aria-hidden="true">🎂</span>
                            {{ eventLabel(o.event_type) || o.event_label || 'Demande personnalisée' }}
                            <span class="mx-1 text-cream-dark">·</span>
                            <span aria-hidden="true">📅</span>
                            {{ formatDate(o.event_date) }}
                            <span v-if="o.guests_count" class="mx-1">
                                · {{ o.guests_count }} personne{{ o.guests_count > 1 ? 's' : '' }}
                            </span>
                        </p>
                        <p v-if="o.quoted_price != null" class="mt-2 text-sm font-semibold text-chocolate">
                            Devis : {{ formatPrice(o.quoted_price) }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center justify-between gap-4 sm:justify-end">
                        <span class="text-sm font-medium text-muted">
                            {{ openOrders[o.id] ? 'Masquer les détails' : 'Voir les détails' }}
                        </span>
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-full bg-cream text-chocolate transition-transform duration-200"
                            :class="{ 'rotate-180': openOrders[o.id] }"
                            aria-hidden="true"
                        >
                            ▾
                        </span>
                    </div>
                </button>

                <Transition name="accordion">
                    <div
                        v-if="openOrders[o.id]"
                        :id="`order-panel-${o.id}`"
                        class="space-y-5 border-t border-cream-dark/80 p-4 sm:p-6"
                    >
                        <!-- 1. Demande du client : section explicite et prioritaire -->
                        <section class="overflow-hidden rounded-2xl border border-cream-dark bg-cream">
                            <div class="flex items-start gap-3 border-b border-cream-dark/80 px-4 py-4 sm:px-5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-caramel text-base font-bold text-white" aria-label="Étape 1">
                                    1
                                </span>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                                    📝
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">
                                        Votre demande initiale
                                    </p>
                                    <h2 class="mt-1 text-lg font-bold text-chocolate">Ce que vous avez demandé</h2>
                                    <p class="mt-1 text-sm text-muted">
                                        Référence : {{ o.reference }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4 p-4 sm:p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white px-3 py-1.5 text-sm font-semibold text-chocolate">
                                        {{ o.event_type ? eventLabel(o.event_type) : (o.event_label || 'Événement personnalisé') }}
                                    </span>
                                    <span v-if="o.guests_count" class="rounded-full bg-white px-3 py-1.5 text-sm text-chocolate-soft">
                                        {{ o.guests_count }} personne{{ o.guests_count > 1 ? 's' : '' }}
                                    </span>
                                    <span class="rounded-full bg-white px-3 py-1.5 text-sm text-chocolate-soft">
                                        {{ o.delivery_type === 'delivery' ? '🚚 Livraison' : '🏪 Click & Collect' }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-muted">
                                        Votre description
                                    </h3>
                                    <p class="whitespace-pre-line break-words rounded-xl border border-cream-dark/80 bg-white p-4 text-sm leading-7 text-chocolate">
                                        {{ o.description || 'Aucune description renseignée.' }}
                                    </p>
                                </div>

                                <div
                                    v-if="o.delivery_type === 'delivery'"
                                    class="rounded-xl border-l-4 border-caramel bg-white p-4"
                                >
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-muted">
                                        Adresse de livraison
                                    </h3>
                                    <p class="mt-2 text-sm font-semibold text-chocolate">
                                        {{ o.delivery_address || 'Adresse non renseignée' }}
                                    </p>
                                    <p class="text-sm text-chocolate-soft">
                                        {{ [o.delivery_postal_code, o.delivery_city].filter(Boolean).join(' ') }}
                                    </p>
                                    <p v-if="o.delivery_notes" class="mt-3 whitespace-pre-line text-sm leading-6 text-muted">
                                        <span class="font-semibold text-chocolate">Précisions :</span>
                                        {{ o.delivery_notes }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 2. Réponse du pâtissier -->
                        <section
                            v-if="hasMerchantResponse(o)"
                            class="overflow-hidden rounded-2xl border border-caramel/40 bg-white"
                        >
                            <div class="flex items-start gap-3 border-b border-cream-dark bg-caramel/10 px-4 py-4 sm:px-5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-caramel text-base font-bold text-white" aria-label="Étape 2">
                                    2
                                </span>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                                    🧁
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">
                                        Réponse du pâtissier
                                    </p>
                                    <h2 class="mt-1 text-lg font-bold text-chocolate">La proposition pour votre commande</h2>
                                    <p class="mt-1 text-sm text-muted">
                                        Le prix et le message du pâtissier sont présentés ci-dessous.
                                    </p>
                                </div>
                                <div v-if="o.quoted_price != null" class="shrink-0 text-right">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Prix proposé</p>
                                    <p class="mt-1 text-xl font-bold text-chocolate sm:text-2xl">
                                        {{ formatPrice(o.quoted_price) }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4 p-4 sm:p-5">
                                <div>
                                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-muted">
                                        Message et description du pâtissier
                                    </h3>
                                    <p class="whitespace-pre-line break-words rounded-xl border border-caramel/20 bg-cream/70 p-4 text-sm leading-7 text-chocolate">
                                        {{ o.merchant_response || 'Le pâtissier n’a pas ajouté de message.' }}
                                    </p>
                                </div>

                                <div
                                    v-if="o.status === 'quoted'"
                                    class="flex items-start gap-3 rounded-xl border border-caramel/20 bg-cream p-4"
                                >
                                    <span class="text-xl" aria-hidden="true">💡</span>
                                    <p class="text-sm leading-6 text-chocolate-soft">
                                        Prenez connaissance de la proposition et des documents disponibles avant de prendre votre décision.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Réponse d'attente si le pâtissier n'a pas encore envoyé de devis -->
                        <section
                            v-else-if="o.status === 'pending_quote'"
                            class="flex items-start gap-3 rounded-2xl border border-cream-dark bg-white p-4 sm:p-5"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cream text-xl" aria-hidden="true">
                                ⏳
                            </span>
                            <div>
                                <h2 class="font-bold text-chocolate">En attente de la réponse du pâtissier</h2>
                                <p class="mt-1 text-sm leading-6 text-muted">
                                    Votre demande est enregistrée. La proposition du pâtissier apparaîtra ici dès qu’elle sera disponible.
                                </p>
                            </div>
                        </section>

                        <!-- 3. Décision du client -->
                        <section
                            v-if="o.status === 'quoted'"
                            class="rounded-2xl border-2 border-dashed border-cream-dark bg-white p-4 sm:p-5"
                        >
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cream text-xl" aria-hidden="true">
                                    ⚖️
                                </span>
                                <div>
                                    <h2 class="text-lg font-bold text-chocolate">
                                        {{ stepNumber(o, 'decision') }}. Votre décision
                                    </h2>
                                    <p class="mt-1 text-sm leading-6 text-muted">
                                        Choisissez le montant à régler pour accepter ce devis.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <button
                                    v-for="pct in [20, 50, 100]"
                                    :key="pct"
                                    type="button"
                                    :aria-pressed="paymentPercentages[o.id] === pct"
                                    :class="[
                                        'flex min-h-24 flex-col items-center justify-center gap-2 rounded-xl border-2 p-4 text-center transition focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2',
                                        paymentPercentages[o.id] === pct
                                            ? 'border-caramel bg-caramel text-white shadow-soft'
                                            : 'border-cream-dark bg-cream text-chocolate hover:border-caramel hover:bg-white',
                                    ]"
                                    @click="paymentPercentages[o.id] = pct"
                                >
                                    <span class="text-sm font-semibold">
                                        {{ pct === 100 ? 'Payer la totalité' : `Acompte ${pct} %` }}
                                    </span>
                                    <span class="text-lg font-bold">
                                        {{ formatPrice(Number(o.quoted_price || 0) * pct / 100) }}
                                    </span>
                                </button>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="rounded-xl border border-cream-dark bg-cream p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Total du devis</p>
                                    <p class="mt-2 text-lg font-bold text-chocolate">{{ formatPrice(o.quoted_price) }}</p>
                                </div>
                                <div class="rounded-xl border border-caramel/30 bg-caramel/10 p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-caramel-dark">
                                        {{ paymentPercentages[o.id] === 100 ? 'Montant total à régler' : `Montant à régler (${paymentPercentages[o.id]} %)` }}
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-chocolate">
                                        {{ formatPrice(Number(o.quoted_price || 0) * Number(paymentPercentages[o.id] || 20) / 100) }}
                                    </p>
                                </div>
                                <div
                                    v-if="paymentPercentages[o.id] < 100"
                                    class="rounded-xl border border-cream-dark bg-white p-4"
                                >
                                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Solde après acompte</p>
                                    <p class="mt-2 text-lg font-bold text-chocolate">
                                        {{ formatPrice(Number(o.quoted_price || 0) * (100 - Number(paymentPercentages[o.id] || 20)) / 100) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <button
                                    type="button"
                                    class="inline-flex flex-1 items-center justify-center rounded-full bg-emerald-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="responding[o.id]"
                                    @click="respond(o, 'accept')"
                                >
                                    <span v-if="responding[o.id]">Traitement en cours…</span>
                                    <span v-else-if="paymentPercentages[o.id] === 100">
                                        ✓ Accepter et payer la totalité
                                    </span>
                                    <span v-else>
                                        ✓ Accepter et payer l’acompte
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-full border border-rose-300 bg-white px-5 py-3.5 text-sm font-bold text-rose-700 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="responding[o.id]"
                                    @click="respond(o, 'reject')"
                                >
                                    ✕ Refuser le devis
                                </button>
                            </div>

                            <p class="mt-4 rounded-xl bg-cream p-4 text-xs leading-6 text-muted">
                                <template v-if="paymentPercentages[o.id] === 100">
                                    Vous réglerez la totalité en ligne. Aucun solde ne restera à régler le jour de la livraison ou du retrait.
                                </template>
                                <template v-else>
                                    Seul l’acompte de
                                    {{ formatPrice(Number(o.quoted_price || 0) * Number(paymentPercentages[o.id] || 20) / 100) }}
                                    est prévu en ligne aujourd’hui. Le solde de
                                    {{ formatPrice(Number(o.quoted_price || 0) * (100 - Number(paymentPercentages[o.id] || 20)) / 100) }}
                                    sera à régler le jour de la livraison ou du retrait, directement auprès du pâtissier.
                                </template>
                            </p>
                        </section>

                        <!-- 4. Règlement enregistré -->
                        <section
                            v-if="Number(o.amount_paid || 0) > 0"
                            class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 sm:p-5"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                                        💳
                                    </span>
                                    <div>
                                        <h2 class="text-lg font-bold text-chocolate">
                                            {{ stepNumber(o, 'payment') }}. Règlement
                                        </h2>
                                        <p class="mt-1 text-sm text-muted">
                                            Le paiement enregistré pour cette commande.
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex w-fit items-center rounded-full px-3 py-1.5 text-xs font-bold"
                                    :class="remainingAmount(o) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                >
                                    {{ remainingAmount(o) > 0 ? 'Acompte versé' : 'Payé intégralement' }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-cream-dark bg-white p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-muted">Total du devis</p>
                                    <p class="mt-2 text-lg font-bold text-chocolate">{{ formatPrice(o.quoted_price) }}</p>
                                </div>
                                <div class="rounded-xl border border-emerald-200 bg-white p-4">
                                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                        {{ remainingAmount(o) > 0 ? 'Acompte versé' : 'Montant versé' }}
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-emerald-700">{{ formatPrice(o.amount_paid) }}</p>
                                </div>
                                <div
                                    v-if="remainingAmount(o) > 0"
                                    class="rounded-xl border border-rose-200 bg-white p-4"
                                >
                                    <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Reste à payer</p>
                                    <p class="mt-2 text-lg font-bold text-rose-700">{{ formatPrice(remainingAmount(o)) }}</p>
                                </div>
                            </div>

                            <div class="mt-5 flex items-center gap-3">
                                <div
                                    class="h-3 flex-1 overflow-hidden rounded-full bg-emerald-100"
                                    role="progressbar"
                                    :aria-valuenow="paymentProgress(o)"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    :aria-label="`Paiement effectué à ${paymentProgress(o)} %`"
                                >
                                    <div
                                        class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                                        :style="{ width: `${paymentProgress(o)}%` }"
                                    />
                                </div>
                                <span class="w-12 text-right text-sm font-bold text-emerald-800">
                                    {{ paymentProgress(o) }} %
                                </span>
                            </div>

                            <div
                                class="mt-4 flex items-start gap-3 rounded-xl p-4"
                                :class="remainingAmount(o) > 0 ? 'border border-amber-200 bg-amber-50' : 'border border-emerald-200 bg-white'"
                            >
                                <span class="text-xl" aria-hidden="true">{{ remainingAmount(o) > 0 ? '💡' : '✅' }}</span>
                                <div>
                                    <p class="font-semibold text-chocolate">
                                        {{ remainingAmount(o) > 0
                                            ? `Reste à payer : ${formatPrice(remainingAmount(o))}`
                                            : 'Votre commande est intégralement réglée.' }}
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-muted">
                                        {{ remainingAmount(o) > 0
                                            ? 'Le solde sera à régler le jour de la livraison ou du retrait, directement auprès du pâtissier.'
                                            : 'Aucun montant ne devrait rester à régler le jour de la livraison ou du retrait.' }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 5. Documents : les trois lignes restent visibles dès qu'un document existe -->
                        <section
                            v-if="hasAnyDocument(o)"
                            class="overflow-hidden rounded-2xl border border-cream-dark bg-white"
                        >
                            <div class="flex items-start gap-3 border-b border-cream-dark bg-cream/70 px-4 py-4 sm:px-5">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-xl" aria-hidden="true">
                                    📄
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-caramel-dark">Fichiers et justificatifs</p>
                                    <h2 class="mt-1 text-lg font-bold text-chocolate">
                                        {{ stepNumber(o, 'documents') }}. Mes documents
                                    </h2>
                                    <p class="mt-1 text-sm text-muted">
                                        Consultez les documents disponibles pour cette demande.
                                    </p>
                                </div>
                            </div>

                            <div class="divide-y divide-cream-dark/80 px-4 sm:px-5">
                                <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-lg" aria-hidden="true">📑</span>
                                        <div>
                                            <p class="font-semibold text-chocolate">Devis</p>
                                            <p class="mt-1 text-sm text-muted">Document commercial initial</p>
                                        </div>
                                    </div>
                                    <a
                                        v-if="o.quote_pdf_path"
                                        :href="documentUrl(o, 'quote')"
                                        target="_blank"
                                        class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-cream"
                                    >
                                        Consulter le devis <span class="ml-2" aria-hidden="true">↗</span>
                                    </a>
                                    <span v-else class="text-sm text-muted">Pas encore disponible</span>
                                </div>

                                <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-lg" aria-hidden="true">🧾</span>
                                        <div>
                                            <p class="font-semibold text-chocolate">Facture d’acompte</p>
                                            <p class="mt-1 text-sm text-muted">Justificatif du premier règlement</p>
                                        </div>
                                    </div>
                                    <a
                                        v-if="o.deposit_invoice_path"
                                        :href="documentUrl(o, 'deposit')"
                                        target="_blank"
                                        class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-cream"
                                    >
                                        Consulter la facture <span class="ml-2" aria-hidden="true">↗</span>
                                    </a>
                                    <span v-else class="text-sm text-muted">Émise après le paiement, si applicable</span>
                                </div>

                                <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-lg" aria-hidden="true">🧾</span>
                                        <div>
                                            <p class="font-semibold text-chocolate">Facture de solde</p>
                                            <p class="mt-1 text-sm text-muted">Document final après livraison ou retrait</p>
                                        </div>
                                    </div>
                                    <a
                                        v-if="o.balance_invoice_path"
                                        :href="documentUrl(o, 'balance')"
                                        target="_blank"
                                        class="inline-flex items-center justify-center rounded-full border border-caramel px-4 py-2 text-sm font-semibold text-caramel-dark transition hover:bg-cream"
                                    >
                                        Consulter la facture <span class="ml-2" aria-hidden="true">↗</span>
                                    </a>
                                    <span v-else class="text-sm text-muted">À venir</span>
                                </div>
                            </div>
                        </section>

                        <!-- Documents en attente si aucun fichier n'est encore attaché -->
                        <section
                            v-else-if="o.status === 'quoted' || o.status === 'accepted' || Number(o.amount_paid || 0) > 0"
                            class="rounded-2xl border border-cream-dark bg-white p-4 sm:p-5"
                        >
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cream text-xl" aria-hidden="true">
                                    📄
                                </span>
                                <div>
                                    <h2 class="font-bold text-chocolate">Documents</h2>
                                    <p class="mt-1 text-sm leading-6 text-muted">
                                        Aucun fichier n’est encore disponible pour cette demande. Les liens apparaîtront ici dès que les documents auront été générés.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Messages de statut -->
                        <section
                            v-if="o.status === 'accepted'"
                            class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4"
                            role="status"
                        >
                            <span class="text-xl" aria-hidden="true">✓</span>
                            <div>
                                <h2 class="font-bold text-blue-900">Devis accepté</h2>
                                <p class="mt-1 text-sm leading-6 text-blue-800">
                                    Vous avez accepté ce devis. Le pâtissier va réaliser votre commande.
                                </p>
                            </div>
                        </section>

                        <section
                            v-if="o.status === 'completed'"
                            class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                            role="status"
                        >
                            <span class="text-xl" aria-hidden="true">🎉</span>
                            <div>
                                <h2 class="font-bold text-emerald-900">Commande terminée</h2>
                                <p class="mt-1 text-sm leading-6 text-emerald-800">Merci pour votre confiance !</p>
                            </div>
                        </section>

                        <section
                            v-if="o.status === 'rejected'"
                            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                            role="status"
                        >
                            <span class="text-xl" aria-hidden="true">ℹ️</span>
                            <div>
                                <h2 class="font-bold text-amber-900">Devis refusé</h2>
                                <p class="mt-1 text-sm leading-6 text-amber-800">Vous avez refusé ce devis.</p>
                            </div>
                        </section>

                        <section
                            v-if="o.status === 'refused_by_merchant'"
                            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                            role="status"
                        >
                            <span class="text-xl" aria-hidden="true">ℹ️</span>
                            <div>
                                <h2 class="font-bold text-amber-900">Demande refusée par le pâtissier</h2>
                                <p class="mt-1 text-sm leading-6 text-amber-800">
                                    Cette demande a été refusée.
                                    <span v-if="o.merchant_response" class="mt-1 block">
                                        Motif : {{ o.merchant_response }}
                                    </span>
                                </p>
                            </div>
                        </section>

                        <section
                            v-if="o.status === 'cancelled'"
                            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                            role="status"
                        >
                            <span class="text-xl" aria-hidden="true">ℹ️</span>
                            <div>
                                <h2 class="font-bold text-amber-900">Demande annulée</h2>
                                <p class="mt-1 text-sm leading-6 text-amber-800">Cette demande a été annulée.</p>
                            </div>
                        </section>
                    </div>
                </Transition>
            </article>
        </section>
    </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const orders = ref([]);
const loading = ref(true);
const loadError = ref('');
const responding = reactive({});
const openOrders = reactive({});
const paymentPercentages = reactive({});

function formatPrice(value) {
    const amount = Number(value ?? 0);
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number.isFinite(amount) ? amount : 0);
}

function formatDate(value) {
    if (!value) return 'Date non renseignée';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return 'Date non renseignée';

    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function eventLabel(type) {
    return {
        birthday: 'Anniversaire',
        wedding: 'Mariage',
        baptism: 'Baptême',
        corporate: 'Entreprise',
        other: 'Autre',
    }[type] || type || '';
}

function statusLabel(status) {
    return {
        pending_quote: 'En attente de devis',
        quoted: 'Devis reçu',
        accepted: 'Acceptée',
        completed: 'Terminée',
        rejected: 'Refusée',
        refused_by_merchant: 'Refusée par le pâtissier',
        cancelled: 'Annulée',
    }[status] || status || 'Statut inconnu';
}

function statusClasses(status) {
    return {
        pending_quote: 'bg-amber-100 text-amber-800',
        quoted: 'bg-caramel/15 text-caramel-dark',
        accepted: 'bg-blue-100 text-blue-800',
        completed: 'bg-emerald-100 text-emerald-800',
        rejected: 'bg-rose-100 text-rose-800',
        refused_by_merchant: 'bg-rose-100 text-rose-800',
        cancelled: 'bg-gray-100 text-gray-700',
    }[status] || 'bg-cream text-chocolate-soft';
}

function remainingAmount(order) {
    const total = Number(order.quoted_price ?? 0);
    const paid = Number(order.amount_paid ?? 0);
    return Math.max(0, total - paid);
}

function paymentProgress(order) {
    const total = Number(order.quoted_price ?? 0);
    const paid = Number(order.amount_paid ?? 0);
    if (total <= 0) return 0;
    return Math.min(100, Math.round((paid / total) * 100));
}

function toggleOrder(orderId) {
    openOrders[orderId] = !openOrders[orderId];
}

function hasMerchantResponse(order) {
    return order.quoted_price != null
        || Boolean(order.merchant_response)
        || Boolean(order.quote_pdf_path)
        || Boolean(order.deposit_invoice_path)
        || Boolean(order.balance_invoice_path);
}

function hasAnyDocument(order) {
    return Boolean(
        order.quote_pdf_path
        || order.deposit_invoice_path
        || order.balance_invoice_path
    );
}

function documentUrl(order, kind) {
    const reference = encodeURIComponent(order.reference);

    const routes = {
        quote: `/api/custom-orders/${reference}/quote-pdf`,
        deposit: `/api/custom-orders/${reference}/invoice/deposit`,
        balance: `/api/custom-orders/${reference}/invoice/balance`,
    };

    return routes[kind] || '#';
}

/**
 * Numérotation dynamique : seules les étapes présentes pour la commande
 * sont prises en compte.
 */
function stepNumber(order, key) {
    const steps = [];

    if (hasMerchantResponse(order)) steps.push('quote');
    if (order.status === 'quoted') steps.push('decision');
    if (Number(order.amount_paid ?? 0) > 0) steps.push('payment');
    if (hasAnyDocument(order)) steps.push('documents');

    const index = steps.indexOf(key);
    return index >= 0 ? index + 1 : '';
}

async function loadOrders() {
    loading.value = true;
    loadError.value = '';

    try {
        const { data } = await axios.get('/api/custom-orders/mine');
        const list = Array.isArray(data) ? data : [];

        orders.value = list;

        list.forEach((order, index) => {
            if (typeof openOrders[order.id] !== 'boolean') {
                openOrders[order.id] = index === 0;
            }

            if (typeof responding[order.id] !== 'boolean') {
                responding[order.id] = false;
            }

            if (![20, 50, 100].includes(Number(paymentPercentages[order.id]))) {
                paymentPercentages[order.id] = 20;
            }
        });
    } catch (error) {
        console.error('Erreur lors du chargement des demandes sur mesure :', error);
        loadError.value =
            error.response?.data?.message
            || 'Une erreur est survenue lors du chargement de vos demandes. Veuillez réessayer.';
    } finally {
        loading.value = false;
    }
}

async function respond(order, decision) {
    if (responding[order.id]) return;

    if (decision === 'accept' && ![20, 50, 100].includes(Number(paymentPercentages[order.id]))) {
        window.alert('Veuillez choisir un pourcentage de paiement valide.');
        return;
    }

    if (decision === 'reject') {
        const confirmed = window.confirm('Voulez-vous vraiment refuser ce devis ?');
        if (!confirmed) return;
    }

    responding[order.id] = true;

    try {
        const payload = { decision };

        if (decision === 'accept') {
            payload.payment_percentage = Number(paymentPercentages[order.id]);
        }

        const { data } = await axios.put(
            `/api/custom-orders/${encodeURIComponent(order.reference)}/respond`,
            payload,
        );

        if (data?.checkout_url) {
            window.location.href = data.checkout_url;
            return;
        }

        if (data && typeof data === 'object') {
            Object.assign(order, data);
        }

        // Recharger les informations depuis le serveur pour refléter le statut,
        // les paiements et les documents les plus récents.
        await loadOrders();
    } catch (error) {
        window.alert(
            error.response?.data?.message
            || 'Une erreur est survenue lors de la réponse au devis.',
        );
    } finally {
        responding[order.id] = false;
    }
}

onMounted(loadOrders);
</script>
