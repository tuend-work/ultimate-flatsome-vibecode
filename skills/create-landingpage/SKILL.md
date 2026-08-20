---
name: create-landingpage
description: >-
  Thiết kế và xây dựng Landing Page mới chuyên nghiệp trên WordPress Flatsome từ ý tưởng, bản thảo hoặc yêu cầu của người dùng bằng 100% phần tử Ultimate Flatsome VibeCode Elements. Sử dụng khi người dùng yêu cầu tạo mới landing page, thiết kế trang bán hàng, giới thiệu dịch vụ.
---

# Create Landing Page Engine

## Mục tiêu (Goal)
Tạo trang Landing Page mới chuẩn UI/UX, hiện đại, giàu tính thẩm mỹ (Aesthetics) và tối ưu hóa chuyển đổi bằng hệ thống Shortcodes VBC Elements thuần túy.

## Hướng dẫn Quy trình (Workflow)

1. **Thu thập Yêu cầu & Xác định Bảng màu**:
   - Xác định ngành nghề (Giáo dục, Bất động sản, Nha khoa, Spa, Thương mại...).
   - Lựa chọn bảng màu chủ đạo (Primary, Dark, Background Light, Accent).

2. **Dựng Khung Layout Chuẩn 8 Section**:
   - Section 1: Header / Sticky Navbar & Hotline Call-to-action.
   - Section 2: Hero Section (Headline H1 lôi cuốn + CTA + Media).
   - Section 3: Value Proposition / Highlights Icons (3 - 5 điểm nổi bật).
   - Section 4: Showcase / Features / Teacher / Product Cards (Grid 3 - 4 cột).
   - Section 5: Split Media / Accordion / Curriculum (2 cột so le).
   - Section 6: Proof of Work / Testimonials / Trust Badges / Stats.
   - Section 7: Form Đăng ký Tư vấn (Tích hợp Contact Form 7).
   - Section 8: Footer thông tin liên hệ và bản quyền.

3. **Biên dịch & Xuất bản**:
   - Sử dụng script `generator.py` để sinh shortcode và đẩy lên WordPress REST API `/vbc/v1/page`.

## Thực thi Tập lệnh (Scripts)
```bash
python .agents/skills/create-landingpage/scripts/generator.py --title "<TIEU_DE>" --slug "<SLUG>" [--template page-blank.php]
```

## Tài liệu Tham khảo (References)
- [Thư viện Mẫu Section Chuẩn](./references/section-templates.md)

## Ví dụ (Examples)
- [Mẫu Toàn Trang Hoàn Chỉnh](./examples/full-page-template.vbc)
