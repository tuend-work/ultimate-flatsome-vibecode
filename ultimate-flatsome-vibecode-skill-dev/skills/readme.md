# Ultimate Flatsome VibeCode Elements & Page Publisher

Plugin WordPress hỗ trợ mở rộng Flatsome UX Builder bằng cách bổ sung các phần tử HTML cơ bản tích hợp sâu, hỗ trợ Responsive hoàn hảo, biên dịch CSS trực tiếp, tích hợp trí tuệ thiết kế **UI-UX Design Intelligence** và xuất bản Landing Page qua REST API.

---

## 1. Hệ thống Shortcodes Tùy Chỉnh (VibeCode Elements)

Các shortcode của VibeCode bắt đầu bằng tiền tố `vbc_`. Chúng hỗ trợ cấu hình Responsive (Desktop, Tablet `__md` ở 849px, Mobile `__sm` ở 549px) cho các thuộc tính CSS như `width`, `height`, `margin`, `padding`, `font_size`, `font_weight`, `text_align`, `display`, `background_color`.

### A. Nhóm Container (Có thẻ đóng)
* `[vbc_div]`, `[vbc_p]`, `[vbc_span]`, `[vbc_a]`, `[vbc_h1]`...`[vbc_h6]`, `[vbc_ul]`, `[vbc_ol]`, `[vbc_li]`, `[vbc_table]`, `[vbc_tr]`, `[vbc_td]`, `[vbc_th]`, `[vbc_b]`, `[vbc_strong]`, `[vbc_em]`, `[vbc_u]`.
* **Bí danh của `div` (Để lồng ghép tránh xung đột bộ parser của WordPress)**:
  - `[vbc_box]` (tương đương `div`)
  - `[vbc_block]` (tương đương `div`)
  - `[vbc_container]` (tương đương `div`)

### B. Nhóm Void (Tự đóng)
* `[vbc_hr]`, `[vbc_br]`
* `[vbc_img]` (Thuộc tính: `img_source="default|manual|post_meta|acf" img_attachment="ID" alt="..."`)

### C. Thư Viện Icon & Media Thông Minh (`[vbc_icon]`)
> [!IMPORTANT]
> **QUY TẮC BẮT BUỘC**: KHÔNG BAO GIỜ sử dụng các ký tự Emoji Unicode thô (ví dụ: 🔥, ⚡, 🚨, 🛑) vì sẽ bị WordPress tự động chuyển thành các ảnh SVG xấu (`s.w.org`). **BẮT BUỘC** luôn dùng `[vbc_icon]` với các vector icon hoặc ảnh SVG từ Thư viện Media.

* Hỗ trợ 2 chế độ chính:
  1. **Chọn Ảnh / SVG từ Thư viện WordPress**: Tích hợp nút **Select Image** chuẩn Flatsome qua thuộc tính `image_id="123"` hoặc `image_url="https://..."`.
  2. **Vector Icon Thông Minh**: Hỗ trợ 5 bộ icon hàng đầu thế giới với bộ chọn trực quan trong UX Builder:
     - `icon_type="lucide"`: `name="shield-check"`, `name="zap"`, `name="rocket"`, `name="award"`
     - `icon_type="fontawesome"`: `name="fa-solid fa-shield-halved"`, `name="fa-solid fa-bolt"`
     - `icon_type="remix"`: `name="ri-shield-check-line"`
     - `icon_type="material"`: `name="shield"`, `name="bolt"`
     - `icon_type="phosphor"`: `name="ph ph-shield-check"`
* **Cấu hình giao diện & huy hiệu (Badge Styling)**:
  - `size="32px"` (Hỗ trợ responsive `size__md`, `size__sm`).
  - `color="#2563eb"`, `hover_color="#1d4ed8"`.
  - `background_color="#eff6ff"`, `padding="12px"`, `border_radius="50%"`, `border_color="#bfdbfe"`, `box_shadow="0 8px 20px rgba(37,99,235,0.15)"`.
  - `display="inline-flex"`, `margin="0 10px 0 0"`.
