with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

html_section = """
        <!-- IMPORT RESIDENTS SECTION -->
        <div id="section-import_residents" class="section">
            <div class="page-header">
                <h2>Import from Access Database</h2>
                <p>Upload CSV exports from your .accdb file to populate households and residents.</p>
            </div>

            <?php if ($ir_message): ?>
            <div class="alert-banner <?= $ir_message_type ?>">
                <?= htmlspecialchars($ir_message) ?>
            </div>
            <?php endif; ?>

            <?php if ($ir_import_done): ?>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
                <div class="stat-card"><div class="stat-num"><?= number_format($ir_import_stats['families_inserted']) ?></div><div class="stat-label">Families Added</div></div>
                <div class="stat-card"><div class="stat-num"><?= number_format($ir_import_stats['residents_inserted']) ?></div><div class="stat-label">Residents Added</div></div>
                <div class="stat-card"><div class="stat-num"><?= number_format($ir_import_stats['families_skipped']) ?></div><div class="stat-label">Families Skipped</div></div>
                <div class="stat-card"><div class="stat-num"><?= number_format($ir_import_stats['residents_skipped']) ?></div><div class="stat-label">Residents Skipped</div></div>
            </div>
            <div style="display:flex;gap:10px">
                <a href="index.php?section=households" class="btn btn-primary">View Households</a>
                <a href="index.php?section=import_residents" class="btn btn-secondary">Import Again</a>
            </div>

            <?php else: ?>

            <div style="display:flex;gap:0;margin-bottom:24px;border:1px solid var(--border);border-radius:10px;overflow:hidden">
                <div style="flex:1;padding:14px 20px;font-size:12px;font-weight:500;background:<?= empty($ir_preview_data)?'#fff':'var(--surface-2)' ?>;color:<?= empty($ir_preview_data)?'var(--navy)':'var(--muted)' ?>;border-right:1px solid var(--border);display:flex;align-items:center;gap:10px">
                    <span style="font-size:11px;font-weight:700;background:<?= empty($ir_preview_data)?'var(--navy)':'var(--border)' ?>;color:<?= empty($ir_preview_data)?'#fff':'var(--muted)' ?>;border-radius:20px;padding:2px 8px;font-family:monospace">01</span> Upload &amp; Preview
                </div>
                <div style="flex:1;padding:14px 20px;font-size:12px;font-weight:500;background:<?= !empty($ir_preview_data)?'#fff':'var(--surface-2)' ?>;color:<?= !empty($ir_preview_data)?'var(--navy)':'var(--muted)' ?>;border-right:1px solid var(--border);display:flex;align-items:center;gap:10px">
                    <span style="font-size:11px;font-weight:700;background:<?= !empty($ir_preview_data)?'var(--navy)':'var(--border)' ?>;color:<?= !empty($ir_preview_data)?'#fff':'var(--muted)' ?>;border-radius:20px;padding:2px 8px;font-family:monospace">02</span> Confirm Import
                </div>
            </div>

            <div class="card">
                <div class="card-header"><div><div class="card-title">Upload CSV Files</div><div class="card-sub">HHID is required; HHMEM is optional for household members</div></div></div>
                <form method="POST" enctype="multipart/form-data" style="margin-top:10px">
                    <input type="hidden" name="ir_action" value="preview">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:500;color:var(--navy);margin-bottom:8px">HHID.csv — Household Heads <span style="color:#dc2626">*</span></label>
                            <div style="border:2px dashed var(--border);border-radius:10px;padding:24px 20px;text-align:center;position:relative;background:#fafafa">
                                <input type="file" name="hhid_csv" accept=".csv,.txt" required style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%" onchange="document.getElementById('ir-name-hhid').textContent=this.files[0]?this.files[0].name:''">
                                <div style="font-size:13px;color:var(--muted);pointer-events:none"><strong style="color:var(--navy)">Choose file</strong> or drag here<br><span id="ir-name-hhid" style="font-size:12px;color:#16a34a;font-family:monospace"></span></div>
                            </div>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:500;color:var(--navy);margin-bottom:8px">HHMEM.csv — Members <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                            <div style="border:2px dashed var(--border);border-radius:10px;padding:24px 20px;text-align:center;position:relative;background:#fafafa">
                                <input type="file" name="hhmem_csv" accept=".csv,.txt" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%" onchange="document.getElementById('ir-name-hhmem').textContent=this.files[0]?this.files[0].name:''">
                                <div style="font-size:13px;color:var(--muted);pointer-events:none"><strong style="color:var(--navy)">Choose file</strong> or drag here<br><span id="ir-name-hhmem" style="font-size:12px;color:#16a34a;font-family:monospace"></span></div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Preview Import</button>
                </form>
            </div>

            <?php if (!empty($ir_preview_data)): ?>
            <div class="card">
                <div class="card-header"><div><div class="card-title">Preview (first 10 households)</div><div class="card-sub">Review before confirming</div></div></div>
                <table class="data-table">
                    <thead><tr><th>Head of Family</th><th>Area</th><th>Address</th><th>Members</th></tr></thead>
                    <tbody>
                    <?php foreach ($ir_preview_data as $row): ?>
                    <tr>
                        <td style="font-weight:500"><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['zone']) ?></td>
                        <td style="color:var(--muted)"><?= htmlspecialchars($row['address']?:'-') ?></td>
                        <td><?php if(empty($row['members'])): ?><span class="tag tag-muted">Head only</span><?php else: ?><span class="tag tag-navy"><?= $row['member_count'] ?> member<?= $row['member_count']!=1?'s':'' ?></span><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);display:flex;gap:8px;align-items:center">
                    <form method="POST">
                        <input type="hidden" name="ir_action" value="import">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('This will replace all existing data. Continue?')">Confirm and Import All</button>
                    </form>
                    <a href="index.php?section=import_residents" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
"""

# Insert before the closing of the content area (same place as statistics/manage_areas)
insert_before = "\\n\\n            </div>\\n\\n</div>"
idx = content.find("\\n\\n            </div>\\n\\n</div>", 86000)
if idx >= 0:
    content = content[:idx] + "\\n" + html_section + content[idx:]
    print("inserted at:", idx)
else:
    # fallback: insert before the modals comment
    idx2 = content.find("<!-- ", 88000)
    if idx2 >= 0:
        content = content[:idx2] + html_section + "\\n\\n" + content[idx2:]
        print("inserted (fallback) at:", idx2)
    else:
        print("FAILED")

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("section exists:", "section-import_residents" in content)
