with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Fix menu-section CSS to display as its own block
content = content.replace(
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-',
    b'.menu-section { display:block; width:100%; clear:both; padding:16px 20px 4px; font-size:10px; font-weight:600; letter-'
)

# Find exact menu-section CSS
idx = content.find(b'menu-section')
if idx >= 0:
    print(repr(content[idx-5:idx+200]))
