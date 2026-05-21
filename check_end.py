with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Find the alerts section end - insert after it
idx = content.find(b'section-alerts')
if idx >= 0:
    print("alerts section found at:", idx)
    # Find the closing div after alerts section
    end = content.find(b'</div>\r\n\r\n\r\n\r\n', idx)
    if end >= 0:
        print("closing found at:", end)
        print(repr(content[end:end+60]))
    else:
        end = content.find(b'</div>\n\n\n\n', idx)
        print("closing2 found at:", end)
        print(repr(content[end:end+60]))
