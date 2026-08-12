# Ultimate Flatsome VibeCode Elements & Page Publisher

Plugin WordPress hỗ trợ mở rộng Flatsome UX Builder bằng cách bổ sung các phần tử HTML cơ bản tích hợp sâu, hỗ trợ Responsive hoàn hảo, biên dịch CSS trực tiếp và xuất bản Landing Page qua REST API.

---

## 1. Hệ thống Shortcodes Tùy Chỉnh (VibeCode Elements)

Các shortcode của VibeCode bắt đầu bằng tiền tố `vbc_`. Chúng hỗ trợ cấu hình Responsive (Desktop, Tablet `__md` ở 849px, Mobile `__sm` ở 549px) cho các thuộc tính CSS như `width`, `height`, `margin`, `padding`, `font_size`, `text_align`, `display`, `background_color`.

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
  1. `pack="fontawesome"` (Font Awesome 6): `name="fa-solid fa-shield-halved"`
  2. `pack="remix"` (Remix Icon): `name="ri-shield-check-line"`
  3. `pack="lucide"` (Lucide Icons): `name="shield-check"`
  4. `pack="phosphor"` (Phosphor Icons): `name="ph-bold ph-shield-check"`
  5. `pack="material"` (Google Material Symbols): `name="security"`
* Thuộc tính: `color="#ef4444"`, `size="24px"`, `custom_class="..."`, `custom_css="..."`.
* Ví dụ: `[vbc_icon pack="fontawesome" name="fa-solid fa-shield-halved" color="#ef4444" size="32px"]`

### D. Thuộc tính CSS Tự Do (`custom_css`)
Sử dụng từ khóa `selector` để định vị phần tử hiện tại.
* Ví dụ: `custom_css="selector { border-radius: 12px; transition: 0.3s; } selector:hover { transform: translateY(-5px); }"`

---

## 2. Kết Hợp Các Phần Tử Mặc Định Của Flatsome (Khuyên Dùng)

Để Landing Page hoạt động hoàn hảo và kế thừa tối đa thiết kế của Flatsome, hãy kết hợp các phần tử VibeCode với các shortcode Flatsome mặc định dưới đây:

### A. Hệ thống Lưới & Layout
* **`[row]` và `[col]`**: Dùng chia cột responsive mặc định của Flatsome.
  - Cấu trúc: `[row] [col span="4" span__sm="12"] ... [/col] [/row]`
  - Thuộc tính: `span` (Desktop), `span__md` (Tablet), `span__sm` (Mobile) từ 1 đến 12.

### B. Nút Bấm Flatsome (`[button]`)
* Kế thừa hoàn hảo các style nút thiết kế sẵn của Flatsome (outline, shade, bevel, gloss).
* Định dạng: `[button text="Đăng ký" color="primary|secondary|alert|success|white" style="outline|shade" size="medium|larger" radius="30" link="URL"]`

### C. Khối Hỏi Đáp (`[accordion]`)
* Rất tốt cho SEO nhờ hỗ trợ Schema FAQ trực tiếp của Flatsome.
* Định dạng:
  ```html
  [accordion faq_schema="true"]
      [accordion-item title="Câu hỏi 1 Title"]Câu trả lời 1...[/accordion-item]
      [accordion-item title="Câu hỏi 2 Title"]Câu trả lời 2...[/accordion-item]
  [/accordion]
  ```

### D. Đánh Giá Khách Hàng (`[testimonial]`)
* Tạo các card nhận xét từ khách hàng đẹp mắt tích hợp sao đánh giá.
* Định dạng: `[testimonial name="Tên" company="Công ty" stars="5" pos="left|center|top" image="ID_Ảnh"] Nội dung đánh giá... [/testimonial]`

### E. Khoảng Cách & Tiêu Đề
* **`[gap]`**: Thêm khoảng trống dọc giữa các phần. Ví dụ: `[gap height="30px"]`.
* **`[ux_banner]`**: Làm banner nền lớn (Hero background) hỗ trợ phủ mờ và parallax.

---

## 3. Quy Tắc Lồng Ghép Thẻ Div (Nesting Best Practice)

> [!WARNING]
> Bộ phân tích cú pháp WordPress shortcode **không hỗ trợ** lồng hai thẻ trùng tên nhau (ví dụ: `[vbc_div] ... [vbc_div] ... [/vbc_div] ... [/vbc_div]` sẽ làm lộ thẻ đóng).

