with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

content = content.replace(
    b'sidebar-logo">\r\n    <div>\r\n        <h1>SPAC</h1>\r\n        <p>Barangay Portal</p>\r\n    </div>\r\n</div>',
    b'sidebar-logo">\r\n    <div style="display:flex;align-items:center;justify-content:space-between">\r\n        <div>\r\n            <h1>SPAC</h1>\r\n            <p>Barangay Portal</p>\r\n        </div>\r\n        <?php if (!empty($brgy_info[\'logo\'])): ?>\r\n            <img src="<?= htmlspecialchars($brgy_info[\'logo\']) ?>" alt="Barangay Logo"\r\n                 style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0">\r\n        <?php else: ?>\r\n            <div style="width:52px;height:52px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)">\r\n                <?= strtoupper(substr($brgy_info[\'name\'] ?? \'B\', 0, 1)) ?>\r\n            </div>\r\n        <?php endif; ?>\r\n    </div>\r\n</div>'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("Fix:", "OK" if b"brgy_info['logo']" in content else "FAILED")
