#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Media Sync Tool
Quét toàn bộ hình ảnh từ HTML / URL nguồn và tải lên WordPress Media Library qua REST API
"""

import os
import sys
import json
import re
import urllib.request
import urllib.parse
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

def sync_media(source_url, output_dir=None):
    root_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..'))
    config = load_config(root_dir)
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')

    if not output_dir:
        slug = re.sub(r'[^a-zA-Z0-9\-]', '', source_url.split('/')[-2] if source_url.endswith('/') else source_url.split('/')[-1]) or 'landingpage'
        output_dir = os.path.join('tmp', slug)

    os.makedirs(output_dir, exist_ok=True)
    media_map_file = os.path.join(output_dir, 'media_map.json')
    html_file = os.path.join(output_dir, 'source.html')

    # 1. Fetch HTML if not present
    if not os.path.exists(html_file):
        print(f"📡 Đang tải mã nguồn từ: {source_url}")
        req = urllib.request.Request(source_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'})
        with urllib.request.urlopen(req, timeout=25) as resp:
            html = resp.read().decode('utf-8', errors='ignore')
        with open(html_file, 'w', encoding='utf-8') as f:
            f.write(html)
    else:
        with open(html_file, 'r', encoding='utf-8') as f:
            html = f.read()

    # 2. Extract image URLs
    img_urls = set()
    for src in re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"]', html, re.IGNORECASE):
        if src and not src.startswith('data:'):
            img_urls.add(urllib.parse.urljoin(source_url, src))

    for dsrc in re.findall(r'data-(?:src|lazy-src|original)=[\'"]([^\'"]+)[\'"]', html, re.IGNORECASE):
        if dsrc and not dsrc.startswith('data:'):
            img_urls.add(urllib.parse.urljoin(source_url, dsrc))

    for bg in re.findall(r'url\([\'"]?(https?:\/\/[^\'")]+|\/[^\'")]+)[\'"]?\)', html, re.IGNORECASE):
        if bg and not bg.startswith('data:') and not bg.endswith('.css') and not bg.endswith('.js'):
            img_urls.add(urllib.parse.urljoin(source_url, bg))

    print(f"🔍 Phát hiện {len(img_urls)} hình ảnh cần đồng bộ lên WordPress.")

    media_map = {}
    if os.path.exists(media_map_file):
        try:
            with open(media_map_file, 'r', encoding='utf-8') as f:
                media_map = json.load(f)
        except Exception:
            media_map = {}

    upload_endpoint = f"{api_url}/vbc/v1/upload"
    check_media_endpoint = f"{api_url}/vbc/v1/check-media"
    count = 0

    # 3. Batch Check existing media on WordPress Media Library via /vbc/v1/check-media
    url_to_filename = {}
    filenames_to_check = []
    for img_url in img_urls:
        if img_url in media_map and media_map[img_url] and ('wpcloud.vn' in media_map[img_url] or '/wp-content/uploads/' in media_map[img_url]):
            continue
        fname = os.path.basename(urllib.parse.urlparse(img_url).path) or f"img_{abs(hash(img_url))}.jpg"
        if not re.search(r'\.(jpg|jpeg|png|webp|svg|gif)$', fname, re.IGNORECASE):
            fname += '.jpg'
        url_to_filename[img_url] = fname
        filenames_to_check.append(fname)

    checked_results = {}
    if filenames_to_check:
        print(f"⚡ Đang kiểm tra nhanh {len(filenames_to_check)} ảnh trên WordPress Media Library...")
        try:
            check_payload = json.dumps({"filenames": filenames_to_check}).encode('utf-8')
            check_req = urllib.request.Request(
                check_media_endpoint,
                data=check_payload,
                headers={
                    'Content-Type': 'application/json',
                    'X-VBC-Token': token,
                    'User-Agent': 'Mozilla/5.0'
                }
            )
            with urllib.request.urlopen(check_req, timeout=15) as resp:
                check_res = json.loads(resp.read().decode('utf-8'))
                checked_results = check_res.get('results', {})
                found_cnt = check_res.get('found_count', 0)
                print(f" -> ✅ Đã tìm thấy {found_cnt}/{len(filenames_to_check)} ảnh đã có sẵn trên WordPress!")
        except Exception as e:
            print(f" -> [WARN] Lỗi khi gọi /vbc/v1/check-media: {e}. Sẽ kiểm tra từng ảnh.")

    # 4. Process each image: use existing or upload new
    for img_url, filename in url_to_filename.items():
        # Check if batch check found it
        if filename in checked_results and checked_results[filename].get('exists'):
            wp_url = checked_results[filename].get('url')
            media_map[img_url] = wp_url
            count += 1
            print(f" -> [{count}] Tái sử dụng (đã có trên WP): {filename} => {wp_url}")
            continue

        local_img_path = os.path.join(output_dir, filename)

        try:
            # Download về local nếu chưa có
            if not os.path.exists(local_img_path):
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(req, timeout=15) as resp:
                    with open(local_img_path, 'wb') as f:
                        f.write(resp.read())

            with open(local_img_path, 'rb') as f:
                img_data = f.read()

            # Upload lên WP
            ext = os.path.splitext(filename)[1].lower()
            content_type_map = {'.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
                                '.webp': 'image/webp', '.svg': 'image/svg+xml', '.gif': 'image/gif'}
            content_type = content_type_map.get(ext, 'image/jpeg')

            upload_req = urllib.request.Request(
                upload_endpoint,
                data=img_data,
                headers={
                    'Content-Type': content_type,
                    'X-File-Name': filename,
                    'X-VBC-Token': token
                }
            )

            with urllib.request.urlopen(upload_req, timeout=30) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                wp_url = data.get('url') or data.get('source_url')
                if wp_url:
                    media_map[img_url] = wp_url
                    count += 1
                    print(f" -> [{count}] Đã upload mới: {filename} => {wp_url}")
        except Exception as e:
            media_map[img_url] = img_url
            print(f" -> [WARN] Lỗi khi xử lý {filename}: {e}")

    with open(media_map_file, 'w', encoding='utf-8') as f:
        json.dump(media_map, f, ensure_ascii=False, indent=2)

    print(f"\n🎉 Đã đồng bộ xong {len(media_map)} hình ảnh. Ánh xạ lưu tại: {media_map_file}")
    return media_map_file

def main():
    parser = argparse.ArgumentParser(description="VibeCode Media Sync")
    parser.add_argument("--url", required=True, help="URL trang nguồn cần clone")
    parser.add_argument("--output_dir", help="Thư mục lưu media map")
    args = parser.parse_args()
    sync_media(args.url, args.output_dir)

if __name__ == "__main__":
    main()
