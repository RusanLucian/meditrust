<?php
// ENVIRONMENT
define('ENV', 'dev'); // 'dev' sau 'prod'

// ERROR HANDLING
if (ENV === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// DATABASE CREDENTIALS
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'meditrust');

// MYSQLI ERROR MODE
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// CONNECT DATABASE
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// SET CHARSET
$conn->set_charset("utf8mb4");

// TIMEZONE
date_default_timezone_set('Europe/Bucharest');

// BASE URL
define('BASE_URL', 'http://localhost/meditrust/');