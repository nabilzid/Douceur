<template>
    <div class="events-page">
        <header class="page-header">
            <span class="tag">Sur mesure</span>
            <h1>Créons votre événement</h1>
            <p>Mariage, anniversaire, baptême… Décrivez votre projet, nous vous répondons avec un devis personnalisé.</p>
        </header>

        <div v-if="loading" class="state">Chargement…</div>
        <div v-else class="grid">
            <article v-for="pkg in packages" :key="pkg.id" class="card">
                <div class="card-visual"><span>🎉</span></div>
                <div class="card-body">
                    <h2>{{ pkg.name }}</h2>
                    <p class="desc">{{ pkg.description }}</p>
                    <div class="footer">
                        <span class="price">à partir de {{ formatPrice(pkg.base_price) }}</span>
                        <RouterLink :to="`/evenements/${pkg.slug}/demande`" class="btn-primary small">
                            Demander un devis →
                        </RouterLink>
                    </div>
                </div>
            </article>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const packages = ref([]);
const loading = ref(true);

function formatPrice(p) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(p);
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/event-packages');
        packages.value = data;
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.page-header { text-align: center; margin-bottom: 3rem; }
.tag { display: inline-block; background: rgba(201, 123, 63, 0.1); color: var(--caramel-dark); padding: 0.35rem 0.9rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; margin-bottom: 1rem; }
.page-header h1 { font-size: 2.75rem; margin-bottom: 0.75rem; }
.page-header p { color: var(--text-muted); font-size: 1.05rem; max-width: 600px; margin: 0 auto; }
.state { text-align: center; padding: 4rem; color: var(--text-muted); }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; }
.card { background: white; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); transition: all 0.3s; display: flex; flex-direction: column; }
.card:hover { transform: translateY(-6px); box-shadow: var(--shadow-md); }
.card-visual { aspect-ratio: 16/9; background: linear-gradient(135deg, var(--cream-dark), rgba(232, 167, 106, 0.4)); display: flex; align-items: center; justify-content: center; font-size: 4rem; }
.card-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; flex: 1; }
.card-body h2 { font-size: 1.35rem; }
.desc { color: var(--text-muted); font-size: 0.9rem; flex: 1; }
.footer { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--cream-dark); }
.price { font-weight: 700; color: var(--chocolate); font-size: 1rem; }
.btn-primary.small { padding: 0.6rem 1.2rem; font-size: 0.85rem; }
@media (max-width: 900px) {
    .grid {
        grid-template-columns: 1fr;
    }
    .page-header h1 {
        font-size: 2rem;
    }
    .form-card {
        padding: 1.25rem;
    }
    .grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>