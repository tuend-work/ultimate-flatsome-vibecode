#!/usr/bin/env node
/**
 * ============================================================================
 * HTML → VBC COMPILER (WITH ELEMENTOR JSON & DATA-SETTINGS TRANSLATOR)
 * Chuyển đổi HTML & Elementor JSON trang nguồn thành Flatsome UX Builder + VBC shortcodes
 * Đảm bảo trang clone có thể chỉnh sửa hoàn toàn bằng UX Builder với độ tương đồng 99%+
 * ============================================================================
 */

const cheerio = require('cheerio');

// ============================================================
// 1. UTILITIES & ENCODING
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

function stylesToCustomCss(styles, extraCss = '') {
    const rules = Object.entries(styles)
        .filter(([k]) => !['font-family'].includes(k))
        .map(([k, v]) => `${k}: ${v};`)
        .join(' ');
    const combined = [rules, extraCss].filter(Boolean).join(' ');
    if (!combined.trim()) return '';
    return `selector { ${combined} }`;
}

function getBgImageUrl($el) {
    const style = $el.attr('style') || '';
    const m = style.match(/background(?:-image)?\s*:\s*url\(['"]?(.*?)['"]?\)/i);
    return m ? m[1] : null;
}

// ============================================================
// 2. RECURSIVE DOM TO VBC ELEMENT TRANSLATOR
// ============================================================

function convertElement($el, $, urlMapping = new Map(), depth = 0) {
    const tag = $el.prop('tagName') ? $el.prop('tagName').toLowerCase() : '';
    if (!tag) return '';
    if (['script', 'style', 'noscript', 'meta', 'link', 'template', 'head'].includes(tag)) return '';

    const style = $el.attr('style') || '';
    const className = $el.attr('class') || '';
    const id = $el.attr('id') || '';
    const styles = parseStyle(style);

    // Headings (h1 -> h6)
    if (/^h[1-6]$/.test(tag)) {
        const level = tag.replace('h', '');
        const vbcTag = `vbc_h${level}`;
        const text = decodeHtmlEntities($el.text().trim());
        let attrs = '';
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        if (id) attrs += ` id="${escapeAttr(id)}"`;
        const customCss = stylesToCustomCss(styles);
        if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
        return `[${vbcTag}${attrs} content="${escapeAttr(text)}"][/${vbcTag}]`;
    }

    // Paragraph
    if (tag === 'p') {
        const text = decodeHtmlEntities($el.text().trim());
        if (!text && $el.children().length === 0) return '';
        let attrs = '';
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        if (id) attrs += ` id="${escapeAttr(id)}"`;
        const customCss = stylesToCustomCss(styles);
        if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;
        
        if ($el.children().length > 0) {
            const children = convertChildren($el, $, urlMapping, depth + 1);
            return `[vbc_p${attrs}]${children}[/vbc_p]`;
        }
        return `[vbc_p${attrs} content="${escapeAttr(text)}"][/vbc_p]`;
    }

    // Image
    if (tag === 'img') {
        let src = $el.attr('src') || $el.attr('data-src') || $el.attr('data-lazy-src') || '';
        const alt = escapeAttr(decodeHtmlEntities($el.attr('alt') || ''));
        const width = $el.attr('width') || '';
        const height = $el.attr('height') || '';
        if (urlMapping && urlMapping.get(src)) src = urlMapping.get(src);
        if (!src || src.startsWith('data:')) return '';
        
        let attrs = `img_source="external" img_url="${src}"`;
        if (alt) attrs += ` alt="${alt}"`;
        if (width) attrs += ` width="${width}"`;
        if (height) attrs += ` height="${height}"`;
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        return `[vbc_img ${attrs}][/vbc_img]`;
    }

    // Link (a)
    if (tag === 'a') {
        const href = decodeHtmlEntities($el.attr('href') || '#');
        const target = $el.attr('target') || '';
        let attrs = `link_url="${escapeAttr(href)}"`;
        if (target) attrs += ` link_target="${target}"`;
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        if (id) attrs += ` id="${escapeAttr(id)}"`;
        
        if ($el.children().length > 0) {
            const children = convertChildren($el, $, urlMapping, depth + 1);
            return `[vbc_a ${attrs}]${children}[/vbc_a]`;
        }
        const text = decodeHtmlEntities($el.text().trim());
        return `[vbc_a ${attrs} content="${escapeAttr(text)}"][/vbc_a]`;
    }

    // Span, Strong, Em, B, I, U
    if (['span', 'strong', 'b', 'em', 'i', 'u'].includes(tag)) {
        const vbcTag = (tag === 'strong' || tag === 'b') ? 'vbc_strong' : 
                       (tag === 'em' || tag === 'i') ? 'vbc_em' : 
                       (tag === 'u') ? 'vbc_u' : 'vbc_span';
        const text = decodeHtmlEntities($el.text().trim());
        if (!text && $el.children().length === 0) return '';
        let attrs = '';
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        if (id) attrs += ` id="${escapeAttr(id)}"`;
        if ($el.children().length > 0) {
            const children = convertChildren($el, $, urlMapping, depth + 1);
            return `[${vbcTag}${attrs}]${children}[/${vbcTag}]`;
        }
        return `[${vbcTag}${attrs} content="${escapeAttr(text)}"][/${vbcTag}]`;
    }

    // Lists (ul, ol, li)
    if (tag === 'ul' || tag === 'ol') {
        let items = '';
        $el.find('> li').each((i, li) => {
            const $li = $(li);
            const text = decodeHtmlEntities($li.text().trim());
            const liClass = $li.attr('class') || '';
            let liAttrs = '';
            if (liClass) liAttrs += ` class="${escapeAttr(liClass)}"`;
            if ($li.children().length > 0) {
                const subChildren = convertChildren($li, $, urlMapping, depth + 1);
                items += `[vbc_li${liAttrs}]${subChildren}[/vbc_li]`;
            } else {
                items += `[vbc_li${liAttrs} content="${escapeAttr(text)}"][/vbc_li]`;
            }
        });
        const vbcTag = tag === 'ul' ? 'vbc_ul' : 'vbc_ol';
        let attrs = '';
        if (className) attrs += ` class="${escapeAttr(className)}"`;
        if (id) attrs += ` id="${escapeAttr(id)}"`;
        return `[${vbcTag}${attrs}]${items}[/${vbcTag}]`;
    }

    // Layout Containers
    let vbcTag = 'vbc_div';
    if (className.includes('container') || className.includes('box') || className.includes('card') || className.includes('shield')) {
        vbcTag = 'vbc_box';
    } else if (className.includes('row') || className.includes('grid') || className.includes('flex')) {
        vbcTag = 'vbc_block';
    } else if (className.includes('col') || className.includes('item') || className.includes('badge')) {
        vbcTag = 'vbc_container';
    }

    let attrs = '';
    if (id) attrs += ` id="${escapeAttr(id)}"`;
    if (className) attrs += ` class="${escapeAttr(className)}"`;
    const customCss = stylesToCustomCss(styles);
    if (customCss) attrs += ` custom_css="${escapeAttr(customCss)}"`;

    const children = convertChildren($el, $, urlMapping, depth + 1);
    if (!children.trim()) {
        const text = decodeHtmlEntities($el.text().trim());
        if (!text) return '';
        return `[${vbcTag}${attrs}][vbc_p content="${escapeAttr(text)}"][/vbc_p][/${vbcTag}]`;
    }

    return `[${vbcTag}${attrs}]${children}[/${vbcTag}]`;
}

function convertChildren($el, $, urlMapping, depth = 0) {
    let result = '';
    $el.contents().each((i, node) => {
        if (node.type === 'text') {
            const text = decodeHtmlEntities(node.data.trim());
            if (text) {
                result += `[vbc_span content="${escapeAttr(text)}"][/vbc_span]`;
            }
        } else if (node.type === 'tag') {
            result += convertElement($(node), $, urlMapping, depth);
        }
    });
    return result;
}

// ============================================================
// 3. ELEMENTOR SPECIFIC COMPILER (PARSES DATA-SETTINGS JSON)
// ============================================================

/**
 * Biên dịch Elementor Sections, Columns & Widgets thành Flatsome + VBC
 */
function translateElementorToVbc($, urlMapping = new Map(), idMapping = new Map()) {
    console.log('\n\x1b[36m[ELEMENTOR TRANSLATOR] Phát hiện trang Elementor! Bắt đầu giải mã data-settings...\x1b[0m');
    
    let allCssBlocks = [];
    let fullOutput = '';
    let sectionCount = 0;

    $('.elementor-top-section').each((secIdx, secEl) => {
        const $sec = $(secEl);
        let secSettings = {};
        try {
            secSettings = JSON.parse($sec.attr('data-settings') || '{}');
        } catch(e) {}
        
        // Build Flatsome [section] attributes
        let sectionAttrs = 'width="full_width"';
        
        // Background Image from data-settings
        if (secSettings.background_image && secSettings.background_image.url) {
            let bgUrl = secSettings.background_image.url;
            if (urlMapping.get(bgUrl)) bgUrl = urlMapping.get(bgUrl);
            const bgId = idMapping.get(bgUrl) || '';
            if (bgId) sectionAttrs += ` bg="${bgId}"`;
            else sectionAttrs += ` bg_image="${escapeAttr(bgUrl)}"`;
            sectionAttrs += ' bg_size="cover" bg_pos="center"';
        }
        
        // Background Color from data-settings or inline style
        if (secSettings.background_background === 'classic' && secSettings.background_color) {
            sectionAttrs += ` bg_color="${secSettings.background_color}"`;
        }
        
        // Padding from data-settings
        if (secSettings.padding && secSettings.padding.top) {
            sectionAttrs += ` padding="${secSettings.padding.top}${secSettings.padding.unit || 'px'}"`;
        }
        
        let secBody = '';
        
        // Columns inside section
        $sec.find('> .elementor-container > .elementor-column').each((colIdx, colEl) => {
            const $col = $(colEl);
            let colSettings = {};
            try {
                colSettings = JSON.parse($col.attr('data-settings') || '{}');
            } catch(e) {}
            
            // Determine column span
            let colSpan = '12';
            const colClass = $col.attr('class') || '';
            const mCol = colClass.match(/elementor-col-(\d+)/);
            if (mCol) {
                const pct = parseInt(mCol[1]);
                if (pct >= 90) colSpan = '12';
                else if (pct >= 60) colSpan = '8';
                else if (pct >= 45) colSpan = '6';
                else if (pct >= 30) colSpan = '4';
                else if (pct >= 20) colSpan = '3';
                else if (pct >= 15) colSpan = '2';
            }
            
            let colContent = '';
            
            // Widgets inside column
            $col.find('> .elementor-widget-wrap > .elementor-widget, > .elementor-column-wrap > .elementor-widget-wrap > .elementor-widget').each((wIdx, wEl) => {
                const $w = $(wEl);
                const wType = $w.attr('data-widget_type') || '';
                let wSettings = {};
                try {
                    wSettings = JSON.parse($w.attr('data-settings') || '{}');
                } catch(e) {}
                
                const container = $w.find('> .elementor-widget-container');
                
                // Extract style blocks
                container.find('style').each((sIdx, sEl) => {
                    let css = $(sEl).html().trim();
                    if (css) {
                        // Apply url mapping inside CSS if any
                        for (const [orig, up] of urlMapping.entries()) {
                            css = css.split(orig).join(up);
                        }
                        allCssBlocks.push(css);
                    }
                    $(sEl).remove();
                });
                
                // Remove meta / title / link clutter
                container.find('meta, title, link').remove();
                
                // Convert each root child inside widget container to VBC elements
                container.children().each((cIdx, ch) => {
                    const converted = convertElement($(ch), $, urlMapping, 0);
                    if (converted.trim()) {
                        colContent += converted + '\n';
                    }
                });
            });
            
            if (colContent.trim()) {
                secBody += `[row]\n[col span="${colSpan}" span__sm="12"]\n${colContent}[/col]\n[/row]\n`;
            }
        });
        
        if (secBody.trim()) {
            fullOutput += `[section ${sectionAttrs}]\n${secBody}[/section]\n\n`;
            sectionCount++;
        }
    });

    // Prepend combined CSS in a clean style block
    if (allCssBlocks.length > 0) {
        const combinedCss = allCssBlocks.join('\n\n');
        fullOutput = `<style>\n${combinedCss}\n</style>\n\n` + fullOutput;
    }

    console.log(`  ✓ Đã dịch thành công ${sectionCount} Elementor Top Sections sang Flatsome [section] + VBC!`);
    console.log(`  ✓ Đã bảo tồn và nhúng ${allCssBlocks.length} khối CSS đặc thù (${Buffer.byteLength(allCssBlocks.join('\n'))} bytes)`);
    
    return fullOutput;
}

// ============================================================
// 4. THEME NOISE CLEANER & MAIN CONTENT EXTRACTOR
// ============================================================

function stripThemeNoise($) {
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
}

function extractMainContent($) {
    const selectors = [
        '.elementor',
        'main .entry-content',
        'main article .entry-content',
        '#main .entry-content',
        '.penci-inner-content',
        '.penci-content-area',
        '.entry-content',
        'article.type-page .content',
        'article .content',
        '.post-content',
        '.page-content',
        '.wrapper-boxed',
        '.site-wrapper',
        'main',
        '#content',
        '#primary',
        'body'
    ];

    for (const sel of selectors) {
        const $el = $(sel).first();
        if (!$el.length) continue;
        const textLen = $el.text().trim().length;
        if (textLen > 200) {
            return $el;
        }
    }
    return $('body');
}

// ============================================================
// 5. MAIN ENTRY POINT: compileHtmlToVbc
// ============================================================

function compileHtmlToVbc(htmlContent, urlMapping = new Map(), idMapping = new Map()) {
    console.log('\n\x1b[35m[HTML→VBC COMPILER] Bắt đầu phân tích cấu trúc DOM trang nguồn...\x1b[0m');
    
    const $ = cheerio.load(htmlContent, { decodeEntities: false });
    
    const pageTitle = $('title').text() || 'Cloned Page';
    console.log(`  ✓ Tiêu đề trang: ${pageTitle}`);

    // Check if page contains Elementor sections
    const hasElementor = $('.elementor-top-section, .elementor-section').length > 0;
    if (hasElementor) {
        return translateElementorToVbc($, urlMapping, idMapping);
    }

    // Otherwise use standard DOM compiler
    stripThemeNoise($);
    let $main = extractMainContent($);

    let vbcOutput = '';
    let sectionCount = 0;
    const blockTags = ['div', 'section', 'article', 'header', 'footer', 'aside', 'nav', 'figure', 'main'];

    $main.children().each((i, child) => {
        const $child = $(child);
        const tag = $child.prop('tagName') ? $child.prop('tagName').toLowerCase() : '';
        if (!tag || ['script', 'style', 'noscript', 'link', 'meta', 'template'].includes(tag)) return;
        
        const text = $child.text().trim();
        const imgCount = $child.find('img').length;
        if (!text && imgCount === 0) return;

        const converted = convertElement($child, $, urlMapping, 0);
        if (!converted.trim()) return;

        if (blockTags.includes(tag)) {
            vbcOutput += `[section width="full_width"]\n[row]\n[col span="12" span__sm="12"]\n${converted}\n[/col]\n[/row]\n[/section]\n\n`;
            sectionCount++;
        } else {
            vbcOutput += converted + '\n';
        }
    });

    console.log(`  ✓ Đã biên dịch ${sectionCount} sections chuẩn sang VBC shortcodes`);
    return vbcOutput;
}

module.exports = { compileHtmlToVbc, decodeHtmlEntities };
