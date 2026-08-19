# -*- coding: utf-8 -*-
import sys
import os
import json
import urllib.request
import urllib.parse
import uuid

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

# Let's test by downloading logo and uploading
config = load_config()
os.makedirs('temp_assets', exist_ok=True)
logo_url = 'https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/logo-70x70.png'
logo_local = 'temp_assets/logo-70x70.png'

req = urllib.request.Request(logo_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as r, open(logo_local, 'wb') as f:
    f.write(r.read())

print("Downloaded logo:", os.path.getsize(logo_local), "bytes")
res = upload_file_to_wp(logo_local, 'madamehuong-logo.png', config)
print("Uploaded logo:", res)
