with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Check sidebar width
idx = content.find(b'232px')
if idx >= 0:
    print("FOUND 232px:")
    print(repr(content[idx-50:idx+50]))
else:
    print("232px NOT FOUND")

# Check menu-section
idx2 = content.find(b'menu-section')
if idx2 >= 0:
    print("FOUND menu-section:")
    print(repr(content[idx2-5:idx2+150]))
else:
    print("menu-section NOT FOUND")

# Check dashboard section issue
idx3 = content.find(b'section-dashboard')
if idx3 >= 0:
    print("FOUND section-dashboard:")
    print(repr(content[idx3-10:idx3+100]))
