<?php

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'college_event_db';

try {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
    $mysqli->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check config.php and confirm database.sql was imported.');
}
