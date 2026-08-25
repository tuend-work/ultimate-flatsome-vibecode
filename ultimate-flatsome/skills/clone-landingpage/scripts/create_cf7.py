# -*- coding: utf-8 -*-
"""
Ultimate Flatsome VibeCode - Contact Form 7 Generator Script
Tự động tạo hoặc cập nhật biểu mẫu Contact Form 7 trên WordPress qua REST API /vbc/v1/cf7.
Trả về mã shortcode [contact-form-7 id="..." title="..."] để nhúng vào layout VBC.
"""

import sys, os, json, argparse, urllib.request, urllib.error

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

def load_config():
    root_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', '..'))
    candidates = [
        os.path.join(root_dir, 'vbc-config.json'),
        os.path.join(root_dir, '..', 'vbc-config.json'),
        os.path.join(root_dir, '..', '..', 'vbc-config.json'),
        os.path.join(os.getcwd(), 'vbc-config.json')
    ]
    for p in candidates:
        if os.path.exists(p):
            with open(p, 'r', encoding='utf-8') as f:
                return json.load(f)
    raise FileNotFoundError("Không tìm thấy tệp vbc-config.json chứa thông tin cấu hình WordPress.")

def build_default_form_content(fields_str, btn_text="Gửi thông tin ngay"):
    fields = [f.strip().lower() for f in fields_str.split(',') if f.strip()]
    lines = []
    
    for f in fields:
        if f in ['name', 'fullname', 'hoten', 'ten', 'your-name']:
            lines.append('<div class="vbc-form-group"><label>Họ và tên *</label>[text* your-name class:vbc-input placeholder "Nhập họ và tên của bạn..."]</div>')
        elif f in ['phone', 'tel', 'sdt', 'so-dien-thoai', 'your-phone']:
            lines.append('<div class="vbc-form-group"><label>Số điện thoại *</label>[tel* your-phone class:vbc-input placeholder "Nhập số điện thoại..."]</div>')
        elif f in ['email', 'your-email']:
            lines.append('<div class="vbc-form-group"><label>Địa chỉ Email</label>[email your-email class:vbc-input placeholder "example@gmail.com"]</div>')
        elif f in ['course', 'khoahoc', 'dichvu', 'service']:
            lines.append('<div class="vbc-form-group"><label>Khóa học quan tâm</label>[select your-course class:vbc-select "Tiếng Anh Mẫu Giáo (3 - 5 tuổi)" "Tiếng Anh Tiểu Học (6 - 11 tuổi)" "Tiếng Anh THCS & THPT" "Tiếng Anh Giao Tiếp 1 Kèm 1"]</div>')
        elif f in ['message', 'noidung', 'ghichu', 'note', 'your-message']:
            lines.append('<div class="vbc-form-group"><label>Nội dung cần tư vấn</label>[textarea your-message class:vbc-textarea placeholder "Nhập câu hỏi hoặc nhu cầu học của bạn..."]</div>')
        elif f in ['age', 'tuoi', 'dob']:
            lines.append('<div class="vbc-form-group"><label>Độ tuổi của bé</label>[select your-age class:vbc-select "3 tuổi" "4 tuổi" "5 tuổi" "Khác"]</div>')
        else:
            clean_name = f.replace('_', '-').replace(' ', '-')
            lines.append(f'<div class="vbc-form-group"><label>{f.capitalize()}</label>[text {clean_name} class:vbc-input placeholder "Nhập {f}..."]</div>')
            
    lines.append(f'<div class="vbc-form-submit">[submit class:vbc-btn-submit "{btn_text}"]</div>')
    return "\n".join(lines)

def create_cf7_form(title, form_content, form_id=None, mail_recipient=None, mail_subject=None):
    config = load_config()
    api_url = config.get('api-url', '').rstrip('/')
    token = config.get('token', '')
    
    endpoint = f"{api_url}/vbc/v1/cf7"
    
    payload = {
        'title': title,
        'form': form_content,
    }
    if form_id:
        payload['id'] = int(form_id)
    if mail_recipient:
        payload['mail_recipient'] = mail_recipient
    if mail_subject:
        payload['mail_subject'] = mail_subject
        
    data = json.dumps(payload).encode('utf-8')
    req = urllib.request.Request(
        endpoint,
        data=data,
        headers={'Content-Type': 'application/json', 'X-VBC-Token': token}
    )
    
    try:
        print(f"🚀 Đang gửi yêu cầu tạo Contact Form 7 '{title}' tới {endpoint}...")
        with urllib.request.urlopen(req, timeout=30) as resp:
            res_data = json.loads(resp.read().decode('utf-8'))
            if res_data.get('success'):
                cf7_id = res_data.get('id')
                shortcode = res_data.get('shortcode')
                print("============================================================")
                print("🎉 TẠO CONTACT FORM 7 THÀNH CÔNG!")
                print("============================================================")
                print(f"Form ID   : {cf7_id}")
                print(f"Title     : {title}")
                print(f"Shortcode : {shortcode}")
                print("============================================================\n")
                return shortcode
            else:
                print(f"❌ Lỗi từ server: {res_data}")
                return None
    except urllib.error.HTTPError as e:
        err_msg = e.read().decode('utf-8')
        print(f"❌ HTTP Error {e.code}: {err_msg}")
        return None
    except Exception as e:
        print(f"❌ Exception: {e}")
        return None

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description="Tạo Contact Form 7 tự động qua REST API")
    parser.add_argument('--title', required=True, help="Tiêu đề biểu mẫu Form")
    parser.add_argument('--fields', default="name,phone,email,message", help="Danh sách trường ngăn cách bằng dấu phẩy: name,phone,email,course,message")
    parser.add_argument('--form_html', default="", help="Mã nguồn Form Contact Form 7 tùy chỉnh (nếu có)")
    parser.add_argument('--id', default=None, help="Form ID để cập nhật thay vì tạo mới")
    parser.add_argument('--button', default="Đăng ký nhận tư vấn miễn phí", help="Chữ trên nút submit")
    parser.add_argument('--recipient', default=None, help="Email nhận thông báo")
    parser.add_argument('--subject', default=None, help="Tiêu đề mail")
    
    args = parser.parse_args()
    
    if args.form_html:
        content = args.form_html
    else:
        content = build_default_form_content(args.fields, args.button)
        
    sc = create_cf7_form(args.title, content, args.id, args.recipient, args.subject)
    if sc:
        print(f"MÃ SHORTCODE DÙNG TRONG VBC:\n{sc}")
