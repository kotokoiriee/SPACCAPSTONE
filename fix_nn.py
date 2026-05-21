with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

content = content.replace("        </div>\n\\n\\n<!-- ", "        </div>\n\n\n<!-- ", 1)

with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "w", encoding="utf-8") as f:
    f.write(content)

print("fixed:", "\\n\\n" not in content)
