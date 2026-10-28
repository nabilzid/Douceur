<!-- Page évenements -->
<template>
    <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <!-- En-tête -->
        <header class="mx-auto mb-12 max-w-2xl text-center sm:mb-16">
            <span
                class="mb-5 inline-flex items-center gap-2 rounded-full bg-caramel/10 px-4 py-2 text-sm font-semibold text-caramel-dark"
            >
                <span>✦</span>
                Sur mesure
            </span>

            <h1 class="font-serif text-4xl font-semibold leading-tight tracking-tight text-chocolate sm:text-5xl lg:text-6xl">
                Créons votre événement
            </h1>

            <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-muted sm:text-lg">
                Mariage, anniversaire, baptême… Confiez-nous vos envies et
                imaginons ensemble une création gourmande qui vous ressemble.
            </p>

            <div class="mx-auto mt-7 h-1 w-16 rounded-full bg-caramel"></div>
        </header>

        <!-- Chargement -->
        <div
            v-if="loading"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="n in 3"
                :key="n"
                class="overflow-hidden rounded-3xl border border-cream-dark bg-white shadow-sm"
            >
                <div class="aspect-[16/9] animate-pulse bg-cream"></div>
                <div class="space-y-4 p-6">
                    <div class="h-6 w-2/3 animate-pulse rounded-lg bg-cream"></div>
                    <div class="h-4 w-full animate-pulse rounded-lg bg-cream"></div>
                    <div class="h-4 w-4/5 animate-pulse rounded-lg bg-cream"></div>
                    <div class="h-10 w-full animate-pulse rounded-xl bg-cream"></div>
                </div>
            </div>
        </div>

        <!-- Liste des formules -->
        <div
            v-else-if="packages.length"
            class="grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3"
        >
            <article
                v-for="pkg in packages"
                :key="pkg.id"
                class="group flex flex-col overflow-hidden rounded-3xl border border-cream-dark/70 bg-white shadow-soft transition duration-300 hover:-translate-y-1.5 hover:shadow-card"
            >
                <!-- Visuel -->
                <div
                    class="relative flex aspect-[16/9] items-center justify-center overflow-hidden bg-gradient-to-br from-cream via-[#f8e6d5] to-[#e8a76a]/40"
                >
                    <div
                        class="absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/30"
                    ></div>
                    <div
                        class="absolute -bottom-12 -left-8 h-40 w-40 rounded-full bg-caramel/10"
                    ></div>

                    <span
                        class="relative text-6xl transition duration-300 group-hover:scale-110 sm:text-7xl"
                        aria-hidden="true"
                    >
                        🎉
                    </span>

                    <span
                        class="absolute left-5 top-5 rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-semibold text-caramel-dark backdrop-blur"
                    >
                        Création personnalisée
                    </span>
                </div>

                <!-- Contenu -->
                <div class="flex flex-1 flex-col p-6 sm:p-7">
                    <h2 class="font-serif text-2xl font-semibold text-chocolate">
                        {{ pkg.name }}
                    </h2>

                    <p class="mt-3 flex-1 text-sm leading-6 text-muted">
                        {{ pkg.description }}
                    </p>

                    <div class="my-6 border-t border-cream-dark"></div>

                    <div class="flex flex-col gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-muted">
                                Tarif indicatif
                            </p>
                            <p class="mt-1 text-xl font-bold text-chocolate">
                                <span class="text-sm font-normal text-muted">À partir de </span>
                                {{ formatPrice(pkg.base_price) }}
                            </p>
                        </div>

                        <RouterLink
                            :to="`/evenements/${pkg.slug}/demande`"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-caramel px-5 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-caramel-dark focus:outline-none focus:ring-2 focus:ring-caramel focus:ring-offset-2"
                        >
                            Demander un devis
                            <span class="transition-transform group-hover:translate-x-1" aria-hidden="true">→</span>
                        </RouterLink>
                    </div>
                </div>
            </article>
        </div>

        <!-- Aucun forfait disponible -->
        <div
            v-else
            class="rounded-3xl border border-dashed border-cream-dark bg-white px-6 py-14 text-center"
        >
            <span class="text-5xl" aria-hidden="true">🍰</span>
            <h2 class="mt-5 font-serif text-2xl font-semibold text-chocolate">
                Nos formules arrivent bientôt
            </h2>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-muted">
                Nos propositions événementielles seront bientôt disponibles.
                Revenez nous voir prochainement !
            </p>
        </div>

        <!-- Bandeau de contact -->
        <section
            class="mt-12 overflow-hidden rounded-3xl bg-cream px-6 py-8 sm:px-10 sm:py-10 lg:mt-16"
        >
            <div class="flex flex-col items-center justify-between gap-6 text-center sm:flex-row sm:text-left">
                <div>
                    <h2 class="font-serif text-2xl font-semibold text-chocolate">
                        Un projet particulier ?
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                        Parlons de vos envies pour créer un événement gourmand
                        entièrement personnalisé.
                    </p>
                </div>

                <RouterLink
                    to="/contact"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-caramel bg-white px-6 py-3 font-semibold text-caramel-dark transition hover:bg-caramel hover:text-white"
                >
                    Nous contacter
                    <span aria-hidden="true">→</span>
                </RouterLink>
            </div>
        </section>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const packages = ref([]);
const loading = ref(true);

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(p);
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/event-packages');
        packages.value = data;
    } catch (error) {
        console.error('Erreur lors du chargement des formules événementielles :', error);
    } finally {
        loading.value = false;
    }
});
</script>
