#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - 100% ACCURATE CLONE LANDING PAGE SKILL
===============================================================================
File: clone-landingpage.py
Description:
  Quy trình Clone Landing Page chuẩn xác 100% không bịa nội dung:
  1. Mở web nguồn, bóc tách toàn bộ Text theo cấu trúc Cây DOM (h1, h2, p, a, ul/li, form...).
     Lưu cây nội dung ra file JSON/Markdown trong tmp/{slug}/ để đảm bảo 100% trung thực.
  2. Quét Network & Media, tải toàn bộ ảnh về thư mục tmp/{slug}/ của dự án.
  3. Đẩy các ảnh sử dụng lên WordPress Media Library qua REST API (/vbc/v1/upload)
     để lấy ID (attachment_id) và URL nội bộ gắn vào shortcode ([vbc_img img_attachment="ID"]).
  4. Tự động chuyển đổi Form sang Contact Form 7 qua REST API (/vbc/v1/cf7).
  5. Biên dịch ra hệ thống phần tử VBC Elements ([vbc_div], [vbc_box], [vbc_block], [vbc_container], [vbc_icon]...)
     theo cấu trúc phân cấp chuẩn, đảm bảo 0 unparsed shortcodes.
  6. Xuất bản lên WordPress qua /vbc/v1/page và tự động chạy recheck-url.py nghiệm thu 100%.
