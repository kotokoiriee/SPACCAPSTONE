<?php
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);

if (session_status() === PHP_SESSION_NONE) {
    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (strpos($script, 'superadmin') !== false) {
        session_name('SPAC_SUPERADMIN');
    } elseif (strpos($script, 'cityhall') !== false) {
        session_name('SPAC_CITYHALL');
    } elseif (strpos($script, 'barangay') !== false) {
        session_name('SPAC_BARANGAY');
    } else {
        session_name('SPAC_SESSION');
    }

    session_set_cookie_params([
        'lifetime' => 3600,
        'path'     => '/',        // ← THIS is the key fix
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: http://localhost/SPAC/index.php");
        exit();
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        header("Location: http://localhost/SPAC/index.php");
        exit();
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}
?>