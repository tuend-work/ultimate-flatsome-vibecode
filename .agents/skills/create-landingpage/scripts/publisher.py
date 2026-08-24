#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Page Publisher Tool for Create Landing Page
Đẩy nội dung VBC Elements đã được AI thiết kế lên WordPress qua REST API (/vbc/v1/page)
"""

import os
import sys
import json
import urllib.request
import argparse

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

def load_config(root_dir):
    candidates = [
        os.path.join(root_dir, 'vbc-config.json'),
        os.path.join(root_dir, '..', 'vbc-config.json'),
        os.path.join(root_dir, '..', '..', 'vbc-config.json'),
        os.path.join(os.getcwd(), 'vbc-config.json')
    ]
    for c in candidates:
        if os.path.exists(c):
            with open(c, 'r', encoding='utf-8') as f:
                return json.load(f)
    raise FileNotFoundError("Không tìm thấy tệp vbc-config.json chứa thông tin cấu hình WordPress.")

def publish_page(title, slug, content_file_or_text, post_id=None, template="page-blank.php", status="publish"):
    root_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..'))
    config = load_config(root_dir)
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')

    if os.path.exists(content_file_or_text):
        with open(content_file_or_text, 'r', encoding='utf-8') as f:
            content = f.read()
    else:
        content = content_file_or_text

    # Clean header reset CSS if not present
    if '#header' not in content:
        reset_prefix = "<style>#header, #footer, .header-wrapper, #wrapper > footer { display: none !important; } body { padding-top: 0 !important; margin: 0 !important; background: #ffffff !important; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; } #main { padding-top: 0 !important; padding-bottom: 0 !important; }</style>\n\n"
        content = reset_prefix + content

    payload = {
        'title': title,
        'slug': slug,
        'content': content,
        'status': status,
        'template': template
    }
    if post_id:
        payload['post_id'] = int(post_id)

    print(f"\n🚀 Đang xuất bản Landing Page '{title}' lên WordPress ({api_url}/vbc/v1/page)...")
    req = urllib.request.Request(
        f"{api_url}/vbc/v1/page",
        data=json.dumps(payload).encode('utf-8'),
        headers={'Content-Type': 'application/json', 'X-VBC-Token': token}
    )

    with urllib.request.urlopen(req, timeout=30) as resp:
        res_data = json.loads(resp.read().decode('utf-8'))
        pub_id = res_data.get('post_id') or res_data.get('id')
        pub_url = res_data.get('url') or res_data.get('link')

        print("============================================================")
        print("🎉 XUẤT BẢN LANDING PAGE THÀNH CÔNG!")
        print("============================================================")
        print(f"Post ID        : {pub_id}")
        print(f"Live Page URL  : {pub_url}")
        print("============================================================\n")
        return pub_id, pub_url

def main():
    parser = argparse.ArgumentParser(description="VibeCode Publisher")
    parser.add_argument("--title", required=True, help="Tiêu đề trang Landing Page")
    parser.add_argument("--slug", required=True, help="Đường dẫn slug trang")
    parser.add_argument("--content", required=True, help="File nội dung shortcode VBC hoặc chuỗi trực tiếp")
    parser.add_argument("--post_id", type=int, help="ID bài viết cập nhật (nếu có)")
    parser.add_argument("--template", default="page-blank.php", help="Page template")
    parser.add_argument("--status", default="publish", help="Trạng thái bài viết")

    args = parser.parse_args()
    publish_page(args.title, args.slug, args.content, args.post_id, args.template, args.status)

if __name__ == "__main__":
    main()
