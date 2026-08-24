<?php
/**
 * Ultimate Flatsome VibeCode - Core Loader & Bootstrap
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ultimate_Flatsome_VibeCode_Core {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Main plugin instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
    }

    /**
     * Load all modular components
     */
    private function load_dependencies() {
        $inc_dir = VBC_PLUGIN_DIR . 'includes/';

        // 1. Core Engine & Optimizations
        require_once $inc_dir . 'core/class-vbc-css-engine.php';
        require_once $inc_dir . 'optimizations/class-vbc-performance.php';

        // 2. Core HTML Elements & UX Builder Schemas
        require_once $inc_dir . 'elements/class-vbc-shortcodes.php';
        require_once $inc_dir . 'elements/class-vbc-ux-builder.php';

        // 3. Components Hub & UI Elements
        require_once $inc_dir . 'elements/class-vbc-components.php';
        require_once $inc_dir . 'elements/components/class-vbc-card.php';
        require_once $inc_dir . 'elements/components/class-vbc-testimonial.php';
        require_once $inc_dir . 'elements/components/class-vbc-accordion.php';
        require_once $inc_dir . 'elements/components/class-vbc-tabs.php';
        require_once $inc_dir . 'elements/components/class-vbc-button.php';
        require_once $inc_dir . 'elements/components/class-vbc-slider.php';
        require_once $inc_dir . 'elements/components/class-vbc-fullpage.php';
        require_once $inc_dir . 'elements/components/class-vbc-post.php';

        // 4. Icons System & Lazy Loading
        require_once $inc_dir . 'icons/class-vbc-icon-manager.php';

        // 5. REST API & Backend Handlers
        require_once $inc_dir . 'api/class-vbc-rest-api.php';

        // 6. Admin Panel, Settings & Project Exporter
        if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
            require_once $inc_dir . 'admin/class-vbc-admin.php';
            require_once $inc_dir . 'admin/class-vbc-project-exporter.php';
        }
    }
}
