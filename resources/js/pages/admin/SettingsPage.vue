<template>
    <div class="admin-page">
        <header class="page-header">
            <div>
                <h1>Paramètres</h1>
                <p class="subtitle">Configuration globale de la boutique</p>
            </div>
        </header>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement…</p>
        </div>

        <form v-else class="settings-form" @submit.prevent="save">
            <section class="card">
                <h2>🚚 Livraison</h2>
                <div class="form-grid">
                    <label>
                        Montant minimum de commande (€)
                        <input v-model.number="form.delivery_min_order_amount" type="number" min="0" step="0.01" required />
                    </label>
                    <label>
                        Seuil de livraison gratuite (€)
                        <input v-model.number="form.delivery_free_threshold" type="number" min="0" step="0.01" required />
                    </label>
                    <label>
                        Frais de livraison (€)
                        <input v-model.number="form.delivery_default_fee" type="number" min="0" step="0.01" required />
                    </label>
                </div>
            </section>

            <section class="card">
                <h2>💰 Taxes</h2>
                <div class="form-grid">
                    <label>
                        Taux de TVA (%)
                        <input v-model.number="form.tax_vat_rate" type="number" min="0" step="0.1" required />
                    </label>
                </div>
            </section>

            <section class="card">
                <h2>🎂 Commandes sur mesure</h2>
                <div class="form-grid">
                    <label>
                        Délai minimum (jours)
                        <input v-model.number="form.custom_order_min_lead_days" type="number" min="1" required />
                    </label>
                </div>
            </section>

            <p v-if="error" class="error">{{ error }}</p>
            <p v-if="success" class="success">✓ Paramètres enregistrés.</p>
            <button type="submit" class="btn-primary" :disabled="saving">
                {{ saving ? 'Enregistrement…' : 'Enregistrer les paramètres' }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const form = reactive({
    delivery_min_order_amount: 15,
    delivery_free_threshold: 30,
    delivery_default_fee: 5,
    tax_vat_rate: 5.5,
    custom_order_min_lead_days: 3,
});
const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const success = ref(false);

async function save() {
    saving.value = true;
    error.value = null;
    success.value = false;
    try {
        await axios.put('/api/admin/settings', form);
        success.value = true;
        setTimeout(() => (success.value = false), 3000);
    } catch (e) {
        error.value = e.response?.data?.message || 'Erreur.';
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/admin/settings');
        Object.assign(form, data);
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.admin-page { max-width: 900px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); margin: 0; }
.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.settings-form { display: flex; flex-direction: column; gap: 1.5rem; }
.card { background: white; border-radius: var(--radius); padding: 1.75rem; box-shadow: var(--shadow-sm); }
.card h2 { font-size: 1.2rem; margin-bottom: 1.25rem; color: var(--chocolate); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
label { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); }
input { padding: 0.75rem 1rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-family: inherit; font-size: 0.95rem; background: var(--cream); }
input:focus { outline: none; border-color: var(--caramel); background: white; }
.error { color: var(--raspberry); font-weight: 500; }
.success { color: #059669; font-weight: 600; }
.btn-primary { align-self: flex-start; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
</style>