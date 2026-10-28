<template>
  <div class="min-h-screen flex flex-col bg-cream">
    <!-- Header + Footer publics : uniquement hors /admin -->
    <template v-if="!isAdminRoute">
      <AppHeader />
    </template>

    <!-- Contenu -->
    <main
      :class="isAdminRoute
        ? 'flex-1'
        : 'flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12'"
    >
      <RouterView v-slot="{ Component }">
        <Transition name="page" mode="out-in">
          <component :is="Component" />
        </Transition>
      </RouterView>
    </main>

    <!-- Footer public uniquement hors /admin -->
    <AppFooter v-if="!isAdminRoute" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppHeader from './components/layout/AppHeader.vue';
import AppFooter from './components/layout/AppFooter.vue';

const route = useRoute();

// ✅ Toutes les routes /admin utilisent leur propre layout
const isAdminRoute = computed(() => route.path.startsWith('/admin'));
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