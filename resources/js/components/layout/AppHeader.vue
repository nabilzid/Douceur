<template>
  <header
    class="sticky top-0 z-50 transition-all duration-300"
    :class="scrolled
      ? 'bg-cream/90 backdrop-blur-xl shadow-soft border-b border-gold/20'
      : 'bg-cream border-b border-transparent'"
  >
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3 md:gap-4 h-16 md:h-20">

        <!-- Logo -->
        <RouterLink :to="isAdmin ? '/admin/dashboard' : '/'" class="group flex items-center gap-2 md:gap-2.5 shrink-0">
          <span class="grid place-items-center w-9 h-9 md:w-10 md:h-10 rounded-2xl bg-gradient-to-br from-caramel to-caramel-dark shadow-soft group-hover:shadow-card transition-all duration-300 group-hover:-rotate-6">
            <span class="text-lg md:text-xl">🧁</span>
          </span>
          <span class="font-display text-lg md:text-2xl font-extrabold text-chocolate tracking-tight whitespace-nowrap">
            {{ isAdmin ? 'Douceur Admin' : 'Douceur' }}<span class="text-caramel">.</span>
          </span>
        </RouterLink>

        <!-- ═══ NAV DESKTOP ═══ -->
        <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">

          <!-- ADMIN : 3 groupes -->
          <template v-if="isAdmin">
            <div
              v-for="group in adminGroups"
              :key="group.key"
              class="relative"
              @mouseenter="openMenu(group.key)"
              @mouseleave="scheduleClose(group.key)"
            >
              <button
                class="flex items-center gap-1.5 px-3 xl:px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap"
                :class="activeMenu === group.key
                  ? 'bg-caramel text-white shadow-soft'
                  : 'text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60'"
              >
                {{ group.label }}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                     class="w-3 h-3 transition-transform duration-200"
                     :class="{ 'rotate-180': activeMenu === group.key }">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <Transition name="dropdown">
                <div
                  v-if="activeMenu === group.key"
                  class="absolute top-full left-1/2 -translate-x-1/2 mt-3 w-72 bg-white rounded-2xl shadow-float border border-cream-dark p-2 z-[100]"
                >
                  <RouterLink
                    v-for="link in group.links"
                    :key="link.to"
                    :to="link.to"
                    @click="closeAllMenus"
                    class="flex items-start gap-3 px-3 py-2.5 rounded-xl text-chocolate-soft hover:bg-cream hover:text-caramel-dark transition-colors"
                  >
                    <span class="text-lg w-6 text-center shrink-0 mt-0.5">{{ link.icon }}</span>
                    <div class="flex flex-col">
                      <span class="text-sm font-medium">{{ link.label }}</span>
                      <span v-if="link.description" class="text-[11px] text-muted leading-tight mt-0.5">
                        {{ link.description }}
                      </span>
                    </div>
                  </RouterLink>
                </div>
              </Transition>
            </div>
          </template>

          <!-- ═══ CLIENT : liens conditionnels ═══ -->
          <template v-else>
            <RouterLink to="/catalogue"
              class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
              active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white">
              Catalogue
            </RouterLink>
            <RouterLink to="/evenements"
              class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
              active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white">
              Événements
            </RouterLink>

            <!-- ✅ Suivi COMMANDE : uniquement invités -->
            <RouterLink
              v-if="!auth.isAuthenticated"
              to="/suivi-commande"
              class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
              active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
            >
              Suivi commande
            </RouterLink>

            <!-- ✅ Suivi DEMANDE : uniquement invités -->
            <RouterLink
              v-if="!auth.isAuthenticated"
              to="/suivi-demande"
              class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
              active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
            >
              Suivi demande
            </RouterLink>
          </template>
        </nav>

        <!-- ═══ ACTIONS ═══ -->
        <div class="flex items-center gap-1.5 md:gap-3 shrink-0">

          <NotificationBell v-if="isAdmin" />

          <RouterLink
            v-if="!isAdmin"
            to="/panier"
            aria-label="Voir le panier"
            class="relative grid place-items-center w-10 h-10 md:w-11 md:h-11 rounded-full bg-white shadow-soft hover:shadow-card hover:bg-cream transition-all group"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                 class="w-5 h-5 text-chocolate group-hover:text-caramel-dark transition-colors">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            <span
              v-if="cartCount > 0"
              class="absolute -top-0.5 -right-0.5 min-w-[20px] h-5 px-1 grid place-items-center bg-raspberry text-white text-[10px] md:text-xs font-bold rounded-full ring-2 ring-cream animate-fade-up"
            >
              {{ cartCount > 99 ? '99+' : cartCount }}
            </span>
          </RouterLink>

          <RouterLink
            v-if="isAdmin"
            to="/"
            title="Voir la boutique"
            class="hidden sm:inline-flex items-center gap-2 px-3 md:px-4 py-2 rounded-full bg-white shadow-soft hover:shadow-card hover:bg-cream text-chocolate-soft hover:text-caramel-dark font-semibold text-xs md:text-sm transition-all"
          >
            🏪 <span class="hidden md:inline">Boutique</span>
          </RouterLink>

          <template v-if="!auth.isAuthenticated">
            <RouterLink to="/connexion"
              class="hidden md:inline-block px-4 py-2 text-chocolate-soft hover:text-caramel font-semibold text-sm transition-colors">
              Connexion
            </RouterLink>
            <RouterLink to="/inscription"
              class="hidden md:inline-block px-4 md:px-5 py-2 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all hover:-translate-y-0.5">
              S'inscrire
            </RouterLink>
          </template>

          <UserMenu
            v-else
            :user="auth.user"
            :initials="initials"
            :role-label="roleLabel"
            @logout="handleLogout"
          />

          <button
            @click="mobileMenuOpen = true"
            aria-label="Ouvrir le menu"
            class="lg:hidden grid place-items-center w-10 h-10 md:w-11 md:h-11 rounded-full bg-white shadow-soft hover:shadow-card transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-chocolate">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <MobileDrawer
      :open="mobileMenuOpen"
      :is-admin="isAdmin"
      :is-authenticated="auth.isAuthenticated"
      :user="auth.user"
      :initials="initials"
      :role-label="roleLabel"
      @close="mobileMenuOpen = false"
      @logout="handleLogout"
    />
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCartStore } from '../../stores/cart';
import { useAuthStore } from '../../stores/auth';
import NotificationBell from './NotificationBell.vue';
import UserMenu from './UserMenu.vue';
import MobileDrawer from './MobileDrawer.vue';

