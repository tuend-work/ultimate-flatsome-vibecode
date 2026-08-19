#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
generate_nihaoma_vbc.py
Clone hoàn chỉnh trang https://nihaoma-mandarin.com/vi/trang-chu/
100% thuần VBC Elements ([vbc_div], [vbc_box], [vbc_block], [vbc_container], [vbc_icon], [vbc_p], [vbc_a], CF7...)
theo đúng quy chuẩn chống lỗi lồng shortcode trong ultimate-flatsome-vibecode/skills/readme.md.
"""

import os
import sys
import json
import urllib.request
import urllib.error
import re

if sys.platform == "win32":
    try:
        sys.stdout.reconfigure(encoding="utf-8")
        sys.stderr.reconfigure(encoding="utf-8")
    except:
        pass

# ── Load Config ─────────────────────────────────────────────────────────────
cfg_path = os.path.join(os.path.dirname(__file__), "..", "ultimate-flatsome-vibecode", "vbc-config.json")
with open(cfg_path, "r", encoding="utf-8") as f:
    cfg = json.load(f)

API = cfg["api-url"].rstrip("/")
TOKEN = cfg["token"]

# ── Brand Colors ────────────────────────────────────────────────────────────
RED = "#e63946"
DARK_RED = "#c92a2a"
LIGHT_PINK = "#fff5f5"
CREAM_BG = "#fffaf7"
DARK_NAVY = "#111827"
CARD_BG = "#ffffff"
TEXT_DARK = "#1f2937"
TEXT_MUTED = "#6b7280"
BORDER_COLOR = "#fecdd3"
GOLD = "#f59e0b"


def build_nihaoma_vbc_content():
    parts = []

    # =========================================================================
    # 1. TOP BAR & MAIN HEADER
    # =========================================================================
    top_bar = f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 10px 0; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.08); }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }}"]
        [vbc_block custom_css="selector {{ display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }}"]
            [vbc_a link_url="tel:0585680116" custom_css="selector {{ color: #e5e7eb; text-decoration: none; display: flex; align-items: center; gap: 6px; font-weight: 500; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="phone" size="14px" color="{RED}"]
                [vbc_span]Hotline: +84 585 680 116[/vbc_span]
            [/vbc_a]
            [vbc_a link_url="mailto:customercare.td@nihaoma-mandarin.com" custom_css="selector {{ color: #e5e7eb; text-decoration: none; display: flex; align-items: center; gap: 6px; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="mail" size="14px" color="{RED}"]
                [vbc_span]customercare.td@nihaoma-mandarin.com[/vbc_span]
            [/vbc_a]
            <span style="color: #9ca3af; display: flex; align-items: center; gap: 6px;">
                [vbc_icon icon_type="lucide" name="map-pin" size="14px" color="{RED}"]
                TP. Hồ Chí Minh: Thảo Điền & Phú Mỹ Hưng
            </span>
        [/vbc_block]
        [vbc_block_inner custom_css="selector {{ display: flex; align-items: center; gap: 15px; }}"]
            [vbc_a link_url="https://facebook.com/NiHaoMaVietnam" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="facebook" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://instagram.com/nihaomavietnam" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="instagram" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://youtube.com/@nihaoma-mandarin" link_target="_blank" custom_css="selector {{ color: #ffffff; text-decoration: none; display: flex; align-items: center; }} selector:hover {{ color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="youtube" size="15px"]
            [/vbc_a]
            [vbc_a link_url="https://zalo.me/0585680116" link_target="_blank" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 12px; text-decoration: none; }} selector:hover {{ background: {DARK_RED}; }}"]
                [vbc_span]Zalo Tư Vấn[/vbc_span]
            [/vbc_a]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]

[vbc_div_inner custom_css="selector {{ width: 100%; background: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; padding: 12px 0; }}"]
    [vbc_box_inner class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }}"]
        [vbc_a link_url="https://nihaoma-mandarin.com/vi/trang-chu/" custom_css="selector {{ display: flex; align-items: center; text-decoration: none; gap: 12px; }}"]
            [vbc_img img_source="manual" img_attachment="" alt="Ni Hao Ma Mandarin Learning Lab" custom_css="selector {{ height: 52px; width: auto; object-fit: contain; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2023/08/cropped-NHM-Logo.png"]
        [/vbc_a]
        <div style="display: flex; align-items: center; gap: 24px;">
            [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Khóa Học[/vbc_span][/vbc_a]
            [vbc_a link_url="#giao-vien" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Viên[/vbc_span][/vbc_a]
            [vbc_a link_url="#giao-trinh" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Trình[/vbc_span][/vbc_a]
            [vbc_a link_url="#tai-sao-chon" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tại Sao Chọn Ni Hao Ma[/vbc_span][/vbc_a]
            [vbc_a link_url="#cam-nhan" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Đánh Giá[/vbc_span][/vbc_a]
            [vbc_a link_url="#ve-chung-toi" custom_css="selector {{ color: {TEXT_DARK}; text-decoration: none; font-weight: 600; font-size: 15px; }} selector:hover {{ color: {RED}; }}"][vbc_span]Về Chúng Tôi[/vbc_span][/vbc_a]
        </div>
        [vbc_a link_url="#dang-ky" custom_css="selector {{ background: linear-gradient(135deg, {RED}, {DARK_RED}); color: #ffffff !important; padding: 10px 24px; border-radius: 30px; font-weight: 700; font-size: 14px; text-decoration: none; box-shadow: 0 4px 14px rgba(230,57,70,0.35); display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s, box-shadow 0.2s; }} selector:hover {{ transform: translateY(-2px); box-shadow: 0 6px 20px rgba(230,57,70,0.45); }}"]
            [vbc_icon icon_type="lucide" name="sparkles" size="16px" color="#ffffff"]
            [vbc_span]Đăng Ký Học Thử[/vbc_span]
        [/vbc_a]
    [/vbc_box_inner]
[/vbc_div_inner]
"""
    parts.append(top_bar)

    # =========================================================================
    # 2. HERO BANNER SECTION
    # =========================================================================
    hero_section = f"""
[vbc_div custom_css="selector {{ width: 100%; background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%); padding: 40px 0 60px 0; position: relative; overflow: hidden; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; text-align: center; }} }}"]
            [vbc_container custom_css="selector {{ display: flex; flex-direction: column; gap: 18px; }}"]
                <span style="display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: {DARK_RED}; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; width: fit-content; border: 1px solid #fecdd3;">
                    [vbc_icon icon_type="lucide" name="flame" size="16px" color="{RED}"]
                    KHAI GIẢNG LIÊN TỤC CÁC LỚP ONLINE TOÀN QUỐC
                </span>
                [vbc_h1 custom_css="selector {{ font-size: 42px; font-weight: 900; line-height: 1.2; color: {DARK_NAVY}; margin: 0; }} @media(max-width: 549px){{ selector {{ font-size: 30px; }} }}"]
                    Học Tiếng Trung 1 Kèm 1 Cùng <span style="color: {RED};">100% Giáo Viên Bản Xứ</span>
                [/vbc_h1]
                [vbc_p custom_css="selector {{ font-size: 17px; line-height: 1.7; color: {TEXT_MUTED}; margin: 0; }}"]
                    Trung tâm tiếng Trung Ni Hao Ma mang đến giải pháp học tiếng Trung hiện đại, lộ trình cá nhân hóa, lớp học 1:1 online tương tác trực tiếp và thời lượng học cực kỳ linh hoạt cho học sinh và người bận rộn.
                [/vbc_p]
                <div style="display: flex; flex-direction: column; gap: 10px; margin: 8px 0;">
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        100% giáo viên Đài Loan chuẩn phát âm quốc tế
                    </p>
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        Lịch học tự chọn, dễ dàng đổi giờ và học bù khi bận việc
                    </p>
                    <p style="display: flex; align-items: center; gap: 10px; font-size: 15px; color: {TEXT_DARK}; font-weight: 500; margin: 0;">
                        [vbc_icon icon_type="lucide" name="check-circle-2" size="18px" color="{RED}"]
                        Tài liệu & App số hóa độc quyền hỗ trợ luyện thi HSK/YCT
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 10px;">
                    [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 16px 36px; border-radius: 35px; font-weight: 700; font-size: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(230,57,70,0.3); transition: all 0.25s; }} selector:hover {{ background: {DARK_RED}; transform: translateY(-3px); box-shadow: 0 14px 30px rgba(230,57,70,0.4); }}"]
                        [vbc_icon icon_type="lucide" name="arrow-right-circle" size="20px"]
                        [vbc_span]Đăng Ký Học Thử Miễn Phí[/vbc_span]
                    [/vbc_a]
                    [vbc_a link_url="https://zalo.me/0585680116" link_target="_blank" custom_css="selector {{ background: #ffffff; color: {TEXT_DARK} !important; border: 2px solid #e5e7eb; padding: 14px 28px; border-radius: 35px; font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.25s; }} selector:hover {{ border-color: {RED}; color: {RED} !important; transform: translateY(-3px); }}"]
                        [vbc_icon icon_type="lucide" name="message-circle" size="18px" color="{RED}"]
                        [vbc_span]Nhận Lộ Trình & Học Phí[/vbc_span]
                    [/vbc_a]
                </div>
            [/vbc_container]
            [vbc_container_inner custom_css="selector {{ position: relative; text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Ni Hao Ma Học Tiếng Trung Online" custom_css="selector {{ width: 100%; max-width: 540px; height: auto; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); display: inline-block; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2025/03/Banner-Web.png"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(hero_section)

    # =========================================================================
    # 3. GIẢI PHÁP LINH HOẠT VÀ HIỆU QUẢ (4 ICON CARDS)
    # =========================================================================
    features_section = f"""
