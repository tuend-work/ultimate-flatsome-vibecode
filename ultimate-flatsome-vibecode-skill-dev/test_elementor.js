const https = require('https');
const cheerio = require('cheerio');
const fs = require('fs');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        console.log('--- ELEMENTOR ANALYSIS ---');
        console.log('Total elementor sections:', $('.elementor-section').length);
        console.log('Total elementor columns:', $('.elementor-column').length);
        console.log('Total elementor widgets:', $('.elementor-widget').length);
        
        // Check data-settings
        const dataSettingsElements = $('[data-settings]');
        console.log('Elements with data-settings:', dataSettingsElements.length);
        dataSettingsElements.each((i, el) => {
            if (i < 10) {
                console.log(`[${i}] <${el.name}> class="${$(el).attr('class')}":`);
                console.log('  data-settings:', $(el).attr('data-settings'));
            }
        });

        // Check elementor-widget types
        const widgetTypes = {};
        $('.elementor-widget').each((i, el) => {
            const widgetType = $(el).attr('data-widget_type') || 'unknown';
            widgetTypes[widgetType] = (widgetTypes[widgetType] || 0) + 1;
        });
        console.log('\nWidget types:', widgetTypes);

        // Check #dv-clean-malware and its direct structure
        console.log('\n#dv-clean-malware exists:', $('#dv-clean-malware').length);
        if ($('#dv-clean-malware').length) {
            console.log('#dv-clean-malware child tags:');
            $('#dv-clean-malware').children().each((i, el) => {
                console.log(`  [${i}] <${el.name}> class="${$(el).attr('class')}" id="${$(el).attr('id')}"`);
            });
        }

        // Check all major sections inside .elementor or body
        console.log('\nAll sections/containers on page:');
        $('section, .elementor-section, .elementor-top-section').each((i, el) => {
            const cls = $(el).attr('class') || '';
            const id = $(el).attr('id') || '';
            const headings = $(el).find('h1, h2, h3, h4').map((j, h) => $(h).text().trim()).get().join(' | ');
            console.log(`Section [${i}] <${el.name}> id="${id}" class="${cls.substring(0, 60)}" -> Headings: "${headings.substring(0, 80)}"`);
        });

        // Headings check
        console.log('\nAll headings in body:');
        $('h1, h2, h3, h4, h5, h6').each((i, el) => {
            const text = $(el).text().trim().replace(/\s+/g, ' ');
            if (text.length > 0) {
                console.log(`  <${el.name}>: ${text.substring(0, 80)}`);
            }
        });
    });
}).on('error', err => console.error(err));
