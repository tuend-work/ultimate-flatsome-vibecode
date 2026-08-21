const https = require('https');
const cheerio = require('cheerio');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        console.log('=== CHECKING STYLES IN WIDGETS ===');
        $('.elementor-widget-html').each((i, el) => {
            const $el = $(el);
            const styleCount = $el.find('style').length;
            const divIds = $el.find('> .elementor-widget-container > div').map((j, d) => $(d).attr('id') || $(d).attr('class')).get();
            console.log(`Widget-HTML [${i}]: styles=${styleCount}, roots=${divIds.join(', ')}`);
        });

        // Let's inspect Widget [0] structure inside #dv-clean-malware
        console.log('\n=== #dv-clean-malware inner HTML structure ===');
        $('#dv-clean-malware').children().each((i, el) => {
            console.log(`Child [${i}] <${el.name}> class="${$(el).attr('class')}"`);
            $(el).children().each((j, sub) => {
                console.log(`   Sub [${j}] <${sub.name}> class="${$(sub).attr('class')}"`);
            });
        });

        // Check why <style> was converted to text
        console.log('\n=== Check style tag handling ===');
        const firstStyle = $('#dv-clean-malware').prev('style').text();
        console.log('Style tag before #dv-clean-malware length:', firstStyle ? firstStyle.length : 'none');
    });
}).on('error', err => console.error(err));
