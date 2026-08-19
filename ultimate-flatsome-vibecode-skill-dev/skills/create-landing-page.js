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
 * Danh sách tất cả các VBC container shortcode tags có thể gây lỗi nesting.
 * WordPress regex parser KHÔNG thể xử lý cùng tag lồng nhau nếu không có suffix _inner.
 * TUYỆT ĐỐI KHÔNG đưa các thẻ void (tự đóng) như vbc_icon, vbc_img, vbc_hr, vbc_br vào đây.
 */
const VBC_NESTABLE_TAGS = [
    'vbc_box', 'vbc_block', 'vbc_container', 'vbc_span',
    'vbc_card', 'vbc_div', 'vbc_post', 'vbc_p', 'vbc_a',
    'vbc_h1', 'vbc_h2', 'vbc_h3', 'vbc_h4', 'vbc_h5', 'vbc_h6',
    'vbc_li', 'vbc_ul', 'vbc_ol', 'vbc_table', 'vbc_tr', 'vbc_td', 'vbc_th',
    'vbc_b', 'vbc_strong', 'vbc_em', 'vbc_u',
    'vbc_testimonial', 'vbc_accordion', 'vbc_accordion_item',
    'vbc_slider', 'vbc_slide', 'vbc_fullpage'
];

/**
 * Tự động chuyển đổi các thẻ VBC không tồn tại (như [vbc_input], [vbc_textarea], [vbc_form])
 * sang các thẻ HTML chuẩn để tránh hiển thị raw shortcode ra frontend.
 */
function fixUnsupportedVbcShortcodes(content) {
    let fixes = 0;
    
    // vbc_input -> <input ... />
    let result = content.replace(/\[vbc_input\s*([^\]]*)\]/g, (match, attrs) => {
        fixes++;
        return `<input ${attrs.trim()} />`;
    });
    
    // vbc_textarea -> <textarea ...>...</textarea>
    result = result.replace(/\[vbc_textarea\s*([^\]]*)\]([\s\S]*?)\[\/vbc_textarea\]/g, (match, attrs, body) => {
        fixes++;
        return `<textarea ${attrs.trim()}>${body}</textarea>`;
    });

    // vbc_select -> <select ...>...</select>
    result = result.replace(/\[vbc_select\s*([^\]]*)\]([\s\S]*?)\[\/vbc_select\]/g, (match, attrs, body) => {
        fixes++;
        return `<select ${attrs.trim()}>${body}</select>`;
    });

    // vbc_form -> <form ...>...</form>
    result = result.replace(/\[vbc_form\s*([^\]]*)\]([\s\S]*?)\[\/vbc_form\]/g, (match, attrs, body) => {
        fixes++;
        return `<form ${attrs.trim()}>${body}</form>`;
    });

    return { content: result, fixes };
}

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
 */
