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

$r = $conn->query("SELECT COUNT(*) as c FROM ayuda_records WHERE status = 'ongoing'");
$stats['ongoing_ayuda'] = $r ? (int)$r->fetch_assoc()['c'] : $stats['total_ayuda'];

$r = $conn->query("SELECT COUNT(*) as c FROM barangays WHERE status = 'active'");
$stats['active_barangays'] = $r ? (int)$r->fetch_assoc()['c'] : $stats['total_barangays'];

$r = $conn->query("SELECT COUNT(*) as c FROM scans");
$stats['total_scans'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

$r = $conn->query("SELECT COUNT(*) as c FROM alerts WHERE resolved = 0");
$stats['total_alerts'] = $r ? (int)$r->fetch_assoc()['c'] : 0;

// Per-barangay counts — no JOINs
$brgy_tmp = [];
$r = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name");
if ($r) while ($row = $r->fetch_assoc()) $brgy_tmp[$row['barangay_id']] = ['name'=>$row['name'], 'total'=>0];

$brgy_fam_tmp = $brgy_tmp;

$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM residents WHERE is_active=1 GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($brgy_tmp[$row['barangay_id']])) $brgy_tmp[$row['barangay_id']]['total'] = (int)$row['c']; }

$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM families GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($brgy_fam_tmp[$row['barangay_id']])) $brgy_fam_tmp[$row['barangay_id']]['total'] = (int)$row['c']; }

$stats['brgy_residents'] = array_values($brgy_tmp);
$stats['brgy_families']  = array_values($brgy_fam_tmp);
$stats['timestamp'] = date('Y-m-d H:i:s');

echo json_encode($stats);
