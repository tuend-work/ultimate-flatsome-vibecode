/**
 * ============================================================================
 * HEADLESS BROWSER FETCHER (PUPPETEER-CORE + CHROME/EDGE)
 * Tự động tải trang bằng trình duyệt thực, thực thi JavaScript, cuộn trang
 * để kích hoạt toàn bộ Lazy Load hình ảnh, CSS động và thành phần tương tác.
 * ============================================================================
 */

const fs = require('fs');
let puppeteer = null;
try {
    puppeteer = require('puppeteer-core');
} catch (e) {
    // Sẽ fallback sang https nếu chưa cài
}

/**
 * Tìm đường dẫn thực thi của Google Chrome hoặc Microsoft Edge trên máy chủ/máy cục bộ
 */
function findBrowserExecutable() {
    const candidatePaths = [
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        (process.env.LOCALAPPDATA || '') + '\\Google\\Chrome\\Application\\chrome.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium-browser',
        '/usr/bin/chromium',
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
    ];

    for (const p of candidatePaths) {
        if (p && fs.existsSync(p)) {
            return p;
        }
    }
    return null;
}

/**
 * Tải HTML đã render hoàn chỉnh bằng Headless Browser
 * @param {string} url - URL cần render
 * @param {object} options - Cấu hình bổ sung (waitTime, viewport, customChromePath)
 * @returns {Promise<string>} - HTML đầy đủ sau khi render JS
 */
async function fetchRenderedHtmlWithBrowser(url, options = {}) {
    if (!puppeteer) {
        throw new Error('Thư viện puppeteer-core chưa được cài đặt!');
    }

    const executablePath = options.chromePath || findBrowserExecutable();
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
            timeout: options.timeout || 60000
        });

        // Tự động cuộn trang để kích hoạt Lazy-load hình ảnh & hoạt ảnh
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

        // Chờ thêm một khoảng ngắn để mọi hiệu ứng/style hoàn tất
        const waitExtra = options.waitTime || 1500;
        await new Promise(r => setTimeout(r, waitExtra));

        // Lấy toàn bộ HTML đã render JS
        const renderedHtml = await page.content();
        console.log(`  ✓ Thu thập thành công ${Buffer.byteLength(renderedHtml)} bytes HTML đã render JS 100%!`);
        
        await browser.close();
        return renderedHtml;
    } catch (err) {
        await browser.close();
        throw err;
    }
}

module.exports = {
    fetchRenderedHtmlWithBrowser,
    findBrowserExecutable
};
