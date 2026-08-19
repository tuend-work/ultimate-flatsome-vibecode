# -*- coding: utf-8 -*-
import sys
import os
import json
import re
import urllib.request
import urllib.parse

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

def load_config():
    with open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8') as f:
        return json.load(f)

with open('madamehuong_assets_mapping.json', 'r', encoding='utf-8') as f:
    assets = json.load(f)

def get_asset(original_url):
    data = assets.get(original_url, {})
    return data.get('url', original_url), data.get('id', 0)

# Extract asset URLs
logo_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/logo-70x70.png")
hero_slide_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/slide-1036x800.png")
aodai_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/slide-1036x404w.png")
center_bg_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/background-5-626x417.jpg")
bao_tro_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/bao-tro-600x170.jpg")
phone_icon_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/catalog/images/phone.png")
zalo_icon_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/catalog/images/zalo.png")

nguyen_lieu_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/nguyen-lieu-300x300.jpg")
sang_trong_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/sang-trong-300x300.jpg")
hong_kong_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/hong-kong-300x300.jpg")
lua_chon_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/lua-chon-uy-tin-300x300.jpg")

an_nhien_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/an-nhien-300x300.jpg")
le_na_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/le-na-300x300.jpg")
yen_nhi_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/yen-nhi-300x300.jpg")
manh_tu_url, _ = get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/images/manh-tu-300x300.jpg")

# 14 Original Products
products_data = [
    {
        "name": "Phan Đình Phùng Phố",
        "spec": "Hộp 6 bánh Vip x 120G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "810.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/phan-dinh-phung-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Đậu Phố",
        "spec": "Hộp 6 bánh x 80G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "520.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-dau-pho-1000x883.jpg")[0]
    },
    {
        "name": "Đồng Xuân 2",
        "spec": "Hộp 4 bánh x 120G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Đậu Xanh, Bánh nướng hương Cốm...",
        "price": "390.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-2-1000x883.jpg")[0]
    },
    {
        "name": "Lý Thường Kiệt Phố",
        "spec": "Hộp 6 bánh Vip x 120G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "810.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/ly-thuong-kiet-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Thiếc Phố",
        "spec": "Hộp 6 bánh x 80G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "500.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-thiec-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Khay Phố",
        "spec": "Hộp 9 bánh x 80G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "1.000.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-khay-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Bài Phố",
        "spec": "Hộp 2 bánh x 120G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Bánh nướng nhân Đậu Xanh thơm ngon hảo hạng...",
        "price": "250.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-bai-pho-1000x883.jpg")[0]
    },
    {
        "name": "Lê Thánh Tông Phố",
        "spec": "Hộp 5 bánh x 120G + 1 Chai Rượu Vang",
        "desc": "Bánh nướng Táo Đỏ - Óc Chó, Sen nhuyễn, Trà Xanh, Đậu Xanh, Hạt Dẻ kèm vang thượng hạng...",
        "price": "1.650.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/le-thanh-tong-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hộp Vip 1",
        "spec": "Hộp 5 bánh x 120G + 1 Chai Rượu Vang Thượng Hạng",
        "desc": "Bánh nướng Táo Đỏ - Óc Chó, Sen nhuyễn, Trà Xanh, Đậu Xanh, Hạt Dẻ kèm vang cao cấp...",
        "price": "1.999.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hop-vip-1-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Đường Phố",
        "spec": "Hộp 6 bánh x 80G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "570.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-duong-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Đào Phố",
        "spec": "Hộp 6 bánh x 80G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Cốm, Hạt Dẻ, Táo Đỏ - Hạt Óc Chó, Đậu Xanh...",
        "price": "570.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-dao-pho-1000x883.jpg")[0]
    },
    {
        "name": "Nguyễn Du Phố",
        "spec": "Hộp 4 bánh Vip x 120G",
        "desc": "Bánh nướng nhân Táo Đỏ - Hạt Óc Chó, Sen nhuyễn, Trà Xanh, Đậu Xanh...",
        "price": "690.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/nguyen-du-pho-1000x883.jpg")[0]
    },
    {
        "name": "Hộp Vip 2",
        "spec": "Hộp 5 bánh x 120G + 1 Chai Rượu Vang",
        "desc": "Bánh nướng Táo Đỏ - Óc Chó, Sen nhuyễn, Trà Xanh, Đậu Xanh, Hạt Dẻ kèm vang cao cấp...",
        "price": "1.999.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hop-vip-2-1000x883.jpg")[0]
    },
    {
        "name": "Hàng Mã Phố",
        "spec": "Hộp 4 bánh x 120G",
        "desc": "Bánh nướng nhân Sen nhuyễn, Trà Xanh, Đậu Xanh, Bánh nướng hương Cốm...",
        "price": "300.000 đ",
        "img": get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-ma-pho-1000x883.jpg")[0]
    }
]

# Gallery images
gallery_images = [
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/lava-trung-chay-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-1-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/dong-xuan-3-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-bo-pho-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-can-pho-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-cot-pho-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hang-gai-pho-1000x883.jpg")[0],
    get_asset("https://www.banhtrungthu-madamehuong.vn/image/cache/catalog/banh-trung-thu/hop-vip-1-1000x883.jpg")[0]
]

