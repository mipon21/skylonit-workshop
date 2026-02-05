#!/usr/bin/env python3
import re

with open('Copy of Copy of Skylon-IT Agreement For FlashBDTopUp.svg', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

print("File length:", len(content))
print("\nFirst 1500 chars:")
print(repr(content[:1500]))
print("\n--- Searching for structure ---")

# Find SVG tags
for tag in ['<text', '<tspan', '<image ', '<path ', '<rect', '<g ', 'xlink:href']:
    count = content.count(tag)
    print(f"  {tag}: {count} occurrences")
    if count > 0 and count < 20:
        idx = content.find(tag)
        print(f"    First at {idx}: {repr(content[idx:idx+150])[:150]}")

# Check if it's base64 image
if 'data:image' in content or 'base64' in content:
    print("\nContains base64/image data: YES")
    idx = content.find('data:')
    if idx >= 0:
        print(f"  data: starts at {idx}, sample: {content[idx:idx+80]}")
else:
    print("\nContains base64/image data: NO")
