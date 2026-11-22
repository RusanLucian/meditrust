<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    header("Location: " . BASE_URL . "medici/" . $id, true, 301);
} else {
    header("Location: " . BASE_URL . "medici", true, 301);
}
exit;
