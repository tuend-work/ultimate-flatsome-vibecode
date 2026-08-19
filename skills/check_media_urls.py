# -*- coding: utf-8 -*-
import urllib.request
import json
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_media_map.json', 'r', encoding='utf-8') as f:
    media_map = json.load(f)

for k, v in media_map.items():
    if any(x in k for x in ['xe-du-lich', 'gio-chay', 'nha-xe', 'gui-xe', 'van-chuyen']):
        try:
            req = urllib.request.Request(v['url'], headers={'User-Agent': 'Mozilla/5.0'})
            res = urllib.request.urlopen(req)
            print(f"OK ({res.status}): {v['url']}")
        except Exception as e:
            print(f"ERR ({e}): {v['url']}")
