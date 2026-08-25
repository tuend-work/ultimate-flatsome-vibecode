---
name: clone-landingpage
description: >-
  Tự động sao chép (clone) toàn bộ giao diện và nội dung từ một trang web bất kỳ sang WordPress Flatsome bằng 100% phần tử Ultimate Flatsome VibeCode Elements do AI trực tiếp sinh ra. Sử dụng khi người dùng yêu cầu clone/sao chép landing page, bóc tách layout từ URL gốc, hoặc chuyển đổi giao diện sang VBC.
---

# Clone Landing Page (AI-First LLM Architecture)

## Mục tiêu (Goal)
Sử dụng **Trí tuệ Nhân tạo (LLM)** để phân tích ngữ cảnh, bố cục thị giác và cấu trúc nội dung từ trang web nguồn, sau đó AI trực tiếp thiết kế và sinh 100% mã nguồn **Native VBC Elements** (`[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_card]`, `[vbc_tabs]`, `[vbc_accordion]`, `[contact-form-7]`, `[vbc_post]`) với tất cả thuộc tính giao diện đưa trực tiếp vào thuộc tính của shortcode, đạt độ tương đồng thị giác (VSI) $\ge 90\%$ và **0 unparsed tags**.

---

## Quy trình Thực hiện (Workflow)

### Bước 1: Quét & Đồng bộ Media lên WordPress Media Library
Chạy script đồng bộ ảnh:
```bash
python .agents/skills/clone-landingpage/scripts/sync_media.py --url "<URL_NGUON>"
```
- Script sẽ tải mã nguồn HTML về `tmp/<slug>/source.html`, phát hiện toàn bộ ảnh và upload lên WordPress Media Library qua REST API `/vbc/v1/upload`.
- Kết quả ánh xạ ảnh gốc $\to$ WordPress URL được lưu trong `tmp/<slug>/media_map.json`.
- **BẮT BUỘC — Kiểm Tra Trùng Lặp Trước Khi Upload**:
  - Trước khi upload, script **PHẢI kiểm tra** xem ảnh cùng tên (`filename`) đã tồn tại trên WordPress Media Library hay chưa, bằng cách:
    1. Gọi endpoint `GET /wp-json/wp/v2/media?search=<filename>&per_page=1` với header `Authorization: Bearer <token>`.
    2. Nếu nhận được `id` và `source_url` → **dùng luôn URL đó**, bỏ qua bước upload.
    3. Nếu chưa tồn tại → mới thực hiện upload qua `/vbc/v1/upload`.
  - Điều này giúp **tránh tạo bản sao trùng lặp** trong thư viện media WordPress và giảm thời gian xử lý.
  - Mọi kết quả cuối cùng (dù upload mới hay lấy từ WP) đều được ghi vào `media_map.json`.

### Bước 2: AI Đọc Hiểu & Bóc Tách Ngữ Cảnh Bố Cục (DOM & Visual Context)
AI đọc `tmp/<slug>/source.html` và phân tích các Section chính:
1. **Header / Topbar**: Logo, hotline, navigation links, CTA button.
2. **Hero Section**: Tiêu đề chính H1, slogan, badge ưu đãi, bullet points lợi ích, form đăng ký, hình ảnh đại diện.
3. **Highlights / Features Grid**: Lưới 3–4 cột với các điểm mạnh dịch vụ, icon vector trực quan.
4. **Programs / Services / Courses**: Thẻ khóa học, bảng giá, chương trình chi tiết theo đối tượng.
5. **Tabs & Accordions**: Lộ trình đào tạo (`[vbc_tabs]`), câu hỏi thường gặp / lợi thế đánh số (`[vbc_accordion]` & `[vbc_accordion_item]`).
6. **Blog / Tin Tức & Sản Phẩm (Dynamic Query)**: Danh sách bài viết blog, tin tức hoặc sản phẩm WooCommerce $\to$ Sử dụng `[vbc_post]` (`post_type="post"` hoặc `post_type="product"`) để truy vấn động từ WordPress Database.
7. **Social Proof & Testimonials**: Cảm nhận khách hàng/học viên, rating, feedback thực tế.
8. **Lead Form & CTA Form (Contact Form 7)**: Biểu mẫu form đăng ký nhận tư vấn, tải tài liệu hoặc đăng ký học.
9. **Footer**: Thông tin liên hệ, bản quyền, liên kết điều khoản.

