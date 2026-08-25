<?php
/**
 * Plugin Name: Ultimate Flatsome
 * Plugin URI: https://github.com/tuend-work/ultimate-flatsome-vibecode
 * Description: Tiện ích mở rộng cao cấp cho theme Flatsome: Quản lý thông tin website tập trung, hệ thống HTML & UX Builder Elements đa năng, hỗ trợ responsive hoàn hảo, chèn dữ liệu động và tối ưu hóa chuyển đổi.
 * Version: 2.5.45
 * Author: Antigravity AI
 * Author URI: https://github.com/tuend-work
 * License: GPL2
 * Text Domain: vibecode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// 1. Define Plugin Constants
define( 'VBC_VERSION', '2.5.45' );
define( 'VBC_PLUGIN_FILE', __FILE__ );
define( 'VBC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VBC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VBC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// 2. Load Core Bootstrap Class
require_once VBC_PLUGIN_DIR . 'includes/class-vbc-core.php';

/**
 * Initialize Plugin Core
 */
function vbc_init_plugin() {
    return Ultimate_Flatsome_VibeCode_Core::instance();
}

// Kick off plugin initialization
vbc_init_plugin();
