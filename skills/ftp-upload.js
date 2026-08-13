const net = require('net');
const fs = require('fs');
const path = require('path');

const config = {
  host: '103.161.172.211',
  user: 'ultimateflatsomevibecodeplugin@ultimateflatsomevibecode.s172d211.wpcloud.vn',
  password: 'KzxYwV#O?c7',
  remotePath: '/ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php',
  localFile: path.join(__dirname, '../ultimate-flatsome-vibecode.php')
};

console.log('Starting FTP Upload to ' + config.host);
const client = net.createConnection(21, config.host);
let step = 0;
let dataSocket = null;

client.setEncoding('utf8');

function sendCmd(cmd) {
  console.log('-> ' + cmd);
  client.write(cmd + '\r\n');
}

client.on('data', (data) => {
  console.log('<- ' + data.trim());
  const code = parseInt(data.substring(0, 3));
  
  if (step === 0 && code === 220) {
    sendCmd('USER ' + config.user);
    step = 1;
  } else if (step === 1 && code === 331) {
    sendCmd('PASS ' + config.password);
    step = 2;
  } else if (step === 2 && code === 230) {
    sendCmd('PASV');
    step = 3;
  } else if (step === 3 && code === 227) {
    const match = data.match(/\(([^)]+)\)/);
    if (!match) {
      console.error('Failed to parse PASV');
      client.end();
      return;
    }
    const parts = match[1].split(',');
    const host = parts.slice(0, 4).join('.');
    const port = (parseInt(parts[4]) << 8) + parseInt(parts[5]);
    console.log(`Connecting to passive data connection: ${host}:${port}`);
    
    dataSocket = net.createConnection(port, host);
    dataSocket.on('connect', () => {
      console.log('Data socket connected.');
      sendCmd('STOR ' + config.remotePath);
      step = 4;
    });
    dataSocket.on('error', (err) => {
      console.error('Data socket error:', err);
    });
  } else if (step === 4 && (code === 150 || code === 125)) {
    console.log('Sending file content...');
    const fileStream = fs.createReadStream(config.localFile);
    fileStream.pipe(dataSocket);
    fileStream.on('end', () => {
      console.log('File content sent, closing data socket.');
      dataSocket.end();
      step = 5;
    });
  } else if (step === 5 && code === 226) {
    console.log('Upload complete successfully!');
    sendCmd('QUIT');
    client.end();
  }
});

client.on('error', (err) => {
  console.error('Control socket error:', err);
});
