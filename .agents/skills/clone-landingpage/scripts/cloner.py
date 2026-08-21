#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - ADVANCED 1:1 CLONER ENGINE (TABS & DYNAMIC POSTS)
===============================================================================
File: cloner.py
Description:
  - Bóc tách 100% cấu trúc HTML DOM sang Native Shortcodes VBC Elements.
  - TỰ ĐỘNG NHẬN DIỆN & CHUYỂN ĐỔI TAB:
      * Nhận diện Bootstrap tabs (.nav-tabs / .tab-content), Elementor tabs, Flatsome tabbed content, ARIA tabs.
      * Biên dịch tự động sang [vbc_tabs] [vbc_tab title="..."] ... [/vbc_tab] [/vbc_tabs].
  - TỰ ĐỘNG NHẬN DIỆN & TRUY VẤN BÀI VIẾT / SẢN PHẨM TỪ BACKEND:
      * Nhận diện danh sách bài viết (.blog-posts, article.post, .penci-grid, v.v.)
      * Nhận diện danh sách sản phẩm WooCommerce (.woocommerce ul.products, .product-item, v.v.)
      * Tự động thay thế khối tĩnh bằng [vbc_post post_type="post|product" posts_per_page="X" columns="Y" layout="grid"]
        để lấy dữ liệu động trực tiếp từ WordPress database.
  - Quét & Tải toàn bộ ảnh, icons, background-image về tmp/{slug}/ và upload lên WP Media Library (/vbc/v1/upload).
  - Tự động xuất bản qua /vbc/v1/page và chạy recheck AI để kiểm định VSI >= 90%.
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
    """Tìm và nạp file cấu hình vbc-config.json"""
    search_paths = [
        custom_path,
        os.path.join(os.path.dirname(__file__), '../vbc-config.json'),
        os.path.join(os.path.dirname(__file__), '../../vbc-config.json'),
        os.path.join(os.path.dirname(__file__), '../../../vbc-config.json'),
        os.path.join(os.getcwd(), 'vbc-config.json'),
        os.path.join(os.getcwd(), 'ultimate-flatsome-vibecode/vbc-config.json')
    ]
    for p in search_paths:
        if p and os.path.exists(p):
            try:
                with open(p, 'r', encoding='utf-8') as f:
                    return json.load(f)
            except Exception as e:
                print(f"[CẢNH BÁO] Không thể đọc cấu hình tại {p}: {e}")
    return {}


# Tag mapping from HTML to VBC Elements
TAG_MAP = {
    'div': 'div', 'section': 'div', 'header': 'div', 'footer': 'div',
    'main': 'div', 'article': 'div', 'aside': 'div', 'nav': 'div',
    'h1': 'h1', 'h2': 'h2', 'h3': 'h3', 'h4': 'h4', 'h5': 'h5', 'h6': 'h6',
    'p': 'p', 'span': 'span', 'i': 'i', 'a': 'a',
    'ul': 'ul', 'ol': 'ol', 'li': 'li',
    'b': 'b', 'strong': 'strong', 'em': 'em', 'u': 'u',
    'table': 'table', 'tr': 'tr', 'td': 'td', 'th': 'th',
    'img': 'img', 'hr': 'hr', 'br': 'br',
}

DIV_ALIASES = [
    'div', 'box', 'block', 'container',
    'block_inner', 'container_inner',
    'div_inner', 'box_inner',
    'div_inner_1', 'box_inner_1', 'block_inner_1', 'container_inner_1'
]


class Node:
    def __init__(self, tag, attrs=None):
        self.tag = tag.lower() if tag else ""
        self.attrs = dict(attrs) if attrs else {}
        self.children = []
        self.text = ""
        self.is_text_node = False


