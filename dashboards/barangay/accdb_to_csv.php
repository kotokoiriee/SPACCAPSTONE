<?php
require_once '../../config/auth_guard.php';
require_role('barangay');

$accdb_path = __DIR__ . DIRECTORY_SEPARATOR . 'HH APPZ LARAM MAINFILE.accdb';
$accdb_exists = file_exists($accdb_path);

// ── Handle CSV download ───────────────────────────────────────────
if (isset($_GET['export']) && $accdb_exists) {
    $table = strtoupper(trim($_GET['export']));
    if (!in_array($table, ['HHID', 'HHMEM'])) die('Invalid table.');

    $dsn = 'odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $accdb_path . ';';
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->query("SELECT * FROM [$table]");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) die('No rows found in ' . $table . '.');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $table . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    } catch (Exception $e) {
        die('ODBC error: ' . $e->getMessage());
    }
}

// ── Check ODBC availability ───────────────────────────────────────
$odbc_ok = extension_loaded('pdo_odbc') || extension_loaded('odbc');
$conn_ok  = false;
$tables   = [];
$conn_err = '';

if ($accdb_exists && $odbc_ok) {
    $dsn = 'odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $accdb_path . ';';
    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn_ok = true;
        // List tables
        $r = $pdo->query("SELECT Name FROM MSysObjects WHERE Type=1 AND Flags=0");
        while ($row = $r->fetch(PDO::FETCH_NUM)) $tables[] = $row[0];
    } catch (Exception $e) {
        $conn_err = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Export from Access – SPAC</title>

<style>
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
    --blue:       #2563eb;
    --blue-l:     #eff6ff;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--white);
    display: flex;
    min-height: 100vh;
    color: var(--text);
    font-size: 14px;
    line-height: 1.5;
}
.sidebar {
    width: 232px; background: var(--white); min-height: 100vh;
    display: flex; flex-direction: column; position: fixed;
    top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100;
}
.sidebar-logo { padding: 24px 20px 20px; border-bottom: 1px solid var(--border); }
.sidebar-logo h1 { color: var(--navy); font-size: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
.sidebar-logo p  { color: var(--muted); font-size: 11px; margin-top: 2px; }
.sidebar-menu { padding: 12px 0; flex: 1; }
.menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }
.menu-item {
    display: flex; align-items: center; gap: 10px; padding: 8px 20px;
    color: var(--muted); font-size: 13px; font-weight: 400;
    transition: all 0.15s; cursor: pointer; background: none;
    border: none; width: 100%; text-align: left;
    font-family: 'DM Sans', sans-serif; text-decoration: none;
}
.menu-item:hover { color: var(--text); background: var(--surface-2); }
.menu-item.active { color: var(--navy); font-weight: 500; background: var(--navy-light); }
.menu-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--border); flex-shrink: 0; transition: background 0.15s; }
.menu-item:hover .menu-dot { background: var(--text); }
.menu-item.active .menu-dot { background: var(--navy); }
.sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
.sidebar-footer a { display: flex; align-items: center; gap: 10px; color: var(--muted); text-decoration: none; font-size: 13px; transition: color 0.15s; }
.sidebar-footer a:hover { color: var(--text); }

