with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Remove the wrongly placed sections (including the stray </div> before them)
stat_start = content.find('\n\n        <!-- STATISTICS SECTION -->')
ma_end_marker = '            </div>\n        </div>\n</div>\n\n\n\n    </div>\n\n</div>'
ma_end_idx = content.find(ma_end_marker, content.find('<!-- MANAGE AREAS SECTION -->'))

if stat_start >= 0 and ma_end_idx >= 0:
    # What to keep after the bad sections
    after = content[ma_end_idx + len(ma_end_marker) - len('\n\n\n\n    </div>\n\n</div>'):]
    # Content before the bad sections
    before = content[:stat_start]
    content = before + '\n\n        ' + after.lstrip('\n')
    print("removed old sections: OK")
    print("section-statistics still in file:", 'section-statistics' in content)
else:
    print("FAILED - stat_start:", stat_start, "ma_end_idx:", ma_end_idx)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'w', encoding='utf-8') as f:
    f.write(content)
