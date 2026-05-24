<?php require_once '../../config/auth_guard.php'; require_role('superadmin'); require_once '../../config/db.php';
// ── Handle POST actions ─────────────────────────────────────────
$action_message = '';
$action_type    = '';

// ADD BARANGAY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_barangay') {
    $name           = trim($conn->real_escape_string($_POST['name'] ?? ''));
    $email          = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $district       = trim($conn->real_escape_string($_POST['district'] ?? ''));
    $contact_number = trim($conn->real_escape_string($_POST['contact_number'] ?? ''));
    $is_pilot       = isset($_POST['is_pilot']) ? 1 : 0;
    $status         = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $address        = trim($conn->real_escape_string($_POST['address'] ?? ''));
    $captain_name   = trim($conn->real_escape_string($_POST['captain_name'] ?? ''));
    $captain_since  = trim($conn->real_escape_string($_POST['captain_since'] ?? ''));
    $population     = !empty($_POST['population']) ? (int)$_POST['population'] : 'NULL';
    $land_area      = trim($conn->real_escape_string($_POST['land_area'] ?? ''));
    $founded_year   = !empty($_POST['founded_year']) ? (int)$_POST['founded_year'] : 'NULL';

    if ($name && $email) {
        $pop_val = ($population === 'NULL') ? 'NULL' : $population;
        $fy_val  = ($founded_year === 'NULL') ? 'NULL' : $founded_year;
        $sql = "INSERT INTO barangays (name, email, district, contact_number, is_pilot, status, address, captain_name, captain_since, population, land_area, founded_year)
                VALUES ('$name', '$email', '$district', '$contact_number', $is_pilot, '$status', '$address', '$captain_name', '$captain_since', $pop_val, '$land_area', $fy_val)";
        try {
            if ($conn->query($sql)) {
                $action_message = "Barangay \"$name\" added successfully!";
                $action_type    = 'success';
            } else {
                $sql2 = "INSERT INTO barangays (name, email, district, contact_number, is_pilot, status)
                         VALUES ('$name', '$email', '$district', '$contact_number', $is_pilot, '$status')";
                if ($conn->query($sql2)) {
                    $action_message = "Barangay \"$name\" added successfully!";
                    $action_type    = 'success';
                } else {
                    $action_message = "Error: " . $conn->error;
                    $action_type    = 'error';
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $action_message = "A barangay with the email \"$email\" already exists.";
            } else {
                try {
                    $sql2 = "INSERT INTO barangays (name, email, district, contact_number, is_pilot, status)
                             VALUES ('$name', '$email', '$district', '$contact_number', $is_pilot, '$status')";
                    if ($conn->query($sql2)) {
                        $action_message = "Barangay \"$name\" added successfully!";
                        $action_type    = 'success';
                    }
                } catch (Exception $e2) {
                    $action_message = "Database error: " . $e2->getMessage();
                }
            }
            if (!$action_type) $action_type = 'error';
        }
    } else {
        $action_message = "Barangay name and email are required.";
        $action_type    = 'error';
    }
}

// ADD USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $full_name    = trim($conn->real_escape_string($_POST['full_name'] ?? ''));
    $email        = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password     = $_POST['password'] ?? '';
    $role         = $_POST['role'] ?? 'barangay';
    $barangay_id  = !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : 'NULL';
    $is_active    = isset($_POST['is_active']) ? 1 : 0;

    if ($full_name && $email && $password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $password_hash = $conn->real_escape_string($password_hash);
        $brgy_val      = $barangay_id === 'NULL' ? 'NULL' : $barangay_id;
        $sql = "INSERT INTO users (full_name, email, password_hash, role, barangay_id, is_active)
                VALUES ('$full_name', '$email', '$password_hash', '$role', $brgy_val, $is_active)";
        if ($conn->query($sql)) {
            $action_message = "User \"$full_name\" created successfully!";
            $action_type    = 'success';
        } else {
            $action_message = "Error: " . $conn->error;
            $action_type    = 'error';
        }
    } else {
        $action_message = "Full name, email and password are required.";
        $action_type    = 'error';
    }
}

// ADD AYUDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_ayuda') {
    $ayuda_name  = trim($conn->real_escape_string($_POST['ayuda_name'] ?? ''));
    $ayuda_type  = trim($conn->real_escape_string($_POST['ayuda_type'] ?? ''));
    $source      = ($_POST['source'] ?? '') === 'barangay' ? 'barangay' : 'cityhall';
    $barangay_id = !empty($_POST['barangay_id']) ? (int)$_POST['barangay_id'] : 'NULL';
    $start_date  = $_POST['start_date'] ?? '';
    $end_date    = $_POST['end_date'] ?? '';

    if ($ayuda_name && $start_date && $end_date) {
        $col_check   = $conn->query("SHOW COLUMNS FROM ayuda_records LIKE 'barangay_id'");
        $col_info    = $col_check ? $col_check->fetch_assoc() : null;
        $allows_null = $col_info && strtoupper($col_info['Null']) === 'YES';
        if (!$allows_null && $barangay_id === 'NULL') {
            $action_message = "Please select a Barangay, or allow City Hall-level ayuda.";
            $action_type    = 'error';
        } else {
            $brgy_val = $barangay_id === 'NULL' ? 'NULL' : $barangay_id;
            $sql = "INSERT INTO ayuda_records (ayuda_name, ayuda_type, source, barangay_id, start_date, end_date)
                    VALUES ('$ayuda_name', '$ayuda_type', '$source', $brgy_val, '$start_date', '$end_date')";
            try {
                if ($conn->query($sql)) {
                    $action_message = "Ayuda \"$ayuda_name\" added successfully!";
                    $action_type    = 'success';
                } else {
                    $action_message = "Error: " . $conn->error;
                    $action_type    = 'error';
                }
            } catch (mysqli_sql_exception $e) {
                $action_message = "Database error: " . $e->getMessage();
                $action_type    = 'error';
            }
        }
    } else {
        $action_message = "Ayuda name, start date, and end date are required.";
        $action_type    = 'error';
    }
}

// EDIT AYUDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_ayuda') {
    $record_id     = (int)($_POST['record_id'] ?? 0);
    $ayuda_name    = trim($conn->real_escape_string($_POST['ayuda_name'] ?? ''));
    $ayuda_type    = trim($conn->real_escape_string($_POST['ayuda_type'] ?? ''));
    $start_date    = $_POST['start_date'] ?? '';
    $mark_complete = isset($_POST['mark_complete']) ? 1 : 0;
    $new_status    = $mark_complete ? 'completed' : 'ongoing';
    $end_date      = $mark_complete ? date('Y-m-d') : ($_POST['end_date'] ?? '');
    $end_date_escaped = $conn->real_escape_string($end_date);

    if ($record_id && $ayuda_name) {
        $sql = "UPDATE ayuda_records SET ayuda_name = '$ayuda_name', ayuda_type = '$ayuda_type',
                start_date = '$start_date', end_date = '$end_date_escaped', status = '$new_status'
                WHERE record_id = $record_id";
        if ($conn->query($sql)) {
            $action_message = "Ayuda \"$ayuda_name\" updated successfully!" . ($mark_complete ? ' Moved to Assistance History.' : '');
            $action_type    = 'success';
        } else {
            $action_message = "Error: " . $conn->error;
            $action_type    = 'error';
        }
    } else {
        $action_message = "Record ID and ayuda name are required.";
        $action_type    = 'error';
    }
}

// UPDATE LOGIN IMAGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_login_image') {
    if (!empty($_FILES['login_image']['tmp_name'])) {
        $ext     = strtolower(pathinfo($_FILES['login_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
       if (in_array($ext, $allowed)) {
    $tmpPath = $_FILES['login_image']['tmp_name'];

    // Resize & compress before base64 encoding
    $info = getimagesize($tmpPath);
    $srcW = $info[0]; $srcH = $info[1];

    // Max width 1200px
    $maxW = 1200;
    if ($srcW > $maxW) {
        $ratio = $maxW / $srcW;
        $newW  = $maxW;
        $newH  = (int)($srcH * $ratio);
    } else {
        $newW = $srcW;
        $newH = $srcH;
    }

    // Create image resource from any type
    $src = imagecreatefromstring(file_get_contents($tmpPath));
    $dst = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

    // Capture as JPEG at 70% quality
    ob_start();
    imagejpeg($dst, null, 70);
    $imgdata = ob_get_clean();
    imagedestroy($src);
    imagedestroy($dst);

    $mime      = 'image/jpeg';
    $login_img = "data:$mime;base64," . base64_encode($imgdata);
            $login_img_escaped = $conn->real_escape_string($login_img);
            $conn->query("INSERT INTO system_settings (setting_key, setting_value)
                          VALUES ('login_image','$login_img_escaped')
                          ON DUPLICATE KEY UPDATE setting_value='$login_img_escaped'");
            $action_message = 'Login page image updated.';
            $action_type    = 'success';
        } else {
            $action_message = 'Invalid file type. Please upload JPG, PNG, GIF, or WEBP.';
            $action_type    = 'error';
        }
    }
}

// UPDATE SYSTEM LOGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_system_logo') {
    if (!empty($_FILES['system_logo']['tmp_name'])) {
        $ext     = strtolower(pathinfo($_FILES['system_logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $imgdata = file_get_contents($_FILES['system_logo']['tmp_name']);
            $mime    = mime_content_type($_FILES['system_logo']['tmp_name']);
            $logo    = $conn->real_escape_string("data:$mime;base64," . base64_encode($imgdata));
            $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('system_logo','$logo') ON DUPLICATE KEY UPDATE setting_value='$logo'");
$system_logo    = "data:$mime;base64," . base64_encode($imgdata);
$action_message = 'Logo updated.';
$action_type    = 'success';
        }
    }
}

// UPDATE ADMIN PROFILE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($conn->real_escape_string($_POST['full_name'] ?? ''));
    $email     = trim($conn->real_escape_string($_POST['email'] ?? ''));
    $password  = $_POST['new_password'] ?? '';
    $user_id   = (int)$_SESSION['user_id'];

    if ($full_name && $email) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $hash = $conn->real_escape_string($hash);
            $sql  = "UPDATE users SET full_name='$full_name', email='$email', password_hash='$hash' WHERE user_id=$user_id";
        } else {
            $sql = "UPDATE users SET full_name='$full_name', email='$email' WHERE user_id=$user_id";
        }
        if ($conn->query($sql)) {
            $_SESSION['full_name'] = $full_name;
            $action_message = "Profile updated successfully!";
            $action_type    = 'success';
        } else {
            $action_message = "Error: " . $conn->error;
            $action_type    = 'error';
        }
    } else {
        $action_message = "Name and email are required.";
        $action_type    = 'error';
    }
}

$system_logo = '';
$r = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'system_logo'");
if ($r && $row = $r->fetch_assoc()) $system_logo = $row['setting_value'];

$login_image = '';
$r = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'login_image'");
if ($r && $row = $r->fetch_assoc()) $login_image = $row['setting_value'];

// ── Stats ──────────────────────────────────────────────────────
$total_residents = $conn->query("SELECT COUNT(*) as c FROM residents WHERE is_active = 1")->fetch_assoc()['c'];
$total_barangays = $conn->query("SELECT COUNT(*) as c FROM barangays")->fetch_assoc()['c'];
$total_ayuda     = $conn->query("SELECT COUNT(*) as c FROM ayuda_records")->fetch_assoc()['c'];
$total_users     = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];