function escapeRawLessThan(content) {
    let fixes = 0;
    const result = content.replace(/<(?!\/|[a-zA-Z!]|\s*$)/g, (match, offset) => {
        const before = content.substring(Math.max(0, offset - 200), offset);
        const lastOpen = before.lastIndexOf('[');
        const lastClose = before.lastIndexOf(']');
        
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
    const pattern = /font-family:\s*['"][^'"]+['"][^;}]*;?/gi;
    const result = content.replace(pattern, () => {
        fixes++;
        return '';
    });
    return { content: result, fixes };
}

/**
 * Tự động loại bỏ hoàn toàn tất cả comment HTML (<!-- ... --> hoặc <-- ... -->)
 * để tránh việc WordPress wpautop tự động bọc thẻ <p> làm sinh ra các khối Text rác trong Flatsome UX Builder.
 */
function stripAllHtmlComments(content) {
    let fixes = 0;
    const pattern = /<!--[\s\S]*?-->|<--[\s\S]*?-->/g;
    const result = content.replace(pattern, () => {
        fixes++;
        return '';
    });
    const cleaned = result.replace(/\n\s*\n\s*\n/g, '\n\n');
    return { content: cleaned, fixes };
}

/**
 * Tự động chuyển đổi các thẻ text VBC (span, p, h1-h6, a) có nội dung trần 
 * sang dạng thuộc tính content="..." để tránh wpautop tự bọc thẻ <p>.
 */
function migrateTagsToContentAttribute(content) {
    let fixes = 0;
    const pattern = /\[vbc_(span|p|h1|h2|h3|h4|h5|h6|a)(_inner(?:_\d+)?)?([^\]]*)\]([^\[]*?)\[\/vbc_\1\2\]/gs;

    const result = content.replace(pattern, (match, tag, suffix, attrs, text) => {
        const trimmedText = text.trim();
        const trimmedAttrs = attrs.trim();

        if (!trimmedText) {
            return match;
        }

        if (trimmedAttrs.includes('content=')) {
            return match;
        }

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

        if (/\bhref=(["'])(.*?)\1/.test(newAttrs) && !newAttrs.includes('link_url=')) {
            newAttrs = newAttrs.replace(/\bhref=(["'])(.*?)\1/, 'link_url="$2"');
            modified = true;
        }

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
    
    // Bước 1: Chuyển đổi các thẻ VBC không tồn tại (vbc_input, vbc_textarea...) sang HTML chuẩn
    const unsuppResult = fixUnsupportedVbcShortcodes(content);
    if (unsuppResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động chuyển đổi ${unsuppResult.fixes} thẻ form chưa đăng ký sang HTML tag chuẩn\x1b[0m`);
    }

    // Bước 2: Loại bỏ hoàn toàn tất cả comment HTML để tránh wpautop sinh thẻ <p>
    const commentResult = stripAllHtmlComments(unsuppResult.content);
    if (commentResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động loại bỏ ${commentResult.fixes} HTML comment để chống sinh thẻ <p> rác\x1b[0m`);
    }

    // Bước 3: Loại bỏ font-family hardcoded để kế thừa trọn vẹn font Flatsome
    const fontResult = stripHardcodedFontFamily(commentResult.content);
    if (fontResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động gỡ bỏ ${fontResult.fixes} khai báo font-family cứng để kế thừa font Flatsome\x1b[0m`);
    }

    // Bước 4: Chuẩn hóa thẻ link [vbc_a]
    const linkResult = fixLinkShortcodes(fontResult.content);
    if (linkResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động chuẩn hóa ${linkResult.fixes} thuộc tính liên kết (href -> link_url)\x1b[0m`);
    }

    // Bước 5: Chuẩn hóa thuộc tính Flexbox/Grid vào custom_css
    const flexResult = fixFlexProperties(linkResult.content);
    if (flexResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động gom ${flexResult.fixes} thuộc tính Flex/Grid vào selector custom_css\x1b[0m`);
    }

    // Bước 6: Chuẩn hóa mã màu Hex
    const hexResult = fixRawHexColors(flexResult.content);
    if (hexResult.fixes > 0) {
        console.log(`  \x1b[32m✓ Tự động bổ sung dấu # cho ${hexResult.fixes} mã màu Hex\x1b[0m`);
    }

    // Bước 7: Chuyển đổi vbc_span chứa icon và text thành vbc_div và vbc_span_inner
    const badgeResult = convertSpanWithIconToDiv(hexResult.content);
    if (badgeResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Chuyển đổi ${badgeResult.fixes} badge span chứa icon thành khối vbc_div an toàn\x1b[0m`);
    }

    // Bước 8: Sửa nested same-tag shortcodes bằng Stack Tokenizer
    const nestResult = fixNestedShortcodes(badgeResult.content);
    if (nestResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động sửa ${nestResult.fixes} trường hợp nested same-tag thành cấu trúc _inner chuẩn\x1b[0m`);
    }

    // Bước 9: Escape ký tự < trong text content
    const escResult = escapeRawLessThan(nestResult.content);
    if (escResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động escape ${escResult.fixes} ký tự < thành &lt;\x1b[0m`);
    }

    // Bước 10: Tự động chuyển đổi text-only tags sang dạng content attribute
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);
    if (contentAttrResult.fixes > 0) {
        console.log(`  \x1b[33m⚠ Tự động chuyển ${contentAttrResult.fixes} thẻ text trần sang thuộc tính content="..."\x1b[0m`);
    }

    // Bước 11: Linter kiểm tra lồng [row] trong [col]
    const rowWarnings = checkRowInColNesting(contentAttrResult.content);
    if (rowWarnings > 0) {
        console.warn(`  \x1b[31m⚠ CẢNH BÁO: Phát hiện ${rowWarnings} khối [row] lồng bên trong [col]! Khuyên dùng CSS Grid thay vì lồng [row].\x1b[0m`);
    }

    const totalFixes = unsuppResult.fixes + commentResult.fixes + fontResult.fixes + linkResult.fixes + flexResult.fixes + hexResult.fixes + badgeResult.fixes + nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    if (totalFixes > 0) {
        console.log(`  \x1b[35m→ Tổng cộng bộ Linter đã tự động tối ưu & sửa ${totalFixes} vấn đề kỹ thuật!\x1b[0m`);
    } else {
        console.log('  \x1b[32m✓ Cú pháp shortcode đạt chuẩn 100%, không cần sửa gì!\x1b[0m');
    }

    return contentAttrResult.content;
}

/**
 * Tự động kiểm tra trang trực tiếp trên frontend xem có bất kỳ shortcode nào bị lộ không
 */
async function verifyLiveFrontend(pageUrl) {
    if (!pageUrl) return;
    try {
        console.log(`\x1b[36m[VERIFICATION] Đang kiểm tra frontend live: ${pageUrl}\x1b[0m`);
        const res = await fetch(pageUrl + '?vbc_verify=' + Date.now());
        if (!res.ok) {
            console.warn(`  \x1b[33m⚠ Không thể tải frontend để verify (HTTP ${res.status}).\x1b[0m`);
            return;
        }
        const html = await res.text();
        const unparsed = html.match(/\[\/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]/g);
        if (unparsed && unparsed.length > 0) {
            console.error(`  \x1b[31m❌ PHÁT HIỆN ${unparsed.length} SHORTCODE BỊ LỘ RA FRONTEND:\x1b[0m`);
            unparsed.slice(0, 10).forEach(s => console.error(`    - ${s}`));
            console.error(`  \x1b[31m👉 Vui lòng kiểm tra lại cấu trúc đóng/mở thẻ và thẻ lồng nhau!\x1b[0m`);
        } else {
            console.log(`  \x1b[32m✓ HOÀN HẢO! 0 shortcode bị lộ ra frontend. Giao diện sạch sẽ 100%!\x1b[0m\n`);
        }
    } catch (e) {
        console.warn(`  \x1b[33m⚠ Bỏ qua bước verify live frontend: ${e.message}\x1b[0m`);
    }
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
                'Content-Type': 'application/json; charset=utf-8',
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

            // 6. Bắt buộc kiểm tra tự động live frontend
            await verifyLiveFrontend(result.url);
        } else {
            throw new Error('Phản hồi từ máy chủ không thành công.');
        }
    } catch (error) {
        console.error(`\x1b[31m✗ Gửi bài lên WordPress thất bại: ${error.message}\x1b[0m`);
        process.exit(1);
    }
}

main();
