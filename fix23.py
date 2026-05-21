files = [
    'C:/xampp/htdocs/SPAC/dashboards/barangay/index.php',
    'C:/xampp/htdocs/SPAC/dashboards/barangay/shared_sidebar.php',
]

for filepath in files:
    with open(filepath, 'rb') as f:
        content = f.read()

    # Fix logo size to match superadmin (80px)
    content = content.replace(
        b'width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0',
        b'width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;object-fit:cover;object-position:center;border:2px solid var(--border);flex-shrink:0'
    )
    content = content.replace(
        b'width:80px;height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:600;flex-shrink:0;border:2px solid var(--border)',
        b'width:80px;height:80px;min-width:80px;min-height:80px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:600;flex-shrink:0;border:2px solid var(--border)'
    )

    with open(filepath, 'wb') as f:
        f.write(content)

    print(filepath.split('/')[-1] + ": OK")

# Fix shared_style.css.php sidebar width to match superadmin (232px)
with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'rb') as f:
    css = f.read()

# Fix sidebar width
css = css.replace(b'width: 210px', b'width: 232px')
css = css.replace(b'width:210px', b'width: 232px')
css = css.replace(b'margin-left: 210px', b'margin-left: 232px')
css = css.replace(b'margin-left:210px', b'margin-left: 232px')

# Fix menu-section color to match superadmin
css = css.replace(
    b'color: #64748b; }',
    b'color: var(--border-2); }'
)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/shared_style.css.php', 'wb') as f:
    f.write(css)

print("shared_style.css.php: OK")
