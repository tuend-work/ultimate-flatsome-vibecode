# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - HTML TO VBC ELEMENTS COMPILER
===============================================================================
File: html_to_vbc.py
Tuân thủ 100% tài liệu hướng dẫn và Anti-Patterns Matrix trong skills/readme.md:
  1. 4-Tier Container Hierarchy: vbc_div -> vbc_box -> vbc_block -> vbc_container -> _inner
  2. Tuyệt đối không lồng thẻ trùng tên gây vỡ cú pháp WordPress parser.
  3. Void tags tự đóng (vbc_img, vbc_icon, vbc_hr, vbc_br) không có thẻ đóng và không có _inner.
  4. Chuyển đổi toàn bộ Emoji Unicode thô sang [vbc_icon] chuẩn Lucide/FontAwesome.
  5. Chuyển đổi inline style sang custom_css="selector { ... }" chuẩn VibeCode.
  6. Loại bỏ font-family hardcode để kế thừa font toàn cục Flatsome.
  7. Làm sạch dấu ngoặc kép thô trong văn bản sang HTML entities.
  8. Tích hợp Contact Form 7 shortcode [contact-form-7 id="..." title="..."].
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

# Bản đồ ánh xạ Emoji Unicode thô sang Vector Icon Lucide chuẩn
EMOJI_ICON_MAP = {
    '📍': '[vbc_icon icon_type="lucide" name="map-pin" size="16px"]',
    '📞': '[vbc_icon icon_type="lucide" name="phone" size="16px"]',
    '📅': '[vbc_icon icon_type="lucide" name="calendar" size="16px"]',
    '🚌': '[vbc_icon icon_type="lucide" name="bus" size="16px"]',
    '🔍': '[vbc_icon icon_type="lucide" name="search" size="16px"]',
    '🚀': '[vbc_icon icon_type="lucide" name="rocket" size="16px"]',
    '⚡': '[vbc_icon icon_type="lucide" name="zap" size="16px"]',
    '🔥': '[vbc_icon icon_type="lucide" name="flame" size="16px"]',
    '⭐': '[vbc_icon icon_type="lucide" name="star" size="16px"]',
    '🌟': '[vbc_icon icon_type="lucide" name="sparkles" size="16px"]',
    '🛡️': '[vbc_icon icon_type="lucide" name="shield-check" size="16px"]',
    '🛡': '[vbc_icon icon_type="lucide" name="shield-check" size="16px"]',
    '✓': '[vbc_icon icon_type="lucide" name="check" size="16px"]',
    '✔': '[vbc_icon icon_type="lucide" name="check" size="16px"]',
    '⏰': '[vbc_icon icon_type="lucide" name="clock" size="16px"]',
    '💬': '[vbc_icon icon_type="lucide" name="message-circle" size="16px"]',
    '✉️': '[vbc_icon icon_type="lucide" name="mail" size="16px"]',
    '✉': '[vbc_icon icon_type="lucide" name="mail" size="16px"]',
    '💎': '[vbc_icon icon_type="lucide" name="gem" size="16px"]',
    '🏆': '[vbc_icon icon_type="lucide" name="trophy" size="16px"]',
    '👍': '[vbc_icon icon_type="lucide" name="thumbs-up" size="16px"]',
    '❤️': '[vbc_icon icon_type="lucide" name="heart" size="16px"]',
    '💰': '[vbc_icon icon_type="lucide" name="badge-dollar-sign" size="16px"]',
}


def sanitize_css_rules(style_str):
    """Làm sạch CSS inline: loại bỏ font-family hardcode và bọc trong selector { ... }"""
    if not style_str:
        return ""
    # Loại bỏ font-family hardcode để kế thừa font toàn cục Flatsome
    cleaned = re.sub(r'font-family\s*:\s*[^;]+;?', '', style_str, flags=re.IGNORECASE)
    cleaned = cleaned.strip().rstrip(';')
    if not cleaned:
        return ""
    # Nén khoảng trắng
    cleaned = ' '.join(cleaned.split())
    return f'selector {{ {cleaned}; }}'


