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
5. **Tabs & Accordions**: Lộ trình đào tạo (`[vbc_tabs]`), câu hỏi thường gặp / lợi thế đánh số (`[vbc_accordion]` & `[vbc_accordion_item]`).
6. **Blog / Tin Tức & Sản Phẩm (Dynamic Query)**: Danh sách bài viết blog, tin tức hoặc sản phẩm WooCommerce $\to$ Sử dụng `[vbc_post]` (`post_type="post"` hoặc `post_type="product"`) để truy vấn động từ WordPress Database.
7. **Social Proof & Testimonials**: Cảm nhận khách hàng/học viên, rating, feedback thực tế.
8. **Lead Form & CTA Form (Contact Form 7)**: Biểu mẫu form đăng ký nhận tư vấn, tải tài liệu hoặc đăng ký học.
9. **Footer**: Thông tin liên hệ, bản quyền, liên kết điều khoản.

### Bước 3: Tự động Tạo Biểu Mẫu Contact Form 7 (BẮT BUỘC)
Khi phát hiện form nhập liệu trên trang nguồn (hoặc khu vực CTA đăng ký nhận ưu đãi/tư vấn):
1. **Phân tích các trường dữ liệu**: Bóc tách tên trường, placeholder, kiểu input (Họ tên, SĐT, Email, Khóa học, Nội dung...).
2. **Chạy script sinh Form CF7 qua REST API**:
   ```bash
   python .agents/skills/clone-landingpage/scripts/create_cf7.py --title "Form Đăng Ký - <Tên Landing Page>" --fields "name,phone,email,course,message" --button "Đăng ký tư vấn miễn phí"
   ```
3. **Lấy mã Shortcode trả về** dạng `[contact-form-7 id="<ID>" title="..."]` để nhúng trực tiếp vào container VBC ở Bước 4.
4. **Quy tắc**: 100% biểu mẫu thu thập thông tin (Lead Form) **BẮT BUỘC** phải được tạo thành form Contact Form 7 thực tế qua API, **TUYỆT ĐỐI KHÔNG** dùng văn bản giả lập tĩnh (`[vbc_p]`) thay cho form.

### Bước 4: AI Sinh 100% Native VBC Elements (Trực Tiếp Gắn Thuộc Tính Styling)
AI viết trực tiếp file mã nguồn VBC Elements lưu tại `tmp/<slug>/compiled_vbc.txt`.
- **Ràng buộc quan trọng**:
  - Gắn thuộc tính trực tiếp vào shortcode: `bg_color="..."`, `color="..."`, `font_size="..."`, `font_weight="..."`, `padding="..."`, `margin="..."`, `border_radius="..."`, `box_shadow="..."`, `display="flex|grid"`, `gap="..."`, `grid_columns="..."`, `text_align="..."`.
  - Thay thế 100% link ảnh bằng URL WordPress từ `media_map.json`.
  - **Tích hợp Form CF7**: Nhúng mã `[contact-form-7 id="..." title="..."]` vào trong khối container `[vbc_container]` / `[vbc_box]` đã được style.
  - **Nội dung Động (Dynamic Posts/Products)**: Khi phân đoạn là danh sách bài viết blog, tin tức kiến thức hoặc sản phẩm, PHẢI dùng `[vbc_post post_type="post|product" posts_per_page="..." columns="..." fields="thumbnail:100%, title:100%, excerpt:100%, button:100%"]` để lấy trực tiếp dữ liệu động từ website.
  - **Truyền nội dung qua input (`text="..."` / `content="..."`)**: Tuyệt đối không lồng thẻ thô hoặc `<img>` vào giữa cặp thẻ `[vbc_p]...[/vbc_p]` hay `[vbc_h1]-[vbc_h6]`, vì WordPress `wpautop` sẽ tự động chèn thẻ `<p>` rác làm vỡ layout. Thay vào đó, hãy truyền nội dung vào input `text="..."` hoặc tách thành các phần tử nguyên tử (`[vbc_img]` + `[vbc_p]`) bên trong Flex container.
  - **Cấu trúc phân cấp không trùng loại thẻ (Zero Same-type Nesting)**: Tuân thủ nghiêm ngặt thứ tự `[vbc_div]` (Section) $\to$ `[vbc_container]` (Container max-width) $\to$ `[vbc_box]` (Grid/Flex) $\to$ `[vbc_block]` (Item) $\to$ Self-closing elements / `[contact-form-7]`. Không lồng thẻ cùng loại vào nhau để đảm bảo **0 unparsed shortcodes**.

### Bước 5: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/clone-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/compiled_vbc.txt" [--post_id <POST_ID>]
```

### Bước 6: Đối Soát Chất Lượng & Thị Giác (Quality Audit)
Chạy script rechecker:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>" --source_url "<SOURCE_URL>"
```
Đảm bảo kết quả đạt:
- **0 Unparsed Shortcodes**.
- **VSI $\ge 90\%$**.
- **Biểu mẫu Contact Form 7 hoạt động**.
- Tương thích 100% với trình kéo thả Flatsome UX Builder.
