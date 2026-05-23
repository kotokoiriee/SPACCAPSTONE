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

// Sidebar counts
$active_alerts=0;$total_residents=0;$total_families=0;$total_zone_leaders=0;$total_officials=0;
$r=$conn->query("SELECT (SELECT COUNT(*) FROM alerts WHERE barangay_id=$bid AND resolved=0) as alerts,(SELECT COUNT(*) FROM residents WHERE barangay_id=$bid AND is_active=1) as residents,(SELECT COUNT(*) FROM families WHERE barangay_id=$bid AND is_active=1) as families,(SELECT COUNT(*) FROM zone_leaders WHERE barangay_id=$bid AND is_active=1) as zone_leaders,(SELECT COUNT(*) FROM barangay_officials WHERE barangay_id=$bid AND is_active=1) as officials");
if($r){$_c=$r->fetch_assoc();$active_alerts=$_c['alerts'];$total_residents=$_c['residents'];$total_families=$_c['families'];$total_zone_leaders=$_c['zone_leaders'];$total_officials=$_c['officials'];}
$total_zones=$total_zone_leaders;

// ── Totals ────────────────────────────────────────────────────────
$total_residents = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id = $bid AND is_active = 1");
if ($r) $total_residents = $r->fetch_assoc()['c'];

$total_households = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM families WHERE barangay_id = $bid");
if ($r) $total_households = $r->fetch_assoc()['c'];

$total_heads = 0;
$r = $conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id = $bid AND is_active = 1 AND relationship = 'Head'");
if ($r) $total_heads = $r->fetch_assoc()['c'];

// ── Age brackets ─────────────────────────────────────────────────
$age_brackets = [
    ['00–04', 0, 4],
    ['05–09', 5, 9],
    ['10–14', 10, 14],
    ['15–19', 15, 19],
    ['20–24', 20, 24],
    ['25–29', 25, 29],
    ['30–34', 30, 34],
    ['35–39', 35, 39],
    ['40–44', 40, 44],
    ['45–49', 45, 49],
    ['50–54', 50, 54],
    ['55–59', 55, 59],
    ['60–64', 60, 64],
    ['65–69', 65, 69],
    ['70–74', 70, 74],
    ['75–79', 75, 79],
    ['80+',   80, 999],
];

$age_data = [];
$age_total_m = 0;
$age_total_f = 0;

foreach ($age_brackets as [$label, $min, $max]) {
    $max_sql = $max === 999 ? '999' : $max;
    $q = "SELECT
        SUM(CASE WHEN LOWER(gender) LIKE 'male%' OR LOWER(gender)='m' THEN 1 ELSE 0 END) as male,
        SUM(CASE WHEN LOWER(gender) LIKE 'female%' OR LOWER(gender)='f' THEN 1 ELSE 0 END) as female
        FROM residents
        WHERE barangay_id = $bid AND is_active = 1
        AND birth_date IS NOT NULL
        AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN $min AND $max_sql";
    $r = $conn->query($q);
    $row = $r ? $r->fetch_assoc() : ['male' => 0, 'female' => 0];
    $m = (int)($row['male'] ?? 0);
    $f = (int)($row['female'] ?? 0);
    $age_data[] = ['label' => $label, 'male' => $m, 'female' => $f, 'total' => $m + $f];
    $age_total_m += $m;
    $age_total_f += $f;
}
$age_total = $age_total_m + $age_total_f;

// ── Sector counts ─────────────────────────────────────────────────
function sector_count($conn, $bid, $where_extra = '') {
    $q = "SELECT
        SUM(CASE WHEN LOWER(gender) LIKE 'male%' OR LOWER(gender)='m' THEN 1 ELSE 0 END) as male,
        SUM(CASE WHEN LOWER(gender) LIKE 'female%' OR LOWER(gender)='f' THEN 1 ELSE 0 END) as female
        FROM residents
        WHERE barangay_id = $bid AND is_active = 1 $where_extra";
    $r = $conn->query($q);
    $row = $r ? $r->fetch_assoc() : ['male' => 0, 'female' => 0];
    return [(int)($row['male'] ?? 0), (int)($row['female'] ?? 0)];
}

