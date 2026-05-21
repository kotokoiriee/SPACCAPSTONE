with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Find the closing of the PHP block
idx = content.find("<!DOCTYPE html>")
print("DOCTYPE at:", idx)
print(repr(content[idx-40:idx+20]))
