<template>
  <div class="max-w-7xl mx-auto">

    <!-- ═══ HEADER ═══ -->
    <header class="mb-6">
      <p class="text-xs font-bold uppercase tracking-[0.18em] text-caramel-dark mb-2">
        Administration
      </p>
      <h1 class="font-display text-3xl md:text-4xl font-bold text-chocolate">
        Utilisateurs
      </h1>
      <p class="mt-2 text-sm text-muted">
        {{ users.length }} utilisateur{{ users.length > 1 ? 's' : '' }} inscrit{{ users.length > 1 ? 's' : '' }}
      </p>
    </header>

    <!-- ═══ CHARGEMENT ═══ -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3 bg-white rounded-2xl shadow-soft">
      <div class="w-10 h-10 rounded-full border-4 border-caramel/20 border-t-caramel animate-spin"></div>
      <p class="text-sm text-muted">Chargement des utilisateurs…</p>
    </div>

    <!-- ═══ ERREUR ═══ -->
    <div v-else-if="error" class="max-w-md mx-auto text-center py-16 px-4 bg-white rounded-2xl shadow-soft">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-raspberry/10 mb-4">
        <span class="text-3xl">⚠️</span>
      </div>
      <h2 class="font-display text-xl font-bold text-chocolate mb-2">Erreur de chargement</h2>
      <p class="text-sm text-raspberry font-medium">{{ error }}</p>
    </div>

    <!-- ═══ TABLEAU ═══ -->
    <div v-else class="bg-white rounded-2xl shadow-card overflow-hidden">

      <!-- En-tête tableau desktop -->
      <div class="hidden md:grid grid-cols-[1.5fr_1.5fr_1fr_1fr] gap-4 border-b border-cream-dark bg-cream/50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted">
        <span>Utilisateur</span>
        <span>Email</span>
        <span>Rôle</span>
        <span>Inscrit le</span>
      </div>

      <!-- Lignes -->
      <div class="divide-y divide-cream-dark/80">
        <div
          v-for="user in users"
          :key="user.id"
          class="grid grid-cols-1 md:grid-cols-[1.5fr_1.5fr_1fr_1fr] gap-3 md:gap-4 px-4 md:px-5 py-4 hover:bg-cream/40 transition-colors"
        >

          <!-- Utilisateur (avatar + nom) -->
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-caramel to-caramel-dark text-white grid place-items-center font-bold text-xs shrink-0">
              {{ initials(user.name) }}
            </div>
            <div class="min-w-0">
              <p class="font-semibold text-chocolate truncate">{{ user.name }}</p>
              <!-- Email visible sur mobile uniquement -->
              <p class="md:hidden mt-0.5 text-xs text-muted truncate">{{ user.email }}</p>
            </div>
          </div>

          <!-- Email (desktop) -->
          <div class="hidden md:flex items-center min-w-0">
            <span class="text-sm text-muted truncate">{{ user.email }}</span>
          </div>

          <!-- Rôle (select) -->
          <div class="flex items-center">
            <select
              :value="user.role"
              @change="changeRole(user, $event.target.value)"
              class="w-full px-3 py-2 rounded-xl border-2 border-cream-dark bg-cream text-sm font-medium text-chocolate focus:outline-none focus:border-caramel focus:bg-white transition-colors cursor-pointer"
            >
              <option value="customer">Client</option>
              <option value="merchant">Commerçant</option>
              <option value="admin">Admin</option>
            </select>
          </div>

          <!-- Date d'inscription -->
          <div class="flex items-center text-xs md:text-sm text-muted">
            📅 {{ formatDate(user.created_at) }}
          </div>

        </div>
      </div>

      <!-- État vide (si jamais 0 users) -->
      <div v-if="users.length === 0" class="px-6 py-16 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-cream text-caramel-dark">
          <span class="text-2xl">👥</span>
        </div>
        <h3 class="mt-4 font-semibold text-chocolate">Aucun utilisateur</h3>
        <p class="mt-1 text-sm text-muted">
          Les utilisateurs inscrits apparaîtront ici.
        </p>
      </div>

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
function formatDate(d) {
  return new Date(d).toLocaleDateString('fr-FR');
}

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