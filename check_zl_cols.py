with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Find all uses of $zl[ to see what columns are referenced
import re
matches = re.findall(r"\$zl\['(\w+)'\]", content)
unique = sorted(set(matches))
print("Columns referenced:", unique)
