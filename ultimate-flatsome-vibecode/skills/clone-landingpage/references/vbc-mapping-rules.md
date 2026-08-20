# Quy Chuẩn Ánh Xạ HTML Sang VBC Elements

Bảng quy tắc chuyển đổi thẻ HTML chuẩn sang Shortcodes của Ultimate Flatsome VibeCode Elements:

---

## 1. Bảng Ánh Xạ Phần Tử (Element Mapping Table)

| Thẻ HTML Gốc | Phần tử VBC Elements | Cú pháp mẫu |
|---|---|---|
| `<div class="wrapper">` | `[vbc_div]` | `[vbc_div custom_css="selector { width: 100%; background: #ffffff; }"]...[/vbc_div]` |
| `<div class="container">` | `[vbc_box]` | `[vbc_box class="container" custom_css="selector { max-width: 1200px; margin: 0 auto; }"]...[/vbc_box]` |
| `<div class="row">` | `[vbc_block]` | `[vbc_block custom_css="selector { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }"]...[/vbc_block]` |
| `<div class="col">` | `[vbc_container]` | `[vbc_container custom_css="selector { padding: 20px; }"]...[/vbc_container]` |
| `<h1>` - `<h6>` | `[vbc_h1]` - `[vbc_h6]` | `[vbc_h2 custom_css="selector { font-size: 32px; font-weight: 800; }"]Tiêu đề[/vbc_h2]` |
| `<p>` | `[vbc_p]` | `[vbc_p custom_css="selector { font-size: 16px; line-height: 1.7; }"]Nội dung[/vbc_p]` |
| `<a>` | `[vbc_a]` | `[vbc_a link_url="#dang-ky" custom_css="selector { background: #f0493e; color: #fff; }"]...[/vbc_a]` |
| `<span>` | `[vbc_span]` | `[vbc_span custom_css="selector { font-weight: 700; }"]Chữ đậm[/vbc_span]` |
| `<i>` / `<svg>` | `[vbc_icon]` | `[vbc_icon icon_type="lucide" name="check-circle" size="20px" color="#f0493e"]` |
| `<form>` | `[contact-form-7]` | `[contact-form-7 id="508" title="Form Tư Vấn"]` |

---

## 2. Quy Tắc Viết CSS Trong VBC

- Luôn dùng từ khóa `selector` làm gốc định kiểu.
- Viết Responsive media queries lồng trực tiếp trong thuộc tính `custom_css`:
  ```css
  selector {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
  }
  @media(max-width: 849px) {
      selector {
          grid-template-columns: 1fr;
      }
  }
  ```