# Generate product cards using alternating vbc_box & vbc_div to prevent shortcode collision
product_cards_shortcode = ""
for p in products_data:
    card = f"""
[col span="6" span__sm="12" span__md="6"]
[vbc_box custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 18px; margin-bottom: 20px; transition: all 0.35s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: row; gap: 16px; align-items: center; }} selector:hover {{ transform: translateY(-4px); box-shadow: 0 12px 30px rgba(184, 134, 11, 0.15); border-color: #d4a853; }} @media(max-width: 550px) {{ selector {{ flex-direction: column; text-align: center; }} }}"]
    [vbc_div custom_css="selector {{ width: 140px; min-width: 140px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }} @media(max-width: 550px) {{ selector {{ width: 100%; min-width: 100%; }} }}"]
        [vbc_img src="{p['img']}" width="100%" alt="{p['name']}" custom_css="selector {{ width: 100%; height: auto; display: block; object-fit: cover; transition: transform 0.4s ease; }} selector:hover {{ transform: scale(1.05); }}"]
    [/vbc_div]
    [vbc_block custom_css="selector {{ flex: 1; display: flex; flex-direction: column; justify-content: space-between; }}"]
        [vbc_container]
            [vbc_h4 custom_css="selector {{ margin: 0 0 4px 0; font-family: 'Playfair Display', Georgia, serif; font-size: 19px; color: #02302e; font-weight: 700; line-height: 1.3; }}"]{p['name']}[/vbc_h4]
            [vbc_span custom_css="selector {{ display: inline-block; background: #fdf6ea; color: #b8860b; font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 20px; margin-bottom: 8px; border: 1px solid #ebd9b8; }}"]{p['spec']}[/vbc_span]
            [vbc_p custom_css="selector {{ margin: 0 0 12px 0; font-size: 13px; color: #666666; line-height: 1.45; }}"]{p['desc']}[/vbc_p]
        [/vbc_container]
        [vbc_container custom_css="selector {{ display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 4px; }}"]
            [vbc_span custom_css="selector {{ font-size: 18px; font-weight: 800; color: #c0392b; font-family: 'Playfair Display', serif; }}"]{p['price']}[/vbc_span]
            [vbc_a href="#lien-he" custom_css="selector {{ display: inline-flex; align-items: center; justify-content: center; background: #02302e; color: #f6c358 !important; padding: 7px 16px; border-radius: 25px; font-size: 13px; font-weight: 700; text-decoration: none; border: 1px solid #d4a853; transition: all 0.3s ease; }} selector:hover {{ background: #d4a853; color: #02302e !important; transform: translateY(-2px); }}"]Đặt Mua Ngay[/vbc_a]
        [/vbc_container]
    [/vbc_block]
[/vbc_box]
[/col]
"""
    product_cards_shortcode += card

# Gallery shortcode
gallery_shortcode = ""
for img_url in gallery_images:
    gallery_shortcode += f"""
[col span="3" span__sm="6" span__md="3"]
    [vbc_box custom_css="selector {{ border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #ebd9b8; background: #ffffff; transition: all 0.35s ease; }} selector:hover {{ transform: scale(1.03); box-shadow: 0 10px 25px rgba(0,0,0,0.12); border-color: #d4a853; }}"]
        [vbc_img src="{img_url}" width="100%" alt="Vị Bánh Madame Hương" custom_css="selector {{ width: 100%; height: 210px; object-fit: cover; display: block; transition: transform 0.5s ease; }} selector:hover {{ transform: scale(1.08); }}"]
    [/vbc_box]
[/col]
"""

