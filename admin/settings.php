<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

// Load current admin details
$admin = getUserById($conn, $_SESSION['user_id']);

if (!$admin) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── Change password ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    adminVerifyCsrf();

    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = '❌ Toate câmpurile sunt obligatorii!';
    } elseif (!password_verify($current_password, $admin['password'])) {
        $error = '❌ Parola curentă este incorectă!';
    } elseif (strlen($new_password) < 6) {
        $error = '❌ Noua parolă trebuie să aibă cel puțin 6 caractere!';
    } elseif ($new_password !== $confirm_password) {
        $error = '❌ Parolele noi nu se potrivesc!';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");

        if (!$upd) {
            $error = '❌ Eroare la pregătirea schimbării parolei!';
        } else {
            $upd->bind_param('si', $hashed, $_SESSION['user_id']);

            if ($upd->execute()) {
                $message = '✅ Parola a fost schimbată cu succes!';
                $admin = getUserById($conn, $_SESSION['user_id']);
            } else {
                $error = '❌ Eroare la schimbarea parolei: ' . $upd->error;
            }
        }
    }
}

// ── Update profile info ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    adminVerifyCsrf();

    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {
        $error = '❌ Numele și emailul sunt obligatorii!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Email invalid!';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");

        if (!$chk) {
            $error = '❌ Eroare la verificarea emailului!';
        } else {
            $chk->bind_param('si', $email, $_SESSION['user_id']);
            $chk->execute();
            $chk_result = $chk->get_result();

            if ($chk_result && $chk_result->num_rows > 0) {
                $error = '❌ Email-ul este deja folosit de alt cont!';
            } else {
                $upd = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");

                if (!$upd) {
                    $error = '❌ Eroare la pregătirea actualizării profilului!';
                } else {
                    $upd->bind_param('sssi', $name, $email, $phone, $_SESSION['user_id']);

                    if ($upd->execute()) {
                        $_SESSION['user_name'] = $name;
                        $message = '✅ Profilul a fost actualizat!';
                        $admin = getUserById($conn, $_SESSION['user_id']);
                    } else {
                        $error = '❌ Eroare la actualizare: ' . $upd->error;
                    }
                }
            }
        }
    }
}

$adminActivePage = 'settings';
$adminPageTitle  = 'Setări Admin';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Setări - Admin MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-body">

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="admin-main">
    <?php require_once '../includes/admin-header.php'; ?>

    <div class="admin-content">

        <?php if ($message): ?>
            <div class="alert alert-success" data-auto-dismiss="4000">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;max-width:900px;">

            <!-- Profile info -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>👤 Informații Profil</h3>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="form-group">
                            <label for="name">Nume complet</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin['name']); ?>"
                                required
                                maxlength="150"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin['email']); ?>"
                                required
                                maxlength="200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>"
                                maxlength="30"
                            >
                        </div>

                        <div class="form-group">
                            <label>Rol</label>
                            <input type="text" class="form-control" value="Administrator" disabled>
                        </div>

                        <button type="submit" class="btn btn-primary">💾 Salvează</button>
                    </form>
                </div>
            </div>

            <!-- Change password -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>🔐 Schimbă Parola</h3>
                </div>
                <div class="admin-card-body">
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="change_password" value="1">

                        <div class="form-group">
                            <label for="current_password">Parola Curentă</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control"
                                placeholder="Parola actuală"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <div class="form-group">
                            <label for="new_password">Parolă Nouă</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control"
                                placeholder="Minim 6 caractere"
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmă Parola Nouă</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Repetă noua parolă"
                                required
                                minlength="6"
                                autocomplete="new-password"
                            >
                        </div>

                        <button type="submit" class="btn btn-warning">🔐 Schimbă Parola</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Site info -->
        <div class="admin-card" style="max-width:900px;margin-top:0;">
            <div class="admin-card-header">
                <h3>ℹ️ Informații Sistem</h3>
            </div>
            <div class="admin-card-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                    <div>
                        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Versiune PHP</div>
                        <div class="fw-bold"><?php echo phpversion(); ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Server</div>
                        <div class="fw-bold"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Baza de date</div>
                        <div class="fw-bold"><?php echo htmlspecialchars(DB_NAME); ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Fusul orar</div>
                        <div class="fw-bold"><?php echo date_default_timezone_get(); ?></div>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Data/Ora Server</div>
                        <div class="fw-bold"><?php echo date('d.m.Y H:i:s'); ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>