// Debug: xem structure của .entry-content
const https = require('https');
const cheerio = require('cheerio');

const url = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';

let html = '';
https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
    res.on('data', d => html += d);
    res.on('end', () => {
        const $ = cheerio.load(html, { decodeEntities: false });
        
        // Thử các selectors
        const selectors = [
            'main .entry-content', 'main article', '#main .entry-content',
            '.entry-content', 'main', '#content', '.site-content', 
            'article.post', '.page-content', '#primary', '.content-area'
        ];
        
        for (const sel of selectors) {
            const $el = $(sel);
            const childrenCount = $el.children().length;
            const textLen = $el.text().trim().length;
            console.log(`${sel}: found=${$el.length}, children=${childrenCount}, text=${textLen}`);
        }
        
        console.log('\n=== BODY DIRECT CHILDREN ===');
        $('body').children().each((i, el) => {
            const $el = $(el);
            const tag = el.name;
            const cls = $el.attr('class') || '';
            const childCount = $el.children().length;
            const txt = $el.text().trim().length;
            console.log(`[${i}] <${tag}> class="${cls}" children=${childCount} text=${txt}`);
        });
        
        // Xem .entry-content children
        console.log('\n=== .entry-content CHILDREN ===');
        $('.entry-content').children().each((i, el) => {
            const $el = $(el);
            const tag = el.name;
            const cls = $el.attr('class') || '';
            const txt = $el.text().trim().substring(0, 60);
            console.log(`[${i}] <${tag}> class="${cls.substring(0,50)}" | "${txt}"`);
        });
    });
}).on('error', err => console.error(err));
