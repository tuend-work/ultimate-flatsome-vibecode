# -*- coding: utf-8 -*-
import sys
import re

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_raw.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Find link rel="stylesheet"
css_links = re.findall(r'<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+)["\']', html, re.IGNORECASE)
print(f"Total stylesheet links: {len(css_links)}")
for l in css_links:
    print("  CSS link:", l)

# Look for custom-css in style tags
for i, block in enumerate(re.findall(r'<style\b[^>]*>([\s\S]*?)</style>', html, re.IGNORECASE)):
    if len(block.strip()) > 200:
        print(f"\nStyle block {i+1} (len {len(block)}):")
        print(block[:500])