* **Ví dụ Shortcode**:
  - Vector Icon có nền huy hiệu tròn:
    ```html
    [vbc_icon icon_type="lucide" name="shield-check" color="#2563eb" size="28px" background_color="#eff6ff" padding="12px" border_radius="50%" border_color="#bfdbfe"]
    ```
  - Ảnh SVG từ Media Library:
    ```html
    [vbc_icon icon_type="image" image_id="120" size="40px"]
    ```

### D. Thuộc tính CSS Tự Do (`custom_css`) & Quy Tắc Flexbox/Grid
Sử dụng từ khóa `selector` để định vị phần tử hiện tại.
* **Tất cả các thuộc tính căn chỉnh nâng cao (`display: flex`, `align-items`, `justify-content`, `gap`, `grid-template-columns`, `border-radius`, `box-shadow`, `color`) BẮT BUỘC khai báo trong `custom_css="selector { ... }"`**.
* Ví dụ: `custom_css="selector { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-radius: 12px; } selector:hover { transform: translateY(-3px); }"`

### E. Thành Phần Danh Sách Bài Viết / Sản Phẩm Động (`[vbc_post]`)
Cho phép truy vấn và xuất bản danh sách bài viết (`post`), sản phẩm (`product`), trang (`page`), hoặc Custom Post Type (`cpt`) bất kỳ. Hỗ trợ chọn chính xác các trường dữ liệu cần hiển thị, tùy biến độ rộng cột cho từng trường và sắp xếp vị trí linh hoạt.

#### 1. Các Nhóm Thuộc Tính Chính:
* **Nguồn dữ liệu (Query)**:
  - `post_type`: `post` (mặc định), `product`, `page`, `any`, hoặc tên CPT (ví dụ: `du-an`, `dich-vu`).
  - `ids`: Danh sách Post ID cụ thể (ví dụ: `ids="12,34,56"`).
  - `taxonomy`: Tên phân loại để lọc (ví dụ: `category`, `product_cat`, `post_tag`, `linh-vuc`).
  - `terms`: Slug hoặc ID chuyên mục (ví dụ: `terms="thiet-ke-web,hosting"` hoặc `terms="12,34"`).
  - `operator`: `IN` (mặc định), `AND`, `NOT IN`.
  - `posts_per_page`: Số bài hiển thị (mặc định `8`, `-1` để lấy tất cả).
  - `orderby`: `date` (mới nhất), `title`, `menu_order`, `rand`, `post__in`, `modified`, `comment_count`, `meta_value_num`, `meta_value`, `ID`.
  - `order`: `DESC` (giảm dần), `ASC` (tăng dần).
  - `meta_key` & `meta_value`: Lọc bài viết theo trường Custom Field.

* **Bố cục & Lưới (Layout)**:
  - `layout`: `grid` (Dạng lưới thẻ Card), `list` (Dạng danh sách hàng ngang), `table` (Dạng bảng danh sách).
  - `columns`: Số cột hiển thị Desktop (`1`, `2`, `3`, `4`, `5`, `6`).
  - `columns__md`: Số cột trên Tablet (mặc định `2`).
  - `columns__sm`: Số cột trên Mobile (mặc định `1`).
  - `gap`: Khoảng cách giữa các bài (ví dụ `24px`).
  - `pagination`: `none` hoặc `numeric` (Phân trang 1, 2, 3...).

* **Cấu Hình Trường Xuất Ra & Độ Rộng Cột (`fields`)**:
  Cấu hình danh sách các trường muốn hiển thị theo thứ tự, định dạng `field_name:width` (Ví dụ: `fields="thumbnail:100%, title:100%, price:50%, button:50%"`).
  - `thumbnail`: Ảnh đại diện bài viết / sản phẩm (Kèm nhãn SALE nếu là sản phẩm khuyến mãi).
  - `title`: Tiêu đề bài viết kèm liên kết (Hỗ trợ cấu hình `title_tag`, `title_size`, `title_color`, `title_lines`).
  - `price`: Giá sản phẩm WooCommerce hoặc Custom Field giá (Hỗ trợ cấu hình `price_color`, `price_size`).
  - `excerpt`: Tóm tắt nội dung (Cấu hình `excerpt_length`, `excerpt_color`).
  - `date`: Ngày đăng bài kèm icon lịch.
  - `author`: Tác giả bài viết kèm icon người dùng.
  - `categories` / `terms`: Huy hiệu nhãn danh mục chuyên mục.
  - `tags`: Huy hiệu thẻ bài viết / sản phẩm.
  - `button`: Nút bấm hành động (Xem chi tiết / Mua ngay).
  - `rating`: Đánh giá số sao (WooCommerce).
  - `sku`: Mã SKU sản phẩm.
  - `meta:meta_key`: Trường Custom Field tùy chỉnh (Ví dụ: `meta:dien_tich:50%:m²`).
  - `acf:acf_key`: Trường ACF (Hỗ trợ text, số, ảnh, link).

