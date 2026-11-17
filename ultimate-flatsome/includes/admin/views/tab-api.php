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
                <h2><span class="dashicons dashicons-rest-api"></span> <?php _e('Thông Tin Xác Thực API', 'vibecode'); ?></h2>
                <p style="color: #64748b; font-size: 13px;">
                    <?php _e('API Token được cấp riêng cho tài khoản quản trị viên hiện tại để bảo vệ các thao tác đăng trang và upload media từ bên ngoài.', 'vibecode'); ?>
                </p>

                <form method="POST" action="">
                    <?php wp_nonce_field( 'vbc_save_settings_nonce', 'vbc_settings_nonce' ); ?>
                    <input type="hidden" name="vbc_action" value="save_general_settings" />
                    <input type="hidden" name="current_tab" value="api" />

                    <div class="vbc-form-group">
                        <label><?php _e('WordPress REST API Base URL', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr( $api_url ); ?>" readonly style="background:#f8fafc; font-family:monospace;" />
                    </div>

                    <div class="vbc-form-group">
                        <label><?php _e('API Token Hiện Tại', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr( $token ); ?>" readonly style="background:#f8fafc; font-family:monospace; font-size:16px; font-weight:700; color:#2563eb;" />
                    </div>

                    <div class="vbc-form-group">
                        <label>
                            <input type="checkbox" name="vbc_regenerate_token" value="1" />
                            <strong><?php _e('Tạo lại Token mới (Lưu ý: Token cũ sẽ bị hủy hiệu lực ngay lập tức)', 'vibecode'); ?></strong>
                        </label>
                    </div>

                    <button type="submit" class="vbc-btn-save"><?php _e('Lưu Thay Đổi & Cập Nhật Token', 'vibecode'); ?></button>
                </form>

                <hr style="margin: 25px 0; border: none; border-top: 1px solid #f1f5f9;">

                <h3><?php _e('Kiểm Tra REST API Endpoints', 'vibecode'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px; color: #475569; font-size: 13px; line-height: 1.8;">
                    <li><strong>Upload Media:</strong> <code>POST <?php echo esc_html( $api_url ); ?>vbc/v1/upload</code> (Header: <code>X-VBC-Token: <?php echo esc_html( substr( $token, 0, 8 ) ); ?>...</code>)</li>
                    <li><strong>Tạo Form CF7:</strong> <code>POST <?php echo esc_html( $api_url ); ?>vbc/v1/cf7</code></li>
                    <li><strong>Đăng / Cập nhật Trang:</strong> <code>POST <?php echo esc_html( $api_url ); ?>vbc/v1/page</code></li>
                    <li><strong>Lấy nội dung Trang:</strong> <code>GET <?php echo esc_html( $api_url ); ?>vbc/v1/page?slug=trang-mau</code></li>
                </ul>
            </div>

