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

### C. Thư Viện Icon Thông Minh (`[vbc_icon]`)
> [!IMPORTANT]
> **QUY TẮC BẮT BUỘC**: KHÔNG BAO GIỜ sử dụng các ký tự Emoji Unicode thô (ví dụ: 🔥, ⚡, 🚨, 🛑) vì sẽ bị WordPress tự động chuyển thành các ảnh SVG xấu (`s.w.org`). **BẮT BUỘC** luôn dùng `[vbc_icon]` với các vector icon từ FontAwesome 6, Remix Icon, Lucide...

* Nạp **duy nhất** thư viện icon được gọi (Selective Lazy Loading), hoàn toàn không làm nặng website.
* Hỗ trợ 5 bộ icon hàng đầu thế giới:
  1. `pack="lucide"` (Khuyên dùng): `name="shield-check"`, `name="zap"`, `name="phone"`, `name="check-circle"`
  2. `pack="fontawesome"` (Font Awesome 6): `name="fa-solid fa-shield-halved"`
  3. `pack="remix"` (Remix Icon): `name="ri-shield-check-line"`
  4. `pack="phosphor"` (Phosphor Icons): `name="ph-bold ph-shield-check"`
  5. `pack="material"` (Google Material Symbols): `name="security"`
* Thuộc tính: `color="#ef4444"`, `size="24px"`, `custom_class="..."`, `custom_css="..."`.
* Ví dụ: `[vbc_icon pack="lucide" name="shield-check" color="#2563eb" size="20px"]`

### D. Thuộc tính CSS Tự Do (`custom_css`) & Quy Tắc Flexbox/Grid
Sử dụng từ khóa `selector` để định vị phần tử hiện tại.
* **Tất cả các thuộc tính căn chỉnh nâng cao (`display: flex`, `align-items`, `justify-content`, `gap`, `grid-template-columns`, `border-radius`, `box-shadow`, `color`) BẮT BUỘC khai báo trong `custom_css="selector { ... }"`**.
* Ví dụ: `custom_css="selector { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-radius: 12px; } selector:hover { transform: translateY(-3px); }"`

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
| ❌ **Dùng `href="..."` thay vì `link_url="..."`** | Thẻ `[vbc_a]` sẽ bị thiếu link đích | Dùng đúng `[vbc_a link_url="..." link_target="_blank"]` |
| ❌ **Viết sai cú pháp comment HTML (`<-- ... -->`)** | Làm lộ comment thô ra ngoài trang web | Luôn viết đúng chuẩn `<!-- ... -->` |
| ❌ **Độ tương phản thấp (&lt; 4.5:1)** | Chữ xám nhạt trên nền trắng gây mỏi mắt và vi phạm chuẩn tiếp cận | Luôn dùng `#0f172a` cho heading và `#475569` cho body |

---

## 6. REST API & CLI Skill

### REST API Endpoints
* **Tải ảnh lên thư viện**: `POST /wp-json/vbc/v1/upload` (Xác thực qua header `X-VBC-Token`)
* **Đăng/Cập nhật trang**: `POST /wp-json/vbc/v1/page` (Xác thực qua header `X-VBC-Token`)

### Sử Dụng CLI Tool (Tích hợp Smart Linter & Sanitizer)
```bash
node skills/create-landing-page.js --title "Tiêu đề trang" --slug "duong-dan-tinh" --file "duong-dan-file-shortcode.txt"
```
*(CLI tự động chạy bộ Stack-based Tokenizer, tự động chuẩn hóa thẻ liên kết, gom thuộc tính Flex/Grid vào selector và cảnh báo lỗi lồng thẻ trước khi xuất bản)*

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
1. Agent tiến hành soạn thảo shortcode theo đúng Design System đã duyệt (100% `[vbc_icon]`, CSS selector chuẩn, không lồng `[row]`).
2. Lưu shortcode vào tệp `.txt` tạm.
3. Thực thi script CLI `skills/create-landing-page.js`.
4. Dọn dẹp tệp tạm, kiểm tra `git status` và gửi link bài viết hoàn tất cho người dùng.