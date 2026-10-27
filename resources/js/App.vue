<template>
    <div class="app">
        <AppHeader />
        <main class="main-content">
            <RouterView v-slot="{ Component }">
                <Transition name="page" mode="out-in">
                    <component :is="Component" />
                </Transition>
            </RouterView>
        </main>
        <AppFooter />
    </div>
</template>

<script setup>
import AppHeader from './components/layout/AppHeader.vue';
import AppFooter from './components/layout/AppFooter.vue';
</script>

<style scoped>
.app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem;
}

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