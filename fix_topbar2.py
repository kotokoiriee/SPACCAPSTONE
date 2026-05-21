with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

content = content.replace(
    ".topbar-right { display: flex; align-items: center; gap: 12px; margin-top: 14px; }",
    ".topbar-right { display: flex; align-items: center; gap: 12px; }"
)

# Fix the topbar itself to have proper alignment
content = content.replace(
    "background: var(--white); padding: 0 28px; height: 56px;\n    display: flex; align-items: center; justify-content: space-between;",
    "background: var(--white); padding: 0 28px; height: 64px;\n    display: flex; align-items: center; justify-content: space-between;"
)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "w", encoding="utf-8") as f:
    f.write(content)

print("done")
