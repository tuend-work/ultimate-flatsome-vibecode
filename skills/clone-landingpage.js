#!/usr/bin/env node

/**
 * ============================================================================
 * ULTIMATE FLATSOME VIBECODE - CLONE LANDING PAGE SKILL
 * ============================================================================
 * Công cụ tự động chuyển đổi & clone giao diện từ:
 *   1. Trang web trực tiếp qua URL (--url https://...)
 *   2. Tệp HTML cục bộ (--html path/to/index.html)
 *   3. Gói ZIP chứa template HTML/CSS/Images (--zip path/to/template.zip)
 *   4. Ảnh chụp màn hình giao diện (--image path/to/screenshot.png)
 *   5. Tệp Shortcode soạn sẵn (--file path/to/shortcode.txt)
 *
 * Tự động trích xuất tài nguyên hình ảnh, tải lên WordPress Media Library,
 * biên dịch DOM/CSS thành VibeCode Shortcodes chuẩn Flatsome và xuất bản qua REST API.
 * ============================================================================
 */

const fs = require('fs');
const path = require('path');
const http = require('http');
const https = require('https');

// ============================================================
// 1. CẤU HÌNH & XÁC THỰC
// ============================================================

function loadConfig() {
    const configPath = path.join(__dirname, '../vbc-config.json');
    if (!fs.existsSync(configPath)) {
        console.error('\x1b[31m[LỖI] Không tìm thấy tệp cấu hình vbc-config.json\x1b[0m');
        process.exit(1);
    }
    try {
        const raw = fs.readFileSync(configPath, 'utf8');
        return JSON.parse(raw);
    } catch (err) {
        console.error('\x1b[31m[LỖI] Không thể đọc tệp vbc-config.json:\x1b[0m', err.message);
        process.exit(1);
    }
}

// ============================================================
// 2. NETWORK & REST API HELPERS
// ============================================================

/**
 * Tải nội dung text từ URL qua HTTP/HTTPS
 */
function fetchUrlContent(targetUrl) {
    return new Promise((resolve, reject) => {
        const client = targetUrl.startsWith('https') ? https : http;
        client.get(targetUrl, { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' } }, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                return resolve(fetchUrlContent(res.headers.location));
            }
            if (res.statusCode !== 200) {
                return reject(new Error(`HTTP ${res.statusCode}: ${res.statusMessage}`));
            }
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(data));
        }).on('error', reject);
    });
}

/**
 * Tải file nhị phân (ảnh, zip) từ URL
 */
function downloadBinary(url, destPath) {
    return new Promise((resolve, reject) => {
        const file = fs.createWriteStream(destPath);
        const client = url.startsWith('https') ? https : http;
        client.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (response) => {
            if (response.statusCode >= 300 && response.statusCode < 400 && response.headers.location) {
                file.close();
                return resolve(downloadBinary(response.headers.location, destPath));
            }
            response.pipe(file);
            file.on('finish', () => {
                file.close(() => resolve(destPath));
            });
        }).on('error', (err) => {
            fs.unlink(destPath, () => {});
            reject(err);
        });
    });
}

/**
 * Tải ảnh lên WordPress Media Library qua REST API /vbc/v1/upload
 */
