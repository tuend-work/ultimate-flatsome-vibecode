const https = require('https');
const cheerio = require('cheerio');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        $('.elementor-top-section').each((i, sec) => {
            const $sec = $(sec);
            const dataSettings = $sec.attr('data-settings') || '{}';
            console.log(`\n========================================`);
            console.log(`TOP SECTION [${i}] (data-id: ${$sec.attr('data-id')})`);
            console.log(`data-settings:`, dataSettings);
            
            // Check columns
            $sec.find('> .elementor-container > .elementor-column').each((cIdx, col) => {
                const $col = $(col);
                const colSettings = $col.attr('data-settings') || '{}';
                const colWidth = $col.attr('data-col') || '';
                console.log(`  COLUMN [${cIdx}] width=${colWidth} settings=${colSettings}`);
                
                // Widgets
                $col.find('.elementor-widget').each((wIdx, w) => {
                    const $w = $(w);
                    const wType = $w.attr('data-widget_type');
                    const wSettings = $w.attr('data-settings') || '{}';
                    const container = $w.find('.elementor-widget-container');
                    const htmlLen = container.html() ? container.html().length : 0;
                    const textSample = container.text().trim().replace(/\s+/g, ' ').substring(0, 100);
                    
                    console.log(`    WIDGET [${wIdx}] type="${wType}" htmlLen=${htmlLen}`);
                    console.log(`    settings: ${wSettings}`);
                    console.log(`    text: "${textSample}"`);
                    
                    // Check elements inside widget container
                    const subTags = [];
                    container.children().each((j, ch) => {
                        subTags.push(`<${ch.name} class="${$(ch).attr('class') || ''}" id="${$(ch).attr('id') || ''}">`);
                    });
                    console.log(`    children: ${subTags.join(', ')}`);
                });
            });
        });
    });
}).on('error', err => console.error(err));
