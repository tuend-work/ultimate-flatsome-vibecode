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
url = f"{config['api-url']}/vbc/v1/page?post_id=479"
req = urllib.request.Request(url, headers={'X-VBC-Token': config['token'], 'User-Agent': 'Mozilla/5.0'})
data = json.loads(urllib.request.urlopen(req).read().decode('utf-8'))
content = data.get('content', '')
print('Total content length:', len(content))
print('\nSample VBC elements in post_content:')
print(content[content.find('</style>')+8:content.find('</style>')+1200])
