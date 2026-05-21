with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find("<!DOCTYPE html>")
# Find the ?> just before <!DOCTYPE
php_end = content.rfind("?>", 0, idx)
print("php_end at:", php_end)
print("inserting after index:", php_end + 2)

ir_logic = open("C:/xampp/htdocs/SPAC/ir_logic.php", "r", encoding="utf-8").read()

content = content[:php_end] + ir_logic + content[php_end:]

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("PHP logic inserted:", "ir_normalize_date" in content)
