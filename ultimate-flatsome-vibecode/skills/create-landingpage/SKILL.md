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

### Bước 2.1: Nhận Diện & Thiết Kế Row Blog (Tin tức) & Row Product (Sản phẩm) -> BẮT BUỘC Dùng Element `[vbc_post]`
Khi thiết kế khu vực Bài viết Blog, Tin tức, Cẩm nang kiến thức hoặc Danh sách Sản phẩm / Bảng giá / Khóa học:
1. **Dạng Row / Grid Blog (Tin tức, Bài viết)**:
   - **BẮT BUỘC sử dụng element `[vbc_post]`** thay vì dựng thủ công các `[col]` tĩnh:
     ```
     [vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
     ```
2. **Dạng Row / Grid Sản Phẩm (WooCommerce / Khóa học / Dịch vụ)**:
   - **BẮT BUỘC sử dụng `[vbc_post]`** với `post_type="product"`:
     ```
     [vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay"]
     ```
3. **Lợi ích**: Đồng bộ 100% dữ liệu động từ WordPress Database, tự động lấy thumbnail, tiêu đề, tóm tắt, giá bán, permalink và tương thích UX Builder.

### Bước 3: AI Thiết Kế Bố Cục Kết Hợp VBC Section Kế Thừa Chuẩn Flatsome & VBC Elements
AI trực tiếp viết mã nguồn lưu tại `tmp/<slug>/created_vbc.txt`.

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
   - `[vbc_h1]-[vbc_h6]`: Tiêu đề kèm `text="..."`, `color`, `font_size`, `font_weight`, `text_align`.
   - `[vbc_p]`: Đoạn văn bản kèm `text="..."`, `color`, `font_size`, `line_height`.
   - `[vbc_img]`: Hình ảnh tự đóng `[vbc_img src="..." alt="..." width="..." border_radius="..."]`.
   - `[vbc_a]`: Nút bấm hoặc liên kết `[vbc_a href="..." text="..." bg_color="..." color="..." padding="..."]`.
   - `[vbc_icon]`: Icon vector từ 5 thư viện `[vbc_icon icon_type="lucide|fontawesome" name="..." size="..." color="..."]`.
   - `[vbc_accordion]` & `[vbc_accordion_item]`: Khối câu hỏi thường gặp FAQ.
   - `[vbc_tabs]` & `[vbc_tab]`: Khối chuyển tab lộ trình học / bảng giá.
   - `[contact-form-7 id="..." title="..."]`: Form thu thập khách hàng thực tế sinh từ Bước 2.
   - `[vbc_post post_type="post|product"]`: Danh sách bài viết / sản phẩm truy vấn động từ WordPress Database.

3. **Ràng buộc quan trọng (CẤM VI PHẠM)**:
   - **100% DÙNG THUỘC TÍNH `text="..."` (SELF-CLOSING SHORTCODES)**:
     > [!CRITICAL]
     > **TUYỆT ĐỐI KHÔNG GẮN CONTENT VÀO SHORTCODE DẠNG ĐÓNG MỞ NHƯ `[vbc_p]...[/vbc_p]`, `[vbc_h1]...[/vbc_h1]`, `[vbc_span]...[/vbc_span]`**.
     > Flatsome UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` hoặc thẻ rác làm hỏng toàn bộ giao diện.
     > - **ĐÚNG:** `[vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]`
     > - **SAI:** `[vbc_p class="target-text"]Khắc phục điểm yếu, nâng band điểm <b>Listening</b>.[/vbc_p]`
     > - **Định dạng HTML:** Viết trực tiếp `<b>`, `<strong>`, `<span>`, `<br>` vào trong `text="..."`.
   - **Zero same-type nesting**: Không lồng cùng loại thẻ vào nhau (không lồng `[row]` trong `[row]`, dùng `[row_inner]` nếu cần sub-grid).
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

### Bước 4: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/create-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/created_vbc.txt" [--post_id <POST_ID>]
```

### Bước 5: AI Agent Đối Soát Từng Section & Tự Động Hoàn Thiện Code (AI Section-by-Section Audit & Polish)
1. **AI Agent Phân Tích & Đối Soát Trực Quan Từng Section**:
   - Sử dụng AI Agent duyệt qua từng section đã tạo để đối soát tính thẩm mỹ, độ tương phản, khoảng cách, font chữ và tỷ lệ bố cục.
   - Phát hiện các điểm cần tối ưu: padding quá lớn/quá nhỏ, màu chữ bị chìm, thiếu bóng đổ, form chưa đẹp.

2. **Chạy Script Rechecker**:
   ```bash
   python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>"
   ```

3. **Yêu Cầu AI Agent Tự Động Sinh Lại Code Mới Nếu Cần Tinh Chỉnh**:
   - Nếu phát hiện bất kỳ khuyết điểm thị giác nào, AI Agent **phải tự động cập nhật lại code VBC cho section đó** và tái xuất bản lên WordPress.
   - Nghiệm thu khi đạt chuẩn:
     - **0 Shortcodes chưa parse**.
     - **Tất cả hình ảnh hiển thị sắc nét**.
     - **Contact Form 7 hoạt động trơn tru**.
     - **Giao diện hiện đại, chuyên nghiệp, responsive 100%**.
4. Cung cấp link live và link chỉnh sửa trực tiếp trên Flatsome UX Builder cho người dùng.