# Header & Full Page Shortcode with global CSS overrides and aliases
full_shortcode = f"""
<style>
/* Ẩn Header & Footer mặc định của Flatsome trên trang Landing Page */
#header, #footer, #top-bar, .header-wrapper, .footer-wrapper {{ display: none !important; }}
html, body {{ background-color: #fdfbf7 !important; margin: 0 !important; padding: 0 !important; font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; scroll-behavior: smooth; }}
#main {{ padding-top: 0 !important; padding-bottom: 0 !important; }}
#wrapper {{ max-width: 100% !important; width: 100% !important; overflow-x: hidden !important; }}
.content-area {{ margin-bottom: 0 !important; padding-bottom: 0 !important; }}
</style>

[section bg_color="#012b28" padding="12px" custom_css="selector {{ border-bottom: 1px solid rgba(212, 168, 83, 0.25); position: sticky; top: 0; z-index: 999; backdrop-filter: blur(10px); }}"]
[row style="collapse" width="custom" custom_width="1200px" v_align="middle"]
    [col span="3" span__sm="6"]
        [vbc_box custom_css="selector {{ display: flex; align-items: center; gap: 10px; }}"]
            [vbc_img src="{logo_url}" width="48px" alt="Madame Hương Logo" custom_css="selector {{ width: 48px; height: auto; display: block; }}"]
            [vbc_div]
                [vbc_span custom_css="selector {{ display: block; font-family: 'Playfair Display', Georgia, serif; font-size: 16px; font-weight: 700; color: #f6c358; letter-spacing: 1px; }}"]MADAME HƯƠNG[/vbc_span]
                [vbc_span custom_css="selector {{ display: block; font-size: 10px; color: rgba(255,255,255,0.7); letter-spacing: 2px; text-transform: uppercase; }}"]Mooncake Heritage[/vbc_span]
            [/vbc_div]
        [/vbc_box]
    [/col]
    [col span="6" hide_for="medium" align="center"]
        [vbc_box custom_css="selector {{ display: flex; justify-content: center; gap: 24px; align-items: center; }}"]
            [vbc_a href="#gioi-thieu" custom_css="selector {{ color: #ffffff !important; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]Giới Thiệu[/vbc_a]
            [vbc_a href="#cam-ket" custom_css="selector {{ color: #ffffff !important; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]Cam Kết[/vbc_a]
            [vbc_a href="#bo-suu-tap" custom_css="selector {{ color: #ffffff !important; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]Bộ Sưu Tập 2026[/vbc_a]
            [vbc_a href="#danh-gia" custom_css="selector {{ color: #ffffff !important; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]Đánh Giá[/vbc_a]
            [vbc_a href="#lien-he" custom_css="selector {{ color: #ffffff !important; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]Ưu Đãi & Báo Giá[/vbc_a]
        [/vbc_box]
    [/col]
    [col span="3" span__sm="6" align="right"]
        [vbc_a href="tel:0785917777" custom_css="selector {{ display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f6c358, #df9b23); color: #012b28 !important; font-weight: 800; font-size: 13px; padding: 8px 16px; border-radius: 25px; text-decoration: none; box-shadow: 0 4px 15px rgba(246, 195, 88, 0.35); transition: all 0.3s; }} selector:hover {{ transform: translateY(-2px); box-shadow: 0 6px 20px rgba(246, 195, 88, 0.5); }}"]
            [vbc_icon icon_type="lucide" name="phone-call" size="15px" color="#012b28"]
            078.591.7777
        [/vbc_a]
    [/col]
[/row]
[/section]

[section bg_color="#012b28" padding="60px 0 50px 0" custom_css="selector {{ background: radial-gradient(circle at 75% 45%, #034842 0%, #012825 65%, #011b19 100%); position: relative; overflow: hidden; }} selector::before {{ content: '✦'; position: absolute; top: 12%; left: 8%; color: rgba(246, 195, 88, 0.4); font-size: 24px; animation: floatSparkle 4s ease-in-out infinite; }} @keyframes floatSparkle {{ 0%, 100% {{ transform: translateY(0) rotate(0deg); opacity: 0.3; }} 50% {{ transform: translateY(-10px) rotate(45deg); opacity: 0.8; }} }}"]
[row width="custom" custom_width="1200px" v_align="middle"]
    [col span="6" span__sm="12"]
        [vbc_box custom_css="selector {{ display: inline-block; background: rgba(246, 195, 88, 0.12); border: 1px solid rgba(246, 195, 88, 0.35); padding: 6px 16px; border-radius: 30px; margin-bottom: 18px; }}"]
            [vbc_span custom_css="selector {{ color: #f6c358; font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }}"]✦ TINH HOA BÁNH TRUNG THU HÀ THÀNH[/vbc_span]
        [/vbc_box]
        [vbc_h1 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 46px; line-height: 1.15; color: #ffffff; font-weight: 700; margin-bottom: 16px; }} @media(max-width: 768px) {{ selector {{ font-size: 32px; }} }}"]Bánh trung thu<br><span style="color: #f6c358; font-style: italic;">Madame Hương</span>[/vbc_h1]
        [vbc_h3 custom_css="selector {{ font-size: 20px; color: #e6dfcf; font-weight: 500; margin-bottom: 16px; letter-spacing: 0.5px; }}"]Dòng Sản Phẩm Cao Cấp Cho Doanh Nghiệp[/vbc_h3]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #b7c7c3; line-height: 1.7; margin-bottom: 28px; max-width: 520px; }}"]Khám phá bộ sưu tập bánh trung thu cao cấp, món quà ý nghĩa để tri ân sâu sắc với nhân viên & quý đối tác. Đẳng cấp thương hiệu hơn 30 năm lưu giữ trọn vẹn phong vị Hà Nội xưa và nay.[/vbc_p]
        [vbc_block custom_css="selector {{ display: flex; gap: 16px; flex-wrap: wrap; }}"]
            [vbc_a href="#bo-suu-tap" custom_css="selector {{ display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: linear-gradient(135deg, #f6c358, #df9b23); color: #012b28 !important; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 30px; text-decoration: none; box-shadow: 0 8px 25px rgba(246, 195, 88, 0.35); transition: all 0.3s ease; }} selector:hover {{ transform: translateY(-3px); box-shadow: 0 12px 30px rgba(246, 195, 88, 0.5); }}"]
                XEM BỘ SƯU TẬP
                [vbc_icon icon_type="lucide" name="arrow-right" size="18px" color="#012b28"]
            [/vbc_a]
            [vbc_a href="tel:0785917777" custom_css="selector {{ display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: rgba(255,255,255,0.08); border: 1px solid rgba(246, 195, 88, 0.4); color: #ffffff !important; font-weight: 700; font-size: 15px; padding: 14px 26px; border-radius: 30px; text-decoration: none; transition: all 0.3s ease; }} selector:hover {{ background: rgba(246, 195, 88, 0.15); border-color: #f6c358; color: #f6c358 !important; }}"]
                [vbc_icon icon_type="lucide" name="phone" size="17px" color="#f6c358"]
                Hotline: 078.591.7777
            [/vbc_a]
        [/vbc_block]
    [/col]
    [col span="6" span__sm="12" align="center"]
        [vbc_box custom_css="selector {{ position: relative; max-width: 540px; margin: 0 auto; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.45)); animation: heroFloat 5s ease-in-out infinite; }} @keyframes heroFloat {{ 0%, 100% {{ transform: translateY(0); }} 50% {{ transform: translateY(-12px); }} }}"]
            [vbc_img src="{hero_slide_url}" width="100%" alt="Bánh trung thu Madame Hương Cao Cấp" custom_css="selector {{ width: 100%; height: auto; display: block; }}"]
        [/vbc_box]
    [/col]
[/row]
[/section]

[section id="gioi-thieu" bg_color="#fdfbf7" padding="70px 0" custom_css="selector {{ position: relative; border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" v_align="middle"]
    [col span="6" span__sm="12"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]Ý NIỆM CỦA MADAME HƯƠNG[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 34px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 20px; }}"]Thương hiệu bánh ngọt hàng đầu Hà Nội[/vbc_h2]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #555555; line-height: 1.75; margin-bottom: 14px; }}"]Là thương hiệu bánh ngọt hàng đầu tại Hà Nội, sản phẩm của <strong style="color: #02302e;">Madame Hương</strong> luôn kiểm soát chất lượng nghiêm ngặt, cùng nhân viên chăm chút cho từng món ăn, đồ uống để phục vụ quý khách hàng một cách trọn vẹn nhất.[/vbc_p]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #555555; line-height: 1.75; margin-bottom: 14px; }}"]Đội ngũ thợ làm bánh, nhân viên phục vụ cũng được đào tạo, hướng dẫn bài bản, đối xử công bằng để họ có được tinh thần thoải mái nhất mà làm tốt công việc của mình.[/vbc_p]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #555555; line-height: 1.75; margin-bottom: 24px; }}"]Tiệm bánh mang phong cách Pháp của một bà chủ hết lòng vì công việc, nâng niu từng chiếc bánh, thu dọn từng khay đựng và niềm nở với mọi khách hàng, ngày ngày vẫn thu hút một lượng lớn những vị khách yêu bánh.[/vbc_p]
        [vbc_a href="tel:0785917777" custom_css="selector {{ display: inline-flex; align-items: center; gap: 8px; background: #02302e; color: #f6c358 !important; padding: 12px 24px; border-radius: 30px; font-weight: 700; font-size: 14px; text-decoration: none; border: 1px solid #d4a853; transition: all 0.3s; }} selector:hover {{ background: #d4a853; color: #02302e !important; }}"]
            [vbc_icon icon_type="lucide" name="phone-call" size="16px" color="#f6c358"]
            Tư Vấn Đặt Hàng: 078.591.7777
        [/vbc_a]
    [/col]
    [col span="6" span__sm="12"]
        [vbc_box custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }}"]
            [vbc_div custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 16px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s; }} selector:hover {{ transform: translateY(-4px); border-color: #d4a853; box-shadow: 0 8px 20px rgba(184, 134, 11, 0.15); }}"]
                [vbc_img src="{nguyen_lieu_url}" width="70px" alt="Nguyên liệu thượng hạng" custom_css="selector {{ width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px; border: 2px solid #ebd9b8; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 6px 0; font-size: 15px; color: #02302e; font-weight: 700; }}"]Nguyên liệu thượng hạng[/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 12px; color: #777777; line-height: 1.4; }}"]Nhập khẩu 100%, chất lượng chuẩn Hồng Kông & Châu Á.[/vbc_p]
            [/vbc_div]
            [vbc_div custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 16px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s; }} selector:hover {{ transform: translateY(-4px); border-color: #d4a853; box-shadow: 0 8px 20px rgba(184, 134, 11, 0.15); }}"]
                [vbc_img src="{sang_trong_url}" width="70px" alt="Sang trọng và Tinh tế" custom_css="selector {{ width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px; border: 2px solid #ebd9b8; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 6px 0; font-size: 15px; color: #02302e; font-weight: 700; }}"]Sang trọng & Tinh tế[/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 12px; color: #777777; line-height: 1.4; }}"]Mẫu hộp làm mới qua từng năm, xứng tầm quà biếu VIP.[/vbc_p]
            [/vbc_div]
            [vbc_div custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 16px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s; }} selector:hover {{ transform: translateY(-4px); border-color: #d4a853; box-shadow: 0 8px 20px rgba(184, 134, 11, 0.15); }}"]
                [vbc_img src="{hong_kong_url}" width="70px" alt="Hương vị HongKong" custom_css="selector {{ width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px; border: 2px solid #ebd9b8; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 6px 0; font-size: 15px; color: #02302e; font-weight: 700; }}"]Hương vị Hồng Kông[/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 12px; color: #777777; line-height: 1.4; }}"]Hương vị gia truyền độc quyền: Bào ngư, Vi cá, Sen nhuyễn.[/vbc_p]
            [/vbc_div]
            [vbc_div custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 16px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: all 0.3s; }} selector:hover {{ transform: translateY(-4px); border-color: #d4a853; box-shadow: 0 8px 20px rgba(184, 134, 11, 0.15); }}"]
                [vbc_img src="{lua_chon_url}" width="70px" alt="Lựa chọn uy tín" custom_css="selector {{ width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin: 0 auto 10px; border: 2px solid #ebd9b8; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 6px 0; font-size: 15px; color: #02302e; font-weight: 700; }}"]Lựa chọn uy tín[/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 12px; color: #777777; line-height: 1.4; }}"]Hơn 10 năm khẳng định uy tín hàng đầu với mọi khách hàng.[/vbc_p]
            [/vbc_div]
        [/vbc_box]
    [/col]
[/row]
[/section]

[section id="cam-ket" bg_color="#ffffff" padding="70px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" v_align="middle"]
    [col span="6" span__sm="12"]
        [vbc_box custom_css="selector {{ border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 2px solid #ebd9b8; }}"]
            [vbc_img src="{aodai_url}" width="100%" alt="Cam kết chất lượng Madame Hương" custom_css="selector {{ width: 100%; height: auto; display: block; }}"]
        [/vbc_box]
    [/col]
    [col span="6" span__sm="12"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]GIÁ TRỊ CỐT LÕI[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 34px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 24px; }}"]Cam kết của Madame Hương[/vbc_h2]
        
        [vbc_block custom_css="selector {{ display: flex; flex-direction: column; gap: 16px; }}"]
            [vbc_container custom_css="selector {{ background: #fdfaf3; border: 1px solid #ebd9b8; border-radius: 12px; padding: 14px 18px; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 4px 0; color: #02302e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }}"]
                    [vbc_icon icon_type="lucide" name="check-circle" size="18px" color="#b8860b"]
                    Nguyên liệu thượng hạng
                [/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 13px; color: #666666; line-height: 1.5; }}"]Nguyên liệu bánh trung thu được nhập khẩu 100%, đảm bảo chất lượng tương đương các sản phẩm tại thị trường Hồng Kông và Châu Á.[/vbc_p]
            [/vbc_container]

            [vbc_container custom_css="selector {{ background: #fdfaf3; border: 1px solid #ebd9b8; border-radius: 12px; padding: 14px 18px; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 4px 0; color: #02302e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }}"]
                    [vbc_icon icon_type="lucide" name="award" size="18px" color="#b8860b"]
                    Sang trọng và Tinh tế
                [/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 13px; color: #666666; line-height: 1.5; }}"]Mẫu mã hộp được làm mới qua từng năm với màu sắc và họa tiết tinh tế. Madame Hương cho ra mắt mẫu hộp mới sang trọng, phù hợp với nhu cầu trao tặng đa dạng của khách hàng.[/vbc_p]
            [/vbc_container]

            [vbc_container custom_css="selector {{ background: #fdfaf3; border: 1px solid #ebd9b8; border-radius: 12px; padding: 14px 18px; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 4px 0; color: #02302e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }}"]
                    [vbc_icon icon_type="lucide" name="sparkles" size="18px" color="#b8860b"]
                    Hương vị HongKong tuyệt hảo
                [/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 13px; color: #666666; line-height: 1.5; }}"]Ngoài các hương vị quen thuộc như trứng – thập cẩm, trà Ô Long, trà xanh, hạt dẻ, sen trắng, đậu đỏ, sầu riêng, Madame Hương Mooncake còn đem đến các dòng cao cấp như Bào ngư và Vi cá mập.[/vbc_p]
            [/vbc_container]

            [vbc_container custom_css="selector {{ background: #fdfaf3; border: 1px solid #ebd9b8; border-radius: 12px; padding: 14px 18px; }}"]
                [vbc_h4 custom_css="selector {{ margin: 0 0 4px 0; color: #02302e; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }}"]
                    [vbc_icon icon_type="lucide" name="shield-check" size="18px" color="#b8860b"]
                    Lựa chọn uy tín hàng đầu
                [/vbc_h4]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 13px; color: #666666; line-height: 1.5; }}"]Sự hài lòng và cảm xúc viên mãn của quý khách chính là điều quan trọng nhất để tạo nên những bước đi vững chắc và khẳng định dấu ấn thương hiệu.[/vbc_p]
            [/vbc_container]
        [/vbc_block]
    [/col]
[/row]
[/section]

[section id="bo-suu-tap" bg_color="#fdfbf7" padding="70px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" align="center"]
    [col span="10" span__sm="12" align="center"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]BỘ SƯU TẬP BÁNH TRUNG THU[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 36px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 12px; }}"]Madame Hương 2026[/vbc_h2]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #666666; max-width: 750px; margin: 0 auto 40px auto; line-height: 1.6; }}"]Năm 2026, Madame Hương Mooncake giới thiệu bộ sưu tập mới <strong style="color: #b8860b;">"SẮC HOA THỊNH VƯỢNG"</strong> - kết tinh của tinh hoa thiết kế, sự thấu hiểu khẩu vị và tinh thần phục vụ tận tâm. Mỗi hộp bánh là một kiệt tác trọn vẹn thành ý.[/vbc_p]
    [/col]
[/row]
[row width="custom" custom_width="1200px"]
    {product_cards_shortcode}
[/row]
[/section]

[section id="gallery" bg_color="#ffffff" padding="70px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" align="center"]
    [col span="10" span__sm="12" align="center"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]HÌNH ẢNH THỰC TẾ[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 34px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 12px; }}"]Các vị bánh trung thu Madame Hương[/vbc_h2]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #666666; max-width: 700px; margin: 0 auto 35px auto; line-height: 1.6; }}"]Bên cạnh thiết kế độc đáo, Madame Hương Mooncake mang đến những hương vị mới mẻ, đầy bất ngờ và lôi cuốn: sầu riêng, mè đen rang, sen nhuyễn trứng muối, thập cẩm thượng hạng...[/vbc_p]
    [/col]
[/row]
[row width="custom" custom_width="1200px"]
    {gallery_shortcode}
[/row]
[/section]

[section id="danh-gia" bg_color="#fdfbf7" padding="70px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" align="center"]
    [col span="10" span__sm="12" align="center"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]TRẢI NGHIỆM KHÁCH HÀNG[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 34px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 12px; }}"]Khách hàng nói về Bánh trung thu Madame Hương[/vbc_h2]
        [vbc_p custom_css="selector {{ font-size: 15px; color: #666666; max-width: 680px; margin: 0 auto 40px auto; line-height: 1.6; }}"]Madame Hương Mooncake trân trọng cảm ơn quý khách hàng đã luôn tin tưởng ủng hộ và đồng hành cùng chúng tôi trong suốt hơn 10 năm qua.[/vbc_p]
    [/col]
[/row]
[row width="custom" custom_width="1200px"]
    [col span="6" span__sm="12" span__md="6"]
        [vbc_box custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; gap: 18px; align-items: flex-start; margin-bottom: 20px; }}"]
            [vbc_img src="{le_na_url}" width="65px" alt="Lê Na" custom_css="selector {{ width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 2px solid #d4a853; }}"]
            [vbc_div]
                [vbc_h4 custom_css="selector {{ margin: 0 0 2px 0; font-size: 17px; color: #02302e; font-weight: 700; }}"]Lê Na[/vbc_h4]
                [vbc_div custom_css="selector {{ color: #f59e0b; font-size: 14px; margin-bottom: 8px; }}"]⭐⭐⭐⭐⭐[/vbc_div]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 14px; color: #555555; line-height: 1.55; font-style: italic; }}"]"Mấy năm gần đây năm nào tôi cũng mua bánh trung thu Madame Hương vì sản phẩm vừa đẹp mắt mà chất lượng lại rất tuyệt."[/vbc_p]
            [/vbc_div]
        [/vbc_box]
    [/col]
    [col span="6" span__sm="12" span__md="6"]
        [vbc_box custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; gap: 18px; align-items: flex-start; margin-bottom: 20px; }}"]
            [vbc_img src="{yen_nhi_url}" width="65px" alt="Yến Nhi" custom_css="selector {{ width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 2px solid #d4a853; }}"]
            [vbc_div]
                [vbc_h4 custom_css="selector {{ margin: 0 0 2px 0; font-size: 17px; color: #02302e; font-weight: 700; }}"]Yến Nhi[/vbc_h4]
                [vbc_div custom_css="selector {{ color: #f59e0b; font-size: 14px; margin-bottom: 8px; }}"]⭐⭐⭐⭐⭐[/vbc_div]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 14px; color: #555555; line-height: 1.55; font-style: italic; }}"]"Bánh Madame Hương rất thơm ngon, hộp quà thiết kế tinh xảo, sang trọng. Mình sẽ tiếp tục ủng hộ bánh trung thu cao cấp Madame Hương!"[/vbc_p]
            [/vbc_div]
        [/vbc_box]
    [/col]
    [col span="6" span__sm="12" span__md="6"]
        [vbc_box custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; gap: 18px; align-items: flex-start; margin-bottom: 20px; }}"]
            [vbc_img src="{manh_tu_url}" width="65px" alt="Mạnh Tú" custom_css="selector {{ width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 2px solid #d4a853; }}"]
            [vbc_div]
                [vbc_h4 custom_css="selector {{ margin: 0 0 2px 0; font-size: 17px; color: #02302e; font-weight: 700; }}"]Mạnh Tú[/vbc_h4]
                [vbc_div custom_css="selector {{ color: #f59e0b; font-size: 14px; margin-bottom: 8px; }}"]⭐⭐⭐⭐⭐[/vbc_div]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 14px; color: #555555; line-height: 1.55; font-style: italic; }}"]"Tôi đặt bánh trung thu số lượng lớn cho công ty mà ở công ty ai cũng khen bánh mẫu mã đẹp, đẳng cấp và ăn rất vừa vị."[/vbc_p]
            [/vbc_div]
        [/vbc_box]
    [/col]
    [col span="6" span__sm="12" span__md="6"]
        [vbc_box custom_css="selector {{ background: #ffffff; border: 1px solid #ebd9b8; border-radius: 16px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; gap: 18px; align-items: flex-start; margin-bottom: 20px; }}"]
            [vbc_img src="{an_nhien_url}" width="65px" alt="An Nhiên" custom_css="selector {{ width: 65px; height: 65px; border-radius: 50%; object-fit: cover; border: 2px solid #d4a853; }}"]
            [vbc_div]
                [vbc_h4 custom_css="selector {{ margin: 0 0 2px 0; font-size: 17px; color: #02302e; font-weight: 700; }}"]An Nhiên[/vbc_h4]
                [vbc_div custom_css="selector {{ color: #f59e0b; font-size: 14px; margin-bottom: 8px; }}"]⭐⭐⭐⭐⭐[/vbc_div]
                [vbc_p custom_css="selector {{ margin: 0; font-size: 14px; color: #555555; line-height: 1.55; font-style: italic; }}"]"Phải công nhận là bánh trung thu ngon, mẫu mã sản phẩm rất đẹp, làm quà biếu rất hợp lý, giá bán lại nhẹ nhàng với túi tiền."[/vbc_p]
            [/vbc_div]
        [/vbc_box]
    [/col]
[/row]
[/section]

[section id="lien-he" bg_color="#ffffff" padding="70px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" v_align="middle"]
    [col span="6" span__sm="12"]
        [vbc_box custom_css="selector {{ background: #fdfaf3; border: 1px solid #ebd9b8; border-radius: 18px; padding: 32px; box-shadow: 0 8px 25px rgba(0,0,0,0.04); }}"]
            [vbc_h3 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 24px; color: #02302e; font-weight: 700; margin-bottom: 10px; }}"]Đăng ký nhận tư vấn & Báo giá ![/vbc_h3]
            [vbc_p custom_css="selector {{ font-size: 14px; color: #666666; margin-bottom: 22px; }}"]Để lại thông tin, đội ngũ chuyên viên Madame Hương sẽ liên hệ tư vấn chi tiết và gửi bảng báo giá chiết khấu tốt nhất.[/vbc_p]
            
            [vbc_div custom_css="selector {{ display: flex; flex-direction: column; gap: 14px; }}"]
                <div style="width: 100%;">
                    <input type="text" placeholder="Họ và tên của bạn *" style="width: 100%; padding: 12px 16px; border: 1px solid #ebd9b8; border-radius: 8px; font-size: 14px; background: #ffffff; outline: none; box-sizing: border-box;" />
                </div>
                <div style="width: 100%;">
                    <input type="tel" placeholder="Số điện thoại liên hệ *" style="width: 100%; padding: 12px 16px; border: 1px solid #ebd9b8; border-radius: 8px; font-size: 14px; background: #ffffff; outline: none; box-sizing: border-box;" />
                </div>
                <div style="width: 100%;">
                    <input type="email" placeholder="Địa chỉ Email" style="width: 100%; padding: 12px 16px; border: 1px solid #ebd9b8; border-radius: 8px; font-size: 14px; background: #ffffff; outline: none; box-sizing: border-box;" />
                </div>
                <div style="width: 100%;">
                    <textarea placeholder="Số lượng dự kiến & Địa chỉ giao hàng..." style="width: 100%; padding: 12px 16px; border: 1px solid #ebd9b8; border-radius: 8px; font-size: 14px; background: #ffffff; outline: none; min-height: 80px; box-sizing: border-box;"></textarea>
                </div>
                [vbc_a href="tel:0785917777" custom_css="selector {{ display: block; text-align: center; background: linear-gradient(135deg, #02302e, #034842); color: #f6c358 !important; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 15px; text-decoration: none; border: 1px solid #d4a853; transition: all 0.3s; margin-top: 6px; box-shadow: 0 4px 15px rgba(2, 48, 46, 0.2); }} selector:hover {{ background: #d4a853; color: #02302e !important; transform: translateY(-2px); }}"]
                    GỬI ĐĂNG KÝ TƯ VẤN NGAY
                [/vbc_a]
            [/vbc_div]
        [/vbc_box]
    [/col]
    [col span="6" span__sm="12"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]DÀNH CHO KHÁCH HÀNG DOANH NGHIỆP[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 32px; color: #02302e; font-weight: 700; line-height: 1.25; margin-bottom: 20px; }}"]Ưu đãi đặc biệt cho doanh nghiệp[/vbc_h2]
        
        [vbc_block custom_css="selector {{ display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; }}"]
            [vbc_container custom_css="selector {{ display: flex; gap: 14px; align-items: center; background: #fdfaf3; padding: 14px 18px; border-radius: 12px; border-left: 4px solid #d4a853; }}"]
                [vbc_icon icon_type="lucide" name="percent" size="22px" color="#b8860b"]
                [vbc_span custom_css="selector {{ font-size: 15px; color: #02302e; font-weight: 600; }}"]Ưu đãi chiết khấu hấp dẫn lên tới 25% khi đặt sớm hoặc số lượng lớn.[/vbc_span]
            [/vbc_container]
            [vbc_container custom_css="selector {{ display: flex; gap: 14px; align-items: center; background: #fdfaf3; padding: 14px 18px; border-radius: 12px; border-left: 4px solid #d4a853; }}"]
                [vbc_icon icon_type="lucide" name="printer" size="22px" color="#b8860b"]
                [vbc_span custom_css="selector {{ font-size: 15px; color: #02302e; font-weight: 600; }}"]Hỗ trợ in ấn Logo doanh nghiệp sắc nét, tạo dấu ấn thương hiệu riêng.[/vbc_span]
            [/vbc_container]
            [vbc_container custom_css="selector {{ display: flex; gap: 14px; align-items: center; background: #fdfaf3; padding: 14px 18px; border-radius: 12px; border-left: 4px solid #d4a853; }}"]
                [vbc_icon icon_type="lucide" name="truck" size="22px" color="#b8860b"]
                [vbc_span custom_css="selector {{ font-size: 15px; color: #02302e; font-weight: 600; }}"]Giao hàng tận nơi toàn quốc, đảm bảo bảo quản tiêu chuẩn cao cấp.[/vbc_span]
            [/vbc_container]
        [/vbc_block]
        
        [vbc_box custom_css="selector {{ border-radius: 14px; overflow: hidden; border: 1px solid #ebd9b8; }}"]
            [vbc_img src="{center_bg_url}" width="100%" alt="Ưu đãi doanh nghiệp Madame Hương" custom_css="selector {{ width: 100%; height: auto; display: block; }}"]
        [/vbc_box]
    [/col]
[/row]
[/section]

[section id="doi-tac" bg_color="#fdfbf7" padding="60px 0" custom_css="selector {{ border-bottom: 1px solid #ede4d3; }}"]
[row width="custom" custom_width="1200px" align="center"]
    [col span="10" span__sm="12" align="center"]
        [vbc_span custom_css="selector {{ display: block; color: #b8860b; font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }}"]ĐỐI TÁC TIN CẬY[/vbc_span]
        [vbc_h2 custom_css="selector {{ font-family: 'Playfair Display', Georgia, serif; font-size: 32px; color: #02302e; font-weight: 700; margin-bottom: 24px; }}"]Bảo trợ bởi Tập đoàn Golden Gate[/vbc_h2]
        [vbc_box custom_css="selector {{ max-width: 900px; margin: 0 auto; background: #ffffff; padding: 24px; border-radius: 16px; border: 1px solid #ebd9b8; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }}"]
            [vbc_img src="{bao_tro_url}" width="100%" alt="Đối tác Golden Gate bảo trợ" custom_css="selector {{ width: 100%; height: auto; display: block; margin: 0 auto; }}"]
        [/vbc_box]
    [/col]
[/row]
[/section]

[section id="footer" bg_color="#012220" padding="60px 0 30px 0" custom_css="selector {{ color: #ffffff; border-top: 2px solid #d4a853; }}"]
[row width="custom" custom_width="1200px"]
    [col span="4" span__sm="12"]
        [vbc_box custom_css="selector {{ display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }}"]
            [vbc_img src="{logo_url}" width="54px" alt="Madame Hương Logo" custom_css="selector {{ width: 54px; height: auto; display: block; }}"]
            [vbc_div]
                [vbc_h3 custom_css="selector {{ margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 20px; color: #f6c358; font-weight: 700; }}"]MADAME HƯƠNG[/vbc_h3]
                [vbc_span custom_css="selector {{ font-size: 11px; color: rgba(255,255,255,0.7); letter-spacing: 2px; text-transform: uppercase; }}"]Mooncake Heritage[/vbc_span]
            [/vbc_div]
        [/vbc_box]
        [vbc_p custom_css="selector {{ font-size: 14px; color: #a9bcba; line-height: 1.6; margin-bottom: 16px; }}"]Thương hiệu bánh trung thu và quà tặng cao cấp hàng đầu Hà Nội, kết tinh văn hóa ẩm thực truyền thống và phong cách tinh tế Pháp.[/vbc_p]
    [/col]
    [col span="4" span__sm="12"]
        [vbc_h4 custom_css="selector {{ color: #f6c358; font-size: 17px; font-weight: 700; margin-bottom: 16px; text-transform: uppercase; }}"]Hệ Thống Trụ Sở[/vbc_h4]
        [vbc_p custom_css="selector {{ font-size: 14px; color: #a9bcba; line-height: 1.6; margin-bottom: 10px; }}"]<strong style="color: #ffffff;">VPGD Hà Nội:</strong> 39 Lý Thường Kiệt, Phường Hàng Bài, Quận Hoàn Kiếm, Hà Nội.[/vbc_p]
        [vbc_p custom_css="selector {{ font-size: 14px; color: #a9bcba; line-height: 1.6; margin-bottom: 10px; }}"]<strong style="color: #ffffff;">Chi Nhánh TP.HCM:</strong> 18 đường T5, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh.[/vbc_p]
        [vbc_p custom_css="selector {{ font-size: 14px; color: #a9bcba; line-height: 1.6; }}"]<strong style="color: #ffffff;">Hotline Đặt Bánh:</strong> <a href="tel:0785917777" style="color: #f6c358; font-weight: 700; text-decoration: none;">078.591.7777</a>[/vbc_p]
    [/col]
    [col span="4" span__sm="12"]
        [vbc_h4 custom_css="selector {{ color: #f6c358; font-size: 17px; font-weight: 700; margin-bottom: 16px; text-transform: uppercase; }}"]Chính Sách & Hỗ Trợ[/vbc_h4]
        [vbc_box custom_css="selector {{ display: flex; flex-direction: column; gap: 8px; }}"]
            [vbc_a href="#" custom_css="selector {{ color: #a9bcba !important; font-size: 14px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]• Phương thức thanh toán & giao hàng[/vbc_a]
            [vbc_a href="#" custom_css="selector {{ color: #a9bcba !important; font-size: 14px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]• Chính sách bảo mật thông tin[/vbc_a]
            [vbc_a href="#" custom_css="selector {{ color: #a9bcba !important; font-size: 14px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]• Chính sách đổi trả sản phẩm[/vbc_a]
            [vbc_a href="#" custom_css="selector {{ color: #a9bcba !important; font-size: 14px; text-decoration: none; transition: color 0.3s; }} selector:hover {{ color: #f6c358 !important; }}"]• Hướng dẫn xuất hóa đơn VAT[/vbc_a]
        [/vbc_box]
    [/col]
[/row]
[row width="custom" custom_width="1200px" custom_css="selector {{ border-top: 1px solid rgba(255,255,255,0.1); margin-top: 30px; padding-top: 20px; }}"]
    [col span="12" align="center"]
        [vbc_p custom_css="selector {{ font-size: 13px; color: rgba(255,255,255,0.6); margin: 0; }}"]© 2026 All rights reserved https://www.banhtrungthu-madamehuong.vn/ - Bản quyền thuộc về Madame Hương Mooncake[/vbc_p]
    [/col]
[/row]
[/section]

[vbc_box custom_css="selector {{ position: fixed; bottom: 25px; right: 25px; display: flex; flex-direction: column; gap: 12px; z-index: 9999; }}"]
    [vbc_a href="https://zalo.me/0785917777" target="_blank" custom_css="selector {{ display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: #0068ff; box-shadow: 0 4px 15px rgba(0, 104, 255, 0.4); transition: transform 0.3s; }} selector:hover {{ transform: scale(1.1); }}"]
        [vbc_img src="{zalo_icon_url}" width="32px" alt="Zalo Madame Hương" custom_css="selector {{ width: 32px; height: 32px; display: block; }}"]
    [/vbc_a]
    [vbc_a href="tel:0785917777" custom_css="selector {{ display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 50%; background: #25d366; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); transition: transform 0.3s; animation: pulsePhone 2s infinite; }} @keyframes pulsePhone {{ 0% {{ box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }} 70% {{ box-shadow: 0 0 0 14px rgba(37, 211, 102, 0); }} 100% {{ box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }} }} selector:hover {{ transform: scale(1.1); }}"]
        [vbc_img src="{phone_icon_url}" width="28px" alt="Hotline Madame Hương" custom_css="selector {{ width: 28px; height: 28px; display: block; }}"]
    [/vbc_a]
[/vbc_box]
"""

