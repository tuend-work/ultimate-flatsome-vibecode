#!/usr/bin/env node

/**
 * CLI Skill: Đẩy Landing Page lên WordPress (Bản Đơn Giản Hóa)
 * Ngôn ngữ: Node.js (Yêu cầu Node.js 18+)
 * Sử dụng: node skills/create-landing-page.js [arguments]
 */

const fs = require('fs');
const path = require('path');

// ============================================================
// SHORTCODE SANITIZER
// Tự động phát hiện & sửa các vấn đề shortcode trước khi publish
// ============================================================

/**
 * Danh sách các VBC shortcode tags có thể gây lỗi nesting.
 * WordPress regex parser KHÔNG thể xử lý cùng tag lồng nhau,
 * ví dụ: [vbc_box] bên trong [vbc_box] → parser bị break.
 */
const VBC_NESTABLE_TAGS = [
    'vbc_box', 'vbc_block', 'vbc_container', 'vbc_span',
    'vbc_card', 'vbc_div'
];

/**
 * Phát hiện và sửa nested same-tag shortcodes.
 * Khi phát hiện tag lồng nhau, inner tag sẽ được thay bằng tag VBC cùng tên nhưng kèm suffix _inner hoặc _inner_1, _inner_2...
 * Điều này tránh việc trùng tên làm hỏng shortcode parser của WordPress, đồng thời vẫn giữ nguyên semantic tag name.
 */
function fixNestedShortcodes(content) {
    let fixed = content;
    let totalFixes = 0;

    for (const tag of VBC_NESTABLE_TAGS) {
        // Regex tìm opening tag: [tag_name ...attributes...]
        const openRegex = new RegExp(`\\[${tag}(\\s[^\\]]*)?\\]`, 'g');
        const closeTag = `[/${tag}]`;
        let changesMade = true;

        // Lặp cho tới khi không còn nesting nào
        while (changesMade) {
            changesMade = false;
            const lines = fixed.split('\n');
            let depth = 0;

            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];

                // Đếm số lần open tag xuất hiện trên dòng này
                const opens = [...line.matchAll(openRegex)];
                const closes = (line.match(new RegExp(`\\[/${tag}\\]`, 'g')) || []).length;

                for (const openMatch of opens) {
                    depth++;
                    if (depth > 1) {
                        // Đây là nested tag → cần thay thế bằng tag có kèm suffix độ sâu
                        const suffix = (depth === 2) ? '_inner' : `_inner_${depth - 2}`;
                        const targetTag = `${tag}${suffix}`;
                        const fullMatch = openMatch[0]; // e.g. [vbc_box display="flex" custom_css="..."]
                        const attrs = openMatch[1] || '';
                        const replacementOpen = `[${targetTag} ${attrs.trim()}]`.replace(/\s+\]$/, ']');
                        
                        lines[i] = lines[i].replace(fullMatch, replacementOpen);
                        totalFixes++;
                        changesMade = true;

                        // Tìm closing tag tương ứng gần nhất để thay thế
                        let innerDepth = 1;
                        for (let j = i; j < lines.length; j++) {
                            const searchLine = (j === i) 
                                ? lines[j].substring(lines[j].indexOf(replacementOpen) + replacementOpen.length)
                                : lines[j];
                            
                            const innerOpens = [...searchLine.matchAll(new RegExp(`\\[${tag}(\\s[^\\]]*)?\\]`, 'g'))].length;
                            const innerCloses = (searchLine.match(new RegExp(`\\[/${tag}\\]`, 'g')) || []).length;
                            
                            innerDepth += innerOpens;
                            innerDepth -= innerCloses;

                            if (innerDepth <= 0) {
                                // Thay closing tag đầu tiên trong dòng j bằng thẻ đóng mới
                                lines[j] = lines[j].replace(closeTag, `[/${targetTag}]`);
                                break;
                            }
                        }
                        break; // Restart scan do content đã thay đổi
                     }
                }

                if (changesMade) break;
                depth -= closes;
                if (depth < 0) depth = 0;
            }

            fixed = lines.join('\n');
        }
    }

    return { content: fixed, fixes: totalFixes };
}


/**
 * Escape ký tự < trong nội dung text (không phải trong shortcode tags).
 * Ví dụ: "Tải Trang < 1.5s" → "Tải Trang &lt; 1.5s"
 * 
 * Quy tắc: Chỉ escape < khi nó KHÔNG phải là:
 * - Phần mở đầu của HTML tag (e.g. <div, </div, <img)
 * - Phần mở đầu của HTML entity (e.g. &lt;)
 */
