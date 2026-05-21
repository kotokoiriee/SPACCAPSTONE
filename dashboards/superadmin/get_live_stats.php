<?php
require_once '../../config/auth_guard.php';
require_role('superadmin');
session_write_close();
require_once '../../config/db.php';

header('Content-Type: application/json');

$stats = [];

$r = $conn->query("SELECT COUNT(*) as c FROM residents WHERE is_active = 1");
$stats['total_residents'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM families");
$stats['total_families'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM barangays");
$stats['total_barangays'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM users");
$stats['total_users'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM ayuda_records");
$stats['total_ayuda'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$col = $conn->query("SHOW COLUMNS FROM ayuda_records LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM ayuda_records WHERE status = 'ongoing'");
    $stats['ongoing_ayuda'] = $r ? (int)$r->fetch_assoc()['c'] : 0;
} else {
    $stats['ongoing_ayuda'] = $stats['total_ayuda'];
}

$col = $conn->query("SHOW COLUMNS FROM barangays LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM barangays WHERE status = 'active'");
    $stats['active_barangays'] = $r ? (int)$r->fetch_assoc()['c'] : 0;
} else {
    $stats['active_barangays'] = $stats['total_barangays'];
}

$r = $conn->query("SELECT COUNT(*) as c FROM scans");
$stats['total_scans'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$tbl = $conn->query("SHOW TABLES LIKE 'alerts'");
if ($tbl && $tbl->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM alerts WHERE resolved = 0");
    $stats['total_alerts'] = $r ? (int)$r->fetch_assoc()['c'] : 0;
} else {
    $stats['total_alerts'] = 0;
}

// Per-barangay resident + family counts for the analytics modals
$brgy_residents = [];
$r = $conn->query("SELECT b.barangay_id, b.name, COUNT(r.resident_id) as total
                   FROM barangays b
                   LEFT JOIN residents r ON r.barangay_id = b.barangay_id AND r.is_active = 1
                   GROUP BY b.barangay_id, b.name ORDER BY b.name");
if ($r) while ($row = $r->fetch_assoc()) $brgy_residents[] = $row;
$stats['brgy_residents'] = $brgy_residents;

$brgy_families = [];
$r = $conn->query("SELECT b.barangay_id, b.name, COUNT(f.id) as total
                   FROM barangays b
                   LEFT JOIN families f ON f.barangay_id = b.barangay_id
                   GROUP BY b.barangay_id, b.name ORDER BY b.name");
if ($r) while ($row = $r->fetch_assoc()) $brgy_families[] = $row;
$stats['brgy_families'] = $brgy_families;

$stats['timestamp'] = date('Y-m-d H:i:s');

echo json_encode($stats);