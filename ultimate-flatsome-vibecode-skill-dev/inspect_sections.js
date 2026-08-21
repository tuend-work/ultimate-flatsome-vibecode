const https = require('https');
const cheerio = require('cheerio');
const fs = require('fs');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        console.log('--- ELEMENTOR SECTIONS IN #dv-clean-malware ---');
        const mainWrap = $('#dv-clean-malware');
        console.log('mainWrap length:', mainWrap.length);
        if (mainWrap.length) {
            mainWrap.children().each((i, el) => {
                const $el = $(el);
                const tag = el.name;
                const cls = $el.attr('class') || '';
                const id = $el.attr('id') || '';
                const text = $el.text().trim().replace(/\s+/g, ' ').substring(0, 100);
                const imgs = $el.find('img').length;
                console.log(`Child [${i}] <${tag}> id="${id}" class="${cls.substring(0,50)}" imgs=${imgs} text="${text}"`);
            });
        }

        // Also check if there are sections inside .elementor-element
        console.log('\n--- ELEMENTOR TOP SECTIONS ---');
        $('.elementor-top-section').each((i, el) => {
            const $el = $(el);
            const dataSettings = $el.attr('data-settings');
            const dataId = $el.attr('data-id');
            const cls = $el.attr('class');
            console.log(`TopSection [${i}] data-id="${dataId}" class="${cls.substring(0, 50)}"`);
            if (dataSettings) console.log(`  data-settings: ${dataSettings}`);
            
            // Check widgets in this top-section
            $el.find('.elementor-widget').each((j, w) => {
                const $w = $(w);
                const wType = $w.attr('data-widget_type');
                const wSettings = $w.attr('data-settings');
                console.log(`    Widget [${j}] type="${wType}" settings=${wSettings || 'none'}`);
            });
        });
        
        // Let's check where the actual landing page content is
        console.log('\n--- LANDING PAGE SECTIONS IN MAIN CONTAINER ---');
        $('#dv-clean-malware > *').each((i, el) => {
            const $el = $(el);
            console.log(`\n=== Section [${i}] <${el.name}> class="${$el.attr('class')}" id="${$el.attr('id')}" ===`);
            // Check headings
            $el.find('h1, h2, h3, h4, h5').each((j, h) => {
                console.log(`  <${h.name}>: ${$(h).text().trim().replace(/\s+/g, ' ')}`);
            });
        });
    });
}).on('error', err => console.error(err));