class DOMBuilder(HTMLParser):
    def __init__(self):
        super().__init__()
        self.root = Node("root")
        self.stack = [self.root]
        self.ignored_tags = {'script', 'noscript'}
        self.current_ignored = None

    def handle_starttag(self, tag, attrs):
        t = tag.lower()
        if t in self.ignored_tags:
            self.current_ignored = t
            return
        if self.current_ignored:
            return

        node = Node(t, attrs)
        self.stack[-1].children.append(node)
        
        if t not in ['img', 'br', 'hr', 'input', 'meta', 'link']:
            self.stack.append(node)

    def handle_endtag(self, tag):
        t = tag.lower()
        if self.current_ignored:
            if t == self.current_ignored:
                self.current_ignored = None
            return

        for i in range(len(self.stack)-1, 0, -1):
            if self.stack[i].tag == t:
                self.stack = self.stack[:i]
                break

    def handle_data(self, data):
        if self.current_ignored:
            return
        if data:
            text_node = Node("#text")
            text_node.is_text_node = True
            text_node.text = data
            self.stack[-1].children.append(text_node)


def render_raw_svg(node):
    if node.is_text_node:
        return node.text
    attrs_str = "".join([f' {k}="{v}"' for k, v in node.attrs.items()])
    if node.tag in ['path', 'circle', 'rect', 'line', 'polygon', 'polyline']:
        return f"<{node.tag}{attrs_str}/>"
    inner = "".join([render_raw_svg(c) for c in node.children])
    return f"<{node.tag}{attrs_str}>{inner}</{node.tag}>"


def convert_node_to_vbc(node, tag_counts=None):
    if tag_counts is None:
        tag_counts = {}

    if node.is_text_node:
        return node.text

    tag = node.tag
    attrs = node.attrs
    
    if tag == 'svg':
        return render_raw_svg(node)

    # 1. TỰ ĐỘNG NHẬN DIỆN KHỐI BÀI VIẾT / SẢN PHẨM -> Chuyển thành [vbc_post] backend
    classes = attrs.get('class', '').lower()
    tag_id = attrs.get('id', '').lower()

    # Kiểm tra danh sách sản phẩm WooCommerce
    if any(k in classes for k in ['products', 'woocommerce-products', 'product-grid', 'shop-products']) and not classes.startswith('product '):
        product_items = [c for c in node.children if 'product' in c.attrs.get('class', '').lower()]
        count = len(product_items) if product_items else 8
        cols = 4
        if 'columns-3' in classes or 'col-3' in classes: cols = 3
        elif 'columns-2' in classes or 'col-2' in classes: cols = 2
        elif 'columns-5' in classes or 'col-5' in classes: cols = 5
        return f'\n[vbc_post post_type="product" posts_per_page="{count}" columns="{cols}" layout="grid" title_size="16px"]\n'

    # Kiểm tra danh sách bài viết Blog / Tin tức
    if any(k in classes for k in ['blog-posts', 'posts-grid', 'penci-grid', 'latest-posts', 'news-grid', 'archive-posts']) or any(k in tag_id for k in ['blog-posts', 'latest-posts']):
        post_items = [c for c in node.children if any(p in c.attrs.get('class', '').lower() for p in ['post', 'article', 'entry', 'grid-item'])]
        count = len(post_items) if post_items else 6
        cols = 3
        if 'columns-2' in classes or 'col-2' in classes or 'two-columns' in classes: cols = 2
        elif 'columns-4' in classes or 'col-4' in classes or 'four-columns' in classes: cols = 4
        return f'\n[vbc_post post_type="post" posts_per_page="{count}" columns="{cols}" layout="grid" title_size="18px"]\n'

    # 2. TỰ ĐỘNG NHẬN DIỆN HỆ THỐNG TABS -> Chuyển thành [vbc_tabs] & [vbc_tab]
    if any(k in classes for k in ['nav-tabs', 'elementor-tabs', 'tabbed-content', 'vbc-tabs-wrapper', 'tabs-container']) or attrs.get('role') == 'tablist':
        # Xử lý các tab items con
        tab_titles = []
        tab_contents = []
        # Tìm tiêu đề tabs và panels
        # Duyệt cây DOM con để gom title và content tương ứng
        return convert_tabs_to_vbc(node, tag_counts)

    base_type = TAG_MAP.get(tag)
    if not base_type:
        inner = "".join([convert_node_to_vbc(c, tag_counts) for c in node.children])
        return inner

    current_count = tag_counts.get(base_type, 0)
    
    if base_type == 'div':
        alias_name = DIV_ALIASES[current_count % len(DIV_ALIASES)]
        vbc_shortcode = f"vbc_{alias_name}"
    else:
        if current_count == 0:
            vbc_shortcode = f"vbc_{base_type}"
        elif current_count == 1:
            vbc_shortcode = f"vbc_{base_type}_inner"
        else:
            suffix_num = min(current_count - 1, 5)
            vbc_shortcode = f"vbc_{base_type}_inner_{suffix_num}"

    atts = []
    if 'id' in attrs and attrs['id']:
        atts.append(f'id="{attrs["id"]}"')
    if 'class' in attrs and attrs['class']:
        atts.append(f'class="{attrs["class"]}"')
    
    if 'style' in attrs and attrs['style']:
        css_style = attrs['style'].strip().rstrip(';')
        clean_css = f"selector {{ {css_style}; }}"
        clean_css = clean_css.replace('"', "'")
        atts.append(f'custom_css="{clean_css}"')

    if tag == 'a':
        link_url = attrs.get('href', '#')
        target = attrs.get('target', '_self')
        atts.append(f'link_url="{link_url}"')
        if target != '_self':
            atts.append(f'link_target="{target}"')
    elif tag == 'img':
        img_url = attrs.get('src') or attrs.get('data-src') or attrs.get('data-lazy-src') or ''
        alt = attrs.get('alt', '')
        atts.append('img_source="manual"')
        atts.append(f'img_url="{img_url}"')
        if alt:
            atts.append(f'alt="{alt}"')
        att_str = " " + " ".join(atts) if atts else ""
        return f"[{vbc_shortcode}{att_str}]"
    elif tag in ['hr', 'br']:
        att_str = " " + " ".join(atts) if atts else ""
        return f"[{vbc_shortcode}{att_str}]"

    att_str = " " + " ".join(atts) if atts else ""
    
    new_counts = tag_counts.copy()
    new_counts[base_type] = current_count + 1
    
    inner_content = "".join([convert_node_to_vbc(c, new_counts) for c in node.children])
    
    return f"[{vbc_shortcode}{att_str}]{inner_content}[/{vbc_shortcode}]"


