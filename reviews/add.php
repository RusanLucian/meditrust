<?php
require_once '../bootstrap.php';

// Doar pacienți
requireRole('patient', '../auth/login.php');

$patient_id = $_SESSION['user_id'];
$doctor_id = $_GET['doctor_id'] ?? '';
$message = '';
$error = '';

if (empty($doctor_id)) {
    header("Location: ../medici/lista.php");
    exit;
}

// Preluare date doctor
$doctor_stmt = $conn->prepare("SELECT name, specialty FROM users WHERE id = ? AND user_type = 'doctor'");
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor = $doctor_stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header("Location: ../medici/lista.php");
    exit;
}

// Check daca pacientul a deja review pentru medicul asta
$check_review_stmt = $conn->prepare("
    SELECT id FROM reviews 
    WHERE doctor_id = ? AND patient_id = ?
");
$check_review_stmt->bind_param("ii", $doctor_id, $patient_id);
$check_review_stmt->execute();

if ($check_review_stmt->get_result()->num_rows > 0) {
    $error = "❌ Ai deja un review pentru acest medic!";
}

// Submit review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = $_POST['comment'] ?? '';

    if (empty($rating) || empty($comment)) {
        $error = "❌ Te rog completează rating și comentariu!";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "❌ Rating-ul trebuie să fie între 1 și 5!";
    } else {
        $insert_stmt = $conn->prepare("
            INSERT INTO reviews (doctor_id, patient_id, rating, comment, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $insert_stmt->bind_param("iiis", $doctor_id, $patient_id, $rating, $comment);

        if ($insert_stmt->execute()) {
            $message = "✅ Review adăugat cu succes!";
        } else {
            $error = "❌ Eroare la adăugare review: " . $insert_stmt->error;
        }
    }
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
        <a href="../medici/detalii.php?id=<?php echo $doctor_id; ?>" class="back-btn">← Înapoi</a>

        <div class="page-title">
            <h2>✍️ Lasă Review</h2>
            <p>Spune-ne cum a fost experiența ta cu <?php echo htmlspecialchars($doctor['name']); ?></p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
                <p class="alert-success-note">
                    <a href="../medici/detalii.php?id=<?php echo $doctor_id; ?>" class="success-back-link">Mergi înapoi la doctor →</a>
                </p>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($error) || !empty($message)): ?>
            <div class="form-section">
                <h3>👨‍⚕️ <?php echo htmlspecialchars($doctor['name']); ?></h3>
                <p class="doctor-specialty">🏥 <?php echo htmlspecialchars($doctor['specialty']); ?></p>

                <form method="POST">
                    <div class="form-group">
                        <label>⭐ Rating <span class="required">*</span></label>
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5" required>
                            <label for="star5">★</label>
                            <input type="radio" id="star4" name="rating" value="4">
                            <label for="star4">★</label>
                            <input type="radio" id="star3" name="rating" value="3">
                            <label for="star3">★</label>
                            <input type="radio" id="star2" name="rating" value="2">
                            <label for="star2">★</label>
                            <input type="radio" id="star1" name="rating" value="1">
                            <label for="star1">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comment">💬 Comentariu <span class="required">*</span></label>
                        <textarea id="comment" name="comment" placeholder="Spune-ne despre experiența ta..." required></textarea>
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