function escapeRawLessThan(content) {
    let fixes = 0;
    // Match < that is NOT followed by a valid HTML tag name, / (closing tag), or ! (comment/doctype)
    const result = content.replace(/<(?!\/|[a-zA-Z!]|\s*$)/g, (match, offset) => {
        // Kiểm tra xem < có nằm trong shortcode attribute hay không
        // Tìm ngược lại xem có đang nằm trong [...] không
        const before = content.substring(Math.max(0, offset - 200), offset);
        const lastOpen = before.lastIndexOf('[');
        const lastClose = before.lastIndexOf(']');
        
        // Nếu đang nằm bên trong shortcode attribute [...], bỏ qua
        if (lastOpen > lastClose) {
            return match;
        }
        
        fixes++;
        return '&lt;';
    });
    return { content: result, fixes };
}

/**
 * Tự động chuyển đổi các thẻ text VBC (span, p, h1-h6, a) có nội dung trần 
 * sang dạng thuộc tính content="..." để tránh wpautop tự bọc thẻ <p>.
 */
function migrateTagsToContentAttribute(content) {
    let fixes = 0;
    // Hỗ trợ cả tag cơ bản và tag có suffix lồng nhau như _inner, _inner_1...
    const pattern = /\[vbc_(span|p|h1|h2|h3|h4|h5|h6|a)(_inner(?:_\d+)?)?([^\]]*)\]([^\[]*?)\[\/vbc_\1\2\]/gs;

    const result = content.replace(pattern, (match, tag, suffix, attrs, text) => {
        const trimmedText = text.trim();
        const trimmedAttrs = attrs.trim();

        // Nếu nội dung trống, không cần convert
        if (!trimmedText) {
            return match;
        }

        // Nếu đã có thuộc tính content, bỏ qua không convert đè
        if (trimmedAttrs.includes('content=')) {
            return match;
        }

        // Escape các dấu nháy kép bên trong text nội dung
        const escapedText = trimmedText.replace(/"/g, '\\"');
        fixes++;

        const fullTag = `vbc_${tag}${suffix || ''}`;
        if (trimmedAttrs) {
            return `[${fullTag} ${trimmedAttrs} content="${escapedText}"][/${fullTag}]`;
        } else {
            return `[${fullTag} content="${escapedText}"][/${fullTag}]`;
        }
    });

    return { content: result, fixes };
}

/**
 * Hàm sanitize tổng hợp — chạy tất cả các bước kiểm tra & sửa lỗi.
 */
function sanitizeShortcodeContent(content) {
    console.log('\n\x1b[35m[SANITIZER] Đang kiểm tra nội dung shortcode...\x1b[0m');
    
    // Bước 1: Sửa nested same-tag shortcodes
    const nestResult = fixNestedShortcodes(content);
    if (nestResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Phát hiện ${nestResult.fixes} trường hợp nested same-tag shortcode → Đã tự động chuyển đổi sang cấu trúc nested suffix (_inner)\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Không có nested same-tag shortcode nào\x1b[0m');
    }

    // Bước 2: Escape ký tự < trong text content
    const escResult = escapeRawLessThan(nestResult.content);
    if (escResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Phát hiện ${escResult.fixes} ký tự < chưa được escape → Đã thay bằng &lt;\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Không có ký tự < nào cần escape\x1b[0m');
    }

    // Bước 3: Tự động chuyển đổi text-only tags sang dạng content attribute
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);
    if (contentAttrResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Phát hiện ${contentAttrResult.fixes} thẻ text trần → Đã tự động chuyển thành thuộc tính content="..."\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Tất cả các thẻ text đều đã chuẩn hóa content\x1b[0m');
    }

    const totalFixes = nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    if (totalFixes > 0) {
        console.log(`  \x1b[35m→ Tổng cộng đã tự động sửa ${totalFixes} vấn đề\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Nội dung sạch, không cần sửa gì!\x1b[0m');
    }

    return contentAttrResult.content;
}

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

    // 4. Sanitize nội dung shortcode (phát hiện & sửa lỗi tự động)
    pageContent = sanitizeShortcodeContent(pageContent);

    // 5. Đẩy nội dung lên API của WordPress để tạo/cập nhật trang
    console.log('\n\x1b[33m[4/4] Đang gửi yêu cầu đăng bài lên WordPress REST API...\x1b[0m');
    
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
