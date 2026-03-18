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

// Load doctor info if doctor
$doctor_info = null;
if ($user['user_type'] === 'doctor') {
    $doc_stmt = $conn->prepare("SELECT specialty_id, bio FROM info_doctori WHERE user_id = ?");
    $doc_stmt->bind_param('i', $user_id);
    $doc_stmt->execute();
    $doctor_info = $doc_stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminVerifyCsrf();

    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $user_type        = $_POST['user_type'] ?? $user['user_type'];
    $phone            = trim($_POST['phone'] ?? '');
    $specialty_id     = (int)($_POST['specialty_id'] ?? 0);
    $bio              = trim($_POST['bio'] ?? '');

    $allowedTypes = ['admin', 'doctor', 'patient'];
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
        } elseif ($password !== '' && $password !== $password_confirm) {
            $error = '❌ Parolele nu se potrivesc!';
        } else {
            // Check email not taken by another user
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            if (!$chk) {
                $error = '❌ Eroare la verificarea emailului!';
            } else {
                $chk->bind_param('si', $email, $user_id);
                $chk->execute();
                $chk_result = $chk->get_result();

                if ($chk_result && $chk_result->num_rows > 0) {
                    $error = '❌ Există deja alt cont cu acest email!';
                }
            }
        }

        // If changing to doctor, verify specialty
        if (empty($error) && $user_type === 'doctor' && $specialty_id <= 0) {
            $error = '❌ Pentru doctori, specialitatea este obligatorie!';
        }
    }

    if (empty($error)) {
        $conn->begin_transaction();

        try {
            if ($password !== '') {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare(
                    "UPDATE users SET name = ?, email = ?, password = ?, user_type = ?, phone = ? WHERE id = ?"
                );
                if (!$upd) {
                    throw new Exception('Eroare la pregătirea actualizării utilizatorului.');
                }
                $upd->bind_param('sssssi', $name, $email, $hashed, $user_type, $phone, $user_id);
            } else {
                $upd = $conn->prepare(
                    "UPDATE users SET name = ?, email = ?, user_type = ?, phone = ? WHERE id = ?"
                );
                if (!$upd) {
                    throw new Exception('Eroare la pregătirea actualizării utilizatorului.');
                }
                $upd->bind_param('ssssi', $name, $email, $user_type, $phone, $user_id);
            }

            if (!$upd->execute()) {
                throw new Exception('Eroare la actualizare utilizator: ' . $upd->error);
            }

            // Handle doctor info
            if ($user_type === 'doctor') {
                $check = $conn->prepare("SELECT id FROM info_doctori WHERE user_id = ?");
                if (!$check) {
                    throw new Exception('Eroare la verificarea informațiilor doctorului.');
                }

                $check->bind_param('i', $user_id);
                $check->execute();
                $check_result = $check->get_result();

                if ($check_result && $check_result->num_rows > 0) {
                    $upd_doc = $conn->prepare(
                        "UPDATE info_doctori SET specialty_id = ?, bio = ? WHERE user_id = ?"
                    );
                    if (!$upd_doc) {
                        throw new Exception('Eroare la pregătirea actualizării informațiilor doctorului.');
                    }
                    $upd_doc->bind_param('isi', $specialty_id, $bio, $user_id);
                } else {
                    $upd_doc = $conn->prepare(
                        "INSERT INTO info_doctori (user_id, specialty_id, bio) VALUES (?, ?, ?)"
                    );
                    if (!$upd_doc) {
                        throw new Exception('Eroare la pregătirea inserării informațiilor doctorului.');
                    }
                    $upd_doc->bind_param('iis', $user_id, $specialty_id, $bio);
                }

                if (!$upd_doc->execute()) {
                    throw new Exception('Eroare la actualizare informații doctor: ' . $upd_doc->error);
                }
            } elseif ($user['user_type'] === 'doctor' && $user_type !== 'doctor') {
                $del_doc = $conn->prepare("DELETE FROM info_doctori WHERE user_id = ?");
                if (!$del_doc) {
                    throw new Exception('Eroare la pregătirea ștergerii informațiilor doctorului.');
                }
                $del_doc->bind_param('i', $user_id);
                $del_doc->execute();
            }

            $conn->commit();
            $message = '✅ Utilizatorul a fost actualizat cu succes!';

            // Reload user data
            $stmt2 = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt2->bind_param('i', $user_id);
            $stmt2->execute();
            $user = $stmt2->get_result()->fetch_assoc();

            if ($user['user_type'] === 'doctor') {
                $doc_stmt = $conn->prepare("SELECT specialty_id, bio FROM info_doctori WHERE user_id = ?");
                $doc_stmt->bind_param('i', $user_id);
                $doc_stmt->execute();
                $doctor_info = $doc_stmt->get_result()->fetch_assoc();
            } else {
                $doctor_info = null;
            }

        } catch (Exception $e) {
            $conn->rollback();
            $error = '❌ ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Get specialties
$specialties = [];
$specResult = $conn->query("SELECT id, name FROM specialties ORDER BY name");
if ($specResult) {
    $specialties = $specResult->fetch_all(MYSQLI_ASSOC);
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
                <h3>✏️ Editează: <?php echo htmlspecialchars($user['name']); ?></h3>
                <a href="users.php" class="btn btn-secondary btn-sm">← Înapoi</a>
            </div>

            <div class="admin-card-body">
                <form method="POST" action="edit-user.php?id=<?php echo (int)$user_id; ?>" id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">👤 Nume complet *</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['name']); ?>"
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
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                maxlength="200"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">🔐 Parolă nouă</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Lasă gol pentru a nu schimba"
                                minlength="6"
                            >
                            <div class="form-text">Completează doar dacă vrei să schimbi parola.</div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">🔐 Confirmă Parola</label>
                            <input
                                type="password"
                                id="password_confirm"
                                name="password_confirm"
                                class="form-control"
                                placeholder="Repetă noua parolă"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_type">👥 Tip utilizator *</label>
                            <?php if ($user['user_type'] === 'admin'): ?>
                                <input type="text" class="form-control" value="Administrator" disabled>
                                <input type="hidden" name="user_type" value="admin">
                                <div class="form-text">Rolul de administrator nu poate fi modificat.</div>
                            <?php else: ?>
                                <select id="user_type" name="user_type" class="form-control" required onchange="toggleSpecialty()">
                                    <option value="patient" <?php echo $user['user_type'] === 'patient' ? 'selected' : ''; ?>>Pacient</option>
                                    <option value="doctor" <?php echo $user['user_type'] === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="phone">📞 Telefon</label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                maxlength="30"
                            >
                        </div>
                    </div>

                    <div id="doctorFields" style="display: <?php echo $user['user_type'] === 'doctor' ? 'block' : 'none'; ?>;">
                        <hr class="separator">

                        <div class="form-group">
                            <label for="specialty_id">🏥 Specialitate *</label>
                            <select id="specialty_id" name="specialty_id" class="form-control">
                                <option value="">-- Selectează specialitate --</option>
                                <?php foreach ($specialties as $spec): ?>
                                    <option
                                        value="<?php echo (int)$spec['id']; ?>"
                                        <?php echo (int)($doctor_info['specialty_id'] ?? 0) === (int)$spec['id'] ? 'selected' : ''; ?>
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
                                placeholder="Descrierea profesională..."
                                maxlength="1000"
                            ><?php echo htmlspecialchars($doctor_info['bio'] ?? ''); ?></textarea>
                        </div>
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
</body>
</html>