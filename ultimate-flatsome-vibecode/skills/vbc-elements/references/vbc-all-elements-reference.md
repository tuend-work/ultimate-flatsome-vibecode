# CẨM NANG TOÀN DIỆN VBC ELEMENTS DÀNH CHO AI AGENT
## Ultimate Flatsome VibeCode Elements (v2.3.0 Specification)

> **Tài liệu chuẩn hóa dành riêng cho AI Agents (Antigravity, Cloner, Generator, Linter, LLM Prompting).**
> Quy định 100% cú pháp, thuộc tính, cơ chế CSS Scope, xử lý lồng nhau (Nesting) và tích hợp WordPress Backend.

---

## 📑 MỤC LỤC
1. [Nguyên Tắc Cốt Lõi Cho AI](#1-nguyên-tắc-cốt-lõi-cho-ai)
2. [Nhóm 1: Khung Bố Cục & Cấu Trúc (Layout & Containers)](#nhóm-1-khung-bố-cục--cấu-trúc-layout--containers)
3. [Nhóm 2: Kiểu Chữ & Văn Bản (Typography)](#nhóm-2-kiểu-chữ--văn-bản-typography)
4. [Nhóm 3: Danh Sách & Bảng Biểu (Lists & Tables)](#nhóm-3-danh-sách--bảng-biểu-lists--tables)
5. [Nhóm 4: Phần Tử Tự Đóng (Void Elements)](#nhóm-4-phần-tử-tự-đóng-void-elements)
6. [Nhóm 5: UI Components Cao Cấp](#nhóm-5-ui-components-cao-cấp)
7. [Nhóm 6: Hệ Thống Icon Vector 5 Bộ](#nhóm-6-hệ-thống-icon-vector-5-bộ)
8. [Nhóm 7: Truy Vấn Động & Tích Hợp Backend (Dynamic Data)](#nhóm-7-truy-vấn-động--tích-hợp-backend-dynamic-data)
9. [Quy Tắc Biên Dịch CSS & Responsive](#quy-tắc-biên-dịch-css--responsive)
10. [Quy Tắc Lồng Nhau (Nesting Tag Suffixes)](#quy-tắc-lồng-nhau-nesting-tag-suffixes)
11. [Bảng Tra Cứu Nhanh Ánh Xạ HTML ➔ VBC](#bảng-tra-cứu-nhanh-ánh-xạ-html--vbc)

---

## 1. NGUYÊN TẮC CỐT LÕI CHO AI

1. **Không dùng thẻ HTML thô**: Tuyệt đối **KHÔNG** xuất thẻ HTML thô (`<div>`, `<p>`, `<span>`, `<img>`...) trực tiếp vào nội dung UX Builder nếu không bắt buộc. 100% bố cục phải bọc bằng `[vbc_*]` shortcodes.
2. **CSS Scope 100% bằng từ khóa `selector`**: Mọi CSS tùy biến phải đặt trong thuộc tính `custom_css="selector { ... }"` để plugin tự động biên dịch thành class ngẫu nhiên duy nhất (ví dụ `.vbc-css-a1b2c3d4`), chống trùng style toàn trang.
3. **Responsive Breakpoints**:
   - Desktop: Mặc định (≥ 850px).
   - Tablet: `@media (max-width: 849px)` (hoặc dùng hậu tố `__md`).
   - Mobile: `@media (max-width: 549px)` (hoặc dùng hậu tố `__sm`).
4. **Nén CSS 1 dòng**: Không để xuống dòng bên trong giá trị thuộc tính `custom_css="..."` để chống bộ lọc `wpautop` của WordPress tự động chèn `<p>` và `<br>` làm vỡ giao diện.

---

## NHÓM 1: KHUNG BỐ CỤC & CẤU TRÚC (LAYOUT & CONTAINERS)

### 1.1. `[vbc_div]` — Khung Section Toàn Chiều Rộng
* **Vai trò**: Đại diện cho section chính của trang (100% width) hoặc khối cha lớn nhất.
* **Các thuộc tính**:
  - `id`: ID định danh duy nhất (dùng neo link menu `#about`, `#pricing`).
  - `class` / `custom_class`: Class CSS bổ sung.
  - `custom_css`: Khối CSS nội tuyến tự động scope (`selector { ... }`).
  - `width`, `height`, `margin`, `padding`, `background_color`, `display`.
* **Cú pháp mẫu**:
```html
[vbc_div id="hero-section" custom_css="selector { width: 100%; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 80px 0; position: relative; }"]
    ...
[/vbc_div]
```

### 1.2. `[vbc_box]` — Khung Giới Hạn Bề Rộng (Container)
* **Vai trò**: Căn giữa nội dung, cố định `max-width` (1200px, 1280px, 1400px).
* **Cú pháp mẫu**:
```html
[vbc_box class="container" custom_css="selector { max-width: 1200px; margin: 0 auto; padding: 0 20px; }"]
    ...
[/vbc_box]
```

### 1.3. `[vbc_block]` — Khung Chia Cột Grid / Flexbox
* **Vai trò**: Layout Row phân chia cột (CSS Grid hoặc Flexbox).
* **Cú pháp mẫu (Grid 3 Cột Responsive)**:
```html
[vbc_block custom_css="selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; } @media(max-width:849px) { selector { grid-template-columns: repeat(2, 1fr); } } @media(max-width:549px) { selector { grid-template-columns: 1fr; } }"]
    ...
[/vbc_block]
```

### 1.4. `[vbc_container]` — Khung Chứa Item Con Trong Cột
* **Vai trò**: Đại diện cho một cột (`col`) hoặc một Card item bên trong Grid/Flexbox.
* **Cú pháp mẫu**:
```html
[vbc_container custom_css="selector { background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e2e8f0; }"]
    ...
[/vbc_container]
```

### 1.5. `[vbc_section]` — Wrapper Bọc Phần Tử Flatsome Gốc
* **Vai trò**: Bọc ngoài các shortcode mặc định của Flatsome (`[row]`, `[col]`, `[ux_banner]`, `[ux_slider]`) để áp dụng custom CSS mà không bị vỡ.
* **Thuộc tính**: `id`, `class`, `custom_css`.
* **Cú pháp mẫu**:
```html
[vbc_section id="flatsome-native-wrap" custom_css="selector { padding: 40px 0; } selector .banner { border-radius: 16px; }"]
    [row]
        [col span="6"]...[/col]
        [col span="6"]...[/col]
    [/row]
[/vbc_section]
```

---

## NHÓM 2: KIỂU CHỮ & VĂN BẢN (TYPOGRAPHY)

> [!CRITICAL]
> **QUY TẮC BẮT BUỘC: 100% SỬ DỤNG SHORTCODE TỰ ĐÓNG VỚI THUỘC TÍNH `text="..."`**
> - **CẤM TUYỆT ĐỐI**: Không bao giờ viết dạng thẻ đóng mở có ruột văn bản như `[vbc_p]...[/vbc_p]`, `[vbc_h1]...[/vbc_h1]`, `[vbc_span]...[/vbc_span]`, `[vbc_a]...[/vbc_a]`.
> - **Lý do**: Flatsome UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` hoặc thẻ rác làm hỏng toàn bộ giao diện.
> - **ĐÚNG**: `[vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]`
> - **SAI**: `[vbc_p class="target-text"]Khắc phục điểm yếu, nâng band điểm <b>Listening</b>.[/vbc_p]`

### 2.1. `[vbc_h1]` đến `[vbc_h6]` — Tiêu Đề Chuẩn SEO
* **Thuộc tính**: `text`, `class`, `custom_css`, `font_family`, `font_size`, `font_size__md`, `font_size__sm`, `font_weight`, `line_height`, `text_align`, `color`, `margin`.
* **Cú pháp mẫu (Tự đóng)**:
```html
[vbc_h1 text="Giải Pháp Công Nghệ Đột Phá 2026" font_size="42px" font_size__sm="28px" font_weight="800" color="#ffffff" line_height="1.2" margin="0 0 16px 0"]
[vbc_h2 text="Tính Năng Nổi Bật" font_size="32px" font_weight="700" color="#0f172a" text_align="center" margin="0 0 20px 0"]
```

### 2.2. `[vbc_p]` — Đoạn Văn Bản
* **Thuộc tính**: `text`, `class`, `color`, `font_size`, `font_size__sm`, `line_height`, `text_align`, `margin`.
* **Cú pháp mẫu (Tự đóng)**:
```html
[vbc_p text="Hệ thống hỗ trợ tự động hóa toàn diện quy trình chuyển đổi giao diện website sang Flatsome UX Builder." color="#64748b" font_size="16px" line_height="1.7" margin="0 0 20px 0"]
[vbc_p text="Chương trình đào tạo phản xạ ngôn ngữ <b>chuẩn quốc tế</b>." class="desc-text"]
```

### 2.3. `[vbc_span]` — Văn Bản Nội Dòng (Inline Text)
* **Thuộc tính**: `text`, `class`, `color`, `font_size`, `font_weight`.
* **Cú pháp mẫu (Tự đóng)**:
```html
[vbc_span text="Nổi bật 99%" color="#2563eb" font_weight="700" class="badge-text"]
```

### 2.4. `[vbc_a]` — Thẻ Liên Kết (Link/Anchor)
* **Thuộc tính**: `href` (hoặc `link_url`), `text`, `target` (`_self` / `_blank`), `rel`, `class`, `custom_css`, `bg_color`, `color`, `font_size`, `font_weight`, `padding`, `border_radius`, `display`.
* **Cú pháp mẫu (Tự đóng)**:
```html
[vbc_a href="https://zalo.me/0123456789" text="Liên hệ tư vấn Zalo" target="_blank" class="btn-zalo" bg_color="#2563eb" color="#ffffff" padding="12px 24px" border_radius="8px" display="inline-block"]
```

### 2.5. `[vbc_b]`, `[vbc_strong]`, `[vbc_em]`, `[vbc_u]` — Định Dạng Chữ
* `[vbc_strong]` / `[vbc_b]`: Chữ in đậm.
* `[vbc_em]`: Chữ in nghiêng.
* `[vbc_u]`: Chữ gạch chân.

---

## NHÓM 3: DANH SÁCH & BẢNG BIỂU (LISTS & TABLES)

### 3.1. Danh Sách: `[vbc_ul]`, `[vbc_ol]`, `[vbc_li]`
* **Thuộc tính `[vbc_ol]`**: `ol_type` (`1`, `a`, `A`, `i`, `I`), `ol_start`.
* **Cú pháp mẫu (Danh sách tính năng kèm icon)**:
```html
[vbc_ul custom_css="selector { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }"]
    [vbc_li custom_css="selector { display: flex; align-items: center; gap: 10px; font-size: 15px; color: #334155; }"]
        [vbc_icon icon_type="lucide" name="check-circle" size="18px" color="#10b981"]
        [vbc_span]Bảo hành mã nguồn trọn đời[/vbc_span]
    [/vbc_li]
    [vbc_li custom_css="selector { display: flex; align-items: center; gap: 10px; font-size: 15px; color: #334155; }"]
        [vbc_icon icon_type="lucide" name="check-circle" size="18px" color="#10b981"]
        [vbc_span]Hỗ trợ kỹ thuật 24/7[/vbc_span]
    [/vbc_li]
[/vbc_ul]
```

### 3.2. Bảng Biểu: `[vbc_table]`, `[vbc_tr]`, `[vbc_th]`, `[vbc_td]`
* **Thuộc tính `[vbc_th]` / `[vbc_td]`**: `colspan`, `rowspan`, `custom_css`.
* **Cú pháp mẫu**:
```html
[vbc_table custom_css="selector { width: 100%; border-collapse: collapse; margin: 20px 0; } selector th, selector td { padding: 12px 16px; border: 1px solid #e2e8f0; text-align: left; } selector th { background: #f8fafc; font-weight: 700; }"]
    [vbc_tr]
        [vbc_th]Tính Năng[/vbc_th]
        [vbc_th]Gói Cơ Bản[/vbc_th]
        [vbc_th]Gói Pro[/vbc_th]
    [/vbc_tr]
    [vbc_tr]
        [vbc_td]Số lượng trang[/vbc_td]
        [vbc_td]1 Trang[/vbc_td]
        [vbc_td]Không giới hạn[/vbc_td]
    [/vbc_tr]
[/vbc_table]
```

---

## NHÓM 4: PHẦN TỬ TỰ ĐÓNG (VOID ELEMENTS)

> ⚠️ **LƯU Ý QUAN TRỌNG**: Các phần tử Void **KHÔNG** có thẻ đóng (Không dùng `[/vbc_img]`).

### 4.1. `[vbc_img]` — Hình Ảnh Responsive
* **Thuộc tính**:
  - `img_source`: `default` (qua WP attachment ID) hoặc `manual` (nhập URL trực tiếp).
  - `img_url` / `src`: URL trực tiếp của ảnh (Ưu tiên đã upload lên Media WordPress).
  - `img_attachment`: ID file đính kèm trong WordPress Media.
  - `alt`: Thuộc tính mô tả ảnh chuẩn SEO.
  - `custom_css`: CSS định kiểu kích thước, bo góc, bóng mờ, object-fit.
* **Cú pháp mẫu**:
```html
[vbc_img img_source="manual" img_url="https://domain.com/wp-content/uploads/banner.webp" alt="Banner Khuyến Mãi" custom_css="selector { width: 100%; max-width: 600px; height: auto; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }"]
```

### 4.2. `[vbc_hr]` — Đường Phân Cách
* **Cú pháp mẫu**:
```html
[vbc_hr custom_css="selector { border: none; border-top: 1px solid #e2e8f0; margin: 40px 0; }"]
```

### 4.3. `[vbc_br]` — Xuống Dòng
* **Cú pháp mẫu**: `[vbc_br]`

---

## NHÓM 5: UI COMPONENTS CAO CẤP

### 5.1. `[vbc_card]` — Khối Thẻ Hiện Đại (Glassmorphism / Glow)
* **Thuộc tính**:
  - `variant`: `glass` (kính mờ), `bordered` (viền mảnh), `elevated` (đổ bóng nổi), `flat`.
  - `hover_effect`: `lift` (nhấc lên), `glow` (tỏa sáng viền), `scale`, `none`.
  - `badge`: Chữ nhãn dán góc (ví dụ: `HOT`, `PHỔ BIẾN NHẤT`).
  - `badge_bg`, `badge_color`, `custom_css`.
* **Cú pháp mẫu**:
```html
[vbc_card variant="glass" hover_effect="glow" badge="KHUYÊN DÙNG" custom_css="selector { padding: 30px; border-radius: 16px; }"]
    [vbc_h3 custom_css="selector { font-size: 22px; font-weight: 700; margin-bottom: 10px; }"]Gói Doanh Nghiệp[/vbc_h3]
    [vbc_p custom_css="selector { font-size: 28px; font-weight: 800; color: #2563eb; }"]4.990.000đ[/vbc_p]
[/vbc_card]
```

### 5.2. `[vbc_testimonial]` — Khối Đánh Giá Khách Hàng Chuẩn CRO
* **Thuộc tính**:
  - `author_name`: Tên khách hàng / người nhận xét.
  - `author_job`: Chức vụ / Công ty (ví dụ: `CEO tại VinaCorp`).
  - `avatar`: URL ảnh đại diện.
  - `rating`: Số sao đánh giá (`1`, `2`, `3`, `4`, `5`).
  - `custom_css`.
* **Cú pháp mẫu**:
```html
[vbc_testimonial author_name="Nguyễn Văn A" author_job="Founder Startup Tech" rating="5" avatar="https://domain.com/uploads/avatar1.jpg" custom_css="selector { background: #ffffff; padding: 24px; border-radius: 12px; }"]
    [vbc_p]Dịch vụ clone trang web siêu nhanh và chính xác từng pixel. Tôi rất hài lòng về trải nghiệm này![/vbc_p]
[/vbc_testimonial]
```

### 5.3. `[vbc_accordion]` & `[vbc_accordion_item]` — Hỏi Đáp Tích Hợp Schema FAQ SEO
* **Thuộc tính `[vbc_accordion]`**: `style` (`separated`, `joined`), `icon` (`arrow`, `plus`, `chevron`), `enable_schema` (`yes` / `no`).
* **Thuộc tính `[vbc_accordion_item]`**: `title` (câu hỏi), `is_open` (`yes` / `no`).
* **Cú pháp mẫu**:
```html
[vbc_accordion style="separated" icon="plus" enable_schema="yes"]
    [vbc_accordion_item title="Thời gian triển khai landing page mất bao lâu?" is_open="yes"]
        [vbc_p]Thời gian triển khai hoàn thiện chỉ từ 1 đến 2 giờ đồng hồ nhờ công nghệ VibeCode AI Automation.[/vbc_p]
    [/vbc_accordion_item]
    [vbc_accordion_item title="Tôi có thể tự chỉnh sửa nội dung bằng kéo thả không?"]
        [vbc_p]Hoàn toàn được! 100% phần tử tương thích hoàn hảo với giao diện kéo thả trực quan Flatsome UX Builder.[/vbc_p]
    [/vbc_accordion_item]
[/vbc_accordion]
```

### 5.4. `[vbc_tabs]` & `[vbc_tab]` — Hệ Thống Chuyển Tab Đa Năng
* **Thuộc tính `[vbc_tabs]`**: `style` (`pills`, `underline`, `cards`, `glass`), `align` (`left`, `center`, `right`, `justify`), `active_tab` (`1`, `2`, ...).
* **Thuộc tính `[vbc_tab]`**: `title`, `icon`, `tab_id`.
* **Cú pháp mẫu**:
```html
[vbc_tabs style="pills" align="center" active_tab="1"]
    [vbc_tab title="Tổng Quan Dịch Vụ" icon="fa fa-info-circle"]
        [vbc_p]Nội dung tab tổng quan chi tiết...[/vbc_p]
    [/vbc_tab]
    [vbc_tab title="Bảng Giá & Gói Cước" icon="fa fa-dollar-sign"]
        [vbc_p]Nội dung tab bảng giá dịch vụ...[/vbc_p]
    [/vbc_tab]
[/vbc_tabs]
```

### 5.5. `[vbc_button]` — Nút Kêu Gọi Hành Động (CTA Button)
* **Thuộc tính**:
  - `text`: Nhãn nút bấm.
  - `link_url`: Đường dẫn link khi click.
  - `link_target`: `_self` / `_blank`.
  - `variant`: `primary`, `gradient`, `glass`, `danger`, `outline`, `3d`.
  - `size`: `small`, `medium`, `large`, `xlarge`.
  - `icon`: Tên class icon (ví dụ: `fa fa-arrow-right` hoặc Lucide icon).
  - `icon_position`: `left` / `right`.
  - `custom_css`.
* **Cú pháp mẫu**:
```html
[vbc_button text="NHẬN BÁO GIÁ NGAY" link_url="#dang-ky" variant="gradient" size="large" icon="fa fa-paper-plane" icon_position="right" custom_css="selector { box-shadow: 0 8px 20px rgba(37,99,235,0.3); border-radius: 50px; font-weight: 700; }"]
```

### 5.6. `[vbc_slider]` & `[vbc_slide]` — Khối Trình Chiếu Splide.js
* **Thuộc tính `[vbc_slider]`**: `per_page` (`1`, `2`, `3`, `4`), `autoplay` (`yes` / `no`), `arrows` (`yes` / `no`), `pagination` (`yes` / `no`), `gap` (`20px`), `loop` (`yes` / `no`).
* **Cú pháp mẫu**:
```html
[vbc_slider per_page="3" autoplay="yes" arrows="yes" pagination="yes" gap="24px"]
    [vbc_slide]
        [vbc_card variant="bordered"]Slide 1[/vbc_card]
    [/vbc_slide]
    [vbc_slide]
        [vbc_card variant="bordered"]Slide 2[/vbc_card]
    [/vbc_slide]
[/vbc_slider]
```

### 5.7. `[vbc_fullpage]` — Section Cuộn Từng Màn Hình (fullPage.js)
* **Thuộc tính**: `navigation` (`yes`/`no`), `scroll_speed` (`700`).

---

## NHÓM 6: HỆ THỐNG ICON VECTOR 5 BỘ

Shortcode `[vbc_icon]` hỗ trợ **5 thư viện Icon vector hàng đầu**, có cơ chế **Selective Lazy Loading** (chỉ nạp file CSS/JS của bộ icon có trên trang):

| `icon_type` | Thư viện | Định dạng thuộc tính `name` | Ví dụ mẫu |
|:---|:---|:---|:---|
| `lucide` (Mặc định) | Lucide Icons | Tên kebab-case (vd: `phone`, `check`, `shield-check`) | `[vbc_icon icon_type="lucide" name="shield-check" size="24px" color="#2563eb"]` |
| `fontawesome` | FontAwesome 6 | Class FA đầy đủ (vd: `fa-solid fa-star`) | `[vbc_icon icon_type="fontawesome" name="fa-solid fa-star" size="20px" color="#f59e0b"]` |
| `remixicon` | Remix Icon | Class Remix (vd: `ri-customer-service-2-fill`) | `[vbc_icon icon_type="remixicon" name="ri-customer-service-2-fill" size="22px" color="#ef4444"]` |
| `phosphor` | Phosphor Icons | Class Phosphor (vd: `ph ph-lightning`) | `[vbc_icon icon_type="phosphor" name="ph ph-lightning" size="24px" color="#8b5cf6"]` |
| `material` | Google Material Symbols | Tên snake_case (vd: `verified`, `schedule`) | `[vbc_icon icon_type="material" name="verified" size="24px" color="#10b981"]` |

---

## NHÓM 7: TRUY VẤN ĐỘNG & TÍCH HỢP BACKEND (DYNAMIC DATA)

### 7.1. `[vbc_post]` — Grid Truy Vấn Bài Viết & Sản Phẩm WooCommerce
> 🚨 **QUY TẮC BẮT BUỘC**: Khi nhận diện hoặc thiết kế khu vực là Row bài viết blog, Tin tức, hoặc Danh sách sản phẩm, **BẮT BUỘC sử dụng element `[vbc_post]`** thay vì dựng cột tĩnh lặp lại.

* **Thuộc tính**:
  - `post_type`: `post` (tin tức), `product` (WooCommerce), hoặc bất kỳ Custom Post Type nào.
  - `posts_per_page`: Số lượng bài hiển thị (mặc định: `8`).
  - `columns`, `columns__md`, `columns__sm`: `1`, `2`, `3`, `4`, `5`.
  - `layout`: `grid`, `list`, `table`.
  - `fields`: Danh sách trường và độ rộng hiển thị (ví dụ: `thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, price:50%, button:100%`).
  - `image_height`: Chiều cao ảnh thumbnail (ví dụ: `220px`).
  - `title_tag`: `h2`, `h3`, `h4`.
  - `button_text`: `Xem Chi Tiết`, `Mua Ngay`.
  - `card_radius`, `card_bg`, `card_border`, `card_shadow`: Bo góc và kiểu dáng card.
* **Cú pháp Blog Grid:**
```html
[vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
```
* **Cú pháp Product Grid:**
```html
[vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay" card_radius="16px"]
```

### 7.2. Dynamic Post Meta & ACF Field Tags
Trong bất kỳ phần tử text nào (`[vbc_p]`, `[vbc_span]`, `[vbc_h2]`), AI có thể sử dụng các thẻ merge tag động:
- `{{post_title}}`: Tiêu đề bài viết/sản phẩm hiện tại.
- `{{post_date}}`: Ngày đăng bài.
- `{{post_author}}`: Tên tác giả.
- `{{post_excerpt}}`: Đoạn trích tóm tắt.
- `{{meta:gia_khuyen_mai}}`: Trích xuất giá trị Post Meta Key WordPress.
- `{{acf:so_dien_thoai_hotline}}`: Trích xuất trường Advanced Custom Fields (ACF).

---

## QUY TẮC BIÊN DỊCH CSS & RESPONSIVE

### 1. Cấu Trúc Khối `custom_css`
Tất cả CSS phải được viết lồng trong `selector`:
```css
custom_css="selector {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
selector:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}
@media (max-width: 849px) {
    selector {
        padding: 16px;
        flex-direction: column;
        gap: 16px;
    }
}
@media (max-width: 549px) {
    selector {
        padding: 12px;
    }
}"
```

### 2. Tự Động Nạp Google Fonts
Chỉ cần khai báo tên font trong thuộc tính `font_family="Outfit"`, plugin sẽ tự động inject thẻ Google Fonts WebFont tương ứng vào `<head>` hoặc `wp_footer`:
```html
[vbc_h1 font_family="Outfit" custom_css="selector { font-weight: 800; }"]Tiêu Đề Đẹp[/vbc_h1]
```

---

## QUY TẮC LỒNG NHAU (NESTING TAG SUFFIXES)

Flatsome / WordPress Shortcode Parser sẽ bị lỗi nếu lồng cùng 1 tên shortcode vào nhau mà không đổi suffix. 
**Quy tắc bất di bất dịch cho AI**:

| Cấp lồng | Cú pháp Shortcode bắt buộc |
|:---|:---|
| **Cấp 1 (Ngoài cùng)** | `[vbc_div]`, `[vbc_block]`, `[vbc_container]`, `[vbc_tabs]`, `[vbc_post]` |
| **Cấp 2 (Con trực tiếp)** | `[vbc_div_inner]`, `[vbc_block_inner]`, `[vbc_container_inner]`, `[vbc_tabs_inner]` |
| **Cấp 3** | `[vbc_div_inner_1]`, `[vbc_block_inner_1]`, `[vbc_container_inner_1]` |
| **Cấp 4** | `[vbc_div_inner_2]`, `[vbc_block_inner_2]`, `[vbc_container_inner_2]` |
| **Cấp 5** | `[vbc_div_inner_3]`, `[vbc_block_inner_3]`, `[vbc_container_inner_3]` |

**Ví dụ lồng chuẩn**:
```html
[vbc_div id="wrapper"]
    [vbc_div_inner class="inner-wrap"]
        [vbc_block custom_css="selector { display: grid; }"]
            [vbc_block_inner custom_css="selector { display: flex; }"]
                [vbc_p]Nội dung lồng hợp lệ 100%[/vbc_p]
            [/vbc_block_inner]
        [/vbc_block]
    [/vbc_div_inner]
[/vbc_div]
```

---

## BẢNG TRA CỨU NHANH ÁNH XẠ HTML ➔ VBC

| Thẻ HTML Gốc | Phần tử VBC Tương Ứng | Nhóm | Lưu ý quan trọng cho AI |
|:---|:---|:---|:---|
| `<section>`, `<div class="wrapper">` | `[vbc_div]` | Container | Bọc toàn section ngoài cùng |
| `<div class="container">` | `[vbc_box]` | Container | Khung giới hạn max-width căn giữa |
| `<div class="row">`, `<div class="grid">` | `[vbc_block]` | Container | Layout phân cột Grid / Flexbox |
| `<div class="col">`, `<div class="item">` | `[vbc_container]` | Container | Cột hoặc khối item con |
| `<h1>` đến `<h6>` | `[vbc_h1]` đến `[vbc_h6]` | Typography | Tiêu đề SEO |
| `<p>` | `[vbc_p]` | Typography | Đoạn văn bản |
| `<span>` | `[vbc_span]` | Typography | Text nội dòng |
| `<a>` | `[vbc_a]` | Typography | Dùng `link_url="..."` thay vì `href` |
| `<b>`, `<strong>` | `[vbc_strong]`, `[vbc_b]` | Typography | Chữ in đậm |
| `<i>`, `<em>` | `[vbc_em]` | Typography | Chữ in nghiêng |
| `<u>` | `[vbc_u]` | Typography | Chữ gạch chân |
| `<ul>`, `<ol>`, `<li>` | `[vbc_ul]`, `[vbc_ol]`, `[vbc_li]` | List | Danh sách có thứ tự / không thứ tự |
| `<table>`, `<tr>`, `<th>`, `<td>` | `[vbc_table]`, `[vbc_tr]`, `[vbc_th]`, `[vbc_td]` | Table | Bảng biểu |
| `<img>` | `[vbc_img]` | Void | **Tự đóng, KHÔNG có thẻ `[/vbc_img]`** |
| `<hr>` | `[vbc_hr]` | Void | **Tự đóng** |
| `<br>` | `[vbc_br]` | Void | **Tự đóng** |
| `<svg>`, `<i class="fa...">` | `[vbc_icon]` | Icon | Hỗ trợ 5 bộ icon vector |
| `<form>` (Contact form) | `[contact-form-7]` | Component | Tích hợp Form liên hệ |
| Khối Card / Hộp tính năng | `[vbc_card]` | Component | Glassmorphism, đổ bóng, viền sáng |
| Đánh giá khách hàng | `[vbc_testimonial]` | Component | Xếp hạng sao, avatar, chức vụ |
| Hỏi đáp / FAQ | `[vbc_accordion]` | Component | Tự động sinh Schema FAQPage |
| Tab chuyển đổi | `[vbc_tabs]` + `[vbc_tab]` | Component | Giao diện Tabbed Navigation |
| Nút bấm CTA | `[vbc_button]` | Component | Nút gradient, 3d, glass, danger |
| Banner trượt / Carousel | `[vbc_slider]` + `[vbc_slide]` | Component | Thư viện Splide.js mượt mà |
| Danh sách bài viết / CPT | `[vbc_post]` | Dynamic | Tự động query database WordPress |
