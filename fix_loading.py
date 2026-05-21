import os

files = [
    'C:/xampp/htdocs/SPAC/dashboards/barangay/index.php',
    'C:/xampp/htdocs/SPAC/dashboards/barangay/shared_sidebar.php',
]

old_statistics = b"onclick=\"window.location.href='statistics.php'\""
new_statistics = b"onclick=\"showLoadingAndGo('statistics.php', this)\""

old_manage = b"onclick=\"window.location.href='manage_areas.php'\""
new_manage = b"onclick=\"showLoadingAndGo('manage_areas.php', this)\""

old_import = b"onclick=\"window.location.href='import_residents.php'\""
new_import = b"onclick=\"showLoadingAndGo('import_residents.php', this)\""

for filepath in files:
    with open(filepath, 'rb') as f:
        content = f.read()
    content = content.replace(old_statistics, new_statistics)
    content = content.replace(old_manage, new_manage)
    content = content.replace(old_import, new_import)
    with open(filepath, 'wb') as f:
        f.write(content)
    print(filepath.split('/')[-1] + ": OK")
