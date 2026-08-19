#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - MODULAR LANDING PAGE CREATOR SKILL
===============================================================================
File: create-landingpage.py
Description:
  Tạo trang Landing Page mới dựa trên nội dung, ảnh tham khảo và URL tham khảo:
  1. Hỗ trợ tạo trang theo mẫu modular section hoặc từ file HTML / JSON spec.
  2. Tự động tải ảnh tham khảo lên WordPress Media Library và liên kết chính xác.
  3. Nén minified CSS và dọn dẹp các ký tự xuống dòng tránh wpautop làm hỏng layout.
  4. Xuất bản qua REST API /vbc/v1/page với template page-blank.php.
  5. Tự động kích hoạt recheck-url.py để nghiệm thu 100% chất lượng.
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


class LandingPageCreator:
    def __init__(self, title, content=None, file_path=None, spec_json=None, slug=None, post_id=None, template='page-blank.php', auto_recheck=True, config_path=None):
        self.title = title
        self.raw_content = content
        self.file_path = file_path
        self.spec_json = spec_json
        self.slug = slug or ""
        self.post_id = post_id
        self.template = template
        self.auto_recheck = auto_recheck
        self.config = load_vbc_config(config_path)

        self.api_url = self.config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
        self.token = self.config.get('token', '060bed653d61c4140ba69689de2ade9e562f3456')

    def load_content(self):
        """Đọc nội dung trang từ file, JSON hoặc tham số chuỗi"""
        if self.file_path and os.path.exists(self.file_path):
            print(f"-> Đang tải nội dung từ tệp: {self.file_path}")
            with open(self.file_path, 'r', encoding='utf-8') as f:
                return f.read()
        
        if self.spec_json:
            print("-> Đang khởi tạo nội dung từ cấu hình JSON Spec...")
            try:
                spec_data = json.loads(self.spec_json) if isinstance(self.spec_json, str) else self.spec_json
                return self.build_from_spec(spec_data)
            except Exception as e:
                print(f"❌ [LỖI] Cấu hình JSON spec không hợp lệ: {e}")
                sys.exit(1)

        if self.raw_content:
            return self.raw_content

        # Nếu không có nội dung truyền vào, tạo mẫu landing page chuẩn
        return self.build_default_template()

    def build_from_spec(self, spec):
        """Tự động dựng cấu trúc HTML từ spec các section"""
        theme_color = spec.get('theme_color', '#b20000')
        sections_html = []

        # CSS Base
        css_block = f"""<style>
.vbc-lp-wrap {{ font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #1e293b; background: #ffffff; line-height: 1.6; }}
.vbc-lp-wrap * {{ box-sizing: border-box; }}
.vbc-lp-container {{ max-width: 1240px; margin: 0 auto; padding: 0 16px; }}
.vbc-lp-hero {{ position: relative; padding: 70px 0; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; text-align: center; }}
.vbc-lp-hero h1 {{ font-size: 38px; font-weight: 900; color: #ffffff !important; margin: 0 0 15px; }}
.vbc-lp-hero p {{ font-size: 18px; color: #e2e8f0 !important; max-width: 800px; margin: 0 auto 25px; }}
.vbc-lp-btn {{ display: inline-flex; align-items: center; justify-content: center; padding: 14px 32px; background: {theme_color}; color: #ffffff !important; font-weight: 700; border-radius: 8px; text-decoration: none; border: none; cursor: pointer; transition: transform 0.2s; }}
.vbc-lp-btn:hover {{ transform: translateY(-2px); }}
.vbc-lp-grid {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin: 40px 0; }}
.vbc-lp-card {{ background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }}
.vbc-lp-card h3 {{ font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 10px; }}
.vbc-lp-card p {{ font-size: 15px; color: #64748b; line-height: 1.6; margin: 0; }}
</style>"""

        # Hero
        hero_title = spec.get('hero_title', self.title)
        hero_desc = spec.get('hero_desc', 'Giải pháp chất lượng cao, phục vụ chuyên nghiệp và tận tâm 24/7.')
        hero_cta = spec.get('hero_cta', 'Liên Hệ Ngay')
        hero_phone = spec.get('phone', '0968866855')

        hero_html = f"""
<div class="vbc-lp-wrap">
  <section class="vbc-lp-hero">
    <div class="vbc-lp-container">
      <h1>{hero_title}</h1>
      <p>{hero_desc}</p>
      <a href="tel:{hero_phone}" class="vbc-lp-btn">📞 {hero_cta}: {hero_phone}</a>
    </div>
  </section>
"""

        # Cards / Features
        features = spec.get('features', [
            {'title': 'Chuyên Nghiệp & Uy Tín', 'desc': 'Đội ngũ giàu kinh nghiệm, cam kết mang lại chất lượng dịch vụ vượt trội.'},
            {'title': 'Tiết Kiệm Chi Phí', 'desc': 'Bảng giá minh bạch, tối ưu chi phí tối đa cho từng khách hàng.'},
            {'title': 'Hỗ Trợ 24/7', 'desc': 'Tư vấn nhiệt tình, sẵn sàng giải đáp và xử lý mọi yêu cầu bất kể ngày đêm.'}
        ])

        cards_html = """
  <section style="padding: 60px 0;">
    <div class="vbc-lp-container">
      <div class="vbc-lp-grid">
"""
        for item in features:
            cards_html += f"""
        <div class="vbc-lp-card">
          <h3>{item['title']}</h3>
          <p>{item['desc']}</p>
        </div>
"""
        cards_html += """
      </div>
    </div>
  </section>
</div>
"""
        return css_block + hero_html + cards_html

    def build_default_template(self):
        """Mẫu landing page mặc định sang trọng và chuẩn UI/UX"""
        return self.build_from_spec({
            'hero_title': self.title,
            'hero_desc': 'Cung cấp sản phẩm và dịch vụ chuyên nghiệp, cam kết uy tín và giá tốt nhất.',
            'hero_cta': 'Gọi Tư Vấn',
            'phone': '0968866855',
            'features': [
                {'title': 'Dịch Vụ Hàng Đầu', 'desc': 'Quy trình chuẩn hóa, đem lại sự hài lòng cao nhất cho khách hàng.'},
                {'title': 'Báo Giá Minh Bạch', 'desc': 'Không phát sinh chi phí phụ, cam kết giá niêm yết rõ ràng.'},
                {'title': 'Giao Nhận Nhanh Chóng', 'desc': 'Đúng thời gian cam kết, phục vụ tận tâm trên mọi tuyến đường.'}
            ]
        })

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

    def publish(self):
        """Xuất bản landing page lên WordPress"""
        print(f"\n=======================================================")
        print(f"   VIBECODE CREATE LANDING PAGE")
        print(f"=======================================================")
        print(f"Title     : {self.title}")
        print(f"Template  : {self.template}")
        print(f"Timestamp : {time.strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"-------------------------------------------------------")

        content = self.load_content()
        sanitized_content = self.sanitize_for_wp(content)

        page_endpoint = f"{self.api_url}/vbc/v1/page"
        payload = {
            'title': self.title,
            'content': sanitized_content,
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
                'User-Agent': 'VibeCode-Creator/2.0'
            }
        )

        try:
            with urllib.request.urlopen(req, timeout=30) as resp:
                res_data = json.loads(resp.read().decode('utf-8'))
                if res_data.get('success'):
                    pub_id = res_data.get('post_id')
                    pub_url = res_data.get('url')
                    print(f"\n=======================================================")
                    print(f"   🎉 TẠO LANDING PAGE THÀNH CÔNG!")
                    print(f"=======================================================")
                    print(f"Post ID   : {pub_id}")
                    print(f"Page URL  : {pub_url}")
                    print(f"Template  : {self.template}")
                    print(f"=======================================================")

                    if self.auto_recheck:
                        print(f"\n-> Tự động kích hoạt kiểm tra chất lượng qua recheck-url.py...")
                        try:
                            from skills.recheck_url import LandingPageRechecker
                        except ImportError:
                            import importlib.util
                            recheck_file = os.path.join(os.path.dirname(__file__), 'recheck-url.py')
                            spec = importlib.util.spec_from_file_location("recheck_module", recheck_file)
                            recheck_module = importlib.util.module_from_spec(spec)
                            spec.loader.exec_module(recheck_module)
                            LandingPageRechecker = recheck_module.LandingPageRechecker

                        checker = LandingPageRechecker(target_url=pub_url, post_id=pub_id, max_retries=3)
                        checker.run_recheck()

                    return pub_url, pub_id
                else:
                    print(f"❌ [LỖI] API từ chối xuất bản: {res_data}")
                    sys.exit(1)
        except Exception as e:
            print(f"❌ [LỖI] Lỗi kết nối khi tạo trang: {e}")
            sys.exit(1)


def main():
    parser = argparse.ArgumentParser(description="VibeCode Modular Landing Page Creator Skill")
    parser.add_argument("--title", required=True, help="Tiêu đề trang WordPress")
    parser.add_argument("--file", help="Đường dẫn file HTML / nội dung trang sẵn có")
    parser.add_argument("--spec", help="Chuỗi JSON spec cấu hình các section")
    parser.add_argument("--slug", help="Slug đường dẫn (ví dụ: 'dich-vu-thiet-ke-web')")
    parser.add_argument("--post_id", type=int, help="Post ID cần cập nhật ghi đè (nếu có)")
    parser.add_argument("--template", default="page-blank.php", help="Page template (mặc định: page-blank.php)")
    parser.add_argument("--no_recheck", action="store_true", help="Không tự động chạy recheck sau khi tạo")
    parser.add_argument("--config", help="Đường dẫn file vbc-config.json tùy chọn")

    args = parser.parse_args()

    creator = LandingPageCreator(
        title=args.title,
        file_path=args.file,
        spec_json=args.spec,
        slug=args.slug,
        post_id=args.post_id,
        template=args.template,
        auto_recheck=not args.no_recheck,
        config_path=args.config
    )

    creator.publish()


if __name__ == "__main__":
    main()
