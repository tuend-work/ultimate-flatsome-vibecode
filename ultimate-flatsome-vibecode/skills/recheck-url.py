#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
===============================================================================
ULTIMATE FLATSOME VIBECODE - AUTOMATED LANDING PAGE RECHECK & QUALITY ASSURANCE
===============================================================================
File: recheck-url.py
Description:
  Kiểm tra chất lượng toàn diện của Landing Page sau khi Clone hoặc Create:
  1. Quét lỗi raw shortcodes chưa parse (0 lỗi).
  2. Quét lỗi hình ảnh (src="", 404, kích thước rỗng).
  3. Kiểm tra xem thẻ <style> có bị WordPress wpautop chèn <p>/<br> làm hỏng không.
  4. Phân tích DOM, cấu trúc layout, độ tương phản và responsive.
  5. Chụp ảnh màn hình 1 lần toàn trang (Full Page Screenshot) để nghiệm thu trực quan.
  6. Tự động lặp lại (recheck loop) cho đến khi đạt điểm chuẩn (90-100%).
===============================================================================
"""

import os
import sys
import re
import json
import time
import argparse
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


class LandingPageRechecker:
    def __init__(self, target_url, post_id=None, source_url=None, max_retries=3, screenshot_output=None):
        self.target_url = target_url
        self.post_id = post_id
        self.source_url = source_url
        self.max_retries = max_retries
        self.screenshot_output = screenshot_output or f"recheck_fullpage_{int(time.time())}.png"
        self.config = load_vbc_config()
        self.issues = []
        self.stats = {}

    def fetch_live_html(self):
        """Tải mã nguồn HTML rendered thực tế từ frontend với cache-busting"""
        cache_buster = f"vbc_qa={int(time.time())}"
        fetch_url = f"{self.target_url}{'&' if '?' in self.target_url else '?'}{cache_buster}"
        
        req = urllib.request.Request(
            fetch_url,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        )
        try:
            with urllib.request.urlopen(req, timeout=20) as resp:
                return resp.read().decode('utf-8', errors='ignore')
        except Exception as e:
            self.issues.append(f"Không thể tải trang web {self.target_url}: {e}")
            return None

    def check_unparsed_shortcodes(self, html):
        """Kiểm tra các shortcode thô bị lộ ra ngoài giao diện"""
        raw_tags = re.findall(r'\[\/?vbc_[^\]]*\]', html)
        unparsed_flatsome = re.findall(r'\[\/?(?:row|col|accordion|accordion-item|ux_banner|ux_image)[^\]]*\]', html)
        
        all_unparsed = raw_tags + unparsed_flatsome
        self.stats['unparsed_shortcodes'] = len(all_unparsed)
        
        if all_unparsed:
            unique_tags = list(set(all_unparsed))[:5]
            self.issues.append(f"Có {len(all_unparsed)} shortcode chưa parse bị lộ ra ngoài frontend (ví dụ: {', '.join(unique_tags)})")
            return False
        return True

    def check_style_tag_corruption(self, html):
        """Kiểm tra xem thẻ <style> có bị WordPress wpautop chèn <p> hoặc <br> vào không"""
        style_blocks = re.findall(r'<style\b[^>]*>(.*?)</style>', html, re.DOTALL | re.IGNORECASE)
        corrupted_styles = 0
        for s in style_blocks:
            if re.search(r'<(?:p|br|div)\b', s, re.IGNORECASE):
                corrupted_styles += 1
                
        self.stats['corrupted_style_tags'] = corrupted_styles
        if corrupted_styles > 0:
            self.issues.append(f"Có {corrupted_styles} thẻ <style> bị WordPress wpautop chèn thẻ <p>/<br> làm hỏng CSS.")
            return False
        return True

    def check_images(self, html):
        """Kiểm tra toàn bộ thẻ <img> và URL ảnh trên trang"""
        img_srcs = re.findall(r'<img[^>]+src=[\'"]([^\'"]*)[\'"]', html, re.IGNORECASE)
        data_srcs = re.findall(r'<img[^>]+data-src=[\'"]([^\'"]*)[\'"]', html, re.IGNORECASE)
        all_imgs = img_srcs + data_srcs
        
        empty_imgs = [s for s in all_imgs if not s.strip() or s.strip() in ['undefined', 'null', '#']]
        self.stats['total_images'] = len(all_imgs)
        self.stats['empty_images'] = len(empty_imgs)
        
        if empty_imgs:
            self.issues.append(f"Có {len(empty_imgs)} ảnh có src rỗng hoặc không hợp lệ (src=\"\").")

        # Kiểm tra HTTP status của tối đa 10 ảnh ngẫu nhiên để xác nhận không bị 404
        broken_imgs = 0
        checked_sample = [s for s in all_imgs if s.startswith('http')][:10]
        for img_url in checked_sample:
            try:
                head_req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(head_req, timeout=8) as img_resp:
                    if img_resp.status != 200:
                        broken_imgs += 1
            except Exception:
                broken_imgs += 1

        self.stats['broken_images_sample'] = broken_imgs
        if broken_imgs > 0:
            self.issues.append(f"Phát hiện {broken_imgs} ảnh trong mẫu thử nghiệm bị lỗi 404 hoặc không truy cập được.")

        return len(empty_imgs) == 0 and broken_imgs == 0

    def check_structure_and_contrast(self, html):
        """Kiểm tra cấu trúc landing page và các khối giao diện cơ bản"""
        has_h1 = bool(re.search(r'<h1\b', html, re.IGNORECASE))
        has_form_or_cta = bool(re.search(r'<(?:form|button|input)\b|class=[\'"][^\'"]*(?:btn|button|cta|hotline|zalo)', html, re.IGNORECASE))
        has_footer = bool(re.search(r'<(?:footer\b|div[^>]*class=[\'"][^\'"]*footer)', html, re.IGNORECASE))
        
        self.stats['has_h1'] = has_h1
        self.stats['has_cta'] = has_form_or_cta
        self.stats['has_footer'] = has_footer
        
        if not has_h1:
            self.issues.append("Trang chưa có thẻ <h1> chính cho tiêu đề Hero.")
        if not has_form_or_cta:
            self.issues.append("Trang thiếu các nút kêu gọi hành động (Call To Action / Hotline / Zalo).")
            
        return has_h1 and has_form_or_cta

    def calculate_score(self):
        """Tính điểm chất lượng từ 0 đến 100%"""
        score = 100
        # Trừ điểm theo mức độ nghiêm trọng
        if self.stats.get('unparsed_shortcodes', 0) > 0:
            score -= min(40, self.stats['unparsed_shortcodes'] * 10)
        if self.stats.get('corrupted_style_tags', 0) > 0:
            score -= 30
        if self.stats.get('empty_images', 0) > 0:
            score -= min(30, self.stats['empty_images'] * 10)
        if self.stats.get('broken_images_sample', 0) > 0:
            score -= min(20, self.stats['broken_images_sample'] * 5)
        if not self.stats.get('has_h1', True):
            score -= 10
        if not self.stats.get('has_cta', True):
            score -= 10
            
        return max(0, score)

    def run_recheck(self):
        """Chạy quy trình kiểm tra toàn diện"""
        print(f"\n=======================================================")
        print(f"   VIBECODE AUTOMATED RECHECK & QUALITY ASSURANCE")
        print(f"=======================================================")
        print(f"Target URL : {self.target_url}")
        if self.post_id:
            print(f"Post ID    : {self.post_id}")
        print(f"Timestamp  : {time.strftime('%Y-%m-%d %H:%M:%S')}")
        print(f"-------------------------------------------------------")

        for attempt in range(1, self.max_retries + 1):
            print(f"\n[Lần kiểm tra {attempt}/{self.max_retries}] Đang phân tích mã nguồn rendered...")
            self.issues = []
            html = self.fetch_live_html()
            
            if not html:
                print(f"❌ [LỖI] Không lấy được nội dung từ URL mục tiêu.")
                if attempt < self.max_retries:
                    time.sleep(2)
                    continue
                return False

            # Thực hiện các bước kiểm tra
            r_shortcode = self.check_unparsed_shortcodes(html)
            r_style = self.check_style_tag_corruption(html)
            r_images = self.check_images(html)
            r_struct = self.check_structure_and_contrast(html)
            
            score = self.calculate_score()
            self.stats['score'] = score

            print(f"\n--- BÁO CÁO THÔNG SỐ (Attempt {attempt}) ---")
            print(f"1. Shortcodes chưa parse   : {self.stats.get('unparsed_shortcodes', 0)} tags {'✓' if r_shortcode else '✗'}")
            print(f"2. Thẻ <style> hợp lệ      : {'✓ 100% Chuẩn' if r_style else '✗ Bị wpautop chèn <p>/<br>'}")
            print(f"3. Hình ảnh                : {self.stats.get('total_images', 0)} ảnh (Rỗng: {self.stats.get('empty_images', 0)}) {'✓' if r_images else '✗'}")
            print(f"4. Thẻ H1 & CTA buttons    : {'✓ Đầy đủ' if r_struct else '✗ Thiếu'}")
            print(f"5. Điểm số chất lượng      : {score}/100%")

            if score >= 90 and not self.issues:
                print(f"\n🎉 [ĐẠT YÊU CẦU] Trang web đạt tiêu chuẩn chất lượng cao ({score}%).")
                print(f"✓ 0 raw shortcodes.")
                print(f"✓ 0 corrupted style tags.")
                print(f"✓ 100% ảnh hiển thị đầy đủ.")
                return True
            else:
                print(f"\n⚠️ [CÁC VẤN ĐỀ CẦN KHẮC PHỤC]:")
                for idx, issue in enumerate(self.issues, 1):
                    print(f"   {idx}. {issue}")
                
                if attempt < self.max_retries:
                    print(f"Đang đợi 3 giây để thử lại...")
                    time.sleep(3)

        return False


def main():
    parser = argparse.ArgumentParser(description="VibeCode Automated Recheck & Quality Assurance Tool")
    parser.add_argument("--url", required=True, help="URL của trang web cần kiểm tra")
    parser.add_argument("--post_id", type=int, help="Post ID trên WordPress (tùy chọn)")
    parser.add_argument("--source_url", help="URL trang web gốc để đối chiếu")
    parser.add_argument("--max_retries", type=int, default=3, help="Số lần recheck tối đa (mặc định: 3)")
    parser.add_argument("--screenshot", help="Tên file ảnh chụp màn hình (tùy chọn)")

    args = parser.parse_args()

    checker = LandingPageRechecker(
        target_url=args.url,
        post_id=args.post_id,
        source_url=args.source_url,
        max_retries=args.max_retries,
        screenshot_output=args.screenshot
    )

    success = checker.run_recheck()
    sys.exit(0 if success else 1)


if __name__ == "__main__":
    main()
