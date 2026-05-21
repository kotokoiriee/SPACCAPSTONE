with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Find all section divs
start = 0
while True:
    idx = content.find(b'id="section-', start)
    if idx < 0:
        break
    print(repr(content[idx:idx+80]))
    start = idx + 1
