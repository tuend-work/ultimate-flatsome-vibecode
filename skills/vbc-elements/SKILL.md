---
name: vbc-elements
description: >-
  Cẩm nang tra cứu và sử dụng toàn bộ hệ thống Shortcodes của Ultimate Flatsome VibeCode Elements. Sử dụng khi cần viết shortcode VBC ([vbc_div], [vbc_box], [vbc_block], [vbc_container], [vbc_h1]-[vbc_h6], [vbc_p], [vbc_a], [vbc_icon]), tra cứu cú pháp CSS selector hoặc tích hợp component.
---

# Ultimate Flatsome VibeCode Elements Catalog & Spec

## Mục tiêu (Goal)
Cung cấp tài liệu tra cứu chuẩn xác nhất về cú pháp, thuộc tính và phương pháp định kiểu CSS cho toàn bộ 15+ phần tử VBC Elements trong Flatsome UX Builder.

## Danh Mục Phần Tử (Element Catalog)

1. **Khung chứa & Bố cục (Layout Containers)**:
   - `[vbc_section]`: Section chính kế thừa toàn bộ tính năng của Section Flatsome (`bg`, `bg_color`, `bg_overlay`, `padding`, `margin`, `dark`, `divider`, `border`...), hỗ trợ `custom_css="selector { ... } selector .child { ... }"` để định kiểu cho Section và toàn bộ phần tử con bên trong.
   - `[vbc_div]`: Thẻ `<div>` full-width bao ngoài toàn bộ Section hoặc khối giao diện tùy biến.
   - `[vbc_box]`: Thẻ `<div>` card/box container (max-width, flex/grid, border-radius).
   - `[vbc_block]`: Khung hàng/grid cấp 1.
   - `[vbc_container]`: Cột/khung chứa item cấp 1.

2. **Cơ Chế Lồng Nhau Đa Cấp (Suffix Nesting Hierarchy - Tương tự Row/Col Flatsome)**:
   WordPress shortcode parser mặc định sẽ bị lỗi đóng nhầm thẻ nếu lồng cùng 1 tên shortcode lặp lại (ví dụ `[vbc_div]...[vbc_div]...[/vbc_div]...[/vbc_div]`). Để giải quyết triệt để và cho phép lồng layout vô hạn độ sâu, hệ thống VBC Elements cung cấp cơ chế hậu tố `_inner` giống hệt Flatsome `[row] -> [row_inner] -> [row_inner_1]` và `[col] -> [col_inner] -> [col_inner_1]`:
   
   - **Cấp 0 (Gốc):** `[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[row]`, `[col]`
   - **Cấp 1 (Lồng cấp 1):** `[vbc_div_inner]`, `[vbc_box_inner]`, `[vbc_block_inner]`, `[vbc_container_inner]`, `[row_inner]`, `[col_inner]`
   - **Cấp 2 (Lồng cấp 2):** `[vbc_div_inner_1]`, `[vbc_box_inner_1]`, `[vbc_block_inner_1]`, `[vbc_container_inner_1]`, `[row_inner_1]`, `[col_inner_1]`
   - **Cấp 3 đến 10 (Lồng cấp sâu hơn):** `[vbc_div_inner_2]` $\to$ `[vbc_div_inner_3]` ... `[vbc_div_inner_10]`
   
   **Ví dụ lồng VBC Div / Row chuẩn UX Builder:**
   ```html
   [row]
     [col span="4" span__sm="12"]
       [vbc_img img_attachment="1787"]
     [/col]
     [col span="8" span__sm="12"]
       [row_inner]
         [col_inner span__sm="12"]
           [row_inner_1]
             [col_inner_1 span="4" span__sm="12"]
               [vbc_p text="Cột 1"]
             [/col_inner_1]
             [col_inner_1 span="4" span__sm="12"]
               [vbc_p text="Cột 2"]
             [/col_inner_1]
             [col_inner_1 span="4" span__sm="12"]
               [vbc_p text="Cột 3"]
             [/col_inner_1]
           [/row_inner_1]
         [/col_inner]
       [/row_inner]
     [/col]
   [/row]
   ```
   **Ví dụ lồng thuần VBC Div Elements:**
   ```html
   [vbc_div class="wrapper-outer"]
     [vbc_div_inner class="content-row"]
       [vbc_div_inner_1 class="card-item"]
         [vbc_h3 text="Tiêu đề card"]
         [vbc_p text="Nội dung mô tả..."]
       [/vbc_div_inner_1]
     [/vbc_div_inner]
   [/vbc_div]
   ```

