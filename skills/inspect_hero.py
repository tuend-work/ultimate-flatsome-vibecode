# -*- coding: utf-8 -*-
import sys
import re

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_main_content.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Split at first section
idx = html.find('<section')
if idx != -1:
    hero_part = html[:idx]
    print("Hero part length:", len(hero_part))
    print("\n--- HERO PART HTML ---")
    print(hero_part[:4000])
else:
    print("No section found")
