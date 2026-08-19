# -*- coding: utf-8 -*-
import sys
import re
import json

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_main_content.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's inspect each section content
sections = re.findall(r'<section\b[^>]*>([\s\S]*?)</section>', html, re.IGNORECASE)

print(f"Total sections: {len(sections)}")

for i, s in enumerate(sections):
    print(f"\n==================== SECTION {i+1} ====================")
    # Print first 1500 chars
    print(s[:1500])
