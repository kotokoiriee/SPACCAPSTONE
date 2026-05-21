with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Check sidebar links for statistics and manage_areas
idx1 = content.find('Statistics')
print("Statistics link:", repr(content[idx1-80:idx1+20]))
print()
idx2 = content.find('Manage Areas')
print("Manage Areas link:", repr(content[idx2-80:idx2+20]))
print()

# Check sectionTitles
idx3 = content.find('sectionTitles')
print("sectionTitles:", content[idx3:idx3+300])
