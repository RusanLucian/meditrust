<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CREDENTIALS
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');  // PAROLĂ GOALĂ!
define('DB_NAME', 'meditrust');

// CONECTARE LA DATABASE - PORT 3307!
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);

// Check conexiune
if ($conn->connect_error) {
    die("Conexiune eșuată: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// TIMEZONE
date_default_timezone_set('Europe/Bucharest');

// BASE URL
define('BASE_URL', 'http://localhost/meditrust/');

// TIPURI UTILIZATORI
define('PACIENT', 'pacient');
define('MEDIC', 'medic');
define('ADMIN', 'admin');
?>