# ONLY container tags that have closing pairs need nesting resolution
def sanitize_shortcodes(content):
    nestable_tags = ['vbc_div', 'vbc_box', 'vbc_block', 'vbc_container', 'vbc_span', 'vbc_p', 'vbc_a']
    fixed = content
    for tag in nestable_tags:
        # Tokenize tag
        tag_regex = re.compile(rf'\[(/?){tag}(\s[^\]]*)?\]')
        tokens = []
        for m in tag_regex.finditer(fixed):
            tokens.append({
                'full': m.group(0),
                'is_close': m.group(1) == '/',
                'attrs': m.group(2) or '',
                'start': m.start(),
                'end': m.end()
            })
        
        if not tokens:
            continue
            
        stack = []
        replacements = []
        for token in tokens:
            if not token['is_close']:
                current_depth = len(stack) + 1
                if current_depth > 1:
                    suffix = '_inner' if current_depth == 2 else f'_inner_{current_depth - 2}'
                    target_tag = f"{tag}{suffix}"
                    new_open = f"[{target_tag}{token['attrs']}]"
                    replacements.append((token['start'], token['end'], new_open))
                    stack.append(target_tag)
                else:
                    stack.append(tag)
            else:
                if stack:
                    expected_tag = stack.pop()
                    if expected_tag != tag:
                        replacements.append((token['start'], token['end'], f"[/{expected_tag}]"))
        
        # Apply replacements from back to front
        replacements.sort(key=lambda x: x[0], reverse=True)
        for start, end, new_text in replacements:
            fixed = fixed[:start] + new_text + fixed[end:]
            
    return fixed

