with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Add top padding to topbar-right to push it down
content = content.replace(
    ".topbar-right { display: flex; align-items: center; gap: 12px; }",
    ".topbar-right { display: flex; align-items: center; gap: 12px; margin-top: 14px; }"
)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "w", encoding="utf-8") as f:
    f.write(content)

print("done")
