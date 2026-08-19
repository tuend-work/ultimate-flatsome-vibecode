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

with open('madamehuong_raw.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's extract text blocks and products
print("--- HEADINGS ---")
for h in re.finditer(r'<(h[1-6])[^>]*>(.*?)</\1>', html, re.I | re.S):
    tag = h.group(1)
    text = re.sub(r'<[^>]+>', '', h.group(2)).strip()
    if text:
        print(f"[{tag}] {text}")

print("\n--- PRODUCTS / ITEMS ---")
# Let's extract product sections
product_blocks = re.findall(r'<div[^>]*class="[^"]*product-layout[^"]*"[^>]*>(.*?)</div>\s*</div>', html, re.S)
if not product_blocks:
    # Alternative search for product items
    product_blocks = re.findall(r'<div[^>]*class="[^"]*product-thumb[^"]*"[^>]*>(.*?)</div>\s*</div>', html, re.S)

print(f"Found {len(product_blocks)} product blocks")

# Let's search for price patterns
prices = re.findall(r'(\d{1,3}(?:\.\d{3})+(?:\s*đ|\s*VND)?)', html)
print(f"Found {len(prices)} price occurrences:", prices[:15])

# Print sample text chunks from body
body_m = re.search(r'<body[^>]*>(.*?)</body>', html, re.I | re.S)
if body_m:
    body_text = re.sub(r'<script[^>]*>.*?</script>', '', body_m.group(1), flags=re.S)
    body_text = re.sub(r'<style[^>]*>.*?</style>', '', body_text, flags=re.S)
    # clean tags
    cleaned = re.sub(r'<[^>]+>', '\n', body_text)
    cleaned = '\n'.join([line.strip() for line in cleaned.splitlines() if line.strip()])
    with open('madamehuong_text.txt', 'w', encoding='utf-8') as f:
        f.write(cleaned)
    print("Saved text to madamehuong_text.txt. Lines:", len(cleaned.splitlines()))
