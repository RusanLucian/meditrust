<?php
require_once '../bootstrap.php';

// Pagina este publica; doar medicul proprietar poate sterge recenzii.
$doctor_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$doctor_id || $doctor_id <= 0) {
    header('Location: lista.php');
    exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$is_patient = ($_SESSION['user_type'] ?? '') === 'patient';
$is_own_profile = $is_logged_in
    && ($_SESSION['user_type'] ?? '') === 'doctor'
    && (int) $_SESSION['user_id'] === $doctor_id;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function getDoctorDetails(mysqli $conn, int $doctorId): ?array
{
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.phone,
               s.name AS specialty, s.description AS specialty_description,
               info.bio, info.avatar
        FROM users u
        LEFT JOIN info_doctori info ON u.id = info.user_id
        LEFT JOIN specialties s ON info.specialty_id = s.id
        WHERE u.id = ? AND u.user_type = 'doctor'
    ");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $doctor ?: null;
}

function getDoctorReviews(mysqli $conn, int $doctorId): array
{
    $stmt = $conn->prepare("
        SELECT r.id, r.rating, r.communication, r.professionalism,
               r.punctuality, r.empathy, r.recommendation,
               r.comment, r.created_at, u.name AS patient_name
        FROM reviews r
        JOIN users u ON r.patient_id = u.id
        WHERE r.doctor_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $reviews;
}

// Conditia doctor_id = ? este verificata chiar in DELETE, nu doar in interfata.
function deleteDoctorReview(mysqli $conn, int $reviewId, int $doctorId): bool
{
    $stmt = $conn->prepare('DELETE FROM reviews WHERE id = ? AND doctor_id = ?');
    $stmt->bind_param('ii', $reviewId, $doctorId);
    $stmt->execute();
    $deleted = $stmt->affected_rows === 1;
    $stmt->close();
    return $deleted;
}

function calculateReviewAverages(array $reviews): array
{
    $fields = ['rating', 'communication', 'professionalism',
               'punctuality', 'empathy', 'recommendation'];
    $averages = array_fill_keys($fields, 0.0);
    if (!$reviews) {
        return $averages;
    }
    foreach ($fields as $field) {
        $averages[$field] = round(
            array_sum(array_column($reviews, $field)) / count($reviews), 1
        );
    }
    return $averages;
}

$doctor = getDoctorDetails($conn, $doctor_id);
if (!$doctor) {
    header('Location: lista.php');
    exit;
}

$review_message = '';
$review_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $submittedToken = $_POST['csrf_token'] ?? null;
    $reviewId = filter_var($_POST['review_id'] ?? null, FILTER_VALIDATE_INT);

    if (!$is_own_profile) {
        http_response_code(403);
        $review_error = '❌ Nu ai permisiunea să ștergi această recenzie.';
    } elseif (!is_string($submittedToken) ||
              !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $review_error = '❌ Cerere invalidă. Reîncarcă pagina și încearcă din nou.';
    } elseif (!$reviewId || $reviewId <= 0) {
        $review_error = '❌ Recenzie invalidă.';
    } else {
        try {
            if (deleteDoctorReview($conn, $reviewId, $doctor_id)) {
                $review_message = '✅ Recenzia a fost ștearsă cu succes!';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $review_error = '❌ Recenzia nu există sau nu aparține acestui medic.';
            }
        } catch (mysqli_sql_exception $e) {
            error_log('MediTrust - eroare stergere recenzie: ' . $e->getMessage());
            $review_error = '❌ A apărut o eroare la ștergerea recenziei.';
        }
    }
}

// Citim recenziile DUPA procesarea stergerii, pentru medii actualizate.
$reviews = getDoctorReviews($conn, $doctor_id);
$averages = calculateReviewAverages($reviews);
$avg_rating = $averages['rating'];
$avg_communication = $averages['communication'];
$avg_professionalism = $averages['professionalism'];
$avg_punctuality = $averages['punctuality'];
$avg_empathy = $averages['empathy'];
$avg_recommendation = $averages['recommendation'];
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
        <?php if ($review_message !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($review_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($review_error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($review_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
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


            <?php if (($_SESSION['user_type'] ?? '') !== 'doctor'): ?>
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
                        <?php if ($is_own_profile): ?>
                            <form method="POST" action="detalii.php?id=<?php echo (int)$doctor_id; ?>"
                                  onsubmit="return confirm('Sigur dorești să ștergi definitiv această recenzie?');"
                                  style="margin-top: 12px;">
                                <input type="hidden" name="csrf_token"
                                       value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="review_id" value="<?php echo (int)$review['id']; ?>">
                                <button type="submit" name="delete_review" value="1" class="btn btn-danger">
                                    🗑️ Șterge recenzia
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>