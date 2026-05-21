with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

stat_start = content.find('\n\n        <!-- STATISTICS SECTION -->')
alerts_close = 96963

# Extract the sections
new_sections = content[stat_start:alerts_close].rstrip()

# Remove them from inside alerts (replace with nothing)
content = content[:stat_start] + content[alerts_close:]

# Now find where alerts closes in the updated content
# alerts section should now close cleanly - find it by looking for the pattern
new_alerts_close = content.find('</div>\n\n\n\n\n\n<!-- ', 86000)
print("new alerts close at:", new_alerts_close)
print("context:", repr(content[new_alerts_close:new_alerts_close+30]))

# Insert the sections right after alerts closing </div>
insert_after = content.find('</div>', new_alerts_close) + len('</div>')
print("insert after:", insert_after)
print("insert context:", repr(content[insert_after:insert_after+30]))

content = content[:insert_after] + '\n\n' + new_sections + '\n\n' + content[insert_after:]

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("statistics:", "OK" if 'section-statistics' in content else "FAILED")
print("manage_areas:", "OK" if 'section-manage_areas' in content else "FAILED")

# Verify alerts no longer wraps statistics
alerts_start = content.find('<div id="section-alerts"')
stat_pos = content.find('<div id="section-statistics"')
print("alerts at:", alerts_start, "| statistics at:", stat_pos)
print("statistics is AFTER alerts:", stat_pos > alerts_start)
