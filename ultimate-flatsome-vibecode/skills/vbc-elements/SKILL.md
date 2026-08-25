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

## ⚠️ QUY TẮC BẮT BUỘC: 100% SHORTCODE TỰ ĐÓNG DÙNG THUỘC TÍNH (NO ENCLOSING TEXT)
> [!CRITICAL]
> **TUYỆT ĐỐI KHÔNG GẮN CONTENT VÀO SHORTCODE DẠNG ĐÓNG MỞ NHƯ `[vbc_p]...[/vbc_p]`, `[vbc_h1]...[/vbc_h1]`, `[vbc_span]...[/vbc_span]`**.
> Flatsome UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` làm hỏng giao diện.
>
> - **ĐÚNG (100% Dùng thuộc tính `text="..."` tự đóng):**
>   ```html
>   [vbc_h2 text="Mục tiêu khoá học" font_size="32px" font_weight="800"]
>   [vbc_p text="Khắc phục điểm yếu, nâng band điểm <b>Listening</b>." class="target-text"]
>   [vbc_a href="#register" text="Đăng ký ngay" class="btn-primary"]
>   [vbc_span text="Hotline: 1900 6364" class="hotline-text"]
>   ```
> - **SAI (CẤM TUYỆT ĐỐI):**
>   ```html
>   <!-- CẤM: UX Builder sẽ chèn thẻ p vào giữa gây vỡ layout -->
>   [vbc_p class="target-text"]Khắc phục điểm yếu, nâng band điểm <b>Listening</b>.[/vbc_p]
>   [vbc_h2]Mục tiêu khoá học[/vbc_h2]
>   ```
> - **Văn bản có định dạng (`<b>`, `<strong>`, `<span>`, `<br>`):** Viết trực tiếp thẻ HTML vào trong `text="..."` (Ví dụ: `text="Học <b>1 kèm 1</b> cùng giáo viên"`). Tuyệt đối không dùng dấu ngoặc vuông `[` hoặc `]` bên trong giá trị thuộc tính.

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

