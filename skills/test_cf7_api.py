# -*- coding: utf-8 -*-
import urllib.request
import json
import sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

config = json.load(open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8'))
url = f"{config['api-url']}/vbc/v1/cf7"

cf7_markup = """<div class="hlhv-form-grid">
  <div class="hlhv-input-group">
    <label>📍 Điểm đi</label>
    [select your-departure "Hà Nội (Bến xe Nước Ngầm)" "Hải Phòng" "Nam Định / Ninh Bình" "Thanh Hóa / Nghệ An" "Đà Nẵng / Huế" "TP. Hồ Chí Minh"]
  </div>
  <div class="hlhv-input-group">
    <label>📍 Điểm đến</label>
    [select your-destination "TP. Hồ Chí Minh (BX Miền Đông)" "Bình Dương / Đồng Nai" "Nha Trang / Phan Thiết" "Đà Nẵng / Quảng Nam" "Nghệ An / Hà Tĩnh" "Hà Nội"]
  </div>
  <div class="hlhv-input-group">
    <label>📅 Ngày đi</label>
    [date departure-date]
  </div>
  <div class="hlhv-input-group">
    <label>🚌 Loại dịch vụ / Dòng xe</label>
    [select service-type "Xe Giường Nằm Cao Cấp" "Xe Limousine VIP" "Thuê Xe Du Lịch 16 - 45 Chỗ" "Gửi Hàng Nhanh Bắc Nam"]
  </div>
  <div class="hlhv-input-group">
    <label>📞 Số điện thoại</label>
    [tel* your-phone placeholder "Nhập SĐT để nhận vé & giá"]
  </div>
</div>
[submit class:hlhv-btn-submit "🔍 TÌM CHUYẾN XE & ĐẶT VÉ NHANH"]"""

payload = {
    'title': 'Form Đặt Vé & Thuê Xe Hoàng Long Hải Vân',
    'form': cf7_markup
}

req = urllib.request.Request(
    url,
    data=json.dumps(payload).encode('utf-8'),
    headers={
        'Content-Type': 'application/json; charset=utf-8',
        'X-VBC-Token': config['token'],
        'User-Agent': 'Mozilla/5.0'
    }
)

res = urllib.request.urlopen(req)
data = json.loads(res.read().decode('utf-8'))
print('CF7 Create Result:', json.dumps(data, ensure_ascii=False, indent=2))