### Bước 3: Tự động Tạo Biểu Mẫu Contact Form 7 (BẮT BUỘC)
Khi phát hiện form nhập liệu trên trang nguồn (hoặc khu vực CTA đăng ký nhận ưu đãi/tư vấn):
1. **Phân tích các trường dữ liệu**: Bóc tách tên trường, placeholder, kiểu input (Họ tên, SĐT, Email, Khóa học, Nội dung...).
2. **Chạy script sinh Form CF7 qua REST API**:
   ```bash
   python .agents/skills/clone-landingpage/scripts/create_cf7.py --title "Form Đăng Ký - <Tên Landing Page>" --fields "name,phone,email,course,message" --button "Đăng ký tư vấn miễn phí"
   ```
3. **Lấy mã Shortcode trả về** dạng `[contact-form-7 id="<ID>" title="..."]` để nhúng trực tiếp vào container VBC ở Bước 4.
4. **Quy tắc**: 100% biểu mẫu thu thập thông tin (Lead Form) **BẮT BUỘC** phải được tạo thành form Contact Form 7 thực tế qua API, **TUYỆT ĐỐI KHÔNG** dùng văn bản giả lập tĩnh (`[vbc_p]`) thay cho form.
5. **BẮT BUỘC — CSS Đồng Bộ Form CF7 Khớp Với Web Gốc**:
   - Sau khi tạo form CF7, **phân tích màu sắc, font chữ, border-radius, padding và màu nền của form gốc** từ `source.html`.
   - Viết một khối CSS tùy chỉnh (sử dụng tính năng Page Custom CSS hoặc `vbc_page_custom_css`) để style các thành phần:
     - `input[type="text"], input[type="tel"], input[type="email"], select, textarea` → border, padding, border-radius, background, font-size.
     - `input[type="submit"], button[type="submit"]` → background-color, color, font-weight, padding, border-radius, hover state.
   - Ví dụ CSS chuẩn tham khảo:
     ```css
     .wpcf7-form input[type="text"],
     .wpcf7-form input[type="tel"],
     .wpcf7-form input[type="email"],
     .wpcf7-form select,
     .wpcf7-form textarea {
       width: 100%;
       padding: 12px 16px;
       border: 1.5px solid #e2e8f0;
       border-radius: 8px;
       font-size: 15px;
       background: #f8fafc;
       margin-bottom: 12px;
       transition: border-color 0.2s;
     }
     .wpcf7-form input[type="text"]:focus,
     .wpcf7-form input[type="tel"]:focus,
     .wpcf7-form input[type="email"]:focus {
       border-color: #F5568F;
       outline: none;
     }
     .wpcf7-form input[type="submit"] {
       width: 100%;
       padding: 14px;
       background: #F5568F;
       color: #fff;
       font-weight: 700;
       font-size: 16px;
       border: none;
       border-radius: 50px;
       cursor: pointer;
       transition: background 0.2s;
     }
     .wpcf7-form input[type="submit"]:hover {
       background: #e0447c;
     }
     ```
   - **Màu sắc, border-radius và font-size PHẢI được tùy chỉnh khớp với thiết kế gốc** — không dùng màu mặc định nếu web gốc có màu riêng.

### Bước 4: AI Sinh Mã Nguồn Kết Hợp Chuẩn Flatsome (Section + Row + Col) & VBC Elements
AI viết trực tiếp file mã nguồn lưu tại `tmp/<slug>/compiled_vbc.txt`.

