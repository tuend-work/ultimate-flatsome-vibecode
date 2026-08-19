# -*- coding: utf-8 -*-
import urllib.request
import re
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

url = 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/?nocache=99999'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

print("=== CHECKING STYLE TAG INTEGRITY ===")
styles = re.findall(r'<style[^>]*>(.*?)</style>', html, re.DOTALL)
for s in styles:
    if 'hlhv-wrap' in s:
        print(' - Contaminated with <p> or <br>:', '<p' in s or '<br' in s)
        print(' - First 150 chars of CSS:', s[:150])

print("\n=== CHECKING IMAGE TAGS ===")
imgs = re.findall(r'<img[^>]+src=[\'"]([^\'"]*)[\'"]', html)
print(f'Total img tags found: {len(imgs)}')
empty_imgs = [src for src in imgs if not src]
print(f'Empty img tags: {len(empty_imgs)}')
for i, img in enumerate(imgs):
    print(f"Image {i+1}: {img}")
