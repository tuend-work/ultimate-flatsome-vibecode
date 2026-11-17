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
            <div class="vbc-info-box">
                <strong>💡 VibeCode AI Project Package:</strong> Khi xuất file ZIP, hệ thống sẽ đóng gói toàn bộ thư mục <code>skills/</code> (gồm các script clone và tạo landing page tự động) cùng tệp <code>vbc-config.json</code> chứa dữ liệu ngữ cảnh website. Antigravity AI sẽ đọc hiểu toàn bộ thông tin này để tạo Landing Page chuẩn xác theo ngữ cảnh của website bạn.
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field( 'vbc_export_project_nonce', 'vbc_export_nonce' ); ?>
                <input type="hidden" name="vbc_action" value="export_antigravity_project" />

                <!-- 1. Kết Nối API & Token -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-admin-network"></span> 1. Thông Tin Kết Nối API</h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('WordPress REST API Endpoint', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr( $api_url ); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#2563eb;" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('VibeCode API Token (User Admin)', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr( $token ); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#059669;" />
                        </div>
                    </div>
                </div>

                <!-- 2. Thông Tin FTP Hosting -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-cloud"></span> 2. Cấu Hình FTP Hosting (Tùy chọn)</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -5px; margin-bottom: 15px;">
                        Thông tin FTP giúp Antigravity có thể tự động tải file chỉnh sửa lên hosting nếu bạn cần can thiệp code trực tiếp. (Nếu để trống, giá trị sẽ là <code>&lt;none&gt;</code>).
                    </p>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Host', 'vibecode'); ?></label>
                            <input type="text" name="ftp_host" value="<?php echo esc_attr( $ftp_host ); ?>" placeholder="Ví dụ: 103.161.172.211 hoặc ftp.domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP User', 'vibecode'); ?></label>
                            <input type="text" name="ftp_user" value="<?php echo esc_attr( $ftp_user ); ?>" placeholder="Ví dụ: myuser@domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Password', 'vibecode'); ?></label>
                            <input type="password" name="ftp_password" value="<?php echo esc_attr( $ftp_password ); ?>" placeholder="••••••••••••" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Đường dẫn thư mục gốc Website trên Hosting (Root Path)', 'vibecode'); ?></label>
                            <input type="text" name="ftp_path" value="<?php echo esc_attr( $ftp_path ); ?>" placeholder="<?php echo esc_attr( $detected_root_path ); ?>" />
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                <?php printf( __( 'Tự động nhận diện thư mục gốc chứa wp-config.php: <code>%s</code>', 'vibecode' ), esc_html( $detected_root_path ) ); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Lựa Chọn Dữ Liệu Ngữ Cảnh Xuất Ra JSON -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-database"></span> 3. Chọn Dữ Liệu Ngữ Cảnh Website Đóng Gói Vào vbc-config.json</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -5px; margin-bottom: 15px;">
                        Tích chọn những dữ liệu bạn muốn đưa vào file cấu hình để AI hiểu sâu về website.
                    </p>

                    <!-- Checkbox Site Name & Tagline -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_site_name" id="chk_site_name" value="1" checked />
                        <div>
                            <label for="chk_site_name" class="vbc-chk-title"><?php _e('Tên Website & Khẩu Hiệu (Site Name & Tagline)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Hiện tại: <strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong> — <em><?php echo esc_html( get_bloginfo( 'description' ) ); ?></em>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Contact Info -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_contact" id="chk_contact" value="1" checked />
                        <div>
                            <label for="chk_contact" class="vbc-chk-title"><?php _e('Thông Tin Thương Hiệu & Liên Hệ (Phone, Email, Địa chỉ, Zalo)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Đồng bộ từ Tab Cài Đặt Chung (Hotline: <strong><?php echo esc_html( Ultimate_Flatsome_General_Settings::get_field_value( 'phone' ) ); ?></strong>, Email: <strong><?php echo esc_html( Ultimate_Flatsome_General_Settings::get_field_value( 'email' ) ); ?></strong>).
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Styles & Flatsome Options -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_styles" id="chk_styles" value="1" checked />
                        <div>
                            <label for="chk_styles" class="vbc-chk-title"><?php _e('Toàn Bộ Cài Đặt Flatsome Theme (Customizer & wp_options)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Trích xuất toàn bộ bảng màu Flatsome (Primary/Secondary/Success/Alert), Typography, Header, Footer, Site Width, Layout, Custom CSS.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Products -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_products" id="chk_products" value="1" <?php echo $product_count > 0 ? 'checked' : ''; ?> />
                        <div>
                            <label for="chk_products" class="vbc-chk-title"><?php _e('Danh Sách Sản Phẩm (WooCommerce Products)', 'vibecode'); ?> <?php echo $product_count > 0 ? "($product_count sản phẩm)" : '(Chưa có sản phẩm)'; ?></label>
                            <div class="vbc-chk-desc">
                                Xuất tên sản phẩm, giá bán, danh mục, link chi tiết và ảnh đại diện.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Services / Pages -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_services" id="chk_services" value="1" checked />
                        <div>
                            <label for="chk_services" class="vbc-chk-title"><?php _e('Danh Sách Dịch Vụ & Trang Quan Trọng', 'vibecode'); ?> (<?php echo $page_count; ?> trang)</label>
                            <div class="vbc-chk-desc">
                                Xuất danh sách trang tĩnh, tiêu đề, tóm tắt và đường dẫn.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Blog Posts -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_posts" id="chk_posts" value="1" <?php echo $post_count > 0 ? 'checked' : ''; ?> />
                        <div>
                            <label for="chk_posts" class="vbc-chk-title"><?php _e('Danh Sách Bài Viết Mới Nhất (Blog Posts)', 'vibecode'); ?> (<?php echo $post_count; ?> bài viết)</label>
                            <div class="vbc-chk-desc">
                                Xuất tiêu đề bài viết, chuyên mục, link và tóm tắt.
                            </div>
                        </div>
                    </div>

                    <!-- Custom Prompt Instruction -->
                    <div class="vbc-form-group" style="margin-top: 20px;">
                        <label><?php _e('Ghi Chú / Yêu Cầu Đặc Biệt Cho Antigravity AI (Tùy chọn)', 'vibecode'); ?></label>
                        <textarea name="custom_instructions" rows="3" placeholder="Ví dụ: Tone màu ưu tiên tông xanh lá pastel kết hợp hồng phấn..."></textarea>
                    </div>

                    <div style="margin-top: 15px;">
                        <label style="font-weight: 600; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="vbc_save_config_data" value="1" checked />
                            <?php _e('Lưu lại thông tin FTP ở trên vào website để tiện cho các lần xuất sau', 'vibecode'); ?>
                        </label>
                    </div>
                </div>

                <!-- Export Action Button -->
                <div style="margin-top: 20px; text-align: center; padding: 25px; background: #ffffff; border-radius: 14px; border: 1px solid #e2e8f0;">
                    <button type="submit" class="vbc-btn-export">
                        <span class="dashicons dashicons-archive" style="font-size: 20px;"></span>
                        <?php printf( __( 'Xuất Gói Dự Án %s-vibecode-project.zip', 'vibecode' ), esc_html( $clean_domain ) ); ?>
                    </button>
                    <p style="color: #64748b; font-size: 13px; margin-top: 10px; margin-bottom: 0;">
                        Tệp ZIP xuất ra chứa toàn bộ thư mục <code>skills/</code> và tệp <code>vbc-config.json</code> để kéo thả trực tiếp vào Antigravity IDE.
                    </p>
                </div>
            </form>