$total_families = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM families");
if ($r) $total_families = $r->fetch_assoc()['c'];

$active_barangays = $total_barangays;
$col = $conn->query("SHOW COLUMNS FROM barangays LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM barangays WHERE status = 'active'");
    if ($r) $active_barangays = $r->fetch_assoc()['c'];
}

$ongoing_ayuda = $total_ayuda;
$col = $conn->query("SHOW COLUMNS FROM ayuda_records LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM ayuda_records WHERE status = 'ongoing'");
    if ($r) $ongoing_ayuda = $r->fetch_assoc()['c'];
}

$completed_ayuda = 0;
$col = $conn->query("SHOW COLUMNS FROM ayuda_records LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM ayuda_records WHERE status = 'completed'");
    if ($r) $completed_ayuda = $r->fetch_assoc()['c'];
}

$total_scans = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM scans");
if ($r) $total_scans = $r->fetch_assoc()['c'];

$total_alerts = 0;
$tbl = $conn->query("SHOW TABLES LIKE 'alerts'");
if ($tbl && $tbl->num_rows > 0) {
    $r = $conn->query("SELECT COUNT(*) as c FROM alerts WHERE resolved = 0");
    if ($r) $total_alerts = $r->fetch_assoc()['c'];
}

// ── Drill-down data ────────────────────────────────────────────
$brgy_residents = [];
$r = $conn->query("SELECT b.name, COUNT(r.resident_id) as total FROM barangays b
                   LEFT JOIN residents r ON r.barangay_id = b.barangay_id AND r.is_active = 1
                   GROUP BY b.barangay_id, b.name ORDER BY b.name");
if ($r) while ($row = $r->fetch_assoc()) $brgy_residents[] = $row;

$brgy_families = [];
$r = $conn->query("SELECT b.name, COUNT(f.id) as total FROM barangays b
                   LEFT JOIN families f ON f.barangay_id = b.barangay_id
                   GROUP BY b.barangay_id, b.name ORDER BY b.name");
if ($r) while ($row = $r->fetch_assoc()) $brgy_families[] = $row;

$active_brgy_list = [];
$col = $conn->query("SHOW COLUMNS FROM barangays LIKE 'status'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT name FROM barangays WHERE status = 'active' ORDER BY name");
} else {
    $r = $conn->query("SELECT name FROM barangays ORDER BY name");
}
if ($r) while ($row = $r->fetch_assoc()) $active_brgy_list[] = $row;

$_brgy_tmp = [];
$r = $conn->query("SELECT barangay_id, name, COALESCE(status,'active') as status, COALESCE(district,'') as district, COALESCE(email,'') as email, COALESCE(contact_number,'') as contact_number, COALESCE(address,'') as address, COALESCE(captain_name,'') as captain_name, COALESCE(captain_since,'') as captain_since, COALESCE(land_area,'') as land_area, COALESCE(founded_year,'') as founded_year, COALESCE(is_pilot,0) as is_pilot FROM barangays ORDER BY name");
if ($r) while ($row = $r->fetch_assoc()) $_brgy_tmp[$row['barangay_id']] = $row;
$r = $conn->query("SELECT barangay_id, COUNT(*) as cnt FROM residents WHERE is_active=1 GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_brgy_tmp[$row['barangay_id']])) $_brgy_tmp[$row['barangay_id']]['total_residents'] = $row['cnt']; }
$r = $conn->query("SELECT barangay_id, COUNT(*) as cnt FROM families GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_brgy_tmp[$row['barangay_id']])) $_brgy_tmp[$row['barangay_id']]['total_families'] = $row['cnt']; }
foreach ($_brgy_tmp as &$_b) { $_b['total_residents'] = $_b['total_residents'] ?? 0; $_b['total_families'] = $_b['total_families'] ?? 0; $_b['population'] = $_b['total_residents']; }
unset($_b);
$all_brgy_list = array_values($_brgy_tmp);
$ongoing_ayuda_list = [];
$has_status = $conn->query("SHOW COLUMNS FROM ayuda_records LIKE 'status'")->num_rows > 0;
$where = $has_status ? "WHERE ar.status = 'ongoing'" : "";
$r = $conn->query("SELECT ar.record_id, ar.ayuda_name, ar.ayuda_type, ar.source, ar.start_date, ar.end_date,
                   COALESCE(b.name, 'City Hall') as origin FROM ayuda_records ar
                   LEFT JOIN barangays b ON b.barangay_id = ar.barangay_id $where ORDER BY ar.record_id DESC");
if ($r) while ($row = $r->fetch_assoc()) $ongoing_ayuda_list[] = $row;

$assistance_history = [];
$where_completed = $has_status ? "WHERE ar.status = 'completed'" : "WHERE 1=0";
$r = $conn->query("SELECT ar.record_id, ar.ayuda_name, ar.ayuda_type, ar.source, ar.start_date, ar.end_date,
                   COALESCE(b.name, 'City Hall') as origin FROM ayuda_records ar
                   LEFT JOIN barangays b ON b.barangay_id = ar.barangay_id $where_completed ORDER BY ar.end_date DESC, ar.record_id DESC");
if ($r) while ($row = $r->fetch_assoc()) $assistance_history[] = $row;

$scan_breakdown = [];
$col = $conn->query("SHOW COLUMNS FROM scans LIKE 'scan_type'");
if ($col && $col->num_rows > 0) {
    $r = $conn->query("SELECT scan_type, COUNT(*) as total FROM scans GROUP BY scan_type");
    if ($r) while ($row = $r->fetch_assoc()) $scan_breakdown[] = $row;
}

$alerts_list = [];
$tbl = $conn->query("SHOW TABLES LIKE 'alerts'");
if ($tbl && $tbl->num_rows > 0) {
    $r = $conn->query("SELECT message, severity, created_at FROM alerts WHERE resolved = 0 ORDER BY created_at DESC LIMIT 50");
    if ($r) while ($row = $r->fetch_assoc()) $alerts_list[] = $row;
}

$all_barangays = [];
$r = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name");
if ($r) while ($row = $r->fetch_assoc()) $all_barangays[] = $row;

$report_barangays = [];
$_rpt_tmp = [];
$r = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name");
if ($r) while ($row = $r->fetch_assoc()) $_rpt_tmp[$row['barangay_id']] = ['name'=>$row['name'], 'total_residents'=>0, 'total_families'=>0, 'total_scans'=>0, 'total_ayuda'=>0];
$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM residents GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_rpt_tmp[$row['barangay_id']])) $_rpt_tmp[$row['barangay_id']]['total_residents'] = $row['c']; }
$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM families GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_rpt_tmp[$row['barangay_id']])) $_rpt_tmp[$row['barangay_id']]['total_families'] = $row['c']; }
$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM scans GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_rpt_tmp[$row['barangay_id']])) $_rpt_tmp[$row['barangay_id']]['total_scans'] = $row['c']; }
$r = $conn->query("SELECT barangay_id, COUNT(*) as c FROM ayuda_records GROUP BY barangay_id");
if ($r) while ($row = $r->fetch_assoc()) { if (isset($_rpt_tmp[$row['barangay_id']])) $_rpt_tmp[$row['barangay_id']]['total_ayuda'] = $row['c']; }
$report_barangays = array_values($_rpt_tmp);