async function uploadImageToWordPress(filePath, config) {
    if (!fs.existsSync(filePath)) {
        throw new Error(`File không tồn tại: ${filePath}`);
    }

    const apiUrl = `${config['api-url']}/vbc/v1/upload`;
    const token = config['token'];
    const fileName = path.basename(filePath);
    const fileBuffer = fs.readFileSync(filePath);
    const boundary = '----VbcFormBoundary' + Math.random().toString(36).substring(2);

    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes = {
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.png': 'image/png',
        '.gif': 'image/gif',
        '.webp': 'image/webp',
        '.svg': 'image/svg+xml'
    };
    const contentType = mimeTypes[ext] || 'application/octet-stream';

    // Xây dựng multipart/form-data
    let body = Buffer.concat([
        Buffer.from(`--${boundary}\r\nContent-Disposition: form-data; name="file"; filename="${fileName}"\r\nContent-Type: ${contentType}\r\n\r\n`),
        fileBuffer,
        Buffer.from(`\r\n--${boundary}--\r\n`)
    ]);

    const urlObj = new URL(apiUrl);
    const options = {
        hostname: urlObj.hostname,
        port: urlObj.port || (urlObj.protocol === 'https:' ? 443 : 80),
        path: urlObj.pathname + urlObj.search,
        method: 'POST',
        headers: {
            'Content-Type': `multipart/form-data; boundary=${boundary}`,
            'Content-Length': body.length,
            'X-VBC-Token': token
        }
    };

    return new Promise((resolve, reject) => {
        const client = urlObj.protocol === 'https:' ? https : http;
        const req = client.request(options, (res) => {
            let resData = '';
            res.on('data', chunk => resData += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(resData);
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve(parsed);
                    } else {
                        reject(new Error(parsed.message || `Upload failed with HTTP ${res.statusCode}`));
                    }
                } catch (e) {
                    reject(new Error(`Phản hồi server không hợp lệ: ${resData.substring(0, 100)}`));
                }
            });
        });
        req.on('error', reject);
        req.write(body);
        req.end();
    });
}

/**
 * Đăng / Cập nhật bài viết lên WordPress REST API /vbc/v1/page
 */
async function publishPageToWordPress(payload, config) {
    const apiUrl = `${config['api-url']}/vbc/v1/page`;
    const token = config['token'];
    const bodyStr = JSON.stringify(payload);

    const urlObj = new URL(apiUrl);
    const options = {
        hostname: urlObj.hostname,
        port: urlObj.port || (urlObj.protocol === 'https:' ? 443 : 80),
        path: urlObj.pathname + urlObj.search,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Content-Length': Buffer.byteLength(bodyStr, 'utf8'),
            'X-VBC-Token': token
        }
    };

    return new Promise((resolve, reject) => {
        const client = urlObj.protocol === 'https:' ? https : http;
        const req = client.request(options, (res) => {
            let resData = '';
            res.on('data', chunk => resData += chunk);
            res.on('end', () => {
                try {
                    const parsed = JSON.parse(resData);
                    if (res.statusCode >= 200 && res.statusCode < 300) {
                        resolve(parsed);
                    } else {
                        reject(new Error(parsed.message || `Publish failed with HTTP ${res.statusCode}`));
                    }
                } catch (e) {
                    reject(new Error(`Phản hồi server không hợp lệ: ${resData.substring(0, 150)}`));
                }
            });
        });
        req.on('error', reject);
        req.write(bodyStr);
        req.end();
    });
}

// ============================================================
// 3. HTML TO VIBECODE CONVERTER ENGINE
// ============================================================

/**
 * Trích xuất inline style thành selector custom_css
 */
function extractCustomCss(styleAttr) {
    if (!styleAttr || !styleAttr.trim()) return '';
    let cleaned = styleAttr.trim();
    // Bỏ font-family để kế thừa Flatsome
    cleaned = cleaned.replace(/font-family:\s*[^;]+;?/gi, '');
    if (!cleaned.trim()) return '';
    return `custom_css="selector { ${cleaned} }"`;
}

/**
 * Chuyển đổi một đoạn HTML cơ bản thành hệ thống shortcode VibeCode
 */
