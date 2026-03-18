<?php
require_once '../bootstrap.php';

requireLogin('login.php');

// Blochează adminul din dashboard-ul normal
if (($_SESSION['user_type'] ?? '') === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_type = $_SESSION['user_type'];

$is_doctor = ($user_type === 'doctor');
$is_patient = ($user_type === 'patient');

// Fetch user data
$user = getUserById($conn, $user_id);

// Fetch doctor info if doctor
$doctor_info = null;
if ($is_doctor) {
    $doc_stmt = $conn->prepare("
        SELECT s.name AS specialty, info.bio
        FROM info_doctori info
        LEFT JOIN specialties s ON info.specialty_id = s.id
        WHERE info.user_id = ?
    ");
    $doc_stmt->bind_param('i', $user_id);
    $doc_stmt->execute();
    $doctor_info = $doc_stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $headerGreeting = '👋 Bine ai venit, ' . htmlspecialchars($user_name) . '!';
    $headerLinks = [
        ['href' => 'logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <div class="welcome-section">
            <h2>📋 Bun venit în MediTrust!</h2>
            <p>Selectează o acțiune din meniu pentru a continua.</p>

            <div class="user-info">
                <div class="info-item">
                    <strong>📧 Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <strong>👥 Tip cont:</strong>
                    <span><?php echo $is_doctor ? '👨‍⚕️ Doctor' : '🧑‍⚕️ Pacient'; ?></span>
                </div>
                <?php if ($is_doctor && $doctor_info): ?>
                    <div class="info-item">
                        <strong>🏥 Specialitate:</strong>
                        <span><?php echo htmlspecialchars($doctor_info['specialty'] ?? 'N/A'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-grid">
            <?php if ($is_patient): ?>

                <div class="card">
                    <div class="card-icon">🔍</div>
                    <h3>Caută Medici</h3>
                    <p>Găsește medicul potrivit pentru tine din baza noastră de date.</p>
                    <a href="../medici/lista.php" class="card-btn">Caută Medici</a>
                </div>

                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Programările Mele</h3>
                    <p>Vizualizează și gestionează programările tale cu medicii.</p>
                    <a href="../pacient/my-appointments.php" class="card-btn">Vezi Programări</a>
                </div>

                <div class="card">
                    <div class="card-icon">✍️</div>
                    <h3>Lasă o Recenzie</h3>
                    <p>Alege un medic și împărtășește experiența ta.</p>
                    <a href="../medici/lista.php" class="card-btn">Lasă Recenzie</a>
                </div>

                <div class="card">
                    <div class="card-icon">👤</div>
                    <h3>Profilul Meu</h3>
                    <p>Editează informațiile tale personale.</p>
                    <a href="profile.php" class="card-btn">Editează Profil</a>
                </div>

            <?php elseif ($is_doctor): ?>

                <div class="card">
                    <div class="card-icon">👨‍⚕️</div>
                    <h3>Profilul Meu</h3>
                    <p>Editează detaliile și informațiile profesionale.</p>
                    <a href="profile.php" class="card-btn">Editează Profil</a>
                </div>

                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Programările Mele</h3>
                    <p>Gestionează programările tale cu pacienții.</p>
                    <a href="../medici/appointments.php" class="card-btn">Vezi Programări</a>
                </div>

                <div class="card">
                    <div class="card-icon">⭐</div>
                    <h3>Recenziile Pacienților</h3>
                    <p>Citește feedback-ul și ratingurile primite de la pacienți.</p>
                    <a href="../medici/detalii.php?id=<?php echo (int)$user_id; ?>" class="card-btn">Vezi Recenzii</a>
                </div>

            <?php endif; ?>

            <div class="card">
                <div class="card-icon">⚙️</div>
                <h3>Setări</h3>
                <p>Schimbă parola și alte preferințe de cont.</p>
                <a href="profile.php" class="card-btn">Setări</a>
            </div>
        </div>
    </div>
</body>
</html>