with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Fix topbar height in index.php inline styles
content = content.replace(
    "background: var(--white); padding: 0 28px; height: 56px;\n\n    display: flex; align-items: center; justify-content: space-between;",
    "background: var(--white); padding: 0 28px; height: 64px;\n\n    display: flex; align-items: center; justify-content: space-between;"
)

print("height fixed:", "height: 64px" in content)

# Also fix topbar-right to ensure vertical centering
content = content.replace(
    ".topbar-right { display: flex; align-items: center; gap: 12px; }",
    ".topbar-right { display: flex; align-items: center; gap: 12px; height: 100%; }"
)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("done")
