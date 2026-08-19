# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - HTML TO VBC ELEMENTS COMPILER
===============================================================================
File: html_to_vbc.py
Description:
  1. Tự động biên dịch và đưa CSS vào từng phần tử dưới dạng custom_css="selector { ... }".
  2. Hỗ trợ toàn diện:
     - Root class rules: selector { ... }
     - Pseudo selectors (:hover, :focus, ::after, ::before): selector:hover { ... }
     - Descendant selectors (.class tag, .class .sub): selector tag { ... }
     - Media Queries (@media (max-width: ...)): @media (...) { selector { ... } }
  3. Phân biệt rõ ràng Container Width:
     - Section ngoài cùng (Hero, Header, Footer, Banner bar): Full-width 100% màn hình.
     - Container con (bọc nội dung căn giữa): [vbc_box class="container"] đồng bộ chuẩn Flatsome theme width.
     - Grid / Flex / Card / Typography: mang custom_css riêng của từng phần tử.
  4. Xóa 100% comment HTML <!-- ... --> và không để CSS thô trong post_content.
  5. Tự động chuyển đổi Emoji sang [vbc_icon] và Form sang [contact-form-7].
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

# Bản đồ ánh xạ Emoji Unicode sang [vbc_icon]
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


def parse_css_into_class_map(css_text):
    """Trích xuất tất cả quy tắc CSS (kèm pseudo, sub-elements, media query) theo từng class"""
    if not css_text:
        return {}

    class_rules = {}

    # 1. Trích xuất Media Queries trước
    media_blocks = []
    def _save_media(m):
        media_header = m.group(1).strip()
        media_body = m.group(2).strip()
        media_blocks.append((media_header, media_body))
        return ""
    
    clean_css = re.sub(r'(@media[^{]+)\{([\s\S]*?\})\s*\}', _save_media, css_text)

    # 2. Xử lý các quy tắc thông thường
    matches = re.findall(r'([^{]+)\{([^}]+)\}', clean_css)
    for sel_str, body in matches:
        sel_str = sel_str.strip()
        body = ' '.join(body.strip().split()).rstrip(';')
        body = re.sub(r'font-family\s*:\s*[^;]+;?', '', body, flags=re.IGNORECASE).strip().rstrip(';')
        if not body:
            continue

        selectors = [s.strip() for s in sel_str.split(',')]
        for sel in selectors:
            m = re.match(r'^\.([a-zA-Z0-9_\-]+)(.*)$', sel)
            if m:
                root_class = m.group(1)
                remainder = m.group(2).strip()
                if root_class not in class_rules:
                    class_rules[root_class] = []
                
                if not remainder:
                    class_rules[root_class].append(f'selector {{ {body}; }}')
                elif remainder.startswith(':'):
                    class_rules[root_class].append(f'selector{remainder} {{ {body}; }}')
                else:
                    class_rules[root_class].append(f'selector {remainder} {{ {body}; }}')

    # 3. Gắn Media Queries vào class tương ứng
    for media_header, media_body in media_blocks:
        sub_matches = re.findall(r'([^{]+)\{([^}]+)\}', media_body)
        for sel_str, body in sub_matches:
            sel_str = sel_str.strip()
            body = ' '.join(body.strip().split()).rstrip(';')
            body = re.sub(r'font-family\s*:\s*[^;]+;?', '', body, flags=re.IGNORECASE).strip().rstrip(';')
            if not body:
                continue

            selectors = [s.strip() for s in sel_str.split(',')]
            for sel in selectors:
                m = re.match(r'^\.([a-zA-Z0-9_\-]+)(.*)$', sel)
                if m:
                    root_class = m.group(1)
                    remainder = m.group(2).strip()
                    if root_class not in class_rules:
                        class_rules[root_class] = []
                    
                    if not remainder:
                        class_rules[root_class].append(f'{media_header} {{ selector {{ {body}; }} }}')
                    elif remainder.startswith(':'):
                        class_rules[root_class].append(f'{media_header} {{ selector{remainder} {{ {body}; }} }}')
                    else:
                        class_rules[root_class].append(f'{media_header} {{ selector {remainder} {{ {body}; }} }}')

    return {k: ' '.join(v) for k, v in class_rules.items()}


