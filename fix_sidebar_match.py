with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix 1: sidebar width 260px -> 232px
content = content.replace(
    b'    width: 260px; background: var(--white); min-height: 100vh;\r\n\r\n    display: flex; flex-direction: column; position: fixed;\r\n\r\n    top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100;\r\n\r\n}',
    b'    width: 232px; background: var(--white); min-height: 100vh;\r\n\r\n    display: flex; flex-direction: column; position: fixed;\r\n\r\n    top: 0; left: 0; border-right: 1px solid var(--border); z-index: 100;\r\n\r\n}'
)

# Fix 2: main margin-left 260px -> 232px
content = content.replace(
    b'{ margin-left: 260px; flex: 1; width: calc(100% - 260px);',
    b'{ margin-left: 232px; flex: 1; width: calc(100% - 232px);'
)

# Fix 3: menu-section color to match superadmin (var(--border-2))
content = content.replace(
    b'color:#64748b; box-sizing:border-box; }',
    b'color: var(--border-2); }'
)

# Fix 4: menu-section padding/font to exactly match superadmin
content = content.replace(
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-spacing:0.1em; text-transform:uppercase; color: var(--border-2); }',
    b'.menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("sidebar width:", "OK" if b'width: 232px' in content else "FAILED")
print("main margin:", "OK" if b'margin-left: 232px' in content else "FAILED")
print("menu-section color:", "OK" if b'color: var(--border-2)' in content else "FAILED")
