<?php
require_once '../bootstrap.php';

// Already logged in as admin → go to dashboard
if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? '') === 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = '❌ Te rog completează email-ul și parola!';
        } else {
            $stmt = $conn->prepare(
                "SELECT id, name, password, user_type FROM users WHERE email = ? AND user_type = 'admin' LIMIT 1"
            );
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID on privilege escalation
                    session_regenerate_id(true);

                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['admin_logged_in_at'] = time();

                    // Rotate CSRF token after login
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: dashboard.php');
                    exit;
                }
            }
            // Generic error to avoid user enumeration
            $error = '❌ Email sau parolă incorectă!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-login-body">
    <div class="admin-login-card">
        <div class="admin-login-logo">
            <img src="../img/meditrust-logo.png" alt="MediTrust Logo">
            <h2>Admin Panel</h2>
            <p>Autentificare administrator MediTrust</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="email">📧 Email</label>
                <input type="email" id="email" name="email"
                       placeholder="admin@exemplu.com"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">🔐 Parolă</label>
                <input type="password" id="password" name="password"
                       placeholder="Parola de administrator"
                       required autocomplete="current-password">
            </div>

            <button type="submit" class="admin-login-btn">🔓 Conectare Admin</button>
        </form>

        <div class="admin-login-back">
            <a href="../index.php">← Înapoi la site</a>
        </div>
    </div>
</body>
</html>
