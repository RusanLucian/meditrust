<?php
require_once '../bootstrap.php';

requireLogin('login.php');

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_type = $_SESSION['user_type'] ?? '';
?>