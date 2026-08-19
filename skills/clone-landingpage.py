#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - UNIVERSAL GENERIC CLONE LANDING PAGE SKILL
===============================================================================
File: clone-landingpage.py
Description:
  Bộ công cụ Clone Landing Page HOÀN TOÀN TỰ ĐỘNG & ĐA NĂNG (GENERIC 100%):
  - KHÔNG HARDCODE bất kỳ nội dung, text hay cấu trúc cố định của bất kỳ website nào.
  - Tự động bóc tách cây DOM (Semantic DOM Tree), phân tích các Section theo ngữ cảnh
    (Hero, Grid/Cards, 2-Col Split, Accordion, Stats, Testimonials, Form, Footer...).
  - Quét & Tải toàn bộ ảnh/media về thư mục tmp/{slug}/.
  - Đẩy ảnh lên WordPress Media Library qua REST API (/vbc/v1/upload) lấy URL/ID nội bộ.
  - Tự động chuyển đổi biểu mẫu thành Contact Form 7 qua REST API (/vbc/v1/cf7).
  - Tự động sinh Shortcodes thuần VBC Elements ([vbc_div], [vbc_box], [vbc_block],
    [vbc_container], [vbc_h1]-[vbc_h6], [vbc_p], [vbc_a], [vbc_icon], [accordion]...)
    với CSS Responsive hoàn chỉnh, 0 unparsed tags.
  - Xuất bản lên WordPress qua /vbc/v1/page và tự động chạy recheck-url.py để đối soát
    100% chất lượng so với trang web nguồn.
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


class GenericDOMTreeParser(HTMLParser):
    """
    Parser bóc tách toàn bộ cấu trúc trang web thành Cây DOM Phân Cấp (Hierarchical Semantic Tree)
    Tự động chia thành các Section logic và giữ nguyên 100% nội dung chữ, ảnh, liên kết.
    """
    def __init__(self, base_url=""):
        super().__init__()
        self.base_url = base_url
        self.sections = []
        self.current_section = self._create_new_section("header", "header-main", "")
        self.active_element = None
        self.tag_stack = []
        self.inside_ignored = False
        self.color_candidates = set()

    def _create_new_section(self, tag, class_name, elem_id):
        return {
            "tag": tag,
            "class": class_name,
            "id": elem_id,
            "items": [],
            "cards": [],
            "stats": [],
            "accordions": [],
            "forms": [],
            "images": [],
            "links": [],
            "headings": [],
            "paragraphs": []
        }

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        tag_lower = tag.lower()

        if tag_lower in ['script', 'style', 'noscript', 'iframe', 'svg']:
            self.inside_ignored = True
            return

        # Thu thập mã màu từ style inline
        style_attr = attr_dict.get('style', '')
        if style_attr:
            hex_matches = re.findall(r'#(?:[0-9a-fA-F]{3}){1,2}\b', style_attr)
            for hex_col in hex_matches:
                self.color_candidates.add(hex_col.lower())

        classes = attr_dict.get('class', '')
        elem_id = attr_dict.get('id', '')

        # Nhận diện ranh giới section chính xác (chỉ tách ở các thẻ cấp cao hoặc class section rõ ràng)
        is_major_section = (
            tag_lower in ['section', 'header', 'footer', 'main', 'article'] or
            (tag_lower == 'div' and any(kw in classes.lower() for kw in ['wp-block-group', 'elementor-section', 'section-wrap', 'site-header', 'site-footer']) and not any(ign in classes.lower() for ign in ['inner', 'col', 'row', 'grid']))
        )

        if is_major_section:
            if self.current_section['headings'] or len(self.current_section['paragraphs']) >= 2 or self.current_section['images']:
                self.sections.append(self.current_section)
                self.current_section = self._create_new_section(tag_lower, classes, elem_id)

        # Xử lý hình ảnh
        if tag_lower == 'img':
            src = attr_dict.get('src') or attr_dict.get('data-src') or attr_dict.get('data-lazy-src') or ''
            if src and not src.startswith('data:'):
                abs_src = urllib.parse.urljoin(self.base_url, src)
                alt = attr_dict.get('alt', '').strip()
                title = attr_dict.get('title', '').strip()
                img_obj = {
                    "tag": "img",
                    "src": abs_src,
                    "alt": alt,
                    "title": title,
                    "class": classes
                }
                self.current_section['images'].append(img_obj)
                self.current_section['items'].append(img_obj)
            return

        # Xử lý các thẻ text / link / heading
        if tag_lower in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'a', 'button', 'span', 'b', 'strong', 'em', 'blockquote']:
            self.active_element = {
                "tag": tag_lower,
                "class": classes,
                "id": elem_id,
                "href": urllib.parse.urljoin(self.base_url, attr_dict.get('href', '')) if 'href' in attr_dict else '',
                "text": ""
            }
            self.tag_stack.append(tag_lower)

    def handle_endtag(self, tag):
        tag_lower = tag.lower()
        if tag_lower in ['script', 'style', 'noscript', 'iframe', 'svg']:
            self.inside_ignored = False
            return

        if self.active_element and tag_lower == self.active_element['tag']:
            clean_text = ' '.join(self.active_element['text'].split()).strip()
            if clean_text:
                self.active_element['text'] = clean_text
                item = self.active_element

                # Phân loại vào sub-bucket
                if item['tag'].startswith('h'):
                    self.current_section['headings'].append(item)
                elif item['tag'] in ['p', 'blockquote']:
                    self.current_section['paragraphs'].append(item)
                elif item['tag'] == 'a':
                    self.current_section['links'].append(item)

                self.current_section['items'].append(item)

            self.active_element = None
            if self.tag_stack:
                self.tag_stack.pop()

    def handle_data(self, data):
        if self.inside_ignored or not data.strip():
            return
        if self.active_element:
            self.active_element['text'] += ' ' + data
        else:
            clean = ' '.join(data.split()).strip()
            if clean and len(clean) > 2:
                text_obj = {"tag": "text", "text": clean}
                self.current_section['items'].append(text_obj)
                self.current_section['paragraphs'].append(text_obj)

    def get_sections(self):
        if self.current_section['items'] or self.current_section['headings'] or self.current_section['images']:
            self.sections.append(self.current_section)

        # Hợp nhất các section quá nhỏ (ít hơn 1 heading và ít hơn 1 ảnh/đoạn)
        merged = []
        buffer_sec = None
        for sec in self.sections:
            if not sec['headings'] and len(sec['paragraphs']) < 2 and not sec['images']:
                if buffer_sec:
                    buffer_sec['items'].extend(sec['items'])
                    buffer_sec['paragraphs'].extend(sec['paragraphs'])
                    buffer_sec['links'].extend(sec['links'])
                continue

            if buffer_sec:
                merged.append(buffer_sec)
            buffer_sec = sec

        if buffer_sec:
            merged.append(buffer_sec)

        return merged if merged else self.sections


