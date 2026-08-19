# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - CONTACT FORM 7 CONVERTER & CREATOR HELPER
===============================================================================
File: cf7_converter.py
Description:
  Chuyển đổi toàn bộ form HTML hoặc dữ liệu trường sang Contact Form 7 shortcode:
  1. Tự động parse input, select, textarea, submit button sang CF7 tag syntax.
  2. Gọi REST API /vbc/v1/cf7 để tạo form trong WordPress.
  3. Trả về shortcode [contact-form-7 id="..." title="..."].
===============================================================================
"""

import re
import json
import urllib.request
import urllib.error


def convert_html_form_to_cf7_markup(form_inner_html):
    """Chuyển đổi các phần tử form HTML sang cú pháp Contact Form 7"""
    markup = form_inner_html

    # 1. Chuyển đổi <select>...</select> -> [select your-name "Option 1" "Option 2"]
    def _repl_select(m):
        full_tag = m.group(0)
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', full_tag)
        name = name_m.group(1) if name_m else 'your-select'
        options = re.findall(r'<option[^>]*>(.*?)</option>', full_tag, re.DOTALL)
        clean_opts = [f'"{o.strip()}"' for o in options if o.strip()]
        opts_str = ' '.join(clean_opts) if clean_opts else '"Lựa chọn 1" "Lựa chọn 2"'
        return f'[select {name} {opts_str}]'

    markup = re.sub(r'<select\b[^>]*>.*?</select>', _repl_select, markup, flags=re.DOTALL | re.IGNORECASE)

    # 2. Chuyển đổi <input type="tel">
    def _repl_tel(m):
        tag = m.group(0)
        req = '*' if 'required' in tag.lower() else ''
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', tag)
        name = name_m.group(1) if name_m else 'your-phone'
        ph_m = re.search(r'placeholder=[\'"]([^\'"]+)[\'"]', tag)
        ph = f' placeholder "{ph_m.group(1)}"' if ph_m else ''
        return f'[tel{req} {name}{ph}]'

    markup = re.sub(r'<input\b[^>]*type=[\'"]tel[\'"][^>]*>', _repl_tel, markup, flags=re.IGNORECASE)

    # 3. Chuyển đổi <input type="email">
    def _repl_email(m):
        tag = m.group(0)
        req = '*' if 'required' in tag.lower() else ''
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', tag)
        name = name_m.group(1) if name_m else 'your-email'
        ph_m = re.search(r'placeholder=[\'"]([^\'"]+)[\'"]', tag)
        ph = f' placeholder "{ph_m.group(1)}"' if ph_m else ''
        return f'[email{req} {name}{ph}]'

    markup = re.sub(r'<input\b[^>]*type=[\'"]email[\'"][^>]*>', _repl_email, markup, flags=re.IGNORECASE)

    # 4. Chuyển đổi <input type="date">
    def _repl_date(m):
        tag = m.group(0)
        req = '*' if 'required' in tag.lower() else ''
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', tag)
        name = name_m.group(1) if name_m else 'departure-date'
        return f'[date{req} {name}]'

    markup = re.sub(r'<input\b[^>]*type=[\'"]date[\'"][^>]*>', _repl_date, markup, flags=re.IGNORECASE)

    # 5. Chuyển đổi <input type="text"> hoặc <input> không có type
    def _repl_text(m):
        tag = m.group(0)
        # Bỏ qua nếu là type=submit, button, hidden
        if re.search(r'type=[\'"](?:submit|button|hidden|radio|checkbox)[\'"]', tag, re.IGNORECASE):
            return tag
        req = '*' if 'required' in tag.lower() else ''
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', tag)
        name = name_m.group(1) if name_m else 'your-text'
        ph_m = re.search(r'placeholder=[\'"]([^\'"]+)[\'"]', tag)
        ph = f' placeholder "{ph_m.group(1)}"' if ph_m else ''
        return f'[text{req} {name}{ph}]'

    markup = re.sub(r'<input\b[^>]*>', _repl_text, markup, flags=re.IGNORECASE)

    # 6. Chuyển đổi <textarea>...</textarea>
    def _repl_textarea(m):
        tag = m.group(0)
        req = '*' if 'required' in tag.lower() else ''
        name_m = re.search(r'name=[\'"]([^\'"]+)[\'"]', tag)
        name = name_m.group(1) if name_m else 'your-message'
        ph_m = re.search(r'placeholder=[\'"]([^\'"]+)[\'"]', tag)
        ph = f' placeholder "{ph_m.group(1)}"' if ph_m else ''
        return f'[textarea{req} {name}{ph}]'

    markup = re.sub(r'<textarea\b[^>]*>.*?</textarea>', _repl_textarea, markup, flags=re.DOTALL | re.IGNORECASE)

    # 7. Chuyển đổi nút Submit (<button type="submit"> hoặc <input type="submit">)
    def _repl_submit_btn(m):
        tag = m.group(0)
        cls_m = re.search(r'class=[\'"]([^\'"]+)[\'"]', tag)
        cls = f' class:{cls_m.group(1).split()[0]}' if cls_m else ''
        text_m = re.search(r'>([^<]+)<', tag)
        text = text_m.group(1).strip() if text_m else 'Gửi ngay'
        return f'[submit{cls} "{text}"]'

    markup = re.sub(r'<button\b[^>]*type=[\'"]submit[\'"][^>]*>.*?</button>', _repl_submit_btn, markup, flags=re.DOTALL | re.IGNORECASE)
    markup = re.sub(r'<button\b(?![^>]*\btype=)[^>]*>.*?</button>', _repl_submit_btn, markup, flags=re.DOTALL | re.IGNORECASE)

    return markup.strip()


def create_cf7_form_via_api(api_url, token, form_title, form_markup):
    """Gửi yêu cầu tạo form lên REST API /vbc/v1/cf7 và nhận về shortcode"""
    endpoint = f"{api_url.rstrip('/')}/vbc/v1/cf7"
    payload = {
        'title': form_title,
        'form': form_markup
    }

    req = urllib.request.Request(
        endpoint,
        data=json.dumps(payload).encode('utf-8'),
        headers={
            'Content-Type': 'application/json; charset=utf-8',
            'X-VBC-Token': token,
            'User-Agent': 'VibeCode-CF7-Helper/2.0'
        }
    )

    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            if data.get('success'):
                return data.get('shortcode'), data.get('id')
    except Exception as e:
        print(f"[CẢNH BÁO] Không thể tạo Contact Form 7 qua API: {e}")

    return None, None
