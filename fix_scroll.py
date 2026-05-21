with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

old = "    var target = document.getElementById('section-' + id);\n    if (target) target.classList.add('active');"
new = "    var target = document.getElementById('section-' + id);\n    if (target) target.classList.add('active');\n    window.scrollTo(0, 0);"

content = content.replace(old, new)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("fixed:", "window.scrollTo(0, 0)" in content)
