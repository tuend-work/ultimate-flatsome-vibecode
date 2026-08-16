# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Python 3 UTF-8 Page Publisher & Updater
"""

import sys
import os
import json
import urllib.request
import urllib.parse
import argparse

# Thiết lập console stdout/stderr luôn dùng UTF-8 trên Windows
if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

def load_config():
    config_path = os.path.join(os.path.dirname(__file__), '../vbc-config.json')
    if not os.path.exists(config_path):
        config_path = os.path.join(os.path.dirname(__file__), '../../ultimate-flatsome-vibecode-skill-dev/vbc-config.json')
    with open(config_path, 'r', encoding='utf-8') as f:
        return json.load(f)

def publish_page(title, slug, content_file, post_id=None, post_status='publish'):
    config = load_config()
    api_url = config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
    token = config.get('token', '')

    with open(content_file, 'r', encoding='utf-8') as f:
        content = f.read()

    endpoint = f"{api_url}/vbc/v1/page"

    payload = {
        'title': title,
        'slug': slug,
        'content': content,
        'status': post_status,
        'post_type': 'page'
    }
    if post_id:
        payload['post_id'] = int(post_id)

    data = json.dumps(payload, ensure_ascii=False).encode('utf-8')

    req = urllib.request.Request(endpoint, data=data, headers={
        'Content-Type': 'application/json; charset=utf-8',
        'X-VBC-Token': token
    })

    try:
        with urllib.request.urlopen(req) as resp:
            resp_data = json.loads(resp.read().decode('utf-8'))
            print("==================================================")
            print("   XUẤT BẢN TRANG WEB THÀNH CÔNG (UTF-8 CHUẨN)")
            print("==================================================")
            print(f"Post ID:   {resp_data.get('post_id')}")
            print(f"Action:    {resp_data.get('action')}")
            print(f"URL:       {resp_data.get('url')}")
            print("==================================================")
            return resp_data
    except urllib.error.HTTPError as e:
        err_msg = e.read().decode('utf-8', errors='ignore')
        print(f"[LỖI HTTP {e.code}]: {err_msg}", file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(f"[LỖI]: {str(e)}", file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Publish/Update WordPress page via VBC REST API')
    parser.add_argument('--title', required=True, help='Tiêu đề trang')
    parser.add_argument('--slug', required=True, help='Đường dẫn slug')
    parser.add_argument('--file', required=True, help='Đường dẫn tệp shortcode')
    parser.add_argument('--post-id', type=int, help='ID bài viết nếu cần cập nhật')
    parser.add_argument('--status', default='publish', help='Trạng thái bài viết')

    args = parser.parse_args()
    publish_page(args.title, args.slug, args.file, args.post_id, args.status)
