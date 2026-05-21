with open("C:/xampp/htdocs/SPAC/dashboards/barangay/index.php", "r", encoding="utf-8", errors="replace") as f:
    content = f.read()

# Remove the stray \n\n text that appears in the content area
content = content.replace("\\n\\n\n        <!-- STATISTICS", "\n        <!-- STATISTICS")
content = content.replace("\\n\\n\n        <!-- IMPORT", "\n        <!-- IMPORT")
content = content.replace("\\n\\n\n        <!-- MANAGE", "\n        <!-- MANAGE")

# Also check if its literal \n\n in the HTML output
count = content.count("\\n\\n")
print("remaining literal \\n\\n count:", count)

# Find and show them
idx = 0
for i in range(min(count, 5)):
    idx = content.find("\\n\\n", idx)
    if idx >= 0:
        print(f"at {idx}:", repr(content[idx-30:idx+30]))
        idx += 1
