<?php
require __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    http_response_code(400);
    exit('Kërkesa nuk është e vlefshme.');
}

$_SESSION = array();
session_destroy();
header('Location: login.php');
exit;