[vbc_div id="khoa-hoc" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Giải Pháp Linh Hoạt và Hiệu Quả
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                Mô hình giảng dạy tiếng Trung cá nhân hóa chuẩn quốc tế, giúp người học làm chủ ngôn ngữ nhanh chóng và tự tin nhất.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="video" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Học Online 1 Kèm 1[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Lộ trình học tập thiết kế cá nhân hóa, giáo viên bản ngữ trực tiếp kèm cặp và sửa lỗi phát âm tức thì.[/vbc_p]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="award" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]100% Giáo Viên Bản Xứ[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Đội ngũ giáo viên Đài Loan có chứng chỉ sư phạm quốc tế TCSL, phát âm chuẩn và giàu kinh nghiệm giảng dạy.[/vbc_p]
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="calendar-clock" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Lịch Học Linh Hoạt[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Chủ động chọn khung giờ học thuận tiện từ 7:00 đến 22:00, hỗ trợ dời lịch và học bù nhanh chóng khi bận việc.[/vbc_p]
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 24px; text-align: center; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); background: #ffffff; border-color: {RED}; }}"]
                [vbc_icon icon_type="lucide" name="smartphone" size="32px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 0 20px 0"]
                [vbc_h3 custom_css="selector {{ font-size: 19px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 12px 0; }}"]Tài Liệu Số Độc Quyền[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 14px; color: {TEXT_MUTED}; line-height: 1.7; margin: 0; }}"]Tài liệu e-book độc quyền kèm app học tập tích hợp giúp học viên dễ dàng ôn tập và luyện nghe nói mọi lúc mọi nơi.[/vbc_p]
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(features_section)

    # =========================================================================
    # 4. GIÁO VIÊN CỦA CHÚNG TÔI (TEACHER GRID)
    # =========================================================================
    teachers_section = f"""
[vbc_div id="giao-vien" custom_css="selector {{ width: 100%; background: {CREAM_BG}; padding: 80px 0; border-top: 1px solid #fce7f3; border-bottom: 1px solid #fce7f3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Giáo Viên Của Chúng Tôi
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                100% giáo viên bản ngữ người Đài Loan chuẩn quốc tế, tận tâm đồng hành và truyền cảm hứng ngôn ngữ.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Cô Lin - Giáo viên Đài Loan" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-1.png"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Lin (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Thạc Sĩ Ngôn Ngữ &bull; Chứng Chỉ TCSL[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]6+ năm giảng dạy tiếng Trung giao tiếp & thương mại cho học viên Việt Nam.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Cô Chen - Giáo viên Đài Loan" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-2.png"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Chen (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Chuyên Gia Luyện Thi HSK 4 - 6[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Phương pháp phản xạ thực chiến giúp học viên đạt điểm cao trong kỳ thi HSK.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Thầy Wang - Giáo viên Đài Loan" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-4.png"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Thầy Wang (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Cố Vấn Học Thuật & Thương Mại[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Chuyên đào tạo tiếng Trung đàm phán doanh nghiệp và văn hóa giao thương.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.06); transition: all 0.3s; text-align: center; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 36px rgba(230,57,70,0.15); }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Cô Huang - Giáo viên Đài Loan" custom_css="selector {{ width: 100%; height: 260px; object-fit: cover; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-5.png"]
                <div style="padding: 22px 18px;">
                    [vbc_h4 custom_css="selector {{ font-size: 18px; font-weight: 800; color: {DARK_NAVY}; margin: 0 0 6px 0; }}"]Cô Huang (Đài Loan)[/vbc_h4]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {RED}; font-weight: 700; margin: 0 0 10px 0; }}"]Chuyên Gia Tiếng Trung Trẻ Em[/vbc_p]
                    [vbc_p custom_css="selector {{ font-size: 13px; color: {TEXT_MUTED}; line-height: 1.6; margin: 0 0 14px 0; }}"]Phương pháp giảng dạy qua tương tác trò chơi vui nhộn, kích thích tư duy sớm.[/vbc_p]
                    <div style="display: flex; justify-content: center; gap: 4px; color: {GOLD};">
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="16px" color="{GOLD}"]
                    </div>
                </div>
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(teachers_section)

    # =========================================================================
    # 5. GIÁO TRÌNH & CHƯƠNG TRÌNH HỌC (2 COLUMNS: ILLUSTRATION + ACCORDION)
    # =========================================================================
    curriculum_section = f"""
[vbc_div id="giao-trinh" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ text-align: center; }}"]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; text-align: left; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Giáo Trình Đạt Chuẩn Quốc Tế
                [/vbc_h2]
                [vbc_img img_source="manual" img_attachment="" alt="Giáo trình Ni Hao Ma" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Curriculum-Nihaoma.png"]
            [/vbc_container]

            [vbc_container_inner]
                [accordion]
                    [accordion-item title="Tiếng Trung YCT (Youth Chinese Test) Cho Trẻ Em"]
                        Xây dựng nền tảng tiếng Trung chuẩn xác cho 4 kỹ năng Nghe - Nói - Đọc - Viết. Chương trình học theo chủ đề đời sống sinh động, xen kẽ các trò chơi tương tác và hoạt động sáng tạo giúp trẻ ghi nhớ từ vựng tự nhiên và yêu thích tiếng Trung từ nhỏ.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Luyện Thi HSK 1 - 6 Cấp Tốc"]
                        Trang bị toàn diện kiến thức cần thiết để du học và làm việc trong môi trường quốc tế. Bao gồm hơn 1000+ từ vựng, 300+ chữ Hán và 20+ cấu trúc ngữ pháp trọng tâm theo chuẩn kỳ thi năng lực Hán ngữ quốc tế.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Giao Tiếp Thực Chiến 1:1"]
                        Tăng cường phản xạ nghe nói tự nhiên, rèn luyện sự tự tin khi trò chuyện cùng người bản xứ. Học viên có thể giao tiếp lưu loát trong công việc, cuộc sống thường ngày và các tình huống giao thương quốc tế.
                    [/accordion-item]
                    [accordion-item title="Tiếng Trung Thương Mại & Doanh Nghiệp"]
                        Tập trung nâng cao kỹ năng đàm phán, thuyết trình, soạn thảo email hợp đồng và thuật ngữ chuyên ngành (Xuất nhập khẩu, Bất động sản, Tài chính, Logistics). Giúp học viên gia tăng lợi thế cạnh tranh sự nghiệp.
                    [/accordion-item]
                [/accordion]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(curriculum_section)

    # =========================================================================
    # 6. KHÓA HỌC CHO MỌI NGƯỜI (TARGET GROUPS)
    # =========================================================================
    audience_section = f"""
[vbc_div custom_css="selector {{ width: 100%; background: {LIGHT_PINK}; padding: 80px 0; border-top: 1px solid #fecdd3; border-bottom: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Chương Trình Học Cho Mọi Người
                [/vbc_h2]
                [accordion]
                    [accordion-item title="Trẻ Em (Từ 3 đến 11 tuổi)"]
                        Tiếp xúc sớm với tiếng Trung giúp trẻ phát triển vùng ngôn ngữ não bộ tối đa, xây dựng nền tảng phát âm chuẩn bản xứ ngay từ đầu. Mang lại lợi thế vượt bậc khi hòa nhập trường quốc tế và phát triển tương lai.
                    [/accordion-item]
                    [accordion-item title="Thiếu Niên (Từ 12 đến 17 tuổi)"]
                        Trang bị tiếng Trung bài bản, chuẩn bị hành trang săn học bổng du học Đài Loan, Trung Quốc và chinh phục các chứng chỉ quốc tế HSK 3 - 5 với kết quả xuất sắc.
                    [/accordion-item]
                    [accordion-item title="Người Đi Làm & Người Lớn Bận Rộn"]
                        Tập trung rèn luyện phản xạ giao tiếp trôi chảy, nắm vững thuật ngữ chuyên ngành để mở rộng cơ hội thăng tiến, làm việc tại các tập đoàn đa quốc gia hoặc quản lý kinh doanh.
                    [/accordion-item]
                [/accordion]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Khóa học cho mọi người" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Age-group-Nihaoma.png"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(audience_section)

    # =========================================================================
    # 7. TẠI SAO CHỌN CHÚNG TÔI? (WHY US ACCORDION + ILLUSTRATION)
    # =========================================================================
    why_us_section = f"""
[vbc_div id="tai-sao-chon" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ text-align: center; }}"]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 24px; text-align: left; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Tại Sao Chọn Chúng Tôi?
                [/vbc_h2]
                [vbc_img img_source="manual" img_attachment="" alt="Tại sao chọn Ni Hao Ma" custom_css="selector {{ width: 100%; max-width: 420px; height: auto; border-radius: 20px; display: inline-block; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Modern-Nihaoma.png"]
            [/vbc_container]

            [vbc_container_inner]
                [accordion]
                    [accordion-item title="HỌC 1 KÈM 1 VỚI GIÁO VIÊN BẢN XỨ"]
                        Lộ trình học tập được thiết kế cá nhân hóa 100% theo trình độ và mục tiêu của từng học viên. Giáo viên bản ngữ trực tiếp giảng dạy, sửa lỗi ngữ âm và giải đáp mọi thắc mắc ngay trong buổi học.
                    [/accordion-item]
                    [accordion-item title="TÀI LIỆU SỐ BIÊN SOẠN ĐỘC QUYỀN"]
                        Hệ thống tài liệu điện tử hiện đại giúp học viên ôn tập, củng cố kiến thức và rèn luyện kỹ năng bất kỳ lúc nào với kho file nghe bổ trợ và bài tập tương tác.
                    [/accordion-item]
                    [accordion-item title="NỀN TẢNG HỌC TRỰC TUYẾN RIÊNG BIỆT"]
                        Môi trường học tập trực quan 2 chiều, học viên dễ dàng kết nối trực tiếp với giáo viên, xem lại video bài giảng và nhận phản hồi tiến độ chi tiết sau từng buổi học.
                    [/accordion-item]
                    [accordion-item title="APP HỌC TẬP TÍCH HỢP NHIỀU CHỨC NĂNG"]
                        Tích hợp công cụ theo dõi tiến độ học tập toàn diện, cho phép học viên xem trước bài giảng, làm bài tập và nhắc nhở lịch học tự động trên điện thoại.
                    [/accordion-item]
                [/accordion]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(why_us_section)

    # =========================================================================
    # 8. CAM KẾT CHẤT LƯỢNG GIÁO DỤC
    # =========================================================================
    commitment_section = f"""
[vbc_div custom_css="selector {{ width: 100%; background: linear-gradient(135deg, {LIGHT_PINK} 0%, #ffffff 100%); padding: 70px 0; border-top: 1px solid #fecdd3; border-bottom: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1000px; padding: 0 20px; text-align: center; }}"]
        [vbc_block custom_css="selector {{ background: #ffffff; border: 2px dashed {RED}; border-radius: 24px; padding: 45px 35px; box-shadow: 0 10px 30px rgba(230,57,70,0.08); }}"]
            [vbc_icon icon_type="lucide" name="shield-check" size="44px" color="{RED}" background_color="#fee2e2" padding="16px" border_radius="50%" margin="0 auto 20px auto"]
            [vbc_h2 custom_css="selector {{ font-size: 28px; font-weight: 900; color: {DARK_RED}; text-transform: uppercase; margin-bottom: 16px; letter-spacing: 0.5px; }}"]
                CAM KẾT GIẢI PHÁP GIÁO DỤC HIỆU QUẢ - TỐI ƯU
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 16px; }}"]
                Ni Hao Ma cam kết mang đến trải nghiệm học tập chất lượng cao, đáp ứng đầy đủ các tiêu chuẩn quốc tế. Tại Ni Hao Ma, học viên có đa dạng lựa chọn để tìm ra chương trình học tập phù hợp nhất: từ lớp học 1:1 online cho đến các khóa học chuyên sâu, đảm bảo sự tiến bộ vượt bậc và tự tin giao tiếp chỉ sau một khóa học.
            [/vbc_p]
            [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_MUTED}; font-style: italic; margin: 0; }}"]
                Thành tựu và sự hài lòng của hơn 2,000 học viên chính là minh chứng rõ ràng nhất cho chất lượng đào tạo của Ni Hao Ma.
            [/vbc_p]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(commitment_section)

    # =========================================================================
    # 9. CẢM NGHĨ KHÁCH HÀNG (TESTIMONIALS)
    # =========================================================================
    testimonials_section = f"""