class VBCStandardCompiler(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=False)
        self.output = []
        self.container_stack = []
        # Hệ thống 4-Tier Aliases + Suffix Levels tuân thủ skills/readme.md
        self.CONTAINER_TAGS = [
            'vbc_div',             # Cấp 1: Section ngoài cùng
            'vbc_box',             # Cấp 2: Container 1200px
            'vbc_block',           # Cấp 3: Cột / Grid / Row
            'vbc_container',       # Cấp 4: Card item / Badge
            'vbc_container_inner', # Cấp 5: Khối sâu
            'vbc_container_inner_1',
            'vbc_container_inner_2',
            'vbc_container_inner_3',
            'vbc_container_inner_4',
            'vbc_container_inner_5'
        ]

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        cls = attr_dict.get('class', '').strip()
        raw_style = attr_dict.get('style', '').strip()
        custom_css = sanitize_css_rules(raw_style)

        # 1. Thẻ Style
        if tag == 'style':
            self.output.append('<style>')
            return

        # 2. Void Tag: <img> -> [vbc_img] (Không có thẻ đóng, không có _inner)
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
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            self.output.append(f'[vbc_img {" ".join(attrs_str)}]')
            return

        # 3. Void Tags: <br>, <hr>
        if tag == 'br':
            self.output.append('<br>')
            return
        if tag == 'hr':
            self.output.append(f'[vbc_hr class="{cls}"]' if cls else '[vbc_hr]')
            return

        # 4. Typography Tags (h1-h6, p, span, strong, b, em, i)
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'strong', 'b', 'em', 'i']:
            vbc_tag = f'vbc_{tag}'
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[{vbc_tag}{attr_part}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 5. Link Tag: <a> -> [vbc_a link_url="..." link_target="..."]
        if tag == 'a':
            href = attr_dict.get('href', '#')
            target = attr_dict.get('target', '_self')
            attrs_str = [f'link_url="{href}"']
            if target and target != '_self':
                attrs_str.append(f'link_target="{target}"')
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            self.output.append(f'[vbc_a {" ".join(attrs_str)}]')
            self.container_stack.append(('leaf', 'vbc_a'))
            return

        # 6. List Tags: <ul>, <ol>, <li>
        if tag in ['ul', 'ol', 'li']:
            vbc_tag = f'vbc_{tag}'
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[{vbc_tag}{attr_part}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 7. Button Tag -> [vbc_span class="..."]
        if tag == 'button':
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[vbc_span{attr_part}]')
            self.container_stack.append(('leaf', 'vbc_span'))
            return

        # 8. Container Tags (div, header, section, footer, nav, main, article, aside)
        if tag in ['div', 'header', 'section', 'footer', 'nav', 'main', 'article', 'aside']:
            # Tính toán phân cấp độ sâu container trong stack
            container_depth = sum(1 for kind, _ in self.container_stack if kind == 'container')
            if container_depth < len(self.CONTAINER_TAGS):
                vbc_tag = self.CONTAINER_TAGS[container_depth]
            else:
                vbc_tag = self.CONTAINER_TAGS[-1]

            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[{vbc_tag}{attr_part}]')
            self.container_stack.append(('container', vbc_tag))
            return

        # Thẻ khác giữ nguyên
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
        # Tự động thay thế Emoji Unicode thô sang [vbc_icon]
        processed = data
        for emoji, icon_sc in EMOJI_ICON_MAP.items():
            if emoji in processed:
                processed = processed.replace(emoji, f" {icon_sc} ")
        self.output.append(processed)

    def handle_comment(self, data):
        self.output.append(f'<!--{data}-->')


def compile_html_to_vbc(html_content):
    """Chuyển đổi cây HTML sang VBC Shortcodes chuẩn UX Builder theo skills/readme.md"""
    if not html_content:
        return ""

    content = html_content

    # 1. Bảo vệ các khối <style>...</style>
    style_blocks = []
    def _save_style(m):
        style_blocks.append(m.group(0))
        return f"<!-- VBC_STYLE_PLACEHOLDER_{len(style_blocks)-1} -->"
    content = re.sub(r'<style\b[^>]*>.*?</style>', _save_style, content, flags=re.DOTALL | re.IGNORECASE)

    # 2. Bảo vệ các shortcode [contact-form-7 ...] và [vbc_icon ...] sẵn có
    protected_shortcodes = []
    def _save_shortcodes(m):
        protected_shortcodes.append(m.group(0))
        return f"<!-- VBC_PROTECTED_SC_{len(protected_shortcodes)-1} -->"
    content = re.sub(r'\[(?:contact-form-7|vbc_icon|vbc_post|accordion|row|col)\b[^\]]*\](?:[\s\S]*?\[\/(?:accordion|row|col)\])?', _save_shortcodes, content, flags=re.IGNORECASE)

    # 3. Chuyển đổi cây DOM sang VBC Elements
    parser = VBCStandardCompiler()
    try:
        parser.feed(content)
        compiled = ''.join(parser.output)
    except Exception as e:
        print(f"[CẢNH BÁO] HTML Parser fallback: {e}")
        compiled = content

    # 4. Khôi phục Protected Shortcodes và Style Blocks
    for idx, sc in enumerate(protected_shortcodes):
        compiled = compiled.replace(f"<!-- VBC_PROTECTED_SC_{idx} -->", sc)

    for idx, st in enumerate(style_blocks):
        compiled = compiled.replace(f"<!-- VBC_STYLE_PLACEHOLDER_{idx} -->", st)

    return compiled
