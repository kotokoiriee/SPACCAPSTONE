with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

new_sections = """
        <!-- STATISTICS SECTION -->
        <div id="section-statistics" class="section">
            <div class="page-header">
                <h2>Household Monitoring Statistical Report</h2>
                <p>Generated <?= date('F j, Y') ?></p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:24px">
                <div class="stat-card"><div class="stat-num"><?= number_format($total_families) ?></div><div class="stat-label">Total Household Heads</div></div>
                <div class="stat-card"><div class="stat-num"><?= number_format($total_residents) ?></div><div class="stat-label">Total Family Members</div></div>
            </div>
            <?php if (!empty($zones_residents)): ?>
            <div class="card" style="margin-bottom:14px">
                <div class="card-header"><div class="card-title">Residents by <?= htmlspecialchars($area_label) ?></div></div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px">
                    <?php foreach ($zones_residents as $zn => $cnt): ?>
                    <div class="stat-card"><div class="stat-num"><?= number_format($cnt) ?></div><div class="stat-label"><?= htmlspecialchars($area_label) ?> <?= $zn ?></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="card">
                    <div class="card-header"><div class="card-title">Population by Age Bracket</div></div>
                    <?php
                    $stat_brackets = [["00-04",0,4],["05-09",5,9],["10-14",10,14],["15-19",15,19],["20-24",20,24],["25-29",25,29],["30-34",30,34],["35-39",35,39],["40-44",40,44],["45-49",45,49],["50-54",50,54],["55-59",55,59],["60-64",60,64],["65+",65,999]];
                    ?>
                    <table class="data-table">
                        <thead><tr><th>Age Group</th><th style="text-align:right">Count</th></tr></thead>
                        <tbody>
                        <?php foreach ($stat_brackets as [$lbl,$mn,$mx]):
                            $mxs = $mx===999?"999":$mx;
                            $ar = $conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id=$bid AND is_active=1 AND birth_date IS NOT NULL AND TIMESTAMPDIFF(YEAR,birth_date,CURDATE()) BETWEEN $mn AND $mxs");
                            $ca = $ar?(int)$ar->fetch_assoc()["c"]:0;
                        ?>
                        <tr><td><?= $lbl ?></td><td style="text-align:right;font-family:monospace"><?= number_format($ca) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card">
                    <div class="card-header"><div class="card-title">Population by Sector</div></div>
                    <?php
                    $sc_r=$conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id=$bid AND is_active=1 AND birth_date IS NOT NULL AND TIMESTAMPDIFF(YEAR,birth_date,CURDATE())>=60");
                    $sc_c=$sc_r?(int)$sc_r->fetch_assoc()["c"]:0;
                    $yu_r=$conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id=$bid AND is_active=1 AND birth_date IS NOT NULL AND TIMESTAMPDIFF(YEAR,birth_date,CURDATE()) BETWEEN 15 AND 24");
                    $yu_c=$yu_r?(int)$yu_r->fetch_assoc()["c"]:0;
                    $ch_r=$conn->query("SELECT COUNT(*) as c FROM residents WHERE barangay_id=$bid AND is_active=1 AND birth_date IS NOT NULL AND TIMESTAMPDIFF(YEAR,birth_date,CURDATE())<18");
                    $ch_c=$ch_r?(int)$ch_r->fetch_assoc()["c"]:0;
                    ?>
                    <table class="data-table">
                        <thead><tr><th>Sector</th><th style="text-align:right">Count</th></tr></thead>
                        <tbody>
                        <tr><td>Senior Citizens (60+)</td><td style="text-align:right;font-family:monospace"><?= number_format($sc_c) ?></td></tr>
                        <tr><td>Youth (15-24)</td><td style="text-align:right;font-family:monospace"><?= number_format($yu_c) ?></td></tr>
                        <tr><td>Children (under 18)</td><td style="text-align:right;font-family:monospace"><?= number_format($ch_c) ?></td></tr>
                        <tr><td style="font-weight:600">Total Residents</td><td style="text-align:right;font-family:monospace;font-weight:600"><?= number_format($total_residents) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MANAGE AREAS SECTION -->
        <div id="section-manage_areas" class="section">
            <div class="page-header">
                <h2>Manage Areas</h2>
                <p>Configure <?= htmlspecialchars($area_label) ?> areas for <?= htmlspecialchars($brgy_info["name"] ?? "") ?></p>
            </div>
            <?php
            $ma_areas = [];
            $ma_r = $conn->query("SELECT a.*, (SELECT COUNT(*) FROM residents r WHERE r.area_id=a.area_id AND r.is_active=1) as rc, (SELECT COUNT(*) FROM families f WHERE f.area_id=a.area_id) as fc FROM barangay_areas a WHERE a.barangay_id=$bid AND a.is_active=1 ORDER BY a.sort_order, a.area_name");
            if ($ma_r) while ($row=$ma_r->fetch_assoc()) $ma_areas[] = $row;
            ?>
            <div class="card">
                <div class="card-header"><div><div class="card-title">Area Label</div><div class="card-sub">e.g. Zone, Purok, Street</div></div></div>
                <form method="POST" action="manage_areas.php" style="margin-top:10px">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                        <input type="text" name="area_label" value="<?= htmlspecialchars($area_label) ?>" class="search-input" style="max-width:260px">
                        <button type="submit" name="update_label" class="btn btn-primary">Update Label</button>
                    </div>
                </form>
            </div>
            <div class="card">
                <div class="card-header"><div class="card-title">Add New Area</div></div>
                <form method="POST" action="manage_areas.php" style="margin-top:10px">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                        <input type="text" name="area_name" placeholder="e.g. Zone 1, Purok 3" required class="search-input">
                        <select name="area_type" class="filter-select">
                            <?php foreach(["Zone","Compound","Subdivision","Street","Purok","Sitio","Block","Area"] as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="add_area" class="btn btn-primary">+ Add Area</button>
                    </div>
                </form>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">All Areas (<?= count($ma_areas) ?>)</div>
                    <a href="manage_areas.php" class="btn btn-ghost btn-sm">Full Page</a>
                </div>
                <?php if (empty($ma_areas)): ?>
                <div class="card-empty">No areas yet. Add one above.</div>
                <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Area Name</th><th>Type</th><th>Residents</th><th>Families</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($ma_areas as $a): ?>
                    <tr>
                        <td style="font-weight:500"><?= htmlspecialchars($a["area_name"]) ?></td>
                        <td><span class="tag tag-navy"><?= htmlspecialchars($a["area_type"]) ?></span></td>
                        <td style="font-family:monospace"><?= number_format($a["rc"]) ?></td>
                        <td style="font-family:monospace"><?= number_format($a["fc"]) ?></td>
                        <td>
                            <form method="POST" action="manage_areas.php" style="display:inline" onsubmit="return confirm('Delete this area?')">
                                <input type="hidden" name="area_id" value="<?= $a["area_id"] ?>">
                                <button type="submit" name="delete_area" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

"""

# Insert before the closing </div>\n\n</div> (content wrapper + layout wrapper)
insert_before = '\n\n            </div>\n\n</div>'
idx = content.find(insert_before, 86000)
if idx >= 0:
    content = content[:idx] + '\n' + new_sections + content[idx:]
    print("inserted at:", idx)
else:
    print("FAILED - not found")

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("statistics:", "OK" if 'section-statistics' in content else "FAILED")
print("manage_areas:", "OK" if 'section-manage_areas' in content else "FAILED")