// Sector counts — using only available columns (birth_date, gender, relationship)
[$all_m, $all_f] = sector_count($conn, $bid, "");
[$sc_m,  $sc_f]  = sector_count($conn, $bid, "AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 60 AND birth_date IS NOT NULL");
[$osc_m, $osc_f] = sector_count($conn, $bid, "AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 6 AND 14 AND birth_date IS NOT NULL");
[$osy_m, $osy_f] = sector_count($conn, $bid, "AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 15 AND 24 AND birth_date IS NOT NULL");

// No citizenship/sector columns — show total as Filipino (all residents), others as 0
$fil_m = $all_m; $fil_f = $all_f;
$for_m = $for_f = $pwd_m = $pwd_f = $ofw_m = $ofw_f = $sp_m = $sp_f = $ip_m = $ip_f = 0;

$sectors = [
    ['Filipino',         $fil_m, $fil_f],
    ['Foreigner',        $for_m, $for_f],
    ['Senior Citizens',  $sc_m,  $sc_f],
    ['PWDs',             $pwd_m, $pwd_f],
    ['OFWs',             $ofw_m, $ofw_f],
    ['Solo Parents',     $sp_m,  $sp_f],
    ['Indigenous (IPs)', $ip_m,  $ip_f],
    ['OSC (6–14 yrs)',   $osc_m, $osc_f],
    ['OSY (15–24 yrs)',  $osy_m, $osy_f],
];

