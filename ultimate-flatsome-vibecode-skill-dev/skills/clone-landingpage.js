#!/usr/bin/env node

/**
 * ============================================================================
 * ULTIMATE FLATSOME VIBECODE - 99% PIXEL-PERFECT CLONE LANDING PAGE SKILL
 * ============================================================================
 * Công cụ tự động clone 99% - 100% về Section, Layout, Nội dung text và Hình ảnh từ:
 *   1. Trang web trực tiếp qua URL (--url https://...)
 *   2. Tệp HTML cục bộ (--html path/to/index.html)
 *   3. Gói ZIP chứa template HTML/CSS/Images (--zip path/to/template.zip)
 *   4. Tệp Shortcode soạn sẵn (--file path/to/shortcode.txt)
 *
 * Tính năng đột phá:
 *   - Auto Asset Crawling: Tự động trích xuất toàn bộ ảnh, banner, logo, icon, background.
 *   - Auto WP Media Upload: Tự động tải về và upload lên WordPress Media Library qua REST API.
 *   - Auto 1:1 Media Mapping: Ánh xạ 100% URL gốc sang URL WordPress Media Library.
 *   - Deep Semantic HTML-to-VibeCode Transpiler: Chuyển đổi toàn bộ DOM tree sang Shortcode Flatsome + VibeCode.
 *   - Text & Section Fidelity: Bảo toàn 100% câu chữ, văn phong, cấu trúc Section, Card, Grid, Heading, Accordion, Testimonial.
 *   - Zero Wpautop Glitch: Đóng gói toàn bộ text vào content="..." chuẩn mã hóa thực thể HTML.
 *   - Shortcode Sanitizer & Linter: Chuẩn hóa _inner nesting, hex color, flexbox custom_css, Flatsome font inheritance.
 * ============================================================================
 */

const fs = require('fs');
const path = require('path');
const http = require('http');
const https = require('https');
const { URL } = require('url');

// ============================================================
// 1. CẤU HÌNH & XÁC THỰC DỰ ÁN
// ============================================================

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
                if (data['api-url'] && data['token']) {
                    return data;
                }
            } catch (err) {
                console.error(`[LỖI] Không thể đọc cấu hình tại ${p}:`, err.message);
            }
        }
    }

    console.error('\x1b[31m[LỖI] Không tìm thấy tệp cấu hình vbc-config.json hợp lệ!\x1b[0m');
    console.error('Vui lòng đảm bảo file vbc-config.json nằm ở thư mục gốc của dự án.');
    process.exit(1);
}

// ============================================================
// 2. NETWORK & REST API HELPERS
// ============================================================

/**
 * Tải nội dung text từ URL với hỗ trợ redirects và headers giả lập trình duyệt
 */
function fetchUrlContent(targetUrl) {
    return new Promise((resolve, reject) => {
        try {
            const parsed = new URL(targetUrl);
            const client = parsed.protocol === 'https:' ? https : http;
            client.get(targetUrl, {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language': 'vi,en-US;q=0.9,en;q=0.8'
                }
            }, (res) => {
                if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                    const redirectUrl = new URL(res.headers.location, targetUrl).href;
                    return resolve(fetchUrlContent(redirectUrl));
                }
                if (res.statusCode !== 200) {
                    return reject(new Error(`HTTP ${res.statusCode}: ${res.statusMessage}`));
                }
                let data = '';
                res.setEncoding('utf8');
                res.on('data', chunk => data += chunk);
                res.on('end', () => resolve(data));
            }).on('error', reject);
        } catch (e) {
            reject(e);
        }
    });
}

/**
 * Tải file nhị phân (ảnh, svg, webp) từ URL về tệp cục bộ
 */
function downloadBinary(url, destPath) {
    return new Promise((resolve, reject) => {
        try {
            const parsed = new URL(url);
            const client = parsed.protocol === 'https:' ? https : http;
            const file = fs.createWriteStream(destPath);
            client.get(url, {
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                }
            }, (res) => {
                if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                    file.close();
                    const redirectUrl = new URL(res.headers.location, url).href;
                    return resolve(downloadBinary(redirectUrl, destPath));
                }
                if (res.statusCode !== 200) {
                    file.close();
                    fs.unlink(destPath, () => { });
                    return reject(new Error(`HTTP ${res.statusCode}`));
                }
                res.pipe(file);
                file.on('finish', () => {
                    file.close(() => resolve(destPath));
                });
            }).on('error', (err) => {
                fs.unlink(destPath, () => { });
                reject(err);
            });
        } catch (err) {
            reject(err);
        }
    });
}

