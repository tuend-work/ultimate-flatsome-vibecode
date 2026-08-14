const net = require('net');
const fs = require('fs');
const path = require('path');

const config = {
  host: '103.161.172.211',
  user: 'ultimateflatsomevibecodeplugin@ultimateflatsomevibecode.s172d211.wpcloud.vn',
  password: 'KzxYwV#O?c7',
};

// Files to upload: [localPath, remotePath]
const FILES = [
  [
    path.join(__dirname, '../ultimate-flatsome-vibecode.php'),
    '/ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php'
  ],
  [
    path.join(__dirname, '../assets/vbc-icon-picker.js'),
    '/ultimate-flatsome-vibecode/assets/vbc-icon-picker.js'
  ],
  [
    path.join(__dirname, '../assets/vbc-icon-picker.css'),
    '/ultimate-flatsome-vibecode/assets/vbc-icon-picker.css'
  ],
];

function uploadFile(localPath, remotePath) {
  return new Promise((resolve, reject) => {
    console.log(`\n=== Uploading: ${path.basename(localPath)} => ${remotePath} ===`);
    const client = net.createConnection(21, config.host);
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
      buffer = lines.pop(); // keep incomplete line in buffer

      for (const line of lines) {
        if (!line.trim()) continue;
        console.log('<- ' + line);
        const code = parseInt(line.substring(0, 3));
        
        // Skip multi-line responses (e.g. "220-...")
        if (line[3] === '-') continue;

        if (step === 0 && code === 220) {
          sendCmd('USER ' + config.user);
          step = 1;
        } else if (step === 1 && code === 331) {
          sendCmd('PASS ' + config.password);
          step = 2;
        } else if (step === 2 && code === 230) {
          // Delete file first to bypass overwrite restriction
          sendCmd('DELE ' + remotePath);
          step = 3;
        } else if (step === 3 && (code === 250 || code === 550)) {
          // 250 = deleted OK, 550 = file didn't exist (both OK)
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
  for (const [local, remote] of FILES) {
    try {
      await uploadFile(local, remote);
    } catch (err) {
      console.error(`FAILED to upload ${path.basename(local)}: ${err.message}`);
      process.exit(1);
    }
  }
  console.log('\n✅ All files uploaded successfully!');
})();