// ── Zone breakdown ────────────────────────────────────────────────
$zone_data = [];
$r = $conn->query("SELECT zone_number, zone_name, COUNT(*) as cnt FROM residents WHERE barangay_id = $bid AND is_active = 1 GROUP BY zone_number, zone_name ORDER BY zone_number, zone_name");
if ($r) while ($row = $r->fetch_assoc()) $zone_data[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistics – SPAC</title>

<?php include __DIR__.'/shared_style.css.php'; ?>
<style>
.summary-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; margin-bottom:24px; }
.sum-card { background:var(--white); border:1px solid var(--border); border-radius:8px; padding:18px 20px; }
.sum-num { font-size:32px; font-weight:300; color:var(--navy); font-family:'DM Mono',monospace; line-height:1; }
.sum-label { color:var(--muted); font-size:12px; margin-top:6px; }
.two-col { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
.stat-table { width:100%; border-collapse:collapse; font-size:13px; }
.stat-table th { text-align:left; padding:8px 12px; color:var(--muted); font-size:11px; font-weight:500; letter-spacing:0.05em; text-transform:uppercase; border-bottom:1px solid var(--border); background:var(--surface-2); }
.stat-table th.num, .stat-table td.num { text-align:right; }
.stat-table td { padding:8px 12px; border-bottom:1px solid var(--border); color:var(--text); }
.stat-table tr:last-child td { border-bottom:none; font-weight:600; }
.stat-table tr:hover td { background:var(--surface-2); }
.card-badge { font-size:11px; color:var(--muted); background:var(--surface-2); padding:2px 8px; border-radius:20px; font-family:'DM Mono',monospace; }
.full-col { grid-column:1/-1; }
.zone-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:10px; padding:4px 0; }
.zone-card { background:var(--surface-2); border:1px solid var(--border); border-radius:8px; padding:14px 16px; }
.zone-card .zone-num { font-size:22px; font-weight:300; color:var(--navy); font-family:"DM Mono",monospace; line-height:1; }
.zone-card .zone-label { font-size:11px; color:var(--muted); margin-top:5px; }
@media(max-width:900px){ .two-col { grid-template-columns:1fr; } }
@media print { .sidebar,.topbar { display:none!important; } .main { margin-left:0!important; } body { background:#fff; } }
</style>

<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"></head>
<body>

<?php include 'shared_sidebar.php'; ?>



<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Statistics</div>
            <div class="topbar-date" id="topbar-date"></div>
        </div>
        <div class="topbar-right">
            <button onclick="window.print()" style="padding:6px 14px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;background:var(--white);color:var(--navy);font-family:'DM Sans',sans-serif">Print / Export PDF</button>
            <span class="brgy-chip"><?= htmlspecialchars($brgy_info['name'] ?? 'Barangay') ?></span>
            <button class="avatar-btn" title="My Profile" style="cursor:default"><?= strtoupper(substr($_SESSION['full_name'] ?? 'B', 0, 1)) ?></button>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div>
                <h2>Household Monitoring Statistical Report</h2>
                <p>Brgy. <?= htmlspecialchars($brgy_info['name'] ?? '') ?> &mdash; generated <?= date('F j, Y') ?></p>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="sum-card">
                <div class="sum-num"><?= number_format($total_households) ?></div>
                <div class="sum-label">Total Household Heads</div>
            </div>
            <div class="sum-card">
                <div class="sum-num"><?= number_format($total_residents) ?></div>
                <div class="sum-label">Total Family Members incl. HH Head</div>
            </div>
            <div class="sum-card">
                <div class="sum-num"><?= number_format($age_total) ?></div>
                <div class="sum-label">Residents with Birth Date on Record</div>
            </div>
        </div>

        <div class="two-col">
            <!-- Age Bracket Table -->
            <div class="card" style="margin-bottom:0">
                <div class="card-header">
                    <div class="card-title">Population by Age Bracket</div>
                    <span class="card-badge"><?= number_format($age_total) ?> total</span>
                </div>
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Age Group</th>
                            <th class="num">Male</th>
                            <th class="num">Female</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($age_data as $row): ?>
                        <tr>
                            <td><?= $row['label'] ?></td>
                            <td class="num"><?= number_format($row['male']) ?></td>
                            <td class="num"><?= number_format($row['female']) ?></td>
                            <td class="num"><?= number_format($row['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php $unknown = $total_residents - $age_total; if ($unknown > 0): ?>
                        <tr style="color:var(--muted)">
                            <td>No birthdate on record</td>
                            <td class="num">—</td>
                            <td class="num">—</td>
                            <td class="num"><?= number_format($unknown) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td>Total</td>
                            <td class="num"><?= number_format($age_total_m) ?></td>
                            <td class="num"><?= number_format($age_total_f) ?></td>
                            <td class="num"><?= number_format($total_residents) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Sector Table -->
            <div class="card" style="margin-bottom:0">
                <div class="card-header">
                    <div class="card-title">Population by Sector</div>
                    <span class="card-badge">RBI Form C</span>
                </div>
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Sector</th>
                            <th class="num">Male</th>
                            <th class="num">Female</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sectors as [$label, $m, $f]): ?>
                        <tr>
                            <td><?= $label ?></td>
                            <td class="num"><?= number_format($m) ?></td>
                            <td class="num"><?= number_format($f) ?></td>
                            <td class="num"><?= number_format($m + $f) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
                    <div style="font-size:12px;color:var(--muted)">Total Household Heads: <strong style="color:var(--navy)"><?= number_format($total_households) ?></strong></div>
                    <div style="font-size:12px;color:var(--muted);margin-top:3px">Total Family Members incl. HH Head: <strong style="color:var(--navy)"><?= number_format($total_residents) ?></strong></div>
                </div>
            </div>
        </div>

        <!-- Zone Breakdown -->
        <?php if (!empty($zone_data)): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Residents by Zone</div>
                <span class="card-badge"><?= count($zone_data) ?> zones</span>
            </div>
            <div class="zone-grid">
                <?php foreach ($zone_data as $z): ?>
                <div class="zone-card">
                    <div class="zone-num"><?= number_format($z['cnt']) ?></div>
                    <div class="zone-label"><?= htmlspecialchars($z['zone_name'] ?: ($z['zone_number'] ? 'Zone ' . $z['zone_number'] : 'Unassigned')) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
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
</script>

<script>
document.querySelectorAll('.menu-item').forEach(function(el){
    el.classList.remove('active');
    var href = el.getAttribute('href') || '';
    if(href === 'statistics.php' || href.endsWith('/statistics.php')) el.classList.add('active');
});
</script>
</body>
</html>