#### 2. Ví dụ Sử Dụng Thực Tế:

* **Lưới Sản Phẩm WooCommerce Nổi Bật (Grid 4 Cột)**:
  ```html
  [vbc_post post_type="product" taxonomy="product_cat" terms="noi-bat" columns="4" columns__md="2" columns__sm="1" posts_per_page="8" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" card_bg="#ffffff" card_radius="16px" card_shadow="0 10px 25px rgba(0,0,0,0.05)"]
  ```

* **Danh Sách Tin Tức / Dịch Vụ Theo IDs Nhập Vào (Grid 3 Cột)**:
  ```html
  [vbc_post post_type="post" ids="12,34,56" columns="3" fields="thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, button:50%" button_text="Xem Chi Tiết"]
  ```

* **Bảng Báo Giá / Thông Số Bất Động Sản (Table List kèm Custom Fields)**:
  ```html
  [vbc_post post_type="du-an" layout="table" posts_per_page="10" fields="thumbnail:80px, title:auto, meta:dia_chi:200px, meta:gia_ban:120px, button:140px"]
  ```

---

## 2. Kết Hợp Các Phần Tử Mặc Định Của Flatsome (Khuyên Dùng)

Để Landing Page hoạt động hoàn hảo và kế thừa tối đa thiết kế của Flatsome, hãy kết hợp các phần tử VibeCode với các shortcode Flatsome mặc định dưới đây:

### A. Hệ thống Lưới & Layout
* **`[row]` và `[col]`**: Dùng chia cột responsive mặc định của Flatsome ở cấp độ Section.
  - Cấu trúc: `[row v_align="middle"] [col span="6" span__sm="12"] ... [/col] [col span="6" span__sm="12"] ... [/col] [/row]`
* > [!WARNING]
  > **KHÔNG BAO GIỜ lồng `[row]` bên trong `[col]`**. Để chia cột bên trong một card hoặc cột, **BẮT BUỘC** dùng CSS Grid hoặc Flexbox:
  > `[vbc_block custom_css="selector { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; } @media(max-width: 549px){ selector { grid-template-columns: 1fr; } }"]`

### B. Khối Thẻ Liên Kết (`[vbc_a]`)
* Thuộc tính chuẩn: `link_url="https://..."` và `link_target="_blank|_self"`.
* Ví dụ nút bấm:
  `[vbc_a link_url="https://zalo.me/..." link_target="_blank" custom_css="selector { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 14px 28px; background: #2563eb; color: #ffffff !important; border-radius: 8px; text-decoration: none; font-weight: 700; } selector:hover { background: #1d4ed8; } selector span { color: #ffffff !important; }"] [vbc_icon pack="lucide" name="zap" color="#ffffff" size="18px"] [vbc_span]Yêu Cầu Ngay[/vbc_span] [/vbc_a]`

### C. Khối Hỏi Đáp (`[accordion]`)
* Rất tốt cho SEO nhờ hỗ trợ Schema FAQ trực tiếp của Flatsome.
* Định dạng:
  ```html
  [accordion faq_schema="true"]
      [accordion-item title="Câu hỏi 1 Title"]Câu trả lời 1...[/accordion-item]
      [accordion-item title="Câu hỏi 2 Title"]Câu trả lời 2...[/accordion-item]
  [/accordion]
  ```

---

## 3. Quy Tắc Lồng Ghép Thẻ Div (Nesting Best Practice)

