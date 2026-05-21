with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Check if there is a fixed position style on topbar-right in index.php
import re
matches = re.findall(r'position.*fixed.*topbar|topbar.*position.*fixed', content)
print("fixed matches:", matches)

# Check inline style on topbar-right div
idx = content.find('topbar-right')
print(repr(content[idx-30:idx+100]))
