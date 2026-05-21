with open('C:/xampp/htdocs/SPAC/dashboards/barangay/statistics.php', 'rb') as f:
    content = f.read()

# Check CSS for menu-section
idx = content.find(b'menu-section')
while idx >= 0:
    print(repr(content[idx-5:idx+200]))
    print()
    idx = content.find(b'menu-section', idx+1)
    if idx > 15000:
        break
