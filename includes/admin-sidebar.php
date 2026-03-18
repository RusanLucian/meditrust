<?php
/**
 * Admin Sidebar - MediTrust Admin Panel
 * Include this file in every admin page.
 * 
 * Required Variables:
 * - $adminActivePage: Current page name (e.g., 'dashboard', 'users', 'doctors', etc.)
 */

$adminActivePage = $adminActivePage ?? '';
$baseUrl = BASE_URL;
?>

<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <img src="<?php echo $baseUrl; ?>img/meditrust-logo.png" alt="MediTrust" class="sidebar-logo">
        <div>
            <h2>MediTrust</h2>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <!-- Principal Section -->
        <div class="nav-section-title">Principal</div>

        <a href="<?php echo $baseUrl; ?>admin/dashboard.php"
           class="nav-item <?php echo $adminActivePage === 'dashboard' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>

        <!-- Management Section -->
        <div class="nav-section-title">Gestionare</div>

        <a href="<?php echo $baseUrl; ?>admin/users.php"
           class="nav-item <?php echo $adminActivePage === 'users' ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span>Utilizatori</span>
        </a>

        <a href="<?php echo $baseUrl; ?>admin/doctors.php"
           class="nav-item <?php echo $adminActivePage === 'doctors' ? 'active' : ''; ?>">
            <span class="nav-icon">👨‍⚕️</span>
            <span>Doctori</span>
        </a>

        <a href="<?php echo $baseUrl; ?>admin/appointments.php"
           class="nav-item <?php echo $adminActivePage === 'appointments' ? 'active' : ''; ?>">
            <span class="nav-icon">📅</span>
            <span>Programări</span>
        </a>

        <!-- Account Section -->
        <div class="nav-section-title">Cont</div>

        <a href="<?php echo $baseUrl; ?>admin/settings.php"
           class="nav-item <?php echo $adminActivePage === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span>Setări</span>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?php echo $baseUrl; ?>admin/logout.php" class="nav-item">
            <span class="nav-icon">🔓</span>
            <span>Delogare</span>
        </a>
    </div>
</aside>