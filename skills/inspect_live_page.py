# -*- coding: utf-8 -*-
import urllib.request
import re
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

url = 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

print("=== BODY CLASSES ===")
body_match = re.search(r'<body[^>]*class=[\'"]([^\'"]+)[\'"]', html)
if body_match:
    print(body_match.group(1))

print("\n=== HERO SECTION HTML (first 500 chars) ===")
hero_match = re.search(r'class=[\'"][^\'"]*hlhv-hero[^\'"]*[\'"].*?(?=class=[\'"][^\'"]*hlhv-booking)', html, re.DOTALL)
if hero_match:
    print(hero_match.group(0)[:500])

print("\n=== STYLESHEET OR INLINE CSS ===")
styles = re.findall(r'<style[^>]*>(.*?)</style>', html, re.DOTALL)
print(f"Total style tags: {len(styles)}")
for i, s in enumerate(styles):
    if 'hlhv' in s:
        print(f"Style tag {i+1} containing hlhv (first 600 chars):\n{s[:600]}\n")
