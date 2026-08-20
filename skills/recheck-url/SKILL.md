---
name: recheck-url
description: >-
  Kiểm tra chất lượng (QA Audit), đo lường độ tương đồng thị giác bằng AI (Visual Similarity Index) và đối soát mã nguồn của trang web sau khi clone. Sử dụng khi người dùng yêu cầu recheck, kiểm tra độ giống nhau giữa 2 trang web, đối soát shortcode hoặc nghiệm thu giao diện.
---

# AI Visual Quality Assurance & Recheck Engine

## Mục tiêu (Goal)
Chụp ảnh 1-shot toàn trang (`CaptureBeyondViewport: true`), tính toán chỉ số tương đồng thị giác AI (Visual Similarity Index - VSI) dựa trên biểu đồ phân bố màu sắc & ma trận bố cục không gian, đồng thời kiểm tra tự động 4 tiêu chí chất lượng nghiêm ngặt (VSI $\ge 90\%$, 0 raw shortcodes, H1/CF7 đầy đủ, Media rendered).

## Hướng dẫn Quy trình (Workflow)

1. **Thu thập Hình ảnh Toàn Trang (1-shot Full Page Capture)**:
   - Chụp ảnh full màn hình trang nguồn và trang đích qua trình duyệt với tham số `CaptureBeyondViewport: true`.

2. **So sánh Thị giác AI (AI Visual Comparison)**:
   - Chuẩn hóa kích thước 2 ảnh về cùng độ phân giải.
   - Trích xuất biểu đồ phân bố màu 8-bin và phân tích độ tương đồng không gian (Spatial Block Matrix).
   - Xuất ra điểm số `Visual Similarity Index (VSI)` từ $0\%$ đến $100\%$.

3. **Kiểm tra Mã Nguồn Rendered (DOM Source Audit)**:
   - Quét mã HTML đã render để phát hiện bất kỳ shortcode thô chưa parse (`[vbc_...`, `[contact-form-7...`).
   - Kiểm tra sự tồn tại của thẻ `<h1...>` phục vụ SEO và biểu mẫu Contact Form 7.
   - Kiểm tra các ảnh rỗng hoặc broken image links.

4. **Tự động Sửa Lỗi (Auto-Remediation)**:
   - Nếu VSI $< 90\%$ hoặc phát hiện lỗi cú pháp, script sẽ tự động kích hoạt tối đa 3 vòng lặp để cập nhật và biên dịch lại nội dung.

## Thực thi Tập lệnh (Scripts)
Chạy script đối soát:
```bash
python .agents/skills/recheck-url/scripts/rechecker.py --url "<TARGET_URL>" --source_url "<SOURCE_URL>" --source_img "<SOURCE_IMG_PATH>" --target_img "<TARGET_IMG_PATH>" [--threshold 90.0]
```

## Tài liệu Tham khảo (References)
- [Bộ 4 Tiêu chí Nghiệm thu Chất lượng](./references/audit-criteria.md)

## Ví dụ (Examples)
- [Mẫu Báo cáo Đối soát Thị giác AI](./examples/sample-audit-report.md)

## Tiêu chí Thành công (Acceptance Criteria)
- Điểm tương đồng VSI $\ge 90.0\%$.
- 0 raw/corrupted shortcodes.
- Hình ảnh hiển thị đầy đủ.