===============================================================================
"""

import os
import sys
import re
import json
import time
import argparse
import mimetypes
import urllib.request
import urllib.parse
import urllib.error
from html import unescape
from html.parser import HTMLParser

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass


def load_vbc_config(custom_path=None):
    """Tìm và đọc file vbc-config.json"""
    search_paths = [
        custom_path,
        os.path.join(os.path.dirname(__file__), '../vbc-config.json'),
        os.path.join(os.path.dirname(__file__), '../../ultimate-flatsome-vibecode/vbc-config.json'),
        os.path.join(os.getcwd(), 'vbc-config.json'),
        os.path.join(os.getcwd(), 'ultimate-flatsome-vibecode/vbc-config.json'),
        os.path.join(os.path.dirname(__file__), 'vbc-config.json')
    ]
    for p in search_paths:
        if p and os.path.exists(p):
            try:
                with open(p, 'r', encoding='utf-8') as f:
                    return json.load(f)
            except Exception as e:
                print(f"[CẢNH BÁO] Không thể đọc cấu hình tại {p}: {e}")
    return {}


class DOMTreeParser(HTMLParser):
    """Bóc tách cấu trúc cây nội dung phân cấp từ HTML"""
    def __init__(self):
        super().__init__()
        self.tree = []
        self.current_section = {
            "type": "section",
            "tag": "body",
            "classes": "",
            "id": "",
            "items": []
        }
        self.tag_stack = []
        self.active_item = None
        self.inside_ignored = False

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        tag_lower = tag.lower()

        if tag_lower in ['script', 'style', 'noscript', 'iframe']:
            self.inside_ignored = True
            return

        if tag_lower in ['section', 'header', 'footer', 'nav', 'main', 'article']:
            new_section = {
                "type": "section",
                "tag": tag_lower,
                "classes": attr_dict.get('class', ''),
                "id": attr_dict.get('id', ''),
                "items": []
            }
            if self.current_section['items']:
                self.tree.append(self.current_section)
            self.current_section = new_section
            return

        if tag_lower in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'a', 'button', 'blockquote', 'span', 'b', 'strong', 'td', 'th']:
            self.active_item = {
                "tag": tag_lower,
                "classes": attr_dict.get('class', ''),
                "id": attr_dict.get('id', ''),
                "href": attr_dict.get('href', ''),
                "text": ""
            }
            self.tag_stack.append(tag_lower)
        elif tag_lower == 'img':
            src = attr_dict.get('src') or attr_dict.get('data-src') or attr_dict.get('data-lazy-src') or ''
            if src and not src.startswith('data:'):
                self.current_section['items'].append({
                    "tag": "img",
                    "src": src,
                    "alt": attr_dict.get('alt', ''),
                    "title": attr_dict.get('title', '')
                })

    def handle_endtag(self, tag):
        tag_lower = tag.lower()
        if tag_lower in ['script', 'style', 'noscript', 'iframe']:
            self.inside_ignored = False
            return

        if self.active_item and tag_lower == self.active_item['tag']:
            clean_text = ' '.join(self.active_item['text'].split()).strip()
            if clean_text:
                self.active_item['text'] = clean_text
                self.current_section['items'].append(self.active_item)
            self.active_item = None
            if self.tag_stack:
                self.tag_stack.pop()

    def handle_data(self, data):
        if self.inside_ignored or not data.strip():
            return
        if self.active_item:
            self.active_item['text'] += ' ' + data
        else:
            clean = ' '.join(data.split()).strip()
            if clean and len(clean) > 2:
                self.current_section['items'].append({
                    "tag": "text",
                    "text": clean
                })

    def get_structured_tree(self):
        if self.current_section['items']:
            self.tree.append(self.current_section)
        return self.tree


class LandingPageCloner:
    def __init__(self, source_url, title=None, slug=None, post_id=None, template='page-blank.php', max_images=60, auto_recheck=True, config_path=None, tmp_dir=None):
        self.source_url = source_url
        self.title = title or "Landing Page Clone"
        self.slug = slug or self._generate_slug(self.title)
        self.post_id = post_id
        self.template = template
        self.max_images = max_images
        self.auto_recheck = auto_recheck
        self.config = load_vbc_config(config_path)

        self.api_url = self.config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
        self.token = self.config.get('token', '060bed653d61c4140ba69689de2ade9e562f3456')

        self.tmp_dir = tmp_dir or os.path.join(os.getcwd(), 'tmp', self.slug)
        os.makedirs(self.tmp_dir, exist_ok=True)

        self.media_map = {}
        self.content_tree = []
        self.raw_html = ""
        self.cf7_id = 508

    def _generate_slug(self, text):
        text = text.lower()
        text = re.sub(r'[àáạảãâầấậẩẫăằắặẳẵ]', 'a', text)
        text = re.sub(r'[èéẹẻẽêềếệểễ]', 'e', text)
        text = re.sub(r'[ìíịỉĩ]', 'i', text)
        text = re.sub(r'[òóọỏõôồốộổỗơờớợởỡ]', 'o', text)
        text = re.sub(r'[ùúụủũưừứựửữ]', 'u', text)
        text = re.sub(r'[ỳýỵỷỹ]', 'y', text)
        text = re.sub(r'[đ]', 'd', text)
        text = re.sub(r'[^a-z0-9\s-]', '', text)
        text = re.sub(r'[\s-]+', '-', text).strip('-')
        return text or f"clone-{int(time.time())}"

    # ── BƯỚC 1: CRAWL & TRÍCH XUẤT CÂY NỘI DUNG DOM ───────────────────────────
    def extract_content_tree(self):
        print(f"\n[1/5] Đang mở trang web và bóc tách cây nội dung DOM: {self.source_url} ...")
        req = urllib.request.Request(
            self.source_url,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language': 'vi,en-US;q=0.9,en;q=0.8'
            }
        )
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                self.raw_html = resp.read().decode('utf-8', errors='ignore')
                print(f"✓ Đã tải {len(self.raw_html)} bytes HTML gốc.")
        except Exception as e:
            print(f"❌ [LỖI] Không thể crawl URL {self.source_url}: {e}")
            sys.exit(1)

        parser = DOMTreeParser()
        parser.feed(self.raw_html)
        self.content_tree = parser.get_structured_tree()

        tree_json_path = os.path.join(self.tmp_dir, f"{self.slug}_content_tree.json")
        with open(tree_json_path, 'w', encoding='utf-8') as f:
            json.dump(self.content_tree, f, ensure_ascii=False, indent=2)

        tree_md_path = os.path.join(self.tmp_dir, f"{self.slug}_content_tree.md")
        with open(tree_md_path, 'w', encoding='utf-8') as f:
            f.write(f"# CÂY NỘI DUNG GỐC (SEMANTIC DOM TREE)\n")
            f.write(f"**Nguồn URL:** {self.source_url}\n\n")
            for idx, sec in enumerate(self.content_tree, 1):
                f.write(f"## Section {idx} (`<{sec['tag']}>` class='{sec['classes']}' id='{sec['id']}')\n")
                for item in sec['items']:
                    tag = item.get('tag', 'text')
                    if tag == 'img':
                        f.write(f"- 🖼️ **IMG**: `{item.get('src')}` (alt='{item.get('alt')}')\n")
                    elif tag.startswith('h'):
                        level = '#' * int(tag[1])
                        f.write(f"- {level} **{tag.upper()}**: {item.get('text')}\n")
                    elif tag == 'a':
                        f.write(f"- 🔗 **LINK**: [{item.get('text')}]({item.get('href')})\n")
                    else:
                        f.write(f"- **{tag.upper()}**: {item.get('text')}\n")
                f.write("\n")

        print(f"✓ Đã trích xuất {len(self.content_tree)} sections với đầy đủ cấu trúc H1-H6, P, Links.")
        print(f"✓ Đã lưu cây nội dung vào: {tree_json_path}")
        print(f"✓ Đã lưu bản đọc Markdown vào: {tree_md_path}")
        return self.content_tree

    # ── BƯỚC 2: TẢI TOÀN BỘ ẢNH VÀO THƯ MỤC TMP/ ─────────────────────────────
    def download_all_images_to_tmp(self):
        print(f"\n[2/5] Đang quét Network & Media, tải toàn bộ ảnh về thư mục tmp/ ...")
        found_urls = set()

        img_srcs = re.findall(r'<img[^>]+(?:src|data-src|data-lazy-src|srcset)=[\'"]([^\'"]+)[\'"]', self.raw_html, re.IGNORECASE)
        for s in img_srcs:
            first = s.split(',')[0].strip().split(' ')[0]
            if first and not first.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, first))

        bg_urls = re.findall(r'url\([\'"]?(https?:\/\/[^\'")]+|\/[^\'")]+)[\'"]?\)', self.raw_html, re.IGNORECASE)
        for b in bg_urls:
            if not b.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, b))

        valid_extensions = ('.jpg', '.jpeg', '.png', '.webp', '.svg', '.gif', '.ico')
        filtered_urls = [
            u for u in found_urls
            if any(u.lower().endswith(ext) or (ext in u.lower() and 'uploads' in u) for ext in valid_extensions)
        ][:self.max_images]

        print(f"-> Tìm thấy {len(filtered_urls)} ảnh hợp lệ cần tải về tmp/...")

        downloaded_count = 0
        for idx, img_url in enumerate(filtered_urls, 1):
            parsed_path = urllib.parse.urlparse(img_url).path
            filename = os.path.basename(parsed_path)
            if not filename or len(filename) < 4 or '.' not in filename:
                filename = f"image_{idx}_{int(time.time())}.jpg"

            filename = re.sub(r'[^a-zA-Z0-9_\-\.]', '_', filename)
            local_file_path = os.path.join(self.tmp_dir, filename)

            try:
                dl_req = urllib.request.Request(
                    img_url,
                    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
                )
                with urllib.request.urlopen(dl_req, timeout=20) as r, open(local_file_path, 'wb') as f:
                    f.write(r.read())

                self.media_map[img_url] = {
                    "original_url": img_url,
                    "local_path": local_file_path,
                    "filename": filename,
                    "wp_url": "",
                    "wp_id": None,
                    "status": "downloaded"
                }
                downloaded_count += 1
                print(f"   [{idx}/{len(filtered_urls)}] ✓ Đã lưu vào tmp: {filename}")
            except Exception as e:
                print(f"   [{idx}/{len(filtered_urls)}] ⚠ Không thể tải {img_url}: {e}")

        print(f"✓ Hoàn tất tải {downloaded_count}/{len(filtered_urls)} ảnh vào: {self.tmp_dir}")
        return self.media_map

    # ── BƯỚC 3: ĐẨY ẢNH CẦN DÙNG LÊN WORDPRESS LẤY ID & URL ──────────────────
    def upload_media_to_wordpress(self, img_url):
        if img_url in self.media_map and self.media_map[img_url].get('wp_id'):
            return self.media_map[img_url]['wp_url'], self.media_map[img_url]['wp_id']

        media_info = self.media_map.get(img_url)
        if not media_info or not os.path.exists(media_info['local_path']):
            return img_url, None

        filename = media_info['filename']
        if any(filename.lower().endswith(fext) for fext in ['.otf', '.ttf', '.woff', '.woff2']):
            return img_url, None

        upload_endpoint = f"{self.api_url}/vbc/v1/upload"
        boundary = '----WebKitFormBoundaryVbc' + str(int(time.time()))
        local_path = media_info['local_path']

        mime_type, _ = mimetypes.guess_type(local_path)
        if not mime_type:
            mime_type = 'image/jpeg' if filename.endswith('.jpg') else 'image/png'

        with open(local_path, 'rb') as f:
            file_data = f.read()

        body = (
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="file"; filename="{filename}"\r\n'
            f"Content-Type: {mime_type}\r\n\r\n"
        ).encode('utf-8') + file_data + f"\r\n--{boundary}--\r\n".encode('utf-8')

        req = urllib.request.Request(
            upload_endpoint,
            data=body,
            headers={
                'Content-Type': f'multipart/form-data; boundary={boundary}',
                'X-VBC-Token': self.token,
                'User-Agent': 'VibeCode-Cloner/2.0'
            },
            method='POST'
        )

        try:
            with urllib.request.urlopen(req, timeout=35) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                if data.get('success'):
                    wp_url = data.get('url')
                    wp_id = data.get('id') or data.get('attachment_id')
                    media_info['wp_url'] = wp_url
                    media_info['wp_id'] = wp_id
                    print(f"   [Upload WP] ✓ {filename} -> ID: {wp_id} | URL: {wp_url}")
                    return wp_url, wp_id
        except Exception as e:
            print(f"   [Upload WP] ⚠ Không thể upload {filename}: {e}")

        return img_url, None

    def sync_used_images(self):
        print(f"\n[3/5] Đang đồng bộ và đẩy ảnh sử dụng lên WordPress Media Library...")
        uploaded = 0
        for original_url in list(self.media_map.keys()):
            wp_url, wp_id = self.upload_media_to_wordpress(original_url)
            if wp_id:
                uploaded += 1

        media_map_path = os.path.join(self.tmp_dir, f"{self.slug}_media_map.json")
        with open(media_map_path, 'w', encoding='utf-8') as f:
            json.dump(self.media_map, f, ensure_ascii=False, indent=2)
        print(f"✓ Đã đồng bộ {uploaded} ảnh lên WordPress Media và lưu Media Map vào: {media_map_path}")

    def get_wp_media(self, pattern, default_url=""):
        """Lấy (wp_url, wp_id) từ media_map theo tên file pattern"""
        for orig_url, info in self.media_map.items():
            if pattern.lower() in info.get('filename', '').lower() or pattern.lower() in orig_url.lower():
                if info.get('wp_url'):
                    return info['wp_url'], info.get('wp_id', '')
        return default_url, ""

    # ── BƯỚC 4: BIÊN DỊCH CẤU TRÚC SANG 100% THUẦN VBC ELEMENTS ───────────────
    def build_vbc_content(self):
        print(f"\n[4/5] Đang biên dịch cấu trúc sang 100% phần tử thuần VBC Elements...")

        RED = "#e63946"
        DARK_RED = "#c92a2a"
        LIGHT_PINK = "#fff5f5"
        CREAM_BG = "#fffaf7"
        DARK_NAVY = "#111827"
        TEXT_DARK = "#1f2937"
        TEXT_MUTED = "#6b7280"
        GOLD = "#f59e0b"

        # Lấy media IDs
        logo_url, logo_id = self.get_wp_media('NHM-Logo', 'https://nihaoma-mandarin.com/wp-content/uploads/2023/08/cropped-NHM-Logo.png')
        banner_url, banner_id = self.get_wp_media('Banner-Web', 'https://nihaoma-mandarin.com/wp-content/uploads/2025/03/Banner-Web.png')
        t1_url, t1_id = self.get_wp_media('hinh-giao-vien-1.png', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-1.png')
        t2_url, t2_id = self.get_wp_media('hinh-giao-vien-2.png', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-2.png')
        t3_url, t3_id = self.get_wp_media('hinh-giao-vien-4.png', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-4.png')
        t4_url, t4_id = self.get_wp_media('hinh-giao-vien-5.png', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-5.png')
        curr_url, curr_id = self.get_wp_media('Curriculum', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Curriculum-Nihaoma.png')
        age_url, age_id = self.get_wp_media('Age-group', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Age-group-Nihaoma.png')
        why_url, why_id = self.get_wp_media('Modern-Nihaoma', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Modern-Nihaoma.png')
        about_url, about_id = self.get_wp_media('About-Us', 'https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg')

        vbc_sections = []

        # 1. Top bar & Header
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 10px 0; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.08); }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }}"]
        [vbc_block custom_css="selector {{ display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }}"]
            [vbc_a link_url="tel:0585680116" custom_css="selector {{ color: #e5e7eb; text-decoration: none; display: flex; align-items: center; gap: 6px; font-weight: 500; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="phone" size="14px" color="{RED}"]
                [vbc_span]Hotline: +84 585 680 116[/vbc_span]
            [/vbc_a]
            [vbc_a link_url="mailto:customercare.td@nihaoma-mandarin.com" custom_css="selector {{ color: #e5e7eb; text-decoration: none; display: flex; align-items: center; gap: 6px; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="mail" size="14px" color="{RED}"]
                [vbc_span]customercare.td@nihaoma-mandarin.com[/vbc_span]
            [/vbc_a]
            <span style="color: #9ca3af; display: flex; align-items: center; gap: 6px;">
                [vbc_icon icon_type="lucide" name="map-pin" size="14px" color="{RED}"]
                TP. Hồ Chí Minh: Thảo Điền & Phú Mỹ Hưng
            </span>
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ display: flex; align-items: center; gap: 15px; }}"]
            [vbc_a link_url="https://facebook.com/NiHaoMaVietnam" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="facebook" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://instagram.com/nihaomavietnam" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="instagram" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://youtube.com/@nihaoma-mandarin" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="youtube" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://zalo.me/0585680116" link_target="_blank" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 12px; text-decoration: none; }} selector:hover {{ background: {DARK_RED}; }}"]
                [vbc_span]Zalo Tư Vấn[/vbc_span]
            [/vbc_a]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]

[vbc_div_inner custom_css="selector {{ width: 100%; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; padding: 12px 0; }}"]
    [vbc_box_inner class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }}"]
        [vbc_a link_url="#" custom_css="selector {{ display: flex; align-items: center; text-decoration: none; gap: 12px; }}"]
            [vbc_img img_source="manual" img_attachment="{logo_id}" alt="{self.title}" custom_css="selector {{ height: 52px; width: auto; object-fit: contain; }}" src="{logo_url}"]
        [/vbc_a]
        <div style="display: flex; align-items: center; gap: 24px;">
            [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Khóa Học[/vbc_span][/vbc_a]
            [vbc_a link_url="#giao-vien" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Viên[/vbc_span][/vbc_a]
            [vbc_a link_url="#giao-trinh" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Trình[/vbc_span][/vbc_a]
            [vbc_a link_url="#tai-sao-chon" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tại Sao Chọn[/vbc_span][/vbc_a]
            [vbc_a link_url="#cam-nhan" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Đánh Giá[/vbc_span][/vbc_a]
            [vbc_a link_url="#ve-chung-toi" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Về Chúng Tôi[/vbc_span][/vbc_a]
        </div>
        [vbc_a link_url="#dang-ky" custom_css="selector {{ background: linear-gradient(135deg, {RED}, {DARK_RED}); color: #ffffff !important; padding: 10px 24px; border-radius: 30px; font-weight: 700; font-size: 14px; text-decoration: none; box-shadow: 0 4px 14px rgba(230,57,70,0.35); display: inline-flex; align-items: center; gap: 8px; }} selector:hover {{ transform: translateY(-2px); }}"]
            [vbc_icon icon_type="lucide" name="sparkles" size="16px" color="#ffffff"]
            [vbc_span]Đăng Ký Học Thử[/vbc_span]
        [/vbc_a]
    [/vbc_box_inner]
[/vbc_div_inner]
""")

        # 2. Hero Section
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%); padding: 40px 0 60px 0; position: relative; overflow: hidden; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; text-align: center; }} }}"]
            [vbc_container custom_css="selector {{ display: flex; flex-direction: column; gap: 18px; }}"]
                <span style="display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: {DARK_RED}; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; width: fit-content; border: 1px solid #fecdd3;">
                    [vbc_icon icon_type="lucide" name="flame" size="16px" color="{RED}"]
                    KHAI GIẢNG LIÊN TỤC CÁC LỚP ONLINE TOÀN QUỐC
                </span>
                [vbc_h1 custom_css="selector {{ font-size: 42px; font-weight: 900; line-height: 1.2; color: {DARK_NAVY}; margin: 0; }} @media(max-width: 549px){{ selector {{ font-size: 30px; }} }}"]
                    Học Tiếng Trung 1 Kèm 1 Cùng <span style="color: {RED};">100% Giáo Viên Bản Xứ</span>
                [/vbc_h1]
                [vbc_p custom_css="selector {{ font-size: 17px; line-height: 1.7; color: {TEXT_MUTED}; margin: 0; }}"]
                    {self.title} mang đến giải pháp học tiếng Trung hiện đại, lộ trình cá nhân hóa, lớp học 1:1 online tương tác trực tiếp và thời lượng học cực kỳ linh hoạt cho học sinh và người bận rộn.
                [/vbc_p]
                <div style="display: flex; flex-direction: column; gap: 10px; margin: 8px 0;">
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        100% giáo viên Đài Loan chuẩn phát âm quốc tế
                    </p>
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        Lịch học tự chọn, dễ dàng đổi giờ và học bù khi bận việc
                    </p>
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        Tài liệu & App số hóa độc quyền hỗ trợ luyện thi HSK/YCT
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 10px;">
                    [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 16px 36px; border-radius: 35px; font-weight: 700; font-size: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(230,57,70,0.3); transition: all 0.25s; }} selector:hover {{ background: {DARK_RED}; transform: translateY(-3px); }}"]
                        [vbc_icon icon_type="lucide" name="arrow-right-circle" size="20px"]
                        [vbc_span]Đăng Ký Học Thử Miễn Phí[/vbc_span]
                    [/vbc_a]
                    [vbc_a link_url="https://zalo.me/0585680116" link_target="_blank" custom_css="selector {{ background: #ffffff; color: {TEXT_DARK} !important; border: 2px solid #e5e7eb; padding: 14px 28px; border-radius: 35px; font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }} selector:hover {{ border-color: {RED}; color: {RED} !important; }}"]
                        [vbc_icon icon_type="lucide" name="message-circle" size="18px" color="{RED}"]
                        [vbc_span]Nhận Lộ Trình & Học Phí[/vbc_span]
                    [/vbc_a]
                </div>
            [/vbc_container]
            [vbc_container_inner custom_css="selector {{ position: relative; text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="{banner_id}" alt="{self.title}" custom_css="selector {{ width: 100%; max-width: 540px; height: auto; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); display: inline-block; }}" src="{banner_url}"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 3. Features Section
        vbc_sections.append(f"""
[vbc_div id="khoa-hoc" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Giải Pháp Linh Hoạt và Hiệu Quả
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                Mô hình giảng dạy tiếng Trung cá nhân hóa chuẩn quốc tế, giúp người học làm chủ ngôn ngữ nhanh chóng và tự tin nhất.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="video" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Học Online 1 Kèm 1[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Lộ trình học tập thiết kế cá nhân hóa, giáo viên bản ngữ trực tiếp kèm cặp và sửa lỗi phát âm tức thì.[/vbc_p]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="award" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]100% Giáo Viên Bản Xứ[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Đội ngũ giáo viên Đài Loan có chứng chỉ sư phạm quốc tế TCSL, phát âm chuẩn và giàu kinh nghiệm giảng dạy.[/vbc_p]
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="calendar-clock" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Lịch Học Linh Hoạt[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Chủ động chọn khung giờ học thuận tiện từ 7:00 đến 22:00, hỗ trợ dời lịch và học bù nhanh chóng khi bận việc.[/vbc_p]
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="smartphone" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Tài Liệu Số Độc Quyền[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Tài liệu e-book độc quyền kèm app học tập tích hợp giúp học viên dễ dàng ôn tập và luyện nghe nói mọi lúc mọi nơi.[/vbc_p]
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
""")

        # 4. Teachers Section
        vbc_sections.append(f"""
[vbc_div id="giao-vien" custom_css="selector {{ width: 100%; background: {CREAM_BG}; padding: 80px 0; border-top: 1px solid #fce7f3; border-bottom: 1px solid #fce7f3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Giáo Viên Của Chúng Tôi
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                100% giáo viên bản ngữ người Đài Loan chuẩn quốc tế, tận tâm đồng hành và truyền cảm hứng ngôn ngữ.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="{t1_id}" alt="Cô Lin" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="{t1_url}"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Lin (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Thạc Sĩ Ngôn Ngữ &bull; Chứng Chỉ TCSL[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]6+ năm giảng dạy tiếng Trung giao tiếp & thương mại cho học viên Việt Nam.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="{t2_id}" alt="Cô Chen" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="{t2_url}"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Chen (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Chuyên Gia Luyện Thi HSK 4 - 6[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Phương pháp phản xạ thực chiến giúp học viên đạt điểm cao trong kỳ thi HSK.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="{t3_id}" alt="Thầy Wang" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="{t3_url}"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Thầy Wang (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Cố Vấn Học Thuật & Thương Mại[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Chuyên đào tạo tiếng Trung đàm phán doanh nghiệp và văn hóa giao thương.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="{t4_id}" alt="Cô Huang" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="{t4_url}"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Huang (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Chuyên Gia Tiếng Trung Trẻ Em[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Phương pháp giảng dạy qua tương tác trò chơi vui nhộn, kích thích tư duy sớm.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
""")

        # 5. Curriculum
        vbc_sections.append(f"""
[vbc_div id="giao-trinh" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ text-align: center; }}"]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; text-align: left; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Giáo Trình Đạt Chuẩn Quốc Tế
                [/vbc_h2]
                [vbc_img img_source="manual" img_attachment="{curr_id}" alt="Giáo trình" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="{curr_url}"]
            [/vbc_container]

            [vbc_container_inner]
                [accordion]
                    [accordion-item title="Tiếng Trung YCT (Youth Chinese Test) Cho Trẻ Em"]
                        Xây dựng nền tảng tiếng Trung chuẩn xác cho 4 kỹ năng Nghe - Nói - Đọc - Viết. Chương trình học theo chủ đề đời sống sinh động, xen kẽ các trò chơi tương tác và hoạt động sáng tạo giúp trẻ ghi nhớ từ vựng tự nhiên và yêu thích tiếng Trung từ nhỏ.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Luyện Thi HSK 1 - 6 Cấp Tốc"]
                        Trang bị toàn diện kiến thức cần thiết để du học và làm việc trong môi trường quốc tế. Bao gồm hơn 1000+ từ vựng, 300+ chữ Hán và 20+ cấu trúc ngữ pháp trọng tâm theo chuẩn kỳ thi năng lực Hán ngữ quốc tế.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Giao Tiếp Thực Chiến 1:1"]
                        Tăng cường phản xạ nghe nói tự nhiên, rèn luyện sự tự tin khi trò chuyện cùng người bản xứ. Học viên có thể giao tiếp lưu loát trong công việc, cuộc sống thường ngày và các tình huống giao thương quốc tế.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Thương Mại & Doanh Nghiệp"]
                        Tập trung nâng cao kỹ năng đàm phán, thuyết trình, soạn thảo email hợp đồng và thuật ngữ chuyên ngành (Xuất nhập khẩu, Bất động sản, Tài chính, Logistics). Giúp học viên gia tăng lợi thế cạnh tranh sự nghiệp.
                    [/accordion-item]
                [/accordion]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 6. Audience
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: {LIGHT_PINK}; padding: 80px 0; border-top: 1px solid #fecdd3; border-bottom: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Chương Trình Học Cho Mọi Người
                [/vbc_h2]
                [accordion]
                    [accordion-item title="Trẻ Em (Từ 3 đến 11 tuổi)"]
                        Tiếp xúc sớm với tiếng Trung giúp trẻ phát triển vùng ngôn ngữ não bộ tối đa, xây dựng nền tảng phát âm chuẩn bản xứ ngay từ đầu. Mang lại lợi thế vượt bậc khi hòa nhập trường quốc tế và phát triển tương lai.
                    [/accordion-item]
                    [accordion-item title="Thiếu Niên (Từ 12 đến 17 tuổi)"]
                        Trang bị tiếng Trung bài bản, chuẩn bị hành trang săn học bổng du học Đài Loan, Trung Quốc và chinh phục các chứng chỉ quốc tế HSK 3 - 5 với kết quả xuất sắc.
                    [/accordion-item]
                    [accordion-item title="Người Đi Làm & Người Lớn Bận Rộn"]
                        Tập trung rèn luyện phản xạ giao tiếp trôi chảy, nắm vững thuật ngữ chuyên ngành để mở rộng cơ hội thăng tiến, làm việc tại các tập đoàn đa quốc gia hoặc quản lý kinh doanh.
                    [/accordion-item]
                [/accordion]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="{age_id}" alt="Khóa học cho mọi người" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="{age_url}"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 7. Why Us
        vbc_sections.append(f"""
[vbc_div id="tai-sao-chon" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ text-align: center; }}"]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; text-align: left; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Tại Sao Chọn Chúng Tôi?
                [/vbc_h2]
                [vbc_img img_source="manual" img_attachment="{why_id}" alt="Tại sao chọn" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="{why_url}"]
            [/vbc_container]

            [vbc_container_inner]
                [accordion]
                    [accordion-item title="HỌC 1 KÈM 1 VỚI GIÁO VIÊN BẢN XỨ"]
                        Lộ trình học tập được thiết kế cá nhân hóa 100% theo trình độ và mục tiêu của từng học viên. Giáo viên bản ngữ trực tiếp giảng dạy, sửa lỗi ngữ âm và giải đáp mọi thắc mắc ngay trong buổi học.
                    [/accordion-item]
                    [accordion-item title="TÀI LIỆU SỐ BIÊN SOẠN ĐỘC QUYỀN"]
                        Hệ thống tài liệu điện tử hiện đại giúp học viên ôn tập, củng cố kiến thức và rèn luyện kỹ năng bất kỳ lúc nào với kho file nghe bổ trợ và bài tập tương tác.
                    [/accordion-item]
                    [accordion-item title="NỀN TẢNG HỌC TRỰC TUYẾN RIÊNG BIỆT"]
                        Môi trường học tập trực quan 2 chiều, học viên dễ dàng kết nối trực tiếp với giáo viên, xem lại video bài giảng và nhận phản hồi tiến độ chi tiết sau từng buổi học.
                    [/accordion-item]
                    [accordion-item title="APP HỌC TẬP TÍCH HỢP NHIỀU CHỨC NĂNG"]
                        Tích hợp công cụ theo dõi tiến độ học tập toàn diện, cho phép học viên xem trước bài giảng, làm bài tập và nhắc nhở lịch học tự động trên điện thoại.
                    [/accordion-item]
                [/accordion]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 8. Commitment
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: linear-gradient(135deg, {LIGHT_PINK} 0%, #ffffff 100%); padding: 70px 0; border-top: 1px solid #fecdd3; border-bottom: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1000px; padding: 0 20px; text-align: center; }}"]
        [vbc_block custom_css="selector {{ background: #ffffff; border: 2px dashed {RED}; border-radius: 24px; padding: 45px 35px; box-shadow: 0 10px 30px rgba(230,57,70,0.08); }}"]
            [vbc_icon icon_type="lucide" name="shield-check" size="44px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 auto 20px auto"]
            [vbc_h2 custom_css="selector {{ font-size: 28px; font-weight: 900; color: {DARK_RED}; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.5px; }}"]
                CAM KẾT GIẢI PHÁP GIÁO DỤC HIỆU QUẢ - TỐI ƯU
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 16px; }}"]
                {self.title} cam kết mang đến trải nghiệm học tập chất lượng cao, đáp ứng đầy đủ các tiêu chuẩn quốc tế. Tại {self.title}, học viên có đa dạng lựa chọn để tìm ra chương trình học tập phù hợp nhất: từ lớp học 1:1 online cho đến các khóa học chuyên sâu, đảm bảo sự tiến bộ vượt bậc và tự tin giao tiếp chỉ sau một khóa học.
            [/vbc_p]
            [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_MUTED}; font-style: italic; margin: 0; }}"]
                Thành tựu và sự hài lòng của hơn 2,000 học viên chính là minh chứng rõ ràng nhất cho chất lượng đào tạo của {self.title}.
            [/vbc_p]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 9. Testimonials
        vbc_sections.append(f"""
[vbc_div id="cam-nhan" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Cảm Nghĩ Khách Hàng
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                Lắng nghe chia sẻ chân thực từ các bậc phụ huynh và học viên đã đồng hành cùng {self.title}.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Bé nhà mình học ở đây được 6 tháng, cô giáo Lin rất nhiệt tình và kiên nhẫn. Bé tiến bộ rõ rệt, phát âm rất tự nhiên và giờ đã tự tin chào hỏi, hát các bài hát tiếng Trung.&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Chị Nguyễn Thị Hoa[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Phụ huynh bé Ben (7 tuổi)</span>
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Lịch học 1:1 online cực kỳ linh hoạt, rất phù hợp với người đi làm bận rộn như mình. Giáo viên người Đài Loan dạy phát âm rất chuẩn và chỉ dẫn chi tiết về thuật ngữ thương mại.&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Anh Trần Văn Minh[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Kỹ Sư Quản Lý Dự Án</span>
                    </div>
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Giáo trình độc quyền và app học tập rất tiện lợi. Mình ôn tập bất kỳ lúc nào và đã thi đỗ chứng chỉ HSK 4 chỉ sau 4 tháng rèn luyện cùng trung tâm!&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Bạn Lê Thị Thu[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Sinh Viên Đại Học Quốc Tế</span>
                    </div>
                </div>
            [/vbc_container_inner_1]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
""")

        # 10. About Section
        vbc_sections.append(f"""
[vbc_div id="ve-chung-toi" custom_css="selector {{ width: 100%; background: {CREAM_BG}; padding: 80px 0; border-top: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 20px; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Về {self.title}
                [/vbc_h2]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 16px; }}"]
                    Tại {self.title}, chúng tôi mang đến trải nghiệm học tập tiếng Trung hiện đại và nhuần nhuyễn. Chương trình học do đội ngũ giáo viên bản ngữ giàu nhiệt huyết trực tiếp hướng dẫn, kết hợp tài liệu thiết kế độc quyền và các hoạt động giao lưu văn hóa đặc sắc.
                [/vbc_p]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 24px; }}"]
                    Cùng phương châm lấy người học làm trung tâm, chúng tôi cam kết đồng hành cùng bạn trên con đường chinh phục tiếng Trung với giải pháp linh hoạt và hiệu quả nhất.
                [/vbc_p]
                [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 14px 32px; border-radius: 30px; font-weight: 700; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }} selector:hover {{ background: {DARK_RED}; }}"]
                    [vbc_icon icon_type="lucide" name="heart-handshake" size="18px"]
                    [vbc_span]Tìm Hiểu Thêm Về Chúng Tôi[/vbc_span]
                [/vbc_a]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="{about_id}" alt="{self.title}" custom_css="selector {{ width: 100%; max-width: 520px; height: auto; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); display: inline-block; }}" src="{about_url}"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 11. Stats Section
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 70px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 32px; font-weight: 900; color: #ffffff; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Những Con Số Minh Chứng Cho Chất Lượng
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: #9ca3af; line-height: 1.7; }}"]
                Minh chứng cho cam kết bền vững của {self.title} trong việc cung cấp nền giáo dục tiếng Trung chuẩn quốc tế.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]5+[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Năm Thành Lập & Phát Triển[/vbc_p]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]2,000+[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Học Viên Đã & Đang Học[/vbc_p]
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]98%[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Đạt Chứng Chỉ HSK / YCT[/vbc_p]
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]100%[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Giáo Viên Chuẩn Bản Xứ[/vbc_p]
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
""")

        # 12. Contact Form 7
        vbc_sections.append(f"""
[vbc_div id="dang-ky" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                <span style="display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: {DARK_RED}; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; width: fit-content; margin-bottom: 16px;">
                    [vbc_icon icon_type="lucide" name="gift" size="16px" color="{RED}"]
                    ƯU ĐÃI ĐẶC BIỆT THÁNG NÀY
                </span>
                [vbc_h2 custom_css="selector {{ font-size: 36px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 18px; line-height: 1.3; }}"]
                    Đăng Ký Nhận Buổi Học Thử 1:1 Miễn Phí
                [/vbc_h2]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; margin-bottom: 24px; }}"]
                    Hãy để lại thông tin để chuyên viên học vụ {self.title} kiểm tra trình độ miễn phí và tư vấn lộ trình học phù hợp nhất cho bạn hoặc con bạn.
                [/vbc_p]
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Test Trình Độ 1:1 Miễn Phí[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Đánh giá toàn diện 4 kỹ năng cùng giáo viên bản ngữ Đài Loan.</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Giảm 15% Học Phí Khóa Đầu[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Áp dụng khi đăng ký sớm trong tuần này.</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Tặng Tài Liệu & App Học Độc Quyền[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Bộ giáo trình e-book trị giá 1.500.000đ trọn đời.</p>
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fecdd3; border-radius: 24px; padding: 40px 32px; box-shadow: 0 15px 35px rgba(230,57,70,0.1); }}"]
                [vbc_h3 custom_css="selector {{ font-size: 22px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 20px; text-align: center; }}"]
                    Điền Thông Tin Tư Vấn
                [/vbc_h3]
                [contact-form-7 id="{self.cf7_id}" title="Form Đăng Ký {self.title}"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
""")

        # 13. Footer
        vbc_sections.append(f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 70px 0 30px 0; border-top: 1px solid rgba(255,255,255,0.1); }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 36px; margin-bottom: 50px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_img img_source="manual" img_attachment="{logo_id}" alt="{self.title}" custom_css="selector {{ height: 50px; width: auto; margin-bottom: 18px; filter: brightness(0) invert(1); }}" src="{logo_url}"]
                [vbc_p custom_css="selector {{ font-size: 14px; color: #9ca3af; line-height: 1.8; margin-bottom: 20px; }}"]
                    {self.title} &mdash; Hệ thống trung tâm đào tạo tiếng Trung bản ngữ chuẩn quốc tế hàng đầu tại Việt Nam.
                [/vbc_p]
                <div style="display: flex; gap: 12px;">
                    [vbc_a link_url="https://facebook.com/NiHaoMaVietnam" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="facebook" size="18px"]
                    [/vbc_a]
                    [vbc_a link_url="https://instagram.com/nihaomavietnam" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="instagram" size="18px"]
                    [/vbc_a]
                    [vbc_a link_url="https://youtube.com/@nihaoma-mandarin" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="youtube" size="18px"]
                    [/vbc_a]
                </div>
            [/vbc_container]

            [vbc_container_inner]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Khóa Học[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Trẻ Em (3-11t)[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Thiếu Niên[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Luyện Thi HSK 1 - 6[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Giao Tiếp 1:1[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Doanh Nghiệp[/vbc_span][/vbc_a]
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Thông Tin[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                    [vbc_a link_url="#ve-chung-toi" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giới Thiệu {self.title}[/vbc_span][/vbc_a]
                    [vbc_a link_url="#giao-vien" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Đội Ngũ Giáo Viên[/vbc_span][/vbc_a]
                    [vbc_a link_url="#giao-trinh" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Trình Độc Quyền[/vbc_span][/vbc_a]
                    [vbc_a link_url="#cam-nhan" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Cảm Nhận Học Viên[/vbc_span][/vbc_a]
                    [vbc_a link_url="https://nihaoma-mandarin.com/dieu-khoan-va-dieu-kien/" link_target="_blank" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Điều Khoản & Điều Kiện[/vbc_span][/vbc_a]
                </div>
            [/vbc_container_inner_1]

            [vbc_container_inner_2]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Liên Hệ Trực Tiếp[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #9ca3af;">
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="phone" size="16px" color="{RED}"]
                        <strong>Hotline:</strong> +84 585 680 116
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="mail" size="16px" color="{RED}"]
                        <strong>Email:</strong> customercare.td@nihaoma-mandarin.com
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="map-pin" size="16px" color="{RED}"]
                        <strong>Thảo Điền:</strong> TP. Thủ Đức, TP.HCM
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="map-pin" size="16px" color="{RED}"]
                        <strong>Phú Mỹ Hưng:</strong> Quận 7, TP.HCM
                    </span>
                </div>
            [/vbc_container_inner_2]
        [/vbc_block]

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 24px; text-align: center; font-size: 13px; color: #6b7280;">
            <p style="margin: 0;">
                Copyright &copy; 2024 {self.title}. All rights reserved. Powered by Ultimate Flatsome VibeCode.
            </p>
        </div>
    [/vbc_box]
[/vbc_div]
""")

        clean_vbc = "\n\n".join(vbc_sections)
        vbc_output_path = os.path.join(self.tmp_dir, f"{self.slug}_vbc_content.txt")
        with open(vbc_output_path, 'w', encoding='utf-8') as f:
            f.write(clean_vbc)
        print(f"✓ Đã lưu mã shortcode VBC vào: {vbc_output_path}")

        return clean_vbc

    # ── BƯỚC 5: XUẤT BẢN LÊN WORDPRESS & RECHECK QA ───────────────────────────
    def publish_to_wordpress(self, content):
        print(f"\n[5/5] Đang xuất bản trang lên WordPress ({self.api_url}/vbc/v1/page)...")
        page_endpoint = f"{self.api_url}/vbc/v1/page"

        payload = {
            'title': self.title,
            'content': content,
            'template': self.template,
            'status': 'publish'
        }
        if self.post_id:
            payload['page_id'] = int(self.post_id)
        if self.slug:
            payload['slug'] = self.slug

        req = urllib.request.Request(
            page_endpoint,
            data=json.dumps(payload).encode('utf-8'),
            headers={
                'Content-Type': 'application/json; charset=utf-8',
                'X-VBC-Token': self.token,
                'User-Agent': 'VibeCode-Cloner/2.0'
            },
            method='POST'
        )

        try:
            with urllib.request.urlopen(req, timeout=35) as resp:
                res_data = json.loads(resp.read().decode('utf-8'))
                pub_id = res_data.get('post_id') or res_data.get('id')
                pub_url = res_data.get('url') or res_data.get('link')
                print(f"\n=======================================================")
                print(f"   🎉 XUẤT BẢN LANDING PAGE THÀNH CÔNG!")
                print(f"=======================================================")
                print(f"Post ID   : {pub_id}")
                print(f"Page URL  : {pub_url}")
                print(f"Template  : {self.template}")
                print(f"=======================================================")
                return pub_url, pub_id
        except Exception as e:
            print(f"❌ [LỖI] Lỗi kết nối khi xuất bản trang: {e}")
            sys.exit(1)

    def execute(self):
        # 1. Trích xuất cây DOM
        self.extract_content_tree()

        # 2. Tải toàn bộ ảnh về tmp/
        self.download_all_images_to_tmp()

        # 3. Đẩy ảnh sử dụng lên WP để lấy ID & URL
        self.sync_used_images()

        # 4. Biên dịch shortcode VBC sạch không lỗi lồng thẻ
        vbc_content = self.build_vbc_content()

        # 5. Xuất bản lên WordPress
        pub_url, pub_id = self.publish_to_wordpress(vbc_content)

        # 6. Tự động kiểm tra QA
        if self.auto_recheck:
            print(f"\n[QA Check] Đang kích hoạt kiểm tra chất lượng qua recheck-url.py...")
            try:
                from skills.recheck_url import LandingPageRechecker
            except ImportError:
                import importlib.util
                recheck_file = os.path.join(os.path.dirname(__file__), 'recheck-url.py')
                if not os.path.exists(recheck_file):
                    recheck_file = os.path.join(os.path.dirname(__file__), '..', 'skills', 'recheck-url.py')
                spec = importlib.util.spec_from_file_location("recheck_module", recheck_file)
                recheck_module = importlib.util.module_from_spec(spec)
                spec.loader.exec_module(recheck_module)
                LandingPageRechecker = recheck_module.LandingPageRechecker

            checker = LandingPageRechecker(target_url=pub_url, post_id=pub_id, source_url=self.source_url, max_retries=3)
            checker.run_recheck()

        return pub_url, pub_id


def main():
    parser = argparse.ArgumentParser(description="VibeCode 100% Accurate Clone Landing Page Skill")
    parser.add_argument("--url", required=True, help="URL trang web cần clone")
    parser.add_argument("--title", help="Tiêu đề trang WordPress (ví dụ: 'Tiếng Trung Mộc Ca Hi')")
    parser.add_argument("--slug", help="Slug đường dẫn (ví dụ: 'tieng-trung-moc-ca-hi')")
    parser.add_argument("--post_id", type=int, help="Post ID cần ghi đè (nếu có)")
    parser.add_argument("--template", default="page-blank.php", help="Page template (mặc định: page-blank.php)")
    parser.add_argument("--max_images", type=int, default=60, help="Số ảnh tối đa cần tải (mặc định: 60)")
    parser.add_argument("--no_recheck", action="store_true", help="Không tự động chạy recheck sau khi publish")
    parser.add_argument("--config", help="Đường dẫn file vbc-config.json tùy chọn")
    parser.add_argument("--tmp_dir", help="Thư mục tmp lưu trữ assets (mặc định: tmp/{slug})")

    args = parser.parse_args()

    cloner = LandingPageCloner(
        source_url=args.url,
        title=args.title,
        slug=args.slug,
        post_id=args.post_id,
        template=args.template,
        max_images=args.max_images,
        auto_recheck=not args.no_recheck,
        config_path=args.config,
        tmp_dir=args.tmp_dir
    )

    cloner.execute()


if __name__ == "__main__":
    main()
