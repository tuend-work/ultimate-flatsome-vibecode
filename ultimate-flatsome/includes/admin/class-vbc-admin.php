<?php
/**
 * Ultimate Flatsome - Admin Dashboard & Settings Management
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Đăng Ký Admin Menu Hệ Thống
 */
function vbc_register_admin_menu() {
    // Menu chính cấp 1 trên thanh Admin Sidebar
    add_menu_page(
        __( 'Ultimate Flatsome', 'vibecode' ),
        __( 'Ultimate Flatsome', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome',
        'vbc_render_admin_settings_page',
        'dashicons-admin-site-alt3',
        30
    );

    // Submenu 1: Cài Đặt Chung (General Settings)
    add_submenu_page(
        'ultimate-flatsome',
        __( 'Cài Đặt Chung - Ultimate Flatsome', 'vibecode' ),
        __( 'Cài Đặt Chung', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome',
        'vbc_render_admin_settings_page'
    );

    // Submenu 2: UX Block Templates (Post Types & Taxonomies)
    add_submenu_page(
        'ultimate-flatsome',
        __( 'UX Block Templates - Ultimate Flatsome', 'vibecode' ),
        __( 'UX Block Templates 🎨', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome-templates',
        'vbc_render_admin_templates_page'
    );

    // Submenu 3: VibeCode Hub (AI Export & Automation)
    add_submenu_page(
        'ultimate-flatsome',
        __( 'VibeCode Hub - Ultimate Flatsome', 'vibecode' ),
        __( 'VibeCode Hub', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome-vibecode',
        'vbc_render_admin_vibecode_page'
    );

    // Submenu 4: Cập Nhật Plugin (GitHub Auto-Updater)
    add_submenu_page(
        'ultimate-flatsome',
        __( 'Cập Nhật Plugin - Ultimate Flatsome', 'vibecode' ),
        __( 'Cập Nhật Plugin 🔄', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome-update',
        'vbc_render_admin_update_page'
    );

    // Submenu 5: API & Xác Thực
    add_submenu_page(
        'ultimate-flatsome',
        __( 'API & Xác Thực - Ultimate Flatsome', 'vibecode' ),
        __( 'API & Xác Thực', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome-api',
        'vbc_render_admin_api_page'
    );

    // Submenu 6: Hướng Dẫn & Shortcodes
    add_submenu_page(
        'ultimate-flatsome',
        __( 'Hướng Dẫn & Shortcodes - Ultimate Flatsome', 'vibecode' ),
        __( 'Hướng Dẫn & Shortcodes', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome-docs',
        'vbc_render_admin_docs_page'
    );

    // Menu phụ dưới Flatsome Panel nếu theme Flatsome đang kích hoạt
    add_submenu_page(
        'flatsome-panel',
        __( 'Ultimate Flatsome', 'vibecode' ),
        __( 'Ultimate Flatsome', 'vibecode' ),
        'manage_options',
        'ultimate-flatsome',
        'vbc_render_admin_settings_page'
    );

    // Đăng ký bí danh menu cũ vibecode-settings để đảm bảo tương thích 100%
    add_submenu_page(
        null,
        __( 'VibeCode Settings', 'vibecode' ),
        __( 'VibeCode Settings', 'vibecode' ),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page'
    );
}
add_action( 'admin_menu', 'vbc_register_admin_menu' );

/**
 * Route Submenu Callbacks
 */
function vbc_render_admin_templates_page() {
    $_GET['tab'] = 'templates';
    vbc_render_admin_settings_page();
}

function vbc_render_admin_vibecode_page() {
    $_GET['tab'] = 'vibecode';
    vbc_render_admin_settings_page();
}

function vbc_render_admin_update_page() {
    $_GET['tab'] = 'update';
    vbc_render_admin_settings_page();
}

function vbc_render_admin_api_page() {
    $_GET['tab'] = 'api';
    vbc_render_admin_settings_page();
}

function vbc_render_admin_docs_page() {
    $_GET['tab'] = 'docs';
    vbc_render_admin_settings_page();
}

/**
 * 2. Xử Lý Lưu Cài Đặt (Save Settings Request)
 */
function vbc_handle_save_settings_request() {
    if ( isset( $_POST['vbc_action'] ) && $_POST['vbc_action'] === 'save_general_settings' ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        check_admin_referer( 'vbc_save_settings_nonce', 'vbc_settings_nonce' );

        $tab = ! empty( $_POST['current_tab'] ) ? sanitize_key( $_POST['current_tab'] ) : 'general';

        // 1. Lưu các trường cấu hình chung (Tab General)
        if ( class_exists( 'Ultimate_Flatsome_General_Settings' ) ) {
            $fields = Ultimate_Flatsome_General_Settings::get_fields_config();
            foreach ( $fields as $key => $config ) {
                $opt_key = $config['option_key'];
                if ( ! isset( $_POST[ $opt_key ] ) ) {
                    continue;
                }

                $raw_val = $_POST[ $opt_key ];
                if ( $config['type'] === 'textarea' ) {
                    // Cho phép chèn mã HTML/JS đối với người dùng có quyền unfiltered_html
                    $clean_val = current_user_can( 'unfiltered_html' ) ? wp_unslash( $raw_val ) : wp_kses_post( $raw_val );
                } elseif ( $config['type'] === 'email' ) {
                    $clean_val = sanitize_email( $raw_val );
                } else {
                    $clean_val = sanitize_text_field( $raw_val );
                }

                // Cập nhật vào bảng wp_options
                update_option( $opt_key, $clean_val );

                // Đồng bộ sang legacy key nếu có để tương thích ngược hoàn hảo
                if ( ! empty( $config['legacy_key'] ) ) {
                    update_option( $config['legacy_key'], $clean_val );
                }
            }
        }

        // 2. Lưu cấu hình FTP
        if ( isset( $_POST['ftp_host'] ) ) update_option( 'vbc_ftp_host', sanitize_text_field( $_POST['ftp_host'] ) );
        if ( isset( $_POST['ftp_user'] ) ) update_option( 'vbc_ftp_user', sanitize_text_field( $_POST['ftp_user'] ) );
        if ( isset( $_POST['ftp_password'] ) ) update_option( 'vbc_ftp_password', sanitize_text_field( $_POST['ftp_password'] ) );
        if ( isset( $_POST['ftp_path'] ) ) update_option( 'vbc_ftp_path', sanitize_text_field( $_POST['ftp_path'] ) );

        // 3. Tạo lại API Token nếu được yêu cầu
        if ( ! empty( $_POST['vbc_regenerate_token'] ) ) {
            $user_id = get_current_user_id();
            $new_token = bin2hex( random_bytes( 20 ) );
            update_user_meta( $user_id, 'vbc_api_token', $new_token );
        }

        wp_redirect( add_query_arg( array( 'page' => 'ultimate-flatsome', 'tab' => $tab, 'saved' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }
}
add_action( 'admin_init', 'vbc_handle_save_settings_request' );

/**
 * 3. Giao Diện Bảng Quản Trị Trung Tâm (Ultimate Flatsome Admin Dashboard)
 */
function vbc_render_admin_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
    if ( $current_tab === 'export' ) {
        $current_tab = 'vibecode';
    }

    $user_id = get_current_user_id();
    $token = get_user_meta( $user_id, 'vbc_api_token', true );
    if ( empty( $token ) ) {
        $token = bin2hex( random_bytes( 20 ) );
        update_user_meta( $user_id, 'vbc_api_token', $token );
    }

    $api_url = get_rest_url( null, '' );
    $site_url = get_site_url();
    $parsed_url = parse_url( $site_url );
    $domain_host = ! empty( $parsed_url['host'] ) ? $parsed_url['host'] : 'website';
    $clean_domain = sanitize_file_name( preg_replace( '/[^a-zA-Z0-9\.\-]/', '-', $domain_host ) );

    // Tự động nhận diện đường dẫn thư mục gốc
    $detected_root_path = defined( 'ABSPATH' ) ? wp_normalize_path( untrailingslashit( ABSPATH ) ) : ( defined( 'WP_CONTENT_DIR' ) ? wp_normalize_path( dirname( WP_CONTENT_DIR ) ) : '/public_html' );
    $ftp_host = get_option( 'vbc_ftp_host', '' );
    $ftp_user = get_option( 'vbc_ftp_user', '' );
    $ftp_password = get_option( 'vbc_ftp_password', '' );
    $ftp_path = get_option( 'vbc_ftp_path', $detected_root_path );

    $has_wc = class_exists( 'WooCommerce' );
    $product_count = $has_wc ? wp_count_posts( 'product' )->publish : 0;
    $post_count = wp_count_posts( 'post' )->publish;
    $page_count = wp_count_posts( 'page' )->publish;

    $fields_config = class_exists( 'Ultimate_Flatsome_General_Settings' ) ? Ultimate_Flatsome_General_Settings::get_fields_config() : array();
    ?>
    <div class="wrap vbc-admin-wrap" style="max-width: 1180px; margin-top: 20px;">
        <style>
            .vbc-admin-header {
                background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);
                padding: 26px 32px;
                border-radius: 14px;
                color: #ffffff;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            .vbc-admin-header h1 {
                color: #ffffff;
                font-size: 25px;
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
                padding: 5px 14px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 700;
            }
            .vbc-nav-tab-wrapper {
                margin-bottom: 25px;
                border-bottom: 2px solid #e2e8f0;
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }
            .vbc-nav-tab {
                padding: 13px 22px;
                text-decoration: none;
                font-weight: 700;
                font-size: 14.5px;
                color: #64748b;
                border-radius: 10px 10px 0 0;
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
                padding-bottom: 14px;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.02);
            }
            .vbc-card {
                background: #ffffff;
                border-radius: 14px;
                border: 1px solid #e2e8f0;
                padding: 30px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.03);
                margin-bottom: 25px;
            }
            .vbc-card h2 {
                font-size: 19px;
                font-weight: 800;
                color: #0f172a;
                margin-top: 0;
                margin-bottom: 16px;
                padding-bottom: 14px;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .vbc-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 22px;
            }
            @media (max-width: 782px) {
                .vbc-grid-2 { grid-template-columns: 1fr; }
            }
            .vbc-form-group {
                margin-bottom: 20px;
            }
            .vbc-form-group label {
                display: block;
                font-weight: 700;
                font-size: 13.5px;
                color: #1e293b;
                margin-bottom: 7px;
            }
            .vbc-form-group input[type="text"],
            .vbc-form-group input[type="password"],
            .vbc-form-group input[type="email"],
            .vbc-form-group textarea,
            .vbc-form-group select {
                width: 100%;
                border: 1.5px solid #cbd5e1;
                border-radius: 8px;
                padding: 10px 14px;
                font-size: 14px;
                box-sizing: border-box;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .vbc-form-group input:focus,
            .vbc-form-group textarea:focus {
                border-color: #2563eb;
                outline: none;
                box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            }
            .vbc-field-row {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 18px 20px;
                margin-bottom: 16px;
                transition: all 0.2s;
            }
            .vbc-field-row:hover {
                border-color: #93c5fd;
                background: #ffffff;
                box-shadow: 0 4px 12px rgba(37,99,235,0.05);
            }
            .vbc-field-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 8px;
                flex-wrap: wrap;
                gap: 8px;
            }
            .vbc-shortcode-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #e0f2fe;
                color: #0369a1;
                border: 1px solid #bae6fd;
                padding: 4px 10px;
                border-radius: 6px;
                font-size: 12px;
                font-family: Consolas, Monaco, monospace;
                font-weight: 600;
            }
            .vbc-copy-btn {
                background: #ffffff;
                color: #2563eb;
                border: 1px solid #bfdbfe;
                border-radius: 6px;
                padding: 3px 10px;
                font-size: 11.5px;
                font-weight: 700;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                transition: all 0.2s;
            }
            .vbc-copy-btn:hover {
                background: #2563eb;
                color: #ffffff;
                border-color: #2563eb;
            }
            .vbc-copy-btn.copied {
                background: #10b981 !important;
                color: #ffffff !important;
                border-color: #10b981 !important;
            }
            .vbc-field-desc {
                font-size: 12px;
                color: #64748b;
                margin-top: 6px;
                line-height: 1.5;
            }
            .vbc-btn-save {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                color: #ffffff !important;
                border: none;
                padding: 13px 32px;
                font-size: 15px;
                font-weight: 800;
                border-radius: 8px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 4px 15px rgba(37,99,235,0.3);
                transition: all 0.2s;
            }
            .vbc-btn-save:hover {
                background: linear-gradient(135deg, #1d4ed8, #1e40af);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37,99,235,0.4);
            }
            .vbc-btn-export {
                background: linear-gradient(135deg, #059669, #047857);
                color: #ffffff !important;
                border: none;
                padding: 14px 34px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 8px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 15px rgba(5,150,105,0.35);
                transition: all 0.2s;
            }
            .vbc-btn-export:hover {
                background: linear-gradient(135deg, #047857, #065f46);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(5,150,105,0.45);
            }
            .vbc-info-box {
                background: #eff6ff;
                border-left: 4px solid #3b82f6;
                padding: 16px 22px;
                border-radius: 0 10px 10px 0;
                margin-bottom: 25px;
                color: #1e3a8a;
                font-size: 13.5px;
                line-height: 1.6;
            }
            .vbc-checkbox-item {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 15px 20px;
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
        </style>

        <!-- Header -->
        <div class="vbc-admin-header">
            <div>
                <h1><span class="dashicons dashicons-admin-site-alt3" style="font-size: 28px; width: 28px; height: 28px;"></span> Ultimate Flatsome</h1>
                <p><?php _e('Trung tâm quản trị website tập trung & Tiện ích mở rộng cao cấp cho Flatsome Theme', 'vibecode'); ?></p>
            </div>
            <div style="text-align: right; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end;">
                <span class="vbc-badge">Phiên bản v<?php echo esc_html( VBC_VERSION ); ?></span>
                <a href="?page=ultimate-flatsome&tab=update" class="vbc-btn-save" style="padding: 6px 16px; font-size: 13px; text-decoration: none; border-radius: 20px; box-shadow: 0 2px 10px rgba(37,99,235,0.25);">
                    <span class="dashicons dashicons-update" style="font-size: 15px; margin-top: 1px;"></span>
                    <?php _e('Cập Nhật Plugin', 'vibecode'); ?>
                </a>
            </div>
        </div>

        <?php if ( isset( $_GET['saved'] ) && $_GET['saved'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible" style="margin-bottom: 25px; border-left-color: #10b981; padding: 12px 18px; border-radius: 8px;">
                <p style="font-size: 14px; font-weight: 700; color: #065f46; margin: 0;">
                    <?php _e('✓ Đã lưu toàn bộ cài đặt và đồng bộ thành công vào wp_options!', 'vibecode'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['update_success'] ) && $_GET['update_success'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible" style="margin-bottom: 25px; border-left-color: #10b981; padding: 14px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(16,185,129,0.15);">
                <p style="font-size: 14.5px; font-weight: 800; color: #065f46; margin: 0;">
                    <?php printf( __( '🎉 Chúc mừng! Plugin Ultimate Flatsome đã được cập nhật thành công lên phiên bản v%s trực tiếp từ GitHub!', 'vibecode' ), esc_html( ! empty( $_GET['new_version'] ) ? $_GET['new_version'] : VBC_VERSION ) ); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['update_error'] ) ) : ?>
            <div class="notice notice-error is-dismissible" style="margin-bottom: 25px; border-left-color: #ef4444; padding: 14px 20px; border-radius: 8px;">
                <p style="font-size: 14px; font-weight: 700; color: #991b1b; margin: 0;">
                    <?php printf( __( '✕ Lỗi cập nhật từ GitHub: %s', 'vibecode' ), esc_html( urldecode( $_GET['update_error'] ) ) ); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="vbc-nav-tab-wrapper">
            <a href="?page=ultimate-flatsome&tab=general" class="vbc-nav-tab <?php echo $current_tab === 'general' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-generic"></span> <?php _e('Cài Đặt Chung', 'vibecode'); ?>
            </a>
            <a href="?page=ultimate-flatsome&tab=templates" class="vbc-nav-tab <?php echo $current_tab === 'templates' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-layout"></span> <?php _e('UX Block Templates 🎨', 'vibecode'); ?>
            </a>
            <a href="?page=ultimate-flatsome&tab=vibecode" class="vbc-nav-tab <?php echo $current_tab === 'vibecode' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-download"></span> <?php _e('VibeCode Hub & Xuất Dự Án', 'vibecode'); ?>
            </a>
            <a href="?page=ultimate-flatsome&tab=update" class="vbc-nav-tab <?php echo $current_tab === 'update' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-update"></span> <?php _e('Cập Nhật Plugin (GitHub)', 'vibecode'); ?>
            </a>
            <a href="?page=ultimate-flatsome&tab=api" class="vbc-nav-tab <?php echo $current_tab === 'api' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-rest-api"></span> <?php _e('API & Xác Thực', 'vibecode'); ?>
            </a>
            <a href="?page=ultimate-flatsome&tab=docs" class="vbc-nav-tab <?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-book"></span> <?php _e('Hướng Dẫn & Shortcodes', 'vibecode'); ?>
            </a>
        </div>

        <!-- =========================================================================
             TAB 1: CÀI ĐẶT CHUNG (GENERAL SETTINGS & WP_OPTIONS SYNC)
             ========================================================================= -->
        <?php if ( $current_tab === 'general' ) : ?>
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

        <!-- =========================================================================
             TAB 2: UX BLOCK TEMPLATES (CHỌN UX BLOCK LÀM TEMPLATE HIỂN THỊ)
             ========================================================================= -->
        <?php elseif ( $current_tab === 'templates' ) : 
            $template_rules = class_exists( 'Ultimate_Flatsome_Template_Builder' ) ? Ultimate_Flatsome_Template_Builder::get_template_rules() : array();
            $ux_blocks = class_exists( 'Ultimate_Flatsome_Template_Builder' ) ? Ultimate_Flatsome_Template_Builder::get_ux_blocks_options() : array();
            
            // Lấy danh sách Public Post Types
            $post_types = get_post_types( array( 'public' => true ), 'objects' );
            unset( $post_types['blocks'] ); // Bỏ UX Blocks ra khỏi danh sách

            // Lấy danh sách Public Taxonomies
            $taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
        ?>
            <div class="vbc-info-box">
                <strong>🎨 Dynamic UX Block Templates Hub:</strong> Cho phép bạn thiết kế và gán bất kỳ <strong>UX Block</strong> nào làm giao diện hiển thị mặc định cho từng loại bài viết (Single Post Types) hoặc từng danh mục/thẻ (Taxonomies). Bạn có thể bấm <strong>"Sửa trong UX Builder"</strong> để kéo thả chỉnh sửa trực quan mọi thành phần giao diện động (Tiêu đề, Ảnh đại diện, Nội dung, Tác giả, Bình luận, Lưới bài viết)!
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field( 'uf_save_templates_nonce', 'uf_templates_nonce' ); ?>
                <input type="hidden" name="vbc_action" value="save_template_rules" />
                <input type="hidden" name="current_tab" value="templates" />

                <!-- 1. Single Post Types Templates -->
                <div class="vbc-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2><span class="dashicons dashicons-admin-post"></span> 1. Giao Diện Chi Tiết (Single Post Types)</h2>
                            <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">
                                Gán UX Block template làm giao diện hiển thị cho từng loại bài viết chi tiết.
                            </p>
                        </div>
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ultimate-flatsome&uf_action=create_sample_template&type=single_post' ), 'uf_create_sample_template' ); ?>" class="button button-secondary" style="font-weight: 700; height: 38px; display: inline-flex; align-items: center; gap: 6px; border-color: #2563eb; color: #2563eb;">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('✨ Tạo Mẫu Bài Viết Chuẩn UX Builder 1-Click', 'vibecode'); ?>
                        </a>
                    </div>

                    <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th style="width: 220px;"><strong>Loại Bài Viết (Post Type)</strong></th>
                                <th><strong>UX Block Template Được Gán</strong></th>
                                <th style="width: 240px; text-align: right;"><strong>Hành Động</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $post_types as $pt_slug => $pt_obj ) : 
                                $rule_key = 'single_' . $pt_slug;
                                $selected_block_id = ! empty( $template_rules[ $rule_key ] ) ? intval( $template_rules[ $rule_key ] ) : '';
                            ?>
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <strong style="font-size: 14px; color: #0f172a;"><?php echo esc_html( $pt_obj->labels->singular_name ?: $pt_obj->label ); ?></strong>
                                        <div style="font-size: 12px; color: #64748b; font-family: monospace;">Slug: <?php echo esc_html( $pt_slug ); ?></div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <select name="uf_template_rules[<?php echo esc_attr( $rule_key ); ?>]" style="width: 100%; max-width: 450px; font-size: 13.5px; border-radius: 6px;">
                                            <?php foreach ( $ux_blocks as $b_id => $b_label ) : ?>
                                                <option value="<?php echo esc_attr( $b_id ); ?>" <?php selected( $selected_block_id, $b_id ); ?>>
                                                    <?php echo esc_html( $b_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="vertical-align: middle; text-align: right;">
                                        <?php if ( ! empty( $selected_block_id ) ) : ?>
                                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $selected_block_id . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #2563eb; border-color: #1d4ed8;">
                                                <span class="dashicons dashicons-edit" style="font-size: 14px; margin-top: 2px;"></span>
                                                <?php _e('Sửa trong UX Builder', 'vibecode'); ?>
                                            </a>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 12.5px;"><?php _e('Giao diện gốc theme', 'vibecode'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Taxonomy & Archive Templates -->
                <div class="vbc-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2><span class="dashicons dashicons-category"></span> 2. Giao Diện Danh Mục & Lưu Trữ (Taxonomies)</h2>
                            <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">
                                Gán UX Block template làm giao diện hiển thị cho các trang Chuyên mục (Category), Thẻ (Tag), Danh mục sản phẩm.
                            </p>
                        </div>
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ultimate-flatsome&uf_action=create_sample_template&type=category' ), 'uf_create_sample_template' ); ?>" class="button button-secondary" style="font-weight: 700; height: 38px; display: inline-flex; align-items: center; gap: 6px; border-color: #059669; color: #059669;">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('✨ Tạo Mẫu Danh Mục Chuẩn UX Builder 1-Click', 'vibecode'); ?>
                        </a>
                    </div>

                    <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th style="width: 220px;"><strong>Phân Loại (Taxonomy)</strong></th>
                                <th><strong>UX Block Template Được Gán</strong></th>
                                <th style="width: 240px; text-align: right;"><strong>Hành Động</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $taxonomies as $tax_slug => $tax_obj ) : 
                                $rule_key = 'taxonomy_' . $tax_slug;
                                $selected_block_id = ! empty( $template_rules[ $rule_key ] ) ? intval( $template_rules[ $rule_key ] ) : '';
                            ?>
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <strong style="font-size: 14px; color: #0f172a;"><?php echo esc_html( $tax_obj->labels->singular_name ?: $tax_obj->label ); ?></strong>
                                        <div style="font-size: 12px; color: #64748b; font-family: monospace;">Taxonomy: <?php echo esc_html( $tax_slug ); ?></div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <select name="uf_template_rules[<?php echo esc_attr( $rule_key ); ?>]" style="width: 100%; max-width: 450px; font-size: 13.5px; border-radius: 6px;">
                                            <?php foreach ( $ux_blocks as $b_id => $b_label ) : ?>
                                                <option value="<?php echo esc_attr( $b_id ); ?>" <?php selected( $selected_block_id, $b_id ); ?>>
                                                    <?php echo esc_html( $b_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="vertical-align: middle; text-align: right;">
                                        <?php if ( ! empty( $selected_block_id ) ) : ?>
                                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $selected_block_id . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #2563eb; border-color: #1d4ed8;">
                                                <span class="dashicons dashicons-edit" style="font-size: 14px; margin-top: 2px;"></span>
                                                <?php _e('Sửa trong UX Builder', 'vibecode'); ?>
                                            </a>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 12.5px;"><?php _e('Giao diện gốc theme', 'vibecode'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-bottom: 30px;">
                    <button type="submit" class="vbc-btn-save" style="font-size: 16px; padding: 14px 36px;">
                        <span class="dashicons dashicons-saved" style="font-size: 18px;"></span>
                        <?php _e('Lưu Toàn Bộ Cấu Hình Templates', 'vibecode'); ?>
                    </button>
                </div>
            </form>

            <!-- 3. Cẩm Nang Dynamic Tags Cho UX Builder -->
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-shortcode"></span> 3. Danh Sách Thẻ Động (Dynamic Tags) Cho UX Builder</h2>
                <p style="color: #64748b; font-size: 13px;">
                    Khi mở UX Builder để thiết kế UX Block template, bạn có thể kéo thả trực tiếp các element trong nhóm <strong>"Ultimate Flatsome Templates"</strong> hoặc chèn các shortcodes bên dưới:
                </p>

                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 220px;"><strong>Shortcode</strong></th>
                            <th style="width: 320px;"><strong>Cú Pháp Mẫu</strong></th>
                            <th><strong>Mô Tả Chức Năng</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[uf_post_title]</code></td>
                            <td><code>[uf_post_title tag="h1" font_size="36px" color="#0f172a"]</code></td>
                            <td>Hiển thị Tiêu đề bài viết / Tên chuyên mục tự động theo bài viết đang xem.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_content]</code></td>
                            <td><code>[uf_post_content]</code></td>
                            <td>Hiển thị toàn bộ nội dung bài viết gốc (<code>the_content</code>).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_thumbnail]</code></td>
                            <td><code>[uf_post_thumbnail border_radius="16px" aspect_ratio="16/9"]</code></td>
                            <td>Hiển thị Ảnh đại diện (Featured Image) bài viết với bo góc và tỷ lệ khung hình tùy biến.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_meta]</code></td>
                            <td><code>[uf_post_meta type="date|author|categories|comments_count"]</code></td>
                            <td>Hiển thị Ngày đăng, Tác giả, Chuyên mục hoặc Số bình luận.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_author]</code></td>
                            <td><code>[uf_post_author avatar_size="80" show_bio="yes"]</code></td>
                            <td>Hiển thị Box Tác Giả chuyên nghiệp (Avatar, Tên tác giả, Tiểu sử và link bài viết).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_comments]</code></td>
                            <td><code>[uf_post_comments]</code></td>
                            <td>Hiển thị Khung bình luận và Form thảo luận chuẩn WordPress/Flatsome.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_navigation]</code></td>
                            <td><code>[uf_post_navigation]</code></td>
                            <td>Khối điều hướng Bài trước / Bài kế tiếp với giao diện thẻ card hiện đại.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_terms]</code></td>
                            <td><code>[uf_post_terms taxonomy="category" bg_color="#eff6ff"]</code></td>
                            <td>Hiển thị danh sách các chuyên mục dạng badge pills bo góc.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_breadcrumb]</code></td>
                            <td><code>[uf_breadcrumb]</code></td>
                            <td>Thanh điều hướng phân cấp Breadcrumbs chuẩn Flatsome.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_archive_title]</code></td>
                            <td><code>[uf_archive_title tag="h1" font_size="38px"]</code></td>
                            <td>Tiêu đề trang Category & Mô tả Chuyên mục.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_archive_posts]</code></td>
                            <td><code>[uf_archive_posts columns="3" image_height="220px"]</code></td>
                            <td>Lưới danh sách bài viết thuộc Category đang xem kèm Phân trang Flatsome chuẩn.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <!-- =========================================================================
             TAB 3: VIBECODE HUB & XUẤT DỰ ÁN ANTIGRAVITY (GIỮ NGUYÊN)
             ========================================================================= -->
        <?php elseif ( $current_tab === 'vibecode' ) : ?>
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

        <!-- =========================================================================
             TAB 3: CẬP NHẬT PLUGIN TỪ GITHUB (AUTO-UPDATER)
             ========================================================================= -->
        <?php elseif ( $current_tab === 'update' ) : ?>
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

        <!-- =========================================================================
             TAB 4: API & XÁC THỰC (GIỮ NGUYÊN)
             ========================================================================= -->
        <?php elseif ( $current_tab === 'api' ) : ?>
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

        <!-- =========================================================================
             TAB 4: HƯỚNG DẪN & SHORTCODES
             ========================================================================= -->
        <?php elseif ( $current_tab === 'docs' ) : ?>
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
        <?php endif; ?>
    </div>

    <!-- Script copy 1-click clipboard & AJAX Updater -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var copyBtns = document.querySelectorAll('.vbc-copy-btn');
        copyBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var text = this.getAttribute('data-clipboard');
                if (!text) return;
                navigator.clipboard.writeText(text).then(function() {
                    var originalHTML = btn.innerHTML;
                    btn.innerHTML = '✓ Đã copy!';
                    btn.classList.add('copied');
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('copied');
                    }, 2000);
                }).catch(function(err) {
                    prompt('Copy shortcode bên dưới:', text);
                });
            });
        });

        // AJAX Check update from GitHub
        var checkBtn = document.getElementById('vbc-btn-check-update');
        if (checkBtn) {
            checkBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var resBox = document.getElementById('vbc-update-check-result');
                checkBtn.disabled = true;
                checkBtn.innerHTML = '<span class="dashicons dashicons-update" style="animation: rotation 1s infinite linear;"></span> Đang kiểm tra...';
                resBox.style.display = 'block';
                resBox.style.background = '#f1f5f9';
                resBox.style.color = '#334155';
                resBox.style.border = '1px solid #cbd5e1';
                resBox.innerHTML = 'Đang kết nối tới GitHub API...';

                var formData = new FormData();
                formData.append('action', 'vbc_check_plugin_update');
                formData.append('security', '<?php echo wp_create_nonce("vbc_ajax_nonce"); ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                }).then(function(r) { return r.json(); }).then(function(res) {
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<span class="dashicons dashicons-search"></span> Kiểm Tra Bản Mới Trên GitHub';
                    if (res.success && res.data) {
                        if (res.data.has_update) {
                            resBox.style.background = '#ecfdf5';
                            resBox.style.color = '#065f46';
                            resBox.style.border = '1px solid #a7f3d0';
                            resBox.innerHTML = '🎉 ' + res.data.message + ' — Hãy nhấn nút "Tải & Cập Nhật Tự Động Từ GitHub Ngay" ở bên dưới!';
                        } else {
                            resBox.style.background = '#eff6ff';
                            resBox.style.color = '#1e40af';
                            resBox.style.border = '1px solid #bfdbfe';
                            resBox.innerHTML = '✓ ' + res.data.message;
                        }
                    } else {
                        resBox.style.background = '#fef2f2';
                        resBox.style.color = '#991b1b';
                        resBox.style.border = '1px solid #fecaca';
                        resBox.innerHTML = '✕ ' + (res.data ? res.data.message : 'Lỗi kết nối kiểm tra.');
                    }
                }).catch(function(err) {
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = '<span class="dashicons dashicons-search"></span> Kiểm Tra Bản Mới Trên GitHub';
                    resBox.style.background = '#fef2f2';
                    resBox.style.color = '#991b1b';
                    resBox.innerHTML = '✕ Lỗi kết nối: ' + err.message;
                });
            });
        }
    });
    </script>
    <style>
        @keyframes rotation {
            from { transform: rotate(0deg); }
            to { transform: rotate(359deg); }
        }
    </style>
    <?php
}

