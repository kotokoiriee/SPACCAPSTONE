<?php
require_once '../../config/auth_guard.php';
require_role('cityhall');
?>
<!DOCTYPE html>
<html>
<head>
    <title>City Hall Dashboard – SPAC</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0a1628; color: #ccd6f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .box { text-align: center; }
        h1 { font-size: 28px; margin-bottom: 10px; }
        p { color: #7a9cc6; }
        a { color: #4a9eff; }
    </style>
</head>
<body>
<div class="box">
    <h1>👋 Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <p>City Hall Dashboard — coming soon</p>
    <br>
    <a href="/SPAC/logout.php">Logout</a>
</div>
</body>
</html>