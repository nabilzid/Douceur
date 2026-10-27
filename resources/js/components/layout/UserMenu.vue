<template>
  <div class="relative" ref="rootEl">
    <button
      @click="open = !open"
      :aria-expanded="open"
      class="flex items-center gap-2 pl-1 pr-2 md:pr-3 py-1 rounded-full bg-white shadow-soft hover:shadow-card transition-all group"
    >
      <div class="w-8 h-8 md:w-9 md:h-9 rounded-full bg-gradient-to-br from-caramel to-caramel-dark text-white grid place-items-center font-bold text-xs md:text-sm ring-2 ring-transparent group-hover:ring-caramel/30 transition-all">
        {{ initials }}
      </div>
      <div class="hidden md:flex flex-col items-start leading-tight">
        <span class="text-xs font-semibold text-chocolate max-w-[120px] truncate">{{ user.name }}</span>
        <span class="text-[10px] uppercase tracking-wider text-muted font-medium">{{ roleLabel }}</span>
      </div>
      <span class="hidden md:inline text-xs text-muted transition-transform duration-200"
            :class="{ 'rotate-180': open }">▾</span>
    </button>

    <Transition name="dropdown">
      <div v-if="open"
        class="absolute top-full right-0 mt-3 min-w-[240px] bg-white rounded-2xl shadow-float p-2 z-[100] border border-cream-dark">

        <!-- Client — logique préservée à l'identique -->
        <template v-if="user.role === 'customer'">
          <RouterLink to="/mon-compte" @click="close"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream hover:text-caramel-dark transition-colors">
            <span class="w-5 text-center">👤</span> Mon compte
          </RouterLink>
          <RouterLink to="/mes-commandes" @click="close"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream hover:text-caramel-dark transition-colors">
            <span class="w-5 text-center">📦</span> Mes commandes
          </RouterLink>
          <RouterLink to="/mes-demandes" @click="close"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream hover:text-caramel-dark transition-colors">
            <span class="w-5 text-center">🎂</span> Mes demandes
          </RouterLink>
          <div class="h-px bg-cream-dark my-1.5"></div>
          <button @click="handleLogout"
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-raspberry text-sm font-medium hover:bg-raspberry/10 transition-colors">
            <span class="w-5 text-center">🚪</span> Déconnexion
          </button>
        </template>

        <!-- Admin/Merchant — logique préservée à l'identique -->
        <template v-else>
          <RouterLink to="/mon-compte" @click="close"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream hover:text-caramel-dark transition-colors">
            <span class="w-5 text-center">👤</span> Mon compte
          </RouterLink>
          <RouterLink to="/" @click="close"
            class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-chocolate-soft text-sm font-medium hover:bg-cream hover:text-caramel-dark transition-colors">
            <span class="w-5 text-center">🏪</span> Voir la boutique
          </RouterLink>
          <div class="h-px bg-cream-dark my-1.5"></div>
          <button @click="handleLogout"
            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-raspberry text-sm font-medium hover:bg-raspberry/10 transition-colors">
            <span class="w-5 text-center">🚪</span> Déconnexion
          </button>
        </template>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

defineProps({
  isAdmin: { type: Boolean, default: false },
  user: { type: Object, required: true },
  initials: { type: String, default: '?' },
  roleLabel: { type: String, default: 'Client' },
});

const emit = defineEmits(['logout']);
const open = ref(false);
const rootEl = ref(null);

function close() { open.value = false; }
function handleLogout() {
  close();
  emit('logout');
}
function onClickOutside(e) {
  if (rootEl.value && !rootEl.value.contains(e.target)) close();
}

onMounted(() => document.addEventListener('click', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>