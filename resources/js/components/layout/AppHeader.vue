<template>
  <header
    class="sticky top-0 z-50 transition-all duration-300"
    :class="scrolled
      ? 'bg-cream/90 backdrop-blur-xl shadow-soft border-b border-gold/20'
      : 'bg-cream border-b border-transparent'"
  >
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between gap-3 md:gap-4 h-16 md:h-20">

        <!-- ═══ LOGO ═══ -->
        <RouterLink to="/" class="group flex items-center gap-2 md:gap-2.5 shrink-0">
          <span class="grid place-items-center w-9 h-9 md:w-10 md:h-10 rounded-2xl bg-gradient-to-br from-caramel to-caramel-dark shadow-soft group-hover:shadow-card transition-all duration-300 group-hover:-rotate-6">
            <span class="text-lg md:text-xl">🧁</span>
          </span>
          <span class="font-display text-lg md:text-2xl font-extrabold text-chocolate tracking-tight whitespace-nowrap">
            Douceur<span class="text-caramel">.</span>
          </span>
        </RouterLink>

        <!-- ═══ NAV DESKTOP CLIENT ═══ -->
        <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
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

          <!-- Suivi : uniquement pour invités (les connectés ont /mes-commandes) -->
          <RouterLink
            v-if="!auth.isAuthenticated"
            to="/suivi-commande"
            class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
            active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
          >
            Suivi commande
          </RouterLink>

          <RouterLink
            v-if="!auth.isAuthenticated"
            to="/suivi-demande"
            class="px-4 py-2 rounded-full text-sm font-medium text-chocolate-soft hover:text-caramel-dark hover:bg-cream-dark/60 transition-all"
            active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
          >
            Suivi demande
          </RouterLink>
        </nav>

        <!-- ═══ ACTIONS DROITE ═══ -->
        <div class="flex items-center gap-1.5 md:gap-3 shrink-0">

          <!-- Panier (toujours visible côté boutique) -->
          <RouterLink
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

          <!-- Non connecté -->
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

          <!-- Connecté : menu utilisateur -->
          <UserMenu
            v-else
            :user="auth.user"
            :initials="initials"
            :role-label="roleLabel"
            @logout="handleLogout"
          />

          <!-- Hamburger mobile -->
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

    <!-- Drawer mobile (réutilise ton composant existant) -->
    <MobileDrawer
      :open="mobileMenuOpen"
      :is-admin="false"
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
import UserMenu from './UserMenu.vue';
import MobileDrawer from './MobileDrawer.vue';

const cartStore = useCartStore();
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const cartCount = computed(() => cartStore.itemCount);

const mobileMenuOpen = ref(false);
const scrolled = ref(false);

const initials = computed(() => {
  if (!auth.user?.name) return '?';
  return auth.user.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
});

const roleLabel = computed(() => {
  if (auth.user?.role === 'admin') return 'Administrateur';
  if (auth.user?.role === 'merchant') return 'Commerçant';
  return 'Client';
});

async function handleLogout() {
  mobileMenuOpen.value = false;
  await auth.logout();
  router.push('/');
}

function onScroll() { scrolled.value = window.scrollY > 8; }

watch(() => route.fullPath, () => { mobileMenuOpen.value = false; });

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
});
onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
});
</script>