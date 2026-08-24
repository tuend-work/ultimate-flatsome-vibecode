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

### Bước 2: AI Thiết Kế & Sinh 100% Native VBC Elements
AI trực tiếp viết mã nguồn VBC Elements lưu tại `tmp/<slug>/created_vbc.txt`.
- Sử dụng đầy đủ các thẻ: `[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_img]`, `[vbc_icon]`, `[vbc_card]`, `[vbc_tabs]`, `[vbc_accordion]`, `[contact-form-7]`, `[vbc_post]`.
- Gắn trực tiếp thuộc tính: `bg_color="..."`, `color="..."`, `font_size="..."`, `font_weight="..."`, `padding="..."`, `margin="..."`, `border_radius="..."`, `box_shadow="..."`, `display="flex|grid"`, `gap="..."`, `grid_columns="..."`, `text_align="..."`.
- Cấu trúc phân cấp lồng nhau chuẩn: dùng `_inner_1`, `_inner_2` để đảm bảo **0 lỗi shortcode**.

### Bước 3: Xuất bản Lên WordPress Qua REST API
Chạy script xuất bản trang:
```bash
python .agents/skills/create-landingpage/scripts/publisher.py --title "<TIEU_DE>" --slug "<SLUG>" --content "tmp/<slug>/created_vbc.txt" [--post_id <POST_ID>]
```

### Bước 4: Kiểm Định & Bàn Giao (Audit & Handover)
Kiểm tra trang web trên trình duyệt và cung cấp link chỉnh sửa trực tiếp trên Flatsome UX Builder cho người dùng.
