<?php

/**
 * MediTrust - Utility Functions
 */

// ============================================================
// AUTH FUNCTIONS
// ============================================================

function esteLogat() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getTipUtilizator() {
    return $_SESSION['user_type'] ?? null;
}

function esteAdmin() {
    return esteLogat() && getTipUtilizator() === 'admin';
}

function esteDoctor() {
    return esteLogat() && getTipUtilizator() === 'doctor';
}

function estePacient() {
    return esteLogat() && getTipUtilizator() === 'patient';
}

function requireLogin($redirect = '../auth/login.php') {
    if (!esteLogat()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireRole($role, $redirect = '../auth/login.php') {
    requireLogin($redirect);

    $userType = $_SESSION['user_type'] ?? '';

    if ($userType !== $role) {
        header('Location: ' . $redirect);
        exit;
    }
}

// ============================================================
// QUERY FUNCTIONS
// ============================================================

function executeQuery($sql, $conn) {
    $result = $conn->query($sql);
    if (!$result) {
        return false;
    }
    return $result;
}

function getRow($result) {
    return $result ? $result->fetch_assoc() : null;
}

function getAll($result) {
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT 
            u.*,
            info.bio,
            info.avatar,
            info.specialty_id,
            s.name AS specialty
        FROM users u
        LEFT JOIN info_doctori info ON u.id = info.user_id
        LEFT JOIN specialties s ON info.specialty_id = s.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

function displayStars($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;

    $stars = str_repeat('⭐', $full_stars);
    if ($half_star) {
        $stars .= '⭐';
    }
    $stars .= str_repeat('☆', $empty_stars);

    return $stars;
}

// ============================================================
// VALIDATION FUNCTIONS
// ============================================================

function esteEmailValid($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function esteParolaValida($parola) {
    return strlen($parola) >= 6;
}

function sanitizeInput($input, $conn) {
    return $conn->real_escape_string(trim($input));
}

// ============================================================
// PASSWORD FUNCTIONS
// ============================================================

function hashParola($parola) {
    return password_hash($parola, PASSWORD_DEFAULT);
}

function verifyPassword($parola, $hash) {
    return password_verify($parola, $hash);
}

// Alias pentru compatibilitate
function verifikaParola($parola, $hash) {
    return verifyPassword($parola, $hash);
}

// ============================================================
// SESSION FLASH FUNCTIONS
// ============================================================

function setSuccess($mesaj) {
    $_SESSION['success'] = $mesaj;
}

function setError($mesaj) {
    $_SESSION['error'] = $mesaj;
}

// Alias pentru compatibilitate
function setEroare($mesaj) {
    setError($mesaj);
}

function getSuccess() {
    if (isset($_SESSION['success'])) {
        $mesaj = $_SESSION['success'];
        unset($_SESSION['success']);
        return $mesaj;
    }
    return null;
}

function getError() {
    if (isset($_SESSION['error'])) {
        $mesaj = $_SESSION['error'];
        unset($_SESSION['error']);
        return $mesaj;
    }
    return null;
}

// Alias pentru compatibilitate
function getEroare() {
    return getError();
}

// ============================================================
// REDIRECT FUNCTIONS
// ============================================================

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// ============================================================
// DOCTOR FUNCTIONS
// ============================================================

function calculeazaRatingMedic($medic_id, $conn) {
    $sql = "
        SELECT 
            AVG(rating) AS avg_rating, 
            COUNT(*) AS total
        FROM reviews 
        WHERE doctor_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $medic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return [
        'rating' => round($row['avg_rating'] ?? 0, 1),
        'reviews' => (int)($row['total'] ?? 0)
    ];
}

// ============================================================
// CSRF TOKEN FUNCTIONS
// ============================================================

function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function adminVerifyCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            http_response_code(403);
            die('❌ Token CSRF invalid.');
        }
    }
}
?>