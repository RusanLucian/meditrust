<?php
require_once '../bootstrap.php';

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($doctor_id <= 0) {
    header('Location: lista.php');
    exit;
}

// Fetch doctor details
$query = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        s.name AS specialty,
        s.description AS specialty_description,
        info.bio,
        info.avatar
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE u.id = ? AND u.user_type = 'doctor'
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    header('Location: lista.php');
    exit;
}

// Fetch reviews for this doctor
$reviews_query = "
    SELECT 
        r.rating,
        r.communication,
        r.professionalism,
        r.punctuality,
        r.empathy,
        r.recommendation,
        r.comment,
        r.created_at,
        u.name AS patient_name
    FROM reviews r
    JOIN users u ON r.patient_id = u.id
    WHERE r.doctor_id = ?
    ORDER BY r.created_at DESC
";

$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bind_param("i", $doctor_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);

// Calculate average ratings
$avg_rating = 0;
$avg_communication = 0;
$avg_professionalism = 0;
$avg_punctuality = 0;
$avg_empathy = 0;
$avg_recommendation = 0;

if (!empty($reviews)) {
    $avg_rating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
    $avg_communication = round(array_sum(array_column($reviews, 'communication')) / count($reviews), 1);
    $avg_professionalism = round(array_sum(array_column($reviews, 'professionalism')) / count($reviews), 1);
    $avg_punctuality = round(array_sum(array_column($reviews, 'punctuality')) / count($reviews), 1);
    $avg_empathy = round(array_sum(array_column($reviews, 'empathy')) / count($reviews), 1);
    $avg_recommendation = round(array_sum(array_column($reviews, 'recommendation')) / count($reviews), 1);
}

$is_logged_in = isset($_SESSION['user_id']);
$is_patient = (($_SESSION['user_type'] ?? '') === 'patient');
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
    if ($is_logged_in) {
        $headerGreeting = '👋 Bine ai venit, ' . ($_SESSION['user_name'] ?? '') . '!';
        $headerLinks = [
            ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
            ['href' => '../auth/logout.php', 'label' => '🔓 Delogare'],
        ];
    } else {
        $headerGreeting = null;
        $headerLinks = [
            ['href' => '../auth/login.php', 'label' => '🔓 Conectare'],
            ['href' => '../auth/register.php', 'label' => '📝 Înregistrare'],
        ];
    }
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="lista.php" class="back-btn">← Înapoi la lista medicilor</a>

        <div class="doctor-detail-card">
            <div class="doctor-detail-header">
                <div class="doctor-avatar">
                    <?php if (!empty($doctor['avatar'])): ?>
                        <img class="doctor-avatar-img"
                             src="../img/<?php echo htmlspecialchars($doctor['avatar']); ?>"
                             alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                    <?php else: ?>
                        👨‍⚕️
                    <?php endif; ?>
                </div>

                <div class="doctor-detail-info">
                    <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <p><strong>🏥 Specialitate:</strong> <?php echo htmlspecialchars($doctor['specialty'] ?? 'N/A'); ?></p>
                    <p><strong>📧 Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                    <p><strong>📞 Telefon:</strong> <?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <div class="rating-section">
                <h3>⭐ Evaluare generală</h3>

                <div class="rating-display">
                    <div class="rating-stars">
                        ⭐ <?php echo $avg_rating; ?>/5
                    </div>
                    <div class="rating-stats">
                        <div class="rating-value"><?php echo $avg_rating; ?></div>
                        <div class="rating-count"><?php echo count($reviews); ?> recenzii</div>
                    </div>
                </div>

                <?php if (!empty($reviews)): ?>
                    <div class="rating-criteria">
                        <h4>Evaluare pe criterii</h4>
                        <p>💬 Comunicare: <strong><?php echo $avg_communication; ?>/5</strong></p>
                        <p>👨‍⚕️ Profesionalism: <strong><?php echo $avg_professionalism; ?>/5</strong></p>
                        <p>⏱️ Punctualitate: <strong><?php echo $avg_punctuality; ?>/5</strong></p>
                        <p>🤝 Empatie: <strong><?php echo $avg_empathy; ?>/5</strong></p>
                        <p>✅ Recomandare: <strong><?php echo $avg_recommendation; ?>/5</strong></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="actions-section">
                <h3>Acțiuni</h3>
                <div class="actions-grid">
                    <?php if ($is_patient): ?>
                        <a href="../pacient/book.php?doctor_id=<?php echo (int)$doctor['id']; ?>" class="action-btn">
                            <span class="icon">📅</span>
                            <span>Programează-te</span>
                        </a>

                        <a href="../reviews/add.php?doctor_id=<?php echo (int)$doctor['id']; ?>" class="action-btn">
                            <span class="icon">✍️</span>
                            <span>Lasă recenzie</span>
                        </a>
                    <?php elseif (!$is_logged_in): ?>
                        <a href="../auth/login.php" class="action-btn">
                            <span class="icon">🔓</span>
                            <span>Conectează-te pentru programare</span>
                        </a>
                    <?php endif; ?>

                    <a href="lista.php" class="action-btn">
                        <span class="icon">←</span>
                        <span>Înapoi la lista medicilor</span>
                    </a>
                </div>
            </div>

            <div class="actions-section">
                <h3>Despre doctor</h3>
                <p><?php echo htmlspecialchars($doctor['bio'] ?? 'Nu este disponibilă o descriere.'); ?></p>
            </div>

            <?php if (!empty($doctor['specialty_description'])): ?>
                <div class="actions-section">
                    <h3>Despre specialitate</h3>
                    <p><?php echo htmlspecialchars($doctor['specialty_description']); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="reviews-section">
            <h3>⭐ Recenziile pacienților</h3>

            <?php if (empty($reviews)): ?>
                <div class="no-reviews">
                    Nu sunt recenzii pentru acest doctor.
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <div class="review-author">
                                    <?php echo htmlspecialchars($review['patient_name']); ?>
                                </div>

                                <div class="review-rating">
                                    ⭐ <?php echo (int)$review['rating']; ?>/5
                                </div>

                                <div class="review-criteria">
                                    <small>
                                        💬 Comunicare: <?php echo (int)$review['communication']; ?>/5 |
                                        👨‍⚕️ Profesionalism: <?php echo (int)$review['professionalism']; ?>/5 |
                                        ⏱️ Punctualitate: <?php echo (int)$review['punctuality']; ?>/5 |
                                        🤝 Empatie: <?php echo (int)$review['empathy']; ?>/5 |
                                        ✅ Recomandare: <?php echo (int)$review['recommendation']; ?>/5
                                    </small>
                                </div>
                            </div>

                            <div class="review-date">
                                <?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?>
                            </div>
                        </div>

                        <div class="review-text">
                            <?php echo htmlspecialchars($review['comment']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
