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
                <strong>💡 Quản Lý Thông Tin Tập Trung:</strong> Toàn bộ thông tin nhập tại đây được lưu trực tiếp vào bảng <code>wp_options</code> của WordPress và đồng bộ 2 chiều với Cài đặt Tổng quan. Bạn có thể sử dụng các mã Shortcode tương ứng để chèn số điện thoại, email, địa chỉ hoặc bản quyền vào bất kỳ đâu trên website (UX Builder, Header, Footer, Trang liên hệ...). Khi cần thay đổi, chỉ cần sửa tại đây 1 lần là toàn bộ web sẽ tự động cập nhật!
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field( 'vbc_save_settings_nonce', 'vbc_settings_nonce' ); ?>
                <input type="hidden" name="vbc_action" value="save_general_settings" />
                <input type="hidden" name="current_tab" value="general" />

                <!-- 1. Thông Tin Cơ Bản Website (WP Core Sync) -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-admin-home"></span> 1. Thông Tin Cơ Bản Website (Đồng bộ với Cài đặt WordPress)</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -6px; margin-bottom: 20px;">
                        Các trường này đồng bộ trực tiếp 2 chiều với <code>wp_options: blogname, blogdescription, admin_email</code>.
                    </p>

                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Tên Website (Site Title)', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_info field="site_name"]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="site_name"]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="text" name="blogname" value="<?php echo esc_attr( get_option( 'blogname' ) ); ?>" placeholder="Ví dụ: Kyna English" />
                        <div class="vbc-field-desc"><?php _e('Tên thương hiệu chính của website.', 'vibecode'); ?></div>
                    </div>

                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Khẩu Hiệu / Slogan (Tagline)', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_info field="tagline"]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="tagline"]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="text" name="blogdescription" value="<?php echo esc_attr( get_option( 'blogdescription' ) ); ?>" placeholder="Ví dụ: Nền Tảng Học Tiếng Anh 1 Kèm 1 Hàng Đầu" />
                        <div class="vbc-field-desc"><?php _e('Khẩu hiệu hoặc mô tả ngắn của website.', 'vibecode'); ?></div>
                    </div>

                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Email Quản Trị Hệ Thống', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_info field="admin_email"]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="admin_email"]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="email" name="admin_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
                        <div class="vbc-field-desc"><?php _e('Email nhận các thông báo hệ thống và quản trị viên.', 'vibecode'); ?></div>
                    </div>
                </div>

                <!-- 2. Thông Tin Liên Hệ & Thương Hiệu Doanh Nghiệp -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-phone"></span> 2. Thông Tin Liên Hệ & Doanh Nghiệp</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -6px; margin-bottom: 20px;">
                        Các trường lưu trữ trực tiếp trong <code>wp_options</code>. Tự động hỗ trợ click-to-call khi dùng tham số <code>link="true"</code>.
                    </p>

                    <div class="vbc-grid-2">
                        <!-- Tên công ty -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Tên Doanh Nghiệp / Công Ty', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_company]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_company]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_company_name" value="<?php echo esc_attr( get_option( 'uf_company_name', '' ) ); ?>" placeholder="CÔNG TY CỔ PHẦN DREAM VIET EDUCATION" />
                        </div>

                        <!-- Hotline chính -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Hotline / Số Điện Thoại Chính', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_phone link="true"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_phone link="true"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_phone" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'phone' ) ); ?>" placeholder="1900 6364 09 hoặc 0912 345 678" />
                        </div>

                        <!-- Hotline phụ -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Hotline Phụ / Kỹ Thuật', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_phone_2 link="true"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_phone_2 link="true"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_phone_2" value="<?php echo esc_attr( get_option( 'uf_phone_2', '' ) ); ?>" placeholder="0987 654 321" />
                        </div>

                        <!-- Zalo -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Số Zalo / Link Chat Zalo', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_zalo link="true"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_zalo link="true"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_zalo" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'zalo' ) ); ?>" placeholder="0912 345 678 hoặc https://zalo.me/0912345678" />
                        </div>

                        <!-- Email liên hệ khách -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Email Liên Hệ / Hỗ Trợ CSKH', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_email link="true"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_email link="true"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="email" name="uf_email" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'email' ) ); ?>" placeholder="hotro@domain.com" />
                        </div>

                        <!-- Giờ làm việc -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Thời Gian Làm Việc', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="hours"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="hours"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_working_hours" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'working_hours' ) ); ?>" placeholder="8:00 - 18:00 (Thứ 2 - Thứ 7)" />
                        </div>
                    </div>

                    <!-- Địa chỉ trụ sở chính -->
                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Địa Chỉ Trụ Sở Chính', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_address]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_address]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="text" name="uf_address" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'address' ) ); ?>" placeholder="P903, Tầng 9, Tòa nhà Diamond Plaza, 34 Lê Duẩn, P. Bến Nghé, Quận 1, TP.HCM" />
                    </div>

                    <!-- Địa chỉ chi nhánh -->
                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Địa Chỉ Chi Nhánh / Văn Phòng 2', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_info field="address_branch"]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="address_branch"]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="text" name="uf_address_branch" value="<?php echo esc_attr( get_option( 'uf_address_branch', '' ) ); ?>" placeholder="Tầng 5, Keangnam Landmark 72, Nam Từ Liêm, Hà Nội" />
                    </div>

                    <div class="vbc-grid-2">
                        <!-- Mã số thuế -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Mã Số Thuế / Giấy Phép ĐKKD', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="tax_code"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="tax_code"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_tax_code" value="<?php echo esc_attr( get_option( 'uf_tax_code', '' ) ); ?>" placeholder="0313589030 do Sở KH&ĐT TP.HCM cấp" />
                        </div>

                        <!-- Link Google Maps -->
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;"><?php _e('Link Bản Đồ Google Maps', 'vibecode'); ?></label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="maps"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="maps"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_google_maps" value="<?php echo esc_attr( get_option( 'uf_google_maps', '' ) ); ?>" placeholder="https://maps.google.com/?q=..." />
                        </div>
                    </div>

                    <!-- Bản quyền Footer -->
                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Bản Quyền Chân Trang (Copyright)', 'vibecode'); ?></label>
                            <div>
                                <span class="vbc-shortcode-badge">[uf_copyright]</span>
                                <button type="button" class="vbc-copy-btn" data-clipboard='[uf_copyright]'>📋 Copy</button>
                            </div>
                        </div>
                        <input type="text" name="uf_copyright" value="<?php echo esc_attr( Ultimate_Flatsome_General_Settings::get_field_value( 'copyright' ) ); ?>" placeholder="© {year} {site_name}. All rights reserved." />
                        <div class="vbc-field-desc"><?php _e('Tự động thay {year} bằng năm hiện tại và {site_name} bằng tên website.', 'vibecode'); ?></div>
                    </div>
                </div>

                <!-- 3. Mạng Xã Hội (Social Media Links) -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-share"></span> 3. Mạng Xã Hội (Social Links)</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -6px; margin-bottom: 20px;">
                        Điền link các kênh mạng xã hội chính thức của doanh nghiệp.
                    </p>

                    <div class="vbc-grid-2">
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Facebook Fanpage / URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="facebook"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="facebook"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_facebook" value="<?php echo esc_attr( get_option( 'uf_facebook', '' ) ); ?>" placeholder="https://facebook.com/fanpage" />
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Kênh YouTube URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="youtube"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="youtube"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_youtube" value="<?php echo esc_attr( get_option( 'uf_youtube', '' ) ); ?>" placeholder="https://youtube.com/@channel" />
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Kênh TikTok URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="tiktok"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="tiktok"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_tiktok" value="<?php echo esc_attr( get_option( 'uf_tiktok', '' ) ); ?>" placeholder="https://tiktok.com/@kynaenglish" />
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Tài Khoản Instagram URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="instagram"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="instagram"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_instagram" value="<?php echo esc_attr( get_option( 'uf_instagram', '' ) ); ?>" placeholder="https://instagram.com/kynaenglish" />
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Facebook Messenger URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="messenger"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="messenger"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_messenger" value="<?php echo esc_attr( get_option( 'uf_messenger', '' ) ); ?>" placeholder="https://m.me/kynaenglish" />
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Kênh / Nhóm Telegram URL</label>
                                <div>
                                    <span class="vbc-shortcode-badge">[uf_info field="telegram"]</span>
                                    <button type="button" class="vbc-copy-btn" data-clipboard='[uf_info field="telegram"]'>📋 Copy</button>
                                </div>
                            </div>
                            <input type="text" name="uf_telegram" value="<?php echo esc_attr( get_option( 'uf_telegram', '' ) ); ?>" placeholder="https://t.me/kynaenglish" />
                        </div>
                    </div>
                </div>

                <!-- 4. Mã Nhúng Scripts & Tracking (Header / Footer) -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-code-standards"></span> 4. Mã Nhúng Scripts & Tracking (Tự Động Inject Vào Header/Footer)</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -6px; margin-bottom: 20px;">
                        Tự động chèn các mã theo dõi mà không cần chỉnh sửa file <code>header.php</code> hoặc <code>footer.php</code> của theme.
                    </p>

                    <div class="vbc-grid-2">
                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Google Analytics (GA4 ID hoặc GTM ID)</label>
                            </div>
                            <input type="text" name="uf_ga_id" value="<?php echo esc_attr( get_option( 'uf_ga_id', '' ) ); ?>" placeholder="G-XXXXXXXXXX hoặc GTM-XXXXXXX" />
                            <div class="vbc-field-desc"><?php _e('Nhập mã GA4 hoặc GTM. Hệ thống tự sinh mã theo dõi vào thẻ <head>.', 'vibecode'); ?></div>
                        </div>

                        <div class="vbc-field-row">
                            <div class="vbc-field-header">
                                <label style="font-weight: 700; color: #1e293b;">Meta Pixel ID (Facebook Pixel)</label>
                            </div>
                            <input type="text" name="uf_fb_pixel_id" value="<?php echo esc_attr( get_option( 'uf_fb_pixel_id', '' ) ); ?>" placeholder="Ví dụ: 123456789012345" />
                            <div class="vbc-field-desc"><?php _e('Nhập ID Meta Pixel để đo lường chuyển đổi quảng cáo Facebook.', 'vibecode'); ?></div>
                        </div>
                    </div>

                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Mã Nhúng Đầu Trang (Chèn vào trước </head>)', 'vibecode'); ?></label>
                        </div>
                        <textarea name="uf_header_scripts" rows="4" style="font-family: Consolas, Monaco, monospace; font-size: 13px;" placeholder="<script> /* Mã Javascript chèn vào Head */ </script>"><?php echo esc_textarea( get_option( 'uf_header_scripts', '' ) ); ?></textarea>
                    </div>

                    <div class="vbc-field-row">
                        <div class="vbc-field-header">
                            <label style="font-weight: 700; color: #1e293b;"><?php _e('Mã Nhúng Cuối Trang (Chèn vào trước </body>)', 'vibecode'); ?></label>
                        </div>
                        <textarea name="uf_footer_scripts" rows="4" style="font-family: Consolas, Monaco, monospace; font-size: 13px;" placeholder="<script> /* Mã Chatbot, Livechat, Script thống kê */ </script>"><?php echo esc_textarea( get_option( 'uf_footer_scripts', '' ) ); ?></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="text-align: right; margin-top: 25px;">
                    <button type="submit" class="vbc-btn-save">
                        <span class="dashicons dashicons-saved" style="font-size: 18px;"></span>
                        <?php _e('Lưu Toàn Bộ Cài Đặt Chung', 'vibecode'); ?>
                    </button>
                </div>
            </form>

