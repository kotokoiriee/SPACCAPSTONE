with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'r', encoding='utf-8', errors='replace') as f:
    content = f.read()

# Find the alerts section div and count where it actually closes
alerts_start = content.find('<div id="section-alerts"')
print("alerts starts at:", alerts_start)

# Count div depth from alerts_start to find its true closing tag
depth = 0
i = alerts_start
while i < len(content):
    if content[i:i+4] == '<div':
        depth += 1
    elif content[i:i+6] == '</div>':
        depth -= 1
        if depth == 0:
            print("alerts closes at:", i)
            print("closing context:", repr(content[i-50:i+30]))
            break
    i += 1
