with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find("<?php\n// -- Import Residents Logic")
print("found at:", idx)
print(repr(content[idx-5:idx+40]))
