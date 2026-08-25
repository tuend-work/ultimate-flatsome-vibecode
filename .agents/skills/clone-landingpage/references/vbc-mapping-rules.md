# Quy Chuẩn Ánh Xạ HTML Sang Bố Cục Flatsome & VBC Elements

Quy chuẩn chuyển đổi HTML sang cấu trúc kết hợp tối ưu: **Khung xương Bố cục bằng Flatsome Native (`[section]`, `[row]`, `[col]`)** + **Nội dung & Phần tử con bằng Ultimate Flatsome VibeCode Elements**.

---

## 1. Bảng Ánh Xạ Cấu Trúc Bố Cục (Layout Architecture)

> 💡 **NGUYÊN TẮC VÀNG**:
> - **Khung xương Bố cục**: Ưu tiên sử dụng `[section]` $\to$ `[row]` $\to$ `[col]` của Flatsome vì tương thích 100% với hệ thống kéo thả, chia cột 12-grid của Flatsome UX Builder.
> - **Phần tử Con & Nội Dung**: Sử dụng VBC Elements (`[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_accordion]`, `[vbc_tabs]`, `[contact-form-7]`, `[vbc_post]`) bên trong `[col]` để style chi tiết typography, gradient, icon, dynamic posts và lead form.

| Thẻ HTML Gốc | Bố cục Khuyên dùng (Flatsome Native) | Bố cục VBC Thuần (Khi cần Grid/Flex đặc thù) |
|---|---|---|
| `<section>`, `<div class="section">` | `[section bg_color="#ffffff" padding="60px" dark="false"]` | `[vbc_div width="100%" bg_color="#ffffff" padding="60px 0"]` |
| `<div class="container">` | `[row width="custom" custom_width="1140px"]` | `[vbc_container max_width="1140px" margin="0 auto"]` |
| `<div class="row">`, `<div class="grid">` | `[row v_align="middle" col_bg="#..." col_bg_radius="16"]` | `[vbc_box display="grid" grid_columns="repeat(3, 1fr)" gap="24px"]` |
| `<div class="col-4">` (1/3 cột) | `[col span="4" span__md="6" span__sm="12" padding="20px"]` | `[vbc_block bg_color="#fff" border_radius="16px" padding="24px"]` |
| `<div class="col-6">` (1/2 cột) | `[col span="6" span__sm="12" align="center"]` | `[vbc_block bg_color="#fff" padding="24px"]` |
| `<div class="col-12">` (Full width) | `[col span="12" align="center"]` | `[vbc_block width="100%"]` |

---

## 2. Bảng Ánh Xạ Phần Tử Con Nội Dung (VBC Atomic Elements)

| Thẻ HTML Gốc | Phần tử VBC Elements | Cú pháp ngắn gọn chuẩn xác (Inputs) |
|---|---|---|
| `<h1>` - `<h6>` | `[vbc_h1]` - `[vbc_h6]` | `[vbc_h2 text="Tiêu đề chính" color="#0f172a" font_size="32px" font_weight="800" text_align="center"]` |
| `<p>` | `[vbc_p]` | `[vbc_p text="Đoạn mô tả ngắn..." color="#64748b" font_size="16px" line_height="1.7"]` |
| `<a>` | `[vbc_a]` | `[vbc_a href="#dang-ky" text="Đăng ký ngay" bg_color="#2563eb" color="#fff" padding="14px 32px" border_radius="50px"]` |
| `<span>` | `[vbc_span]` | `[vbc_span text="Ưu đãi 50%" color="#ef4444" font_weight="700"]` |
| `<img>` | `[vbc_img]` | `[vbc_img src="https://.../img.png" alt="..." width="100%" border_radius="12px"]` *(Tự đóng)* |
| `<i>` / `<svg>` | `[vbc_icon]` | `[vbc_icon icon_type="lucide" name="check-circle" size="20px" color="#10b981"]` |
| `<form>` | `[contact-form-7]` | `[contact-form-7 id="<ID>" title="..."]` *(Tạo tự động qua `create_cf7.py`)* |
| `<details>`, FAQ list | `[vbc_accordion]` | `[vbc_accordion][vbc_accordion_item title="..."]...[/vbc_accordion_item][/vbc_accordion]` |
| Tabs | `[vbc_tabs]` | `[vbc_tabs style="pills"][vbc_tab title="..."]...[/vbc_tab][/vbc_tabs]` |
| Blog Posts / Products | `[vbc_post]` | `[vbc_post post_type="post" posts_per_page="3" columns="3"]` *(Dynamic Query)* |
---

## 🚨 QUY TẮC BẮT BUỘC: TRUYỀN NỘI DUNG VÀO INPUT CỦA SHORTCODE (CHỐNG LỖI WPAUTOP)

> ⚠️ **TUYỆT ĐỐI KHÔNG** truyền nội dung bằng cách lồng thẻ thô `<img ...>` hoặc HTML vào giữa cặp thẻ `[vbc_p]...[/vbc_p]` hay `[vbc_h1]-[vbc_h6]`, vì bộ xử lý `wpautop` của WordPress sẽ tự động nhồi thẻ `<p>` rác vào trong làm vỡ bố cục giao diện.

### ❌ CÁCH VIẾT SAI (BỊ WPAUTOP INJECT THẺ P):
```html
<!-- SAI: Nhét trực tiếp thẻ img vào giữa cặp thẻ vbc_p -->
[vbc_p color="#1e293b" font_size="16px" font_weight="600" margin="0 0 12px 0"]<img src="https://.../icon-check.png" width="18px" height="18px" style="vertical-align:middle; margin-right:8px;"> Học 1 kèm 1, sửa lỗi ngay lập tức.[/vbc_p]
```

### ✅ CÁCH VIẾT ĐÚNG CHUẨN 100%:
**Cách 1: Truyền nội dung qua thuộc tính `text="..."` hoặc `content="..."`**
```html
[vbc_p text="Học 1 kèm 1, sửa lỗi ngay lập tức." color="#1e293b" font_size="16px" font_weight="600" margin="0 0 12px 0"]
```

**Cách 2: Tách biệt Icon/Image và Text trong Flex Container**
```html
[vbc_block display="flex" align_items="center" gap="10px" margin="0 0 12px 0"]
  [vbc_img src="https://.../icon-check.png" width="18px" height="18px"]
  [vbc_p text="Học 1 kèm 1, sửa lỗi ngay lập tức." color="#1e293b" font_size="16px" font_weight="600" margin="0"]
[/vbc_block]
```

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
