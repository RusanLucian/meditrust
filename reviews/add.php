
<?php
require_once '../bootstrap.php';

requireRole('patient', '../auth/login.php');

$patient_id = (int) $_SESSION['user_id'];
$doctor_id = (int) ($_GET['doctor_id'] ?? 0);

$message = '';
$error = '';
$already_reviewed = false;

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Obtine informatiile medicului.
 */
function getDoctor(mysqli $conn, int $doctor_id): ?array
{
    $stmt = $conn->prepare("
        SELECT
            u.name,
            s.name AS specialty
        FROM users u
        LEFT JOIN info_doctori info
            ON u.id = info.user_id
        LEFT JOIN specialties s
            ON info.specialty_id = s.id
        WHERE u.id = ?
          AND u.user_type = 'doctor'
    ");

    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();

    $doctor = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $doctor ?: null;
}

/**
 * Verifica daca pacientul are cel putin
 * o consultatie finalizata la medic.
 */
function hasCompletedAppointment(
    mysqli $conn,
    int $doctor_id,
    int $patient_id
): bool {

    $stmt = $conn->prepare("
        SELECT id
        FROM appointments
        WHERE doctor_id = ?
          AND patient_id = ?
          AND status = 'completed'
          AND appointment_date <= NOW()
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $doctor_id,
        $patient_id
    );

    $stmt->execute();

    $exists = $stmt->get_result()->num_rows > 0;

    $stmt->close();

    return $exists;
}

/**
 * Verifica daca pacientul a publicat
 * deja o recenzie pentru medic.
 */
function hasReviewedDoctor(
    mysqli $conn,
    int $doctor_id,
    int $patient_id
): bool {

    $stmt = $conn->prepare("
        SELECT id
        FROM reviews
        WHERE doctor_id = ?
          AND patient_id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $doctor_id,
        $patient_id
    );

    $stmt->execute();

    $exists = $stmt->get_result()->num_rows > 0;

    $stmt->close();

    return $exists;
}

/**
 * Valideaza notele si comentariul.
 *
 * Returneaza null daca datele sunt valide.
 */
function validateReview(
    array $ratings,
    string $comment
): ?string {

    foreach ($ratings as $value) {
        if ($value < 1 || $value > 5) {
            return "❌ Toate criteriile trebuie evaluate cu note între 1 și 5!";
        }
    }

    if ($comment === '') {
        return "❌ Te rog completează comentariul!";
    }

    if (mb_strlen($comment) > 2000) {
        return "❌ Comentariul nu poate depăși 2000 de caractere!";
    }

    return null;
}

/**
 * Calculeaza media celor cinci criterii.
 */
function calculateRating(array $ratings): int
{
    return (int) round(
        array_sum($ratings) / count($ratings)
    );
}

/**
 * Salveaza recenzia in baza de date.
 */
function createReview(
    mysqli $conn,
    int $doctor_id,
    int $patient_id,
    int $rating,
    string $comment,
    array $ratings
): bool {

    $stmt = $conn->prepare("
        INSERT INTO reviews (
            doctor_id,
            patient_id,
            rating,
            comment,
            created_at,
            communication,
            professionalism,
            punctuality,
            empathy,
            recommendation
        )
        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
    ");

    $communication = $ratings['communication'];
    $professionalism = $ratings['professionalism'];
    $punctuality = $ratings['punctuality'];
    $empathy = $ratings['empathy'];
    $recommendation = $ratings['recommendation'];

    $stmt->bind_param(
        "iiisiiiii",
        $doctor_id,
        $patient_id,
        $rating,
        $comment,
        $communication,
        $professionalism,
        $punctuality,
        $empathy,
        $recommendation
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}

/**
 * Valideaza si publica recenzia.
 *
 * Returneaza null la succes
 * sau un mesaj de eroare.
 */
function submitReview(
    mysqli $conn,
    int $doctor_id,
    int $patient_id,
    array $ratings,
    string $comment
): ?string {

    try {
        $conn->begin_transaction();

        // Blocam randul pacientului pentru a serializa
        // cererile de recenzie ale aceluiasi pacient.
        $lock_stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE id = ?
              AND user_type = 'patient'
            FOR UPDATE
        ");

        $lock_stmt->bind_param("i", $patient_id);
        $lock_stmt->execute();

        $patientExists =
            $lock_stmt->get_result()->num_rows === 1;

        $lock_stmt->close();

        if (!$patientExists) {
            $conn->rollback();
            return "❌ Contul pacientului nu este valid!";
        }

        // Verificam consultatia finalizata
        if (!hasCompletedAppointment(
            $conn,
            $doctor_id,
            $patient_id
        )) {
            $conn->rollback();

            return "❌ Poți evalua acest medic doar după o consultație finalizată!";
        }

        // Verificam recenziile existente
        if (hasReviewedDoctor(
            $conn,
            $doctor_id,
            $patient_id
        )) {
            $conn->rollback();

            return "❌ Ai deja un review pentru acest medic!";
        }

        // Calculam nota finala
        $rating = calculateRating($ratings);

        // Salvam recenzia
        $success = createReview(
            $conn,
            $doctor_id,
            $patient_id,
            $rating,
            $comment,
            $ratings
        );

        if (!$success) {
            $conn->rollback();

            return "❌ Recenzia nu a putut fi salvată!";
        }

        $conn->commit();

        return null;

    } catch (mysqli_sql_exception $e) {
        $conn->rollback();

        error_log(
            "Eroare review MediTrust: " . $e->getMessage()
        );

        return "❌ A apărut o eroare la salvarea recenziei!";
    }
}

/**
 * Genereaza campurile HTML pentru evaluare.
 *
 * Functia este pastrata pentru compatibilitate
 * cu HTML-ul existent.
 */
function ratingOptions(string $name, string $label): void
{
    $selected = $_POST[$name] ?? '';

    echo '<div class="form-group">';

    echo '<label>'
        . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        . ' <span class="required">*</span></label>';

    echo '<select name="'
        . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
        . '" required>';

    echo '<option value="">Alege nota</option>';

    for ($i = 5; $i >= 1; $i--) {
        $isSelected = (string) $selected === (string) $i
            ? 'selected'
            : '';

        echo '<option value="' . $i . '" '
            . $isSelected . '>'
            . $i . ' stele</option>';
    }

    echo '</select>';
    echo '</div>';
}

// ============================================
// LOGICA PRINCIPALA
// ============================================

// Validare ID medic
if ($doctor_id <= 0) {
    header("Location: ../medici/lista.php");
    exit;
}

// Preluare date doctor
$doctor_stmt = $conn->prepare("
    SELECT 
        u.name,
        s.name AS specialty
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE u.id = ? AND u.user_type = 'doctor'
");
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor = $doctor_stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header("Location: ../medici/lista.php");
    exit;
}

// Check dacă pacientul are deja review pentru medicul ăsta
$check_review_stmt = $conn->prepare("
    SELECT id FROM reviews
    WHERE doctor_id = ? AND patient_id = ?
");
$check_review_stmt->bind_param("ii", $doctor_id, $patient_id);
$check_review_stmt->execute();

if ($already_reviewed) {
    $error = "❌ Ai deja un review pentru acest medic!";
}

// Submit review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_reviewed) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5 || $comment === '') {
            $error = "❌ Te rog completează rating și comentariu valid!";
        } else {
            $insert_stmt = $conn->prepare("
                INSERT INTO reviews (doctor_id, patient_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insert_stmt->bind_param("iiis", $doctor_id, $patient_id, $rating, $comment);

            if ($error === '') {
                $message = "✅ Review adăugat cu succes!";

                $already_reviewed = true;
                $_POST = [];

                $_SESSION['csrf_token'] = bin2hex(
                    random_bytes(32)
                );
            }
        }
    }
}

function ratingOptions($name, $label) {
    $selected = $_POST[$name] ?? '';
    echo '<div class="form-group">';
    echo '<label>' . htmlspecialchars($label) . ' <span class="required">*</span></label>';
    echo '<select name="' . htmlspecialchars($name) . '" required>';
    echo '<option value="">Alege nota</option>';
    for ($i = 5; $i >= 1; $i--) {
        $isSelected = ((string)$selected === (string)$i) ? 'selected' : '';
        echo '<option value="' . $i . '" ' . $isSelected . '>' . $i . ' stele</option>';
    }
    echo '</select>';
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lasă Review - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="review-add-page">
    <?php
    $headerUseLogo = false;
    $headerTitle = '📋 MediTrust';
    $headerLinks = [
        ['href' => '../medici/lista.php', 'label' => 'Medici'],
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => '../auth/logout.php', 'label' => 'Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../medici/detalii.php?id=<?php echo (int)$doctor_id; ?>" class="back-btn">← Înapoi</a>

        <div class="page-title">
            <h2>✍️ Lasă Review</h2>
            <p>Spune-ne cum a fost experiența ta cu <?php echo htmlspecialchars($doctor['name']); ?></p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
                <p class="alert-success-note">
                    <a href="../medici/detalii.php?id=<?php echo (int)$doctor_id; ?>" class="success-back-link">Mergi înapoi la doctor →</a>
                </p>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($message) && !$already_reviewed): ?>
            <div class="form-section">
                <h3>👨‍⚕️ <?php echo htmlspecialchars($doctor['name']); ?></h3>
                <p class="doctor-specialty">🏥 <?php echo htmlspecialchars($doctor['specialty'] ?? 'Specialitate indisponibilă'); ?></p>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <h3>⭐ Evaluare pe criterii</h3>

                    <?php
                    ratingOptions('communication', 'Comunicare');
                    ratingOptions('professionalism', 'Profesionalism');
                    ratingOptions('punctuality', 'Punctualitate');
                    ratingOptions('empathy', 'Empatie');
                    ratingOptions('recommendation', 'Recomandare');
                    ?>

                    <div class="form-group">
                        <label for="comment">💬 Comentariu <span class="required">*</span></label>
                        <textarea id="comment" name="comment" placeholder="Spune-ne despre experiența ta..." required><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary review-submit-btn">
                        ✅ Trimite Review
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
