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

## Quy Tắc Định Kiểu CSS (CSS Selector Rules)
- Luôn sử dụng từ khóa `selector` để áp dụng CSS trực tiếp lên phần tử:
  ```css
  custom_css="selector { background: #fdf6eb; padding: 40px; border-radius: 16px; } selector:hover { transform: translateY(-4px); }"
  ```
- Viết Responsive trực tiếp trong `custom_css`:
  ```css
  @media(max-width: 849px) { selector { padding: 20px; } }
  ```

## Tài liệu Tham khảo (References)
- [Bảng tra cứu toàn bộ Shortcodes VBC](./references/shortcodes-catalog.md)
- [Hướng dẫn viết CSS Responsive](./references/responsive-css-guide.md)

## Ví dụ (Examples)
- [Mẫu Section Hero hiện đại](./examples/modern-hero-section.vbc)
