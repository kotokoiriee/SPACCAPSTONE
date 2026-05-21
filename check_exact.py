with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find("<!DOCTYPE html>")
print(repr(content[idx-60:idx+5]))
