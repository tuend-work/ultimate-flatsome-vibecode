# Thư Viện Mẫu Bố Cục Chuẩn Flatsome (Section + Row + Col) & VBC Elements

Thư viện mẫu các khối giao diện chuẩn sử dụng **Flatsome Native Layout (`[section]`, `[row]`, `[col]`)** kết hợp **Ultimate Flatsome VibeCode Elements** cho trải nghiệm kéo thả 100% trực quan trên Flatsome UX Builder:

---

## 1. Hero Section (2 Cột Text + Image Chuẩn Flatsome)
```html
[section bg_color="#fdf6eb" padding="80px" dark="false"]
  [row width="custom" custom_width="1140px" v_align="middle"]
    [col span="7" span__md="12" span__sm="12"]
      [vbc_h1 text="Giải Pháp Đột Phá Cho Doanh Nghiệp" color="#1e293b" font_size="42px" font_weight="900" line_height="1.2" margin="0 0 16px 0"]
      [vbc_p text="Tối ưu hóa hiệu suất và nâng cao năng lực cạnh tranh với nền tảng công nghệ tiên tiến chuẩn quốc tế." color="#475569" font_size="16px" line_height="1.7" margin="0 0 28px 0"]
      [vbc_a href="#dang-ky" text="Khám Phá Ngay →" bg_color="#f0493e" color="#ffffff" font_size="16px" font_weight="700" padding="14px 36px" border_radius="50px" display="inline-block" text_decoration="none"]
    [/col]
    [col span="5" span__md="12" span__sm="12" align="center"]
      [vbc_img src="https://.../hero-image.png" alt="Hero Image" width="100%" border_radius="20px"]
    [/col]
  [/row]
[/section]
```

---

## 2. 3-Column Features Grid Section (Lưới 3 Cột Điểm Nổi Bật)
```html
[section bg_color="#ffffff" padding="70px"]
  [row width="custom" custom_width="1140px"]
    [col span="12" align="center"]
      [vbc_p text="TÍNH NĂNG VƯỢT TRỘI" color="#f0493e" font_size="14px" font_weight="700" margin="0 0 8px 0"]
      [vbc_h2 text="Tại sao hơn 50.000 học viên tin chọn?" color="#1e293b" font_size="32px" font_weight="800" margin="0 0 40px 0"]
    [/col]
  [/row]
  [row width="custom" custom_width="1140px" col_bg="#f8fafc" col_bg_radius="16" padding="28px"]
    [col span="4" span__md="6" span__sm="12"]
      [vbc_icon icon_type="lucide" name="book-open" size="36px" color="#f0493e" margin="0 0 16px 0"]
      [vbc_h3 text="Giáo trình chuẩn Mỹ" color="#1e293b" font_size="18px" font_weight="700" margin="0 0 8px 0"]
      [vbc_p text="Bám sát khung Common Core Standards giúp trẻ phát triển ngôn ngữ tự nhiên." color="#64748b" font_size="14px"]
    [/col]
    [col span="4" span__md="6" span__sm="12"]
      [vbc_icon icon_type="lucide" name="users" size="36px" color="#2563eb" margin="0 0 16px 0"]
      [vbc_h3 text="100% Giáo viên bản ngữ" color="#1e293b" font_size="18px" font_weight="700" margin="0 0 8px 0"]
      [vbc_p text="Đội ngũ giảng viên giàu kinh nghiệm có chứng chỉ quốc tế TESOL, CELTA." color="#64748b" font_size="14px"]
    [/col]
    [col span="4" span__md="6" span__sm="12"]
      [vbc_icon icon_type="lucide" name="sparkles" size="36px" color="#10b981" margin="0 0 16px 0"]
      [vbc_h3 text="Học qua tương tác AI" color="#1e293b" font_size="18px" font_weight="700" margin="0 0 8px 0"]
      [vbc_p text="Gia sư AI 24/7 đồng hành luyện phát âm và sửa lỗi tức thì cho học viên." color="#64748b" font_size="14px"]
    [/col]
  [/row]
[/section]
```

---

