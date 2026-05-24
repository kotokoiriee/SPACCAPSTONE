<?php
require_once "../../config/db.php";
$conn->query("ALTER TABLE residents ADD INDEX idx_barangay_id (barangay_id)");
$conn->query("ALTER TABLE families ADD INDEX idx_barangay_id (barangay_id)");
$conn->query("ALTER TABLE users ADD INDEX idx_barangay_id (barangay_id)");
$conn->query("ALTER TABLE scans ADD INDEX idx_barangay_id (barangay_id)");
$conn->query("ALTER TABLE ayuda_records ADD INDEX idx_barangay_id (barangay_id)");
echo "Indexes added!";
?>