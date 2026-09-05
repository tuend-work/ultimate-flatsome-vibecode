#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Browser-First Media & DOM Sync Tool
Sử dụng Headless Chromium (Playwright) để mở trang web trên trình duyệt thực,
thực thi JavaScript, cuộn trang kích hoạt 100% Lazy Load & Dynamic Elements,
lưu Rendered HTML DOM và đồng bộ ảnh lên WordPress Media Library.
"""

import os
import sys
import json
import re
import time
import urllib.request
import urllib.parse
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
    raise FileNotFoundError("Không tìm thấy tệp vbc-config.json chứa thông tin cấu hình WordPress.")


def fetch_rendered_browser_data(source_url, output_html_path, output_screenshot_path=None):
    """
    Khởi chạy trình duyệt Chromium (Playwright) để:
    1. Render đầy đủ JavaScript (React, Vue, Next.js, Hydration).
    2. Cuộn trang từ từ kích hoạt 100% Lazy-loaded images và CSS animations.
    3. Trích xuất toàn bộ URL hình ảnh thực tế từ DOM runtime.
    4. Lưu Rendered HTML DOM vào output_html_path.
    5. Chụp ảnh Full-Page Screenshot vào output_screenshot_path.
    """
    print(f"\n🌐 [BROWSER ENGINE] Đang mở trình duyệt Headless Chromium để tải URL: {source_url}...")
    try:
        from playwright.sync_api import sync_playwright
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            context = browser.new_context(
                viewport={'width': 1440, 'height': 900},
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36'
            )
            page = context.new_page()

            # Mở URL và chờ network idle
            try:
                page.goto(source_url, wait_until='networkidle', timeout=40000)
            except Exception as nav_e:
                print(f"  [Thông báo] Chờ networkidle hết thời gian ({nav_e}), tiếp tục với trạng thái hiện tại của trang...")

            print("  ✓ Đang tự động cuộn toàn bộ trang để kích hoạt Lazy-load & Render động...")
            page.evaluate("""async () => {
                await new Promise((resolve) => {
                    let totalHeight = 0;
                    let distance = 350;
                    let timer = setInterval(() => {
                        let scrollHeight = document.body.scrollHeight;
                        window.scrollBy(0, distance);
                        totalHeight += distance;
                        if (totalHeight >= scrollHeight) {
                            clearInterval(timer);
                            window.scrollTo(0, 0);
                            resolve();
                        }
                    }, 80);
                });
            }""")

            time.sleep(1.5)

            # 1. Trích xuất toàn bộ URL ảnh trực tiếp từ JavaScript runtime
            extracted_imgs = page.evaluate("""() => {
                const urls = new Set();
                
                // 1. Quét tất cả thẻ img
                document.querySelectorAll('img').forEach(img => {
                    const src = img.currentSrc || img.src || img.getAttribute('src');
                    if (src && !src.startsWith('data:')) urls.add(src);
                    
                    const dsrc = img.getAttribute('data-src') || img.getAttribute('data-lazy-src') || img.getAttribute('data-original');
                    if (dsrc && !dsrc.startsWith('data:')) urls.add(dsrc);

                    const srcset = img.getAttribute('srcset') || img.getAttribute('data-srcset');
                    if (srcset) {
                        srcset.split(',').forEach(part => {
                            const u = part.trim().split(' ')[0];
                            if (u && !u.startsWith('data:')) urls.add(u);
                        });
                    }
                });

                // 2. Quét picture source
                document.querySelectorAll('picture source').forEach(source => {
                    const srcset = source.getAttribute('srcset') || source.getAttribute('data-srcset');
                    if (srcset) {
                        srcset.split(',').forEach(part => {
                            const u = part.trim().split(' ')[0];
                            if (u && !u.startsWith('data:')) urls.add(u);
                        });
                    }
                });

                // 3. Quét CSS background-image
                document.querySelectorAll('*').forEach(el => {
                    const bg = window.getComputedStyle(el).backgroundImage;
                    if (bg && bg !== 'none') {
                        const matches = bg.matchAll(/url\\(['"]?([^'"\\)]+)['"]?\\)/g);
                        for (const match of matches) {
                            const u = match[1];
                            if (u && !u.startsWith('data:') && !u.endsWith('.css') && !u.endsWith('.js')) {
                                urls.add(u);
                            }
                        }
                    }
                });

                return Array.from(urls);
            }""")

            # 2. Lấy Full Rendered HTML DOM
            rendered_html = page.content()
            with open(output_html_path, 'w', encoding='utf-8') as f:
                f.write(rendered_html)
            print(f"  ✓ Đã lưu Full Rendered HTML DOM ({len(rendered_html):,} bytes) vào: {output_html_path}")

            # 3. Chụp Full Page Screenshot
            if output_screenshot_path:
                try:
                    page.screenshot(path=output_screenshot_path, full_page=True)
                    print(f"  ✓ Đã chụp ảnh Full-Page Screenshot thực tế: {output_screenshot_path}")
                except Exception as ss_e:
                    print(f"  [CẢNH BÁO] Không thể chụp screenshot toàn trang: {ss_e}")

            browser.close()
            return rendered_html, extracted_imgs

    except Exception as e:
        print(f"⚠️ [CẢNH BÁO] Lỗi khi chạy Playwright ({e}). Chuyển sang HTTP fallback...")
        req = urllib.request.Request(source_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'})
        with urllib.request.urlopen(req, timeout=30) as resp:
            html = resp.read().decode('utf-8', errors='ignore')
        with open(output_html_path, 'w', encoding='utf-8') as f:
            f.write(html)
        return html, []


def sync_media(source_url, output_dir=None, force_refresh=False):
    root_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..'))
    config = load_config(root_dir)
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')

    if not output_dir:
        slug = re.sub(r'[^a-zA-Z0-9\-]', '', source_url.split('/')[-2] if source_url.endswith('/') else source_url.split('/')[-1]) or 'landingpage'
        output_dir = os.path.join('tmp', slug)

    os.makedirs(output_dir, exist_ok=True)
    media_map_file = os.path.join(output_dir, 'media_map.json')
    html_file = os.path.join(output_dir, 'source.html')
    screenshot_file = os.path.join(output_dir, 'source_screenshot.png')

    # 1. Thu thập HTML Rendered thực tế từ Trình duyệt (Playwright)
    runtime_imgs = []
    if force_refresh or not os.path.exists(html_file):
        html, runtime_imgs = fetch_rendered_browser_data(source_url, html_file, screenshot_file)
    else:
        with open(html_file, 'r', encoding='utf-8') as f:
            html = f.read()
        print(f"📁 Sử dụng file HTML DOM đã có sẵn: {html_file}")

    # 2. Tổng hợp danh sách hình ảnh từ cả Runtime Browser & Rendered HTML Regex
    img_urls = set()
    for u in runtime_imgs:
        if u and not u.startswith('data:'):
            img_urls.add(urllib.parse.urljoin(source_url, u))

    for src in re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"]', html, re.IGNORECASE):
        if src and not src.startswith('data:'):
            img_urls.add(urllib.parse.urljoin(source_url, src))

    for dsrc in re.findall(r'data-(?:src|lazy-src|original)=[\'"]([^\'"]+)[\'"]', html, re.IGNORECASE):
        if dsrc and not dsrc.startswith('data:'):
            img_urls.add(urllib.parse.urljoin(source_url, dsrc))

    for bg in re.findall(r'url\([\'"]?(https?:\/\/[^\'")]+|\/[^\'")]+)[\'"]?\)', html, re.IGNORECASE):
        if bg and not bg.startswith('data:') and not bg.endswith('.css') and not bg.endswith('.js'):
            img_urls.add(urllib.parse.urljoin(source_url, bg))

    print(f"🔍 Phát hiện tổng cộng {len(img_urls)} hình ảnh cần đồng bộ lên WordPress Media Library.")

    media_map = {}
    if os.path.exists(media_map_file):
        try:
            with open(media_map_file, 'r', encoding='utf-8') as f:
                media_map = json.load(f)
        except Exception:
            media_map = {}

    upload_endpoint = f"{api_url}/vbc/v1/upload"
    check_media_endpoint = f"{api_url}/vbc/v1/check-media"
    count = 0

    # 3. Batch Check existing media on WordPress Media Library via /vbc/v1/check-media
    url_to_filename = {}
    filenames_to_check = []
    for img_url in img_urls:
        if img_url in media_map and media_map[img_url] and ('/wp-content/uploads/' in media_map[img_url] or 'wpcloud.vn' in media_map[img_url]):
            continue
        fname = os.path.basename(urllib.parse.urlparse(img_url).path) or f"img_{abs(hash(img_url))}.jpg"
        if not re.search(r'\.(jpg|jpeg|png|webp|svg|gif)$', fname, re.IGNORECASE):
            fname += '.jpg'
        url_to_filename[img_url] = fname
        filenames_to_check.append(fname)

    checked_results = {}
    if filenames_to_check:
        print(f"⚡ Đang kiểm tra nhanh {len(filenames_to_check)} ảnh trên WordPress Media Library...")
        try:
            check_payload = json.dumps({"filenames": filenames_to_check}).encode('utf-8')
            check_req = urllib.request.Request(
                check_media_endpoint,
                data=check_payload,
                headers={
                    'Content-Type': 'application/json',
                    'X-VBC-Token': token,
                    'User-Agent': 'Mozilla/5.0'
                }
            )
            with urllib.request.urlopen(check_req, timeout=15) as resp:
                check_res = json.loads(resp.read().decode('utf-8'))
                checked_results = check_res.get('results', {})
                found_cnt = check_res.get('found_count', 0)
                print(f" -> ✅ Đã tìm thấy {found_cnt}/{len(filenames_to_check)} ảnh đã có sẵn trên WordPress!")
        except Exception as e:
            print(f" -> [WARN] Lỗi khi gọi /vbc/v1/check-media: {e}. Sẽ tải từng ảnh.")

    # 4. Xử lý từng ảnh: dùng URL WP đã có hoặc upload mới
    for img_url, filename in url_to_filename.items():
        if filename in checked_results and checked_results[filename].get('exists'):
            wp_url = checked_results[filename].get('url')
            media_map[img_url] = wp_url
            count += 1
            print(f" -> [{count}] Tái sử dụng (đã có trên WP): {filename} => {wp_url}")
            continue

        local_img_path = os.path.join(output_dir, filename)

        try:
            if not os.path.exists(local_img_path):
                req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(req, timeout=15) as resp:
                    with open(local_img_path, 'wb') as f:
                        f.write(resp.read())

            with open(local_img_path, 'rb') as f:
                img_data = f.read()

            ext = os.path.splitext(filename)[1].lower()
            content_type_map = {
                '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
                '.webp': 'image/webp', '.svg': 'image/svg+xml', '.gif': 'image/gif'
            }
            content_type = content_type_map.get(ext, 'image/jpeg')

            upload_req = urllib.request.Request(
                upload_endpoint,
                data=img_data,
                headers={
                    'Content-Type': content_type,
                    'X-File-Name': filename,
                    'X-VBC-Token': token
                }
            )

            with urllib.request.urlopen(upload_req, timeout=30) as resp:
                data = json.loads(resp.read().decode('utf-8'))
                wp_url = data.get('url') or data.get('source_url')
                if wp_url:
                    media_map[img_url] = wp_url
                    count += 1
                    print(f" -> [{count}] Đã upload mới: {filename} => {wp_url}")
        except Exception as e:
            media_map[img_url] = img_url
            print(f" -> [WARN] Lỗi khi xử lý {filename}: {e}")

    with open(media_map_file, 'w', encoding='utf-8') as f:
        json.dump(media_map, f, ensure_ascii=False, indent=2)

    print(f"\n🎉 Đã đồng bộ xong {len(media_map)} hình ảnh. Ánh xạ lưu tại: {media_map_file}")
    return media_map_file


def main():
    parser = argparse.ArgumentParser(description="VibeCode Browser-First Media & DOM Sync Tool")
    parser.add_argument("--url", required=True, help="URL trang nguồn cần clone")
    parser.add_argument("--output_dir", help="Thư mục lưu media map và source.html")
    parser.add_argument("--force_refresh", action="store_true", help="Bắt buộc tải lại DOM mới từ trình duyệt")
    args = parser.parse_args()
    sync_media(args.url, args.output_dir, args.force_refresh)


if __name__ == "__main__":
    main()
