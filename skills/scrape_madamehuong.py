# -*- coding: utf-8 -*-
import sys
import os
import json
import re
import urllib.request
import urllib.parse

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

url = 'https://www.banhtrungthu-madamehuong.vn/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'})
html = urllib.request.urlopen(req).read().decode('utf-8', errors='ignore')

# Save raw HTML to file for detailed analysis
with open('madamehuong_raw.html', 'w', encoding='utf-8') as f:
    f.write(html)

print("Saved raw HTML. Length:", len(html))

# Extract all images
pattern = r'(?:src|data-src|data-original|background-image:\s*url\()[\s=\'\"]*([^\s\'\"\)\>]+\.(?:png|jpg|jpeg|webp|svg|gif))'
images = set()
for m in re.finditer(pattern, html, re.I):
    src = m.group(1)
    if src.startswith('//'):
        src = 'https:' + src
    elif not src.startswith('http'):
        src = urllib.parse.urljoin(url, src)
    images.add(src)

print(f"Total {len(images)} images found:")
for img in sorted(list(images)):
    print(" -", img)
