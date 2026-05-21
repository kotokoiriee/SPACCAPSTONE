with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Check what showSection does with the id
idx = content.find('function showSection')
print(content[idx:idx+400])
