#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - 90% - 100% PIXEL PERFECT CLONE LANDING PAGE SKILL
===============================================================================
File: clone-landingpage.py
Description:
  Tự động clone bất kỳ trang web nào về WordPress với độ tương đồng 90-100%:
  1. Trích xuất toàn bộ cấu trúc HTML, CSS, Font, Màu sắc, Ảnh, Icon, SVG từ URL gốc.
  2. Tự động tải tất cả hình ảnh/media về và upload lên WordPress Media Library qua REST API.
  3. Tự động ánh xạ (map) link ảnh sang URL WordPress và tối ưu responsive (768px/1024px).
  4. Nén minified CSS và xử lý tương thích wpautop, hỗ trợ đầy đủ responsive Desktop/Mobile.
  5. Xuất bản lên WordPress với template page-blank.php hoặc full-width.
  6. Tự động kích hoạt recheck-url.py để nghiệm thu 100% chất lượng.
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


class LandingPageCloner:
    def __init__(self, source_url, title=None, slug=None, post_id=None, template='page-blank.php', max_images=40, auto_recheck=True, config_path=None):
        self.source_url = source_url
        self.title = title or "Landing Page Clone"
        self.slug = slug or ""
        self.post_id = post_id
        self.template = template
        self.max_images = max_images
        self.auto_recheck = auto_recheck
        self.config = load_vbc_config(config_path)
        
        self.api_url = self.config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
        self.token = self.config.get('token', '060bed653d61c4140ba69689de2ade9e562f3456')
        
        self.media_map = {}
        self.downloaded_media = []

    def fetch_source(self):
        """Crawl toàn bộ mã nguồn HTML của trang web gốc"""
        print(f"\n[1/5] Đang thu thập nội dung từ trang web gốc: {self.source_url} ...")
        req = urllib.request.Request(
            self.source_url,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language': 'vi,en-US;q=0.9,en;q=0.8'
            }
        )
        try:
            with urllib.request.urlopen(req, timeout=25) as resp:
                html = resp.read().decode('utf-8', errors='ignore')
                print(f"✓ Đã tải {len(html)} bytes HTML gốc.")
                return html
        except Exception as e:
            print(f"❌ [LỖI] Không thể crawl URL {self.source_url}: {e}")
            sys.exit(1)

    def extract_and_upload_media(self, html):
        """Trích xuất tất cả ảnh từ HTML, tải về và upload lên WordPress Media"""
        print(f"\n[2/5] Đang trích xuất và tải lên WordPress Media Library...")
        
        # Tìm tất cả link ảnh trong <img>, background-image, svg
        found_urls = set()
        img_srcs = re.findall(r'<img[^>]+(?:src|data-src|srcset)=[\'"]([^\'"]+)[\'"]', html, re.IGNORECASE)
        for s in img_srcs:
            # Nếu có srcset, lấy URL đầu tiên
            first = s.split(',')[0].strip().split(' ')[0]
            if first and not first.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, first))

        bg_urls = re.findall(r'url\([\'"]?(https?:\/\/[^\'")]+|\/[^\'")]+)[\'"]?\)', html, re.IGNORECASE)
        for b in bg_urls:
            if not b.startswith('data:'):
                found_urls.add(urllib.parse.urljoin(self.source_url, b))

        filtered_urls = [u for u in found_urls if any(u.lower().endswith(ext) or ext in u.lower() for ext in ['.jpg', '.jpeg', '.png', '.webp', '.svg', '.gif'])][:self.max_images]
        print(f"-> Tìm thấy {len(filtered_urls)} file ảnh hợp lệ cần đồng bộ.")

        os.makedirs('temp_clone_media', exist_ok=True)
        uploaded_count = 0

        for idx, img_url in enumerate(filtered_urls, 1):
            filename = os.path.basename(urllib.parse.urlparse(img_url).path)
            if not filename or len(filename) < 4:
                filename = f"media_{idx}_{int(time.time())}.jpg"
            
            # Xóa các tham số query trong tên file
            filename = re.sub(r'[^a-zA-Z0-9_\-\.]', '_', filename)
            local_path = os.path.join('temp_clone_media', filename)

            try:
                # Tải file về cục bộ
                dl_req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(dl_req, timeout=15) as r, open(local_path, 'wb') as f:
                    f.write(r.read())

                # Upload lên WordPress qua /vbc/v1/upload
                wp_url, att_id = self.upload_file_to_wp(local_path, filename)
                if wp_url:
                    self.media_map[img_url] = {
                        'url': wp_url,
                        'id': att_id,
                        'filename': filename
                    }
                    uploaded_count += 1
                    print(f" [{idx}/{len(filtered_urls)}] ✓ {filename} -> {wp_url}")
            except Exception as e:
                print(f" [{idx}/{len(filtered_urls)}] ⚠ Bỏ qua {filename}: {e}")

        print(f"✓ Hoàn tất tải lên {uploaded_count}/{len(filtered_urls)} ảnh.")
        
        # Lưu bản đồ media ra file JSON
        map_filename = f"{self.slug or 'cloned'}_media_map.json"
        with open(map_filename, 'w', encoding='utf-8') as f:
            json.dump(self.media_map, f, ensure_ascii=False, indent=2)
        print(f"✓ Đã lưu Media Map vào: {map_filename}")

    def upload_file_to_wp(self, local_path, filename):
        """Upload file lên WordPress REST API qua /vbc/v1/upload"""
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

        req = urllib.request.Request(
            upload_endpoint,
            data=body,
            headers={
                'Content-Type': f'multipart/form-data; boundary={boundary}',
                'X-VBC-Token': self.token,
                'User-Agent': 'VibeCode-Cloner/2.0'
            }
        )

        with urllib.request.urlopen(req, timeout=30) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            if data.get('success'):
                return data.get('url'), data.get('id') or data.get('attachment_id')
        return None, None

    def sanitize_for_wp(self, html):
        """Nén CSS và dọn dẹp khoảng trắng để tránh wpautop chèn <p>/<br>"""
        # 1. Minify toàn bộ thẻ <style> thành 1 dòng liên tục
        html = re.sub(
            r'<style\b[^>]*>(.*?)</style>',
            lambda m: '<style>' + ' '.join(m.group(1).split()) + '</style>',
            html,
            flags=re.DOTALL
        )
        # 2. Xóa các khoảng trống xuống dòng liên tiếp giữa các thẻ HTML
        html = re.sub(r'>\s*\n+\s*<', '><', html)
        return html.strip()

    def convert_forms_to_cf7(self, html):
        """Tự động chuyển đổi các thẻ <form> hoặc cụm input sang Contact Form 7 shortcode"""
        from skills.cf7_converter import convert_html_form_to_cf7_markup, create_cf7_form_via_api

        form_matches = list(re.finditer(r'<form\b[^>]*>(.*?)</form>', html, re.DOTALL | re.IGNORECASE))
        if not form_matches:
            return html

        print(f"-> Phát hiện {len(form_matches)} form biểu mẫu cần chuyển đổi sang Contact Form 7...")
        processed_html = html
        for idx, match in enumerate(form_matches, 1):
            full_form = match.group(0)
            inner_form = match.group(1)

            cf7_markup = convert_html_form_to_cf7_markup(inner_form)
            form_title = f"Form Clone #{idx} - {self.title}"

            cf7_shortcode, cf7_id = create_cf7_form_via_api(self.api_url, self.token, form_title, cf7_markup)
            if cf7_shortcode:
                print(f"   [Form #{idx}] ✓ Đã tạo CF7 ID {cf7_id}: {cf7_shortcode}")
                processed_html = processed_html.replace(full_form, cf7_shortcode)
            else:
                print(f"   [Form #{idx}] ⚠ Không thể tạo CF7 qua API, giữ nguyên form.")

        return processed_html

    def build_page_html(self, raw_html):
        """Xây dựng mã HTML hoàn chỉnh chuẩn SEO, Responsive và thay thế toàn bộ URL ảnh & Form sang CF7"""
        print(f"\n[3/5] Đang tái tạo cấu trúc giao diện, đồng bộ ảnh và chuyển đổi Form sang CF7...")
        
        # 1. Thay thế các URL ảnh nguồn sang link WordPress Media
        processed_html = raw_html
        for src_url, media_info in self.media_map.items():
            wp_img_url = media_info['url']
            processed_html = processed_html.replace(src_url, wp_img_url)

        # 2. Chuyển đổi toàn bộ thẻ <form> và input sang Contact Form 7 shortcode
        processed_html = self.convert_forms_to_cf7(processed_html)

        # 3. Chuyển đổi HTML sang hệ thống phần tử VBC Elements [vbc_*]
        try:
            from skills.html_to_vbc import compile_html_to_vbc
        except ImportError:
            import importlib.util
            vbc_compiler_file = os.path.join(os.path.dirname(__file__), 'html_to_vbc.py')
            spec = importlib.util.spec_from_file_location("vbc_compiler_module", vbc_compiler_file)
            vbc_compiler_module = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(vbc_compiler_module)
            compile_html_to_vbc = vbc_compiler_module.compile_html_to_vbc

        print(f"-> Đang biên dịch cấu trúc DOM sang các phần tử VBC Elements...")
        processed_html = compile_html_to_vbc(processed_html)

        return self.sanitize_for_wp(processed_html)

    def publish_to_wordpress(self, content):
        """Gửi nội dung tới REST API /vbc/v1/page"""
        print(f"\n[4/5] Đang xuất bản trang lên WordPress...")
        page_endpoint = f"{self.api_url}/vbc/v1/page"
        
        payload = {
            'title': self.title,
            'content': content,
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
            }
        )

        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                res_data = json.loads(resp.read().decode('utf-8'))
                if res_data.get('success'):
                    pub_id = res_data.get('post_id')
                    pub_url = res_data.get('url')
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
        """Chạy toàn bộ quy trình clone landing page"""
        raw_html = self.fetch_source()
        self.extract_and_upload_media(raw_html)
        final_html = self.build_page_html(raw_html)
        
        pub_url, pub_id = self.publish_to_wordpress(final_html)

        if self.auto_recheck:
            print(f"\n[5/5] Tự động kích hoạt kiểm tra chất lượng qua recheck-url.py...")
            try:
                from skills.recheck_url import LandingPageRechecker
            except ImportError:
                import importlib.util
                recheck_file = os.path.join(os.path.dirname(__file__), 'recheck-url.py')
                spec = importlib.util.spec_from_file_location("recheck_module", recheck_file)
                recheck_module = importlib.util.module_from_spec(spec)
                spec.loader.exec_module(recheck_module)
                LandingPageRechecker = recheck_module.LandingPageRechecker

            checker = LandingPageRechecker(target_url=pub_url, post_id=pub_id, source_url=self.source_url, max_retries=3)
            checker.run_recheck()

        return pub_url, pub_id


