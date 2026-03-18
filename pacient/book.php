<?php
require_once '../bootstrap.php';

requireRole('patient', '../auth/login.php');

$patient_id = $_SESSION['user_id'];
$selected_doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$message = '';
$error = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Preluare doctori cu specialități
$doctors_stmt = $conn->query("
    SELECT 
        u.id, 
        u.name, 
        s.name AS specialty
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE u.user_type = 'doctor'
    ORDER BY u.name
");
$doctors = $doctors_stmt ? $doctors_stmt->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $doctor_id = (int)($_POST['doctor_id'] ?? 0);
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if ($doctor_id <= 0 || empty($appointment_date) || empty($appointment_time)) {
            $error = "❌ Te rog completează toate câmpurile!";
        } else {
            $full_datetime = $appointment_date . ' ' . $appointment_time;

            if (strtotime($full_datetime) < time()) {
                $error = "❌ Nu poți rezerva un appointment în trecut!";
            } else {
                $insert_stmt = $conn->prepare("
                    INSERT INTO appointments (doctor_id, patient_id, appointment_date, notes, status)
                    VALUES (?, ?, ?, ?, 'scheduled')
                ");

                if (!$insert_stmt) {
                    $error = "❌ Eroare la pregătirea rezervării!";
                } else {
                    $insert_stmt->bind_param("iiss", $doctor_id, $patient_id, $full_datetime, $notes);

                    if ($insert_stmt->execute()) {
                        $message = "✅ Appointment rezervat cu succes!";
                        $_POST = [];
                        $selected_doctor_id = 0;
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = "❌ Eroare la rezervare: " . $insert_stmt->error;
                    }
                }
            }
        }
    }
}

$current_doctor_id = (int)($_POST['doctor_id'] ?? $selected_doctor_id);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezervă Appointment - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $headerUseLogo = false;
    $headerTitle = '📋 MediTrust';
    $headerLinks = [
        ['href' => '../medici/lista.php', 'label' => 'Medici'],
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => 'my-appointments.php', 'label' => 'Programările Mele'],
        ['href' => '../auth/logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../pacient/my-appointments.php" class="back-btn">← Înapoi</a>

        <div class="page-title">
            <h2>📅 Rezervă Appointment</h2>
            <p>Alege medicul și data dorită pentru appointment</p>
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

        <div class="form-section">
            <h3>Completează formularul</h3>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label for="doctor_id">Alege Medic <span class="required">*</span></label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">-- Selectează Medic --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo (int)$doctor['id']; ?>"
                                <?php echo $current_doctor_id === (int)$doctor['id'] ? 'selected' : ''; ?>>
                                👨‍⚕️ <?php echo htmlspecialchars($doctor['name']); ?>
                                <?php if (!empty($doctor['specialty'])): ?>
                                    - <?php echo htmlspecialchars($doctor['specialty']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Data Appointment <span class="required">*</span></label>
                    <input
                        type="date"
                        name="appointment_date"
                        id="appointment_date"
                        required
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($_POST['appointment_date'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="appointment_time">Ora Appointment <span class="required">*</span></label>
                    <input
                        type="time"
                        name="appointment_time"
                        id="appointment_time"
                        required
                        value="<?php echo htmlspecialchars($_POST['appointment_time'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="notes">Note (Opțional)</label>
                    <textarea
                        name="notes"
                        id="notes"
                        placeholder="Adaugă orice informație suplimentară..."
                    ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    ✅ Rezervă Appointment
                </button>
            </form>
        </div>
    </div>
</body>
</html>