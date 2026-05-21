with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Check the init function area
idx = content.find(b'urlParams')
if idx >= 0:
    print("FOUND urlParams:")
    print(repr(content[idx-50:idx+300]))

# Check section-dashboard class logic
idx2 = content.find(b'section-dashboard')
if idx2 >= 0:
    print("FOUND section-dashboard:")
    print(repr(content[idx2-5:idx2+150]))
