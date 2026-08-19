# Clone Landing Page Skill (`clone-landingpage.py`)

## 1. Giới Thiệu
Skill **`clone-landingpage.py`** dùng để clone bất kỳ trang web nào về WordPress với độ tương đồng về hình ảnh, giao diện, nội dung, layout và icon từ **90% đến 100%**.

---

## 2. Các Tính Năng Đột Phá

1. **Auto Asset Crawling & Batch Upload**:
   - Tự động trích xuất toàn bộ ảnh, banner, vector icon, SVG từ URL gốc.
   - Tự động tải về và upload lên WordPress Media Library qua REST API (`/vbc/v1/upload`).
2. **Auto Media Mapping & Thumbnail Optimization**:
   - Tự động thay thế toàn bộ URL ảnh gốc thành URL media nội bộ trên website đích.
   - Hỗ trợ các kích thước responsive (768px, 1024px) giúp tốc độ load trang đạt dưới 1 giây.
3. **Chống Lỗi `wpautop` & Minify CSS**:
   - Tự động nén toàn bộ khối `<style>` thành một dòng liên tục, loại bỏ hoàn toàn hiện tượng WordPress tự chèn `<p>` và `<br>` làm hỏng stylesheet.
4. **Tương Thích Mọi Giao Diện (Theme Independent)**:
   - Sử dụng template `page-blank.php` giúp loại bỏ toàn bộ widget/sidebar mặc định của Flatsome, mang lại giao diện tinh gọn chuẩn 100% theo mẫu gốc.
5. **Tự Động Kích Hoạt `recheck-url.py`**:
   - Sau khi xuất bản, tự động kiểm tra mã nguồn rendered để đảm bảo không còn raw shortcode và ảnh hiển thị trọn vẹn.

---

## 3. Hướng Dẫn Sử Dụng CLI

```bash
# Clone một trang web bất kỳ
python skills/clone-landingpage.py --url https://hoanglonghaivanexpress.com/ --title "XE KHÁCH BẮC NAM & CHO THUÊ XE DU LỊCH" --slug "xe-khach-bac-nam"

# Clone và cập nhật vào Post ID cụ thể
python skills/clone-landingpage.py --url https://hoanglonghaivanexpress.com/ --post_id 479 --title "XE KHÁCH BẮC NAM & CHO THUÊ XE DU LỊCH"
```

---

## 4. Bảng Tham Số CLI

| Tham Số | Bắt Buộc | Mặc Định | Ý Nghĩa |
| :--- | :---: | :---: | :--- |
| `--url` | Có | - | URL trang web gốc cần clone |
| `--title` | Không | `Landing Page Clone` | Tiêu đề trang trên WordPress |
| `--slug` | Không | Tự động | Đường dẫn slug của trang |
| `--post_id` | Không | `None` | Post ID cần cập nhật ghi đè (nếu có) |
| `--template` | Không | `page-blank.php` | Page template sử dụng |
| `--max_images` | Không | `40` | Số lượng ảnh tối đa tải lên Media Library |
| `--no_recheck` | Không | `False` | Tắt tự động gọi recheck sau khi clone |
