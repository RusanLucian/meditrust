<?php
require_once dirname(__DIR__) . '/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    header("Location: " . BASE_URL . "admin/users/" . $id . "/edit", true, 301);
} else {
    header("Location: " . BASE_URL . "admin/users", true, 301);
}
exit;
