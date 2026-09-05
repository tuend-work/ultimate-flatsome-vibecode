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
| Row / Grid Blog Posts | `[vbc_post]` | `[vbc_post post_type="post" posts_per_page="3" columns="3" layout="grid"]` *(Dynamic Query)* |
| Row / Grid Products | `[vbc_post]` | `[vbc_post post_type="product" posts_per_page="4" columns="4" layout="grid"]` *(Dynamic Query)* |

---

## 3. Quy Chuẩn Nhận Diện Row Blog & Row Product (BẮT BUỘC DÙNG `[vbc_post]`)

> 🚨 **QUY TẮC BẮT BUỘC**: Khi quét mã nguồn HTML hoặc cấu trúc trang, nếu gặp khu vực hiển thị danh sách bài viết (Blog / Tin tức / Cẩm nang) hoặc danh sách sản phẩm (Products / Khóa học / Dịch vụ), **BẮT BUỘC sử dụng element `[vbc_post]`** thay vì dựng thủ công từng cột `[col]` tĩnh lặp đi lặp lại.

### 1. Dạng Row / Grid Bài Viết Blog (`post_type="post"`):
- **Dấu hiệu nhận biết**: Danh sách 3-4 bài viết có ảnh thumbnail, tiêu đề bài viết, tóm tắt, ngày đăng, tác giả, nút xem chi tiết.
- **Cú pháp sử dụng**:
  ```html
  [vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
  ```

### 2. Dạng Row / Grid Sản Phẩm (`post_type="product"` / CPT):
- **Dấu hiệu nhận biết**: Lưới 3-4 sản phẩm có ảnh sản phẩm, tên, danh mục, giá bán (`_price`, `sale_price`), nút mua hàng/đăng ký.
- **Cú pháp sử dụng**:
  ```html
  [vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay"]
  ```
---

## 🚨 QUY TẮC BẮT BUỘC: CÚ PHÁP SHORTCODE & NỘI DUNG VBC ELEMENTS

> ⚠️ **TUYỆT ĐỐI TUÂN THỦ 4 NGUYÊN TẮC VÀNG SAU ĐÂY ĐỂ TRÁNH LỖI VỠ GIAO DIỆN:**

### 1. Dùng thuộc tính `text="..."` tự đóng (Chống lỗi `wpautop` chèn thẻ `<p>` rác):
```html
<!-- ĐÚNG: Truyền nội dung qua thuộc tính text="..." -->
[vbc_p text="Học 1 kèm 1, sửa lỗi ngay lập tức." color="#1e293b" font_size="16px" font_weight="600" margin="0 0 12px 0"]
```

### 2. Dấu Nháy (Quote Nesting Rule — CẤM LỒNG NHÁY KÉP TRONG THUỘC TÍNH):
Khi giá trị thuộc tính nằm trong dấu nháy kép `text="..."`, **TUYỆT ĐỐI KHÔNG DÙNG NHÁY KÉP `"` BÊN TRONG**. Mọi thuộc tính HTML bên trong (`class`, `style`, `id`) **BẮT BUỘC DÙNG NHÁY ĐƠN `'`**.
- ❌ **SAI:** `[vbc_p text="<span class="adv-num">01</span><span class="adv-title">Cam kết</span>"]`
- ✅ **ĐÚNG:** `[vbc_p text="<span class='adv-num'>01</span> <span class='adv-title'>Cam kết</span>"]`

### 3. CẤM Thẻ Khối & Danh Sách Trong Thẻ `<p>` / `[vbc_p]`:
Thẻ `<p>` KHÔNG ĐƯỢC CHỨA `<ul>`, `<ol>`, `<li>`, `<div>`, `<h3>`. Danh sách phải được bọc trong container `[vbc_div]` hoặc tách thành các flex items:
- ❌ **SAI:** `[vbc_p text="<ul class="check-list"><li>Item 1</li><li>Item 2</li></ul>"]`
- ✅ **ĐÚNG (Cách 1 — HTML List trong Container):**
  ```html
  [vbc_div class="check-list-wrapper"]
    <ul class="check-list">
      <li>Học 1 kèm 1, sửa lỗi ngay lập tức.</li>
      <li>Lộ trình riêng theo năng lực &amp; mục tiêu của từng bé.</li>
    </ul>
  [/vbc_div]
  ```
- ✅ **ĐÚNG (Cách 2 — VBC Flex Items):**
  ```html
  [vbc_div class="check-list" display="flex" flex_direction="column" gap="10px"]
    [vbc_div class="check-item" display="flex" align_items="center" gap="8px"]
      [vbc_icon name="check-circle" size="18px" color="#10b981"]
      [vbc_p text="Học 1 kèm 1, sửa lỗi ngay lập tức." margin="0"]
    [/vbc_div]
  [/vbc_div]
  ```

### 4. Bóc Tách Khối Phức Hợp (Số thứ tự + Tiêu đề):
- ❌ **SAI:** `[vbc_p text="<span class="adv-num">01</span><span class="adv-title">Cam kết hiệu quả</span>"]`
- ✅ **ĐÚNG:**
  ```html
  [vbc_div class="adv-header" display="flex" align_items="center" gap="12px"]
    [vbc_span text="01" class="adv-num"]
    [vbc_h4 text="Cam kết hiệu quả – Cá nhân hóa 100%" class="adv-title"]
  [/vbc_div]
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

---

## 4. Quy Chuẩn CSS Bắt Buộc Cho Flatsome Accordion (Mũi Tên Sang Bên Phải)

Khi sử dụng `[accordion]` / `[accordion-item]`, Flatsome mặc định đặt `.toggle` tuyệt đối bên trái đè lên text. Để mũi tên nằm **hoàn toàn bên phải** và text bên trái giống website gốc, **BẮT BUỘC** khai báo trong `custom_css` của `[vbc_section]`:

```css
selector .accordion-title {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  text-decoration: none !important;
  position: relative !important;
}
selector .accordion-title > span,
selector .accordion-title span {
  order: 1 !important;
  flex: 1 1 auto !important;
  text-align: left !important;
  margin: 0 !important;
}
selector .accordion-title > .toggle,
selector .accordion-title .toggle,
selector .accordion-title button.toggle {
  order: 2 !important;
  position: static !important;
  left: auto !important;
  right: auto !important;
  top: auto !important;
  bottom: auto !important;
  float: none !important;
  transform: none !important;
  margin: 0 0 0 16px !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
}
selector .accordion-title.active > .toggle,
selector .accordion-title.active .toggle,
selector .accordion-title.active button.toggle {
  transform: rotate(180deg) !important;
}
```
