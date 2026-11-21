
<?php
require_once '../bootstrap.php';

requireRole('patient', '../auth/login.php');

$patient_id = (int) $_SESSION['user_id'];
$selected_doctor_id = (int) ($_GET['doctor_id'] ?? 0);

$message = '';
$error = '';

// Generare token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Obtine lista medicilor disponibili in aplicatie.
 */
function getDoctors(mysqli $conn): array
{
    $sql = "
        SELECT
            u.id,
            u.name,
            s.name AS specialty
        FROM users u
        LEFT JOIN info_doctori info
            ON u.id = info.user_id
        LEFT JOIN specialties s
            ON info.specialty_id = s.id
        WHERE u.user_type = 'doctor'
        ORDER BY u.name
    ";

    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Verifica daca data si ora sunt valide.
 *
 * Returneaza mesajul de eroare sau null.
 */
function validateAppointmentDateTime(
    string $date,
    string $time
): ?string {

    // Verificare format data
    $dateObject = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $date
    );

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        return "❌ Data selectată este invalidă!";
    }

    // Verificare format ora
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        return "❌ Formatul orei este invalid!";
    }

    [$hour, $minute] = array_map(
        'intval',
        explode(':', $time)
    );

    if ($hour > 23 || $minute > 59) {
        return "❌ Ora selectată este invalidă!";
    }

    // Program de lucru
    $totalMinutes = $hour * 60 + $minute;

    $startMinutes = 8 * 60;
    $endMinutes = 17 * 60 + 45;

    if (
        $totalMinutes < $startMinutes ||
        $totalMinutes > $endMinutes
    ) {
        return "❌ Ora programării trebuie să fie între 08:00 și 17:45!";
    }

    // Intervale de 15 minute
    if ($minute % 15 !== 0) {
        return "❌ Programările se pot face doar din 15 în 15 minute!";
    }

    // Verificare data si ora in trecut
    $appointmentDateTime = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i',
        $date . ' ' . $time
    );

    if (
        !$appointmentDateTime ||
        $appointmentDateTime <= new DateTimeImmutable()
    ) {
        return "❌ Nu poți rezerva o programare în trecut!";
    }

    return null;
}

/**
 * Verifica daca medicul este disponibil.
 *
 * Programarile anulate nu ocupa intervalul.
 */
function isDoctorAvailable(
    mysqli $conn,
    int $doctor_id,
    string $appointment_datetime
): bool {

    $stmt = $conn->prepare("
        SELECT id
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND status <> 'cancelled'
        LIMIT 1
    ");

    $stmt->bind_param(
        "is",
        $doctor_id,
        $appointment_datetime
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $available = $result->num_rows === 0;

    $stmt->close();

    return $available;
}

/**
 * Insereaza o programare in baza de date.
 *
 * Aceasta functie este apelata din tranzactia
 * care protejeaza rezervarea.
 */
function createAppointment(
    mysqli $conn,
    int $doctor_id,
    int $patient_id,
    string $appointment_datetime,
    string $notes
): bool {

    $stmt = $conn->prepare("
        INSERT INTO appointments
            (
                doctor_id,
                patient_id,
                appointment_date,
                notes,
                status
            )
        VALUES (?, ?, ?, ?, 'scheduled')
    ");

    $stmt->bind_param(
        "iiss",
        $doctor_id,
        $patient_id,
        $appointment_datetime,
        $notes
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

/**
 * Verifica medicul si rezerva intervalul.
 *
 * Blocheaza randul medicului in timpul tranzactiei,
 * pentru a preveni doua rezervari simultane.
 *
 * Returneaza null la succes sau un mesaj de eroare.
 */
function bookAppointment(
    mysqli $conn,
    int $doctor_id,
    int $patient_id,
    string $appointment_datetime,
    string $notes
): ?string {

    try {
        $conn->begin_transaction();

        // Verifica existenta medicului si blocheaza randul
        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE id = ?
              AND user_type = 'doctor'
            FOR UPDATE
        ");

        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $doctorExists = $result->num_rows === 1;

        $stmt->close();

        if (!$doctorExists) {
            $conn->rollback();
            return "❌ Medicul selectat nu există!";
        }

        // Verifica disponibilitatea
        if (!isDoctorAvailable(
            $conn,
            $doctor_id,
            $appointment_datetime
        )) {
            $conn->rollback();

            return "❌ Medicul are deja o programare la data și ora selectate. Alege alt interval!";
        }

        // Creeaza programarea
        $success = createAppointment(
            $conn,
            $doctor_id,
            $patient_id,
            $appointment_datetime,
            $notes
        );

        if (!$success) {
            $conn->rollback();
            return "❌ Programarea nu a putut fi salvată!";
        }

        $conn->commit();

        return null;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();

        error_log(
            "Eroare rezervare MediTrust: " . $e->getMessage()
        );

        return "❌ A apărut o eroare la rezervare. Încearcă din nou!";
    }
}

// Obtine lista medicilor
$doctors = getDoctors($conn);

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificare CSRF
    if (!hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token'] ?? ''
    )) {
        $error = "❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou!";

    } else {

        // Preluare date formular
        $doctor_id = (int) ($_POST['doctor_id'] ?? 0);
        $appointment_date = trim($_POST['appointment_date'] ?? '');
        $appointment_time = trim($_POST['appointment_time'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Verificare campuri obligatorii
        if (
            $doctor_id <= 0 ||
            $appointment_date === '' ||
            $appointment_time === ''
        ) {
            $error = "❌ Completează toate câmpurile obligatorii!";

        } else {

            // Validare data si ora
            $error = validateAppointmentDateTime(
                $appointment_date,
                $appointment_time
            ) ?? '';

            if ($error === '') {

                $full_datetime = $appointment_date
                    . ' '
                    . $appointment_time
                    . ':00';

                // Verificare si salvare
                $error = bookAppointment(
                    $conn,
                    $doctor_id,
                    $patient_id,
                    $full_datetime,
                    $notes
                ) ?? '';

                if ($error === '') {
                    $message = "✅ Programare rezervată cu succes!";

                    $_POST = [];
                    $selected_doctor_id = 0;

                    $_SESSION['csrf_token'] = bin2hex(
                        random_bytes(32)
                    );
                }
            }
        }
    }
}

// Medic selectat in formular
$current_doctor_id = (int) (
    $_POST['doctor_id'] ?? $selected_doctor_id
);
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
            <p>Alege medicul, data și ora dorită pentru appointment</p>
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
                        min="08:00"
                        max="17:45"
                        step="900"
                        value="<?php echo htmlspecialchars($_POST['appointment_time'] ?? ''); ?>"
                    >
                    <small>Program disponibil: 08:00 - 17:45, din 15 în 15 minute.</small>
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