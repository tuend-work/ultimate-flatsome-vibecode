<?php
/**
 * Ultimate Flatsome VibeCode - Component Registration Hub
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_register_component_shortcodes() {
    $component_tags = array(
        'card' => 'vbc_card_shortcode',
        'testimonial' => 'vbc_testimonial_shortcode',
        'accordion' => 'vbc_accordion_shortcode',
        'accordion_item' => 'vbc_accordion_item_shortcode',
        'tabs' => 'vbc_tabs_shortcode',
        'tab' => 'vbc_tab_shortcode',
        'button' => 'vbc_button_shortcode',
        'slider' => 'vbc_slider_shortcode',
        'slide' => 'vbc_slide_shortcode',
        'fullpage' => 'vbc_fullpage_shortcode',
        'post' => 'vbc_post_shortcode',
        'icon' => 'vbc_icon_shortcode_renderer',
    );

    foreach ($component_tags as $comp => $handler) {
        add_shortcode('vbc_' . $comp, $handler);
        add_shortcode('vbc_' . $comp . '_inner', $handler);
        for ($i = 1; $i <= 10; $i++) {
            add_shortcode('vbc_' . $comp . '_inner_' . $i, $handler);
        }
    }
}
