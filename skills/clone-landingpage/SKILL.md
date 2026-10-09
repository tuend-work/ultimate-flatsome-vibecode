---
name: clone-landingpage
description: >-
  Tự động sao chép (clone) toàn bộ giao diện và nội dung từ một trang web bất kỳ sang WordPress Flatsome bằng 100% phần tử Ultimate Flatsome VibeCode Elements do AI trực tiếp sinh ra dựa trên ảnh chụp màn hình toàn trang qua trình duyệt thật Antigravity. Không dùng code web gốc, đảm bảo code sạch tuyệt đối.
---

# Clone Landing Page (Antigravity Real Browser & AI Visual Generative Architecture)

## Mục tiêu (Goal)
Sử dụng **Trình duyệt thật của Antigravity (`browser_subagent`)** để truy cập, cuộn trang kích hoạt hiệu ứng/lazy-load và chụp ảnh màn hình toàn trang (**Full-Page Screenshot**). 
Sau đó, **AI đóng vai trò Senior UI/UX & Frontend Engineer trực tiếp quan sát ảnh chụp màn hình toàn trang để TẠO SINH 100% MÃ NGUỒN MỚI TINH (Generative AI from Visual)** bằng hệ thống **Native VBC Elements** (`[vbc_section]`, `[row]`, `[col]`, `[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_post]`, `[contact-form-7]`).

> [!CRITICAL]
> **QUY TẮC CỐT LÕI — CODE SẠCH TUYỆT ĐỐI, KHÔNG DÙNG LẠI CODE CỦA WEB GỐC**:
> - **KHÔNG bóc tách hay copy-paste mã HTML/CSS rối rắm, rác của trang nguồn**. Việc bóc tách HTML thô từ web gốc luôn để lại class rác, mã CSS inline thừa, cấu trúc thẻ div lồng nhau quá sâu làm hỏng Flatsome UX Builder.
> - **AI quan sát trực tiếp ảnh chụp màn hình toàn trang (Full-Page Screenshot)** như một bản vẽ thiết kế (Figma/UI Design), hiểu bố cục, màu sắc, khoảng cách, font chữ, sau đó **tự tay viết lại 100% mã nguồn VBC Elements sạch, tối ưu, chuẩn semantic và tương thích hoàn hảo với UX Builder**.

---

## Quy trình Thực hiện (Workflow)

### Bước 1: Điều khiển Trình Duyệt Thật Antigravity (`browser_subagent`) & Đồng Bộ Media
1. **Khởi chạy Trình duyệt thật qua Antigravity `browser_subagent`**:
   - Sử dụng tool `browser_subagent` để mở trình duyệt thật (Real Browser) điều hướng đến `<URL_NGUON>`.
   - Mô phỏng hành vi người dùng thật: cuộn trang từ từ từ trên xuống dưới để kích hoạt toàn bộ cơ chế **Lazy Load ảnh**, **Web Fonts**, **CSS Animations** và các thành phần JavaScript động.
   - Chụp ảnh màn hình toàn trang (**Full-Page Screenshot**) lưu vào `tmp/<slug>/source_screenshot.png` hoặc thư mục artifacts.
   - Trình duyệt Antigravity tự động ghi hình phiên duyệt web dưới dạng video WebP phục vụ kiểm chứng trực quan.

2. **Đồng bộ Media lên WordPress Media Library**:
   - Chạy script đồng bộ ảnh:
     ```bash
     python .agents/skills/clone-landingpage/scripts/sync_media.py --url "<URL_NGUON>"
     ```
   - **Quy tắc Kiểm tra Trùng lặp trước khi upload**: Script tự động kiểm tra ảnh đã tồn tại trên WordPress Media Library hay chưa (`/vbc/v1/check-media`). Nếu đã có $\to$ tái sử dụng URL WordPress, không tạo bản sao thừa. Kết quả ánh xạ lưu tại `tmp/<slug>/media_map.json`.

