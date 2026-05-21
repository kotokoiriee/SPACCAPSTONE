with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Find and show the area around 'active_section'
idx = content.find(b'active_section')
if idx >= 0:
    print("FOUND active_section at byte:", idx)
    print(repr(content[idx-10:idx+60]))
else:
    print("active_section NOT FOUND")

# Find localStorage
idx2 = content.find(b'localStorage.setItem')
if idx2 >= 0:
    print("FOUND localStorage at byte:", idx2)
    print(repr(content[idx2-5:idx2+100]))
else:
    print("localStorage NOT FOUND")

# Find the init function
idx3 = content.find(b'getItem')
if idx3 >= 0:
    print("FOUND getItem at byte:", idx3)
    print(repr(content[idx3-20:idx3+150]))
else:
    print("getItem NOT FOUND")