/**
 * 4. Trường User Profile (Chỉ dành riêng cho Administrator)
 */
add_action( 'show_user_profile', 'vbc_user_profile_fields' );
add_action( 'edit_user_profile', 'vbc_user_profile_fields' );

function vbc_user_profile_fields( $user ) {
    if ( ! current_user_can( 'manage_options' ) || ! user_can( $user, 'administrator' ) ) {
        return;
    }

    $token = get_user_meta( $user->ID, 'vbc_api_token', true );
    if ( empty( $token ) ) {
        $token = bin2hex( random_bytes( 20 ) );
        update_user_meta( $user->ID, 'vbc_api_token', $token );
    }
    ?>
    <h3><?php _e( 'Ultimate Flatsome API Settings', 'vibecode' ); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="vbc_api_token"><?php _e( 'API Token', 'vibecode' ); ?></label></th>
            <td>
                <input type="text" name="vbc_api_token" id="vbc_api_token" value="<?php echo esc_attr( $token ); ?>" class="regular-text" readonly style="background-color: #f0f0f0; font-family: monospace;" />
                <p class="description"><?php _e( 'Token này được sử dụng để xác thực các yêu cầu API bên ngoài (Antigravity skills).', 'vibecode' ); ?></p>
                <br>
                <label>
                    <input type="checkbox" name="vbc_regenerate_token" value="1" />
                    <?php _e( 'Tạo lại token mới (Regenerate API Token)', 'vibecode' ); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}

add_action( 'personal_options_update', 'vbc_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'vbc_save_user_profile_fields' );

function vbc_save_user_profile_fields( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) || ! current_user_can( 'manage_options' ) || ! user_can( $user_id, 'administrator' ) ) {
        return;
    }
    if ( ! empty( $_POST['vbc_regenerate_token'] ) ) {
        $new_token = bin2hex( random_bytes( 20 ) );
        update_user_meta( $user_id, 'vbc_api_token', $new_token );
    }
}
