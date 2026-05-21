with open("C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

idx = content.find('.topbar-right')
print(content[idx:idx+200])
print()
idx2 = content.find('.brgy-chip')
print(content[idx2:idx2+150])
print()
idx3 = content.find('.avatar-btn')
print(content[idx3:idx3+150])
