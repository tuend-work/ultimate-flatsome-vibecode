<?php
/**
 * Ultimate Flatsome VibeCode - Button Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_button_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'text' => 'Click Here',
        'url' => '#',
        'target' => '_self',
        'variant' => 'danger',
        'class' => '',
        'custom_class' => '',
        'custom_css' => '',
        'padding' => '', 'padding__md' => '', 'padding__sm' => '',
        'margin' => '', 'margin__md' => '', 'margin__sm' => '',
        'width' => '', 'width__md' => '', 'width__sm' => '',
        'height' => '', 'height__md' => '', 'height__sm' => '',
        'border_radius' => '',
        'font_size' => '', 'font_size__md' => '', 'font_size__sm' => '',
        'background_color' => '',
        'text_color' => '',
        'font_family' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-btn');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $padding = !empty($atts['padding']) ? $atts['padding'] : '16px 38px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '30px';
    $font_size = !empty($atts['font_size']) ? $atts['font_size'] : '15px';

    $btn_rules = '.' . $unique_class . ' { ';
    $btn_rules .= 'display: inline-block; ';
    $btn_rules .= 'padding: ' . $padding . '; ';
    $btn_rules .= 'border-radius: ' . $radius . '; ';
    $btn_rules .= 'font-weight: 800; ';
    $btn_rules .= 'font-size: ' . $font_size . '; ';
    $btn_rules .= 'text-decoration: none; ';
    $btn_rules .= 'text-align: center; ';
    $btn_rules .= 'transition: all 0.3s ease; ';
    $btn_rules .= 'letter-spacing: 0.5px; ';
    $btn_rules .= '} ';

    if ($atts['variant'] === 'danger' || $atts['variant'] === 'primary') {
        $btn_rules .= '.' . $unique_class . ' { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; box-shadow: 0 4px 25px rgba(239, 68, 68, 0.5); } ';
        $btn_rules .= '.' . $unique_class . ':hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(239, 68, 68, 0.7); color: #ffffff; } ';
    } elseif ($atts['variant'] === 'glass') {
        $btn_rules .= '.' . $unique_class . ' { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; backdrop-filter: blur(8px); } ';
        $btn_rules .= '.' . $unique_class . ':hover { background: rgba(255, 255, 255, 0.15); border-color: #ffffff; transform: translateY(-3px); color: #ffffff; } ';
    } elseif ($atts['variant'] === 'custom') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : '#2563eb';
        $color = !empty($atts['text_color']) ? $atts['text_color'] : '#ffffff';
        $btn_rules .= '.' . $unique_class . ' { background: ' . $bg . '; color: ' . $color . '; } ';
        $btn_rules .= '.' . $unique_class . ':hover { transform: translateY(-3px); opacity: 0.9; color: ' . $color . '; } ';
    }

    $btn_rules = str_replace(array("\r", "\n"), ' ', $btn_rules);
    $btn_rules = preg_replace('/\s+/', ' ', $btn_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $btn_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $btn_rules;
        $default_css = '';
    }

    $btn_text = !empty($atts['text']) ? esc_html($atts['text']) : do_shortcode(vbc_clean_inner_content($content));
    $btn_text = preg_replace('/<\/?p[^>]*>/i', '', $btn_text);
    $btn_text = preg_replace('/<\/?div[^>]*>/i', '', $btn_text);
    $btn_text = str_replace(array('<br>', '<br />'), '', $btn_text);

    return '<a href="' . esc_url($atts['url']) . '" target="' . esc_attr($atts['target']) . '" class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $default_css . $compiled_css . $btn_text . '</a>';
}
