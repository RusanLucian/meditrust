<?php
require_once '../bootstrap.php';

// Fetch doctors with specialties and average rating
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.phone,
        s.name AS specialty,
        s.id AS specialty_id,
        info.bio,
        info.avatar,
        IFNULL(AVG(r.rating), 0) AS avg_rating
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    LEFT JOIN reviews r ON u.id = r.doctor_id
    WHERE u.user_type = 'doctor'
    GROUP BY u.id, u.name, u.email, u.phone, s.name, s.id, info.bio, info.avatar
    ORDER BY avg_rating DESC, s.name, u.name
";

$doctors = [];
$result = $conn->query($query);
if ($result) {
    $doctors = $result->fetch_all(MYSQLI_ASSOC);
}

// Get unique specialties for filter
$specialties = [];
$specialty_query = "
    SELECT DISTINCT s.id, s.name
    FROM specialties s
    INNER JOIN info_doctori info ON s.id = info.specialty_id
    WHERE info.user_id IN (SELECT id FROM users WHERE user_type = 'doctor')
    ORDER BY s.name
";

$specialty_result = $conn->query($specialty_query);
if ($specialty_result) {
    $specialties = $specialty_result->fetch_all(MYSQLI_ASSOC);
}

// Filters
$filtered_doctors = $doctors;

$selected_specialty = $_GET['specialty'] ?? '';
$selected_rating = $_GET['rating'] ?? '';

if (!empty($selected_specialty)) {
    $filtered_doctors = array_filter($filtered_doctors, function ($d) use ($selected_specialty) {
        return (int)($d['specialty_id'] ?? 0) === (int)$selected_specialty;
    });
}

if (!empty($selected_rating)) {
    $filtered_doctors = array_filter($filtered_doctors, function ($d) use ($selected_rating) {
        return (float)$d['avg_rating'] >= (float)$selected_rating;
    });
}

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Medici - MediTrust</title>
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
        <?php if ($is_logged_in): ?>
            <a href="../auth/dashboard.php" class="back-btn">← Înapoi la Dashboard</a>
        <?php else: ?>
            <a href="../index.php" class="back-btn">← Înapoi la pagina principală</a>
        <?php endif; ?>

        <h1>🏥 Lista Medici</h1>

        <div class="filters">
            <h2>Filtrare</h2>

            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <select id="specialty" name="specialty" onchange="this.form.submit()">
                        <option value="">-- Toate specialitățile --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo (int)$spec['id']; ?>"
                                <?php echo (string)$selected_specialty === (string)$spec['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <select id="rating" name="rating" onchange="this.form.submit()">
                        <option value="">-- Rating minim --</option>
                        <option value="3" <?php echo (string)$selected_rating === '3' ? 'selected' : ''; ?>>⭐ 3+</option>
                        <option value="4" <?php echo (string)$selected_rating === '4' ? 'selected' : ''; ?>>⭐ 4+</option>
                        <option value="5" <?php echo (string)$selected_rating === '5' ? 'selected' : ''; ?>>⭐ 5</option>
                    </select>
                </div>

                <?php if (!empty($selected_specialty) || !empty($selected_rating)): ?>
                    <a href="lista.php" class="back-btn">Resetează filtrele</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="doctors-grid">
            <?php if (empty($filtered_doctors)): ?>
                <div class="no-data">
                    <div class="no-data-icon">👨‍⚕️</div>
                    <p>Nu am găsit medici pentru filtrele selectate.</p>
                </div>
            <?php else: ?>
                <?php foreach ($filtered_doctors as $doctor): ?>
                    <?php
                    $bio_preview = trim($doctor['bio'] ?? '');
                    $bio_text = $bio_preview !== ''
                        ? mb_strimwidth($bio_preview, 0, 100, '...')
                        : 'Fără descriere disponibilă.';
                    ?>

                    <div class="doctor-card">
                        <div class="doctor-header doctor-header-with-avatar">
                            <div class="doctor-card-avatar">
                                <?php if (!empty($doctor['avatar'])): ?>
                                    <img class="doctor-card-avatar-img"
                                         src="../img/<?php echo htmlspecialchars($doctor['avatar']); ?>"
                                         alt="<?php echo htmlspecialchars($doctor['name']); ?>">
                                <?php else: ?>
                                    👨‍⚕️
                                <?php endif; ?>
                            </div>

                            <div class="doctor-card-title">
                                <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                <p><?php echo htmlspecialchars($doctor['specialty'] ?? 'Specialitate indisponibilă'); ?></p>
                                <p>⭐ <?php echo number_format((float)$doctor['avg_rating'], 1); ?>/5</p>
                            </div>
                        </div>

                        <div class="doctor-body">
                            <div class="doctor-info">
                                <strong>📞 Telefon</strong>
                                <?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?>
                            </div>

                            <div class="doctor-info">
                                <strong>📧 Email</strong>
                                <?php echo htmlspecialchars($doctor['email']); ?>
                            </div>

                            <div class="doctor-info">
                                <strong>ℹ️ Descriere</strong>
                                <?php echo htmlspecialchars($bio_text); ?>
                            </div>
                        </div>

                        <div class="doctor-footer">
                            <a href="detalii.php?id=<?php echo (int)$doctor['id']; ?>" class="btn-view">
                                Vezi Detalii
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
