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
    add_shortcode('vbc_card', 'vbc_card_shortcode');
    add_shortcode('vbc_testimonial', 'vbc_testimonial_shortcode');
    add_shortcode('vbc_accordion', 'vbc_accordion_shortcode');
    add_shortcode('vbc_accordion_item', 'vbc_accordion_item_shortcode');
    add_shortcode('vbc_tabs', 'vbc_tabs_shortcode');
    add_shortcode('vbc_tab', 'vbc_tab_shortcode');
    add_shortcode('vbc_tabs_inner', 'vbc_tabs_shortcode');
    add_shortcode('vbc_tab_inner', 'vbc_tab_shortcode');
    for ($i = 1; $i <= 5; $i++) {
        add_shortcode('vbc_tabs_inner_' . $i, 'vbc_tabs_shortcode');
        add_shortcode('vbc_tab_inner_' . $i, 'vbc_tab_shortcode');
    }
    add_shortcode('vbc_button', 'vbc_button_shortcode');
    add_shortcode('vbc_slider', 'vbc_slider_shortcode');
    add_shortcode('vbc_slide', 'vbc_slide_shortcode');
    add_shortcode('vbc_fullpage', 'vbc_fullpage_shortcode');
    add_shortcode('vbc_post', 'vbc_post_shortcode');
    add_shortcode('vbc_post_inner', 'vbc_post_shortcode');
    for ($i = 1; $i <= 5; $i++) {
        add_shortcode('vbc_post_inner_' . $i, 'vbc_post_shortcode');
    }
    add_shortcode('vbc_icon', 'vbc_icon_shortcode_renderer');
    add_shortcode('vbc_icon_inner', 'vbc_icon_shortcode_renderer');
    for ($i = 1; $i <= 5; $i++) {
        add_shortcode('vbc_icon_inner_' . $i, 'vbc_icon_shortcode_renderer');
    }
}
