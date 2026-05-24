<?php
require_once __DIR__ . '/config/db.php';

$success = '';
$error   = '';

// Fetch barangay list
$barangays = $conn->query("SELECT barangay_id, name FROM barangays ORDER BY name ASC");
$barangay_list = [];
while ($row = $barangays->fetch_assoc()) {
    $barangay_list[] = $row;
}

// Fetch zones for selected barangay (via AJAX)
if (isset($_GET['get_zones'])) {
    $bid = (int)$_GET['barangay_id'];
    $zones = $conn->query("SELECT DISTINCT zone_number FROM zone_leaders WHERE barangay_id = $bid AND is_active = 1 ORDER BY zone_number ASC");
    $result = [];
    while ($row = $zones->fetch_assoc()) $result[] = $row;
    echo json_encode($result);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barangay_id    = (int)$_POST['barangay_id'];
    $full_name      = $conn->real_escape_string(trim($_POST['full_name']));
    $address        = $conn->real_escape_string(trim($_POST['address']));
    $zone_number    = (int)$_POST['zone_number'];
    $contact_number = $conn->real_escape_string(trim($_POST['contact_number']));
    $assistance_type  = $conn->real_escape_string(trim($_POST['assistance_type']));
    $assistance_month = $conn->real_escape_string(trim($_POST['assistance_month'] ?? ''));
    $description    = $conn->real_escape_string(trim($_POST['description'] ?? ''));

    if (!$barangay_id || !$full_name || !$address || !$zone_number || !$contact_number || !$assistance_type) {
        $error = "Please fill in all required fields.";
    } else {
        $conn->query("INSERT INTO guest_reports (barangay_id, full_name, address, zone_number, contact_number, assistance_type, assistance_month, description)
                      VALUES ($barangay_id, '$full_name', '$address', $zone_number, '$contact_number', '$assistance_type', '$assistance_month', '$description')");
        $success = "Your report has been submitted successfully! The barangay staff will review it shortly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPAC – Report Unreceived Assistance</title>
    
    <style>
        :root {
            --white:      #ffffff;
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--surface-2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .wrapper { width: 100%; max-width: 520px; }
        .logo-area { text-align: center; margin-bottom: 24px; }
        .logo-area h1 {
            color: var(--navy); font-size: 32px; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase;
            font-family: 'DM Mono', monospace;
        }
        .logo-area p { color: var(--muted); font-size: 12px; margin-top: 4px; }
        .card {
            background: var(--white); border-radius: 12px;
            padding: 32px; border: 1px solid var(--border);
        }
        .card-header { margin-bottom: 24px; }
        .card-header h2 { color: var(--navy); font-size: 16px; font-weight: 500; }
        .card-header p { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; color: var(--text); font-size: 12px;
            font-weight: 500; margin-bottom: 5px;
        }
        .req { color: var(--red); }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 9px 12px;
            background: var(--white); border: 1px solid var(--border);
            border-radius: 6px; color: var(--text); font-size: 13px;
            outline: none; transition: border-color 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { border-color: var(--navy); }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .full { grid-column: 1 / -1; }
        .btn-primary {
            width: 100%; padding: 11px; background: var(--navy); color: #fff;
            border: none; border-radius: 6px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: background 0.15s;
            font-family: 'DM Sans', sans-serif; margin-top: 8px;
        }
        .btn-primary:hover { background: var(--navy-mid); }
        .btn-back {
            width: 100%; padding: 10px; background: transparent; color: var(--muted);
            border: 1px solid var(--border); border-radius: 6px; font-size: 13px;
            cursor: pointer; margin-top: 8px; transition: all 0.15s;
            font-family: 'DM Sans', sans-serif; text-decoration: none;
            display: block; text-align: center;
        }
        .btn-back:hover { border-color: var(--navy); color: var(--navy); background: var(--navy-light); }
        .alert {
            padding: 12px 16px; border-radius: 6px; font-size: 13px;
            margin-bottom: 20px; border: 1px solid transparent;
        }
        .alert-success { background: var(--green-l); color: var(--green); border-color: #bbf7d0; }
        .alert-error   { background: var(--red-l);   color: var(--red);   border-color: #fecaca; }
        .divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }
        .hint { color: var(--muted); font-size: 11px; margin-top: 4px; }
        .success-screen { text-align: center; padding: 20px 0; }
        .success-icon { font-size: 48px; margin-bottom: 12px; }
        .success-screen h3 { color: var(--green); font-size: 18px; font-weight: 500; margin-bottom: 8px; }
        .success-screen p { color: var(--muted); font-size: 13px; margin-bottom: 24px; line-height: 1.6; }
    </style>
<link rel="stylesheet" href="/SPAC/assets/fonts/fonts.css"></head>
<body>
<div class="wrapper">

    <div class="logo-area">
        <h1>SPAC</h1>
        <p>San Pedro Assistance Card System</p>
    </div>

    <div class="card">

        <?php if ($success): ?>
        <!-- SUCCESS SCREEN -->
        <div class="success-screen">
            <div class="success-icon">✅</div>
            <h3>Report Submitted!</h3>
            <p><?= htmlspecialchars($success) ?></p>
            <a href="guest_report.php" class="btn-primary" style="display:block;text-decoration:none;text-align:center;">Submit Another Report</a>
            <a href="index.php" class="btn-back">← Back to Login</a>
        </div>

        <?php else: ?>
        <!-- REPORT FORM -->
        <div class="card-header">
            <h2>Report Unreceived Assistance</h2>
            <p>Fill out this form if you have not received an assistance you are entitled to. Barangay staff will review your report.</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">

                <!-- Barangay -->
                <div class="form-group full">
                    <label>Barangay <span class="req">*</span></label>
                    <select name="barangay_id" id="barangay_id" onchange="loadZones(this.value)" required>
                        <option value="">— Select your barangay —</option>
                        <?php foreach ($barangay_list as $b): ?>
                        <option value="<?= $b['barangay_id'] ?>"
                            <?= isset($_POST['barangay_id']) && $_POST['barangay_id'] == $b['barangay_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Full Name -->
                <div class="form-group full">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" placeholder="Enter your full name" required
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>

                <!-- Contact Number -->
                <div class="form-group">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" required
                           value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
                </div>

                <!-- Zone -->
                <div class="form-group">
                    <label>Zone Number <span class="req">*</span></label>
                    <select name="zone_number" id="zone_select" required>
                        <option value="">— Select zone —</option>
                    </select>
                    <p class="hint">Select barangay first</p>
                </div>

                <!-- Address -->
                <div class="form-group full">
                    <label>Address <span class="req">*</span></label>
                    <input type="text" name="address" placeholder="House no., Street, Sitio/Compound" required
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>

                <!-- Assistance Type -->
                <div class="form-group full">
                    <label>Type of Assistance Not Received <span class="req">*</span></label>
                    <select name="assistance_type" required>
                        <option value="">— Select type —</option>
                        <option value="Food Assistance"      <?= (($_POST['assistance_type'] ?? '') === 'Food Assistance')      ? 'selected' : '' ?>>Food Assistance</option>
                        <option value="Medical / Health"     <?= (($_POST['assistance_type'] ?? '') === 'Medical / Health')     ? 'selected' : '' ?>>Medical / Health</option>
                        <option value="Financial Aid"        <?= (($_POST['assistance_type'] ?? '') === 'Financial Aid')        ? 'selected' : '' ?>>Financial Aid</option>
                        <option value="Livelihood"           <?= (($_POST['assistance_type'] ?? '') === 'Livelihood')           ? 'selected' : '' ?>>Livelihood</option>
                        <option value="Educational"          <?= (($_POST['assistance_type'] ?? '') === 'Educational')          ? 'selected' : '' ?>>Educational</option>
                        <option value="Disaster Relief"      <?= (($_POST['assistance_type'] ?? '') === 'Disaster Relief')      ? 'selected' : '' ?>>Disaster Relief</option>
                        <option value="Senior Citizen"       <?= (($_POST['assistance_type'] ?? '') === 'Senior Citizen')       ? 'selected' : '' ?>>Senior Citizen Benefit</option>
                        <option value="PWD"                  <?= (($_POST['assistance_type'] ?? '') === 'PWD')                  ? 'selected' : '' ?>>PWD Assistance</option>
                        <option value="Other"                <?= (($_POST['assistance_type'] ?? '') === 'Other')                ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group full"><label>Month of Assistance Not Received <span class="req">*</span></label><select name="assistance_month" required><option value="">— Select month —</option><option value="2026-01">January 2026</option><option value="2026-02">February 2026</option><option value="2026-03">March 2026</option><option value="2026-04">April 2026</option><option value="2026-05">May 2026</option><option value="2026-06">June 2026</option><option value="2026-07">July 2026</option><option value="2026-08">August 2026</option><option value="2026-09">September 2026</option><option value="2026-10">October 2026</option><option value="2026-11">November 2026</option><option value="2026-12">December 2026</option></select></div>
                <!-- Description -->
                <div class="form-group full">
                    <label>Details / Description <span style="color:var(--muted);font-weight:400">(optional)</span></label>
                    <textarea name="description" placeholder="Describe the assistance you did not receive, when it was distributed, etc."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

            </div>

            <hr class="divider">
            <button type="submit" class="btn-primary">Submit Report</button>
        </form>

        <a href="index.php" class="btn-back">← Back to Login</a>
        <?php endif; ?>

    </div>

    <div style="text-align:center;color:var(--muted);font-size:12px;margin-top:16px;">
        City Government of San Pedro, Laguna
    </div>
</div>

<script>
function loadZones(barangayId) {
    var select = document.getElementById('zone_select');
    select.innerHTML = '<option value="">Loading...</option>';
    if (!barangayId) {
        select.innerHTML = '<option value="">— Select zone —</option>';
        return;
    }
    fetch('guest_report.php?get_zones=1&barangay_id=' + barangayId)
        .then(function(r) { return r.json(); })
        .then(function(zones) {
            select.innerHTML = '<option value="">— Select zone —</option>';
            if (!zones.length) {
                select.innerHTML += '<option value="" disabled>No zones found</option>';
                return;
            }
            zones.forEach(function(z) {
                select.innerHTML += '<option value="' + z.zone_number + '">Zone ' + z.zone_number + '</option>';
            });
        })
        .catch(function() {
            select.innerHTML = '<option value="">Error loading zones</option>';
        });
}
</script>
</body>
</html>
