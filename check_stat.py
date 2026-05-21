with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

idx = content.find('section-statistics')
if idx >= 0:
    print("Found at:", idx)
    print(content[idx:idx+300])
else:
    print("NOT FOUND")
