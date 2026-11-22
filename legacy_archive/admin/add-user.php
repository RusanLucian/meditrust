<?php
require_once dirname(__DIR__) . '/bootstrap.php';
header("Location: " . BASE_URL . "admin/users/create", true, 301);
exit;
