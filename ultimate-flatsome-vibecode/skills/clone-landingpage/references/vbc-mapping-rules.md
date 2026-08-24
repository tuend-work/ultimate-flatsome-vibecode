# Quy Chuẩn Ánh Xạ HTML Sang VBC Elements (v2.3.2)

Bảng quy tắc chuyển đổi thẻ HTML chuẩn sang Shortcodes của **Ultimate Flatsome VibeCode Elements**:

---

## 1. Bảng Ánh Xạ Phần Tử (Element Mapping Table)

> 💡 **Ưu tiên**: Sử dụng thuộc tính nhập trực tiếp của shortcode (`color`, `bg_color`, `font_size`, `padding`, `margin`, `border_radius`, `box_shadow`, `display`, `gap`, `grid_columns`) để dễ dàng chỉnh sửa trực quan trên Flatsome UX Builder.

| Thẻ HTML Gốc | Phần tử VBC Elements | Cú pháp ngắn gọn khuyên dùng (Inputs) | Cú pháp Custom CSS (Khi cần nâng cao) |
|---|---|---|---|
| `<section>`, `<div class="wrapper">` | `[vbc_div]` | `[vbc_div width="100%" bg_color="#ffffff" padding="60px 0"]` | `[vbc_div custom_css="selector { ... }"]` |
| `<div class="container">` | `[vbc_box]` | `[vbc_box max_width="1200px" margin="0 auto" padding="0 20px"]` | `[vbc_box custom_css="selector { ... }"]` |
| `<div class="row">`, `<div class="grid">` | `[vbc_block]` | `[vbc_block display="grid" grid_columns="repeat(3, 1fr)" grid_columns__sm="1fr" gap="24px"]` | `[vbc_block custom_css="selector { ... }"]` |
| `<div class="col">`, `<div class="card">` | `[vbc_container]` | `[vbc_container bg_color="#fff" border_radius="16px" padding="24px" box_shadow="0 4px 6px -1px rgba(0,0,0,0.05)"]` | `[vbc_container custom_css="selector:hover { transform: translateY(-4px); }"]` |
| `<h1>` - `<h6>` | `[vbc_h1]` - `[vbc_h6]` | `[vbc_h2 font_family="Outfit" font_size="32px" font_size__sm="24px" font_weight="800" color="#0f172a"]` | `[vbc_h2 custom_css="selector { ... }"]` |
| `<p>` | `[vbc_p]` | `[vbc_p font_size="16px" color="#64748b" line_height="1.7" margin="0 0 16px 0"]` | `[vbc_p custom_css="selector { ... }"]` |
| `<a>` | `[vbc_a]` | `[vbc_a link_url="#dang-ky" bg_color="#2563eb" color="#fff" padding="12px 24px" border_radius="50px" font_weight="600"]` | `[vbc_a custom_css="selector:hover { opacity: 0.9; }"]` |
| `<span>` | `[vbc_span]` | `[vbc_span color="#2563eb" font_weight="700"]` | `[vbc_span custom_css="selector { ... }"]` |
| `<img>` | `[vbc_img]` | `[vbc_img img_source="manual" img_url="https://.../img.png" max_width="500px" border_radius="12px"]` *(Tự đóng)* | `[vbc_img custom_css="selector { ... }"]` |
| `<i>` / `<svg>` | `[vbc_icon]` | `[vbc_icon icon_type="lucide" name="check-circle" size="20px" color="#10b981"]` | — |
| `<details>`, `<div class="accordion">` | `[vbc_accordion]` / `[accordion]` | `[vbc_accordion]` hoặc `[accordion]` chứa các `[accordion-item]` / `[vbc_box_inner_X]` phân tầng | — |
| `<form>` | `[contact-form-7]` | `[contact-form-7 id="508" title="Form Tư Vấn"]` | — |

---

## 2. Quy Tắc Responsive 3 Màn Hình Trong Input

Mọi thuộc tính đều hỗ trợ hậu tố `__md` (Tablet max 849px) và `__sm` (Mobile max 549px):
```html
[vbc_block 
    display="grid" 
    grid_columns="repeat(3, 1fr)" 
    grid_columns__md="repeat(2, 1fr)" 
    grid_columns__sm="1fr" 
    gap="24px" 
    gap__sm="16px"
]
    [vbc_container padding="24px" padding__sm="16px" bg_color="#fff" border_radius="12px"]
        [vbc_h3 font_size="22px" font_size__sm="18px" font_weight="700" color="#1e293b"]Tiêu đề[/vbc_h3]
        [vbc_p font_size="15px" font_size__sm="14px" color="#64748b"]Nội dung mô tả...[/vbc_p]
    [/vbc_container]
[/vbc_block]
```

---

## 3. Khi Nào Mới Cần Dùng `custom_css`?

Chỉ dùng `custom_css` cho các trạng thái không thể nhập bằng input thông thường:
- Hiệu ứng hover: `custom_css="selector { transition: all 0.3s; } selector:hover { transform: translateY(-5px); }"`
- Phần tử giả `::before`, `::after`: `custom_css="selector::before { content: ''; position: absolute; ... }"`
- Hiệu ứng animation phức tạp hoặc backdrop filter glassmorphism.