sanitized_shortcode = sanitize_shortcodes(full_shortcode.strip())

# Save generated shortcode to file
with open('madamehuong_page_content.txt', 'w', encoding='utf-8') as f:
    f.write(sanitized_shortcode)

print("Sanitized Shortcode saved. Length:", len(sanitized_shortcode))

# Publish / Update page via REST API
config = load_config()
api_url = config.get('api-url', 'https://ultimateflatsomevibecode.s172d211.wpcloud.vn/wp-json').rstrip('/')
token = config.get('token', '')

endpoint = f"{api_url}/vbc/v1/page"
payload = {
    'post_id': 436,
    'title': 'Bánh Trung Thu Madame Hương - Tinh Hoa Hà Thành',
    'slug': 'banh-trung-thu-madame-huong',
    'content': sanitized_shortcode,
    'status': 'publish',
    'post_type': 'page'
}

data = json.dumps(payload, ensure_ascii=False).encode('utf-8')
req = urllib.request.Request(endpoint, data=data, headers={
    'Content-Type': 'application/json; charset=utf-8',
    'X-VBC-Token': token
})

try:
    with urllib.request.urlopen(req) as resp:
        resp_data = json.loads(resp.read().decode('utf-8'))
        print("\n==================================================")
        print("   ĐÃ SỬA VÀ CẬP NHẬT TRANG CLONE MADAME HƯƠNG!")
        print("==================================================")
        print(f"Post ID:   {resp_data.get('post_id')}")
        print(f"Action:    {resp_data.get('action')}")
        print(f"URL:       {resp_data.get('url')}")
        print("==================================================\n")
except urllib.error.HTTPError as e:
    err_msg = e.read().decode('utf-8', errors='ignore')
    print(f"[LỖI HTTP {e.code}]: {err_msg}", file=sys.stderr)
    sys.exit(1)
except Exception as e:
    print(f"[LỖI]: {str(e)}", file=sys.stderr)
    sys.exit(1)
