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
             TAB CONTENT RENDERER (MODULAR VIEWS)
             ========================================================================= -->
        <?php
        $allowed_tabs = array( 'general', 'templates', 'vibecode', 'update', 'api', 'docs' );
        if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
            $current_tab = 'general';
        }
        $tab_view_file = VBC_PLUGIN_DIR . 'includes/admin/views/tab-' . $current_tab . '.php';
        if ( file_exists( $tab_view_file ) ) {
            include $tab_view_file;
        }
        ?>
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
