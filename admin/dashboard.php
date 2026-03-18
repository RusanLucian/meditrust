<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

// ── Statistics ──────────────────────────────────────────────
$totalUsers          = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalDoctors        = $conn->query("SELECT COUNT(*) AS c FROM users WHERE user_type = 'doctor'")->fetch_assoc()['c'];
$totalPatients       = $conn->query("SELECT COUNT(*) AS c FROM users WHERE user_type = 'patient'")->fetch_assoc()['c'];
$totalAppointments   = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()['c'];
$pendingAppointments = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status IN ('scheduled','pending')")->fetch_assoc()['c'];
$totalReviews        = $conn->query("SELECT COUNT(*) AS c FROM reviews")->fetch_assoc()['c'];

// ── Recent appointments ──────────────────────────────────────
$recentApps = $conn->query("
    SELECT 
        a.id, 
        a.appointment_date, 
        a.status,
        p.name AS patient_name,
        d.name AS doctor_name, 
        s.name as specialty
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id = d.id
    LEFT JOIN info_doctori info ON d.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    ORDER BY a.id DESC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// ── Recently registered users ──────────────────────────────
$recentUsers = $conn->query("
    SELECT id, name, email, user_type
    FROM users
    ORDER BY id DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$adminActivePage = 'dashboard';
$adminPageTitle  = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Dashboard - Admin MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-body">

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="admin-main">
    <?php require_once '../includes/admin-header.php'; ?>

    <div class="admin-content">

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalUsers; ?></h3>
                    <p>Total Utilizatori</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">👨‍⚕️</div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalDoctors; ?></h3>
                    <p>Doctori</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">🧑‍⚕️</div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalPatients; ?></h3>
                    <p>Pacienți</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">📅</div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalAppointments; ?></h3>
                    <p>Total Programări</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">⏳</div>
                <div class="stat-info">
                    <h3><?php echo (int)$pendingAppointments; ?></h3>
                    <p>Programări în Așteptare</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">⭐</div>
                <div class="stat-info">
                    <h3><?php echo (int)$totalReviews; ?></h3>
                    <p>Total Recenzii</p>
                </div>
            </div>
        </div>

        <!-- Two columns: recent appointments + recent users -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;flex-wrap:wrap;">

            <!-- Recent Appointments -->
            <div class="admin-card" style="min-width:0;">
                <div class="admin-card-header">
                    <h3>📅 Programări Recente</h3>
                    <a href="appointments.php" class="btn btn-sm btn-outline">Vezi toate</a>
                </div>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Pacient</th>
                                <th>Doctor</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentApps)): ?>
                                <tr><td colspan="4" class="text-center text-muted" style="padding:20px;">Nicio programare.</td></tr>
                            <?php else: foreach ($recentApps as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                    <td class="no-wrap"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($app['appointment_date']))); ?></td>
                                    <td><span class="badge badge-<?php echo htmlspecialchars($app['status']); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="admin-card" style="min-width:0;">
                <div class="admin-card-header">
                    <h3>👥 Utilizatori Recenți</h3>
                    <a href="users.php" class="btn btn-sm btn-outline">Vezi toți</a>
                </div>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nume</th>
                                <th>Email</th>
                                <th>Rol</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentUsers)): ?>
                                <tr><td colspan="3" class="text-center text-muted" style="padding:20px;">Niciun utilizator.</td></tr>
                            <?php else: foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-thumb"><?php echo htmlspecialchars(strtoupper(mb_substr($u['name'], 0, 1))); ?></div>
                                            <span class="user-cell-name"><?php echo htmlspecialchars($u['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><span class="badge badge-<?php echo htmlspecialchars($u['user_type']); ?>"><?php echo htmlspecialchars($u['user_type']); ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /grid -->

    </div><!-- /admin-content -->
</div><!-- /admin-main -->

<script src="../js/admin.js"></script>
</body>
</html>