<?php
/**
 * Ultimate Flatsome VibeCode - Card Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_card_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'variant' => 'glass',
        'class' => '',
        'custom_class' => '',
        'custom_css' => '',
        'padding' => '', 'padding__md' => '', 'padding__sm' => '',
        'margin' => '', 'margin__md' => '', 'margin__sm' => '',
        'width' => '', 'width__md' => '', 'width__sm' => '',
        'height' => '', 'height__md' => '', 'height__sm' => '',
        'background_color' => '', 'background_color__md' => '', 'background_color__sm' => '',
        'border_radius' => '',
        'border_color' => '',
        'glow_color' => '',
        'font_family' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $padding = !empty($atts['padding']) ? $atts['padding'] : '35px 30px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '20px';
    $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255,255,255,0.08)';
    $glow = !empty($atts['glow_color']) ? $atts['glow_color'] : 'rgba(239, 68, 68, 0.2)';
    $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'rgba(255, 255, 255, 0.03)';

    $res = vbc_compile_element_css($atts, 'vbc-card');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $card_rules = '.' . $unique_class . ' { ';
    $card_rules .= 'background: ' . $bg . '; ';
    $card_rules .= 'backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); ';
    $card_rules .= 'border: 1px solid ' . $border . '; ';
    $card_rules .= 'border-radius: ' . $radius . '; ';
    $card_rules .= 'padding: ' . $padding . '; ';
    $card_rules .= 'box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); ';
    $card_rules .= 'transition: all 0.3s ease; ';
    $card_rules .= '} ';
    $card_rules .= '.' . $unique_class . ':hover { ';
    $card_rules .= 'transform: translateY(-8px); ';
    $card_rules .= 'border-color: ' . $glow . '; ';
    $card_rules .= 'box-shadow: 0 20px 40px ' . $glow . '; ';
    $card_rules .= '} ';

    $card_rules = str_replace(array("\r", "\n"), ' ', $card_rules);
    $card_rules = preg_replace('/\s+/', ' ', $card_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $card_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $card_rules;
        $default_css = '';
    }

    $class_str = trim('vbc-component-card ' . $unique_class . ' ' . $atts['custom_class']);

    $inner_content = !empty($atts['content']) ? $atts['content'] : $content;
    $inner_content = vbc_clean_inner_content($inner_content);
    return '<div class="' . esc_attr($class_str) . '">' . $default_css . $compiled_css . do_shortcode($inner_content) . '</div>';
}
