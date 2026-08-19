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

# Extract all images
img_pattern = re.compile(r'<img[^>]+(?:src|data-src|data-lazy-src)=["\']([^"\'\s>]+)["\']', re.IGNORECASE)
images = list(set(img_pattern.findall(html)))
print(f'Total unique images found: {len(images)}')
for i, img in enumerate(sorted(images)):
    print(f'  {i+1}: {img}')

# Extract background images from style or css
bg_pattern = re.compile(r'url\(["\']?([^"\'\)]+\.(?:png|jpg|jpeg|webp|svg|gif)[^"\'\)]*)["\']?\)', re.IGNORECASE)
bg_images = list(set(bg_pattern.findall(html)))
print(f'\nTotal background images found: {len(bg_images)}')
for i, bg in enumerate(sorted(bg_images)):
    print(f'  {i+1}: {bg}')

# Extract all headings
heading_pattern = re.compile(r'<(h[1-6])[^>]*>(.*?)</\1>', re.IGNORECASE | re.DOTALL)
headings = heading_pattern.findall(html)
print('\nHeadings found:')
for tag, text in headings:
    clean_text = re.sub(r'<[^>]+>', ' ', text).strip()
    clean_text = re.sub(r'\s+', ' ', clean_text)
    if clean_text:
        print(f'[{tag}]: {clean_text}')
