#!/usr/bin/env node

/**
 * CLI Skill: Tạo Landing Page từ hình ảnh & mô tả qua Gemini và đẩy lên WordPress
 * Ngôn ngữ: Node.js (Yêu cầu Node.js 18+ để hỗ trợ fetch toàn cục)
 * Sử dụng: node skills/create-landing-page.js [arguments]
 */

const fs = require('fs');
const path = require('path');

// 1. Phân tích tham số dòng lệnh (CLI Arguments)
function parseArgs() {
    const args = {};
    for (let i = 2; i < process.argv.length; i++) {
        const arg = process.argv[i];
        if (arg.startsWith('--')) {
            const key = arg.slice(2);
            const nextVal = process.argv[i + 1];
            if (nextVal && !nextVal.startsWith('--')) {
                args[key] = nextVal;
                i++;
            } else {
                args[key] = true;
            }
        }
    }
    return args;
}

async function main() {
    const args = parseArgs();

    // Hiển thị Banner giới thiệu
    console.log('\x1b[36m==================================================\x1b[0m');
    console.log('\x1b[1m\x1b[36m   VIBECODE AI LANDING PAGE BUILDER SKILL\x1b[0m');
    console.log('\x1b[36m==================================================\x1b[0m');

    // Đọc file config nếu có
    let config = {};
    const configPath = path.join(process.cwd(), 'vbc-config.json');
    if (fs.existsSync(configPath)) {
        try {
            config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        } catch (e) {
            console.warn('\x1b[33m[CẢNH BÁO] Không thể đọc file vbc-config.json: ' + e.message + '\x1b[0m');
        }
    }

    // Kiểm tra các thông số bắt buộc
    const apiUrl = args['api-url'] || config['api-url'] || config['apiUrl'];
    const token = args['token'] || config['token'];
    const description = args['description'];
    const apiKey = process.env.GEMINI_API_KEY || args['gemini-key'] || config['gemini-key'] || config['geminiKey'];

    if (!apiUrl) {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc --api-url (Ví dụ: https://my-site.com/wp-json)\x1b[0m');
        process.exit(1);
    }
    if (!token) {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc --token (Lấy từ User Profile trong WordPress Admin)\x1b[0m');
        process.exit(1);
    }
    if (!description) {
        console.error('\x1b[31m[LỖI] Thiếu tham số bắt buộc --description (Mô tả nội dung trang)\x1b[0m');
        process.exit(1);
    }
    if (!apiKey) {
        console.error('\x1b[31m[LỖI] Thiếu GEMINI_API_KEY trong biến môi trường hoặc tham số --gemini-key\x1b[0m');
        process.exit(1);
    }

    const title = args['title'] || 'Landing Page Generated';
    const slug = args['slug'] || '';
    const postId = args['post-id'] || '';
    const imageRef = args['image-ref']; // Đường dẫn ảnh chụp layout mẫu (nếu có)
    const imageUpload = args['image-upload']; // Danh sách ảnh cần upload (phân cách bằng dấu phẩy)

    const uploadedAssets = [];

    // 2. Thực hiện Tải ảnh tài nguyên lên WordPress (nếu có)
    if (imageUpload) {
        const filePaths = imageUpload.split(',').map(p => p.trim());
        console.log(`\n\x1b[33m[1/4] Đang chuẩn bị tải lên ${filePaths.length} ảnh tài nguyên...\x1b[0m`);

        for (const filePath of filePaths) {
            const absolutePath = path.resolve(filePath);
            if (!fs.existsSync(absolutePath)) {
                console.warn(`\x1b[31m[CẢNH BÁO] File ảnh không tồn tại tại: ${absolutePath}. Bỏ qua.\x1b[0m`);
                continue;
            }

            try {
                console.log(` -> Đang tải lên: ${path.basename(absolutePath)}...`);
                const fileBuffer = fs.readFileSync(absolutePath);
                const blob = new Blob([fileBuffer]);
                const formData = new FormData();
                formData.append('file', blob, path.basename(absolutePath));

                // Gọi REST API upload
                const uploadRes = await fetch(`${apiUrl.replace(/\/$/, '')}/vbc/v1/upload`, {
                    method: 'POST',
                    headers: {
                        'X-VBC-Token': token
                    },
                    body: formData
                });

                if (!uploadRes.ok) {
                    const errData = await uploadRes.json();
                    throw new Error(errData.message || `HTTP ${uploadRes.status}`);
                }

                const result = await uploadRes.json();
                if (result.success) {
                    console.log(`    \x1b[32m✓ Tải lên thành công! ID: ${result.attachment_id}, URL: ${result.url}\x1b[0m`);
                    uploadedAssets.push({
                        id: result.attachment_id,
                        url: result.url,
                        filename: path.basename(filePath)
                    });
                }
            } catch (error) {
                console.error(`    \x1b[31m✗ Tải ảnh ${path.basename(filePath)} thất bại: ${error.message}\x1b[0m`);
            }
        }
    } else {
        console.log('\n\x1b[33m[1/4] Không có ảnh tài nguyên cần tải lên.\x1b[0m');
    }

    // 3. Chuẩn bị prompt và gọi Gemini API
    console.log('\n\x1b[33m[2/4] Đang phân tích yêu cầu và gọi Gemini API tạo shortcode...\x1b[0m');

    // Khai báo các ảnh đã upload để Gemini biết và sử dụng
    let uploadAssetsInfo = '';
    if (uploadedAssets.length > 0) {
        uploadAssetsInfo = '\nCác hình ảnh tài nguyên sau đã được tải lên WordPress. Bạn BẮT BUỘC phải sử dụng chúng làm ảnh chính trong trang của bạn bằng cách gán thuộc tính `img_source="default"` và `img_attachment="ID"` tương ứng:\n';
        uploadedAssets.forEach(asset => {
            uploadAssetsInfo += `- File gốc: "${asset.filename}" -> Sử dụng thuộc tính: img_source="default" img_attachment="${asset.id}" (URL hiển thị: ${asset.url})\n`;
        });
    }

    const systemPrompt = `Bạn là một chuyên gia lập trình WordPress, chuyên biệt về thiết kế giao diện Flatsome Theme và UX Builder.
Yêu cầu của bạn là tạo ra mã nguồn shortcode Flatsome và VibeCode kết hợp để tạo ra một Landing Page siêu đẹp, cực kỳ sang trọng, chuyên nghiệp và đặc biệt phải hỗ trợ Responsive tuyệt vời (tự động điều chỉnh đẹp mắt trên máy tính, máy tính bảng và điện thoại di động).

Bạn sẽ sử dụng kết hợp giữa:
1. Hệ thống layout Grid mặc định của Flatsome (Cực kỳ khuyên dùng để căn chỉnh chia cột Responsive):
   - [row]...[/row]
   - [col span="12" span__md="6" span__sm="12"]...[/col]
     Trong đó:
     * span: Độ rộng cột trên Desktop (từ 1 đến 12).
     * span__md: Độ rộng cột trên Tablet (từ 1 đến 12).
     * span__sm: Độ rộng cột trên Mobile (từ 1 đến 12).

2. Các phần tử HTML tùy chỉnh mang tiền tố "vbc_" (do plugin của chúng tôi định nghĩa), hỗ trợ chèn các thẻ HTML cơ bản và có cài đặt Responsive riêng.
   Các shortcode VibeCode khả dụng bao gồm:
   - Thẻ Container: [vbc_div], [vbc_p], [vbc_span], [vbc_a], [vbc_h1], [vbc_h2], [vbc_h3], [vbc_h4], [vbc_h5], [vbc_h6], [vbc_ul], [vbc_ol], [vbc_li], [vbc_table], [vbc_tr], [vbc_td], [vbc_th], [vbc_b], [vbc_strong], [vbc_em], [vbc_u]
   - Thẻ Void (Tự đóng): [vbc_hr], [vbc_br], [vbc_img]

Các thuộc tính quan trọng của các phần tử vbc_*:
   * custom_class: Class CSS.
   * custom_css: CSS tùy chỉnh (Ví dụ: selector { border-radius: 8px; transition: all .3s; } selector:hover { transform: translateY(-5px); }). Từ khóa "selector" sẽ tự động map với class duy nhất của element. Bạn có thể sử dụng hover, active, media query trong này.
   * custom_attributes: Ví dụ: data-aos="fade-up"
   * Các thuộc tính CSS được hỗ trợ cấu hình Responsive độc lập (bằng cách thêm __md cho tablet và __sm cho mobile):
     - width, width__md, width__sm (Ví dụ: width="100%" width__sm="50%")
     - height, height__md, height__sm
     - margin, margin__md, margin__sm (Ví dụ: margin="0 0 20px 0" margin__sm="0 0 10px 0")
     - padding, padding__md, padding__sm (Ví dụ: padding="20px" padding__sm="10px")
     - font_size, font_size__md, font_size__sm (Ví dụ: font_size="32px" font_size__sm="20px")
     - text_align, text_align__md, text_align__sm (các giá trị: left, center, right, justify)
     - display, display__md, display__sm (các giá trị: block, inline-block, inline, flex, grid, none)
     - background_color, background_color__md, background_color__sm (Mã màu HEX hoặc RGB)
   * Thuộc tính riêng biệt:
     - Thẻ [vbc_a]: Bắt buộc đi kèm link_source="manual" link_url="https://example.com" link_target="_self" hoặc "_blank".
     - Thẻ [vbc_img]: Bắt buộc đi kèm img_source="manual" img_url="URL_ANH" HOẶC img_source="default" img_attachment="ID_ANH_THU_VIEN".
     - Thẻ [vbc_td] hoặc [vbc_th]: colspan, rowspan.
     - Thẻ [vbc_ol]: ol_type (giá trị: 1, a, A, i, I), ol_start.

Quy tắc thiết kế Landing Page:
- Thiết kế hiện đại: Phải cực kỳ thu hút, sử dụng phối màu hài hòa (Ví dụ: Sleek Dark Mode, Pastel hoặc Gradient rực rỡ).
- Cấu trúc đầy đủ: Gồm Hero Section (tiêu đề lớn, nút kêu gọi hành động CTA), Features (các tính năng nổi bật dạng lưới chia cột), Testimonials (cảm nhận khách hàng), và Footer liên hệ.
- Responsive tuyệt đối: Mọi tiêu đề lớn phải nhỏ lại trên mobile (sử dụng font_size__sm). Các layout chia cột ngang (Ví dụ: 3 cột [col span="4"]) bắt buộc phải đổi thành 1 cột dọc trên mobile ([col span="4" span__sm="12"]) để tránh méo mó giao diện.
- Không dùng văn bản giả lập (Lorem Ipsum). Hãy viết nội dung mẫu quảng cáo chân thực, cuốn hút bằng Tiếng Việt dựa trên mô tả của người dùng.
- Trả về kết quả CHỈ chứa mã shortcode nằm trong khối code markdown được đánh dấu bằng chữ "shortcodes" (Ví dụ: \`\`\`shortcodes\\n mã_shortcode...\\n \`\`\`). Không giải thích gì thêm bên ngoài khối code này.`;

    const userPrompt = `Dưới đây là mô tả nội dung và phong cách Landing Page tôi muốn bạn tạo:
--------------------------------------------------
Mô tả: ${description}
Tiêu đề trang: ${title}
--------------------------------------------------
${uploadAssetsInfo}
Hãy sinh mã shortcode Flatsome & VibeCode Responsive tương ứng.`;

    const requestBody = {
        contents: [
            {
                role: 'user',
                parts: [
                    { text: systemPrompt },
                    { text: userPrompt }
                ]
            }
        ],
        generationConfig: {
            responseMimeType: 'text/plain'
        }
    };

    // Nếu có ảnh layout tham chiếu, truyền thêm ảnh dạng base64 sang Gemini
    if (imageRef) {
        const absoluteRefPath = path.resolve(imageRef);
        if (fs.existsSync(absoluteRefPath)) {
            console.log(` -> Phát hiện ảnh layout tham khảo tại: ${path.basename(absoluteRefPath)}. Đang nạp ảnh vào Gemini...`);
            const refBuffer = fs.readFileSync(absoluteRefPath);
            const base64Data = refBuffer.toString('base64');
            let mimeType = 'image/jpeg';
            if (absoluteRefPath.endsWith('.png')) mimeType = 'image/png';
            else if (absoluteRefPath.endsWith('.webp')) mimeType = 'image/webp';

            requestBody.contents[0].parts.push({
                inlineData: {
                    mimeType: mimeType,
                    data: base64Data
                }
            });
        } else {
            console.warn(`\x1b[31m[CẢNH BÁO] Không tìm thấy ảnh layout tại: ${absoluteRefPath}. Chỉ sinh nội dung theo text mô tả.\x1b[0m`);
        }
    }

    let generatedShortcodes = '';
    try {
        const geminiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${apiKey}`;
        const response = await fetch(geminiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });

        if (!response.ok) {
            const errJson = await response.json();
            throw new Error(errJson.error?.message || `HTTP ${response.status}`);
        }

        const data = await response.json();
        const rawText = data.candidates?.[0]?.content?.parts?.[0]?.text || '';
        
        // Trích xuất shortcode từ thẻ ```shortcodes
        const match = rawText.match(/```shortcodes\s*([\s\S]*?)\s*```/);
        if (match && match[1]) {
            generatedShortcodes = match[1].trim();
        } else {
            // Thử trích xuất từ khối code markdown bất kỳ nếu không đúng tag
            const fallbackMatch = rawText.match(/```(?:[a-zA-Z]*)\s*([\s\S]*?)\s*```/);
            if (fallbackMatch && fallbackMatch[1]) {
                generatedShortcodes = fallbackMatch[1].trim();
            } else {
                generatedShortcodes = rawText.trim();
            }
        }

        if (!generatedShortcodes) {
            throw new Error('Không nhận được nội dung shortcode hợp lệ từ Gemini API.');
        }

        console.log('\x1b[32m✓ Đã sinh mã shortcode thành công!\x1b[0m');
    } catch (error) {
        console.error(`\x1b[31m✗ Không thể sinh code từ Gemini API: ${error.message}\x1b[0m`);
        process.exit(1);
    }

    // 4. Đẩy shortcode lên API của WordPress để tạo/cập nhật trang
    console.log('\n\x1b[33m[3/4] Đang gửi yêu cầu đăng bài lên WordPress REST API...\x1b[0m');
    
    try {
        const postData = {
            title: title,
            content: generatedShortcodes,
            status: 'publish',
            post_type: 'page'
        };
        if (slug) postData.slug = slug;
        if (postId) postData.post_id = postId;

        const pageRes = await fetch(`${apiUrl.replace(/\/$/, '')}/vbc/v1/page`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-VBC-Token': token
            },
            body: JSON.stringify(postData)
        });

        if (!pageRes.ok) {
            const errData = await pageRes.json();
            throw new Error(errData.message || `HTTP ${pageRes.status}`);
        }

        const result = await pageRes.json();
        if (result.success) {
            console.log('\n\x1b[32m==================================================\x1b[0m');
            console.log('\x1b[1m\x1b[32m   XUẤT BẢN LANDING PAGE THÀNH CÔNG!\x1b[0m');
            console.log('\x1b[32m==================================================\x1b[0m');
            console.log(`ID bài viết: ${result.post_id}`);
            console.log(`Hành động:   ${result.action === 'create' ? 'Tạo mới trang' : 'Cập nhật trang'}`);
            console.log(`Đường link:  \x1b[36m\x1b[4m${result.url}\x1b[0m`);
            console.log('\x1b[32m==================================================\x1b[0m\n');
        } else {
            throw new Error('Phản hồi từ máy chủ không thành công.');
        }
    } catch (error) {
        console.error(`\x1b[31m✗ Gửi bài lên WordPress thất bại: ${error.message}\x1b[0m`);
        process.exit(1);
    }
}

main();
