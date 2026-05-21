with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'.sidebar-menu .menu-section { display: block; padding: 14px 20px 2px; font-size: 9px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #cbd5e1; }',
    b'.sidebar-menu .menu-section { display: block; padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: #64748b; }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b'color: #64748b' in content else "FAILED")
