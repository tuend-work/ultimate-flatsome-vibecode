# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - HTML TO VBC ELEMENTS COMPILER (TREE PARSER)
===============================================================================
File: html_to_vbc.py
Description:
  Chuyển đổi cây HTML sang hệ thống shortcode [vbc_*] của plugin:
  1. Header / Section -> [vbc_div]
  2. Container -> [vbc_box]
  3. Row / Grid -> [vbc_block]
  4. Card / Item -> [vbc_container]
  5. Nested Containers -> [vbc_container_inner], [vbc_container_inner_1]...
  6. Typography -> [vbc_h1], [vbc_h2], [vbc_h3], [vbc_h4], [vbc_p], [vbc_span], [vbc_strong]
  7. Links / Buttons -> [vbc_a link_url="..."]
  8. Images -> [vbc_img img_url="..." alt="..." class="..."]
  9. Lists -> [vbc_ul], [vbc_li]
  10. Forms -> [contact-form-7 id="..." title="..."]
===============================================================================
"""

import re
import sys
from html.parser import HTMLParser

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass


class VBCCompilerParser(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=False)
        self.output = []
        self.container_stack = []
        self.CONTAINER_TAGS = [
            'vbc_div',
            'vbc_box',
            'vbc_block',
            'vbc_container',
            'vbc_container_inner',
            'vbc_container_inner_1',
            'vbc_container_inner_2',
            'vbc_container_inner_3',
            'vbc_container_inner_4',
            'vbc_container_inner_5'
        ]

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        cls = attr_dict.get('class', '')
        custom_css = attr_dict.get('style', '')

        # 1. Thẻ Style - giữ nguyên
        if tag == 'style':
            self.output.append(f"<style>")
            return

        # 2. Void Tag: IMG -> [vbc_img]
        if tag == 'img':
            src = attr_dict.get('src', '')
            alt = attr_dict.get('alt', '')
            attrs_str = []
            if src:
                attrs_str.append(f'img_url="{src}"')
            if alt:
                attrs_str.append(f'alt="{alt}"')
            if cls:
                attrs_str.append(f'class="{cls}"')
            self.output.append(f'[vbc_img {" ".join(attrs_str)}]')
            return

        # 3. Void Tag: BR, HR
        if tag == 'br':
            self.output.append('<br>')
            return
        if tag == 'hr':
            self.output.append(f'[vbc_hr class="{cls}"]' if cls else '[vbc_hr]')
            return

        # 4. Typography Tags (h1 - h6, p, span, strong, b, em, i)
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'strong', 'b', 'em', 'i']:
            vbc_tag = f'vbc_{tag}'
            cls_attr = f' class="{cls}"' if cls else ''
            self.output.append(f'[{vbc_tag}{cls_attr}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 5. Link Tag: A -> [vbc_a]
        if tag == 'a':
            href = attr_dict.get('href', '#')
            target = attr_dict.get('target', '_self')
            attrs_str = [f'link_url="{href}"']
            if target and target != '_self':
                attrs_str.append(f'link_target="{target}"')
            if cls:
                attrs_str.append(f'class="{cls}"')
            self.output.append(f'[vbc_a {" ".join(attrs_str)}]')
            self.container_stack.append(('leaf', 'vbc_a'))
            return

        # 6. List Tags: UL, OL, LI
        if tag in ['ul', 'ol', 'li']:
            vbc_tag = f'vbc_{tag}'
            cls_attr = f' class="{cls}"' if cls else ''
            self.output.append(f'[{vbc_tag}{cls_attr}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 7. Button Tag -> [vbc_span class="..."] (Leaf button style)
        if tag == 'button':
            cls_attr = f' class="{cls}"' if cls else ''
            self.output.append(f'[vbc_span{cls_attr}]')
            self.container_stack.append(('leaf', 'vbc_span'))
            return

        # 8. Container Tags (div, header, section, footer, nav, main, article, aside)
        if tag in ['div', 'header', 'section', 'footer', 'nav', 'main', 'article', 'aside']:
            # Đếm số lượng container cha đang mở trong stack
            container_depth = sum(1 for kind, _ in self.container_stack if kind == 'container')
            if container_depth < len(self.CONTAINER_TAGS):
                vbc_tag = self.CONTAINER_TAGS[container_depth]
            else:
                vbc_tag = self.CONTAINER_TAGS[-1]

            cls_attr = f' class="{cls}"' if cls else ''
            self.output.append(f'[{vbc_tag}{cls_attr}]')
            self.container_stack.append(('container', vbc_tag))
            return

        # Các thẻ khác giữ nguyên
        self.output.append(f'<{tag}>')
        self.container_stack.append(('raw', tag))

    def handle_endtag(self, tag):
        if tag == 'style':
            self.output.append('</style>')
            return
        if tag in ['img', 'br', 'hr']:
            return

        if self.container_stack:
            kind, vbc_tag = self.container_stack.pop()
            if kind in ['container', 'leaf']:
                self.output.append(f'[/{vbc_tag}]')
            else:
                self.output.append(f'</{vbc_tag}>')
        else:
            self.output.append(f'</{tag}>')

    def handle_data(self, data):
        self.output.append(data)

    def handle_comment(self, data):
        self.output.append(f'<!--{data}-->')


def compile_html_to_vbc(html_content):
    """Chuyển đổi cây HTML sang VBC Shortcodes chuẩn UX Builder"""
    if not html_content:
        return ""

    content = html_content

    # 1. Bảo vệ các khối <style>...</style>
    style_blocks = []
    def _save_style(m):
        style_blocks.append(m.group(0))
        return f"<!-- VBC_STYLE_PLACEHOLDER_{len(style_blocks)-1} -->"
    content = re.sub(r'<style\b[^>]*>.*?</style>', _save_style, content, flags=re.DOTALL | re.IGNORECASE)

    # 2. Bảo vệ các shortcode [contact-form-7 ...]
    cf7_blocks = []
    def _save_cf7(m):
        cf7_blocks.append(m.group(0))
        return f"<!-- VBC_CF7_PLACEHOLDER_{len(cf7_blocks)-1} -->"
    content = re.sub(r'\[contact-form-7\b[^\]]*\]', _save_cf7, content, flags=re.IGNORECASE)

    # 3. Dùng HTML Parser để chuyển đổi cây DOM
    parser = VBCCompilerParser()
    try:
        parser.feed(content)
        compiled = ''.join(parser.output)
    except Exception as e:
        print(f"[CẢNH BÁO] HTML Parser fallback: {e}")
        compiled = content

    # 4. Khôi phục Contact Form 7 và Style Blocks
    for idx, cf7 in enumerate(cf7_blocks):
        compiled = compiled.replace(f"<!-- VBC_CF7_PLACEHOLDER_{idx} -->", cf7)

    for idx, st in enumerate(style_blocks):
        compiled = compiled.replace(f"<!-- VBC_STYLE_PLACEHOLDER_{idx} -->", st)

    return compiled
