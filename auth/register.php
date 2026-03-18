<?php
require_once '../bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get specialties safely
$specialties = [];
$specResult = $conn->query("SELECT id, name FROM specialties ORDER BY name");
if ($specResult) {
    $specialties = $specResult->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_type = $_POST['user_type'] ?? 'patient';
        $phone = trim($_POST['phone'] ?? '');
        $specialty_id = (int)($_POST['specialty_id'] ?? 0);
        $bio = trim($_POST['bio'] ?? '');

        $allowedTypes = ['patient', 'doctor'];
        $user_type = in_array($user_type, $allowedTypes, true) ? $user_type : 'patient';

        if (empty($name) || empty($email) || empty($password)) {
            $error = "Te rog completează toate câmpurile obligatorii!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email-ul nu este valid!";
        } elseif ($password !== $confirm_password) {
            $error = "Parolele nu se potrivesc!";
        } elseif (strlen($password) < 6) {
            $error = "Parola trebuie să aibă cel puțin 6 caractere!";
        } elseif ($user_type === 'doctor' && $specialty_id <= 0) {
            $error = "Pentru doctori, specialitatea este obligatorie!";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if (!$stmt) {
                $error = "❌ Eroare la verificarea emailului!";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $error = "Email-ul este deja înregistrat!";
                } else {
                    $conn->begin_transaction();

                    try {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        $insert_stmt = $conn->prepare(
                            "INSERT INTO users (name, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?)"
                        );

                        if (!$insert_stmt) {
                            throw new Exception('Eroare la pregătirea creării contului!');
                        }

                        $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $user_type, $phone);

                        if (!$insert_stmt->execute()) {
                            throw new Exception('Eroare la crearea contului: ' . $insert_stmt->error);
                        }

                        $user_id = $conn->insert_id;

                        if ($user_type === 'doctor') {
                            $doc_insert = $conn->prepare(
                                "INSERT INTO info_doctori (user_id, specialty_id, bio) VALUES (?, ?, ?)"
                            );

                            if (!$doc_insert) {
                                throw new Exception('Eroare la pregătirea profilului doctorului!');
                            }

                            $doc_insert->bind_param("iis", $user_id, $specialty_id, $bio);

                            if (!$doc_insert->execute()) {
                                throw new Exception('Eroare la crearea profilului doctor: ' . $doc_insert->error);
                            }
                        }

                        $conn->commit();
                        $success = "✅ Cont creat cu succes! Poți să te loghezi acum.";
                        $_POST = [];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } catch (Exception $e) {
                        $conn->rollback();
                        $error = "❌ " . htmlspecialchars($e->getMessage());
                    }
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
    <title>Register - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <main class="container auth-shell auth-shell-register">
        <section class="auth-info">
            <img src="../img/meditrust-logo.png" alt="MediTrust Logo" class="auth-logo">
            <h1>Creează cont MediTrust</h1>
            <p>Înregistrează-te ca pacient sau doctor și gestionează online consultațiile, recenziile și istoricul activității.</p>
            <a href="../index.php" class="auth-back-link">← Înapoi la pagina principală</a>
        </section>

        <section class="auth-card">
            <div class="auth-card-header">
                <h2>📝 Înregistrare</h2>
                <p>Completează datele de mai jos pentru a crea contul.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <br><a href="login.php" class="auth-inline-link">Du-te la login →</a>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="name">👤 Nume complet</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="John Doe"
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required
                    >
                </div>

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

                <div class="auth-form-row">
                    <div class="form-group">
                        <label for="password">🔐 Parola</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minim 6 caractere"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">🔐 Confirmă parola</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirmă parola"
                            required
                        >
                    </div>
                </div>

                <div class="auth-form-row">
                    <div class="form-group">
                        <label for="user_type">👥 Tip cont</label>
                        <select id="user_type" name="user_type" onchange="toggleSpecialty()" required>
                            <option value="patient" <?php echo ($_POST['user_type'] ?? 'patient') === 'patient' ? 'selected' : ''; ?>>Pacient</option>
                            <option value="doctor" <?php echo ($_POST['user_type'] ?? '') === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">📱 Telefon</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="+40 123 456 789"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >
                    </div>
                </div>

                <div class="form-group specialty-group" id="specialty-group" style="display:none;">
                    <label for="specialty_id">🏥 Specialitate *</label>
                    <select id="specialty_id" name="specialty_id">
                        <option value="">-- Selectează specialitate --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo (int)$spec['id']; ?>" <?php echo (string)($_POST['specialty_id'] ?? '') === (string)$spec['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group doctor-bio" id="doctor-bio" style="display:none;">
                    <label for="bio">ℹ️ Biografie</label>
                    <textarea id="bio" name="bio" placeholder="Descrierea profesională..." maxlength="1000"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="auth-submit">Creează cont</button>
            </form>

            <div class="auth-footer">
                Ai deja cont? <a href="login.php">Conectează-te</a>
            </div>
        </section>
    </main>

    <script>
        function toggleSpecialty() {
            const userType = document.getElementById('user_type').value;
            const specialtyGroup = document.getElementById('specialty-group');
            const bioGroup = document.getElementById('doctor-bio');
            const specialtySelect = document.getElementById('specialty_id');

            if (userType === 'doctor') {
                specialtyGroup.style.display = 'block';
                bioGroup.style.display = 'block';
                specialtySelect.required = true;
            } else {
                specialtyGroup.style.display = 'none';
                bioGroup.style.display = 'none';
                specialtySelect.required = false;
                specialtySelect.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', toggleSpecialty);
    </script>
</body>
</html>