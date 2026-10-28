<!-- Page 404 -->
<template>
  <div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full text-center">

      <!-- Icône 404 -->
      <div class="relative inline-block mb-6">
        <div class="text-7xl md:text-8xl select-none">🧁</div>
        <span class="absolute -top-2 -right-4 px-2.5 py-1 rounded-full bg-raspberry text-white text-xs font-bold shadow-soft">
          404
        </span>
      </div>

      <!-- Titre -->
      <h1 class="font-display text-3xl md:text-4xl font-bold text-chocolate mb-3">
        Page introuvable
      </h1>

      <!-- Message -->
      <p class="text-base text-muted leading-relaxed mb-8">
        Oups… On dirait que cette page s'est perdue en cuisine.
        Retournez à l'accueil pour retrouver nos douceurs.
      </p>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <RouterLink
          to="/"
          class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full bg-caramel hover:bg-caramel-dark text-white font-semibold text-sm shadow-soft hover:shadow-card transition-all hover:-translate-y-0.5"
        >
          ← Retour à l'accueil
        </RouterLink>
        <RouterLink
          to="/catalogue"
          class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full bg-white hover:bg-cream text-chocolate-soft hover:text-caramel-dark border-2 border-cream-dark hover:border-caramel-light font-semibold text-sm transition-all"
        >
          Voir le catalogue
        </RouterLink>
      </div>

    </div>
  </div>
</template>