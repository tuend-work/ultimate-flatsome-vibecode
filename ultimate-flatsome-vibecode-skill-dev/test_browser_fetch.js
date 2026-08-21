const puppeteer = require('puppeteer-core');
const fs = require('fs');

function findChromePath() {
    const candidatePaths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        process.env.LOCALAPPDATA + '\\Google\\Chrome\\Application\\chrome.exe'
    ];

    for (const p of candidatePaths) {
        if (p && fs.existsSync(p)) {
            return p;
        }
    }
    return null;
}

async function fetchRenderedHtml(url, options = {}) {
    const executablePath = options.chromePath || findChromePath();
    if (!executablePath) {
        throw new Error('Không tìm thấy trình duyệt Chrome hoặc Edge trên hệ thống!');
    }

    console.log(`\n\x1b[36m[HEADLESS BROWSER] Đang khởi chạy trình duyệt: ${executablePath}...\x1b[0m`);
    
    const browser = await puppeteer.launch({
        executablePath,
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--window-size=1440,900'
        ]
    });

    try {
        const page = await browser.newPage();
        await page.setViewport({ width: 1440, height: 900 });
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36');

        console.log(`  ✓ Đang tải URL và chờ JS render: \x1b[36m${url}\x1b[0m...`);
        await page.goto(url, {
            waitUntil: 'networkidle2',
            timeout: 60000
        });

        // Trigger lazy loads by auto-scrolling
        console.log('  ✓ Đang tự động cuộn trang để kích hoạt Lazy-load & Render động...');
        await page.evaluate(async () => {
            await new Promise((resolve) => {
                let totalHeight = 0;
                const distance = 400;
                const timer = setInterval(() => {
                    const scrollHeight = document.body.scrollHeight;
                    window.scrollBy(0, distance);
                    totalHeight += distance;

                    if (totalHeight >= scrollHeight) {
                        clearInterval(timer);
                        window.scrollTo(0, 0);
                        resolve();
                    }
                }, 100);
            });
        });

        // Wait an extra second for any animations or remaining network calls
        await new Promise(r => setTimeout(r, 1500));

        // Get fully rendered HTML
        const renderedHtml = await page.content();
        console.log(`  ✓ Thu thập thành công ${Buffer.byteLength(renderedHtml)} bytes HTML đã render JS 100%!`);
        
        await browser.close();
        return renderedHtml;
    } catch (err) {
        await browser.close();
        throw err;
    }
}

// Test
const testUrl = 'https://damtrungkien.com/dich-vu-xu-ly-ma-doc-wordpress/';
fetchRenderedHtml(testUrl)
    .then(html => {
        fs.writeFileSync('f:/DEV/ultimate-flatsome-vibecode/tmp/rendered_page.html', html, 'utf8');
        console.log('✓ Saved to f:/DEV/ultimate-flatsome-vibecode/tmp/rendered_page.html');
    })
    .catch(err => {
        console.error('Lỗi fetchRenderedHtml:', err);
    });
