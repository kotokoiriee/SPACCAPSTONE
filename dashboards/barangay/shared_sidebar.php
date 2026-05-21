<div class="sidebar">
<div class="sidebar-logo">
    <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
            <h1>SPAC</h1>
            <p>Barangay Portal</p>
        </div>
        <?php if (!empty($brgy_info['logo'])): ?>
            <img src="<?= htmlspecialchars($brgy_info['logo']) ?>" alt="Barangay Logo"
                 style="width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;object-fit:cover;object-position:center;border:2px solid var(--border);flex-shrink:0">
        <?php else: ?>
            <div style="width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;flex-shrink:0;border:2px solid var(--border)">
                <?= strtoupper(substr($brgy_info['name'] ?? 'B', 0, 1)) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
    <div class="sidebar-menu">
        <div class="menu-section">Overview</div>
        <a class="menu-item" href="index.php"><span class="menu-dot"></span> Dashboard</a>
        <a class="menu-item" href="index.php?section=profile"><span class="menu-dot"></span> Barangay Profile</a>
        <div class="menu-section">People</div>
        <a class="menu-item" href="index.php?section=officials"><span class="menu-dot"></span> Officials &amp; Staff <span class="menu-badge"><?= $total_officials ?></span></a>
        <a class="menu-item" href="index.php?section=zones"><span class="menu-dot"></span> <?= htmlspecialchars($area_label) ?> Leaders <span class="menu-badge"><?= $total_zones ?></span></a>
        <a class="menu-item" href="index.php?section=households"><span class="menu-dot"></span> Households <span class="menu-badge"><?= number_format($total_families) ?></span></a>
        <a class="menu-item" href="index.php?section=residents"><span class="menu-dot"></span> Residents <span class="menu-badge"><?= number_format($total_residents) ?></span></a>
        <div class="menu-section">Reports</div>
        <a class="menu-item" href="statistics.php"><span class="menu-dot"></span> Statistics</a>
        <div class="menu-section">Services</div>
        <a class="menu-item" href="manage_areas.php"><span class="menu-dot"></span> Manage Areas</a>
        <a class="menu-item" href="import_residents.php"><span class="menu-dot"></span> Import Residents</a>
        <a class="menu-item" href="index.php?section=ayuda"><span class="menu-dot"></span> Ayuda / Assistance</a>
        <a class="menu-item" href="index.php?section=qr"><span class="menu-dot"></span> Scan QR / History</a>
        <div class="menu-section">Management</div>
        <a class="menu-item" href="index.php?section=alerts">
            <span class="menu-dot"></span> Alerts
            <?php if ($active_alerts > 0): ?>
            <span class="menu-badge alert"><?= $active_alerts ?></span>
            <?php endif; ?>
        </a>
    </div>
    <div class="sidebar-footer">
        <a href="../../logout.php"><span class="menu-dot"></span> Logout</a>
    </div>
</div>