.main { margin-left: 232px; flex: 1; display: flex; flex-direction: column; }
.topbar {
    background: var(--white); padding: 0 28px; height: 56px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10;
}
.topbar-title { color: var(--navy); font-size: 14px; font-weight: 500; }
.topbar-date  { color: var(--muted); font-size: 12px; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.brgy-chip {
    background: var(--navy-light); color: var(--navy-mid);
    font-size: 11px; font-weight: 500; padding: 4px 10px;
    border-radius: 20px; border: 1px solid var(--border);
}
.content { padding: 24px 28px; }
.page-header { margin-bottom: 24px; }
.page-header h2 { font-size: 18px; font-weight: 500; color: var(--navy); }
.page-header p  { color: var(--muted); font-size: 13px; margin-top: 2px; }

.card { background: var(--white); border-radius: 8px; border: 1px solid var(--border); padding: 20px; margin-bottom: 14px; }
.card-title { font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 4px; }
.card-sub   { font-size: 13px; color: var(--text); margin-bottom: 16px; }

.status-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 6px; margin-bottom: 8px; font-size: 13px; }
.status-row.ok  { background: var(--green-l); color: var(--green); border: 1px solid #bbf7d0; }
.status-row.err { background: var(--red-l); color: var(--red); border: 1px solid #fecaca; }
.status-row.warn { background: var(--amber-l); color: var(--amber); border: 1px solid #fde68a; }

.dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

.btn {
    padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 500;
    cursor: pointer; border: 1px solid var(--border); transition: all 0.15s;
    font-family: 'DM Sans', sans-serif; display: inline-flex; align-items: center; gap: 6px;
    text-decoration: none;
}
.btn-primary   { background: var(--navy); color: #fff; border-color: var(--navy); }
.btn-primary:hover { background: var(--navy-mid); }
.btn-secondary { background: var(--white); color: var(--text); }
.btn-secondary:hover { background: var(--surface-2); }
.btn-green { background: var(--green); color: #fff; border-color: var(--green); }
.btn-green:hover { background: #15803d; }

.export-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 16px; }
.export-card {
    border: 1px solid var(--border); border-radius: 8px; padding: 18px 20px;
    display: flex; flex-direction: column; gap: 10px;
}
.export-card h3 { font-size: 14px; font-weight: 600; color: var(--navy); }
.export-card p  { font-size: 12px; color: var(--muted); }

.fix-box {
    background: var(--surface-2); border: 1px solid var(--border); border-radius: 6px;
    padding: 14px 16px; margin-top: 14px;
}
.fix-box p { font-size: 12px; color: var(--text); margin-bottom: 8px; font-weight: 500; }
.fix-box ol { margin-left: 16px; }
.fix-box ol li { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
code {
    font-family: 'DM Mono', monospace; font-size: 12px;
    background: var(--border); padding: 1px 5px; border-radius: 3px; color: var(--navy);
}
</style>
<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"></head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <h1>SPAC</h1>
        <p>Barangay Portal</p>
    </div>
    <div class="sidebar-menu">
        <div class="menu-section">Overview</div>
        <a class="menu-item" href="index.php"><span class="menu-dot"></span> Dashboard</a>
        <a class="menu-item" href="index.php?section=profile"><span class="menu-dot"></span> Barangay Profile</a>
        <div class="menu-section">People</div>
        <a class="menu-item" href="index.php?section=officials"><span class="menu-dot"></span> Officials &amp; Staff</a>
        <a class="menu-item" href="index.php?section=zones"><span class="menu-dot"></span> Zone Leaders</a>
        <a class="menu-item" href="index.php?section=households"><span class="menu-dot"></span> Households</a>
        <a class="menu-item" href="index.php?section=residents"><span class="menu-dot"></span> Residents</a>
        <div class="menu-section">Services</div>
        <a class="menu-item active" href="accdb_to_csv.php"><span class="menu-dot"></span> Export from Access</a>
        <a class="menu-item" href="manage_areas.php"><span class="menu-dot"></span> Manage Areas</a>
        <a class="menu-item" href="import_residents.php"><span class="menu-dot"></span> Import Residents</a>
        <a class="menu-item" href="index.php?section=ayuda"><span class="menu-dot"></span> Ayuda / Assistance</a>
        <a class="menu-item" href="index.php?section=qr"><span class="menu-dot"></span> Scan QR / History</a>
        <div class="menu-section">Management</div>
        <a class="menu-item" href="index.php?section=alerts"><span class="menu-dot"></span> Alerts</a>
    </div>
    <div class="sidebar-footer">
        <a href="../../logout.php"><span class="menu-dot"></span> Logout</a>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Export from Access Database</div>
            <div class="topbar-date" id="topbar-date"></div>
        </div>
        <div class="topbar-right">
            <span class="brgy-chip">Barangay Laram</span>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <h2>Export from Access Database</h2>
            <p>This tool reads the .accdb file on the server and lets you download the tables as CSV — no Microsoft Access needed.</p>
        </div>

        <!-- Status checks -->
        <div class="card">
            <div class="card-title">System Status</div>
            <div class="card-sub">Checking if everything is ready</div>

            <!-- File check -->
            <div class="status-row <?= $accdb_exists ? 'ok' : 'err' ?>">
                <div class="dot"></div>
                <?php if ($accdb_exists): ?>
                    .accdb file found on server — <strong>HH APPZ LARAM MAINFILE.accdb</strong>
                <?php else: ?>
                    .accdb file not found at <code><?= htmlspecialchars($accdb_path) ?></code>
                <?php endif; ?>
            </div>

            <!-- ODBC check -->
            <div class="status-row <?= $odbc_ok ? 'ok' : 'err' ?>">
                <div class="dot"></div>
                <?php if ($odbc_ok): ?>
                    PDO ODBC extension is loaded
                <?php else: ?>
                    PDO ODBC extension not loaded — see fix below
                <?php endif; ?>
            </div>

            <!-- Connection check -->
            <?php if ($odbc_ok && $accdb_exists): ?>
            <div class="status-row <?= $conn_ok ? 'ok' : 'err' ?>">
                <div class="dot"></div>
                <?php if ($conn_ok): ?>
                    Connected to .accdb successfully — <?= count($tables) ?> table(s) found
                <?php else: ?>
                    Cannot connect: <?= htmlspecialchars($conn_err) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Fix instructions if ODBC missing -->
            <?php if (!$odbc_ok): ?>
            <div class="fix-box">
                <p>How to enable PDO ODBC in XAMPP:</p>
                <ol>
                    <li>Open <code>C:\xampp\php\php.ini</code> in a text editor</li>
                    <li>Find the line <code>;extension=pdo_odbc</code> and remove the semicolon so it reads <code>extension=pdo_odbc</code></li>
                    <li>Also find <code>;extension=odbc</code> and remove the semicolon</li>
                    <li>Save the file and restart Apache in XAMPP Control Panel</li>
                    <li>Refresh this page</li>
                </ol>
            </div>
            <?php endif; ?>

            <!-- Fix instructions if connection failed -->
            <?php if ($odbc_ok && $accdb_exists && !$conn_ok): ?>
            <div class="fix-box">
                <p>The ODBC driver couldn't connect. This usually means the 32/64-bit Access driver is missing.</p>
                <ol>
                    <li>Download <strong>Microsoft Access Database Engine 2016 Redistributable</strong> (64-bit) from Microsoft's website</li>
                    <li>Install it and restart Apache in XAMPP</li>
                    <li>Refresh this page</li>
                </ol>
                <p style="margin-top:10px">Or use the alternative below — export via MDB Viewer Plus (free, no install needed).</p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($conn_ok): ?>
        <!-- Export buttons -->
        <div class="card">
            <div class="card-title">Download Tables as CSV</div>
            <div class="card-sub">Click to download — then upload them on the Import Residents page</div>
            <div class="export-grid">
                <div class="export-card">
                    <h3>HHID — Household Heads</h3>
                    <p>Required. Contains the head of each household with their name, zone, address, and contact info.</p>
                    <?php if (in_array('HHID', $tables)): ?>
                        <a href="?export=HHID" class="btn btn-green">Download HHID.csv</a>
                    <?php else: ?>
                        <span style="font-size:12px;color:var(--red)">HHID table not found in this .accdb</span>
                    <?php endif; ?>
                </div>
                <div class="export-card">
                    <h3>HHMEM — Household Members</h3>
                    <p>Optional. Links family members to their household head via the HHID/HHIDID column.</p>
                    <?php if (in_array('HHMEM', $tables)): ?>
                        <a href="?export=HHMEM" class="btn btn-secondary">Download HHMEM.csv</a>
                    <?php else: ?>
                        <span style="font-size:12px;color:var(--muted)">HHMEM table not found (optional)</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:10px;align-items:center">
                <a href="import_residents.php" class="btn btn-primary">Go to Import Residents</a>
                <span style="font-size:12px;color:var(--muted)">After downloading, upload the CSVs on the Import page</span>
            </div>
        </div>

        <?php if (!empty($tables)): ?>
        <div class="card">
            <div class="card-title">All Tables in .accdb</div>
            <div class="card-sub" style="margin-bottom:10px">For reference — all tables detected in your Access file</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px">
                <?php foreach ($tables as $t): ?>
                    <span style="font-family:'DM Mono',monospace;font-size:12px;background:var(--surface-2);padding:3px 10px;border-radius:4px;border:1px solid var(--border);color:var(--navy)"><?= htmlspecialchars($t) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Alternative if ODBC not working -->
        <div class="card">
            <div class="card-title">Alternative — MDB Viewer Plus</div>
            <div class="card-sub">If the ODBC driver can't be set up, use this free tool instead</div>
            <ol style="margin-left:16px;margin-top:8px">
                <li style="font-size:13px;color:var(--text);margin-bottom:6px">Download <strong>MDB Viewer Plus</strong> — search "MDB Viewer Plus download" (it's free, no install needed)</li>
                <li style="font-size:13px;color:var(--text);margin-bottom:6px">Open the file: <code>C:\xampp\htdocs\SPAC\dashboards\barangay\HH APPZ LARAM MAINFILE.accdb</code></li>
                <li style="font-size:13px;color:var(--text);margin-bottom:6px">Click the <strong>HHID</strong> table in the left panel</li>
                <li style="font-size:13px;color:var(--text);margin-bottom:6px">Go to <strong>File → Export → CSV</strong> and save as <code>HHID.csv</code></li>
                <li style="font-size:13px;color:var(--text);margin-bottom:6px">Repeat for the <strong>HHMEM</strong> table, save as <code>HHMEM.csv</code></li>
                <li style="font-size:13px;color:var(--text)">Go to <a href="import_residents.php" style="color:var(--navy);font-weight:500">Import Residents</a> and upload both files</li>
            </ol>
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
</body>
</html>