# Hướng Dẫn Nhận Diện DOM Patterns & Section Classification

Tài liệu hướng dẫn cách bóc tách cây DOM và phân loại các khối giao diện (Section Patterns) khi thực hiện clone landing page.

---

## 1. Các Mẫu Section Điển Hình (Common Section Patterns)

| Loại Section | Dấu hiệu Nhận diện DOM | Cấu trúc VBC Elements tương ứng |
|---|---|---|
| **Header / Navigation** | Thẻ `<header>`, class chứa `header`, `site-header`, vị trí `index == 1`. Chứa logo và `<nav>`. | `[vbc_div]` sticky top, `[vbc_box]` max-width 1200px, flexbox layout |
| **Hero Banner** | Thẻ `section` đầu trang chứa ảnh khổ lớn (`cover`, `banner`), thẻ `<h1>` hoặc tiêu đề chính. | `[vbc_div]` background màu nhẹ, `[vbc_box]` chứa ảnh banner full-width hoặc 2 cột text + ảnh |
| **Highlights / Features Grid** | Danh sách 3-5 icon kèm tiêu đề ngắn, nền tròn/badge. | `[vbc_block_inner]` grid layout 4-5 cột, `[vbc_icon]` badge |
| **Teacher / Testimonial Cards** | Danh sách thẻ chứa avatar tròn/vuông, tên, đánh giá sao (`★★★★★`), thông tin bio. | `[vbc_block_inner]` CSS grid 3 cột, thẻ card nền trắng có shadow |
| **Split 2-Col (Media + Tabs / Checklist)** | 1 ảnh lớn/minh họa 1 bên, bên còn lại là danh sách tab hoặc checklist có icon tick. | `[vbc_block]` grid 2 cột (1.1fr 0.9fr hoặc 0.9fr 1.1fr) |
| **Cam kết / Box Nổi bật** | Khung nền màu cam/be viền bo tròn ở giữa trang. | `[vbc_box]` centered, padding 30px, border-radius 20px |
| **Form Tư vấn** | Khối chứa form nhập liệu `<form>`, input họ tên, sđt, email hoặc nút đăng ký. | `[vbc_div id="dang-ky"]` chứa form `[contact-form-7]` |
| **Footer** | Thẻ `<footer>`, class `footer`, vị trí cuối trang. | `[vbc_div]` nền tối, 3-4 cột thông tin liên hệ và copyright |
