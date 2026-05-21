with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors='replace') as f:
    content = f.read()

# Find ALL topbar-related CSS in index.php
import re
for m in re.finditer(r'topbar', content):
    print(f"pos {m.start()}:", repr(content[m.start()-20:m.start()+80]))
    print()
