const https = require('https');
const cheerio = require('cheerio');
const fs = require('fs');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

// Utility functions
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

function convertElement($el, $, urlMapping = new Map(), depth = 0) {
    const tag = $el.prop('tagName') ? $el.prop('tagName').toLowerCase() : '';
    if (!tag) return '';
    if (['script', 'style', 'noscript', 'meta', 'link', 'template', 'head'].includes(tag)) return '';

    const style = $el.attr('style') || '';
    const className = $el.attr('class') || '';
    const id = $el.attr('id') || '';
    const styles = parseStyle(style);

    // Headings
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
        
        // If has complex children (like links, images, icons)
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

    // Link
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

    // Span, Strong, Em
    if (['span', 'strong', 'b', 'em', 'i'].includes(tag)) {
        const vbcTag = (tag === 'strong' || tag === 'b') ? 'vbc_strong' : (tag === 'em' || tag === 'i') ? 'vbc_em' : 'vbc_span';
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

    // List
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

    // Container / Div / Box / Section
    let vbcTag = 'vbc_div';
    if (className.includes('container') || className.includes('box') || className.includes('card')) {
        vbcTag = 'vbc_box';
    } else if (className.includes('row') || className.includes('grid') || className.includes('flex')) {
        vbcTag = 'vbc_block';
    } else if (className.includes('col') || className.includes('item')) {
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

// MAIN EXECUTION
let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        console.log('=== TRANSLATING ELEMENTOR TO VBC ===');
        
        let allCssBlocks = [];
        let fullOutput = '';
        let sectionCount = 0;

        $('.elementor-top-section').each((secIdx, secEl) => {
            const $sec = $(secEl);
            let secSettings = {};
            try {
                secSettings = JSON.parse($sec.attr('data-settings') || '{}');
            } catch(e) {}
            
            // Build section attributes from data-settings and styles
            let sectionAttrs = 'width="full_width"';
            
            if (secSettings.background_background === 'classic' && secSettings.background_color) {
                sectionAttrs += ` bg_color="${secSettings.background_color}"`;
            }
            if (secSettings.padding && secSettings.padding.top) {
                sectionAttrs += ` padding="${secSettings.padding.top}${secSettings.padding.unit || 'px'}"`;
            }
            
            let secBody = '';
            
            // Iterate columns
            $sec.find('> .elementor-container > .elementor-column').each((colIdx, colEl) => {
                const $col = $(colEl);
                let colSettings = {};
                try {
                    colSettings = JSON.parse($col.attr('data-settings') || '{}');
                } catch(e) {}
                
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
                }
                
                let colContent = '';
                
                // Find all widgets inside column
                $col.find('> .elementor-widget-wrap > .elementor-widget, > .elementor-column-wrap > .elementor-widget-wrap > .elementor-widget').each((wIdx, wEl) => {
                    const $w = $(wEl);
                    const wType = $w.attr('data-widget_type') || '';
                    const container = $w.find('> .elementor-widget-container');
                    
                    // Extract styles
                    container.find('style').each((sIdx, sEl) => {
                        const css = $(sEl).html().trim();
                        if (css) allCssBlocks.push(css);
                        $(sEl).remove();
                    });
                    
                    container.find('meta, title, link').remove();
                    
                    // Convert each root child inside widget container
                    container.children().each((cIdx, ch) => {
                        const converted = convertElement($(ch), $, new Map(), 0);
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
        
        // Append all collected CSS in a clean style block or VBC container
        if (allCssBlocks.length > 0) {
            const combinedCss = allCssBlocks.join('\n\n');
            fullOutput = `<style>\n${combinedCss}\n</style>\n\n` + fullOutput;
        }
        
        console.log(`✓ Translated ${sectionCount} Elementor top sections`);
        console.log(`✓ Total Output Size: ${Buffer.byteLength(fullOutput)} bytes`);
        console.log(`✓ Total CSS size: ${Buffer.byteLength(allCssBlocks.join('\n'))} bytes`);
        
        fs.writeFileSync('f:/DEV/ultimate-flatsome-vibecode/tmp/elementor_translated.vbc', fullOutput, 'utf8');
        console.log('✓ Saved to f:/DEV/ultimate-flatsome-vibecode/tmp/elementor_translated.vbc');
    });
}).on('error', err => console.error(err));
