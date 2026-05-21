<?php
require_once '../../config/auth_guard.php';
require_role('barangay');
require_once '../../config/db.php';

// Release session lock immediately — this is a read-only JSON endpoint
session_write_close();

header('Content-Type: application/json');

$family_id = (int)($_GET['family_id'] ?? 0);
$bid       = (int)$_SESSION['barangay_id'];

$members = [];
$r = $conn->query("SELECT resident_id, full_name, birth_date, contact_number, relationship 
                   FROM residents 
                   WHERE family_id = $family_id AND barangay_id = $bid AND is_active = 1 
                   ORDER BY relationship = 'Head' DESC, full_name ASC");
if ($r) while ($row = $r->fetch_assoc()) $members[] = $row;

echo json_encode($members);