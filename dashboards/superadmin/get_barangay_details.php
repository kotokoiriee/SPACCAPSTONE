<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../../config/auth_guard.php';
require_role('superadmin');
require_once '../../config/db.php';

if (!isset($conn) || !$conn) {
    die(json_encode(['error' => 'DB connection failed']));
}

header('Content-Type: application/json');

$bid = isset($_GET['barangay_id']) ? (int)$_GET['barangay_id'] : 0;

if (!$bid) {
    echo json_encode(['error' => 'Missing barangay_id']);
    exit;
}

// ── Barangay meta ───────────────────────────────────────────────A──
$brgy = null;
$r = $conn->query(
    "SELECT b.barangay_id, b.name, b.captain_name, b.district, b.logo, b.captain_photo,
            COALESCE(b.area_label, 'Zone') AS area_label,
            o.photo AS official_captain_photo
     FROM barangays b
     LEFT JOIN barangay_officials o 
        ON o.barangay_id = b.barangay_id 
        AND o.position = 'Barangay Captain' 
        AND o.is_active = 1
     WHERE b.barangay_id = $bid
     LIMIT 1"
);
if ($r && $r->num_rows > 0) $brgy = $r->fetch_assoc();
// ── Families with live member_count ──────────────────────────────
$families = [];
$r = $conn->query(
    "SELECT f.id, f.head_name, f.zone_number, f.address,
            (SELECT COUNT(*) FROM residents r2
             WHERE r2.family_id = f.id AND r2.is_active = 1) AS member_count
     FROM families f
     WHERE f.barangay_id = $bid
     ORDER BY f.zone_number ASC, f.head_name ASC
     LIMIT 500"
);
if ($r) while ($row = $r->fetch_assoc()) $families[] = $row;

// ── Residents ─────────────────────────────────────────────────────
$residents = [];
$r = $conn->query(
    "SELECT r.resident_id, r.full_name, r.zone_number,
            r.birth_date, r.contact_number, r.relationship,
            f.head_name AS family_head
     FROM residents r
     LEFT JOIN families f ON f.id = r.family_id
     WHERE r.barangay_id = $bid AND r.is_active = 1
     ORDER BY r.full_name ASC
     LIMIT 1000"
);
if ($r) while ($row = $r->fetch_assoc()) $residents[] = $row;

// ── Zone summary ──────────────────────────────────────────────────
$zones = [];
$r = $conn->query(
    "SELECT f.zone_number,
            COUNT(DISTINCT f.id)            AS families,
            COUNT(DISTINCT res.resident_id) AS residents
     FROM families f
     LEFT JOIN residents res ON res.family_id = f.id AND res.is_active = 1
     WHERE f.barangay_id = $bid
     GROUP BY f.zone_number
     ORDER BY f.zone_number ASC"
);
if ($r) while ($row = $r->fetch_assoc()) $zones[] = $row;

// Use official captain photo if available, fallback to barangay captain_photo
if ($brgy && !empty($brgy['official_captain_photo'])) {
    $brgy['captain_photo'] = $brgy['official_captain_photo'];
}

echo json_encode([
    'barangay'  => $brgy,
    'families'  => $families,
    'residents' => $residents,
    'zones'     => $zones,
    'counts'    => [
        'families'  => count($families),
        'residents' => count($residents),
    ],
    'timestamp' => date('Y-m-d H:i:s'),
]);