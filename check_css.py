with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Check .section CSS
idx = content.find('.section {')
print("CSS:", repr(content[idx:idx+100]))
print()

# Check .section.active CSS
idx2 = content.find('.section.active')
print("active CSS:", repr(content[idx2:idx2+80]))
print()

# Check full showSection function
idx3 = content.find('function showSection')
print("showSection:", content[idx3:idx3+500])