> [!WARNING]
> Bộ phân tích cú pháp WordPress shortcode **không hỗ trợ** lồng hai thẻ trùng tên nhau (ví dụ: `[vbc_div] ... [vbc_div] ... [/vbc_div] ... [/vbc_div]` sẽ làm lộ thẻ đóng).

**Quy tắc đúng khi cần lồng các khối Div**:
Sử dụng tuần tự các thẻ bí danh theo cấp bậc:
1. Cấp 1 (Ngoài cùng - Section): `[vbc_div]`
2. Cấp 2 (Container 1200px): `[vbc_box]`
3. Cấp 3 (Cột / Khối nội dung): `[vbc_block]`
4. Cấp 4 (Phần tử con / Badge / Card item): `[vbc_container]`

---

## 4. Trí Tuệ Thiết Kế UI-UX (Design Intelligence Engine)

Để Landing Page đạt chuẩn quốc tế (Aesthetics WOW, Tỷ lệ chuyển đổi CRO cao), Agent tuân thủ hệ thống ma trận thiết kế dưới đây:

### A. Ma Trận 6 Phong Cách Thiết Kế (UI Styles)

| Style | Tone Màu Chủ Đạo | Typography | Đặc Trưng Visual | Phù Hợp Cho |
|---|---|---|---|---|
| **Stripe Clean Enterprise** | `#ffffff`, `#f8fafc`, `#2563eb`, `#0f172a` | Inter / Plus Jakarta Sans | Viền mỏng 1px `#e2e8f0`, đổ bóng sắc nét, tối giản, thanh lịch | SaaS, B2B, Tech Service, Tài chính |
| **Sleek Dark Tech** | `#090d16`, `#0f172a`, `#38bdf8`, `#10b981` | Outfit / Lexend | Glassmorphism blur 16px, viền mờ `rgba(255,255,255,0.08)`, Neon glow | AI, Dev Tools, Hosting, Security, Crypto |
| **Luxury Editorial** | `#ffffff`, `#faf9f6`, `#1c1917`, `#d97706` | Cormorant Garamond / Montserrat | Chữ Serif sang trọng, viền vàng kim, không gian thở lớn | Bất động sản, Spa thẩm mỹ, Trang sức |
| **Healthcare & Trust** | `#ffffff`, `#f0fdf4`, `#059669`, `#0284c7` | Be Vietnam Pro / Inter | Thẻ bo góc 14px, màu xanh ngọc / xanh y tế, cảm giác tin cậy | Phòng khám, Y tế, Dược phẩm, Nha khoa |
| **Neo-Brutalism** | `#ffffff`, `#fef08a`, `#000000`, `#ef4444` | Syne / Space Grotesk | Viền đen đậm `2px solid #000`, đổ bóng cứng `4px 4px 0px #000` | Agency sáng tạo, GenZ, Khóa học Kỹ thuật |
| **Claymorphism 3D** | `#f8fafc`, `#e0e7ff`, `#4f46e5`, `#ec4899` | Quicksand / Nunito | Bo góc lớn `24px`, đổ bóng kép mềm 3D, nút bấm bồng bềnh | Giáo dục trẻ em, Game, App giải trí |

### B. Hệ Thống 5 Lớp Màu Chuẩn (5-Role Color Palette)
1. **Primary Brand Color**: Màu nhận diện chính (Ví dụ `#2563eb` hoặc `#0f172a`).
2. **Secondary / Surface Color**: Màu nền của các thẻ card (Ví dụ `#ffffff` hoặc `#f8fafc`).
3. **CTA Accent Color**: Màu nút bấm hành động chuyển đổi cao (Ví dụ `#2563eb`, `#10b981`, `#f59e0b`).
4. **Section Background**: Màu nền section xen kẽ (`#ffffff` và `#f8fafc` để tạo nhịp điệu thị giác).
5. **Text Hierarchy**: Text Tiêu đề (`#0f172a`), Text Nội dung (`#475569`), Text Muted (`#94a3b8`).

### C. Bộ 3 Conversion Blueprints (Cấu Trúc Section Chuẩn CRO)

