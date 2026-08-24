---
name: clone-landingpage
description: >-
  Tự động sao chép (clone) toàn bộ giao diện và nội dung từ một trang web bất kỳ sang WordPress Flatsome bằng 100% phần tử Ultimate Flatsome VibeCode Elements do AI trực tiếp sinh ra. Sử dụng khi người dùng yêu cầu clone/sao chép landing page, bóc tách layout từ URL gốc, hoặc chuyển đổi giao diện sang VBC.
---

# Clone Landing Page (AI-First LLM Architecture)

## Mục tiêu (Goal)
Sử dụng **Trí tuệ Nhân tạo (LLM)** để phân tích ngữ cảnh, bố cục thị giác và cấu trúc nội dung từ trang web nguồn, sau đó AI trực tiếp thiết kế và sinh 100% mã nguồn **Native VBC Elements** (`[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_card]`, `[vbc_tabs]`, `[vbc_accordion]`, `[contact-form-7]`, `[vbc_post]`) với tất cả thuộc tính giao diện đưa trực tiếp vào thuộc tính của shortcode, đạt độ tương đồng thị giác (VSI) $\ge 90\%$ và **0 unparsed tags**.

---

## Quy trình Thực hiện (Workflow)

### Bước 1: Quét & Đồng bộ Media lên WordPress Media Library
Chạy script đồng bộ ảnh:
```bash
python .agents/skills/clone-landingpage/scripts/sync_media.py --url "<URL_NGUON>"
```
- Script sẽ tải mã nguồn HTML về `tmp/<slug>/source.html`, phát hiện toàn bộ ảnh và upload lên WordPress Media Library qua REST API `/vbc/v1/upload`.
- Kết quả ánh xạ ảnh gốc $\to$ WordPress URL được lưu trong `tmp/<slug>/media_map.json`.

### Bước 2: AI Đọc Hiểu & Bóc Tách Ngữ Cảnh Bố Cục (DOM & Visual Context)
AI đọc `tmp/<slug>/source.html` và phân tích các Section chính:
1. **Header / Topbar**: Logo, hotline, navigation links, CTA button.
2. **Hero Section**: Tiêu đề chính H1, slogan, badge ưu đãi, bullet points lợi ích, form đăng ký, hình ảnh đại diện.
3. **Highlights / Features Grid**: Lưới 3–4 cột với các điểm mạnh dịch vụ, icon vector trực quan.
4. **Programs / Services / Courses**: Thẻ khóa học, bảng giá, chương trình chi tiết theo đối tượng.
5. **Tabs & Accordions**: Lộ trình đào tạo (`[vbc_tabs]`), câu hỏi thường gặp / lợi thế đánh số (`[accordion]` / `[vbc_accordion]`).
6. **Blog / Tin Tức & Sản Phẩm (Dynamic Query)**: Danh sách bài viết blog, tin tức hoặc sản phẩm WooCommerce $\to$ Sử dụng `[vbc_post]` (`post_type="post"` hoặc `post_type="product"`) để truy vấn động từ WordPress Database.
7. **Social Proof & Testimonials**: Cảm nhận khách hàng/học viên, rating, feedback thực tế.
8. **Lead Form & CTA Banner**: Form Contact Form 7 (`[contact-form-7]`) và nút kêu gọi hành động.
9. **Footer**: Thông tin liên hệ, bản quyền, liên kết điều khoản.

### Bước 3: AI Sinh 100% Native VBC Elements (Trực Tiếp Gắn Thuộc Tính Styling)
AI viết trực tiếp file mã nguồn VBC Elements lưu tại `tmp/<slug>/compiled_vbc.txt`.
- **Ràng buộc quan trọng**:
  - Gắn thuộc tính trực tiếp vào shortcode: `bg_color="..."`, `color="..."`, `font_size="..."`, `font_weight="..."`, `padding="..."`, `margin="..."`, `border_radius="..."`, `box_shadow="..."`, `display="flex|grid"`, `gap="..."`, `grid_columns="..."`, `text_align="..."`.
  - Thay thế 100% link ảnh bằng URL WordPress từ `media_map.json`.
  - **Nội dung Động (Dynamic Posts/Products)**: Khi phân đoạn là danh sách bài viết blog, tin tức kiến thức hoặc sản phẩm, PHẢI dùng `[vbc_post post_type="post|product" posts_per_page="..." columns="..." fields="thumbnail:100%, title:100%, excerpt:100%, button:100%"]` để lấy trực tiếp dữ liệu động từ website.
  - Giữ cấu trúc phân cấp lồng nhau chuẩn xác: dùng `_inner_1`, `_inner_2` cho các thẻ con bên trong để đảm bảo **0 unparsed shortcodes**.

### Bước 4: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/clone-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/compiled_vbc.txt" [--post_id <POST_ID>]
```

### Bước 5: Đối Soát Chất Lượng & Thị Giác (Quality Audit)
Chạy script rechecker:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>" --source_url "<SOURCE_URL>"
```
Đảm bảo kết quả đạt:
- **0 Unparsed Shortcodes**.
- **VSI $\ge 90\%$**.
- Tương thích 100% với trình kéo thả Flatsome UX Builder.
