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
    wp_api_base = api_url.replace('/wp-json/vbc/v1', '') if '/wp-json/vbc/v1' in api_url else api_url.rsplit('/vbc/v1', 1)[0]
    count = 0

    def check_existing_wp_media(filename):
        """Kiểm tra ảnh đã tồn tại trên WP Media Library theo tên file. Trả về URL nếu tìm thấy, None nếu chưa."""
        search_name = os.path.splitext(filename)[0]  # bỏ extension để tìm rộng hơn
        search_url = f"{wp_api_base}/wp-json/wp/v2/media?search={urllib.parse.quote(search_name)}&per_page=5"
        try:
            req = urllib.request.Request(search_url, headers={
                'User-Agent': 'Mozilla/5.0',
                'X-VBC-Token': token
            })
            with urllib.request.urlopen(req, timeout=10) as resp:
                items = json.loads(resp.read().decode('utf-8'))
                for item in items:
                    src = item.get('source_url', '')
                    # Khớp tên file chính xác (bỏ qua -1, -2 suffix)
                    src_base = os.path.splitext(os.path.basename(src))[0]
                    if src_base == search_name or src_base.startswith(search_name + '-'):
                        return src
        except Exception:
            pass
        return None

    for img_url in img_urls:
        if img_url in media_map and media_map[img_url] and not media_map[img_url].startswith('http://') or img_url in media_map and media_map[img_url] and 'wpcloud.vn' in media_map[img_url]:
            continue

        filename = os.path.basename(urllib.parse.urlparse(img_url).path) or f"img_{abs(hash(img_url))}.jpg"
        if not re.search(r'\.(jpg|jpeg|png|webp|svg|gif)$', filename, re.IGNORECASE):
            filename += '.jpg'

        local_img_path = os.path.join(output_dir, filename)

        try:
            # ✅ Bước 1: Kiểm tra đã tồn tại trên WP Media Library chưa
            existing_url = check_existing_wp_media(filename)
            if existing_url:
                media_map[img_url] = existing_url
                count += 1
                print(f" -> [{count}] Tái sử dụng (đã tồn tại): {filename} => {existing_url}")
                continue

            # ✅ Bước 2: Download về local nếu chưa có
            if not os.path.exists(local_img_path):
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(req, timeout=15) as resp:
                    with open(local_img_path, 'wb') as f:
                        f.write(resp.read())

            with open(local_img_path, 'rb') as f:
                img_data = f.read()

            # ✅ Bước 3: Upload lên WP
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
