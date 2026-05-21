with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

content = content.replace("\ufeff<?php\n// -- Import Residents Logic --", "\n// -- Import Residents Logic --", 1)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("fixed:", "\ufeff<?php\n// -- Import Residents Logic" not in content)
print("logic still there:", "ir_normalize_date" in content)
