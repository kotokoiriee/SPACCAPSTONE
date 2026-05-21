with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Find all topbar divs
import re
matches = [(m.start(), content[m.start():m.start()+300]) for m in re.finditer(r'class="topbar"', content)]
print(f"Found {len(matches)} topbars\n")
for i, (pos, html) in enumerate(matches):
    print(f"--- Topbar {i+1} at {pos} ---")
    print(html[:200])
    print()
