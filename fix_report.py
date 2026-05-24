content = open('C:/xampp/htdocs/SPAC/guest_report.php', 'r', encoding='utf-8').read()

content = content.replace(
    "$assistance_type = $conn->real_escape_string(trim($_POST['assistance_type']));",
    "$assistance_type  = $conn->real_escape_string(trim($_POST['assistance_type']));\n    $assistance_month = $conn->real_escape_string(trim($_POST['assistance_month'] ?? ''));"
)

content = content.replace(
    'INSERT INTO guest_reports (barangay_id, full_name, address, zone_number, contact_number, assistance_type, description)',
    'INSERT INTO guest_reports (barangay_id, full_name, address, zone_number, contact_number, assistance_type, assistance_month, description)'
)

content = content.replace(
    "VALUES ($barangay_id, '$full_name', '$address', $zone_number, '$contact_number', '$assistance_type', '$description')",
    "VALUES ($barangay_id, '$full_name', '$address', $zone_number, '$contact_number', '$assistance_type', '$assistance_month', '$description')"
)

content = content.replace(
    '<!-- Description -->',
    '<div class="form-group full"><label>Month of Assistance Not Received <span class="req">*</span></label><select name="assistance_month" required><option value="">— Select month —</option><option value="2026-01">January 2026</option><option value="2026-02">February 2026</option><option value="2026-03">March 2026</option><option value="2026-04">April 2026</option><option value="2026-05">May 2026</option><option value="2026-06">June 2026</option><option value="2026-07">July 2026</option><option value="2026-08">August 2026</option><option value="2026-09">September 2026</option><option value="2026-10">October 2026</option><option value="2026-11">November 2026</option><option value="2026-12">December 2026</option></select></div>\n                <!-- Description -->'
)

open('C:/xampp/htdocs/SPAC/guest_report.php', 'w', encoding='utf-8').write(content)
print('Done!')
