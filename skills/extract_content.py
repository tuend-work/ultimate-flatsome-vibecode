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

with open('hoanglong_raw.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's extract the main content container
# Look for #content or main or section
main_match = re.search(r'<main[^>]*>([\s\S]*?)</main>', html, re.IGNORECASE)
if not main_match:
    main_match = re.search(r'<div id="content"[^>]*>([\s\S]*?)</div>\s*<!-- #content -->', html, re.IGNORECASE)

content_html = main_match.group(1) if main_match else html

print("Content HTML extracted length:", len(content_html))

# Let's save content_html to a file for easy reading
with open('hoanglong_main_content.html', 'w', encoding='utf-8') as f:
    f.write(content_html)

print("Saved to hoanglong_main_content.html")
