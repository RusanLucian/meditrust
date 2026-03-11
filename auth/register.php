<?php
require_once '../bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'pacient';
    $phone = $_POST['phone'] ?? '';
    $specialty = $_POST['specialty'] ?? null;

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Te rog completează toate câmpurile obligatorii!";
    } elseif ($password !== $confirm_password) {
        $error = "Parolele nu se potrivesc!";
    } elseif (strlen($password) < 6) {
        $error = "Parola trebuie să aibă cel puțin 6 caractere!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email-ul este deja înregistrat!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_stmt = $conn->prepare("
                INSERT INTO users (name, email, password, user_type, phone, specialty) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insert_stmt->bind_param("ssssss", $name, $email, $hashed_password, $user_type, $phone, $specialty);

            if ($insert_stmt->execute()) {
                $success = "✅ Cont creat cu succes! Poți să te loghezi acum.";
            } else {
                $error = "❌ Eroare la crearea contului!";
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
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <br><a href="login.php" class="auth-inline-link">Du-te la login →</a>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">👤 Nume complet</label>
                    <input type="text" id="name" name="name" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="email">📧 Email</label>
                    <input type="email" id="email" name="email" placeholder="exemplu@email.com" autocomplete="off" required>
                </div>

                <div class="auth-form-row">
                    <div class="form-group">
                        <label for="password">🔐 Parola</label>
                        <input type="password" id="password" name="password" placeholder="Minim 6 caractere" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">🔐 Confirmă parola</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmă parola" required>
                    </div>
                </div>

                <div class="auth-form-row">
                    <div class="form-group">
                        <label for="user_type">👥 Tip cont</label>
                        <select id="user_type" name="user_type" onchange="toggleSpecialty()" required>
                            <option value="pacient">Pacient</option>
                            <option value="doctor">Doctor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">📱 Telefon</label>
                        <input type="tel" id="phone" name="phone" placeholder="+40 123 456 789">
                    </div>
                </div>

                <div class="form-group specialty-group" id="specialty-group">
                    <label for="specialty">🏥 Specialitate</label>
                    <select id="specialty" name="specialty">
                        <option value="">Selectează specialitate</option>
                        <option value="Cardiologie">Cardiologie</option>
                        <option value="Neurologie">Neurologie</option>
                        <option value="Oftalmologie">Oftalmologie</option>
                        <option value="Dermatologie">Dermatologie</option>
                        <option value="Pediatrie">Pediatrie</option>
                        <option value="Ortopedie">Ortopedie</option>
                    </select>
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
            
            if (userType === 'doctor') {
                specialtyGroup.classList.add('active');
            } else {
                specialtyGroup.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', toggleSpecialty);
    </script>
</body>
</html>