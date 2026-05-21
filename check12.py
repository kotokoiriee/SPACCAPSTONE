with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Check the actual sidebar HTML structure
idx = content.find(b'sidebar-menu">')
if idx >= 0:
    print(repr(content[idx:idx+800]))