#### 🏛️ Kiến Trúc Bố Cục Ưu Tiên (Layout Backbone):
1. **Khung xương Bố cục (Structure)**: **Ưu tiên sử dụng `[section]` + `[row]` + `[col]` chuẩn của Flatsome**:
   - `[section bg_color="#..." bg="<img_url>" padding="60px" dark="true|false"]`: Quản lý toàn bộ Section full-width, màu nền, padding và divider.
   - `[row width="custom" custom_width="1140px" v_align="middle|top" col_bg="#..." col_bg_radius="16" padding="20px"]`: Quản lý lưới căn giữa, độ rộng container, flexbox vertical align và màu nền các cột.
   - `[col span="4" span__md="6" span__sm="12" align="center|left" bg_color="#..." bg_radius="16" padding="24px"]`: Quản lý hệ thống 12 cột responsive chuẩn Flatsome UX Builder.
   *(Lưu ý: Đối với các layout flex/grid phức tạp đặc thù, có thể sử dụng `[vbc_div]` $\to$ `[vbc_container]` $\to$ `[vbc_box]` $\to$ `[vbc_block]`)*.

2. **Phần tử Con Nguyên Tử (Atomic Elements)**: Đặt trực tiếp bên trong `[col]` hoặc `[vbc_block]`:
   - `[vbc_h1]-[vbc_h6]`: Tiêu đề kèm thuộc tính `text="..."`, `color`, `font_size`, `font_weight`, `text_align`.
   - `[vbc_p]`: Đoạn văn bản kèm `text="..."`, `color`, `font_size`, `line_height`.
   - `[vbc_img]`: Hình ảnh tự đóng `[vbc_img src="..." alt="..." width="..." border_radius="..."]`.
   - `[vbc_a]`: Nút bấm hoặc liên kết `[vbc_a href="..." text="..." bg_color="..." color="..." padding="..."]`.
   - `[vbc_icon]`: Icon vector từ 5 thư viện `[vbc_icon icon_type="lucide|fontawesome" name="..." size="..." color="..."]`.
   - `[vbc_accordion]` & `[vbc_accordion_item]`: Khối câu hỏi thường gặp FAQ.
   - `[vbc_tabs]` & `[vbc_tab]`: Khối chuyển tab lộ trình học / bảng giá.
   - `[contact-form-7 id="..." title="..."]`: Form thu thập khách hàng thực tế sinh từ Bước 3.
   - `[vbc_post post_type="post|product"]`: Danh sách bài viết / sản phẩm truy vấn động từ WordPress Database.

3. **Ràng buộc quan trọng**:
   - Thay thế 100% link ảnh bằng URL WordPress từ `media_map.json`.
   - **Truyền nội dung qua input (`text="..."` / `content="..."`)**: Tuyệt đối không lồng thẻ thô hoặc `<img>` vào giữa cặp thẻ `[vbc_p]...[/vbc_p]` hay `[vbc_h1]-[vbc_h6]`, vì WordPress `wpautop` sẽ tự động chèn thẻ `<p>` rác làm vỡ layout.
   - **Zero same-type nesting**: Tuyệt đối không lồng cùng loại thẻ vào nhau (ví dụ: không lồng `[row]` trong `[row]`, dùng `[row_inner]` nếu cần sub-grid).

### Bước 5: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/clone-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/compiled_vbc.txt" [--post_id <POST_ID>]
```

### Bước 6: Đối Soát Chất Lượng & Thị Giác (Quality Audit)
Chạy script rechecker:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>" --source_url "<SOURCE_URL>"
```
Đảm bảo kết quả đạt:
- **0 Unparsed Shortcodes**.
- **VSI $\ge 90\%$**.
- **Biểu mẫu Contact Form 7 hoạt động**.
- Tương thích 100% với trình kéo thả Flatsome UX Builder.
