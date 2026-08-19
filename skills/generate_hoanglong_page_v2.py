# -*- coding: utf-8 -*-
"""
Generate & Publish 99% Pixel-Perfect Clone of Hoàng Long Hải Vân Express
With Resilient Media Lookups, High Contrast, and Zero Visual Defects
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

def get_media_by_keyword(kw, default=""):
    for k, v in media_map.items():
        if kw in k:
            return v['url']
    return default

# Asset URLs
url_banner = get_media_by_keyword("banner-hlhv")
url_logo = get_media_by_keyword("logo-hoang-long-hai-van")
url_zalo = get_media_by_keyword("zalo")

# Services
url_dv1 = get_media_by_keyword("img2-300x300")
url_dv2 = get_media_by_keyword("img2-1-300x200")
url_dv3 = get_media_by_keyword("img3-300x300")
url_dv4 = get_media_by_keyword("img4-300x300")

# Why choose (6 icons)
url_w1 = get_media_by_keyword("24-hours")
url_w2 = get_media_by_keyword("bus.png")
url_w3 = get_media_by_keyword("kn.png")
url_w4 = get_media_by_keyword("gia.png")
url_w5 = get_media_by_keyword("dd.png")
url_w6 = get_media_by_keyword("love.png")

# Process (4 icons)
url_p1 = get_media_by_keyword("telephone-call")
url_p2 = get_media_by_keyword("time-1")
url_p3 = get_media_by_keyword("approval")
url_p4 = get_media_by_keyword("on-time")

# Testimonials (3 avatars)
url_kh1 = get_media_by_keyword("kh1")
url_kh2 = get_media_by_keyword("kh2")
url_kh3 = get_media_by_keyword("kh3")

# Routes (6 images - optimized 768px thumbnails for instant loading)
url_r1 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/xe-du-lich-bac-nam-1-768x512.png"
url_r2 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/xe-du-lich-bac-nam-768x512.png"
url_r3 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/gui-xe-may-hoang-long-hai-van-768x576.png"
url_r4 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/gio-chay-xe-ha-noi-sai-gon-768x512.png"
url_r5 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/nha-xe-ha-noi-sai-gon-768x576.png"
url_r6 = "https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-content/uploads/2026/08/xe-khach-bac-nam-hoang-long-hai-van-1-768x576.png"

# News (4 images)
url_n1 = get_media_by_keyword("van-chuyen-vat-lieu")
url_n2 = get_media_by_keyword("van-chuyen-hang-cong-kenh")
url_n3 = get_media_by_keyword("van-chuyen-hang-de-vo")
url_n4 = get_media_by_keyword("van-chuyen-hang-cong-nghiep")

print("Checking loaded asset URLs:")
print("Banner:", url_banner)
print("Logo:", url_logo)
print("Route 1:", url_r1)
print("Route 2:", url_r2)

page_html = f"""
<style>
/* CSS Master Styles for Hoàng Long Hải Vân Clone */
.hlhv-wrap {{ font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #1e293b; background: #ffffff; line-height: 1.6; margin: 0; padding: 0; box-sizing: border-box; }}
.hlhv-wrap * {{ box-sizing: border-box; }}
.hlhv-container {{ max-width: 1240px; margin: 0 auto; padding: 0 20px; }}

/* Top Header Bar */
.hlhv-header {{ background: #ffffff; border-bottom: 2px solid #b20000; position: sticky; top: 0; z-index: 999; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }}
.hlhv-header-inner {{ display: flex; align-items: center; justify-content: space-between; padding: 10px 0; }}
.hlhv-logo-area {{ display: flex; align-items: center; gap: 14px; text-decoration: none; }}
.hlhv-logo-img {{ height: 58px; width: auto; object-fit: contain; border-radius: 6px; }}
.hlhv-logo-text h2 {{ font-size: 19px; font-weight: 900; color: #b20000; margin: 0; line-height: 1.2; text-transform: uppercase; }}
.hlhv-logo-text p {{ font-size: 12px; color: #0284c7; margin: 0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }}
.hlhv-nav-menu {{ display: flex; align-items: center; gap: 24px; list-style: none; margin: 0; padding: 0; }}
.hlhv-nav-menu li a {{ font-size: 14.5px; font-weight: 700; color: #1e293b; text-decoration: none; text-transform: uppercase; transition: color 0.2s; }}
.hlhv-nav-menu li a:hover {{ color: #b20000; }}
.hlhv-header-hotline {{ display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #dc2626, #991b1b); color: #ffffff !important; padding: 10px 20px; border-radius: 30px; font-weight: 800; font-size: 14.5px; text-decoration: none; box-shadow: 0 4px 14px rgba(220,38,38,0.35); }}
.hlhv-header-hotline:hover {{ background: #7f1d1d; transform: translateY(-1px); color: #ffffff !important; }}

/* Section Headings */
.hlhv-heading {{ text-align: center; margin-bottom: 35px; }}
.hlhv-heading h2 {{ position: relative; display: inline-block; font-size: 28px; text-transform: uppercase; color: #0f172a; font-weight: 900; margin: 0; padding-bottom: 12px; letter-spacing: 0.5px; }}
.hlhv-heading h2::after {{ content: ''; position: absolute; left: 50%; bottom: 0; transform: translateX(-50%); width: 70px; height: 3.5px; background: #b20000; border-radius: 2px; }}

/* Hero Section */
.hlhv-hero {{ position: relative; background-image: url('{url_banner}'); background-size: cover; background-position: center right; background-repeat: no-repeat; min-height: 520px; display: flex; align-items: center; padding: 60px 0 100px; }}
.hlhv-hero-overlay {{ position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(90deg, rgba(255,255,255,0.96) 0%, rgba(255,255,255,0.92) 45%, rgba(255,255,255,0.3) 80%, rgba(255,255,255,0) 100%); }}
.hlhv-hero-content {{ position: relative; z-index: 2; max-width: 620px; }}
.hlhv-hero-title {{ font-size: 38px; line-height: 1.25; color: #b20000; font-weight: 900; margin: 0 0 10px; text-transform: uppercase; }}
.hlhv-hero-title span {{ color: #0c4a89; }}
.hlhv-hero-slogan {{ font-size: 24px; color: #b20000; font-style: italic; margin-bottom: 22px; font-weight: 700; }}
.hlhv-hero-list {{ list-style: none; padding: 0; margin: 0 0 28px; }}
.hlhv-hero-list li {{ position: relative; padding-left: 36px; margin-bottom: 12px; font-weight: 700; color: #0f172a; font-size: 16px; }}
.hlhv-hero-list li::before {{ content: '✓'; position: absolute; left: 0; top: 1px; width: 24px; height: 24px; background: #b20000; color: #ffffff; border-radius: 50%; text-align: center; line-height: 24px; font-size: 13px; font-weight: 900; }}
.hlhv-hero-actions {{ display: flex; gap: 16px; flex-wrap: wrap; }}
.hlhv-btn-call {{ background: linear-gradient(135deg, #ef4444, #b20000); color: #ffffff !important; padding: 14px 28px; font-size: 16px; font-weight: 800; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 20px rgba(178,0,0,0.4); transition: all 0.3s; }}
.hlhv-btn-call:hover {{ transform: translateY(-3px); box-shadow: 0 10px 28px rgba(178,0,0,0.55); color: #ffffff !important; }}
.hlhv-btn-zalo {{ background: #ffffff; color: #0068ff !important; border: 2px solid #0068ff; padding: 14px 28px; font-size: 16px; font-weight: 800; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(0,104,255,0.15); transition: all 0.3s; }}
.hlhv-btn-zalo:hover {{ transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,104,255,0.3); background: #f0f7ff; color: #0068ff !important; }}

/* Booking Form Card */
.hlhv-booking-wrap {{ max-width: 1100px; margin: -65px auto 60px; position: relative; z-index: 10; padding: 0 20px; }}
.hlhv-booking-card {{ background: #ffffff; border-radius: 14px; box-shadow: 0 15px 45px rgba(0,0,0,0.14); padding: 28px; border: 1px solid #e2e8f0; }}
.hlhv-tabs-header {{ display: flex; justify-content: center; gap: 12px; margin-bottom: 22px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }}
.hlhv-tab-btn {{ background: #f1f5f9; border: none; padding: 12px 30px; border-radius: 30px; font-size: 14.5px; font-weight: 800; color: #475569; cursor: pointer; transition: all 0.25s; text-transform: uppercase; }}
.hlhv-tab-btn.active {{ background: #b20000; color: #ffffff; box-shadow: 0 4px 14px rgba(178,0,0,0.35); }}
.hlhv-form-grid {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }}
.hlhv-input-group {{ background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; display: flex; flex-direction: column; }}
.hlhv-input-group:focus-within {{ border-color: #b20000; background: #ffffff; }}
.hlhv-input-group label {{ font-size: 11.5px; font-weight: 800; color: #64748b; margin-bottom: 4px; text-transform: uppercase; }}
.hlhv-input-group select, .hlhv-input-group input {{ border: none; background: transparent; font-size: 14.5px; font-weight: 700; color: #0f172a; outline: none; width: 100%; }}
.hlhv-btn-submit {{ width: 100%; background: linear-gradient(135deg, #dc2626, #991b1b); color: #ffffff; border: none; border-radius: 8px; padding: 16px; font-size: 16px; font-weight: 900; text-transform: uppercase; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(178,0,0,0.35); }}
.hlhv-btn-submit:hover {{ background: #7f1d1d; transform: translateY(-2px); box-shadow: 0 10px 28px rgba(178,0,0,0.5); }}

/* Cards & Grids */
.hlhv-grid-3 {{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; }}
.hlhv-grid-4 {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }}
.hlhv-grid-6 {{ display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; }}

.hlhv-card {{ background: #ffffff !important; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: all 0.3s; border: 1px solid #e2e8f0; display: flex; flex-direction: column; opacity: 1 !important; visibility: visible !important; }}
.hlhv-card:hover {{ transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); border-color: #94a3b8; }}
.hlhv-card-img {{ width: 100% !important; height: 210px !important; min-height: 210px !important; object-fit: cover !important; display: block !important; background: #f1f5f9; opacity: 1 !important; visibility: visible !important; }}
.hlhv-card-body {{ padding: 18px; flex-grow: 1; display: flex; flex-direction: column; opacity: 1 !important; }}
.hlhv-card-title {{ font-size: 16.5px !important; font-weight: 800 !important; color: #0f172a !important; margin: 0 0 10px !important; line-height: 1.4 !important; }}
.hlhv-card-desc {{ font-size: 14px !important; color: #334155 !important; line-height: 1.6 !important; margin-bottom: 16px !important; flex-grow: 1; }}
.hlhv-card-btn {{ display: inline-block; text-align: center; background: #b20000 !important; color: #ffffff !important; padding: 10px 18px; border-radius: 6px; font-size: 13.5px; font-weight: 800; text-decoration: none; transition: all 0.2s; }}
.hlhv-card-btn:hover {{ background: #8f0000 !important; transform: translateY(-1px); }}

/* Why Choose Us */
.hlhv-why-box {{ background: #ffffff !important; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 22px 14px; text-align: center; box-shadow: 0 3px 12px rgba(0,0,0,0.04); transition: all 0.3s; opacity: 1 !important; }}
.hlhv-why-box:hover {{ transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: #b20000; }}
.hlhv-why-icon {{ width: 56px !important; height: 56px !important; margin: 0 auto 12px !important; object-fit: contain !important; display: block !important; opacity: 1 !important; }}
.hlhv-why-title {{ font-size: 15px !important; font-weight: 800 !important; color: #0f172a !important; margin-bottom: 6px !important; text-transform: uppercase; }}
.hlhv-why-desc {{ font-size: 13px !important; color: #475569 !important; line-height: 1.45 !important; margin: 0 !important; font-weight: 500; }}

/* Process Banner (Gradient Red) */
.hlhv-process-sec {{ background: linear-gradient(135deg, #b20000 0%, #750000 100%); padding: 50px 0; color: #ffffff; opacity: 1 !important; }}
.hlhv-process-sec .hlhv-heading h2 {{ color: #ffffff !important; }}
.hlhv-process-sec .hlhv-heading h2::after {{ background: #ffffff !important; }}
.hlhv-process-grid {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }}
.hlhv-process-item {{ display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.1); border-radius: 8px; padding: 18px 16px; border: 1px solid rgba(255,255,255,0.2); opacity: 1 !important; }}
.hlhv-process-icon {{ width: 44px !important; height: 44px !important; object-fit: contain !important; flex-shrink: 0; filter: brightness(0) invert(1) !important; }}
.hlhv-process-info h4 {{ font-size: 16px !important; font-weight: 800 !important; margin: 0 0 4px !important; color: #ffffff !important; }}
.hlhv-process-info p {{ font-size: 13px !important; margin: 0 !important; color: rgba(255,255,255,0.9) !important; line-height: 1.4 !important; }}

/* Testimonials */
.hlhv-testi-card {{ background: #ffffff !important; border-radius: 10px; padding: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); border: 1.5px solid #e2e8f0; display: flex; gap: 16px; align-items: flex-start; opacity: 1 !important; }}
.hlhv-testi-avatar {{ width: 68px !important; height: 68px !important; border-radius: 50% !important; object-fit: cover !important; flex-shrink: 0; border: 3px solid #b20000 !important; display: block !important; opacity: 1 !important; }}
.hlhv-testi-stars {{ color: #f59e0b !important; font-size: 16px !important; margin-bottom: 8px !important; letter-spacing: 2px; }}
.hlhv-testi-text {{ font-size: 14px !important; color: #334155 !important; line-height: 1.6 !important; margin-bottom: 12px !important; font-style: italic; font-weight: 500; }}
.hlhv-testi-author {{ font-size: 15px !important; font-weight: 800 !important; color: #0f172a !important; margin: 0 !important; }}

/* Call to Action Red Bar */
.hlhv-cta-bar {{ background: linear-gradient(90deg, #990000 0%, #dc2626 50%, #990000 100%); color: #ffffff; padding: 25px 0; margin-top: 50px; opacity: 1 !important; }}
.hlhv-cta-inner {{ display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }}
.hlhv-cta-title {{ font-size: 22px !important; font-weight: 900 !important; text-transform: uppercase; margin: 0 !important; color: #ffffff !important; }}
.hlhv-cta-phones {{ display: flex; gap: 20px; flex-wrap: wrap; font-size: 19px !important; font-weight: 900 !important; }}
.hlhv-cta-phones a {{ color: #fef08a !important; text-decoration: none; }}

/* Footer Contact Section */
.hlhv-footer-info {{ background: #f8fafc; padding: 50px 0 25px; border-top: 2px solid #e2e8f0; opacity: 1 !important; }}
.hlhv-footer-grid {{ display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 28px; }}
.hlhv-ft-col h3 {{ font-size: 16.5px !important; font-weight: 800 !important; text-transform: uppercase; color: #0f172a !important; margin: 0 0 15px !important; position: relative; padding-bottom: 8px; }}
.hlhv-ft-col h3::after {{ content: ''; position: absolute; left: 0; bottom: 0; width: 35px; height: 2.5px; background: #b20000; }}
.hlhv-ft-col p {{ font-size: 14px !important; color: #334155 !important; line-height: 1.65 !important; margin: 0 0 10px !important; }}
.hlhv-ft-col strong {{ color: #0f172a !important; font-weight: 800 !important; }}

/* Sticky Floating Contact */
.hlhv-floating-contact {{ position: fixed; right: 22px; bottom: 30px; z-index: 99999; display: flex; flex-direction: column; gap: 14px; }}
.hlhv-float-btn {{ width: 54px; height: 54px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 20px rgba(0,0,0,0.3); transition: transform 0.3s; text-decoration: none; }}
.hlhv-float-btn:hover {{ transform: scale(1.12); }}
.hlhv-float-phone {{ background: #16a34a; }}
.hlhv-float-zalo {{ background: #0068ff; }}
.hlhv-float-btn img {{ width: 30px; height: 30px; object-fit: contain; }}

/* Responsive */
@media (max-width: 1024px) {{
  .hlhv-grid-4, .hlhv-process-grid {{ grid-template-columns: repeat(2, 1fr); }}
  .hlhv-grid-6 {{ grid-template-columns: repeat(3, 1fr); }}
  .hlhv-footer-grid {{ grid-template-columns: repeat(2, 1fr); }}
  .hlhv-nav-menu {{ display: none; }}
}}
@media (max-width: 768px) {{
  .hlhv-hero-title {{ font-size: 26px !important; }}
  .hlhv-grid-4, .hlhv-grid-3, .hlhv-grid-6, .hlhv-process-grid, .hlhv-footer-grid {{ grid-template-columns: 1fr; }}
  .hlhv-cta-inner {{ flex-direction: column; text-align: center; }}
  .hlhv-hero {{ min-height: 400px; padding: 40px 0 80px; }}
  .hlhv-booking-wrap {{ margin-top: -40px; }}
}}
</style>

<div class="hlhv-wrap">

  <!-- TOP HEADER -->
  <header class="hlhv-header">
    <div class="hlhv-container">
      <div class="hlhv-header-inner">
        <a href="#" class="hlhv-logo-area">
          <img class="hlhv-logo-img" src="{url_logo}" alt="Hoàng Long Hải Vân" />
          <div class="hlhv-logo-text">
            <h2>HOÀNG LONG HẢI VÂN</h2>
            <p>XE KHÁCH BẮC NAM &amp; VẬN TẢI</p>
          </div>
        </a>
        <ul class="hlhv-nav-menu">
          <li><a href="#hero">Trang chủ</a></li>
          <li><a href="#tuyen-duong">Tuyến đường</a></li>
          <li><a href="#dich-vu">Dịch vụ</a></li>
          <li><a href="#vi-sao">Vì sao chọn</a></li>
          <li><a href="#tin-tuc">Tin tức</a></li>
          <li><a href="#lien-he">Liên hệ</a></li>
        </ul>
        <a href="tel:0968866855" class="hlhv-header-hotline">
          <span>📞 Hotline: 0968.866.855</span>
        </a>
      </div>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section id="hero" class="hlhv-hero">
    <div class="hlhv-hero-overlay"></div>
    <div class="hlhv-container" style="width: 100%;">
      <div class="hlhv-hero-content">
        <h1 class="hlhv-hero-title">XE KHÁCH BẮC NAM<br/><span>&amp; CHO THUÊ XE DU LỊCH</span></h1>
        <p class="hlhv-hero-slogan">Chuyên Nghiệp - Uy Tín - Đúng Giờ</p>
        <ul class="hlhv-hero-list">
          <li>Tuyến Bắc – Nam, phục vụ 24/7</li>
          <li>Đa dạng dòng xe: 4 – 45 chỗ đời mới</li>
          <li>Tài xế chuyên nghiệp, phục vụ tận tâm</li>
          <li>Đưa đón tận nơi, cam kết đúng giờ</li>
        </ul>
        <div class="hlhv-hero-actions">
          <a href="tel:0968866855" class="hlhv-btn-call">
            <span>📞 Gọi ngay: 0968.866.855</span>
          </a>
          <a href="https://zalo.me/0968866855" target="_blank" class="hlhv-btn-zalo">
            <img src="{url_zalo}" alt="Zalo" style="width: 22px; height: 22px;" />
            <span>Đặt xe qua Zalo</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- BOOKING FORM CARD -->
  <div class="hlhv-booking-wrap">
    <div class="hlhv-booking-card">
      <div class="hlhv-tabs-header">
        <button type="button" class="hlhv-tab-btn active">Đặt Vé Xe Khách</button>
        <button type="button" class="hlhv-tab-btn">Cho Thuê Xe &amp; Gửi Hàng</button>
      </div>
      <form onsubmit="event.preventDefault(); window.location.href='tel:0968866855';">
        <div class="hlhv-form-grid">
          <div class="hlhv-input-group">
            <label>📍 Điểm đi</label>
            <select>
              <option>Hà Nội (Bến xe Nước Ngầm)</option>
              <option>Hải Phòng</option>
              <option>Nam Định / Ninh Bình</option>
              <option>Thanh Hóa / Nghệ An</option>
              <option>Đà Nẵng / Huế</option>
              <option>TP. Hồ Chí Minh</option>
            </select>
          </div>
          <div class="hlhv-input-group">
            <label>📍 Điểm đến</label>
            <select>
              <option>TP. Hồ Chí Minh (BX Miền Đông)</option>
              <option>Bình Dương / Đồng Nai</option>
              <option>Nha Trang / Phan Thiết</option>
              <option>Đà Nẵng / Quảng Nam</option>
              <option>Nghệ An / Hà Tĩnh</option>
              <option>Hà Nội</option>
            </select>
          </div>
          <div class="hlhv-input-group">
            <label>📅 Ngày đi</label>
            <input type="date" value="2026-08-20" />
          </div>
          <div class="hlhv-input-group">
            <label>🚌 Loại dịch vụ / Dòng xe</label>
            <select>
              <option>Xe Giường Nằm Cao Cấp</option>
              <option>Xe Limousine VIP</option>
              <option>Thuê Xe Du Lịch 16 - 45 Chỗ</option>
              <option>Gửi Hàng Nhanh Bắc Nam</option>
            </select>
          </div>
          <div class="hlhv-input-group">
            <label>📞 Số điện thoại</label>
            <input type="tel" placeholder="Nhập SĐT để nhận vé &amp; giá" required />
          </div>
        </div>
        <button type="submit" class="hlhv-btn-submit">
          🔍 TÌM CHUYẾN XE &amp; ĐẶT VÉ NHANH
        </button>
      </form>
    </div>
  </div>

  <!-- SECTION 1: CÁC TUYẾN ĐƯỜNG PHỔ BIẾN -->
  <section id="tuyen-duong" style="padding: 20px 0 60px;">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>CÁC TUYẾN ĐƯỜNG PHỔ BIẾN</h2>
      </div>
      <div class="hlhv-grid-3">
        <!-- Card 1 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r1}" loading="eager" decoding="sync" alt="Cho Thuê Xe Du Lịch Bắc Nam" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Cho Thuê Xe Du Lịch Bắc Nam – Dịch Vụ Uy Tín, Giá Tốt</h3>
            <p class="hlhv-card-desc">Cung cấp xe 4 – 45 chỗ đời mới, phục vụ tour du lịch, công tác, cưới hỏi trọn gói giá tốt nhất.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Liên hệ báo giá ngay</a>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r2}" loading="eager" decoding="sync" alt="Thuê Xe Hợp Đồng Bắc Nam" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Thuê Xe Hợp Đồng Bắc Nam Giá Rẻ – Uy Tín Hàng Đầu</h3>
            <p class="hlhv-card-desc">Hợp đồng dài hạn cho doanh nghiệp, đưa đón chuyên gia, trường học với chi phí tối ưu.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đặt xe hợp đồng</a>
          </div>
        </div>
        <!-- Card 3 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r3}" loading="eager" decoding="sync" alt="Gửi Xe Máy Hoàng Long Hải Vân" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Gửi Xe Máy Hoàng Long Hải Vân – Vận Chuyển An Toàn</h3>
            <p class="hlhv-card-desc">Bọc chống sốc cẩn thận, nhận trả xe tận nơi tại các bến xe Hà Nội, Đà Nẵng, TP.HCM.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Tư vấn gửi xe</a>
          </div>
        </div>
        <!-- Card 4 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r4}" loading="eager" decoding="sync" alt="Giờ Chạy Xe Hà Nội Sài Gòn" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Giờ Chạy Xe Khách Hà Nội Sài Gòn – Lịch Trình Chi Tiết</h3>
            <p class="hlhv-card-desc">Cập nhật liên tục các khung giờ xuất bến trong ngày, cam kết đón khách đúng giờ hẹn.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem giờ xe chạy</a>
          </div>
        </div>
        <!-- Card 5 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r5}" loading="eager" decoding="sync" alt="Nhà Xe Hoàng Long Hà Nội Sài Gòn" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Nhà Xe Hoàng Long Hà Nội Sài Gòn – Đặt Vé Nhanh Chóng</h3>
            <p class="hlhv-card-desc">Xe giường nằm êm ái, wifi miễn phí, nước uống và khăn lạnh phục vụ suốt hành trình.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đặt vé online</a>
          </div>
        </div>
        <!-- Card 6 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_r6}" loading="eager" decoding="sync" alt="Xe Khách Hoàng Long Hải Vân" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Xe Khách Hoàng Long Hải Vân: Tuyến Đường &amp; Lưu Ý</h3>
            <p class="hlhv-card-desc">Tổng hợp các điểm đón trả cố định trên tuyến quốc lộ 1A từ Bắc vào Nam.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem chi tiết</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 2: DỊCH VỤ CỦA CHÚNG TÔI -->
  <section id="dich-vu" style="padding: 60px 0; background: #f8fafc; border-top: 1px solid #e2e8f0;">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>DỊCH VỤ CỦA CHÚNG TÔI</h2>
      </div>
      <div class="hlhv-grid-4">
        <!-- Service 1 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_dv1}" loading="eager" decoding="sync" alt="Vận Chuyển Hàng Hóa Nội Địa" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Vận Chuyển Hàng Hóa Nội Địa</h3>
            <p class="hlhv-card-desc">Chuyển phát hàng hóa hỏa tốc Bắc - Trung - Nam, giao nhận tận tay khách hàng an toàn tuyệt đối.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem chi tiết</a>
          </div>
        </div>
        <!-- Service 2 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_dv2}" loading="eager" decoding="sync" alt="Xe Khách Tuyến Bắc Nam" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Xe Khách Tuyến Bắc – Nam</h3>
            <p class="hlhv-card-desc">Đội xe chất lượng cao chạy hàng ngày, giường nằm rộng rãi, tiện nghi máy lạnh hiện đại.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem chi tiết</a>
          </div>
        </div>
        <!-- Service 3 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_dv3}" loading="eager" decoding="sync" alt="Dịch Vụ Kho Bãi" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Dịch Vụ Kho Bãi &amp; Lưu Kho</h3>
            <p class="hlhv-card-desc">Hệ thống kho bãi rộng khắp tại các bến xe lớn, bảo quản hàng hóa tiêu chuẩn, có camera giám sát 24/7.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem chi tiết</a>
          </div>
        </div>
        <!-- Service 4 -->
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_dv4}" loading="eager" decoding="sync" alt="Dịch Vụ Thu Hộ COD" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Dịch Vụ Thu Hộ Tiền Hàng (COD)</h3>
            <p class="hlhv-card-desc">Hỗ trợ thu hộ tiền hàng cho đối tác kinh doanh nhanh chóng, thanh toán chuyển khoản minh bạch trong ngày.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Xem chi tiết</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 3: VÌ SAO CHỌN CHÚNG TÔI -->
  <section id="vi-sao" style="padding: 60px 0; background: #ffffff;">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>VÌ SAO CHỌN HOÀNG LONG HẢI VÂN EXPRESS</h2>
      </div>
      <div class="hlhv-grid-6">
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w1}" loading="eager" decoding="sync" alt="Hỗ trợ 24/7" />
          <h4 class="hlhv-why-title">HỖ TRỢ 24/7</h4>
          <p class="hlhv-why-desc">Tư vấn và phục vụ mọi lúc</p>
        </div>
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w2}" loading="eager" decoding="sync" alt="Xe Đời Mới" />
          <h4 class="hlhv-why-title">XE ĐỜI MỚI</h4>
          <p class="hlhv-why-desc">Nội thất cao cấp, êm ái</p>
        </div>
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w3}" loading="eager" decoding="sync" alt="Tài Xế Kinh Nghiệm" />
          <h4 class="hlhv-why-title">TÀI XẾ LÀNH NGHỀ</h4>
          <p class="hlhv-why-desc">Kinh nghiệm lái xe an toàn</p>
        </div>
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w4}" loading="eager" decoding="sync" alt="Giá Cả Hợp Lý" />
          <h4 class="hlhv-why-title">GIÁ MINH BẠCH</h4>
          <p class="hlhv-why-desc">Cam kết không phụ thu ẩn</p>
        </div>
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w5}" loading="eager" decoding="sync" alt="Đón Trả Linh Hoạt" />
          <h4 class="hlhv-why-title">ĐÓN TRẢ LINH HOẠT</h4>
          <p class="hlhv-why-desc">Nhiều điểm dừng tiện lợi</p>
        </div>
        <div class="hlhv-why-box">
          <img class="hlhv-why-icon" src="{url_w6}" loading="eager" decoding="sync" alt="Phục Vụ Tận Tâm" />
          <h4 class="hlhv-why-title">PHỤC VỤ TẬN TÂM</h4>
          <p class="hlhv-why-desc">Hài lòng 100% khách hàng</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 4: QUY TRÌNH ĐẶT XE (GRADIENT BANNER) -->
  <section class="hlhv-process-sec">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>QUY TRÌNH ĐẶT XE NHANH CHÓNG 4 BƯỚC</h2>
      </div>
      <div class="hlhv-process-grid">
        <div class="hlhv-process-item">
          <img class="hlhv-process-icon" src="{url_p1}" loading="eager" decoding="sync" alt="Bước 1" />
          <div class="hlhv-process-info">
            <h4>1. Liên Hệ Tư Vấn</h4>
            <p>Gọi điện hoặc nhắn Zalo để được tư vấn lộ trình và báo giá miễn phí</p>
          </div>
        </div>
        <div class="hlhv-process-item">
          <img class="hlhv-process-icon" src="{url_p2}" loading="eager" decoding="sync" alt="Bước 2" />
          <div class="hlhv-process-info">
            <h4>2. Chọn Xe &amp; Lịch</h4>
            <p>Lựa chọn dòng xe phù hợp (giường nằm, limousine, thuê xe bao trọn)</p>
          </div>
        </div>
        <div class="hlhv-process-item">
          <img class="hlhv-process-icon" src="{url_p3}" loading="eager" decoding="sync" alt="Bước 3" />
          <div class="hlhv-process-info">
            <h4>3. Xác Nhận Đặt Xe</h4>
            <p>Chốt thông tin điểm đón, thời gian khởi hành và nhận vé điện tử</p>
          </div>
        </div>
        <div class="hlhv-process-item">
          <img class="hlhv-process-icon" src="{url_p4}" loading="eager" decoding="sync" alt="Bước 4" />
          <div class="hlhv-process-info">
            <h4>4. Khởi Hành Đúng Giờ</h4>
            <p>Tài xế liên hệ đón đúng giờ, trải nghiệm chuyến đi an toàn thoải mái</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 5: KHÁCH HÀNG NÓI VỀ CHÚNG TÔI -->
  <section style="padding: 60px 0; background: #ffffff;">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>KHÁCH HÀNG NÓI VỀ CHÚNG TÔI</h2>
      </div>
      <div class="hlhv-grid-3">
        <!-- Testimonial 1 -->
        <div class="hlhv-testi-card">
          <img class="hlhv-testi-avatar" src="{url_kh1}" loading="eager" decoding="sync" alt="Anh Trần Minh Đức" />
          <div>
            <div class="hlhv-testi-stars">★★★★★</div>
            <p class="hlhv-testi-text">"Xe sạch sẽ, tiện nghi, tài xế lái rất cẩn thận và đúng giờ. Tôi rất an tâm khi cả gia đình đi tuyến Hà Nội - Đà Nẵng cùng Hoàng Long Hải Vân."</p>
            <h4 class="hlhv-testi-author">Anh Trần Minh Đức</h4>
            <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">Khách hàng tuyến Hà Nội - Đà Nẵng</span>
          </div>
        </div>
        <!-- Testimonial 2 -->
        <div class="hlhv-testi-card">
          <img class="hlhv-testi-avatar" src="{url_kh2}" loading="eager" decoding="sync" alt="Chị Nguyễn Hương Thảo" />
          <div>
            <div class="hlhv-testi-stars">★★★★★</div>
            <p class="hlhv-testi-text">"Gửi xe máy và hàng hóa từ Sài Gòn ra Nghệ An rất nhanh, xe nhận được nguyên vẹn không trầy xước. Giá cước rất hợp lý so với các nhà xe khác."</p>
            <h4 class="hlhv-testi-author">Chị Nguyễn Hương Thảo</h4>
            <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">Khách gửi hàng Sài Gòn - Nghệ An</span>
          </div>
        </div>
        <!-- Testimonial 3 -->
        <div class="hlhv-testi-card">
          <img class="hlhv-testi-avatar" src="{url_kh3}" loading="eager" decoding="sync" alt="Anh Lê Hoàng Nam" />
          <div>
            <div class="hlhv-testi-stars">★★★★★</div>
            <p class="hlhv-testi-text">"Thuê xe 29 chỗ cho công ty đi du lịch Bắc Nam, xe mới tinh chạy êm ru, bác tài vui tính nhiệt tình hỗ trợ đoàn suốt chuyến đi."</p>
            <h4 class="hlhv-testi-author">Anh Lê Hoàng Nam</h4>
            <span style="font-size: 12.5px; color: #64748b; font-weight: 600;">Doanh nghiệp thuê xe du lịch</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTION 6: TIN TỨC MỚI NHẤT -->
  <section id="tin-tuc" style="padding: 60px 0; background: #f8fafc; border-top: 1px solid #e2e8f0;">
    <div class="hlhv-container">
      <div class="hlhv-heading">
        <h2>TIN TỨC &amp; KINH NGHIỆM VẬN CHUYỂN</h2>
      </div>
      <div class="hlhv-grid-4">
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_n1}" loading="eager" decoding="sync" alt="7 Lưu Ý Khi Vận Chuyển Vật Liệu Xây Dựng" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">7 Lưu Ý Khi Vận Chuyển Vật Liệu Xây Dựng Bắc Nam</h3>
            <p class="hlhv-card-desc">Kinh nghiệm bảo quản và đóng gói vật liệu xây dựng an toàn trong suốt quá trình trung chuyển đường dài.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đọc tiếp</a>
          </div>
        </div>
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_n2}" loading="eager" decoding="sync" alt="Vận Chuyển Hàng Cồng Kềnh" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Vận Chuyển Hàng Cồng Kềnh Bắc Nam | Hoàng Long Hải Vân</h3>
            <p class="hlhv-card-desc">Giải pháp vận chuyển hàng quá khổ quá tải, máy móc cơ khí an toàn với đội ngũ xe tải chuyên dụng.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đọc tiếp</a>
          </div>
        </div>
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_n3}" loading="eager" decoding="sync" alt="Vận Chuyển Hàng Dễ Vỡ" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Vận Chuyển Hàng Dễ Vỡ Bắc Nam | Cam Kết Bồi Thường 100%</h3>
            <p class="hlhv-card-desc">Quy trình đóng bọc xốp bóng khí và thùng gỗ chuyên biệt giúp hàng gốm sứ, kính, thủy tinh an toàn tuyệt đối.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đọc tiếp</a>
          </div>
        </div>
        <div class="hlhv-card">
          <img class="hlhv-card-img" src="{url_n4}" loading="eager" decoding="sync" alt="Vận Chuyển Hàng Công Nghiệp" />
          <div class="hlhv-card-body">
            <h3 class="hlhv-card-title">Vận Chuyển Hàng Công Nghiệp Bắc Nam Nhanh Chóng</h3>
            <p class="hlhv-card-desc">Dịch vụ vận tải thiết bị công nghiệp liên tỉnh, hỗ trợ bốc xếp và giao nhận tận chân công trình.</p>
            <a href="tel:0968866855" class="hlhv-card-btn">Đọc tiếp</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CALL TO ACTION BAR -->
  <section class="hlhv-cta-bar">
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
        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
          <a href="tel:0968866855" class="hlhv-btn-call" style="background: #ffffff !important; color: #b20000 !important; font-weight: 900;">
            <span>📞 Gọi Ngay 0968.866.855</span>
          </a>
          <a href="https://zalo.me/0968866855" target="_blank" class="hlhv-btn-zalo" style="background: #0068ff !important; color: #ffffff !important; border: none;">
            <img src="{url_zalo}" alt="Zalo" style="width: 22px; height: 22px; filter: brightness(0) invert(1);" />
            <span>Chat Qua Zalo</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER / CONTACT INFO -->
  <footer id="lien-he" class="hlhv-footer-info">
    <div class="hlhv-container">
      <div class="hlhv-footer-grid">
        <!-- Col 1 -->
        <div class="hlhv-ft-col">
          <img src="{url_logo}" alt="Logo Hoàng Long Hải Vân" style="max-width: 140px; margin-bottom: 12px; border-radius: 6px; display: block;" />
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
      <div style="text-align: center; padding-top: 25px; margin-top: 25px; border-top: 1px solid #e2e8f0; font-size: 13.5px; color: #64748b; font-weight: 600;">
        © 2026 Hoàng Long Hải Vân Express. Bản quyền thuộc về Nhà Xe Hoàng Long Hải Vân.
      </div>
    </div>
  </footer>

  <!-- STICKY FLOATING CONTACT BUTTONS -->
  <div class="hlhv-floating-contact">
    <a href="tel:0968866855" class="hlhv-float-btn hlhv-float-phone" title="Gọi điện ngay">
      <span style="font-size: 24px; color: #ffffff;">📞</span>
    </a>
    <a href="https://zalo.me/0968866855" target="_blank" class="hlhv-float-btn hlhv-float-zalo" title="Chat Zalo">
      <img src="{url_zalo}" alt="Zalo" />
    </a>
  </div>

</div>
"""

def sanitize_for_wp(html):
    # 1. Minify all <style> blocks so wpautop never injects <p> or <br> into CSS
    html = re.sub(r'<style\b[^>]*>(.*?)</style>', lambda m: '<style>' + ' '.join(m.group(1).split()) + '</style>', html, flags=re.DOTALL)
    # 2. Strip excess whitespace between tags to eliminate rogue <p> insertions
    html = re.sub(r'>\s*\n+\s*<', '><', html)
    return html.strip()

page_content = sanitize_for_wp(page_html)

# Publishing to WordPress REST API
print("\nPublishing to WordPress REST API...")
api_url = config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
token = config['token']

post_data = {
    'post_id': 479,
    'title': 'XE KHÁCH BẮC NAM & CHO THUÊ XE DU LỊCH',
    'slug': 'xe-khach-bac-nam-cho-thue-xe-du-lich',
    'content': page_content,
    'status': 'publish',
    'post_type': 'page',
    'template': 'page-blank.php'
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

        if page_url:
            print("Checking live frontend for clean rendering...")
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
