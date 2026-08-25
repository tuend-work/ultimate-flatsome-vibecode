---
name: create-landingpage
description: >-
  Thiết kế và xây dựng Landing Page mới chuyên nghiệp trên WordPress Flatsome từ ý tưởng, bản thảo hoặc yêu cầu của người dùng bằng 100% phần tử Ultimate Flatsome VibeCode Elements do AI trực tiếp thiết kế. Sử dụng khi người dùng yêu cầu tạo mới landing page, thiết kế trang bán hàng, giới thiệu dịch vụ.
---

# Create Landing Page (AI-First LLM Architecture)

## Mục tiêu (Goal)
Sử dụng **Trí tuệ Nhân tạo (LLM)** để thiết kế toàn diện một Landing Page đạt chuẩn UI/UX quốc tế, tỉ lệ chuyển đổi (CRO) cao, tương thích 100% với Flatsome UX Builder bằng hệ thống **Ultimate Flatsome VibeCode Elements** với thuộc tính styling đưa trực tiếp vào từng thẻ.

---

## Quy trình Thực hiện (Workflow)

### Bước 1: Phân tích Ý tưởng & Bảng Màu Thương Hiệu (Design Concept)
AI xác định:
1. **Chủ đề & Mục tiêu trang**: Bán hàng (Sales Page), Thu thập khách hàng tiềm năng (Lead Gen), Giới thiệu công ty (Corporate Landing Page).
2. **Bảng màu chủ đạo**: Primary Color, Secondary Color, Accent/CTA Color, Neutral Dark/Light Background.
3. **Cấu trúc Bố cục (Layout Sections)**:
   - Header Navigation & CTA
   - Hero Section cuốn hút kèm Form hoặc Button hành động
   - Problem / Solution & Điểm nổi bật (Highlights Grid)
   - Chi tiết Khóa học / Dịch vụ / Sản phẩm (Cards / Tabs)
   - Đội ngũ chuyên gia / Quy trình làm việc (Steps / Workflow)
   - Bằng chứng xã hội / Đánh giá khách hàng (Testimonials)
   - Bảng giá / Ưu đãi có thời hạn (Pricing Table)
   - Câu hỏi thường gặp (FAQ Accordion)
   - Form Đăng ký tư vấn cuối trang (Contact Form 7)
   - Footer thông tin thương hiệu & bản quyền

### Bước 2: Tự động Tạo Biểu Mẫu Contact Form 7 (BẮT BUỘC)
Khi thiết kế landing page có khu vực thu thập thông tin khách hàng (Hero Form, Lead Gen CTA, Form Tư Vấn, Form Đăng Ký):
1. **Xác định các trường nhập liệu cần thiết**: Họ tên, Số điện thoại, Email, Dịch vụ/Khóa học quan tâm, Lời nhắn.
2. **Chạy script sinh Form CF7 qua REST API**:
   ```bash
   python .agents/skills/create-landingpage/scripts/create_cf7.py --title "Form Tư Vấn - <Tên Landing Page>" --fields "name,phone,email,course,message" --button "Đăng ký nhận ưu đãi ngay"
   ```
3. **Lấy mã Shortcode trả về** dạng `[contact-form-7 id="<ID>" title="..."]` để nhúng vào layout VBC.
4. **Quy tắc**: 100% biểu mẫu thu thập thông tin khách hàng **BẮT BUỘC** phải được tạo thành form Contact Form 7 thực tế qua API, **TUYỆT ĐỐI KHÔNG** dùng văn bản giả lập tĩnh (`[vbc_p]`) thay cho form.
5. **BẮT BUỘC — CSS Đồng Bộ Khớp Với Bảng Màu Thiết Kế**:
   - Sau khi tạo form CF7, **viết khối CSS tùy chỉnh** để style các thành phần form khớp với bảng màu và thiết kế của landing page:
     - `input[type="text"], input[type="tel"], input[type="email"], select, textarea` → border, padding, border-radius, background, font-size.
     - `input[type="submit"]` → background-color (= màu CTA chính), color, font-weight, padding, border-radius, hover state.
   - CSS này được nhúng qua `vbc_page_custom_css` hoặc trường **Page Custom CSS** trong UX Builder.
   - **Màu sắc button PHẢI khớp với CTA Color của thiết kế** — không dùng màu xám mặc định WordPress.
   - Ví dụ CSS template tham khảo:
     ```css
     .wpcf7-form input[type="text"],
     .wpcf7-form input[type="tel"],
     .wpcf7-form input[type="email"],
     .wpcf7-form select,
     .wpcf7-form textarea {
       width: 100%; padding: 12px 16px;
       border: 1.5px solid #e2e8f0; border-radius: 8px;
       font-size: 15px; background: #f8fafc;
       margin-bottom: 12px; transition: border-color 0.2s;
     }
     .wpcf7-form input[type="submit"] {
       width: 100%; padding: 14px;
       background: <CTA_COLOR>; color: #fff;
       font-weight: 700; font-size: 16px;
       border: none; border-radius: 50px;
       cursor: pointer; transition: background 0.2s;
     }
     .wpcf7-form input[type="submit"]:hover { background: <CTA_HOVER_COLOR>; }
     ```

### Bước 3: AI Thiết Kế Bố Cục Chuẩn Flatsome (Section + Row + Col) & VBC Elements
AI trực tiếp viết mã nguồn lưu tại `tmp/<slug>/created_vbc.txt`.

