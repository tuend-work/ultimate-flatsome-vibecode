# -*- coding: utf-8 -*-
import sys
import os
import json
import urllib.request
import uuid
import re

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

def load_config():
    with open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8') as f:
        return json.load(f)

config = load_config()
api_url = config['api-url'].rstrip('/') + '/vbc/v1/upload'
token = config['token']

with open('hoanglong_raw.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Collect all image URLs
img_pattern = re.compile(r'<img[^>]+(?:src|data-src|data-lazy-src)=["\']([^"\'\s>]+)["\']', re.IGNORECASE)
images = set(img_pattern.findall(html))

# Also add background images
bg_pattern = re.compile(r'url\(["\']?([^"\'\)]+\.(?:png|jpg|jpeg|webp|svg|gif)[^"\'\)]*)["\']?\)', re.IGNORECASE)
for bg in bg_pattern.findall(html):
    images.add(bg)

# Explicit essential assets
images.add('https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/banner-hlhv.jpg')
images.add('https://hoanglonghaivanexpress.com/wp-content/uploads/2026/06/logo-hoang-long-hai-van-1024x1024.jpg')
images.add('https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg')

os.makedirs('temp_hoanglong_media', exist_ok=True)

media_map = {} # original -> {url, id}

print(f"Total media assets to process: {len(images)}")

for i, img_url in enumerate(sorted(images)):
    if not img_url or img_url.startswith('data:'):
        continue
    
    full_url = img_url
    if full_url.startswith('/'):
        full_url = 'https://hoanglonghaivanexpress.com' + full_url
    elif not full_url.startswith('http'):
        full_url = 'https://hoanglonghaivanexpress.com/' + full_url.lstrip('/')

    # Determine filename
    filename = os.path.basename(urllib.parse.urlparse(full_url).path) or f"asset_{i+1}.png"
    if not os.path.splitext(filename)[1]:
        filename += '.png'
    
    local_path = os.path.join('temp_hoanglong_media', filename)

    try:
        # Download
        req = urllib.request.Request(full_url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=15) as res:
            with open(local_path, 'wb') as out:
                out.write(res.read())

        # Upload to WordPress
        boundary = uuid.uuid4().hex
        with open(local_path, 'rb') as f:
            file_bytes = f.read()

        ext = os.path.splitext(filename)[1].lower()
        mime_types = {
            '.jpg': 'image/jpeg',
            '.jpeg': 'image/jpeg',
            '.png': 'image/png',
            '.gif': 'image/gif',
            '.webp': 'image/webp',
            '.svg': 'image/svg+xml'
        }
        content_type = mime_types.get(ext, 'application/octet-stream')

        body = (
            f'--{boundary}\r\n'
            f'Content-Disposition: form-data; name="file"; filename="{filename}"\r\n'
            f'Content-Type: {content_type}\r\n\r\n'
        ).encode('utf-8') + file_bytes + f'\r\n--{boundary}--\r\n'.encode('utf-8')

        up_req = urllib.request.Request(
            api_url,
            data=body,
            headers={
                'Content-Type': f'multipart/form-data; boundary={boundary}',
                'X-VBC-Token': token
            },
            method='POST'
        )

        with urllib.request.urlopen(up_req, timeout=20) as up_res:
            res_data = json.loads(up_res.read().decode('utf-8'))
            if res_data.get('success'):
                media_map[img_url] = {
                    'url': res_data['url'],
                    'id': res_data.get('attachment_id') or res_data.get('id')
                }
                media_map[full_url] = media_map[img_url]
                print(f"[{i+1}/{len(images)}] ✓ Uploaded {filename} -> ID {media_map[img_url]['id']}")
            else:
                print(f"[{i+1}/{len(images)}] ✗ Upload error: {res_data}")

    except Exception as e:
        print(f"[{i+1}/{len(images)}] ⚠ Failed {filename} ({full_url}): {e}")

with open('hoanglong_media_map.json', 'w', encoding='utf-8') as f:
    json.dump(media_map, f, ensure_ascii=False, indent=2)

print(f"\nCompleted! Saved {len(media_map)} mappings to hoanglong_media_map.json")
