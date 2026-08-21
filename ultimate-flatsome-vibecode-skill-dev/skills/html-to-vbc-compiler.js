#!/usr/bin/env node
/**
 * ============================================================================
 * HTML → VBC COMPILER
 * Chuyển đổi HTML trang nguồn thành Flatsome UX Builder + VBC shortcodes
 * Đảm bảo trang clone có thể chỉnh sửa hoàn toàn bằng UX Builder
 * ============================================================================
 */

const cheerio = require('cheerio');

// ============================================================
// UTILITY
// ============================================================

function decodeHtmlEntities(str) {
    if (!str) return '';
    return str
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&#038;/g, '&')
        .replace(/&nbsp;/g, ' ')
        .replace(/&#(\d+);/g, (m, code) => String.fromCharCode(parseInt(code)))
        .replace(/&amp;#038;/g, '&');
}

function escapeAttr(str) {
    if (!str) return '';
    return str.replace(/"/g, '&quot;');
}

/**
 * Parse inline style thành object key-value
 */
function parseStyle(styleStr) {
    if (!styleStr) return {};
    const obj = {};
    styleStr.split(';').forEach(rule => {
        const idx = rule.indexOf(':');
        if (idx === -1) return;
        const key = rule.substring(0, idx).trim();
        const val = rule.substring(idx + 1).trim();
        if (key && val) obj[key] = val;
    });
    return obj;
}

/**
 * Chuyển style object thành chuỗi custom_css cho VBC
 * VBC dùng: custom_css="selector { ... }"
 */
function stylesToCustomCss(styles, extraCss = '') {
    const rules = Object.entries(styles)
        .filter(([k]) => !['font-family'].includes(k)) // bỏ font-family
        .map(([k, v]) => `${k}: ${v};`)
        .join(' ');
    const combined = [rules, extraCss].filter(Boolean).join(' ');
    if (!combined.trim()) return '';
    return `selector { ${combined} }`;
}

/**
 * Lấy background-image URL từ inline style
 */
function getBgImageUrl($el) {
    const style = $el.attr('style') || '';
    const m = style.match(/background(?:-image)?\s*:\s*url\(['"]?(.*?)['"]?\)/i);
    return m ? m[1] : null;
}

// ============================================================
// ELEMENT CONVERTERS
// ============================================================

/**
 * Convert thẻ img sang [vbc_img]
 */
function convertImg($el, urlMapping) {
    let src = $el.attr('src') || $el.attr('data-src') || $el.attr('data-lazy-src') || '';
    const alt = escapeAttr(decodeHtmlEntities($el.attr('alt') || ''));
    const width = $el.attr('width') || '';
    const height = $el.attr('height') || '';
    
    // Map URL nếu có
    if (urlMapping && urlMapping.get(src)) src = urlMapping.get(src);
    if (!src || src.startsWith('data:')) return '';
    
    let attrs = `img_source="external" img_url="${src}"`;
    if (alt) attrs += ` alt="${alt}"`;
    if (width) attrs += ` width="${width}"`;
    if (height) attrs += ` height="${height}"`;
    
    // Style từ thẻ img
    const styles = parseStyle($el.attr('style'));
    if (Object.keys(styles).length > 0) {
        const css = stylesToCustomCss(styles);
        if (css) attrs += ` custom_css="${escapeAttr(css)}"`;
    }

    return `[vbc_img ${attrs}][/vbc_img]`;
}

/**
 * Convert thẻ a sang [vbc_a]
 */
function convertA($el, $, urlMapping, innerContent) {
    const href = decodeHtmlEntities($el.attr('href') || '#');
    const target = $el.attr('target') || '';
    const title = escapeAttr(decodeHtmlEntities($el.attr('title') || ''));
    
    let attrs = `link_url="${escapeAttr(href)}"`;
    if (target) attrs += ` link_target="${target}"`;
    if (title) attrs += ` title="${title}"`;

    const styles = parseStyle($el.attr('style'));
    const customCss = stylesToCustomCss(styles);
    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;

    // Nếu chỉ có text bên trong
    const textContent = decodeHtmlEntities($el.text().trim());
    if (!innerContent && textContent) {
        return `[vbc_a ${attrs} content="${escapeAttr(textContent)}"][/vbc_a]`;
    }
    
    return `[vbc_a ${attrs}]${innerContent || ''}[/vbc_a]`;
}

/**
 * Convert heading tags sang VBC heading
 */
function convertHeading($el, tag, $, urlMapping) {
    const level = tag.replace('h', '');
    const vbcTag = `vbc_h${level}`;
    
    const styles = parseStyle($el.attr('style'));
    const customCss = stylesToCustomCss(styles);
    let attrs = '';
    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
    
    const textContent = decodeHtmlEntities($el.text().trim());
    if (textContent) {
        return `[${vbcTag}${attrs} content="${escapeAttr(textContent)}"][/${vbcTag}]`;
    }
    return '';
}

/**
 * Convert <p> sang [vbc_p]
 */
function convertP($el, $, urlMapping) {
    const styles = parseStyle($el.attr('style'));
    const customCss = stylesToCustomCss(styles);
    let attrs = '';
    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
    
    // Lấy inner HTML và convert recursively
    const innerHtml = $el.html() || '';
    const converted = convertInlineContent(innerHtml, $, urlMapping);
    const clean = decodeHtmlEntities(cheerio.load(converted).text().trim());
    
    if (!clean) return '';
    
    // Nếu có HTML phức tạp, dùng inner content
    if (/<[^>]+>/.test(converted)) {
        return `[vbc_p${attrs}]${converted}[/vbc_p]`;
    }
    
    return `[vbc_p${attrs} content="${escapeAttr(clean)}"][/vbc_p]`;
}

/**
 * Convert inline content (strong, em, span, a, img trong text)
 */
function convertInlineContent(html, $, urlMapping) {
    if (!html) return '';
    const $doc = cheerio.load(`<div>${html}</div>`);
    const $root = $doc('div');
    
    let result = '';
    $root.contents().each((i, node) => {
        if (node.type === 'text') {
            result += decodeHtmlEntities(node.data || '');
        } else if (node.type === 'tag') {
            const $node = $doc(node);
            const tag = node.name.toLowerCase();
            switch (tag) {
                case 'strong':
                case 'b':
                    result += `[vbc_strong content="${escapeAttr(decodeHtmlEntities($node.text()))}"][/vbc_strong]`;
                    break;
                case 'em':
                case 'i':
                    result += `[vbc_em content="${escapeAttr(decodeHtmlEntities($node.text()))}"][/vbc_em]`;
                    break;
                case 'span': {
                    const styles = parseStyle($node.attr('style'));
                    const customCss = stylesToCustomCss(styles);
                    let attrs = '';
                    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
                    const text = decodeHtmlEntities($node.text());
                    result += `[vbc_span${attrs} content="${escapeAttr(text)}"][/vbc_span]`;
                    break;
                }
                case 'a': {
                    const href = decodeHtmlEntities($node.attr('href') || '#');
                    const target = $node.attr('target') || '';
                    let aAttrs = `link_url="${escapeAttr(href)}"`;
                    if (target) aAttrs += ` link_target="${target}"`;
                    const text = decodeHtmlEntities($node.text());
                    result += `[vbc_a ${aAttrs} content="${escapeAttr(text)}"][/vbc_a]`;
                    break;
                }
                case 'img':
                    result += convertImg($node, urlMapping);
                    break;
                case 'br':
                    result += '<br/>';
                    break;
                default:
                    result += decodeHtmlEntities($node.text());
            }
        }
    });
    
    return result;
}

/**
 * Convert <ul>/<ol> sang [vbc_ul]/[vbc_ol]
 */
function convertList($el, tag, $, urlMapping) {
    const vbcTag = `vbc_${tag}`;
    let items = '';
    $el.find('> li').each((i, li) => {
        const $li = $(li);
        const text = decodeHtmlEntities($li.text().trim());
        if (text) items += `[vbc_li content="${escapeAttr(text)}"][/vbc_li]`;
    });
    if (!items) return '';
    return `[${vbcTag}]${items}[/${vbcTag}]`;
}

// ============================================================
// BLOCK-LEVEL SECTION DETECTOR
// ============================================================

/**
 * Phân tích CSS class để lấy các thông tin layout
 */
function parseLayoutFromClass(className) {
    const classes = (className || '').split(/\s+/);
    const layout = {
        columns: 12,   // Bootstrap columns (12-based)
        hasFlex: false,
        isContainer: false,
        isRow: false,
        isCol: false,
        isFull: false,
    };
    
    classes.forEach(c => {
        if (/^col-(xs|sm|md|lg|xl)-(\d+)$/.test(c)) {
            layout.isCol = true;
            layout.columns = parseInt(c.match(/\d+$/)[0]);
        }
        if (c === 'container' || c === 'container-fluid') layout.isContainer = true;
        if (c === 'row' || c === 'flex-row') layout.isRow = true;
        if (c === 'd-flex' || c === 'flex' || /flex/.test(c)) layout.hasFlex = true;
        if (c === 'fullwidth' || c === 'full-width' || c === 'container-full') layout.isFull = true;
    });
    
    return layout;
}

/**
 * Xác định width cho Flatsome column từ Bootstrap col
 */
function bootstrapColToFlatsomeWidth(cols) {
    const map = { 1: '1/12', 2: '1/6', 3: '1/4', 4: '1/3', 5: '5/12', 6: '1/2', 7: '7/12', 8: '2/3', 9: '3/4', 10: '5/6', 11: '11/12', 12: '1/1' };
    return map[cols] || '1/1';
}

// ============================================================
// MAIN DOM COMPILER
// ============================================================

/**
 * Phần trung tâm: Chuyển đổi một DOM element thành VBC shortcode
 */
function convertElement($el, $, urlMapping, depth = 0) {
    const tag = $el.prop('tagName') ? $el.prop('tagName').toLowerCase() : '';
    if (!tag) return '';

    const style = $el.attr('style') || '';
    const className = $el.attr('class') || '';
    const styles = parseStyle(style);
    
    // Bỏ qua elements không liên quan
    if (['script', 'style', 'noscript', 'meta', 'link', 'head'].includes(tag)) return '';
    
    // === Heading tags ===
    if (/^h[1-6]$/.test(tag)) {
        return convertHeading($el, tag, $, urlMapping);
    }
    
    // === Paragraph ===
    if (tag === 'p') {
        return convertP($el, $, urlMapping);
    }
    
    // === Image ===
    if (tag === 'img') {
        return convertImg($el, urlMapping);
    }
    
    // === Lists ===
    if (tag === 'ul') return convertList($el, 'ul', $, urlMapping);
    if (tag === 'ol') return convertList($el, 'ol', $, urlMapping);
    
    // === Anchor ===
    if (tag === 'a') {
        const innerHtml = $el.html() || '';
        const hasImg = $el.find('img').length > 0;
        const innerContent = hasImg ? convertChildren($el, $, urlMapping, depth + 1) : '';
        return convertA($el, $, urlMapping, innerContent);
    }
    
    // === Button ===
    if (tag === 'button') {
        const text = decodeHtmlEntities($el.text().trim());
        const customCss = stylesToCustomCss(styles);
        let attrs = '';
        if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
        if (text) attrs += ` content="${escapeAttr(text)}"`;
        return `[vbc_a${attrs} link_url="#"][/vbc_a]`;
    }
    
    // === Inline elements (span, strong, em) ===
    if (['span', 'strong', 'b', 'em', 'i', 'u'].includes(tag)) {
        const customCss = stylesToCustomCss(styles);
        const text = decodeHtmlEntities($el.text().trim());
        if (!text) return '';
        const vbcTag = tag === 'strong' || tag === 'b' ? 'vbc_strong' : 
                       tag === 'em' || tag === 'i' ? 'vbc_em' :
                       tag === 'u' ? 'vbc_u' : 'vbc_span';
        let attrs = '';
        if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
        return `[${vbcTag}${attrs} content="${escapeAttr(text)}"][/${vbcTag}]`;
    }
    
    // === Block container: div, section, article, main, aside, header, footer ===
    return convertBlock($el, $, urlMapping, depth);
}

/**
 * Convert children của một element
 */
function convertChildren($el, $, urlMapping, depth = 0) {
    let result = '';
    $el.children().each((i, child) => {
        const $child = $(child);
        result += convertElement($child, $, urlMapping, depth);
    });
    return result;
}

/**
 * Convert block-level containers → [vbc_div] / [vbc_box] / [vbc_container]
 * Với proper Flatsome layout wrapping
 */
function convertBlock($el, $, urlMapping, depth = 0) {
    const className = $el.attr('class') || '';
    const style = $el.attr('style') || '';
    const styles = parseStyle(style);
    const tag = $el.prop('tagName').toLowerCase();
    const id = $el.attr('id') || '';
    
    // Build custom_css từ styles
    const bgImage = getBgImageUrl($el);
    let bgUrl = bgImage;
    if (bgUrl && urlMapping && urlMapping.get(bgUrl)) bgUrl = urlMapping.get(bgUrl);
    
    // Loại bỏ background-image khỏi styles vì sẽ set riêng
    const filteredStyles = Object.fromEntries(
        Object.entries(styles).filter(([k]) => !k.includes('background-image'))
    );
    
    const customCss = stylesToCustomCss(filteredStyles);
    
    // Xây dựng attrs
    let attrs = '';
    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
    if (bgUrl) attrs += ` bg_image="${escapeAttr(bgUrl)}" bg_size="cover" bg_pos="center"`;
    if (id) attrs += ` el_id="${id}"`;
    if (className) attrs += ` el_class="${className}"`;
    
    // Children
    const children = convertChildren($el, $, urlMapping, depth + 1);
    
    // Chọn VBC tag phù hợp theo context
    let vbcTag = 'vbc_div';
    if (tag === 'section' || depth === 0) vbcTag = 'vbc_box';
    if (className.includes('container')) vbcTag = 'vbc_container';
    
    // Nếu không có children có ý nghĩa, check text trực tiếp
    if (!children.trim()) {
        const text = decodeHtmlEntities($el.text().trim());
        if (!text) return '';
        return `[${vbcTag}${attrs}][vbc_p content="${escapeAttr(text)}"][/vbc_p][/${vbcTag}]`;
    }
    
    return `[${vbcTag}${attrs}]${children}[/${vbcTag}]`;
}

/**
 * Loại bỏ các phần tử theme không liên quan đến nội dung trang:
 * header, nav, footer, sidebar, floating buttons, modal, scripts...
 */
function stripThemeNoise($) {
    const noiseSelectors = [
        // Navigation & Header
        'header', 'nav', '.site-header', '.page-header', '.header-wrap',
        '.penci-header-wrap', '.header-style-header-3', '.header-3',
        '.nav-menu', '.navigation', '.main-nav', '.primary-nav',
        '.topbar', '.top-bar', '.penci-top-bar',
        '.hamburger', '.penci-menu-hbg', '.mobile-menu',
        // Footer
        'footer', '.site-footer', '.footer-widget', '.footer-widgets',
        '.footer-bottom', '.footer-top', '.copyright',
        // Sidebar
        '.sidebar', '.widget-area', '.widget', '#sidebar',
        // Breadcrumbs
        '.breadcrumbs', '.breadcrumb', '.penci-breadcrumb',
        // Floating & Overlay UI
        '.box_fixRight', '.tv-glow-button', '.tv-mobile-bar',
        '.crystal-wrapper', '.premium-popup', '.dtk-modal',
        '.cookie-notice', '.cookie-banner', '.gdpr',
        '.rd-progress-container', '.scroll-to-top', '.back-to-top',
        // Comment section
        '#comments', '.comments-area', '.comment-form',
        // Related posts
        '.related-posts', '.penci-related-posts',
        // Social share
        '.social-share', '.share-buttons', '.penci-social-share',
        // Post meta
        '.post-meta', '.entry-meta', '.penci-post-tags',
        // Newsletter popup
        '.newsletter-popup', '.subscribe-popup',
        // Scripts & invisible
        'script', 'style', 'noscript', 'template',
        'link[rel="stylesheet"]', 'meta',
    ];

    let removed = 0;
    for (const sel of noiseSelectors) {
        const count = $(sel).length;
        $(sel).remove();
        removed += count;
    }
    
    console.log(`  ✓ Đã loại bỏ ${removed} phần tử theme noise (header/nav/footer/popup...)`);
}

/**
 * Xác định vùng content chính của trang
 * Hỗ trợ các theme: Flatsome, Penci, GeneratePress, Astra, OceanWP...
 */
function extractMainContent($) {
    // Thứ tự ưu tiên selector - từ cụ thể đến tổng quát
    const selectors = [
        // Flatsome
        'main .entry-content',
        'main article .entry-content',
        '#main .entry-content',
        // Penci theme
        '.penci-inner-content',
        '.penci-content-area',
        '.penci-single-content',
        // Common WordPress
        '.entry-content',
        'article.type-page .content',
        'article .content',
        '.post-content',
        '.page-content',
        '.content-inner',
        // Container wrappers
        '.wrapper-boxed',
        '.site-wrapper',
        '.wrapper',
        'main',
        '#content',
        '#primary',
        '.site-content',
        // Fallback
        'body'
    ];

    for (const sel of selectors) {
        const $el = $(sel).first();
        if (!$el.length) continue;
        
        const childCount = $el.children().filter((i, el) => {
            const tag = el.name ? el.name.toLowerCase() : '';
            return !['script', 'style', 'link', 'meta', 'noscript'].includes(tag);
        }).length;
        
        const textLen = $el.text().trim().length;
        
        // Chỉ chấp nhận nếu có nhiều children và content
        if (childCount >= 2 && textLen > 200) {
            console.log(`  ✓ Vùng content chính: ${sel} (${childCount} children, ${textLen} chars)`);
            return $el;
        }
    }
    
    console.log('  ⚠ Dùng <body> làm fallback content area');
    return $('body');
}

// ============================================================
// TAB DETECTOR & CONVERTER
// ============================================================

/**
 * Phát hiện và convert tabs structure → [vbc_tabs][vbc_tab]
 */
function detectAndConvertTabs($, urlMapping) {
    // Common tab patterns: Bootstrap, Flatsome, jQuery UI, WooCommerce
    const tabPatterns = [
        { nav: '.nav-tabs, .tabs-nav, ul.tabs', content: '.tab-content, .tab-pane, .tabs-content' },
        { nav: '.wc-tabs, .woocommerce-tabs ul.tabs', content: '.woocommerce-Tabs-panel' },
    ];
    
    // Detect tabs in DOM
    let tabSections = [];
    $('[role="tablist"], .nav-tabs, .tabs-nav, ul.tabs').each((i, el) => {
        const $nav = $(el);
        const titles = [];
        $nav.find('a, button, li').each((j, item) => {
            const text = decodeHtmlEntities($(item).text().trim());
            if (text) titles.push(text);
        });
        if (titles.length >= 2) tabSections.push({ $nav, titles });
    });
    
    return tabSections;
}

// ============================================================
// FULL PAGE COMPILER (EXPORTED)
// ============================================================

/**
 * Biên dịch HTML trang nguồn thành VBC shortcodes với Flatsome layout
 * @param {string} htmlContent - Raw HTML từ trang gốc
 * @param {Map} urlMapping - Map URL gốc → WP URL
 * @param {Map} idMapping - Map URL gốc → WP Attachment ID 
 * @returns {string} - VBC shortcodes ready to publish
 */
function compileHtmlToVbc(htmlContent, urlMapping = new Map(), idMapping = new Map()) {
    console.log('\n\x1b[35m[HTML→VBC COMPILER] Bắt đầu biên dịch HTML → VBC shortcodes...\x1b[0m');
    
    const $ = cheerio.load(htmlContent, { decodeEntities: false });
    
    // Lấy metadata TRƯỚC khi strip (title/desc cần lấy sớm)
    const pageTitle = $('title').text() || 'Cloned Page';
    console.log(`  ✓ Tiêu đề trang: ${pageTitle}`);

    // 1. Xóa noise NGOÀI content (header, footer, nav của toàn trang)
    // KHÔNG xóa wrapper nội dung - chỉ xóa các element nằm ngoài
    const outerNoiseSelectors = [
        'header', 'nav', 'footer',
        '.box_fixRight', '.tv-glow-button', '.tv-mobile-bar',
        '.crystal-wrapper', '.premium-popup', '.dtk-modal',
        '.rd-progress-container', '.cookie-notice', '.cookie-banner',
    ];
    let outerRemoved = 0;
    for (const sel of outerNoiseSelectors) {
        const count = $(sel).length;
        $(sel).remove();
        outerRemoved += count;
    }
    
    // 2. Trích xuất content chính
    let $main = extractMainContent($);
    
    // 3. Xóa noise BÊN TRONG content (topbar, share, tags, comments nằm trong wrapper)
    const innerNoiseSelectors = [
        '.penci-header-wrap', '.penci-top-bar', '.penci-menu-hbg',
        '.penci-menu-hbg-overlay', '.header-style-header-3',
        '.penci-breadcrumb', '.breadcrumbs', '.breadcrumb',
        '#comments', '.comments-area', '.comment-form',
        '.related-posts', '.penci-related-posts', '.penci-post-tags',
        '.social-share', '.penci-social-share',
        '.penci-footer', '.footer-widget', '.footer-bottom',
        '.newsletter-popup', '.subscribe-popup',
    ];
    let innerRemoved = 0;
    for (const sel of innerNoiseSelectors) {
        const found = $main.find(sel);
        innerRemoved += found.length;
        found.remove();
        // Cũng xóa nếu $main CHÍNH là element này
        if ($main.is(sel)) {
            innerRemoved++;
        }
    }
    
    console.log(`  ✓ Đã loại bỏ ${outerRemoved} outer + ${innerRemoved} inner theme noise elements`);
    
    // Nếu chỉ có 1 child duy nhất, drill down thêm 1 level
    const blockTags = ['div', 'section', 'article', 'header', 'footer', 'aside', 'nav', 'figure', 'main'];
    const meaningfulChildren = $main.children().filter((i, el) => {
        const tag = el.name ? el.name.toLowerCase() : '';
        return !['script', 'style', 'link', 'meta', 'noscript', 'template'].includes(tag);
    });
    
    if (meaningfulChildren.length === 1) {
        const $onlyChild = $(meaningfulChildren[0]);
        const childTag = meaningfulChildren[0].name ? meaningfulChildren[0].name.toLowerCase() : '';
        if (blockTags.includes(childTag)) {
            const childText = $onlyChild.text().trim().length;
            const grandChildCount = $onlyChild.children().length;
            console.log(`  ✓ Drill down vào single child: <${childTag}.${$onlyChild.attr('class') || ''}> (${grandChildCount} grandchildren, ${childText} chars)`);
            $main = $onlyChild;
        }
    }
    
    // Phát hiện tabs
    const tabs = detectAndConvertTabs($, urlMapping);
    if (tabs.length > 0) {
        console.log(`  ✓ Phát hiện ${tabs.length} tabs component`);
    }
    
    // Convert từng section chính
    let vbcOutput = '';
    let sectionCount = 0;

    $main.children().each((i, child) => {
        const $child = $(child);
        const tag = $child.prop('tagName') ? $child.prop('tagName').toLowerCase() : '';
        
        // Bỏ qua elements không liên quan
        if (!tag || ['script', 'style', 'noscript', 'link', 'meta', 'template'].includes(tag)) return;
        
        const text = $child.text().trim();
        const imgCount = $child.find('img').length;
        if (!text && imgCount === 0) return;
        if (text.length < 3 && imgCount === 0) return;
        
        // Convert element
        const converted = convertElement($child, $, urlMapping, 0);
        if (!converted.trim()) return;
        
        // Block-level elements → bọc trong Flatsome [section][row][col]
        if (blockTags.includes(tag)) {
            const bgImage = getBgImageUrl($child);
            let bgUrl = bgImage;
            if (bgUrl && urlMapping.get(bgUrl)) bgUrl = urlMapping.get(bgUrl);
            const bgId = bgUrl && idMapping.get(bgUrl) ? idMapping.get(bgUrl) : '';
            
            const childStyles = parseStyle($child.attr('style') || '');
            let sectionAttrs = 'width="full_width"';
            
            if (bgId) sectionAttrs += ` bg="${bgId}" bg_size="cover" bg_pos="center"`;
            else if (bgUrl) sectionAttrs += ` bg_image="${escapeAttr(bgUrl)}" bg_size="cover" bg_pos="center"`;
            
            const bgColor = childStyles['background-color'] || '';
            if (bgColor && !bgColor.includes('url(')) {
                sectionAttrs += ` bg_color="${bgColor}"`;
            }
            
            const paddingTop = childStyles['padding-top'] || '';
            if (paddingTop) sectionAttrs += ` padding="${paddingTop.replace('px', '')}"`;
            
            vbcOutput += `[section ${sectionAttrs}]\n[row]\n[col span="12" span__sm="12"]\n${converted}\n[/col]\n[/row]\n[/section]\n\n`;
            sectionCount++;
        } else {
            // Inline/text elements
            vbcOutput += converted + '\n';
        }
    });
    
    console.log(`  ✓ Đã biên dịch ${sectionCount} sections thành VBC shortcodes`);
    console.log(`  ✓ Tổng kích thước output: ${Buffer.byteLength(vbcOutput)} bytes`);
    
    return vbcOutput;
}

module.exports = { compileHtmlToVbc, decodeHtmlEntities };
