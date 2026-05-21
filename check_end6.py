with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

ma_idx = content.find('<!-- MANAGE AREAS SECTION -->')
print(repr(content[ma_idx+3400:ma_idx+3800]))
