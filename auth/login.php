<?php
require_once '../bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Te rog completează email și parola!";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['user_type'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "❌ Parola greșită!";
            }
        } else {
            $error = "❌ Email nu există!";
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
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="exemplu@email.com" autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label for="password">🔐 Parola</label>
                    <input type="password" id="password" name="password" placeholder="Parola" required>
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