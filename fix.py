with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix 1
content = content.replace(
    b"isset($_GET['msg']) && 'dashboard'",
    b"isset($_GET['section']) ? $_GET['section'] : 'dashboard'"
)

# Fix 2
content = content.replace(
    b"    localStorage.setItem('activeSection', id);\r\n\r\n    document.querySelectorAll('.section').forEach(function(s){ s.classList.remove('active'); });",
    b"    localStorage.setItem('activeSection', id);\r\n\r\n    document.querySelectorAll('.section').forEach(function(s){ s.classList.remove('active'); });\r\n    var url = new URL(window.location.href);\r\n    url.searchParams.set('section', id);\r\n    window.history.replaceState({}, '', url.toString());"
)

# Fix 3
content = content.replace(
    b"aved = localStorage.getItem('activeSection') || 'dashboard';\r\n\r\n    var menuBtn",
    b"aved = localStorage.getItem('activeSection') || 'dashboard';\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';\r\n    var menuBtn"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix 1:", "OK" if b"isset($_GET['section'])" in content else "FAILED")
print("Fix 2:", "OK" if b"replaceState" in content else "FAILED")
print("Fix 3:", "OK" if b"urlParams" in content else "FAILED")
