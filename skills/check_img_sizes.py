# -*- coding: utf-8 -*-
import urllib.request
import json
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

config = json.load(open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8'))
media_map = json.load(open('hoanglong_media_map.json', 'r', encoding='utf-8'))

for k in ['xe-du-lich-bac-nam-1.png', 'xe-du-lich-bac-nam.png', 'gui-xe-may-hoang-long-hai-van.png', 'gio-chay-xe-ha-noi-sai-gon.png', 'nha-xe-ha-noi-sai-gon.png', 'xe-khach-bac-nam-hoang-long-hai-van-1.png']:
    for mk, mv in media_map.items():
        if k in mk:
            att_id = mv['id']
            url = f"{config['api-url']}/wp/v2/media/{att_id}"
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
            data = json.loads(urllib.request.urlopen(req).read().decode('utf-8'))
            print(f"=== {k} ===")
            sizes = data.get('media_details', {}).get('sizes', {})
            # choose best size around 600-1024 width
            best_url = mv['url']
            if 'large' in sizes:
                best_url = sizes['large']['source_url']
            elif 'medium_large' in sizes:
                best_url = sizes['medium_large']['source_url']
            elif 'medium' in sizes:
                best_url = sizes['medium']['source_url']
            print(f"BEST URL: {best_url}")
            for sname, sinfo in sizes.items():
                print(f"   {sname} ({sinfo.get('width')}x{sinfo.get('height')}): {sinfo['source_url']}")
