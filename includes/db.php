<?php
mysqli_report(MYSQLI_REPORT_OFF);

$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'clubhub';

$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);
$db_error = false;

if (!$conn) {
    error_log('Club Hub database connection failed: ' . mysqli_connect_error());
    $db_error = true;
} elseif (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('Club Hub database charset setup failed: ' . mysqli_error($conn));
    $db_error = true;
}
