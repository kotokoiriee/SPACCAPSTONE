files = [
    'C:/xampp/htdocs/SPAC/dashboards/barangay/statistics.php',
    'C:/xampp/htdocs/SPAC/dashboards/barangay/manage_areas.php',
    'C:/xampp/htdocs/SPAC/dashboards/barangay/import_residents.php',
]

for filepath in files:
    with open(filepath, 'rb') as f:
        content = f.read()

    start = content.find(b'<div class="sidebar">')
    end = content.find(b'sidebar-footer')
    end = content.find(b'</div>', end)
    end = content.find(b'</div>', end+1) + 6

    name = filepath.split('/')[-1]
    if start >= 0 and end >= 0:
        print("=== " + name + " ===")
        print("start=" + str(start) + " end=" + str(end))
        print(repr(content[end-30:end+30]))
    else:
        print("=== " + name + " === NOT FOUND")