**Quy tắc đúng khi cần lồng các khối Div**:
Sử dụng tuần tự các thẻ bí danh theo cấp bậc:
1. Cấp 1 (Ngoài cùng): `[vbc_div]`
2. Cấp 2: `[vbc_box]`
3. Cấp 3: `[vbc_block]`
4. Cấp 4 (Trong cùng): `[vbc_container]`

Ví dụ:
```html
[vbc_div class="outer-card"]
    [vbc_box class="inner-header"]
        [vbc_block class="inner-title"]...[/vbc_block]
    [/vbc_box]
[/vbc_div]
```

---

## 4. REST API & CLI Skill

### REST API Endpoints
* **Tải ảnh lên thư viện**: `POST /wp-json/vbc/v1/upload` (Xác thực qua header `X-VBC-Token`)
* **Đăng/Cập nhật trang**: `POST /wp-json/vbc/v1/page` (Xác thực qua header `X-VBC-Token`)

### Sử dụng CLI Tool
Công cụ giúp đẩy file nội dung shortcode cục bộ trực tiếp lên website.
```bash
node skills/create-landing-page.js --title "Tiêu đề trang" --slug "duong-dan-tinh" --file "duong-dan-file-shortcode.txt" --image-upload "anh1.jpg,anh2.png"
```
*(Tự động thay thế các placeholder dạng `{{image_1_url}}` và `{{image_1_id}}` trong file shortcode bằng thông tin ảnh sau khi upload)*

---

## 5. Quy Trình Tạo Landing Page Qua Skill (Interactive 2-Step Workflow)

Khi người dùng yêu cầu tạo Landing Page mới qua Skill, Agent **BẮT BUỘC** tuân thủ quy trình 2 bước sau:

### **Bước 1: Hiển thị Mẫu Gợi Ý Đầu Vào (Form Input Template)**
Agent trình bày bảng thông số gợi ý cấu hình dưới đây để người dùng dễ dàng copy, chỉnh sửa hoặc phản hồi:

```markdown
📋 **BẢNG XÁC NHẬN THÔNG TIN TẠO LANDING PAGE**

Vui lòng kiểm tra hoặc tùy chỉnh các thông số dưới đây trước khi tiến hành khởi tạo:

1. 📌 **Tiêu đề bài viết (Title)**: [Tiêu đề đầy đủ chuẩn SEO]
2. 🔗 **Đường dẫn tĩnh (Slug)**: [slug-baiviet-tieng-viet-khong-dau]
3. 🎨 **Phong cách Thiết kế (Style)**: 
   - [ ] Sleek Dark Minimalist (Đen huyền bí công nghệ)
   - [ ] Stripe Clean Enterprise (Trắng sáng Doanh nghiệp)
   - [ ] Claymorphism (Soft 3D Khối Đất Sét)
   - [ ] Neo-Brutalism (Viền đen đậm cá tính)
4. 💎 **Bộ Icon ưu tiên (Icon Pack)**: 
   - [ ] Font Awesome 6 (`pack="fontawesome"`)
   - [ ] Remix Icon (`pack="remix"`)
   - [ ] Lucide Icons (`pack="lucide"`)
5. 🧩 **Cấu trúc Nguồn Section**: [10 Section chuẩn conversion rate]
6. 📝 **Thông tin đặc thù (nếu có)**: [Giá dịch vụ, các gói, thông tin liên hệ...]

---
👉 *Bạn có thể copy bảng trên để tùy chỉnh thông tin hoặc phản hồi **"Đồng ý" / "OK"** để bắt đầu khởi tạo trang ngay!*
```

### **Bước 2: Tiến hành Khởi tạo & Chạy CLI Skill**
Chỉ khi người dùng nhấn Enter/Phản hồi đồng ý hoặc gửi bảng thông tin đã sửa:
1. Agent mới tiến hành soạn thảo shortcode (Dùng 100% `[vbc_icon]`, KHÔNG DÙNG Unicode Emoji thô).
2. Lưu shortcode vào tệp `.txt` tạm.
3. Thực thi script CLI `skills/create-landing-page.js`.
4. Dọn dẹp tệp tạm, kiểm tra `git status` và thông báo đường dẫn bài viết hoàn tất cho người dùng.