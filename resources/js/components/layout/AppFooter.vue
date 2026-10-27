
<template>
  <footer class="relative mt-24 overflow-hidden bg-chocolate text-cream">

    <!-- Décoration supérieure -->
    <div class="h-1 w-full bg-gradient-to-r from-caramel-dark via-gold to-caramel" />

    <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-10">

      <!-- Bannière signature -->
      <div
        class="relative -mt-0 overflow-hidden border-b border-gold/20
               py-12 sm:py-16 lg:py-20"
      >
        <div
          class="pointer-events-none absolute -right-12 -top-20
                 h-64 w-64 rounded-full border border-gold/10
                 sm:h-80 sm:w-80"
        />
        <div
          class="pointer-events-none absolute -right-2 top-0
                 h-48 w-48 rounded-full border border-gold/10
                 sm:h-64 sm:w-64"
        />

        <div
          class="relative flex flex-col items-start justify-between
                 gap-8 md:flex-row md:items-center"
        >
          <div class="max-w-2xl">
            <span
              class="mb-4 inline-flex items-center gap-2
                     text-xs font-semibold uppercase tracking-[0.25em]
                     text-caramel"
            >
              <span class="h-px w-8 bg-caramel" />
              L'art de la gourmandise
            </span>

            <h2
              class="font-display text-3xl font-bold leading-tight
                     text-gold sm:text-4xl lg:text-5xl"
            >
              Une petite douceur,<br class="hidden sm:block" />
              un grand moment de bonheur.
            </h2>

            <p class="mt-4 max-w-lg text-sm leading-7 text-cream/65 sm:text-base">
              Des créations artisanales imaginées avec passion,
              pour rendre chaque instant un peu plus délicieux.
            </p>
          </div>

          <RouterLink
            to="/catalogue"
            class="group inline-flex shrink-0 items-center gap-3
                   rounded-full bg-caramel px-6 py-4
                   text-sm font-semibold text-chocolate
                   shadow-soft transition-all duration-300
                   hover:-translate-y-1 hover:bg-gold
                   focus-visible:outline-none focus-visible:ring-2
                   focus-visible:ring-gold focus-visible:ring-offset-2
                   focus-visible:ring-offset-chocolate"
          >
            Découvrir nos créations

            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              class="h-4 w-4 transition-transform duration-300
                     group-hover:translate-x-1"
              aria-hidden="true"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M7 17 17 7M7 7h10v10"
              />
            </svg>
          </RouterLink>
        </div>
      </div>

      <!-- Contenu principal -->
      <div class="grid grid-cols-1 gap-12 py-12 sm:grid-cols-2
                  lg:grid-cols-12 lg:gap-10 lg:py-16">

        <!-- Marque -->
        <div class="sm:col-span-2 lg:col-span-4">

          <RouterLink
            to="/"
            class="group inline-flex items-center gap-3"
            aria-label="Douceur, accueil"
          >
            <span
              class="grid h-12 w-12 place-items-center rounded-2xl
                     bg-gradient-to-br from-caramel to-caramel-dark
                     shadow-soft transition-transform duration-300
                     group-hover:rotate-3"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-7 w-7 text-chocolate"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M5 14h14l-1.5 7h-11L5 14Z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M7 14a5 5 0 0 1 10 0M12 9V6m0 0a2 2 0 1 0-2-2"
                />
              </svg>
            </span>

            <span>
              <span
                class="block font-display text-3xl font-extrabold
                       tracking-tight text-gold"
              >
                Douceur<span class="text-caramel">.</span>
              </span>
              <span
                class="mt-0.5 block text-[10px] uppercase
                       tracking-[0.24em] text-cream/50"
              >
                Pâtisserie artisanale
              </span>
            </span>
          </RouterLink>

          <p class="mt-6 max-w-sm text-sm leading-7 text-cream/65">
            Le goût des bonnes choses, le plaisir du fait maison
            et l'amour des détails. Bienvenue dans notre univers
            gourmand, imaginé à Paris depuis 2020.
          </p>

          <!-- Coordonnées -->
          <div class="mt-7 space-y-4">

            <a
              href="https://www.google.com/maps/search/?api=1&query=12+rue+des+P%C3%A2tissiers+75011+Paris"
              target="_blank"
              rel="noopener noreferrer"
              class="group flex items-start gap-3 text-sm
                     text-cream/75 transition-colors
                     hover:text-caramel"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                class="mt-0.5 h-5 w-5 shrink-0 text-caramel"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"
                />
                <circle cx="12" cy="10" r="2.5" />
              </svg>

              <span class="leading-6">
                12 rue des Pâtissiers<br />
                75011 Paris
              </span>
            </a>

            <a
              href="tel:+33123456789"
              class="group flex items-center gap-3 text-sm
                     text-cream/75 transition-colors
                     hover:text-caramel"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                class="h-5 w-5 shrink-0 text-caramel"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M5 3h4l2 5-2.5 1.5a15 15 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2Z"
                />
              </svg>
              01 23 45 67 89
            </a>

            <a
              href="mailto:contact@douceur.fr"
              class="group flex items-center gap-3 text-sm
                     text-cream/75 transition-colors
                     hover:text-caramel"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.6"
                class="h-5 w-5 shrink-0 text-caramel"
                aria-hidden="true"
              >
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m4 7 8 6 8-6"
                />
              </svg>
              contact@douceur.fr
            </a>

          </div>

          <!-- Réseaux sociaux -->
          <div class="mt-8 flex items-center gap-3">

            <a
              href="#"
              aria-label="Instagram"
              class="grid h-11 w-11 place-items-center rounded-full
                     border border-cream/15 text-cream/75
                     transition-all duration-300
                     hover:border-caramel hover:bg-caramel
                     hover:text-chocolate
                     focus-visible:outline-none focus-visible:ring-2
                     focus-visible:ring-gold"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                class="h-5 w-5"
                aria-hidden="true"
              >
                <rect x="3" y="3" width="18" height="18" rx="5" />
                <circle cx="12" cy="12" r="4" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
              </svg>
            </a>

            <a
              href="#"
              aria-label="Facebook"
              class="grid h-11 w-11 place-items-center rounded-full
                     border border-cream/15 text-cream/75
                     transition-all duration-300
                     hover:border-caramel hover:bg-caramel
                     hover:text-chocolate
                     focus-visible:outline-none focus-visible:ring-2
                     focus-visible:ring-gold"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="h-5 w-5"
                aria-hidden="true"
              >
                <path
                  d="M13.5 21v-8h2.7l.4-3.1h-3.1V8c0-.9.3-1.5 1.6-1.5h1.7V3.7c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v1.8H7V13h2.8v8h3.7Z"
                />
              </svg>
            </a>

          </div>
        </div>

        <!-- Navigation -->
        <div class="lg:col-span-2 lg:col-start-6">
          <h3
            class="mb-6 flex items-center gap-3 font-display
                   text-base font-bold text-gold"
          >
            La boutique
            <span class="h-px flex-1 bg-gold/20" />
          </h3>

          <ul class="space-y-4 text-sm">
            <li>
              <RouterLink
                to="/catalogue"
                class="footer-link"
              >
                Nos créations
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/evenements" class="footer-link">
                Événements
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/panier" class="footer-link">
                Mon panier
              </RouterLink>
            </li>
          </ul>

          <h3
            class="mb-6 mt-10 flex items-center gap-3 font-display
                   text-base font-bold text-gold"
          >
            Votre espace
            <span class="h-px flex-1 bg-gold/20" />
          </h3>

          <ul class="space-y-4 text-sm">
            <li>
              <RouterLink to="/suivi-commande" class="footer-link">
                Suivre ma commande
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/suivi-demande" class="footer-link">
                Suivre ma demande
              </RouterLink>
            </li>
            <li>
              <a href="mailto:contact@douceur.fr" class="footer-link">
                Nous contacter
              </a>
            </li>
          </ul>
        </div>

        <!-- Informations -->
        <div class="lg:col-span-4 lg:col-start-9">
          <h3
            class="mb-6 flex items-center gap-3 font-display
                   text-base font-bold text-gold"
          >
            Informations
            <span class="h-px flex-1 bg-gold/20" />
          </h3>

          <ul class="space-y-4 text-sm">
            <li>
              <RouterLink to="/cgv" class="footer-link">
                Conditions générales de vente
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/mentions-legales" class="footer-link">
                Mentions légales
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/confidentialite" class="footer-link">
                Politique de confidentialité
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/cookies" class="footer-link">
                Gestion des cookies
              </RouterLink>
            </li>
          </ul>

          <!-- Petit élément de signature -->
          <div
            class="mt-10 rounded-2xl border border-gold/15
                   bg-white/[0.03] p-5"
          >
            <span class="text-xs font-semibold uppercase
                         tracking-[0.2em] text-caramel">
              Notre philosophie
            </span>

            <p class="mt-3 font-display text-lg italic
                      leading-relaxed text-cream/90">
              « Le bonheur se partage aussi à travers une pâtisserie. »
            </p>
          </div>
        </div>

      </div>

      <!-- Bas de page -->
      <div
        class="flex flex-col gap-4 border-t border-gold/20
               py-6 text-xs text-cream/50
               sm:flex-row sm:items-center sm:justify-between"
      >
        <p>
          © {{ year }} Douceur. Tous droits réservés.
        </p>

        <p class="flex items-center gap-2">
          Imaginé avec
          <span class="text-caramel" aria-label="amour">♥</span>
          à Paris
        </p>

        <RouterLink
          to="/"
          class="inline-flex items-center gap-2
                 transition-colors hover:text-gold"
        >
          Retour à l'accueil
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
              d="M12 19V5m-7 7 7-7 7 7"
            />
          </svg>
        </RouterLink>
      </div>

    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear();
</script>

<style scoped>
.footer-link {
  display: inline-flex;
  align-items: center;
  color: rgb(255 248 240 / 70%);
  transition: color 200ms ease, transform 200ms ease;
}

.footer-link::before {
  content: "";
  width: 0;
  height: 1px;
  margin-right: 0;
  background-color: currentColor;
  transition: width 200ms ease, margin-right 200ms ease;
}

.footer-link:hover {
  color: #e8c49a;
  transform: translateX(2px);
}

.footer-link:hover::before {
  width: 10px;
  margin-right: 8px;
}

.footer-link:focus-visible {
  outline: 2px solid #e8c49a;
  outline-offset: 4px;
  border-radius: 2px;
}
</style>
