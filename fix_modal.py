content = open('C:/xampp/htdocs/SPAC/index.php', 'r', encoding='utf-8').read()

modal = '''
<div id="reportModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
  <div style="background:#fff;border-radius:12px;padding:2rem;max-width:520px;width:90%;margin:auto;position:relative;">
    <button onclick="document.getElementById('reportModal').style.display='none'" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:22px;cursor:pointer;color:#333;">&times;</button>
    <h2 style="margin:0 0 4px;font-size:18px;color:#0f172a;">Report Missing Assistance</h2>
    <p style="margin:0 0 16px;font-size:13px;color:#64748b;">No login required. Our team will follow up with your barangay.</p>
    <form method="POST" action="guest_report.php">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Full Name <span style="color:red">*</span></label>
          <input type="text" name="full_name" placeholder="Enter your full name" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Contact Number <span style="color:red">*</span></label>
          <input type="text" name="contact_number" placeholder="09xx xxx xxxx" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Zone Number <span style="color:red">*</span></label>
          <input type="number" name="zone_number" placeholder="e.g. 1" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Address <span style="color:red">*</span></label>
          <input type="text" name="address" placeholder="House no., Street, Purok" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Barangay <span style="color:red">*</span></label>
          <select name="barangay_id" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select barangay —</option>
            <?php foreach($barangay_list as $b): ?>
            <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Month of Assistance <span style="color:red">*</span></label>
          <select name="assistance_month" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select month —</option>
            <option value="2026-01">January 2026</option>
            <option value="2026-02">February 2026</option>
            <option value="2026-03">March 2026</option>
            <option value="2026-04">April 2026</option>
            <option value="2026-05">May 2026</option>
            <option value="2026-06">June 2026</option>
            <option value="2026-07">July 2026</option>
            <option value="2026-08">August 2026</option>
            <option value="2026-09">September 2026</option>
            <option value="2026-10">October 2026</option>
            <option value="2026-11">November 2026</option>
            <option value="2026-12">December 2026</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Type of Assistance <span style="color:red">*</span></label>
          <select name="assistance_type" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;">
            <option value="">— Select type —</option>
            <option value="Food Assistance">Food Assistance</option>
            <option value="Medical / Health">Medical / Health</option>
            <option value="Financial Aid">Financial Aid</option>
            <option value="Livelihood">Livelihood</option>
            <option value="Educational">Educational</option>
            <option value="Disaster Relief">Disaster Relief</option>
            <option value="Senior Citizen">Senior Citizen Benefit</option>
            <option value="PWD">PWD Assistance</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label style="font-size:12px;font-weight:500;display:block;margin-bottom:4px;">Description</label>
          <textarea name="description" placeholder="Describe what happened..." rows="3" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;box-sizing:border-box;resize:vertical;"></textarea>
        </div>
      </div>
      <button type="submit" style="width:100%;padding:11px;background:#f0a500;border:none;border-radius:6px;font-size:14px;font-weight:600;color:#1a1200;cursor:pointer;margin-top:12px;">Submit Report</button>
    </form>
  </div>
</div>
'''

content = content.replace('</body>', modal + '\n</body>')
open('C:/xampp/htdocs/SPAC/index.php', 'w', encoding='utf-8').write(content)
print('Done!')
