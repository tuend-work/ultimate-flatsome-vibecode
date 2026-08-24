# Danh Mục Chi Tiết 15+ Shortcodes VBC Elements

Bảng tra cứu đầy đủ các thuộc tính và cách dùng của từng shortcode:

---

### 1. `[vbc_div]`
- **Mô tả:** Thẻ bao ngoài Section toàn màn hình.
- **Thuộc tính:** `id`, `class`, `custom_css`.
- **Ví dụ:**
  ```html
  [vbc_div id="about" custom_css="selector { width: 100%; background: #ffffff; padding: 60px 0; }"]...[/vbc_div]
  ```

### 2. `[vbc_box]`
- **Mô tả:** Thẻ giới hạn bề rộng (container).
- **Thuộc tính:** `class`, `custom_css`.
- **Ví dụ:**
  ```html
  [vbc_box class="container" custom_css="selector { max-width: 1200px; margin: 0 auto; padding: 0 20px; }"]...[/vbc_box]
  ```

### 3. `[vbc_block]` & `[vbc_block_inner]`
- **Mô tả:** Khung chứa Grid/Flexbox cấp 1 và cấp 2.
- **Thuộc tính:** `class`, `custom_css`.

### 4. `[vbc_container]` & `[vbc_container_inner]`
- **Mô tả:** Khung item con trong Grid.
- **Thuộc tính:** `class`, `custom_css`.

### 5. `[vbc_h1]` - `[vbc_h6]`
- **Mô tả:** Các thẻ tiêu đề chuẩn SEO.
- **Thuộc tính:** `class`, `custom_css`.

### 6. `[vbc_p]` & `[vbc_span]`
- **Mô tả:** Đoạn văn bản và văn bản nội dòng.
- **Thuộc tính:** `class`, `custom_css`.

### 7. `[vbc_a]`
- **Mô tả:** Thẻ liên kết `<a>`.
- **Thuộc tính:** `link_url`, `link_target`, `class`, `custom_css`.

### 8. `[vbc_icon]`
- **Mô tả:** Biểu tượng vector SVG.
- **Thuộc tính:** `icon_type` (`lucide` / `fontawesome`), `name`, `size`, `color`.
- **Ví dụ:**
  ```html
  [vbc_icon icon_type="lucide" name="star" size="18px" color="#f59e0b"]
  ```

### 9. `[vbc_tabs]` & `[vbc_tab]`
- **Mô tả:** Hệ thống Tabs chuyển đổi nội dung tương tác (Responsive Tabbed Navigation & Content Panes).
- **Thuộc tính `[vbc_tabs]`:** `style` (`pills`, `underline`, `cards`, `glass`), `align` (`left`, `center`, `right`, `justify`), `active_tab` (`1`, `2`, ...), `tab_bg`, `tab_active_bg`, `tab_color`, `tab_active_color`, `class`, `custom_css`.
- **Thuộc tính `[vbc_tab]`:** `title`, `icon`, `tab_id`, `custom_class`.
- **Ví dụ:**
  ```html
  [vbc_tabs style="pills" align="center" active_tab="1"]
    [vbc_tab title="Tổng Quan" icon="fa fa-info-circle"]
      [vbc_p]Nội dung giới thiệu tổng quan dịch vụ...[/vbc_p]
    [/vbc_tab]
    [vbc_tab title="Bảng Giá" icon="fa fa-tag"]
      [vbc_p]Bảng giá chi tiết các gói...[/vbc_p]
    [/vbc_tab]
  [/vbc_tabs]
  ```

### 10. `[vbc_post]`
- **Mô tả:** Truy vấn động danh sách Bài viết (Blog posts) hoặc Sản phẩm (WooCommerce products) trực tiếp từ database backend WordPress.
- **Thuộc tính:** `post_type` (`post` / `product` / CPT), `posts_per_page`, `columns` (`2`, `3`, `4`, `5`), `layout` (`grid`, `list`, `table`), `orderby`, `order`, `taxonomy`, `terms`, `title_size`, `title_color`.
- **Ví dụ:**
  ```html
  [vbc_post post_type="post" posts_per_page="6" columns="3" layout="grid"]
  [vbc_post post_type="product" posts_per_page="8" columns="4" layout="grid"]
  ```

