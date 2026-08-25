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

### Bước 4: AI Sinh Mã Nguồn Kết Hợp VBC Section Kế Thừa Chuẩn Flatsome & VBC Elements
AI viết trực tiếp file mã nguồn lưu tại `tmp/<slug>/compiled_vbc.txt`.

#### 🏛️ Kiến Trúc Bố Cục Ưu Tiên (Layout Backbone):
1. **Khung xương Bố cục (Structure)**: **100% sử dụng `[vbc_section]` (kế thừa Section Flatsome) + `[row]` + `[col]`**:
   - `[vbc_section id="section-xxx" bg_color="#..." padding="60px" dark="true|false" custom_css="..."]`:
     - **Kế thừa toàn bộ thuộc tính của Section Flatsome**: `bg`, `bg_color`, `bg_overlay`, `padding`, `padding__sm`, `padding__md`, `margin`, `height`, `dark`, `divider`, `divider_top`, `border`, `effect`, `parallax`...
     - **BẮT BUỘC gán `id="section-<tên>"`** cho mỗi `[vbc_section]` để làm CSS scope identifier.
     - **Toàn bộ CSS của section và các phần tử con bên trong ĐƯA TRỰC TIẾP vào `custom_css="..."`** sử dụng từ khóa `selector`.
   - `[row width="custom" custom_width="1140px" v_align="middle|top"]`: **Toàn bộ `[row]` PHẢI nằm bên trong `[vbc_section]`** — tuyệt đối không đặt `[row]` độc lập ngoài section.
   - `[col span="4" span__md="6" span__sm="12" align="center|left" bg_color="#..." bg_radius="16" padding="24px"]`: Quản lý hệ thống 12 cột responsive chuẩn Flatsome UX Builder.
   *(Lưu ý: Đối với các layout flex/grid phức tạp đặc thù, có thể sử dụng `[vbc_div]` $\to$ `[vbc_container]` $\to$ `[vbc_box]` $\to$ `[vbc_block]` — không dùng `[col]` bên trong `[col]`)*.

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

3. **Ràng buộc quan trọng (CẤM VI PHẠM)**:
   - Thay thế 100% link ảnh bằng URL WordPress từ `media_map.json`.
   - **100% DÙNG THUỘC TÍNH `text="..."` (SELF-CLOSING SHORTCODES)**:
     > [!CRITICAL]
     > **TUYỆT ĐỐI KHÔNG GẮN CONTENT VÀO SHORTCODE DẠNG ĐÓNG MỞ NHƯ `[vbc_p]...[/vbc_p]`, `[vbc_h1]...[/vbc_h1]`, `[vbc_span]...[/vbc_span]`**.
     > Flatsome UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` hoặc thẻ rác làm hỏng toàn bộ giao diện.
     > - **ĐÚNG:** `[vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]`
     > - **SAI:** `[vbc_p class="target-text"]Khắc phục điểm yếu, nâng band điểm <b>Listening</b>.[/vbc_p]`
     > - **Định dạng HTML:** Viết trực tiếp `<b>`, `<strong>`, `<span>`, `<br>` vào trong `text="..."`.
   - **Zero same-type nesting**: Tuyệt đối không lồng cùng loại thẻ vào nhau (ví dụ: không lồng `[row]` trong `[row]` hoặc `[col]` trong `[col]`, dùng `[row_inner]` / `[vbc_box]` nếu cần sub-grid).
   - **Tuyệt đối không dùng dấu ngoặc vuông `[` hoặc `]` trong các giá trị thuộc tính**: Kể cả trong `custom_css` (dùng class selector như `.wpcf7-tel`, `.wpcf7-submit`, không dùng `[type='tel']`).

4. **BẮT BUỘC — Đưa CSS của Các Phần Tử Con Vào Custom CSS (Selector) Của VBC Section**:
   - **KHÔNG đưa CSS vào Custom Field** mà đưa trực tiếp vào thuộc tính `custom_css="..."` của `[vbc_section]`.
   - **Cú pháp sử dụng từ khóa `selector`**: Từ khóa `selector` tự động đại diện cho chính Section cha (`#section-id`), từ đó dễ dàng target và style cho mọi phần tử con bên trong (KHÔNG DÙNG DẤU `[` HOẶC `]` TRONG SELECTOR):
     ```
     [vbc_section id="section-register" bg_color="#F5568F" padding="80px" padding__sm="50px" dark="true" custom_css="
       selector { background: linear-gradient(135deg, #F5568F 0%, #e0447c 100%); }
       selector .wpcf7-form input.wpcf7-text,
       selector .wpcf7-form input.wpcf7-tel,
       selector .wpcf7-form input.wpcf7-email,
       selector .wpcf7-form textarea {
         width: 100%; padding: 13px 16px;
         border: 1.5px solid #e2e8f0; border-radius: 10px;
         font-size: 15px; background: #f8fafc; color: #1e293b; box-sizing: border-box;
       }
       selector .wpcf7-form input:focus {
         border-color: #F5568F; box-shadow: 0 0 0 3px rgba(245,86,143,0.12); outline: none; background: #ffffff;
       }
       selector .wpcf7-form input.wpcf7-submit {
         width: 100%; padding: 15px 24px;
         background: #F5568F; color: #ffffff;
         font-weight: 700; font-size: 16px;
         border: none; border-radius: 50px; cursor: pointer;
       }
       selector .wpcf7-form input.wpcf7-submit:hover { background: #e0447c; }
     "]
       [row width="custom" custom_width="840px"]
         [col span="12" bg_color="#ffffff" bg_radius="24" padding="48px"]
           [vbc_h2 text="Đăng ký tư vấn ngay" color="#1e293b" font_size="26px" font_weight="800" text_align="center" margin="0 0 28px 0"]
           [contact-form-7 id="..." title="..."]
         [/col]
       [/row]
     [/vbc_section]
     ```
   - **Ưu điểm**: CSS được đóng gói trọn vẹn trong từng section, tương thích 100% trong UX Builder, độc lập và không phụ thuộc vào file/field bên ngoài.

