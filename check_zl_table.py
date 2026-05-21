import subprocess
result = subprocess.run(
    ['C:/xampp/mysql/bin/mysql.exe', '-u', 'root', '-e', 'DESCRIBE zone_leaders;', 'spac_db'],
    capture_output=True, text=True
)
print(result.stdout)
print(result.stderr)
