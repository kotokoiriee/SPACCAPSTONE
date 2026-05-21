files = [
    'C:/xampp/htdocs/SPAC/dashboards/barangay/index.php',
    'C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php',
]

for filepath in files:
    with open(filepath, 'rb') as f:
        content = f.read()

    # Fix index.php style (has \r\n\r\n spacing)
    content = content.replace(
        b'sidebar-logo h1 {\r\n\r\n    color: var(--navy); font-size: 16px; font-weight: 600;\r\n\r\n    letter-spacing: 0.08em; text-transform: uppercase;\r\n\r\n}',
        b'sidebar-logo h1 {\r\n\r\n    color: var(--navy); font-size: 16px; font-weight: 700;\r\n\r\n    letter-spacing: 0.02em; text-transform: uppercase;\r\n\r\n}'
    )

    # Fix shared_style.css.php (single line style)
    content = content.replace(
        b'.sidebar-logo h1 { color: var(--navy); font-size: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }',
        b'.sidebar-logo h1 { color: var(--navy); font-size: 16px; font-weight: 700; letter-spacing: 0.02em; text-transform: uppercase; }'
    )

    with open(filepath, 'wb') as f:
        f.write(content)

    name = filepath.split('/')[-1]
    print(name + ": OK" if b'font-weight: 700' in content else name + ": FAILED - checking...")
    if b'font-weight: 700' not in content:
        idx = content.find(b'sidebar-logo h1')
        if idx >= 0:
            print("  actual: " + repr(content[idx:idx+120]))
