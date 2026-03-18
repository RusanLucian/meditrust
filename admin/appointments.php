<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

// ── Update appointment status ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    adminVerifyCsrf();
    $app_id    = (int)($_POST['appointment_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    $allowedStatuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'rejected', 'pending'];
    if ($app_id > 0 && in_array($newStatus, $allowedStatuses, true)) {
        $upd = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $upd->bind_param('si', $newStatus, $app_id);
        $message = $upd->execute() ? '✅ Status actualizat cu succes!' : '❌ Eroare la actualizare!';
    } else {
        $error = '❌ Date invalide!';
    }
}

// ── Filters ──────────────────────────────────────────────────
$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['search'] ?? '');

$allowedStatuses = ['scheduled', 'confirmed', 'completed', 'cancelled', 'rejected', 'pending'];
$filterStatus = in_array($filterStatus, $allowedStatuses, true) ? $filterStatus : '';

$query  = "
    SELECT 
        a.id, 
        a.appointment_date, 
        a.status, 
        a.notes,
        p.name AS patient_name, 
        p.email AS patient_email,
        d.name AS doctor_name, 
        s.name as specialty
    FROM appointments a
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id  = d.id
    LEFT JOIN info_doctori id ON d.id = id.user_id
    LEFT JOIN specialties s ON id.specialty_id = s.id
    WHERE 1=1
";
$params = [];
$types  = '';

if ($filterStatus !== '') {
    $query  .= " AND a.status = ?";
    $params[] = $filterStatus;
    $types   .= 's';
}

if ($filterSearch !== '') {
    $query  .= " AND (p.name LIKE ? OR d.name LIKE ?)";
    $like     = '%' . $filterSearch . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

$query .= " ORDER BY a.appointment_date DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$adminActivePage = 'appointments';
$adminPageTitle  = 'Gestionare Programări';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Programări - Admin MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-body">

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="admin-main">
    <?php require_once '../includes/admin-header.php'; ?>

    <div class="admin-content">

        <?php if ($message): ?><div class="alert alert-success" data-auto-dismiss="4000"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"   data-auto-dismiss="5000"><?php echo htmlspecialchars($error);   ?></div><?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3>📅 Toate Programările <span class="text-muted">(<?php echo count($appointments); ?>)</span></h3>
                <form method="GET" action="appointments.php" class="filter-bar">
                    <input type="text" name="search" placeholder="🔍 Caută pacient/doctor..."
                           value="<?php echo htmlspecialchars($filterSearch); ?>"
                           style="min-width:200px;">
                    <select name="status">
                        <option value="">Toate statusurile</option>
                        <option value="scheduled"  <?php echo $filterStatus === 'scheduled'  ? 'selected' : ''; ?>>Programat</option>
                        <option value="pending"    <?php echo $filterStatus === 'pending'    ? 'selected' : ''; ?>>În așteptare</option>
                        <option value="confirmed"  <?php echo $filterStatus === 'confirmed'  ? 'selected' : ''; ?>>Confirmat</option>
                        <option value="completed"  <?php echo $filterStatus === 'completed'  ? 'selected' : ''; ?>>Finalizat</option>
                        <option value="cancelled"  <?php echo $filterStatus === 'cancelled'  ? 'selected' : ''; ?>>Anulat</option>
                        <option value="rejected"   <?php echo $filterStatus === 'rejected'   ? 'selected' : ''; ?>>Respins</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">Filtrează</button>
                    <?php if ($filterStatus || $filterSearch): ?>
                        <a href="appointments.php" class="btn btn-outline">✕ Resetează</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pacient</th>
                            <th>Doctor</th>
                            <th>Specialitate</th>
                            <th>Data & Ora</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">📅</div>
                                        <p>Nicio programare găsită.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: foreach ($appointments as $app): ?>
                            <tr>
                                <td class="text-muted"><?php echo (int)$app['id']; ?></td>
                                <td>
                                    <div class="user-cell-name"><?php echo htmlspecialchars($app['patient_name']); ?></div>
                                    <div class="user-cell-email text-muted"><?php echo htmlspecialchars($app['patient_email']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($app['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['specialty'] ?? '—'); ?></td>
                                <td class="no-wrap"><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($app['appointment_date']))); ?></td>
                                <td style="max-width:160px;white-space:normal;font-size:12px;">
                                    <?php echo htmlspecialchars(mb_strimwidth($app['notes'] ?? '', 0, 60, '…')); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($app['status']); ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Quick status update form -->
                                    <form method="POST" action="appointments.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int)$app['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" onchange="this.form.submit()"
                                                style="padding:5px 8px;border:1.5px solid #e0e0e0;border-radius:6px;font-size:12px;cursor:pointer;background:#fff;">
                                            <option value="">— Schimbă status —</option>
                                            <option value="scheduled">Programat</option>
                                            <option value="confirmed">Confirmă</option>
                                            <option value="completed">Finalizează</option>
                                            <option value="cancelled">Anulează</option>
                                            <option value="rejected">Respinge</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>