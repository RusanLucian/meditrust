<?php
require_once '../bootstrap.php';

requireLogin('../auth/login.php');

$search = $_GET['search'] ?? '';
$specialty = $_GET['specialty'] ?? '';

$query = "SELECT * FROM users WHERE user_type = 'doctor'";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($specialty)) {
    $query .= " AND specialty = ?";
    $params[] = $specialty;
    $types .= 's';
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$doctors = $stmt->get_result();

$specialties_result = $conn->query("SELECT DISTINCT specialty FROM users WHERE user_type = 'doctor' AND specialty IS NOT NULL");
$specialties = [];
while ($row = $specialties_result->fetch_assoc()) {
    if (!empty($row['specialty'])) {
        $specialties[] = $row['specialty'];
    }
}

// Funcție pentru calculare rating
function getDoctorRating($doctor_id, $conn) {
    $rating_stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
        FROM reviews 
        WHERE doctor_id = ?
    ");
    $rating_stmt->bind_param("i", $doctor_id);
    $rating_stmt->execute();
    $result = $rating_stmt->get_result()->fetch_assoc();
    
    return [
        'avg_rating' => round($result['avg_rating'] ?? 0, 1),
        'total_reviews' => $result['total_reviews'] ?? 0
    ];
}

?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medici - MediTrust</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php
    $headerGreeting = '👋 Bine ai venit, ' . ($_SESSION['user_name'] ?? 'User') . '!';
    $headerLinks = [
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => '../auth/logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../auth/dashboard.php" class="back-btn">← Înapoi la Dashboard</a>

        <div class="filters">
            <h2>🔍 Caută Medici</h2>
            <form method="GET">
                <div class="filter-group">
                    <input type="text" name="search" placeholder="Caută după nume..." value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                    <select name="specialty">
                        <option value="">Toate Specialitățile</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialty === $spec ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit">🔍 Caută</button>
                    <a href="lista.php" class="reset-btn">↻ Resetează</a>
                </div>
            </form>
        </div>

        <?php if ($doctors && $doctors->num_rows > 0): ?>
            <div class="doctors-grid">
                <?php while ($doctor = $doctors->fetch_assoc()): ?>
                    <?php 
                        $rating_data = getDoctorRating($doctor['id'], $conn);
                        $avg_rating = $rating_data['avg_rating'];
                        $total_reviews = $rating_data['total_reviews'];
                        $stars = displayStars($avg_rating);
                    ?>
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <h3>👨‍⚕️ <?php echo htmlspecialchars($doctor['name']); ?></h3>
                            <p><?php echo htmlspecialchars($doctor['specialty'] ?? 'Medic'); ?></p>
                        </div>
                        <div class="doctor-body">
                            <div class="doctor-info">
                                <strong>📧 Email:</strong>
                                <span><?php echo htmlspecialchars($doctor['email']); ?></span>
                            </div>
                            <div class="doctor-info">
                                <strong>📱 Telefon:</strong>
                                <span><?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="rating">
                                <span class="stars"><?php echo $stars; ?></span>
                                <span class="review-count"><?php echo $avg_rating; ?>/5 (<?php echo $total_reviews; ?> <?php echo $total_reviews === 1 ? 'review' : 'review-uri'; ?>)</span>
                            </div>
                            <div class="doctor-footer">
                                <a href="detalii.php?id=<?php echo $doctor['id']; ?>" class="btn-view">👁️ Vezi Detalii</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-doctors">
                <p>❌ Nu s-au găsit medici matching pe criteriile tale.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>