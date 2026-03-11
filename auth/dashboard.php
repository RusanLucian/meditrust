<?php
require_once '../bootstrap.php';

requireLogin('login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_type = $_SESSION['user_type'];
$is_doctor = ($user_type === 'doctor');
$is_patient = in_array($user_type, ['patient', 'pacient'], true);

// Fetch user data
$user = getUserById($conn, $user_id);
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
    $headerGreeting = '👋 Bine ai venit, ' . $user_name . '!';
    $headerLinks = [
        ['href' => 'logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <!-- Welcome Section -->
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
                <?php if ($is_doctor): ?>
                <div class="info-item">
                    <strong>🏥 Specialitate:</strong>
                    <span><?php echo htmlspecialchars($user['specialty'] ?? 'N/A'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <?php if ($is_patient): ?>
                
                <!-- Card: Caută Medici -->
                <div class="card">
                    <div class="card-icon">🔍</div>
                    <h3>Caută Medici</h3>
                    <p>Găsește medicul potrivit pentru tine din baza noastră de date</p>
                    <a href="../medici/lista.php" class="card-btn">Cauta Medici</a>
                </div>

                <!-- Card: Appointments -->
                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Appointment-urile Mele</h3>
                    <p>Vizualizează și gestionează programările tale cu medicii</p>
                    <a href="../pacient/my-appointments.php" class="card-btn">Vezi Appointments</a>
                </div>

                <!-- Card: Recenzii -->
                <div class="card">
                    <div class="card-icon">✍️</div>
                    <h3>Lasă o Recenzie</h3>
                    <p>Partajează experiența ta și ajută alți pacienți</p>
                    <a href="../medici/lista.php" class="card-btn">Lasă Recenzie</a>
                </div>

                <!-- Card: Profil -->
                <div class="card">
                    <div class="card-icon">👤</div>
                    <h3>Profilul Meu</h3>
                    <p>Editează informațiile tale personale</p>
                    <a href="profile.php" class="card-btn">Editează Profil</a>
                </div>

            <?php else: ?>
                
                <!-- Card: Profilul Meu (Doctor) -->
                <div class="card">
                    <div class="card-icon">👨‍⚕️</div>
                    <h3>Profilul Meu</h3>
                    <p>Editează detaliile și informațiile profesionale</p>
                    <a href="profile.php" class="card-btn">Editează Profil</a>
                </div>

                <!-- Card: Appointments (Doctor) -->
                <div class="card">
                    <div class="card-icon">📅</div>
                    <h3>Appointment-urile Mele</h3>
                    <p>Gestionează programările tale cu pacienții</p>
                    <a href="../medici/appointments.php" class="card-btn">Vezi Appointments</a>
                </div>

                <!-- Card: Recenzii (Doctor) -->
                <div class="card">
                    <div class="card-icon">⭐</div>
                    <h3>Recenziile Pacienților</h3>
                    <p>Citește feedback-ul și ratingurile primite de la pacienți</p>
                    <a href="../medici/detalii.php?id=<?php echo $user_id; ?>" class="card-btn">Vezi Recenzii</a>
                </div>

            <?php endif; ?>

            <!-- Card: Setări (Pentru toți) -->
            <div class="card">
                <div class="card-icon">⚙️</div>
                <h3>Setări</h3>
                <p>Schimbă parola și alte preferințe de cont</p>
                <a href="profile.php" class="card-btn">Setări</a>
            </div>

        </div>
    </div>
</body>
</html>