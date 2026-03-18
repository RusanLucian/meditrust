<?php
/**
 * Admin Authentication Guard
 * Include this at the top of every admin page (after bootstrap.php).
 * - Checks the user is logged in and has role 'admin'
 * - Enforces session timeout (30 minutes of inactivity)
 * - Regenerates CSRF token if not set
 */

if (!defined('ADMIN_SESSION_TIMEOUT')) {
    define('ADMIN_SESSION_TIMEOUT', 1800);
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

if (isset($_SESSION['admin_logged_in_at'])) {
    if (time() - $_SESSION['admin_logged_in_at'] > ADMIN_SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'admin/login.php?timeout=1');
        exit;
    }

    $_SESSION['admin_logged_in_at'] = time();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>