def clean_custom_css_for_attr(css_str):
    """Đảm bảo custom_css an toàn với dấu ngoặc kép của shortcode WordPress"""
    if not css_str:
        return ""
    cleaned = css_str.replace('"', "'")
    cleaned = ' '.join(cleaned.split())
    return cleaned.strip()


class VBCPerElementCompiler(HTMLParser):
    def __init__(self, class_css_map=None):
        super().__init__(convert_charrefs=False)
        self.output = []
        self.container_stack = []
        self.class_css_map = class_css_map or {}
        self.CONTAINER_TAGS = [
            'vbc_div',             # Cấp 1: Full-width Section
            'vbc_box',             # Cấp 2: Theme Container (đồng bộ Flatsome)
            'vbc_block',           # Cấp 3: Grid / Row / Sub-wrap
            'vbc_container',       # Cấp 4: Card item / Badge
            'vbc_container_inner', # Cấp 5: Khối sâu
            'vbc_container_inner_1',
            'vbc_container_inner_2',
            'vbc_container_inner_3',
            'vbc_container_inner_4',
            'vbc_container_inner_5'
        ]

    def get_element_custom_css(self, cls, raw_style, is_container=False):
        """Tổng hợp CSS từ class và inline style thành custom_css='selector { ... }'"""
        rules = []

        # 1. Lấy CSS từ class map
        if cls:
            for c in cls.split():
                if c in self.class_css_map:
                    rule = self.class_css_map[c]
                    if is_container:
                        rule = re.sub(r'max-width\s*:\s*(?:1[0-3]\d{2}px|1080px|1170px|1200px|1240px)\s*;?', '', rule, flags=re.IGNORECASE)
                    rules.append(rule)

        # 2. Lấy CSS từ inline style
        if raw_style:
            inline = raw_style.strip().rstrip(';')
            inline = re.sub(r'font-family\s*:\s*[^;]+;?', '', inline, flags=re.IGNORECASE)
            if is_container:
                inline = re.sub(r'max-width\s*:\s*(?:1[0-3]\d{2}px|1080px|1170px|1200px|1240px)\s*;?', '', inline, flags=re.IGNORECASE)
            inline = inline.strip().rstrip(';')
            if inline:
                rules.append(f'selector {{ {inline}; }}')

        combined = ' '.join(rules).strip()
        return clean_custom_css_for_attr(combined)

    def handle_starttag(self, tag, attrs):
        attr_dict = dict(attrs)
        cls = attr_dict.get('class', '').strip()
        raw_style = attr_dict.get('style', '').strip()

        # 1. Thẻ Style - bỏ qua
        if tag == 'style':
            return

        # 2. Void Tag: IMG -> [vbc_img]
        if tag == 'img':
            src = attr_dict.get('src', '')
            alt = attr_dict.get('alt', '')
            custom_css = self.get_element_custom_css(cls, raw_style)
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

        # 3. Void Tags: BR, HR
        if tag == 'br':
            self.output.append('<br>')
            return
        if tag == 'hr':
            custom_css = self.get_element_custom_css(cls, raw_style)
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[vbc_hr{attr_part}]')
            return

        # 4. Typography Tags (h1-h6, p, span, strong, b, em, i)
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'strong', 'b', 'em', 'i']:
            vbc_tag = f'vbc_{tag}'
            custom_css = self.get_element_custom_css(cls, raw_style)
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[{vbc_tag}{attr_part}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 5. Link Tag: A -> [vbc_a]
        if tag == 'a':
            href = attr_dict.get('href', '#')
            target = attr_dict.get('target', '_self')
            custom_css = self.get_element_custom_css(cls, raw_style)
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

        # 6. List Tags: UL, OL, LI
        if tag in ['ul', 'ol', 'li']:
            vbc_tag = f'vbc_{tag}'
            custom_css = self.get_element_custom_css(cls, raw_style)
            attrs_str = []
            if cls:
                attrs_str.append(f'class="{cls}"')
            if custom_css:
                attrs_str.append(f'custom_css="{custom_css}"')
            attr_part = (' ' + ' '.join(attrs_str)) if attrs_str else ''
            self.output.append(f'[{vbc_tag}{attr_part}]')
            self.container_stack.append(('leaf', vbc_tag))
            return

        # 7. Button Tag -> [vbc_span]
        if tag == 'button':
            custom_css = self.get_element_custom_css(cls, raw_style)
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
            container_depth = sum(1 for kind, _ in self.container_stack if kind == 'container')
            if container_depth < len(self.CONTAINER_TAGS):
                vbc_tag = self.CONTAINER_TAGS[container_depth]
            else:
                vbc_tag = self.CONTAINER_TAGS[-1]

            # Section ngoài cùng (Cấp 1): Full Width (width: 100%)
            is_full_width_section = (container_depth == 0) or tag in ['header', 'section', 'footer', 'nav']
            # Container bọc nội dung (Cấp 2): Đồng bộ với Theme Flatsome
            is_theme_container = (vbc_tag == 'vbc_box') or any(k in cls.lower() for k in ['container', 'wrap', 'wrapper'])

            custom_css = self.get_element_custom_css(cls, raw_style, is_container=is_theme_container)

            # Tự động gán class 'container' chuẩn Flatsome cho các khối container con
            if is_theme_container and not is_full_width_section:
                classes = cls.split()
                if 'container' not in classes and 'container-fluid' not in classes:
                    classes.insert(0, 'container')
                cls = ' '.join(classes)

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
        processed = data
        for emoji, icon_sc in EMOJI_ICON_MAP.items():
            if emoji in processed:
                processed = processed.replace(emoji, f" {icon_sc} ")
        self.output.append(processed)

    def handle_comment(self, data):
        pass


def compile_html_to_vbc(html_content, return_css=False):
    """Chuyển đổi toàn diện HTML sang VBC Shortcodes với CSS tích hợp trực tiếp vào từng phần tử"""
    if not html_content:
        return ("", "") if return_css else ""

    content = html_content

    # 1. Trích xuất toàn bộ CSS từ thẻ <style>
    style_matches = re.findall(r'<style\b[^>]*>(.*?)<\/style>', content, flags=re.DOTALL | re.IGNORECASE)
    all_css_text = ' '.join(style_matches)
    class_css_map = parse_css_into_class_map(all_css_text)

    # 2. Xóa toàn bộ thẻ <style> khỏi content
    content = re.sub(r'<style\b[^>]*>(.*?)<\/style>', '', content, flags=re.DOTALL | re.IGNORECASE)

    # 3. Bảo vệ các shortcode [contact-form-7 ...] và [vbc_icon ...]
    protected_shortcodes = []
    def _save_shortcodes(m):
        protected_shortcodes.append(m.group(0))
        return f"__VBC_PROTECTED_SC_{len(protected_shortcodes)-1}__"
    content = re.sub(r'\[(?:contact-form-7|vbc_icon|vbc_post|accordion|row|col)\b[^\]]*\](?:[\s\S]*?\[\/(?:accordion|row|col)\])?', _save_shortcodes, content, flags=re.IGNORECASE)

    # 4. Biên dịch cây DOM sang VBC Elements với CSS nhúng trực tiếp
    parser = VBCPerElementCompiler(class_css_map=class_css_map)
    try:
        parser.feed(content)
        compiled = ''.join(parser.output)
    except Exception as e:
        print(f"[CẢNH BÁO] HTML Parser fallback: {e}")
        compiled = content

    # 5. Khôi phục Protected Shortcodes
    for idx, sc in enumerate(protected_shortcodes):
        compiled = compiled.replace(f"__VBC_PROTECTED_SC_{idx}__", sc)

    # 6. Xóa triệt để tất cả HTML comments <!-- ... -->
    compiled = re.sub(r'<!--[\s\S]*?-->', '', compiled)

    # 7. Dọn dẹp dòng trống
    compiled = re.sub(r'\n{3,}', '\n\n', compiled).strip()

    if return_css:
        return compiled, all_css_text

    return compiled
