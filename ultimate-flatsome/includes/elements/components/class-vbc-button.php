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
        // Icon options
        'icon' => '',
        'icon_position' => 'left', // left | right
        'icon_animation' => 'none', // slide | bounce | none
        // Visual effects
        'shimmer' => 'false',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-btn');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $padding = !empty($atts['padding']) ? $atts['padding'] : '16px 36px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '30px';
    $font_size = !empty($atts['font_size']) ? $atts['font_size'] : '15px';
    $variant = strtolower(trim($atts['variant']));

    $btn_rules = '.' . $unique_class . ' { ';
    $btn_rules .= 'display: inline-flex; align-items: center; justify-content: center; gap: 10px; ';
    $btn_rules .= 'padding: ' . $padding . '; ';
    $btn_rules .= 'border-radius: ' . $radius . '; ';
    $btn_rules .= 'font-weight: 800; ';
    $btn_rules .= 'font-size: ' . $font_size . '; ';
    $btn_rules .= 'text-decoration: none; ';
    $btn_rules .= 'text-align: center; ';
    $btn_rules .= 'transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); ';
    $btn_rules .= 'letter-spacing: 0.5px; position: relative; overflow: hidden; cursor: pointer; ';
    $btn_rules .= '} ';

    if ($variant === 'danger' || $variant === 'primary') {
        $btn_rules .= '.' . $unique_class . ' { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; box-shadow: 0 4px 25px rgba(239, 68, 68, 0.4); } ';
        $btn_rules .= '.' . $unique_class . ':hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(239, 68, 68, 0.65); color: #ffffff; } ';
    } elseif ($variant === 'success') {
        $btn_rules .= '.' . $unique_class . ' { background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; box-shadow: 0 4px 25px rgba(16, 185, 129, 0.4); } ';
        $btn_rules .= '.' . $unique_class . ':hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(16, 185, 129, 0.65); color: #ffffff; } ';
    } elseif ($variant === 'glass') {
        $btn_rules .= '.' . $unique_class . ' { background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; backdrop-filter: blur(10px); } ';
        $btn_rules .= '.' . $unique_class . ':hover { background: rgba(255, 255, 255, 0.18); border-color: #ffffff; transform: translateY(-3px); color: #ffffff; } ';
    } elseif ($variant === 'outline') {
        $color = !empty($atts['text_color']) ? $atts['text_color'] : '#2563eb';
        $btn_rules .= '.' . $unique_class . ' { background: transparent; border: 2px solid ' . $color . '; color: ' . $color . '; } ';
        $btn_rules .= '.' . $unique_class . ':hover { background: ' . $color . '; color: #ffffff; transform: translateY(-3px); box-shadow: 0 8px 25px rgba(37,99,235,0.25); } ';
    } elseif ($variant === 'custom') {
        $bg = !empty($atts['background_color']) ? $atts['background_color'] : '#2563eb';
        $color = !empty($atts['text_color']) ? $atts['text_color'] : '#ffffff';
        $btn_rules .= '.' . $unique_class . ' { background: ' . $bg . '; color: ' . $color . '; } ';
        $btn_rules .= '.' . $unique_class . ':hover { transform: translateY(-3px); opacity: 0.92; color: ' . $color . '; box-shadow: 0 8px 25px rgba(0,0,0,0.15); } ';
    }

    // Hiệu ứng Shimmer quét sáng
    if ($atts['shimmer'] === 'true' || $atts['shimmer'] === '1') {
        $btn_rules .= '.' . $unique_class . '::after { content: ""; position: absolute; top: -50%; left: -60%; width: 40%; height: 200%; background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%); transform: rotate(30deg); transition: none; animation: vbcShimmer 3.5s infinite; } ';
        $btn_rules .= '@keyframes vbcShimmer { 0% { left: -60%; } 20% { left: 140%; } 100% { left: 140%; } } ';
    }

    // Hiệu ứng Icon Animation
    if (!empty($atts['icon']) && $atts['icon_animation'] === 'slide') {
        $btn_rules .= '.' . $unique_class . ' .vbc-btn-icon { transition: transform 0.25s ease; } ';
        if ($atts['icon_position'] === 'right') {
            $btn_rules .= '.' . $unique_class . ':hover .vbc-btn-icon { transform: translateX(5px); } ';
        } else {
            $btn_rules .= '.' . $unique_class . ':hover .vbc-btn-icon { transform: translateX(-4px); } ';
        }
    } elseif (!empty($atts['icon']) && $atts['icon_animation'] === 'bounce') {
        $btn_rules .= '.' . $unique_class . ':hover .vbc-btn-icon { animation: vbcBtnBounce 0.6s infinite alternate; } ';
        $btn_rules .= '@keyframes vbcBtnBounce { from { transform: translateY(0); } to { transform: translateY(-3px); } } ';
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

    // Xây dựng Icon nếu có
    $icon_html = '';
    if (!empty($atts['icon'])) {
        $icon_code = '[vbc_icon name="' . esc_attr($atts['icon']) . '" size="1.2em" display="inline-flex"]';
        $icon_html = '<span class="vbc-btn-icon">' . do_shortcode($icon_code) . '</span>';
    }

    $inner_html = ($atts['icon_position'] === 'right') ? ($btn_text . $icon_html) : ($icon_html . $btn_text);

    return '<a href="' . esc_url($atts['url']) . '" target="' . esc_attr($atts['target']) . '" class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $default_css . $compiled_css . $inner_html . '</a>';
}