### Bước 2: AI Quan Sát Ảnh Chụp Toàn Trang & Kiến Tạo Bố Cục Thị Giác (AI Visual Perception)
AI Agent quan sát trực tiếp ảnh chụp toàn trang `source_screenshot.png` (Visual-Driven) để phân tích layout và phân chia các Section chức năng:
1. **Header / Topbar**: Logo, hotline, navigation links, CTA button.
2. **Hero Section**: Tiêu đề chính H1, slogan, badge ưu đãi, bullet points lợi ích, form đăng ký hoặc nút hành động, ảnh đại diện nổi bật.
3. **Highlights / Features Grid**: Lưới 3–4 cột với các điểm mạnh dịch vụ, icon vector trực quan.
4. **Programs / Services / Courses**: Thẻ khóa học, bảng giá, chương trình chi tiết theo đối tượng.
5. **Tabs & Accordions**: Lộ trình đào tạo (`[vbc_tabs]`), câu hỏi thường gặp FAQ (`[vbc_accordion]` & `[vbc_accordion_item]`).
6. **Blog / Tin Tức & Sản Phẩm (Dynamic Query)**: Danh sách bài viết blog, tin tức hoặc sản phẩm WooCommerce $\to$ Sử dụng `[vbc_post]` (`post_type="post"` hoặc `post_type="product"`).
7. **Social Proof & Testimonials**: Đánh giá khách hàng/học viên, rating, feedback thực tế.
8. **Lead Form & CTA Form (Contact Form 7)**: Biểu mẫu form đăng ký nhận tư vấn, tải tài liệu.
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

### Bước 3.1: Nhận Diện Danh Sách Bài Viết (Blog) & Sản Phẩm (Product) -> BẮT BUỘC Dùng VBC Post Element `[vbc_post]`
Khi phát hiện khu vực danh sách Tin tức, Bài viết Blog, Kiến thức, hoặc Danh sách Sản phẩm/Khóa học:
1. **Nhận diện dạng Row / Grid Blog (Tin tức / Kiến thức)**:
   - Thay vì dựng thủ công các `[col]` tĩnh lặp lại, **BẮT BUỘC sử dụng element `[vbc_post]`** với `post_type="post"` để hiển thị danh sách bài viết chuẩn WordPress động.
   - Cú pháp chuẩn:
     ```
     [vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem chi tiết" card_radius="18px"]
     ```
2. **Nhận diện dạng Row / Grid Sản Phẩm (WooCommerce / Khóa học / Dịch vụ)**:
   - **BẮT BUỘC sử dụng `[vbc_post]`** với `post_type="product"` (hoặc Custom Post Type tương ứng):
     ```
     [vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay"]
     ```
3. **Ưu điểm**: Tự động lấy ảnh đại diện, tiêu đề, tóm tắt, giá bán, ngày đăng, liên kết permalink tự động và tương thích 100% với Flatsome UX Builder.
5. **BẮT BUỘC — Quản Lý Custom CSS Chuẩn Hóa (Chỉ Dùng Field Công Khai `vbc_page_css`)**:
   - Sau khi tạo form CF7, **phân tích màu sắc, font chữ, border-radius, padding và màu nền của form** từ ảnh chụp màn hình toàn trang.
   - Khuyến khích đưa CSS tùy chỉnh trực tiếp vào thuộc tính `custom_css="..."` của `[vbc_section]` chứa form/phần tử để style các thành phần khớp với thiết kế.
   - Nếu có CSS dùng chung cấp độ trang (Page Custom CSS): **BẮT BUỘC lưu vào Custom Field công khai `vbc_page_css`**.
   - **TUYỆT ĐỐI KHÔNG dùng field ẩn `_custom_css`**.
   - **KHÔNG TỰ TIỆN chèn CSS ẩn header/footer** (`#header, #footer { display: none !important; }`) trừ khi người dùng có yêu cầu rõ ràng.
   - **BẮT BUỘC ĐƯA TRỰC TIẾP VÀO `custom_css="..."` CỦA `[vbc_section]` CHỨA FORM ĐÓ**, sử dụng cú pháp `selector`:
     - Định kiểu container section: `selector { ... }`
     - Định kiểu các input con: `selector .wpcf7-form input.wpcf7-text`, `selector .wpcf7-form input.wpcf7-tel`, `selector .wpcf7-form select`, `selector .wpcf7-form textarea` $\to$ border, padding, border-radius, background, font-size.
     - Định kiểu submit: `selector .wpcf7-form input.wpcf7-submit` $\to$ background-color, color, font-weight, padding, border-radius, hover state.
   - Ví dụ CSS chuẩn được đóng gói ngay trong `[vbc_section]`:
     ```css
     selector .wpcf7-form input.wpcf7-text,
     selector .wpcf7-form input.wpcf7-tel,
     selector .wpcf7-form input.wpcf7-email,
     selector .wpcf7-form select,
     selector .wpcf7-form textarea {
       width: 100%;
       padding: 12px 16px;
       border: 1.5px solid #e2e8f0;
       border-radius: 8px;
       font-size: 15px;
       background: #f8fafc;
       margin-bottom: 12px;
       box-sizing: border-box;
       transition: border-color 0.2s;
     }
     selector .wpcf7-form input:focus {
       border-color: #F5568F;
       outline: none;
     }
     selector .wpcf7-form input.wpcf7-submit {
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
     selector .wpcf7-form input.wpcf7-submit:hover {
       background: #e0447c;
     }
     ```
   - **Màu sắc, border-radius và font-size PHẢI được tùy chỉnh khớp với thiết kế** — không dùng màu mặc định nếu bản vẽ có màu riêng.

