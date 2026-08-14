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
/**
 * Phát hiện và sửa nested same-tag shortcodes bằng phương pháp Stack Tokenizer.
 * Quét tuần tự toàn bộ token mở và đóng, thay thế chính xác cặp thẻ theo cấp độ sâu.
 */
function fixNestedShortcodes(content) {
    let fixed = content;
    let totalFixes = 0;

    for (const tag of VBC_NESTABLE_TAGS) {
        // Chuẩn hóa: Gỡ bỏ các hậu tố _inner trước đó để kiểm tra từ đầu
        const normRegex = new RegExp(`\\[(/?)${tag}_inner(?:_\\d+)?(\\s[^\\]]*)?\\]`, 'g');
        fixed = fixed.replace(normRegex, (match, slash, attrs) => `[${slash || ''}${tag}${attrs || ''}]`);

        // Tìm tất cả token của tag này
        const tagRegex = new RegExp(`\\[(/?)${tag}(\\s[^\\]]*)?\\]`, 'g');
        let match;
        const tokens = [];
        while ((match = tagRegex.exec(fixed)) !== null) {
            tokens.push({
                full: match[0],
                isClose: match[1] === '/',
                attrs: match[2] || '',
                index: match.index,
                length: match[0].length
            });
        }

        if (tokens.length === 0) continue;

        const stack = [];
        const replacements = [];

        for (const token of tokens) {
            if (!token.isClose) {
                const currentDepth = stack.length + 1;
                if (currentDepth > 1) {
                    const suffix = (currentDepth === 2) ? '_inner' : `_inner_${currentDepth - 2}`;
                    const targetTag = `${tag}${suffix}`;
                    const newOpen = `[${targetTag}${token.attrs}]`;
                    replacements.push({
                        start: token.index,
                        end: token.index + token.length,
                        newText: newOpen
                    });
                    stack.push(targetTag);
                    totalFixes++;
                } else {
                    stack.push(tag);
                }
            } else {
                if (stack.length > 0) {
                    const expectedTag = stack.pop();
                    if (expectedTag !== tag) {
                        replacements.push({
                            start: token.index,
                            end: token.index + token.length,
                            newText: `[/${expectedTag}]`
                        });
                    }
                }
            }
        }

        // Áp dụng thay thế từ cuối lên đầu để không làm lệch index
        replacements.sort((a, b) => b.start - a.start);
        for (const r of replacements) {
            fixed = fixed.substring(0, r.start) + r.newText + fixed.substring(r.end);
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
 * Tự động loại bỏ các khai báo font-family cứng trong custom_css để kế thừa
 * trọn vẹn font chữ mặc định của Flatsome (đã được cấu hình tiếng Việt trong Theme Options).
 */
function stripHardcodedFontFamily(content) {
    let fixes = 0;
    // Tìm font-family trong custom_css
    const pattern = /font-family:\s*['"][^'"]+['"][^;}]*;?/gi;
    const result = content.replace(pattern, () => {
        fixes++;
        return '';
    });
    return { content: result, fixes };
}

/**
 * Tự động sửa các comment HTML bị viết sai cú pháp (như <-- ... --> thành <!-- ... -->).
 */
function fixInvalidHtmlComments(content) {
    let fixes = 0;
    const pattern = /<--\s*(.*?)\s*-->/g;
    const result = content.replace(pattern, (match, text) => {
        fixes++;
        return `<!-- ${text} -->`;
    });
    return { content: result, fixes };
}

/**
 * Tự động chuyển đổi các thẻ text VBC (span, p, h1-h6, a) có nội dung trần 
 * sang dạng thuộc tính content="..." để tránh wpautop tự bọc thẻ <p>.
 * 
 * QUAN TRỌNG: Sử dụng &quot; thay vì \" vì bộ phân tích shortcode của WordPress
 * sẽ bị vỡ thuộc tính ngay khi gặp dấu ngoặc kép đầu tiên.
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

        // Escape các dấu nháy kép bên trong text thành &quot; an toàn cho shortcode WordPress
        const escapedText = trimmedText.replace(/"/g, '&quot;');
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
 * Chuẩn hóa các thuộc tính của thẻ [vbc_a].
 * Tự động chuyển đổi href="..." -> link_url="..." và target="..." -> link_target="..."
 */
function fixLinkShortcodes(content) {
    let fixes = 0;
    const pattern = /\[vbc_a(_inner(?:_\d+)?)?([^\]]*)\]/g;
    const result = content.replace(pattern, (match, suffix, attrs) => {
        let modified = false;
        let newAttrs = attrs;

        // Chuyển href thành link_url nếu chưa có link_url
        if (/\bhref=(["'])(.*?)\1/.test(newAttrs) && !newAttrs.includes('link_url=')) {
            newAttrs = newAttrs.replace(/\bhref=(["'])(.*?)\1/, 'link_url="$2"');
            modified = true;
        }

        // Chuyển target thành link_target nếu chưa có link_target
        if (/\btarget=(["'])(.*?)\1/.test(newAttrs) && !newAttrs.includes('link_target=')) {
            newAttrs = newAttrs.replace(/\btarget=(["'])(.*?)\1/, 'link_target="$2"');
            modified = true;
        }

        if (modified) {
            fixes++;
            const tag = `vbc_a${suffix || ''}`;
            return `[${tag} ${newAttrs.trim()}]`;
        }
        return match;
    });
    return { content: result, fixes };
}

/**
 * Tự động chuyển đổi các thuộc tính Flexbox/Grid trần 
 * (align_items, justify_content, gap, flex_direction, flex_wrap) vào custom_css.
 * Điều này đảm bảo tất cả các style căn chỉnh đều được biên dịch chính xác 100% vào CSS selector.
 */
function fixFlexProperties(content) {
    let fixes = 0;
    const pattern = /\[(vbc_(?:div|box|block|container)(?:_inner(?:_\d+)?)?)(\s[^\]]*)\]/g;

    const result = content.replace(pattern, (match, tag, attrs) => {
        const flexProps = ['align_items', 'justify_content', 'gap', 'flex_direction', 'flex_wrap'];
        const extracted = [];
        let newAttrs = attrs;

        for (const prop of flexProps) {
            const propRegex = new RegExp(`\\b${prop}=(["'])(.*?)\\1`, 'i');
            const found = newAttrs.match(propRegex);
            if (found) {
                const cssName = prop.replace(/_/g, '-');
                extracted.push(`${cssName}: ${found[2]};`);
                newAttrs = newAttrs.replace(propRegex, '').trim();
            }
        }

        if (extracted.length > 0) {
            fixes++;
            // Gộp vào custom_css hiện có hoặc tạo custom_css mới
            const customCssRegex = /\bcustom_css=(["'])(.*?)\1/;
            const cssMatch = newAttrs.match(customCssRegex);

            if (cssMatch) {
                let existingCss = cssMatch[2];
                if (existingCss.includes('selector {')) {
                    existingCss = existingCss.replace('selector {', `selector { ${extracted.join(' ')} `);
                } else {
                    existingCss = `selector { ${extracted.join(' ')} } ` + existingCss;
                }
                newAttrs = newAttrs.replace(customCssRegex, `custom_css="${existingCss.trim()}"`);
            } else {
                newAttrs += ` custom_css="selector { ${extracted.join(' ')} }"`;
            }

            return `[${tag} ${newAttrs.replace(/\s+/g, ' ').trim()}]`;
        }

        return match;
    });

    return { content: result, fixes };
}

/**
 * Chuẩn hóa các mã màu Hex thiếu dấu #.
 */
function fixRawHexColors(content) {
    let fixes = 0;
    const colorProps = ['background_color', 'color', 'border_color', 'glow_color', 'text_color'];
    let fixed = content;

    for (const prop of colorProps) {
        const regex = new RegExp(`\\b${prop}=(["'])([0-9a-fA-F]{3,8})\\1`, 'g');
        fixed = fixed.replace(regex, (match, quote, hex) => {
            fixes++;
            return `${prop}=${quote}#${hex}${quote}`;
        });
    }

    return { content: fixed, fixes };
}

/**
 * Phát hiện và cảnh báo nếu có cấu trúc [row] bị lồng bên trong [col].
 */
function checkRowInColNesting(content) {
    let warnings = 0;
    const colRegex = /\[col\s[^\]]*\]/g;
    let colMatch;

    while ((colMatch = colRegex.exec(content)) !== null) {
        const afterCol = content.substring(colMatch.index);
        const nextColClose = afterCol.indexOf('[/col]');
        if (nextColClose !== -1) {
            const colContent = afterCol.substring(0, nextColClose);
            if (/\[row[\s\]]/.test(colContent)) {
                warnings++;
            }
        }
    }

    return warnings;
}

/**
 * Tự động chuyển đổi:
 * [vbc_span ...][vbc_icon ... attrs] Text [/vbc_span]
 * thành:
 * [vbc_div ... display="flex" ...][vbc_icon ... attrs][vbc_span_inner content="Text"][/vbc_span_inner][/vbc_div]
 */
function convertSpanWithIconToDiv(content) {
    let fixes = 0;
    const pattern = /\[vbc_span([^\]]*)\]\s*(\[vbc_icon[^\]]*\])\s*([^\[]+?)\s*\[\/vbc_span\]/gs;

    const result = content.replace(pattern, (match, spanAttrs, iconShortcode, text) => {
        const trimmedText = text.trim();
        const trimmedSpanAttrs = spanAttrs.trim();

        const escapedText = trimmedText.replace(/"/g, '&quot;');
        fixes++;

        let divAttrs = trimmedSpanAttrs;
        if (!divAttrs.includes('display=')) {
            divAttrs += ' display="inline-flex"';
        }
        if (!divAttrs.includes('align_items=')) {
            divAttrs += ' align_items="center"';
        }
        if (!divAttrs.includes('gap=')) {
            divAttrs += ' gap="8px"';
        }

        return `[vbc_div ${divAttrs.trim()}]${iconShortcode}[vbc_span_inner content="${escapedText}"][/vbc_span_inner][/vbc_div]`;
    });

    return { content: result, fixes };
}

/**
 * Hàm sanitize tổng hợp — chạy tất cả các bước kiểm tra & sửa lỗi thông minh.
 */
function sanitizeShortcodeContent(content) {
    console.log('\n\x1b[35m[SANITIZER & LINTER] Đang kiểm tra và chuẩn hóa nội dung shortcode...\x1b[0m');
    
    // Bước 1: Sửa các comment HTML sai cú pháp (<-- ... -->)
    const commentResult = fixInvalidHtmlComments(content);
    if (commentResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động sửa ${commentResult.fixes} HTML comment sai cú pháp sang <!-- ... -->\x1b[0m`);
    }

    // Bước 2: Loại bỏ font-family hardcoded để kế thừa trọn vẹn font Flatsome
    const fontResult = stripHardcodedFontFamily(commentResult.content);
    if (fontResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động gỡ bỏ ${fontResult.fixes} khai báo font-family cứng để kế thừa font Flatsome\x1b[0m`);
    }

    // Bước 3: Chuẩn hóa thẻ link [vbc_a]
    const linkResult = fixLinkShortcodes(fontResult.content);
    if (linkResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động chuẩn hóa ${linkResult.fixes} thuộc tính liên kết (href -> link_url)\x1b[0m`);
    }

    // Bước 4: Chuẩn hóa thuộc tính Flexbox/Grid vào custom_css
    const flexResult = fixFlexProperties(linkResult.content);
    if (flexResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động gom ${flexResult.fixes} thuộc tính Flex/Grid vào selector custom_css\x1b[0m`);
    }

    // Bước 5: Chuẩn hóa mã màu Hex
    const hexResult = fixRawHexColors(flexResult.content);
    if (hexResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động bổ sung dấu # cho ${hexResult.fixes} mã màu Hex\x1b[0m`);
    }

    // Bước 6: Chuyển đổi vbc_span chứa icon và text thành vbc_div và vbc_span_inner
    const badgeResult = convertSpanWithIconToDiv(hexResult.content);
    if (badgeResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Chuyển đổi ${badgeResult.fixes} badge span chứa icon thành khối vbc_div an toàn\x1b[0m`);
    }

    // Bước 7: Sửa nested same-tag shortcodes bằng Stack Tokenizer
    const nestResult = fixNestedShortcodes(badgeResult.content);
    if (nestResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động sửa ${nestResult.fixes} trường hợp nested same-tag thành cấu trúc _inner chuẩn\x1b[0m`);
    }

    // Bước 8: Escape ký tự < trong text content
    const escResult = escapeRawLessThan(nestResult.content);
    if (escResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động escape ${escResult.fixes} ký tự < thành &lt;\x1b[0m`);
    }

    // Bước 9: Tự động chuyển đổi text-only tags sang dạng content attribute
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);
    if (contentAttrResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động chuyển ${contentAttrResult.fixes} thẻ text trần sang thuộc tính content="..."\x1b[0m`);
    }

    // Bước 10: Linter kiểm tra lồng [row] trong [col]
    const rowWarnings = checkRowInColNesting(contentAttrResult.content);
    if (rowWarnings > 0) {
        console.warn(`  \x1b[31m⚠ CẢNH BÁO: Phát hiện ${rowWarnings} khối [row] lồng bên trong [col]! Khuyên dùng CSS Grid thay vì lồng [row].\x1b[0m`);
    }

    const totalFixes = commentResult.fixes + fontResult.fixes + linkResult.fixes + flexResult.fixes + hexResult.fixes + badgeResult.fixes + nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    if (totalFixes > 0) {
        console.log(`  \x1b[35m→ Tổng cộng bộ Linter đã tự động tối ưu & sửa ${totalFixes} vấn đề kỹ thuật!\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Cú pháp shortcode đạt chuẩn 100%, không cần sửa gì!\x1b[0m');
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