* **Blueprint 1: Dịch Vụ Cứu Hộ / Sửa Chữa Khẩn Cấp (On-Demand & Emergency Service)**:
  1. Hero Banner (Headline giải quyết nỗi đau + Hotline/Zalo nổi bật + Badge 24/7).
  2. Pain Points (Bắt bệnh nhanh - 6 thẻ sự cố thường gặp).
  3. Core Solutions (Hệ thống dịch vụ trọng tâm).
  4. 5-Step Process (Quy trình kỹ thuật an toàn dữ liệu).
  5. Transparent Pricing (Bảng giá 3 gói rõ ràng).
  6. Quality Guarantees (Cam kết an toàn, bảo hành 30-60 ngày).
  7. Social Proof & Numbers (Số liệu ấn tượng + Tech Stack).
  8. Testimonials (Đánh giá thực tế từ khách hàng).
  9. FAQ Accordion (Câu hỏi thường gặp có Schema).
  10. Final Emergency CTA (Hotline & Chat Zalo khẩn cấp).

* **Blueprint 2: B2B Enterprise & SaaS Software**:
  1. Hero (Product Value Prop + Live Dashboard Mockup + Trial CTA).
  2. Client Logo Bar (Đối tác tin tưởng).
  3. Feature Bento Grid (Các tính năng đột phá).
  4. Interactive Demo / Comparison Table (So sánh với giải pháp truyền thống).
  5. Security & Compliance (Chứng chỉ bảo mật, Uptime SLA).
  6. Tiered Pricing (Monthly/Annual toggle).
  7. Case Studies / ROI Proof (Hiệu quả thực tế).
  8. FAQ & Enterprise Contact Form.

---

## 5. Danh Sách Cấm Kỵ Tuyệt Đối (Anti-Patterns Matrix)

| Anti-Pattern | Lý Do Cấm Kỵ | Giải Pháp Bắt Buộc |
|---|---|---|
| ❌ **Hardcode `font-family` trong shortcode** | Làm hỏng hiển thị tiếng Việt có dấu và mất tính đồng bộ với Flatsome | **KHÔNG khai báo font-family** trong `custom_css`. Để trang web tự động kế thừa font chữ toàn cục đã cấu hình trong Flatsome Customizer |
| ❌ **Dùng dấu ngoặc kép thô `"` trong text** | Làm vỡ bộ phân tích thuộc tính shortcode của WordPress | Dùng HTML entities `&ldquo;` / `&rdquo;` hoặc `&quot;` cho các trích dẫn |
| ❌ **Dùng Emoji Unicode thô (🔥, ⚡, 🚨)** | WordPress core tự biến emoji thành thẻ ảnh SVG `s.w.org` xấu và làm chậm trang | Luôn dùng `[vbc_icon pack="..." name="..."]` |
| ❌ **Lồng `[row]` bên trong `[col]`** | Flatsome shortcode parser bị vỡ cú pháp và lộ thẻ đóng ra ngoài | Dùng CSS Grid `display: grid; grid-template-columns: 1fr 1fr; gap: 16px;` |
| ❌ **Thuộc tính Flex trần ngoài `custom_css`** | `align_items`, `gap`, `justify_content` trần không được PHP tự động render | Khai báo trực tiếp trong `custom_css="selector { display: flex; align-items: center; gap: 12px; }"` |
| ❌ **Tự chế thẻ shortcode không tồn tại (`[vbc_input]`, `[vbc_textarea]`, `[vbc_form]`)** | Plugin chưa đăng ký shortcode này, làm chuỗi shortcode bị in thô nguyên văn ra frontend | Dùng trực tiếp thẻ HTML chuẩn: `<input ... />`, `<textarea ...></textarea>`, `<form ...></form>` hoặc bọc chúng trong `[vbc_div]` |
| ❌ **Lồng các thẻ Container cùng tên mà không dùng alias hoặc `_inner`** | Bộ phân tích cú pháp WordPress shortcode sẽ đóng thẻ ngoài ngay khi gặp `[/tag]` đầu tiên, làm bung các thẻ đóng bên trong ra frontend | Dùng xen kẽ các bí danh `[vbc_div]` -> `[vbc_box]` -> `[vbc_block]` -> `[vbc_container]` hoặc dùng hậu tố `_inner`, `_inner_1` |
| ❌ **Gán suffix `_inner` hoặc viết thẻ đóng cho thẻ tự đóng (`[vbc_icon]`, `[vbc_img]`)** | Thẻ void không có thẻ đóng `[/...]`, nếu gán `_inner` sai sẽ làm hỏng bộ phân tích | Giữ nguyên `[vbc_icon ...]` và `[vbc_img ...]`, không thêm thẻ đóng |