3. **Tiêu đề & Nội dung (Typography)**:
   - `[vbc_h1]` đến `[vbc_h6]`: Thẻ heading chuẩn SEO, hỗ trợ `custom_css`.
   - `[vbc_p]`: Thẻ đoạn văn bản `<p>`.
   - `[vbc_span]`: Thẻ văn bản nội dòng `<span>`.

4. **Liên kết & Biểu tượng (Interactive & Icons)**:
   - `[vbc_a link_url="..."]`: Thẻ liên kết `<a>` với URL linh hoạt.
   - `[vbc_icon icon_type="lucide" name="..." size="..." color="..."]`: Icon vector Lucide hoặc FontAwesome.

5. **Danh Sách Động Bài Viết & Sản Phẩm (Dynamic Query — `[vbc_post]`)**:
   - `[vbc_post]`: Shortcode truy vấn và hiển thị danh sách bài viết blog, tin tức hoặc sản phẩm WooCommerce linh hoạt theo Grid / List / Table.
   - **BẮT BUỘC**: Sử dụng khi nhận diện khu vực là row bài viết blog, tin tức hoặc danh sách sản phẩm / khóa học thay vì dựng cột tĩnh lặp lại.
   - **Cú pháp Blog Grid:** `[vbc_post post_type="post" posts_per_page="3" columns="3" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết"]`
   - **Cú pháp Product Grid:** `[vbc_post post_type="product" posts_per_page="4" columns="4" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay"]`

## ⚠️ QUY TẮC BẮT BUỘC: CÚ PHÁP SHORTCODE & XỬ LÝ NỘI DUNG

### 1. Quy Tắc Tự Đóng & Dùng Thuộc Tính (`text="..."`)
> [!CRITICAL]
> **TUYỆT ĐỐI KHÔNG GẮN VĂN BẢN THUẦN VÀO DẠNG ĐÓNG MỞ NHƯ `[vbc_p]...[/vbc_p]`, `[vbc_h1]...[/vbc_h1]`, `[vbc_span]...[/vbc_span]`**.
> Flatsome UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` làm hỏng giao diện.
>
> - **ĐÚNG (Dùng thuộc tính `text="..."` tự đóng):**
>   ```html
>   [vbc_h2 text="Mục tiêu khoá học" font_size="32px" font_weight="800"]
>   [vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]
>   [vbc_a href="#register" text="Đăng ký ngay" class="btn-primary"]
>   [vbc_span text="Hotline: 1900 6364" class="hotline-text"]
>   ```

### 2. Quy Tắc Dấu Nháy (Quote Nesting Rule — CẤM LỒNG NHÁY KÉP TRONG THUỘC TÍNH)
> [!WARNING]
> Khi giá trị thuộc tính nằm trong dấu nháy kép `text="..."`, **TUYỆT ĐỐI KHÔNG ĐƯỢC DÙNG NHÁY KÉP `"` BÊN TRONG**.
> Nếu cần dùng HTML (như `<span>`, `<b>`, `<a>`) bên trong `text="..."`, **100% CÁC THUỘC TÍNH HTML PHẢI DÙNG DẤU NHÁY ĐƠN `'`**.
>
> - ❌ **SAI (VỠ NÁT SHORTCODE PARSER DO NHÁY KÉP LỒNG NHAU):**
>   ```html
>   [vbc_p text="<span class="adv-num">01</span><span class="adv-title">Cam kết hiệu quả</span>"]
>   ```
> - ✅ **ĐÚNG (Dùng nháy đơn `'` cho class/style bên trong `text="..."`):**
>   ```html
>   [vbc_p text="<span class='adv-num'>01</span> <span class='adv-title'>Cam kết hiệu quả</span>"]
>   ```

