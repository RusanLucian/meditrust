<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: auth/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrust - Evaluare Medici</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="landing-page">
    <main class="container landing-shell">
        <section class="landing-hero">
            <div class="landing-brand">
                <img src="img/meditrust-logo.png" alt="MediTrust Logo" class="landing-logo">
                <div>
                    <h1>MediTrust</h1>
                    <p class="landing-subtitle">Platformă digitală pentru alegerea medicului potrivit și gestionarea rapidă a programărilor.</p>
                </div>
            </div>

            <div class="landing-actions">
                <a href="auth/login.php" class="landing-btn primary">🔓 Conectare</a>
                <a href="auth/register.php" class="landing-btn secondary">📝 Înregistrare</a>
                <a href="medici/lista.php" class="landing-link">sau vezi lista de medici</a>
            </div>
        </section>

        <section class="landing-features">
            <h2>✨ De ce MediTrust?</h2>
            <div class="landing-feature-grid">
                <a href="medici/lista.php" class="landing-feature-item">
                    <strong>🔍 Caută Medici</strong>
                    <span>Filtrezi rapid după specialitate și alegi medicul potrivit.</span>
                </a>
                <a href="medici/lista.php" class="landing-feature-item">
                    <strong>⭐ Citește Recenzii</strong>
                    <span>Vezi experiențele altor pacienți înainte să programezi consultația.</span>
                </a>
                <a href="auth/login.php" class="landing-feature-item">
                    <strong>📅 Fă Programări</strong>
                    <span>Programezi online în câteva secunde, direct din contul tău.</span>
                </a>
                <a href="medici/lista.php" class="landing-feature-item">
                    <strong>💬 Lasă Feedback</strong>
                    <span>Contribui cu recenzii utile pentru comunitatea MediTrust.</span>
                </a>
            </div>
        </section>
    </main>
</body>
</html>