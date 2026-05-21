with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    lines = f.readlines()

for i in range(740, 752):
    print(f"{i+1}: {lines[i]}", end="")
