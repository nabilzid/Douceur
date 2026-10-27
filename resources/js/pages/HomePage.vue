<template>
    <div class="home-page">
        <section class="hero">
            <div class="hero-content fade-in-up">
                <span class="hero-tag">✨ Artisanal & fait maison</span>
                <h1 class="hero-title">
                    Des pâtisseries<br>
                    <em>qui fondent</em> de plaisir
                </h1>
                <p class="hero-subtitle">
                    Commandez vos gâteaux, viennoiseries et douceurs préférés.
                    Livraison à domicile ou retrait en boutique.
                </p>
                <div class="hero-actions">
                    <RouterLink to="/catalogue" class="btn-primary">
                        Découvrir le catalogue →
                    </RouterLink>
                    <RouterLink to="/catalogue" class="btn-secondary">
                        Nos formules événements
                    </RouterLink>
                </div>
            </div>
            <div class="hero-visual fade-in-up">
                <div class="hero-card">
                    <span class="hero-emoji">🍰</span>
                </div>
                <div class="hero-blob"></div>
            </div>
        </section>

        <section class="features">
            <div class="feature">
                <span class="feature-icon">🎁</span>
                <h3>Livraison > 20€</h3>
                <p>Livraison offerte dès 20€ d'achat dans notre zone.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🕒</span>
                <h3>Click & Collect</h3>
                <p>Commandez en ligne, retirez en boutique.</p>
            </div>
            <div class="feature">
                <span class="feature-icon">🎉</span>
                <h3>Événements</h3>
                <p>Formules sur mesure pour vos grands moments.</p>
            </div>
        </section>
    </div>
</template>

<style scoped>
.hero {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 3rem;
    align-items: center;
    padding: 3rem 0 5rem;
}

.hero-tag {
    display: inline-block;
    background: rgba(201, 123, 63, 0.1);
    color: var(--caramel-dark);
    padding: 0.4rem 1rem;
    border-radius: 999px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.hero-title {
    font-size: 3.5rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    margin-bottom: 1.5rem;
}

.hero-title em {
    font-style: italic;
    color: var(--caramel);
    position: relative;
}

.hero-subtitle {
    font-size: 1.15rem;
    color: var(--text-muted);
    max-width: 480px;
    margin-bottom: 2.5rem;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.hero-visual {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.hero-card {
    position: relative;
    z-index: 2;
    width: 320px;
    height: 320px;
    background: linear-gradient(135deg, var(--cream-dark), white);
    border-radius: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-lg);
    transform: rotate(-6deg);
    transition: transform 0.4s ease;
}

.hero-card:hover {
    transform: rotate(0deg) scale(1.03);
}

.hero-emoji {
    font-size: 9rem;
}

.hero-blob {
    position: absolute;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(232, 167, 106, 0.35), transparent 70%);
    border-radius: 50%;
    z-index: 1;
    animation: float 6s ease-in-out infinite;
}

.features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 3rem;
}

.feature {
    background: white;
    border-radius: var(--radius);
    padding: 2rem 1.5rem;
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.feature:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.feature-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 1rem;
}

.feature h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
}

.feature p {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
}

@media (max-width: 900px) {
    .hero {
        grid-template-columns: 1fr;
        text-align: center;
        padding: 2rem 0 3rem;
        gap: 2rem;
    }
    .hero-title {
        font-size: 2.25rem;
    }
    .hero-subtitle {
        margin-left: auto;
        margin-right: auto;
    }
    .hero-actions {
        justify-content: center;
        flex-direction: column;
        align-items: stretch;
    }
    .hero-visual {
        min-height: 260px;
    }
    .hero-card {
        width: 220px;
        height: 220px;
    }
    .hero-emoji {
        font-size: 5rem;
    }
    .hero-blob {
        width: 260px;
        height: 260px;
    }
    .features {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>