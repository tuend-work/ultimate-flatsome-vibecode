<?php
/**
 * Admin Tab View
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-admin-generic"></span> 1. Shortcodes Thông Tin Website (Dùng Cho Toàn Bộ Website)</h2>
                <p style="color: #64748b; font-size: 13px;">
                    Dán các shortcode này vào bất kỳ bài viết, trang, UX Builder hoặc widget footer để hiển thị thông tin động:
                </p>
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 220px;"><strong>Shortcode</strong></th>
                            <th style="width: 260px;"><strong>Cú pháp có Link bấm gọi/mở</strong></th>
                            <th><strong>Mô Tả & Công Dụng</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[uf_phone]</code></td>
                            <td><code>[uf_phone link="true"]</code></td>
                            <td>Hiển thị số điện thoại Hotline. Thuộc tính <code>link="true"</code> tự động tạo thẻ gọi <code>&lt;a href="tel:..."&gt;</code>.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_phone_2]</code></td>
                            <td><code>[uf_phone_2 link="true"]</code></td>
                            <td>Hiển thị số điện thoại phụ / kỹ thuật.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_zalo]</code></td>
                            <td><code>[uf_zalo link="true"]</code></td>
                            <td>Hiển thị số Zalo hoặc link chat Zalo OA.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_email]</code></td>
                            <td><code>[uf_email link="true"]</code></td>
                            <td>Hiển thị email liên hệ. Thuộc tính <code>link="true"</code> tạo link <code>&lt;a href="mailto:..."&gt;</code>.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_address]</code></td>
                            <td><code>[uf_address]</code></td>
                            <td>Hiển thị địa chỉ trụ sở chính của doanh nghiệp.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_company]</code></td>
                            <td><code>[uf_company]</code></td>
                            <td>Hiển thị tên công ty / doanh nghiệp.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_copyright]</code></td>
                            <td><code>[uf_copyright]</code></td>
                            <td>Hiển thị bản quyền chân trang (tự động cập nhật năm hiện tại).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_info field="site_name"]</code></td>
                            <td><code>[uf_info field="site_name"]</code></td>
                            <td>Hiển thị Tên website (lấy từ wp_options: blogname).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_info field="tagline"]</code></td>
                            <td><code>[uf_info field="tagline"]</code></td>
                            <td>Hiển thị Khẩu hiệu website (lấy từ wp_options: blogdescription).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_info field="hours"]</code></td>
                            <td><code>[uf_info field="hours"]</code></td>
                            <td>Hiển thị Thời gian làm việc của công ty.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_info field="tax_code"]</code></td>
                            <td><code>[uf_info field="tax_code"]</code></td>
                            <td>Hiển thị Mã số thuế / ĐKKD.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_info field="facebook"]</code></td>
                            <td><code>[uf_info field="facebook" link="true"]</code></td>
                            <td>Hiển thị URL hoặc nút link mở Facebook Fanpage.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_option key="..."]</code></td>
                            <td><code>[uf_option key="blogname"]</code></td>
                            <td>Truy xuất an toàn bất kỳ giá trị nào trong bảng <code>wp_options</code>.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="vbc-card">
                <h2><span class="dashicons dashicons-layout"></span> 2. Danh Sách Shortcode VibeCode Elements (UX Builder)</h2>
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;"><strong>Shortcode</strong></th>
                            <th><strong>Mô Tả & Cú Pháp Mẫu</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[vbc_section]</code></td>
                            <td>Khối section chuyên nghiệp có hỗ trợ CSS Engine: <code>[vbc_section custom_css="selector { ... }"] ... [/vbc_section]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_post]</code></td>
                            <td>Truy vấn bài viết & sản phẩm: <code>[vbc_post post_type="post" posts_per_page="3" columns="3" layout="grid"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_icon]</code></td>
                            <td>Thư viện vector icon thông minh: <code>[vbc_icon pack="lucide" name="shield-check" color="#2563eb" size="24px"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_card]</code></td>
                            <td>Khối thẻ card kính mờ: <code>[vbc_card variant="glass" border_radius="20px"] ... [/vbc_card]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_accordion]</code></td>
                            <td>Khối hỏi đáp SEO FAQ / Accordion: <code>[accordion][accordion-item title="Tiêu đề?"]Nội dung...[/accordion-item][/accordion]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_tabs]</code></td>
                            <td>Khối chuyển tab tương tác: <code>[vbc_tabs style="pills"][vbc_tab title="Tab 1"]Nội dung 1[/vbc_tab][/vbc_tabs]</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
