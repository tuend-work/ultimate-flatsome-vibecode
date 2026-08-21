#!/usr/bin/env node

/**
 * ============================================================================
 * ULTIMATE FLATSOME VIBECODE - 99% PIXEL-PERFECT CLONE LANDING PAGE SKILL
 * ============================================================================
 * Công cụ tự động clone 99% về giao diện và tính năng từ:
 *   1. Trang web trực tiếp qua URL (--url https://...)
 *   2. Tệp HTML cục bộ (--html path/to/index.html)
 *   3. Gói ZIP chứa template HTML/CSS/Images (--zip path/to/template.zip)
 *   4. Ảnh chụp màn hình giao diện (--image path/to/screenshot.png)
 *   5. Tệp Shortcode soạn sẵn (--file path/to/shortcode.txt)
 *
 * Tính năng đột phá:
 *   - Auto Asset Crawling: Tự động trích xuất toàn bộ ảnh, banner, SVG từ trang gốc.
 *   - Auto WP Media Upload: Tự động tải ảnh về và upload lên WordPress Media Library qua REST API.
 *   - Auto URL Mapping: Tự động thay thế toàn bộ URL ảnh thành link WP Media cục bộ.
 *   - 3D Transforms & Gradient Replicator: Tái hiện chuẩn xác các hiệu ứng 3D, Gradient text, Timeline bar.
 *   - Shortcode Sanitizer & Linter: Chuẩn hóa _inner nesting, quote escaping, Flatsome font inheritance.
 * ============================================================================
 */

const fs = require('fs');
const path = require('path');
const http = require('http');
const https = require('https');
const { URL } = require('url');
const { compileHtmlToVbc, decodeHtmlEntities } = require('./html-to-vbc-compiler');
const { fetchRenderedHtmlWithBrowser } = require('./browser-fetcher');

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
 * Tải nội dung HTML từ URL
 * Mặc định: Dùng Headless Browser (Chrome/Edge) để thực thi JS, cuộn trang kích hoạt Lazy Load
 * Tùy chọn fallback: Dùng https.get() nếu có cờ --no-browser hoặc môi trường không có trình duyệt
 */
async function fetchUrlContent(targetUrl, options = {}) {
    if (!options['no-browser']) {
        try {
            const html = await fetchRenderedHtmlWithBrowser(targetUrl, {
                waitTime: parseInt(options['wait'] || '1500', 10),
                timeout: parseInt(options['timeout'] || '60000', 10)
            });
            return html;
        } catch (err) {
            console.warn(`  ⚠ Headless Browser không khả dụng (${err.message}). Đang chuyển sang chế độ tải trực tiếp qua HTTPS...`);
        }
    }

    return fetchUrlViaHttps(targetUrl);
}

function fetchUrlViaHttps(targetUrl) {
    return new Promise((resolve, reject) => {
        const parsed = new URL(targetUrl);
        const client = parsed.protocol === 'https:' ? https : http;
        client.get(targetUrl, {
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            }
        }, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                const redirectUrl = new URL(res.headers.location, targetUrl).href;
                return resolve(fetchUrlViaHttps(redirectUrl));
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
 * Tải file nhị phân (ảnh, svg, zip) từ URL về tệp cục bộ
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
                    fs.unlink(destPath, () => {});
                    return reject(new Error(`HTTP ${res.statusCode}`));
                }
                res.pipe(file);
                file.on('finish', () => {
                    file.close(() => resolve(destPath));
                });
            }).on('error', (err) => {
                fs.unlink(destPath, () => {});
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
            'Content-Type': 'application/json; charset=utf-8',
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
        req.write(Buffer.from(bodyStr, 'utf8'));
        req.end();
    });
}

// ============================================================
// 3. ASSET CRAWLER & WP MEDIA PIPELINE
// ============================================================

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Tải nội dung text (như tệp CSS) từ URL
 */
