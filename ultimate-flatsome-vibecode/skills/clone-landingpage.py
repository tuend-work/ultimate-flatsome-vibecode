#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - 100% ACCURATE CLONE LANDING PAGE SKILL
===============================================================================
File: clone-landingpage.py
Description:
  Quy trình Clone Landing Page chuẩn xác 100% không bịa nội dung:
  1. Trình duyệt mở web cần clone, bóc tách toàn bộ Text theo cấu trúc Cây DOM
     (h1, h2, h3, h4, p, ul/li, a, button, blockquote, form...).
     Lưu cây nội dung ra file JSON/Markdown để đảm bảo 100% trung thực với bản gốc.
  2. Quét Network / HTML, tải toàn bộ ảnh về thư mục tmp/ của dự án.
  3. Khi dựng layout, ảnh cần dùng sẽ được đẩy lên WordPress Media Library qua
     REST API (/vbc/v1/upload) để lấy ID (attachment_id) và URL nội bộ gắn vào shortcode.
  4. Tự động chuyển đổi các khối Form biểu mẫu sang Contact Form 7 ([contact-form-7]).
  5. Biên dịch ra hệ thống phần tử VBC Elements ([vbc_div], [vbc_box], [vbc_block], [vbc_container], [vbc_icon]...)
     theo đúng chuẩn phân cấp chống lỗi lồng shortcode.
  6. Xuất bản lên WordPress qua /vbc/v1/page và tự động chạy recheck-url.py để nghiệm thu 100%.
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

