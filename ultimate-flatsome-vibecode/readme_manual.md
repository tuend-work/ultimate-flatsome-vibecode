Thư mục này chứa code của plugin Flatsome VibeCode tôi bán cho khách.
Chứa các element VBC_xxxx để sửa web bằng UXBuilder của flatsome. 
- có trang chức năng để nhập trang từ công cụ SAAS (Trang của tôi chứa plugin SAAS).

- Có thêm chức năng xuất ra 1 file zip project dùng cho antigravity. Gồm thư mục skill và file vbc-config.json để tạo prompt cho Antigravity clone trang về Flatsome UX Builder. 
File vbc-config.json chứa các thông tin cơ bản của trang web như: Tiêu đề trang, mô tả trang, thông tin liên hệ, danh sách sản phẩm, danh sách dịch vụ, danh sách bài viết,... Có thêm thông tin ftp của trang để Antigravity có thể upload file tuỳ chỉnh lên hosting của khách hàng.

-  Antigravity sử dụng skill và các thông tin có trong vbc-config.json để  tạo trang cho hợp ngữ cảnh. 
+ Skill sẽ chứa các skill clone landing page hoặc create landing page.
+ File vbc-config.json sẽ giúp antigravity hiểu được nội dung của trang web để đưa vào trang web landing page mới được tạo (Nếu người dùng không chỉ định khi prompt). 