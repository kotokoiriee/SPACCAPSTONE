with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Remove the duplicate old line that appears before urlParams
content = content.replace(
    b"getItem('activeSection') || 'dashboard';\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';",
    b"var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';"
)

# Also remove the PHP active class from section-dashboard since JS handles it
content = content.replace(
    b'id="section-dashboard" class="section <?= $active_section === \'dashboard\' ? \'active\' : \'\' ?>"',
    b'id="section-dashboard" class="section"'
)

# Same for officials section
content = content.replace(
    b'id="section-officials" class="section <?= $active_section === \'officials\' ? \'active\' : \'\' ?>"',
    b'id="section-officials" class="section"'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix 1 (duplicate):", "OK" if b"getItem('activeSection') || 'dashboard';\r\n    var urlParams" not in content else "FAILED")
print("Fix 2 (dashboard class):", "OK" if b'$active_section' not in content else "FAILED - still has active_section")
