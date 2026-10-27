<template>
  <Teleport to="body">
    <Transition name="overlay">
      <div v-if="open" class="fixed inset-0 bg-chocolate/40 backdrop-blur-sm z-[90] lg:hidden"
           @click="$emit('close')"></div>
    </Transition>
    <Transition name="slide">
      <aside v-if="open" class="fixed top-0 right-0 h-full w-[85%] max-w-sm bg-cream z-[100] lg:hidden flex flex-col shadow-float">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gold/20">
          <span class="font-display text-lg font-extrabold text-chocolate">
            {{ isAdmin ? 'Douceur Admin' : 'Douceur' }}<span class="text-caramel">.</span>
          </span>
          <button @click="$emit('close')" aria-label="Fermer"
            class="grid place-items-center w-9 h-9 rounded-full bg-white shadow-soft hover:bg-cream-dark transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-chocolate">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-1">

          <!-- ADMIN : groupes -->
          <template v-if="isAdmin">
            <div v-for="group in adminGroups" :key="group.key" class="mb-2">
              <p class="px-4 py-2 text-[10px] uppercase tracking-wider text-muted font-bold">
                {{ group.label }}
              </p>
              <RouterLink
                v-for="link in group.links"
                :key="link.to"
                :to="link.to"
                @click="$emit('close')"
                class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
                active-class="bg-caramel text-white hover:bg-caramel-dark hover:text-white"
              >
                <span class="text-lg w-6 text-center">{{ link.icon }}</span>
                {{ link.label }}
              </RouterLink>
            </div>
            <div class="h-px bg-cream-dark my-2"></div>
            <RouterLink
              to="/"
              @click="$emit('close')"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
            >
              🏪 Voir la boutique
            </RouterLink>
          </template>

          <!-- CLIENT -->
          <template v-else>
            <RouterLink to="/catalogue" @click="$emit('close')"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
              active-class="bg-caramel text-white">
              <span class="text-lg w-6 text-center">🍰</span> Catalogue
            </RouterLink>
            <RouterLink to="/evenements" @click="$emit('close')"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
              active-class="bg-caramel text-white">
              <span class="text-lg w-6 text-center">🎉</span> Événements
            </RouterLink>

            <!-- ✅ Suivi COMMANDE : uniquement invités -->
            <RouterLink
              v-if="!isAuthenticated"
              to="/suivi-commande"
              @click="$emit('close')"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
              active-class="bg-caramel text-white"
            >
              <span class="text-lg w-6 text-center">🔍</span> Suivi commande
            </RouterLink>

            <!-- ✅ Suivi DEMANDE : uniquement invités -->
            <RouterLink
              v-if="!isAuthenticated"
              to="/suivi-demande"
              @click="$emit('close')"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark hover:text-caramel-dark transition-colors"
              active-class="bg-caramel text-white"
            >
              <span class="text-lg w-6 text-center">🔍</span> Suivi demande
            </RouterLink>
          </template>

          <!-- Non connecté -->
          <template v-if="!isAuthenticated">
            <div class="h-px bg-cream-dark my-2"></div>
            <RouterLink to="/connexion" @click="$emit('close')"
              class="px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark text-center transition-colors">
              Connexion
            </RouterLink>
            <RouterLink to="/inscription" @click="$emit('close')"
              class="px-4 py-3 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-center transition-colors mt-1">
              S'inscrire
            </RouterLink>
          </template>

          <!-- Connecté -->
          <template v-if="isAuthenticated">
            <div class="h-px bg-cream-dark my-2"></div>
            <div class="px-4 py-2 flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-caramel to-caramel-dark text-white grid place-items-center font-bold text-xs">
                {{ initials }}
              </div>
              <div class="flex flex-col leading-tight">
                <span class="text-xs font-semibold text-chocolate">{{ user.name }}</span>
                <span class="text-[10px] uppercase tracking-wider text-muted">{{ roleLabel }}</span>
              </div>
            </div>

            <template v-if="user.role === 'customer'">
              <RouterLink to="/mon-compte" @click="$emit('close')" class="px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark transition-colors">👤 Mon compte</RouterLink>
              <RouterLink to="/mes-commandes" @click="$emit('close')" class="px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark transition-colors">📦 Mes commandes</RouterLink>
              <RouterLink to="/mes-demandes" @click="$emit('close')" class="px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark transition-colors">🎂 Mes demandes</RouterLink>
            </template>
            <template v-else>
              <RouterLink to="/mon-compte" @click="$emit('close')" class="px-4 py-3 rounded-xl text-chocolate-soft font-medium hover:bg-cream-dark transition-colors">👤 Mon compte</RouterLink>
            </template>

            <button @click="$emit('logout')"
              class="text-left px-4 py-3 rounded-xl text-raspberry font-medium hover:bg-raspberry/10 transition-colors">
              🚪 Déconnexion
            </button>
          </template>
        </nav>
      </aside>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  isAdmin: { type: Boolean, default: false },
  isAuthenticated: { type: Boolean, default: false },
  user: { type: Object, default: null },
  initials: { type: String, default: '?' },
  roleLabel: { type: String, default: 'Client' },
});

const emit = defineEmits(['close', 'logout']);

const adminGroups = computed(() => {
  const groups = [
    {
      key: 'pilotage',
      label: 'Pilotage',
      links: [
        { to: '/admin/dashboard', icon: '📊', label: 'Tableau de bord' },
        { to: '/admin/commandes', icon: '📦', label: 'Commandes' },
      ],
    },
    {
      key: 'catalogue',
      label: 'Catalogue',
      links: [
        { to: '/admin/produits', icon: '🍰', label: 'Produits' },
        { to: '/admin/sur-mesure', icon: '🎂', label: 'Sur mesure' },
      ],
    },
  ];
  if (props.user?.role === 'admin') {
    groups.push({
      key: 'administration',
      label: 'Administration',
      links: [
        { to: '/admin/utilisateurs', icon: '👥', label: 'Utilisateurs' },
        { to: '/admin/parametres', icon: '⚙️', label: 'Paramètres' },
      ],
    });
  }
  return groups;
});

function onKeydown(e) {
  if (e.key === 'Escape') emit('close');
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
</script>

<style scoped>
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); }
</style>