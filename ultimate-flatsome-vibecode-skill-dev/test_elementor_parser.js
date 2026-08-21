const https = require('https');
const cheerio = require('cheerio');
const fs = require('fs');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        console.log('--- TESTING ELEMENTOR PARSER ---');
        
        let allCollectedCss = [];
        let vbcSections = [];

        $('.elementor-top-section').each((secIdx, secEl) => {
            const $sec = $(secEl);
            let secSettings = {};
            try {
                secSettings = JSON.parse($sec.attr('data-settings') || '{}');
            } catch(e) {}
            
            console.log(`\nSection [${secIdx}] settings:`, secSettings);
            
            let secContent = '';
            
            $sec.find('> .elementor-container > .elementor-column').each((colIdx, colEl) => {
                const $col = $(colEl);
                let colSettings = {};
                try {
                    colSettings = JSON.parse($col.attr('data-settings') || '{}');
                } catch(e) {}
                
                // Determine col width
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
                
                let colWidgetsContent = '';
                
                $col.find('> .elementor-widget-wrap > .elementor-widget, > .elementor-column-wrap > .elementor-widget-wrap > .elementor-widget').each((wIdx, wEl) => {
                    const $w = $(wEl);
                    const wType = $w.attr('data-widget_type') || '';
                    const container = $w.find('> .elementor-widget-container');
                    
                    // Extract styles from widget
                    container.find('style').each((sIdx, sEl) => {
                        allCollectedCss.push($(sEl).html());
                        $(sEl).remove();
                    });
                    
                    // Clean meta / title tags inside widget if any
                    container.find('meta, title, link').remove();
                    
                    console.log(`  Col [${colIdx}] Widget [${wIdx}] type="${wType}": container children = ${container.children().length}`);
                    container.children().each((cIdx, ch) => {
                        console.log(`    Child [${cIdx}] <${ch.name} id="${$(ch).attr('id')}" class="${$(ch).attr('class')}"> textLen=${$(ch).text().trim().length}`);
                    });
                });
            });
        });
        
        console.log(`\nTotal collected CSS blocks: ${allCollectedCss.length}`);
        console.log(`Total collected CSS bytes: ${allCollectedCss.join('\n').length}`);
    });
}).on('error', err => console.error(err));
