with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

idx = content.find(b'urlParams')
if idx >= 0:
    print(repr(content[idx-100:idx+400]))