## 3. Lead Generation CTA & Contact Form 7 Section
```html
[section bg_color="#F5568F" padding="70px" dark="true"]
  [row width="custom" custom_width="840px"]
    [col span="12" bg_color="#ffffff" bg_radius="24" padding="40px"]
      [vbc_h2 text="Đăng ký nhận tư vấn và học thử 01 buổi miễn phí" color="#1e293b" font_size="26px" font_weight="800" text_align="center" line_height="1.3" margin="0 0 24px 0"]
      [contact-form-7 id="1391" title="Form Đăng Ký Tư Vấn"]
      [vbc_p text="Chúng tôi cam kết bảo mật thông tin và liên hệ lại trong vòng 24h." color="#94a3b8" font_size="12px" text_align="center" margin="16px 0 0 0"]
    [/col]
  [/row]
[/section]
```

---

## 4. FAQ Accordion Section (Hỏi Đáp Thường Gặp)
```html
[section bg_color="#ffffff" padding="70px"]
  [row width="custom" custom_width="960px"]
    [col span="12" align="center"]
      [vbc_p text="FAQ" color="#F5568F" font_size="14px" font_weight="700" margin="0 0 8px 0"]
      [vbc_h2 text="Câu Hỏi Thường Gặp" color="#1e293b" font_size="32px" font_weight="800" margin="0 0 36px 0"]
    [/col]
  [/row]
  [row width="custom" custom_width="960px"]
    [col span="12"]
      [vbc_accordion style="separated" icon="plus" enable_schema="yes"]
        [vbc_accordion_item title="Khóa học dành cho độ tuổi nào?"]
          [vbc_p text="Chương trình được thiết kế chuyên biệt cho trẻ từ 3 đến 5 tuổi, chưa từng tiếp xúc hoặc mới bắt đầu học tiếng Anh." color="#475569"]
        [/vbc_accordion_item]
        [vbc_accordion_item title="Bé học trực tuyến có mang lại hiệu quả không?"]
          [vbc_p text="Với phương pháp tương tác đa giác quan, gamification và giáo viên kèm sát, trẻ học một cách hào hứng và tiếp thu tự nhiên như tiếng mẹ đẻ." color="#475569"]
        [/vbc_accordion_item]
      [/vbc_accordion]
    [/col]
  [/row]
[/section]
```

---

## 5. Blog Posts Grid Section (Tin Tức / Bài Viết Mới Nhất)

> 🚨 **BẮT BUỘC**: Khi thiết kế khu vực danh sách bài viết blog hoặc tin tức, sử dụng element `[vbc_post]` để tự động query bài viết động từ WordPress Database.

```html
[section bg_color="#f8fafc" padding="80px 0"]
  [row width="custom" custom_width="1200px"]
    [col span="12" align="center"]
      [vbc_p text="KIẾN THỨC HỮU ÍCH" color="#2563eb" font_size="14px" font_weight="700" margin="0 0 8px 0"]
      [vbc_h2 text="Cẩm Nang & Tin Tức Mới Nhất" color="#0f172a" font_size="34px" font_weight="800" margin="0 0 40px 0"]
    [/col]
  [/row]
  [row width="custom" custom_width="1200px"]
    [col span="12"]
      [vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" layout="grid" image_height="220px" title_tag="h3" button_text="Xem Chi Tiết" card_radius="16px"]
    [/col]
  [/row]
[/section]
```

---

## 6. Products / Courses Grid Section (Sản Phẩm / Khóa Học Nổi Bật)

> 🚨 **BẮT BUỘC**: Khi hiển thị danh sách sản phẩm WooCommerce, khóa học hoặc bảng giá, sử dụng `[vbc_post]` với `post_type="product"`.

```html
[section bg_color="#ffffff" padding="80px 0"]
  [row width="custom" custom_width="1200px"]
    [col span="12" align="center"]
      [vbc_p text="DANH MỤC KHÓA HỌC" color="#16a34a" font_size="14px" font_weight="700" margin="0 0 8px 0"]
      [vbc_h2 text="Khóa Học Tiêu Biểu Dành Cho Bạn" color="#0f172a" font_size="34px" font_weight="800" margin="0 0 40px 0"]
    [/col]
  [/row]
  [row width="custom" custom_width="1200px"]
    [col span="12"]
      [vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" layout="grid" fields="thumbnail:100%, categories:100%, title:100%, price:50%, button:50%" button_text="Đăng Ký Ngay" card_radius="16px"]
    [/col]
  [/row]
[/section]
```

