with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find the end of the alerts section - look for its closing </div> (the section div)
alerts_idx = content.find('id="section-alerts"')
print("alerts section at:", alerts_idx)
# Show what closes the alerts section
print(repr(content[alerts_idx+1800:alerts_idx+2100]))
