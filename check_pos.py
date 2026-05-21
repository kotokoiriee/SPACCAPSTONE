with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

idx = content.find('id="section-statistics"')
# Show 200 chars before to see if it is inside content div
print(repr(content[idx-200:idx+50]))
