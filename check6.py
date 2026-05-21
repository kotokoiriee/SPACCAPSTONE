with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

idx = content.find(b'(function() {')
while idx >= 0:
    print(f"Found at byte {idx}:")
    print(repr(content[idx:idx+300]))
    print()
    idx = content.find(b'(function() {', idx+1)
