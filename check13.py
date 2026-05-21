with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Find the full sidebar menu and show it
idx = content.find(b'sidebar-menu">')
if idx >= 0:
    print(repr(content[idx:idx+2000]))