### 3. Quy Tắc Thẻ Khối & Danh Sách (CẤM NHỒI `<ul>`, `<ol>`, `<div>` VÀO `[vbc_p]`)
> [!CRITICAL]
> Thẻ `<p>` trong chuẩn HTML5 **KHÔNG ĐƯỢC CHỨA** thẻ khối như `<ul>`, `<ol>`, `<li>`, `<div>`, `<h3>`. Trình duyệt sẽ tự ngắt `<p>` làm vỡ tan DOM.
> **TUYỆT ĐỐI KHÔNG** nhét danh sách `<ul><li>` vào `text="..."` của `[vbc_p]`.
>
> - ❌ **SAI (HTML KHÔNG HỢP LỆ & VỠ SHORTCODE):**
>   ```html
>   [vbc_p text="<ul class="check-list"><li>Học 1 kèm 1.</li><li>Lộ trình riêng.</li></ul>"]
>   ```
> - ✅ **ĐÚNG (Cách 1 — Bọc `<ul>` chuẩn trong `[vbc_div]` hoặc `[vbc_block]`):**
>   ```html
>   [vbc_div class="check-list-wrapper"]
>     <ul class="check-list">
>       <li>Học 1 kèm 1, sửa lỗi ngay lập tức.</li>
>       <li>Lộ trình riêng theo năng lực &amp; mục tiêu của từng bé.</li>
>       <li>Cam kết đầu ra bằng hợp đồng đào tạo pháp lý.</li>
>     </ul>
>   [/vbc_div]
>   ```
> - ✅ **ĐÚNG (Cách 2 — Từng mục dùng VBC Flex Items):**
>   ```html
>   [vbc_div class="check-list" display="flex" flex_direction="column" gap="10px"]
>     [vbc_div class="check-item" display="flex" align_items="center" gap="8px"]
>       [vbc_icon name="check-circle" size="18px" color="#10b981"]
>       [vbc_p text="Học 1 kèm 1, sửa lỗi ngay lập tức." margin="0"]
>     [/vbc_div]
>     [vbc_div class="check-item" display="flex" align_items="center" gap="8px"]
>       [vbc_icon name="check-circle" size="18px" color="#10b981"]
>       [vbc_p text="Lộ trình riêng theo năng lực &amp; mục tiêu của từng bé." margin="0"]
>     [/vbc_div]
>   [/vbc_div]
>   ```

### 4. Quy Tắc Phần Tử Phức Hợp (Compound Elements: Số Thứ Tự + Tiêu Đề)
> Khi gặp khối gồm nhiều thành phần (ví dụ: Số thứ tự `01` + Tiêu đề), **BÓC TÁCH RIÊNG TỪNG ELEMENT** bên trong `[vbc_div]`, không nhồi nhét tất cả vào 1 thẻ `[vbc_p]`:
>
> - ✅ **ĐÚNG (Bóc tách rõ ràng):**
>   ```html
>   [vbc_div class="adv-header" display="flex" align_items="center" gap="12px"]
>     [vbc_span text="01" class="adv-num"]
>     [vbc_h4 text="Cam kết hiệu quả – Cá nhân hóa 100%" class="adv-title"]
>   [/vbc_div]
>   ```

## Quy Tắc Định Kiểu CSS (CSS Selector Rules)
- Luôn sử dụng từ khóa `selector` để áp dụng CSS trực tiếp lên phần tử:
  ```css
  custom_css="selector { background: #fdf6eb; padding: 40px; border-radius: 16px; } selector:hover { transform: translateY(-4px); }"
  ```
- Viết Responsive trực tiếp trong `custom_css`:
  ```css
  @media(max-width: 849px) { selector { padding: 20px; } }
  ```
- **TUYỆT ĐỐI KHÔNG DÙNG NGOẶC VUÔNG `[` hoặc `]` TRONG `custom_css`**: Không dùng `input[type='tel']`, hãy dùng class selector như `input.wpcf7-tel` hoặc `.wpcf7-form input`.

## Tài liệu Tham khảo (References)
- [Bảng tra cứu toàn bộ Shortcodes VBC](./references/shortcodes-catalog.md)
- [Hướng dẫn viết CSS Responsive](./references/responsive-css-guide.md)

## Ví dụ (Examples)
- [Mẫu Section Hero hiện đại](./examples/modern-hero-section.vbc)