### Bước 4: AI Trực Tiếp Tạo Sinh 100% Mã Nguồn Chuẩn VBC Elements từ Ảnh Chụp Màn Hình (Visual-Driven AI Code Synthesis)
AI viết trực tiếp file mã nguồn lưu tại `tmp/<slug>/compiled_vbc.txt`.

> [!IMPORTANT]
> **TIÊU CHUẨN CODE SẠCH — NGUYÊN BẢN VBC ELEMENTS (100% CLEAN GENERATIVE CODE)**:
> 1. **Tuyệt đối KHÔNG copy cấu trúc HTML gốc**: Không giữ lại các thẻ `div` lồng nhau vô nghĩa, không dùng lại class CSS của web gốc (ví dụ: `elementor-...`, `wp-block-...`, `tailwind-classes`).
> 2. **AI tái thiết kế bằng Visual Mindset**: Nhìn ảnh chụp $\to$ ánh xạ thẳng sang **VBC Layout sạch nhất có thể**: `[vbc_section]` bọc ngoài $\to$ `[row]` căn giữa $\to$ `[col]` chia cột $\to$ các leaf tags tự đóng (`[vbc_h2]`, `[vbc_p]`, `[vbc_img]`, `[vbc_a]`, `[vbc_icon]`).
> 3. **Toàn bộ styling đưa vào thuộc tính shortcode hoặc `custom_css="selector { ... }"` của `[vbc_section]`**.

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
     > - **ĐÚNG:** `[vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]`
     > - **SAI:** `[vbc_p class="target-text"]Khắc phục điểm yếu, nâng band điểm <b>Listening</b>.[/vbc_p]`
   - **QUOTE NESTING RULE (CẤM NHÁY KÉP TRONG THUỘC TÍNH)**:
     > [!WARNING]
     > Khi giá trị thuộc tính nằm trong dấu nháy kép `text="..."`, **100% THUỘC TÍNH HTML BÊN TRONG BẮT BUỘC DÙNG NHÁY ĐƠN `'`**.
     > - ❌ **SAI:** `[vbc_p text="<span class="adv-num">01</span><span class="adv-title">Cam kết</span>"]`
     > - ✅ **ĐÚNG:** `[vbc_p text="<span class='adv-num'>01</span> <span class='adv-title'>Cam kết</span>"]`
   - **CẤM THẺ KHỐI & DANH SÁCH TRONG `[vbc_p]`**:
     > Thẻ `<p>` KHÔNG ĐƯỢC CHỨA `<ul>`, `<ol>`, `<li>`, `<div>`, `<h3>`. Danh sách phải đặt trong `[vbc_div]<ul>...</ul>[/vbc_div]` hoặc tách thành các flex items.
     > - ❌ **SAI:** `[vbc_p text="<ul class="check-list"><li>Item 1</li></ul>"]`
   - **BÓC TÁCH PHẦN TỬ PHỨC HỢP**:
     > Khi gặp khối gồm nhiều thành phần (ví dụ: Số thứ tự `01` + Tiêu đề), bóc tách riêng thành `[vbc_span]` + `[vbc_h4]` bên trong `[vbc_div]`.
   - **Zero same-type nesting**: Tuyệt đối không lồng cùng loại thẻ vào nhau (ví dụ: không lồng `[row]` trong `[row]` hoặc `[col]` trong `[col]`, dùng `[row_inner]` / `[vbc_box]` nếu cần sub-grid).
   - **Tuyệt đối không dùng dấu ngoặc vuông `[` hoặc `]` trong các giá trị thuộc tính**: Kể cả trong `custom_css` (dùng class selector như `.wpcf7-tel`, `.wpcf7-submit`, không dùng `[type='tel']`).

4. **BẮT BUỘC — Đưa CSS của Các Phần Tử Con Vào Custom CSS Của VBC Section Hoặc Public Meta `vbc_page_css`**:
   - Khuyến khích đưa trực tiếp vào thuộc tính `custom_css="..."` của `[vbc_section]`, hoặc nếu cần CSS toàn trang thì lưu vào **Custom Field công khai `vbc_page_css`** (Tuyệt đối không dùng field ẩn `_custom_css`).
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
