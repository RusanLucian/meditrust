<?php
/**
 * Admin Top Header - MediTrust Admin Panel
 * Include this file after the sidebar in every admin page.
 * 
 * Required Variables:
 * - $adminPageTitle: Page title shown in topbar (string)
 * 
 * Optional Variables:
 * - $adminBreadcrumb: Breadcrumb text (string)
 */

$adminPageTitle = $adminPageTitle ?? 'Admin Panel';
$adminBreadcrumb = $adminBreadcrumb ?? 'Admin &rsaquo; ' . htmlspecialchars($adminPageTitle);
$adminUserName = $_SESSION['user_name'] ?? 'Admin';
$adminUserInitial = strtoupper(mb_substr($adminUserName, 0, 1));
$adminLogoutUrl = BASE_URL . 'admin/logout.php';
?>

<header class="admin-topbar">
    <div class="topbar-left">
        <h1><?php echo htmlspecialchars($adminPageTitle); ?></h1>
        <div class="breadcrumb"><?php echo $adminBreadcrumb; ?></div>
    </div>
    <div class="topbar-right">
        <div class="admin-user-info">
            <div class="admin-avatar"><?php echo htmlspecialchars($adminUserInitial); ?></div>
            <span><?php echo htmlspecialchars($adminUserName); ?></span>
        </div>
        <a href="<?php echo $adminLogoutUrl; ?>" class="btn btn-sm btn-danger">🔓 Ieșire</a>
    </div>
</header>