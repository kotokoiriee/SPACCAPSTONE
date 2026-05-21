with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'rb') as f:
    content = f.read()

# Fix sidebar width to 232px
content = content.replace(b'width: 260px', b'width: 232px')
content = content.replace(b'width:260px', b'width: 232px')
content = content.replace(b'margin-left: 260px', b'margin-left: 232px')
content = content.replace(b'margin-left:260px', b'margin-left: 232px')

# Fix calc width
content = content.replace(b'calc(100% - 260px)', b'calc(100% - 232px)')

# Fix menu-section color to match superadmin
content = content.replace(b'color: #64748b;', b'color: var(--border-2);')
content = content.replace(b'color:#64748b;', b'color: var(--border-2);')

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'wb') as f:
    f.write(content)

print("Done!")
