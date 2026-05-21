with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix 1: Fix the main width - remove the calc that still uses 232px
content = content.replace(
    b'{ margin-left: 260px; flex: 1; width: calc(100% - 232px); display: flex; flex-direction: column; min',
    b'{ margin-left: 260px; flex: 1; width: calc(100% - 260px); display: flex; flex-direction: column; min'
)

# Fix 2: Fix dashboard not showing - make default 'dashboard' work
content = content.replace(
    b"isset($_GET['section']) ? $_GET['section'] : 'dashboard'",
    b"isset($_GET['section']) && $_GET['section'] !== '' ? $_GET['section'] : 'dashboard'"
)

# Fix 3: Clean up the messy menu-section CSS and replace with clean version
content = content.replace(
    b'div..menu-section { display:block !important; width:100% !important; clear:both !important; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-',
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix 1 (width):", "OK" if b'calc(100% - 260px)' in content else "FAILED")
print("Fix 2 (dashboard):", "OK" if b"!== ''" in content else "FAILED")
print("Fix 3 (menu-section CSS):", "OK" if b'.menu-section { display:block; width:100%' in content else "FAILED")
