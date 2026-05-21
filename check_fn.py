with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

print("showLoadingAndGo in file:", b'showLoadingAndGo' in content)
idx = content.find(b'function showLoadingAndGo')
print("function defined:", idx >= 0)
if idx >= 0:
    print(repr(content[idx:idx+100]))
