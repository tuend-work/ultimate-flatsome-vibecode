# Thư Viện Mẫu Bố Cục (Section Templates)

Thư viện mẫu các khối giao diện chuẩn sử dụng VBC Elements:

---

## 1. Hero Section (2 Cột Tiêu Chuẩn)
```html
[vbc_div custom_css="selector { width: 100%; background: #fdf6eb; padding: 70px 0; }"]
    [vbc_box class="container" custom_css="selector { margin: 0 auto; max-width: 1200px; padding: 0 20px; }"]
        [vbc_block custom_css="selector { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center; } @media(max-width: 849px){ selector { grid-template-columns: 1fr; text-align: center; } }"]
            <div>
                [vbc_h1 custom_css="selector { font-size: 42px; font-weight: 900; color: #222f3e; margin: 0 0 16px 0; }"]Giải Pháp Đột Phá Cho Doanh Nghiệp[/vbc_h1]
                [vbc_p custom_css="selector { font-size: 16px; color: #576574; line-height: 1.7; margin-bottom: 24px; }"]Tối ưu hóa hiệu suất và nâng cao năng lực cạnh tranh với giải pháp công nghệ tiên tiến.[/vbc_p]
                [vbc_a link_url="#dang-ky" custom_css="selector { background: #f0493e; color: #ffffff !important; padding: 14px 32px; border-radius: 30px; font-weight: 700; text-decoration: none; }"]
                    [vbc_span]Khám Phá Ngay[/vbc_span]
                [/vbc_a]
            </div>
            <div style="text-align: center;">
                <img src="https://via.placeholder.com/500x400" alt="Hero" style="width: 100%; border-radius: 20px;" />
            </div>
        [/vbc_block]
    [/vbc_box]
[/vbc_div]
```

## 2. 3-Card Grid Section
```html
[vbc_div custom_css="selector { width: 100%; background: #ffffff; padding: 80px 0; }"]
    [vbc_box class="container" custom_css="selector { margin: 0 auto; max-width: 1200px; padding: 0 20px; }"]
        [vbc_block_inner custom_css="selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; } @media(max-width: 849px){ selector { grid-template-columns: 1fr; } }"]
            <!-- Card items -->
        [/vbc_block_inner]
    [/vbc_box]
[/vbc_div]
```