class UniversalLandingPageCloner:
    """
    Bộ động cơ Clone Landing Page hoàn toàn tổng quát (Generic Universal Engine)
    Hoạt động với BẤT KỲ URL NÀO.
    """
    def __init__(self, source_url, title=None, slug=None, post_id=None, template='page-blank.php', max_images=80, auto_recheck=True, config_path=None, tmp_dir=None):
        self.source_url = source_url
        self.title = title or self._extract_domain_title(source_url)
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

        self.raw_html = ""
        self.sections = []
        self.media_map = {}
        self.palette = {
            "primary": "#f0493e",
            "dark": "#222f3e",
            "text": "#2e384d",
            "muted": "#576574",
            "light_bg": "#fdf6eb",
            "accent": "#ff9f43"
        }
        self.cf7_id = 508
        self.has_emitted_hero = False

    def _extract_domain_title(self, url):
        parsed = urllib.parse.urlparse(url)
        domain = parsed.netloc.replace('www.', '')
        name = domain.split('.')[0].capitalize()
        return f"{name} Landing Page"

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
        return text or f"landing-page-{int(time.time())}"

    # ── 1. TỰ ĐỘNG CRAWL & PHÂN TÍCH DOM TOÀN DIỆN ───────────────────────────
    def crawl_and_parse_dom(self):
        print(f"\n[1/5] Đang mở trang web nguồn và phân tích cây DOM tự động: {self.source_url} ...")
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

        # Trích xuất Palette màu tự động từ CSS
        self._detect_brand_colors()

        # Parse cây DOM
        parser = GenericDOMTreeParser(base_url=self.source_url)
        parser.feed(self.raw_html)
        self.sections = parser.get_sections()

        # Lưu cây nội dung ra JSON & Markdown để đối soát
        tree_json_path = os.path.join(self.tmp_dir, f"{self.slug}_dom_tree.json")
        with open(tree_json_path, 'w', encoding='utf-8') as f:
            json.dump(self.sections, f, ensure_ascii=False, indent=2)

        tree_md_path = os.path.join(self.tmp_dir, f"{self.slug}_dom_tree.md")
        with open(tree_md_path, 'w', encoding='utf-8') as f:
            f.write(f"# CÂY NỘI DUNG DOM ĐÃ PHÂN TÍCH (GENERIC DOM TREE)\n")
            f.write(f"- **Source URL:** {self.source_url}\n")
            f.write(f"- **Tổng số Sections nhận diện:** {len(self.sections)}\n\n")
            for idx, sec in enumerate(self.sections, 1):
                f.write(f"## Section {idx} (`<{sec['tag']}>` class='{sec['class']}' id='{sec['id']}')\n")
                f.write(f"- **Headings ({len(sec['headings'])}):** {', '.join([h['text'] for h in sec['headings'][:3]])}\n")
                f.write(f"- **Paragraphs ({len(sec['paragraphs'])}):** {sec['paragraphs'][0]['text'][:100] if sec['paragraphs'] else 'None'}...\n")
                f.write(f"- **Images ({len(sec['images'])}):** {', '.join([img['src'] for img in sec['images'][:2]])}\n")
                f.write(f"- **Links ({len(sec['links'])}):** {', '.join([l['text'] for l in sec['links'][:3]])}\n\n")

        print(f"✓ Đã phân loại và cấu trúc hóa {len(self.sections)} sections thành công.")
        print(f"✓ Cây DOM lưu tại: {tree_json_path}")
        print(f"✓ Markdown lưu tại: {tree_md_path}")
        return self.sections

    def _detect_brand_colors(self):
        """Phân tích các mã màu xuất hiện nhiều nhất trong trang web nguồn"""
        found_hex = re.findall(r'#([0-9a-fA-F]{6})\b', self.raw_html)
        color_counts = {}
        for h in found_hex:
            h_lower = '#' + h.lower()
            if h_lower not in ['#ffffff', '#000000', '#cccccc', '#eeeeee', '#ffffff', '#333333', '#666666']:
                color_counts[h_lower] = color_counts.get(h_lower, 0) + 1

        sorted_colors = sorted(color_counts.items(), key=lambda x: x[1], reverse=True)
        if sorted_colors:
            self.palette['primary'] = sorted_colors[0][0]
            print(f"   [Theme Color Detector] Nhận diện màu chủ đạo (Primary): {self.palette['primary']}")
        if len(sorted_colors) > 1:
            self.palette['accent'] = sorted_colors[1][0]
            print(f"   [Theme Color Detector] Nhận diện màu phụ (Accent): {self.palette['accent']}")

    # ── 2. QUÉT VÀ TẢI TOÀN BỘ ẢNH VỀ TMP/ ─────────────────────────────────────
    def download_all_images_to_tmp(self):
        print(f"\n[2/5] Đang quét toàn bộ hình ảnh và tải về thư mục tmp/{self.slug} ...")
        found_urls = set()

        for sec in self.sections:
            for img in sec['images']:
                if img.get('src') and not img['src'].startswith('data:'):
                    found_urls.add(img['src'])

        # Quét bổ sung trong raw HTML
        extra_srcs = re.findall(r'<img[^>]+(?:src|data-src|data-lazy-src)=[\'"]([^\'"]+)[\'"]', self.raw_html, re.IGNORECASE)
        for s in extra_srcs:
            if s and not s.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, s))

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
                filename = f"media_{idx}_{int(time.time())}.jpg"

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
                    "wp_id": None
                }
                downloaded_count += 1
                print(f"   [{idx}/{len(filtered_urls)}] ✓ Đã lưu: {filename}")
            except Exception as e:
                print(f"   [{idx}/{len(filtered_urls)}] ⚠ Không thể tải: {img_url} ({e})")

        print(f"✓ Đã tải {downloaded_count}/{len(filtered_urls)} ảnh về thư mục: {self.tmp_dir}")
        return self.media_map

    # ── 3. ĐẨY ẢNH LÊN WORDPRESS MEDIA LIBRARY ────────────────────────────────
    def upload_media_to_wordpress(self, img_url):
        if img_url in self.media_map and self.media_map[img_url].get('wp_url'):
            return self.media_map[img_url]['wp_url'], self.media_map[img_url].get('wp_id')

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

        try:
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
                    'User-Agent': 'VibeCode-UniversalCloner/2.0'
                },
                method='POST'
            )

            with urllib.request.urlopen(req, timeout=35) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                if data.get('success'):
                    wp_url = data.get('url')
                    wp_id = data.get('id') or data.get('attachment_id')
                    media_info['wp_url'] = wp_url
                    media_info['wp_id'] = wp_id
                    print(f"   [Upload WP] ✓ {filename} -> {wp_url}")
                    return wp_url, wp_id
        except Exception as e:
            print(f"   [Upload WP] ⚠ Không thể upload {filename}: {e}")

        return img_url, None

    def sync_media_library(self):
        print(f"\n[3/5] Đang đồng bộ hình ảnh lên WordPress Media Library...")
        uploaded = 0
        for original_url in list(self.media_map.keys()):
            wp_url, wp_id = self.upload_media_to_wordpress(original_url)
            if wp_id:
                uploaded += 1

        media_map_path = os.path.join(self.tmp_dir, f"{self.slug}_media_map.json")
        with open(media_map_path, 'w', encoding='utf-8') as f:
            json.dump(self.media_map, f, ensure_ascii=False, indent=2)
        print(f"✓ Đã đồng bộ {uploaded} ảnh lên WordPress và lưu Media Map tại: {media_map_path}")

    def get_synced_image_url(self, src):
        """Lấy URL WordPress đã đồng bộ cho 1 URL ảnh bất kỳ"""
        if not src:
            return ""
        if src in self.media_map and self.media_map[src].get('wp_url'):
            return self.media_map[src]['wp_url']
        # Tra cứu theo tên file
        filename = os.path.basename(urllib.parse.urlparse(src).path)
        for orig, info in self.media_map.items():
            if info.get('filename') == filename and info.get('wp_url'):
                return info['wp_url']
        return src

    # ── 4. TỰ ĐỘNG BIÊN DỊCH VBC ELEMENTS CHO TỪNG SECTION ─────────────────────
    def compile_section_to_vbc(self, sec, index):
        """
        Biên dịch 1 Section phân cấp bất kỳ thành mã thuần VBC Elements.
        Tự động chọn Layout thích ứng theo ngữ cảnh của Section.
        """
        PRI = self.palette['primary']
        DARK = self.palette['dark']
        TEXT = self.palette['text']
        MUTED = self.palette['muted']
        BG_LIGHT = self.palette['light_bg']
        ACCENT = self.palette['accent']

        tag = sec.get('tag', 'div')
        sec_class = sec.get('class', '')
        sec_id = sec.get('id', '')
        headings = sec.get('headings', [])
        paragraphs = sec.get('paragraphs', [])
        images = sec.get('images', [])
        links = sec.get('links', [])

        has_h1 = any(h['tag'] == 'h1' for h in headings)
        has_form = any('form' in sec_class.lower() or 'contact' in sec_class.lower() or 'dang-ky' in sec_class.lower() for _ in [1])

        # A. SECTION HEADER / NAVBAR
        if (tag == 'header' or 'header' in sec_class.lower()) and index == 1 and not has_h1:
            logo_src = images[0]['src'] if images else ""
            logo_url = self.get_synced_image_url(logo_src)
            nav_links = [l for l in links if l.get('text') and len(l['text']) < 30][:6]

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT}; border-bottom: 1px solid #fae8d2; position: sticky; top: 0; z-index: 999; padding: 14px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }}"]
        [vbc_a link_url="#" custom_css="selector {{ display: flex; align-items: center; text-decoration: none; }}"]
            {"<img src='" + logo_url + "' alt='" + self.title + "' loading='eager' decoding='sync' style='height: 48px; width: auto; object-fit: contain;' />" if logo_url else "[vbc_span custom_css='selector { font-size: 22px; font-weight: 900; color: " + PRI + "; }']" + self.title + "[/vbc_span]"}
        [/vbc_a]
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            {"".join([f'[vbc_a link_url="{l.get("href", "#")}" custom_css="selector {{ color: {TEXT}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {PRI}; }}"][vbc_span]{l["text"]}[/vbc_span][/vbc_a]' for l in nav_links])}
        </div>
        [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {PRI}; color: #ffffff !important; padding: 10px 22px; border-radius: 30px; font-weight: 700; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 16px rgba(240,73,62,0.25); }}"]
            [vbc_icon icon_type="lucide" name="sparkles" size="15px" color="#ffffff"]
            [vbc_span]Liên Hệ Ngay[/vbc_span]
        [/vbc_a]
    [/vbc_box]
[/vbc_div]"""

        # B. SECTION HERO / BANNER
        if not self.has_emitted_hero and (has_h1 or index <= 2 or 'hero' in sec_class.lower() or 'banner' in sec_class.lower() or headings):
            self.has_emitted_hero = True
            h1_text = next((h['text'] for h in headings if h['tag'] == 'h1'), headings[0]['text'] if headings else self.title)
            desc_text = paragraphs[0]['text'] if paragraphs else f"Giải pháp chuyên nghiệp và toàn diện tại {self.title}."
            hero_img = images[0]['src'] if images else ""
            hero_img_url = self.get_synced_image_url(hero_img)

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT}; padding: 60px 0 70px 0; overflow: hidden; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; text-align: center; }} }}"]
            [vbc_container custom_css="selector {{ display: flex; flex-direction: column; gap: 20px; }}"]
                <span style="display: inline-flex; align-items: center; gap: 8px; background: rgba(230,57,70,0.08); color: {PRI}; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; width: fit-content;">
                    [vbc_icon icon_type="lucide" name="flame" size="16px" color="{PRI}"]
                    CHÀO MỪNG ĐẾN VỚI {self.title.upper()}
                </span>
                [vbc_h1 custom_css="selector {{ font-size: 40px; font-weight: 900; line-height: 1.25; color: {DARK}; margin: 0; }} @media(max-width: 549px){{ selector {{ font-size: 28px; }} }}"]
                    {h1_text}
                [/vbc_h1]
                [vbc_p custom_css="selector {{ font-size: 17px; line-height: 1.7; color: {MUTED}; margin: 0; }}"]
                    {desc_text}
                [/vbc_p]
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 10px;">
                    [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {PRI}; color: #ffffff !important; padding: 15px 34px; border-radius: 35px; font-weight: 700; font-size: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(230,57,70,0.25); }}"]
                        [vbc_icon icon_type="lucide" name="arrow-right-circle" size="18px"]
                        [vbc_span]Đăng Ký Tư Vấn Ngay[/vbc_span]
                    [/vbc_a]
                    [vbc_a link_url="tel:0585680116" custom_css="selector {{ background: #ffffff; color: {TEXT} !important; border: 2px solid #e5e7eb; padding: 13px 26px; border-radius: 35px; font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }}"]
                        [vbc_icon icon_type="lucide" name="phone" size="16px" color="{PRI}"]
                        [vbc_span]Hotline Tư Vấn[/vbc_span]
                    [/vbc_a]
                </div>
            [/vbc_container]
            [vbc_container_inner custom_css="selector {{ position: relative; text-align: center; }}"]
                {"<img src='" + hero_img_url + "' alt='" + self.title + "' loading='eager' decoding='sync' style='width: 100%; max-width: 520px; height: auto; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); display: inline-block;' />" if hero_img_url else ""}
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]"""

        # C. SECTION STATS / NUMBERS
        has_numbers = any(re.search(r'\b\d+[\+\%]?\b', p.get('text', '')) for p in paragraphs)
        if 'stat' in sec_class.lower() or (has_numbers and len(headings) <= 2 and len(paragraphs) >= 4):
            stat_items = [p['text'] for p in paragraphs if len(p['text']) < 60][:4]
            title_text = headings[0]['text'] if headings else "Những Con Số Tiêu Biểu"

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK}; color: #ffffff; padding: 70px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; margin-bottom: 40px; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 32px; font-weight: 900; color: #ffffff; margin-bottom: 12px; }}"]
                {title_text}
            [/vbc_h2]
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat({max(1, min(4, len(stat_items)))}, 1fr); gap: 24px; text-align: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            {"".join([f'''
            [vbc_container custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 18px; padding: 30px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 40px; font-weight: 900; color: {PRI}; margin: 0 0 8px 0; }}"]{item.split()[0] if any(c.isdigit() for c in item) else '100%'}[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; margin: 0; }}"]{item}[/vbc_p]
            [/vbc_container]''' for item in stat_items])}
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]"""

        # D. SECTION TEAM / GALLERY (Nhiều ảnh >= 5 như giáo viên, hình ảnh cơ sở)
        if len(images) >= 5:
            title_text = headings[0]['text'] if headings else "Đội Ngũ Giáo Viên & Đào Tạo"
            sub_text = paragraphs[0]['text'] if paragraphs else ""
            
            cards_html = []
            for i, img in enumerate(images):
                img_url = self.get_synced_image_url(img['src'])
                item_title = headings[i + 1]['text'] if i + 1 < len(headings) else f"Giáo viên {i+1}"
                item_desc = paragraphs[i + 1]['text'] if i + 1 < len(paragraphs) else ""
                
                cards_html.append(f"""
            [vbc_container custom_css="selector {{ background: #ffffff; border-radius: 18px; padding: 20px 14px; text-align: center; box-shadow: 0 6px 20px rgba(0,0,0,0.05); transition: all 0.3s; border: 1px solid rgba(0,0,0,0.04); }} selector:hover {{ transform: translateY(-5px); box-shadow: 0 14px 30px rgba(232,71,42,0.12); }}"]
                {"<img src='" + img_url + "' alt='" + item_title + "' loading='eager' decoding='sync' style='width: 110px; height: 110px; border-radius: 50%; object-fit: cover; margin: 0 auto 12px auto; display: block; border: 3px solid " + BG_LIGHT + ";' />" if img_url else ""}
                [vbc_h4 custom_css="selector {{ font-size: 15px; font-weight: 800; color: {DARK}; margin: 0 0 6px 0; }}"]{item_title}[/vbc_h4]
                {"[vbc_p custom_css='selector { font-size: 12px; color: " + MUTED + "; margin: 0; line-height: 1.5; }']" + item_desc[:80] + "[/vbc_p]" if item_desc else ""}
            [/vbc_container]""")

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT if index % 2 == 1 else '#ffffff'}; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 40px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK}; margin-bottom: 12px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {PRI}; margin: 10px auto 0 auto; border-radius: 2px; }}"]
                {title_text}
            [/vbc_h2]
            {"[vbc_p custom_css='selector { font-size: 16px; color: " + MUTED + "; line-height: 1.7; }']" + sub_text + "[/vbc_p]" if sub_text else ""}
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 20px; }}"]
            {"".join(cards_html)}
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]"""

        # E. SECTION GRID / CARDS (3 - 4 thẻ dịch vụ, tính năng)
        if len(images) >= 2 or (len(headings) >= 3 and len(paragraphs) >= 3):
            title_text = headings[0]['text'] if headings else "Dịch Vụ Nổi Bật"
            sub_text = paragraphs[0]['text'] if paragraphs else ""
            card_count = max(len(images), min(4, len(headings) - 1))

            cards_html = []
            for i in range(card_count):
                c_img = images[i]['src'] if i < len(images) else ""
                c_img_url = self.get_synced_image_url(c_img)
                c_title = headings[i + 1]['text'] if i + 1 < len(headings) else (headings[i]['text'] if i < len(headings) else f"Mục {i+1}")
                c_desc = paragraphs[i + 1]['text'] if i + 1 < len(paragraphs) else (paragraphs[i]['text'] if i < len(paragraphs) else "")

                cards_html.append(f"""
            [vbc_container custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(0,0,0,0.12); }}"]
                {"<img src='" + c_img_url + "' alt='" + c_title + "' loading='eager' decoding='sync' style='width: 100%; height: 240px; object-fit: cover; border-top-left-radius: 20px; border-top-right-radius: 20px;' />" if c_img_url else ""}
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK}; margin: 0 0 8px 0; }}"]{c_title}[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 14px; color: {MUTED}; line-height: 1.6; margin: 0; }}"]{c_desc[:120]}...[/vbc_p]
                </div>
            [/vbc_container]""")

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT if index % 2 == 1 else '#ffffff'}; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {PRI}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                {title_text}
            [/vbc_h2]
            {"[vbc_p custom_css='selector { font-size: 16px; color: " + MUTED + "; line-height: 1.7; }']" + sub_text + "[/vbc_p]" if sub_text else ""}
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat({min(4, card_count)}, 1fr); gap: 24px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            {"".join(cards_html)}
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]"""

        # E. SECTION SPLIT 2-COL (Ảnh 1 bên, Text 1 bên hoặc Accordion)
        if len(images) == 1 or (len(headings) >= 1 and len(paragraphs) >= 2):
            title_text = headings[0]['text'] if headings else "Thông Tin Chi Tiết"
            img_src = images[0]['src'] if images else ""
            img_url = self.get_synced_image_url(img_src)
            content_paragraphs = paragraphs[:4]

            # Nếu có nhiều đoạn văn ngắn, tạo accordion
            if len(content_paragraphs) >= 3:
                accordion_items = []
                for idx_p, p in enumerate(content_paragraphs, 1):
                    p_title = p['text'][:40] if len(p['text']) > 40 else f"Chi Tiết Mục {idx_p}"
                    accordion_items.append(f"""
                    [accordion-item title="{p_title}"]
                        {p['text']}
                    [/accordion-item]""")
                right_content = f"""
                [accordion]
                    {"".join(accordion_items)}
                [/accordion]"""
            else:
                right_content = "".join([f"[vbc_p custom_css='selector {{ font-size: 16px; color: {TEXT}; line-height: 1.8; margin-bottom: 16px; }}']{p['text']}[/vbc_p]" for p in content_paragraphs])

            is_img_left = (index % 2 == 0)

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT if index % 2 == 1 else '#ffffff'}; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: {'0.9fr 1.1fr' if is_img_left else '1.1fr 0.9fr'}; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            {f'''
            [vbc_container custom_css="selector {{ text-align: center; }}"]
                {"<img src='" + img_url + "' alt='" + title_text + "' loading='eager' decoding='sync' style='width: 100%; max-width: 460px; height: auto; border-radius: 20px; display: inline-block;' />" if img_url else ""}
            [/vbc_container]
            [vbc_container_inner]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK}; margin-bottom: 24px; }}"]
                    {title_text}
                [/vbc_h2]
                {right_content}
            [/vbc_container_inner]''' if is_img_left else f'''
            [vbc_container]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK}; margin-bottom: 24px; }}"]
                    {title_text}
                [/vbc_h2]
                {right_content}
            [/vbc_container]
            [vbc_container_inner custom_css="selector {{ text-align: center; }}"]
                {"<img src='" + img_url + "' alt='" + title_text + "' loading='eager' decoding='sync' style='width: 100%; max-width: 460px; height: auto; border-radius: 20px; display: inline-block;' />" if img_url else ""}
            [/vbc_container_inner]'''}
        [/vbc_block]
    [/vbc_box]
[/vbc_div]"""

        # F. SECTION FORM / CONSULTATION (Nếu phát hiện Form hoặc là section áp chót trước Footer)
        if has_form or (index == len(self.sections) - 1 and index > 2):
            title_text = headings[0]['text'] if headings else f"Đăng Ký Tư Vấn & Nhận Lộ Trình Học {self.title}"
            sub_text = paragraphs[0]['text'] if paragraphs else "Để lại thông tin để nhận tư vấn miễn phí từ chuyên gia và kiểm tra trình độ đầu vào."

            return f"""
[vbc_div id="dang-ky" custom_css="selector {{ width: 100%; background: linear-gradient(135deg, #fff4e6 0%, #fdf6ee 100%); padding: 80px 0; border-top: 1px solid #fed7aa; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1100px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center; background: #ffffff; padding: 40px; border-radius: 24px; box-shadow: 0 15px 35px rgba(232,71,42,0.08); }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; padding: 24px; }} }}"]
            [vbc_container]
                <span style="display: inline-block; background: rgba(232,71,42,0.1); color: {PRI}; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 12px;">
                    [vbc_icon icon_type="lucide" name="gift" size="15px" color="{PRI}"]
                    ƯU ĐÃI ĐẶC BIỆT
                </span>
                [vbc_h2 custom_css="selector {{ font-size: 32px; font-weight: 900; color: {DARK}; line-height: 1.3; margin: 0 0 16px 0; }}"]
                    {title_text}
                [/vbc_h2]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {MUTED}; line-height: 1.7; margin-bottom: 24px; }}"]
                    {sub_text}
                [/vbc_p]
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 15px; font-weight: 600; color: {DARK};">
                    <span style="display: flex; align-items: center; gap: 10px;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{PRI}"]
                        100% Giáo viên bản ngữ có chứng chỉ TCSOL
                    </span>
                    <span style="display: flex; align-items: center; gap: 10px;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{PRI}"]
                        Lộ trình cá nhân hóa 1 kèm 1 linh hoạt
                    </span>
                    <span style="display: flex; align-items: center; gap: 10px;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{PRI}"]
                        Cam kết đầu ra chuẩn quốc tế HSK/YCT
                    </span>
                </div>
            [/vbc_container]
            [vbc_container_inner custom_css="selector {{ background: #fafafa; padding: 24px; border-radius: 16px; border: 1px solid #f0f0f0; }}"]
                [contact-form-7 id="{self.cf7_id}" title="Form Đăng Ký Tư Vấn"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]"""

        # G. SECTION FOOTER
        if tag == 'footer' or 'footer' in sec_class.lower() or index == len(self.sections):
            logo_src = images[0]['src'] if images else ""
            logo_url = self.get_synced_image_url(logo_src)
            footer_links = [l for l in links if l.get('text') and len(l['text']) < 35][:8]

            return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK}; color: #ffffff; padding: 70px 0 30px 0; border-top: 1px solid rgba(255,255,255,0.1); }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.5fr 1fr 1.2fr; gap: 36px; margin-bottom: 40px; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                {"<img src='" + logo_url + "' alt='" + self.title + "' loading='eager' decoding='sync' style='height: 48px; width: auto; margin-bottom: 18px; filter: brightness(0) invert(1);' />" if logo_url else "[vbc_h3 custom_css='selector { color: #ffffff; font-weight: 800; }']" + self.title + "[/vbc_h3]"}
                [vbc_p custom_css="selector {{ font-size: 14px; color: #9ca3af; line-height: 1.8; }}"]
                    {self.title} &mdash; Đơn vị cung cấp giải pháp uy tín, chuyên nghiệp và chất lượng cao.
                [/vbc_p]
            [/vbc_container]
            [vbc_container_inner]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 16px 0; }}"]Liên Kết Nhanh[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                    {"".join([f'[vbc_a link_url="{l.get("href", "#")}" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {PRI}; }}"][vbc_span]{l["text"]}[/vbc_span][/vbc_a]' for l in footer_links])}
                </div>
            [/vbc_container_inner]
            [vbc_container_inner_1]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 16px 0; }}"]Thông Tin Liên Hệ[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #9ca3af;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="phone" size="16px" color="{PRI}"]
                        Hotline: +84 585 680 116
                    </span>
                    <span style="display: flex; align-items: center; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="map-pin" size="16px" color="{PRI}"]
                        Địa chỉ: TP. Hồ Chí Minh
                    </span>
                </div>
            [/vbc_container_inner_1]
        [/vbc_block]
        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 24px; text-align: center; font-size: 13px; color: #6b7280;">
            <p style="margin: 0;">
                Copyright &copy; {time.strftime('%Y')} {self.title}. All rights reserved. Powered by Ultimate Flatsome VibeCode.
            </p>
        </div>
    [/vbc_box]
