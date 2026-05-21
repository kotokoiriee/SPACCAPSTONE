with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix sidebar width from 232px to 260px
content = content.replace(
    b'width: 232px; background: var(--white); min-height: 100vh;',
    b'width: 260px; background: var(--white); min-height: 100vh;'
)

# Fix main margin to match
content = content.replace(
    b'.main { margin-left: 232px;',
    b'.main { margin-left: 260px;'
)

# Fix menu-section to be block and have proper spacing
content = content.replace(
    b'.menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); }',
    b'.menu-section { padding: 16px 20px 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--border-2); display: block; white-space: nowrap; }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Width fix:", "OK" if b'width: 260px' in content else "FAILED")
print("Margin fix:", "OK" if b'margin-left: 260px' in content else "FAILED")
print("Section fix:", "OK" if b'white-space: nowrap' in content else "FAILED")