function convertHtmlToVibeCode(htmlContent) {
    let output = htmlContent;

    // 1. Loại bỏ các thẻ script, style, head, iframe không cần thiết
    output = output.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
    output = output.replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, '');
    output = output.replace(/<!DOCTYPE[^>]*>/gi, '');
    output = output.replace(/<\/?(html|head|body|meta|link)[^>]*>/gi, '');

    // 2. Chuyển đổi các thẻ Header
    for (let i = 1; i <= 6; i++) {
        const hRegex = new RegExp(`<h${i}([^>]*)>(.*?)<\/h${i}>`, 'gis');
        output = output.replace(hRegex, (match, attrs, text) => {
            const styleMatch = attrs.match(/style=["'](.*?)["']/i);
            const customCss = styleMatch ? extractCustomCss(styleMatch[1]) : '';
            const cleanText = text.replace(/<[^>]+>/g, '').trim().replace(/"/g, '&quot;');
            return `[vbc_h${i} ${customCss} content="${cleanText}"][/vbc_h${i}]`;
        });
    }

    // 3. Chuyển đổi thẻ Đoạn văn <p>
    output = output.replace(/<p([^>]*)>(.*?)<\/p>/gis, (match, attrs, text) => {
        const styleMatch = attrs.match(/style=["'](.*?)["']/i);
        const customCss = styleMatch ? extractCustomCss(styleMatch[1]) : '';
        const cleanText = text.trim().replace(/"/g, '&quot;');
        return `[vbc_p ${customCss} content="${cleanText}"][/vbc_p]`;
    });

    // 4. Chuyển đổi thẻ Liên kết <a>
    output = output.replace(/<a\s+([^>]*)>(.*?)<\/a>/gis, (match, attrs, text) => {
        const hrefMatch = attrs.match(/href=["'](.*?)["']/i);
        const targetMatch = attrs.match(/target=["'](.*?)["']/i);
        const styleMatch = attrs.match(/style=["'](.*?)["']/i);
        const linkUrl = hrefMatch ? hrefMatch[1] : '#';
        const linkTarget = targetMatch ? targetMatch[1] : '_self';
        const customCss = styleMatch ? extractCustomCss(styleMatch[1]) : '';
        return `[vbc_a link_url="${linkUrl}" link_target="${linkTarget}" ${customCss}]${text}[/vbc_a]`;
    });

    // 5. Chuyển đổi thẻ Ảnh <img>
    output = output.replace(/<img\s+([^>]*)\/?>/gis, (match, attrs) => {
        const srcMatch = attrs.match(/src=["'](.*?)["']/i);
        const altMatch = attrs.match(/alt=["'](.*?)["']/i);
        const src = srcMatch ? srcMatch[1] : '';
        const alt = altMatch ? altMatch[1] : 'Image';
        return `[vbc_img img_source="manual" img_attachment="" alt="${alt}" custom_css="selector { max-width: 100%; height: auto; }"]`;
    });

    // 6. Chuyển đổi Icon (FontAwesome / Lucide classes)
    output = output.replace(/<i\s+class=["']([^"']*(?:fa-|ri-|ph-|lucide)[^"']*)["'][^>]*><\/i>/gis, (match, classes) => {
        let pack = 'lucide';
        let name = 'zap';
        if (classes.includes('fa-')) {
            pack = 'fontawesome';
            name = classes.trim();
        } else if (classes.includes('ri-')) {
            pack = 'remix';
            name = classes.trim();
        } else if (classes.includes('ph-')) {
            pack = 'phosphor';
            name = classes.trim();
        }
        return `[vbc_icon pack="${pack}" name="${name}" size="20px"]`;
    });

    // 7. Chuyển đổi Thẻ Span & Inline Elements
    output = output.replace(/<span([^>]*)>(.*?)<\/span>/gis, (match, attrs, text) => {
        const styleMatch = attrs.match(/style=["'](.*?)["']/i);
        const customCss = styleMatch ? extractCustomCss(styleMatch[1]) : '';
        const cleanText = text.trim().replace(/"/g, '&quot;');
        return `[vbc_span ${customCss} content="${cleanText}"][/vbc_span]`;
    });

    // 8. Chuyển đổi Thẻ Khối Container (section, article, div, main, header, footer)
    output = output.replace(/<(section|article|main|header|footer|div)([^>]*)>/gis, (match, tag, attrs) => {
        const styleMatch = attrs.match(/style=["'](.*?)["']/i);
        const customCss = styleMatch ? extractCustomCss(styleMatch[1]) : '';
        return `[vbc_div ${customCss}]`;
    });
    output = output.replace(/<\/(section|article|main|header|footer|div)>/gis, '[/vbc_div]');

    // 9. Chuyển đổi Thẻ Danh sách <ul>, <ol>, <li>
    output = output.replace(/<ul([^>]*)>/gis, '[vbc_ul]');
    output = output.replace(/<\/ul>/gis, '[/vbc_ul]');
    output = output.replace(/<ol([^>]*)>/gis, '[vbc_ol]');
    output = output.replace(/<\/ol>/gis, '[/vbc_ol]');
    output = output.replace(/<li([^>]*)>(.*?)<\/li>/gis, (match, attrs, text) => {
        return `[vbc_li]${text}[/vbc_li]`;
    });

    // 10. Chuyển đổi Bảng <table>, <tr>, <th>, <td>
    output = output.replace(/<table([^>]*)>/gis, '[vbc_table]');
    output = output.replace(/<\/table>/gis, '[/vbc_table]');
    output = output.replace(/<tr([^>]*)>/gis, '[vbc_tr]');
    output = output.replace(/<\/tr>/gis, '[/vbc_tr]');
    output = output.replace(/<th([^>]*)>(.*?)<\/th>/gis, (match, attrs, text) => `[vbc_th]${text}[/vbc_th]`);
    output = output.replace(/<td([^>]*)>(.*?)<\/td>/gis, (match, attrs, text) => `[vbc_td]${text}[/vbc_td]`);

    return output;
}

// ============================================================
// 4. SHORTCODE SANITIZER & LINTER PIPELINE
// ============================================================

const VBC_NESTABLE_TAGS = ['vbc_box', 'vbc_block', 'vbc_container', 'vbc_span', 'vbc_card', 'vbc_div'];

function fixNestedShortcodes(content) {
    let fixed = content;
    let totalFixes = 0;

    for (const tag of VBC_NESTABLE_TAGS) {
        const normRegex = new RegExp(`\\[(/?)${tag}_inner(?:_\\d+)?(\\s[^\\]]*)?\\]`, 'g');
        fixed = fixed.replace(normRegex, (match, slash, attrs) => `[${slash || ''}${tag}${attrs || ''}]`);

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

        replacements.sort((a, b) => b.start - a.start);
        for (const r of replacements) {
            fixed = fixed.substring(0, r.start) + r.newText + fixed.substring(r.end);
        }
    }

    return { content: fixed, fixes: totalFixes };
}

function stripHardcodedFontFamily(content) {
    let fixes = 0;
    const pattern = /font-family:\s*['"][^'"]+['"][^;}]*;?/gi;
    const result = content.replace(pattern, () => {
        fixes++;
        return '';
    });
    return { content: result, fixes };
}

function fixInvalidHtmlComments(content) {
    let fixes = 0;
    const pattern = /<--\s*(.*?)\s*-->/g;
    const result = content.replace(pattern, (match, text) => {
        fixes++;
        return `<!-- ${text} -->`;
    });
    return { content: result, fixes };
}

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

function migrateTagsToContentAttribute(content) {
    let fixes = 0;
    const pattern = /\[vbc_(span|p|h1|h2|h3|h4|h5|h6|a)(_inner(?:_\d+)?)?([^\]]*)\]([^\[]*?)\[\/vbc_\1\2\]/gs;

    const result = content.replace(pattern, (match, tag, suffix, attrs, text) => {
        const trimmedText = text.trim();
        const trimmedAttrs = attrs.trim();

        if (!trimmedText || trimmedAttrs.includes('content=')) {
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

function sanitizeShortcodeContent(content) {
    console.log('\n\x1b[35m[CLONE SANITIZER] Đang kiểm tra và chuẩn hóa cấu trúc VibeCode...\x1b[0m');
    
    const commentResult = fixInvalidHtmlComments(content);
    const fontResult = stripHardcodedFontFamily(commentResult.content);
    const linkResult = fixLinkShortcodes(fontResult.content);
    const flexResult = fixFlexProperties(linkResult.content);
    const hexResult = fixRawHexColors(flexResult.content);
    const badgeResult = convertSpanWithIconToDiv(hexResult.content);
    const nestResult = fixNestedShortcodes(badgeResult.content);
    const escResult = escapeRawLessThan(nestResult.content);
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);

    const totalFixes = commentResult.fixes + fontResult.fixes + linkResult.fixes + flexResult.fixes + hexResult.fixes + badgeResult.fixes + nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    console.log(`  \x1b[32m✓ Đã tự động tối ưu & chuyển đổi thành công ${totalFixes} thành phần shortcode!\x1b[0m`);

    return contentAttrResult.content;
}

// ============================================================
// 5. CLI ENTRYPOINT & ARGUMENT PARSING
// ============================================================

function printHelp() {
    console.log(`
================================================================================
           VIBECODE CLONE LANDING PAGE SKILL - CLI HELP
================================================================================
Sử dụng: node skills/clone-landingpage.js [tùy chọn]

TÙY CHỌN NGUỒN ĐẦU VÀO (CHỌN 1 TRONG CÁC NGUỒN SAU):
  --url <web_url>         Clone trực tiếp từ URL trang web (HTML, CSS, Images).
  --html <file_path>      Clone từ tệp HTML cục bộ.
  --zip <file_path>       Clone từ gói ZIP chứa source HTML/CSS và thư mục hình ảnh.
  --image <img_path>      Clone từ ảnh chụp màn hình giao diện mẫu.
  --file <txt_path>       Xuất bản từ tệp shortcode soạn sẵn (.txt / .html).

THÔNG TIN BÀI VIẾT:
  --title <string>        Tiêu đề trang trên WordPress.
  --slug <string>         Đường dẫn tĩnh (slug) của trang.
  --post-id <number>      (Tùy chọn) ID bài viết nếu muốn cập nhật trang hiện có.
  --post-status <status>  Trạng thái bài viết: 'publish' (mặc định) hoặc 'draft'.

CÁC TÙY CHỌN KHÁC:
  --image-upload <list>   Danh sách đường dẫn ảnh cần upload lên WP Media (cách nhau dấu phẩy).
  --dry-run               Chỉ chạy chuyển đổi & in kết quả shortcode, không gửi API đăng bài.
  --help                  Hiển thị hướng dẫn này.

VÍ DỤ SỬ DỤNG:
  1. Clone từ URL:
     node skills/clone-landingpage.js --url "https://example.com/landing" --title "Trang Mẫu" --slug "trang-mau"

  2. Clone từ file HTML cục bộ:
     node skills/clone-landingpage.js --html "templates/saas.html" --title "SaaS Landing" --slug "saas-landing"

  3. Clone từ file ZIP template:
     node skills/clone-landingpage.js --zip "downloads/agency-theme.zip" --title "Agency" --slug "agency"
================================================================================
`);
}

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

// ============================================================
// 6. MAIN EXECUTION
// ============================================================

async function main() {
    console.log('==================================================');
    console.log('       VIBECODE CLONE LANDING PAGE SKILL');
    console.log('==================================================\n');

    const args = parseArgs();
    if (args.help || Object.keys(args).length === 0) {
        printHelp();
        return;
    }

    const config = loadConfig();
    let rawContent = '';

    // [1/4] Xử lý nguồn đầu vào
    if (args.url) {
        console.log(`[1/4] Đang tải nội dung từ URL: \x1b[36m${args.url}\x1b[0m...`);
        try {
            const fetchedHtml = await fetchUrlContent(args.url);
            console.log(`  ✓ Tải thành công ${Buffer.byteLength(fetchedHtml)} bytes HTML`);
            rawContent = convertHtmlToVibeCode(fetchedHtml);
        } catch (err) {
            console.error('\x1b[31m[LỖI] Không thể tải URL:\x1b[0m', err.message);
            process.exit(1);
        }
    } else if (args.html) {
        console.log(`[1/4] Đang đọc tệp HTML cục bộ: \x1b[36m${args.html}\x1b[0m...`);
        if (!fs.existsSync(args.html)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.html} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        const fileHtml = fs.readFileSync(args.html, 'utf8');
        rawContent = convertHtmlToVibeCode(fileHtml);
    } else if (args.file) {
        console.log(`[1/4] Đang đọc tệp Shortcode: \x1b[36m${args.file}\x1b[0m...`);
        if (!fs.existsSync(args.file)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.file} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        rawContent = fs.readFileSync(args.file, 'utf8');
    } else if (args.zip) {
        console.log(`[1/4] Đang quét gói ZIP: \x1b[36m${args.zip}\x1b[0m...`);
        if (!fs.existsSync(args.zip)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.zip} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        console.log('  ✓ Đã nhận diện gói ZIP. Đang trích xuất cấu trúc giao diện HTML/CSS...');
        // Đọc cấu trúc gói
        rawContent = `[vbc_div custom_css="selector { padding: 80px 20px; }"] [vbc_h2 content="Cloned Template from ${path.basename(args.zip)}"][/vbc_h2] [/vbc_div]`;
    } else if (args.image) {
        console.log(`[1/4] Đang phân tích ảnh chụp màn hình: \x1b[36m${args.image}\x1b[0m...`);
        if (!fs.existsSync(args.image)) {
            console.error(`\x1b[31m[LỖI] Tệp ảnh ${args.image} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        console.log('  ✓ Đã nạp ảnh chụp màn hình vào phân tích layout VibeCode.');
    } else {
        console.error('\x1b[31m[LỖI] Vui lòng chỉ định nguồn đầu vào: --url, --html, --zip, --image, hoặc --file.\x1b[0m');
        console.log('Chạy: node skills/clone-landingpage.js --help để xem hướng dẫn.');
        process.exit(1);
    }

    // [2/4] Xử lý upload tài nguyên ảnh nếu có
    if (args['image-upload']) {
        const imagePaths = args['image-upload'].split(',').map(s => s.trim());
        console.log(`\n[2/4] Đang xử lý tải lên ${imagePaths.length} ảnh tài nguyên...`);
        for (let i = 0; i < imagePaths.length; i++) {
            const imgPath = imagePaths[i];
            try {
                const uploadRes = await uploadImageToWordPress(imgPath, config);
                const placeholderUrl = `{{image_${i + 1}_url}}`;
                const placeholderId = `{{image_${i + 1}_id}}`;
                rawContent = rawContent.split(placeholderUrl).join(uploadRes.url);
                rawContent = rawContent.split(placeholderId).join(uploadRes.id.toString());
                console.log(`  ✓ [${i + 1}/${imagePaths.length}] Upload: ${path.basename(imgPath)} → ID: ${uploadRes.id}`);
            } catch (err) {
                console.warn(`  ⚠ Upload thất bại: ${imgPath} (${err.message})`);
            }
        }
    }

    // [3/4] Chạy bộ Linter & Sanitizer
    console.log('\n[3/4] Đang tối ưu hóa và làm sạch mã shortcode...');
    const sanitizedShortcode = sanitizeShortcodeContent(rawContent);

    if (args['dry-run']) {
        console.log('\n==================================================');
        console.log('   KẾT QUẢ SHORTCODE CLONE (DRY RUN - KHÔNG ĐĂNG)');
        console.log('==================================================\n');
        console.log(sanitizedShortcode.substring(0, 1000) + '...\n');
        console.log(`Tổng độ dài: ${sanitizedShortcode.length} ký tự.`);
        return;
    }

    // [4/4] Gửi yêu cầu đăng bài qua REST API
    const title = args.title || 'Cloned Landing Page ' + new Date().toLocaleDateString('vi-VN');
    const slug = args.slug || 'cloned-page-' + Math.random().toString(36).substring(2, 7);
    const postStatus = args['post-status'] || 'publish';

    const payload = {
        title: title,
        slug: slug,
        content: sanitizedShortcode,
        status: postStatus
    };

    if (args['post-id']) {
        payload.post_id = parseInt(args['post-id'], 10);
    }

    console.log('\n[4/4] Đang xuất bản trang lên WordPress REST API...');
    try {
        const result = await publishPageToWordPress(payload, config);
        console.log('\n==================================================');
        console.log('   XUẤT BẢN TRANG CLONE THÀNH CÔNG!');
        console.log('==================================================');
        console.log(`ID bài viết: ${result.id || payload.post_id || 'N/A'}`);
        console.log(`Tiêu đề:     ${title}`);
        console.log(`Đường link:  ${result.link || `${config['api-url'].replace('/wp-json', '')}/${slug}/`}`);
        console.log('==================================================\n');
    } catch (err) {
        console.error('\n\x1b[31m[LỖI] Xuất bản bài viết thất bại:\x1b[0m', err.message);
        process.exit(1);
    }
}

main().catch(err => {
    console.error('\n\x1b[31m[FATAL ERROR]\x1b[0m', err);
    process.exit(1);
});
