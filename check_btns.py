with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

for kw in [b'statistics.php', b'manage_areas.php', b'import_residents.php']:
    idx = content.find(kw)
    if idx >= 0:
        print(repr(content[idx-80:idx+50]))
        print()
