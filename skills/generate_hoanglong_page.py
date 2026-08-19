# -*- coding: utf-8 -*-
"""
Generate & Publish 99% Pixel-Perfect Clone of Hoàng Long Hải Vân Express
To WordPress via VibeCode REST API
"""
import sys
import os
import json
import urllib.request
import re

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

def load_config():
    with open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8') as f:
        return json.load(f)

def load_media_map():
    with open('hoanglong_media_map.json', 'r', encoding='utf-8') as f:
        return json.load(f)

config = load_config()
media_map = load_media_map()

def get_media(key, default_url=""):
    item = media_map.get(key)
    if item:
        return item['url'], item['id']
    return default_url, ""

# Asset URLs
url_banner, id_banner = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/banner-hlhv.jpg")
url_logo, id_logo = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/06/logo-hoang-long-hai-van-1024x1024.jpg")
url_zalo, id_zalo = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/zalo.png")

# Services
url_dv1, id_dv1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/img2-300x300.png")
url_dv2, id_dv2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/img2-1-300x200.png")
url_dv3, id_dv3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/img3-300x300.png")
url_dv4, id_dv4 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/04/img4-300x300.png")

# Why choose (6 icons)
url_w1, id_w1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/24-hours.png")
url_w2, id_w2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/bus.png")
url_w3, id_w3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/kn.png")
url_w4, id_w4 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/gia.png")
url_w5, id_w5 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/dd.png")
url_w6, id_w6 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/love.png")

# Process (4 icons)
url_p1, id_p1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/telephone-call.png")
url_p2, id_p2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/time-1.png")
url_p3, id_p3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/approval.png")
url_p4, id_p4 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/on-time.png")

# Testimonials (3 avatars)
url_kh1, id_kh1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/kh1-150x150.png")
url_kh2, id_kh2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/kh2-150x150.png")
url_kh3, id_kh3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/05/kh3-150x150.png")

# Popular routes (6 images)
url_r1, id_r1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/xe-du-lich-bac-nam-1.png")
url_r2, id_r2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/xe-du-lich-bac-nam.png")
url_r3, id_r3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/gui-xe-may-hoang-long-hai-van.png")
url_r4, id_r4 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/gio-chay-xe-ha-noi-sai-gon.png")
url_r5, id_r5 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/nha-xe-ha-noi-sai-gon.png")
url_r6, id_r6 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/xe-khach-bac-nam-hoang-long-hai-van-1.png")

# News (4 images)
url_n1, id_n1 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/van-chuyen-vat-lieu-xay-dung-bac-nam.png")
url_n2, id_n2 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/van-chuyen-hang-cong-kenh-bac-nam.png")
url_n3, id_n3 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/van-chuyen-hang-de-vo-bac-nam.png")
url_n4, id_n4 = get_media("https://hoanglonghaivanexpress.com/wp-content/uploads/2026/08/van-chuyen-hang-cong-nghiep-bac-nam.png")

