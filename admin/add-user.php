<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name      = trim($_POST['name']      ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $user_type = $_POST['user_type']      ?? 'pacient';
    $phone     = trim($_POST['phone']     ?? '');
    $specialty = trim($_POST['specialty'] ?? '');

    $allowedTypes = ['admin', 'doctor', 'medic', 'patient', 'pacient'];
    $user_type = in_array($user_type, $allowedTypes, true) ? $user_type : 'pacient';

    if (empty($name) || empty($email) || empty($password)) {
        $error = '❌ Câmpurile Nume, Email și Parolă sunt obligatorii!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Adresa de email nu este validă!';
    } elseif (strlen($password) < 6) {
        $error = '❌ Parola trebuie să aibă cel puțin 6 caractere!';
    } else {
        // Check duplicate email
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = '❌ Există deja un cont cu acest email!';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $specialtyVal = (in_array($user_type, ['doctor', 'medic']) && $specialty !== '') ? $specialty : null;

            $ins = $conn->prepare(
                "INSERT INTO users (name, email, password, user_type, phone, specialty) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param('ssssss', $name, $email, $hashed, $user_type, $phone, $specialtyVal);

            if ($ins->execute()) {
                $message = '✅ Utilizatorul a fost creat cu succes!';
                // Clear POST data so form resets
                $_POST = [];
            } else {
                $error = '❌ Eroare la crearea utilizatorului: ' . htmlspecialchars($conn->error);
            }
        }
    }
}

$adminActivePage = 'users';
$adminPageTitle  = 'Adaugă Utilizator';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Adaugă Utilizator - Admin MediTrust</title>
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
                <h3>➕ Adaugă Utilizator Nou</h3>
                <a href="users.php" class="btn btn-secondary btn-sm">← Înapoi</a>
            </div>
            <div class="admin-card-body">
                <form method="POST" action="add-user.php" id="addUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">👤 Nume complet *</label>
                            <input type="text" id="name" name="name" class="form-control"
                                   placeholder="Ex: Ion Popescu"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   required maxlength="150">
                        </div>
                        <div class="form-group">
                            <label for="email">📧 Email *</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   placeholder="exemplu@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required maxlength="200">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">🔐 Parolă *</label>
                            <input type="password" id="password" name="password" class="form-control"
                                   placeholder="Minim 6 caractere"
                                   required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">🔐 Confirmă Parola *</label>
                            <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                                   placeholder="Repetă parola"
                                   required minlength="6">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_type">👥 Tip utilizator *</label>
                            <select id="user_type" name="user_type" class="form-control" required>
                                <option value="pacient"  <?php echo ($_POST['user_type'] ?? '') === 'pacient'  ? 'selected' : ''; ?>>Pacient</option>
                                <option value="patient"  <?php echo ($_POST['user_type'] ?? '') === 'patient'  ? 'selected' : ''; ?>>Patient</option>
                                <option value="medic"    <?php echo ($_POST['user_type'] ?? '') === 'medic'    ? 'selected' : ''; ?>>Medic</option>
                                <option value="doctor"   <?php echo ($_POST['user_type'] ?? '') === 'doctor'   ? 'selected' : ''; ?>>Doctor</option>
                                <option value="admin"    <?php echo ($_POST['user_type'] ?? '') === 'admin'    ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="phone">📞 Telefon</label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                   placeholder="+40 712 345 678"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                   maxlength="30">
                        </div>
                    </div>

                    <div class="form-group" id="specialtyGroup">
                        <label for="specialty">🏥 Specialitate (doar pentru doctori)</label>
                        <input type="text" id="specialty" name="specialty" class="form-control"
                               placeholder="Ex: Cardiologie, Pediatrie..."
                               value="<?php echo htmlspecialchars($_POST['specialty'] ?? ''); ?>"
                               maxlength="150">
                    </div>

                    <hr class="separator">
                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <a href="users.php" class="btn btn-secondary">Anulează</a>
                        <button type="submit" class="btn btn-primary"
                                onclick="return validateForm('addUserForm')">
                            ✅ Creează Utilizator
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>
