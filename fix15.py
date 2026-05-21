with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:var(--muted); opacity:0.6; box-sizing:border-box; }',
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color:#64748b; box-sizing:border-box; }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b'color:#64748b' in content else "FAILED")
