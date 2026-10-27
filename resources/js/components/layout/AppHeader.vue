<template>
    <header class="header">
        <div class="header-inner">
            <!-- Logo -->
            <RouterLink :to="isAdmin ? '/admin/dashboard' : '/'" class="logo">
                <span class="logo-icon">🧁</span>
                <span class="logo-text">{{ isAdmin ? 'Douceur Admin' : 'Douceur' }}</span>
            </RouterLink>

            <!-- Navigation ADMIN (visible) -->
            <nav v-if="isAdmin" class="nav-admin">
                <RouterLink to="/admin/dashboard" class="admin-nav-link">Tableau de bord</RouterLink>
                <RouterLink to="/admin/commandes" class="admin-nav-link">Commandes</RouterLink>
                <RouterLink to="/admin/produits" class="admin-nav-link">Produits</RouterLink>
                <RouterLink to="/admin/sur-mesure" class="admin-nav-link">Sur mesure</RouterLink>
                <template v-if="auth.user?.role === 'admin'">
                    <RouterLink to="/admin/parametres" class="admin-nav-link">Paramètres</RouterLink>
                    <RouterLink to="/admin/utilisateurs" class="admin-nav-link">Utilisateurs</RouterLink>
                </template>
            </nav>

            <!-- Navigation CLIENT (visible) -->
            <nav v-else class="nav-main">
                <RouterLink to="/catalogue" class="nav-link">Catalogue</RouterLink>
                <RouterLink to="/evenements" class="nav-link">Événements</RouterLink>
            </nav>

            <!-- Actions à droite -->
            <div class="header-actions">
                <!-- Panier (client uniquement) -->
                <NotificationBell v-if="isAdmin" />
                <RouterLink v-if="!isAdmin" to="/panier" class="cart-btn" aria-label="Panier">
                    <span class="cart-icon">🛒</span>
                    <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
                </RouterLink>

                <!-- Bouton boutique (admin) -->
                <RouterLink v-if="isAdmin" to="/" class="shop-btn" title="Voir la boutique">
                    🏪 Boutique
                </RouterLink>

                <!-- Non connecté -->
                <template v-if="!auth.isAuthenticated">
                    <RouterLink to="/connexion" class="btn-login">Connexion</RouterLink>
                    <RouterLink to="/inscription" class="btn-signup">S'inscrire</RouterLink>
                </template>

                <!-- Connecté : menu utilisateur -->
                <div v-else class="user-menu-wrapper" ref="userMenuRef">
                    <button class="user-menu-btn" @click="toggleUserMenu">
                        <div class="user-avatar">{{ initials }}</div>
                        <div class="user-info">
                            <span class="user-name">{{ auth.user.name }}</span>
                            <span class="user-role">{{ roleLabel }}</span>
                        </div>
                        <span class="chevron" :class="{ open: userMenuOpen }">▾</span>
                    </button>

                    <Transition name="dropdown">
                        <div v-if="userMenuOpen" class="user-dropdown">
                            <!-- Client -->
                            <template v-if="auth.user.role === 'customer'">
                                <RouterLink to="/mon-compte" class="dropdown-item" @click="closeUserMenu">
                                    <span class="item-icon">👤</span> Mon compte
                                </RouterLink>
                                <RouterLink to="/mes-commandes" class="dropdown-item" @click="closeUserMenu">
                                    <span class="item-icon">📦</span> Mes commandes
                                </RouterLink>
                                <RouterLink to="/mes-demandes" class="dropdown-item" @click="closeUserMenu">
                                    <span class="item-icon">🎂</span> Mes demandes
                                </RouterLink>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item logout" @click="handleLogout">
                                    <span class="item-icon">🚪</span> Déconnexion
                                </button>
                            </template>

                            <!-- Admin/Merchant : menu minimal -->
                            <template v-else>
                                <RouterLink to="/mon-compte" class="dropdown-item" @click="closeUserMenu">
                                    <span class="item-icon">👤</span> Mon compte
                                </RouterLink>
                                <RouterLink to="/" class="dropdown-item" @click="closeUserMenu">
                                    <span class="item-icon">🏪</span> Voir la boutique
                                </RouterLink>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item logout" @click="handleLogout">
                                    <span class="item-icon">🚪</span> Déconnexion
                                </button>
                            </template>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import NotificationBell from './NotificationBell.vue';

const cartStore = useCartStore();
const auth = useAuthStore();
const router = useRouter();

const cartCount = computed(() => cartStore.itemCount);
const isAdmin = computed(() => ['admin', 'merchant'].includes(auth.user?.role));

const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const initials = computed(() => {
    if (!auth.user?.name) return '?';
    return auth.user.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const roleLabel = computed(() => {
    if (auth.user?.role === 'admin') return 'Administrateur';
    if (auth.user?.role === 'merchant') return 'Commerçant';
    return 'Client';
});

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
}

function closeUserMenu() {
    userMenuOpen.value = false;
}

function handleClickOutside(event) {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        closeUserMenu();
    }
}

