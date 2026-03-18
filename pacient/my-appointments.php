<?php
require_once '../bootstrap.php';

// Doar pacienți
requireRole('patient', '../auth/login.php');

$patient_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Anulare programare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $appointment_id = (int)($_POST['appointment_id'] ?? 0);

        if ($appointment_id > 0) {
            $cancel_stmt = $conn->prepare("
                UPDATE appointments
                SET status = 'cancelled'
                WHERE id = ? AND patient_id = ?
            ");

            if (!$cancel_stmt) {
                $error = "❌ Eroare la pregătirea anulării!";
            } else {
                $cancel_stmt->bind_param("ii", $appointment_id, $patient_id);

                if ($cancel_stmt->execute()) {
                    $message = "✅ Programarea a fost anulată cu succes!";
                } else {
                    $error = "❌ Eroare la anulare: " . $cancel_stmt->error;
                }
            }
        } else {
            $error = "❌ ID invalid pentru programare.";
        }
    }
}

// Preluare programări
$appointments_stmt = $conn->prepare("
    SELECT
        a.*,
        u.name AS doctor_name,
        u.phone AS doctor_phone,
        s.name AS specialty,
        info.bio
    FROM appointments a
    JOIN users u ON a.doctor_id = u.id
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC
");
$appointments_stmt->bind_param("i", $patient_id);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();

// Separare programări viitoare și trecute
$upcoming_appointments = [];
$past_appointments = [];
$current_time = time();

while ($app = $appointments_result->fetch_assoc()) {
    $appointment_timestamp = strtotime($app['appointment_date']);
    $status = $app['status'] ?? '';

    if ($appointment_timestamp > $current_time && $status !== 'cancelled') {
        $upcoming_appointments[] = $app;
    } else {
        $past_appointments[] = $app;
    }
}

$total_appointments = count($upcoming_appointments) + count($past_appointments);
$cancelled_count = 0;

foreach (array_merge($upcoming_appointments, $past_appointments) as $appointment_item) {
    if (($appointment_item['status'] ?? '') === 'cancelled') {
        $cancelled_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programările Mele - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="my-appointments-page">
    <?php
    $headerLinks = [
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => 'book.php', 'label' => '+ Rezervă Programare'],
        ['href' => '../auth/logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../auth/dashboard.php" class="back-btn">← Înapoi la Dashboard</a>

        <div class="appointments-hero">
            <div>
                <h2>Programările Mele</h2>
                <p>Vezi rapid programările viitoare, istoricul și statusul lor.</p>
            </div>
            <a href="book.php" class="book-new-btn">+ Rezervă Programare</a>
        </div>

        <div class="appointments-stats">
            <div class="stat-card">
                <span class="stat-label">Viitoare</span>
                <strong><?php echo count($upcoming_appointments); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Trecute</span>
                <strong><?php echo count($past_appointments); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Anulate</span>
                <strong><?php echo $cancelled_count; ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total</span>
                <strong><?php echo $total_appointments; ?></strong>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="appointments-section">
            <div class="section-title">
                <h3>📅 Programări Viitoare</h3>
            </div>

            <?php if (!empty($upcoming_appointments)): ?>
                <div class="appointments-list">
                    <?php foreach ($upcoming_appointments as $app): ?>
                        <div class="appointment-card upcoming-card">
                            <div class="appointment-main">
                                <h4>👨‍⚕️ <?php echo htmlspecialchars($app['doctor_name']); ?></h4>
                                <p><strong>Specialitate:</strong> <?php echo htmlspecialchars($app['specialty'] ?? 'N/A'); ?></p>
                                <p><strong>Telefon:</strong> <?php echo htmlspecialchars($app['doctor_phone'] ?? 'N/A'); ?></p>
                                <?php if (!empty($app['bio'])): ?>
                                    <p class="doctor-bio">
                                        <?php echo htmlspecialchars(mb_strimwidth($app['bio'], 0, 120, '...')); ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="appointment-meta">
                                <span class="meta-pill">📅 <?php echo date('d.m.Y', strtotime($app['appointment_date'])); ?></span>
                                <span class="meta-pill">🕐 <?php echo date('H:i', strtotime($app['appointment_date'])); ?></span>
                                <span class="status-pill status-scheduled">Programat</span>
                            </div>

                            <div class="appointment-actions">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="appointment_id" value="<?php echo (int)$app['id']; ?>">
                                    <button
                                        type="submit"
                                        name="cancel_appointment"
                                        class="btn btn-cancel"
                                        onclick="return confirm('Ești sigur că vrei să anulezi această programare?')"
                                    >
                                        ❌ Anulează Programarea
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <p>Nu ai programări viitoare.</p>
                    <a href="book.php" class="book-new-btn">+ Rezervă Programare Acum</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="appointments-section">
            <div class="section-title">
                <h3>📜 Programări Trecute</h3>
            </div>

            <?php if (!empty($past_appointments)): ?>
                <div class="appointments-list">
                    <?php foreach ($past_appointments as $app): ?>
                        <div class="appointment-card past-card <?php echo ($app['status'] ?? '') === 'cancelled' ? 'cancelled' : ''; ?>">
                            <div class="appointment-main">
                                <h4>👨‍⚕️ <?php echo htmlspecialchars($app['doctor_name']); ?></h4>
                                <p><strong>Specialitate:</strong> <?php echo htmlspecialchars($app['specialty'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="appointment-meta">
                                <span class="meta-pill">📅 <?php echo date('d.m.Y', strtotime($app['appointment_date'])); ?></span>
                                <span class="meta-pill">🕐 <?php echo date('H:i', strtotime($app['appointment_date'])); ?></span>
                                <span class="status-pill <?php echo ($app['status'] ?? '') === 'cancelled' ? 'status-cancelled' : 'status-completed'; ?>">
                                    <?php echo ($app['status'] ?? '') === 'cancelled' ? 'Anulat' : 'Completat'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <p>Nu ai programări trecute.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>