<template>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="brand-icon">🧁</span>
                <span class="brand-name">Douceur Admin</span>
            </div>

            <nav class="admin-nav">
                <RouterLink to="/admin/dashboard" class="admin-link">
                    <span class="link-icon">📊</span> Tableau de bord
                </RouterLink>
                <RouterLink to="/admin/produits" class="admin-link">
                    <span class="link-icon">🍰</span> Produits & stock
                </RouterLink>
                <RouterLink to="/admin/commandes" class="admin-link">
                    <span class="link-icon">📦</span> Commandes
                </RouterLink>
                <RouterLink to="/admin/sur-mesure" class="admin-link">
                    <span class="link-icon">🎂</span> Sur mesure
                </RouterLink>

                <template v-if="isAdmin">
                    <RouterLink to="/admin/parametres" class="admin-link">
                        <span class="link-icon">⚙️</span> Paramètres
                    </RouterLink>
                    <RouterLink to="/admin/utilisateurs" class="admin-link">
                        <span class="link-icon">👥</span> Utilisateurs
                    </RouterLink>
                </template>
            </nav>

            <div class="admin-footer">
                <p class="admin-user">{{ auth.user?.name }}</p>
                <RouterLink to="/" class="shop-link">← Voir la boutique</RouterLink>
                <button class="logout-btn" @click="handleLogout">Déconnexion</button>
            </div>
        </aside>

        <main class="admin-main">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const isAdmin = computed(() => auth.user?.role === 'admin');

async function handleLogout() {
    await auth.logout();
    router.push('/connexion');
}
</script>

<style scoped>
.admin-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: var(--cream);
}

.admin-sidebar {
    background: var(--chocolate);
    color: var(--cream);
    padding: 1.5rem 1rem;
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
}

.admin-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Playfair Display', serif;
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--gold);
    margin-bottom: 2rem;
    padding: 0 0.5rem;
}

.admin-nav {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    flex: 1;
}

.admin-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    color: rgba(253, 248, 240, 0.75);
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s;
}

.admin-link:hover {
    background: rgba(253, 248, 240, 0.08);
    color: var(--cream);
    text-decoration: none;
}

.admin-link.router-link-active {
    background: var(--caramel);
    color: white;
}

.link-icon {
    font-size: 1.1rem;
}

.admin-footer {
    border-top: 1px solid rgba(253, 248, 240, 0.15);
    padding-top: 1rem;
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.admin-user {
    font-size: 0.85rem;
    color: rgba(253, 248, 240, 0.5);
    margin: 0;
    padding: 0 0.5rem;
}

.shop-link,
.logout-btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    color: rgba(253, 248, 240, 0.7);
    border-radius: var(--radius-sm);
    text-align: left;
    transition: all 0.2s;
}

.shop-link:hover,
.logout-btn:hover {
    background: rgba(253, 248, 240, 0.08);
    color: var(--cream);
    text-decoration: none;
}

.logout-btn {
    color: var(--raspberry);
}

.admin-main {
    padding: 2rem 2.5rem;
    min-width: 0;
    overflow-x: auto;
}

@media (max-width: 900px) {
    .admin-layout {
        grid-template-columns: 1fr;
    }
    .admin-sidebar {
        position: static;
        height: auto;
    }
    .admin-nav {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .admin-link {
        flex: 1 1 auto;
        justify-content: center;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    .admin-main {
        padding: 1.5rem 1rem;
    }
}
</style>