async function handleLogout() {
    closeUserMenu();
    await auth.logout();
    router.push('/');
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(253, 248, 240, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(212, 165, 116, 0.2);
}

.header-inner {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0.85rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--chocolate);
    letter-spacing: -0.02em;
    flex-shrink: 0;
}

.logo:hover {
    color: var(--chocolate);
}

.logo-icon {
    font-size: 1.4rem;
}

/* Navigation client */
.nav-main {
    display: flex;
    gap: 1.75rem;
    align-items: center;
    flex: 1;
}

.nav-link {
    color: var(--chocolate-soft);
    font-weight: 500;
    font-size: 0.95rem;
    position: relative;
    padding: 0.25rem 0;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--caramel);
    transition: width 0.3s ease;
    border-radius: 2px;
}

.nav-link:hover::after,
.nav-link.router-link-active::after {
    width: 100%;
}

.nav-link.router-link-active {
    color: var(--caramel-dark);
}

/* Navigation admin */
.nav-admin {
    display: flex;
    gap: 0.35rem;
    align-items: center;
    flex: 1;
    justify-content: center;
    flex-wrap: wrap;
}

.admin-nav-link {
    padding: 0.55rem 1rem;
    border-radius: 999px;
    color: var(--chocolate-soft);
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
    white-space: nowrap;
}

.admin-nav-link:hover {
    background: var(--cream);
    color: var(--caramel-dark);
    text-decoration: none;
}

.admin-nav-link.router-link-active {
    background: var(--caramel);
    color: white;
}

/* Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

/* Panier */
.cart-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: white;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
    color: var(--chocolate);
}

.cart-btn:hover {
    background: var(--cream);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    text-decoration: none;
}

.cart-icon {
    font-size: 1.3rem;
}

.cart-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: var(--raspberry);
    color: white;
    border-radius: 999px;
    padding: 0.1rem 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    border: 2px solid var(--cream);
}

/* Bouton boutique (admin) */
.shop-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    background: white;
    color: var(--chocolate-soft);
    font-weight: 600;
    font-size: 0.85rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
}

.shop-btn:hover {
    background: var(--cream);
    color: var(--caramel-dark);
    text-decoration: none;
}

/* Boutons non connecté */
.btn-login,
.btn-signup {
    padding: 0.5rem 1.1rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.btn-login {
    color: var(--chocolate-soft);
}

.btn-login:hover {
    color: var(--caramel);
}

.btn-signup {
    background: var(--caramel);
    color: white;
}

.btn-signup:hover {
    background: var(--caramel-dark);
    text-decoration: none;
}

/* Menu utilisateur */
.user-menu-wrapper {
    position: relative;
}

.user-menu-btn {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.35rem 0.75rem 0.35rem 0.35rem;
    border-radius: 999px;
    background: white;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
    color: var(--chocolate);
}

.user-menu-btn:hover {
    box-shadow: var(--shadow-md);
    background: var(--cream);
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--caramel), var(--caramel-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.2;
}

.user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--chocolate);
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-role {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.chevron {
    font-size: 0.8rem;
    color: var(--text-muted);
    transition: transform 0.2s;
}

.chevron.open {
    transform: rotate(180deg);
}

/* Dropdown */
.user-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 220px;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: 0.5rem;
    z-index: 100;
    border: 1px solid var(--cream-dark);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.9rem;
    border-radius: var(--radius-sm);
    color: var(--chocolate-soft);
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.15s;
    width: 100%;
    text-align: left;
}

.dropdown-item:hover {
    background: var(--cream);
    color: var(--caramel-dark);
    text-decoration: none;
}

.item-icon {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.dropdown-divider {
    height: 1px;
    background: var(--cream-dark);
    margin: 0.4rem 0;
}

.dropdown-item.logout {
    color: var(--raspberry);
}

.dropdown-item.logout:hover {
    background: rgba(201, 79, 109, 0.08);
    color: var(--raspberry);
}

/* Transition dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* Responsive */
@media (max-width: 1100px) {
    .admin-nav-link {
        font-size: 0.85rem;
        padding: 0.45rem 0.75rem;
    }
}

@media (max-width: 900px) {
    .header-inner {
        padding: 0.75rem 1rem;
        gap: 0.75rem;
    }
    .logo {
        font-size: 1.3rem;
    }
    .nav-main {
        gap: 1rem;
    }
    .nav-link {
        font-size: 0.85rem;
    }
    .user-info {
        display: none;
    }
    .user-menu-btn {
        padding: 0.25rem;
    }
    .chevron {
        display: none;
    }
    .nav-admin {
        display: none;
    }
    .shop-btn span {
        display: none;
    }
}

@media (max-width: 600px) {
    .nav-main {
        display: none;
    }
    .header-actions {
        gap: 0.5rem;
    }
    .cart-btn {
        width: 40px;
        height: 40px;
    }
    .btn-login,
    .btn-signup {
        padding: 0.45rem 0.9rem;
        font-size: 0.85rem;
    }
}
</style>