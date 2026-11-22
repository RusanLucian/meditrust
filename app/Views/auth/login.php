<main class="container auth-shell auth-shell-login">
    <section class="auth-info">
        <img src="<?= asset('img/meditrust-logo.png') ?>" alt="MediTrust Logo" class="auth-logo">
        <h1>Bine ai revenit la MediTrust</h1>
        <p>Conectează-te pentru a gestiona programările, recenziile și profilul tău medical într-un singur loc.</p>
        <a href="<?= route('home') ?>" class="auth-back-link">← Înapoi la pagina principală</a>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h2>🔓 Conectare</h2>
            <p>Introdu email-ul și parola contului tău.</p>
        </div>

        <form method="POST" action="<?= route('login') ?>" class="auth-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="email">📧 Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="exemplu@email.com"
                    value="<?= e(old('email')) ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">🔐 Parola</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Parola"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="auth-submit">Conectare</button>
        </form>

        <div class="auth-footer">
            Nu ai cont? <a href="<?= route('register') ?>">Creează cont</a>
        </div>
    </section>
</main>
