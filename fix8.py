with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Force dashboard as default by clearing localStorage first
content = content.replace(
    b"document.addEventListener('DOMContentLoaded', function() {\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';\r\n    var menuBtn = document.querySelector('.menu-item[onclick*=\"' + saved + '\"]');\r\n    showSection(saved, menuBtn);\r\n});",
    b"document.addEventListener('DOMContentLoaded', function() {\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';\r\n    var menuBtn = document.querySelector('.menu-item[onclick*=\"' + saved + '\"]');\r\n    if (!menuBtn) {\r\n        saved = 'dashboard';\r\n        menuBtn = document.querySelector('.menu-item[onclick*=\"dashboard\"]');\r\n    }\r\n    showSection(saved, menuBtn);\r\n});"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"if (!menuBtn)" in content else "FAILED")
