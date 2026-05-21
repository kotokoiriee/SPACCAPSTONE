<?php
require_once '../../config/auth_guard.php';
require_role('superadmin');
session_write_close();
require_once '../../config/db.php';

header('Content-Type: application/json');

$users = [];
$r = $conn->query("SELECT user_id, is_online, last_seen FROM users");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $users[$row['user_id']] = [
            'is_online' => (int)$row['is_online'],
            'last_seen' => $row['last_seen']
        ];
    }
}
echo json_encode($users);