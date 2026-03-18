<?php
require_once '../bootstrap.php';

// Redirect dacă utilizatorul este deja autentificat
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['user_type'] ?? '') === 'admin') {
        header("Location: ../admin/dashboard.php", true, 302);
        exit;
    }

    if (in_array($_SESSION['user_type'] ?? '', ['doctor', 'patient'], true)) {
        header("Location: dashboard.php", true, 302);
        exit;
    }
}

// Generează token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = '❌ Te rog completează email și parola!';
        } else {
            $stmt = $conn->prepare("SELECT id, name, password, user_type FROM users WHERE email = ? LIMIT 1");

            if (!$stmt) {
                $error = '❌ Eroare la autentificare. Încearcă din nou.';
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_type'] = $user['user_type'];

                        // Regenerează CSRF după login
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        if (($user['user_type'] ?? '') === 'admin') {
                            header("Location: ../admin/dashboard.php", true, 302);
                            exit;
                        }

                        header("Location: dashboard.php", true, 302);
                        exit;
                    }
                }

                $error = '❌ Email sau parolă incorectă!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <main class="container auth-shell auth-shell-login">
        <section class="auth-info">
            <img src="../img/meditrust-logo.png" alt="MediTrust Logo" class="auth-logo">
            <h1>Bine ai revenit la MediTrust</h1>
            <p>Conectează-te pentru a gestiona programările, recenziile și profilul tău medical într-un singur loc.</p>
            <a href="../index.php" class="auth-back-link">← Înapoi la pagina principală</a>
        </section>

        <section class="auth-card">
            <div class="auth-card-header">
                <h2>🔓 Conectare</h2>
                <p>Introdu email-ul și parola contului tău.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="exemplu@email.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                Nu ai cont? <a href="register.php">Creează cont</a>
            </div>
        </section>
    </main>
</body>
</html>