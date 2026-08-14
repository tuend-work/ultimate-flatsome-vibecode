- Thư mục này chứa code bổ sung cho plugin Ultimate Flatsome VibeCode. Dùng để bổ sung thêm Endpoint POST /wp-json/vbc/v1/auto-clone theo mô hình AI-Powered Backend (tích hợp trực tiếp Gemini API Key vào cài đặt plugin) để đạt chất lượng 99%. Plugin này private tôi dùng riêng cho dịch vụ SAAS của tôi.
- Phải cài plugin ultimate-flatsome-vibecode để hoạt động
- Tôi sẽ dùng plugin này để tạo  dịch vụ SAAS "Dịch vụ clone trang landingpage bất kỳ về flatsome UX Builder". khách hàng nhập url hoặc ảnh vào form và bấm Clone page. Plugin này sẽ thực hiện tạo trang về Flatsome UX Builder. Nếu khách hàng thấy thích thì bấm tải về. Tôi cũng sẽ xây dựng 1 trang admin để quản lý các mẫu đã tạo và hiển thị danh sách đó lên trang chủ. Để khách hàng có thể chọn mẫu từ danh sách đó tải về. Khi bấm tải về thì nó sẽ xuất ra 1 file zip gồm:
+ file json chứa content html shortcode
+ file vbc code.
+ file ảnh.

Bên plugin Ultimate Flatsome VibeCode Server sẽ có 1 form nhập trang từ công cụ SAAS. Tức là khi khách hàng vào trang của họ và bấm vào nút "Nhập trang từ SAAS" thì nó sẽ gửi yêu cầu tới plugin này để lấy nội dung trang.

