# Create Landing Page Skill (`create-landingpage.py`)

## 1. Giới Thiệu
Skill **`create-landingpage.py`** dùng để khởi tạo nhanh các trang Landing Page hiện đại, chuẩn SEO và tối ưu tỷ lệ chuyển đổi cao (High-converting UI/UX) dựa trên nội dung yêu cầu, file HTML có sẵn hoặc cấu hình JSON Modular Sections.

---

## 2. Các Tính Năng Chính

1. **Khởi Tạo Linh Hoạt**:
   - Hỗ trợ tạo trang từ file HTML nội dung có sẵn (`--file path/to/content.html`).
   - Hỗ trợ tạo trang từ chuỗi cấu hình JSON (`--spec '{"hero_title": "...", "features": [...]}'`).
   - Tự động sinh giao diện mặc định chuẩn cao cấp nếu chỉ truyền tiêu đề `--title`.
2. **Tự Động Nén CSS Chống `wpautop`**:
   - Minify 100% các khối CSS `<style>` để bảo toàn stylesheet trên WordPress.
3. **Template Độc Lập**:
   - Tự động áp dụng template `page-blank.php` loại bỏ các widget thừa của theme Flatsome.
4. **Tích Hợp Tự Động QA Recheck**:
   - Sau khi xuất bản, tự động gọi `recheck-url.py` để kiểm tra độ hoàn thiện của trang.

---

## 3. Hướng Dẫn Sử Dụng CLI

```bash
# 1. Tạo trang từ file HTML soạn sẵn
python skills/create-landingpage.py --title "Dịch Vụ Cho Thuê Xe Du Lịch 2026" --file my_landing_page.html --slug "cho-thue-xe-du-lich-2026"

# 2. Tạo trang từ cấu hình JSON spec
python skills/create-landingpage.py --title "Vận Chuyển Hàng Hóa Bắc Nam" --spec '{"hero_title": "DỊCH VỤ VẬN TẢI HÀNG HÓA SIÊU TỐC", "phone": "0968866855"}'

# 3. Cập nhật đè vào Post ID có sẵn
python skills/create-landingpage.py --title "Bánh Trung Thu Cao Cấp" --file content.html --post_id 350
```

---

## 4. Bảng Tham Số CLI

| Tham Số | Bắt Buộc | Mặc Định | Ý Nghĩa |
| :--- | :---: | :---: | :--- |
| `--title` | Có | - | Tiêu đề trang WordPress |
| `--file` | Không | `None` | Đường dẫn file HTML nội dung cần xuất bản |
| `--spec` | Không | `None` | Chuỗi JSON cấu hình các section |
| `--slug` | Không | Tự động | Đường dẫn slug của trang |
| `--post_id` | Không | `None` | Post ID cần cập nhật ghi đè (nếu có) |
| `--template` | Không | `page-blank.php` | Template trang sử dụng |
| `--no_recheck` | Không | `False` | Tắt tự động chạy recheck |
