<main class="container landing-shell">
    <section class="landing-hero">
        <div class="landing-brand">
            <img src="<?= asset('img/meditrust-logo.png') ?>" alt="MediTrust Logo" class="landing-logo">
            <div>
                <h1>MediTrust</h1>
                <p class="landing-subtitle">Platformă online pentru evaluarea personalului medical și gestionarea programărilor.</p>
            </div>
        </div>

        <div class="landing-actions">
            <?php if ($user = auth()): ?>
                <a href="<?= route('dashboard') ?>" class="landing-btn primary">📊 Accesează Contul Meu</a>
                <a href="<?= route('doctors.index') ?>" class="landing-btn secondary">👨‍⚕️ Caută Medici</a>
            <?php else: ?>
                <a href="<?= route('login') ?>" class="landing-btn primary">🔓 Conectare</a>
                <a href="<?= route('register') ?>" class="landing-btn secondary">📝 Înregistrare</a>
                <a href="<?= route('doctors.index') ?>" class="landing-link">sau vezi lista de medici</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="landing-features">
        <h2>✨ De ce MediTrust?</h2>
        <div class="landing-feature-grid">
            <a href="<?= route('doctors.index') ?>" class="landing-feature-item">
                <strong>🔍 Caută Medici</strong>
                <span>Filtrezi rapid după specialitate și alegi medicul potrivit.</span>
            </a>
            <a href="<?= route('doctors.index') ?>" class="landing-feature-item">
                <strong>⭐ Citește Recenzii</strong>
                <span>Vezi experiențele altor pacienți înainte să programezi consultația.</span>
            </a>
            <a href="<?= auth() ? route('doctors.index') : route('login') ?>" class="landing-feature-item">
                <strong>📅 Fă Programări</strong>
                <span>Programezi online în câteva secunde, direct din contul tău.</span>
            </a>
            <a href="<?= route('doctors.index') ?>" class="landing-feature-item">
                <strong>💬 Lasă Feedback</strong>
                <span>Contribui cu recenzii utile pentru comunitatea MediTrust.</span>
            </a>
        </div>
    </section>
</main>
