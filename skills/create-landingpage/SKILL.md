---
name: create-landingpage
description: >-
  Thiết kế và xây dựng Landing Page mới chuyên nghiệp trên WordPress Flatsome từ ý tưởng, bản thảo hoặc yêu cầu của người dùng bằng 100% phần tử Ultimate Flatsome VibeCode Elements do AI trực tiếp thiết kế. Sử dụng khi người dùng yêu cầu tạo mới landing page, thiết kế trang bán hàng, giới thiệu dịch vụ.
---

# Create Landing Page (AI-First LLM Architecture)

## Mục tiêu (Goal)
Sử dụng **Trí tuệ Nhân tạo (LLM)** để thiết kế toàn diện một Landing Page đạt chuẩn UI/UX quốc tế, tỉ lệ chuyển đổi (CRO) cao, tương thích 100% với Flatsome UX Builder bằng hệ thống **Ultimate Flatsome VibeCode Elements** với thuộc tính styling đưa trực tiếp vào từng thẻ.

---

## Quy trình Thực hiện (Workflow)

### Bước 1: Phân tích Ý tưởng & Bảng Màu Thương Hiệu (Design Concept)
AI xác định:
1. **Chủ đề & Mục tiêu trang**: Bán hàng (Sales Page), Thu thập khách hàng tiềm năng (Lead Gen), Giới thiệu công ty (Corporate Landing Page).
2. **Bảng màu chủ đạo**: Primary Color, Secondary Color, Accent/CTA Color, Neutral Dark/Light Background.
3. **Cấu trúc Bố cục (Layout Sections)**:
   - Header Navigation & CTA
   - Hero Section cuốn hút kèm Form hoặc Button hành động
   - Problem / Solution & Điểm nổi bật (Highlights Grid)
   - Chi tiết Khóa học / Dịch vụ / Sản phẩm (Cards / Tabs)
   - Đội ngũ chuyên gia / Quy trình làm việc (Steps / Workflow)
   - Bằng chứng xã hội / Đánh giá khách hàng (Testimonials)
   - Bảng giá / Ưu đãi có thời hạn (Pricing Table)
   - Câu hỏi thường gặp (FAQ Accordion)
   - Form Đăng ký tư vấn cuối trang (Contact Form 7)
   - Footer thông tin thương hiệu & bản quyền

### Bước 2: Tự động Tạo Biểu Mẫu Contact Form 7 (BẮT BUỘC)
Khi thiết kế landing page có khu vực thu thập thông tin khách hàng (Hero Form, Lead Gen CTA, Form Tư Vấn, Form Đăng Ký):
1. **Xác định các trường nhập liệu cần thiết**: Họ tên, Số điện thoại, Email, Dịch vụ/Khóa học quan tâm, Lời nhắn.
2. **Chạy script sinh Form CF7 qua REST API**:
   ```bash
   python .agents/skills/create-landingpage/scripts/create_cf7.py --title "Form Tư Vấn - <Tên Landing Page>" --fields "name,phone,email,course,message" --button "Đăng ký nhận ưu đãi ngay"
   ```
3. **Lấy mã Shortcode trả về** dạng `[contact-form-7 id="<ID>" title="..."]` để nhúng vào layout VBC.
4. **Quy tắc**: 100% biểu mẫu thu thập thông tin khách hàng **BẮT BUỘC** phải được tạo thành form Contact Form 7 thực tế qua API, **TUYỆT ĐỐI KHÔNG** dùng văn bản giả lập tĩnh (`[vbc_p]`) thay cho form.

### Bước 3: AI Thiết Kế & Sinh 100% Native VBC Elements
AI trực tiếp viết mã nguồn VBC Elements lưu tại `tmp/<slug>/created_vbc.txt`.
- Sử dụng đầy đủ các thẻ: `[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_card]`, `[vbc_tabs]`, `[vbc_accordion]`, `[contact-form-7]`, `[vbc_post]`.
- Gắn trực tiếp thuộc tính: `bg_color="..."`, `color="..."`, `font_size="..."`, `font_weight="..."`, `padding="..."`, `margin="..."`, `border_radius="..."`, `box_shadow="..."`, `display="flex|grid"`, `gap="..."`, `grid_columns="..."`, `text_align="..."`.
- **Tích hợp Form CF7**: Nhúng mã `[contact-form-7 id="..." title="..."]` vào trong khối container `[vbc_container]` / `[vbc_box]` đã được style.
- **Truyền nội dung qua input (`text="..."` / `content="..."`)**: Tuyệt đối không lồng thẻ thô hoặc `<img>` vào giữa cặp thẻ `[vbc_p]...[/vbc_p]` hay `[vbc_h1]-[vbc_h6]`, vì WordPress `wpautop` sẽ tự động chèn thẻ `<p>` rác làm vỡ layout. Thay vào đó, hãy truyền nội dung vào input `text="..."` hoặc tách thành các phần tử nguyên tử (`[vbc_img]` + `[vbc_p]`) bên trong Flex container.
- **Cấu trúc phân cấp không trùng loại thẻ (Zero Same-type Nesting)**: Tuân thủ nghiêm ngặt thứ tự `[vbc_div]` (Section) $\to$ `[vbc_container]` (Container max-width) $\to$ `[vbc_box]` (Grid/Flex) $\to$ `[vbc_block]` (Item) $\to$ Self-closing elements / `[contact-form-7]`. Không lồng thẻ cùng loại vào nhau để đảm bảo **0 unparsed shortcodes**.

### Bước 4: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/create-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/created_vbc.txt" [--post_id <POST_ID>]
```

### Bước 5: Kiểm Định & Bàn Giao (Audit & Handover)
Chạy rechecker và kiểm tra trang web trên trình duyệt:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>"
```
Cung cấp link live và link chỉnh sửa trực tiếp trên Flatsome UX Builder cho người dùng.
