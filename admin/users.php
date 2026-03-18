<?php
require_once '../bootstrap.php';
require_once 'auth-check.php';

$message = '';
$error   = '';

// ── Delete user ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    adminVerifyCsrf();
    $del_id = (int)($_POST['user_id'] ?? 0);

    if ($del_id > 0 && $del_id !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");

        if (!$stmt) {
            $error = '❌ Eroare la pregătirea ștergerii utilizatorului!';
        } else {
            $stmt->bind_param('i', $del_id);
            $message = $stmt->execute() && $stmt->affected_rows > 0
                ? '✅ Utilizatorul a fost șters cu succes!'
                : '❌ Nu s-a putut șterge utilizatorul (poate este administrator).';
        }
    } else {
        $error = '❌ ID invalid sau nu poți șterge propriul cont.';
    }
}

// ── Filters ──────────────────────────────────────────────────
$filterType   = $_GET['type'] ?? '';
$filterSearch = trim($_GET['search'] ?? '');

$allowedTypes = ['admin', 'doctor', 'patient'];
$filterType = in_array($filterType, $allowedTypes, true) ? $filterType : '';

$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.user_type, 
        u.phone,
        s.name AS specialty
    FROM users u
    LEFT JOIN info_doctori info ON u.id = info.user_id
    LEFT JOIN specialties s ON info.specialty_id = s.id
    WHERE 1=1
";

$params = [];
$types  = '';

if ($filterType !== '') {
    $query .= " AND u.user_type = ?";
    $params[] = $filterType;
    $types .= 's';
}

if ($filterSearch !== '') {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $like = '%' . $filterSearch . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$query .= " ORDER BY u.id DESC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    $users = [];
    $error = '❌ Eroare la pregătirea listei de utilizatori!';
} else {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$adminActivePage = 'users';
$adminPageTitle  = 'Gestionare Utilizatori';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>Utilizatori - Admin MediTrust</title>
    <link rel="stylesheet" href="../css/admin-style.css">
</head>
<body class="admin-body">

<?php require_once '../includes/admin-sidebar.php'; ?>

<div class="admin-main">
    <?php require_once '../includes/admin-header.php'; ?>

    <div class="admin-content">

        <?php if ($message): ?>
            <div class="alert alert-success" data-auto-dismiss="4000">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error" data-auto-dismiss="5000">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3>👥 Toți Utilizatorii <span class="text-muted">(<?php echo count($users); ?>)</span></h3>
                <div class="section-toolbar">
                    <form method="GET" action="users.php" class="filter-bar">
                        <input
                            type="text"
                            name="search"
                            placeholder="🔍 Caută după nume / email..."
                            value="<?php echo htmlspecialchars($filterSearch); ?>"
                            style="min-width:220px;"
                        >

                        <select name="type">
                            <option value="">Toate tipurile</option>
                            <option value="admin" <?php echo $filterType === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="doctor" <?php echo $filterType === 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                            <option value="patient" <?php echo $filterType === 'patient' ? 'selected' : ''; ?>>Pacient</option>
                        </select>

                        <button type="submit" class="btn btn-secondary">Filtrează</button>

                        <?php if ($filterType || $filterSearch): ?>
                            <a href="users.php" class="btn btn-outline">✕ Resetează</a>
                        <?php endif; ?>
                    </form>

                    <a href="add-user.php" class="btn btn-primary">➕ Adaugă Utilizator</a>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Utilizator</th>
                            <th>Tip</th>
                            <th>Telefon</th>
                            <th>Specialitate</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">👥</div>
                                        <p>Niciun utilizator găsit.</p>
                                        <a href="add-user.php" class="btn btn-primary">Adaugă primul utilizator</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="text-muted"><?php echo (int)$u['id']; ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-thumb">
                                                <?php echo htmlspecialchars(strtoupper(mb_substr($u['name'], 0, 1))); ?>
                                            </div>
                                            <div>
                                                <div class="user-cell-name"><?php echo htmlspecialchars($u['name']); ?></div>
                                                <div class="user-cell-email"><?php echo htmlspecialchars($u['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($u['user_type']); ?>">
                                            <?php echo htmlspecialchars($u['user_type'] === 'patient' ? 'pacient' : $u['user_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($u['specialty'] ?? '—'); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit-user.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-sm btn-warning">✏️ Editează</a>

                                            <?php if ($u['user_type'] !== 'admin'): ?>
                                                <form id="del-<?php echo (int)$u['id']; ?>" method="POST" action="users.php" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                    <input type="hidden" name="delete_user" value="1">
                                                </form>

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmDelete('del-<?php echo (int)$u['id']; ?>', '<?php echo htmlspecialchars($u['name'], ENT_QUOTES); ?>')"
                                                >
                                                    🗑️ Șterge
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay confirm-modal" id="confirmDeleteModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-body" style="text-align:center;padding:35px 25px;">
            <div class="confirm-icon">⚠️</div>
            <h4>Confirmare ștergere</h4>
            <p>Ești sigur că vrei să ștergi utilizatorul <strong id="deleteTargetName"></strong>? Această acțiune este ireversibilă.</p>
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