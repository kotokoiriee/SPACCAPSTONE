with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Check what the section CSS looks like
idx = content.find(b'.section {')
if idx >= 0:
    print("FOUND .section CSS:")
    print(repr(content[idx:idx+150]))

idx2 = content.find(b'.section.active')
if idx2 >= 0:
    print("FOUND .section.active CSS:")
    print(repr(content[idx2:idx2+100]))

# Check DOMContentLoaded placement
idx3 = content.find(b'DOMContentLoaded')
if idx3 >= 0:
    print("FOUND DOMContentLoaded:")
    print(repr(content[idx3-20:idx3+400]))