$users_all = [];
$r = $conn->query("SELECT u.user_id, u.full_name, u.email, u.role, u.is_active, u.created_at,
                   b.name as barangay_name FROM users u
                   LEFT JOIN barangays b ON b.barangay_id = u.barangay_id
                   ORDER BY u.role, u.full_name");
if ($r) while ($row = $r->fetch_assoc()) $users_all[] = $row;

$recent_users = [];
$r = $conn->query("SELECT u.full_name, u.email, u.role, u.is_active, u.created_at,
                   b.name as barangay_name FROM users u
                   LEFT JOIN barangays b ON b.barangay_id = u.barangay_id
                   ORDER BY u.created_at DESC LIMIT 10");
if ($r) while ($row = $r->fetch_assoc()) $recent_users[] = $row;

$admin_info = [];
$r = $conn->query("SELECT user_id, full_name, email, role, created_at FROM users WHERE user_id = " . (int)$_SESSION['user_id']);
if ($r) $admin_info = $r->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <title>Super Admin – SPAC</title>
    <style>
        /* ── WHITE + NAVY THEME ─────────────────────────────── */
        :root {
            --white:      #ffffff;
            --surface:    #ffffff;
            --surface-2:  #f1f4f8;
            --border:     #e2e8f0;
            --border-2:   #cbd5e1;
            --muted:      #64748b;
            --text:       #1e293b;
            --navy:       #0f172a;
            --navy-mid:   #1e3a5f;
            --navy-light: #eef2f7;
            --red:        #dc2626;
            --red-l:      #fef2f2;
            --green:      #16a34a;
            --green-l:    #f0fdf4;
            --amber:      #d97706;
            --amber-l:    #fffbeb;
            --blue:       #1d4ed8;
            --blue-l:     #eff6ff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--white); display: flex; min-height: 100vh; color: var(--text); font-size: 14px; line-height: 1.5; }

        /* ── SIDEBAR ── */
        .sidebar { width: 232px; background: var(--white); min-height: 100vh; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100; }
        .sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
        .sidebar-logo h1 { color: var(--navy); font-size: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
        .sidebar-logo p { color: var(--muted); font-size: 11px; margin-top: 2px; font-weight: 400; }
        .sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; }
        .sidebar-menu::-webkit-scrollbar { width: 0; }
        .menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }
        .menu-item { display: flex; align-items: center; gap: 10px; padding: 8px 20px; color: var(--muted); font-size: 13px; font-weight: 400; transition: all 0.15s; cursor: pointer; background: none; border: none; width: 100%; text-align: left; font-family: 'DM Sans', sans-serif; text-decoration: none; }
        .menu-item:hover { color: var(--text); background: var(--surface-2); }
        .menu-item.active { color: var(--navy); font-weight: 500; background: var(--navy-light); }
        .menu-item.active .menu-dot { background: var(--navy); }
        .menu-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--border); flex-shrink: 0; transition: background 0.15s; }
        .menu-item:hover .menu-dot { background: var(--text); }
        .menu-badge { margin-left: auto; background: var(--surface-2); color: var(--muted); font-size: 11px; font-weight: 500; padding: 1px 7px; border-radius: 20px; font-family: 'DM Mono', monospace; }
        .menu-badge.alert { background: var(--red-l); color: var(--red); }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
        .sidebar-footer a { display: flex; align-items: center; gap: 10px; color: var(--muted); text-decoration: none; font-size: 13px; transition: color 0.15s; }
        .sidebar-footer a:hover { color: var(--text); }

        /* ── MAIN ── */
        .main { margin-left: 232px; flex: 1; display: flex; flex-direction: column; }
        .topbar { background: var(--white); padding: 0 28px; height: 56px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10; }
        .topbar-title { color: var(--navy); font-size: 14px; font-weight: 500; }
        .topbar-date { color: var(--muted); font-size: 12px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .role-chip { background: var(--navy-light); color: var(--navy-mid); font-size: 11px; font-weight: 500; padding: 4px 10px; border-radius: 20px; border: 1px solid var(--border); }
        .avatar-btn { width: 32px; height: 32px; background: var(--navy); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 500; cursor: pointer; border: none; font-family: 'DM Sans', sans-serif; transition: opacity 0.15s; }
        .avatar-btn:hover { opacity: 0.85; }

        /* ── CONTENT ── */
        .content { padding: 24px 28px; }
        .page-header { margin-bottom: 20px; }
        .page-header h2 { font-size: 18px; font-weight: 500; color: var(--navy); }
        .page-header p { color: var(--muted); font-size: 13px; margin-top: 2px; }

        /* ── STAT CARDS ── */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
        .stats-grid-analytics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .stat-card { background: var(--white); border-radius: 8px; padding: 18px 20px; border: 1px solid var(--border); }
        .stat-card.clickable { cursor: pointer; transition: border-color 0.15s; }
        .stat-card.clickable:hover { border-color: var(--navy); }
        .stat-num { font-size: 28px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; line-height: 1; }
        .stat-label { color: var(--muted); font-size: 12px; margin-top: 6px; }
        .stat-hint { color: var(--navy-mid); font-size: 11px; margin-top: 2px; opacity: 0.6; }
        .stat-card.danger { border-color: var(--red-l); }
        .stat-card.danger .stat-num { color: var(--red); }

        /* ── SECTION LABEL ── */
        .sec-label { font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; }

        /* ── QUICK ACTIONS ── */
        .actions-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px; }
        .action-btn { background: var(--white); border: 1px solid var(--border); border-radius: 8px; padding: 14px 16px; text-align: left; cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif; width: 100%; }
        .action-btn:hover { border-color: var(--navy); background: var(--navy-light); }
        .action-btn h3 { font-size: 13px; font-weight: 500; color: var(--navy); }
        .action-btn p { font-size: 12px; color: var(--muted); margin-top: 2px; }

        /* ── INFO CARD ── */
        .info-card { background: var(--white); border-radius: 8px; border: 1px solid var(--border); padding: 4px 20px; margin-bottom: 14px; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--muted); }
        .info-value { color: var(--text); font-weight: 500; }

        /* ── MODAL ── */
        .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.35); z-index: 500; align-items: center; justify-content: center; }
        .modal-backdrop.open { display: flex; }
        .modal { background: var(--white); border-radius: 10px; width: 90%; max-width: 560px; max-height: 88vh; display: flex; flex-direction: column; border: 1px solid var(--border); overflow: hidden; animation: slideUp 0.2s ease; }
        .modal.modal-wide { max-width: 800px; }
        .modal.modal-xl   { max-width: 960px; }
        @keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .modal-header h3 { color: var(--navy); font-size: 14px; font-weight: 500; }
        .modal-close { background: none; border: none; cursor: pointer; color: var(--muted); font-size: 18px; line-height: 1; padding: 2px 6px; border-radius: 4px; transition: background 0.15s; }
        .modal-close:hover { background: var(--surface-2); }
        .modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }

        /* ── FORM ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { color: var(--text); font-size: 12px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; color: var(--text); outline: none; font-family: 'DM Sans', sans-serif; transition: border-color 0.15s; background: var(--white); }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--navy); }
        .form-group .hint { color: var(--muted); font-size: 11px; }
        .form-sub-label { font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid var(--border); }
        .checkbox-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; }
        .checkbox-row input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--navy); cursor: pointer; }
        .checkbox-row label { font-size: 13px; color: var(--text); cursor: pointer; }
        .form-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; border: 1px solid var(--border); transition: all 0.15s; font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: var(--navy); color: #fff; border-color: var(--navy); }
        .btn-primary:hover { background: var(--navy-mid); border-color: var(--navy-mid); }
        .btn-secondary { background: var(--white); color: var(--text); }
        .btn-secondary:hover { background: var(--surface-2); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .btn-ghost { background: transparent; color: var(--muted); border-color: transparent; font-size: 12px; padding: 6px 12px; }
        .btn-ghost:hover { background: var(--surface-2); color: var(--text); }

        .req { color: var(--red); }
        .pw-wrap { position: relative; }
        .pw-wrap input { width: 100%; padding-right: 38px; }
        .pw-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); font-size: 14px; }

        /* ── ALERT BANNER ── */
        .alert-banner { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: center; gap: 8px; border: 1px solid transparent; }
        .alert-banner.success { background: var(--green-l); color: var(--green); border-color: #bbf7d0; }
        .alert-banner.error   { background: var(--red-l); color: var(--red); border-color: #fecaca; }

        /* ── DATA TABLE ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .data-table th { text-align: left; padding: 8px 12px; color: var(--muted); font-size: 11px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; border-bottom: 1px solid var(--border); background: var(--surface-2); }
        .data-table td { padding: 10px 12px; border-bottom: 1px solid var(--border); color: var(--text); }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: var(--surface-2); }
        .data-table .num { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--navy); }
        .modal-empty { text-align: center; padding: 32px; color: var(--muted); font-size: 13px; }

        /* ── TAGS ── */
        .tag { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 500; }
        .tag-active     { background: var(--green-l); color: var(--green); }
        .tag-inactive   { background: var(--red-l); color: var(--red); }
        .tag-navy       { background: var(--navy-light); color: var(--navy-mid); }
        .tag-superadmin { background: #f0ebfe; color: #553c9a; }
        .tag-cityhall   { background: var(--blue-l); color: var(--blue); }
        .tag-barangay   { background: var(--navy-light); color: var(--navy-mid); }
        .tag-mayor      { background: var(--amber-l); color: var(--amber); }
        .tag-pilot      { background: var(--blue-l); color: var(--blue); font-size: 10px; }
        .source-tag { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 500; }
        .source-tag.city { background: var(--blue-l); color: var(--blue); }
        .source-tag.brgy { background: var(--green-l); color: var(--green); }
        .sev-high   { color: var(--red); font-weight: 500; }
        .sev-medium { color: var(--amber); font-weight: 500; }
        .sev-low    { color: var(--muted); font-weight: 500; }

        /* ── SCAN BAR ── */
        .scan-bar-wrap { margin-top: 4px; height: 4px; background: var(--border); border-radius: 4px; overflow: hidden; }
        .scan-bar { height: 100%; background: var(--navy); border-radius: 4px; }

        /* ── EDIT AYUDA ── */
        .edit-form-section { display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .edit-form-section.visible { display: block; }

        /* ── REPORT TABS ── */
        .tab-row { display: flex; gap: 4px; margin-bottom: 14px; }
        .tab-btn { padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid var(--border); background: var(--white); color: var(--muted); transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
        .tab-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* ── REPORT SUMMARY ── */
        .report-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .report-stat { background: var(--surface-2); border-radius: 6px; padding: 14px; border: 1px solid var(--border); text-align: center; }
        .report-stat .rs-value { color: var(--navy); font-size: 24px; font-weight: 300; font-family: 'DM Mono', monospace; }
        .report-stat .rs-label { color: var(--muted); font-size: 10.5px; font-weight: 500; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.05em; }

        /* ── BARANGAY DIRECTORY ── */
        .search-bar-input {
            width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;
            font-size: 13px; color: var(--text); background: var(--white);
            font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.15s;
            margin-bottom: 14px;
        }
        .search-bar-input:focus { border-color: var(--navy); }
        .search-bar-input::placeholder { color: var(--muted); }

        .brgy-list-item { display: flex; align-items: center; justify-content: space-between; padding: 11px 14px; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; transition: all 0.15s; background: var(--white); margin-bottom: 6px; }
        .brgy-list-item:hover { border-color: var(--navy); background: var(--navy-light); }
        .bli-name { font-size: 13px; font-weight: 500; color: var(--text); }
        .bli-meta { font-size: 11.5px; color: var(--muted); margin-top: 2px; }
        .bli-right { display: flex; align-items: center; gap: 8px; }
        .bli-arr { color: var(--muted); font-size: 16px; }

        /* ── BRGY DETAIL ── */
        .brgy-detail-panel { display: none; position: relative; z-index: 1; }
        .brgy-detail-panel.visible { display: block; }
        .brgy-detail-header { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
        .brgy-avatar { width: 48px; height: 48px; border-radius: 8px; background: var(--navy); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; font-weight: 300; flex-shrink: 0; overflow: hidden; }
        .brgy-avatar-info h4 { color: var(--navy); font-size: 16px; font-weight: 500; }
        .brgy-avatar-info p  { color: var(--muted); font-size: 12px; margin-top: 3px; }

        .brgy-stats-mini { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .bsm-card { background: var(--surface-2); border-radius: 8px; padding: 14px 12px; text-align: center; border: 1px solid var(--border); }
        .bsm-val { font-size: 22px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; }
        .bsm-lbl { font-size: 10.5px; font-weight: 500; color: var(--muted); margin-top: 3px; text-transform: uppercase; letter-spacing: 0.05em; }

        .captain-card { display: flex; align-items: center; gap: 12px; background: var(--surface-2); border-radius: 8px; padding: 12px 14px; border: 1px solid var(--border); margin-bottom: 16px; }
        .captain-avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--navy); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 300; flex-shrink: 0; }
        .captain-info h5 { font-size: 13px; font-weight: 500; color: var(--text); }
        .captain-info p  { font-size: 11.5px; color: var(--muted); margin-top: 2px; }

        .brgy-info-section { margin-bottom: 16px; }
        .brgy-info-section h5 { font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .brgy-info-section h5::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .brgy-field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .brgy-field { background: var(--surface-2); border: 1px solid var(--border); border-radius: 6px; padding: 10px 12px; }
        .brgy-field .bf-label { font-size: 10.5px; font-weight: 500; color: var(--muted); margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.04em; }
        .brgy-field .bf-val { font-size: 13px; font-weight: 500; color: var(--text); }
        .brgy-field.full { grid-column: 1 / -1; }

        /* ── USER ACCOUNTS ── */
        .user-filter-bar { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; align-items: center; }
        .role-filter-btn { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; cursor: pointer; border: 1px solid var(--border); background: var(--white); color: var(--muted); transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
        .role-filter-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }
        .user-group-header { display: flex; align-items: center; gap: 10px; padding: 10px 0 6px; margin-top: 6px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); }
        .user-group-header::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .user-card { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 6px; background: var(--white); transition: 0.15s; }
        .user-card:hover { border-color: var(--navy); background: var(--navy-light); }
        .user-card-left { display: flex; align-items: center; gap: 10px; }
        .user-mini-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500; color: #fff; flex-shrink: 0; }
        .user-mini-avatar.role-superadmin { background: #553c9a; }
        .user-mini-avatar.role-cityhall   { background: var(--blue); }
        .user-mini-avatar.role-barangay   { background: var(--navy); }
        .user-mini-avatar.role-mayor      { background: var(--amber); }
        .user-card-name  { font-size: 13px; font-weight: 500; color: var(--text); }
        .user-card-email { font-size: 11.5px; color: var(--muted); margin-top: 1px; }
        .user-card-right { display: flex; align-items: center; gap: 8px; text-align: right; }
        .user-card-brgy  { font-size: 11.5px; color: var(--muted); font-weight: 500; }

        /* ── PROFILE MODAL ── */
        .profile-hero { background: var(--navy); border-radius: 8px; padding: 20px 22px; color: #fff; display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
        .profile-big-avatar { width: 52px; height: 52px; border-radius: 8px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; font-weight: 300; flex-shrink: 0; }
        .profile-hero-name { font-size: 16px; font-weight: 500; }
        .profile-hero-sub  { font-size: 12px; opacity: 0.65; margin-top: 2px; }
        .profile-meta { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
        .profile-meta span { font-size: 11px; background: rgba(255,255,255,0.12); padding: 3px 9px; border-radius: 20px; opacity: 0.85; }

        /* ── ONLINE DOT ── */
        .online-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; background: var(--border); transition: background 0.3s; }
        .online-dot.is-online  { background: var(--green); }
        .online-dot.is-offline { background: var(--border); }

        /* ── MODAL STABLE HEIGHT ── */
        #modal-allbrgy .modal-body, #modal-user-accounts .modal-body { min-height: 480px; }
        #brgy-list-container { max-height: 380px; overflow-y: auto; }
        #user-accounts-list  { max-height: 360px; overflow-y: auto; }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-logo">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
            <h1>SPAC</h1>
            <p>City of San Pedro, Laguna</p>
        </div>
        <?php if (!empty($system_logo)): ?>
            <img src="<?= htmlspecialchars($system_logo) ?>"
                 style="width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;object-fit:cover;object-position:center;border:2px solid var(--border);flex-shrink:0">
        <?php else: ?>
            <div style="width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;flex-shrink:0;border:2px solid var(--border)">
                S
            </div>
        <?php endif; ?>
    </div>
</div>
    <div class="sidebar-menu">
        <div class="menu-section">Main</div>
        <a href="#" class="menu-item active"><span class="menu-dot"></span> Dashboard</a>

        <div class="menu-section">Management</div>
       <button type="button" class="menu-item" onclick="openModal('modal-allbrgy')">
    <span class="menu-dot"></span> Barangays
    <span class="menu-badge" id="sidebar-badge-barangays"><?= number_format($total_barangays) ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-history')">
    <span class="menu-dot"></span> Assistance History
    <span class="menu-badge"><?= $completed_ayuda ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-user-accounts')">
    <span class="menu-dot"></span> User Accounts
    <span class="menu-badge" id="sidebar-badge-users"><?= number_format($total_users) ?></span>
</button>
<!-- ... further down ... -->
<button type="button" class="menu-item" onclick="openModal('modal-residents')">
    <span class="menu-dot"></span> Registered Residents
    <span class="menu-badge" id="sidebar-badge-residents"><?= number_format($total_residents) ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-families')">
    <span class="menu-dot"></span> Total Families
    <span class="menu-badge" id="sidebar-badge-families"><?= number_format($total_families) ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-ayuda')">
    <span class="menu-dot"></span> Ongoing Ayuda
    <span class="menu-badge" id="sidebar-badge-ongoing"><?= $ongoing_ayuda ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-scans')">
    <span class="menu-dot"></span> Number of Scans
    <span class="menu-badge" id="sidebar-badge-scans"><?= number_format($total_scans) ?></span>
</button>
<button type="button" class="menu-item" onclick="openModal('modal-alerts')">
    <span class="menu-dot"></span> Alerts
    <span class="menu-badge alert" id="sidebar-badge-alerts" style="<?= $total_alerts > 0 ? '' : 'display:none' ?>"><?= $total_alerts ?></span>
</button>
    </div>
    <div class="sidebar-footer">
        <a href="http://localhost/SPAC/logout.php"><span class="menu-dot"></span> Logout</a>
    </div>
</div>

<!-- ── MAIN ── -->
<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Dashboard Overview</div>
         <div class="topbar-date" id="topbar-date"></div>
<span id="live-indicator" style="font-size:11px;color:var(--muted);opacity:0.5;transition:opacity 0.4s;margin-left:8px"></span>
        </div>
        <div class="topbar-right">
            <span class="role-chip">Super Administrator</span>
            <button class="avatar-btn" onclick="openModal('modal-profile')" title="My Profile">
                <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
            </button>
            <div>
                <div style="font-size:13px;font-weight:500;color:var(--navy)"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
            </div>
        </div>
    </div>

    <div class="content">
        <?php if ($action_message): ?>
        <div class="alert-banner <?= $action_type ?>">
            <?= $action_type === 'success' ? '✓' : '✕' ?> <?= htmlspecialchars($action_message) ?>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h2>SPAC System Overview</h2>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></p>
        </div>

        <!-- Top stats -->
      <div class="stats-grid">
    <div class="stat-card">
        <div class="stat-num" id="top-stat-residents"><?= number_format($total_residents) ?></div>
        <div class="stat-label">Total Residents</div>
    </div>
    <div class="stat-card">
        <div class="stat-num" id="top-stat-barangays"><?= number_format($total_barangays) ?></div>
        <div class="stat-label">Barangays</div>
    </div>
    <div class="stat-card">
        <div class="stat-num" id="top-stat-ayuda"><?= number_format($total_ayuda) ?></div>
        <div class="stat-label">Assistance Records</div>
    </div>
    <div class="stat-card">
        <div class="stat-num" id="top-stat-users"><?= number_format($total_users) ?></div>
        <div class="stat-label">System Users</div>
    </div>
</div>

        <!-- Analytics -->
        <div class="sec-label">Analytics Overview</div>
        <div class="stats-grid-analytics">
<div class="stat-card clickable" onclick="openModal('modal-residents')">
    <div class="stat-num" id="analytics-residents"><?= number_format($total_residents) ?></div>
    <div class="stat-label">Registered Residents</div>
    <div class="stat-hint">Click for per-barangay breakdown →</div>
</div>
<div class="stat-card clickable" onclick="openModal('modal-families')">
    <div class="stat-num" id="analytics-families"><?= number_format($total_families) ?></div>
    <div class="stat-label">Total Families</div>
    <div class="stat-hint">Click for per-barangay breakdown →</div>
</div>
<div class="stat-card clickable" onclick="openModal('modal-activebrgy')">
    <div class="stat-num" id="analytics-activebrgy"><?= $active_barangays ?></div>
    <div class="stat-label">Active Barangays</div>
    <div class="stat-hint">Click to view active list →</div>
</div>
<div class="stat-card clickable" onclick="openModal('modal-ayuda')">
    <div class="stat-num" id="analytics-ongoing"><?= $ongoing_ayuda ?></div>
    <div class="stat-label">Ongoing Ayuda</div>
    <div class="stat-hint">City hall + Barangay sources →</div>
</div>
<div class="stat-card clickable" onclick="openModal('modal-scans')">
    <div class="stat-num" id="analytics-scans"><?= number_format($total_scans) ?></div>
    <div class="stat-label">Total Scans</div>
    <div class="stat-hint">Click for scan type breakdown →</div>
</div>
<div class="stat-card clickable <?= $total_alerts > 0 ? 'danger' : '' ?>" id="analytics-alerts-card" onclick="openModal('modal-alerts')">
    <div class="stat-num" id="analytics-alerts"><?= $total_alerts ?></div>
    <div class="stat-label">Active Alerts</div>
    <div class="stat-hint">Click to review →</div>
</div>
        </div>

        <!-- Quick Actions -->
        <div class="sec-label">Quick Actions</div>
        <div class="actions-grid">
            <button type="button" class="action-btn" onclick="openModal('modal-add-barangay')">
                <h3>Add Barangay</h3><p>Register a new barangay</p>
            </button>
            <button type="button" class="action-btn" onclick="openModal('modal-add-user')">
                <h3>Add User</h3><p>Create a new system account</p>
            </button>
            <button type="button" class="action-btn" onclick="openModal('modal-add-ayuda')">
                <h3>Add Ayuda</h3><p>Create a new ayuda record</p>
            </button>
            <button type="button" class="action-btn" onclick="openModal('modal-reports')">
                <h3>View Reports</h3><p>Generate system reports</p>
            </button>
        </div>

        <!-- System Info -->
        <div class="sec-label">System Information</div>
        <div class="info-card">
            <div class="info-row"><span class="info-label">System</span><span class="info-value">SPAC – San Pedro Assistance Card</span></div>
            <div class="info-row"><span class="info-label">Logged in as</span><span class="info-value"><?= htmlspecialchars($_SESSION['full_name']) ?></span></div>
            <div class="info-row"><span class="info-label">Role</span><span class="tag tag-navy">Super Administrator</span></div>
            <div class="info-row"><span class="info-label">City</span><span class="info-value">San Pedro, Laguna</span></div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════
     ANALYTICS MODALS
══════════════════════════════ -->

<!-- RESIDENTS -->
<div class="modal-backdrop" id="modal-residents">
    <div class="modal">
        <div class="modal-header"><h3>Registered Residents per Barangay</h3><button class="modal-close" onclick="closeModal('modal-residents')">×</button></div>
        <div class="modal-body">
            <?php if (empty($brgy_residents)): ?><div class="modal-empty">No data available.</div>
            <?php else: ?>
            <table class="data-table" id="tbl-modal-residents"><thead><tr><th>#</th><th>Barangay</th><th>Residents</th></tr></thead>
            <?php foreach ($brgy_residents as $i => $row): ?>
            <tr><td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td><td><?= htmlspecialchars($row['name']) ?></td><td class="num"><?= number_format($row['total']) ?></td></tr>
            <?php endforeach; ?></tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- FAMILIES -->
<div class="modal-backdrop" id="modal-families">
    <div class="modal">
        <div class="modal-header"><h3>Total Families per Barangay</h3><button class="modal-close" onclick="closeModal('modal-families')">×</button></div>
        <div class="modal-body">
            <?php if (empty($brgy_families)): ?><div class="modal-empty">No data available.</div>
            <?php else: ?>
           <table class="data-table" id="tbl-modal-families"><thead><tr><th>#</th><th>Barangay</th><th>Families</th></tr></thead>
            <?php foreach ($brgy_families as $i => $row): ?>
            <tr><td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td><td><?= htmlspecialchars($row['name']) ?></td><td class="num"><?= number_format($row['total']) ?></td></tr>
            <?php endforeach; ?></tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ACTIVE BARANGAYS -->
<div class="modal-backdrop" id="modal-activebrgy">
    <div class="modal">
        <div class="modal-header"><h3>Active Barangays</h3><button class="modal-close" onclick="closeModal('modal-activebrgy')">×</button></div>
        <div class="modal-body">
            <?php if (empty($active_brgy_list)): ?><div class="modal-empty">No active barangays found.</div>
            <?php else: ?>
            <table class="data-table"><thead><tr><th>#</th><th>Barangay</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($active_brgy_list as $i => $row): ?>
            <tr><td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td><td><?= htmlspecialchars($row['name']) ?></td><td><span class="tag tag-active">Active</span></td></tr>
            <?php endforeach; ?></tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- ══════════════════════════════
     BARANGAY DIRECTORY MODAL
══════════════════════════════ -->
<div class="modal-backdrop" id="modal-allbrgy">
    <div class="modal modal-wide">
        <div class="modal-header">
            <h3>Barangay Directory</h3>
            <button class="modal-close" onclick="closeBrgyModal()">×</button>
        </div>
        <div class="modal-body">
            <!-- LIST VIEW -->
            <div id="brgy-list-view">
                <input type="text" class="search-bar-input" id="brgy-search-input" placeholder="Search by name, captain, or district..." oninput="filterBrgy(this.value)">
                <div id="brgy-list-container">
                    <?php foreach ($all_brgy_list as $brgy): ?>
                    <div class="brgy-list-item" onclick="showBrgyDetail(<?= htmlspecialchars(json_encode($brgy)) ?>)" data-search="<?= strtolower(htmlspecialchars($brgy['name'].' '.$brgy['captain_name'].' '.$brgy['district'])) ?>">
                        <div>
                            <div class="bli-name">
                                <?= htmlspecialchars($brgy['name']) ?>
                                <?php if ($brgy['is_pilot']): ?><span class="tag tag-pilot" style="margin-left:6px">Pilot</span><?php endif; ?>
                            </div>
                            <div class="bli-meta">
                                <?= $brgy['captain_name'] ? 'Kap. '.htmlspecialchars($brgy['captain_name']) : 'Captain not set' ?>
                                <?= $brgy['district'] ? ' · '.htmlspecialchars($brgy['district']) : '' ?>
                            </div>
                        </div>
                        <div class="bli-right">
                            <span class="tag <?= $brgy['status'] === 'active' ? 'tag-active' : 'tag-inactive' ?>"><?= ucfirst($brgy['status']) ?></span>
                            <span class="bli-arr">›</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($all_brgy_list)): ?><div class="modal-empty">No barangays found.</div><?php endif; ?>
                </div>
            </div>

          <!-- DETAIL VIEW -->
            <div id="brgy-detail-view" class="brgy-detail-panel">
                <button type="button" onclick="backToBrgyList()" class="btn btn-ghost btn-sm" style="margin-bottom:14px">← Back to List</button>

                <div class="brgy-detail-header">
                <div class="brgy-avatar" id="bdet-avatar" style="overflow:hidden;padding:0;width:100px;height:100px;border-radius:50%;">
    <img id="bdet-avatar-img" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;border-radius:50%;">
    <span id="bdet-avatar-letter" style="font-size:18px;font-weight:300;color:#fff;">B</span>
</div>
                    <div class="brgy-avatar-info">
                        <h4 id="bdet-name">—</h4>
                        <p id="bdet-district">—</p>
                        <div style="display:flex;gap:6px;margin-top:6px" id="bdet-tags"></div>
                    </div>
                </div>

                <div class="brgy-stats-mini">
                    <div class="bsm-card"><div class="bsm-val" id="bdet-residents">0</div><div class="bsm-lbl">Residents</div></div>
                    <div class="bsm-card"><div class="bsm-val" id="bdet-families">0</div><div class="bsm-lbl">Families</div></div>
                    <div class="bsm-card"><div class="bsm-val" id="bdet-population">—</div><div class="bsm-lbl">Population</div></div>
                </div>

               <!-- AFTER -->
<div class="captain-card" id="bdet-captain-card">
    <div class="captain-avatar" id="bdet-captain-avatar"
         style="overflow:hidden; padding:0; width:70px; height:70px; border-radius:50%; background:var(--navy); display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; font-weight:300; flex-shrink:0;">
        <img id="bdet-captain-img" src="" alt=""
             style="width:100%; height:100%; object-fit:cover; display:none; border-radius:50%;">
        <span id="bdet-captain-letter">?</span>
    </div>
    <div class="captain-info">
        <h5 id="bdet-captain-name">Not set</h5>
        <p id="bdet-captain-since">Barangay Captain</p>
    </div>
</div>
                <div class="brgy-info-section">
                    <h5>Contact & Location</h5>
                    <div class="brgy-field-grid">
                        <div class="brgy-field"><div class="bf-label">Email</div><div class="bf-val" id="bdet-email">—</div></div>
                        <div class="brgy-field"><div class="bf-label">Contact Number</div><div class="bf-val" id="bdet-contact">—</div></div>
                        <div class="brgy-field full"><div class="bf-label">Address</div><div class="bf-val" id="bdet-address">—</div></div>
                    </div>
                </div>


<div class="brgy-info-section">
    <h5>Additional Information</h5>
    <div class="brgy-field-grid">
        <div class="brgy-field"><div class="bf-label">District</div><div class="bf-val" id="bdet-district-info">—</div></div>
        <div class="brgy-field"><div class="bf-label">Year Founded</div><div class="bf-val" id="bdet-founded">—</div></div>
    </div>
</div>
            </div>
        </div>
    </div>
</div>


<!-- ONGOING AYUDA -->
<div class="modal-backdrop" id="modal-ayuda">
    <div class="modal modal-wide">
        <div class="modal-header"><h3>Ongoing Ayuda / Assistance</h3><button class="modal-close" onclick="closeModal('modal-ayuda')">×</button></div>
        <div class="modal-body">
            <?php if (empty($ongoing_ayuda_list)): ?><div class="modal-empty">No ongoing ayuda at the moment.</div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>#</th><th>Ayuda / Assistance</th><th>Type</th><th>Source</th><th>Origin</th><th>Dates</th></tr></thead>
                <tbody>
                <?php foreach ($ongoing_ayuda_list as $i => $row): ?>
                <tr onclick="openAyudaEdit('<?= $row['record_id'] ?>','<?= htmlspecialchars($row['ayuda_name'],ENT_QUOTES) ?>','<?= htmlspecialchars($row['ayuda_type'],ENT_QUOTES) ?>','<?= $row['source'] ?>','<?= htmlspecialchars($row['origin'],ENT_QUOTES) ?>','<?= $row['start_date'] ?>','<?= $row['end_date'] ?>')" style="cursor:pointer">
                    <td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($row['ayuda_name']) ?></td>
                    <td style="color:var(--muted)"><?= htmlspecialchars($row['ayuda_type'] ?: '—') ?></td>
                    <td><span class="source-tag <?= $row['source']==='cityhall'?'city':'brgy' ?>"><?= $row['source']==='cityhall'?'City Hall':'Barangay' ?></span></td>
                    <td><?= htmlspecialchars($row['origin']) ?></td>
                    <td style="font-size:11.5px;color:var(--muted);white-space:nowrap"><?= date('M d, Y',strtotime($row['start_date'])) ?><br>→ <?= date('M d, Y',strtotime($row['end_date'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- EDIT AYUDA -->
<div class="modal-backdrop" id="modal-edit-ayuda">
    <div class="modal">
        <div class="modal-header"><h3>Ayuda Details</h3><button class="modal-close" onclick="closeModal('modal-edit-ayuda')">×</button></div>
        <div class="modal-body">
            <div class="info-card" style="margin-bottom:0">
                <div class="info-row"><span class="info-label">Ayuda Name</span><span class="info-value" id="view-ayuda-name">—</span></div>
                <div class="info-row"><span class="info-label">Type</span><span class="info-value" id="view-ayuda-type">—</span></div>
                <div class="info-row"><span class="info-label">Source</span><span class="info-value" id="view-ayuda-source">—</span></div>
                <div class="info-row"><span class="info-label">Origin</span><span class="info-value" id="view-ayuda-origin">—</span></div>
                <div class="info-row"><span class="info-label">Start Date</span><span class="info-value" id="view-ayuda-start">—</span></div>
                <div class="info-row"><span class="info-label">End Date</span><span class="info-value" id="view-ayuda-end">—</span></div>
            </div>
            <div class="edit-form-section" id="edit-form-section">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="edit_ayuda">
                    <input type="hidden" name="record_id" id="edit-record-id">
                    <div class="form-grid">
                        <div class="form-group full"><label>Rename Ayuda <span class="req">*</span></label><input type="text" name="ayuda_name" id="edit-ayuda-name" required></div>
                        <div class="form-group"><label>Ayuda Type</label>
                            <select name="ayuda_type" id="edit-ayuda-type">
                                <option value="">— Select Type —</option>
                                <option value="Food">Food</option><option value="Cash">Cash</option>
                                <option value="Medical">Medical</option><option value="Clothing">Clothing</option>
                                <option value="Educational">Educational</option><option value="Livelihood">Livelihood</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Start Date</label><input type="date" name="start_date" id="edit-ayuda-start"></div>
                        <div class="form-group full">
                            <div class="checkbox-row"><input type="checkbox" name="mark_complete" id="edit-mark-complete" value="1"><label for="edit-mark-complete">Mark as Complete</label></div>
                            <span class="hint">Checking this moves the ayuda to Assistance History.</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="hideEditForm()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            <div class="form-actions" id="details-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-ayuda')">Close</button>
                <button type="button" class="btn btn-primary" onclick="showEditForm()">Edit This Ayuda →</button>
            </div>
        </div>
    </div>
</div>

<!-- ASSISTANCE HISTORY -->
<div class="modal-backdrop" id="modal-history">
    <div class="modal modal-wide">
        <div class="modal-header"><h3>Assistance History</h3><button class="modal-close" onclick="closeModal('modal-history')">×</button></div>
        <div class="modal-body">
            <?php if (empty($assistance_history)): ?><div class="modal-empty">No completed ayuda yet.</div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>#</th><th>Ayuda</th><th>Type</th><th>Source</th><th>Origin</th><th>Start</th><th>Completed</th></tr></thead>
                <tbody>
                <?php foreach ($assistance_history as $i => $row): ?>
                <tr>
                    <td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($row['ayuda_name']) ?></td>
                    <td style="color:var(--muted)"><?= htmlspecialchars($row['ayuda_type'] ?: '—') ?></td>
                    <td><span class="source-tag <?= $row['source']==='cityhall'?'city':'brgy' ?>"><?= $row['source']==='cityhall'?'City Hall':'Barangay' ?></span></td>
                    <td><?= htmlspecialchars($row['origin']) ?></td>
                    <td style="color:var(--muted);white-space:nowrap"><?= $row['start_date'] ? date('M d, Y',strtotime($row['start_date'])) : '—' ?></td>
                    <td style="white-space:nowrap"><span class="tag tag-active">✓ <?= $row['end_date'] ? date('M d, Y',strtotime($row['end_date'])) : '—' ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SCANS -->
<div class="modal-backdrop" id="modal-scans">
    <div class="modal">
        <div class="modal-header"><h3>Scan Breakdown</h3><button class="modal-close" onclick="closeModal('modal-scans')">×</button></div>
        <div class="modal-body">
            <?php if (empty($scan_breakdown)): ?><div class="modal-empty">No scan data available.</div>
            <?php else: $scan_total = array_sum(array_column($scan_breakdown,'total')); ?>
            <table class="data-table"><thead><tr><th>Scan Type</th><th>Count</th><th>Share</th></tr></thead><tbody>
            <?php foreach ($scan_breakdown as $row): $pct = $scan_total > 0 ? round($row['total']/$scan_total*100) : 0; ?>
            <tr>
                <td><?= htmlspecialchars(ucwords(str_replace('_',' ',$row['scan_type']))) ?></td>
                <td class="num"><?= number_format($row['total']) ?></td>
                <td style="width:140px"><div style="font-size:12px;color:var(--navy);font-weight:500;margin-bottom:3px"><?= $pct ?>%</div><div class="scan-bar-wrap"><div class="scan-bar" style="width:<?= $pct ?>%"></div></div></td>
            </tr>
            <?php endforeach; ?></tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ALERTS -->
<div class="modal-backdrop" id="modal-alerts">
    <div class="modal">
        <div class="modal-header"><h3>Active Alerts</h3><button class="modal-close" onclick="closeModal('modal-alerts')">×</button></div>
        <div class="modal-body">
            <?php if (empty($alerts_list)): ?><div class="modal-empty">No active alerts. System is running normally.</div>
            <?php else: ?>
            <table class="data-table"><thead><tr><th>Severity</th><th>Message</th><th>Time</th></tr></thead><tbody>
            <?php foreach ($alerts_list as $row): ?>
            <tr>
                <td><span class="sev-<?= strtolower($row['severity']) ?>"><?= ucfirst($row['severity']) ?></span></td>
                <td><?= htmlspecialchars($row['message']) ?></td>
                <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?= date('M d, H:i',strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?></tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- ══════════════════════════════
     USER ACCOUNTS MODAL
══════════════════════════════ -->
<div class="modal-backdrop" id="modal-user-accounts">
    <div class="modal modal-wide">
        <div class="modal-header">
            <h3>System User Accounts</h3>
            <button class="modal-close" onclick="closeModal('modal-user-accounts')">×</button>
        </div>
        <div class="modal-body">
            <div class="user-filter-bar">
                <input type="text" id="user-search-input" placeholder="Search by name, email, or barangay..." oninput="filterUsers(this.value)" style="flex:1;padding:7px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;color:var(--text)">
                <button class="role-filter-btn active" onclick="setRoleFilter('all', this)">All</button>
                <button class="role-filter-btn" onclick="setRoleFilter('superadmin', this)">Super Admin</button>
                <button class="role-filter-btn" onclick="setRoleFilter('cityhall', this)">City Hall</button>
                <button class="role-filter-btn" onclick="setRoleFilter('barangay', this)">Barangay</button>
                <button class="role-filter-btn" onclick="setRoleFilter('mayor', this)">Mayor</button>
            </div>

            <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center">
                <?php
                $role_counts = ['superadmin'=>0,'cityhall'=>0,'barangay'=>0,'mayor'=>0];
                foreach ($users_all as $u) { if (isset($role_counts[$u['role']])) $role_counts[$u['role']]++; }
                $count_labels = ['superadmin'=>'Super Admin','cityhall'=>'City Hall','barangay'=>'Barangay','mayor'=>'Mayor'];
                foreach ($count_labels as $rk => $rl): if ($role_counts[$rk] < 1) continue; ?>
                <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:6px;padding:5px 12px;font-size:12px;color:var(--muted)">
                    <span style="color:var(--navy);font-family:'DM Mono',monospace;font-size:15px;font-weight:300"><?= $role_counts[$rk] ?></span> <?= $rl ?>
                </div>
                <?php endforeach; ?>
                <div style="margin-left:auto">
                    <button type="button" class="btn btn-primary btn-sm" onclick="closeModal('modal-user-accounts');openModal('modal-add-user')">+ Add User</button>
                </div>
            </div>

            <div id="user-accounts-list">
                <?php
                $role_order = ['superadmin','mayor','cityhall','barangay'];
                $role_names = ['superadmin'=>'Super Administrators','mayor'=>'Mayor / Executive','cityhall'=>'City Hall Staff','barangay'=>'Barangay Users'];
                foreach ($role_order as $current_role):
                    $group_users = array_filter($users_all, fn($u) => $u['role'] === $current_role);
                    if (empty($group_users)) continue;
                ?>
                <div class="user-group" data-role="<?= $current_role ?>">
                    <div class="user-group-header"><?= $role_names[$current_role] ?? ucfirst($current_role) ?> (<?= count($group_users) ?>)</div>
                    <?php foreach ($group_users as $u): ?>
                    <div class="user-card" data-search="<?= strtolower(htmlspecialchars($u['full_name'].' '.$u['email'].' '.($u['barangay_name']??''))) ?>" data-role="<?= $u['role'] ?>">
                        <div class="user-card-left">
                            <div class="user-mini-avatar role-<?= $u['role'] ?>"><?= strtoupper(substr($u['full_name'],0,1)) ?></div>
                            <div>
                                <div class="user-card-name"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div class="user-card-email"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                        <div class="user-card-right">
                            <?php if ($u['barangay_name']): ?><div class="user-card-brgy"><?= htmlspecialchars($u['barangay_name']) ?></div><?php endif; ?>
                            <span class="online-dot" id="dot-<?= $u['user_id'] ?>" title="Checking..."></span>
                            <span class="tag <?= $u['is_active'] ? 'tag-active' : 'tag-inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($users_all)): ?><div class="modal-empty">No users found.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════
     ACTION MODALS
══════════════════════════════ -->

<!-- ADD BARANGAY -->
<div class="modal-backdrop" id="modal-add-barangay">
    <div class="modal modal-wide">
        <div class="modal-header"><h3>Add New Barangay</h3><button class="modal-close" onclick="closeModal('modal-add-barangay')">×</button></div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_barangay">
                <div class="form-sub-label">Basic Information</div>
                <div class="form-grid" style="margin-bottom:16px">
                    <div class="form-group full"><label>Barangay Name <span class="req">*</span></label><input type="text" name="name" placeholder="e.g. Barangay San Antonio" required></div>
                    <div class="form-group"><label>Email Address <span class="req">*</span></label><input type="email" name="email" placeholder="brgy@sanpedro.gov.ph" required></div>
                    <div class="form-group"><label>Contact Number</label><input type="text" name="contact_number" placeholder="09XX-XXX-XXXX"></div>
                    <div class="form-group"><label>District</label><input type="text" name="district" placeholder="e.g. District 1"></div>
                    <div class="form-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="form-group full"><div class="checkbox-row"><input type="checkbox" name="is_pilot" id="is_pilot" value="1"><label for="is_pilot">Mark as Pilot Barangay</label></div></div>
                </div>
                <div class="form-sub-label">Address & Location</div>
                <div class="form-grid" style="margin-bottom:16px">
                    <div class="form-group full"><label>Full Address</label><input type="text" name="address" placeholder="e.g. Barangay San Antonio, San Pedro, Laguna"></div>
                    <div class="form-group"><label>Land Area (hectares)</label><input type="text" name="land_area" placeholder="e.g. 120.5 ha"></div>
                    <div class="form-group"><label>Year Founded</label><input type="number" name="founded_year" placeholder="e.g. 1950" min="1800" max="2030"></div>
                    <div class="form-group"><label>Estimated Population</label><input type="number" name="population" placeholder="e.g. 12500" min="0"></div>
                </div>
                <div class="form-sub-label">Barangay Captain</div>
                <div class="form-grid">
                    <div class="form-group"><label>Captain's Full Name</label><input type="text" name="captain_name" placeholder="e.g. Juan Dela Cruz"></div>
                    <div class="form-group"><label>Captain Since (Year)</label><input type="text" name="captain_since" placeholder="e.g. 2023"></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-barangay')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Barangay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD USER -->
<div class="modal-backdrop" id="modal-add-user">
    <div class="modal">
        <div class="modal-header"><h3>Add New User Account</h3><button class="modal-close" onclick="closeModal('modal-add-user')">×</button></div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_user">
                <div class="form-grid">
                    <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="full_name" placeholder="Juan Dela Cruz" required></div>
                    <div class="form-group"><label>Email Address <span class="req">*</span></label><input type="email" name="email" placeholder="user@spac.gov.ph" required></div>
                    <div class="form-group"><label>Role <span class="req">*</span></label>
                        <select name="role" id="role-select" onchange="toggleBrgyField(this.value)">
                            <option value="barangay">Barangay</option>
                            <option value="cityhall">City Hall</option>
                            <option value="mayor">Mayor</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group" id="brgy-field">
                        <label>Assign Barangay</label>
                        <select name="barangay_id">
                            <option value="">— Select Barangay —</option>
                            <?php foreach ($all_barangays as $brgy): ?>
                            <option value="<?= $brgy['barangay_id'] ?>"><?= htmlspecialchars($brgy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="hint">Required for Barangay role</span>
                    </div>
                    <div class="form-group full"><label>Password <span class="req">*</span></label>
                        <div class="pw-wrap"><input type="password" name="password" id="pw-input" placeholder="Minimum 8 characters" required><button type="button" class="pw-toggle" onclick="togglePw()">&#128065;</button></div>
                    </div>
                    <div class="form-group full"><div class="checkbox-row"><input type="checkbox" name="is_active" id="is_active" value="1" checked><label for="is_active">Account is Active</label></div></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-user')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ADD AYUDA -->
<div class="modal-backdrop" id="modal-add-ayuda">
    <div class="modal">
        <div class="modal-header"><h3>Add New Ayuda / Assistance</h3><button class="modal-close" onclick="closeModal('modal-add-ayuda')">×</button></div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_ayuda">
                <div class="form-grid">
                    <div class="form-group full"><label>Ayuda Name <span class="req">*</span></label><input type="text" name="ayuda_name" placeholder="e.g. Rice Subsidy Program" required></div>
                    <div class="form-group">
                        <label>Ayuda Type</label>
                        <select name="ayuda_type" id="ayuda-type-select" onchange="toggleAyudaOther(this.value)">
                            <option value="">— Select Type —</option>
                            <option value="Food">Food</option><option value="Cash">Cash</option>
                            <option value="Medical">Medical</option><option value="Clothing">Clothing</option>
                            <option value="Educational">Educational</option><option value="Livelihood">Livelihood</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="form-group" id="ayuda-other-field" style="display:none">
                        <label>Please specify</label>
                        <input type="text" name="ayuda_type_other" id="ayuda-type-other" placeholder="e.g. Shelter, Hygiene Kit...">
                    </div>
                    <div class="form-group">
                        <label>Source <span class="req">*</span></label>
                        <select name="source" id="ayuda-source-select" onchange="toggleAyudaBrgyField(this.value)">
                            <option value="cityhall">City Hall</option><option value="barangay">Barangay</option>
                        </select>
                    </div>
                    <div class="form-group" id="ayuda-brgy-field" style="display:none">
                        <label>Assign Barangay</label>
                        <select name="barangay_id">
                            <option value="">— Select Barangay —</option>
                            <?php foreach ($all_barangays as $brgy): ?>
                            <option value="<?= $brgy['barangay_id'] ?>"><?= htmlspecialchars($brgy['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="hint">Required for Barangay-sourced ayuda</span>
                    </div>
                    <div class="form-group"><label>Start Date <span class="req">*</span></label><input type="date" name="start_date" required></div>
                    <div class="form-group"><label>End Date <span class="req">*</span></label><input type="date" name="end_date" required></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-ayuda')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Ayuda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- REPORTS -->
<div class="modal-backdrop" id="modal-reports">
    <div class="modal modal-wide">
        <div class="modal-header"><h3>System Reports</h3><button class="modal-close" onclick="closeModal('modal-reports')">×</button></div>
        <div class="modal-body">
            <div class="report-summary">
                <div class="report-stat"><div class="rs-value"><?= number_format($total_residents) ?></div><div class="rs-label">Residents</div></div>
                <div class="report-stat"><div class="rs-value"><?= number_format($total_families) ?></div><div class="rs-label">Families</div></div>
                <div class="report-stat"><div class="rs-value"><?= number_format($total_scans) ?></div><div class="rs-label">Scans</div></div>
                <div class="report-stat"><div class="rs-value"><?= number_format($total_ayuda) ?></div><div class="rs-label">Ayuda</div></div>
            </div>
            <div class="tab-row">
                <button class="tab-btn active" onclick="switchTab('tab-barangay', this)">By Barangay</button>
                <button class="tab-btn" onclick="switchTab('tab-users', this)">Recent Users</button>
            </div>
            <div class="tab-panel active" id="tab-barangay">
                <?php if (empty($report_barangays)): ?><div class="modal-empty">No barangay data available.</div>
                <?php else: ?>
                <table class="data-table"><thead><tr><th>#</th><th>Barangay</th><th>Residents</th><th>Families</th><th>Scans</th><th>Ayuda</th></tr></thead><tbody>
                <?php foreach ($report_barangays as $i => $row): ?>
                <tr><td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td><td><?= htmlspecialchars($row['name']) ?></td><td class="num"><?= number_format($row['total_residents']) ?></td><td class="num"><?= number_format($row['total_families']) ?></td><td class="num"><?= number_format($row['total_scans']) ?></td><td class="num"><?= number_format($row['total_ayuda']) ?></td></tr>
                <?php endforeach; ?></tbody></table>
                <?php endif; ?>
            </div>
            <div class="tab-panel" id="tab-users">
                <?php if (empty($recent_users)): ?><div class="modal-empty">No users found.</div>
                <?php else: ?>
                <table class="data-table"><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Barangay</th><th>Status</th></tr></thead><tbody>
                <?php foreach ($recent_users as $i => $row): ?>
                <tr><td style="color:var(--muted);font-size:12px"><?= str_pad($i+1,2,'0',STR_PAD_LEFT) ?></td><td><?= htmlspecialchars($row['full_name']) ?></td><td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($row['email']) ?></td><td><span class="tag tag-<?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span></td><td><?= $row['barangay_name'] ? htmlspecialchars($row['barangay_name']) : '—' ?></td><td><span class="tag <?= $row['is_active'] ? 'tag-active' : 'tag-inactive' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td></tr>
                <?php endforeach; ?></tbody></table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════
     PROFILE MODAL
══════════════════════════════ -->
<div class="modal-backdrop" id="modal-profile">
    <div class="modal">
        <div class="modal-header"><h3>My Profile</h3><button class="modal-close" onclick="closeModal('modal-profile')">×</button></div>
        <div class="modal-body">
            <div class="profile-hero">
                <div class="profile-big-avatar"><?= strtoupper(substr($_SESSION['full_name'],0,1)) ?></div>
                <div>
                    <div class="profile-hero-name"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <div class="profile-hero-sub"><?= htmlspecialchars($admin_info['email'] ?? '') ?></div>
                    <div class="profile-meta">
                        <span>Super Administrator</span>
                        <span>SPAC System</span>
                        <?php if (!empty($admin_info['created_at'])): ?>
                        <span>Since <?= date('M Y', strtotime($admin_info['created_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="tab-row" id="profile-tabs">
                <button class="tab-btn active" onclick="switchProfileTab('tab-profile-view', this)">Profile Info</button>
                <button class="tab-btn" onclick="switchProfileTab('tab-profile-edit', this)">Edit Profile</button>
                <button class="tab-btn" onclick="switchProfileTab('tab-profile-pw', this)">Change Password</button>
            </div>

            <div class="tab-panel active" id="tab-profile-view">
                <div class="info-card">
                    <div class="info-row"><span class="info-label">Full Name</span><span class="info-value"><?= htmlspecialchars($_SESSION['full_name']) ?></span></div>
                    <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($admin_info['email'] ?? '—') ?></span></div>
                    <div class="info-row"><span class="info-label">Role</span><span class="tag tag-navy">Super Administrator</span></div>
                    <div class="info-row"><span class="info-label">Account Created</span><span class="info-value"><?= !empty($admin_info['created_at']) ? date('F d, Y', strtotime($admin_info['created_at'])) : '—' ?></span></div>
                    <div class="info-row"><span class="info-label">System</span><span class="info-value">SPAC – San Pedro, Laguna</span></div>
                    <div class="info-row" style="flex-direction:column;align-items:flex-start;gap:10px">
    <span class="info-label">Login Page Image</span>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <?php if (!empty($login_image)): ?>
            <img src="<?= htmlspecialchars($login_image) ?>"
                 style="width:120px;height:72px;border-radius:6px;object-fit:cover;border:1px solid var(--border)">
        <?php else: ?>
            <div style="width:120px;height:72px;border-radius:6px;background:var(--surface-2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;color:var(--muted)">No image set</div>
        <?php endif; ?>
        <div>
            <form method="POST" enctype="multipart/form-data" style="display:inline">
                <input type="hidden" name="action" value="update_login_image">
                <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text);background:var(--white);transition:background 0.15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--white)'">
                    🖼 Upload Login Image
                    <input type="file" name="login_image" accept="image/*" style="display:none" onchange="this.closest('form').submit()">
                </label>
            </form>
            <div style="font-size:11px;color:var(--muted);margin-top:5px">Recommended: 1200×800px or wider. Shown on the login page.</div>
        </div>
    </div>
</div>
                    <div class="info-row" style="flex-direction:column;align-items:flex-start;gap:10px">
    <span class="info-label">System Logo</span>
    <div style="display:flex;align-items:center;gap:12px">
        <?php if (!empty($system_logo)): ?>
            <img src="<?= htmlspecialchars($system_logo) ?>"
                 style="width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;object-fit:cover;object-position:center;border:2px solid var(--border);flex-shrink:0">
        <?php else: ?>
            <div style="width:80px;height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:500">S</div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" style="display:inline">
            <input type="hidden" name="action" value="update_system_logo">
            <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text);background:var(--white);transition:background 0.15s" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--white)'">
                📷 Change Logo
                <input type="file" name="system_logo" accept="image/*" style="display:none" onchange="this.closest('form').submit()">
            </label>
        </form>
    </div>
</div>
                </div>
            </div>

            <div class="tab-panel" id="tab-profile-edit">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-grid">
                        <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" required></div>
                        <div class="form-group full"><label>Email Address <span class="req">*</span></label><input type="email" name="email" value="<?= htmlspecialchars($admin_info['email'] ?? '') ?>" required></div>
                        <div class="form-group full"><span class="hint" style="background:var(--surface-2);padding:10px 12px;border-radius:6px;display:block;border:1px solid var(--border)">Leave the password field empty to keep your current password.</span></div>
                        <div class="form-group full"><label>New Password <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                            <div class="pw-wrap"><input type="password" name="new_password" id="profile-pw-input" placeholder="Leave blank to keep current"><button type="button" class="pw-toggle" onclick="toggleProfilePw()">&#128065;</button></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="switchProfileTab('tab-profile-view', document.querySelector('#profile-tabs .tab-btn'))">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="tab-panel" id="tab-profile-pw">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($admin_info['email'] ?? '') ?>">
                    <div class="form-grid">
                        <div class="form-group full"><label>New Password <span class="req">*</span></label>
                            <div class="pw-wrap"><input type="password" name="new_password" id="profile-pw2-input" placeholder="Enter new password" required><button type="button" class="pw-toggle" onclick="toggleProfilePw2()">&#128065;</button></div>
                        </div>
                        <div class="form-group full"><label>Confirm New Password <span class="req">*</span></label>
                            <div class="pw-wrap"><input type="password" id="profile-pw-confirm" placeholder="Re-enter new password"></div>
                            <span class="hint" id="pw-match-hint" style="color:var(--red);display:none">Passwords do not match.</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="switchProfileTab('tab-profile-view', document.querySelector('#profile-tabs .tab-btn'))">Cancel</button>
                        <button type="submit" class="btn btn-primary" onclick="return checkPwMatch()">Update Password</button>
                    </div>
                </form>
            </div>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:space-between">
                <a href="http://localhost/SPAC/logout.php" class="btn btn-sm" style="background:var(--red-l);color:var(--red);border-color:transparent">Logout</a>
                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-profile')">Close</button>
            </div>
        </div>
    </div>
</div>


<script>
(function() {
    var d = new Date();
    document.getElementById('topbar-date').textContent = d.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
})();

function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

document.querySelectorAll('.modal-backdrop').forEach(function(b) {
    b.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.open').forEach(function(m){ closeModal(m.id); });
});

// ── BARANGAY DIRECTORY ─────────────────────────────────────────
function closeBrgyModal() { closeModal('modal-allbrgy'); setTimeout(backToBrgyList, 300); }
function backToBrgyList() {
    document.getElementById('brgy-list-view').style.display = '';
    document.getElementById('brgy-detail-view').classList.remove('visible');
}
function showBrgyDetail(brgy) {
    document.getElementById('brgy-list-view').style.display = 'none';
    document.getElementById('brgy-detail-view').classList.add('visible');

    // Render cached data instantly so modal feels fast
    renderBrgyDetail(brgy);

    // Show loading spinners on the live-count fields only
    document.getElementById('bdet-residents').textContent = '...';
    document.getElementById('bdet-families').textContent  = '...';

    // Fetch fresh counts from get_barangay_details.php
     fetch('get_barangay_details.php?barangay_id=' + brgy.barangay_id)
        .then(function(res) { return res.json(); })
        .then(function(data) {
    if (data && data.counts) {
        document.getElementById('bdet-residents').textContent = parseInt(data.counts.residents || 0).toLocaleString();
        document.getElementById('bdet-families').textContent  = parseInt(data.counts.families  || 0).toLocaleString();
    }

    // ── Barangay logo ──────────────────────────────────────
    var img    = document.getElementById('bdet-avatar-img');
    var letter = document.getElementById('bdet-avatar-letter');
    var logo   = (data && data.barangay && data.barangay.logo) ? data.barangay.logo.trim() : '';

    if (logo && logo !== 'null' && logo.length > 10) {
        img.onload = function() {
            img.style.display = 'block';
            letter.style.display = 'none';
        };
        img.onerror = function() {
            img.style.display = 'none';
            letter.style.display = '';
        };
        img.src = logo;
    } else {
        img.src = '';
        img.style.display = 'none';
        letter.style.display = '';
    }

    // ── Captain photo ──────────────────────────────────────
    var capImg    = document.getElementById('bdet-captain-img');
    var capLetter = document.getElementById('bdet-captain-letter');
    var capPhoto  = (data && data.barangay && data.barangay.captain_photo) ? data.barangay.captain_photo.trim() : '';

    if (capPhoto && capPhoto !== 'null' && capPhoto.length > 10) {
        capImg.onload = function() {
            capImg.style.display = 'block';
            capLetter.style.display = 'none';
        };
        capImg.onerror = function() {
            capImg.style.display = 'none';
            capLetter.style.display = '';
        };
        capImg.src = capPhoto;
    } else {
        capImg.src = '';
        capImg.style.display = 'none';
        capLetter.style.display = '';
    }
})
      
}

function renderBrgyDetail(brgy) {
    document.getElementById('bdet-avatar-letter').textContent = (brgy.name || 'B').charAt(0).toUpperCase();
    document.getElementById('bdet-name').textContent = brgy.name || '—';
    document.getElementById('bdet-district').textContent = brgy.district || 'No district set';

    var tagsEl = document.getElementById('bdet-tags');
    tagsEl.innerHTML = '';
    tagsEl.innerHTML += '<span class="tag ' + (brgy.status === 'active' ? 'tag-active' : 'tag-inactive') + '">' + (brgy.status === 'active' ? 'Active' : 'Inactive') + '</span>';
    if (parseInt(brgy.is_pilot)) tagsEl.innerHTML += '<span class="tag tag-pilot" style="margin-left:6px">Pilot</span>';

    document.getElementById('bdet-residents').textContent  = parseInt(brgy.total_residents || 0).toLocaleString();
    document.getElementById('bdet-families').textContent   = parseInt(brgy.total_families  || 0).toLocaleString();
    document.getElementById('bdet-population').textContent = brgy.population && parseInt(brgy.population) > 0 ? parseInt(brgy.population).toLocaleString() : '—';

    // AFTER
    var capName = brgy.captain_name && brgy.captain_name.trim() ? brgy.captain_name : null;
    document.getElementById('bdet-captain-name').textContent = capName ? 'Kap. ' + capName : 'Not assigned';
    document.getElementById('bdet-captain-letter').textContent = capName ? capName.charAt(0).toUpperCase() : '?';

    var sinceText = 'Barangay Captain';
    if (brgy.captain_since && brgy.captain_since.trim()) sinceText += ' · Since ' + brgy.captain_since;
    document.getElementById('bdet-captain-since').textContent = sinceText;

    document.getElementById('bdet-email').textContent         = brgy.email || '—';
    document.getElementById('bdet-contact').textContent       = brgy.contact_number || '—';
    document.getElementById('bdet-address').textContent       = brgy.address || '—';
    document.getElementById('bdet-district-info').textContent = brgy.district || '—';
    document.getElementById('bdet-founded').textContent       = brgy.founded_year && parseInt(brgy.founded_year) > 0 ? brgy.founded_year : '—';
}
function filterBrgy(val) {
    var v = val.toLowerCase(); var hasVisible = false;
    document.querySelectorAll('#brgy-list-container .brgy-list-item').forEach(function(el) {
        var show = el.dataset.search.indexOf(v) >= 0;
        el.style.display = show ? '' : 'none';
        if (show) hasVisible = true;
    });
    var empty = document.getElementById('brgy-no-results');
    if (!empty) { empty = document.createElement('div'); empty.id = 'brgy-no-results'; empty.className = 'modal-empty'; document.getElementById('brgy-list-container').appendChild(empty); }
    empty.textContent = 'No barangay found matching "' + val + '".';
    empty.style.display = (!hasVisible && v.length > 0) ? '' : 'none';
}

// ── USER ACCOUNTS ──────────────────────────────────────────────
var activeRoleFilter = 'all';
function filterUsers(val) {
    var v = val.toLowerCase(); var hasVisible = false;
    document.querySelectorAll('#user-accounts-list .user-card').forEach(function(el) {
        var show = el.dataset.search.indexOf(v) >= 0 && (activeRoleFilter === 'all' || el.dataset.role === activeRoleFilter);
        el.style.display = show ? '' : 'none';
        if (show) hasVisible = true;
    });
    updateGroupVisibility();
}
function setRoleFilter(role, btn) {
    activeRoleFilter = role;
    document.querySelectorAll('.role-filter-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.user-group').forEach(function(g) {
        g.style.display = (role === 'all' || g.dataset.role === role) ? '' : 'none';
    });
    filterUsers(document.getElementById('user-search-input').value);
}
function updateGroupVisibility() {
    if (activeRoleFilter !== 'all') return;
    document.querySelectorAll('.user-group').forEach(function(g) {
        var visible = Array.from(g.querySelectorAll('.user-card')).some(function(c){ return c.style.display !== 'none'; });
        g.style.display = visible ? '' : 'none';
    });
}

// ── TABS ───────────────────────────────────────────────────────
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}
function switchProfileTab(tabId, btn) {
    ['tab-profile-view','tab-profile-edit','tab-profile-pw'].forEach(function(t){
        var el = document.getElementById(t);
        if (el) el.classList.remove('active');
    });
    document.querySelectorAll('#profile-tabs .tab-btn').forEach(function(b){ b.classList.remove('active'); });
    var target = document.getElementById(tabId);
    if (target) target.classList.add('active');
    if (btn) btn.classList.add('active');
}

// ── AYUDA EDIT ─────────────────────────────────────────────────
function showEditForm() { document.getElementById('edit-form-section').classList.add('visible'); document.getElementById('details-actions').style.display = 'none'; }
function hideEditForm() { document.getElementById('edit-form-section').classList.remove('visible'); document.getElementById('details-actions').style.display = 'flex'; }
function openAyudaEdit(id, name, type, source, origin, start, end) {
    document.getElementById('view-ayuda-name').textContent   = name || '—';
    document.getElementById('view-ayuda-type').textContent   = type || '—';
    document.getElementById('view-ayuda-source').textContent = source === 'cityhall' ? 'City Hall' : 'Barangay';
    document.getElementById('view-ayuda-origin').textContent = origin || '—';
    document.getElementById('view-ayuda-start').textContent  = start ? new Date(start).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}) : '—';
    document.getElementById('view-ayuda-end').textContent    = end   ? new Date(end).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'}) : '—';
    document.getElementById('edit-record-id').value  = id;
    document.getElementById('edit-ayuda-name').value = name;
    document.getElementById('edit-ayuda-start').value = start;
    document.getElementById('edit-mark-complete').checked = false;
    var ts = document.getElementById('edit-ayuda-type');
    ts.selectedIndex = 0;
    for (var i = 0; i < ts.options.length; i++) { if (ts.options[i].value === type) { ts.selectedIndex = i; break; } }
    hideEditForm();
    openModal('modal-edit-ayuda');
}

// ── FORM HELPERS ───────────────────────────────────────────────
function toggleBrgyField(role) { document.getElementById('brgy-field').style.display = role === 'barangay' ? 'flex' : 'none'; }
toggleBrgyField(document.getElementById('role-select').value);
function toggleAyudaBrgyField(s) { document.getElementById('ayuda-brgy-field').style.display = s === 'barangay' ? 'flex' : 'none'; }
function toggleAyudaOther(val) {
    var of = document.getElementById('ayuda-other-field'), oi = document.getElementById('ayuda-type-other'), sel = document.getElementById('ayuda-type-select');
    if (val === 'Others') { of.style.display='flex'; oi.required=true; oi.name='ayuda_type'; sel.name=''; oi.focus(); }
    else { of.style.display='none'; oi.required=false; oi.name='ayuda_type_other'; sel.name='ayuda_type'; }
}
function togglePw() { var i=document.getElementById('pw-input'); i.type=i.type==='password'?'text':'password'; }
function toggleProfilePw()  { var i=document.getElementById('profile-pw-input');  if(i) i.type=i.type==='password'?'text':'password'; }
function toggleProfilePw2() { var i=document.getElementById('profile-pw2-input'); if(i) i.type=i.type==='password'?'text':'password'; }
function checkPwMatch() {
    var p1 = document.getElementById('profile-pw2-input').value;
    var p2 = document.getElementById('profile-pw-confirm').value;
    if (p1 !== p2) { document.getElementById('pw-match-hint').style.display = ''; return false; }
    return true;
}

// ── ONLINE STATUS ──────────────────────────────────────────────
function refreshOnlineStatus() {
    fetch('get_online_status.php')
        .then(function(res){ return res.json(); })
        .then(function(data) {
            document.querySelectorAll('.online-dot').forEach(function(dot) {
                var uid = dot.id.replace('dot-','');
                if (data[uid] !== undefined) {
                    var online = data[uid].is_online === 1;
                    dot.className = 'online-dot ' + (online ? 'is-online' : 'is-offline');
                    dot.title = online ? 'Online' : (data[uid].last_seen ? 'Last seen: ' + data[uid].last_seen : 'Offline');
                }
            });
        }).catch(function(){});
}
setTimeout(refreshOnlineStatus, 5000);
setInterval(refreshOnlineStatus, 300000);

<?php if ($action_message && $action_type === 'error'): ?>
<?php if (isset($_POST['action']) && $_POST['action'] === 'add_barangay'): ?>
window.addEventListener('load', function() { openModal('modal-add-barangay'); });
<?php elseif (isset($_POST['action']) && $_POST['action'] === 'add_user'): ?>
window.addEventListener('load', function() { openModal('modal-add-user'); });
<?php elseif (isset($_POST['action']) && $_POST['action'] === 'add_ayuda'): ?>
window.addEventListener('load', function() { openModal('modal-add-ayuda'); });
<?php elseif (isset($_POST['action']) && $_POST['action'] === 'edit_ayuda'): ?>
window.addEventListener('load', function() { openModal('modal-ayuda'); });
<?php elseif (isset($_POST['action']) && $_POST['action'] === 'update_profile'): ?>
window.addEventListener('load', function() { openModal('modal-profile'); });
<?php endif; ?>
<?php endif; ?>

var msg = document.querySelector('.alert-banner');
if (msg) setTimeout(function(){ msg.style.transition = '0.4s'; msg.style.opacity = '0'; setTimeout(function(){ msg.remove(); }, 400); }, 3500);


// ── KEEPALIVE: prevent logout on idle ─────────────────────────
(function keepAlive() {
    setInterval(function() {
        fetch('keepalive.php', {
            method: 'GET',
            credentials: 'same-origin'
        }).catch(function() {});
    }, 120000);
})();

// ── REALTIME POLLING: reflect barangay changes ─────────────────
function fetchLiveStats() {
    fetch('get_live_stats.php')
        .then(function(res) { return res.json(); })
        .then(function(data) { applyLiveStats(data); })
        .catch(function() {});
}
function fmtNum(n) { return parseInt(n).toLocaleString(); }

function applyLiveStats(data) {
    // Top stats grid
    var topMap = {
        'top-stat-residents': data.total_residents,
        'top-stat-barangays': data.total_barangays,
        'top-stat-ayuda':     data.total_ayuda,
        'top-stat-users':     data.total_users,
    };
    Object.keys(topMap).forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.textContent = fmtNum(topMap[id]);
    });

    // Analytics cards
    var analyticsMap = {
        'analytics-residents':  data.total_residents,
        'analytics-families':   data.total_families,
        'analytics-activebrgy': data.active_barangays,
        'analytics-ongoing':    data.ongoing_ayuda,
        'analytics-scans':      data.total_scans,
        'analytics-alerts':     data.total_alerts,
    };
    Object.keys(analyticsMap).forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.textContent = fmtNum(analyticsMap[id]);
    });

    // Alert card danger color
    var alertCard = document.getElementById('analytics-alerts-card');
    if (alertCard) alertCard.classList.toggle('danger', parseInt(data.total_alerts) > 0);

    // Sidebar badges
    var sidebarMap = {
        'sidebar-badge-barangays': data.total_barangays,
        'sidebar-badge-residents': data.total_residents,
        'sidebar-badge-families':  data.total_families,
        'sidebar-badge-ongoing':   data.ongoing_ayuda,
        'sidebar-badge-scans':     data.total_scans,
        'sidebar-badge-users':     data.total_users,
        'sidebar-badge-alerts':    data.total_alerts,
    };
    Object.keys(sidebarMap).forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = fmtNum(sidebarMap[id]);
        if (id === 'sidebar-badge-alerts') {
            el.style.display = parseInt(data.total_alerts) > 0 ? '' : 'none';
        }
    });

    // Refresh per-barangay tables in open modals
    refreshBrgyTable('tbl-modal-residents', data.brgy_residents, 'total');
    refreshBrgyTable('tbl-modal-families',  data.brgy_families,  'total');

    // Live indicator
    var ind = document.getElementById('live-indicator');
    if (ind) {
        ind.textContent = 'Updated ' + new Date().toLocaleTimeString('en-PH', {hour:'2-digit', minute:'2-digit'});
        ind.style.opacity = '1';
        setTimeout(function(){ ind.style.opacity = '0.5'; }, 1500);
    }
}

function refreshBrgyTable(tableId, rows, countKey) {
    var tbl = document.getElementById(tableId);
    if (!tbl) return;
    var tbody = tbl.querySelector('tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    rows.forEach(function(row, i) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td style="color:var(--muted);font-size:12px">' + String(i+1).padStart(2,'0') + '</td>' +
            '<td>' + row.name + '</td>' +
            '<td class="num">' + fmtNum(row[countKey]) + '</td>';
        tbody.appendChild(tr);
    });
}
setTimeout(fetchLiveStats, 5000);
setInterval(fetchLiveStats, 300000);
</script>
</body>
</html>
