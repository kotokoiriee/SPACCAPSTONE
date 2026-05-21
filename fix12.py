files_info = [
    ('C:/xampp/htdocs/SPAC/dashboards/barangay/statistics.php', 8310, 10624),
    ('C:/xampp/htdocs/SPAC/dashboards/barangay/manage_areas.php', 6656, 8970),
    ('C:/xampp/htdocs/SPAC/dashboards/barangay/import_residents.php', 22294, 24608),
]

replacement = b"<?php include 'shared_sidebar.php'; ?>\r\n\r\n"

for filepath, start, end in files_info:
    with open(filepath, 'rb') as f:
        content = f.read()

    new_content = content[:start] + replacement + content[end:]

    with open(filepath, 'wb') as f:
        f.write(new_content)

    name = filepath.split('/')[-1]
    print(name + ": OK" if b"shared_sidebar.php" in new_content else name + ": FAILED")
