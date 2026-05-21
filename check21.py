with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_sidebar.php', 'rb') as f:
    content = f.read()

idx = content.find(b'sidebar-logo">')
if idx >= 0:
    print(repr(content[idx:idx+200]))
