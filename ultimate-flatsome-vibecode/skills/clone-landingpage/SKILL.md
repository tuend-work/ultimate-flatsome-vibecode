---
name: clone-landingpage
description: >-
  Tự động sao chép (clone) toàn bộ giao diện và nội dung từ một trang web bất kỳ sang WordPress Flatsome bằng 100% phần tử Ultimate Flatsome VibeCode Elements. Sử dụng khi người dùng yêu cầu clone/sao chép landing page, bóc tách layout từ URL gốc, hoặc chuyển đổi giao diện sang VBC.
---

# Clone Landing Page (Universal Generic Engine)

## Mục tiêu (Goal)
Tự động bóc tách cấu trúc DOM, phân tích ngữ cảnh từng Section, tải và đồng bộ hóa toàn bộ hình ảnh lên thư viện WordPress Media Library, chuyển đổi form sang Contact Form 7 và biên dịch thành shortcodes VBC Elements thuần với độ tương đồng thị giác (VSI) $\ge 90\%$.

## Hướng dẫn Quy trình (Workflow)

1. **Phân tích Cây DOM (Semantic DOM Tree Parsing)**:
   - Quét cấu trúc trang web nguồn từ URL.
   - Trích xuất bảng màu chủ đạo (Brand Primary/Dark/Accent Palette).
   - Phân loại các Section theo ngữ cảnh: Hero Banner, Highlights Grid, Teachers/Testimonial Cards, 2-Col Split, FAQ Accordions, CF7 Form, Footer.

2. **Quét & Tải Media**:
   - Tự động phát hiện toàn bộ ảnh trong các thẻ `<img>`, background CSS `url(...)`.
   - Tải về thư mục cục bộ `tmp/<slug>/`.

3. **Đồng bộ WordPress Media Library**:
   - Tải ảnh lên WordPress qua REST API `/vbc/v1/upload`.
   - Lưu trữ ánh xạ URL gốc $\to$ URL nội bộ WordPress trong `tmp/<slug>/media_map.json`.

4. **Biên dịch Shortcode VBC Elements**:
   - Sinh shortcodes thuần `[vbc_div]`, `[vbc_box]`, `[vbc_block]`, `[vbc_container]`, `[vbc_h1]-[vbc_h6]`, `[vbc_p]`, `[vbc_a]`, `[vbc_icon]`, `[contact-form-7]`.
   - Áp dụng CSS Responsive hoàn chỉnh, không để sót bất kỳ unparsed tags nào.

5. **Xuất bản & Tự động Đối soát**:
   - Xuất bản lên WordPress qua REST API `/vbc/v1/page`.
   - Tự động kích hoạt skill `recheck-url` để chụp ảnh 1-shot và đối soát thị giác bằng AI.

## Thực thi Tập lệnh (Scripts)
Chạy script thực thi chính:
```bash
python .agents/skills/clone-landingpage/scripts/cloner.py --url "<URL_NGUON>" --title "<TIEU_DE>" --slug "<SLUG>" [--post_id <POST_ID>]
```

## Tài liệu Tham khảo (References)
- [Quy tắc ánh xạ DOM & Section Patterns](./references/dom-pattern-guide.md)
- [Quy chuẩn Shortcodes VBC Elements](./references/vbc-mapping-rules.md)

## Ví dụ (Examples)
- [Mẫu đầu ra VBC chuẩn](./examples/sample-landing-page.vbc)

## Ràng buộc & Tiêu chuẩn Chất lượng (Constraints)
- **100% Generic**: Tuyệt đối không hardcode nội dung hoặc cấu trúc cố định của một website cụ thể vào script lõi.
- **Visual Similarity Index (VSI)**: Phải đạt $\ge 90\%$ trước khi bàn giao cho người dùng.
- **0 Unparsed Tags**: Không để lộ mã shortcode thô ra frontend.
