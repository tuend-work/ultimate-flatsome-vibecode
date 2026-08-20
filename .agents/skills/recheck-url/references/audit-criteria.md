# Bộ 4 Tiêu Chí Nghiệm Thu Chất Lượng (Quality Assurance Criteria)

Mọi trang Landing Page sau khi clone bắt buộc phải vượt qua 4 tiêu chí sau:

---

## 1. Bảng Tiêu Chí Nghiệm Thu

| # | Tiêu Chí Kiểm Định | Ngưỡng Đạt (Threshold) | Cách Kiểm Tra |
|---|---|:---:|---|
| **1** | **Độ tương đồng thị giác (VSI)** | $\ge 90.0\%$ | So sánh histogram 8-bin và ma trận không gian giữa ảnh web nguồn & web đích |
| **2** | **Mã Shortcodes chưa parse** | **0 tags** | Quét chuỗi `[vbc_`, `[contact-form-7` trong rendered HTML |
| **3** | **Hình ảnh rendered đầy đủ** | $\ge 6$ ảnh, 0 ảnh rỗng | Quét danh sách thẻ `<img>` hợp lệ, không chứa ảnh trắng/broken |
| **4** | **Thẻ H1 & Form Contact Form 7** | **Bắt buộc có** | Kiểm tra thẻ `<h1>` phục vụ SEO và Form CF7 hoạt động |

---

## 2. Quy Trình Xử Lý Khi Không Đạt

- **Nếu VSI $< 90\%$**: Kiểm tra sự chênh lệch màu nền hoặc thiếu section, cập nhật lại CSS và cấu trúc shortcode.
- **Nếu phát hiện Raw Shortcode**: Kiểm tra đóng/mở tag `[/vbc_...]` và đăng ký shortcode trong PHP plugin.