const cartStore = useCartStore();
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const cartCount = computed(() => cartStore.itemCount);
const isAdmin = computed(() => ['admin', 'merchant'].includes(auth.user?.role));

const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const activeMenu = ref(null);
let closeTimer = null;

const adminGroups = computed(() => {
  const groups = [
    {
      key: 'pilotage',
      label: 'Pilotage',
      links: [
        { to: '/admin/dashboard', icon: '📊', label: 'Tableau de bord', description: "Vue d'ensemble du jour" },
        { to: '/admin/commandes', icon: '📦', label: 'Commandes', description: 'À préparer / en cours' },
      ],
    },
    {
      key: 'catalogue',
      label: 'Catalogue',
      links: [
        { to: '/admin/produits', icon: '🍰', label: 'Produits', description: 'Gérer la boutique' },
        { to: '/admin/sur-mesure', icon: '🎂', label: 'Sur mesure', description: 'Demandes clients' },
      ],
    },
  ];
  if (auth.user?.role === 'admin') {
    groups.push({
      key: 'administration',
      label: 'Administration',
      links: [
        { to: '/admin/utilisateurs', icon: '👥', label: 'Utilisateurs', description: 'Rôles & accès' },
        { to: '/admin/parametres', icon: '⚙️', label: 'Paramètres', description: 'Configuration' },
      ],
    });
  }
  return groups;
});

const initials = computed(() => {
  if (!auth.user?.name) return '?';
  return auth.user.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const roleLabel = computed(() => {
  if (auth.user?.role === 'admin') return 'Administrateur';
  if (auth.user?.role === 'merchant') return 'Commerçant';
  return 'Client';
});

function openMenu(key) { clearTimeout(closeTimer); activeMenu.value = key; }
function scheduleClose(key) {
  closeTimer = setTimeout(() => { if (activeMenu.value === key) activeMenu.value = null; }, 150);
}
function closeAllMenus() { activeMenu.value = null; mobileMenuOpen.value = false; }

async function handleLogout() {
  closeAllMenus();
  await auth.logout();
  router.push('/');
}

function onScroll() { scrolled.value = window.scrollY > 8; }

watch(() => route.fullPath, () => closeAllMenus());

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
  clearTimeout(closeTimer);
});
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translate(-50%, -6px);
}
</style>