with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix the sidebar-menu to use flex-direction column
idx = content.find(b'.sidebar-menu {')
if idx >= 0:
    print("sidebar-menu CSS:")
    print(repr(content[idx:idx+200]))

# Find the full menu-section rule
idx2 = content.find(b'enu .menu-section {')
if idx2 >= 0:
    print("menu-section CSS:")
    print(repr(content[idx2-5:idx2+300]))
