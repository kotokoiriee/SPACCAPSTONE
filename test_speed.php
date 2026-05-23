<?php
$start = microtime(true);
$_SESSION['user_id'] = 1;
$_SESSION['full_name'] = 'Test';
$_SESSION['role'] = 'superadmin';

ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once __DIR__ . '/dashboards/superadmin/index.php';
ob_end_clean();

echo "Total dashboard load: " . round((microtime(true) - $start) * 1000) . "ms";
?>
