# CẨM NANG TOÀN DIỆN VBC ELEMENTS DÀNH CHO AI AGENT
## Ultimate Flatsome VibeCode Elements (v2.3.2 Specification)

> **Tài liệu chuẩn hóa dành riêng cho AI Agents (Antigravity, Cloner, Generator, Linter, LLM Prompting).**
> Cập nhật hệ thống **Thuộc Tính Nhập Trực Tiếp (First-Class Shortcode Inputs)** hỗ trợ Responsive hoàn hảo trên UX Builder, giảm thiểu tối đa việc phải viết CSS thô vào `custom_css`.

---

## 📑 MỤC LỤC
1. [Nguyên Tắc Cốt Lõi: Ưu Tiên Thuộc Tính Trực Tiếp](#1-nguyên-tắc-cốt-lõi-ưu-tiên-thuộc-tính-trực-tiếp)
2. [Bảng Tra Cứu 30+ Thuộc Tính Nhập Trực Tiếp & Responsive](#2-bảng-tra-cứu-30-thuộc-tính-nhập-trực-tiếp--responsive)
3. [Nhóm 1: Khung Bố Cục & Cấu Trúc (Layout & Containers)](#nhóm-1-khung-bố-cục--cấu-trúc-layout--containers)
4. [Nhóm 2: Kiểu Chữ & Văn Bản (Typography)](#nhóm-2-kiểu-chữ--văn-bản-typography)
5. [Nhóm 3: Danh Sách & Bảng Biểu (Lists & Tables)](#nhóm-3-danh-sách--bảng-biểu-lists--tables)
6. [Nhóm 4: Phần Tử Tự Đóng (Void Elements)](#nhóm-4-phần-tử-tự-đóng-void-elements)
7. [Nhóm 5: UI Components Cao Cấp](#nhóm-5-ui-components-cao-cấp)
8. [Nhóm 6: Hệ Thống Icon Vector 5 Bộ](#nhóm-6-hệ-thống-icon-vector-5-bộ)
9. [Nhóm 7: Truy Vấn Động & Tích Hợp Backend (Dynamic Data)](#nhóm-7-truy-vấn-động--tích-hợp-backend-dynamic-data)
10. [Khi Nào Cần Dùng `custom_css`?](#khi-nào-cần-dùng-custom_css)
11. [Quy Tắc Lồng Nhau (Nesting Tag Suffixes)](#quy-tắc-lồng-nhau-nesting-tag-suffixes)
12. [Bảng Tra Cứu Nhanh Ánh Xạ HTML ➔ VBC](#bảng-tra-cứu-nhanh-ánh-xạ-html--vbc)

---

## 1. NGUYÊN TẮC CỐT LÕI: ƯU TIÊN THUỘC TÍNH TRỰC TIẾP

1. **Ưu tiên Thuộc tính Ngắn gọn (Direct Attributes)**: Hãy dùng các thuộc tính như `color`, `bg_color`, `font_size`, `padding`, `margin`, `border_radius`, `box_shadow`, `display="flex"`, `gap="20px"`, `grid_template_columns="repeat(3, 1fr)"` trực tiếp trong shortcode. Người dùng sẽ dễ dàng kéo thanh trượt / chỉnh sửa trực quan trên UX Builder.
2. **Cơ chế Responsive 3 Màn Hình**: Mọi thuộc tính định dạng đều hỗ trợ 3 màn hình:
   - **Desktop (≥ 850px)**: `tên_thuộc_tính="giá_trị"` (ví dụ: `font_size="32px"`, `padding="30px"`)
   - **Tablet (≤ 849px)**: `tên_thuộc_tính__md="giá_trị"` (ví dụ: `font_size__md="24px"`, `padding__md="20px"`)
   - **Mobile (≤ 549px)**: `tên_thuộc_tính__sm="giá_trị"` (ví dụ: `font_size__sm="18px"`, `padding__sm="12px"`)
3. **Chỉ dùng `custom_css` khi có hiệu ứng nâng cao**: Chỉ dùng `custom_css` khi cần hover (`selector:hover { ... }`), giả lập phần tử (`selector::before`), animation phức tạp hoặc gradient phức tạp.
4. **Không dùng thẻ HTML thô**: Tuyệt đối **KHÔNG** xuất `<div>`, `<p>`, `<span>`, `<img>` thô vào post content.

---

## 2. BẢNG TRA CỨU 30+ THUỘC TÍNH NHẬP TRỰC TIẾP & RESPONSIVE

Mọi shortcode VBC (`[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]`-`[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_card]`, v.v.) đều hỗ trợ các thuộc tính sau:

### 🎨 A. Màu Sắc & Nền (Colors & Backgrounds)
| Thuộc tính | Bí danh (Alias) | Responsive? | Mô tả & Ví dụ |
|:---|:---|:---:|:---|
| `color` | — | Có (`__md`, `__sm`) | Màu chữ: `#ffffff`, `#2563eb`, `rgba(0,0,0,0.8)` |
| `background_color` | `bg_color` | Có (`__md`, `__sm`) | Màu nền: `#ffffff`, `#0f172a`, `transparent` |
| `background` | `bg_gradient` | Có (`__md`, `__sm`) | Nền gradient: `linear-gradient(135deg, #2563eb, #7c3aed)` |
| `bg_image` | — | Có (`__md`, `__sm`) | URL ảnh nền: `https://domain.com/bg.jpg` |

### 🔤 B. Kiểu Chữ & Văn Bản (Typography)
| Thuộc tính | Responsive? | Mô tả & Ví dụ |
|:---|:---:|:---|
| `font_family` | Không | Tên Google Font: `Outfit`, `Inter`, `Montserrat` (Tự động nạp) |
| `font_size` | Có (`__md`, `__sm`) | Cỡ chữ: `16px`, `1.25rem`, `32px`, `48px` |
| `font_weight` | Có (`__md`, `__sm`) | Độ đậm: `300`, `400`, `500`, `600`, `700`, `800`, `900`, `bold` |
| `line_height` | Có (`__md`, `__sm`) | Chiều cao dòng: `1.2`, `1.5`, `1.7`, `28px` |
| `letter_spacing` | Có (`__md`, `__sm`) | Khoảng cách chữ: `0.5px`, `1px`, `-0.5px` |
| `text_align` | Có (`__md`, `__sm`) | Canh lề: `left`, `center`, `right`, `justify` |
| `text_transform` | Có (`__md`, `__sm`) | Hoa/thường: `uppercase`, `lowercase`, `capitalize`, `none` |
| `text_decoration`| Có (`__md`, `__sm`) | Gạch chân/xóa: `none`, `underline`, `line-through` |

### 📐 C. Kích Thước & Bố Cục (Dimensions & Layout)
| Thuộc tính | Responsive? | Mô tả & Ví dụ |
|:---|:---:|:---|
| `display` | Có (`__md`, `__sm`) | `block`, `flex`, `inline-flex`, `grid`, `inline-block`, `none` |
| `width` | Có (`__md`, `__sm`) | Độ rộng: `100%`, `350px`, `auto` |
| `max_width` | Có (`__md`, `__sm`) | Chiều rộng tối đa: `1200px`, `600px`, `100%` |
| `min_width` | Có (`__md`, `__sm`) | Chiều rộng tối thiểu: `250px` |
| `height` | Có (`__md`, `__sm`) | Chiều cao: `100%`, `450px`, `auto` |
| `min_height` | Có (`__md`, `__sm`) | Chiều cao tối thiểu: `300px`, `100vh` |
| `margin` | Có (`__md`, `__sm`) | Lề ngoài: `0 auto`, `20px 0`, `0 0 16px 0` |
| `padding` | Có (`__md`, `__sm`) | Lề trong: `24px`, `15px 30px`, `60px 0` |
| `overflow` | Có (`__md`, `__sm`) | Tràn viền: `hidden`, `visible`, `auto`, `scroll` |

### 🔲 D. Flexbox & CSS Grid Layout
| Thuộc tính | Bí danh | Responsive? | Mô tả & Ví dụ |
|:---|:---|:---:|:---|
| `flex_direction` | — | Có (`__md`, `__sm`) | `row`, `column`, `row-reverse`, `column-reverse` |
| `justify_content` | — | Có (`__md`, `__sm`) | `flex-start`, `center`, `flex-end`, `space-between`, `space-around`, `space-evenly` |
| `align_items` | — | Có (`__md`, `__sm`) | `flex-start`, `center`, `flex-end`, `stretch`, `baseline` |
| `flex_wrap` | — | Có (`__md`, `__sm`) | `nowrap`, `wrap`, `wrap-reverse` |
| `gap` | `grid_gap` | Có (`__md`, `__sm`) | Khoảng cách item: `16px`, `24px`, `1.5rem` |
| `grid_template_columns` | `grid_columns` | Có (`__md`, `__sm`) | Cột Grid: `repeat(3, 1fr)`, `1fr 2fr`, `repeat(auto-fit, minmax(280px, 1fr))` |

### 🔘 E. Viền, Bo Góc & Đổ Bóng (Borders & Shadows)
| Thuộc tính | Responsive? | Mô tả & Ví dụ |
|:---|:---:|:---|
| `border` | Có (`__md`, `__sm`) | Đường viền: `1px solid #e2e8f0`, `2px dashed #2563eb` |
| `border_radius` | Có (`__md`, `__sm`) | Bo góc: `8px`, `12px`, `16px`, `50%`, `9999px` |
| `border_color` | Có (`__md`, `__sm`) | Màu viền: `#e2e8f0`, `#2563eb` |
| `box_shadow` | Có (`__md`, `__sm`) | Đổ bóng: `0 10px 25px rgba(0,0,0,0.1)`, `0 4px 6px -1px rgba(0,0,0,0.05)` |

### 📍 F. Vị Trí & Hiệu Ứng (Positioning & Effects)
| Thuộc tính | Responsive? | Mô tả & Ví dụ |
|:---|:---:|:---|
| `position` | Có (`__md`, `__sm`) | `relative`, `absolute`, `fixed`, `sticky`, `static` |
| `top`, `bottom`, `left`, `right` | Có (`__md`, `__sm`) | Toạ độ: `0`, `20px`, `50%`, `auto` |
| `z_index` | Có (`__md`, `__sm`) | Thứ tự hiển thị lớp: `1`, `10`, `99`, `999` |
| `opacity` | Có (`__md`, `__sm`) | Độ trong suốt: `0.9`, `0.5`, `1` |
| `cursor` | Có (`__md`, `__sm`) | Con trỏ chuột: `pointer`, `default`, `not-allowed` |
| `transition` | Không | Chuyển động: `all 0.3s ease`, `transform 0.2s` |

---

## NHÓM 1: KHUNG BỐ CỤC & CẤU TRÚC (LAYOUT & CONTAINERS)

### 1.1. `[vbc_div]` — Khung Section Toàn Chiều Rộng
* **Cách dùng ngắn gọn chuẩn 2026**:
```html
[vbc_div id="hero" width="100%" bg_color="#0f172a" padding="80px 0" padding__md="50px 0" padding__sm="30px 0" position="relative"]
    ...
[/vbc_div]
```

### 1.2. `[vbc_box]` — Khung Giới Hạn Bề Rộng (Container)
* **Cách dùng ngắn gọn**:
```html
[vbc_box max_width="1200px" margin="0 auto" padding="0 20px" padding__sm="0 15px"]
    ...
[/vbc_box]
```

### 1.3. `[vbc_block]` — Khung Chia Cột Grid / Flexbox
* **Grid 3 Cột Responsive cực kỳ ngắn gọn**:
```html
[vbc_block display="grid" grid_columns="repeat(3, 1fr)" grid_columns__md="repeat(2, 1fr)" grid_columns__sm="1fr" gap="24px" gap__sm="16px"]
    ...
[/vbc_block]
```

* **Flexbox Hàng Ngang Căn Giữa**:
```html
[vbc_block display="flex" justify_content="space-between" align_items="center" flex_direction__sm="column" gap="16px"]
    ...
[/vbc_block]
```

### 1.4. `[vbc_container]` — Khung Chứa Item Con / Card Item
* **Cách dùng ngắn gọn**:
```html
[vbc_container bg_color="#ffffff" border_radius="16px" padding="24px" border="1px solid #e2e8f0" box_shadow="0 4px 6px -1px rgba(0,0,0,0.05)"]
    ...
[/vbc_container]
```

### 1.5. `[vbc_section]` — Wrapper Bọc Phần Tử Flatsome Gốc
```html
[vbc_section id="flatsome-row-wrap" custom_css="selector { padding: 40px 0; } selector .banner { border-radius: 16px; }"]
    [row]
        [col span="6"]...[/col]
        [col span="6"]...[/col]
    [/row]
[/vbc_section]
```

---

## NHÓM 2: KIỂU CHỮ & VĂN BẢN (TYPOGRAPHY)

### 2.1. `[vbc_h1]` đến `[vbc_h6]` — Tiêu Đề Chuẩn SEO
* **Thuộc tính ngắn gọn**: `font_family`, `font_size`, `font_weight`, `line_height`, `color`, `text_align`, `margin`.
* **Cú pháp mẫu**:
```html
[vbc_h1 font_family="Outfit" font_size="42px" font_size__md="32px" font_size__sm="26px" font_weight="800" color="#ffffff" line_height="1.2" margin="0 0 16px 0" text_align="center"]
    Giải Pháp Tự Động Hóa VibeCode 2026
[/vbc_h1]

[vbc_h2 font_family="Inter" font_size="32px" font_size__sm="24px" font_weight="700" color="#0f172a" text_align="left" margin="0 0 12px 0"]
    Bảng Giá Dịch Vụ
[/vbc_h2]
```

### 2.2. `[vbc_p]` — Đoạn Văn Bản
```html
[vbc_p font_size="16px" font_size__sm="14px" color="#64748b" line_height="1.7" margin="0 0 20px 0"]
    Tối ưu hóa toàn diện trải nghiệm kéo thả trên WordPress Flatsome UX Builder.
[/vbc_p]
```

### 2.3. `[vbc_span]` — Văn Bản Nội Dòng
```html
[vbc_span color="#2563eb" font_weight="700"]Chính xác 99%[/vbc_span]
```

### 2.4. `[vbc_a]` — Thẻ Liên Kết (Link / Button Anchor)
* **Thuộc tính**: `link_url`, `link_target` (`_self`/`_blank`), `color`, `bg_color`, `padding`, `border_radius`, `font_weight`.
```html
[vbc_a link_url="https://zalo.me/0123456789" link_target="_blank" display="inline-flex" align_items="center" gap="8px" bg_color="#2563eb" color="#ffffff" padding="12px 24px" border_radius="50px" font_weight="600" text_decoration="none"]
    [vbc_icon icon_type="lucide" name="phone" size="18px" color="#fff"]
    [vbc_span]Tư Vấn Zalo Miễn Phí[/vbc_span]
[/vbc_a]
```

---

## NHÓM 3: DANH SÁCH & BẢNG BIỂU (LISTS & TABLES)

### 3.1. Danh Sách: `[vbc_ul]`, `[vbc_ol]`, `[vbc_li]`
```html
[vbc_ul display="flex" flex_direction="column" gap="12px" padding="0" margin="0"]
    [vbc_li display="flex" align_items="center" gap="10px" font_size="15px" color="#334155"]
        [vbc_icon icon_type="lucide" name="check-circle" size="18px" color="#10b981"]
        [vbc_span]Cam kết hoàn tiền trong 30 ngày[/vbc_span]
    [/vbc_li]
    [vbc_li display="flex" align_items="center" gap="10px" font_size="15px" color="#334155"]
        [vbc_icon icon_type="lucide" name="check-circle" size="18px" color="#10b981"]
        [vbc_span]Hỗ trợ kỹ thuật 24/7[/vbc_span]
    [/vbc_li]
[/vbc_ul]
```

### 3.2. Bảng Biểu: `[vbc_table]`, `[vbc_tr]`, `[vbc_th]`, `[vbc_td]`
```html
[vbc_table width="100%" margin="20px 0" border="1px solid #e2e8f0"]
    [vbc_tr bg_color="#f8fafc"]
        [vbc_th padding="12px 16px" font_weight="700" color="#0f172a"]Gói Cước[/vbc_th]
        [vbc_th padding="12px 16px" font_weight="700" color="#0f172a"]Giá[/vbc_th]
    [/vbc_tr]
    [vbc_tr]
        [vbc_td padding="12px 16px" border="1px solid #e2e8f0"]Cơ Bản[/vbc_td]
        [vbc_td padding="12px 16px" border="1px solid #e2e8f0"]1.990.000đ[/vbc_td]
    [/vbc_tr]
[/vbc_table]
```

---

## NHÓM 4: PHẦN TỬ TỰ ĐÓNG (VOID ELEMENTS)

> ⚠️ **LƯU Ý QUAN TRỌNG**: Các phần tử Void **KHÔNG** có thẻ đóng (Không dùng `[/vbc_img]`).

### 4.1. `[vbc_img]` — Hình Ảnh
* **Thuộc tính**: `img_source="manual"`, `img_url`, `alt`, `width`, `max_width`, `height`, `border_radius`, `box_shadow`.
```html
[vbc_img img_source="manual" img_url="https://domain.com/uploads/banner.webp" alt="Banner Khuyến Mãi" width="100%" max_width="600px" height="auto" border_radius="16px" box_shadow="0 10px 25px rgba(0,0,0,0.1)"]
```

### 4.2. `[vbc_hr]` — Đường Phân Cách
```html
[vbc_hr margin="40px 0" border="none" border_color="#e2e8f0" border_width="1px" border_style="solid"]
```

### 4.3. `[vbc_br]` — Xuống Dòng: `[vbc_br]`

---

## NHÓM 5: UI COMPONENTS CAO CẤP

### 5.1. `[vbc_card]` — Khối Thẻ Hiện Đại
```html
[vbc_card variant="glass" hover_effect="glow" badge="KHUYÊN DÙNG" padding="30px" border_radius="16px"]
    [vbc_h3 font_size="22px" font_weight="700" margin="0 0 10px 0"]Gói Doanh Nghiệp[/vbc_h3]
    [vbc_p font_size="28px" font_weight="800" color="#2563eb"]4.990.000đ[/vbc_p]
[/vbc_card]
```

### 5.2. `[vbc_testimonial]` — Đánh Giá Khách Hàng Chuẩn CRO
```html
[vbc_testimonial author_name="Nguyễn Văn A" author_job="Founder Startup Tech" rating="5" avatar="https://domain.com/uploads/avatar1.jpg" bg_color="#ffffff" padding="24px" border_radius="12px" box_shadow="0 4px 6px -1px rgba(0,0,0,0.05)"]
    [vbc_p]Dịch vụ clone trang web siêu nhanh và chính xác từng pixel![/vbc_p]
[/vbc_testimonial]
```

### 5.3. `[vbc_accordion]` & `[vbc_accordion_item]` — Hỏi Đáp Schema FAQ
```html
[vbc_accordion style="separated" icon="plus" enable_schema="yes"]
    [vbc_accordion_item title="Thời gian triển khai landing page mất bao lâu?" is_open="yes"]
        [vbc_p]Thời gian triển khai hoàn thiện chỉ từ 1 đến 2 giờ đồng hồ.[/vbc_p]
    [/vbc_accordion_item]
    [vbc_accordion_item title="Tôi có thể tự chỉnh sửa bằng kéo thả không?"]
        [vbc_p]Hoàn toàn được với Flatsome UX Builder 100% trực quan.[/vbc_p]
    [/vbc_accordion_item]
[/vbc_accordion]
```

### 5.4. `[vbc_tabs]` & `[vbc_tab]` — Hệ Thống Chuyển Tab
```html
[vbc_tabs style="pills" align="center" active_tab="1"]
    [vbc_tab title="Tổng Quan" icon="fa fa-info-circle"]
        [vbc_p]Nội dung tab tổng quan...[/vbc_p]
    [/vbc_tab]
    [vbc_tab title="Bảng Giá" icon="fa fa-tag"]
        [vbc_p]Nội dung tab bảng giá...[/vbc_p]
    [/vbc_tab]
[/vbc_tabs]
```

### 5.5. `[vbc_button]` — Nút CTA
```html
[vbc_button text="ĐĂNG KÝ NGAY" link_url="#dang-ky" variant="gradient" size="large" icon="fa fa-arrow-right" icon_position="right" border_radius="50px" box_shadow="0 8px 20px rgba(37,99,235,0.3)"]
```

### 5.6. `[vbc_slider]` & `[vbc_slide]` — Trình Chiếu Splide.js
```html
[vbc_slider per_page="3" per_page__md="2" per_page__sm="1" autoplay="yes" arrows="yes" pagination="yes" gap="24px"]
    [vbc_slide]
        [vbc_container bg_color="#f8fafc" padding="20px" border_radius="12px"]Slide 1[/vbc_container]
    [/vbc_slide]
    [vbc_slide]
        [vbc_container bg_color="#f8fafc" padding="20px" border_radius="12px"]Slide 2[/vbc_container]
    [/vbc_slide]
[/vbc_slider]
```

### 5.7. `[contact-form-7]` — Biểu Mẫu Thu Thập Thông Tin Khách Hàng (BẮT BUỘC)
Tất cả các biểu mẫu đăng ký, tư vấn, nhận ưu đãi trên Landing Page **PHẢI** sử dụng biểu mẫu Contact Form 7 thực tế:

1. **Tạo Form qua lệnh Python**:
```bash
python .agents/skills/clone-landingpage/scripts/create_cf7.py --title "Form Tư Vấn Khóa Học" --fields "name,phone,email,course,message" --button "Đăng Ký Tư Vấn Miễn Phí"
```
2. **Nhúng Shortcode vào Layout VBC**:
```html
[vbc_div bg_color="#F5568F" padding="70px 0" display="block"]
    [vbc_container max_width="800px" margin="0 auto" padding="40px" bg_color="#ffffff" border_radius="24px" display="block"]
        [vbc_h3 color="#1e293b" font_size="28px" font_weight="800" text_align="center" margin="0 0 24px 0" text="Nhận tư vấn và học thử miễn phí"]
        [contact-form-7 id="1391" title="Form Đăng Ký Tư Vấn - Tiếng Anh Mẫu Giáo"]
    [/vbc_container]
[/vbc_div]
```

---

## NHÓM 6: HỆ THỐNG ICON VECTOR 5 BỘ

| `icon_type` | Thư viện | Định dạng thuộc tính `name` | Ví dụ mẫu |
|:---|:---|:---|:---|
| `lucide` (Mặc định) | Lucide Icons | Kebab-case (`phone`, `check`, `shield-check`) | `[vbc_icon icon_type="lucide" name="shield-check" size="24px" color="#2563eb"]` |
| `fontawesome` | FontAwesome 6 | Class FA đầy đủ (`fa-solid fa-star`) | `[vbc_icon icon_type="fontawesome" name="fa-solid fa-star" size="20px" color="#f59e0b"]` |
| `remixicon` | Remix Icon | Class Remix (`ri-customer-service-2-fill`) | `[vbc_icon icon_type="remixicon" name="ri-customer-service-2-fill" size="22px" color="#ef4444"]` |
| `phosphor` | Phosphor Icons | Class Phosphor (`ph ph-lightning`) | `[vbc_icon icon_type="phosphor" name="ph ph-lightning" size="24px" color="#8b5cf6"]` |
| `material` | Google Material Symbols | Snake_case (`verified`, `schedule`) | `[vbc_icon icon_type="material" name="verified" size="24px" color="#10b981"]` |

---

## NHÓM 7: TRUY VẤN ĐỘNG & TÍCH HỢP BACKEND (DYNAMIC DATA)

> 🚨 **QUY TẮC BẮT BUỘC**: Khi quét thấy khu vực danh sách Bài viết Blog, Tin tức hoặc Danh sách Sản phẩm, **BẮT BUỘC sử dụng element `[vbc_post]`** thay vì dựng thủ công các `[col]` tĩnh lặp lại.

### 7.1. Cú pháp Blog Grid (Tin Tức / Bài Viết Mới Nhất)
```html
[vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
```

### 7.2. Cú pháp Product Grid (Sản Phẩm WooCommerce / Khóa Học)
```html
[vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay" card_radius="16px"]
```

### 7.3. Cấu hình linh hoạt các trường (`fields`):
- `fields="thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, price:50%, button:100%"`
- **Merge Tags hỗ trợ**: `{{post_title}}`, `{{post_date}}`, `{{meta:gia_ban}}`, `{{acf:hotline}}`.

### 7.4. Shortcodes Thông Tin Website & Doanh Nghiệp (Ultimate Flatsome General Info)
> Quản lý tập trung tại **Ultimate Flatsome > Cài Đặt Chung**, tự động đồng bộ `wp_options`.

- `[uf_phone]`: Hotline dạng văn bản thuần (ví dụ `0912 345 678`).
- `[uf_phone link="true"]`: Hotline dạng thẻ gọi `<a href="tel:0912345678">`.
- `[uf_phone_2 link="true"]`: Hotline phụ / Kỹ thuật.
- `[uf_zalo link="true"]`: Link chat Zalo OA (`https://zalo.me/...`).
- `[uf_email link="true"]`: Email dạng thẻ mailto `<a href="mailto:...">`.
- `[uf_address]`: Địa chỉ trụ sở chính doanh nghiệp.
- `[uf_company]`: Tên công ty / Doanh nghiệp.
- `[uf_copyright]`: Bản quyền chân trang (Tự động thay `{year}` theo năm hiện tại).
- `[uf_info field="site_name"]`: Tên website (từ `wp_options: blogname`).
- `[uf_info field="tagline"]`: Khẩu hiệu website (từ `wp_options: blogdescription`).
- `[uf_info field="hours"]`: Thời gian làm việc.
- `[uf_info field="tax_code"]`: Mã số thuế / ĐKKD.
- `[uf_info field="facebook" link="true"]`: Link Facebook Fanpage.
- `[uf_info field="youtube" link="true"]`: Link Kênh YouTube.
- `[uf_info field="tiktok" link="true"]`: Link Kênh TikTok.
- `[uf_info field="instagram" link="true"]`: Link Instagram.
- `[uf_info field="messenger" link="true"]`: Link Messenger Chat.
- `[uf_info field="telegram" link="true"]`: Link Telegram Chat.
- `[uf_info field="maps" link="true"]`: Link Google Maps.
- `[uf_option key="..."]`: Truy xuất bất kỳ giá trị nào trong bảng `wp_options`.

---

## KHI NÀO CẦN DÙNG `custom_css`?

Chỉ nên dùng `custom_css` cho các trường hợp đặc biệt không thể biểu diễn bằng thuộc tính ngắn gọn:
1. **Hiệu ứng rê chuột (Hover states)**:
   ```html
   [vbc_container bg_color="#fff" border_radius="12px" padding="24px" custom_css="selector { transition: all 0.3s ease; } selector:hover { transform: translateY(-6px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); }"]
   ```
2. **Pseudo-elements (`::before`, `::after`) & Keyframe Animations**:
   ```html
   [vbc_div custom_css="selector::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.4); z-index: 1; }"]
   ```

---

## QUY TẮC LỒNG NHAU (NESTING TAG SUFFIXES)

| Cấp lồng | Cú pháp Shortcode bắt buộc |
|:---|:---|
| **Cấp 1 (Ngoài cùng)** | `[vbc_div]`, `[vbc_block]`, `[vbc_container]`, `[vbc_tabs]`, `[vbc_post]` |
| **Cấp 2 (Con trực tiếp)** | `[vbc_div_inner]`, `[vbc_block_inner]`, `[vbc_container_inner]`, `[vbc_tabs_inner]` |
| **Cấp 3** | `[vbc_div_inner_1]`, `[vbc_block_inner_1]`, `[vbc_container_inner_1]` |
| **Cấp 4** | `[vbc_div_inner_2]`, `[vbc_block_inner_2]`, `[vbc_container_inner_2]` |
| **Cấp 5** | `[vbc_div_inner_3]`, `[vbc_block_inner_3]`, `[vbc_container_inner_3]` |

---

## BẢNG TRA CỨU NHANH ÁNH XẠ HTML ➔ VBC

| Thẻ HTML Gốc | Phần tử VBC Tương Ứng | Nhóm | Thuộc tính khuyên dùng thay vì custom_css |
|:---|:---|:---|:---|
| `<section>`, `<div class="wrapper">` | `[vbc_div]` | Container | `width="100%"`, `bg_color`, `padding`, `padding__md`, `padding__sm` |
| `<div class="container">` | `[vbc_box]` | Container | `max_width="1200px"`, `margin="0 auto"`, `padding="0 20px"` |
| `<div class="row">`, `<div class="grid">` | `[vbc_block]` | Container | `display="grid"`, `grid_columns="repeat(3, 1fr)"`, `gap="24px"` |
| `<div class="col">`, `<div class="item">` | `[vbc_container]` | Container | `bg_color`, `border_radius`, `padding`, `border`, `box_shadow` |
| `<h1>` đến `<h6>` | `[vbc_h1]` đến `[vbc_h6]` | Typography | `font_size`, `font_weight`, `color`, `line_height`, `text_align` |
| `<p>` | `[vbc_p]` | Typography | `font_size`, `color`, `line_height`, `margin` |
| `<span>` | `[vbc_span]` | Typography | `color`, `font_weight`, `font_size` |
| `<a>` | `[vbc_a]` | Typography | `link_url`, `color`, `bg_color`, `padding`, `border_radius` |
| `<b>`, `<strong>` | `[vbc_strong]`, `[vbc_b]` | Typography | In đậm |
| `<i>`, `<em>` | `[vbc_em]` | Typography | In nghiêng |
| `<ul>`, `<ol>`, `<li>` | `[vbc_ul]`, `[vbc_ol]`, `[vbc_li]` | List | `display="flex"`, `flex_direction="column"`, `gap="12px"` |
| `<table>`, `<tr>`, `<th>`, `<td>` | `[vbc_table]`, `[vbc_tr]`, `[vbc_th]`, `[vbc_td]` | Table | `border`, `padding`, `bg_color`, `colspan`, `rowspan` |
| `<img>` | `[vbc_img]` | Void | **Tự đóng**, `img_url`, `alt`, `max_width`, `border_radius` |
| `<hr>` | `[vbc_hr]` | Void | **Tự đóng**, `margin="40px 0"`, `border_color` |
| `<br>` | `[vbc_br]` | Void | **Tự đóng** |
| `<svg>`, `<i class="fa...">` | `[vbc_icon]` | Icon | `icon_type`, `name`, `size`, `color` |
| `<form>` | `[contact-form-7]` | Component | Form liên hệ CF7 |
| Khối Card | `[vbc_card]` | Component | `variant="glass"`, `hover_effect="glow"`, `badge` |
| Đánh giá khách hàng | `[vbc_testimonial]` | Component | `author_name`, `rating="5"`, `avatar` |
| Hỏi đáp / FAQ | `[vbc_accordion]` | Component | `style="separated"`, `icon="plus"`, `enable_schema="yes"` |
| Tab chuyển đổi | `[vbc_tabs]` + `[vbc_tab]` | Component | `style="pills"`, `align="center"` |
| Nút bấm CTA | `[vbc_button]` | Component | `variant="gradient"`, `size="large"`, `icon` |
| Carousel / Slider | `[vbc_slider]` + `[vbc_slide]` | Component | `per_page="3"`, `autoplay="yes"`, `gap="24px"` |
| Danh sách bài viết | `[vbc_post]` | Dynamic | `post_type="post"`, `columns="3"`, `layout="grid"` |
