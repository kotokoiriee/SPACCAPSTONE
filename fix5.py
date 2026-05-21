with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix the broken merged line
content = content.replace(
    b"    var saved = localStorage.var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';",
    b"    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"var saved = localStorage.var" not in content else "FAILED")
