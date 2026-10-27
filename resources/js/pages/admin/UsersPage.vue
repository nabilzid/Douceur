<template>
    <div class="admin-page">
        <header class="page-header">
            <div>
                <h1>Utilisateurs</h1>
                <p class="subtitle">{{ users.length }} utilisateur(s) inscrit(s)</p>
            </div>
        </header>

        <div v-if="loading" class="state">
            <div class="spinner"></div>
            <p>Chargement…</p>
        </div>

        <div v-else-if="error" class="state error">{{ error }}</div>

        <div v-else class="table-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>
                            <div class="user-cell">
                                <div class="avatar">{{ initials(user.name) }}</div>
                                <strong>{{ user.name }}</strong>
                            </div>
                        </td>
                        <td class="email">{{ user.email }}</td>
                        <td>
                            <select :value="user.role" @change="changeRole(user, $event.target.value)">
                                <option value="customer">Client</option>
                                <option value="merchant">Commerçant</option>
                                <option value="admin">Admin</option>
                            </select>
                        </td>
                        <td class="date">{{ formatDate(user.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const users = ref([]);
const loading = ref(true);
const error = ref(null);

function initials(name) {
    if (!name) return '?';
    return name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase();
}
function formatDate(d) { return new Date(d).toLocaleDateString('fr-FR'); }

async function changeRole(user, role) {
    try {
        await axios.put(`/api/admin/users/${user.id}/role`, { role });
        user.role = role;
    } catch (e) {
        alert(e.response?.data?.message || 'Erreur.');
    }
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/admin/users');
        users.value = data;
    } catch (e) {
        error.value = e.response?.data?.message || 'Erreur de chargement.';
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.admin-page { max-width: 1400px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 2.25rem; margin: 0 0 0.5rem; color: var(--chocolate); }
.subtitle { color: var(--text-muted); margin: 0; }
.state { text-align: center; padding: 4rem 1rem; color: var(--text-muted); }
.state.error { color: var(--raspberry); }
.spinner { width: 48px; height: 48px; border: 4px solid var(--cream-dark); border-top-color: var(--caramel); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.table-wrapper { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.users-table { width: 100%; border-collapse: collapse; }
.users-table th, .users-table td { padding: 1rem 1.25rem; text-align: left; font-size: 0.9rem; border-bottom: 1px solid var(--cream-dark); }
.users-table th { background: var(--cream); font-weight: 600; color: var(--chocolate-soft); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.04em; }
.users-table tr:last-child td { border-bottom: none; }
.users-table tbody tr { transition: background 0.15s; }
.users-table tbody tr:hover { background: var(--cream); }
.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--caramel), var(--caramel-dark)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; flex-shrink: 0; }
.email { color: var(--text-muted); }
.date { color: var(--text-muted); font-size: 0.85rem; }
select { padding: 0.45rem 0.75rem; border: 1.5px solid var(--cream-dark); border-radius: var(--radius-sm); font-size: 0.85rem; background: var(--cream); }
select:focus { outline: none; border-color: var(--caramel); background: white; }
@media (max-width: 700px) {
    .users-table th, .users-table td { padding: 0.75rem 0.5rem; font-size: 0.8rem; }
    .email { display: none; }
}
</style>