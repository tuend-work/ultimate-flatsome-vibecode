# -*- coding: utf-8 -*-
import sys
import os
import json
import urllib.request
import urllib.parse
import uuid
import time

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

def load_config():
    with open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8') as f:
        return json.load(f)

def upload_file_to_wp(file_path, filename, config):
    boundary = uuid.uuid4().hex
    api_url = config['api-url'].rstrip('/') + '/vbc/v1/upload'
    token = config['token']

    with open(file_path, 'rb') as f:
        file_bytes = f.read()

    body = (
        f'--{boundary}\r\n'
        f'Content-Disposition: form-data; name="file"; filename="{filename}"\r\n'
        f'Content-Type: application/octet-stream\r\n\r\n'
    ).encode('utf-8') + file_bytes + f'\r\n--{boundary}--\r\n'.encode('utf-8')

    req = urllib.request.Request(
        api_url,
        data=body,
        headers={
            'Content-Type': f'multipart/form-data; boundary={boundary}',
            'X-VBC-Token': token
        }
    )

    with urllib.request.urlopen(req) as resp:
        return json.loads(resp.read().decode('utf-8'))

config = load_config()
os.makedirs('temp_assets', exist_ok=True)

# List of all images discovered
image_urls = [
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/logo-70x70.png",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/slide-1036x800.png",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/slide-1036x404w.png",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/background-5-626x417.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/bao-tro-600x170.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/catalog/images/phone.png",
    "https://www.banhtrungthu-madamehuong.vn/image/catalog/images/zalo.png",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/nguyen-lieu-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/sang-trong-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/hong-kong-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/lua-chon-uy-tin-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/an-nhien-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/le-na-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/yen-nhi-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/manh-tu-300x300.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-1-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-2-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-3-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-bai-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-bo-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-can-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-cot-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-dao-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-dau-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-duong-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-gai-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-khay-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-ma-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-thiec-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hop-vip-1-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hop-vip-2-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/lava-trung-chay-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/le-thanh-tong-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/ly-thuong-kiet-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/nguyen-du-pho-1000x883.jpg",
    "https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/phan-dinh-phung-pho-1000x883.jpg"
]

mapping = {}

print(f"Starting download and upload for {len(image_urls)} assets...")

for idx, url in enumerate(image_urls, 1):
    parsed = urllib.parse.urlparse(url)
    raw_name = os.path.basename(parsed.path)
    clean_name = f"madamehuong-{raw_name}"
    local_path = os.path.join('temp_assets', clean_name)
    
    try:
        # Download
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        with urllib.request.urlopen(req, timeout=15) as r, open(local_path, 'wb') as f:
            f.write(r.read())
        
        # Upload
        res = upload_file_to_wp(local_path, clean_name, config)
        if res.get('success'):
            mapping[url] = {
                'id': res.get('id'),
                'url': res.get('url'),
                'name': clean_name
            }
            print(f"[{idx}/{len(image_urls)}] Uploaded: {clean_name} -> ID: {res.get('id')} ({res.get('url')})")
        else:
            print(f"[{idx}/{len(image_urls)}] Error uploading {clean_name}: {res}")
    except Exception as e:
        print(f"[{idx}/{len(image_urls)}] Failed {url}: {e}")
    time.sleep(0.1)

with open('madamehuong_assets_mapping.json', 'w', encoding='utf-8') as f:
    json.dump(mapping, f, ensure_ascii=False, indent=2)

print(f"\nAll done! Total {len(mapping)}/{len(image_urls)} assets uploaded and mapped.")
