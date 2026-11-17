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
                <strong>🔄 Tự Động Cập Nhật Trực Tiếp Từ GitHub:</strong> Tính năng này cho phép bạn cập nhật plugin <strong>Ultimate Flatsome</strong> lên phiên bản mới nhất trực tiếp từ kho lưu trữ GitHub chính thức (nhánh <code>main</code>, thư mục <code>ultimate-flatsome</code>). Hệ thống sẽ tự động tải file ZIP, giải nén và cập nhật toàn bộ tính năng mà không làm mất các cài đặt cấu hình hiện có của bạn.
            </div>

            <div class="vbc-card">
                <h2><span class="dashicons dashicons-update"></span> <?php _e('Trạng Thái Phiên Bản & Cập Nhật GitHub', 'vibecode'); ?></h2>
                
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 22px; margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;"><?php _e('Phiên bản hiện tại trên Website:', 'vibecode'); ?></div>
                            <div style="font-size: 24px; font-weight: 800; color: #0f172a;">v<?php echo esc_html( VBC_VERSION ); ?></div>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 4px;"><?php _e('Kho lưu trữ nguồn GitHub:', 'vibecode'); ?></div>
                            <div style="font-size: 14px; font-weight: 700; color: #2563eb;">
                                <a href="https://github.com/tuend-work/ultimate-flatsome-vibecode/tree/main/ultimate-flatsome" target="_blank" rel="noopener noreferrer" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                                    <span class="dashicons dashicons-external" style="font-size: 16px;"></span> tuend-work/ultimate-flatsome-vibecode (main)
                                </a>
                            </div>
                        </div>
                        <div>
                            <button type="button" id="vbc-btn-check-update" class="button button-secondary" style="font-weight: 700; height: 40px; display: inline-flex; align-items: center; gap: 6px; padding: 0 16px; border-radius: 8px;">
                                <span class="dashicons dashicons-search" style="font-size: 16px;"></span>
                                <?php _e('Kiểm Tra Bản Mới Trên GitHub', 'vibecode'); ?>
                            </button>
                        </div>
                    </div>
                    <div id="vbc-update-check-result" style="margin-top: 16px; display: none; padding: 14px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600;"></div>
                </div>

                <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn tải về và ghi đè cập nhật plugin Ultimate Flatsome lên phiên bản mới nhất từ GitHub?');">
                    <?php wp_nonce_field( 'vbc_update_plugin_nonce', 'vbc_update_nonce' ); ?>
                    <input type="hidden" name="vbc_action" value="update_plugin_from_github" />
                    <input type="hidden" name="current_tab" value="update" />

                    <div class="vbc-form-group">
                        <label><?php _e('GitHub Personal Access Token (Tùy chọn)', 'vibecode'); ?></label>
                        <input type="password" name="uf_github_token" value="<?php echo esc_attr( get_option( 'uf_github_token', '' ) ); ?>" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx (Chỉ bắt buộc nếu GitHub Repository ở chế độ Riêng Tư / Private)" />
                        <span class="vbc-field-desc">
                            <?php _e('Nếu Repository GitHub là Public (Công khai), bạn có thể để trống trường này. Nếu Repo là Private, hãy tạo một Classic Token có quyền `repo` tại GitHub > Settings > Developer settings > Personal access tokens.', 'vibecode'); ?>
                        </span>
                    </div>

                    <div style="margin-top: 25px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <button type="submit" class="vbc-btn-save" style="font-size: 16px; padding: 14px 32px;">
                            <span class="dashicons dashicons-download" style="font-size: 20px;"></span>
                            <?php _e('Tải & Cập Nhật Tự Động Từ GitHub Ngay', 'vibecode'); ?>
                        </button>
                        <span style="color: #64748b; font-size: 13px;">
                            <?php _e('Thao tác sẽ tự động tải file zip từ GitHub, giải nén thư mục <code>ultimate-flatsome</code> và ghi đè an toàn.', 'vibecode'); ?>
                        </span>
                    </div>
                </form>
            </div>

