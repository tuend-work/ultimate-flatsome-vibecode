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
   - `[vbc_block_inner]`: Khung hàng/grid con cấp 2.
   - `[vbc_container]`: Cột/khung chứa item cấp 1.
   - `[vbc_container_inner]`: Cột/khung chứa item con cấp 2.

2. **Tiêu đề & Nội dung (Typography)**:
   - `[vbc_h1]` đến `[vbc_h6]`: Thẻ heading chuẩn SEO, hỗ trợ `custom_css`.
   - `[vbc_p]`: Thẻ đoạn văn bản `<p>`.
   - `[vbc_span]`: Thẻ văn bản nội dòng `<span>`.

3. **Liên kết & Biểu tượng (Interactive & Icons)**:
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

