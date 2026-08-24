<?php
/**
 * Ultimate Flatsome VibeCode - Admin Settings & User Token Management
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_register_admin_menu() {
    // Menu chính cấp 1 trên thanh Admin Sidebar
    add_menu_page(
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page',
        'dashicons-layout',
        30
    );

    // Menu phụ dưới Flatsome Panel nếu có
    add_submenu_page(
        'flatsome-panel',
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode Settings', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page'
    );

    // Menu phụ dưới Cài đặt (Settings) dự phòng
    add_options_page(
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page'
    );
}



function vbc_handle_save_settings_request() {
    if (isset($_POST['vbc_action']) && $_POST['vbc_action'] === 'save_general_settings') {
        if (!current_user_can('manage_options')) return;
        check_admin_referer('vbc_save_settings_nonce', 'vbc_settings_nonce');

        if (isset($_POST['ftp_host'])) update_option('vbc_ftp_host', sanitize_text_field($_POST['ftp_host']));
        if (isset($_POST['ftp_user'])) update_option('vbc_ftp_user', sanitize_text_field($_POST['ftp_user']));
        if (isset($_POST['ftp_password'])) update_option('vbc_ftp_password', sanitize_text_field($_POST['ftp_password']));
        if (isset($_POST['ftp_path'])) update_option('vbc_ftp_path', sanitize_text_field($_POST['ftp_path']));

        if (isset($_POST['brand_phone'])) update_option('vbc_brand_phone', sanitize_text_field($_POST['brand_phone']));
        if (isset($_POST['brand_email'])) update_option('vbc_brand_email', sanitize_email($_POST['brand_email']));
        if (isset($_POST['brand_address'])) update_option('vbc_brand_address', sanitize_text_field($_POST['brand_address']));
        if (isset($_POST['brand_zalo'])) update_option('vbc_brand_zalo', sanitize_text_field($_POST['brand_zalo']));
        if (isset($_POST['brand_hours'])) update_option('vbc_brand_hours', sanitize_text_field($_POST['brand_hours']));

        if (!empty($_POST['vbc_regenerate_token'])) {
            $user_id = get_current_user_id();
            $new_token = bin2hex(random_bytes(20));
            update_user_meta($user_id, 'vbc_api_token', $new_token);
        }

        $tab = !empty($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'export';
        wp_redirect(add_query_arg(array('page' => 'vibecode-settings', 'tab' => $tab, 'saved' => '1'), admin_url('admin.php')));
        exit;
    }
}

// Giao diện Trang Quản Trị VibeCode
function vbc_render_admin_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'export';
    $user_id = get_current_user_id();
    $token = get_user_meta($user_id, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $token);
    }

    $api_url = get_rest_url(null, '');
    $site_url = get_site_url();
    $parsed_url = parse_url($site_url);
    $domain_host = !empty($parsed_url['host']) ? $parsed_url['host'] : 'website';
    $clean_domain = sanitize_file_name(preg_replace('/[^a-zA-Z0-9\.\-]/', '-', $domain_host));

    // Tự động nhận diện đường dẫn thư mục gốc website (Root Path chứa wp-config.php)
    $detected_root_path = defined('ABSPATH') ? wp_normalize_path(untrailingslashit(ABSPATH)) : (defined('WP_CONTENT_DIR') ? wp_normalize_path(dirname(WP_CONTENT_DIR)) : '/public_html');
    $detected_plugin_path = defined('WP_PLUGIN_DIR') ? wp_normalize_path(WP_PLUGIN_DIR) : $detected_root_path . '/wp-content/plugins';
    $ftp_host = get_option('vbc_ftp_host', '');
    $ftp_user = get_option('vbc_ftp_user', '');
    $ftp_password = get_option('vbc_ftp_password', '');
    $ftp_path = get_option('vbc_ftp_path', '');
    if (empty($ftp_path)) {
        $ftp_path = $detected_root_path;
    }

    $brand_phone = get_option('vbc_brand_phone', '');
    $brand_email = get_option('vbc_brand_email', get_option('admin_email'));
    $brand_address = get_option('vbc_brand_address', '');
    $brand_zalo = get_option('vbc_brand_zalo', '');
    $brand_hours = get_option('vbc_brand_hours', '8:00 - 18:00 (T2 - T7)');

    $has_wc = class_exists('WooCommerce');
    $product_count = $has_wc ? wp_count_posts('product')->publish : 0;
    $post_count = wp_count_posts('post')->publish;
    $page_count = wp_count_posts('page')->publish;

    ?>
    <div class="wrap vbc-admin-wrap" style="max-width: 1100px; margin-top: 20px;">
        <style>
            .vbc-admin-header {
                background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);
                padding: 25px 30px;
                border-radius: 12px;
                color: #ffffff;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            }
            .vbc-admin-header h1 {
                color: #ffffff;
                font-size: 24px;
                font-weight: 800;
                margin: 0 0 6px 0;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .vbc-admin-header p {
                color: #94a3b8;
                margin: 0;
                font-size: 14px;
            }
            .vbc-badge {
                display: inline-block;
                background: rgba(37,99,235,0.25);
                color: #60a5fa;
                border: 1px solid rgba(59,130,246,0.35);
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
            }
            .vbc-nav-tab-wrapper {
                margin-bottom: 25px;
                border-bottom: 2px solid #e2e8f0;
                display: flex;
                gap: 8px;
            }
            .vbc-nav-tab {
                padding: 12px 20px;
                text-decoration: none;
                font-weight: 700;
                font-size: 14px;
                color: #64748b;
                border-radius: 8px 8px 0 0;
                background: #f1f5f9;
                transition: all 0.2s;
                border: 1px solid #e2e8f0;
                border-bottom: none;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .vbc-nav-tab:hover {
                color: #2563eb;
                background: #ffffff;
            }
            .vbc-nav-tab.active {
                color: #2563eb;
                background: #ffffff;
                border-top: 3px solid #2563eb;
                margin-bottom: -2px;
                padding-bottom: 13px;
            }
            .vbc-card {
                background: #ffffff;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.03);
                margin-bottom: 25px;
            }
            .vbc-card h2 {
                font-size: 18px;
                font-weight: 800;
                color: #0f172a;
                margin-top: 0;
                margin-bottom: 15px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .vbc-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            @media (max-width: 782px) {
                .vbc-grid-2 { grid-template-columns: 1fr; }
            }
            .vbc-form-group {
                margin-bottom: 18px;
            }
            .vbc-form-group label {
                display: block;
                font-weight: 700;
                font-size: 13px;
                color: #334155;
                margin-bottom: 6px;
            }
            .vbc-form-group input[type="text"],
            .vbc-form-group input[type="password"],
            .vbc-form-group input[type="email"],
            .vbc-form-group textarea,
            .vbc-form-group select {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                padding: 8px 12px;
                font-size: 14px;
            }
            .vbc-checkbox-item {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 14px 18px;
                margin-bottom: 12px;
                display: flex;
                align-items: flex-start;
                gap: 14px;
                transition: all 0.2s;
            }
            .vbc-checkbox-item:hover {
                border-color: #93c5fd;
                background: #eff6ff;
            }
            .vbc-checkbox-item input[type="checkbox"] {
                margin-top: 3px;
                width: 18px;
                height: 18px;
            }
            .vbc-checkbox-item .vbc-chk-title {
                font-weight: 700;
                color: #0f172a;
                font-size: 14px;
                margin-bottom: 3px;
            }
            .vbc-checkbox-item .vbc-chk-desc {
                font-size: 12px;
                color: #64748b;
                line-height: 1.5;
            }
            .vbc-btn-export {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                color: #ffffff !important;
                border: none;
                padding: 14px 32px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 8px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 15px rgba(37,99,235,0.35);
                transition: all 0.2s;
            }
            .vbc-btn-export:hover {
                background: linear-gradient(135deg, #1d4ed8, #1e40af);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37,99,235,0.45);
            }
            .vbc-info-box {
                background: #eff6ff;
                border-left: 4px solid #3b82f6;
                padding: 15px 20px;
                border-radius: 0 8px 8px 0;
                margin-bottom: 20px;
                color: #1e3a8a;
                font-size: 13px;
                line-height: 1.6;
            }
        </style>

        <!-- Header -->
        <div class="vbc-admin-header">
            <div>
                <h1><span class="dashicons dashicons-superhero-alt" style="font-size: 28px; width: 28px; height: 28px;"></span> Ultimate Flatsome VibeCode</h1>
                <p><?php _e('Trình mở rộng chuyên sâu UX Builder & Cổng tự động hóa Landing Page chuẩn CRO', 'vibecode'); ?></p>
            </div>
            <div style="text-align: right;">
                <span class="vbc-badge">Phiên bản 1.7.0</span>
            </div>
        </div>

        <?php if (isset($_GET['saved']) && $_GET['saved'] === '1'): ?>
            <div class="notice notice-success is-dismissible" style="margin-bottom: 20px; border-left-color: #10b981;">
                <p><strong><?php _e('✓ Đã lưu cài đặt cấu hình thành công!', 'vibecode'); ?></strong></p>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="vbc-nav-tab-wrapper">
            <a href="?page=vibecode-settings&tab=export" class="vbc-nav-tab <?php echo $current_tab === 'export' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-download"></span> <?php _e('Xuất Dự Án Antigravity', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=api" class="vbc-nav-tab <?php echo $current_tab === 'api' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-rest-api"></span> <?php _e('API & Xác Thực', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=config" class="vbc-nav-tab <?php echo $current_tab === 'config' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-settings"></span> <?php _e('FTP & Thương Hiệu', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=docs" class="vbc-nav-tab <?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-book"></span> <?php _e('Hướng Dẫn & Shortcodes', 'vibecode'); ?>
            </a>
        </div>

        <!-- TAB 1: EXPORT TO ANTIGRAVITY PROJECT -->
        <?php if ($current_tab === 'export'): ?>
            <div class="vbc-info-box">
                <strong>💡 Antigravity Project Package:</strong> Khi xuất file ZIP, hệ thống sẽ đóng gói toàn bộ thư mục <code>skills/</code> (gồm các script clone và tạo landing page tự động) cùng tệp <code>vbc-config.json</code> chứa dữ liệu ngữ cảnh website. Antigravity AI sẽ đọc hiểu toàn bộ thông tin này để tạo Landing Page chuẩn xác theo ngữ cảnh của website bạn.
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field('vbc_export_project_nonce', 'vbc_export_nonce'); ?>
                <input type="hidden" name="vbc_action" value="export_antigravity_project" />

                <!-- 1. Kết Nối API & Token -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-admin-network"></span> 1. Thông Tin Kết Nối API</h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('WordPress REST API Endpoint', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr($api_url); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#2563eb;" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('VibeCode API Token (User Admin)', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr($token); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#059669;" />
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
                            <input type="text" name="ftp_host" value="<?php echo esc_attr($ftp_host); ?>" placeholder="Ví dụ: 103.161.172.211 hoặc ftp.domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP User', 'vibecode'); ?></label>
                            <input type="text" name="ftp_user" value="<?php echo esc_attr($ftp_user); ?>" placeholder="Ví dụ: myuser@domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Password', 'vibecode'); ?></label>
                            <input type="password" name="ftp_password" value="<?php echo esc_attr($ftp_password); ?>" placeholder="••••••••••••" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Đường dẫn thư mục gốc Website trên Hosting (Root Path)', 'vibecode'); ?></label>
                            <input type="text" name="ftp_path" value="<?php echo esc_attr($ftp_path); ?>" placeholder="<?php echo esc_attr($detected_root_path); ?>" />
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                <?php printf(__('Tự động nhận diện thư mục gốc chứa wp-config.php: <code>%s</code>', 'vibecode'), esc_html($detected_root_path)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Lựa Chọn Dữ Liệu Ngữ Cảnh Xuất Ra JSON -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-database"></span> 3. Chọn Dữ Liệu Ngữ Cảnh Website Đóng Gói Vào vbc-config.json</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -5px; margin-bottom: 15px;">
                        Tích chọn những dữ liệu bạn muốn đưa vào file cấu hình. Bất kỳ trường nào không chọn hoặc trống dữ liệu sẽ tự động được gán là <code>&lt;none&gt;</code> để AI nhận diện và yêu cầu nhập lại nếu cần.
                    </p>

                    <!-- Checkbox Site Name & Tagline -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_site_name" id="chk_site_name" value="1" checked />
                        <div>
                            <label for="chk_site_name" class="vbc-chk-title"><?php _e('Tên Website & Khẩu Hiệu (Site Name & Tagline)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Hiện tại: <strong><?php echo esc_html(get_bloginfo('name')); ?></strong> — <em><?php echo esc_html(get_bloginfo('description')); ?></em>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Contact Info -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_contact" id="chk_contact" value="1" checked />
                        <div style="width: 100%;">
                            <label for="chk_contact" class="vbc-chk-title"><?php _e('Thông Tin Thương Hiệu & Liên Hệ (Phone, Email, Địa chỉ, Zalo)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc" style="margin-bottom: 10px;">
                                Antigravity AI sẽ tự động điền các thông tin này vào Hero Banner, Nút Hotline và Footer của Landing Page.
                            </div>
                            <div class="vbc-grid-2" style="margin-top: 10px;">
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Hotline / Số điện thoại', 'vibecode'); ?></label>
                                    <input type="text" name="brand_phone" value="<?php echo esc_attr($brand_phone); ?>" placeholder="0912 345 678" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Email Liên Hệ', 'vibecode'); ?></label>
                                    <input type="email" name="brand_email" value="<?php echo esc_attr($brand_email); ?>" placeholder="contact@domain.com" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Địa chỉ Doanh Nghiệp', 'vibecode'); ?></label>
                                    <input type="text" name="brand_address" value="<?php echo esc_attr($brand_address); ?>" placeholder="Số 123 Đường ABC, Hà Nội" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Link Zalo / Chat Hotline', 'vibecode'); ?></label>
                                    <input type="text" name="brand_zalo" value="<?php echo esc_attr($brand_zalo); ?>" placeholder="https://zalo.me/0912345678" style="width:100%;" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Styles & Flatsome Options -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_styles" id="chk_styles" value="1" checked />
                        <div>
                            <label for="chk_styles" class="vbc-chk-title"><?php _e('Toàn Bộ Cài Đặt Flatsome Theme (Customizer & wp_options)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Trích xuất toàn bộ cấu hình Flatsome trong bảng <code>wp_options</code> và Customizer (Bảng màu Primary/Secondary/Success/Alert, Typography, Header, Footer, Site Width, Layout, Custom CSS, Social Links...). Antigravity AI sẽ đồng bộ 100% style của Landing Page với giao diện tổng thể website.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Products -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_products" id="chk_products" value="1" <?php echo $product_count > 0 ? 'checked' : ''; ?> />
                        <div>
                            <label for="chk_products" class="vbc-chk-title"><?php _e('Danh Sách Sản Phẩm (WooCommerce Products)', 'vibecode'); ?> <?php echo $product_count > 0 ? "($product_count sản phẩm)" : '(Chưa có sản phẩm)'; ?></label>
                            <div class="vbc-chk-desc">
                                Xuất tên sản phẩm, giá bán, danh mục, link chi tiết và ảnh đại diện để AI chèn bảng giá/card sản phẩm.
                            </div>
                            <?php if ($product_count > 0): ?>
                                <div style="margin-top: 8px;">
                                    <label style="font-size: 12px; font-weight: 600;"><?php _e('Số lượng sản phẩm cần lấy:', 'vibecode'); ?></label>
                                    <select name="products_count" style="width: 120px; font-size: 12px;">
                                        <option value="5">5 sản phẩm</option>
                                        <option value="10" selected>10 sản phẩm</option>
                                        <option value="20">20 sản phẩm</option>
                                        <option value="50">50 sản phẩm</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Checkbox Services / Pages -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_services" id="chk_services" value="1" checked />
                        <div>
                            <label for="chk_services" class="vbc-chk-title"><?php _e('Danh Sách Dịch Vụ & Trang Quan Trọng', 'vibecode'); ?> (<?php echo $page_count; ?> trang)</label>
                            <div class="vbc-chk-desc">
                                Xuất danh sách trang tĩnh, tiêu đề, tóm tắt và đường dẫn để AI hiểu các dịch vụ trọng tâm của website.
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
                            <?php if ($post_count > 0): ?>
                                <div style="margin-top: 8px;">
                                    <label style="font-size: 12px; font-weight: 600;"><?php _e('Số lượng bài viết cần lấy:', 'vibecode'); ?></label>
                                    <select name="posts_count" style="width: 120px; font-size: 12px;">
                                        <option value="5">5 bài viết</option>
                                        <option value="10" selected>10 bài viết</option>
                                        <option value="20">20 bài viết</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Custom Prompt Instruction -->
                    <div class="vbc-form-group" style="margin-top: 20px;">
                        <label><?php _e('Ghi Chú / Yêu Cầu Đặc Biệt Cho Antigravity AI (Tùy chọn)', 'vibecode'); ?></label>
                        <textarea name="custom_instructions" rows="3" placeholder="Ví dụ: Trang web chuyên cung cấp dịch vụ máy chủ và hosting tốc độ cao, tone màu ưu tiên Dark Tech Sleek..."></textarea>
                    </div>

                    <div style="margin-top: 15px;">
                        <label style="font-weight: 600; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="vbc_save_config_data" value="1" checked />
                            <?php _e('Lưu lại thông tin liên hệ và FTP ở trên vào website để tiện cho các lần xuất sau', 'vibecode'); ?>
                        </label>
                    </div>
                </div>

                <!-- Export Action Button -->
                <div style="margin-top: 20px; text-align: center; padding: 20px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <button type="submit" class="vbc-btn-export">
                        <span class="dashicons dashicons-archive" style="font-size: 20px;"></span>
                        <?php printf(__('Xuất File Dự Án %s-vibecode-project.zip', 'vibecode'), esc_html($clean_domain)); ?>
                    </button>
                    <p style="color: #64748b; font-size: 13px; margin-top: 10px; margin-bottom: 0;">
                        Tệp ZIP xuất ra chứa toàn bộ thư mục <code>skills/</code> và tệp <code>vbc-config.json</code> đã được cấu hình đầy đủ.
                    </p>
                </div>
            </form>

        <!-- TAB 2: API & TOKEN -->
        <?php elseif ($current_tab === 'api'): ?>
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-rest-api"></span> <?php _e('Thông Tin Xác Thực API', 'vibecode'); ?></h2>
                <p style="color: #64748b; font-size: 13px;">
                    <?php _e('API Token được cấp riêng cho tài khoản quản trị viên hiện tại để bảo vệ các thao tác đăng trang và upload media từ bên ngoài.', 'vibecode'); ?>
                </p>

                <form method="POST" action="">
                    <?php wp_nonce_field('vbc_save_settings_nonce', 'vbc_settings_nonce'); ?>
                    <input type="hidden" name="vbc_action" value="save_general_settings" />
                    <input type="hidden" name="current_tab" value="api" />

                    <div class="vbc-form-group">
                        <label><?php _e('WordPress REST API Base URL', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr($api_url); ?>" readonly style="background:#f8fafc; font-family:monospace;" />
                    </div>

                    <div class="vbc-form-group">
                        <label><?php _e('API Token Hiện Tại', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr($token); ?>" readonly style="background:#f8fafc; font-family:monospace; font-size:16px; font-weight:700; color:#2563eb;" />
                    </div>

                    <div class="vbc-form-group">
                        <label>
                            <input type="checkbox" name="vbc_regenerate_token" value="1" />
                            <strong><?php _e('Tạo lại Token mới (Lưu ý: Token cũ sẽ bị hủy hiệu lực ngay lập tức)', 'vibecode'); ?></strong>
                        </label>
                    </div>

                    <button type="submit" class="button button-primary"><?php _e('Lưu Thay Đổi & Cập Nhật Token', 'vibecode'); ?></button>
                </form>

                <hr style="margin: 25px 0; border: none; border-top: 1px solid #f1f5f9;">

                <h3><?php _e('Kiểm Tra REST API Endpoints', 'vibecode'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px; color: #475569; font-size: 13px; line-height: 1.8;">
                    <li><strong>Upload Media:</strong> <code>POST <?php echo esc_html($api_url); ?>/vbc/v1/upload</code> (Header: <code>X-VBC-Token: <?php echo esc_html(substr($token, 0, 8)); ?>...</code>)</li>
                    <li><strong>Đăng / Cập nhật Trang:</strong> <code>POST <?php echo esc_html($api_url); ?>/vbc/v1/page</code></li>
                    <li><strong>Lấy nội dung Trang:</strong> <code>GET <?php echo esc_html($api_url); ?>/vbc/v1/page?slug=trang-mau</code></li>
                </ul>
            </div>

        <!-- TAB 3: FTP & BRAND CONFIG -->
        <?php elseif ($current_tab === 'config'): ?>
            <form method="POST" action="">
                <?php wp_nonce_field('vbc_save_settings_nonce', 'vbc_settings_nonce'); ?>
                <input type="hidden" name="vbc_action" value="save_general_settings" />
                <input type="hidden" name="current_tab" value="config" />

                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-cloud"></span> <?php _e('Cài Đặt FTP Hosting Mặc Định', 'vibecode'); ?></h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Host', 'vibecode'); ?></label>
                            <input type="text" name="ftp_host" value="<?php echo esc_attr($ftp_host); ?>" placeholder="103.161.172.211" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP User', 'vibecode'); ?></label>
                            <input type="text" name="ftp_user" value="<?php echo esc_attr($ftp_user); ?>" placeholder="user@domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Password', 'vibecode'); ?></label>
                            <input type="password" name="ftp_password" value="<?php echo esc_attr($ftp_password); ?>" placeholder="••••••••••••" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Đường dẫn thư mục gốc Website trên Hosting (Root Path)', 'vibecode'); ?></label>
                            <input type="text" name="ftp_path" value="<?php echo esc_attr($ftp_path); ?>" placeholder="<?php echo esc_attr($detected_root_path); ?>" />
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                <?php printf(__('Tự động nhận diện thư mục gốc chứa wp-config.php: <code>%s</code>', 'vibecode'), esc_html($detected_root_path)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-businessperson"></span> <?php _e('Thông Tin Thương Hiệu Doanh Nghiệp', 'vibecode'); ?></h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('Số điện thoại / Hotline', 'vibecode'); ?></label>
                            <input type="text" name="brand_phone" value="<?php echo esc_attr($brand_phone); ?>" placeholder="0912 345 678" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Email liên hệ', 'vibecode'); ?></label>
                            <input type="email" name="brand_email" value="<?php echo esc_attr($brand_email); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Địa chỉ công ty', 'vibecode'); ?></label>
                            <input type="text" name="brand_address" value="<?php echo esc_attr($brand_address); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Link Zalo / Tư vấn', 'vibecode'); ?></label>
                            <input type="text" name="brand_zalo" value="<?php echo esc_attr($brand_zalo); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Giờ làm việc', 'vibecode'); ?></label>
                            <input type="text" name="brand_hours" value="<?php echo esc_attr($brand_hours); ?>" />
                        </div>
                    </div>

                    <button type="submit" class="button button-primary" style="margin-top: 10px;"><?php _e('Lưu Cấu Hình Mặc Định', 'vibecode'); ?></button>
                </div>
            </form>

        <!-- TAB 4: DOCS -->
        <?php elseif ($current_tab === 'docs'): ?>
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-book"></span> <?php _e('Danh Sách Shortcode VibeCode Phổ Biến', 'vibecode'); ?></h2>
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;"><strong>Shortcode</strong></th>
                            <th><strong>Mô Tả & Cú Pháp Mẫu</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[vbc_icon]</code></td>
                            <td>Thư viện vector icon thông minh: <code>[vbc_icon pack="lucide" name="shield-check" color="#2563eb" size="24px"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_card]</code></td>
                            <td>Khối thẻ card kính mờ: <code>[vbc_card variant="glass" border_radius="20px"] ... [/vbc_card]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_testimonial]</code></td>
                            <td>Đánh giá khách hàng: <code>[vbc_testimonial name="Nguyễn Văn A" stars="5" avatar_url="..."] Nhận xét... [/vbc_testimonial]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_accordion]</code></td>
                            <td>Khối hỏi đáp SEO FAQ: <code>[vbc_accordion][vbc_accordion_item title="Câu hỏi?"]Trả lời...[/vbc_accordion_item][/vbc_accordion]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_button]</code></td>
                            <td>Nút bấm chuyển đổi: <code>[vbc_button text="Xem Thêm" url="#link" variant="danger"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_slider]</code></td>
                            <td>Khối trượt Splide: <code>[vbc_slider per_page="1"][vbc_slide]...[/vbc_slide][/vbc_slider]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_fullpage]</code></td>
                            <td>Cuộn full-screen từng màn hình: <code>[vbc_fullpage]...[/vbc_fullpage]</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Giữ lại trường hiển thị Token trong User Profile cá nhân (chỉ dành riêng cho Administrator)
add_action('show_user_profile', 'vbc_user_profile_fields');
add_action('edit_user_profile', 'vbc_user_profile_fields');

function vbc_user_profile_fields($user) {
    // Chỉ duy nhất Administrator mới được tạo và hiển thị trường VibeCode API Settings
    if (!current_user_can('manage_options') || !user_can($user, 'administrator')) {
        return;
    }

    $token = get_user_meta($user->ID, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user->ID, 'vbc_api_token', $token);
    }
    ?>
    <h3><?php _e('VibeCode API Settings', 'vibecode'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="vbc_api_token"><?php _e('API Token', 'vibecode'); ?></label></th>
            <td>
                <input type="text" name="vbc_api_token" id="vbc_api_token" value="<?php echo esc_attr($token); ?>" class="regular-text" readonly style="background-color: #f0f0f0; font-family: monospace;" />
                <p class="description"><?php _e('Token này được sử dụng để xác thực các yêu cầu API bên ngoài (ví dụ: Antigravity skill để tạo Landing Page).', 'vibecode'); ?></p>
                <br>
                <label>
                    <input type="checkbox" name="vbc_regenerate_token" value="1" />
                    <?php _e('Tạo lại token mới (Regenerate API Token)', 'vibecode'); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'vbc_save_user_profile_fields');
add_action('edit_user_profile_update', 'vbc_save_user_profile_fields');

function vbc_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id) || !current_user_can('manage_options') || !user_can($user_id, 'administrator')) {
        return;
    }
    if (!empty($_POST['vbc_regenerate_token'])) {
        $new_token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $new_token);
    }
}


/**
 * 4. CỔNG REST API (UPLOAD & PAGE CREATOR)
 */
add_action('rest_api_init', 'vbc_register_rest_routes');