# Build the complete Shortcode
shortcode = f"""
[vbc_div custom_css="selector {{ width: 100%; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333; }}"]

<style>
/* CSS Reset & General Styles */
.hlhv-container {{ max-width: 1240px; margin: 0 auto; padding: 0 15px; box-sizing: border-box; }}
.hlhv-heading {{ text-align: center; margin-bottom: 35px; }}
.hlhv-heading h2 {{ position: relative; display: inline-block; font-size: 28px; text-transform: uppercase; color: #111; font-weight: 800; margin: 0; padding-bottom: 12px; }}
.hlhv-heading h2::after {{ content: ''; position: absolute; left: 50%; bottom: 0; transform: translateX(-50%); width: 60px; height: 3px; background: #b20000; border-radius: 2px; }}

/* Global Overrides for 99% Visual Fidelity */
.hlhv-card, .hlhv-card-img, .hlhv-why-box, .hlhv-why-icon, .hlhv-process-item, .hlhv-process-icon, .hlhv-testi-card, .hlhv-testi-avatar {{
  opacity: 1 !important;
  visibility: visible !important;
}}
.hlhv-card {{
  background: #ffffff !important;
  border: 1px solid #e2e8f0 !important;
}}
.hlhv-card-title {{
  color: #111827 !important;
  font-size: 16px !important;
  font-weight: 800 !important;
}}
.hlhv-card-desc {{
  color: #4b5563 !important;
  font-size: 13.5px !important;
}}
.hlhv-card-btn {{
  background: #b20000 !important;
  color: #ffffff !important;
}}
.hlhv-why-box {{
  background: #ffffff !important;
  border: 1px solid #e5e7eb !important;
}}
.hlhv-why-title {{
  color: #111827 !important;
  font-size: 15px !important;
  font-weight: 800 !important;
}}
.hlhv-why-desc {{
  color: #4b5563 !important;
  font-size: 13px !important;
}}
.hlhv-testi-card {{
  background: #ffffff !important;
  border: 1px solid #e5e7eb !important;
}}
.hlhv-testi-text {{
  color: #374151 !important;
  font-size: 13.5px !important;
}}
.hlhv-testi-author {{
  color: #111827 !important;
  font-size: 14px !important;
  font-weight: 800 !important;
}}

/* Hero Banner */
.hlhv-hero {{ position: relative; min-height: 620px; background-color: #f8fafc; background-image: url('{url_banner}'); background-size: cover; background-position: center right; background-repeat: no-repeat; display: flex; align-items: center; padding: 60px 0 130px; border-bottom: 2px solid #b20000; }}
.hlhv-hero-overlay {{ position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,0.97) 0%, rgba(255,255,255,0.92) 48%, rgba(255,255,255,0.2) 100%); pointer-events: none; }}
.hlhv-hero-content {{ position: relative; z-index: 2; max-width: 620px; padding: 20px 0; }}
.hlhv-hero-title {{ font-size: 38px; line-height: 1.25; font-weight: 900; color: #b20000; text-transform: uppercase; margin-bottom: 22px; letter-spacing: -0.5px; }}
.hlhv-hero-list {{ list-style: none; padding: 0; margin: 0 0 32px 0; }}
.hlhv-hero-list li {{ position: relative; padding-left: 38px; margin-bottom: 14px; font-size: 17px; font-weight: 700; color: #111827; }}
.hlhv-hero-list li::before {{ content: '✓'; position: absolute; left: 0; top: 0; width: 26px; height: 26px; background: #b20000; color: #fff; border-radius: 50%; text-align: center; line-height: 26px; font-size: 14px; font-weight: 900; box-shadow: 0 2px 6px rgba(178,0,0,0.3); }}
.hlhv-hero-actions {{ display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 25px; }}

.hlhv-btn-call {{ background: linear-gradient(135deg, #e60000, #b20000); color: #fff !important; padding: 15px 28px; font-size: 16px; font-weight: 800; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 20px rgba(178,0,0,0.4); transition: all 0.3s; }}
.hlhv-btn-call:hover {{ transform: translateY(-3px); box-shadow: 0 10px 28px rgba(178,0,0,0.55); color: #fff !important; }}
.hlhv-btn-zalo {{ background: #fff; color: #0068ff !important; border: 2px solid #0068ff; padding: 15px 28px; font-size: 16px; font-weight: 800; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(0,104,255,0.15); transition: all 0.3s; }}
.hlhv-btn-zalo:hover {{ transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,104,255,0.3); background: #f0f7ff; color: #0068ff !important; }}

/* Booking Form */
.hlhv-booking-wrap {{ max-width: 1080px; margin: -70px auto 60px; position: relative; z-index: 10; padding: 0 15px; }}
.hlhv-booking-card {{ background: #fff; border-radius: 14px; box-shadow: 0 14px 45px rgba(0,0,0,0.15); padding: 28px; border: 1px solid #e5e7eb; }}
.hlhv-tabs-header {{ display: flex; justify-content: center; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }}
.hlhv-tab-btn {{ background: #f5f5f5; border: none; padding: 12px 32px; border-radius: 25px; font-size: 15px; font-weight: 800; color: #555; cursor: pointer; transition: all 0.25s; text-transform: uppercase; }}
.hlhv-tab-btn.active {{ background: #b20000; color: #fff; box-shadow: 0 4px 14px rgba(178,0,0,0.35); }}
.hlhv-form-grid {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 20px; }}
.hlhv-input-group {{ background: #fbfbfb; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; flex-direction: column; transition: border-color 0.2s; }}
.hlhv-input-group:focus-within {{ border-color: #b20000; background: #fff; }}
.hlhv-input-group label {{ font-size: 12px; font-weight: 800; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }}
.hlhv-input-group select, .hlhv-input-group input {{ border: none; background: transparent; font-size: 15px; font-weight: 600; color: #1e293b; outline: none; width: 100%; }}
.hlhv-btn-submit {{ width: 100%; background: linear-gradient(135deg, #d30000, #a00000); color: #fff; border: none; border-radius: 8px; padding: 16px; font-size: 16.5px; font-weight: 900; text-transform: uppercase; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(178,0,0,0.35); letter-spacing: 0.5px; }}
.hlhv-btn-submit:hover {{ background: #850000; transform: translateY(-2px); box-shadow: 0 10px 28px rgba(178,0,0,0.5); }}

/* Cards & Grid Styles */
.hlhv-grid-4 {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }}
.hlhv-grid-3 {{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }}
.hlhv-grid-6 {{ display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px; }}

.hlhv-card {{ background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid #e9ecef; display: flex; flex-direction: column; }}
.hlhv-card:hover {{ transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.13); border-color: #cbd5e1; }}
.hlhv-card-img {{ width: 100%; height: 210px; min-height: 210px; object-fit: cover; display: block; background: #f1f5f9; }}
.hlhv-card-body {{ padding: 18px; flex-grow: 1; display: flex; flex-direction: column; }}
.hlhv-card-title {{ font-size: 16px; font-weight: 800; color: #111827; margin: 0 0 10px; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }}
.hlhv-card-desc {{ font-size: 13.5px; color: #4b5563; line-height: 1.55; margin-bottom: 16px; flex-grow: 1; }}
.hlhv-card-btn {{ display: inline-block; text-align: center; background: #b20000; color: #fff !important; padding: 9px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: all 0.2s; }}
.hlhv-card-btn:hover {{ background: #8f0000; transform: translateY(-1px); }}

/* Why Choose Us Icons */
.hlhv-why-box {{ background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 22px 14px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.04); transition: all 0.3s; }}
.hlhv-why-box:hover {{ transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); border-color: #b20000; }}
.hlhv-why-icon {{ width: 56px; height: 56px; margin: 0 auto 12px; object-fit: contain; }}
.hlhv-why-title {{ font-size: 15px; font-weight: 700; color: #111; margin-bottom: 6px; text-transform: uppercase; }}
.hlhv-why-desc {{ font-size: 12.5px; color: #666; line-height: 1.4; margin: 0; }}

/* Process Banner (Gradient Red) */
.hlhv-process-sec {{ background: linear-gradient(135deg, #b20000 0%, #750000 100%); padding: 50px 0; color: #fff; }}
.hlhv-process-sec .hlhv-heading h2 {{ color: #fff; }}
.hlhv-process-sec .hlhv-heading h2::after {{ background: #fff; }}
.hlhv-process-grid {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }}
.hlhv-process-item {{ display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.08); border-radius: 8px; padding: 18px 16px; border: 1px solid rgba(255,255,255,0.15); }}
.hlhv-process-icon {{ width: 44px; height: 44px; object-fit: contain; flex-shrink: 0; filter: brightness(0) invert(1); }}
.hlhv-process-info h4 {{ font-size: 16px; font-weight: 700; margin: 0 0 4px; color: #fff; }}
.hlhv-process-info p {{ font-size: 13px; margin: 0; color: rgba(255,255,255,0.85); line-height: 1.4; }}

/* Testimonials */
.hlhv-testi-card {{ background: #fff; border-radius: 8px; padding: 22px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #eee; display: flex; gap: 16px; align-items: flex-start; }}
.hlhv-testi-avatar {{ width: 65px; height: 65px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 2px solid #b20000; }}
.hlhv-testi-stars {{ color: #ffb800; font-size: 15px; margin-bottom: 8px; }}
.hlhv-testi-text {{ font-size: 13.5px; color: #555; line-height: 1.5; margin-bottom: 10px; font-style: italic; }}
.hlhv-testi-author {{ font-size: 14px; font-weight: 700; color: #111; margin: 0; }}

/* Call to Action Red Bar */
.hlhv-cta-bar {{ background: linear-gradient(90deg, #990000 0%, #c40000 50%, #990000 100%); color: #fff; padding: 25px 0; margin-top: 50px; }}
.hlhv-cta-inner {{ display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }}
.hlhv-cta-title {{ font-size: 20px; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }}
.hlhv-cta-phones {{ display: flex; gap: 20px; flex-wrap: wrap; font-size: 18px; font-weight: 800; color: #ffeb3b; }}
.hlhv-cta-phones a {{ color: #fff !important; text-decoration: none; transition: color 0.2s; }}
.hlhv-cta-phones a:hover {{ color: #ffeb3b !important; }}

/* Footer Contact Section */
.hlhv-footer-info {{ background: #fdfdfd; padding: 45px 0 20px; border-top: 1px solid #eee; }}
.hlhv-footer-grid {{ display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 25px; }}
.hlhv-ft-col h3 {{ font-size: 16px; font-weight: 800; text-transform: uppercase; color: #111; margin: 0 0 15px; position: relative; padding-bottom: 8px; }}
.hlhv-ft-col h3::after {{ content: ''; position: absolute; left: 0; bottom: 0; width: 35px; height: 2px; background: #b20000; }}
.hlhv-ft-col p {{ font-size: 13.5px; color: #555; line-height: 1.6; margin: 0 0 10px; }}
.hlhv-ft-col strong {{ color: #222; font-weight: 700; }}

/* Sticky Floating Contact */
.hlhv-floating-contact {{ position: fixed; right: 20px; bottom: 30px; z-index: 99999; display: flex; flex-direction: column; gap: 12px; }}
.hlhv-float-btn {{ width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3); transition: transform 0.3s; position: relative; }}
.hlhv-float-btn:hover {{ transform: scale(1.1); }}
.hlhv-float-phone {{ background: #28a745; }}
.hlhv-float-zalo {{ background: #0068ff; }}
.hlhv-float-btn img {{ width: 28px; height: 28px; object-fit: contain; }}

/* Responsive */
@media (max-width: 1024px) {{
  .hlhv-grid-4, .hlhv-process-grid {{ grid-template-columns: repeat(2, 1fr); }}
  .hlhv-grid-6 {{ grid-template-columns: repeat(3, 1fr); }}
  .hlhv-footer-grid {{ grid-template-columns: repeat(2, 1fr); }}
}}
@media (max-width: 768px) {{
  .hlhv-hero-title {{ font-size: 26px; }}
  .hlhv-grid-4, .hlhv-grid-3, .hlhv-grid-6, .hlhv-process-grid, .hlhv-footer-grid {{ grid-template-columns: 1fr; }}
  .hlhv-cta-inner {{ flex-direction: column; text-align: center; }}
  .hlhv-hero {{ min-height: 380px; padding: 30px 0; }}
}}
</style>

<!-- HERO SECTION -->
[vbc_div class="hlhv-hero"]
  <div class="hlhv-hero-overlay"></div>
  <div class="hlhv-container" style="width: 100%;">
    <div class="hlhv-hero-content">
      [vbc_h1 class="hlhv-hero-title"]XE KHÁCH BẮC NAM<br/>&amp; CHO THUÊ XE DU LỊCH[/vbc_h1]
      <ul class="hlhv-hero-list">
        <li>Tuyến Bắc – Nam, phục vụ 24/7</li>
        <li>Đa dạng dòng xe: 4 – 45 chỗ đời mới</li>
        <li>Tài xế chuyên nghiệp, phục vụ tận tâm</li>
        <li>Đưa đón tận nơi, cam kết đúng giờ</li>
      </ul>
      <div class="hlhv-hero-actions">
        [vbc_a link_url="tel:0968866855" class="hlhv-btn-call"]
          <span>📞 Gọi ngay: 0968.866.855</span>
        [/vbc_a]
        [vbc_a link_url="https://zalo.me/0968866855" link_target="_blank" class="hlhv-btn-zalo"]
          <img src="{url_zalo}" alt="Zalo" style="width: 22px; height: 22px;"/>
          <span>Đặt xe qua Zalo</span>
        [/vbc_a]
      </div>
    </div>
  </div>
[/vbc_div]

<!-- BOOKING FORM WRAPPER -->
<div class="hlhv-booking-wrap">
  <div class="hlhv-booking-card">
    <div class="hlhv-tabs-header">
      <button type="button" class="hlhv-tab-btn active">Đặt Vé Xe Khách</button>
      <button type="button" class="hlhv-tab-btn">Cho Thuê Xe &amp; Gửi Hàng</button>
    </div>
    <form onsubmit="event.preventDefault(); alert('Cảm ơn quý khách! Tổng đài Hoàng Long Hải Vân sẽ liên hệ lại ngay trong 3 phút.');">
      <div class="hlhv-form-grid">
        <div class="hlhv-input-group">
          <label>📍 Điểm Đi</label>
          <select name="diem_di" required>
            <option value="">-- Chọn điểm xuất phát --</option>
            <option value="Hà Nội" selected>Hà Nội (Bến xe Nước Ngầm / Giáp Bát)</option>
            <option value="Hải Phòng">Hải Phòng</option>
            <option value="Ninh Bình">Ninh Bình</option>
            <option value="Thanh Hóa">Thanh Hóa</option>
            <option value="Nghệ An">Nghệ An (Vinh)</option>
            <option value="Hà Tĩnh">Hà Tĩnh</option>
            <option value="Quảng Bình">Quảng Bình</option>
            <option value="Quảng Trị">Quảng Trị</option>
            <option value="Huế">Thừa Thiên Huế</option>
            <option value="Đà Nẵng">Đà Nẵng</option>
            <option value="Quảng Nam">Quảng Nam</option>
            <option value="Quảng Ngãi">Quảng Ngãi</option>
            <option value="Bình Định">Bình Định (Quy Nhơn)</option>
            <option value="Khánh Hòa">Khánh Hòa (Nha Trang)</option>
            <option value="Ninh Thuận">Ninh Thuận (Phan Rang)</option>
            <option value="Bình Thuận">Bình Thuận (Phan Thiết)</option>
            <option value="Đồng Nai">Đồng Nai (Biên Hòa)</option>
            <option value="TP.HCM">TP. Hồ Chí Minh (Bến xe Miền Đông / An Sương)</option>
          </select>
        </div>
        <div class="hlhv-input-group">
          <label>📍 Điểm Đến</label>
          <select name="diem_den" required>
            <option value="">-- Chọn điểm đến --</option>
            <option value="TP.HCM" selected>TP. Hồ Chí Minh (Bến xe Miền Đông / An Sương)</option>
            <option value="Bình Dương">Bình Dương / Thủ Dầu Một</option>
            <option value="Đồng Nai">Đồng Nai</option>
            <option value="Nha Trang">Nha Trang / Cam Ranh</option>
            <option value="Quy Nhơn">Quy Nhơn / Bình Định</option>
            <option value="Đà Nẵng">Đà Nẵng</option>
            <option value="Huế">Huế</option>
            <option value="Quảng Trị">Quảng Trị</option>
            <option value="Quảng Bình">Quảng Bình</option>
            <option value="Hà Tĩnh">Hà Tĩnh</option>
            <option value="Nghệ An">Nghệ An</option>
            <option value="Thanh Hóa">Thanh Hóa</option>
            <option value="Hà Nội">Hà Nội</option>
          </select>
        </div>
        <div class="hlhv-input-group">
          <label>📅 Ngày Đi</label>
          <input type="date" name="ngay_di" required />
        </div>
        <div class="hlhv-input-group">
          <label>🚍 Loại Xe / Dịch Vụ</label>
          <select name="loai_xe">
            <option value="Xe giường nằm cao cấp">Xe Giường Nằm Cao Cấp</option>
            <option value="Limousine VIP">Limousine VIP 34 Phòng</option>
            <option value="Thuê xe 4 - 7 chỗ">Thuê xe du lịch 4 - 7 chỗ</option>
            <option value="Thuê xe 16 - 29 chỗ">Thuê xe du lịch 16 - 29 chỗ</option>
            <option value="Thuê xe 35 - 45 chỗ">Thuê xe du lịch 35 - 45 chỗ</option>
            <option value="Gửi xe máy Bắc Nam">Gửi xe máy Bắc Nam</option>
            <option value="Gửi hàng hóa / Bưu kiện">Gửi hàng hóa / Bưu phẩm</option>
          </select>
        </div>
        <div class="hlhv-input-group">
          <label>📞 Số Điện Thoại</label>
          <input type="tel" name="phone" placeholder="Nhập số điện thoại nhận vé..." required />
        </div>
      </div>
      <button type="submit" class="hlhv-btn-submit">🔍 TÌM CHUYẾN XE &amp; ĐẶT VÉ NHANH</button>
    </form>
  </div>
</div>

<!-- SECTION 1: CÁC TUYẾN ĐƯỜNG PHỔ BIẾN -->
[vbc_div custom_css="selector {{ padding: 30px 0 60px; }}"]
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="CÁC TUYẾN ĐƯỜNG PHỔ BIẾN"][/vbc_h2]
    </div>
    <div class="hlhv-grid-3">
      <!-- Route 1 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r1}" alt="Cho Thuê Xe Du Lịch Bắc Nam" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Cho Thuê Xe Du Lịch Bắc Nam | Hoàng Long Hải Vân</h3>
          <p class="hlhv-card-desc">Cung cấp dịch vụ cho thuê xe du lịch đời mới từ 4 đến 45 chỗ, phục vụ hợp đồng tour du lịch, cưới hỏi, công tác an toàn tiện nghi.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Liên hệ báo giá ngay</span>[/vbc_a]
        </div>
      </div>
      <!-- Route 2 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r2}" alt="Thuê Xe Hợp Đồng Bắc Nam" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Thuê Xe Hợp Đồng Bắc Nam | Hoàng Long Hải Vân</h3>
          <p class="hlhv-card-desc">Hợp đồng thuê xe dài hạn cho doanh nghiệp, cơ quan và gia đình với giá ưu đãi, tài xế lịch sự, nhiều năm kinh nghiệm.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Đặt xe hợp đồng</span>[/vbc_a]
        </div>
      </div>
      <!-- Route 3 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r3}" alt="Gửi Xe Máy Hoàng Long Hải Vân" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Gửi Xe Máy Hoàng Long Hải Vân | Vận Chuyển An Toàn</h3>
          <p class="hlhv-card-desc">Nhận vận chuyển xe máy Bắc Nam bao bọc cẩn thận, cam kết không trầy xước, giao nhận nhanh chóng tận bến xe.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Tư vấn gửi xe</span>[/vbc_a]
        </div>
      </div>
      <!-- Route 4 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r4}" alt="Giờ Chạy Xe Hà Nội Sài Gòn" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Giờ Chạy Xe Hà Nội - Sài Gòn | Lịch Trình Liên Tục</h3>
          <p class="hlhv-card-desc">Cập nhật lịch trình xe chạy cố định hàng ngày từ các bến xe lớn, đón trả khách linh hoạt dọc tuyến quốc lộ 1A.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem giờ xe chạy</span>[/vbc_a]
        </div>
      </div>
      <!-- Route 5 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r5}" alt="Nhà Xe Hoàng Long Hà Nội Sài Gòn" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Nhà Xe Hoàng Long Hà Nội Sài Gòn | Đặt Vé Trực Tuyến</h3>
          <p class="hlhv-card-desc">Hệ thống xe khách giường nằm chất lượng cao, phục vụ nước uống, khăn lạnh, wifi miễn phí suốt toàn bộ hành trình.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Đặt vé online</span>[/vbc_a]
        </div>
      </div>
      <!-- Route 6 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_r6}" alt="Xe Khách Hoàng Long Hải Vân Tuyến Bắc Nam" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Xe Khách Hoàng Long Hải Vân: Tuyến Đường &amp; Lưu Ý</h3>
          <p class="hlhv-card-desc">Thông tin chi tiết giá vé, điểm đón trả tại Hà Nội, Đà Nẵng, Nha Trang, Sài Gòn và những lưu ý an toàn khi di chuyển.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem chi tiết</span>[/vbc_a]
        </div>
      </div>
    </div>
  </div>
[/vbc_div]

<!-- SECTION 2: DỊCH VỤ CỦA CHÚNG TÔI -->
[vbc_div custom_css="selector {{ background: #f8fafc; padding: 60px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }}"]
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="DỊCH VỤ CỦA CHÚNG TÔI"][/vbc_h2]
    </div>
    <div class="hlhv-grid-4">
      <!-- Service 1 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_dv1}" alt="Vận chuyển nội địa" />
        <div class="hlhv-card-body text-center" style="text-align: center;">
          <h3 class="hlhv-card-title" style="text-transform: uppercase;">Vận Chuyển Nội Địa</h3>
          <p class="hlhv-card-desc">Dịch vụ vận chuyển hàng hóa Bắc Nam an toàn, nhanh chóng, cước phí cạnh tranh nhất thị trường.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem chi tiết</span>[/vbc_a]
        </div>
      </div>
      <!-- Service 2 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_dv2}" alt="Xe Khách Bắc Nam" />
        <div class="hlhv-card-body text-center" style="text-align: center;">
          <h3 class="hlhv-card-title" style="text-transform: uppercase;">Xe Khách Bắc Nam</h3>
          <p class="hlhv-card-desc">Chuyên tuyến Bắc Nam xe giường nằm cao cấp, tiện nghi sang trọng, chạy liên tục hàng ngày.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem chi tiết</span>[/vbc_a]
        </div>
      </div>
      <!-- Service 3 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_dv3}" alt="Dịch vụ kho bãi" />
        <div class="hlhv-card-body text-center" style="text-align: center;">
          <h3 class="hlhv-card-title" style="text-transform: uppercase;">Dịch Vụ Kho Bãi</h3>
          <p class="hlhv-card-desc">Hệ thống kho bãi lưu trữ hàng hóa an toàn, phân loại chuyên nghiệp tại Hà Nội, Đà Nẵng và TP.HCM.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem chi tiết</span>[/vbc_a]
        </div>
      </div>
      <!-- Service 4 -->
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_dv4}" alt="Dịch vụ thu hộ COD" />
        <div class="hlhv-card-body text-center" style="text-align: center;">
          <h3 class="hlhv-card-title" style="text-transform: uppercase;">Dịch Vụ Thu Hộ (COD)</h3>
          <p class="hlhv-card-desc">Giao nhận hàng hóa và thu tiền tận nơi trên toàn quốc uy tín, đối soát minh bạch, nhanh chóng.</p>
          [vbc_a link_url="tel:0968866855" class="hlhv-card-btn"]<span>Xem chi tiết</span>[/vbc_a]
        </div>
      </div>
    </div>
  </div>
[/vbc_div]

<!-- SECTION 3: VÌ SAO CHỌN HOÀNG LONG HẢI VÂN EXPRESS -->
[vbc_div custom_css="selector {{ padding: 60px 0; }}"]
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="VÌ SAO CHỌN HOÀNG LONG HẢI VÂN EXPRESS"][/vbc_h2]
    </div>
    <div class="hlhv-grid-6">
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w1}" alt="Hỗ trợ 24/7" />
        <h4 class="hlhv-why-title">Hỗ Trợ 24/7</h4>
        <p class="hlhv-why-desc">Tư vấn và đặt xe mọi lúc mọi nơi</p>
      </div>
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w2}" alt="Xe đời mới" />
        <h4 class="hlhv-why-title">Xe Đời Mới</h4>
        <p class="hlhv-why-desc">Đầy đủ tiện nghi, sạch sẽ thoáng mát</p>
      </div>
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w3}" alt="Tài xế kinh nghiệm" />
        <h4 class="hlhv-why-title">Tài Xế Lành Nghề</h4>
        <p class="hlhv-why-desc">Kinh nghiệm lâu năm chạy tuyến Bắc Nam</p>
      </div>
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w4}" alt="Giá rõ ràng" />
        <h4 class="hlhv-why-title">Giá Rõ Ràng</h4>
        <p class="hlhv-why-desc">Báo giá niêm yết, không phát sinh chi phí</p>
      </div>
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w5}" alt="Đón trả linh hoạt" />
        <h4 class="hlhv-why-title">Đón Trả Linh Hoạt</h4>
        <p class="hlhv-why-desc">Đón trả tận nơi tại các điểm hẹn thuận tiện</p>
      </div>
      <div class="hlhv-why-box">
        <img class="hlhv-why-icon" src="{url_w6}" alt="Phục vụ tận tâm" />
        <h4 class="hlhv-why-title">Phục Vụ Tận Tâm</h4>
        <p class="hlhv-why-desc">Xem hành khách như người thân gia đình</p>
      </div>
    </div>
  </div>
[/vbc_div]

<!-- SECTION 4: QUY TRÌNH ĐẶT XE (GRADIENT BANNER) -->
<div class="hlhv-process-sec">
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="QUY TRÌNH ĐẶT XE NHANH CHÓNG 4 BƯỚC"][/vbc_h2]
    </div>
    <div class="hlhv-process-grid">
      <div class="hlhv-process-item">
        <img class="hlhv-process-icon" src="{url_p1}" alt="Bước 1" />
        <div class="hlhv-process-info">
          <h4>1. Liên Hệ Tư Vấn</h4>
          <p>Gọi điện hoặc nhắn Zalo để được tư vấn lộ trình và báo giá miễn phí</p>
        </div>
      </div>
      <div class="hlhv-process-item">
        <img class="hlhv-process-icon" src="{url_p2}" alt="Bước 2" />
        <div class="hlhv-process-info">
          <h4>2. Chọn Xe &amp; Lịch</h4>
          <p>Lựa chọn dòng xe phù hợp (giường nằm, limousine, thuê xe bao trọn)</p>
        </div>
      </div>
      <div class="hlhv-process-item">
        <img class="hlhv-process-icon" src="{url_p3}" alt="Bước 3" />
        <div class="hlhv-process-info">
          <h4>3. Xác Nhận Đặt Xe</h4>
          <p>Chốt thông tin điểm đón, thời gian khởi hành và nhận vé điện tử</p>
        </div>
      </div>
      <div class="hlhv-process-item">
        <img class="hlhv-process-icon" src="{url_p4}" alt="Bước 4" />
        <div class="hlhv-process-info">
          <h4>4. Khởi Hành Đúng Giờ</h4>
          <p>Tài xế liên hệ đón đúng giờ, trải nghiệm chuyến đi an toàn thoải mái</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- SECTION 5: KHÁCH HÀNG NÓI VỀ CHÚNG TÔI -->
[vbc_div custom_css="selector {{ padding: 60px 0; background: #fff; }}"]
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="KHÁCH HÀNG NÓI VỀ CHÚNG TÔI"][/vbc_h2]
    </div>
    <div class="hlhv-grid-3">
      <!-- Testimonial 1 -->
      <div class="hlhv-testi-card">
        <img class="hlhv-testi-avatar" src="{url_kh1}" alt="Anh Trần Minh Đức" />
        <div>
          <div class="hlhv-testi-stars">★★★★★</div>
          <p class="hlhv-testi-text">"Xe sạch sẽ, tiện nghi, tài xế lái rất cẩn thận và đúng giờ. Tôi rất an tâm khi cả gia đình đi tuyến Hà Nội - Đà Nẵng cùng Hoàng Long Hải Vân."</p>
          <h4 class="hlhv-testi-author">Anh Trần Minh Đức</h4>
          <span style="font-size: 12px; color: #888;">Khách hàng tuyến Hà Nội - Đà Nẵng</span>
        </div>
      </div>
      <!-- Testimonial 2 -->
      <div class="hlhv-testi-card">
        <img class="hlhv-testi-avatar" src="{url_kh2}" alt="Chị Nguyễn Hương Thảo" />
        <div>
          <div class="hlhv-testi-stars">★★★★★</div>
          <p class="hlhv-testi-text">"Gửi xe máy và hàng hóa từ Sài Gòn ra Nghệ An rất nhanh, xe nhận được nguyên vẹn không trầy xước. Giá cước rất hợp lý so với các nhà xe khác."</p>
          <h4 class="hlhv-testi-author">Chị Nguyễn Hương Thảo</h4>
          <span style="font-size: 12px; color: #888;">Khách gửi hàng Sài Gòn - Nghệ An</span>
        </div>
      </div>
      <!-- Testimonial 3 -->
      <div class="hlhv-testi-card">
        <img class="hlhv-testi-avatar" src="{url_kh3}" alt="Anh Lê Hoàng Nam" />
        <div>
          <div class="hlhv-testi-stars">★★★★★</div>
          <p class="hlhv-testi-text">"Thuê xe 29 chỗ cho công ty đi du lịch Bắc Nam, xe mới tinh chạy êm ru, bác tài vui tính nhiệt tình hỗ trợ đoàn suốt chuyến đi."</p>
          <h4 class="hlhv-testi-author">Anh Lê Hoàng Nam</h4>
          <span style="font-size: 12px; color: #888;">Doanh nghiệp thuê xe du lịch</span>
        </div>
      </div>
    </div>
  </div>
[/vbc_div]

<!-- SECTION 6: TIN TỨC MỚI NHẤT -->
[vbc_div custom_css="selector {{ padding: 60px 0; background: #f8fafc; border-top: 1px solid #eee; }}"]
  <div class="hlhv-container">
    <div class="hlhv-heading">
      [vbc_h2 content="TIN TỨC &amp; KINH NGHIỆM VẬN CHUYỂN"][/vbc_h2]
    </div>
    <div class="hlhv-grid-4">
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_n1}" alt="7 Lưu Ý Khi Vận Chuyển Vật Liệu Xây Dựng" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">7 Lưu Ý Khi Vận Chuyển Vật Liệu Xây Dựng Bắc Nam</h3>
          <p class="hlhv-card-desc">Kinh nghiệm bảo quản và đóng gói vật liệu xây dựng an toàn trong suốt quá trình trung chuyển đường dài.</p>
          [vbc_a link_url="#" class="hlhv-card-btn"]<span>Đọc tiếp</span>[/vbc_a]
        </div>
      </div>
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_n2}" alt="Vận Chuyển Hàng Cồng Kềnh" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Vận Chuyển Hàng Cồng Kềnh Bắc Nam | Hoàng Long Hải Vân</h3>
          <p class="hlhv-card-desc">Giải pháp vận chuyển hàng quá khổ quá tải, máy móc cơ khí an toàn với đội ngũ xe tải chuyên dụng.</p>
          [vbc_a link_url="#" class="hlhv-card-btn"]<span>Đọc tiếp</span>[/vbc_a]
        </div>
      </div>
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_n3}" alt="Vận Chuyển Hàng Dễ Vỡ" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Vận Chuyển Hàng Dễ Vỡ Bắc Nam | Cam Kết Bồi Thường 100%</h3>
          <p class="hlhv-card-desc">Quy trình đóng bọc xốp bóng khí và thùng gỗ chuyên biệt giúp hàng gốm sứ, kính, thủy tinh an toàn tuyệt đối.</p>
          [vbc_a link_url="#" class="hlhv-card-btn"]<span>Đọc tiếp</span>[/vbc_a]
        </div>
      </div>
      <div class="hlhv-card">
        <img class="hlhv-card-img" src="{url_n4}" alt="Vận Chuyển Hàng Công Nghiệp" />
        <div class="hlhv-card-body">
          <h3 class="hlhv-card-title">Vận Chuyển Hàng Công Nghiệp Bắc Nam Nhanh Chóng</h3>
          <p class="hlhv-card-desc">Dịch vụ vận tải thiết bị công nghiệp liên tỉnh, hỗ trợ bốc xếp và giao nhận tận chân công trình.</p>
          [vbc_a link_url="#" class="hlhv-card-btn"]<span>Đọc tiếp</span>[/vbc_a]
        </div>
      </div>
    </div>
  </div>
[/vbc_div]

<!-- CALL TO ACTION BAR -->
<div class="hlhv-cta-bar">
  <div class="hlhv-container">
    <div class="hlhv-cta-inner">
      <div>
        <h3 class="hlhv-cta-title">GỌI NGAY HOÀNG LONG HẢI VÂN EXPRESS</h3>
        <div class="hlhv-cta-phones" style="margin-top: 6px;">
          <a href="tel:0968866855">📞 0968 . 866 . 855</a>
          <a href="tel:0888055558">📞 0888 . 055 . 558</a>
          <a href="tel:0922256777">📞 0922 . 256 . 777</a>
        </div>
      </div>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        [vbc_a link_url="tel:0968866855" class="hlhv-btn-call" custom_css="selector {{ background: #fff !important; color: #b20000 !important; font-weight: 800; }}"]
          <span>📞 Gọi Ngay 0968.866.855</span>
        [/vbc_a]
        [vbc_a link_url="https://zalo.me/0968866855" link_target="_blank" class="hlhv-btn-zalo" custom_css="selector {{ background: #0068ff !important; color: #fff !important; border: none; }}"]
          <img src="{url_zalo}" alt="Zalo" style="width: 20px; height: 20px; filter: brightness(0) invert(1);" />
          <span>Chat Qua Zalo</span>
        [/vbc_a]
      </div>
    </div>
  </div>
</div>

<!-- FOOTER / CONTACT INFO -->
<div class="hlhv-footer-info">
  <div class="hlhv-container">
    <div class="hlhv-footer-grid">
      <!-- Col 1 -->
      <div class="hlhv-ft-col">
        <img src="{url_logo}" alt="Logo Hoàng Long Hải Vân" style="max-width: 140px; margin-bottom: 12px;" />
        <h3>NHÀ XE HOÀNG LONG HẢI VÂN</h3>
        <p>Thương hiệu vận tải hành khách &amp; hàng hóa Bắc Nam uy tín chất lượng hàng đầu. Cam kết an toàn, đúng giờ và chu đáo.</p>
        <p><strong>Hotline:</strong> 0968.866.855 - 0888.055.558</p>
      </div>
      <!-- Col 2 -->
      <div class="hlhv-ft-col">
        <h3>THÔNG TIN LIÊN HỆ</h3>
        <p><strong>📍 Trụ Sở Hà Nội:</strong> Bến xe Nước Ngầm, Km8 Giải Phóng, Hoàng Mai, Hà Nội.</p>
        <p><strong>Điện thoại:</strong> 0968.866.855</p>
        <p><strong>Giờ làm việc:</strong> Phục vụ 24/7 toàn bộ các ngày trong tuần.</p>
      </div>
      <!-- Col 3 -->
      <div class="hlhv-ft-col">
        <h3>CƠ SỞ 2 - TP.HCM</h3>
        <p><strong>📍 Văn phòng TP.HCM:</strong> Bến xe Miền Đông Mới / Bến xe An Sương, TP. Hồ Chí Minh.</p>
        <p><strong>Điện thoại:</strong> 0888.055.558</p>
        <p><strong>Dịch vụ:</strong> Đón trả khách &amp; nhận gửi hàng liên tỉnh.</p>
      </div>
      <!-- Col 4 -->
      <div class="hlhv-ft-col">
        <h3>CƠ SỞ 3 - THỦ ĐỨC</h3>
        <p><strong>📍 Chi nhánh Thủ Đức:</strong> Ngã tư Bình Phước, TP. Thủ Đức, TP. Hồ Chí Minh.</p>
        <p><strong>Điện thoại:</strong> 0922.256.777</p>
        <p><strong>Dịch vụ:</strong> Kho trung chuyển hàng hóa &amp; bãi đỗ xe du lịch.</p>
      </div>
    </div>
    <div style="text-align: center; padding-top: 25px; margin-top: 25px; border-top: 1px solid #eee; font-size: 13px; color: #888;">
      © 2026 Hoàng Long Hải Vân Express. Bản quyền thuộc về Nhà Xe Hoàng Long Hải Vân.
    </div>
  </div>
</div>

<!-- STICKY FLOATING CONTACT BUTTONS -->
<div class="hlhv-floating-contact">
  <a href="tel:0968866855" class="hlhv-float-btn hlhv-float-phone" title="Gọi điện ngay">
    <span style="font-size: 22px; color: #fff;">📞</span>
  </a>
  <a href="https://zalo.me/0968866855" target="_blank" class="hlhv-float-btn hlhv-float-zalo" title="Chat Zalo">
    <img src="{url_zalo}" alt="Zalo" />
  </a>
</div>

[/vbc_div]
"""

