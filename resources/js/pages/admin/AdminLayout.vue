<!-- Page principale de l'admin -->
<template>
  <div class="min-h-screen bg-cream-dark/30 flex">

    <!-- ═══ SIDEBAR DESKTOP ═══ -->
    <aside class="hidden lg:flex flex-col w-64 xl:w-72 shrink-0 bg-chocolate text-cream sticky top-0 h-screen">

      <!-- Logo -->
      <RouterLink
        to="/admin/dashboard"
        class="flex items-center gap-3 px-6 py-6 border-b border-white/10 shrink-0 group"
      >
        <span class="grid place-items-center w-10 h-10 rounded-2xl bg-gradient-to-br from-caramel to-caramel-dark shadow-soft group-hover:rotate-6 transition-transform duration-300">
          <span class="text-xl">🧁</span>
        </span>
        <div class="flex flex-col leading-tight">
          <span class="font-display text-lg font-extrabold text-white">
            Douceur<span class="text-caramel">.</span>
          </span>
          <span class="text-[10px] uppercase tracking-wider text-cream/50 font-semibold">
            Espace admin
          </span>
        </div>
      </RouterLink>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <p class="px-3 py-2 text-[10px] uppercase tracking-wider text-cream/40 font-bold">
          Navigation
        </p>

        <RouterLink
          v-for="link in sidebarLinks"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-cream/70 hover:bg-white/5 hover:text-white transition-colors group"
          active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white shadow-soft"
        >
          <span class="text-lg w-6 text-center shrink-0 group-hover:scale-110 transition-transform">
            {{ link.icon }}
          </span>
          <span class="truncate">{{ link.label }}</span>
        </RouterLink>
      </nav>

      <!-- Footer sidebar -->
      <div class="p-3 border-t border-white/10 space-y-1 shrink-0">
        <!-- ✅ Boutique dans un nouvel onglet -->
        <a
          href="/"
          target="_blank"
          rel="noopener noreferrer"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-cream/70 hover:bg-white/5 hover:text-white transition-colors group"
        >
          <span class="text-lg w-6 text-center shrink-0">🏪</span>
          <span>Voir la boutique</span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
               class="w-3 h-3 ml-auto text-cream/40 group-hover:text-cream/70 transition-colors">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
          </svg>
        </a>

        <button
          @click="handleLogout"
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-raspberry/80 hover:bg-raspberry/10 hover:text-raspberry transition-colors"
        >
          <span class="text-lg w-6 text-center shrink-0">🚪</span>
          <span>Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- ═══ CONTENU ═══ -->
    <div class="flex-1 flex flex-col min-w-0">

      <!-- ✅ Header slim : contexte discret, pas de titre dupliqué -->
      <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b border-cream-dark">
        <div class="flex items-center justify-between gap-4 px-4 md:px-8 h-16">

          <!-- Burger mobile + contexte -->
          <div class="flex items-center gap-3 min-w-0">
            <button
              @click="mobileMenuOpen = true"
              aria-label="Ouvrir le menu"
              class="lg:hidden grid place-items-center w-10 h-10 rounded-full bg-cream hover:bg-cream-dark transition-colors shrink-0"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-chocolate">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
              </svg>
            </button>

            <!-- Contexte discret (remplace le h1 qui dupliquait le titre) -->
            <div class="hidden md:flex items-center gap-2 text-xs text-muted">
              <span class="font-display font-bold text-chocolate">Douceur</span>
              <span class="text-cream-dark">·</span>
              <span>Espace admin</span>
            </div>
          </div>

          <!-- Actions droite -->
          <div class="flex items-center gap-2 md:gap-3 shrink-0">

            <NotificationBell />

            <!-- ✅ Boutique dans un nouvel onglet -->
            <a
              href="/"
              target="_blank"
              rel="noopener noreferrer"
              title="Voir la boutique"
              class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-full bg-cream hover:bg-cream-dark text-chocolate-soft hover:text-caramel-dark font-semibold text-xs transition-colors"
            >
              🏪 <span>Boutique</span>
            </a>

            <!-- UserMenu simplifié -->
            <div class="relative" ref="userMenuRef">
              <button
                @click="userMenuOpen = !userMenuOpen"
                :aria-expanded="userMenuOpen"
                class="flex items-center gap-2 pl-1 pr-2 md:pr-3 py-1 rounded-full bg-cream hover:bg-cream-dark transition-all group"
              >
                <div class="w-8 h-8 md:w-9 md:h-9 rounded-full bg-gradient-to-br from-caramel to-caramel-dark text-white grid place-items-center font-bold text-xs md:text-sm ring-2 ring-transparent group-hover:ring-caramel/30 transition-all">
                  {{ initials }}
                </div>
                <div class="hidden md:flex flex-col items-start leading-tight">
                  <span class="text-xs font-semibold text-chocolate max-w-[120px] truncate">{{ auth.user?.name }}</span>
                  <span class="text-[10px] uppercase tracking-wider text-muted font-medium">{{ roleLabel }}</span>
                </div>
                <span
                  class="hidden md:inline text-xs text-muted transition-transform duration-200"
                  :class="{ 'rotate-180': userMenuOpen }"
                >▾</span>
              </button>

              <Transition name="dropdown">
                <div v-if="userMenuOpen"
                  class="absolute top-full right-0 mt-2 min-w-[200px] bg-white rounded-2xl shadow-float p-2 z-[100] border border-cream-dark">
                  <RouterLink
                    to="/mon-compte"
                    @click="userMenuOpen = false"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream transition-colors"
                  >
                    <span class="w-5 text-center">👤</span> Mon compte
                  </RouterLink>
                  <div class="h-px bg-cream-dark my-1.5"></div>
                  <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-raspberry text-sm font-medium hover:bg-raspberry/10 transition-colors"
                  >
                    <span class="w-5 text-center">🚪</span> Déconnexion
                  </button>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </header>

      <!-- Contenu page -->
      <main class="flex-1 p-4 md:p-6 lg:p-8">
        <RouterView />
      </main>
    </div>

    <!-- ═══ DRAWER MOBILE ═══ -->
    <Teleport to="body">
      <Transition name="overlay">
        <div
          v-if="mobileMenuOpen"
          class="fixed inset-0 bg-chocolate/40 backdrop-blur-sm z-[90] lg:hidden"
          @click="mobileMenuOpen = false"
        ></div>
      </Transition>
      <Transition name="slide">
        <aside
          v-if="mobileMenuOpen"
          class="fixed top-0 left-0 h-full w-[85%] max-w-xs bg-chocolate z-[100] lg:hidden flex flex-col shadow-float"
        >
          <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
            <div class="flex flex-col leading-tight">
              <span class="font-display text-lg font-extrabold text-white">
                Douceur<span class="text-caramel">.</span>
              </span>
              <span class="text-[10px] uppercase tracking-wider text-cream/50 font-semibold">
                Espace admin
              </span>
            </div>
            <button
              @click="mobileMenuOpen = false"
              aria-label="Fermer"
              class="grid place-items-center w-9 h-9 rounded-full hover:bg-white/10 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <RouterLink
              v-for="link in sidebarLinks"
              :key="link.to"
              :to="link.to"
              @click="mobileMenuOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-cream/70 hover:bg-white/5 hover:text-white transition-colors"
              active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
            >
              <span class="text-lg w-6 text-center shrink-0">{{ link.icon }}</span>
              <span>{{ link.label }}</span>
            </RouterLink>
          </nav>

          <div class="p-3 border-t border-white/10 space-y-1">
            <!-- ✅ Boutique dans un nouvel onglet mobile -->
            <a
              href="/"
              target="_blank"
              rel="noopener noreferrer"
              @click="mobileMenuOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-cream/70 hover:bg-white/5 hover:text-white transition-colors"
            >
              <span class="text-lg w-6 text-center shrink-0">🏪</span>
              <span>Voir la boutique</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                   class="w-3 h-3 ml-auto text-cream/40">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
            <button
              @click="handleLogout"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-raspberry/80 hover:bg-raspberry/10 hover:text-raspberry transition-colors"
            >
              <span class="text-lg w-6 text-center shrink-0">🚪</span>
              <span>Déconnexion</span>
            </button>
          </div>
        </aside>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import NotificationBell from '../../components/layout/NotificationBell.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const userMenuOpen = ref(false);
