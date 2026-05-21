with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

idx = content.find('id="section-statistics"')
# Show 400 chars before it
print(repr(content[idx-400:idx+50]))
