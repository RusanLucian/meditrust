<?php
/**
 * Admin Sidebar - MediTrust Admin Panel
 * Include this file in every admin page.
 * $adminActivePage should be set before including (e.g. 'dashboard', 'users', etc.)
 */
$adminActivePage = $adminActivePage ?? '';
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <img src="<?php echo BASE_URL; ?>img/meditrust-logo.png" alt="MediTrust">
        <div>
            <h2>MediTrust</h2>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>

        <a href="<?php echo BASE_URL; ?>admin/dashboard.php"
           class="nav-item <?php echo $adminActivePage === 'dashboard' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-title">Gestionare</div>

        <a href="<?php echo BASE_URL; ?>admin/users.php"
           class="nav-item <?php echo $adminActivePage === 'users' ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span>Utilizatori</span>
        </a>

        <a href="<?php echo BASE_URL; ?>admin/doctors.php"
           class="nav-item <?php echo $adminActivePage === 'doctors' ? 'active' : ''; ?>">
            <span class="nav-icon">👨‍⚕️</span>
            <span>Doctori</span>
        </a>

        <a href="<?php echo BASE_URL; ?>admin/appointments.php"
           class="nav-item <?php echo $adminActivePage === 'appointments' ? 'active' : ''; ?>">
            <span class="nav-icon">📅</span>
            <span>Programări</span>
        </a>

        <div class="nav-section-title">Cont</div>

        <a href="<?php echo BASE_URL; ?>admin/settings.php"
           class="nav-item <?php echo $adminActivePage === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span>Setări</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>admin/logout.php" class="nav-item">
            <span class="nav-icon">🔓</span>
            <span>Delogare</span>
        </a>
    </div>
</aside>
