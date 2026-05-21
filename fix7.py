with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Wrap the init function to run on DOMContentLoaded
content = content.replace(
    b"(function() {\r\n\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';\r\n    var menuBtn = document.querySelector('.menu-item[onclick*=\"' + saved + '\"]');\r\n\r\n    s",
    b"document.addEventListener('DOMContentLoaded', function() {\r\n    var urlParams = new URLSearchParams(window.location.search);\r\n    var fromUrl = urlParams.get('section');\r\n    var saved = fromUrl || localStorage.getItem('activeSection') || 'dashboard';\r\n    var menuBtn = document.querySelector('.menu-item[onclick*=\"' + saved + '\"]');\r\n    s"
)

# Fix the closing of that function
content = content.replace(
    b"    showSection(saved, menuBtn);\r\n\r\n})();",
    b"    showSection(saved, menuBtn);\r\n});"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"DOMContentLoaded" in content else "FAILED")
