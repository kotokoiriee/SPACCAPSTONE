<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once '../../config/auth_guard.php';

require_role('barangay');

require_once '../../config/db.php';



$bid = (int)$_SESSION['barangay_id'];

$uid = (int)$_SESSION['user_id'];



// �??????�?????? Handle POST actions �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????

if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    if (isset($_POST['update_account'])) {

        $new_name = $conn->real_escape_string(trim($_POST['account_fullname']));

        $new_pass = $_POST['account_password'] ?? '';

        if ($new_name) {

            if ($new_pass) {

                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

                $hashed = $conn->real_escape_string($hashed);

                $conn->query("UPDATE users SET full_name='$new_name', password_hash='$hashed' WHERE user_id=$uid");

            } else {

                $conn->query("UPDATE users SET full_name='$new_name' WHERE user_id=$uid");

            }

            $_SESSION['full_name'] = $new_name;

        }

        session_write_close();

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=profile_updated');

        exit;

    }



    session_write_close();



  if (isset($_POST['add_official'])) {

    $name    = $conn->real_escape_string(trim($_POST['official_name']));

    $pos     = $conn->real_escape_string(trim($_POST['official_position']));

    $contact = $conn->real_escape_string(trim($_POST['official_contact'] ?? ''));

    $email   = $conn->real_escape_string(trim($_POST['official_email'] ?? ''));



    // Handle photo BEFORE the INSERT

    $photo = '';

    if (!empty($_FILES['official_photo']['tmp_name'])) {

        $ext     = strtolower(pathinfo($_FILES['official_photo']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg','jpeg','png','gif','webp'];

        if (in_array($ext, $allowed)) {

            $imgdata = file_get_contents($_FILES['official_photo']['tmp_name']);

            $mime    = mime_content_type($_FILES['official_photo']['tmp_name']);

            $photo   = $conn->real_escape_string("data:$mime;base64," . base64_encode($imgdata));

        }

    }



    // Single INSERT ? no follow-up UPDATE needed, avoids trigger conflict

    $conn->query("INSERT INTO barangay_officials (barangay_id, full_name, position, contact_number, email, photo) 

                  VALUES ($bid, '$name', '$pos', '$contact', '$email', '$photo')");



    if (stripos($pos, 'captain') !== false) {

        $conn->query("UPDATE barangays SET captain_name = '$name' WHERE barangay_id = $bid");

    }



    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=official_added');

    exit;

}

   if (isset($_POST['delete_official'])) {

        $oid = (int)$_POST['official_id'];

        $conn->query("UPDATE barangay_officials SET is_active = 0 WHERE official_id = $oid AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=official_removed');

        exit;

    }



   if (isset($_POST['edit_official'])) {

        $oid     = (int)$_POST['official_id'];

        $name    = $conn->real_escape_string(trim($_POST['official_name']));

        $pos     = $conn->real_escape_string(trim($_POST['official_position']));

        $contact = $conn->real_escape_string(trim($_POST['official_contact'] ?? ''));

        $email   = $conn->real_escape_string(trim($_POST['official_email'] ?? ''));

        $conn->query("UPDATE barangay_officials SET full_name='$name', position='$pos', contact_number='$contact', email='$email' WHERE official_id=$oid AND barangay_id=$bid");

        if (stripos($pos, 'captain') !== false) {

            $conn->query("UPDATE barangays SET captain_name='$name' WHERE barangay_id=$bid");

        }

        // Also save photo if uploaded

        if (!empty($_FILES['official_photo']['tmp_name'])) {

            $ext     = strtolower(pathinfo($_FILES['official_photo']['name'], PATHINFO_EXTENSION));

            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (in_array($ext, $allowed)) {

                $imgdata = file_get_contents($_FILES['official_photo']['tmp_name']);

                $mime    = mime_content_type($_FILES['official_photo']['tmp_name']);

                $photo   = $conn->real_escape_string("data:$mime;base64," . base64_encode($imgdata));

                $conn->query("UPDATE barangay_officials SET photo='$photo' WHERE official_id=$oid AND barangay_id=$bid");

            }

        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=official_updated');

        exit;

    }

    if (isset($_POST['upload_official_photo']) && !empty($_FILES['official_photo']['tmp_name'])) {

    $oid     = (int)$_POST['official_id'];

    $ext     = strtolower(pathinfo($_FILES['official_photo']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (in_array($ext, $allowed)) {

        $imgdata = file_get_contents($_FILES['official_photo']['tmp_name']);

        $mime    = mime_content_type($_FILES['official_photo']['tmp_name']);

        $photo   = $conn->real_escape_string("data:$mime;base64," . base64_encode($imgdata));

        $conn->query("UPDATE barangay_officials SET photo='$photo' WHERE official_id=$oid AND barangay_id=$bid");

    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=profile_updated');

    exit;

}



    if (isset($_POST['add_zone_leader'])) {

        $zone    = (int)$_POST['zone_number'];

        $zname   = $conn->real_escape_string(trim($_POST['zone_name'] ?? ''));

        $leader  = $conn->real_escape_string(trim($_POST['leader_name']));

        $contact = $conn->real_escape_string(trim($_POST['leader_contact'] ?? ''));

        $conn->query("INSERT INTO zone_leaders (barangay_id, zone_number, zone_name, leader_name, contact_number) VALUES ($bid, $zone, '$zname', '$leader', '$contact')");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=zone_added');

        exit;

    }



    if (isset($_POST['delete_zone_leader'])) {

        $zlid = (int)$_POST['zone_leader_id'];

        $conn->query("UPDATE zone_leaders SET is_active = 0 WHERE id = $zlid AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=zone_removed');

        exit;

    }



    if (isset($_POST['edit_zone_leader'])) {

        $zlid    = (int)$_POST['zone_leader_id'];

        $zname   = $conn->real_escape_string(trim($_POST['zone_name'] ?? ''));

        $leader  = $conn->real_escape_string(trim($_POST['leader_name']));

        $contact = $conn->real_escape_string(trim($_POST['leader_contact'] ?? ''));

        $conn->query("UPDATE zone_leaders SET zone_name='$zname', leader_name='$leader', contact_number='$contact' WHERE id = $zlid AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=zone_updated');

        exit;

    }



    if (isset($_POST['add_alert'])) {

        $msg = $conn->real_escape_string(trim($_POST['alert_message']));

        $sev = in_array($_POST['alert_severity'], ['low','medium','high']) ? $_POST['alert_severity'] : 'low';

        $conn->query("INSERT INTO alerts (barangay_id, message, severity) VALUES ($bid, '$msg', '$sev')");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=alert_posted');

        exit;

    }



    if (isset($_POST['resolve_alert'])) {

        $aid = (int)$_POST['alert_id'];

        $conn->query("UPDATE alerts SET resolved = 1 WHERE alert_id = $aid AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=alert_resolved');

        exit;

    }



    if (isset($_POST['update_brgy_logo']) || (isset($_POST['update_profile']) && !empty($_FILES['brgy_logo']['tmp_name']))) {

        if (!empty($_FILES['brgy_logo']['tmp_name'])) {

            $ext     = strtolower(pathinfo($_FILES['brgy_logo']['name'], PATHINFO_EXTENSION));

            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (in_array($ext, $allowed)) {

                $imgdata = file_get_contents($_FILES['brgy_logo']['tmp_name']);

                $mime    = mime_content_type($_FILES['brgy_logo']['tmp_name']);

                $logo    = $conn->real_escape_string("data:$mime;base64," . base64_encode($imgdata));

                $conn->query("UPDATE barangays SET logo='$logo' WHERE barangay_id=$bid");

            }

        }

        if (isset($_POST['update_brgy_logo']) && !isset($_POST['update_profile'])) {

            header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=logo_updated');

            exit;

        }

    }



    if (isset($_POST['update_profile'])) {

        $captain         = $conn->real_escape_string(trim($_POST['captain_name'] ?? ''));

        $email           = $conn->real_escape_string(trim($_POST['brgy_email'] ?? ''));

        $contact         = $conn->real_escape_string(trim($_POST['brgy_contact'] ?? ''));

        $district        = $conn->real_escape_string(trim($_POST['brgy_district'] ?? ''));

        $address         = $conn->real_escape_string(trim($_POST['brgy_address'] ?? ''));

        $founded_year    = (int)($_POST['brgy_founded_year'] ?? 0);

        $pop             = (int)($_POST['brgy_population'] ?? 0);

        $allowed_labels  = ['Zone','Purok','Sitio','Street','Subdivision','Block'];

        $area_label_save = in_array($_POST['area_label'] ?? '', $allowed_labels) ? $_POST['area_label'] : 'Zone';

        $fy_sql          = $founded_year > 0 ? $founded_year : 'NULL';

        $conn->query("UPDATE barangays SET 

            captain_name='$captain', 

            email='$email', 

            contact_number='$contact', 

            district='$district',

            address='$address',

            founded_year=$fy_sql,

            population=$pop, 

            area_label='$area_label_save' 

            WHERE barangay_id=$bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=profile_updated');

        exit;

    }



    if (isset($_POST['add_family'])) {

        $head_name   = $conn->real_escape_string(trim($_POST['head_name']));

        $zone_number = (int)$_POST['zone_number'];

        $address     = $conn->real_escape_string(trim($_POST['address'] ?? ''));

        $conn->query("INSERT INTO families (barangay_id, head_name, zone_number, address, member_count) VALUES ($bid, '$head_name', $zone_number, '$address', 0)");

        $family_id = $conn->insert_id;

        $bdate     = $conn->real_escape_string(trim($_POST['head_birthdate'] ?? ''));

        $contact   = $conn->real_escape_string(trim($_POST['head_contact'] ?? ''));

        $conn->query("INSERT INTO residents (barangay_id, family_id, full_name, birth_date, contact_number, zone_number, relationship, is_active) VALUES ($bid, $family_id, '$head_name', '$bdate', '$contact', $zone_number, 'Head', 1)");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=family_added');

        exit;

    }



    if (isset($_POST['add_family_member'])) {

        $family_id = (int)$_POST['family_id'];

        $full_name = $conn->real_escape_string(trim($_POST['member_name']));

        $bdate     = $conn->real_escape_string(trim($_POST['member_birthdate'] ?? ''));

        $contact   = $conn->real_escape_string(trim($_POST['member_contact'] ?? ''));

        $relation  = $conn->real_escape_string(trim($_POST['member_relationship'] ?? ''));

        $frow = $conn->query("SELECT zone_number FROM families WHERE family_id = $family_id AND barangay_id = $bid");

        $zone_number = 0;

        if ($frow && $f = $frow->fetch_assoc()) $zone_number = (int)$f['zone_number'];

        if (!$conn->ping()) {

            $conn->close();

            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        }

        $conn->query("INSERT INTO residents (barangay_id, family_id, full_name, birth_date, contact_number, zone_number, relationship, is_active) VALUES ($bid, $family_id, '$full_name', '$bdate', '$contact', $zone_number, '$relation', 1)");

       $conn->query("UPDATE families SET member_count = (SELECT COUNT(*) FROM residents WHERE family_id = $family_id AND is_active = 1) WHERE family_id = $family_id");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=member_added');

        exit;

    }



    if (isset($_POST['edit_family'])) {

        $fid       = (int)$_POST['family_id'];

        $head_name = $conn->real_escape_string(trim($_POST['head_name']));

        $address   = $conn->real_escape_string(trim($_POST['address'] ?? ''));

        $conn->query("UPDATE families SET head_name='$head_name', address='$address' WHERE id = $fid AND barangay_id = $bid");

        $conn->query("UPDATE residents SET full_name='$head_name' WHERE family_id = $fid AND relationship = 'Head' AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=family_updated');

        exit;

    }

if (isset($_POST['delete_family'])) {

    $fid = (int)$_POST['family_id'];



    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $conn->query("DELETE FROM residents WHERE family_id = $fid AND barangay_id = $bid");

    $conn->query("DELETE FROM families WHERE family_id = $fid AND barangay_id = $bid");

    $conn->query("SET FOREIGN_KEY_CHECKS = 1");



    header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=family_deleted');

    exit;

}

    if (isset($_POST['delete_member'])) {

        $rid = (int)$_POST['resident_id'];

        $fid = (int)$_POST['family_id'];

        $conn->query("UPDATE residents SET is_active = 0 WHERE resident_id = $rid AND barangay_id = $bid");

        $conn->query("UPDATE families SET member_count = (SELECT COUNT(*) FROM residents WHERE family_id = $fid AND is_active = 1) WHERE id = $fid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=member_deleted');

        exit;

    }



    if (isset($_POST['edit_member'])) {

        $rid      = (int)$_POST['resident_id'];

        $fname    = $conn->real_escape_string(trim($_POST['member_name']));

        $bdate    = $conn->real_escape_string(trim($_POST['member_birthdate'] ?? ''));

        $contact  = $conn->real_escape_string(trim($_POST['member_contact'] ?? ''));

        $relation = $conn->real_escape_string(trim($_POST['member_relationship'] ?? ''));

        $conn->query("UPDATE residents SET full_name='$fname', birth_date='$bdate', contact_number='$contact', relationship='$relation' WHERE resident_id = $rid AND barangay_id = $bid");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=member_updated');

        exit;

    }



    if (isset($_POST['add_assistance_record'])) {

        $aname = $conn->real_escape_string(trim($_POST['assistance_name']));

        $atype = $conn->real_escape_string(trim($_POST['assistance_type']));

        $src   = $conn->real_escape_string(trim($_POST['assistance_source']));

        $sdate = $conn->real_escape_string(trim($_POST['assistance_start_date']));

        $edate = !empty($_POST['assistance_end_date']) ? "'" . $conn->real_escape_string(trim($_POST['assistance_end_date'])) . "'" : 'NULL';

        $desc  = $conn->real_escape_string(trim($_POST['assistance_description'] ?? ''));

        $conn->query("INSERT INTO assistance_programs (barangay_id, name, type, source, start_date, end_date, description) VALUES ($bid, '$aname', '$atype', '$src', '$sdate', $edate, '$desc')");

        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=assistance_saved');

        exit;

    }

}



// �??????�?????? Flash messages from redirect �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????

$flash_messages = [

    'family_added'     => ['Family added successfully.', 'success'],

    'family_updated'   => ['Family updated.', 'success'],

    'family_deleted'   => ['Family removed.', 'success'],

    'member_added'     => ['Member added successfully.', 'success'],

    'member_updated'   => ['Member updated.', 'success'],

    'member_deleted'   => ['Member removed.', 'success'],

    'official_added'   => ['Official added.', 'success'],

    'official_removed' => ['Official removed.', 'success'],

    'official_updated' => ['Official updated.', 'success'], 

    'zone_added'       => ['Zone leader added.', 'success'],

    'zone_removed'     => ['Zone leader removed.', 'success'],

    'zone_updated'     => ['Zone leader updated.', 'success'],

    'alert_posted'     => ['Alert posted.', 'success'],

    'alert_resolved'   => ['Alert resolved.', 'success'],

    'profile_updated'  => ['Profile updated.', 'success'],

    'logo_updated'     => ['Logo updated.', 'success'],

    'assistance_saved' => ['Assistance record saved.', 'success'],

];



$action_message = '';

$action_type    = '';

if (!empty($_GET['msg']) && isset($flash_messages[$_GET['msg']])) {

    [$action_message, $action_type] = $flash_messages[$_GET['msg']];

}



// �??????�?????? Data fetching �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????

$assistance_programs = [];

$r = $conn->query("SELECT program_id as id, name, type, source, start_date FROM assistance_programs WHERE barangay_id = $bid ORDER BY start_date DESC");

if ($r) while ($row = $r->fetch_assoc()) $assistance_programs[] = $row;



$cityhall_programs = [];

$r = $conn->query("SELECT record_id as id, ayuda_name as name, ayuda_type as type, source, start_date FROM ayuda_records WHERE source = 'cityhall' ORDER BY start_date DESC");

if ($r) while ($row = $r->fetch_assoc()) $cityhall_programs[] = $row;



$families_by_zone = [];

$r = $conn->query("SELECT f.*, (SELECT COUNT(*) FROM residents r WHERE r.family_id = f.id AND r.is_active = 1) as member_count FROM families f WHERE f.barangay_id = $bid ORDER BY f.zone_number ASC, f.head_name ASC");

if ($r) while ($row = $r->fetch_assoc()) $families_by_zone[$row['zone_number']][] = $row;



$brgy_info = [];

$r = $conn->query("SELECT * FROM barangays WHERE barangay_id = $bid");

if ($r && $r->num_rows > 0) $brgy_info = $r->fetch_assoc();

$area_label        = $brgy_info['area_label'] ?? 'Zone';

$area_label_plural = $area_label . 's';



$total_residents = $total_families = $total_officials = $total_zones = $active_alerts = 0;

$r = $conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id = $bid AND is_active = 1"); if ($r) $total_residents = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM families WHERE barangay_id = $bid"); if ($r) $total_families = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM barangay_officials WHERE barangay_id = $bid AND is_active = 1"); if ($r) $total_officials = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(DISTINCT zone_number) as c FROM zone_leaders WHERE barangay_id = $bid AND is_active = 1"); if ($r) $total_zones = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM alerts WHERE barangay_id = $bid AND resolved = 0"); if ($r) $active_alerts = $r->fetch_assoc()['c'];



$officials = [];

$r = $conn->query("SELECT *, official_id as id FROM barangay_officials WHERE barangay_id = $bid AND is_active = 1 ORDER BY FIELD(position, 'Barangay Captain', 'Barangay Secretary', 'Barangay Treasurer', 'Kagawad', 'SK Chairman', 'Barangay Health Worker', 'Tanod', 'Staff', 'Other') ASC");

if ($r) while ($row = $r->fetch_assoc()) $officials[] = $row;



$zone_leaders = [];

$r = $conn->query("SELECT * FROM zone_leaders WHERE barangay_id = $bid AND is_active = 1 ORDER BY zone_number ASC");

if ($r) while ($row = $r->fetch_assoc()) $zone_leaders[] = $row;



$residents = [];

$r = $conn->query("SELECT * FROM residents WHERE barangay_id = $bid AND is_active = 1 ORDER BY full_name ASC LIMIT 500");

if ($r) while ($row = $r->fetch_assoc()) $residents[] = $row;



$families = [];

$r = $conn->query("SELECT * FROM families WHERE barangay_id = $bid ORDER BY head_name ASC LIMIT 500");

if ($r) while ($row = $r->fetch_assoc()) $families[] = $row;



$zones_residents = [];

$r = $conn->query("SELECT zone_number, COUNT(*) as cnt FROM residents WHERE barangay_id = $bid AND zone_number IS NOT NULL AND zone_number > 0 AND is_active = 1 GROUP BY zone_number ORDER BY zone_number ASC");
if ($r) while ($row = $r->fetch_assoc()) $zones_residents[$row['zone_number']] = $row['cnt'];



$zones_families = [];

$r = $conn->query("SELECT zone_number, COUNT(*) as cnt FROM families WHERE barangay_id = $bid AND zone_number IS NOT NULL AND zone_number > 0 GROUP BY zone_number ORDER BY zone_number ASC");

if ($r) while ($row = $r->fetch_assoc()) $zones_families[$row['zone_number']] = $row['cnt'];



$ayuda_types = [];

$r = $conn->query("SELECT * FROM ayuda_types WHERE barangay_id = $bid OR barangay_id IS NULL ORDER BY name ASC");

if ($r) while ($row = $r->fetch_assoc()) $ayuda_types[] = $row;



$ayuda_records = [];

$r = $conn->query("SELECT ar.*, r.full_name, at2.name as ayuda_name FROM ayuda_records ar LEFT JOIN residents r ON ar.resident_id = r.resident_id LEFT JOIN ayuda_types at2 ON ar.ayuda_type_id = at2.id WHERE ar.barangay_id = $bid ORDER BY ar.date_given DESC LIMIT 200");

if ($r) while ($row = $r->fetch_assoc()) $ayuda_records[] = $row;



$alerts = [];

$r = $conn->query("SELECT * FROM alerts WHERE barangay_id = $bid ORDER BY resolved ASC, created_at DESC");

if ($r) while ($row = $r->fetch_assoc()) $alerts[] = $row;



$qr_history = [];

$r = $conn->query("SELECT qh.*, r.full_name FROM qr_history qh LEFT JOIN residents r ON qh.resident_id = r.resident_id WHERE qh.barangay_id = $bid ORDER BY qh.scan_time DESC LIMIT 100");

if ($r) while ($row = $r->fetch_assoc()) $qr_history[] = $row;



$admin_info = [];

$r = $conn->query("SELECT user_id, full_name, email, role, created_at FROM users WHERE user_id = $uid");

if ($r) $admin_info = $r->fetch_assoc();

?>



<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Barangay Dashboard ? SPAC</title>



<style>

/* �??????�?????? WHITE BACKGROUND THEME �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

:root {

    --white:      #ffffff;

    --surface:    #ffffff;      /* changed: was #f4f6f9, now white */

    --surface-2:  #f1f4f8;      /* changed: was #eef0f4, slightly deeper for contrast */

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

}



* { margin: 0; padding: 0; box-sizing: border-box; }

body { font-family: 'DM Sans', sans-serif; background: var(--white); display: flex; min-height: 100vh; color: var(--text); font-size: 14px; line-height: 1.5; }



/* �??????�?????? SIDEBAR �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.sidebar {

    width: 232px; background: var(--white); min-height: 100vh;

    display: flex; flex-direction: column; position: fixed;

    top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100;

}

.sidebar-logo {

    padding: 24px 20px 20px; border-bottom: 1px solid var(--border);

}

.sidebar-logo h1 {

    color: var(--navy); font-size: 16px; font-weight: 600;

    letter-spacing: 0.08em; text-transform: uppercase;

}

.sidebar-logo p { color: var(--muted); font-size: 11px; margin-top: 2px; font-weight: 400; }

.sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; }

.sidebar-menu::-webkit-scrollbar { width: 0; }

.menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }

.menu-item {

    display: flex; align-items: center; gap: 10px; padding: 8px 20px;

    color: var(--muted); font-size: 13px; font-weight: 400;

    transition: all 0.15s; cursor: pointer; background: none;

    border: none; width: 100%; text-align: left; font-family: 'DM Sans', sans-serif;

}

.menu-item:hover { color: var(--text); background: var(--surface-2); }

.menu-item.active { color: var(--navy); font-weight: 500; background: var(--navy-light); }

.menu-item.active .menu-dot { background: var(--navy); }

.menu-dot {

    width: 5px; height: 5px; border-radius: 50%;

    background: var(--border); flex-shrink: 0; transition: background 0.15s;

}

.menu-item:hover .menu-dot { background: var(--text); }

.menu-badge {

    margin-left: auto; background: var(--surface-2); color: var(--muted);

    font-size: 11px; font-weight: 500; padding: 1px 7px;

    border-radius: 20px; font-family: 'DM Mono', monospace;

}

.menu-badge.alert { background: var(--red-l); color: var(--red); }

.sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }

.sidebar-footer a {

    display: flex; align-items: center; gap: 10px; color: var(--muted);

    text-decoration: none; font-size: 13px; transition: color 0.15s;

}

.sidebar-footer a:hover { color: var(--text); }



/* �??????�?????? MAIN �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.main { margin-left: 232px; flex: 1; display: flex; flex-direction: column; }

.topbar {

    background: var(--white); padding: 0 28px; height: 56px;

    display: flex; align-items: center; justify-content: space-between;

    border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10;

}

.topbar-title { color: var(--navy); font-size: 14px; font-weight: 500; }

.topbar-date { color: var(--muted); font-size: 12px; }

.topbar-right { display: flex; align-items: center; gap: 12px; }

.brgy-chip {

    background: var(--navy-light); color: var(--navy-mid);

    font-size: 11px; font-weight: 500; padding: 4px 10px;

    border-radius: 20px; border: 1px solid var(--border);

}

.avatar-btn {

    width: 32px; height: 32px; background: var(--navy);

    border-radius: 50%; display: flex; align-items: center; justify-content: center;

    color: #fff; font-size: 12px; font-weight: 500;

    cursor: pointer; border: none; font-family: 'DM Sans', sans-serif;

    transition: opacity 0.15s;

}

.avatar-btn:hover { opacity: 0.85; }



/* �??????�?????? CONTENT �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.content { padding: 24px 28px; }

.section { display: none; }

.section.active { display: block; }

.page-header { margin-bottom: 20px; }

.page-header h2 { font-size: 18px; font-weight: 500; color: var(--navy); }

.page-header p { color: var(--muted); font-size: 13px; margin-top: 2px; }



/* �??????�?????? STAT CARDS �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px; }

.stat-card {

    background: var(--white); border-radius: 8px; padding: 18px 20px;

    border: 1px solid var(--border);

}

.stat-card.clickable { cursor: pointer; transition: border-color 0.15s; }

.stat-card.clickable:hover { border-color: var(--navy); }

.stat-num { font-size: 28px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; line-height: 1; }

.stat-label { color: var(--muted); font-size: 12px; margin-top: 6px; }

.stat-hint { color: var(--navy-mid); font-size: 11px; margin-top: 2px; opacity: 0.6; }

.stat-card.danger { border-color: var(--red-l); }

.stat-card.danger .stat-num { color: var(--red); }



/* �??????�?????? QUICK ACTIONS �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 24px; }

.action-btn {

    background: var(--white); border: 1px solid var(--border);

    border-radius: 8px; padding: 14px 16px; text-align: left;

    cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif;

    width: 100%;

}

.action-btn:hover { border-color: var(--navy); background: var(--navy-light); }

.action-btn h3 { font-size: 13px; font-weight: 500; color: var(--navy); }

.action-btn p { font-size: 12px; color: var(--muted); margin-top: 2px; }



/* �??????�?????? CARDS �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.card { background: var(--white); border-radius: 8px; border: 1px solid var(--border); padding: 20px; margin-bottom: 14px; }

.card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }

.card-title { font-size: 13px; font-weight: 500; color: var(--navy); }

.card-sub { font-size: 12px; color: var(--muted); margin-top: 1px; }

.card-empty { text-align: center; padding: 28px; color: var(--muted); font-size: 13px; }



/* �??????�?????? SECTION LABEL �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.sec-label {

    font-size: 10px; font-weight: 600; letter-spacing: 0.1em;

    text-transform: uppercase; color: var(--muted); margin-bottom: 10px;

}



/* �??????�?????? TABLE �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }

.data-table th {

    text-align: left; padding: 8px 12px; color: var(--muted);

    font-size: 11px; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase;

    border-bottom: 1px solid var(--border); background: var(--surface-2);

}

.data-table td { padding: 10px 12px; border-bottom: 1px solid var(--border); color: var(--text); }

.data-table tr:last-child td { border-bottom: none; }

.data-table tr:hover td { background: var(--surface-2); }

.data-table .mono { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--navy); }



/* �??????�?????? TAGS / BADGES �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.tag {

    display: inline-block; padding: 2px 8px; border-radius: 20px;

    font-size: 11px; font-weight: 500;

}

.tag-navy    { background: var(--navy-light); color: var(--navy-mid); }

.tag-active  { background: var(--green-l); color: var(--green); }

.tag-inactive{ background: var(--red-l); color: var(--red); }

.tag-high    { background: var(--red-l); color: var(--red); }

.tag-medium  { background: var(--amber-l); color: var(--amber); }

.tag-low     { background: var(--green-l); color: var(--green); }

.tag-resolved{ background: var(--surface-2); color: var(--muted); }



/* �??????�?????? BUTTONS �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.btn {

    padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 500;

    cursor: pointer; border: 1px solid var(--border); transition: all 0.15s;

    font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 6px;

}

.btn-primary { background: var(--navy); color: #fff; border-color: var(--navy); }

.btn-primary:hover { background: var(--navy-mid); border-color: var(--navy-mid); }

.btn-secondary { background: var(--white); color: var(--text); }

.btn-secondary:hover { background: var(--surface-2); }

.btn-danger { background: var(--red-l); color: var(--red); border-color: transparent; }

.btn-danger:hover { background: #fee2e2; }

.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; font-size: 12px; padding: 6px 12px; }

.btn-ghost:hover { background: var(--surface-2); color: var(--text); }

.btn-sm { padding: 5px 12px; font-size: 12px; }



/* SEARCH / FILTER */

.search-bar {

    display: flex; gap: 10px; align-items: center;

    margin-bottom: 14px; flex-wrap: wrap;

}

.search-input {

    flex: 1; min-width: 200px; padding: 8px 12px;

    border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); background: var(--white);

    font-family: 'DM Sans', sans-serif; outline: none; transition: border-color 0.15s;

}

.search-input:focus { border-color: var(--navy); }

.search-input::placeholder { color: var(--muted); }

.filter-select {

    padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); background: var(--white);

    font-family: 'DM Sans', sans-serif; outline: none; cursor: pointer;

}



/* �??????�?????? MODAL �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.modal-backdrop { pointer-events: none; display: none; position: fixed; inset: 0;

    background: rgba(15,23,42,0.35); z-index: 500;

    align-items: center; justify-content: center;

}

.modal-backdrop.open { pointer-events: auto; display: flex; }

.modal {

    background: var(--white); border-radius: 10px; width: 90%; max-width: 560px;

    max-height: 88vh; display: flex; flex-direction: column;

    border: 1px solid var(--border); overflow: hidden;

    animation: slideUp 0.2s ease;

}

.modal.modal-wide { max-width: 760px; }

@keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-header {

    padding: 18px 22px; border-bottom: 1px solid var(--border);

    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;

}

.modal-header h3 { color: var(--navy); font-size: 14px; font-weight: 500; }

.modal-close {

    background: none; border: none; cursor: pointer; color: var(--muted);

    font-size: 18px; line-height: 1; padding: 2px 6px; border-radius: 4px;

    transition: background 0.15s;

}

.modal-close:hover { background: var(--surface-2); }

.modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }



/* �??????�?????? FORM �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.form-group { display: flex; flex-direction: column; gap: 5px; }

.form-group.full { grid-column: 1 / -1; }

.form-group label { color: var(--text); font-size: 12px; font-weight: 500; }

.form-group input, .form-group select, .form-group textarea {

    padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px;

    font-size: 13px; color: var(--text); outline: none;

    font-family: 'DM Sans', sans-serif; transition: border-color 0.15s; background: var(--white);

}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--navy); }

.form-group .hint { color: var(--muted); font-size: 11px; }

.form-actions {

    display: flex; gap: 8px; justify-content: flex-end;

    margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);

}

.req { color: var(--red); }



/* �??????�?????? ALERT BANNER �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.alert-banner {

    padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;

    font-size: 13px; display: flex; align-items: center; gap: 8px;

    border: 1px solid transparent;

}

.alert-banner.success { background: var(--green-l); color: var(--green); border-color: #bbf7d0; }

.alert-banner.error   { background: var(--red-l); color: var(--red); border-color: #fecaca; }



/* �??????�?????? INFO ROW �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.info-row {

    display: flex; justify-content: space-between; align-items: center;

    padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 13px;

}

.info-row:last-child { border-bottom: none; }

.info-label { color: var(--muted); }

.info-value { color: var(--text); font-weight: 500; }



/* �??????�?????? OFFICIAL CARD �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.official-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--border); }

.official-row:last-child { border-bottom: none; }

.official-avatar {

    width: 80px; height: 80px; border-radius: 50%; background: var(--navy-light);

    color: var(--navy-mid); display: flex; align-items: center; justify-content: center;

    font-size: 60px; font-weight: 500; flex-shrink: 0;

}

.official-name { font-size: 15px; font-weight: 500; color: var(--text); }

.official-pos  { font-size: 13px; color: var(--muted); }



/* �??????�?????? ZONE CARD �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.zones-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }

.zone-card {

    background: var(--surface-2); border: 1px solid var(--border);

    border-radius: 8px; padding: 14px 16px; cursor: pointer;

    transition: border-color 0.15s;

}

.zone-card:hover { border-color: var(--navy); background: var(--navy-light); }

.zone-num { font-size: 20px; font-weight: 300; color: var(--navy); font-family: 'DM Mono', monospace; }

.zone-leader { font-size: 13px; font-weight: 500; color: var(--text); margin-top: 4px; }

.zone-meta { font-size: 11px; color: var(--muted); margin-top: 2px; }



/* �??????�?????? ALERT ITEM �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.alert-row {

    display: flex; align-items: flex-start; gap: 12px;

    padding: 12px 0; border-bottom: 1px solid var(--border);

}

.alert-row:last-child { border-bottom: none; }

.alert-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; background: var(--muted); }

.alert-dot.high   { background: var(--red); }

.alert-dot.medium { background: var(--amber); }

.alert-msg  { font-size: 13px; color: var(--text); flex: 1; }

.alert-time { font-size: 11px; color: var(--muted); margin-top: 2px; }



/* �??????�?????? PROFILE HERO �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.profile-hero {

    background: var(--navy); border-radius: 8px; padding: 24px;

    color: #fff; display: flex; align-items: center; gap: 18px; margin-bottom: 16px;

}

.profile-avatar-lg {

    width: 52px; height: 52px; border-radius: 8px;

    background: rgba(255,255,255,0.15); display: flex; align-items: center;

    justify-content: center; font-size: 20px; font-weight: 300; flex-shrink: 0;

}

.profile-hero-name { font-size: 18px; font-weight: 500; }

.profile-hero-sub { font-size: 13px; opacity: 0.65; margin-top: 2px; }



/* �??????�?????? PROFILE FIELDS GRID �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.field-box { background: var(--surface-2); border-radius: 6px; padding: 12px 14px; border: 1px solid var(--border); }

.field-box.full { grid-column: 1 / -1; }

.field-label { font-size: 11px; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }

.field-val { font-size: 13px; font-weight: 500; color: var(--text); }



/* �??????�?????? REPORT TAB �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.tab-row { display: flex; gap: 4px; margin-bottom: 14px; }

.tab-btn {

    padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;

    cursor: pointer; border: 1px solid var(--border); background: var(--white);

    color: var(--muted); transition: all 0.15s; font-family: 'DM Sans', sans-serif;

}

.tab-btn.active { background: var(--navy); color: #fff; border-color: var(--navy); }

.tab-panel { display: none; }

.tab-panel.active { display: block; }



/* �??????�?????? QR HERO �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.qr-hero {

    background: var(--surface-2); border: 1px solid var(--border); border-radius: 8px;

    padding: 36px; text-align: center; margin-bottom: 14px;

}

.qr-hero h3 { font-size: 15px; font-weight: 500; color: var(--navy); }

.qr-hero p { font-size: 13px; color: var(--muted); margin-top: 4px; }



/* �??????�?????? CHECKBOX �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

.checkbox-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; }

.checkbox-row input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--navy); cursor: pointer; }

.checkbox-row label { font-size: 13px; color: var(--text); cursor: pointer; }



/* �??????�?????? SEARCH SUMMARY �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? */

#search-summary { font-size: 12px; color: var(--muted); margin-bottom: 8px; }

/*
 * --------------------------------------------------------------
 *  NAVBAR THEME PATCH � matches your login page (#0d1b3e navy)
 *  Paste this INSIDE your <style> block, AFTER all existing CSS.
 *  It overrides only the sidebar + topbar. Nothing else changes.
 * --------------------------------------------------------------
 */

/* -- NEW CSS VARS (add these to :root too, or just leave here) -- */
:root {
    --nav-bg:     #0d1b3e;
    --nav-text:   #c8d6f0;
    --nav-muted:  #6e89b8;
    --nav-border: rgba(255, 255, 255, 0.08);
    --nav-hover:  rgba(255, 255, 255, 0.07);
    --nav-accent: #3b82f6;
}

/* -- SIDEBAR -- */
.sidebar {
    background:   var(--nav-bg) !important;
    border-right: 1px solid var(--nav-border) !important;
}

.sidebar-logo {
    border-bottom: 1px solid var(--nav-border) !important;
}
.sidebar-logo h1 {
    color: #ffffff !important;
}
.sidebar-logo p {
    color: var(--nav-muted) !important;
}

/* Logo circle fallback letter */
.sidebar-logo > div > div[style*="background:var(--navy)"] {
    background: rgba(255, 255, 255, 0.12) !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}

/* -- MENU SECTIONS -- */
.menu-section {
    color:   var(--nav-muted) !important;
    opacity: 0.7;
}

/* -- MENU ITEMS -- */
.menu-item {
    color: var(--nav-text) !important;
}
.menu-item:hover {
    color:      #ffffff !important;
    background: var(--nav-hover) !important;
}
.menu-item.active {
    color:      #ffffff !important;
    background: rgba(59, 130, 246, 0.18) !important;
}

/* -- MENU DOTS -- */
.menu-dot {
    background: var(--nav-muted) !important;
}
.menu-item:hover .menu-dot {
    background: #ffffff !important;
}
.menu-item.active .menu-dot {
    background: var(--nav-accent) !important;
}

/* -- MENU BADGES -- */
.menu-badge {
    background: rgba(255, 255, 255, 0.1) !important;
    color:      var(--nav-text) !important;
}
.menu-badge.alert {
    background: rgba(220, 38, 38, 0.25) !important;
    color:      #fca5a5 !important;
}

/* -- SIDEBAR FOOTER -- */
.sidebar-footer {
    border-top: 1px solid var(--nav-border) !important;
}
.sidebar-footer a {
    color: var(--nav-muted) !important;
}
.sidebar-footer a:hover {
    color: #ffffff !important;
}

/* -- TOPBAR -- */
.topbar {
    background:    var(--nav-bg) !important;
    border-bottom: 1px solid var(--nav-border) !important;
}
.topbar-title {
    color: #ffffff !important;
}
.topbar-date {
    color: var(--nav-muted) !important;
}

/* live-indicator */
#live-indicator {
    color: var(--nav-muted) !important;
}

/* -- ROLE CHIP -- */
.role-chip {
    background:   rgba(255, 255, 255, 0.10) !important;
    color:        #c8d6f0 !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
}

/* -- AVATAR BUTTON -- */
.avatar-btn {
    background: rgba(255, 255, 255, 0.15) !important;
    border:     1px solid rgba(255, 255, 255, 0.20) !important;
}
.avatar-btn:hover {
    background: rgba(255, 255, 255, 0.22) !important;
}

/* -- USERNAME TEXT next to avatar -- */
.topbar-right > div > div:first-child {
    color: #ffffff !important;
}

.sidebar { background: #ffffff !important; border-right: 1px solid #e2e8f0 !important; }
.menu-section { color: #94a3b8 !important; opacity: 1 !important; }
.menu-item { color: #64748b !important; }
.menu-item:hover { color: #1e293b !important; background: #f1f4f8 !important; }
.menu-item.active { color: #0d1b3e !important; background: #eef2f7 !important; }
.menu-dot { background: #cbd5e1 !important; }
.menu-item:hover .menu-dot { background: #1e293b !important; }
.menu-item.active .menu-dot { background: #0d1b3e !important; }
.menu-badge { background: #f1f4f8 !important; color: #64748b !important; }
.menu-badge.alert { background: #fef2f2 !important; color: #dc2626 !important; }
.sidebar-logo { border-bottom: 1px solid #e2e8f0 !important; }
.sidebar-logo h1 { color: #0f172a !important; }
.sidebar-logo p { color: #64748b !important; }
.sidebar-footer { border-top: 1px solid #e2e8f0 !important; }
.sidebar-footer a { color: #64748b !important; }
.sidebar-footer a:hover { color: #1e293b !important; }
.menu-item.active { border-left: 3px solid #0d1b3e !important; background: #f0f4f8 !important; color: #0d1b3e !important; padding-left: 17px !important; }
.menu-item.active .menu-dot { background: #3b82f6 !important; }
</style>

<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"></head>

<body>



<!-- �??????�?????? SIDEBAR �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? -->

<div class="sidebar">

<div class="sidebar-logo">

    <div style="display:flex;align-items:center;justify-content:space-between">

        <div>

            <h1>SPAC</h1>

            <p>Barangay Portal</p>

        </div>

        <?php if (!empty($brgy_info['logo'])): ?>

            <img src="<?= htmlspecialchars($brgy_info['logo']) ?>" alt="Barangay Logo"

                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0">

        <?php else: ?>

            <div style="width:80px;height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)">

                <?= strtoupper(substr($brgy_info['name'] ?? 'B', 0, 1)) ?>

            </div>

        <?php endif; ?>

    </div>

</div>

    <div class="sidebar-menu">

        <div class="menu-section">Overview</div>

        <button class="menu-item active" onclick="showSection('dashboard', this)"><span class="menu-dot"></span> Dashboard</button>

        <button class="menu-item" onclick="showSection('profile', this)"><span class="menu-dot"></span> Barangay Profile</button>



        <div class="menu-section">People</div>

        <button class="menu-item" onclick="showSection('officials', this)">

            <span class="menu-dot"></span> Officials &amp; Staff

            <span class="menu-badge"><?= $total_officials ?></span>

        </button>

        <button class="menu-item" onclick="showSection('zones', this)">

            <span class="menu-dot"></span> <?= htmlspecialchars($area_label) ?> Leaders

            <span class="menu-badge"><?= $total_zones ?></span>

        </button>

        <button class="menu-item" onclick="showSection('households', this)">

            <span class="menu-dot"></span> Households

            <span class="menu-badge"><?= number_format($total_families) ?></span>

        </button>

        <button class="menu-item" onclick="showSection('residents', this)">

            <span class="menu-dot"></span> Residents

            <span class="menu-badge"><?= number_format($total_residents) ?></span>

        </button>



        <div class="menu-section">Reports</div>

        <button class="menu-item" onclick="window.location.href='statistics.php'"><span class="menu-dot"></span> Statistics</button>



        <div class="menu-section">Services</div>

        <button class="menu-item" onclick="window.location.href='import_residents.php'"><span class="menu-dot"></span> Import Residents</button>

        <button class="menu-item" onclick="showSection('ayuda', this)"><span class="menu-dot"></span> Ayuda / Assistance</button>

        <button class="menu-item" onclick="showSection('qr', this)"><span class="menu-dot"></span> Scan QR / History</button>



        <div class="menu-section">Management</div>

        <button class="menu-item" onclick="showSection('alerts', this)">

            <span class="menu-dot"></span> Alerts

            <?php if ($active_alerts > 0): ?>

            <span class="menu-badge alert"><?= $active_alerts ?></span>

            <?php endif; ?>

        </button>

    </div>

    <div class="sidebar-footer">

        <a href="<?= str_repeat('../', 2) ?>logout.php"><span class="menu-dot"></span> Logout</a>

    </div>

</div>



<!-- �??????�?????? MAIN �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�?????? -->

<div class="main">

    <div class="topbar">

        <div>

            <div class="topbar-title" id="topbar-title">Dashboard</div>

            <div class="topbar-date" id="topbar-date"></div>

        </div>

        <div class="topbar-right">

            <span class="brgy-chip"><?= htmlspecialchars($brgy_info['name'] ?? 'Barangay') ?></span>

            <button class="avatar-btn" onclick="openModal('modal-profile')" title="My Profile">

                <?= strtoupper(substr($_SESSION['full_name'] ?? 'B', 0, 1)) ?>

            </button>

        </div>

    </div>



    <div class="content">



        <?php if ($action_message): ?>

        <div class="alert-banner <?= $action_type ?>">

            <?= $action_type === 'success' ? '�?????' : '�?????' ?> <?= htmlspecialchars($action_message) ?>

        </div>

        <?php endif; ?>



        <!-- �???��???� DASHBOARD �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <?php $active_section = isset($_GET['msg']) && 'dashboard'; ?>

        <div id="section-dashboard" class="section <?= $active_section === 'dashboard' ? 'active' : '' ?>">

            <div class="page-header">

                <h2><?= htmlspecialchars($brgy_info['name'] ?? 'Dashboard') ?></h2>

                <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></p>

            </div>



            <div class="stats-grid">

                <div class="stat-card clickable" onclick="showSection('residents', document.querySelector('[onclick*=residents]'))">

                    <div class="stat-num"><?= number_format($total_residents) ?></div>

                    <div class="stat-label">Residents</div>

                </div>

                <div class="stat-card clickable" onclick="showSection('households', document.querySelector('[onclick*=households]'))">

                    <div class="stat-num"><?= number_format($total_families) ?></div>

                    <div class="stat-label">Households</div>

                </div>

                <div class="stat-card clickable" onclick="showSection('officials', document.querySelector('[onclick*=officials]'))">

                    <div class="stat-num"><?= $total_officials ?></div>

                    <div class="stat-label">Officials</div>

                </div>

                <div class="stat-card clickable" onclick="showSection('zones', document.querySelector('[onclick*=zones]'))">

                    <div class="stat-num"><?= $total_zones ?></div>

                    <div class="stat-label"><?= htmlspecialchars($area_label_plural) ?></div>

                </div>

                <div class="stat-card clickable <?= $active_alerts > 0 ? 'danger' : '' ?>" onclick="showSection('alerts', document.querySelector('[onclick*=alerts]'))">

                    <div class="stat-num"><?= $active_alerts ?></div>

                    <div class="stat-label">Active Alerts</div>

                </div>

            </div>



            <div class="sec-label">Quick Actions</div>

            <div class="actions-grid">

                

                <button class="action-btn" onclick="openModal('modal-families-drill')">

                    <h3>Manage Families</h3><p><?= number_format($total_families) ?> households registered</p>

                </button>

                <button class="action-btn" onclick="openModal('modal-add-assistance')">

                    <h3>Add Assistance</h3><p>Log ayuda distribution</p>

                </button>

                <button class="action-btn" onclick="openModal('modal-add-alert')">

                    <h3>Post Alert</h3><p>Send a barangay-wide alert</p>

                </button>

                <button class="action-btn" onclick="showSection('qr', document.querySelector('[onclick*=qr]'))">

                    <h3>Scan QR</h3><p>Open QR scanner for residents</p>

                </button>

            </div>



            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

                <div class="card">

                    <div class="card-header">

                        <div><div class="card-title">Recent QR Scans</div></div>

                        <button class="btn btn-ghost" onclick="showSection('qr', document.querySelector('[onclick*=qr]'))">View all</button>

                    </div>

                    <?php if (empty($qr_history)): ?>

                    <div class="card-empty">No scans yet.</div>

                    <?php else: ?>

                    <table class="data-table">

                        <thead><tr><th>Resident</th><th>Time</th></tr></thead>

                        <tbody>

                        <?php foreach (array_slice($qr_history, 0, 5) as $scan): ?>

                        <tr>

                            <td><?= htmlspecialchars($scan['full_name'] ?? 'Unknown') ?></td>

                            <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($scan['scan_time'] ?? '') ?></td>

                        </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                    <?php endif; ?>

                </div>



                <div class="card">

                    <div class="card-header">

                        <div><div class="card-title">Active Alerts</div></div>

                        <button class="btn btn-sm btn-primary" onclick="openModal('modal-add-alert')">+ New</button>

                    </div>

                    <?php $unresolved = array_filter($alerts, fn($a) => !$a['resolved']); ?>

                    <?php if (empty($unresolved)): ?>

                    <div class="card-empty">All clear.</div>

                    <?php else: ?>

                    <?php foreach (array_slice($unresolved, 0, 4) as $al): ?>

                    <div class="alert-row">

                        <div class="alert-dot <?= $al['severity'] ?>"></div>

                        <div>

                            <div class="alert-msg"><?= htmlspecialchars($al['message']) ?></div>

                            <div class="alert-time"><?= htmlspecialchars($al['created_at']) ?></div>

                        </div>

                    </div>

                    <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </div>



        <!-- �???��???� PROFILE �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-profile" class="section">

            <div class="page-header">

                <h2>Barangay Profile</h2>

                <p><?= htmlspecialchars($brgy_info['name'] ?? '') ?></p>

            </div>

            <div class="profile-hero">

    <div style="position:relative;flex-shrink:0">

        <?php if (!empty($brgy_info['logo'])): ?>

            <img src="<?= htmlspecialchars($brgy_info['logo']) ?>" alt="Logo"

                 style="width:52px;height:52px;border-radius:8px;object-fit:cover;border:2px solid rgba(255,255,255,0.2)">

        <?php else: ?>

            <div class="profile-avatar-lg"><?= strtoupper(substr($brgy_info['name'] ?? 'B', 0, 1)) ?></div>

        <?php endif; ?>

        <label title="Change logo" style="position:absolute;bottom:-6px;right:-6px;width:22px;height:22px;border-radius:50%;background:#fff;color:var(--navy);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;border:1px solid var(--border);box-shadow:0 1px 4px rgba(0,0,0,0.15)">

            �??�

            <input type="file" accept="image/*" style="display:none" onchange="handleLogoUpload(this)">

        </label>

    </div>

                <div style="flex:1">

                    <div class="profile-hero-name"><?= htmlspecialchars($brgy_info['name'] ?? '') ?></div>

                    <div class="profile-hero-sub">District <?= htmlspecialchars($brgy_info['district'] ?? 'N/A') ?> ? Kap. <?= htmlspecialchars($brgy_info['captain_name'] ?? 'N/A') ?></div>

                </div>

                <button class="btn btn-secondary btn-sm" onclick="openModal('modal-edit-profile')" style="color:#fff;background:rgba(255,255,255,0.12);border-color:rgba(255,255,255,0.2)">Edit</button>

            </div>

            <div class="field-grid">

                <div class="field-box"><div class="field-label">Email</div><div class="field-val"><?= htmlspecialchars($brgy_info['email'] ?? '?') ?></div></div>

                <div class="field-box"><div class="field-label">Contact</div><div class="field-val"><?= htmlspecialchars($brgy_info['contact_number'] ?? '?') ?></div></div>

                <div class="field-box full"><div class="field-label">Address</div><div class="field-val"><?= htmlspecialchars($brgy_info['address'] ?? '?') ?></div></div>

                <div class="field-box"><div class="field-label">Population</div><div class="field-val"><?= number_format($brgy_info['population'] ?? 0) ?></div></div>

                <div class="field-box"><div class="field-label">District</div><div class="field-val"><?= htmlspecialchars($brgy_info['district'] ?? '?') ?></div></div>

                <div class="field-box"><div class="field-label">Founded</div><div class="field-val"><?= htmlspecialchars($brgy_info['founded_year'] ?? '?') ?></div></div>

                <div class="field-box"><div class="field-label">Area Label</div><div class="field-val"><?= htmlspecialchars($area_label) ?></div></div>

            </div>

        </div>



        <!-- �???��???� OFFICIALS �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-officials" class="section <?= $active_section === 'officials' ? 'active' : '' ?>">

                        <!-- ORG CHART -->

            <div class="card" style="margin-bottom:14px">

                <div class="card-header">

                    <div><div class="card-title">Barangay Council Structure</div></div>

                </div>

                <div style="overflow-x:auto;padding:16px 0">

                    <div style="display:flex;flex-direction:column;align-items:center;gap:0;min-width:400px">

                        <?php

                        $org_ranks = [

                            'Barangay Captain'   => [],

                            'Barangay Captain'   => [],

                            'Barangay Secretary/Treasurer' => [],

                            'Kagawad'            => [],

                            'SK Chairman'        => [],

                            'Barangay Health Worker' => [],

                            'Tanod'              => [],

                            'Staff'              => [],

                            'Other'              => [],

                        ];

                        foreach ($officials as $of) {

                            $pos = $of['position'];

                            if ($pos === 'Barangay Secretary' || $pos === 'Barangay Treasurer') $org_ranks['Barangay Secretary/Treasurer'][] = $of;

                            elseif (isset($org_ranks[$pos])) $org_ranks[$pos][] = $of;

                            else $org_ranks['Other'][] = $of;

                        }

                        $org_rows = array_filter($org_ranks, fn($r) => !empty($r));

                        $row_keys = array_keys($org_rows);

                        ?>

                        <?php foreach ($org_rows as $rank => $members): ?>

                        <?php $is_captain = ($rank === 'Barangay Captain'); ?>

                        <?php if (!$is_captain): ?>

                        <div style="width:2px;height:24px;background:var(--border)"></div>

                        <?php if (count($members) > 1): ?>

                        <div style="height:2px;background:var(--border);width:<?= (count($members) - 1) * 180 ?>px;pointer-events:none"></div>

                        <?php endif; ?>

                        <?php endif; ?>

                        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">

                        <?php foreach ($members as $of): ?>

                        <div style="display:flex;flex-direction:column;align-items:center">

                            <?php if (!$is_captain && count($members) > 1): ?>

                            <div style="width:2px;height:24px;background:var(--border)"></div>

                            <?php endif; ?>

                            <div style="<?= $is_captain ? 'background:var(--navy);color:#fff;' : 'background:var(--surface-2);border:1px solid var(--border);' ?>border-radius:8px;padding:<?= $is_captain ? '12px 20px' : '10px 16px' ?>;text-align:center;min-width:<?= $is_captain ? '160' : '140' ?>px">

                                <?php if (!empty($of['photo'])): ?>

                                    <img src="<?= htmlspecialchars($of['photo']) ?>" style="width:<?= $is_captain ? '48' : '40' ?>px;height:<?= $is_captain ? '48' : '40' ?>px;border-radius:50%;object-fit:cover;border:2px solid <?= $is_captain ? 'rgba(255,255,255,0.3)' : 'var(--border)' ?>;margin-bottom:6px">

                                <?php else: ?>

                                    <div style="width:<?= $is_captain ? '48' : '40' ?>px;height:<?= $is_captain ? '48' : '40' ?>px;border-radius:50%;background:<?= $is_captain ? 'rgba(255,255,255,0.15)' : 'var(--navy-light)' ?>;color:<?= $is_captain ? '#fff' : 'var(--navy-mid)' ?>;display:flex;align-items:center;justify-content:center;font-size:<?= $is_captain ? '18' : '15' ?>px;font-weight:500;margin:0 auto 6px"><?= strtoupper(substr($of['full_name'],0,1)) ?></div>

                                <?php endif; ?>

                                <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($of['full_name']) ?></div>

                                <div style="font-size:10px;opacity:0.7;margin-top:2px"><?= htmlspecialchars($of['position']) ?></div>

                            </div>

                        </div>

                        <?php endforeach; ?>

                        </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

            <div class="page-header"><h2>Officials &amp; Staff</h2><p>Active council members and staff</p></div>

            <div class="card">

                <div class="card-header">

                    <div><div class="card-title">All Officials (<?= count($officials) ?>)</div></div>

                    <button class="btn btn-sm btn-primary" onclick="openModal('modal-add-official')">+ Add</button>

                </div>

                <?php if (empty($officials)): ?>

                <div class="card-empty">No officials added yet.</div>

                <?php else: ?>

                <?php foreach ($officials as $of): ?>

                <div class="official-row">

                   <div class="official-avatar" style="overflow:hidden;padding:0">

    <?php if (!empty($of['photo'])): ?>

        <img src="<?= htmlspecialchars($of['photo']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">

    <?php else: ?>

        <span style="font-size:13px;font-weight:500"><?= strtoupper(substr($of['full_name'], 0, 1)) ?></span>

    <?php endif; ?>

</div>

                    <div style="flex:1">

                        <div class="official-name"><?= htmlspecialchars($of['full_name']) ?></div>

                        <div class="official-pos"><?= htmlspecialchars($of['position']) ?><?= $of['contact_number'] ? ' ? ' . htmlspecialchars($of['contact_number']) : '' ?></div>

                    </div>

                <div style="display:flex;gap:6px;align-items:center;pointer-events:auto;position:relative;z-index:999">

                        <button onclick="openEditOfficial(<?= $of['id'] ?>, '<?= htmlspecialchars($of['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($of['position'], ENT_QUOTES) ?>', '<?= htmlspecialchars($of['contact_number'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($of['email'] ?? '', ENT_QUOTES) ?>', <?= !empty($of['photo']) ? 'true' : 'false' ?>)" class="btn btn-ghost btn-sm" type="button">Edit</button>

                        <form method="POST" onsubmit="return confirm('Remove this official?')" style="margin:0">

                            <input type="hidden" name="official_id" value="<?= $of['id'] ?>">

                            <button name="delete_official" class="btn btn-danger btn-sm" type="submit">Remove</button>

                        </form>

                    </div>

                </div>

                <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>



        <!-- �???��???� ZONES �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-zones" class="section">

            <div class="page-header">

                <h2><?= htmlspecialchars($area_label) ?> Leaders</h2>

                <p><?= count($zone_leaders) ?> areas registered</p>

            </div>

            <div class="card">

                <div class="card-header">

                    <div><div class="card-title"><?= htmlspecialchars($area_label_plural) ?> Overview</div></div>

                    <button class="btn btn-sm btn-primary" onclick="openModal('modal-add-zone')">+ Add Leader</button>

                </div>

                <?php if (empty($zone_leaders)): ?>

                <div class="card-empty">No zone leaders added yet.</div>

                <?php else: ?>

                <div class="zones-grid">

                    <?php foreach ($zone_leaders as $zl): ?>

                    <?php

                        $isNumbered = in_array($area_label, ['Zone','Purok','Block']);

                        $areaDisplay = $isNumbered ? $area_label . ' ' . $zl['zone_number'] : ($zl['zone_name'] ?: $area_label . ' ' . $zl['zone_number']);

                    ?>

                    <div class="zone-card" style="position:relative">

                        <div onclick="openZoneDetail(<?= $zl['zone_number'] ?>, '<?= htmlspecialchars($zl['zone_name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($zl['leader_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($zl['contact_number'] ?? '', ENT_QUOTES) ?>')">

                            <div class="zone-num"><?= htmlspecialchars($areaDisplay) ?></div>

                            <div class="zone-leader"><?= htmlspecialchars($zl['leader_name']) ?></div>

                            <div class="zone-meta"><?= $zones_families[$zl['zone_number']] ?? 0 ?> families ? <?= $zones_residents[$zl['zone_number']] ?? 0 ?> residents</div>

                        </div>

                        <div style="display:flex;gap:6px;margin-top:10px">

                            <button onclick="openEditZone(<?= $zl['id'] ?>, '<?= htmlspecialchars($zl['zone_name'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($zl['leader_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($zl['contact_number'] ?? '', ENT_QUOTES) ?>')" class="btn btn-ghost btn-sm" style="flex:1">Edit</button>

                            <form method="POST" onsubmit="return confirm('Remove?')" style="flex:1">

                                <input type="hidden" name="zone_leader_id" value="<?= $zl['id'] ?>">

                                <button name="delete_zone_leader" class="btn btn-danger btn-sm" style="width:100%">Delete</button>

                            </form>

                        </div>

                    </div>

                    <?php endforeach; ?>

                </div>

                <?php endif; ?>

            </div>

        </div>



        <!-- �???��???� HOUSEHOLDS �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-households" class="section">

            <div class="page-header"><h2>Households</h2><p>Family records per <?= htmlspecialchars($area_label) ?></p></div>



            <div class="search-bar">

                <input type="text" class="search-input" id="global-family-search" placeholder="Search by name or address..." oninput="globalFamilySearch(this.value)">

                <select class="filter-select" id="zone-filter" onchange="globalFamilySearch(document.getElementById('global-family-search').value)">

                    <option value="">All <?= htmlspecialchars($area_label_plural) ?></option>

                    <?php foreach ($zone_leaders as $zl):

                        $isNumbered = in_array($area_label, ['Zone','Purok','Block']);

                        $optLabel = $isNumbered ? $area_label . ' ' . $zl['zone_number'] : ($zl['zone_name'] ?: $area_label . ' ' . $zl['zone_number']);

                    ?>

                    <option value="<?= $zl['zone_number'] ?>"><?= htmlspecialchars($optLabel) ?></option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div id="search-summary"></div>



            <?php if (!empty($zones_families)): ?>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:14px">

                <?php foreach ($zones_families as $zn => $cnt): ?>

                <div class="stat-card">

                    <div class="stat-num"><?= $cnt ?></div>

                    <div class="stat-label"><?= htmlspecialchars($area_label) ?> <?= $zn ?> Families</div>

                </div>

                <?php endforeach; ?>

            </div>

            <?php endif; ?>



            <div class="card">

                <div class="card-header">

                    <div>

                        <div class="card-title">All Households (<?= count($families) ?>)</div>

                        <div class="card-sub"><?= count($families) ?> households registered</div>

                    </div>

                    <button class="btn btn-sm btn-primary" onclick="openModal('modal-families-drill')">Manage</button>

                </div>

                <?php if (empty($families)): ?>

                <div class="card-empty">No households found.</div>

                <?php else: ?>

                <table class="data-table" id="tbl-families">

                    <thead><tr><th>Head of Family</th><th><?= htmlspecialchars($area_label) ?></th><th>Address</th><th>Members</th></tr></thead>

                    <tbody>

                    <?php foreach ($families as $fam): ?>

                    <tr data-zone="<?= $fam['zone_number'] ?>"

                        data-name="<?= strtolower(htmlspecialchars($fam['head_name'] ?? '')) ?>"

                        data-address="<?= strtolower(htmlspecialchars($fam['address'] ?? '')) ?>">

                        <td style="font-weight:500"><?= htmlspecialchars($fam['family_name'] ?? $fam['head_name'] ?? '?') ?></td>

                        <td><?= $fam['zone_number'] ? htmlspecialchars($area_label) . ' ' . $fam['zone_number'] : '?' ?></td>

                        <td style="color:var(--muted)"><?= htmlspecialchars($fam['address'] ?? '?') ?></td>

                        <td class="mono"><?= $fam['member_count'] ?? '?' ?></td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <div id="no-results-msg" style="display:none;text-align:center;padding:24px;color:var(--muted);font-size:13px">No families match your search.</div>

                <?php endif; ?>

            </div>

        </div>



        <!-- �???��???� RESIDENTS �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-residents" class="section">

            <div class="page-header"><h2>Residents</h2><p>All registered residents</p></div>

            <?php if (!empty($zones_residents)): ?>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:14px">

                <?php foreach ($zones_residents as $zn => $cnt): ?>

                <div class="stat-card">

                    <div class="stat-num"><?= $cnt ?></div>

                    <div class="stat-label"><?= htmlspecialchars($area_label) ?> <?= $zn ?></div>

                </div>

                <?php endforeach; ?>

            </div>

            <?php endif; ?>

            <div class="card">

                <div class="card-header">

                    <div><div class="card-title">All Residents (<?= count($residents) ?>)</div></div>

                    <input type="text" class="search-input" placeholder="Search resident..." oninput="filterTable(this,'tbl-residents')" style="width:200px">

                </div>

                <?php if (empty($residents)): ?>

                <div class="card-empty">No residents found.</div>

                <?php else: ?>

                <table class="data-table" id="tbl-residents">

                    <thead><tr><th>Name</th><th><?= htmlspecialchars($area_label) ?></th><th>Birthdate</th><th>Contact</th></tr></thead>

                    <tbody>

                    <?php foreach ($residents as $res): ?>

                    <tr>

                        <td style="font-weight:500"><?= htmlspecialchars($res['full_name'] ?? '?') ?></td>

                        <td><?= $res['zone_number'] ? htmlspecialchars($area_label) . ' ' . $res['zone_number'] : '?' ?></td>

                        <td style="color:var(--muted)"><?= htmlspecialchars($res['birth_date'] ?? '?') ?></td>

                        <td style="color:var(--muted)"><?= htmlspecialchars($res['contact_number'] ?? '?') ?></td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <?php endif; ?>

            </div>

        </div>



        

        <!-- �???��???� AYUDA �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

<div id="section-ayuda" class="section">

    <div class="page-header"><h2>Ayuda / Assistance</h2><p>Assistance programs and distribution records</p></div>



    <!-- Barangay Programs -->

    <div class="card" style="margin-bottom:14px">

        <div class="card-header">

            <div><div class="card-title">Barangay Programs</div><div class="card-sub">Added by your barangay</div></div>

            <button class="btn btn-sm btn-primary" onclick="openModal('modal-add-assistance')">+ Add</button>

        </div>

        <?php if (empty($assistance_programs)): ?>

        <div class="card-empty">No barangay programs added yet.</div>

        <?php else: ?>

        <table class="data-table">

            <thead><tr><th style="width:50%">Name</th><th style="width:25%;text-align:left">Type</th><th style="width:25%">Start Date</th></tr></thead>

            <tbody>

            <?php foreach ($assistance_programs as $ap): ?>

            <tr>

                <td style="font-weight:500"><?= htmlspecialchars($ap['name']) ?></td>

                <td><span class="tag tag-navy"><?= htmlspecialchars($ap['type'] ?? '?') ?></span></td>

                <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($ap['start_date'] ?? '?') ?></td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php endif; ?>

    </div>



    <!-- City Hall Programs -->

    <div class="card">

        <div class="card-header">

            <div><div class="card-title">City Hall Programs</div><div class="card-sub">Assistance from City Hall</div></div>

        </div>

        <?php if (empty($cityhall_programs)): ?>

        <div class="card-empty">No City Hall programs at this time.</div>

        <?php else: ?>

        <table class="data-table">

            <thead><tr><th style="width:50%">Name</th><th style="width:25%;text-align:left">Type</th><th style="width:25%">Start Date</th></tr></thead>

            <tbody>

            <?php foreach ($cityhall_programs as $cp): ?>

            <tr>

                <td style="font-weight:500"><?= htmlspecialchars($cp['name']) ?></td>

                <td><span class="tag tag-navy"><?= htmlspecialchars($cp['type'] ?? '?') ?></span></td>

                <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($cp['start_date'] ?? '?') ?></td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php endif; ?>

    </div>

</div>



        <!-- �???��???� QR �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-qr" class="section">

            <div class="page-header"><h2>Scan QR / History</h2><p>QR scanning and attendance records</p></div>

            <div class="qr-hero">

                <h3>Scan a Resident QR Code</h3>

                <p>Use a QR scanner or camera to scan a resident's QR code.</p>

                <button class="btn btn-primary" style="margin-top:14px" onclick="openModal('modal-qr-scan')">Open Scanner</button>

            </div>

            <div class="card">

                <div class="card-header">

                    <div><div class="card-title">Scan History (<?= count($qr_history) ?>)</div></div>

                    <input type="text" class="search-input" placeholder="Search..." oninput="filterTable(this,'tbl-qr')" style="width:180px">

                </div>

                <?php if (empty($qr_history)): ?>

                <div class="card-empty">No scan history yet.</div>

                <?php else: ?>

                <table class="data-table" id="tbl-qr">

                    <thead><tr><th>Resident</th><th>Scan Time</th><th>Purpose</th></tr></thead>

                    <tbody>

                    <?php foreach ($qr_history as $scan): ?>

                    <tr>

                        <td><?= htmlspecialchars($scan['full_name'] ?? 'Unknown') ?></td>

                        <td style="color:var(--muted)"><?= htmlspecialchars($scan['scan_time'] ?? '') ?></td>

                        <td style="color:var(--muted)"><?= htmlspecialchars($scan['purpose'] ?? 'General') ?></td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <?php endif; ?>

            </div>

        </div>



        <!-- �???��???� ALERTS �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->

        <div id="section-alerts" class="section">

            <div class="page-header"><h2>Alerts</h2><p><?= htmlspecialchars($brgy_info['name'] ?? '') ?></p></div>

            <div class="card">

                <div class="card-header">

                    <div><div class="card-title">All Alerts (<?= count($alerts) ?>)</div></div>

                    <button class="btn btn-sm btn-primary" onclick="openModal('modal-add-alert')">+ Post Alert</button>

                </div>

                <?php if (empty($alerts)): ?>

                <div class="card-empty">No alerts found.</div>

                <?php else: ?>

                <?php foreach ($alerts as $al): ?>

                <div class="alert-row" style="<?= $al['resolved'] ? 'opacity:0.5' : '' ?>">

                    <div class="alert-dot <?= $al['resolved'] ? '' : $al['severity'] ?>"></div>

                    <div style="flex:1">

                        <div class="alert-msg"><?= htmlspecialchars($al['message']) ?></div>

                        <div class="alert-time">

                            <span class="tag tag-<?= $al['resolved'] ? 'resolved' : $al['severity'] ?>"><?= ucfirst($al['resolved'] ? 'Resolved' : $al['severity']) ?></span>

                            ? <?= htmlspecialchars($al['created_at']) ?>

                        </div>

                    </div>

                    <?php if (!$al['resolved']): ?>

                    <form method="POST">

                        <input type="hidden" name="alert_id" value="<?= $al['alert_id'] ?>">

                        <button name="resolve_alert" class="btn btn-ghost btn-sm">Resolve</button>

                    </form>

                    <?php endif; ?>

                </div>

                <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>



    </div>

</div>





<!-- �???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???�

     MODALS

�???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???��???� -->



<!-- Add Assistance -->

<div class="modal-backdrop" id="modal-add-assistance">

    <div class="modal">

        <div class="modal-header"><h3>Add Assistance</h3><button class="modal-close" onclick="closeModal('modal-add-assistance')">?????</button></div>

       <form method="POST" enctype="multipart/form-data"><div class="modal-body">

            <div class="form-grid">

                <div class="form-group full"><label>Name <span class="req">*</span></label><input type="text" name="assistance_name" required placeholder="e.g. Rice Pack Distribution"></div>

                <div class="form-group"><label>Type</label>

                    <select name="assistance_type">

                        <option value="">? Select ?</option>

                        <option>Food Assistance</option><option>Medical / Health</option>

                        <option>Financial Aid</option><option>Livelihood</option>

                        <option>Educational</option><option>Disaster Relief</option><option>Other</option>

                    </select>

                </div>

                <div class="form-group"><label>Source</label><input type="text" name="assistance_source" placeholder="e.g. DSWD, LGU"></div>

                <div class="form-group full"><label>Start Date <span class="req">*</span></label><input type="date" name="assistance_start_date" required></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-assistance')">Cancel</button>

                <button type="submit" name="add_assistance_record" class="btn btn-primary">Save</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Edit Profile -->

<div class="modal-backdrop" id="modal-edit-profile">

    <div class="modal">

        <div class="modal-header"><h3>Edit Barangay Profile</h3><button class="modal-close" onclick="closeModal('modal-edit-profile')">?????</button></div>

         <form method="POST" enctype="multipart/form-data"><div class="modal-body">

            <div class="form-grid">

                <div class="form-group"><label>Captain Name</label><input type="text" name="captain_name" value="<?= htmlspecialchars($brgy_info['captain_name'] ?? '') ?>"></div>

                <div class="form-group"><label>District</label><input type="text" name="brgy_district" value="<?= htmlspecialchars($brgy_info['district'] ?? '') ?>"></div>

                <div class="form-group"><label>Email</label><input type="email" name="brgy_email" value="<?= htmlspecialchars($brgy_info['email'] ?? '') ?>"></div>

                <div class="form-group"><label>Contact</label><input type="text" name="brgy_contact" value="<?= htmlspecialchars($brgy_info['contact_number'] ?? '') ?>"></div>

                <div class="form-group"><label>Population</label><input type="number" name="brgy_population" value="<?= $brgy_info['population'] ?? 0 ?>"></div>

                <div class="form-group"><label>Year Founded</label><input type="number" name="brgy_founded_year" value="<?= htmlspecialchars($brgy_info['founded_year'] ?? '') ?>" placeholder="e.g. 1950" min="1800" max="2030"></div>

                <div class="form-group full"><label>Address</label><input type="text" name="brgy_address" value="<?= htmlspecialchars($brgy_info['address'] ?? '') ?>" placeholder="e.g. Barangay Laram, San Pedro, Laguna"></div>

                <div class="form-group"><label>Area Label</label>

                    <select name="area_label">

                        <option value="Zone"        <?= ($brgy_info['area_label'] ?? 'Zone') === 'Zone'        ? 'selected' : '' ?>>Zone</option>

                        <option value="Purok"       <?= ($brgy_info['area_label'] ?? '') === 'Purok'       ? 'selected' : '' ?>>Purok</option>

                        <option value="Sitio"       <?= ($brgy_info['area_label'] ?? '') === 'Sitio'       ? 'selected' : '' ?>>Sitio</option>

                        <option value="Street"      <?= ($brgy_info['area_label'] ?? '') === 'Street'      ? 'selected' : '' ?>>Street</option>

                        <option value="Subdivision" <?= ($brgy_info['area_label'] ?? '') === 'Subdivision' ? 'selected' : '' ?>>Subdivision</option>

                        <option value="Block"       <?= ($brgy_info['area_label'] ?? '') === 'Block'       ? 'selected' : '' ?>>Block</option>

                    </select>

                    <span class="hint">How areas are labeled across your portal</span>

                </div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-profile')">Cancel</button>

                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Add Official -->

<div class="modal-backdrop" id="modal-add-official">

    <div class="modal">

        <div class="modal-header">

            <h3>Add Official</h3>

            <button class="modal-close" onclick="closeModal('modal-add-official')">?????</button>

        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="modal-body">

                <div class="form-grid">

                    <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="official_name" required></div>

                    <div class="form-group"><label>Position <span class="req">*</span></label>

                        <select name="official_position" required>

                            <option value="">? Select ?</option>

                            <option>Barangay Captain</option><option>Kagawad</option>

                            <option>SK Chairman</option><option>Barangay Secretary</option>

                            <option>Barangay Treasurer</option><option>Barangay Health Worker</option>

                            <option>Tanod</option><option>Staff</option><option>Other</option>

                        </select>

                    </div>

                    <div class="form-group"><label>Contact</label><input type="text" name="official_contact" placeholder="09XX-XXX-XXXX"></div>

                    <div class="form-group"><label>Email</label><input type="email" name="official_email"></div>

                    <div class="form-group full">

                        <label>Photo <span style="color:var(--muted);font-weight:400">(optional)</span></label>

                        <div style="display:flex;align-items:center;gap:12px">

                            <div id="add-official-photo-preview" style="width:44px;height:44px;border-radius:50%;background:var(--navy-light);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--navy-mid);flex-shrink:0">�???�</div>

                            <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text);background:var(--white)">

                                �???� Upload Photo

                                <input type="file" name="official_photo" accept="image/*" style="display:none" onchange="previewAddOfficialPhoto(this)">

                            </label>

                        </div>

                    </div>

                </div>

                <div class="form-actions">

                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-official')">Cancel</button>

                    <button type="submit" name="add_official" class="btn btn-primary">Save</button>

                </div>

            </div>

        </form>

    </div>

</div>

<!-- Add Zone Leader -->

<div class="modal-backdrop" id="modal-add-zone">

    <div class="modal">

        <div class="modal-header"><h3>Add <?= htmlspecialchars($area_label) ?> Leader</h3><button class="modal-close" onclick="closeModal('modal-add-zone')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <div class="form-grid">

                <div class="form-group"><label>Number <span class="req">*</span></label><input type="number" name="zone_number" min="1" max="50" required></div>

                <div class="form-group"><label>Name / Label</label><input type="text" name="zone_name" placeholder="e.g. Sitio Malaya"></div>

                <div class="form-group"><label>Leader Name <span class="req">*</span></label><input type="text" name="leader_name" required></div>

                <div class="form-group"><label>Contact</label><input type="text" name="leader_contact" placeholder="09XX-XXX-XXXX"></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-zone')">Cancel</button>

                <button type="submit" name="add_zone_leader" class="btn btn-primary">Save</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Edit Zone Leader -->

<div class="modal-backdrop" id="modal-edit-zone">

    <div class="modal">

        <div class="modal-header"><h3>Edit <?= htmlspecialchars($area_label) ?> Leader</h3><button class="modal-close" onclick="closeModal('modal-edit-zone')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <input type="hidden" name="edit_zone_leader" value="1">

            <input type="hidden" name="zone_leader_id" id="edit-zone-id">

            <div class="form-grid">

                <div class="form-group"><label>Name / Label</label><input type="text" name="zone_name" id="edit-zone-name"></div>

                <div class="form-group"><label>Leader Name <span class="req">*</span></label><input type="text" name="leader_name" id="edit-zone-leader" required></div>

                <div class="form-group"><label>Contact</label><input type="text" name="leader_contact" id="edit-zone-contact"></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-zone')">Cancel</button>

                <button type="submit" class="btn btn-primary">Save</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Zone Detail -->

<div class="modal-backdrop" id="modal-zone-detail">

    <div class="modal">

        <div class="modal-header"><h3 id="zone-detail-title">Area Details</h3><button class="modal-close" onclick="closeModal('modal-zone-detail')">?????</button></div>

        <div class="modal-body" id="zone-detail-body"></div>

    </div>

</div>



<!-- Add Alert -->

<div class="modal-backdrop" id="modal-add-alert">

    <div class="modal">

        <div class="modal-header"><h3>Post Alert</h3><button class="modal-close" onclick="closeModal('modal-add-alert')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <div class="form-grid">

                <div class="form-group full"><label>Message <span class="req">*</span></label><textarea name="alert_message" rows="3" required placeholder="Describe the alert..."></textarea></div>

                <div class="form-group full"><label>Severity</label>

                    <select name="alert_severity">

                        <option value="low">Low ? Informational</option>

                        <option value="medium">Medium ? Advisory</option>

                        <option value="high">High ? Urgent</option>

                    </select>

                </div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-alert')">Cancel</button>

                <button type="submit" name="add_alert" class="btn btn-primary">Post Alert</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Families Drill-down -->

<div class="modal-backdrop" id="modal-families-drill">

    <div class="modal modal-wide">

        <div class="modal-header">

            <h3 id="fdrill-title">Families by <?= htmlspecialchars($area_label) ?></h3>

            <button class="modal-close" onclick="closeFamiliesDrill()">?????</button>

        </div>

        <div class="modal-body" style="min-height:440px">

            <div id="fdrill-zones">

                <div style="font-size:12px;color:var(--muted);margin-bottom:12px">Select an area to view households</div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">

                    <?php

                    $all_zone_nums = array_unique(array_merge(array_keys($zones_families), array_keys($families_by_zone)));

                    sort($all_zone_nums);

                    foreach ($all_zone_nums as $zn):

                        $fcount = count($families_by_zone[$zn] ?? []);

                    ?>

                    <div onclick="showFamiliesInZone(<?= $zn ?>)" class="zone-card" style="text-align:center">

                        <div class="zone-num"><?= $zn ?></div>

                        <div style="font-size:11px;color:var(--muted);margin-top:3px"><?= htmlspecialchars($area_label) ?> <?= $zn ?></div>

                        <div style="font-size:11px;color:var(--navy);font-weight:500;margin-top:2px"><?= $fcount ?> families</div>

                    </div>

                    <?php endforeach; ?>

                    <?php if (empty($all_zone_nums)): ?><div class="card-empty" style="grid-column:1/-1">No families registered yet.</div><?php endif; ?>

                </div>

                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">

                    <button class="btn btn-primary btn-sm" onclick="openAddFamilyForm()">+ Add Family</button>

                </div>

            </div>



            <div id="fdrill-families" style="display:none">

                <button onclick="showFdrillZones()" class="btn btn-ghost btn-sm" style="margin-bottom:12px">�???� Back</button>

                <div id="fdrill-families-list"></div>

                <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">

                    <button class="btn btn-primary btn-sm" onclick="openAddFamilyForm()">+ Add Family</button>

                </div>

            </div>



            <div id="fdrill-members" style="display:none">

                <button onclick="showFdrillFamilies()" class="btn btn-ghost btn-sm" style="margin-bottom:12px">�???� Back</button>

                <div id="fdrill-members-content"></div>

            </div>



            <div id="fdrill-add-family" style="display:none">

                <button onclick="showFdrillZones()" class="btn btn-ghost btn-sm" style="margin-bottom:12px">�???� Cancel</button>

                <div style="font-size:13px;font-weight:500;color:var(--navy);margin-bottom:12px">Add New Family</div>

                <form method="POST">

                    <input type="hidden" name="add_family" value="1">

                    <div class="form-grid">

                        <div class="form-group full"><label>Head of Family <span class="req">*</span></label><input type="text" name="head_name" required></div>

                        <div class="form-group"><label><?= htmlspecialchars($area_label) ?> Number <span class="req">*</span></label><input type="number" name="zone_number" id="add-family-zone" min="1" max="50" required></div>

                        <div class="form-group"><label>Birthdate</label><input type="date" name="head_birthdate" id="head-bdate" oninput="calcAge(this,'head-age-display')"></div>

                        <div class="form-group"><label>Age</label><div id="head-age-display" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;color:var(--muted)">?</div></div>

                        <div class="form-group"><label>Contact</label><input type="text" name="head_contact"></div>

                        <div class="form-group"><label>Address</label><input type="text" name="address"></div>

                    </div>

                    <div class="form-actions"><button type="submit" class="btn btn-primary">Save Family</button></div>

                </form>

            </div>

        </div>

    </div>

</div>



<!-- Add Member -->

<div class="modal-backdrop" id="modal-add-member">

    <div class="modal">

        <div class="modal-header"><h3>Add Family Member</h3><button class="modal-close" onclick="closeModal('modal-add-member')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <input type="hidden" name="add_family_member" value="1">

            <input type="hidden" name="family_id" id="add-member-family-id">

            <div style="font-size:12px;color:var(--muted);margin-bottom:14px">Family: <strong id="add-member-family-name" style="color:var(--navy)"></strong></div>

            <div class="form-grid">

                <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="member_name" required></div>

                <div class="form-group"><label>Birthdate</label><input type="date" name="member_birthdate" id="member-bdate" oninput="calcAge(this,'member-age-display')"></div>

                <div class="form-group"><label>Age</label><div id="member-age-display" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;color:var(--muted)">?</div></div>

                <div class="form-group"><label>Relationship</label>

                    <select name="member_relationship">

                        <option value="">? Select ?</option>

                        <option>Spouse</option><option>Son</option><option>Daughter</option>

                        <option>Father</option><option>Mother</option><option>Sibling</option>

                        <option>Grandchild</option><option>Grandparent</option><option>In-law</option>

                        <option>Other Relative</option><option>Non-relative</option>

                    </select>

                </div>

                <div class="form-group"><label>Contact</label><input type="text" name="member_contact"></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-member')">Cancel</button>

                <button type="submit" class="btn btn-primary">Add Member</button>

            </div>

        </div></form>

    </div>

</div>



<!-- Edit Family -->

<div class="modal-backdrop" id="modal-edit-family">

    <div class="modal">

        <div class="modal-header"><h3>Edit Family</h3><button class="modal-close" onclick="closeModal('modal-edit-family')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <input type="hidden" name="edit_family" value="1">

            <input type="hidden" name="family_id" id="edit-family-id">

            <div class="form-grid">

                <div class="form-group full"><label>Head of Family <span class="req">*</span></label><input type="text" name="head_name" id="edit-family-headname" required></div>

                <div class="form-group full"><label>Address</label><input type="text" name="address" id="edit-family-address"></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-family')">Cancel</button>

                <button type="submit" class="btn btn-primary">Save</button>

            </div>

        </div></form>

    </div>

</div>



<form method="POST" id="form-delete-family" style="display:none">

    <input type="hidden" name="delete_family" value="1">

    <input type="hidden" name="family_id" id="delete-family-id">

</form>



<!-- Edit Member -->

<div class="modal-backdrop" id="modal-edit-member">

    <div class="modal">

        <div class="modal-header"><h3>Edit Member</h3><button class="modal-close" onclick="closeModal('modal-edit-member')">?????</button></div>

        <form method="POST"><div class="modal-body">

            <input type="hidden" name="edit_member" value="1">

            <input type="hidden" name="resident_id" id="edit-member-rid">

            <div class="form-grid">

                <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="member_name" id="edit-member-name" required></div>

                <div class="form-group"><label>Birthdate</label><input type="date" name="member_birthdate" id="edit-member-bdate" oninput="calcAge(this,'edit-member-age')"></div>

                <div class="form-group"><label>Age</label><div id="edit-member-age" style="padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;color:var(--muted)">?</div></div>

                <div class="form-group"><label>Relationship</label>

                    <select name="member_relationship" id="edit-member-rel">

                        <option value="">? Select ?</option>

                        <option>Head</option><option>Spouse</option><option>Son</option><option>Daughter</option>

                        <option>Father</option><option>Mother</option><option>Sibling</option>

                        <option>Grandchild</option><option>Grandparent</option><option>In-law</option>

                        <option>Other Relative</option><option>Non-relative</option>

                    </select>

                </div>

                <div class="form-group"><label>Contact</label><input type="text" name="member_contact" id="edit-member-contact"></div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-member')">Cancel</button>

                <button type="submit" class="btn btn-primary">Save</button>

            </div>

        </div></form>

    </div>

</div>



<!-- QR Scanner -->

<div class="modal-backdrop" id="modal-qr-scan">

    <div class="modal">

        <div class="modal-header"><h3>QR Scanner</h3><button class="modal-close" onclick="closeModal('modal-qr-scan')">?????</button></div>

        <div class="modal-body" style="text-align:center;padding:40px 22px">

            <div style="font-size:15px;font-weight:500;color:var(--navy)">QR Scanner</div>

            <div style="font-size:13px;color:var(--muted);margin-top:6px">Connect a QR scanner or use a camera to scan resident codes.</div>

            <div id="qr-reader" style="margin-top:20px"></div>

            <div id="qr-result" style="margin-top:12px;font-weight:500;color:var(--navy)"></div>

        </div>

    </div>

</div>





<script>

(function() {

    var d = new Date();

    document.getElementById('topbar-date').textContent = d.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

})();



var areaLabel = <?= json_encode($area_label) ?>;

var sectionTitles = {

    dashboard: 'Dashboard', profile: 'Barangay Profile',

    officials: 'Officials & Staff', zones: areaLabel + ' Leaders',

    households: 'Households', residents: 'Residents',

    ayuda: 'Ayuda / Assistance', qr: 'Scan QR / History', alerts: 'Alerts'

};



function showSection(id, btn) {

    localStorage.setItem('activeSection', id);

    document.querySelectorAll('.section').forEach(function(s){ s.classList.remove('active'); });

    document.getElementById('section-' + id).classList.add('active');

    document.querySelectorAll('.menu-item').forEach(function(l){ l.classList.remove('active'); });

    if (btn) btn.classList.add('active');

    document.getElementById('topbar-title').textContent = sectionTitles[id] || 'Dashboard';

}



function openModal(id) { document.getElementById(id).classList.add('open'); document.body.style.overflow = 'hidden'; }

function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }



document.querySelectorAll('.modal-backdrop').forEach(function(b) {

    b.addEventListener('click', function(e){ if (e.target === this) closeModal(this.id); });

});

document.addEventListener('keydown', function(e) {

    if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.open').forEach(function(m){ closeModal(m.id); });

});



var zoneResidents = <?= json_encode($zones_residents) ?>;

var zoneFamilies  = <?= json_encode($zones_families) ?>;



function openZoneDetail(zn, zname, leader, contact) {

    document.getElementById('zone-detail-title').textContent = areaLabel + ' ' + zn + (zname ? ' ? ' + zname : '');

    document.getElementById('zone-detail-body').innerHTML =

        '<div class="info-row"><span class="info-label">Leader</span><span class="info-value">' + leader + '</span></div>' +

        '<div class="info-row"><span class="info-label">Contact</span><span class="info-value">' + (contact || '?') + '</span></div>' +

        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">' +

        '<div class="stat-card"><div class="stat-num">' + (zoneFamilies[zn] || 0) + '</div><div class="stat-label">Families</div></div>' +

        '<div class="stat-card"><div class="stat-num">' + (zoneResidents[zn] || 0) + '</div><div class="stat-label">Residents</div></div>' +

        '</div>';

    openModal('modal-zone-detail');

}



function filterTable(input, tableId) {

    var v = input.value.toLowerCase();

    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(row) {

        row.style.display = row.textContent.toLowerCase().indexOf(v) >= 0 ? '' : 'none';

    });

}



function switchTab(tabId, btn, groupId) {

    var group = document.getElementById(groupId || 'profile-tabs');

    var panelPrefix = tabId.replace(/-[^-]*$/, '-');

    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });

    document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });

    document.getElementById(tabId).classList.add('active');

    if (btn) btn.classList.add('active');

}



function globalFamilySearch(query) {

    var q = (query || '').toLowerCase().trim();

    var zoneFilter = document.getElementById('zone-filter');

    var selectedZone = zoneFilter ? zoneFilter.value : '';

    var rows = document.querySelectorAll('#tbl-families tbody tr');

    var visible = 0;

    rows.forEach(function(row) {

        var name    = row.getAttribute('data-name') || '';

        var address = row.getAttribute('data-address') || '';

        var zone    = row.getAttribute('data-zone') || '';

        var matchSearch = !q || name.indexOf(q) >= 0 || address.indexOf(q) >= 0;

        var matchZone   = !selectedZone || zone === selectedZone;

        if (matchSearch && matchZone) { row.style.display = ''; visible++; }

        else row.style.display = 'none';

    });

    var summary = document.getElementById('search-summary');

    var noRes   = document.getElementById('no-results-msg');

    if (summary) summary.textContent = (q || selectedZone) ? 'Showing ' + visible + ' of ' + rows.length + ' households' : '';

    if (noRes) noRes.style.display = (visible === 0 && (q || selectedZone)) ? '' : 'none';

}



var familiesByZone = <?= json_encode($families_by_zone) ?>;

var currentDrillZone = null;



function closeFamiliesDrill() { closeModal('modal-families-drill'); setTimeout(showFdrillZones, 300); }

function showFdrillZones() {

    document.getElementById('fdrill-zones').style.display = '';

    document.getElementById('fdrill-families').style.display = 'none';

    document.getElementById('fdrill-members').style.display = 'none';

    document.getElementById('fdrill-add-family').style.display = 'none';

    document.getElementById('fdrill-title').textContent = 'Families by ' + areaLabel;

}

function showFdrillFamilies() {

    document.getElementById('fdrill-zones').style.display = 'none';

    document.getElementById('fdrill-families').style.display = '';

    document.getElementById('fdrill-members').style.display = 'none';

    document.getElementById('fdrill-add-family').style.display = 'none';

    document.getElementById('fdrill-title').textContent = areaLabel + ' ' + currentDrillZone + ' ? Households';

}

function showFamiliesInZone(zn) {

    currentDrillZone = zn;

    var families = familiesByZone[zn] || [];

    var html = '';

    if (!families.length) { html = '<div class="card-empty">No families in ' + areaLabel + ' ' + zn + ' yet.</div>'; }

    else {

        families.forEach(function(f) {

            html += '<div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">' +

                '<div style="width:34px;height:34px;border-radius:50%;background:var(--navy-light);color:var(--navy-mid);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:500;flex-shrink:0;cursor:pointer" onclick="showFamilyMembers(' + f.id + ',\'' + escHtml(f.head_name) + '\')">' + escHtml(f.head_name.charAt(0).toUpperCase()) + '</div>' +

                '<div style="flex:1;cursor:pointer" onclick="showFamilyMembers(' + f.id + ',\'' + escHtml(f.head_name) + '\')">' +

                '<div style="font-size:13px;font-weight:500;color:var(--text)">' + escHtml(f.head_name) + '</div>' +

                '<div style="font-size:11px;color:var(--muted)">' + (f.address || 'No address') + ' ? ' + (f.member_count || 0) + ' members</div>' +

                '</div>' +

                '<div style="display:flex;gap:5px">' +

                '<button onclick="openEditFamily(' + f.id + ',\'' + escHtml(f.head_name) + '\',\'' + escHtml(f.address||'') + '\')" class="btn btn-ghost btn-sm">Edit</button>' +

                '<button onclick="confirmDeleteFamily(' + f.id + ',\'' + escHtml(f.head_name) + '\')" class="btn btn-danger btn-sm">Delete</button>' +

                '</div></div>';

        });

    }

    document.getElementById('fdrill-families-list').innerHTML = html;

    showFdrillFamilies();

}

function showFamilyMembers(familyId, headName) {

    document.getElementById('fdrill-title').textContent = headName + ' ? Members';

    document.getElementById('fdrill-members-content').innerHTML = '<div class="card-empty">Loading...</div>';

    document.getElementById('fdrill-zones').style.display = 'none';

    document.getElementById('fdrill-families').style.display = 'none';

    document.getElementById('fdrill-members').style.display = '';

    document.getElementById('fdrill-add-family').style.display = 'none';

    fetch('get_family_members.php?family_id=' + familyId + '&bid=<?= $bid ?>')

        .then(function(r){ return r.json(); })

        .then(function(members) {

            var html = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">' +

                '<span style="font-size:12px;color:var(--muted)">' + members.length + ' member(s)</span>' +

                '<button class="btn btn-primary btn-sm" onclick="openAddMember(' + familyId + ',\'' + escHtml(headName) + '\')">+ Add Member</button></div>';

            if (!members.length) { html += '<div class="card-empty">No members yet.</div>'; }

            else {

                html += '<table class="data-table"><thead><tr><th>Name</th><th>Relationship</th><th>Age</th><th>Contact</th><th></th></tr></thead><tbody>';

                members.forEach(function(m) {

                    var age = m.birth_date ? calcAgeFromDate(m.birth_date) + ' yrs' : '?';

                    html += '<tr>' +

                        '<td style="font-weight:500">' + escHtml(m.full_name) + '</td>' +

                        '<td><span class="tag tag-navy">' + escHtml(m.relationship || '?') + '</span></td>' +

                        '<td style="color:var(--muted)">' + age + '</td>' +

                        '<td style="color:var(--muted)">' + (m.contact_number || '?') + '</td>' +

                        '<td style="white-space:nowrap">' +

                        '<button onclick="openEditMember(' + m.resident_id + ',\'' + escHtml(m.full_name) + '\',\'' + (m.birth_date||'') + '\',\'' + escHtml(m.relationship||'') + '\',\'' + escHtml(m.contact_number||'') + '\')" class="btn btn-ghost btn-sm">Edit</button>' +

                        '<form method="POST" style="display:inline" onsubmit="return confirm(\'Remove?\')">' +

                        '<input type="hidden" name="delete_member" value="1"><input type="hidden" name="resident_id" value="' + m.resident_id + '"><input type="hidden" name="family_id" value="' + familyId + '">' +

                        '<button type="submit" class="btn btn-danger btn-sm">?????</button></form></td></tr>';

                });

                html += '</tbody></table>';

            }

            document.getElementById('fdrill-members-content').innerHTML = html;

        })

        .catch(function(){ document.getElementById('fdrill-members-content').innerHTML = '<div class="card-empty">Could not load members.</div>'; });

}

function openAddMember(familyId, familyName) {

    document.getElementById('add-member-family-id').value = familyId;

    document.getElementById('add-member-family-name').textContent = familyName;

    openModal('modal-add-member');

}

function openAddFamilyForm() {

    ['fdrill-zones','fdrill-families','fdrill-members'].forEach(function(id){ document.getElementById(id).style.display = 'none'; });

    document.getElementById('fdrill-add-family').style.display = '';

    document.getElementById('fdrill-title').textContent = 'Add New Family';

    if (currentDrillZone) document.getElementById('add-family-zone').value = currentDrillZone;

}

function openEditFamily(fid, headName, address) {

    document.getElementById('edit-family-id').value = fid;

    document.getElementById('edit-family-headname').value = headName;

    document.getElementById('edit-family-address').value = address;

    openModal('modal-edit-family');

}

function confirmDeleteFamily(fid, headName) {

    if (confirm('Delete family of ' + headName + '? This will also remove all members.')) {

        document.getElementById('delete-family-id').value = fid;

        document.getElementById('form-delete-family').submit();

    }

}

function openEditZone(id, zname, leader, contact) {

    document.getElementById('edit-zone-id').value = id;

    document.getElementById('edit-zone-name').value = zname;

    document.getElementById('edit-zone-leader').value = leader;

    document.getElementById('edit-zone-contact').value = contact;

    openModal('modal-edit-zone');

}

function openEditMember(rid, name, bdate, relation, contact) {

    document.getElementById('edit-member-rid').value = rid;

    document.getElementById('edit-member-name').value = name;

    document.getElementById('edit-member-bdate').value = bdate;

    document.getElementById('edit-member-contact').value = contact;

    var sel = document.getElementById('edit-member-rel');

    for (var i = 0; i < sel.options.length; i++) { if (sel.options[i].value === relation) { sel.selectedIndex = i; break; } }

    if (bdate) document.getElementById('edit-member-age').textContent = calcAgeFromDate(bdate) + ' yrs';

    openModal('modal-edit-member');

}

function calcAge(input, displayId) {

    document.getElementById(displayId).textContent = input.value ? calcAgeFromDate(input.value) + ' yrs' : '?';

}

function calcAgeFromDate(dateStr) {

    var today = new Date(), bdate = new Date(dateStr);

    var age = today.getFullYear() - bdate.getFullYear();

    var m = today.getMonth() - bdate.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < bdate.getDate())) age--;

    return age;

}

function escHtml(str) {

    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');

}



(function() {

    var saved = localStorage.getItem('activeSection') || 'dashboard';

    var menuBtn = document.querySelector('.menu-item[onclick*="' + saved + '"]');

    showSection(saved, menuBtn);

})();



var msg = document.querySelector('.alert-banner');

if (msg) setTimeout(function(){ msg.style.transition = '0.4s'; msg.style.opacity = '0'; setTimeout(function(){ msg.remove(); }, 400); }, 3500);



// �??????�?????? SESSION KEEPALIVE �??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????�??????

(function keepAlive() {

    setInterval(function() {

        fetch('keepalive.php', {

            method:      'GET',

            credentials: 'same-origin'

        }).catch(function() {});

    }, 120000);

})();



function openEditOfficial(id, name, position, contact, email, hasPhoto) {

    document.getElementById('edit-official-id').value = id;

    document.getElementById('edit-official-name').value = name;

    document.getElementById('edit-official-contact').value = contact;

    document.getElementById('edit-official-email').value = email;

    var sel = document.getElementById('edit-official-position');

    for (var i = 0; i < sel.options.length; i++) {

        if (sel.options[i].value === position) { sel.selectedIndex = i; break; }

    }

    var preview = document.getElementById('edit-official-photo-preview');

    if (hasPhoto) {

        preview.innerHTML = '<span style="font-size:10px;color:var(--muted)">Photo saved</span>';

    } else {

        preview.innerHTML = name.charAt(0).toUpperCase();

    }

    openModal('modal-edit-official');

}

function previewOfficialPhoto(input) {

    if (!input.files || !input.files[0]) return;

    var reader = new FileReader();

    reader.onload = function(e) {

        var preview = document.getElementById('edit-official-photo-preview');

        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';

    };

    reader.readAsDataURL(input.files[0]);

}

function previewAddOfficialPhoto(input) {

    if (!input.files || !input.files[0]) return;

    var reader = new FileReader();

    reader.onload = function(e) {

        var preview = document.getElementById('add-official-photo-preview');

        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';

    };

    reader.readAsDataURL(input.files[0]);

}

function handleLogoUpload(input) {

    if (!input.files || !input.files[0]) return;

    var form = document.createElement('form');

    form.method = 'POST';

    form.enctype = 'multipart/form-data';

    form.style.display = 'none';



    var fileInput = document.createElement('input');

    fileInput.type = 'file';

    fileInput.name = 'brgy_logo';

    var dt = new DataTransfer();

    dt.items.add(input.files[0]);

    fileInput.files = dt.files;



    var hidden = document.createElement('input');

    hidden.type = 'hidden';

    hidden.name = 'update_brgy_logo';

    hidden.value = '1';



    form.appendChild(fileInput);

    form.appendChild(hidden);

    document.body.appendChild(form);

    form.submit();

}



</script>



<!-- My Profile -->

<div class="modal-backdrop" id="modal-profile">

    <div class="modal">

        <div class="modal-header">

            <h3>My Profile</h3>

            <button class="modal-close" onclick="closeModal('modal-profile')">?????</button>

        </div>

        <div class="modal-body">

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">

                <div style="width:44px;height:44px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:300">

                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'B', 0, 1)) ?>

                </div>

                <div>

                    <div style="font-size:14px;font-weight:500;color:var(--navy)"><?= htmlspecialchars($_SESSION['full_name']) ?></div>

                    <div style="font-size:12px;color:var(--muted)">Barangay Admin</div>

                </div>

            </div>

            <div class="info-row"><span class="info-label">Name</span><span class="info-value"><?= htmlspecialchars($_SESSION['full_name']) ?></span></div>

            <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($brgy_info['email'] ?? '?') ?></span></div>

            <div class="info-row"><span class="info-label">Role</span><span class="tag tag-navy">Barangay Admin</span></div>

            <div class="info-row"><span class="info-label">Barangay</span><span class="info-value"><?= htmlspecialchars($brgy_info['name'] ?? '?') ?></span></div>

            <div class="info-row"><span class="info-label">Since</span><span class="info-value"><?= !empty($admin_info['created_at']) ? date('F d, Y', strtotime($admin_info['created_at'])) : '?' ?></span></div>

            <div class="info-row" style="flex-direction:column;align-items:flex-start;gap:10px">

                <span class="info-label">Account Settings</span>

                <button class="btn btn-secondary btn-sm" onclick="closeModal('modal-profile');openModal('modal-edit-account')">Edit Account Info</button>

            </div>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:space-between">

                <a href="<?= str_repeat('../', 2) ?>logout.php" class="btn btn-danger btn-sm">Logout</a>

                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-profile')">Close</button>

            </div>

        </div>

    </div>

</div>



<!-- Edit Account -->

<div class="modal-backdrop" id="modal-edit-account">

    <div class="modal">

        <div class="modal-header">

            <h3>Edit Account</h3>

            <button class="modal-close" onclick="closeModal('modal-edit-account')">?????</button>

        </div>

        <form method="POST"><div class="modal-body">

            <input type="hidden" name="update_account" value="1">

            <div class="form-grid">

                <div class="form-group full">

                    <label>Full Name <span class="req">*</span></label>

                    <input type="text" name="account_fullname" 

                        value="<?= htmlspecialchars($admin_info['full_name'] ?? '') ?>" required>

                </div>

                <div class="form-group full">

                    <label>Email <span style="color:var(--muted);font-weight:400">(barangay email ? edit in Barangay Profile)</span></label>

                    <input type="text" 

                        value="<?= htmlspecialchars($brgy_info['email'] ?? '?') ?>" 

                        disabled 

                        style="background:var(--surface-2);color:var(--muted);cursor:not-allowed">

                </div>

                <div class="form-group full">

                    <label>New Password <span style="color:var(--muted);font-weight:400">(leave blank to keep current)</span></label>

                    <input type="password" name="account_password" placeholder="Enter new password">

                </div>

            </div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-account')">Cancel</button>

                <button type="submit" class="btn btn-primary">Save Changes</button>

            </div>

        </div></form>

    </div>

</div>

<!-- Edit Official -->

<div class="modal-backdrop" id="modal-edit-official">

    <div class="modal">

        <div class="modal-header"><h3>Edit Official</h3><button class="modal-close" onclick="closeModal('modal-edit-official')">?????</button></div>

       <form method="POST" enctype="multipart/form-data"><div class="modal-body">

            <input type="hidden" name="edit_official" value="1">

            <input type="hidden" name="official_id" id="edit-official-id">

            <div class="form-grid">

                <div class="form-group full"><label>Full Name <span class="req">*</span></label><input type="text" name="official_name" id="edit-official-name" required></div>

                <div class="form-group"><label>Position</label>

                    <select name="official_position" id="edit-official-position">

                        <option value="">? Select ?</option>

                        <option>Barangay Captain</option><option>Kagawad</option>

                        <option>SK Chairman</option><option>Barangay Secretary</option>

                        <option>Barangay Treasurer</option><option>Barangay Health Worker</option>

                        <option>Tanod</option><option>Staff</option><option>Other</option>

                    </select>

                </div>

                <div class="form-group"><label>Contact</label><input type="text" name="official_contact" id="edit-official-contact"></div>

               <div class="form-group"><label>Email</label><input type="email" name="official_email" id="edit-official-email"></div>

            </div>

            <div class="form-group full">

    <label>Photo</label>

    <div style="display:flex;align-items:center;gap:12px">

        <div id="edit-official-photo-preview" style="width:44px;height:44px;border-radius:50%;background:var(--navy-light);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--navy-mid);flex-shrink:0"></div>

        <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;color:var(--text);background:var(--white)">

            �???� Upload Photo

            <input type="file" name="official_photo" accept="image/*" style="display:none" onchange="previewOfficialPhoto(this)">

        </label>

        <input type="hidden" name="upload_official_photo" value="1">

    </div>

</div>

            <div class="form-actions">

                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-official')">Cancel</button>

                <button type="submit" class="btn btn-primary">Save Changes</button>

            </div>

        </div></form>

    </div>

</div>

</body>

</html>






















































































