def main():
    parser = argparse.ArgumentParser(description="VibeCode 90-100% Accurate Clone Landing Page Skill")
    parser.add_argument("--url", required=True, help="URL trang web cần clone")
    parser.add_argument("--title", help="Tiêu đề trang WordPress (ví dụ: 'XE KHÁCH BẮC NAM & CHO THUÊ XE DU LỊCH')")
    parser.add_argument("--slug", help="Slug đường dẫn (ví dụ: 'xe-khach-bac-nam')")
    parser.add_argument("--post_id", type=int, help="Post ID cần ghi đè (nếu có)")
    parser.add_argument("--template", default="page-blank.php", help="Page template (mặc định: page-blank.php)")
    parser.add_argument("--max_images", type=int, default=40, help="Số ảnh tối đa cần tải lên (mặc định: 40)")
    parser.add_argument("--no_recheck", action="store_true", help="Không tự động chạy recheck sau khi publish")
    parser.add_argument("--config", help="Đường dẫn file vbc-config.json tùy chọn")

    args = parser.parse_args()

    cloner = LandingPageCloner(
        source_url=args.url,
        title=args.title,
        slug=args.slug,
        post_id=args.post_id,
        template=args.template,
        max_images=args.max_images,
        auto_recheck=not args.no_recheck,
        config_path=args.config
    )

    cloner.execute()


if __name__ == "__main__":
    main()
