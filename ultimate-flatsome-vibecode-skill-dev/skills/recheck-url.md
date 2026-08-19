# Recheck URL Skill (`recheck-url.py`)

## 1. Giới Thiệu
Skill **`recheck-url.py`** là công cụ kiểm tra tự động và đảm bảo chất lượng (QA) cho Landing Page sau khi khởi tạo bằng `clone-landingpage.py` hoặc `create-landingpage.py`.

Skill đảm bảo trang xuất bản đạt **90% đến 100%** độ hoàn thiện và không mắc các lỗi phổ biến trên WordPress / Flatsome.

---

## 2. Tiêu Chí Kiểm Tra (QA Checklist)

1. **Shortcodes Chưa Parse (0 lỗi)**:
   - Quét toàn bộ HTML rendered để phát hiện các shortcode bị lộ ra ngoài giao diện (ví dụ `[vbc_div]`, `[vbc_card]`, `[accordion]`, `[row]`, `[col]`).
2. **Khối CSS `<style>` Không Bị `wpautop` Phá Vỡ**:
   - Xác minh WordPress không chèn thẻ `<p>` hay `<br>` vào bên trong stylesheet CSS.
3. **Hình Ảnh & Icon Toàn Vẹn**:
   - Kiểm tra `0` ảnh có `src=""` rỗng, `null` hoặc `undefined`.
   - Kiểm tra HTTP status 200 của các file ảnh trên WordPress Media.
4. **Cấu Trúc HTML & Kêu Gọi Hành Động (CTA)**:
   - Có thẻ `<h1>` rõ ràng, nút Hotline, nút Zalo, Form đặt xe/báo giá.
5. **Điểm Chất Lượng & Vòng Lặp Recheck**:
   - Tự động đánh giá theo thang điểm 100%. Tự động chạy lại (recheck loop) nếu phát hiện lỗi hoặc trang chưa đồng bộ xong.

---

## 3. Hướng Dẫn Sử Dụng CLI

```bash
# Kiểm tra nhanh một URL bất kỳ
python skills/recheck-url.py --url https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/

# Kiểm tra với Post ID và số lần thử lại tối đa 5 lần
python skills/recheck-url.py --url https://ultimateflatsomevibecode.s172d211.wpcloud.vn/xe-khach-bac-nam-cho-thue-xe-du-lich/ --post_id 479 --max_retries 5
```

---

## 4. Tham Số CLI

| Tham Số | Bắt Buộc | Mặc Định | Ý Nghĩa |
| :--- | :---: | :---: | :--- |
| `--url` | Có | - | URL trang web thực tế cần kiểm tra |
| `--post_id` | Không | `None` | ID bài viết / trang trên WordPress |
| `--source_url` | Không | `None` | URL trang web gốc để so sánh đối chiếu |
| `--max_retries` | Không | `3` | Số lần thử lại tối đa khi kiểm tra |
| `--screenshot` | Không | `recheck_fullpage_<timestamp>.png` | Tên file lưu ảnh chụp màn hình |
