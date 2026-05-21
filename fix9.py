with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'.sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; overflow-x: hidden; }',
    b'.sidebar-menu { padding: 12px 0; flex: 1; overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b'flex-direction: column' in content else "FAILED")