---

## 6. Bộ Công Cụ CLI Skills (CLI Skills Suite)

### REST API Endpoints
* **Tải ảnh lên thư viện**: `POST /wp-json/vbc/v1/upload` (Xác thực qua header `X-VBC-Token`)
* **Đăng/Cập nhật trang**: `POST /wp-json/vbc/v1/page` (Xác thực qua header `X-VBC-Token`)

---

### A. Skill 1: Xuất Bản Landing Page Từ Shortcode (`create-landing-page.js`)
Công cụ xuất bản nhanh từ file shortcode soạn sẵn kèm bộ linter & sanitizer tự động và tự động kiểm tra xác thực frontend live.
```bash
node skills/create-landing-page.js --title "Tiêu đề trang" --slug "duong-dan-tinh" --file "duong-dan-file-shortcode.txt"
```

---

### B. Skill 2: Clone Giao Diện Đa Nguồn Về Flatsome VibeCode (`clone-landingpage.js`)
Công cụ mạnh mẽ hỗ trợ chuyển đổi mọi nguồn giao diện (URL trực tiếp, HTML, ZIP, Screenshot) thành cấu trúc VibeCode chuẩn Flatsome:

1. **Clone từ URL trang web trực tiếp**:
   ```bash
   node skills/clone-landingpage.js --url "https://example.com/landing-mau" --title "Trang Mẫu Clone" --slug "trang-mau-clone"
   ```
2. **Clone từ file HTML cục bộ**:
   ```bash
   node skills/clone-landingpage.js --html "templates/saas-dark.html" --title "SaaS Dark Template" --slug "saas-dark-template"
   ```
3. **Clone từ gói ZIP template**:
   ```bash
   node skills/clone-landingpage.js --zip "downloads/agency-theme.zip" --title "Agency Landing" --slug "agency-landing"
   ```
4. **Chế độ kiểm tra xem trước không xuất bản (Dry Run)**:
   ```bash
   node skills/clone-landingpage.js --url "https://example.com" --dry-run
   ```
*(Tự động trích xuất CSS inline, tải và upload toàn bộ ảnh lên WordPress Media Library, tự động gom thuộc tính Flex/Grid, chuẩn hóa nesting `_inner` và tự động verify 0 unparsed shortcodes trên frontend)*

---

### C. Quy Tắc Chụp Ảnh Xác Thực Trực Quan (Fast 1-Shot Visual Verification)
* **Tuyệt đối không chụp ảnh từng phần nhỏ, không cuộn chụp nhiều lần gây mất thời gian**.
* **Luôn chụp 1 lần duy nhất toàn bộ trang (Single Full-Page Screenshot)** bằng cách bật `CaptureBeyondViewport: true` trong Browser tool.
* Sau khi chụp, hiển thị ảnh chụp toàn trang hoàn chỉnh cho người dùng đối soát ngay.

---

## 7. Quy Trình Tạo Landing Page Qua Skill (Interactive 2-Step Workflow)

### **Bước 1: Phân Tích Ngành Nghề & Đề Xuất Design System Box**
Agent trình bày bảng thông số và Design System gợi ý:

```markdown
📋 **BẢNG XÁC NHẬN THÔNG TIN & HỆ THỐNG THIẾT KẾ (DESIGN SYSTEM)**

Vui lòng kiểm tra hoặc tùy chỉnh các thông số dưới đây trước khi tiến hành khởi tạo:

1. 📌 **Tiêu đề bài viết (Title)**: [Tiêu đề chuẩn SEO]
2. 🔗 **Đường dẫn tĩnh (Slug)**: [slug-tieng-viet-khong-dau]
3. 🎨 **Phong cách Đề xuất (Style)**: Stripe Clean Enterprise / Sleek Dark Tech...
4. 💎 **Bảng Màu (5 Roles)**:
   - Primary Brand: #2563eb | Secondary: #f8fafc | CTA Accent: #10b981 | Text: #0f172a
5. 🔤 **Bộ Font (Typography Pair)**: Lexend (Heading) + Inter (Body)
6. 🧩 **Cấu trúc Nguồn Section**: [10 Section chuẩn Conversion Blueprint]
7. 📞 **Thông tin liên hệ / Hotline**: [Số điện thoại, Zalo, địa chỉ...]

---
👉 *Bạn có thể tùy chỉnh thông tin hoặc phản hồi **"Đồng ý" / "OK"** để bắt đầu khởi tạo trang ngay!*
```

