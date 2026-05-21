with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find('zone_leaders')
while idx >= 0:
    line = content[idx:idx+120]
    if 'SELECT' in line or 'query' in line.lower():
        print(repr(line))
        print()
    idx = content.find('zone_leaders', idx+1)
