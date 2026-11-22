<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Panel - MediTrust') ?></title>
    <link rel="stylesheet" href="<?= asset('css/admin-style.css') ?>">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <a href="<?= route('admin.dashboard') ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                <img src="<?= asset('img/meditrust-logo.png') ?>" alt="MediTrust" class="sidebar-logo">
                <div>
                    <h2 style="margin:0;font-size:1.2rem;">MediTrust</h2>
                    <span style="font-size:0.8rem;color:#90caf9;">Admin Panel</span>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Principal</div>
            <a href="<?= route('admin.dashboard') ?>" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-title">Gestionare</div>
            <a href="<?= route('admin.users') ?>" class="nav-item <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span>
                <span>Utilizatori</span>
            </a>
            <a href="<?= route('admin.doctors') ?>" class="nav-item <?= ($activePage ?? '') === 'doctors' ? 'active' : '' ?>">
                <span class="nav-icon">👨‍⚕️</span>
                <span>Doctori</span>
            </a>
            <a href="<?= route('admin.appointments') ?>" class="nav-item <?= ($activePage ?? '') === 'appointments' ? 'active' : '' ?>">
                <span class="nav-icon">📅</span>
                <span>Programări</span>
            </a>

            <div class="nav-section-title">Cont</div>
            <a href="<?= route('admin.settings') ?>" class="nav-item <?= ($activePage ?? '') === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span>
                <span>Setări</span>
            </a>
        
            <a href="<?= route('logout') ?>" class="nav-item" style="color:#ef5350;">
                <span class="nav-icon">🚪</span>
                <span>Deconectare</span>
            </a>
        </nav>
    </aside>

    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
                <div class="breadcrumb"><?= e($breadcrumb ?? 'Admin › ' . ($pageTitle ?? 'Dashboard')) ?></div>
            </div>
            <div class="topbar-right">
                <?php $currentUser = auth(); ?>
                <div class="admin-user-info">
                    <div class="admin-avatar">
                        <?= strtoupper(mb_substr($currentUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span><?= e($currentUser['name'] ?? 'Administrator') ?></span>
                </div>
                <a href="<?= route('logout') ?>" class="btn btn-sm btn-danger">🔓 Ieșire</a>
            </div>
        </header>

        <!-- Main Content -->
        <div class="admin-content">
            <?php if ($success = flash('success')): ?>
                <div class="alert alert-success" style="background:#e8f5e9;color:#2e7d32;padding:12px 16px;border-radius:6px;margin-bottom:20px;border:1px solid #c8e6c9;">
                    <?= e($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error = flash('error')): ?>
                <div class="alert alert-danger" style="background:#ffebee;color:#c62828;padding:12px 16px;border-radius:6px;margin-bottom:20px;border:1px solid #ffcdd2;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>
</body>
</html>
