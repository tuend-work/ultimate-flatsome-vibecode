# Danh Mục Chi Tiết Hệ Thống Shortcodes Flatsome & VBC Elements

Tài liệu tra cứu đầy đủ các thuộc tính và cách kết hợp tối ưu giữa **Khung xương Bố cục Flatsome Native (`[section]`, `[row]`, `[col]`)** và **Ultimate Flatsome VibeCode Elements**:

---

## 🏛️ PHẦN 1: KHUNG XƯƠNG BỐ CỤC (FLATSOME NATIVE LAYOUT)

### 1. `[section]`
- **Mô tả:** Thẻ bao bọc toàn bộ Section full-width chuẩn Flatsome.
- **Thuộc tính chính:**
  - `bg_color`: Màu nền mã Hex (vd: `#ffffff`, `#f8fafc`, `#F5568F`).
  - `bg`: Đường dẫn hình ảnh nền URL.
  - `bg_overlay`: Màu lớp phủ (vd: `rgba(0,0,0,0.5)`).
  - `padding`: Khoảng cách đệm trên dưới (vd: `60px`, `80px`).
  - `padding__sm`: Padding trên màn hình di động (vd: `30px`).
  - `dark`: Chế độ chữ sáng trên nền tối (`true` / `false`).
  - `effect`: Hiệu ứng hạt (`snow`, `sparkle`, `rain`, `confetti`).
- **Ví dụ:**
  ```html
  [section bg_color="#f8fafc" padding="70px" dark="false"]...[/section]
  ```

### 2. `[row]`
- **Mô tả:** Thẻ quản lý hàng và lưới cột 12-grid chuẩn Flatsome.
- **Thuộc tính chính:**
  - `width`: Chiều rộng (`custom`, `full-width`, mặc định 1080px).
  - `custom_width`: Độ rộng tối đa (vd: `1140px`, `960px`, `1200px`).
  - `v_align`: Canh lề dọc (`middle`, `top`, `bottom`, `equal-height`).
  - `h_align`: Canh lề ngang (`center`, `right`).
  - `col_bg`: Màu nền áp dụng cho tất cả các cột con (vd: `#ffffff`).
  - `col_bg_radius`: Bo góc các cột con (vd: `16`).
  - `padding`: Padding bên trong các cột con (vd: `24px`).
- **Ví dụ:**
  ```html
  [row width="custom" custom_width="1140px" v_align="middle"]...[/row]
  ```

### 3. `[col]`
- **Mô tả:** Cột con trong lưới 12-grid responsive chuẩn Flatsome UX Builder.
- **Thuộc tính chính:**
  - `span`: Độ rộng cột trên Desktop (vd: `12` = 100%, `6` = 50%, `4` = 33.3%, `3` = 25%).
  - `span__md`: Độ rộng cột trên Tablet (vd: `6` hoặc `12`).
  - `span__sm`: Độ rộng cột trên Mobile (vd: `12`).
  - `bg_color`: Màu nền riêng cho cột.
  - `bg_radius`: Bo góc cột (px).
  - `padding`: Khoảng cách đệm trong cột (vd: `24px`).
  - `margin`: Lề ngoài cột (vd: `0 0 20px 0`).
  - `align`: Canh lề văn bản (`left`, `center`, `right`).
  - `depth`: Đổ bóng (`1`, `2`, `3`, `4`, `5`).
  - `depth_hover`: Đổ bóng khi rê chuột.
  - `animate`: Hiệu ứng xuất hiện (`fadeInUp`, `fadeInLeft`, `bounceIn`).
- **Ví dụ:**
  ```html
  [col span="4" span__md="6" span__sm="12" align="center" bg_color="#ffffff" bg_radius="16" padding="24px" depth="2"]
    ...
  [/col]
  ```

---

## 🎨 PHẦN 2: PHẦN TỬ CON NGUYÊN TỬ (VBC ATOMIC ELEMENTS)

> [!CRITICAL]
> **100% PHẦN TỬ VĂN BẢN PHẢI DÙNG THUỘC TÍNH `text="..."` (SELF-CLOSING SHORTCODES)**
> - **CẤM TUYỆT ĐỐI**: Không bao giờ viết dạng thẻ đóng mở có ruột văn bản như `[vbc_p]...[/vbc_p]`, `[vbc_h2]...[/vbc_h2]`, `[vbc_span]...[/vbc_span]`. UX Builder và bộ lọc `wpautop` của WordPress sẽ tự động nhồi nhét thẻ `<p>` vào ruột thẻ, sinh ra cấu trúc lỗi `<p><p>...</p></p>` làm hỏng giao diện.
> - **ĐÚNG**: `[vbc_p text="Nâng band điểm <b>Listening</b>." class="target-text"]`
> - **SAI**: `[vbc_p class="target-text"]Nâng band điểm <b>Listening</b>.[/vbc_p]`
> - **Định dạng HTML**: Viết trực tiếp `<b>`, `<strong>`, `<span>`, `<br>` vào trong `text="..."`. Không dùng ngoặc vuông `[` hoặc `]` trong thuộc tính.

### 4. `[vbc_h1]` đến `[vbc_h6]`
- **Mô tả:** Các thẻ tiêu đề chuẩn SEO.
- **Thuộc tính:** `text`, `color`, `font_size`, `font_size__sm`, `font_weight`, `line_height`, `text_align`, `margin`.
- **Ví dụ:**
  ```html
  [vbc_h2 text="Mục tiêu khóa học" color="#1e293b" font_size="32px" font_weight="800" text_align="center" margin="0 0 16px 0"]
  ```

