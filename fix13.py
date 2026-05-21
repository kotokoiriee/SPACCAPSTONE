with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'rb') as f:
    content = f.read()

# Fix the double dot typo in selector
content = content.replace(
    b'.sidebar-menu div..menu-section {',
    b'.sidebar-menu .menu-section {'
)

# Remove menu-section-inline rule entirely
start = content.find(b'.menu-section-inline {')
if start >= 0:
    end = content.find(b'}', start) + 1
    content = content[:start] + content[end:]

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'wb') as f:
    f.write(content)

print("Fix 1:", "OK" if b'.sidebar-menu .menu-section {' in content else "FAILED")
print("Fix 2:", "OK" if b'.menu-section-inline' not in content else "FAILED")
