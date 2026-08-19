# -*- coding: utf-8 -*-
import urllib.request
import re
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

url = 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/?nocache=3333'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

match = re.search(r'<section id="tuyen-duong".*?</section>', html, re.DOTALL)
if match:
    print("=== TUYEN DUONG SECTION HTML ===")
    print(match.group(0)[:2000])

match_body = re.search(r'<div class="entry-content[^"]*">(.*?)</div>\s*<!-- \.entry-content -->', html, re.DOTALL)
if match_body:
    print("\n=== ENTRY CONTENT FIRST 1000 CHARS ===")
    print(match_body.group(1)[:1000])
