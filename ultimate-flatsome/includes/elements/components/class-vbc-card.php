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
        'hover_effect' => 'lift',
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
    $glow = !empty($atts['glow_color']) ? $atts['glow_color'] : 'rgba(239, 68, 68, 0.2)';
    $variant = strtolower(trim($atts['variant']));
    $hover_effect = strtolower(trim($atts['hover_effect']));

    // Cấu hình style mặc định theo từng Variant
    if ($variant === 'flat') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : '#ffffff';
        $border = !empty($atts['border_color']) ? $atts['border_color'] : '#e2e8f0';
        $shadow = '0 4px 15px rgba(0, 0, 0, 0.04)';
        $backdrop = '';
    } elseif ($variant === 'bordered') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'transparent';
        $border = !empty($atts['border_color']) ? $atts['border_color'] : '#cbd5e1';
        $shadow = 'none';
        $backdrop = '';
    } elseif ($variant === 'elevated') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : '#ffffff';
        $border = !empty($atts['border_color']) ? $atts['border_color'] : 'transparent';
        $shadow = '0 20px 45px rgba(0, 0, 0, 0.08)';
        $backdrop = '';
    } elseif ($variant === 'gradient') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'linear-gradient(135deg, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.02) 100%)';
        $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255, 255, 255, 0.12)';
        $shadow = '0 15px 35px rgba(0, 0, 0, 0.3)';
        $backdrop = 'backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); ';
    } else {
        // Mặc định: Glassmorphism
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'rgba(255, 255, 255, 0.03)';
        $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255, 255, 255, 0.08)';
        $shadow = '0 15px 35px rgba(0, 0, 0, 0.4)';
        $backdrop = 'backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); ';
    }

    $res = vbc_compile_element_css($atts, 'vbc-card');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $card_rules = '.' . $unique_class . ' { ';
    $card_rules .= 'background: ' . $bg . '; ';
    if ($backdrop) $card_rules .= $backdrop;
    $card_rules .= 'border: 1px solid ' . $border . '; ';
    $card_rules .= 'border-radius: ' . $radius . '; ';
    $card_rules .= 'padding: ' . $padding . '; ';
    $card_rules .= 'box-shadow: ' . $shadow . '; ';
    $card_rules .= 'transition: all 0.3s ease; ';
    $card_rules .= '} ';

    // Cấu hình Hiệu ứng Hover
    if ($hover_effect === 'lift') {
        $card_rules .= '.' . $unique_class . ':hover { transform: translateY(-8px); border-color: ' . $glow . '; box-shadow: 0 20px 40px ' . $glow . '; } ';
    } elseif ($hover_effect === 'glow') {
        $card_rules .= '.' . $unique_class . ':hover { border-color: ' . $glow . '; box-shadow: 0 0 35px ' . $glow . '; } ';
    } elseif ($hover_effect === 'scale') {
        $card_rules .= '.' . $unique_class . ':hover { transform: scale(1.025); border-color: ' . $glow . '; } ';
    } elseif ($hover_effect === 'border') {
        $card_rules .= '.' . $unique_class . ':hover { border-color: ' . $glow . '; } ';
    }
    // hover_effect === 'none' -> không thêm hover rule

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
