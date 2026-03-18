<?php
require_once '../bootstrap.php';

requireLogin('login.php');

$user_id = $_SESSION['user_id'];

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch user data
$user = getUserById($conn, $user_id);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $message = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $bio   = trim($_POST['bio'] ?? '');

            if (empty($name) || empty($email)) {
                $message = '❌ Nume și email sunt obligatorii!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = '❌ Email invalid!';
            } else {
                // Check duplicate email
                $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $chk->bind_param("si", $email, $user_id);
                $chk->execute();
                $chk_result = $chk->get_result();

                if ($chk_result && $chk_result->num_rows > 0) {
                    $message = '❌ Există deja un cont cu acest email!';
                } else {
                    $conn->begin_transaction();

                    try {
                        $update_stmt = $conn->prepare("
                            UPDATE users
                            SET name = ?, email = ?, phone = ?
                            WHERE id = ?
                        ");
                        $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);

                        if (!$update_stmt->execute()) {
                            throw new Exception('Eroare la actualizarea profilului: ' . $update_stmt->error);
                        }

                        // Update doctor bio separately
                        if (($user['user_type'] ?? '') === 'doctor') {
                            $bio_stmt = $conn->prepare("
                                UPDATE info_doctori
                                SET bio = ?
                                WHERE user_id = ?
                            ");
                            $bio_stmt->bind_param("si", $bio, $user_id);

                            if (!$bio_stmt->execute()) {
                                throw new Exception('Eroare la actualizarea biografiei: ' . $bio_stmt->error);
                            }
                        }

                        $conn->commit();

                        $_SESSION['user_name'] = $name;
                        $message = '✅ Profil actualizat cu succes!';
                        $user = getUserById($conn, $user_id);
                    } catch (Exception $e) {
                        $conn->rollback();
                        $message = '❌ ' . $e->getMessage();
                    }
                }
            }
        }

        if ($action === 'change_password') {
            $old_password     = $_POST['old_password'] ?? '';
            $new_password     = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!password_verify($old_password, $user['password'])) {
                $message = '❌ Parola veche este greșită!';
            } elseif ($new_password !== $confirm_password) {
                $message = '❌ Parolele noi nu se potrivesc!';
            } elseif (strlen($new_password) < 6) {
                $message = '❌ Parola trebuie să aibă cel puțin 6 caractere!';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $password_stmt->bind_param("si", $hashed_password, $user_id);

                if ($password_stmt->execute()) {
                    $message = '✅ Parola schimbată cu succes!';
                    $user = getUserById($conn, $user_id);
                } else {
                    $message = '❌ Eroare la schimbarea parolei: ' . $password_stmt->error;
                }
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
    <title>Profil - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $headerLinks = [
        ['href' => 'dashboard.php', 'label' => '← Dashboard'],
        ['href' => 'logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="dashboard.php" class="back-btn">← Înapoi la Dashboard</a>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-card">
                <h2>👤 Editare Profil</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label for="name">📝 Nume Complet:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">📧 Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">📱 Telefon:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <?php if (($user['user_type'] ?? '') === 'doctor'): ?>
                        <div class="form-group">
                            <label for="bio">ℹ️ Bio:</label>
                            <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="submit-btn">💾 Salvează Modificări</button>
                </form>
            </div>

            <div class="password-card">
                <h2>🔐 Schimbă Parola</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="old_password">Parola Veche:</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Parola Nouă:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmă Parola:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="submit-btn">🔄 Schimbă Parola</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>