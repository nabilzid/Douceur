<template>
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="brand-name">Douceur</span>
                <p class="brand-tagline">Pâtisseries artisanales, faites avec amour.</p>
            </div>
            <div class="footer-links">
                <RouterLink to="/catalogue">Catalogue</RouterLink>
                <RouterLink to="/panier">Panier</RouterLink>
            </div>
            <p class="footer-copy">© {{ year }} Douceur — Tous droits réservés</p>
        </div>
    </footer>
</template>

<script setup>
const year = new Date().getFullYear();
</script>

<style scoped>
.footer {
    background: var(--chocolate);
    color: var(--cream);
    padding: 3rem 1.5rem 1.5rem;
    margin-top: 4rem;
}

.footer-inner {
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.footer-brand {
    max-width: 320px;
}

.brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.65rem;
    font-weight: 800;
    color: var(--gold);
}

.brand-tagline {
    margin-top: 0.5rem;
    color: rgba(253, 248, 240, 0.7);
    font-size: 0.9rem;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: rgba(253, 248, 240, 0.85);
    font-size: 0.9rem;
    font-weight: 500;
}

.footer-links a:hover {
    color: var(--gold);
}

.footer-copy {
    margin-top: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(212, 165, 116, 0.2);
    font-size: 0.8rem;
    color: rgba(253, 248, 240, 0.5);
}
</style>