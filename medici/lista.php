<?php
require_once '../bootstrap.php';

requireLogin('login.php');

// Fetch all doctors with their specialties
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.phone,
        s.name AS specialty,
        s.id AS specialty_id,
        info.bio,
        info.avatar
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE u.user_type = 'doctor'
    ORDER BY s.name, u.name
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

$filtered_doctors = $doctors;
if (!empty($_GET['specialty'])) {
    $specialty_filter = (int)$_GET['specialty'];
    $filtered_doctors = array_filter($doctors, function ($d) use ($specialty_filter) {
        return (int)($d['specialty_id'] ?? 0) === $specialty_filter;
    });
}
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
    $headerGreeting = '👋 Bine ai venit, ' . ($_SESSION['user_name'] ?? '') . '!';
    $headerLinks = [
        ['href' => '../auth/dashboard.php', 'label' => 'Dashboard'],
        ['href' => '../auth/logout.php', 'label' => '🔓 Delogare'],
    ];
    require_once '../includes/header.php';
    ?>

    <div class="container">
        <a href="../auth/dashboard.php" class="back-btn">← Înapoi la Dashboard</a>

        <h1>🏥 Lista Medici</h1>

        <div class="filters">
            <h2>Filtrare</h2>
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <select id="specialty" name="specialty" onchange="this.form.submit()">
                        <option value="">-- Toate specialitățile --</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo (int)$spec['id']; ?>"
                                <?php echo (string)($_GET['specialty'] ?? '') === (string)$spec['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="doctors-grid">
            <?php if (empty($filtered_doctors)): ?>
                <div class="no-data">
                    <div class="no-data-icon">👨‍⚕️</div>
                    <p>Nu am găsit medici cu această specialitate.</p>
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
                        <div class="doctor-header">
                            <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                            <p><?php echo htmlspecialchars($doctor['specialty'] ?? 'Specialitate indisponibilă'); ?></p>
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