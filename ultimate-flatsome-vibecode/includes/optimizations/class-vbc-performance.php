<?php
/**
 * Ultimate Flatsome VibeCode - Performance & Cleanup Tweaks
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_disable_wp_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'vbc_disable_emojis_tinymce');
    add_filter('wp_resource_hints', 'vbc_disable_emojis_dns_prefetch', 10, 2);
}

function vbc_disable_emojis_tinymce($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    }
    return array();
}

function vbc_disable_emojis_dns_prefetch($urls, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
        $urls = array_diff($urls, array($emoji_svg_url));
    }
    return $urls;
}

/**
 * 7. LOẠI BỎ ĐĂNG KÝ TRÙNG LẶP ĐỂ BẢO VỆ CẤU HÌNH NÂNG CAO
 * (Các phần tử đã được đăng ký đầy đủ và tối ưu trong vbc_register_ux_builder_elements)
 */

// Filter dọn dẹp các thẻ p và br tự động sinh quanh các shortcodes
function vbc_clean_shortcode_html($content) {
    $array = array(
        '<p>[' => '[',
        ']</p>' => ']',
        ']<br />' => ']',
        ']<br>' => ']',
        '<br />[' => '[',
        '<br>[' => '['
    );
    return strtr($content, $array);
}
add_filter('the_content', 'vbc_clean_shortcode_html', 100);

/**
 * 8. INJECT ACCORDION FAQ CARD STYLES
 * Đồng bộ phong cách Card FAQ hiện đại, bo góc 12px, hover mượt mà 99% theo web gốc
 */
