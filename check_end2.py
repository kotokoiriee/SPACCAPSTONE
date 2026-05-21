with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find the exact end of manage_areas section
ma_idx = content.find('<!-- MANAGE AREAS SECTION -->')
print("manage areas at:", ma_idx)
# Show a chunk after manage areas to find its closing div
print(repr(content[ma_idx+2000:ma_idx+2200]))