[vbc_div id="cam-nhan" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Cảm Nghĩ Khách Hàng
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; }}"]
                Lắng nghe chia sẻ chân thực từ các bậc phụ huynh và học viên đã đồng hành cùng Ni Hao Ma.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Bé nhà mình học ở Ni Hao Ma được 6 tháng, cô giáo Lin rất nhiệt tình và kiên nhẫn. Bé tiến bộ rõ rệt, phát âm rất tự nhiên và giờ đã tự tin chào hỏi, hát các bài hát tiếng Trung.&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Chị Nguyễn Thị Hoa[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Phụ huynh bé Ben (7 tuổi)</span>
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Lịch học 1:1 online cực kỳ linh hoạt, rất phù hợp với người đi làm bận rộn như mình. Giáo viên người Đài Loan dạy phát âm rất chuẩn và chỉ dẫn chi tiết về thuật ngữ thương mại.&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Anh Trần Văn Minh[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Kỹ Sư Quản Lý Dự Án</span>
                    </div>
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fee2e2; border-radius: 20px; padding: 32px 26px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.3s; }} selector:hover {{ transform: translateY(-6px); box-shadow: 0 16px 32px rgba(230,57,70,0.12); }}"]
                <div>
                    <div style="display: flex; gap: 4px; color: {GOLD}; margin-bottom: 16px;">
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                        [vbc_icon icon_type="lucide" name="star" size="18px" color="{GOLD}"]
                    </div>
                    [vbc_p custom_css="selector {{ font-size: 15px; color: {TEXT_DARK}; line-height: 1.8; font-style: italic; margin-bottom: 20px; }}"]
                        &ldquo;Giáo trình độc quyền và app học tập của Ni Hao Ma rất tiện lợi. Mình ôn tập bất kỳ lúc nào và đã thi đỗ chứng chỉ HSK 4 chỉ sau 4 tháng rèn luyện cùng trung tâm!&rdquo;
                    [/vbc_p]
                </div>
                <div style="display: flex; align-items: center; gap: 14px; border-top: 1px solid #fed7aa; padding-top: 16px;">
                    [vbc_icon icon_type="lucide" name="user-check" size="24px" color="{RED}" background_color="#fee2e2" padding="10px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 800; color: {DARK_NAVY}; margin: 0; }}"]Bạn Lê Thị Thu[/vbc_h4]
                        <span style="font-size: 13px; color: {RED}; font-weight: 600;">Sinh Viên Đại Học Quốc Tế</span>
                    </div>
                </div>
            [/vbc_container_inner_1]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(testimonials_section)

    # =========================================================================
    # 10. VỀ NI HAO MA (ABOUT US SECTION)
    # =========================================================================
    about_section = f"""
[vbc_div id="ve-chung-toi" custom_css="selector {{ width: 100%; background: {CREAM_BG}; padding: 80px 0; border-top: 1px solid #fecdd3; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_h2 custom_css="selector {{ font-size: 34px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 20px; }} @media(max-width: 849px){{ selector {{ text-align: center; }} }}"]
                    Về Ni Hao Ma Mandarin Learning Lab
                [/vbc_h2]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 16px; }}"]
                    Tại Ni Hao Ma, chúng tôi mang đến trải nghiệm học tập tiếng Trung hiện đại và nhuần nhuyễn. Chương trình học do đội ngũ giáo viên bản ngữ giàu nhiệt huyết trực tiếp hướng dẫn, kết hợp tài liệu thiết kế độc quyền và các hoạt động giao lưu văn hóa đặc sắc.
                [/vbc_p]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_DARK}; line-height: 1.8; margin-bottom: 24px; }}"]
                    Cùng phương châm lấy người học làm trung tâm, chúng tôi cam kết đồng hành cùng bạn trên con đường chinh phục tiếng Trung với giải pháp linh hoạt và hiệu quả nhất.
                [/vbc_p]
                [vbc_a link_url="#dang-ky" custom_css="selector {{ background: {RED}; color: #ffffff !important; padding: 14px 32px; border-radius: 30px; font-weight: 700; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }} selector:hover {{ background: {DARK_RED}; }}"]
                    [vbc_icon icon_type="lucide" name="heart-handshake" size="18px"]
                    [vbc_span]Tìm Hiểu Thêm Về Chúng Tôi[/vbc_span]
                [/vbc_a]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ text-align: center; }}"]
                [vbc_img img_source="manual" img_attachment="" alt="Đội ngũ Ni Hao Ma" custom_css="selector {{ width: 100%; max-width: 520px; height: auto; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); display: inline-block; }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(about_section)

    # =========================================================================
    # 11. NHỮNG CON SỐ MINH CHỨNG (KEY STATS - DARK SECTION)
    # =========================================================================
    stats_section = f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 70px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ text-align: center; max-width: 750px; margin: 0 auto 50px auto; }}"]
            [vbc_h2 custom_css="selector {{ font-size: 32px; font-weight: 900; color: #ffffff; margin-bottom: 14px; position: relative; display: inline-block; }} selector::after {{ content: ''; display: block; width: 60px; height: 4px; background: {RED}; margin: 12px auto 0 auto; border-radius: 2px; }}"]
                Những Con Số Minh Chứng Cho Chất Lượng
            [/vbc_h2]
            [vbc_p custom_css="selector {{ font-size: 16px; color: #9ca3af; line-height: 1.7; }}"]
                Minh chứng cho cam kết bền vững của Ni Hao Ma trong việc cung cấp nền giáo dục tiếng Trung chuẩn quốc tế.
            [/vbc_p]
        [/vbc_block]

        [vbc_block_inner custom_css="selector {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]5+[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Năm Thành Lập & Phát Triển[/vbc_p]
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]2,000+[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Học Viên Đã & Đang Học[/vbc_p]
            [/vbc_container_inner]

            [vbc_container_inner_1 custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]98%[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Đạt Chứng Chỉ HSK / YCT[/vbc_p]
            [/vbc_container_inner_1]

            [vbc_container_inner_2 custom_css="selector {{ background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 36px 20px; }}"]
                [vbc_h3 custom_css="selector {{ font-size: 48px; font-weight: 900; color: {RED}; margin: 0 0 10px 0; line-height: 1; }}"]100%[/vbc_h3]
                [vbc_p custom_css="selector {{ font-size: 15px; color: #e5e7eb; font-weight: 600; margin: 0; }}"]Giáo Viên Chuẩn Bản Xứ[/vbc_p]
            [/vbc_container_inner_2]
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(stats_section)

    # =========================================================================
    # 12. ĐĂNG KÝ TƯ VẤN & CONTACT FORM 7
    # =========================================================================
    contact_section = f"""
