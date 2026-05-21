with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Remove the wrongly placed sections
stat_start = content.find('\n\n        <!-- STATISTICS SECTION -->')
stat_end = content.find('        </div>\n', content.find('<!-- MANAGE AREAS SECTION -->'))
stat_end = content.find('        </div>\n', stat_end + 1) + len('        </div>\n')

print("stat_start:", stat_start)
print("stat_end:", stat_end)
print("content at end:", repr(content[stat_end-20:stat_end+50]))
