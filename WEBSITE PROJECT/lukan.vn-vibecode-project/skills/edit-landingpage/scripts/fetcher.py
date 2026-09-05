# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Landing Page Content Fetcher
Tải nội dung shortcodes gốc và metadata từ WordPress REST API qua Post ID hoặc Slug hoặc URL
"""

import os
import sys
import json
import argparse
import urllib.request
import urllib.parse

# Đảm bảo stdout encoding UTF-8
sys.stdout.reconfigure(encoding='utf-8')

def load_config():
    # Tìm vbc-config.json trong thư mục hiện tại hoặc thư mục cha
    paths = ['vbc-config.json', '../vbc-config.json', '../../vbc-config.json']
    for p in paths:
        if os.path.exists(p):
            with open(p, 'r', encoding='utf-8') as f:
                return json.load(f)
    return {}

def fetch_page(post_id=None, slug=None, url=None, output_path=None):
    config = load_config()
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')

    if not api_url or not token:
        print("[ERROR] Không tìm thấy 'api-url' hoặc 'token' trong vbc-config.json!")
        sys.exit(1)

    params = {}
    if post_id:
        params['post_id'] = post_id
    elif slug:
        params['slug'] = slug
    elif url:
        # Extract slug from URL if possible
        parsed = urllib.parse.urlparse(url)
        path_slug = parsed.path.strip('/').split('/')[-1]
        if path_slug:
            params['slug'] = path_slug
        else:
            print("[ERROR] Không trích xuất được slug từ URL:", url)
            sys.exit(1)
    else:
        print("[ERROR] Vui lòng cung cấp ít nhất một trong các tham số: --post_id, --slug hoặc --url!")
        sys.exit(1)

    query_str = urllib.parse.urlencode(params)
    endpoint = f"{api_url}/vbc/v1/page?{query_str}"

    print(f"📥 Đang tải nội dung trang từ WordPress ({endpoint})...")

    req = urllib.request.Request(
        endpoint,
        headers={
            'X-VBC-Token': token,
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) VibeCode-Fetcher/2.0',
            'Accept': 'application/json'
        }
    )

    try:
        with urllib.request.urlopen(req, timeout=25) as resp:
            data = json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        print(f"[ERROR] HTTP Error {e.code}: {e.read().decode('utf-8', errors='ignore')}")
        sys.exit(1)
    except Exception as e:
        print(f"[ERROR] Không kết nối được tới REST API: {e}")
        sys.exit(1)

    if not data.get('success'):
        print(f"[ERROR] API trả về lỗi: {data.get('message', 'Không tìm thấy trang')}")
        sys.exit(1)

    p_id = data.get('id') or data.get('post_id')
    p_title = data.get('title', 'Untitled')
    p_slug = data.get('slug', 'trang')
    p_content = data.get('post_content', '')
    p_css = data.get('custom_css', '')
    p_template = data.get('template', 'default')
    p_uxb = data.get('ux_builder_url', '')
    p_url = data.get('url', '')

    if not output_path:
        os.makedirs(f"tmp/{p_slug}", exist_ok=True)
        output_path = f"tmp/{p_slug}/original_vbc.txt"
    else:
        os.makedirs(os.path.dirname(os.path.abspath(output_path)), exist_ok=True)

    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(p_content)

    meta_path = output_path.replace('.txt', '_meta.json')
    with open(meta_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

    print("=" * 60)
    print("🎉 TẢI NỘI DUNG TRANG THÀNH CÔNG!")
    print("=" * 60)
    print(f"Post ID        : {p_id}")
    print(f"Tiêu đề        : {p_title}")
    print(f"Slug           : {p_slug}")
    print(f"Template       : {p_template}")
    print(f"Live URL       : {p_url}")
    print(f"UX Builder Link: {p_uxb}")
    print(f"Tổng ký tự     : {len(p_content):,} chars")
    print(f"File lưu trữ   : {output_path}")
    print(f"Metadata file  : {meta_path}")
    print("=" * 60)

    return data

def main():
    parser = argparse.ArgumentParser(description="Tải nội dung trang từ WordPress REST API để chỉnh sửa.")
    parser.add_argument('--post_id', type=int, help='ID của bài viết/trang WordPress')
    parser.add_argument('--slug', type=str, help='Slug của trang')
    parser.add_argument('--url', type=str, help='URL của trang')
    parser.add_argument('--output', type=str, help='Đường dẫn file lưu nội dung shortcodes tải về')
    args = parser.parse_args()

    fetch_page(post_id=args.post_id, slug=args.slug, url=args.url, output_path=args.output)

if __name__ == '__main__':
    main()