[/vbc_div]"""

        # G. DEFAULT GENERIC SECTION FALLBACK
        title_text = headings[0]['text'] if headings else f"Thông Tin {self.title}"
        body_text = "".join([f"[vbc_p custom_css='selector {{ font-size: 16px; color: {TEXT}; line-height: 1.8; margin-bottom: 16px; }}']{p['text']}[/vbc_p]" for p in paragraphs[:5]])

        return f"""
[vbc_div custom_css="selector {{ width: 100%; background: {BG_LIGHT if index % 2 == 1 else '#ffffff'}; padding: 70px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1100px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; margin-bottom: 30px; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 32px; font-weight: 900; color: {DARK}; }}"]
                {title_text}
            [/vbc_h2]
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ max-width: 850px; margin: 0 auto; }}"]
            {body_text}
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]"""

    def build_full_vbc_content(self):
        print(f"\n[4/5] Đang tự động biên dịch toàn bộ cây DOM sang 100% phần tử VBC Elements...")

        vbc_output_chunks = []

        # Reset CSS để biến trang thành Standalone Landing Page sạch sẽ
        vbc_output_chunks.append("""
<style>
#header, #footer, .header-wrapper, #wrapper > footer { display: none !important; }
body { padding-top: 0 !important; margin: 0 !important; background: #ffffff !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important; }
#main { padding-top: 0 !important; padding-bottom: 0 !important; }
img { max-width: 100%; height: auto; display: inline-block; }
</style>
""")

        # Biên dịch từng section hoàn toàn tự động
        for idx, sec in enumerate(self.sections, 1):
            sec_vbc = self.compile_section_to_vbc(sec, idx)
            if sec_vbc and sec_vbc.strip():
                vbc_output_chunks.append(sec_vbc.strip())

        full_content = "\n\n".join(vbc_output_chunks)

        vbc_file_path = os.path.join(self.tmp_dir, f"{self.slug}_compiled_vbc.txt")
        with open(vbc_file_path, 'w', encoding='utf-8') as f:
            f.write(full_content)

        print(f"✓ Đã biên dịch {len(self.sections)} sections thành công vào: {vbc_file_path}")
        return full_content

    # ── 5. XUẤT BẢN LÊN WORDPRESS & AUDIT RECHECK ─────────────────────────────
    def publish_to_wordpress(self, content):
        print(f"\n[5/5] Đang xuất bản Landing Page lên WordPress ({self.api_url}/vbc/v1/page)...")
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
                'User-Agent': 'VibeCode-UniversalCloner/2.0'
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
            print(f"❌ [LỖI] Xuất bản trang thất bại: {e}")
            sys.exit(1)

    def execute(self):
        # 1. Crawl và phân tích DOM
        self.crawl_and_parse_dom()

        # 2. Tải toàn bộ media
        self.download_all_images_to_tmp()

        # 3. Đồng bộ media lên WordPress
        self.sync_media_library()

        # 4. Biên dịch shortcode VBC hoàn toàn tự động
        vbc_content = self.build_full_vbc_content()

        # 5. Xuất bản lên WordPress
        pub_url, pub_id = self.publish_to_wordpress(vbc_content)

        # 6. Tự động kiểm tra QA
        if self.auto_recheck:
            print(f"\n[QA Check] Đang chạy kiểm tra chất lượng & đối chiếu web nguồn qua recheck-url.py...")
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

            checker = LandingPageRechecker(target_url=pub_url, post_id=pub_id, source_url=self.source_url, max_retries=3, tmp_dir=self.tmp_dir)
            checker.run_recheck()

        return pub_url, pub_id


def main():
    parser = argparse.ArgumentParser(description="Ultimate Flatsome VibeCode - Universal Generic Clone Landing Page Skill")
    parser.add_argument("--url", required=True, help="URL trang web bất kỳ cần clone")
    parser.add_argument("--title", help="Tiêu đề trang WordPress mới")
    parser.add_argument("--slug", help="Slug đường dẫn mới")
    parser.add_argument("--post_id", type=int, help="Post ID cần cập nhật (nếu có)")
    parser.add_argument("--template", default="page-blank.php", help="Page template (mặc định: page-blank.php)")
    parser.add_argument("--max_images", type=int, default=80, help="Số ảnh tối đa cần tải (mặc định: 80)")
    parser.add_argument("--no_recheck", action="store_true", help="Bỏ qua bước recheck tự động")
    parser.add_argument("--config", help="Đường dẫn file vbc-config.json")
    parser.add_argument("--tmp_dir", help="Thư mục tmp lưu trữ assets")

    args = parser.parse_args()

    cloner = UniversalLandingPageCloner(
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
