<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $user_type        = $_POST['user_type'] ?? 'patient';
    $phone            = trim($_POST['phone'] ?? '');
    $specialty_id     = (int)($_POST['specialty_id'] ?? 0);
    $bio              = trim($_POST['bio'] ?? '');

    $allowedTypes = ['admin', 'doctor', 'patient'];
    $user_type = in_array($user_type, $allowedTypes, true) ? $user_type : 'patient';

    if (empty($name) || empty($email) || empty($password)) {
        $error = '❌ Câmpurile Nume, Email și Parolă sunt obligatorii!';
    } elseif ($password !== $password_confirm) {
        $error = '❌ Parolele nu se potrivesc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Adresa de email nu este validă!';
    } elseif (strlen($password) < 6) {
        $error = '❌ Parola trebuie să aibă cel puțin 6 caractere!';
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$chk) {
            $error = '❌ Eroare la pregătirea verificării emailului!';
        } else {
            $chk->bind_param('s', $email);
            $chk->execute();
            $result = $chk->get_result();

            if ($result && $result->num_rows > 0) {
                $error = '❌ Există deja un cont cu acest email!';
            } elseif ($user_type === 'doctor' && $specialty_id <= 0) {
                $error = '❌ Pentru doctori, specialitatea este obligatorie!';
            }
        }
    }

    if (empty($error)) {
        $conn->begin_transaction();

        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $ins = $conn->prepare(
                "INSERT INTO users (name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?)"
            );

            if (!$ins) {
                throw new Exception('Eroare la pregătirea inserării utilizatorului.');
            }

            $ins->bind_param('sssss', $name, $email, $hashed, $user_type, $phone);

            if (!$ins->execute()) {
                throw new Exception('Eroare la crearea utilizatorului: ' . $ins->error);
            }

            $user_id = $conn->insert_id;

            if ($user_type === 'doctor') {
                $ins_doc = $conn->prepare(
                    "INSERT INTO info_doctori (user_id, specialty_id, bio, avatar) VALUES (?, ?, ?, NULL)"
                );

                if (!$ins_doc) {
                    throw new Exception('Eroare la pregătirea inserării informațiilor doctorului.');
                }

                $ins_doc->bind_param('iis', $user_id, $specialty_id, $bio);

                if (!$ins_doc->execute()) {
                    throw new Exception('Eroare la crearea informațiilor doctorului: ' . $ins_doc->error);
                }
            }

            $conn->commit();
            $message = '✅ Utilizatorul a fost creat cu succes!';
            $_POST = [];
        } catch (Exception $e) {
            $conn->rollback();
            $error = '❌ ' . htmlspecialchars($e->getMessage());
        }
    }
}

$specialties = [];
$specResult = $conn->query("SELECT id, name FROM specialties ORDER BY name");
if ($specResult) {
    $specialties = $specResult->fetch_all(MYSQLI_ASSOC);
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
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                placeholder="Ex: Ion Popescu"
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                required
                                maxlength="150"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">📧 Email *</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="exemplu@email.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required
                                maxlength="200"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">🔐 Parolă *</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Minim 6 caractere"
                                required
                                minlength="6"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">🔐 Confirmă Parola *</label>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="form-control"
                                placeholder="Repetă parola"
                                required
                                minlength="6"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_type">👥 Tip utilizator *</label>
                            <select id="user_type" name="user_type" class="form-control" required onchange="toggleSpecialty()">
                                <option value="patient" <?php echo ($_POST['user_type'] ?? '') === 'patient' ? 'selected' : ''; ?>>Pacient</option>
                                <option value="doctor" <?php echo ($_POST['user_type'] ?? '') === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                                <option value="admin" <?php echo ($_POST['user_type'] ?? '') === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="phone">📞 Telefon</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                placeholder="+40 712 345 678"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                maxlength="30"
                            >
                        </div>
                    </div>

                    <div id="doctorFields" style="display:none;">
                        <hr class="separator">

                        <div class="form-group">
                            <label for="specialty_id">🏥 Specialitate *</label>
                            <select id="specialty_id" name="specialty_id" class="form-control">
                                <option value="">-- Selectează specialitate --</option>
                                <?php foreach ($specialties as $spec): ?>
                                    <option
                                        value="<?php echo (int)$spec['id']; ?>"
                                        <?php echo (string)($_POST['specialty_id'] ?? '') === (string)$spec['id'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo htmlspecialchars($spec['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="bio">📝 Biografie</label>
                            <textarea
                                id="bio"
                                name="bio"
                                class="form-control"
                                rows="4"
                                placeholder="Descrierea profesională a doctorului..."
                                maxlength="1000"
                            ><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                            <div class="form-text">Opțional - informații despre experiență și specializare</div>
                        </div>
                    </div>

                    <hr class="separator">

                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <a href="users.php" class="btn btn-secondary">Anulează</a>
                        <button type="submit" class="btn btn-primary">✅ Creează Utilizator</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function toggleSpecialty() {
    const userType = document.getElementById('user_type').value;
    const doctorFields = document.getElementById('doctorFields');
    const specialtySelect = document.getElementById('specialty_id');

    if (userType === 'doctor') {
        doctorFields.style.display = 'block';
        specialtySelect.required = true;
    } else {
        doctorFields.style.display = 'none';
        specialtySelect.required = false;
        specialtySelect.value = '';
    }
}

document.addEventListener('DOMContentLoaded', toggleSpecialty);
</script>

<script src="../js/admin.js"></script>
</body>
</html>