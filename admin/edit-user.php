<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    header('Location: users.php');
    exit;
}

// Load user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name      = trim($_POST['name']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $user_type = $_POST['user_type']      ?? $user['user_type'];
    $phone     = trim($_POST['phone']     ?? '');
    $specialty = trim($_POST['specialty'] ?? '');

    $allowedTypes = ['admin', 'doctor', 'medic', 'patient', 'pacient'];
    $user_type = in_array($user_type, $allowedTypes, true) ? $user_type : $user['user_type'];

    // Prevent demoting the only admin
    if ($user['user_type'] === 'admin' && $user_type !== 'admin') {
        $adminCount = $conn->query("SELECT COUNT(*) AS c FROM users WHERE user_type = 'admin'")->fetch_assoc()['c'];
        if ($adminCount <= 1) {
            $error = '❌ Nu poți modifica rolul singurului administrator!';
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($email)) {
            $error = '❌ Câmpurile Nume și Email sunt obligatorii!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '❌ Adresa de email nu este validă!';
        } elseif ($password !== '' && strlen($password) < 6) {
            $error = '❌ Parola trebuie să aibă cel puțin 6 caractere!';
        } else {
            // Check email not taken by another user
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->bind_param('si', $email, $user_id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = '❌ Există deja alt cont cu acest email!';
            }
        }
    }

    if (empty($error)) {
        $specialtyVal = (in_array($user_type, ['doctor', 'medic']) && $specialty !== '') ? $specialty : null;

        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $upd = $conn->prepare(
                "UPDATE users SET name=?, email=?, password=?, user_type=?, phone=?, specialty=? WHERE id=?"
            );
            $upd->bind_param('ssssssi', $name, $email, $hashed, $user_type, $phone, $specialtyVal, $user_id);
        } else {
            $upd = $conn->prepare(
                "UPDATE users SET name=?, email=?, user_type=?, phone=?, specialty=? WHERE id=?"
            );
            $upd->bind_param('sssssi', $name, $email, $user_type, $phone, $specialtyVal, $user_id);
        }

        if ($upd->execute()) {
            $message = '✅ Utilizatorul a fost actualizat cu succes!';
            // Reload fresh data
            $stmt2 = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt2->bind_param('i', $user_id);
            $stmt2->execute();
            $user = $stmt2->get_result()->fetch_assoc();
        } else {
            $error = '❌ Eroare la actualizare: ' . htmlspecialchars($conn->error);
        }
    }
}

$adminActivePage = 'users';
$adminPageTitle  = 'Editează Utilizator';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Editează Utilizator - Admin MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-body">

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="admin-main">
    <?php require_once '../includes/admin-header.php'; ?>

    <div class="admin-content">

        <?php if ($message): ?><div class="alert alert-success" data-auto-dismiss="4000"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <div class="admin-card" style="max-width:700px;">
            <div class="admin-card-header">
                <h3>✏️ Editează: <?php echo htmlspecialchars($user['name']); ?></h3>
                <a href="users.php" class="btn btn-secondary btn-sm">← Înapoi</a>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="edit-user.php?id=<?php echo (int)$user_id; ?>" id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">👤 Nume complet *</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="<?php echo htmlspecialchars($user['name']); ?>"
                                   required maxlength="150">
                        </div>
                        <div class="form-group">
                            <label for="email">📧 Email *</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   required maxlength="200">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">🔐 Parolă nouă</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Lasă gol pentru a nu schimba"
                                   minlength="6">
                            <div class="form-text">Completează doar dacă vrei să schimbi parola.</div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">🔐 Confirmă Parola</label>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                                   placeholder="Repetă noua parolă">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_type">👥 Tip utilizator *</label>
                            <?php if ($user['user_type'] === 'admin'): ?>
                                <input type="text" class="form-control" value="Administrator" disabled
                                       title="Rolul de administrator nu poate fi schimbat">
                                <input type="hidden" name="user_type" value="admin">
                                <div class="form-text">Rolul de administrator nu poate fi modificat.</div>
                            <?php else: ?>
                                <select id="user_type" name="user_type" class="form-control" required>
                                    <option value="pacient"  <?php echo $user['user_type'] === 'pacient'  ? 'selected' : ''; ?>>Pacient</option>
                                    <option value="patient"  <?php echo $user['user_type'] === 'patient'  ? 'selected' : ''; ?>>Patient</option>
                                    <option value="medic"    <?php echo $user['user_type'] === 'medic'    ? 'selected' : ''; ?>>Medic</option>
                                    <option value="doctor"   <?php echo $user['user_type'] === 'doctor'   ? 'selected' : ''; ?>>Doctor</option>
                                    <option value="admin"    <?php echo $user['user_type'] === 'admin'    ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="phone">📞 Telefon</label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   maxlength="30">
                        </div>
                    </div>

                    <div class="form-group" id="specialtyGroup">
                        <label for="specialty">🏥 Specialitate</label>
                        <input type="text" id="specialty" name="specialty" class="form-control"
                               placeholder="Ex: Cardiologie, Pediatrie..."
                               value="<?php echo htmlspecialchars($user['specialty'] ?? ''); ?>"
                               maxlength="150">
                    </div>

                    <hr class="separator">
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <a href="users.php" class="btn btn-secondary">Anulează</a>
                        <button type="submit" class="btn btn-primary">✅ Salvează Modificările</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="../js/admin.js"></script>
<script>
// Pre-set user_type value for specialty toggle
document.addEventListener('DOMContentLoaded', function() {
    // If user_type select is disabled (admin), set the hidden value
    var sel = document.getElementById('user_type');
    if (sel) {
        // Trigger specialty toggle on load
        var event = new Event('change');
        sel.dispatchEvent(event);
    }
});
</script>
</body>
</html>
