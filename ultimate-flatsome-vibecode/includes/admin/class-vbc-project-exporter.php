<?php
/**
 * Ultimate Flatsome VibeCode - Project Exporter (Zip Archive)
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Xử lý Request Xuất Dự Án Antigravity (.zip)
add_action('admin_init', 'vbc_handle_export_project_request');
function vbc_handle_export_project_request() {
    if (!isset($_POST['vbc_action']) || $_POST['vbc_action'] !== 'export_antigravity_project') {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền thực hiện hành động này.', 'vibecode'));
    }

    check_admin_referer('vbc_export_project_nonce', 'vbc_export_nonce');

    // Lưu cấu hình nếu người dùng tích chọn
    if (!empty($_POST['vbc_save_config_data'])) {
        if (isset($_POST['ftp_host'])) update_option('vbc_ftp_host', sanitize_text_field($_POST['ftp_host']));
        if (isset($_POST['ftp_user'])) update_option('vbc_ftp_user', sanitize_text_field($_POST['ftp_user']));
        if (isset($_POST['ftp_password'])) update_option('vbc_ftp_password', sanitize_text_field($_POST['ftp_password']));
        if (isset($_POST['ftp_path'])) update_option('vbc_ftp_path', sanitize_text_field($_POST['ftp_path']));
        if (isset($_POST['brand_phone'])) update_option('vbc_brand_phone', sanitize_text_field($_POST['brand_phone']));
        if (isset($_POST['brand_email'])) update_option('vbc_brand_email', sanitize_email($_POST['brand_email']));
        if (isset($_POST['brand_address'])) update_option('vbc_brand_address', sanitize_text_field($_POST['brand_address']));
        if (isset($_POST['brand_zalo'])) update_option('vbc_brand_zalo', sanitize_text_field($_POST['brand_zalo']));
        if (isset($_POST['brand_hours'])) update_option('vbc_brand_hours', sanitize_text_field($_POST['brand_hours']));
    }

    // 1. API URL & Token
    $api_url = get_rest_url(null, '');
    $user_id = get_current_user_id();
    $token = get_user_meta($user_id, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $token);
    }

    // 2. FTP Info (Fallback <none> & Auto detect root path)
    $detected_root_path = defined('ABSPATH') ? wp_normalize_path(untrailingslashit(ABSPATH)) : (defined('WP_CONTENT_DIR') ? wp_normalize_path(dirname(WP_CONTENT_DIR)) : '/public_html');
    $detected_plugin_path = defined('WP_PLUGIN_DIR') ? wp_normalize_path(WP_PLUGIN_DIR) : $detected_root_path . '/wp-content/plugins';
    $saved_ftp_path = get_option('vbc_ftp_path', '');
    $default_ftp_path = !empty($saved_ftp_path) ? $saved_ftp_path : $detected_root_path;

    $ftp_host = !empty($_POST['ftp_host']) ? trim($_POST['ftp_host']) : (get_option('vbc_ftp_host') ?: '<none>');
    $ftp_user = !empty($_POST['ftp_user']) ? trim($_POST['ftp_user']) : (get_option('vbc_ftp_user') ?: '<none>');
    $ftp_password = !empty($_POST['ftp_password']) ? trim($_POST['ftp_password']) : (get_option('vbc_ftp_password') ?: '<none>');
    $ftp_path = !empty($_POST['ftp_path']) ? trim($_POST['ftp_path']) : ($default_ftp_path ?: '<none>');

    // 3. Website Context & Brand Info
    $site_url = get_site_url();
    $parsed_url = parse_url($site_url);
    $domain_host = !empty($parsed_url['host']) ? $parsed_url['host'] : 'website';
    $clean_domain = sanitize_file_name(preg_replace('/[^a-zA-Z0-9\.\-]/', '-', $domain_host));

    $site_name = isset($_POST['include_site_name']) ? (get_bloginfo('name') ?: '<none>') : '<none>';
    $site_tagline = isset($_POST['include_site_tagline']) ? (get_bloginfo('description') ?: '<none>') : '<none>';
    $language = get_locale() ?: '<none>';
    $timezone = wp_timezone_string() ?: '<none>';

    $contact = array(
        'company_name' => isset($_POST['include_contact']) ? (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('company_name') ?: '<none>') : '<none>') : '<none>',
        'phone' => isset($_POST['include_contact']) ? (!empty($_POST['brand_phone']) ? trim($_POST['brand_phone']) : (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('phone') ?: '<none>') : (get_option('vbc_brand_phone') ?: '<none>'))) : '<none>',
        'phone_2' => isset($_POST['include_contact']) ? (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('phone_2') ?: '<none>') : '<none>') : '<none>',
        'email' => isset($_POST['include_contact']) ? (!empty($_POST['brand_email']) ? trim($_POST['brand_email']) : (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('email') ?: (get_option('admin_email') ?: '<none>')) : (get_option('vbc_brand_email') ?: (get_option('admin_email') ?: '<none>')))) : '<none>',
        'address' => isset($_POST['include_contact']) ? (!empty($_POST['brand_address']) ? trim($_POST['brand_address']) : (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('address') ?: '<none>') : (get_option('vbc_brand_address') ?: '<none>'))) : '<none>',
        'address_branch' => isset($_POST['include_contact']) ? (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('address_branch') ?: '<none>') : '<none>') : '<none>',
        'zalo' => isset($_POST['include_contact']) ? (!empty($_POST['brand_zalo']) ? trim($_POST['brand_zalo']) : (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('zalo') ?: '<none>') : (get_option('vbc_brand_zalo') ?: '<none>'))) : '<none>',
        'working_hours' => isset($_POST['include_contact']) ? (!empty($_POST['brand_hours']) ? trim($_POST['brand_hours']) : (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('working_hours') ?: '<none>') : (get_option('vbc_brand_hours') ?: '<none>'))) : '<none>',
        'tax_code' => isset($_POST['include_contact']) ? (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('tax_code') ?: '<none>') : '<none>') : '<none>',
        'copyright' => isset($_POST['include_contact']) ? (class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('copyright') ?: '<none>') : '<none>') : '<none>',
        'social' => array(
            'facebook' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('facebook') ?: '<none>') : '<none>',
            'youtube' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('youtube') ?: '<none>') : '<none>',
            'tiktok' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('tiktok') ?: '<none>') : '<none>',
            'instagram' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('instagram') ?: '<none>') : '<none>',
            'messenger' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('messenger') ?: '<none>') : '<none>',
            'telegram' => class_exists('Ultimate_Flatsome_General_Settings') ? (Ultimate_Flatsome_General_Settings::get_field_value('telegram') ?: '<none>') : '<none>',
        ),
    );

    // Toàn bộ cài đặt Flatsome (Customizer theme_mods + các options Flatsome trong wp_options)
    $flatsome_settings = '<none>';
    $brand_styles = '<none>';
    if (isset($_POST['include_styles'])) {
        global $wpdb;
        $theme_mods = get_theme_mods();

        // 1. Trích xuất các trường cơ bản cho brand_styles
        $brand_styles = array(
            'primary_color' => isset($theme_mods['color_primary']) ? $theme_mods['color_primary'] : '#2563eb',
            'secondary_color' => isset($theme_mods['color_secondary']) ? $theme_mods['color_secondary'] : '#f8fafc',
            'success_color' => isset($theme_mods['color_success']) ? $theme_mods['color_success'] : '#10b981',
            'alert_color' => isset($theme_mods['color_alert']) ? $theme_mods['color_alert'] : '#ef4444',
            'font_heading' => isset($theme_mods['type_headings_font']) ? $theme_mods['type_headings_font'] : '<none>',
            'font_body' => isset($theme_mods['type_texts_font']) ? $theme_mods['type_texts_font'] : '<none>',
            'font_nav' => isset($theme_mods['type_nav_font']) ? $theme_mods['type_nav_font'] : '<none>',
            'font_alt' => isset($theme_mods['type_alt_font']) ? $theme_mods['type_alt_font'] : '<none>',
        );

        // 2. Lấy toàn bộ các option liên quan đến flatsome trong database wp_options
        $db_options_raw = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'flatsome%' 
               OR option_name LIKE 'theme_mods_flatsome%'
        ", ARRAY_A);

        $flatsome_db_options = array();
        if (!empty($db_options_raw)) {
            foreach ($db_options_raw as $row) {
                $opt_key = $row['option_name'];
                $opt_val = maybe_unserialize($row['option_value']);
                $flatsome_db_options[$opt_key] = $opt_val;
            }
        }

        // 3. Làm sạch và chuẩn hóa theme_mods (loại bỏ các object không thể serialize)
        $clean_theme_mods = array();
        if (is_array($theme_mods)) {
            foreach ($theme_mods as $k => $v) {
                if (is_scalar($v) || is_array($v) || is_null($v)) {
                    $clean_theme_mods[$k] = $v;
                }
            }
        }

        // 4. Tổ chức đối tượng flatsome_settings theo cấu trúc chuyên nghiệp
        $flatsome_settings = array(
            'colors' => array(
                'primary' => isset($theme_mods['color_primary']) ? $theme_mods['color_primary'] : '<none>',
                'secondary' => isset($theme_mods['color_secondary']) ? $theme_mods['color_secondary'] : '<none>',
                'success' => isset($theme_mods['color_success']) ? $theme_mods['color_success'] : '<none>',
                'alert' => isset($theme_mods['color_alert']) ? $theme_mods['color_alert'] : '<none>',
                'links' => isset($theme_mods['color_links']) ? $theme_mods['color_links'] : '<none>',
                'texts' => isset($theme_mods['color_texts']) ? $theme_mods['color_texts'] : '<none>',
                'body_bg' => isset($theme_mods['body_bg']) ? $theme_mods['body_bg'] : '<none>',
            ),
            'typography' => array(
                'font_headings' => isset($theme_mods['type_headings_font']) ? $theme_mods['type_headings_font'] : '<none>',
                'font_body' => isset($theme_mods['type_texts_font']) ? $theme_mods['type_texts_font'] : '<none>',
                'font_nav' => isset($theme_mods['type_nav_font']) ? $theme_mods['type_nav_font'] : '<none>',
                'font_alt' => isset($theme_mods['type_alt_font']) ? $theme_mods['type_alt_font'] : '<none>',
                'font_headings_weight' => isset($theme_mods['type_headings_weight']) ? $theme_mods['type_headings_weight'] : '<none>',
                'font_body_weight' => isset($theme_mods['type_texts_weight']) ? $theme_mods['type_texts_weight'] : '<none>',
            ),
            'layout' => array(
                'site_width' => isset($theme_mods['site_width']) ? $theme_mods['site_width'] : '<none>',
                'layout' => isset($theme_mods['layout']) ? $theme_mods['layout'] : '<none>',
                'box_shadow' => isset($theme_mods['box_shadow']) ? $theme_mods['box_shadow'] : '<none>',
                'content_bg' => isset($theme_mods['content_bg']) ? $theme_mods['content_bg'] : '<none>',
                'container_padding' => isset($theme_mods['container_padding']) ? $theme_mods['container_padding'] : '<none>',
            ),
            'header' => array(
                'site_logo' => isset($theme_mods['site_logo']) ? (is_numeric($theme_mods['site_logo']) ? wp_get_attachment_url($theme_mods['site_logo']) : $theme_mods['site_logo']) : '<none>',
                'site_logo_dark' => isset($theme_mods['site_logo_dark']) ? (is_numeric($theme_mods['site_logo_dark']) ? wp_get_attachment_url($theme_mods['site_logo_dark']) : $theme_mods['site_logo_dark']) : '<none>',
                'header_height' => isset($theme_mods['header_height']) ? $theme_mods['header_height'] : '<none>',
                'header_bg' => isset($theme_mods['header_bg']) ? $theme_mods['header_bg'] : '<none>',
            ),
            'footer' => array(
                'footer_1_color' => isset($theme_mods['footer_1_color']) ? $theme_mods['footer_1_color'] : '<none>',
                'footer_2_color' => isset($theme_mods['footer_2_color']) ? $theme_mods['footer_2_color'] : '<none>',
                'footer_bottom_text' => isset($theme_mods['footer_bottom_text']) ? $theme_mods['footer_bottom_text'] : '<none>',
            ),
            'social_links' => array(
                'facebook' => isset($theme_mods['facebook_url']) ? $theme_mods['facebook_url'] : '<none>',
                'twitter' => isset($theme_mods['twitter_url']) ? $theme_mods['twitter_url'] : '<none>',
                'instagram' => isset($theme_mods['instagram_url']) ? $theme_mods['instagram_url'] : '<none>',
                'youtube' => isset($theme_mods['youtube_url']) ? $theme_mods['youtube_url'] : '<none>',
                'zalo' => isset($theme_mods['zalo_url']) ? $theme_mods['zalo_url'] : (!empty($_POST['brand_zalo']) ? trim($_POST['brand_zalo']) : '<none>'),
                'phone' => isset($theme_mods['phone_url']) ? $theme_mods['phone_url'] : (!empty($_POST['brand_phone']) ? trim($_POST['brand_phone']) : '<none>'),
                'email' => isset($theme_mods['email_url']) ? $theme_mods['email_url'] : (!empty($_POST['brand_email']) ? trim($_POST['brand_email']) : '<none>'),
            ),
            'custom_css' => array(
                'all' => isset($theme_mods['custom_css']) ? $theme_mods['custom_css'] : '<none>',
                'tablet' => isset($theme_mods['custom_css_tablet']) ? $theme_mods['custom_css_tablet'] : '<none>',
                'mobile' => isset($theme_mods['custom_css_mobile']) ? $theme_mods['custom_css_mobile'] : '<none>',
            ),
            'theme_mods' => !empty($clean_theme_mods) ? $clean_theme_mods : '<none>',
            'db_options' => !empty($flatsome_db_options) ? $flatsome_db_options : '<none>',
        );
    }

    $products_data = '<none>';
    if (isset($_POST['include_products'])) {
        $products_count = !empty($_POST['products_count']) ? min(max(1, intval($_POST['products_count'])), 50) : 10;
        $products = array();
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $products_count,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pid = get_the_ID();
                $price = '';
                $regular_price = '';
                if (function_exists('wc_get_product')) {
                    $wc_prod = wc_get_product($pid);
                    if ($wc_prod) {
                        $price = $wc_prod->get_price();
                        $regular_price = $wc_prod->get_regular_price();
                    }
                }
                $terms = get_the_terms($pid, 'product_cat');
                $cat_names = array();
                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $t) $cat_names[] = $t->name;
                }
                $thumb = wp_get_attachment_url(get_post_thumbnail_id($pid));
                $products[] = array(
                    'title' => get_the_title(),
                    'price' => $price ?: '<none>',
                    'regular_price' => $regular_price ?: '<none>',
                    'categories' => !empty($cat_names) ? implode(', ', $cat_names) : '<none>',
                    'url' => get_permalink(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>',
                    'image' => $thumb ?: '<none>'
                );
            }
            wp_reset_postdata();
        }
        $products_data = !empty($products) ? $products : '<none>';
    }

    $services_data = '<none>';
    if (isset($_POST['include_services'])) {
        $pages = array();
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 15,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pages[] = array(
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'slug' => get_post_field('post_name'),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>'
                );
            }
            wp_reset_postdata();
        }
        $services_data = !empty($pages) ? $pages : '<none>';
    }

    $posts_data = '<none>';
    if (isset($_POST['include_posts'])) {
        $posts_count = !empty($_POST['posts_count']) ? min(max(1, intval($_POST['posts_count'])), 30) : 10;
        $posts = array();
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_count,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $categories = get_the_category();
                $cat_list = array();
                if (!empty($categories)) {
                    foreach ($categories as $c) $cat_list[] = $c->name;
                }
                $posts[] = array(
                    'title' => get_the_title(),
                    'categories' => !empty($cat_list) ? implode(', ', $cat_list) : '<none>',
                    'url' => get_permalink(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>',
                    'date' => get_the_date('Y-m-d')
                );
            }
            wp_reset_postdata();
        }
        $posts_data = !empty($posts) ? $posts : '<none>';
    }

    $custom_note = !empty($_POST['custom_instructions']) ? sanitize_textarea_field($_POST['custom_instructions']) : '<none>';

    // Cấu trúc chuẩn file vbc-config.json
    $config_array = array(
        'api-url' => $api_url,
        'token' => $token,
        'ftp' => array(
            'host' => $ftp_host,
            'user' => $ftp_user,
            'password' => $ftp_password,
            'path' => $ftp_path,
            'root_path' => $ftp_path,
            'plugins_path' => $detected_plugin_path,
        ),
        'website_context' => array(
            'site_name' => $site_name,
            'site_tagline' => $site_tagline,
            'site_url' => $site_url,
            'language' => $language,
            'timezone' => $timezone,
            'contact' => $contact,
            'brand_styles' => $brand_styles,
            'flatsome_settings' => $flatsome_settings,
            'products' => $products_data,
            'services' => $services_data,
            'posts' => $posts_data,
            'custom_instructions' => $custom_note,
        )
    );

    $json_content = json_encode($config_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Tên file ZIP: {domain}-vibecode-project.zip
    $zip_filename = $clean_domain . '-vibecode-project.zip';
    $skills_dir = defined('VBC_PLUGIN_DIR') ? VBC_PLUGIN_DIR . 'skills/' : dirname(dirname(dirname(__FILE__))) . '/skills/';

    // Tạo ZIP bằng ZipArchive
    if (class_exists('ZipArchive')) {
        $temp_zip = wp_tempnam($zip_filename);
        $zip = new ZipArchive();
        if ($zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('vbc-config.json', $json_content);

            if (is_dir($skills_dir)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($skills_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    $sub_path = substr($item->getPathname(), strlen($skills_dir));
                    $zip_path = 'skills/' . str_replace('\\', '/', $sub_path);
                    if ($item->isDir()) {
                        $zip->addEmptyDir($zip_path);
                    } elseif ($item->isFile()) {
                        $zip->addFile($item->getPathname(), $zip_path);
                    }
                }
            }

            $zip->close();

            if (file_exists($temp_zip) && filesize($temp_zip) > 0) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
                header('Content-Length: ' . filesize($temp_zip));
                header('Pragma: no-cache');
                header('Expires: 0');
                readfile($temp_zip);
                @unlink($temp_zip);
                exit;
            }
        }
    }

    // Fallback PclZip
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    $temp_zip = wp_tempnam($zip_filename);
    $archive = new PclZip($temp_zip);

    $temp_config_file = wp_tempnam('vbc-config.json');
    file_put_contents($temp_config_file, $json_content);

    $files_to_add = array($temp_config_file);
    if (is_dir($skills_dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($skills_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $files_to_add[] = $item->getPathname();
            }
        }
    }

    $archive->create($files_to_add, PCLZIP_OPT_REMOVE_PATH, dirname($skills_dir));
    @unlink($temp_config_file);

    if (file_exists($temp_zip)) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($temp_zip));
        readfile($temp_zip);
        @unlink($temp_zip);
        exit;
    }

    wp_die(__('Không thể tạo tệp nén ZIP trên máy chủ.', 'vibecode'));
}

// Xử lý lưu cấu hình chung & tái tạo token
add_action('admin_init', 'vbc_handle_save_settings_request');
