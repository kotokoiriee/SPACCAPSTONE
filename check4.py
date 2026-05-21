with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Find all occurrences
start = 0
while True:
    idx = content.find(b'active_section', start)
    if idx < 0:
        break
    print(f"Found at byte {idx}:")
    print(repr(content[idx-20:idx+80]))
    print()
    start = idx + 1
