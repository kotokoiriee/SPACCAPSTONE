with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find('.content {')
print(content[idx:idx+200])
print()
idx2 = content.find('.main {')
print(content[idx2:idx2+200])
