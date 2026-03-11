<?php
require_once '../bootstrap.php';

// Doar doctori
requireRole('doctor', '../auth/login.php');

$doctor_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Preluare date doctor
$user = getUserById($conn, $doctor_id);

// Actualizare status appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!empty($appointment_id) && !empty($status)) {
        $update_stmt = $conn->prepare("
            UPDATE appointments 
            SET status = ? 
            WHERE id = ? AND doctor_id = ?
        ");
        $update_stmt->bind_param("sii", $status, $appointment_id, $doctor_id);

        if ($update_stmt->execute()) {
            $message = "✅ Status actualizat cu succes!";
        } else {
            $error = "❌ Eroare la actualizare!";
        }
    }
}

// Preluare appointment-uri
$appointments_stmt = $conn->prepare("
    SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as patient_phone
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date DESC
");
$appointments_stmt->bind_param("i", $doctor_id);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();

// Separare appointment-uri
$upcoming_appointments = [];
$past_appointments = [];
$current_time = time();

while ($app = $appointments_result->fetch_assoc()) {
    if (strtotime($app['appointment_date']) > $current_time && $app['status'] !== 'cancelled') {
        $upcoming_appointments[] = $app;
    } else {
        $past_appointments[] = $app;
    }
}

$total_appointments = count($upcoming_appointments) + count($past_appointments);
$cancelled_count = 0;
foreach ($past_appointments as $past_app) {
    if (($past_app['status'] ?? '') === 'cancelled') {
        $cancelled_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment-urile Mele - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="doctor-appointments-page">
    <?php
    $headerLinks = [
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => '../auth/logout.php', 'label' => 'Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../auth/dashboard.php" class="back-btn">← Înapoi la Dashboard</a>

        <div class="doctor-appointments-hero">
            <div>
                <h2>Appointment-urile Mele</h2>
                <p>Gestionează programările cu pacienții și actualizează statusul în timp real.</p>
            </div>
        </div>

        <div class="doctor-appointments-stats">
            <div class="doctor-stat-card">
                <span class="stat-label">Viitoare</span>
                <strong><?php echo count($upcoming_appointments); ?></strong>
            </div>
            <div class="doctor-stat-card">
                <span class="stat-label">Trecute</span>
                <strong><?php echo count($past_appointments); ?></strong>
            </div>
            <div class="doctor-stat-card">
                <span class="stat-label">Anulate</span>
                <strong><?php echo $cancelled_count; ?></strong>
            </div>
            <div class="doctor-stat-card">
                <span class="stat-label">Total</span>
                <strong><?php echo $total_appointments; ?></strong>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- UPCOMING APPOINTMENTS -->
        <div class="appointments-section">
            <h3>📅 Appointment-uri Viitoare</h3>
            <?php if (!empty($upcoming_appointments)): ?>
                <div class="appointments-list">
                <?php foreach ($upcoming_appointments as $app): ?>
                    <?php
                        $status = $app['status'] ?? 'scheduled';
                        $status_class = $status === 'completed' ? 'status-completed' : ($status === 'cancelled' ? 'status-cancelled' : 'status-scheduled');
                        $status_label = $status === 'completed' ? 'Completat' : ($status === 'cancelled' ? 'Anulat' : 'Programat');
                    ?>
                    <div class="appointment-card doctor-appointment-card">
                        <div class="doctor-appointment-main">
                            <h4>👤 <?php echo htmlspecialchars($app['patient_name']); ?></h4>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['patient_email']); ?></p>
                            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($app['patient_phone'] ?: 'N/A'); ?></p>
                            <p><strong>Note:</strong> <?php echo htmlspecialchars($app['notes'] ?: 'Nu sunt note'); ?></p>
                        </div>
                        <div class="appointment-meta">
                            <span class="meta-pill">📅 <?php echo date('d.m.Y', strtotime($app['appointment_date'])); ?></span>
                            <span class="meta-pill">🕐 <?php echo date('H:i', strtotime($app['appointment_date'])); ?></span>
                            <span class="status-pill <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                        </div>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                            <select name="status" class="status-select">
                                <option value="scheduled" <?php echo $status === 'scheduled' ? 'selected' : ''; ?>>Programat</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completat</option>
                                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Anulat</option>
                            </select>
                            <button type="submit" name="update_status" class="btn status-update-btn">Update status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <p>📭 Nu ai appointment-uri viitoare</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAST APPOINTMENTS -->
        <div class="appointments-section">
            <h3>📜 Appointment-uri Trecute</h3>
            <?php if (!empty($past_appointments)): ?>
                <div class="appointments-list">
                <?php foreach ($past_appointments as $app): ?>
                    <?php
                        $status = $app['status'] ?? 'completed';
                        $status_class = $status === 'completed' ? 'status-completed' : ($status === 'cancelled' ? 'status-cancelled' : 'status-scheduled');
                        $status_label = $status === 'completed' ? 'Completat' : ($status === 'cancelled' ? 'Anulat' : 'Programat');
                    ?>
                    <div class="appointment-card doctor-appointment-card past-card <?php echo $status === 'cancelled' ? 'cancelled' : ''; ?>">
                        <div class="doctor-appointment-main">
                            <h4>👤 <?php echo htmlspecialchars($app['patient_name']); ?></h4>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['patient_email']); ?></p>
                            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($app['patient_phone'] ?: 'N/A'); ?></p>
                        </div>
                        <div class="appointment-meta">
                            <span class="meta-pill">📅 <?php echo date('d.m.Y', strtotime($app['appointment_date'])); ?></span>
                            <span class="meta-pill">🕐 <?php echo date('H:i', strtotime($app['appointment_date'])); ?></span>
                            <span class="status-pill <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📭</div>
                    <p>📭 Nu ai appointment-uri trecute</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>