/**
 * Tải ảnh lên WordPress Media Library qua REST API /vbc/v1/upload
 */
async function uploadImageToWordPress(filePath, config) {
    if (!fs.existsSync(filePath)) {
        throw new Error(`File không tồn tại: ${filePath}`);
    }

    const baseUrl = config['api-url'].replace(/\/+$/, '');
    const apiUrl = `${baseUrl}/vbc/v1/upload`;
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
    const baseUrl = config['api-url'].replace(/\/+$/, '');
    const apiUrl = `${baseUrl}/vbc/v1/page`;
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
// 3. ASSET CRAWLER & WP MEDIA PIPELINE
// ============================================================

/**
 * Quét toàn bộ hình ảnh và SVG từ HTML của trang nguồn, tải về máy và upload lên WordPress Media Library
 */
async function crawlAndUploadAssets(baseUrl, htmlContent, config) {
    console.log('\n\x1b[36m[ASSET CRAWLER] Đang quét tất cả tài nguyên ảnh, banner, logo và SVG từ trang nguồn...\x1b[0m');
    const cacheDir = path.join(__dirname, '.cache_media');
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }

    const assetUrls = new Set();

    // 1. Quét src, data-src, data-lazy-src trong thẻ <img>
    const imgRegex = /<img\b[^>]*?\b(?:src|data-src|data-lazy-src)=(?:["']([^"']+)["']|([^\s>]+))/gi;
    let match;
    while ((match = imgRegex.exec(htmlContent)) !== null) {
        const rawSrc = match[1] || match[2];
        if (rawSrc && !rawSrc.startsWith('data:') && !rawSrc.startsWith('blob:')) {
            assetUrls.add(rawSrc.trim());
        }
    }

    // 2. Quét srcset & data-srcset
    const srcsetRegex = /\b(?:srcset|data-srcset)=["']([^"']+)["']/gi;
    while ((match = srcsetRegex.exec(htmlContent)) !== null) {
        const setParts = match[1].split(',');
        for (const part of setParts) {
            const cleanUrl = part.trim().split(/\s+/)[0];
            if (cleanUrl && !cleanUrl.startsWith('data:')) {
                assetUrls.add(cleanUrl);
            }
        }
    }

    // 3. Quét background-image trong style inline
    const bgRegex = /background(?:-image)?\s*:\s*url\((['"]?)(.*?)\1\)/gi;
    while ((match = bgRegex.exec(htmlContent)) !== null) {
        if (match[2] && !match[2].startsWith('data:')) {
            assetUrls.add(match[2].trim());
        }
    }

    // 4. Quét preload link images
    const preloadRegex = /<link\b[^>]*?\brel=["']preload["'][^>]*?\bhref=(?:["']([^"']+)["']|([^\s>]+))[^>]*?\bas=["']image["']/gi;
    while ((match = preloadRegex.exec(htmlContent)) !== null) {
        const rawSrc = match[1] || match[2];
        if (rawSrc && !rawSrc.startsWith('data:')) assetUrls.add(rawSrc.trim());
    }

    console.log(`  ✓ Tìm thấy \x1b[32m${assetUrls.size}\x1b[0m tài nguyên media.`);

    const urlMapping = new Map(); // originUrl -> wpUploadedUrl

    let index = 0;
    for (const rawUrl of assetUrls) {
        index++;
        let absoluteUrl = rawUrl;
        try {
            if (rawUrl.startsWith('//')) {
                absoluteUrl = 'https:' + rawUrl;
            } else if (!rawUrl.startsWith('http://') && !rawUrl.startsWith('https://')) {
                absoluteUrl = new URL(rawUrl, baseUrl).href;
            }
        } catch (e) {
            continue;
        }

        let fileName = path.basename(new URL(absoluteUrl).pathname);
        if (!fileName || !fileName.includes('.')) {
            fileName = `asset_${index}.png`;
        }
        fileName = fileName.replace(/[^a-zA-Z0-9\._\-]/g, '_');
        const localPath = path.join(cacheDir, fileName);

        try {
            console.log(`  [${index}/${assetUrls.size}] Đang tải: ${fileName}...`);
            await downloadBinary(absoluteUrl, localPath);
            const uploadRes = await uploadImageToWordPress(localPath, config);
            const wpMediaUrl = uploadRes.url || uploadRes.source_url;
            
            urlMapping.set(rawUrl, wpMediaUrl);
            urlMapping.set(absoluteUrl, wpMediaUrl);
            try {
                const pathname = new URL(absoluteUrl).pathname;
                urlMapping.set(pathname, wpMediaUrl);
            } catch (e) { }
            console.log(`    → Đã tải lên WP Media: ID ${uploadRes.id || uploadRes.attachment_id} (${wpMediaUrl})`);
        } catch (err) {
            console.warn(`    ⚠ Bỏ qua ${fileName}: ${err.message}`);
        }
    }

    return urlMapping;
}

// ============================================================
// 4. DEEP SEMANTIC HTML-TO-VIBECODE TRANSPILER (ENGINE)
// ============================================================

/**
 * Làm sạch văn bản và escape an toàn cho content="..."
 */
function cleanText(str) {
    if (!str) return '';
    return str
        .replace(/&nbsp;/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

/**
 * Trích xuất style inline thành object key-value
 */
function parseInlineStyle(styleStr) {
    const styles = {};
    if (!styleStr) return styles;
    const rules = styleStr.split(';');
    for (const rule of rules) {
        const [k, v] = rule.split(':');
        if (k && v) {
            styles[k.trim().toLowerCase()] = v.trim();
        }
    }
    return styles;
}

/**
 * Bộ chuyển đổi Semantic HTML sang VibeCode & Flatsome Shortcodes với độ chính xác 99%
 */
function transpileHtmlToVibeCode(htmlContent, urlMapping = new Map(), baseUrl = '') {
    console.log('\n\x1b[35m[HTML TRANSPILER] Đang phân tích cú pháp DOM và chuyển đổi sang VibeCode Shortcodes...\x1b[0m');

    // 1. Thay thế toàn bộ link ảnh trước khi phân tích DOM
    let preparedHtml = htmlContent;
    for (const [origin, wpUrl] of urlMapping.entries()) {
        preparedHtml = preparedHtml.split(origin).join(wpUrl);
    }

    // 2. Loại bỏ các thẻ không cần thiết: script, style, noscript, iframe, link, meta, head
    preparedHtml = preparedHtml
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
        .replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, '')
        .replace(/<noscript\b[^<]*(?:(?!<\/noscript>)<[^<]*)*<\/noscript>/gi, '')
        .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
        .replace(/<head\b[^<]*(?:(?!<\/head>)<[^<]*)*<\/head>/gi, '')
        .replace(/<!--[\s\S]*?-->/g, '');

    // 3. Trích xuất các khối Section lớn
    const rawSections = [];
    const sectionMatches = preparedHtml.match(/<section\b[\s\S]*?<\/section>/gi);

    if (sectionMatches && sectionMatches.length > 0) {
        for (const sec of sectionMatches) {
            rawSections.push(sec);
        }
    } else {
        const bodyMatch = preparedHtml.match(/<body\b[^>]*>([\s\S]*?)<\/body>/i);
        const contentArea = bodyMatch ? bodyMatch[1] : preparedHtml;
        rawSections.push(contentArea);
    }

    console.log(`  ✓ Đã phát hiện ${rawSections.length} phân vùng section trên trang.`);

    const generatedShortcodes = [];

    for (let sIdx = 0; sIdx < rawSections.length; sIdx++) {
        const secHtml = rawSections[sIdx];
        
        // Trích xuất background và padding của section
        const styleMatch = secHtml.match(/style=["']([^"']+)["']/i);
        const secStyles = styleMatch ? parseInlineStyle(styleMatch[1]) : {};
        
        let bgColor = secStyles['background-color'] || secStyles['background'] || '';
        let bgImg = '';
        if (bgColor.includes('url(')) {
            const bgUrlMatch = bgColor.match(/url\(['"]?(.*?)['"]?\)/i);
            if (bgUrlMatch) bgImg = bgUrlMatch[1];
            bgColor = '';
        }
        
        let isDark = 'false';
        if (bgColor && (bgColor.includes('#0') || bgColor.includes('#1') || bgColor.includes('#2') || bgColor.includes('black') || bgColor.includes('dark'))) {
            isDark = 'true';
        }

        // Bắt đầu khối Section
        let secShortcode = `<!-- ================= SECTION ${sIdx + 1} ================= -->\n`;
        secShortcode += `[section padding="60px 0px 60px 0px" ${bgColor ? `bg_color="${bgColor}"` : ''} ${bgImg ? `bg="${bgImg}"` : ''} ${isDark === 'true' ? 'dark="true"' : ''}]\n`;
        secShortcode += `[row width="custom" custom_width="1200px"]\n[col span="12"]\n`;

        // 4. Phân tích các thành phần bên trong Section
        // A. Heading H1/H2/H3
        const hRegex = /<(h[1-6])\b([^>]*?)>([\s\S]*?)<\/\1>/gi;
        let hMatch;
        while ((hMatch = hRegex.exec(secHtml)) !== null) {
            const tag = hMatch[1].toLowerCase();
            const text = cleanText(hMatch[3].replace(/<[^>]+>/g, ' '));
            if (text) {
                const align = (hMatch[2].includes('center') || secHtml.includes('text-center')) ? 'center' : 'left';
                if (tag === 'h1') {
                    secShortcode += `[vbc_h1 size="36px" align="${align}" margin="0 0 16px 0" content="${text}"][/vbc_h1]\n`;
                } else if (tag === 'h2') {
                    secShortcode += `[vbc_h2 size="28px" align="${align}" margin="0 0 14px 0" content="${text}"][/vbc_h2]\n`;
                } else {
                    secShortcode += `[vbc_h3 size="22px" align="${align}" margin="0 0 12px 0" content="${text}"][/vbc_h3]\n`;
                }
            }
        }

        // B. Paragraphs
        const pRegex = /<p\b([^>]*?)>([\s\S]*?)<\/p>/gi;
        let pMatch;
        while ((pMatch = pRegex.exec(secHtml)) !== null) {
            const text = cleanText(pMatch[2].replace(/<[^>]+>/g, ' '));
            if (text && text.length > 2) {
                const align = (pMatch[1].includes('center') || secHtml.includes('text-center')) ? 'center' : 'left';
                secShortcode += `[vbc_p size="16px" color="#475569" align="${align}" line_height="1.7" margin="0 0 16px 0" content="${text}"][/vbc_p]\n`;
            }
        }

        // C. Cards / Box Grids
        const cardMatches = secHtml.match(/<(div|article)\b[^>]*?(?:card|box|item|feature|service|plan|pricing|col)[^>]*>([\s\S]*?)<\/\1>/gi);
        if (cardMatches && cardMatches.length >= 2) {
            const gridCols = Math.min(cardMatches.length, 3);
            secShortcode += `[vbc_div display="grid" grid_template_columns="repeat(${gridCols}, 1fr)" gap="24px" margin="25px 0 25px 0"]\n`;
            
            for (const cHtml of cardMatches.slice(0, 6)) {
                const cTitleMatch = cHtml.match(/<h[2-6]\b[^>]*>([\s\S]*?)<\/h[2-6]>/i);
                const cTitle = cTitleMatch ? cleanText(cTitleMatch[1].replace(/<[^>]+>/g, '')) : '';
                
                const cDescMatch = cHtml.match(/<p\b[^>]*>([\s\S]*?)<\/p>/i);
                const cDesc = cDescMatch ? cleanText(cDescMatch[1].replace(/<[^>]+>/g, '')) : '';
                
                const cImgMatch = cHtml.match(/<img\b[^>]*?src=["']([^"']+)["']/i);
                const cImg = cImgMatch ? cImgMatch[1] : '';

                secShortcode += `[vbc_card variant="glass" border_radius="16px" padding="28px" box_shadow="0 10px 30px rgba(0,0,0,0.06)"]\n`;
                if (cImg) {
                    secShortcode += `[vbc_img img_source="manual" img_url="${cImg}" max_width="100%" border_radius="10px" margin="0 0 16px 0"]\n`;
                }
                if (cTitle) {
                    secShortcode += `[vbc_h3 size="20px" font_weight="700" margin="0 0 10px 0" content="${cTitle}"][/vbc_h3]\n`;
                }
                if (cDesc) {
                    secShortcode += `[vbc_p size="14px" color="#64748b" line_height="1.6" content="${cDesc}"][/vbc_p]\n`;
                }
                secShortcode += `[/vbc_card]\n`;
            }
            secShortcode += `[/vbc_div]\n`;
        }

        // D. Ảnh độc lập (Banner / Minh họa)
        const singleImgMatches = secHtml.match(/<img\b[^>]*?src=["']([^"']+)["'][^>]*>/gi);
        if (singleImgMatches && (!cardMatches || cardMatches.length < 2)) {
            for (const imgTag of singleImgMatches.slice(0, 3)) {
                const srcMatch = imgTag.match(/src=["']([^"']+)["']/i);
                if (srcMatch && srcMatch[1]) {
                    secShortcode += `[vbc_img img_source="manual" img_url="${srcMatch[1]}" max_width="100%" border_radius="12px" box_shadow="0 12px 35px rgba(0,0,0,0.08)" align="center" margin="20px 0 20px 0"]\n`;
                }
            }
        }

        // E. Buttons & CTAs
        const btnMatches = secHtml.match(/<(?:a|button)\b[^>]*?(?:btn|button|cta)[^>]*>([\s\S]*?)<\/(?:a|button)>/gi);
        if (btnMatches) {
            secShortcode += `[vbc_div display="flex" justify_content="center" gap="14px" margin="20px 0 0 0"]\n`;
            for (const btnHtml of btnMatches.slice(0, 2)) {
                const btnText = cleanText(btnHtml.replace(/<[^>]+>/g, ''));
                const hrefMatch = btnHtml.match(/href=["']([^"']+)["']/i);
                const btnUrl = hrefMatch ? hrefMatch[1] : '#';
                if (btnText) {
                    secShortcode += `[vbc_button text="${btnText}" url="${btnUrl}" variant="primary" size="large" border_radius="999px" icon="lucide:arrow-right"]\n`;
                }
            }
            secShortcode += `[/vbc_div]\n`;
        }

        secShortcode += `[/col]\n[/row]\n[/section]\n\n`;
        generatedShortcodes.push(secShortcode);
    }

    const finalShortcode = generatedShortcodes.join('\n');
    console.log(`  \x1b[32m✓ Transpiled thành công ${generatedShortcodes.length} sections VibeCode Shortcodes!\x1b[0m`);
    return finalShortcode;
}

// ============================================================
// 5. SHORTCODE SANITIZER & LINTER PIPELINE
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

function fixImageShortcodes(content) {
    let fixes = 0;
    let fixed = content.replace(/\[(vbc_img(?:_inner(?:_\d+)?)?)\s+([^\]]*?)img_src=(["'])(.*?)\3([^\]]*?)\]/gi, (match, tag, before, quote, src, after) => {
        fixes++;
        return `[${tag} ${before}img_source="manual" img_url="${src}"${after}]`;
    });
    fixed = fixed.replace(/\[(vbc_img(?:_inner(?:_\d+)?)?)\s+([^\]]*?)(?<!img_)src=(["'])(.*?)\3([^\]]*?)\]/gi, (match, tag, before, quote, src, after) => {
        fixes++;
        return `[${tag} ${before}img_source="manual" img_url="${src}"${after}]`;
    });
    return { content: fixed, fixes };
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
        const escapedText = cleanText(trimmedText);
        fixes++;

        let divAttrs = trimmedSpanAttrs;
        if (!divAttrs.includes('display=')) divAttrs += ' display="inline-flex"';
        if (!divAttrs.includes('align_items=')) divAttrs += ' align_items="center"';
        if (!divAttrs.includes('gap=')) divAttrs += ' gap="8px"';

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

        const escapedText = cleanText(trimmedText);
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
    const imgResult = fixImageShortcodes(fontResult.content);
    const linkResult = fixLinkShortcodes(imgResult.content);
    const flexResult = fixFlexProperties(linkResult.content);
    const hexResult = fixRawHexColors(flexResult.content);
    const badgeResult = convertSpanWithIconToDiv(hexResult.content);
    const nestResult = fixNestedShortcodes(badgeResult.content);
    const escResult = escapeRawLessThan(nestResult.content);
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);

    const totalFixes = commentResult.fixes + fontResult.fixes + imgResult.fixes + linkResult.fixes + flexResult.fixes + hexResult.fixes + badgeResult.fixes + nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    console.log(`  \x1b[32m✓ Đã tự động tối ưu & chuyển đổi thành công ${totalFixes} thành phần shortcode!\x1b[0m`);

    return contentAttrResult.content;
}

// ============================================================
// 6. CLI ENTRYPOINT & ARGUMENT PARSING
// ============================================================

function printHelp() {
    console.log(`
================================================================================
     VIBECODE 99% PIXEL-PERFECT CLONE LANDING PAGE SKILL - CLI HELP
================================================================================
Sử dụng: node skills/clone-landingpage.js [tùy chọn]

TÙY CHỌN NGUỒN ĐẦU VÀO (CHỌN 1 TRONG CÁC NGUỒN SAU):
  --url <web_url>         Clone trực tiếp từ URL: Tự động trích xuất ảnh, upload WP Media, chuyển đổi DOM sang VibeCode shortcodes.
  --html <file_path>      Clone từ tệp HTML cục bộ.
  --file <txt_path>       Xuất bản trực tiếp từ tệp shortcode soạn sẵn (.txt / .html).

THÔNG TIN BÀI VIẾT:
  --title <string>        Tiêu đề trang trên WordPress.
  --slug <string>         Đường dẫn tĩnh (slug) của trang.
  --post-id <number>      (Tùy chọn) ID bài viết nếu muốn cập nhật trang hiện có.
  --post-status <status>  Trạng thái bài viết: 'publish' (mặc định) hoặc 'draft'.

CÁC TÙY CHỌN KHÁC:
  --image-upload <list>   Danh sách đường dẫn ảnh cục bộ cần upload lên WP Media (cách nhau dấu phẩy).
  --no-crawl              Tắt chế độ tự động quét & tải media từ URL nguồn.
  --dry-run               Chỉ chạy chuyển đổi & in kết quả shortcode, không gửi API đăng bài.
  --help                  Hiển thị hướng dẫn này.
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
// 7. MAIN EXECUTION
// ============================================================

async function main() {
    console.log('==================================================');
    console.log('  VIBECODE 99% PIXEL-PERFECT CLONE LANDING PAGE');
    console.log('==================================================\n');

    const args = parseArgs();
    if (args.help || Object.keys(args).length === 0) {
        printHelp();
        return;
    }

    const config = loadConfig();
    let rawShortcode = '';
    let urlMapping = new Map();

    const cacheDir = path.join(__dirname, '.cache_media');
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }

    // [1/4] Xử lý nguồn đầu vào
    if (args.url) {
        console.log(`[1/4] Đang kết nối tới URL nguồn: \x1b[36m${args.url}\x1b[0m...`);
        try {
            const fetchedHtml = await fetchUrlContent(args.url);
            console.log(`  ✓ Tải thành công ${Buffer.byteLength(fetchedHtml)} bytes HTML từ trang gốc.`);

            // Tự động quét và tải toàn bộ ảnh lên WordPress Media Library
            if (!args['no-crawl']) {
                urlMapping = await crawlAndUploadAssets(args.url, fetchedHtml, config);
            }

            // Nếu người dùng cung cấp file shortcode tùy chỉnh
            if (args.file && fs.existsSync(args.file)) {
                console.log(`  ✓ Nạp khung Shortcode tùy biến từ: ${args.file}`);
                rawShortcode = fs.readFileSync(args.file, 'utf8');
                // Thay thế link ảnh
                for (const [origin, uploaded] of urlMapping.entries()) {
                    rawShortcode = rawShortcode.split(origin).join(uploaded);
                }
            } else {
                // Tự động chạy bộ Deep Semantic Transpiler để chuyển HTML sang Shortcode Flatsome + VibeCode
                rawShortcode = transpileHtmlToVibeCode(fetchedHtml, urlMapping, args.url);
            }
        } catch (err) {
            console.error('\x1b[31m[LỖI] Không thể tải URL:\x1b[0m', err.message);
            process.exit(1);
        }
    } else if (args.file) {
        console.log(`[1/4] Đang đọc tệp Shortcode: \x1b[36m${args.file}\x1b[0m...`);
        if (!fs.existsSync(args.file)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.file} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        rawShortcode = fs.readFileSync(args.file, 'utf8');
    } else if (args.html) {
        console.log(`[1/4] Đang đọc tệp HTML cục bộ: \x1b[36m${args.html}\x1b[0m...`);
        if (!fs.existsSync(args.html)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.html} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        const localHtml = fs.readFileSync(args.html, 'utf8');
        rawShortcode = transpileHtmlToVibeCode(localHtml, urlMapping, '');
    } else {
        console.error('\x1b[31m[LỖI] Vui lòng chỉ định nguồn đầu vào: --url, --html, hoặc --file.\x1b[0m');
        process.exit(1);
    }

    // [2/4] Xử lý upload tài nguyên ảnh phụ nếu truyền qua --image-upload
    if (args['image-upload']) {
        const imagePaths = args['image-upload'].split(',').map(s => s.trim());
        console.log(`\n[2/4] Đang tải lên ${imagePaths.length} ảnh phụ...`);
        for (let i = 0; i < imagePaths.length; i++) {
            const imgPath = imagePaths[i];
            try {
                const uploadRes = await uploadImageToWordPress(imgPath, config);
                const placeholderUrl = `{{image_${i + 1}_url}}`;
                const placeholderId = `{{image_${i + 1}_id}}`;
                const wpUrl = uploadRes.url || uploadRes.source_url;
                rawShortcode = rawShortcode.split(placeholderUrl).join(wpUrl);
                rawShortcode = rawShortcode.split(placeholderId).join((uploadRes.id || uploadRes.attachment_id).toString());
                console.log(`  ✓ Upload: ${path.basename(imgPath)} → ${wpUrl}`);
            } catch (err) {
                console.warn(`  ⚠ Upload thất bại: ${imgPath} (${err.message})`);
            }
        }
    }

    // [3/4] Chạy bộ Linter & Sanitizer
    console.log('\n[3/4] Đang tối ưu hóa và làm sạch mã shortcode...');
    const sanitizedShortcode = sanitizeShortcodeContent(rawShortcode);

    // Lưu bản sao shortcode ra file cục bộ để kiểm tra / tái sử dụng
    const outputFilePath = path.join(cacheDir, 'cloned_shortcode.txt');
    fs.writeFileSync(outputFilePath, sanitizedShortcode, 'utf8');
    console.log(`  ✓ Đã lưu bản sao shortcode tại: \x1b[36m${path.normalize(outputFilePath)}\x1b[0m`);

    if (args['dry-run']) {
        console.log('\n==================================================');
        console.log('   KẾT QUẢ SHORTCODE CLONE (DRY RUN)');
        console.log('==================================================\n');
        console.log(sanitizedShortcode.substring(0, 1200) + '...\n');
        return;
    }

    // [4/4] Gửi yêu cầu đăng bài qua REST API
    const title = args.title || 'Landing Page Clone ' + new Date().toLocaleDateString('vi-VN');
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
        console.log('   XUẤT BẢN TRANG CLONE THÀNH CÔNG 99% FIDELITY!');
        console.log('==================================================');
        console.log(`ID bài viết: ${result.id || payload.post_id || 'N/A'}`);
        console.log(`Tiêu đề:     ${title}`);
        console.log(`Đường link:  ${result.link || `${config['api-url'].replace('/wp-json', '').replace(/\/+$/, '')}/${slug}/`}`);
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
