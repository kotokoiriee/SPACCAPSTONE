with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

# Remove the PHP active_section assignment line entirely
content = content.replace(
    b"        <?php $active_section = isset($_GET['section']) && $_GET['section'] !== '' ? $_GET['section'] : 'dashboard'; ?>\r\n",
    b""
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b'active_section' not in content else "FAILED")
