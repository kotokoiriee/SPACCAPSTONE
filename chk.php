<?php
session_start();
$bid = $_SESSION['barangay_id'] ?? 1;
$conn = new mysqli('localhost','root','','spac_db');
$brgy = $conn->query("SELECT * FROM barangays WHERE barangay_id=$bid")->fetch_assoc();
$logo = $brgy['logo'] ?? '';
echo "logo length: ".strlen($logo)."<br>";
echo "starts with: ".substr($logo, 0, 30)."<br>";
if($logo) echo '<img src="'.$logo.'" style="width:80px;height:80px;border-radius:50%">';
else echo "NO LOGO";
?>
