<?php
require_once '../../config/auth_guard.php';
require_role('barangay');
require_once '../../config/db.php';

$bid = (int)$_SESSION['barangay_id'];

$brgy_info = [];
$r = $conn->query("SELECT * FROM barangays WHERE barangay_id = $bid");
if ($r && $r->num_rows > 0) $brgy_info = $r->fetch_assoc();
$area_label = $brgy_info['area_label'] ?? 'Zone';
$area_label_plural = $area_label . 's';
$active_alerts=0;$total_residents=0;$total_families=0;$total_zone_leaders=0;$total_officials=0;
$r=$conn->query("SELECT (SELECT COUNT(*) FROM alerts WHERE barangay_id=$bid AND resolved=0) as alerts,(SELECT COUNT(*) FROM residents WHERE barangay_id=$bid AND is_active=1) as residents,(SELECT COUNT(*) FROM families WHERE barangay_id=$bid AND is_active=1) as families,(SELECT COUNT(*) FROM zone_leaders WHERE barangay_id=$bid AND is_active=1) as zone_leaders,(SELECT COUNT(*) FROM barangay_officials WHERE barangay_id=$bid AND is_active=1) as officials");
if($r){$_c=$r->fetch_assoc();$active_alerts=$_c['alerts'];$total_residents=$_c['residents'];$total_families=$_c['families'];$total_zone_leaders=$_c['zone_leaders'];$total_officials=$_c['officials'];}
$total_zones=$total_zone_leaders;

function get_columns($conn, $table) {
    $cols = [];
    $r = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($r) while ($row = $r->fetch_assoc()) $cols[] = strtolower($row['Field']);
    return $cols;
}

function normalize_date($val) {
    if (empty($val)) return null;
    $val = trim($val);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;
    $ts = strtotime($val);
    if ($ts === false) return null;
    return date('Y-m-d', $ts);
}

function parse_zone($area) {
    // Returns [zone_number, zone_name]
    $area = trim($area);
    if (empty($area)) return [0, ''];
    // If it starts with ZONE followed by a number
    if (preg_match('/^ZONE\s*(\d+)$/i', $area, $m)) {
        return [(int)$m[1], $area];
    }
    // Named area — no number
    return [0, $area];
}