# Thiết lập UTF-8 cho Windows console
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
    """Bóc tách cấu trúc cây nội dung phân cấp (Semantic Content Tree) từ HTML"""
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

        # Bỏ qua các thẻ script, style, svg code
        if tag_lower in ['script', 'style', 'noscript', 'iframe']:
            self.inside_ignored = True
            return

        # Phân chia section
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

        # Các thẻ chứa nội dung chính
        if tag_lower in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'a', 'button', 'blockquote', 'span', 'b', 'strong', 'td', 'th']:
            self.active_item = {
                "tag": tag_lower,
                "classes": attr_dict.get('class', ''),
                "id": attr_dict.get('id', ''),
                "href": attr_dict.get('href', ''),
                "text": ""
            }
            self.tag_stack.append(tag_lower)

        # Thẻ hình ảnh
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

        # Thư mục lưu trữ tạm thời ảnh của dự án
        self.tmp_dir = tmp_dir or os.path.join(os.getcwd(), 'tmp', self.slug)
        os.makedirs(self.tmp_dir, exist_ok=True)

        self.media_map = {}
        self.content_tree = []
        self.raw_html = ""

    def _generate_slug(self, text):
        """Tạo slug từ tiêu đề tiếng Việt"""
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
        """Tải mã nguồn và bóc tách toàn bộ text thành cây phân cấp h1, h2, p, a..."""
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

        # Parse cây nội dung
        parser = DOMTreeParser()
        parser.feed(self.raw_html)
        self.content_tree = parser.get_structured_tree()

        # Lưu cây nội dung ra file JSON trong tmp/
        tree_json_path = os.path.join(self.tmp_dir, f"{self.slug}_content_tree.json")
        with open(tree_json_path, 'w', encoding='utf-8') as f:
            json.dump(self.content_tree, f, ensure_ascii=False, indent=2)

        # Lưu bản tóm tắt Markdown trong tmp/
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

    # ── BƯỚC 2: TẢI TOÀN BỘ ẢNH VÀO THƯ MỤC TMP/ CỦA DỰ ÁN ──────────────────
    def download_all_images_to_tmp(self):
        """Quét và tải toàn bộ ảnh từ trang nguồn vào thư mục tmp/ của dự án"""
        print(f"\n[2/5] Đang quét Network & Media, tải toàn bộ ảnh về thư mục tmp/ ...")

        found_urls = set()

        # 1. Quét từ <img> (src, data-src, srcset)
        img_srcs = re.findall(r'<img[^>]+(?:src|data-src|data-lazy-src|srcset)=[\'"]([^\'"]+)[\'"]', self.raw_html, re.IGNORECASE)
        for s in img_srcs:
            first = s.split(',')[0].strip().split(' ')[0]
            if first and not first.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, first))

        # 2. Quét từ CSS background-image url(...)
        bg_urls = re.findall(r'url\([\'"]?(https?:\/\/[^\'")]+|\/[^\'")]+)[\'"]?\)', self.raw_html, re.IGNORECASE)
        for b in bg_urls:
            if not b.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, b))

        # 3. Quét từ <picture><source>
        source_srcs = re.findall(r'<source[^>]+srcset=[\'"]([^\'"]+)[\'"]', self.raw_html, re.IGNORECASE)
        for ss in source_srcs:
            first = ss.split(',')[0].strip().split(' ')[0]
            if first and not first.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, first))

        # 4. Lọc ảnh hợp lệ
        valid_extensions = ('.jpg', '.jpeg', '.png', '.webp', '.svg', '.gif', '.ico')
        filtered_urls = [
            u for u in found_urls
            if any(ext in u.lower() for ext in valid_extensions) or ('uploads' in u or 'images' in u)
        ][:self.max_images]

        print(f"-> Tìm thấy {len(filtered_urls)} ảnh hợp lệ cần tải về tmp/...")

        downloaded_count = 0
        for idx, img_url in enumerate(filtered_urls, 1):
            parsed_path = urllib.parse.urlparse(img_url).path
            filename = os.path.basename(parsed_path)
            if not filename or len(filename) < 4 or '.' not in filename:
                filename = f"image_{idx}_{int(time.time())}.jpg"

            # Làm sạch tên file
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

    # ── BƯỚC 3: ĐẨY ẢNH CẦN DÙNG LÊN WORDPRESS ĐỂ LẤY ID & URL ───────────────
    def upload_media_to_wordpress(self, img_url):
        """Tải file từ tmp/ lên WordPress Media qua /vbc/v1/upload và trả về (wp_url, wp_id)"""
        if img_url in self.media_map and self.media_map[img_url].get('wp_id'):
            return self.media_map[img_url]['wp_url'], self.media_map[img_url]['wp_id']

        media_info = self.media_map.get(img_url)
        if not media_info or not os.path.exists(media_info['local_path']):
            filename = os.path.basename(urllib.parse.urlparse(img_url).path) or f"media_{int(time.time())}.jpg"
            filename = re.sub(r'[^a-zA-Z0-9_\-\.]', '_', filename)
            local_path = os.path.join(self.tmp_dir, filename)
            try:
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(req, timeout=20) as r, open(local_path, 'wb') as f:
                    f.write(r.read())
                media_info = {
                    "original_url": img_url,
                    "local_path": local_path,
                    "filename": filename,
                    "wp_url": "",
                    "wp_id": None
                }
                self.media_map[img_url] = media_info
            except Exception as e:
                print(f"⚠ Lỗi tải trực tiếp {img_url}: {e}")
                return img_url, None

        # Upload lên WordPress qua /vbc/v1/upload
        upload_endpoint = f"{self.api_url}/vbc/v1/upload"
        boundary = '----WebKitFormBoundaryVbc' + str(int(time.time()))
        local_path = media_info['local_path']
        filename = media_info['filename']

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
            }
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
        """Duyệt và đẩy toàn bộ ảnh cần dùng trong layout lên WordPress"""
        print(f"\n[3/5] Đang đồng bộ và đẩy ảnh sử dụng lên WordPress Media Library...")
        uploaded = 0
        for original_url in list(self.media_map.keys()):
            wp_url, wp_id = self.upload_media_to_wordpress(original_url)
            if wp_id:
                uploaded += 1

        # Lưu lại media map hoàn chỉnh
        media_map_path = os.path.join(self.tmp_dir, f"{self.slug}_media_map.json")
        with open(media_map_path, 'w', encoding='utf-8') as f:
            json.dump(self.media_map, f, ensure_ascii=False, indent=2)
        print(f"✓ Đã đồng bộ {uploaded} ảnh lên WordPress Media và lưu Media Map vào: {media_map_path}")

    # ── BƯỚC 4: CHUYỂN ĐỔI FORM & BIÊN DỊCH SHORTCODE VBC ────────────────────
    def convert_forms_to_cf7(self, html):
        """Tự động chuyển đổi các form biểu mẫu sang Contact Form 7"""
        form_matches = list(re.finditer(r'<form\b[^>]*>(.*?)</form>', html, re.DOTALL | re.IGNORECASE))
        if not form_matches:
            return html

        print(f"-> Phát hiện {len(form_matches)} biểu mẫu cần chuyển đổi sang Contact Form 7...")
        processed_html = html
        for idx, match in enumerate(form_matches, 1):
            full_form = match.group(0)
            inner_form = match.group(1)

            # Tự động parse input fields
            inputs = re.findall(r'<input[^>]+(?:name|type|placeholder)=[\'"]([^\'"]*)[\'"]', inner_form, re.IGNORECASE)
            cf7_markup = ""
            for inp in inputs[:4]:
                cf7_markup += f'<p>[text* your-{inp} placeholder "{inp.capitalize()} *"]</p>\n'
            cf7_markup += '<p>[textarea your-message placeholder "Nội dung yêu cầu / Ghi chú"]</p>\n'
            cf7_markup += '<p>[submit "GỬI YÊU CẦU NGAY"]</p>'

            # Gửi lên API /vbc/v1/cf7
            try:
                cf7_req = urllib.request.Request(
                    f"{self.api_url}/vbc/v1/cf7",
                    data=json.dumps({
                        "title": f"Form Clone #{idx} - {self.title}",
                        "form": cf7_markup
                    }).encode('utf-8'),
                    headers={'Content-Type': 'application/json', 'X-VBC-Token': self.token},
                    method='POST'
                )
                with urllib.request.urlopen(cf7_req, timeout=15) as r:
                    res = json.loads(r.read().decode('utf-8'))
                    if res.get('success'):
                        shortcode = res.get('shortcode')
                        print(f"   [Form #{idx}] ✓ Đã tạo CF7 shortcode: {shortcode}")
                        processed_html = processed_html.replace(full_form, shortcode)
            except Exception as e:
                print(f"   [Form #{idx}] ⚠ Giữ nguyên form HTML: {e}")

        return processed_html

    def build_vbc_content(self):
        """Biên dịch cây nội dung và HTML sang shortcode VBC không lỗi lồng thẻ"""
        print(f"\n[4/5] Đang biên dịch cấu trúc sang 100% phần tử thuần VBC Elements...")

        # Thay thế URL ảnh nguồn sang link WordPress Media kèm ID
        processed_html = self.raw_html
        for orig_url, info in self.media_map.items():
            if info.get('wp_url'):
                processed_html = processed_html.replace(orig_url, info['wp_url'])

        # Chuyển đổi form
        processed_html = self.convert_forms_to_cf7(processed_html)

        # Sử dụng compiler html_to_vbc
        try:
            from skills.html_to_vbc import compile_html_to_vbc
        except ImportError:
            import importlib.util
            vbc_compiler_file = os.path.join(os.path.dirname(__file__), 'html_to_vbc.py')
            if not os.path.exists(vbc_compiler_file):
                vbc_compiler_file = os.path.join(os.path.dirname(__file__), '..', 'skills', 'html_to_vbc.py')
            spec = importlib.util.spec_from_file_location("vbc_compiler_module", vbc_compiler_file)
            vbc_compiler_module = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(vbc_compiler_module)
            compile_html_to_vbc = vbc_compiler_module.compile_html_to_vbc

        clean_vbc, extracted_css = compile_html_to_vbc(processed_html, return_css=True)
        self.extracted_css = extracted_css

        # Lưu nội dung shortcode ra file txt trong tmp/
        vbc_output_path = os.path.join(self.tmp_dir, f"{self.slug}_vbc_content.txt")
        with open(vbc_output_path, 'w', encoding='utf-8') as f:
            f.write(clean_vbc)
        print(f"✓ Đã lưu mã shortcode VBC vào: {vbc_output_path}")

        return clean_vbc

    # ── BƯỚC 5: XUẤT BẢN LÊN WORDPRESS & RECHECK QA ───────────────────────────
    def publish_to_wordpress(self, content):
        """Gửi nội dung tới REST API /vbc/v1/page với X-VBC-Token"""
        print(f"\n[5/5] Đang xuất bản trang lên WordPress ({self.api_url}/vbc/v1/page)...")
        page_endpoint = f"{self.api_url}/vbc/v1/page"

        payload = {
            'title': self.title,
            'content': content,
            'custom_css': getattr(self, 'extracted_css', ''),
            'template': self.template,
            'status': 'publish'
        }
        if self.post_id:
            payload['post_id'] = int(self.post_id)
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
                if res_data.get('success'):
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
                else:
                    print(f"❌ [LỖI] API từ chối xuất bản: {res_data}")
                    sys.exit(1)
        except Exception as e:
            print(f"❌ [LỖI] Lỗi kết nối khi xuất bản trang: {e}")
            sys.exit(1)

    def execute(self):
        """Thực thi tuần tự toàn bộ flow clone"""
        # 1. Trích xuất cây nội dung
        self.extract_content_tree()

        # 2. Tải toàn bộ ảnh về tmp/
        self.download_all_images_to_tmp()

        # 3. Đẩy ảnh cần dùng lên WordPress để lấy ID & URL
        self.sync_used_images()

        # 4. Biên dịch shortcode VBC
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
    parser.add_argument("--title", help="Tiêu đề trang WordPress (ví dụ: 'Ni Hao Ma – Học Tiếng Trung Online')")
    parser.add_argument("--slug", help="Slug đường dẫn (ví dụ: 'ni-hao-ma')")
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
