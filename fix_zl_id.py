with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Count occurrences
print("zl['id'] count:", content.count("$zl['id']"))

# Fix $zl['id'] -> $zl['zone_leader_id']
content = content.replace("$zl['id']", "$zl['zone_leader_id']")

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("fixed, remaining:", content.count("$zl['id']"))
