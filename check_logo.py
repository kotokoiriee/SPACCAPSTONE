with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

idx = content.find(b'sidebar-logo h1')
if idx >= 0:
    end = content.find(b'}', idx) + 1
    print(repr(content[idx:end]))
