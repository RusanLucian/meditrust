<?php
require_once '../bootstrap.php';

requireLogin('../auth/login.php');

$doctor_id = $_GET['id'] ?? '';
if (empty($doctor_id)) {
    header("Location: lista.php");
    exit;
}

// Preluare date doctor
$doctor_stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'doctor'");
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor = $doctor_stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header("Location: lista.php");
    exit;
}

// Rating și review-uri
$rating_stmt = $conn->prepare("
    SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
    FROM reviews 
    WHERE doctor_id = ?
");
$rating_stmt->bind_param("i", $doctor_id);
$rating_stmt->execute();
$rating_data = $rating_stmt->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'] ?? 0;

// Review-uri
$reviews_stmt = $conn->prepare("
    SELECT r.*, u.name as patient_name
    FROM reviews r
    JOIN users u ON r.patient_id = u.id
    WHERE r.doctor_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reviews_stmt->bind_param("i", $doctor_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Check daca userul curent e pacient
$is_patient = ($_SESSION['user_type'] ?? 'patient') === 'patient';
$patient_id = $is_patient ? $_SESSION['user_id'] : null;

// Check daca pacientul a lăsat review
$has_reviewed = false;
if ($is_patient) {
    $check_review_stmt = $conn->prepare("
        SELECT id FROM reviews 
        WHERE doctor_id = ? AND patient_id = ?
    ");
    $check_review_stmt->bind_param("ii", $doctor_id, $patient_id);
    $check_review_stmt->execute();
    $has_reviewed = $check_review_stmt->get_result()->num_rows > 0;
}

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($doctor['name']); ?> - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $headerLinks = [
        ['href' => 'lista.php', 'label' => '← Medici'],
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => '../auth/logout.php', 'label' => 'Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="lista.php" class="back-btn">← Înapoi la Lista</a>

        <!-- Doctor Details Card -->
        <div class="doctor-detail-card">
            <div class="doctor-detail-header">
                <div class="doctor-avatar">👨‍⚕️</div>
                <div class="doctor-detail-info">
                    <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <p><strong>🏥 Specialitate:</strong> <?php echo htmlspecialchars($doctor['specialty'] ?? 'N/A'); ?></p>
                    <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                    <p><strong>📱 Telefon:</strong> <?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></p>
                    <p><strong>ℹ️ Bio:</strong> <?php echo htmlspecialchars($doctor['bio'] ?? 'Nu are descriere'); ?></p>
                </div>
            </div>

            <!-- Rating Section -->
            <div class="rating-section">
                <h3>⭐ Rating și Review-uri</h3>
                <div class="rating-display">
                    <div class="rating-stars"><?php echo displayStars($avg_rating); ?></div>
                    <div class="rating-stats">
                        <div class="rating-value"><?php echo $avg_rating; ?>/5</div>
                        <div class="rating-count"><?php echo $total_reviews; ?> <?php echo $total_reviews === 1 ? 'recenzie' : 'recenzii'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <?php if ($is_patient): ?>
                <div class="actions-section">
                    <h3>⚡ Acțiuni Rapide</h3>
                    <div class="actions-grid">
                        <a href="../pacient/book.php?doctor_id=<?php echo $doctor['id']; ?>" class="action-btn">
                            <span class="icon">📅</span>
                            <span>Rezervă Appointment</span>
                        </a>
                        <?php if (!$has_reviewed): ?>
                            <a href="../reviews/add.php?doctor_id=<?php echo $doctor['id']; ?>" class="action-btn">
                                <span class="icon">✍️</span>
                                <span>Lasă Review</span>
                            </a>
                        <?php else: ?>
                            <div class="action-btn is-disabled">
                                <span class="icon">✓</span>
                                <span>Ai lăsat deja review</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h3>💬 Recenzii Pacienți</h3>
            <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <div class="review-author">👤 <?php echo htmlspecialchars($review['patient_name']); ?></div>
                                <div class="review-rating"><?php echo str_repeat('⭐', $review['rating']); ?> <?php echo $review['rating']; ?>/5</div>
                            </div>
                            <div class="review-date"><?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?></div>
                        </div>
                        <div class="review-text">
                            <?php echo htmlspecialchars($review['comment']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-reviews">
                    <p>❌ Nu sunt recenzii încă. Fii primul care lasă o recenzie!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>