### Bước 5: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/clone-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/compiled_vbc.txt" [--post_id <POST_ID>]
```

### Bước 6: AI Agent Đối Soát Từng Section & Tự Động Sinh Lại Code (AI Section-by-Section Recheck & Auto-Fix)

1. **AI Agent Phân Tích & Đối Soát Trực Quan Từng Section (Section Gap Analysis)**:
   - Sử dụng AI Agent kết hợp `browser_subagent` và script `rechecker.py` để duyệt qua từng section của trang nguồn và trang clone:
     - So sánh bố cục lưới (số cột, tỷ lệ khoảng cách, padding).
     - So sánh typography (màu chữ, font-size, độ tương phản không bị chìm nền).
     - So sánh hình ảnh & icons (tỷ lệ ảnh, bo góc `border_radius`, bóng đổ `box_shadow`, icon checklist).
     - So sánh các tương tác (Accordion FAQ, nút CTA, form fields).

2. **Chạy Script Rechecker**:
   ```bash
   python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>" --source_url "<SOURCE_URL>"
   ```

3. **Yêu Cầu AI Agent Tự Động Sinh Lại Code Mới (Auto-Remediation)**:
   - **BẮT BUỘC**: Nếu phát hiện bất kỳ Section nào có sai khác (vỡ layout, chữ bị chìm màu, hình ảnh bị méo mó, hoặc VSI $< 90\%$):
     - AI Agent **phải chỉ rõ từng điểm sai khác theo từng section**.
     - AI Agent **phải tự động cập nhật lại script generator và sinh lại code VBC mới** cho section đó.
     - Tái xuất bản và kiểm định lại cho đến khi đạt chuẩn hoàn hảo:
       - **Độ tương đồng thị giác (VSI) $\ge 90.0\%$**.
       - **0 Shortcodes chưa parse**.
       - **Hình ảnh hiển thị đầy đủ, không broken link**.
       - **Biểu mẫu Contact Form 7 hiển thị đẹp mắt và hoạt động chuẩn**.
       - **Tương thích 100% với trình kéo thả Flatsome UX Builder**.
