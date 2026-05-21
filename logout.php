<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', 'localhost');
session_start();
require_once 'config/db.php';

// ── Mark user as offline ─────────────────────────────
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $conn->query("UPDATE users SET is_online = 0, last_seen = NOW() WHERE user_id = $uid");
    $ip = $_SERVER['REMOTE_ADDR'];
    $conn->query("INSERT INTO audit_logs (user_id, action, ip_address) VALUES ($uid, 'LOGOUT', '$ip')");
}
// ─────────────────────────────────────────────────────

session_unset();
session_destroy();
header("Location: http://localhost/SPAC/index.php");
exit();
?>