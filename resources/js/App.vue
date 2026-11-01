<template>
  <div class="min-h-screen flex flex-col bg-cream">
    <!-- ✅ Header public : uniquement hors /admin et hors vérification email -->
    <AppHeader v-if="showPublicLayout" />

    <!-- Contenu -->
    <main
      :class="isAdminRoute
        ? 'flex-1'
        : showPublicLayout
          ? 'flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12'
          : 'flex-1 flex items-center justify-center'"
    >
      <RouterView v-slot="{ Component }">
        <Transition name="page" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <!-- ✅ Footer public : uniquement hors /admin et hors vérification email -->
    <AppFooter v-if="showPublicLayout" />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AppHeader from './components/layout/AppHeader.vue';
import AppFooter from './components/layout/AppFooter.vue';
import { useAuthStore } from './stores/auth';

const route = useRoute();
const auth = useAuthStore();

// ✅ Toutes les routes /admin utilisent leur propre layout
const isAdminRoute = computed(() => route.path.startsWith('/admin'));

// ✅ Afficher header/footer SEULEMENT si :
//    - pas sur /admin
//    - ET (pas connecté OU email vérifié)
const showPublicLayout = computed(() => {
  if (isAdminRoute.value) return false;
  if (!auth.isAuthenticated) return true;           // invité → layout normal
  if (auth.user?.email_verified_at) return true;    // client vérifié → layout normal
  return false;                                      // client non vérifié → pas de layout
});

onMounted(async () => {
  if (!auth.user) await auth.fetchUser();
});
</script>

<style scoped>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(12px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>