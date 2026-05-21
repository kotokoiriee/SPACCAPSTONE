with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'rb') as f:
    content = f.read()

idx = content.find(b'.sidebar-menu .menu-section {')
if idx >= 0:
    print(repr(content[idx:idx+200]))
