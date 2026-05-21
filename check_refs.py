import os
results = []
folder = 'C:/xampp/htdocs/SPAC/dashboards/barangay/'
for fname in os.listdir(folder):
    if fname.endswith('.php'):
        with open(folder + fname, 'r', encoding='utf-8', errors='replace') as f:
            content = f.read()
        if 'statistics.php' in content:
            results.append(fname)

print("Files referencing statistics.php:", results)