#### 🏛️ Kiến Trúc Bố Cục Ưu Tiên (Layout Backbone):
1. **Khung xương Bố cục (Structure)**: **Ưu tiên sử dụng `[section]` + `[row]` + `[col]` chuẩn của Flatsome**:
   - `[section class="section-hero" bg_color="#..." padding="60px" dark="true|false"]`: **BẮT BUỘC gán `class="section-<tên>"` cho mỗi section** để làm CSS scope selector.
   - `[row width="custom" custom_width="1140px" v_align="middle|top"]`: **Toàn bộ `[row]` PHẢI nằm bên trong `[section]`** — tuyệt đối không đặt `[row]` độc lập ngoài section.
   - `[col span="4" span__md="6" span__sm="12" align="center|left" bg_color="#..." bg_radius="16" padding="24px"]`: Quản lý hệ thống 12 cột responsive chuẩn Flatsome UX Builder.
   *(Lưu ý: Đối với các layout flex/grid phức tạp đặc thù, có thể sử dụng `[vbc_div]` $\to$ `[vbc_container]` $\to$ `[vbc_box]` $\to$ `[vbc_block]` — không dùng `[col]` bên trong `[col]`)*.

2. **Phần tử Con Nguyên Tử (Atomic Elements)**: Đặt trực tiếp bên trong `[col]` hoặc `[vbc_block]`:
   - `[vbc_h1]-[vbc_h6]`: Tiêu đề kèm `text="..."`, `color`, `font_size`, `font_weight`, `text_align`.
   - `[vbc_p]`: Đoạn văn bản kèm `text="..."`, `color`, `font_size`, `line_height`.
   - `[vbc_img]`: Hình ảnh tự đóng `[vbc_img src="..." alt="..." width="..." border_radius="..."]`.
   - `[vbc_a]`: Nút bấm hoặc liên kết `[vbc_a href="..." text="..." bg_color="..." color="..." padding="..."]`.
   - `[vbc_icon]`: Icon vector từ 5 thư viện `[vbc_icon icon_type="lucide|fontawesome" name="..." size="..." color="..."]`.
   - `[vbc_accordion]` & `[vbc_accordion_item]`: Khối câu hỏi thường gặp FAQ.
   - `[vbc_tabs]` & `[vbc_tab]`: Khối chuyển tab lộ trình học / bảng giá.
   - `[contact-form-7 id="..." title="..."]`: Form thu thập khách hàng thực tế sinh từ Bước 2.
   - `[vbc_post post_type="post|product"]`: Danh sách bài viết / sản phẩm truy vấn động từ WordPress Database.

3. **Ràng buộc quan trọng**:
   - **Truyền nội dung qua input (`text="..."` / `content="..."`)**: Tuyệt đối không lồng thẻ thô hoặc `<img>` vào giữa cặp thẻ `[vbc_p]...[/vbc_p]` hay `[vbc_h1]-[vbc_h6]`, vì WordPress `wpautop` sẽ tự động chèn thẻ `<p>` rác làm vỡ layout.
   - **Zero same-type nesting**: Không lồng cùng loại thẻ vào nhau (không lồng `[row]` trong `[row]` hoặc `[col]` trong `[col]`, dùng `[row_inner]` / `[vbc_box]` nếu cần sub-grid).

4. **BẮT BUỘC — CSS Scoped Theo Section Class (Nhúng `<style>` Trong Content)**:
   - Mỗi section có màu sắc, font, form đặc thù PHẢI dùng CSS scoped theo class của section đó.
   - **Cơ chế**: Nhúng trực tiếp `<style>` block vào **đầu file `created_vbc.txt`** (trước các shortcodes), trong đó dùng class của `[section]` làm selector cha:
     ```html
     <style>
     /* === Section Hero === */
     .section-hero .title { font-size: 40px; }
     /* === Section Register (Form CF7) === */
     .section-register .wpcf7-form input[type="text"],
     .section-register .wpcf7-form input[type="tel"],
     .section-register .wpcf7-form input[type="email"],
     .section-register .wpcf7-form select,
     .section-register .wpcf7-form textarea {
       width: 100%; padding: 13px 16px;
       border: 1.5px solid #e2e8f0; border-radius: 10px;
       font-size: 15px; background: #f8fafc; color: #1e293b; box-sizing: border-box;
     }
     .section-register .wpcf7-form input[type="text"]:focus,
     .section-register .wpcf7-form input[type="tel"]:focus,
     .section-register .wpcf7-form input[type="email"]:focus {
       border-color: #F5568F; box-shadow: 0 0 0 3px rgba(245,86,143,0.12); outline: none; background: #ffffff;
     }
     .section-register .wpcf7-form input[type="submit"] {
       width: 100%; padding: 14px;
       background: #F5568F; color: #fff;
       font-weight: 700; border: none; border-radius: 50px; cursor: pointer;
     }
     .section-register .wpcf7-form input[type="submit"]:hover { background: #e0447c; }
     </style>
     ```
   - **TUYỆT ĐỐI KHÔNG** để CSS form `[contact-form-7]` rời rạc — PHẢI embed trực tiếp bằng thẻ `<style>` ở đầu content kèm selector `.section-xxx` để đảm bảo áp dụng 100%.

### Bước 4: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/create-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/created_vbc.txt" [--post_id <POST_ID>]
```

### Bước 5: Kiểm Định & Bàn Giao (Audit & Handover)
Chạy rechecker và kiểm tra trang web trên trình duyệt:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>"
```
Cung cấp link live và link chỉnh sửa trực tiếp trên Flatsome UX Builder cho người dùng.
