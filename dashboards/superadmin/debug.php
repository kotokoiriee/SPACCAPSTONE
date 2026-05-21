<?php
session_start();
echo 'Session ID: ' . session_id() . '<br>';
$uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET';
echo 'user_id: ' . $uid . '<br>';
echo 'role: ' . $role;
?>
