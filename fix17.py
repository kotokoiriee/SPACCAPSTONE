with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0"',
    b'style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0"'
)

content = content.replace(
    b'style="width:52px;height:52px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)"',
    b'style="width:80px;height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)"'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b'width:80px;height:80px' in content else "FAILED")
