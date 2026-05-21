with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Update sidebar buttons to use showSection instead of page navigation
content = content.replace(
    b"onclick=\"showLoadingAndGo('statistics.php', this)\"",
    b"onclick=\"showSection('statistics', this)\""
)
content = content.replace(
    b"onclick=\"showLoadingAndGo('manage_areas.php', this)\"",
    b"onclick=\"showSection('manage_areas', this)\""
)

# Add section titles
content = content.replace(
    b"    ayuda: 'Ayuda / Assistance'",
    b"    statistics: 'Statistics', manage_areas: 'Manage Areas',\n    ayuda: 'Ayuda / Assistance'"
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("sidebar buttons:", "OK" if b"showSection('statistics'" in content else "FAILED")
print("section titles:", "OK" if b"statistics: 'Statistics'" in content else "FAILED")
