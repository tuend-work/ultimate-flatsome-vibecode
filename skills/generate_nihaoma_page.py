#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
generate_nihaoma_page.py
Clone: https://nihaoma-mandarin.com/vi/trang-chu/
Dung kien truc Flatsome-native 2-pass voi vbc_section wrapper.
"""
import os, sys, json, urllib.request, urllib.error

if sys.platform == "win32":
    try:
        sys.stdout.reconfigure(encoding="utf-8")
        sys.stderr.reconfigure(encoding="utf-8")
    except: pass

cfg_path = os.path.join(os.path.dirname(__file__), "..", "ultimate-flatsome-vibecode", "vbc-config.json")
with open(cfg_path, "r", encoding="utf-8") as f:
    cfg = json.load(f)
API   = cfg["api-url"].rstrip("/")
TOKEN = cfg["token"]

sys.path.insert(0, os.path.dirname(__file__))
from flatsome_elements import (
    FlatSection, compile_page, esc,
    make_title, make_divider, make_button, make_gap,
    make_row, make_col, make_ux_banner, make_text_box,
    make_ux_image_box, make_featured_box, make_testimonial,
    make_accordion, section_header
)

# ── Color palette (from nihaoma source) ──────────────────────────────────────
RED     = "#e84c4c"
PINK_BG = "#fef5f5"
DARK    = "#1a1a2e"
WHITE   = "#ffffff"
YELLOW  = "#f0c040"


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 1: HERO BANNER
# ─────────────────────────────────────────────────────────────────────────────
def s1_hero():
    sec = FlatSection("sec-hero", section_class="sec-hero", bg_color=PINK_BG, padding="40px")

    tb = make_text_box(
        content=(
            '[title text="KHAI GIANG LIEN TUC" tag_name="h1" style="normal"]'
            "\n<p>Lop hoc Online 1:1 &mdash; Thoi luong linh hoat</p>"
            "\n" + make_button("Dang ky ngay", "#", color="alert", size="large")
        ),
        position_x="50", position_y="50", width="60",
        text_color="dark", text_align="left"
    )
    banner = make_ux_banner(
        bg_url="https://nihaoma-mandarin.com/wp-content/uploads/2025/03/Banner-Web.png",
        height="520px", height_sm="360px",
        bg_overlay="rgba(254,245,245,0.5)", bg_pos="right center",
        content=tb
    )
    sec.add(banner)

    sec.add_css("", f"background: {PINK_BG};")
    sec.add_css(" h1 .section-title-main",
                f"font-size:46px; font-weight:900; color:{RED}; text-transform:uppercase; line-height:1.2;")
    sec.add_css(" .banner-layer p", "font-size:18px; color:#333; margin:12px 0 20px;")
    sec.add_css(f" .button.alert",
                f"background:{RED}; color:#fff; border-radius:30px; padding:14px 38px; font-size:16px; font-weight:700;")
    sec.add_css(f" .button.alert:hover", "opacity:0.88; transform:translateY(-2px);")
    sec.add_css("@media (max-width:768px)", "")
    sec.add_css(" h1 .section-title-main @media (max-width:768px)", "font-size:28px;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 2: GIAI PHAP LINH HOAT (4 icon features)
# ─────────────────────────────────────────────────────────────────────────────
def s2_solutions():
    sec = FlatSection("sec-solutions", section_class="sec-solutions", bg_color=WHITE, padding="60px")
    sec.add(section_header("Giai phap linh hoat va hieu qua", divider_color=RED))
    sec.add(make_gap("30px"))

    feats = [
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Curriculum-Nihaoma.png",
         "Hoc Online 1:1",
         "Lo trinh hoc tap ca nhan hoa, giao vien ban ngu truc tiep giang day va giai dap cho tung hoc vien."),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Modern-Nihaoma.png",
         "Tai lieu so doc quyen",
         "Tai lieu dien tu giup hoc vien on tap, cung co kien thuc va ren luyen ky nang bat ky dau voi file nghe bo tro."),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Age-group-Nihaoma.png",
         "Nen tang hoc truc tuyen rieng",
         "Moi truong hoc tap tuong tac 2 chieu, hoc vien de dang ket noi truc tiep voi giao vien de duoc ho tro ngay."),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg",
         "App hoc tap tich hop",
         "Tich hop cong cu theo doi tien do hoc tap, cho phep hoc vien xem truoc bai giang va on tap sau buoi hoc."),
    ]
    cols = []
    for img, title, desc in feats:
        fb = make_featured_box(img_url=img, title=title,
                               content=f"<p>{esc(desc)}</p>", pos="top", img_width="70")
        cols.append(make_col(fb, span="3", span_sm="6", span_md="6"))
    sec.add(make_row("\n".join(cols), gap="20px"))

    sec.add_css(" .section-title", f"font-size:28px; font-weight:800; color:{DARK};")
    sec.add_css(" .featured-box", "text-align:center; padding:28px 16px; border-radius:12px; transition:box-shadow 0.3s;")
    sec.add_css(" .featured-box:hover", f"box-shadow:0 8px 24px rgba(232,76,76,0.12);")
    sec.add_css(" .icon-box-img", "width:70px; margin:0 auto 16px;")
    sec.add_css(" .icon-box-text h5", f"font-size:16px; font-weight:700; color:{DARK}; text-transform:none; margin-bottom:8px;")
    sec.add_css(" .icon-box-text p", "font-size:14px; color:#64748b; line-height:1.7;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 3: GIAO VIEN
# ─────────────────────────────────────────────────────────────────────────────
def s3_teachers():
    sec = FlatSection("sec-teachers", section_class="sec-teachers", bg_color=PINK_BG, padding="60px")
    sec.add(section_header("Giao vien cua Chung toi", divider_color=RED))
    sec.add(make_gap("30px"))

    teachers = [
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-1.png",
         "Giao vien Dai Loan", "100% ban ngu, chung chi giang day quoc te TCSL/TESOL"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-2.png",
         "Phuong phap hien dai", "Giang day theo chuan quoc te HSK va YCT"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-4.png",
         "Kinh nghiem day dan", "5+ nam giang day tieng Trung cho nguoi Viet"),
    ]
    cols = []
    for img, title, desc in teachers:
        ib = make_ux_image_box(img_url=img, title=title,
                               content=f"<p>{esc(desc)}</p>",
                               image_hover="zoom", text_pos="bottom", text_align="center")
        cols.append(make_col(ib, span="4", span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(cols), gap="24px"))

    sec.add_css(" .box", "border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 4px 16px rgba(0,0,0,0.06); transition:transform 0.3s;")
    sec.add_css(" .box:hover", "transform:translateY(-4px);")
    sec.add_css(" .box-image img", "height:260px; object-fit:cover; width:100%;")
    sec.add_css(" .box-text", "padding:20px;")
    sec.add_css(" .box-text h3", f"font-size:17px; font-weight:700; color:{DARK};")
    sec.add_css(" .box-text p", "font-size:14px; color:#64748b;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 4: GIAO TRINH (6 courses)
# ─────────────────────────────────────────────────────────────────────────────
def s4_curriculum():
    sec = FlatSection("sec-curriculum", section_class="sec-curriculum", bg_color=WHITE, padding="60px")
    sec.add(section_header("Giao Trinh", divider_color=RED))
    sec.add(make_gap("30px"))

    courses = [
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Curriculum-Nihaoma.png",
         "Tieng Trung YCT",
         "Xay dung nen tang tieng trung co ban cho 4 ky nang. Hoc theo chu de, xen ke cac hoat dong va tro choi giup be de ghi nho hon.",
         "https://nihaoma-mandarin.com/vi/yct-cho-tre-em/"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Modern-Nihaoma.png",
         "Tieng Trung HSK",
         "Trang bi cho hoc vien cac kien thuc can thiet de du hoc va lam viec, bao gom hon 1000+ tu vung, 300+ chu han va 20+ diem ngu phap.",
         "https://nihaoma-mandarin.com/vi/tieng-trung-hsk/"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg",
         "Tieng Trung Giao Tiep",
         "Tang phan xa va tu tin su dung tieng Trung. Hoc vien co the giao tiep luu loat cac chu de hang ngay va cong viec.",
         "https://nihaoma-mandarin.com/vi/tieng-trung-giao-tiep/"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Certified-Native-Teacher-Nihaoma-4.jpg",
         "Tieng Trung Thuong Mai",
         "Tap trung nang cao ky nang nghe noi. Hoc vien se nam vung cac tu vung chuyen nganh de giao tiep voi doi tac, khach hang.",
         "https://nihaoma-mandarin.com/vi/tieng-trung-thuong-mai/"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-5.png",
         "Tre Em (3-11 tuoi)",
         "Tiep xuc som voi ngon ngu giup tre co nen tang vung chac tu nho. Mang lai loi the lon khi muon thanh thao ngon ngu trong tuong lai.",
         "https://nihaoma-mandarin.com/vi/tieng-trung-tre-em/"),
        ("https://nihaoma-mandarin.com/wp-content/uploads/2024/11/hinh-giao-vien-6.png",
         "Nguoi Di Lam & Nguoi Lon",
         "Tap trung giao tiep tro chay, nang cao co hoi nghe nghiep va phat trien ban than. Tao loi the canh tranh tren thi truong rong lon.",
         "https://nihaoma-mandarin.com/vi/tieng-trung-nguoi-di-lam/"),
    ]
    cols = []
    for img, title, desc, link in courses:
        ib = make_ux_image_box(img_url=img, title=title,
                               content=f"<p>{esc(desc)}</p>\n{make_button('Xem them', link, color='alert', size='small', style='outline')}",
                               link=link, image_hover="zoom", text_pos="bottom", text_align="left")
        cols.append(make_col(ib, span="4", span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(cols), gap="24px"))

    sec.add_css(" .box", f"border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.07); background:#fff; transition:transform 0.3s;")
    sec.add_css(" .box:hover", f"transform:translateY(-4px); box-shadow:0 10px 30px rgba(232,76,76,0.12);")
    sec.add_css(" .box-image img", "height:180px; object-fit:cover; width:100%;")
    sec.add_css(" .box-text", "padding:20px;")
    sec.add_css(" .box-text h3", f"font-size:17px; font-weight:700; color:{DARK}; margin-bottom:10px;")
    sec.add_css(" .box-text p", "font-size:14px; color:#64748b; line-height:1.7; margin-bottom:16px;")
    sec.add_css(" .button.is-outline", f"border:2px solid {RED}; color:{RED}; border-radius:20px; padding:8px 20px; font-size:13px;")
    sec.add_css(" .button.is-outline:hover", f"background:{RED}; color:#fff;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 5: CHO MOI NGUOI
# ─────────────────────────────────────────────────────────────────────────────
def s5_audience():
    sec = FlatSection("sec-audience", section_class="sec-audience", bg_color=PINK_BG, padding="60px")

    left = (
        make_title("Cho Moi Nguoi", tag="h2", style="normal", color=RED) + "\n" +
        make_divider(width="60px", color=RED) + "\n" +
        make_gap("16px") + "\n" +
        "<p>Ni Hao Ma phuc vu moi doi tuong tu tre em den nguoi di lam, tu nguoi moi bat dau den nguoi thi chung chi quoc te.</p>\n"
        "<ul style='list-style:none;padding:0;'>"
        "<li style='padding:8px 0;'>&check; <strong>Tre Em 3-11 tuoi</strong> &mdash; Tieng Trung nen tang</li>"
        "<li style='padding:8px 0;'>&check; <strong>Thieu Nien 12-17 tuoi</strong> &mdash; Luyen thi chung chi</li>"
        "<li style='padding:8px 0;'>&check; <strong>Nguoi Di Lam</strong> &mdash; Giao tiep, thuong mai</li>"
        "<li style='padding:8px 0;'>&check; <strong>Hoc Online hoac Offline</strong> &mdash; Linh hoat 100%</li>"
        "</ul>\n" +
        make_gap("16px") + "\n" +
        make_button("Tim khoa hoc phu hop", "https://nihaoma-mandarin.com/vi/tieng-trung-online/", color="alert", size="large")
    )
    right = "[ux_image img='https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Age-group-Nihaoma.png' image_size='large' width='100']"

    sec.add(make_row(
        make_col(left, span="6", span_sm="12") + "\n" + make_col(right, span="6", span_sm="12"),
        v_align="middle", gap="40px"
    ))

    sec.add_css(" h2 .section-title-main", f"font-size:36px; font-weight:900; color:{RED}; line-height:1.2;")
    sec.add_css(" p", "font-size:15px; color:#555; line-height:1.7;")
    sec.add_css(" li", "font-size:15px; color:#333;")
    sec.add_css(f" .button.alert", f"background:{RED}; color:#fff; border-radius:30px; padding:14px 32px; font-weight:700;")
    sec.add_css(f" .button.alert:hover", "opacity:0.88; transform:translateY(-2px);")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 6: TAI SAO CHON CHUNG TOI
# ─────────────────────────────────────────────────────────────────────────────
def s6_why_us():
    sec = FlatSection("sec-whyus", section_class="sec-whyus", bg_color=WHITE, padding="60px")

    left_img = "[ux_image img='https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Curriculum-Nihaoma.png' image_size='large' width='100']"

    right_content = (
        make_title("TAI SAO CHON CHUNG TOI?", tag="h2", style="normal", color=RED) + "\n" +
        make_divider(width="60px", color=RED) + "\n" +
        make_gap("20px") + "\n" +
        make_featured_box(
            img_url="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Certified-Native-Teacher-Nihaoma-4.jpg",
            title="HOC 1 KEM 1 VOI GIAO VIEN",
            content="<p>Lo trinh hoc tap thiet ke ca nhan hoa, giao vien ban ngu truc tiep giang day va giai dap cho tung hoc vien.</p>",
            pos="left", img_width="56"
        ) + "\n" +
        make_featured_box(
            img_url="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Modern-Nihaoma.png",
            title="TAI LIEU SO BIEN SOAN DOC QUYEN",
            content="<p>Tai lieu dien tu giup hoc vien on tap, cung co kien thuc va ren luyen ky nang bat ky dau voi file nghe bo tro.</p>",
            pos="left", img_width="56"
        ) + "\n" +
        make_featured_box(
            img_url="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg",
            title="NEN TANG HOC TRUC TUYEN RIENG BIET",
            content="<p>Moi truong hoc tap tuong tac 2 chieu, hoc vien de dang ket noi truc tiep voi giao vien de duoc ho tro ngay.</p>",
            pos="left", img_width="56"
        ) + "\n" +
        make_featured_box(
            img_url="https://nihaoma-mandarin.com/wp-content/uploads/2024/01/Age-group-Nihaoma.png",
            title="APP HOC TAP TICH HOP NHIEU CHUC NANG",
            content="<p>Tich hop cong cu theo doi tien do hoc tap, cho phep hoc vien xem truoc bai giang va on tap sau buoi hoc.</p>",
            pos="left", img_width="56"
        )
    )

    sec.add(make_row(
        make_col(left_img, span="5", span_sm="12") + "\n" + make_col(right_content, span="7", span_sm="12"),
        v_align="middle", gap="40px"
    ))

    sec.add_css(" h2 .section-title-main", f"font-size:28px; font-weight:900; color:{RED}; text-transform:uppercase;")
    sec.add_css(" .featured-box", "margin-bottom:22px; padding:0;")
    sec.add_css(" .icon-box-img", "min-width:48px; flex-shrink:0;")
    sec.add_css(" .icon-box-text h5", f"font-size:14px; font-weight:700; color:{DARK}; text-transform:uppercase; margin-bottom:6px;")
    sec.add_css(" .icon-box-text p", "font-size:14px; color:#64748b; line-height:1.7; margin:0;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 7: CAM KET + STATS
# ─────────────────────────────────────────────────────────────────────────────
def s7_commitment():
    sec = FlatSection("sec-commit", section_class="sec-commit", bg_color=PINK_BG, padding="60px")
    sec.add(make_title("CAM KET GIAI PHAP GIAO DUC HIEU QUA - TOI UU", tag="h2", style="bold-center", color=RED))
    sec.add(make_divider(width="80px", color=RED, align="center"))
    sec.add(make_gap("20px"))
    sec.add(
        make_row(
            make_col(
                "<p style='text-align:center;font-size:15px;color:#555;line-height:1.8;max-width:820px;margin:0 auto 30px;'>"
                "Ni Hao Ma cam ket mang den trai nghiem hoc tap chat luong cao, dap ung nhung tieu chuan quoc te. "
                "Tai Ni Hao Ma, hoc vien co da dang lua chon tu cac lop hoc nhom cho den cac lop day kem 1:1, "
                "dam bao trai nghiem hoc linh hoat va hieu qua."
                "</p>",
                span="12"
            ),
            gap=""
        )
    )

    stats = [
        ("5+",    "Nam thanh lap"),
        ("2000+", "Hoc vien duoc dao tao"),
        ("0%",    "Ty le bo hoc giua chung"),
        ("100%",  "Giao vien ban ngu"),
    ]
    cols = []
    for num, lbl in stats:
        cols.append(
            make_col(
                f"<div class='nhm-stat'><span class='nhm-num'>{num}</span><span class='nhm-lbl'>{esc(lbl)}</span></div>",
                span="3", span_sm="6"
            )
        )
    sec.add(make_row("\n".join(cols), gap="20px"))

    sec.add_css(" .section-title", f"font-size:26px; color:{RED};")
    sec.add_css(" .nhm-stat", "text-align:center; padding:30px 20px; background:#fff; border-radius:16px; box-shadow:0 4px 16px rgba(232,76,76,0.08); display:flex; flex-direction:column; align-items:center;")
    sec.add_css(" .nhm-num", f"font-size:52px; font-weight:900; color:{RED}; line-height:1; margin-bottom:8px; display:block;")
    sec.add_css(" .nhm-lbl", "font-size:14px; color:#64748b; font-weight:600; display:block;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 8: CAM NGHI KHACH HANG
# ─────────────────────────────────────────────────────────────────────────────
def s8_testimonials():
    sec = FlatSection("sec-reviews", section_class="sec-reviews", bg_color=WHITE, padding="60px")
    sec.add(section_header("Cam nghi khach hang", divider_color=RED))
    sec.add(make_gap("30px"))

    reviews = [
        ("Nguyen Thi Hoa", "Phu huynh hoc vien", "5",
         "Con toi hoc o Ni Hao Ma duoc 6 thang, giao vien rat nhiet tinh va phuong phap day rat thu vi. Con tien bo ro ret, gio da biet giao tiep co ban bang tieng Trung."),
        ("Tran Van Minh", "Ky su, hoc vien nguoi di lam", "5",
         "Lich hoc rat linh hoat, phu hop voi nguoi ban ron nhu toi. Giao vien nguoi ban xu day rat chuan, toi tien bo nhanh hon so voi cac trung tam khac tung hoc."),
        ("Le Thi Thu", "Sinh vien nam 3", "5",
         "Tai lieu cua Ni Hao Ma rat hay, co app ho tro on bai moi luc moi noi. Chi sau 3 thang toi da dau HSK 2 voi diem cao."),
    ]
    cols = []
    for name, co, stars, txt in reviews:
        tst = make_testimonial(
            content=f"<p>&ldquo;{esc(txt)}&rdquo;</p>",
            name=name, company=co, stars=stars, pos="top"
        )
        cols.append(make_col(tst, span="4", span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(cols), gap="24px"))

    sec.add_css(" .testimonial-box", f"background:{PINK_BG}; border-radius:16px; padding:28px; border:1px solid #fce4e4; transition:transform 0.3s;")
    sec.add_css(" .testimonial-box:hover", "transform:translateY(-3px);")
    sec.add_css(" .testimonial-text", "font-size:14px; color:#555; line-height:1.8; font-style:italic; margin-bottom:16px;")
    sec.add_css(" .testimonial-name", f"font-weight:700; color:{DARK};")
    sec.add_css(" .testimonial-company", f"font-size:13px; color:{RED};")
    sec.add_css(" .star-rating span", f"background:{YELLOW};")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 9: VE NI HAO MA
# ─────────────────────────────────────────────────────────────────────────────
def s9_about():
    sec = FlatSection("sec-about", section_class="sec-about", bg_color=PINK_BG, padding="60px")

    left = (
        make_title("Ve Ni Hao Ma", tag="h2", style="normal", color=RED) + "\n" +
        make_divider(width="60px", color=RED) + "\n" +
        make_gap("16px") + "\n" +
        "<p>Ni Hao Ma Mandarin Learning Lab la trung tam tieng Trung chuyen biet voi doi ngu giao vien 100% nguoi ban xu Dai Loan.</p>\n"
        "<p>Chung toi cam ket mang den giai phap hoc tieng Trung toan dien: tu lop hoc 1:1 online cho den cac chuong trinh trai he Dai Loan, phu hop voi moi lua tuoi.</p>\n" +
        make_gap("16px") + "\n" +
        make_button("Tim hieu them ve chung toi",
                    "https://nihaoma-mandarin.com/vi/gioi-thieu/",
                    color="alert", size="large")
    )
    right = "[ux_image img='https://nihaoma-mandarin.com/wp-content/uploads/2024/01/About-Us-Nihaoma.jpg' image_size='large' width='100' image_radius='16']"

    sec.add(make_row(
        make_col(left, span="6", span_sm="12") + "\n" + make_col(right, span="6", span_sm="12"),
        v_align="middle", gap="40px"
    ))

    sec.add_css(" h2 .section-title-main", f"font-size:32px; font-weight:900; color:{RED};")
    sec.add_css(" p", "font-size:15px; color:#555; line-height:1.8; margin-bottom:14px;")
    sec.add_css(f" .button.alert", f"background:{RED}; color:#fff; border-radius:30px; padding:14px 32px; font-weight:700;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# SECTION 10: NHUNG CON SO (dark bg)
# ─────────────────────────────────────────────────────────────────────────────
def s10_stats():
    sec = FlatSection("sec-stats", section_class="sec-stats", bg_color=DARK, padding="50px")
    sec.add(make_title("Nhung con so minh chung cho chat luong", tag="h2", style="bold-center", color=WHITE))
    sec.add(make_divider(width="60px", color=RED, align="center"))
    sec.add(make_gap("30px"))

    stats = [("5+","Nam thanh lap"),("2000+","Hoc vien"),("0%","Bo hoc giua chung"),("0%","Khong hai long")]
    cols = []
    for num, lbl in stats:
        cols.append(make_col(
            f"<div class='dk-stat'><span class='dk-num'>{num}</span><span class='dk-lbl'>{esc(lbl)}</span></div>",
            span="3", span_sm="6"
        ))
    sec.add(make_row("\n".join(cols), gap="20px"))

    sec.add_css("", f"background:{DARK};")
    sec.add_css(" .section-title", "color:#ffffff;")
    sec.add_css(" .dk-stat", "text-align:center; display:flex; flex-direction:column; align-items:center; padding:20px 10px;")
    sec.add_css(" .dk-num", f"font-size:54px; font-weight:900; color:{RED}; display:block; line-height:1;")
    sec.add_css(" .dk-lbl", "font-size:14px; color:#94a3b8; margin-top:8px; display:block; font-weight:600;")
    return sec


# ─────────────────────────────────────────────────────────────────────────────
# PUBLISH
# ─────────────────────────────────────────────────────────────────────────────
def publish_page(content, title, slug, page_id=None):
    """Dang/cap nhat trang qua endpoint /vbc/v1/page voi X-VBC-Token"""
    payload = json.dumps({
        "title":    title,
        "content":  content,
        "slug":     slug,
        "status":   "publish",
        "template": "page-blank.php",
        "page_id":  page_id,
    }).encode("utf-8")
    headers = {
        "Content-Type": "application/json; charset=utf-8",
        "X-VBC-Token":  TOKEN,
    }
    req = urllib.request.Request(
        f"{API}/vbc/v1/page",
        data=payload, headers=headers, method="POST"
    )
    try:
        with urllib.request.urlopen(req, timeout=30) as r:
            return json.loads(r.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace")
        print(f"[LOI HTTP] {e.code}: {body[:800]}")
        raise


def main():
    print("="*60)
    print("CLONE: nihaoma-mandarin.com -> WordPress")
    print("="*60)

    sections = [
        s1_hero(),
        s2_solutions(),
        s3_teachers(),
        s4_curriculum(),
        s5_audience(),
        s6_why_us(),
        s7_commitment(),
        s8_testimonials(),
        s9_about(),
        s10_stats(),
    ]

    content = compile_page(sections)
    print(f"\n[OK] Compiled {len(sections)} sections, {len(content)} chars")

    print("\n[2/3] Publishing to WordPress...")
    result = publish_page(
        content=content,
        title="Ni Hao Ma - Trung Tam Tieng Trung Ban Ngu Online",
        slug="ni-hao-ma"
    )
    print("[DEBUG] Response:", json.dumps(result, ensure_ascii=False)[:300])
    page_id   = result.get("post_id") or result.get("id")
    page_link = result.get("url") or result.get("link", "")
    print(f"[OK] Published! ID={page_id}")
    print(f"[OK] URL: {page_link}")

    if page_link:
        print("\n[3/3] Running recheck-url...")
        recheck = os.path.join(os.path.dirname(__file__), "recheck-url.py")
        if os.path.exists(recheck) and page_id:
            os.system(f'python "{recheck}" --url "{page_link}" --post_id {page_id}')

    return page_id, page_link


if __name__ == "__main__":
    main()
