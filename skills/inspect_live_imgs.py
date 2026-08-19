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

html = urllib.request.urlopen('https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/').read().decode('utf-8')
imgs = re.findall(r'<img[^>]+class=[\'"][^\'"]*hlhv[^\'"]*[\'"][^>]*>', html)
print(f"Total hlhv imgs found on page: {len(imgs)}")
for i, img in enumerate(imgs[:10]):
    print(f"{i+1}: {img}")
