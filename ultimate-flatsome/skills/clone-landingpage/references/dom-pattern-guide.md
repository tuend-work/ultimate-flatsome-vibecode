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
| **Accordion / Collapsible List (01, 02, 03... / FAQ)** | Danh sách các dòng đánh số `01`, `02`, `03`... hoặc câu hỏi FAQ, có ký hiệu toggle `+`/`-` hoặc mũi tên, ngăn cách bởi đường kẻ ngang mỏng `border-bottom`. Một hàng mở rộng chứa ảnh + checklist và các hàng khác thu gọn. | Khối container pastel `[vbc_box]`, phân tầng các hàng `[vbc_box_inner_X]` với đường viền mỏng `border="0 0 1px solid #..."`, số thứ tự lớn màu thương hiệu `01`, tiêu đề và icon `+`/`—`, hoặc `[vbc_accordion]` / `[accordion]`. |
| **Blog / Tin Tức / Kiến Thức (Dynamic Posts)** | Khối hiển thị 3-4 bài viết tin tức có ảnh thumbnail, tiêu đề bài viết, tóm tắt, ngày đăng, nút *"Đọc bài viết"* / *"Xem thêm"*. | Sử dụng phần tử động `[vbc_post post_type="post" posts_per_page="3" columns="3" columns__sm="1" fields="thumbnail:100%, title:100%, excerpt:100%, button:100%"]` để truy vấn bài viết thực tế từ cơ sở dữ liệu WordPress, tự động liên kết permalink. |
| **Sản Phẩm / Khóa Học / Bảng Giá (Dynamic Products)** | Danh sách thẻ sản phẩm / khóa học có ảnh, danh mục, giá tiền (`_price`), giảm giá, nút mua hàng / đăng ký. | Sử dụng `[vbc_post post_type="product" posts_per_page="4" columns="4" columns__sm="1" fields="thumbnail:100%, title:100%, price:50%, button:50%"]` để lấy dữ liệu động từ WooCommerce hoặc CPT. |
| **Cam kết / Box Nổi bật** | Khung nền màu cam/be viền bo tròn ở giữa trang. | `[vbc_box]` centered, padding 30px, border-radius 20px |
| **Form Tư vấn** | Khối chứa form nhập liệu `<form>`, input họ tên, sđt, email hoặc nút đăng ký. | `[vbc_div id="dang-ky"]` chứa form `[contact-form-7]` |
| **Footer** | Thẻ `<footer>`, class `footer`, vị trí cuối trang. | `[vbc_div]` nền tối, 3-4 cột thông tin liên hệ và copyright |
