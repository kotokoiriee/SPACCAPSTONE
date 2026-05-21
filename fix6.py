with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b"    document.querySelectorAll('.section').forEach(function(s){ s.classList.remove('active'); });\r\n    var url = new URL(window.location.href);\r\n    url.searchParams.set('section', id);\r\n    window.history.replaceState({}, '', url.toString());\r\n\r\n    document.getElementById('section-' + id).classList.add('active');",
    b"    document.querySelectorAll('.section').forEach(function(s){ s.classList.remove('active'); });\r\n    var target = document.getElementById('section-' + id);\r\n    if (target) target.classList.add('active');\r\n    var url = new URL(window.location.href);\r\n    url.searchParams.set('section', id);\r\n    window.history.replaceState({}, '', url.toString());"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"if (target) target.classList.add('active')" in content else "FAILED")
