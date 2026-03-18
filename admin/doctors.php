<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

// ── Delete doctor ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doctor'])) {
    adminVerifyCsrf();
    $del_id = (int)($_POST['doctor_id'] ?? 0);

    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type = 'doctor'");
        $stmt->bind_param('i', $del_id);
        $message = $stmt->execute() && $stmt->affected_rows > 0
            ? '✅ Doctorul a fost șters cu succes!'
            : '❌ Nu s-a putut șterge doctorul.';
    }
}

// ── Filters ──────────────────────────────────────────────────
$filterSearch    = trim($_GET['search']    ?? '');
$filterSpecialty = (int)($_GET['specialty'] ?? 0);

$query  = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.phone, 
        u.user_type,
        s.id as specialty_id,
        s.name as specialty,
        id.bio,
        id.avatar,
        COUNT(DISTINCT a.id) AS total_appointments,
        ROUND(AVG(r.rating), 1) AS avg_rating,
        COUNT(DISTINCT r.id) AS total_reviews
    FROM users u
    LEFT JOIN info_doctori id ON u.id = id.user_id
    LEFT JOIN specialties s ON id.specialty_id = s.id
    LEFT JOIN appointments a ON a.doctor_id = u.id
    LEFT JOIN reviews r ON r.doctor_id = u.id
    WHERE u.user_type = 'doctor'
";
$params = [];
$types  = '';

if ($filterSearch !== '') {
    $query  .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $like     = '%' . $filterSearch . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}

if ($filterSpecialty > 0) {
    $query  .= " AND s.id = ?";
    $params[] = $filterSpecialty;
    $types   .= 'i';
}

$query .= " GROUP BY u.id ORDER BY u.name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get all specialties for filter dropdown
$specs = $conn->query(
    "SELECT id, name FROM specialties ORDER BY name"
)->fetch_all(MYSQLI_ASSOC);

$adminActivePage = 'doctors';
$adminPageTitle  = 'Gestionare Doctori';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Doctori - Admin MediTrust</title>
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
                <h3>👨‍⚕️ Toți Doctorii <span class="text-muted">(<?php echo count($doctors); ?>)</span></h3>
                <div class="section-toolbar">
                    <form method="GET" action="doctors.php" class="filter-bar">
                        <input type="text" name="search" placeholder="🔍 Caută..."
                               value="<?php echo htmlspecialchars($filterSearch); ?>"
                               style="min-width:180px;">
                        <select name="specialty">
                            <option value="">Toate specialitățile</option>
                            <?php foreach ($specs as $s): ?>
                                <option value="<?php echo $s['id']; ?>"
                                    <?php echo $filterSpecialty === $s['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-secondary">Filtrează</button>
                        <?php if ($filterSearch || $filterSpecialty): ?>
                            <a href="doctors.php" class="btn btn-outline">✕ Resetează</a>
                        <?php endif; ?>
                    </form>
                    <a href="add-user.php" class="btn btn-primary">➕ Adaugă Doctor</a>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doctor</th>
                            <th>Specialitate</th>
                            <th>Telefon</th>
                            <th>Programări</th>
                            <th>Rating</th>
                            <th>Recenzii</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($doctors)): ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon">👨‍⚕️</div>
                                        <p>Niciun doctor găsit.</p>
                                        <a href="add-user.php" class="btn btn-primary">Adaugă primul doctor</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: foreach ($doctors as $doc): ?>
                            <tr>
                                <td class="text-muted"><?php echo (int)$doc['id']; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-thumb" style="background:linear-gradient(135deg,#28c76f,#0f8a4c);">
                                            <?php echo htmlspecialchars(strtoupper(mb_substr($doc['name'], 0, 1))); ?>
                                        </div>
                                        <div>
                                            <div class="user-cell-name"><?php echo htmlspecialchars($doc['name']); ?></div>
                                            <div class="user-cell-email"><?php echo htmlspecialchars($doc['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($doc['specialty'])): ?>
                                        <span class="badge badge-doctor"><?php echo htmlspecialchars($doc['specialty']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($doc['phone'] ?? '—'); ?></td>
                                <td class="text-center"><?php echo (int)$doc['total_appointments']; ?></td>
                                <td class="text-center">
                                    <?php if ($doc['avg_rating'] > 0): ?>
                                        ⭐ <?php echo number_format((float)$doc['avg_rating'], 1); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo (int)$doc['total_reviews']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-user.php?id=<?php echo (int)$doc['id']; ?>" class="btn btn-sm btn-warning">✏️ Editează</a>
                                        <form id="deldoc-<?php echo (int)$doc['id']; ?>" method="POST" action="doctors.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="doctor_id" value="<?php echo (int)$doc['id']; ?>">
                                            <input type="hidden" name="delete_doctor" value="1">
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="confirmDelete('deldoc-<?php echo (int)$doc['id']; ?>', 'Dr. <?php echo htmlspecialchars($doc['name'], ENT_QUOTES); ?>')">
                                            🗑️ Șterge
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal-overlay confirm-modal" id="confirmDeleteModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-body" style="text-align:center;padding:35px 25px;">
            <div class="confirm-icon">⚠️</div>
            <h4>Confirmare ștergere</h4>
            <p>Ești sigur că vrei să ștergi <strong id="deleteTargetName"></strong>? Această acțiune este ireversibilă.</p>
            <div style="display:flex;gap:12px;justify-content:center;margin-top:20px;">
                <button class="btn btn-secondary" onclick="closeModal('confirmDeleteModal')">Anulează</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Da, șterge!</button>
            </div>
        </div>
    </div>
</div>

<script src="../js/admin.js"></script>
</body>
</html>