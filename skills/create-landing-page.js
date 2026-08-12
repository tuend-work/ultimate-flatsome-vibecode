#!/usr/bin/env node

/**
 * CLI Skill: Đẩy Landing Page lên WordPress (Bản Đơn Giản Hóa)
 * Ngôn ngữ: Node.js (Yêu cầu Node.js 18+)
 * Sử dụng: node skills/create-landing-page.js [arguments]
 */

const fs = require('fs');
const path = require('path');

// 1. Phân tích tham số dòng lệnh (CLI Arguments)
function parseArgs() {
    const args = {};
    for (let i = 2; i < process.argv.length; i++) {
        const arg = process.argv[i];
        if (arg.startsWith('--')) {
            const key = arg.slice(2);
            const nextVal = process.argv[i + 1];
            if (nextVal && !nextVal.startsWith('--')) {
                args[key] = nextVal;
                i++;
            } else {
                args[key] = true;
            }
        }
    }
    return args;
}

async function main() {
    const args = parseArgs();

    // Hiển thị Banner giới thiệu
    console.log('\x1b[36m==================================================\x1b[0m');
    console.log('\x1b[1m\x1b[36m       VIBECODE PAGE PUBLISHER CLI TOOL\x1b[0m');
    console.log('\x1b[36m==================================================\x1b[0m');

    // Đọc file config nếu có
    let config = {};
    const configPath = path.join(process.cwd(), 'vbc-config.json');
    if (fs.existsSync(configPath)) {
        try {
            config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        } catch (e) {
            console.warn('\x1b[33m[CẢNH BÁO] Không thể đọc file vbc-config.json: ' + e.message + '\x1b[0m');
        }
    }

    // Kiểm tra các thông số bắt buộc
    const apiUrl = args['api-url'] || config['api-url'] || config['apiUrl'];
    const token = args['token'] || config['token'];
    
    if (!apiUrl) {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc --api-url (Ví dụ: https://my-site.com/wp-json)\x1b[0m');
        process.exit(1);
    }
    if (!token) {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc --token (Lấy từ User Profile trong WordPress Admin)\x1b[0m');
        process.exit(1);
    }

    const title = args['title'] || 'Page Generated';
    const slug = args['slug'] || '';
    const postId = args['post-id'] || '';
    const imageUpload = args['image-upload']; // Danh sách ảnh cần upload (phân cách bằng dấu phẩy)
    const fileSource = args['file'];
    const contentString = args['content'];

    let pageContent = '';

    // Lấy nội dung shortcode
    if (fileSource) {
        const absoluteFile = path.resolve(fileSource);
        if (!fs.existsSync(absoluteFile)) {
            console.error(`\x1b[31m[LỖI] File chứa nội dung shortcode không tồn tại: ${absoluteFile}\x1b[0m`);
            process.exit(1);
        }
        pageContent = fs.readFileSync(absoluteFile, 'utf8');
    } else if (contentString) {
        pageContent = contentString;
    } else {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc: Cần cung cấp --file <đường_dẫn> hoặc --content "<nội_dung_shortcode>"\x1b[0m');
        process.exit(1);
    }

    const uploadedAssets = [];

    // 2. Thực hiện Tải ảnh tài nguyên lên WordPress (nếu có)
    if (imageUpload) {
        const filePaths = imageUpload.split(',').map(p => p.trim());
        console.log(`\n\x1b[33m[1/3] Đang chuẩn bị tải lên ${filePaths.length} ảnh tài nguyên...\x1b[0m`);

        for (const filePath of filePaths) {
            const absolutePath = path.resolve(filePath);
            if (!fs.existsSync(absolutePath)) {
                console.warn(`\x1b[31m[CẢNH BÁO] File ảnh không tồn tại tại: ${absolutePath}. Bỏ qua.\x1b[0m`);
                continue;
            }

            try {
                console.log(` -> Đang tải lên: ${path.basename(absolutePath)}...`);
                const fileBuffer = fs.readFileSync(absolutePath);
                const blob = new Blob([fileBuffer]);
                const formData = new FormData();
                formData.append('file', blob, path.basename(absolutePath));

                // Gọi REST API upload
                const uploadRes = await fetch(`${apiUrl.replace(/\/$/, '')}/vbc/v1/upload`, {
                    method: 'POST',
                    headers: {
                        'X-VBC-Token': token
                    },
                    body: formData
                });

                if (!uploadRes.ok) {
                    const errData = await uploadRes.json();
                    throw new Error(errData.message || `HTTP ${uploadRes.status}`);
                }

                const result = await uploadRes.json();
                if (result.success) {
                    console.log(`    \x1b[32m✓ Tải lên thành công! ID: ${result.attachment_id}, URL: ${result.url}\x1b[0m`);
                    uploadedAssets.push({
                        id: result.attachment_id,
                        url: result.url,
                        filename: path.basename(filePath)
                    });
                }
            } catch (error) {
                console.error(`    \x1b[31m✗ Tải ảnh ${path.basename(filePath)} thất bại: ${error.message}\x1b[0m`);
            }
        }
    } else {
        console.log('\n\x1b[33m[1/3] Không có ảnh tài nguyên cần tải lên.\x1b[0m');
    }

    // 3. Thay thế các placeholder ảnh trong nội dung trang (ví dụ: {{image_1_url}}, {{image_1_id}})
    if (uploadedAssets.length > 0) {
        console.log('\n\x1b[33m[2/3] Đang tiến hành thay thế placeholder ảnh trong nội dung...\x1b[0m');
        uploadedAssets.forEach((asset, index) => {
            const num = index + 1;
            const urlPlaceholder = `{{image_${num}_url}}`;
            const idPlaceholder = `{{image_${num}_id}}`;
            
            console.log(` -> Thay thế: ${urlPlaceholder} -> ${asset.url}`);
            console.log(` -> Thay thế: ${idPlaceholder} -> ${asset.id}`);
            
            pageContent = pageContent.replaceAll(urlPlaceholder, asset.url);
            pageContent = pageContent.replaceAll(idPlaceholder, String(asset.id));
        });
    } else {
        console.log('\n\x1b[33m[2/3] Không cần xử lý thay thế ảnh placeholder.\x1b[0m');
    }

    // 4. Đẩy nội dung lên API của WordPress để tạo/cập nhật trang
    console.log('\n\x1b[33m[3/3] Đang gửi yêu cầu đăng bài lên WordPress REST API...\x1b[0m');
    
    try {
        const postData = {
            title: title,
            content: pageContent,
            status: 'publish',
            post_type: 'page'
        };
        if (slug) postData.slug = slug;
        if (postId) postData.post_id = postId;

        const pageRes = await fetch(`${apiUrl.replace(/\/$/, '')}/vbc/v1/page`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-VBC-Token': token
            },
            body: JSON.stringify(postData)
        });

        if (!pageRes.ok) {
            const errData = await pageRes.json();
            throw new Error(errData.message || `HTTP ${pageRes.status}`);
        }

        const result = await pageRes.json();
        if (result.success) {
            console.log('\n\x1b[32m==================================================\x1b[0m');
            console.log('\x1b[1m\x1b[32m   XUẤT BẢN TRANG WEB THÀNH CÔNG!\x1b[0m');
            console.log('\x1b[32m==================================================\x1b[0m');
            console.log(`ID bài viết: ${result.post_id}`);
            console.log(`Hành động:   ${result.action === 'create' ? 'Tạo mới trang' : 'Cập nhật trang'}`);
            console.log(`Đường link:  \x1b[36m\x1b[4m${result.url}\x1b[0m`);
            console.log('\x1b[32m==================================================\x1b[0m\n');
        } else {
            throw new Error('Phản hồi từ máy chủ không thành công.');
        }
    } catch (error) {
        console.error(`\x1b[31m✗ Gửi bài lên WordPress thất bại: ${error.message}\x1b[0m`);
        process.exit(1);
    }
}

main();