### **Bước 2: Soạn Thảo, Tối Ưu Hóa & Xuất Bản**
1. Agent tiến hành soạn thảo shortcode theo đúng Design System đã duyệt (100% `[vbc_icon]`, CSS selector chuẩn, không lồng `[row]`, các thẻ form dùng HTML chuẩn).
2. Lưu shortcode vào tệp `.txt` tạm.
3. Thực thi script CLI `skills/create-landing-page.js` (hoặc `skills/clone-landingpage.js`).
4. **BẮT BUỘC: Kiểm tra tự động kết quả Frontend**: Tải URL live của trang vừa tạo và quét kiểm tra Regex `\[/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]`.
5. Đảm bảo **100% 0 shortcode bị lộ** mới bàn giao cho người dùng.

---

## 8. QUY TẮC BẮT BUỘC: CHỐNG LỖI HIỂN THỊ SHORTCODE RA NGOÀI FRONTEND

> [!CAUTION]
> Tuyệt đối **KHÔNG ĐƯỢC** để bất kỳ chuỗi shortcode nào (như `[vbc_...]` hay `[/vbc_...]`) hiển thị thô ra ngoài trang web cho khách hàng thấy.

### 5 Nguyên nhân cốt lõi gây lỗi lộ shortcode & Cách phòng tránh:
1. **Lồng các thẻ cùng tên (Nesting collision)**:
   - *Nguyên nhân*: WordPress shortcode parser không hỗ trợ lồng 2 thẻ cùng tên (như `[vbc_div] ... [vbc_div] ... [/vbc_div] ... [/vbc_div]`). Thẻ đóng đầu tiên sẽ kết thúc thẻ mở ngoài cùng, làm lộ các thẻ đóng bên trong ra ngoài trang web.
   - *Quy tắc*: Bắt buộc dùng bí danh xen kẽ (`vbc_box`, `vbc_block`, `vbc_container`) hoặc gán suffix `_inner`, `_inner_1`, `_inner_2`.
2. **Tự chế các shortcode không tồn tại**:
   - *Nguyên nhân*: Viết `[vbc_input]`, `[vbc_textarea]`, `[vbc_select]`, `[vbc_form]` trong khi plugin chưa đăng ký các shortcode này.
   - *Quy tắc*: Dùng thẻ HTML chuẩn `<input ... />`, `<textarea ...></textarea>`, `<select ...></select>`, `<form ...></form>` hoặc bọc chúng trong `[vbc_div]`.
3. **Thẻ tự đóng (Void tags) bị gán suffix hoặc thẻ đóng sai**:
   - *Nguyên nhân*: `[vbc_icon]`, `[vbc_img]`, `[vbc_hr]`, `[vbc_br]` là thẻ tự đóng, KHÔNG có `[/vbc_icon]` và KHÔNG được gán `_inner`.
4. **Sai dấu ngoặc kép hoặc quote không cân đối**:
   - *Nguyên nhân*: Dùng dấu nháy kép `"` thô bên trong `content="..."` hoặc `custom_css="selector { ... }"` làm vỡ regex attribute của WordPress.
   - *Quy tắc*: Dùng `&quot;` trong content hoặc kiểm tra kỹ dấu ngoặc nhọn `{ }`.
5. **QUY TRÌNH KIỂM TRA BẮT BUỘC (MANDATORY LIVE CHECK)**:
   - Sau khi tạo/sửa trang, Agent **BẮT BUỘC** phải tải lại HTML của trang live và quét regex:
     `\[/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]`
   - Chỉ khi kết quả trả về **0 unparsed shortcodes** thì mới được coi là hoàn tất công việc!