def convert_tabs_to_vbc(node, tag_counts):
    """Bóc tách cấu trúc Tab đa dạng sang [vbc_tabs] chuẩn"""
    tabs_output = ['[vbc_tabs style="pills" align="left"]']
    
    # Tìm các tab-item hoặc tab-pane
    tab_panes = []
    def find_panes(n):
        c_cls = n.attrs.get('class', '').lower()
        if any(k in c_cls for k in ['tab-pane', 'elementor-tab-content', 'tab-panel']) or n.attrs.get('role') == 'tabpanel':
            tab_panes.append(n)
        for c in n.children:
            find_panes(c)
    find_panes(node)

    if tab_panes:
        for idx, pane in enumerate(tab_panes, 1):
            title = pane.attrs.get('data-title') or pane.attrs.get('aria-label') or f"Tab {idx}"
            content_vbc = "".join([convert_node_to_vbc(c, tag_counts) for c in pane.children])
            tabs_output.append(f'  [vbc_tab title="{title}"]\n{content_vbc}\n  [/vbc_tab]')
    else:
        # Fallback render children
        for c in node.children:
            tabs_output.append(convert_node_to_vbc(c, tag_counts))

    tabs_output.append('[/vbc_tabs]')
    return "\n".join(tabs_output)


def convert_html_to_vbc_ast(html_code):
    """Compile HTML tree to Native VBC Shortcodes AST"""
    parser = DOMBuilder()
    parser.feed(html_code)
    
    vbc_parts = []
    for top_child in parser.root.children:
        vbc_parts.append(convert_node_to_vbc(top_child))
    
    return "\n".join(vbc_parts)


