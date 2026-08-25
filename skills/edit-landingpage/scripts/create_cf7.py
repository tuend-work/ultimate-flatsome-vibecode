#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Contact Form 7 Creator
Tạo biểu mẫu CF7 chuẩn UX/UI qua REST API (/vbc/v1/cf7)
"""

import os
import sys
import json
import urllib.request
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
    raise FileNotFoundError("Không tìm thấy tệp vbc-config.json.")

def create_cf7_form(title, fields, button_text="Gửi Đăng Ký Ngay"):
    root_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..'))
    config = load_config(root_dir)
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')

    payload = {
        'title': title,
        'fields': fields,
        'button_text': button_text
    }

    print(f"\n📝 Đang tạo Form Contact Form 7 '{title}' ({api_url}/vbc/v1/cf7)...")
    req = urllib.request.Request(
        f"{api_url}/vbc/v1/cf7",
        data=json.dumps(payload).encode('utf-8'),
        headers={'Content-Type': 'application/json', 'X-VBC-Token': token}
    )

    with urllib.request.urlopen(req, timeout=30) as resp:
        res_data = json.loads(resp.read().decode('utf-8'))
        form_id = res_data.get('form_id')
        shortcode = res_data.get('shortcode')

        print("============================================================")
        print("🎉 TẠO FORM CONTACT FORM 7 THÀNH CÔNG!")
        print("============================================================")
        print(f"Form ID   : {form_id}")
        print(f"Shortcode : {shortcode}")
        print("============================================================\n")
        return form_id, shortcode

def main():
    parser = argparse.ArgumentParser(description="Create CF7 Form via VBC API")
    parser.add_argument("--title", required=True, help="Tiêu đề của form")
    parser.add_argument("--fields", default="name,phone,email,message", help="Danh sách trường ngăn cách bởi dấu phẩy")
    parser.add_argument("--button_text", default="Gửi Đăng Ký Ngay", help="Văn bản nút bấm submit")

    args = parser.parse_args()
    fields_list = [f.strip() for f in args.fields.split(',') if f.strip()]
    create_cf7_form(args.title, fields_list, args.button_text)

if __name__ == "__main__":
    main()
