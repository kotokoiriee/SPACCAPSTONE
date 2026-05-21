with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'rb') as f:
    content = f.read()

fn = b"""
function showLoadingAndGo(url, btn) {
    document.querySelectorAll('.menu-item').forEach(function(l){ l.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(255,255,255,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;';
    overlay.innerHTML = '<div style="text-align:center"><div style="width:24px;height:24px;border:2px solid #e2e8f0;border-top-color:#0f172a;border-radius:50%;animation:spin 0.6s linear infinite;margin:0 auto"></div><div style="font-size:12px;color:#64748b;margin-top:8px">Loading...</div></div>';
    if (!document.getElementById('spin-style')) {
        var s = document.createElement('style');
        s.id = 'spin-style';
        s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(s);
    }
    document.body.appendChild(overlay);
    window.location.href = url;
}
"""

# Insert right before the closing </script> tag
content = content.replace(b'</script>', fn + b'</script>', 1)

with open('C:/xampp/htdocs/SPAC/dashboards/barangay/index.php', 'wb') as f:
    f.write(content)

# Verify
idx = content.find(b'function showLoadingAndGo')
print("function defined:", idx >= 0)
print("OK" if idx >= 0 else "FAILED")