from nesting_sanitizer import sanitize_nesting

# Clean & sanitize shortcode nesting
shortcode = sanitize_nesting(shortcode.strip())

with open('hoanglong_page_content.txt', 'w', encoding='utf-8') as f:
    f.write(shortcode)

print(f"Generated clean shortcode ({len(shortcode)} characters)")

# Publishing to WordPress REST API
print("\nPublishing to WordPress REST API...")
api_url = config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
token = config['token']

post_data = {
    'post_id': 479,
    'title': 'XE KHÁCH BẮC NAM & CHO THUÊ XE DU LỊCH',
    'slug': 'xe-khach-bac-nam-cho-thue-xe-du-lich',
    'content': shortcode,
    'status': 'publish',
    'post_type': 'page'
}

req_data = json.dumps(post_data).encode('utf-8')
page_req = urllib.request.Request(
    f"{api_url}/vbc/v1/page",
    data=req_data,
    headers={
        'Content-Type': 'application/json; charset=utf-8',
        'X-VBC-Token': token
    },
    method='POST'
)

try:
    with urllib.request.urlopen(page_req, timeout=30) as res:
        res_body = res.read().decode('utf-8')
        result = json.loads(res_body)
        print("\n==================================================")
        print("   XUẤT BẢN TRANG XE KHÁCH HOÀNG LONG HẢI VÂN THÀNH CÔNG!")
        print("==================================================")
        print("Post ID:   ", result.get('post_id'))
        print("Action:    ", result.get('action'))
        print("Page URL:  ", result.get('url'))
        print("==================================================\n")
        
        page_url = result.get('url')

        # MANDATORY VERIFICATION STEP
        if page_url:
            print("Checking live frontend for unparsed shortcode tags...")
            req = urllib.request.Request(f"{page_url}?verify={os.urandom(4).hex()}", headers={'User-Agent': 'Mozilla/5.0'})
            live_html = urllib.request.urlopen(req).read().decode('utf-8')
            unparsed = re.findall(r'\[/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]', live_html)
            print(f"Unparsed shortcode count on live page: {len(unparsed)}")
            if len(unparsed) == 0:
                print("✓ PERFECT! 0 unparsed shortcode tags found on frontend.")
            else:
                print("❌ FAILED! Unparsed tags found:", unparsed[:10])

except Exception as e:
    print("Error publishing page:", e)
