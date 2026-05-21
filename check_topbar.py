with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find('.topbar')
print(content[idx:idx+400])
