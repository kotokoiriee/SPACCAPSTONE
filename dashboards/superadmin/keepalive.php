<?php
ini_set('session.gc_maxlifetime', 3600);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/auth_guard.php';
$_SESSION['last_activity'] = time();
session_write_close();
header('Content-Type: application/json');
echo json_encode(['ok' => true]);