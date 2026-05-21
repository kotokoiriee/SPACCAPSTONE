with open('C:/xampp/htdocs/SPAC/dashboards/barangay/statistics.php', 'rb') as f:
    content = f.read()

idx = content.find(b'sidebar-menu')
while idx >= 0:
    print(repr(content[idx-5:idx+150]))
    print()
    idx = content.find(b'sidebar-menu', idx+1)
