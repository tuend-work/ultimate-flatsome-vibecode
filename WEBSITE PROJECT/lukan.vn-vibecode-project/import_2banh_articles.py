#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script copy 12 bài viết mới nhất từ 2banh.vn (chuyên mục Các loại xe khác)
- Làm sạch text link (unwrap thẻ <a>, giữ nguyên nội dung văn bản)
- Tải toàn bộ hình ảnh về và upload lên WordPress Media Library (lukan.vn)
- Đặt ảnh đầu tiên làm Featured Image (Ảnh đại diện)
- Gán chuyên mục: Các loại xe khác, Tin tức
- Xuất bản thành Post bài viết chuẩn trên WordPress
"""

import os
import sys
import json
import re
import urllib.request
import urllib.parse
from bs4 import BeautifulSoup

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

API_URL = "http://lukan.vn/wp-json"
TOKEN = "069271c621ab07bfa6d298817be101e23cd032e9"
FORUM_URL = "https://www.2banh.vn/forums/cac-loai-xe-khac.60/"

def get_latest_thread_urls(limit=12):
    print(f"🔍 Đang quét danh sách bài viết từ: {FORUM_URL}...")
    req = urllib.request.Request(FORUM_URL, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'})
    html = urllib.request.urlopen(req, timeout=25).read().decode('utf-8', errors='ignore')
    soup = BeautifulSoup(html, 'html.parser')

    items = soup.select('li.discussionListItem:not(.sticky)')
    threads = []
    for item in items:
        title_el = item.select_one('h3.title a.PreviewTooltip') or item.select_one('h3.title a:not(.prefixLink)')
        if not title_el:
            continue
        title = title_el.get_text(strip=True)
        href = title_el.get('href')
        if href and not href.startswith('http'):
            href = 'https://www.2banh.vn/' + href.lstrip('/')
        threads.append({'title': title, 'url': href})
        if len(threads) >= limit:
            break

    print(f"✅ Đã tìm thấy {len(threads)} bài viết mới nhất.")
    return threads

def upload_image_to_wp(img_url, fallback_name="image"):
    try:
        req = urllib.request.Request(img_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        with urllib.request.urlopen(req, timeout=25) as resp:
            img_data = resp.read()

        parsed = urllib.parse.urlparse(img_url)
        fname = os.path.basename(parsed.path)
        if not fname or not re.search(r'\.(jpg|jpeg|png|webp|gif)$', fname, re.IGNORECASE):
            fname = f"{fallback_name}_{abs(hash(img_url))}.jpg"

        ext = os.path.splitext(fname)[1].lower()
        ct_map = {
            '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg',
            '.png': 'image/png', '.webp': 'image/webp', '.gif': 'image/gif'
        }
        content_type = ct_map.get(ext, 'image/jpeg')

        up_req = urllib.request.Request(
            f"{API_URL}/vbc/v1/upload",
            data=img_data,
            headers={
                'Content-Type': content_type,
                'X-File-Name': fname,
                'X-VBC-Token': TOKEN,
                'User-Agent': 'Mozilla/5.0'
            }
        )
        with urllib.request.urlopen(up_req, timeout=35) as up_resp:
            res = json.loads(up_resp.read().decode('utf-8'))
            if res.get('success'):
                return res.get('id') or res.get('attachment_id'), res.get('url')
    except Exception as e:
        print(f"      [Lỗi upload ảnh {img_url}]: {e}")
    return None, None

def check_existing_post(slug):
    try:
        req = urllib.request.Request(
            f"{API_URL}/vbc/v1/page?slug={slug}",
            headers={'X-VBC-Token': TOKEN, 'User-Agent': 'Mozilla/5.0'}
        )
        with urllib.request.urlopen(req, timeout=15) as resp:
            res = json.loads(resp.read().decode('utf-8'))
            if res.get('success') and res.get('post_id'):
                return res.get('post_id'), res.get('url')
    except Exception:
        pass
    return None, None

def import_single_article(article_info, index, total):
    thread_url = article_info['url']
    print(f"\n==================================================================")
    print(f"▶ [{index}/{total}] Bắt đầu xử lý: {article_info['title']}")
    print(f"  URL gốc: {thread_url}")

    req = urllib.request.Request(thread_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
    html = urllib.request.urlopen(req, timeout=25).read().decode('utf-8', errors='ignore')
    soup = BeautifulSoup(html, 'html.parser')

    # 1. Tiêu đề bài viết
    h1 = soup.select_one('h1')
    title = h1.get_text(strip=True) if h1 else article_info['title']

    # 2. Slug bài viết
    slug_match = re.search(r'/threads/([^/]+?)(?:\.\d+)?/?$', thread_url)
    slug = slug_match.group(1) if slug_match else re.sub(r'[^a-zA-Z0-9\-]', '', title.lower())

    # Kiểm tra xem bài viết đã tồn tại chưa
    existing_id, existing_url = check_existing_post(slug)
    if existing_id:
        print(f"  ℹ️ Bài viết đã tồn tại (ID: {existing_id}). Sẽ cập nhật nội dung...")

    # 3. Lấy nội dung bài viết
    msg = soup.select_one('.messageText')
    if not msg:
        print(f"  ❌ Không tìm thấy thẻ .messageText trong bài viết!")
        return None

    # Xóa bỏ các thành phần rác của diễn đàn
    for el in msg.select('script, style, iframe, .sharePage, .messageMeta, .likesSummary, .attachedFiles, .bbCodeQuote'):
        el.decompose()

    # 4. Làm sạch text link (Unwrap toàn bộ thẻ <a> để giữ lại text thuần túy)
    links = msg.find_all('a')
    links_cleaned_count = len(links)
    for a in links:
        a.unwrap()
    print(f"  🧹 Đã làm sạch {links_cleaned_count} text link trong bài viết.")

    # 5. Tải ảnh về và upload lên WordPress Media Library
    imgs = msg.find_all('img')
    print(f"  🖼️ Tìm thấy {len(imgs)} hình ảnh. Đang tiến hành tải về và upload lên web...")
    
    first_att_id = None
    first_att_url = None
    uploaded_images_count = 0

    for img_idx, img in enumerate(imgs, 1):
        src = img.get('src') or img.get('data-url')
        if not src or src.startswith('data:'):
            continue
        src = urllib.parse.urljoin('https://www.2banh.vn/', src)
        
        # Bỏ qua các icon mặt cười (smilies) nếu có
        if 'styles/default/xenforo/clear.png' in src or 'smilies' in src:
            img.decompose()
            continue

        print(f"    -> Đang tải ảnh ({img_idx}/{len(imgs)}): {src[:65]}...")
        att_id, wp_url = upload_image_to_wp(src, f"{slug}-{img_idx}")
        if wp_url:
            uploaded_images_count += 1
            img['src'] = wp_url
            img['style'] = "max-width: 100%; height: auto; border-radius: 8px; margin: 18px auto; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);"
            img['loading'] = "lazy"
            for attr in ['data-url', 'itemprop', 'class', 'width', 'height']:
                if attr in img.attrs:
                    del img.attrs[attr]

            if not first_att_id and att_id:
                first_att_id = att_id
                first_att_url = wp_url

    # 6. Chuẩn hóa cấu trúc HTML
    full_text = msg.get_text(separator=' ', strip=True)
    excerpt = full_text[:250] + '...' if len(full_text) > 250 else full_text

    # Chuyển đổi msg sang HTML nội dung sạch
    # Đổi msg tag từ blockquote thành div hoặc unwrap
    msg.name = 'div'
    for attr in list(msg.attrs.keys()):
        del msg.attrs[attr]
    msg['class'] = 'vbc-article-content'

    content_html = str(msg)

    # 7. Xuất bản lên WordPress
    payload = {
        'title': title,
        'slug': slug,
        'content': content_html,
        'excerpt': excerpt,
        'status': 'publish',
        'post_type': 'post',
        'template': 'default',
        'thumbnail_id': first_att_id,
        'category_names': ['Các loại xe khác', 'Tin tức']
    }
    if existing_id:
        payload['post_id'] = existing_id

    pub_req = urllib.request.Request(
        f"{API_URL}/vbc/v1/page",
        data=json.dumps(payload).encode('utf-8'),
        headers={
            'Content-Type': 'application/json',
            'X-VBC-Token': TOKEN,
            'User-Agent': 'Mozilla/5.0'
        }
    )
    with urllib.request.urlopen(pub_req, timeout=35) as pub_resp:
        result = json.loads(pub_resp.read().decode('utf-8'))
        post_id = result.get('post_id')
        post_url = result.get('url')
        print(f"  🎉 XUẤT BẢN THÀNH CÔNG! Post ID: {post_id}")
        print(f"  🔗 Link bài viết: {post_url}")
        print(f"  📸 Ảnh đại diện (ID: {first_att_id}): {first_att_url}")
        return {
            'index': index,
            'title': title,
            'post_id': post_id,
            'url': post_url,
            'images_count': uploaded_images_count,
            'links_cleaned': links_cleaned_count,
            'thumbnail_url': first_att_url,
            'source_url': thread_url
        }

def main():
    threads = get_latest_thread_urls(12)
    results = []
    for idx, t in enumerate(threads, 1):
        try:
            res = import_single_article(t, idx, len(threads))
            if res:
                results.append(res)
        except Exception as e:
            print(f"  ❌ Lỗi khi xử lý bài viết {t['title']}: {e}")

    print("\n" + "="*70)
    print("🏆 BÁO CÁO TỔNG KẾT COPY 12 BÀI VIẾT TỪ 2BANH.VN VỀ LUKAN.VN")
    print("="*70)
    print(f"Tổng số bài viết đã xuất bản: {len(results)}/12 bài")
    total_imgs = sum(r['images_count'] for r in results)
    total_links = sum(r['links_cleaned'] for r in results)
    print(f"Tổng số hình ảnh đã tải về và đồng bộ lên Media Library: {total_imgs} ảnh")
    print(f"Tổng số text links đã làm sạch: {total_links} links")
    print("="*70)

    # Lưu kết quả ra file JSON
    out_file = "f:/DEV/ultimate-flatsome-vibecode/WEBSITE PROJECT/lukan.vn-vibecode-project/import_results.json"
    with open(out_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print(f"Đã lưu chi tiết danh sách vào: {out_file}\n")

if __name__ == '__main__':
    main()
