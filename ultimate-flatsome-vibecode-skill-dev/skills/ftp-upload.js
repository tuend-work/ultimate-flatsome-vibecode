const net = require('net');
const fs = require('fs');
const path = require('path');

// 1. Tự động nạp thông tin FTP từ file vbc-config.json
function loadConfig() {
  const possiblePaths = [
    path.join(__dirname, '../vbc-config.json'),
    path.join(__dirname, '../../vbc-config.json'),
    path.join(process.cwd(), 'vbc-config.json'),
    path.join(process.cwd(), '../vbc-config.json')
  ];

  for (const p of possiblePaths) {
    if (fs.existsSync(p)) {
      try {
        const raw = fs.readFileSync(p, 'utf8');
        const data = JSON.parse(raw);
        if (data.ftp) {
          console.log(`[VibeCode FTP] Đã nạp cấu hình từ: ${path.normalize(p)}`);
          return data.ftp;
        }
      } catch (err) {
        console.error(`[VibeCode FTP Error] Lỗi đọc file cấu hình tại ${p}:`, err.message);
      }
    }
  }

  console.error('\x1b[31m[LỖI] Không tìm thấy file vbc-config.json chứa thông tin FTP!\x1b[0m');
  console.error('Vui lòng đảm bảo file vbc-config.json nằm ở thư mục gốc của dự án.');
  process.exit(1);
}

const ftpConfig = loadConfig();

if (!ftpConfig.host || ftpConfig.host === '<none>' || !ftpConfig.user || ftpConfig.user === '<none>' || !ftpConfig.password || ftpConfig.password === '<none>') {
  console.error('\x1b[31m[LỖI] Thông tin FTP trong vbc-config.json chưa đầy đủ hoặc có giá trị <none>.\x1b[0m');
  console.error('Vui lòng nhập đúng Host, User, Password trong file vbc-config.json trước khi chạy.');
  process.exit(1);
}

// 2. Xác định danh sách file cần upload
const args = process.argv.slice(2);
let filesToUpload = [];

if (args.length >= 2) {
  // Upload từ tham số CLI: node skills/ftp-upload.js <localPath> <remotePath>
  filesToUpload.push([path.resolve(args[0]), args[1]]);
} else if (args.length === 1) {
  const localFile = path.resolve(args[0]);
  const baseRemote = ftpConfig.plugins_path || ftpConfig.path || '';
  const remotePath = (baseRemote.replace(/\/+$/, '') + '/ultimate-flatsome-vibecode/' + path.basename(localFile)).replace(/\\/g, '/');
  filesToUpload.push([localFile, remotePath]);
} else {
  // Mặc định upload các file chính của plugin nếu tồn tại
  const candidates = [
    [
      path.join(__dirname, '../ultimate-flatsome-vibecode.php'),
      (ftpConfig.plugins_path || ftpConfig.path || '').replace(/\/+$/, '') + '/ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php'
    ],
    [
      path.join(__dirname, '../assets/vbc-icon-picker.js'),
      (ftpConfig.plugins_path || ftpConfig.path || '').replace(/\/+$/, '') + '/ultimate-flatsome-vibecode/assets/vbc-icon-picker.js'
    ],
    [
      path.join(__dirname, '../assets/vbc-icon-picker.css'),
      (ftpConfig.plugins_path || ftpConfig.path || '').replace(/\/+$/, '') + '/ultimate-flatsome-vibecode/assets/vbc-icon-picker.css'
    ]
  ];

  for (const [loc, rem] of candidates) {
    if (fs.existsSync(loc)) {
      filesToUpload.push([loc, rem.replace(/\\/g, '/')]);
    }
  }
}

if (filesToUpload.length === 0) {
  console.log('[VibeCode FTP] Không tìm thấy file nào cần upload. Cú pháp: node skills/ftp-upload.js <localFile> [remoteFile]');
  process.exit(0);
}

function uploadFile(localPath, remotePath) {
  return new Promise((resolve, reject) => {
    console.log(`\n=== Uploading: ${path.basename(localPath)} => ${remotePath} ===`);
    const client = net.createConnection(21, ftpConfig.host);
    client.setEncoding('utf8');
    let step = 0;
    let dataSocket = null;
    let buffer = '';

    function sendCmd(cmd) {
      const display = cmd.startsWith('PASS') ? 'PASS ***' : cmd;
      console.log('-> ' + display);
      client.write(cmd + '\r\n');
    }

    function handleResponse(data) {
      buffer += data;
      const lines = buffer.split('\r\n');
      buffer = lines.pop();

      for (const line of lines) {
        if (!line.trim()) continue;
        console.log('<- ' + line);
        const code = parseInt(line.substring(0, 3));
        
        if (line[3] === '-') continue;

        if (step === 0 && code === 220) {
          sendCmd('USER ' + ftpConfig.user);
          step = 1;
        } else if (step === 1 && code === 331) {
          sendCmd('PASS ' + ftpConfig.password);
          step = 2;
        } else if (step === 2 && code === 230) {
          sendCmd('DELE ' + remotePath);
          step = 3;
        } else if (step === 3 && (code === 250 || code === 550)) {
          if (code === 250) console.log('Remote file deleted OK.');
          if (code === 550) console.log('Remote file not found, will create new.');
          sendCmd('PASV');
          step = 4;
        } else if (step === 4 && code === 227) {
          const match = line.match(/\(([^)]+)\)/);
          if (!match) { client.end(); reject(new Error('Failed to parse PASV')); return; }
          const parts = match[1].split(',');
          const host = parts.slice(0, 4).join('.');
          const port = (parseInt(parts[4]) << 8) + parseInt(parts[5]);
          console.log(`Passive mode: ${host}:${port}`);

          dataSocket = net.createConnection(port, host);
          dataSocket.on('connect', () => {
            console.log('Data socket connected.');
            sendCmd('STOR ' + remotePath);
            step = 5;
          });
          dataSocket.on('error', (err) => {
            console.error('Data socket error:', err.message);
            reject(err);
          });
        } else if (step === 5 && (code === 150 || code === 125)) {
          console.log('Transferring file...');
          const fileStream = fs.createReadStream(localPath);
          fileStream.pipe(dataSocket);
          fileStream.on('end', () => {
            console.log('File content sent.');
            dataSocket.end();
            step = 6;
          });
          fileStream.on('error', (err) => {
            console.error('File read error:', err.message);
            reject(err);
          });
        } else if (step === 6 && code === 226) {
          console.log('Upload complete!');
          sendCmd('QUIT');
          client.end();
          resolve();
        } else if (code >= 500) {
          console.error('FTP Error:', line);
          client.end();
          reject(new Error('FTP error: ' + line));
        }
      }
    }

    client.on('data', handleResponse);
    client.on('error', (err) => { console.error('Socket error:', err.message); reject(err); });
    client.on('close', () => { if (step < 6) console.log('Connection closed before completion.'); });
  });
}

(async () => {
  for (const [local, remote] of filesToUpload) {
    try {
      await uploadFile(local, remote);
    } catch (err) {
      console.error(`FAILED to upload ${path.basename(local)}: ${err.message}`);
      process.exit(1);
    }
  }
  console.log('\n✅ All files uploaded successfully!');
})();