const userMenuRef = ref(null);
const mobileMenuOpen = ref(false);

const isAdmin = computed(() => auth.user?.role === 'admin');

// ✅ Navigation admin : ajouter une page = 1 ligne ici
const sidebarLinks = computed(() => {
  const links = [
    { to: '/admin/dashboard', icon: '📊', label: 'Tableau de bord' },
    { to: '/admin/commandes', icon: '📦', label: 'Commandes' },
    { to: '/admin/produits', icon: '🍰', label: 'Produits & stock' },
    { to: '/admin/sur-mesure', icon: '🎂', label: 'Sur mesure' },
  ];
  // ✅ Réservé admin (merchants ne voient pas ces liens)
  if (isAdmin.value) {
    links.push({ to: '/admin/utilisateurs', icon: '👥', label: 'Utilisateurs' });
    links.push({ to: '/admin/parametres', icon: '⚙️', label: 'Paramètres' });
  }
  return links;
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

async function handleLogout() {
  userMenuOpen.value = false;
  mobileMenuOpen.value = false;
  await auth.logout();
  router.push('/connexion');
}

function onClickOutside(e) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    userMenuOpen.value = false;
  }
}

function onKeydown(e) {
  if (e.key === 'Escape') {
    userMenuOpen.value = false;
    mobileMenuOpen.value = false;
  }
}

watch(() => route.fullPath, () => {
  userMenuOpen.value = false;
  mobileMenuOpen.value = false;
});

onMounted(() => {
  document.addEventListener('click', onClickOutside);
  document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside);
  document.removeEventListener('keydown', onKeydown);
});
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>