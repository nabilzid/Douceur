<!-- Page d'accueil site -->
<template>
    <div class="mx-auto max-w-7xl">
        <!-- Hero -->
        <section
            class="relative grid items-center gap-10 overflow-hidden py-10 md:py-16 lg:grid-cols-2 lg:gap-14 lg:py-20"
        >
            <!-- Décoration de fond -->
            <div
                class="pointer-events-none absolute -left-20 top-10 h-64 w-64 rounded-full bg-caramel/10 blur-3xl"
            ></div>

            <div class="relative z-10 text-center lg:text-left">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-caramel/20 bg-caramel/10 px-4 py-2 text-xs font-semibold tracking-wide text-caramel-dark sm:text-sm"
                >
                    ✨ Artisanal & fait maison
                </span>

                <h1
                    class="mt-6 font-display text-4xl font-bold leading-tight tracking-tight text-chocolate sm:text-5xl lg:text-6xl"
                >
                    Des pâtisseries
                    <span class="block italic text-caramel">
                        qui fondent
                    </span>
                    de plaisir
                </h1>

                <p
                    class="mx-auto mt-6 max-w-xl text-base leading-7 text-muted sm:text-lg lg:mx-0"
                >
                    Commandez vos gâteaux, viennoiseries et douceurs préférés.
                    Des créations gourmandes préparées avec soin, en livraison
                    ou à retirer en boutique.
                </p>

                <div
                    class="mt-8 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start"
                >
                    <RouterLink
                        to="/catalogue"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-caramel px-7 py-3.5 text-sm font-semibold text-white shadow-soft transition-all hover:-translate-y-0.5 hover:bg-caramel-dark hover:shadow-card"
                    >
                        Découvrir le catalogue
                        <span aria-hidden="true">→</span>
                    </RouterLink>

                    <RouterLink
                        to="/evenements"
                        class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-cream-dark bg-white px-7 py-3.5 text-sm font-semibold text-chocolate transition-all hover:border-caramel hover:bg-cream"
                    >
                        Nos formules événements
                    </RouterLink>
                </div>

                <!-- Points forts -->
                <div
                    class="mt-9 flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm text-chocolate-soft lg:justify-start"
                >
                    <span class="inline-flex items-center gap-2">
                        <span class="text-caramel">✓</span>
                        Fait maison
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="text-caramel">✓</span>
                        Commande en ligne
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <span class="text-caramel">✓</span>
                        Pour toutes les occasions
                    </span>
                </div>
            </div>

            <!-- Visuel gourmand -->
            <div class="relative mx-auto w-full max-w-lg">
                <div
                    class="absolute inset-5 rounded-[2.5rem] bg-caramel/10 blur-2xl"
                ></div>

                <div
                    class="relative flex aspect-square items-center justify-center overflow-hidden rounded-[2rem] border border-white/80 bg-gradient-to-br from-cream via-white to-cream-dark shadow-card sm:rounded-[2.5rem]"
                >
                    <div
                        class="absolute left-6 top-6 h-20 w-20 rounded-full bg-caramel/10"
                    ></div>

                    <div
                        class="absolute bottom-8 right-8 h-28 w-28 rounded-full bg-raspberry/10"
                    ></div>

                    <div
                        class="relative z-10 flex flex-col items-center text-center"
                    >
                        <span
                            class="text-8xl drop-shadow-sm sm:text-9xl"
                            aria-hidden="true"
                        >
                            🍰
                        </span>

                        <p
                            class="mt-5 font-display text-2xl font-bold text-chocolate sm:text-3xl"
                        >
                            Un peu de douceur ?
                        </p>

                        <p class="mt-2 text-sm text-muted">
                            Le bonheur se partage aussi en gourmandises.
                        </p>
                    </div>

                    <div
                        class="absolute right-4 top-1/4 rotate-6 rounded-2xl bg-white px-4 py-3 shadow-soft sm:-right-2"
                    >
                        <p class="text-sm font-semibold text-chocolate">
                            ✨ Avec amour
                        </p>
                        <p class="mt-0.5 text-xs text-muted">
                            Pour vos moments précieux
                        </p>
                    </div>

                    <div
                        class="absolute bottom-6 left-4 -rotate-3 rounded-2xl bg-white px-4 py-3 shadow-soft sm:-left-2"
                    >
                        <p class="text-sm font-semibold text-chocolate">
                            🎂 Sur mesure
                        </p>
                        <p class="mt-0.5 text-xs text-muted">
                            Vos envies, nos créations
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Avantages -->
        <section class="pb-10 pt-4 md:pb-16">
            <div class="mb-8 text-center">
                <span
                    class="text-xs font-bold uppercase tracking-[0.2em] text-caramel-dark"
                >
                    L'esprit Douceur
                </span>

                <h2
                    class="mt-2 font-display text-2xl font-bold text-chocolate sm:text-3xl"
                >
                    La gourmandise à votre façon
                </h2>

                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-muted">
                    Une envie sucrée, un événement à célébrer ou simplement
                    le plaisir de se faire plaisir : nous sommes là pour vous.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-5">
                <article
                    class="group rounded-2xl border border-cream-dark/70 bg-white p-6 text-center shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-card sm:p-7"
                >
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-caramel/10 transition-colors group-hover:bg-caramel/15"
                    >
                        <span class="text-3xl" aria-hidden="true">🛍️</span>
                    </div>

                    <h3
                        class="mt-4 font-display text-lg font-bold text-chocolate"
                    >
                        Livraison & retrait
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-muted">
                        Choisissez le mode de récupération qui vous convient
                        au moment de votre commande.
                    </p>
                </article>

                <article
                    class="group rounded-2xl border border-cream-dark/70 bg-white p-6 text-center shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-card sm:p-7"
                >
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-caramel/10 transition-colors group-hover:bg-caramel/15"
                    >
                        <span class="text-3xl" aria-hidden="true">🥐</span>
                    </div>

                    <h3
                        class="mt-4 font-display text-lg font-bold text-chocolate"
                    >
                        Le plaisir du fait maison
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-muted">
                        Découvrez notre sélection de pâtisseries, gâteaux,
                        viennoiseries et autres douceurs.
                    </p>
                </article>

                <article
                    class="group rounded-2xl border border-cream-dark/70 bg-white p-6 text-center shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-card sm:p-7"
                >
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-caramel/10 transition-colors group-hover:bg-caramel/15"
                    >
                        <span class="text-3xl" aria-hidden="true">🎉</span>
                    </div>

                    <h3
                        class="mt-4 font-display text-lg font-bold text-chocolate"
                    >
                        Vos événements
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-muted">
                        Mariage, anniversaire ou autre célébration :
                        imaginez une formule adaptée à votre occasion.
                    </p>

                    <RouterLink
                        to="/evenements"
                        class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-caramel-dark transition-colors hover:text-caramel"
                    >
                        Découvrir les formules
                        <span aria-hidden="true">→</span>
                    </RouterLink>
                </article>
            </div>
        </section>

        <!-- Appel à l'action -->
        <section
            class="relative mb-8 overflow-hidden rounded-3xl bg-chocolate px-6 py-10 text-center sm:px-10 sm:py-12"
        >
            <div
                class="pointer-events-none absolute -right-10 -top-16 h-48 w-48 rounded-full bg-caramel/20 blur-2xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-raspberry/10 blur-2xl"
            ></div>

            <div class="relative z-10">
                <span class="text-3xl" aria-hidden="true">🍓</span>

                <h2
                    class="mt-3 font-display text-2xl font-bold text-white sm:text-3xl"
                >
                    Une envie de douceur ?
                </h2>

                <p
                    class="mx-auto mt-3 max-w-xl text-sm leading-6 text-white/75 sm:text-base"
                >
                    Parcourez notre catalogue et trouvez votre prochaine
                    gourmandise préférée.
                </p>

                <RouterLink
                    to="/catalogue"
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-caramel px-7 py-3.5 text-sm font-semibold text-white transition-all hover:-translate-y-0.5 hover:bg-caramel-dark"
                >
                    Explorer les gourmandises
                    <span aria-hidden="true">→</span>
                </RouterLink>
            </div>
        </section>
    </div>
</template>