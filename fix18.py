with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_sidebar.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'sidebar-logo">\n    <div>\n        <h1>SPAC</h1>\n        <p>Barangay Portal</p>\n    </div>\n</div>',
    b'sidebar-logo">\n    <div style="display:flex;align-items:center;justify-content:space-between">\n        <div>\n            <h1>SPAC</h1>\n            <p>Barangay Portal</p>\n        </div>\n        <?php if (!empty($brgy_info[\'logo\'])): ?>\n            <img src="<?= htmlspecialchars($brgy_info[\'logo\']) ?>" alt="Barangay Logo"\n                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0">\n        <?php else: ?>\n            <div style="width:80px;height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)">\n                <?= strtoupper(substr($brgy_info[\'name\'] ?? \'B\', 0, 1)) ?>\n            </div>\n        <?php endif; ?>\n    </div>\n</div>'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_sidebar.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"brgy_info['logo']" in content else "FAILED")
