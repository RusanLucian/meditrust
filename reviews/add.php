<?php
require_once '../bootstrap.php';

requireRole('patient', '../auth/login.php');

$patient_id = $_SESSION['user_id'];
$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$message = '';
$error = '';
$already_reviewed = false;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($doctor_id <= 0) {
    header("Location: ../medici/lista.php");
    exit;
}

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

$check_review_stmt = $conn->prepare("
    SELECT id FROM reviews
    WHERE doctor_id = ? AND patient_id = ?
");
$check_review_stmt->bind_param("ii", $doctor_id, $patient_id);
$check_review_stmt->execute();

if ($check_review_stmt->get_result()->num_rows > 0) {
    $already_reviewed = true;
    $error = "❌ Ai deja un review pentru acest medic!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_reviewed) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } else {
        $communication = isset($_POST['communication']) ? (int)$_POST['communication'] : 0;
        $professionalism = isset($_POST['professionalism']) ? (int)$_POST['professionalism'] : 0;
        $punctuality = isset($_POST['punctuality']) ? (int)$_POST['punctuality'] : 0;
        $empathy = isset($_POST['empathy']) ? (int)$_POST['empathy'] : 0;
        $recommendation = isset($_POST['recommendation']) ? (int)$_POST['recommendation'] : 0;
        $comment = trim($_POST['comment'] ?? '');

        $ratings = [$communication, $professionalism, $punctuality, $empathy, $recommendation];

        foreach ($ratings as $value) {
            if ($value < 1 || $value > 5) {
                $error = "❌ Toate criteriile trebuie evaluate cu note între 1 și 5!";
                break;
            }
        }

        if (empty($error) && $comment === '') {
            $error = "❌ Te rog completează comentariul!";
        }

        if (empty($error)) {
            $rating = round(array_sum($ratings) / count($ratings));

            $insert_stmt = $conn->prepare("
                INSERT INTO reviews 
                (doctor_id, patient_id, rating, comment, created_at, communication, professionalism, punctuality, empathy, recommendation)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
            ");
            $insert_stmt->bind_param(
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

            if ($insert_stmt->execute()) {
                $message = "✅ Review adăugat cu succes!";
                $_POST = [];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $error = "❌ Eroare la adăugare review: " . $insert_stmt->error;
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
