with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

js_to_add = b"""
function showLoadingAndGo(url, btn) {
    // Highlight the clicked button instantly
    document.querySelectorAll('.menu-item').forEach(function(l){ l.classList.remove('active'); });
    if (btn) btn.classList.add('active');

    // Show a loading overlay so it feels instant
    var overlay = document.createElement('div');
    overlay.id = 'nav-loading-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(255,255,255,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;';
    overlay.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:10px">' +
        '<div style="width:24px;height:24px;border:2px solid var(--border);border-top-color:var(--navy);border-radius:50%;animation:spin 0.6s linear infinite"></div>' +
        '<div style="font-size:12px;color:var(--muted)">Loading...</div></div>';

    // Add spin animation if not already present
    if (!document.getElementById('spin-style')) {
        var style = document.createElement('style');
        style.id = 'spin-style';
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    document.body.appendChild(overlay);
    window.location.href = url;
}
"""

# Insert before closing </script> tag
content = content.replace(b'// \xe2\x80\x93\xe2\x80\x93 SESSION KEEPALIVE', js_to_add + b'// \xe2\x80\x93\xe2\x80\x93 SESSION KEEPALIVE')

# Fallback if encoding differs
if b'showLoadingAndGo' not in content:
    content = content.replace(b'function openEditOfficial', js_to_add + b'function openEditOfficial')

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

print("OK" if b'showLoadingAndGo' in content else "FAILED")
