<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ganesh_booking_db';

try {
    $conn = new mysqli($host, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check XAMPP MySQL and db.php settings.');
}
