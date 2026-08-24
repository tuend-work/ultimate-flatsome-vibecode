const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

// 1. Tự động nạp thông tin FTP từ file vbc-config.json
function loadConfig() {
  const possiblePaths = [
    path.join(__dirname, '../vbc-config.json'),
    path.join(__dirname, '../../vbc-config.json'),
    path.join(process.cwd(), 'vbc-config.json'),
    path.join(process.cwd(), 'ultimate-flatsome-vibecode/vbc-config.json')
  ];

  for (const p of possiblePaths) {
    if (fs.existsSync(p)) {
      try {
        const raw = fs.readFileSync(p, 'utf8');
        const data = JSON.parse(raw);
        if (data.ftp) {
          console.log(`[VibeCode FTP] Đã nạp cấu hình từ: ${path.normalize(p)}`);
          return data;
        }
      } catch (err) {
        console.error(`[VibeCode FTP Error] Lỗi đọc file cấu hình tại ${p}:`, err.message);
      }
    }
  }

  console.error('\x1b[31m[LỖI] Không tìm thấy file vbc-config.json chứa thông tin FTP!\x1b[0m');
  process.exit(1);
}

const config = loadConfig();
const ftpConfig = config.ftp;

if (!ftpConfig.host || ftpConfig.host === '<none>' || !ftpConfig.user || ftpConfig.user === '<none>' || !ftpConfig.password || ftpConfig.password === '<none>') {
  console.error('\x1b[31m[LỖI] Thông tin FTP trong vbc-config.json chưa đầy đủ hoặc có giá trị <none>.\x1b[0m');
  process.exit(1);
}

console.log(`\n📡 Bắt đầu đẩy toàn bộ mã nguồn plugin lên WordPress FTP (${ftpConfig.host})...`);

const pythonScript = `
import ftplib, os, json, sys

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except Exception:
        pass

cfg = json.loads('''${JSON.stringify(config)}''')
ftp_cfg = cfg['ftp']
host = ftp_cfg['host']
user = ftp_cfg['user']
password = ftp_cfg['password']
plugins_path = ftp_cfg.get('plugins_path') or ftp_cfg.get('path') or '/wp-content/plugins'

print(f"Connecting to FTP {host} as {user}...")
ftp = ftplib.FTP(host)
ftp.login(user, password)
print("✓ Đăng nhập FTP thành công!")

remote_base = f"{plugins_path.rstrip('/')}/ultimate-flatsome-vibecode"

def ensure_remote_dir(path):
    parts = path.strip('/').split('/')
    cur = ""
    for part in parts:
        cur += "/" + part
        try:
            ftp.mkd(cur)
        except Exception:
            pass

def upload_dir(local_dir, remote_dir):
    ensure_remote_dir(remote_dir)
    items = sorted(os.listdir(local_dir))
    for item in items:
        if item in ['.git', '__pycache__', '.DS_Store', 'tmp', 'node_modules']:
            continue
        local_path = os.path.join(local_dir, item)
        remote_path = f"{remote_dir}/{item}".replace('\\\\', '/')
        
        if os.path.isdir(local_path):
            upload_dir(local_path, remote_path)
        else:
            rel = os.path.relpath(local_path, os.getcwd())
            print(f" -> Đang tải lên: {rel}")
            with open(local_path, 'rb') as f:
                ftp.storbinary(f"STOR {remote_path}", f)

target_dir = os.path.abspath('ultimate-flatsome-vibecode')
upload_dir(target_dir, remote_base)
ftp.quit()
print("\\n============================================================")
print("🎉 TẤT CẢ FILE PLUGIN ĐÃ ĐƯỢC ĐẨY LÊN WORDPRESS THÀNH CÔNG!")
print("============================================================")
`;

const res = spawnSync('python', ['-c', pythonScript], { stdio: 'inherit', encoding: 'utf-8' });

if (res.status !== 0) {
  console.error('\n❌ Có lỗi xảy ra trong quá trình upload FTP.');
  process.exit(res.status || 1);
}
