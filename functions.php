<?php

function esteLogat() {
    return isset($_SESSION['user_id']) &&
!empty($_SESSION['user_id']);
}

function getTipUtilizator() {
    return $_SESSION['tip_utilizator'] ?? null;
}

function esteAdmin() {
    return esteLogat() && getTipUtilizator() === 'admin';
}

function esteMedic() {
    return esteLogat() && getTipUtilizator() === 'medic';
}

function verificareLogin() {
    if (!esteLogat()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }
}

function requireLogin($redirect = '../auth/login.php') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireRole($role, $redirect = '../auth/login.php') {
    requireLogin($redirect);

    if (($_SESSION['user_type'] ?? 'patient') !== $role) {
        header('Location: ' . $redirect);
        exit;
    }
}

function executeQuery($sql, $conn) {
   $result = $conn->query($sql);
    if (!$result) {
        die("Eroare Query: " . $conn->error);  
    }
    return $result; 
}

function getRow($result) {
    return $result->fetch_assoc();
}

function getAll($result) {
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserById($conn, $user_id) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    return $user_stmt->get_result()->fetch_assoc();
}

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

function esteEmailValid($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function esteParolaValida($parola) {
    return strlen($parola) >= 6;
}

function sanitizeInput($input, $conn) {
    return $conn->real_escape_string(trim($input));
}

function hashParola($parola) {
    return password_hash($parola, PASSWORD_BCRYPT);
}

function verifiYParola($parola, $hash) {
    return password_verify($parola, $hash);
}

function setSuccess($mesaj) {
    $_SESSION['success'] = $mesaj;
}

function setEroare($mesaj) {
    $_SESSION['error'] = $mesaj;
}

function getSuccess() {
    if (isset($_SESSION['succes'])) {
        $mesaj = $_SESSION['succes'];
        unset($_SESSION['succes']);
        return $mesaj;
    }
    return null;
}

function getEroare() {
    if (isset($_SESSION['eroare'])) {
        $mesaj = $_SESSION['eroare'];
        unset($_SESSION['eroare']);
        return $mesaj;
    }
    return null;
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function calculeazaRatingMedic($medic_id, $conn) {
    $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total
FROM evaluari WHERE medic_id = medic_id";
    $result = executeQuery($sql, $conn);
    $row = getRow($result);
    return [
        'rating' => round($row['avg_rating'] ?? 0, 1),
        'evaluari' => $row['total'] ?? 0
    ];
}
?>