<?php
require_once '../bootstrap.php';

requireRole('patient', '../auth/login.php');

$patient_id = $_SESSION['user_id'];
$selected_doctor_id = $_GET['doctor_id'] ?? '';
$message = '';
$error = '';

// Preluare doctori
$doctors_stmt = $conn->query("SELECT id, name, specialty FROM users WHERE user_type = 'doctor' ORDER BY name");
$doctors = $doctors_stmt->fetch_all(MYSQLI_ASSOC);

// Submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        $error = "❌ Te rog completează toate câmpurile!";
    } else {
        $full_datetime = $appointment_date . ' ' . $appointment_time;

        $insert_stmt = $conn->prepare("
            INSERT INTO appointments (doctor_id, patient_id, appointment_date, notes, status)
            VALUES (?, ?, ?, ?, 'scheduled')
        ");
        $insert_stmt->bind_param("iiss", $doctor_id, $patient_id, $full_datetime, $notes);

        if ($insert_stmt->execute()) {
            $message = "✅ Appointment rezervat cu succes!";
            $_POST = []; // Resetează form
        } else {
            $error = "❌ Eroare la rezervare: " . $insert_stmt->error;
        }
    }
}
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
        ['href' => 'my-appointments.php', 'label' => 'Appointment-urile Mele'],
        ['href' => '../auth/logout.php', 'label' => 'Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../auth/dashboard.php" class="back-btn">← Înapoi</a>

        <div class="page-title">
            <h2>📅 Rezervă Appointment</h2>
            <p>Alege medicul și data dorită pentru appointment</p>
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

        <div class="form-section">
            <h3>Completează formularul</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="doctor_id">Alege Medic <span class="required">*</span></label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">-- Selectează Medic --</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" <?php echo ($selected_doctor_id == $doctor['id']) ? 'selected' : ''; ?>>
                                👨‍⚕️ <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialty']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Data Appointment <span class="required">*</span></label>
                    <input type="date" name="appointment_date" id="appointment_date" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="appointment_time">Ora Appointment <span class="required">*</span></label>
                    <input type="time" name="appointment_time" id="appointment_time" required>
                </div>

                <div class="form-group">
                    <label for="notes">Note (Opțional)</label>
                    <textarea name="notes" id="notes" placeholder="Adaugă orice informație suplimentară..."></textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%;">
                    ✅ Rezervă Appointment
                </button>
            </form>
        </div>
    </div>
</body>
</html>