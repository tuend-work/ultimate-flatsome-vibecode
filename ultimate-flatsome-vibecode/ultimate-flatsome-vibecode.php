<?php
/**
 * Plugin Name: Ultimate Flatsome VibeCode Elements
 * Plugin URI: https://github.com/tuend-work/ultimate-flatsome-vibecode
 * Description: Thêm các phần tử HTML cơ bản tích hợp sâu với Flatsome UX Builder, hỗ trợ responsive hoàn hảo, chèn dữ liệu động (Post Meta, ACF) và chỉnh sửa CSS nâng cao.
 * Version: 2.5.25
 * Author: Antigravity AI
 * Author URI: https://github.com/tuend-work
 * License: GPL2
 * Text Domain: vibecode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// 1. Define Plugin Constants
define( 'VBC_VERSION', '2.5.25' );
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