$message = '';
$message_type = '';
$preview_data = [];
$import_done = false;
$import_stats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'preview') {
        $hhid_file  = $_FILES['hhid_csv']  ?? null;
        $hhmem_file = $_FILES['hhmem_csv'] ?? null;

        if (!$hhid_file || $hhid_file['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please upload the HHID (household heads) CSV file.';
            $message_type = 'error';
        } else {
            $hhid_rows = [];
            if (($fh = fopen($hhid_file['tmp_name'], 'r')) !== false) {
                $headers = fgetcsv($fh);
                $headers = array_map('trim', $headers);
                while (($row = fgetcsv($fh)) !== false) {
                    if (count($row) < 2) continue;
                    $hhid_rows[] = array_combine(
                        array_map('strtoupper', $headers),
                        array_pad($row, count($headers), '')
                    );
                }
                fclose($fh);
            }

            $hhmem_rows = [];
            if ($hhmem_file && $hhmem_file['error'] === UPLOAD_ERR_OK) {
                if (($fh = fopen($hhmem_file['tmp_name'], 'r')) !== false) {
                    $headers = fgetcsv($fh);
                    $headers = array_map('trim', $headers);
                    while (($row = fgetcsv($fh)) !== false) {
                        if (count($row) < 2) continue;
                        $hhmem_rows[] = array_combine(
                            array_map('strtoupper', $headers),
                            array_pad($row, count($headers), '')
                        );
                    }
                    fclose($fh);
                }
            }

            $fk_col = null;
            if (!empty($hhmem_rows)) {
                $hhmem_cols = array_keys($hhmem_rows[0]);
                foreach (['ID','HHIDID','HHID_ID','HHID','HH_ID'] as $candidate) {
                    if (in_array($candidate, $hhmem_cols)) { $fk_col = $candidate; break; }
                }
            }

            foreach (array_slice($hhid_rows, 0, 10) as $hh) {
                $hhid = $hh['ID'] ?? '';
                $name = trim(implode(' ', array_filter([
                    $hh['FIRSTNAME'] ?? '', $hh['MIDNAME'] ?? '',
                    $hh['LASTNAME'] ?? '', $hh['SUFFIXNAME'] ?? ''
                ])));
                [$zone, $zone_name] = parse_zone($hh['AREA'] ?? '');
                $members = [];
                if ($fk_col) {
                    foreach ($hhmem_rows as $mem) {
                        $mem_id = rtrim($mem[$fk_col] ?? '', '.0');
                        $hh_id  = rtrim($hhid, '.0');
                        if ($mem_id == $hh_id) {
                            $members[] = trim(implode(' ', array_filter([
                                $mem['FIRSTNAME'] ?? '', $mem['MIDNAME'] ?? '',
                                $mem['LASTNAME'] ?? '', $mem['SUFFIXNAME'] ?? ''
                            ])));
                        }
                    }
                }
                $preview_data[] = [
                    'name'    => $name,
                    'zone'    => $zone_name ?: ($zone ?: '—'),
                    'address' => trim(implode(', ', array_filter([
                        ($hh['BLK'] ?? '') ? 'Blk ' . $hh['BLK'] : '',
                        ($hh['LOT'] ?? '') ? 'Lot ' . $hh['LOT'] : '',
                        $hh['STREET'] ?? '',
                    ]))),
                    'members' => $members,
                    'member_count' => count($members),
                ];
            }

            $_SESSION['import_hhid']  = $hhid_rows;
            $_SESSION['import_hhmem'] = $hhmem_rows;
            $_SESSION['import_fk']    = $fk_col;

            $message = 'Preview ready — showing first ' . min(10, count($hhid_rows)) . ' of ' . count($hhid_rows) . ' households. Confirm to import all.';
            $message_type = 'info';
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'import') {
        $hhid_rows  = $_SESSION['import_hhid']  ?? [];
        $hhmem_rows = $_SESSION['import_hhmem'] ?? [];
        $fk_col     = $_SESSION['import_fk']    ?? null;

        if (empty($hhid_rows)) {
            $message = 'No data found in session. Please upload and preview again.';
            $message_type = 'error';
        } else {
            $fam_inserted = 0;
            $fam_skipped  = 0;
            $res_inserted = 0;
            $res_skipped  = 0;

            $conn->begin_transaction();
            try {
                // Area cache - auto-create areas from CSV
                $area_cache = [];
                $get_area_id = function($zone_name_val) use ($conn, $bid, &$area_cache) {
                    if (empty($zone_name_val)) return null;
                    $key = strtoupper(trim($zone_name_val));
                    if (isset($area_cache[$key])) return $area_cache[$key];
                    // Check if exists
                    $esc = $conn->real_escape_string($key);
                    $r = $conn->query("SELECT area_id FROM barangay_areas WHERE barangay_id=$bid AND UPPER(area_name)='$esc' AND is_active=1");
                    if ($r && $r->num_rows > 0) {
                        $aid = $r->fetch_assoc()['area_id'];
                    } else {
                        // Determine type
                        if (preg_match('/^ZONE/i', $key)) $atype = 'Zone';
                        elseif (preg_match('/CMPD|COMPOUND/i', $key)) $atype = 'Compound';
                        elseif (preg_match('/SUBD|SUBDIVISION/i', $key)) $atype = 'Subdivision';
                        elseif (preg_match('/PUROK/i', $key)) $atype = 'Purok';
                        elseif (preg_match('/SITIO/i', $key)) $atype = 'Sitio';
                        elseif (preg_match('/STREET|ST\./i', $key)) $atype = 'Street';
                        else $atype = 'Area';
                        $mx_r = $conn->query("SELECT MAX(sort_order) as mx FROM barangay_areas WHERE barangay_id=$bid");
                        $mx = ($mx_r->fetch_assoc()['mx'] ?? -1) + 1;
                        $orig = $conn->real_escape_string(trim($zone_name_val));
                        $conn->query("INSERT INTO barangay_areas (barangay_id, area_name, area_type, sort_order) VALUES ($bid, '$orig', '$atype', $mx)");
                        $aid = $conn->insert_id;
                    }
                    $area_cache[$key] = $aid;
                    return $aid;
                };

                // Clear existing data before import
                $conn->query("SET FOREIGN_KEY_CHECKS=0");
                $conn->query("DELETE FROM residents WHERE barangay_id=$bid");
                $conn->query("DELETE FROM families WHERE barangay_id=$bid");
                $conn->query("SET FOREIGN_KEY_CHECKS=1");

                foreach ($hhid_rows as $hh) {
                    $hhid      = $hh['ID'] ?? '';
                    $fname     = $conn->real_escape_string($hh['FIRSTNAME'] ?? '');
                    $mname     = $conn->real_escape_string($hh['MIDNAME'] ?? '');
                    $lname     = $conn->real_escape_string($hh['LASTNAME'] ?? '');
                    $sname     = $conn->real_escape_string($hh['SUFFIXNAME'] ?? '');
                    $full_name = $conn->real_escape_string(trim(implode(' ', array_filter([
                        $hh['FIRSTNAME'] ?? '', $hh['MIDNAME'] ?? '',
                        $hh['LASTNAME'] ?? '', $hh['SUFFIXNAME'] ?? ''
                    ]))));

                    [$zone, $zone_name_raw] = parse_zone($hh['AREA'] ?? '');
                    $area_id_val = $get_area_id($zone_name_raw);
                    $zone_name = $conn->real_escape_string($zone_name_raw);

                    $contact   = $conn->real_escape_string($hh['CONTACTNO'] ?? '');
                    $bday      = normalize_date($hh['BDAY'] ?? '');
                    $bday_sql  = $bday ? "'$bday'" : 'NULL';
                    $blk       = $conn->real_escape_string($hh['BLK'] ?? '');
                    $lot       = $conn->real_escape_string($hh['LOT'] ?? '');
                    $street    = $conn->real_escape_string($hh['STREET'] ?? '');
                    $address   = $conn->real_escape_string(implode(', ', array_filter([
                        $blk ? "Blk $blk" : '', $lot ? "Lot $lot" : '', $street
                    ])));
                    $gender    = $conn->real_escape_string($hh['GENDER'] ?? '');

                    if (!$full_name) { $fam_skipped++; continue; }

                    $conn->query("INSERT INTO families (barangay_id, head_name, zone_number, zone_name, area_id, address, member_count) VALUES ($bid, '$full_name', $zone, '$zone_name', " . ($area_id_val ?? 'NULL') . ", '$address', 0)");
                    $fam_id = $conn->insert_id;
                    if (!$fam_id) { $fam_skipped++; continue; }
                    $fam_inserted++;

                    $conn->query("INSERT INTO residents (barangay_id, family_id, full_name, birth_date, contact_number, zone_number, zone_name, area_id, relationship, gender, is_active) VALUES ($bid, $fam_id, '$full_name', $bday_sql, '$contact', $zone, '$zone_name', " . ($area_id_val ?? 'NULL') . ", 'Head', '$gender', 1)");
                    if ($conn->insert_id) $res_inserted++;

                    if ($fk_col) {
                        foreach ($hhmem_rows as $mem) {
                            $mem_id = rtrim($mem[$fk_col] ?? '', '.0');
                            $hh_id  = rtrim($hhid, '.0');
                            if ($mem_id != $hh_id) continue;

                            $mfull = $conn->real_escape_string(trim(implode(' ', array_filter([
                                $mem['FIRSTNAME'] ?? '', $mem['MIDNAME'] ?? '',
                                $mem['LASTNAME'] ?? '', $mem['SUFFIXNAME'] ?? ''
                            ]))));
                            if (!$mfull) { $res_skipped++; continue; }

                            $mbday     = normalize_date($mem['BDAY'] ?? $mem['BIRTHDATE'] ?? '');
                            $mbday_sql = $mbday ? "'$mbday'" : 'NULL';
                            $mcontact  = $conn->real_escape_string($mem['CONTACTNO'] ?? $mem['CONTACT'] ?? '');
                            $mrelation = $conn->real_escape_string($mem['RELATIONSHIP'] ?? '');
                            $mgender   = $conn->real_escape_string($mem['GENDER'] ?? '');

                            $conn->query("INSERT INTO residents (barangay_id, family_id, full_name, birth_date, contact_number, zone_number, zone_name, area_id, relationship, gender, is_active) VALUES ($bid, $fam_id, '$mfull', $mbday_sql, '$mcontact', $zone, '$zone_name', " . ($area_id_val ?? 'NULL') . ", '$mrelation', '$mgender', 1)");
                            if ($conn->insert_id) $res_inserted++; else $res_skipped++;
                        }
                    }

                    $conn->query("UPDATE families SET member_count = (SELECT COUNT(*) FROM residents WHERE family_id = $fam_id AND is_active = 1) WHERE id = $fam_id");
                }

                $conn->commit();
                unset($_SESSION['import_hhid'], $_SESSION['import_hhmem'], $_SESSION['import_fk']);
                $import_done = true;
                $import_stats = [
                    'families_inserted'  => $fam_inserted,
                    'families_skipped'   => $fam_skipped,
                    'residents_inserted' => $res_inserted,
                    'residents_skipped'  => $res_skipped,
                ];
                $message = 'Import complete.';
                $message_type = 'success';

            } catch (Exception $e) {
                $conn->rollback();
                $message = 'Import failed: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Import Residents — SPAC</title>

<?php include __DIR__.'/shared_style.css.php'; ?>
<style>
.page-header { margin-bottom:28px; }
.page-header h2 { font-size:20px; font-weight:600; color:var(--navy); margin:0 0 4px; }
.page-header p { font-size:13px; color:var(--muted); margin:0; }

.steps { display:flex; gap:0; margin-bottom:28px; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.step { flex:1; padding:14px 20px; font-size:12px; font-weight:500; color:var(--muted); background:var(--surface-2); border-right:1px solid var(--border); display:flex; align-items:center; gap:10px; }
.step:last-child { border-right:none; }
.step.active { background:#fff; color:var(--navy); }
.step.done { background:#f0fdf4; color:#16a34a; }
.step-num { font-size:11px; font-weight:700; background:var(--border); color:var(--muted); border-radius:20px; padding:2px 8px; font-family:"DM Mono",monospace; }
.step.active .step-num { background:var(--navy); color:#fff; }
.step.done .step-num { background:#16a34a; color:#fff; }

.card-title { font-size:14px; font-weight:600; color:var(--navy); margin-bottom:4px; }
.card-sub { font-size:12px; color:var(--muted); margin-bottom:20px; }

.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
.form-group label { display:block; font-size:12px; font-weight:500; color:var(--navy); margin-bottom:8px; }
.req { color:#dc2626; }

.file-drop { border:2px dashed var(--border); border-radius:10px; padding:32px 20px; text-align:center; position:relative; cursor:pointer; transition:border-color .15s, background .15s; background:#fafafa; }
.file-drop:hover { border-color:var(--navy); background:#f8faff; }
.file-drop input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.file-drop-label { font-size:13px; color:var(--muted); pointer-events:none; }
.file-drop-label strong { color:var(--navy); }
.file-name { display:block; margin-top:8px; font-size:12px; color:#16a34a; font-weight:500; font-family:"DM Mono",monospace; }

.btn-primary { background:var(--navy); color:#fff; border:none; padding:10px 24px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:"DM Sans",sans-serif; text-decoration:none; display:inline-block; }
.btn-primary:hover { opacity:.9; }
.btn-secondary { background:var(--surface-2); color:var(--navy); border:1px solid var(--border); padding:10px 24px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:"DM Sans",sans-serif; text-decoration:none; display:inline-block; }

.alert-banner { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:20px; }
.alert-banner.success { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.alert-banner.error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.stat-card { background:#fff; border:1px solid var(--border); border-radius:10px; padding:20px; }
.stat-num { font-size:32px; font-weight:300; color:var(--navy); font-family:"DM Mono",monospace; line-height:1; }
.stat-label { font-size:12px; color:var(--muted); margin-top:6px; }

/* Preview table */
.preview-table { width:100%; border-collapse:collapse; font-size:12px; }
.preview-table th { background:var(--surface-2); padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); }
.preview-table td { padding:8px 12px; border-bottom:1px solid var(--border); color:var(--text); }
.preview-table tr:last-child td { border-bottom:none; }
.preview-wrap { overflow-x:auto; border:1px solid var(--border); border-radius:8px; margin-bottom:20px; }
</style>
<style>
.page-header { margin-bottom:28px; }
.page-header h2 { font-size:20px; font-weight:600; color:var(--navy); margin:0 0 4px; }
.page-header p { font-size:13px; color:var(--muted); margin:0; }

.steps { display:flex; gap:0; margin-bottom:28px; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.step { flex:1; padding:14px 20px; font-size:12px; font-weight:500; color:var(--muted); background:var(--surface-2); border-right:1px solid var(--border); display:flex; align-items:center; gap:10px; }
.step:last-child { border-right:none; }
.step.active { background:#fff; color:var(--navy); }
.step.done { background:#f0fdf4; color:#16a34a; }
.step-num { font-size:11px; font-weight:700; background:var(--border); color:var(--muted); border-radius:20px; padding:2px 8px; font-family:"DM Mono",monospace; }
.step.active .step-num { background:var(--navy); color:#fff; }
.step.done .step-num { background:#16a34a; color:#fff; }

.card-title { font-size:14px; font-weight:600; color:var(--navy); margin-bottom:4px; }
.card-sub { font-size:12px; color:var(--muted); margin-bottom:20px; }

.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
.form-group label { display:block; font-size:12px; font-weight:500; color:var(--navy); margin-bottom:8px; }
.req { color:#dc2626; }

.file-drop { border:2px dashed var(--border); border-radius:10px; padding:32px 20px; text-align:center; position:relative; cursor:pointer; transition:border-color .15s, background .15s; background:#fafafa; }
.file-drop:hover { border-color:var(--navy); background:#f8faff; }
.file-drop input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.file-drop-label { font-size:13px; color:var(--muted); pointer-events:none; }
.file-drop-label strong { color:var(--navy); }
.file-name { display:block; margin-top:8px; font-size:12px; color:#16a34a; font-weight:500; font-family:"DM Mono",monospace; }

.btn-primary { background:var(--navy); color:#fff; border:none; padding:10px 24px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:"DM Sans",sans-serif; text-decoration:none; display:inline-block; }
.btn-primary:hover { opacity:.9; }
.btn-secondary { background:var(--surface-2); color:var(--navy); border:1px solid var(--border); padding:10px 24px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:"DM Sans",sans-serif; text-decoration:none; display:inline-block; }

.alert-banner { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:20px; }
.alert-banner.success { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.alert-banner.error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

.stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px; }
.stat-card { background:#fff; border:1px solid var(--border); border-radius:10px; padding:20px; }
.stat-num { font-size:32px; font-weight:300; color:var(--navy); font-family:"DM Mono",monospace; line-height:1; }
.stat-label { font-size:12px; color:var(--muted); margin-top:6px; }

/* Preview table */
.preview-table { width:100%; border-collapse:collapse; font-size:12px; }
.preview-table th { background:var(--surface-2); padding:8px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border); }
.preview-table td { padding:8px 12px; border-bottom:1px solid var(--border); color:var(--text); }
.preview-table tr:last-child td { border-bottom:none; }
.preview-wrap { overflow-x:auto; border:1px solid var(--border); border-radius:8px; margin-bottom:20px; }
</style>
<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"></head>
<body>
<?php include 'shared_sidebar.php'; ?>



<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Import Residents</div>
            <div class="topbar-date" id="topbar-date"></div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <span class="brgy-chip"><?= htmlspecialchars($brgy_info['name'] ?? 'Barangay') ?></span>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <h2>Import from Access Database</h2>
            <p>Upload CSV exports from your .accdb file to populate households and residents.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert-banner <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <?php if ($import_done): ?>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-num"><?= number_format($import_stats['families_inserted']) ?></div><div class="stat-label">Families Added</div></div>
            <div class="stat-card"><div class="stat-num"><?= number_format($import_stats['residents_inserted']) ?></div><div class="stat-label">Residents Added</div></div>
            <div class="stat-card"><div class="stat-num"><?= number_format($import_stats['families_skipped']) ?></div><div class="stat-label">Families Skipped</div></div>
            <div class="stat-card"><div class="stat-num"><?= number_format($import_stats['residents_skipped']) ?></div><div class="stat-label">Residents Skipped</div></div>
        </div>
        <div style="display:flex;gap:10px">
            <a href="index.php?section=households" class="btn btn-primary">View Households</a>
            <a href="import_residents.php" class="btn btn-secondary">Import Again</a>
        </div>

        <?php else: ?>

        <div class="steps">
            <div class="step <?= empty($preview_data) ? 'active' : 'done' ?>">
                <span class="step-num">01</span> Export CSVs from Access
            </div>
            <div class="step <?= empty($preview_data) ? '' : 'active' ?>">
                <span class="step-num">02</span> Upload and Preview
            </div>
            <div class="step">
                <span class="step-num">03</span> Confirm Import
            </div>
        </div>

        <div class="card">
            <div class="card-title">Upload CSV Files</div>
            <div class="card-sub">HHID is required; HHMEM is optional for household members</div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="preview">
                <div class="form-grid">
                    <div class="form-group">
                        <label>HHID.csv — Household Heads <span class="req">*</span></label>
                        <div class="file-drop" id="drop-hhid">
                            <input type="file" name="hhid_csv" accept=".csv,.txt,.tsv" required onchange="showFileName(this,'drop-hhid','name-hhid')">
                            <div class="file-drop-label">
                                <strong>Choose file</strong> or drag here
                                <span class="file-name" id="name-hhid"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>HHMEM.csv — Household Members <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                        <div class="file-drop" id="drop-hhmem">
                            <input type="file" name="hhmem_csv" accept=".csv,.txt,.tsv" onchange="showFileName(this,'drop-hhmem','name-hhmem')">
                            <div class="file-drop-label">
                                <strong>Choose file</strong> or drag here
                                <span class="file-name" id="name-hhmem"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:16px">
                    <button type="submit" class="btn btn-primary">Preview Import</button>
                </div>
            </form>
        </div>

        <?php if (!empty($preview_data)): ?>
        <div class="card">
            <div class="card-title">Preview (first 10 households)</div>
            <div class="card-sub">Review before confirming</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Head of Family</th>
                        <th>Area</th>
                        <th>Address</th>
                        <th>Members</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($preview_data as $row): ?>
                <tr>
                    <td style="font-weight:500"><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['zone']) ?></td>
                    <td style="color:var(--muted)"><?= htmlspecialchars($row['address'] ?: '—') ?></td>
                    <td>
                        <?php if (empty($row['members'])): ?>
                            <span class="tag tag-muted">Head only</span>
                        <?php else: ?>
                            <span class="tag tag-navy"><?= $row['member_count'] ?> member<?= $row['member_count'] != 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);display:flex;gap:8px;align-items:center">
                <form method="POST">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('This will import all households and members. Continue?')">
                        Confirm and Import All
                    </button>
                </form>
                <a href="import_residents.php" class="btn btn-secondary">Cancel</a>
                <span style="font-size:12px;color:var(--muted);margin-left:4px">Data stored in session — confirm within this browser tab</span>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var d = new Date();
    document.getElementById('topbar-date').textContent = d.toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
})();
function showFileName(input, dropId, nameId) {
    var drop = document.getElementById(dropId);
    var nameEl = document.getElementById(nameId);
    if (input.files && input.files[0]) {
        nameEl.textContent = input.files[0].name;
        drop.classList.add('has-file');
    } else {
        nameEl.textContent = '';
        drop.classList.remove('has-file');
    }
}
</script>

<script>
document.querySelectorAll('.menu-item').forEach(function(el){
    el.classList.remove('active');
    var href = el.getAttribute('href') || '';
    if(href === 'import_residents.php' || href.endsWith('/import_residents.php')) el.classList.add('active');
});
</script>
</body>
</html>