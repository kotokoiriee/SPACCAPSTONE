with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

for kw in [b'.sidebar {', b'.sidebar-logo {', b'.sidebar-menu {', b'.menu-section {']:
    idx = content.find(kw)
    if idx >= 0:
        end = content.find(b'}', idx) + 1
        print(repr(content[idx:end]))
        print()