class LandingPageCloner:
    def __init__(self, source_url, title="", slug="", post_id=None, template="page-blank.php", auto_recheck=True, tmp_dir=None):
        self.source_url = source_url
        self.config = load_vbc_config()
        self.api_url = self.config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
        self.token = self.config.get('token', '060bed653d61c4140ba69689de2ade9e562f3456')
        
        self.slug = slug or self._generate_slug_from_url(source_url)
        self.title = title or self.slug.replace('-', ' ').title()
        self.post_id = post_id
        self.template = template
        self.auto_recheck = auto_recheck
        self.tmp_dir = tmp_dir or os.path.join('tmp', self.slug)
        os.makedirs(self.tmp_dir, exist_ok=True)

        self.raw_html = ""
        self.extracted_styles = []
        self.extracted_scripts = []
        self.main_content_html = ""
        self.media_map = {}

    def _generate_slug_from_url(self, url):
        path = urllib.parse.urlparse(url).path.strip('/')
        if not path:
            return 'cloned-landing-page'
        last_seg = path.split('/')[-1]
        slug = re.sub(r'[^a-zA-Z0-9_-]', '-', last_seg).lower()
        return slug or 'cloned-landing-page'

    def fetch_source_page(self):
        """Tải toàn bộ mã nguồn HTML từ trang web nguồn"""
        print(f"[1/5] Đang tải mã nguồn từ: {self.source_url}")
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'vi,en-US;q=0.9,en;q=0.8'
        }
        req = urllib.request.Request(self.source_url, headers=headers)
        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                self.raw_html = resp.read().decode('utf-8', errors='ignore')
        except Exception as e:
            print(f"[LỖI] Không thể tải URL {self.source_url}: {e}")
            local_source = os.path.join(self.tmp_dir, 'source.html')
            if os.path.exists(local_source):
                print(f"[Fallback] Đang sử dụng mã nguồn lưu tạm tại {local_source}")
                with open(local_source, 'r', encoding='utf-8') as f:
                    self.raw_html = f.read()
            else:
                raise e

        with open(os.path.join(self.tmp_dir, 'source.html'), 'w', encoding='utf-8') as f:
            f.write(self.raw_html)

        if not self.title or self.title == self.slug.replace('-', ' ').title():
            t_match = re.search(r'<title>(.*?)</title>', self.raw_html, re.IGNORECASE)
            if t_match:
                self.title = unescape(t_match.group(1).strip())

        print(f"   -> Đã tải thành công: {len(self.raw_html)} ký tự | Tiêu đề: {self.title}")

    def extract_exact_dom_and_styles(self):
        """Bóc tách chính xác 100% các khối CSS và Cây DOM của trang web"""
        print(f"[2/5] Đang bóc tách cấu trúc DOM và Stylesheet chính xác 1:1...")
        
        styles = re.findall(r'<style[^>]*>(.*?)</style>', self.raw_html, re.DOTALL | re.IGNORECASE)
        relevant_styles = []
        for s in styles:
            if len(s.strip()) > 30:
                relevant_styles.append(s.strip())
        
        self.extracted_styles = relevant_styles

        patterns = [
            r'(<div id="dv-[^"]*"[^>]*>.*?</div>\s*<!--.*?-->|<div id="dv-[^"]*"[^>]*>.*?</div>\s*</div>)',
            r'(<div class="[^"]*(?:landing|lp-|page-content|elementor elementor-|entry-content)[^"]*"[^>]*>.*?</div>\s*<!-- \.entry-content -->|<div class="[^"]*entry-content[^"]*"[^>]*>.*?</div>)',
            r'(<main[^>]*>.*?</main>)',
            r'(<article[^>]*>.*?</article>)'
        ]

        main_match = None
        for pat in patterns:
            main_match = re.search(pat, self.raw_html, re.DOTALL | re.IGNORECASE)
            if main_match:
                self.main_content_html = main_match.group(1)
                break

        if not self.main_content_html:
            body_m = re.search(r'<body[^>]*>(.*?)</body>', self.raw_html, re.DOTALL | re.IGNORECASE)
            self.main_content_html = body_m.group(1) if body_m else self.raw_html

        print(f"   -> Đã bóc tách khối nội dung chính: {len(self.main_content_html)} ký tự")

    def sync_all_images_to_wordpress(self):
        """Quét toàn bộ ảnh trong HTML & CSS, tải về và đồng bộ lên WordPress Media Library"""
        print(f"[3/5] Đang quét và đồng bộ hình ảnh lên WordPress Media Library...")

        img_srcs = re.findall(r'<img[^>]+(?:src|data-src|data-lazy-src)=[\'"]([^\'"]+)[\'"]', self.main_content_html + self.raw_html, re.IGNORECASE)
        bg_srcs = re.findall(r'url\([\'"]?(https?://[^\'")\s]+)[\'"]?\)', self.main_content_html + "\n".join(self.extracted_styles), re.IGNORECASE)

        all_imgs = []
        for src in img_srcs + bg_srcs:
            if src and not src.startswith('data:') and not src.startswith('#'):
                abs_url = urllib.parse.urljoin(self.source_url, src)
                if abs_url not in all_imgs:
                    all_imgs.append(abs_url)

        print(f"   -> Phát hiện {len(all_imgs)} hình ảnh cần đồng bộ.")

        media_map_file = os.path.join(self.tmp_dir, 'media_map.json')
        if os.path.exists(media_map_file):
            try:
                with open(media_map_file, 'r', encoding='utf-8') as f:
                    self.media_map = json.load(f)
            except Exception:
                self.media_map = {}

        for idx, img_url in enumerate(all_imgs, 1):
            if img_url in self.media_map and self.media_map[img_url].startswith('http'):
                continue

            filename = os.path.basename(urllib.parse.urlparse(img_url).path)
            if not filename or len(filename) > 50:
                filename = f"media_{idx}.png"
            local_path = os.path.join(self.tmp_dir, filename)

            try:
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
                with urllib.request.urlopen(req, timeout=25) as r, open(local_path, 'wb') as f:
                    f.write(r.read())

                upload_endpoint = f"{self.api_url}/vbc/v1/upload"
                boundary = '----WebKitFormBoundaryVbc' + str(int(time.time()))
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

                up_req = urllib.request.Request(
                    upload_endpoint,
                    data=body,
                    headers={
                        'Content-Type': f'multipart/form-data; boundary={boundary}',
                        'X-VBC-Token': self.token,
                        'User-Agent': 'VibeCode-Cloner/2.0'
                    },
                    method='POST'
                )

                with urllib.request.urlopen(up_req, timeout=35) as resp:
                    data = json.loads(resp.read().decode('utf-8'))
                    if data.get('success'):
                        wp_url = data.get('url')
                        self.media_map[img_url] = wp_url
                        print(f"   [{idx}/{len(all_imgs)}] ✓ {filename} -> {wp_url}")
                    else:
                        self.media_map[img_url] = img_url
            except Exception:
                self.media_map[img_url] = img_url

        with open(media_map_file, 'w', encoding='utf-8') as f:
            json.dump(self.media_map, f, ensure_ascii=False, indent=2)

    def compile_vbc_content(self):
        """Biên dịch mã nguồn HTML & CSS sang 100% Native Shortcodes VBC Elements"""
        print(f"[4/5] Đang biên dịch mã nguồn sang 100% Native Shortcodes VBC Elements...")

        processed_html = self.main_content_html
        for orig_url, wp_url in self.media_map.items():
            if orig_url and wp_url:
                processed_html = processed_html.replace(orig_url, wp_url)

        processed_html = re.sub(r'data-src=[\'"]([^\'"]+)[\'"]', r'src="\1"', processed_html)
        processed_html = re.sub(r'data-lazy-src=[\'"]([^\'"]+)[\'"]', r'src="\1"', processed_html)
        processed_html = re.sub(r'loading=[\'"]lazy[\'"]', 'loading="eager"', processed_html)

        # Chuyển đổi toàn bộ DOM sang AST Shortcodes VBC Elements
        print(f"   -> Đang chuyển đổi cây DOM sang Native VBC Elements (Tabs, Posts, Cards, Containers)...")
        vbc_body = convert_html_to_vbc_ast(processed_html)

        all_css = "\n".join(self.extracted_styles)
        for orig_url, wp_url in self.media_map.items():
            if orig_url and wp_url:
                all_css = all_css.replace(orig_url, wp_url)

        reset_css = f"""
<style>
#header, #footer, .header-wrapper, #wrapper > footer {{ display: none !important; }}
body {{ padding-top: 0 !important; margin: 0 !important; background: #ffffff !important; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }}
#main {{ padding-top: 0 !important; padding-bottom: 0 !important; }}
img {{ max-width: 100%; height: auto; }}
{all_css}
</style>
"""

        vbc_output = f"""{reset_css}

{vbc_body}
"""
        with open(os.path.join(self.tmp_dir, 'compiled_vbc.txt'), 'w', encoding='utf-8') as f:
            f.write(vbc_output)

        print(f"   -> Đã biên dịch xong: {len(vbc_output)} bytes.")
        return vbc_output

    def publish_to_wordpress(self, vbc_content):
        """Xuất bản nội dung lên WordPress REST API"""
        print(f"[5/5] Đang xuất bản lên WordPress ({self.api_url}/vbc/v1/page)...")
        payload = {
            'title': self.title,
            'slug': self.slug,
            'content': vbc_content,
            'template': self.template,
            'status': 'publish'
        }
        if self.post_id:
            payload['id'] = self.post_id

        pub_req = urllib.request.Request(
            f"{self.api_url}/vbc/v1/page",
            data=json.dumps(payload).encode('utf-8'),
            headers={
                'Content-Type': 'application/json; charset=utf-8',
                'X-VBC-Token': self.token,
                'User-Agent': 'VibeCode-Cloner/2.0'
            },
            method='POST'
        )

        with urllib.request.urlopen(pub_req, timeout=40) as resp:
            res = json.loads(resp.read().decode('utf-8'))
            pub_url = res.get('url') or res.get('link')
            pub_id = res.get('post_id') or res.get('id')
            print("\n" + "="*60)
            print("   🎉 XUẤT BẢN LANDING PAGE THÀNH CÔNG!")
            print("="*60)
            print(f"Post ID  : {pub_id}")
            print(f"Page URL : {pub_url}")
            print("="*60)
            return pub_url, pub_id

    def run(self):
        self.fetch_source_page()
        self.extract_exact_dom_and_styles()
        self.sync_all_images_to_wordpress()
        vbc_content = self.compile_vbc_content()
        pub_url, pub_id = self.publish_to_wordpress(vbc_content)

        if self.auto_recheck:
            possible_recheck_paths = [
                os.path.join(os.path.dirname(__file__), '..', '..', 'recheck-url', 'scripts', 'rechecker.py'),
                os.path.join(os.path.dirname(__file__), '..', 'recheck-url', 'scripts', 'rechecker.py'),
                os.path.join(os.getcwd(), '.agents', 'skills', 'recheck-url', 'scripts', 'rechecker.py'),
                os.path.join(os.getcwd(), 'skills', 'recheck-url', 'scripts', 'rechecker.py')
            ]
            recheck_file = next((p for p in possible_recheck_paths if os.path.exists(p)), None)
            if recheck_file:
                import importlib.util
                spec = importlib.util.spec_from_file_location("recheck_module", recheck_file)
                recheck_module = importlib.util.module_from_spec(spec)
                spec.loader.exec_module(recheck_module)
                LandingPageRechecker = recheck_module.LandingPageRechecker
                checker = LandingPageRechecker(target_url=pub_url, post_id=pub_id, source_url=self.source_url, max_retries=3, tmp_dir=self.tmp_dir)
                checker.run_recheck()

        return pub_url, pub_id


def main():
    parser = argparse.ArgumentParser(description="Ultimate Flatsome VibeCode - Advanced 1:1 Cloner (Tabs & Posts)")
    parser.add_argument("--url", required=True, help="URL của trang web cần clone")
    parser.add_argument("--title", default="", help="Tiêu đề trang trên WordPress")
    parser.add_argument("--slug", default="", help="Slug URL của trang đích")
    parser.add_argument("--post_id", type=int, default=None, help="ID của trang nếu cần ghi đè")
    parser.add_argument("--template", default="page-blank.php", help="Page template")
    parser.add_argument("--no_recheck", action="store_true", help="Bỏ qua bước tự động recheck")

    args = parser.parse_args()

    cloner = LandingPageCloner(
        source_url=args.url,
        title=args.title,
        slug=args.slug,
        post_id=args.post_id,
        template=args.template,
        auto_recheck=(not args.no_recheck)
    )
    cloner.run()


if __name__ == '__main__':
    main()
