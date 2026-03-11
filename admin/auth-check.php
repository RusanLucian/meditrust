<?php
/**
 * Admin Authentication Guard
 * Include this at the top of every admin page (after bootstrap.php).
 * - Checks the user is logged in and has role 'admin'
 * - Enforces session timeout (30 minutes of inactivity)
 * - Regenerates CSRF token if not set
 */

// Session timeout: 30 minutes
define('ADMIN_SESSION_TIMEOUT', 1800);

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

// Inactivity timeout
if (isset($_SESSION['admin_logged_in_at'])) {
    if (time() - $_SESSION['admin_logged_in_at'] > ADMIN_SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'admin/login.php?timeout=1');
        exit;
    }
    // Refresh last activity timestamp
    $_SESSION['admin_logged_in_at'] = time();
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verify the CSRF token from POST data.
 * Call this at the top of any POST handler in admin pages.
 */
function adminVerifyCsrf(): void {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('❌ Token CSRF invalid. <a href="javascript:history.back()">Înapoi</a>');
    }
}
