
<template>
  <section class="mb-8 md:mb-12">

    <!-- En-tête discret -->
    <div class="mb-5 text-center">
      <p
        class="text-[10px] font-semibold uppercase
               tracking-[0.28em] text-caramel-dark"
      >
        Trouvez votre bonheur
      </p>

      <h2
        class="mt-2 font-display text-xl font-bold
               tracking-tight text-chocolate sm:text-2xl"
      >
        Nos catégories
      </h2>
    </div>

    <!-- Filtres -->
    <div
      class="flex flex-wrap items-center justify-center gap-2.5 md:gap-3"
      role="group"
      aria-label="Filtrer les pâtisseries par catégorie"
    >
      <!-- Toutes les catégories -->
      <button
        type="button"
        :aria-pressed="modelValue === null"
        @click="$emit('update:modelValue', null)"
        class="category-pill"
        :class="modelValue === null
          ? 'category-pill-active'
          : 'category-pill-idle'"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.7"
          class="h-4 w-4"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m19 14 1.1 2.9L23 18l-2.9 1.1L19 22l-1.1-2.9L15 18l2.9-1.1L19 14Z"
          />
        </svg>

        <span>Tout</span>
      </button>

      <!-- Catégories dynamiques -->
      <button
        v-for="type in types"
        :key="type.id"
        type="button"
        :aria-pressed="modelValue === type.slug"
        @click="$emit('update:modelValue', type.slug)"
        class="category-pill"
        :class="modelValue === type.slug
          ? 'category-pill-active'
          : 'category-pill-idle'"
      >
        <span
          class="text-base leading-none"
          aria-hidden="true"
        >
          {{ type.icon || '🍮' }}
        </span>

        <span>{{ type.name }}</span>

        <svg
          v-if="modelValue === type.slug"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          class="h-3.5 w-3.5"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m5 10 3 3 7-7"
          />
        </svg>
      </button>
    </div>

  </section>
</template>

<script setup>
defineProps({
  types: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: String,
    default: null,
  },
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.category-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  min-height: 42px;
  padding: 0.65rem 1.1rem;
  border: 1px solid transparent;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.25rem;
  transition:
    background-color 200ms ease,
    border-color 200ms ease,
    color 200ms ease,
    box-shadow 200ms ease,
    transform 200ms ease;
}

.category-pill-active {
  color: #fffaf4;
  background: #704832;
  border-color: #704832;
  box-shadow: 0 4px 12px rgb(85 53 35 / 14%);
}

.category-pill-idle {
  color: #634738;
  background: #fffdf9;
  border-color: #eadbcb;
}

.category-pill-idle:hover {
  color: #704832;
  background: #f8eee3;
  border-color: #c99b74;
  transform: translateY(-2px);
}

.category-pill-active:hover {
  background: #5c3828;
  border-color: #5c3828;
}

.category-pill:active {
  transform: scale(0.97);
}

.category-pill:focus-visible {
  outline: 2px solid #ad7955;
  outline-offset: 3px;
}

@media (prefers-reduced-motion: reduce) {
  .category-pill {
    transition: none;
  }

  .category-pill:hover,
  .category-pill:active {
    transform: none;
  }
}
</style>
