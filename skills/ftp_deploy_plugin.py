# -*- coding: utf-8 -*-
import ftplib
import json
import os

config = json.load(open('ultimate-flatsome-vibecode/vbc-config.json', 'r', encoding='utf-8'))
ftp_cfg = config['ftp']

print("Connecting to FTP...")
ftp = ftplib.FTP(ftp_cfg['host'])
ftp.login(ftp_cfg['user'], ftp_cfg['password'])

target_dir = 'ultimate-flatsome-vibecode'
print(f"Target dir: {target_dir}")

local_file = 'ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php'
with open(local_file, 'rb') as f:
    remote_path = f"{target_dir}/ultimate-flatsome-vibecode.php"
    print(f"Uploading {local_file} -> {remote_path} ...")
    ftp.storbinary(f"STOR {remote_path}", f)

ftp.quit()
print("Deploy plugin via FTP successful!")
