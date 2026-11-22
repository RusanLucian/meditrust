<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$doctorId = (int)($_GET['doctor_id'] ?? 0);
if ($doctorId > 0) {
    header("Location: " . BASE_URL . "medici/" . $doctorId . "/review", true, 301);
} else {
    header("Location: " . BASE_URL . "medici", true, 301);
}
exit;
