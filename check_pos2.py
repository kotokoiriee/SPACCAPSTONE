with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Show what comes just before section-statistics
idx = content.find('<!-- STATISTICS SECTION -->')
print(repr(content[idx-300:idx+50]))