### 5. `[vbc_p]` & `[vbc_span]`
- **Mô tả:** Đoạn văn bản và cụm từ nội dòng.
- **Thuộc tính:** `text`, `color`, `font_size`, `font_size__sm`, `line_height`, `text_align`, `margin`, `class`.
- **Ví dụ:**
  ```html
  [vbc_p text="Chương trình đào tạo phản xạ ngôn ngữ chuẩn quốc tế." color="#475569" font_size="15px" line_height="1.7"]
  [vbc_p text="Hiểu rõ cấu trúc đề thi trong <b>2 kỹ năng</b> chính." class="desc-text"]
  ```

### 6. `[vbc_img]` *(Tự đóng)*
- **Mô tả:** Thẻ hình ảnh tự đóng, chống sinh thẻ p rác.
- **Thuộc tính:** `src` (hoặc `img_url`), `alt`, `width`, `height`, `object_fit`, `border_radius`, `box_shadow`, `margin`.
- **Ví dụ:**
  ```html
  [vbc_img src="https://.../banner.png" alt="Banner" width="100%" border_radius="16px"]
  ```

### 7. `[vbc_a]`
- **Mô tả:** Nút bấm hành động CTA hoặc liên kết.
- **Thuộc tính:** `href` (hoặc `link_url`), `text`, `bg_color`, `color`, `font_size`, `font_weight`, `padding`, `border_radius`, `display`.
- **Ví dụ:**
  ```html
  [vbc_a href="#dang-ky" text="Học Thử Miễn Phí Ngay" bg_color="#F5568F" color="#ffffff" font_size="16px" font_weight="700" padding="16px 36px" border_radius="50px" display="inline-block" text_decoration="none"]
  ```

### 8. `[vbc_icon]` *(Tự đóng)*
- **Mô tả:** Biểu tượng vector SVG từ 5 bộ icon (`lucide`, `fontawesome`, `remixicon`, `phosphor`, `material`).
- **Thuộc tính:** `icon_type`, `name`, `size`, `color`, `margin`.
- **Ví dụ:**
  ```html
  [vbc_icon icon_type="lucide" name="shield-check" size="28px" color="#10b981"]
  ```

### 9. `[contact-form-7]`
- **Mô tả:** Biểu mẫu thu thập khách hàng thực tế (BẮT BUỘC).
- **Tạo Form:** Chạy `python .agents/skills/clone-landingpage/scripts/create_cf7.py --title "..." --fields "name,phone,email,course,message"`.
- **Ví dụ nhúng:**
  ```html
  [contact-form-7 id="1391" title="Form Đăng Ký Tư Vấn - Tiếng Anh Mẫu Giáo"]
  ```

### 10. `[vbc_accordion]` & `[vbc_accordion_item]`
- **Mô tả:** Khối hỏi đáp thường gặp FAQ hoặc danh sách lợi ích xổ xuống.
- **Ví dụ:**
  ```html
  [vbc_accordion style="separated" icon="plus" enable_schema="yes"]
    [vbc_accordion_item title="Hình thức học như thế nào?"]
      [vbc_p text="Học trực tuyến 1 kèm 1 hoặc nhóm nhỏ qua nền tảng tương tác thông minh." color="#475569"]
    [/vbc_accordion_item]
  [/vbc_accordion]
  ```

### 11. `[vbc_tabs]` & `[vbc_tab]`
- **Mô tả:** Hệ thống tab chuyển đổi nội dung linh hoạt.
- **Ví dụ:**
  ```html
  [vbc_tabs style="pills" align="center" active_tab="1"]
    [vbc_tab title="Star 1 (3-4 tuổi)" icon="fa fa-star"]
      [vbc_p text="Làm quen với âm thanh, từ vựng cơ bản và bài hát tiếng Anh." color="#475569"]
    [/vbc_tab]
    [vbc_tab title="Star 2 (4-5 tuổi)" icon="fa fa-star"]
      [vbc_p text="Xây dựng phản xạ giao tiếp tự nhiên và phát âm chuẩn phonic." color="#475569"]
    [/vbc_tab]
  [/vbc_tabs]
  ```

### 12. `[vbc_post]`
- **Mô tả:** Truy vấn động và hiển thị danh sách Bài viết Blog, Tin tức, Sản phẩm WooCommerce hoặc Custom Post Types với bố cục Grid / List / Table và hệ thống thẻ Card bo góc chuyên nghiệp.
- **Quy tắc:** BẮT BUỘC sử dụng khi nhận diện khu vực hiển thị danh sách bài viết (Blog / Tin tức) hoặc danh sách sản phẩm / khóa học / bảng giá.
- **Thuộc tính chính:**
  - `post_type`: Loại bài viết (`post`, `product`, hoặc slug CPT bất kỳ). Mặc định `post`.
  - `posts_per_page`: Số lượng bài viết/sản phẩm hiển thị. Mặc định `8`.
  - `columns`, `columns__md`, `columns__sm`: Số cột chia responsive (ví dụ: `3`, `2`, `1`).
  - `layout`: Bố cục hiển thị (`grid`, `list`, `table`). Mặc định `grid`.
  - `fields`: Danh sách trường và độ rộng hiển thị (ví dụ: `thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, price:50%, button:100%`).
  - `image_height`: Chiều cao ảnh thumbnail (ví dụ: `220px`).
  - `image_fit`: Kiểu căn ảnh (`cover`, `contain`).
  - `title_tag`: Thẻ tiêu đề (`h2`, `h3`, `h4`).
  - `button_text`: Văn bản nút CTA (ví dụ: `Xem Chi Tiết`, `Đọc Bài Viết`, `Mua Ngay`).
  - `card_bg`, `card_radius`, `card_border`, `card_shadow`: Định dạng thẻ Card.
- **Ví dụ Blog Grid:**
  ```html
  [vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
  ```
- **Ví dụ WooCommerce Product Grid:**
  ```html
  [vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Mua Ngay" card_radius="16px"]
  ```
