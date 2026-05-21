with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find where statistics section starts (inside alerts)
stat_start = content.find('\n\n        <!-- STATISTICS SECTION -->')
print("stat_start:", stat_start)

# Find where alerts truly closes (after manage_areas)
alerts_close = 96963  # confirmed position
print("alerts_close context:", repr(content[alerts_close:alerts_close+10]))

# Extract the two new sections from inside alerts
new_sections = content[stat_start:alerts_close]
print("extracted length:", len(new_sections))
print("starts with:", repr(new_sections[:60]))
print("ends with:", repr(new_sections[-60:]))