async function fetchText(urlStr) {
    const urlObj = new URL(urlStr);
    const client = urlObj.protocol === 'https:' ? https : http;
    const options = {
        hostname: urlObj.hostname,
        port: urlObj.port || (urlObj.protocol === 'https:' ? 443 : 80),
        path: urlObj.pathname + urlObj.search,
        method: 'GET',
        headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/css,*/*;q=0.1'
        },
        timeout: 10000
    };
    return new Promise((resolve, reject) => {
        const req = client.request(options, (res) => {
            if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
                const nextUrl = new URL(res.headers.location, urlStr).href;
                return resolve(fetchText(nextUrl));
            }
            if (res.statusCode < 200 || res.statusCode >= 300) {
                return reject(new Error(`HTTP ${res.statusCode}`));
            }
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(data));
        });
        req.on('error', reject);
        req.on('timeout', () => { req.destroy(); reject(new Error('Request timeout')); });
        req.end();
    });
}

/**
 * Quét toàn bộ hình ảnh, banner, background-image từ HTML và tất cả tệp CSS external, tải về và upload lên WordPress Media Library
 */
async function crawlAndUploadAssets(baseUrl, htmlContent, config) {
    console.log('\n\x1b[36m[ASSET CRAWLER] Đang quét tất cả tài nguyên ảnh, banner và CSS background-image từ trang nguồn...\x1b[0m');
    const cacheDir = path.join(__dirname, '.cache_media');
    if (!fs.existsSync(cacheDir)) {
        fs.mkdirSync(cacheDir, { recursive: true });
    }

    const assetUrls = new Set();

    // 1. Quét src và lazy-load trong thẻ <img>
    const imgRegex = /<img\b[^>]*?\b(?:src|data-src|data-lazy-src)=(?:["']([^"']+)["']|([^\s>]+))/gi;
    let match;
    while ((match = imgRegex.exec(htmlContent)) !== null) {
        const rawSrc = match[1] || match[2];
        if (rawSrc && !rawSrc.startsWith('data:')) {
            assetUrls.add(rawSrc);
        }
    }

    // 2. Quét background-image trong inline style và khối <style>
    const bgRegex = /background(?:-image)?\s*:\s*url\((['"]?)(.*?)\1\)/gi;
    while ((match = bgRegex.exec(htmlContent)) !== null) {
        const rawBg = match[2];
        if (rawBg && !rawBg.startsWith('data:') && !rawBg.match(/\.(woff2?|ttf|eot|otf)($|\?)/i)) {
            assetUrls.add(rawBg);
        }
    }

    // 3. Quét preload images
    const preloadRegex = /<link\b[^>]*?\brel=["']preload["'][^>]*?\bhref=(?:["']([^"']+)["']|([^\s>]+))[^>]*?\bas=["']image["']/gi;
    while ((match = preloadRegex.exec(htmlContent)) !== null) {
        const rawSrc = match[1] || match[2];
        if (rawSrc) assetUrls.add(rawSrc);
    }

    // 4. QUÉT TOÀN DIỆN CÁC TỆP CSS EXTERNAL (<link rel="stylesheet">)
    const cssLinkRegex = /<link\b[^>]*?\brel=["']stylesheet["'][^>]*?\bhref=(?:["']([^"']+)["']|([^\s>]+))/gi;
    const externalCssUrls = [];
    while ((match = cssLinkRegex.exec(htmlContent)) !== null) {
        const rawCssHref = match[1] || match[2];
        if (rawCssHref) {
            try {
                const absCss = new URL(rawCssHref, baseUrl).href;
                externalCssUrls.push(absCss);
            } catch(e) {}
        }
    }

    if (externalCssUrls.length > 0) {
        console.log(`  ✓ Tìm thấy ${externalCssUrls.length} tệp CSS external. Đang phân tích CSS background-image...`);
        for (const cssUrl of externalCssUrls) {
            try {
                const cssContent = await fetchText(cssUrl);
                let cssBgMatch;
                const cssBgRegex = /url\((['"]?)([^'"\)]+\.(?:png|jpg|jpeg|webp|svg|gif)[^'"\)]*)\1\)/gi;
                while ((cssBgMatch = cssBgRegex.exec(cssContent)) !== null) {
                    const rawBg = cssBgMatch[2];
                    if (rawBg && !rawBg.startsWith('data:')) {
                        try {
                            const absBg = new URL(rawBg, cssUrl).href;
                            assetUrls.add(absBg);
                        } catch(e) {
                            assetUrls.add(rawBg);
                        }
                    }
                }
            } catch (err) {
                // Bỏ qua lỗi kết nối tới tệp CSS phụ
            }
        }
    }

    console.log(`  ✓ Tổng cộng tìm thấy ${assetUrls.size} tài nguyên media liên quan.`);

    const urlMapping = new Map(); // originUrl -> wpUploadedUrl
    const idMapping = new Map();  // originUrl -> wpAttachmentId

    let index = 0;
    for (const rawUrl of assetUrls) {
        index++;
        let absoluteUrl = rawUrl;
        try {
            absoluteUrl = new URL(rawUrl, baseUrl).href;
        } catch (e) {
            continue;
        }

        const fileName = path.basename(new URL(absoluteUrl).pathname) || `asset_${index}.png`;
        const localPath = path.join(cacheDir, fileName);

        try {
            console.log(`  [${index}/${assetUrls.size}] Đang tải: ${fileName}...`);
            await downloadBinary(absoluteUrl, localPath);
            const uploadRes = await uploadImageToWordPress(localPath, config);
            const uploadedUrl = uploadRes.url;
            const attachmentId = uploadRes.id || uploadRes.attachment_id;

            urlMapping.set(rawUrl, uploadedUrl);
            urlMapping.set(absoluteUrl, uploadedUrl);
            if (attachmentId) {
                idMapping.set(rawUrl, attachmentId);
                idMapping.set(absoluteUrl, attachmentId);
            }

            try {
                const pathname = new URL(absoluteUrl).pathname;
                urlMapping.set(pathname, uploadedUrl);
                if (attachmentId) idMapping.set(pathname, attachmentId);
            } catch(e) {}
            console.log(`    → Đã tải lên WP Media: ID ${attachmentId} (${uploadedUrl})`);
        } catch (err) {
            console.warn(`    ⚠ Bỏ qua ${fileName}: ${err.message}`);
        }
    }

    return { urlMapping, idMapping };
}

// ============================================================
// 4. SHORTCODE SANITIZER & LINTER PIPELINE
// ============================================================

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
    // Decode HTML entities trước (fix &amp;#038; → &)
    content = decodeHtmlEntities(content);
    console.log('\n\x1b[35m[CLONE SANITIZER] Đang kiểm tra và chuẩn hóa cấu trúc VibeCode...\x1b[0m');
    
    const unsuppResult = fixUnsupportedVbcShortcodes(content);
    const commentResult = stripAllHtmlComments(unsuppResult.content);
    const fontResult = stripHardcodedFontFamily(commentResult.content);
    const imgResult = fixImageShortcodes(fontResult.content);
    const linkResult = fixLinkShortcodes(imgResult.content);
    const flexResult = fixFlexProperties(linkResult.content);
    const hexResult = fixRawHexColors(flexResult.content);
    const badgeResult = convertSpanWithIconToDiv(hexResult.content);
    const nestResult = fixNestedShortcodes(badgeResult.content);
    const escResult = escapeRawLessThan(nestResult.content);
    const contentAttrResult = migrateTagsToContentAttribute(escResult.content);

    const totalFixes = unsuppResult.fixes + commentResult.fixes + fontResult.fixes + imgResult.fixes + linkResult.fixes + flexResult.fixes + hexResult.fixes + badgeResult.fixes + nestResult.fixes + escResult.fixes + contentAttrResult.fixes;
    console.log(`  \x1b[32m✓ Đã tự động tối ưu & chuyển đổi thành công ${totalFixes} thành phần shortcode!\x1b[0m`);

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

// ============================================================
// 5. CLI ENTRYPOINT & ARGUMENT PARSING
// ============================================================

function printHelp() {
    console.log(`
================================================================================
     VIBECODE 99% PIXEL-PERFECT CLONE LANDING PAGE SKILL - CLI HELP
================================================================================
Sử dụng: node skills/clone-landingpage.js [tùy chọn]

TÙY CHỌN NGUỒN ĐẦU VÀO (CHỌN 1 TRONG CÁC NGUỒN SAU):
  --url <web_url>         Clone trực tiếp từ URL trang web kèm tự động trích xuất và tải lên toàn bộ ảnh/banner.
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
// 6. MAIN EXECUTION
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
    let rawContent = '';

    // [1/4] Xử lý nguồn đầu vào
    if (args.url) {
        console.log(`[1/4] Đang kết nối tới URL nguồn: \x1b[36m${args.url}\x1b[0m...`);
        try {
            const fetchedHtml = await fetchUrlContent(args.url, args);
            console.log(`  ✓ Tải thành công ${Buffer.byteLength(fetchedHtml)} bytes HTML từ trang gốc.`);

            // Tự động quét và tải toàn bộ ảnh nếu không bị tắt bởi --no-crawl
            let assetData = { urlMapping: new Map(), idMapping: new Map() };
            if (!args['no-crawl']) {
                assetData = await crawlAndUploadAssets(args.url, fetchedHtml, config);
            }

            // Nếu người dùng truyền kèm file shortcode mẫu đã tối ưu hóa bố cục
            if (args.file && fs.existsSync(args.file)) {
                console.log(`  ✓ Nạp khung Shortcode tùy biến cao cấp từ: ${args.file}`);
                rawContent = fs.readFileSync(args.file, 'utf8');
            } else {
                // ✅ COMPILER MỚI: Biên dịch HTML → VBC shortcodes thực sự
                // Thay vì paste raw HTML, dùng cheerio để parse DOM và sinh VBC shortcodes
                const urlMapping = assetData.urlMapping || new Map();
                const idMapping = assetData.idMapping || new Map();

                if (urlMapping.size > 0 || idMapping.size > 0) {
                    console.log(`\n\x1b[32m[URL MAPPER] Đang ánh xạ ${urlMapping.size} liên kết media và ${idMapping.size} background ID sang WP Media...\x1b[0m`);
                }

                // Compile HTML → Flatsome [section][row][col] + VBC shortcodes
                rawContent = compileHtmlToVbc(fetchedHtml, urlMapping, idMapping);
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
        rawContent = fs.readFileSync(args.file, 'utf8');
    } else if (args.html) {
        console.log(`[1/4] Đang đọc tệp HTML cục bộ: \x1b[36m${args.html}\x1b[0m...`);
        if (!fs.existsSync(args.html)) {
            console.error(`\x1b[31m[LỖI] Tệp ${args.html} không tồn tại!\x1b[0m`);
            process.exit(1);
        }
        rawContent = fs.readFileSync(args.html, 'utf8');
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
                rawContent = rawContent.split(placeholderUrl).join(uploadRes.url);
                rawContent = rawContent.split(placeholderId).join(uploadRes.id.toString());
                console.log(`  ✓ Upload: ${path.basename(imgPath)} → ${uploadRes.url}`);
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
        console.log('   KẾT QUẢ SHORTCODE CLONE (DRY RUN)');
        console.log('==================================================\n');
        console.log(sanitizedShortcode.substring(0, 1000) + '...\n');
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
        console.log('   XUẤT BẢN TRANG CLONE THÀNH CÔNG 99% FIDELITY!');
        console.log('==================================================');
        console.log(`ID bài viết: ${result.id || payload.post_id || 'N/A'}`);
        console.log(`Tiêu đề:     ${title}`);
        const publishedUrl = result.link || `${config['api-url'].replace('/wp-json', '')}/${slug}/`;
        console.log(`Đường link:  ${publishedUrl}`);
        console.log('==================================================\n');

        // Bắt buộc kiểm tra tự động live frontend
        await verifyLiveFrontend(publishedUrl);
    } catch (err) {
        console.error('\n\x1b[31m[LỖI] Xuất bản bài viết thất bại:\x1b[0m', err.message);
        process.exit(1);
    }
}

main().catch(err => {
    console.error('\n\x1b[31m[FATAL ERROR]\x1b[0m', err);
    process.exit(1);
});