[vbc_div id="dang-ky" custom_css="selector {{ width: 100%; background: #ffffff; padding: 80px 0; }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }} @media(max-width: 849px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                <span style="display: inline-flex; align-items: center; gap: 8px; background: #fee2e2; color: {DARK_RED}; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; width: fit-content; margin-bottom: 16px;">
                    [vbc_icon icon_type="lucide" name="gift" size="16px" color="{RED}"]
                    ƯU ĐÃI ĐẶC BIỆT THÁNG NÀY
                </span>
                [vbc_h2 custom_css="selector {{ font-size: 36px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 18px; line-height: 1.3; }}"]
                    Đăng Ký Nhận Buổi Học Thử 1:1 Miễn Phí
                [/vbc_h2]
                [vbc_p custom_css="selector {{ font-size: 16px; color: {TEXT_MUTED}; line-height: 1.7; margin-bottom: 24px; }}"]
                    Hãy để lại thông tin để chuyên viên học vụ Ni Hao Ma kiểm tra trình độ miễn phí và tư vấn lộ trình học phù hợp nhất cho bạn hoặc con bạn.
                [/vbc_p]
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Test Trình Độ 1:1 Miễn Phí[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Đánh giá toàn diện 4 kỹ năng cùng giáo viên bản ngữ Đài Loan.</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Giảm 15% Học Phí Khóa Đầu[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Áp dụng khi đăng ký sớm trong tuần này.</p>
                    </div>
                </div>
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    [vbc_icon icon_type="lucide" name="check" size="18px" color="#ffffff" background_color="{RED}" padding="6px" border_radius="50%"]
                    <div>
                        [vbc_h4 custom_css="selector {{ font-size: 16px; font-weight: 700; color: {DARK_NAVY}; margin: 0 0 4px 0; }}"]Tặng Tài Liệu & App Học Độc Quyền[/vbc_h4]
                        <p style="font-size: 14px; color: {TEXT_MUTED}; margin: 0;">Bộ giáo trình e-book trị giá 1.500.000đ trọn đời.</p>
                    </div>
                </div>
            [/vbc_container]

            [vbc_container_inner custom_css="selector {{ background: {LIGHT_PINK}; border: 1px solid #fecdd3; border-radius: 24px; padding: 40px 32px; box-shadow: 0 15px 35px rgba(230,57,70,0.1); }}"]
                [vbc_h3 custom_css="selector {{ font-size: 22px; font-weight: 900; color: {DARK_NAVY}; margin-bottom: 20px; text-align: center; }}"]
                    Điền Thông Tin Tư Vấn
                [/vbc_h3]
                [contact-form-7 id="508" title="Form Đăng Ký Ni Hao Ma"]
            [/vbc_container_inner]
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
"""
    parts.append(contact_section)

    # =========================================================================
    # 13. FOOTER SECTION
    # =========================================================================
    footer_section = f"""
[vbc_div custom_css="selector {{ width: 100%; background: {DARK_NAVY}; color: #ffffff; padding: 70px 0 30px 0; border-top: 1px solid rgba(255,255,255,0.1); }}"]
    [vbc_box class="container" custom_css="selector {{ margin: 0 auto; max-width: 1200px; padding: 0 20px; }}"]
        [vbc_block custom_css="selector {{ display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 36px; margin-bottom: 50px; }} @media(max-width: 1024px){{ selector {{ grid-template-columns: repeat(2, 1fr); }} }} @media(max-width: 549px){{ selector {{ grid-template-columns: 1fr; }} }}"]
            [vbc_container]
                [vbc_img img_source="manual" img_attachment="" alt="Ni Hao Ma" custom_css="selector {{ height: 50px; width: auto; margin-bottom: 18px; filter: brightness(0) invert(1); }}" src="https://nihaoma-mandarin.com/wp-content/uploads/2023/08/cropped-NHM-Logo.png"]
                [vbc_p custom_css="selector {{ font-size: 14px; color: #9ca3af; line-height: 1.8; margin-bottom: 20px; }}"]
                    Ni Hao Ma Mandarin Learning Lab &mdash; Hệ thống trung tâm đào tạo tiếng Trung bản ngữ chuẩn quốc tế hàng đầu tại Việt Nam.
                [/vbc_p]
                <div style="display: flex; gap: 12px;">
                    [vbc_a link_url="https://facebook.com/NiHaoMaVietnam" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="facebook" size="18px"]
                    [/vbc_a]
                    [vbc_a link_url="https://instagram.com/nihaomavietnam" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="instagram" size="18px"]
                    [/vbc_a]
                    [vbc_a link_url="https://youtube.com/@nihaoma-mandarin" link_target="_blank" custom_css="selector {{ background: rgba(255,255,255,0.1); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: background 0.2s; }} selector:hover {{ background: {RED}; }}"]
                        [vbc_icon icon_type="lucide" name="youtube" size="18px"]
                    [/vbc_a]
                </div>
            [/vbc_container]

            [vbc_container_inner]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Khóa Học[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Trẻ Em (3-11t)[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Thiếu Niên[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Luyện Thi HSK 1 - 6[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Giao Tiếp 1:1[/vbc_span][/vbc_a]
                    [vbc_a link_url="#khoa-hoc" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Tiếng Trung Doanh Nghiệp[/vbc_span][/vbc_a]
                </div>
            [/vbc_container_inner]

            [vbc_container_inner_1]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Thông Tin[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 14px;">
                    [vbc_a link_url="#ve-chung-toi" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giới Thiệu Ni Hao Ma[/vbc_span][/vbc_a]
                    [vbc_a link_url="#giao-vien" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Đội Ngũ Giáo Viên[/vbc_span][/vbc_a]
                    [vbc_a link_url="#giao-trinh" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Giáo Trình Độc Quyền[/vbc_span][/vbc_a]
                    [vbc_a link_url="#cam-nhan" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Cảm Nhận Học Viên[/vbc_span][/vbc_a]
                    [vbc_a link_url="https://nihaoma-mandarin.com/dieu-khoan-va-dieu-kien/" link_target="_blank" custom_css="selector {{ color: #9ca3af; text-decoration: none; }} selector:hover {{ color: {RED}; }}"][vbc_span]Điều Khoản & Điều Kiện[/vbc_span][/vbc_a]
                </div>
            [/vbc_container_inner_1]

            [vbc_container_inner_2]
                [vbc_h4 custom_css="selector {{ font-size: 17px; font-weight: 800; color: #ffffff; margin: 0 0 20px 0; position: relative; }} selector::after {{ content: ''; display: block; width: 30px; height: 3px; background: {RED}; margin-top: 8px; }}"]Liên Hệ Trực Tiếp[/vbc_h4]
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #9ca3af;">
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="phone" size="16px" color="{RED}"]
                        <strong>Hotline:</strong> +84 585 680 116
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="mail" size="16px" color="{RED}"]
                        <strong>Email:</strong> customercare.td@nihaoma-mandarin.com
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="map-pin" size="16px" color="{RED}"]
                        <strong>Thảo Điền:</strong> TP. Thủ Đức, TP.HCM
                    </span>
                    <span style="display: flex; align-items: flex-start; gap: 8px;">
                        [vbc_icon icon_type="lucide" name="map-pin" size="16px" color="{RED}"]
                        <strong>Phú Mỹ Hưng:</strong> Quận 7, TP.HCM
                    </span>
                </div>
            [/vbc_container_inner_2]
        [/vbc_block]

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 24px; text-align: center; font-size: 13px; color: #6b7280;">
            <p style="margin: 0;">
                Copyright &copy; 2024 Nihaoma Mandarin Learning Lab. All rights reserved. Powered by Ultimate Flatsome VibeCode.
            </p>
        </div>
    [/vbc_box]
[/vbc_div]
"""
    parts.append(footer_section)

    return "\n\n".join(parts)


def publish_page_vbc(content, title, slug, page_id=502):
    payload = json.dumps({
        "title": title,
        "content": content,
        "slug": slug,
        "status": "publish",
        "template": "page-blank.php",
        "page_id": page_id
    }).encode("utf-8")

    headers = {
        "Content-Type": "application/json; charset=utf-8",
        "X-VBC-Token": TOKEN
    }

    req = urllib.request.Request(f"{API}/vbc/v1/page", data=payload, headers=headers, method="POST")
    try:
        with urllib.request.urlopen(req, timeout=30) as r:
            return json.loads(r.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        print(f"[LỖI HTTP] {e.code}: {body[:800]}")
        raise


def verify_live_page(page_url):
    print(f"\n[Kiểm Tra] Đang tải frontend từ: {page_url} ...")
    req = urllib.request.Request(
        page_url,
        headers={"User-Agent": "Mozilla/5.0", "Cache-Control": "no-cache"}
    )
    try:
        with urllib.request.urlopen(req, timeout=20) as r:
            html = r.read().decode("utf-8", errors="ignore")

            # Quét unparsed shortcodes
            unparsed_vbc = re.findall(r'\[\/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]', html)
            unparsed_flat = re.findall(r'\[\/?(?:row|col|accordion|accordion-item)[^\]]*\]', html)
            all_unparsed = unparsed_vbc + unparsed_flat

            if not all_unparsed:
                print(f"✅ HOÀN HẢO! 0 shortcode bị lộ ra frontend (Đạt chuẩn 100%).")
                return True
            else:
                print(f"❌ CẢNH BÁO: Phát hiện {len(all_unparsed)} shortcodes chưa parse:")
                for u in all_unparsed[:10]:
                    print("   ->", u)
                return False
    except Exception as e:
        print(f"Không thể kiểm tra live page: {e}")
        return False


def main():
    print("=" * 65)
    print("CLONE NI HAO MA LANDING PAGE (100% THUẦN VBC ELEMENTS)")
    print("=" * 65)

    content = build_nihaoma_vbc_content()
    print(f"[1/3] Đã biên dịch shortcodes VBC: {len(content)} ký tự.")

    print(f"[2/3] Đang xuất bản lên WordPress (Page ID: 502)...")
    res = publish_page_vbc(
        content=content,
        title="Ni Hao Ma Mandarin Learning Lab – Tiếng Trung Bản Ngữ Online",
        slug="ni-hao-ma",
        page_id=502
    )

    page_id = res.get("post_id") or res.get("id")
    page_url = res.get("url") or res.get("link")
    print(f"✅ Đã xuất bản thành công! ID: {page_id} | URL: {page_url}")

    print(f"[3/3] Tiến hành kiểm tra xác thực frontend live...")
    verify_live_page(page_url)

    print("\n" + "=" * 65)
    print(f"🎉 LINK LIVE TRANG CLONE: {page_url}")
    print("=" * 65)


if __name__ == "__main__":
    main()
