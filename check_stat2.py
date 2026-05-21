with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find statistics section and show first 500 chars
idx = content.find('section-statistics')
print(content[idx-10:idx+500])
