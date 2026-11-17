<?php
/**
 * Ultimate Flatsome VibeCode - Badge / Status Pill Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_badge_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'text'             => '',
        'variant'          => 'gradient', // gradient | glass | soft | solid | outline
        'color_scheme'     => 'danger',   // danger | primary | success | warning | purple
        'pulse'            => 'false',    // true | false (pulsing animated dot)
        'icon'             => '',
        'size'             => 'sm',       // sm | md | lg
        'border_radius'    => '9999px',
        'font_size'        => '',
        'color'            => '',
        'background_color' => '',
        'class'            => '',
        'custom_class'     => '',
        'custom_css'       => '',
        'margin'           => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-badge');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $variant = strtolower(trim($atts['variant']));
    $scheme  = strtolower(trim($atts['color_scheme']));
    $size    = strtolower(trim($atts['size']));
    $pulse   = in_array(strtolower(trim($atts['pulse'])), array('true', '1', 'yes'), true);

    // Padding & Font Size theo size
    if ($size === 'lg') {
        $padding = '8px 20px';
        $font_sz = !empty($atts['font_size']) ? $atts['font_size'] : '14px';
        $dot_size = '9px';
    } elseif ($size === 'md') {
        $padding = '6px 16px';
        $font_sz = !empty($atts['font_size']) ? $atts['font_size'] : '13px';
        $dot_size = '8px';
    } else {
        // sm
        $padding = '4px 12px';
        $font_sz = !empty($atts['font_size']) ? $atts['font_size'] : '11.5px';
        $dot_size = '7px';
    }

    // Color definitions
    $palettes = array(
        'danger'  => array('primary' => '#ef4444', 'secondary' => '#dc2626', 'soft' => '#fef2f2', 'border' => '#fecaca'),
        'primary' => array('primary' => '#2563eb', 'secondary' => '#1d4ed8', 'soft' => '#eff6ff', 'border' => '#bfdbfe'),
        'success' => array('primary' => '#10b981', 'secondary' => '#059669', 'soft' => '#ecfdf5', 'border' => '#a7f3d0'),
        'warning' => array('primary' => '#f59e0b', 'secondary' => '#d97706', 'soft' => '#fffbeb', 'border' => '#fde68a'),
        'purple'  => array('primary' => '#8b5cf6', 'secondary' => '#7c3aed', 'soft' => '#f5f3ff', 'border' => '#ddd6fe'),
    );
    $c = isset($palettes[$scheme]) ? $palettes[$scheme] : $palettes['danger'];

    $badge_rules = '.' . $unique_class . ' { ';
    $badge_rules .= 'display: inline-flex; align-items: center; gap: 7px; ';
    $badge_rules .= 'padding: ' . $padding . '; ';
    $badge_rules .= 'border-radius: ' . esc_attr($atts['border_radius']) . '; ';
    $badge_rules .= 'font-size: ' . $font_sz . '; ';
    $badge_rules .= 'font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; ';
    $badge_rules .= 'line-height: 1; vertical-align: middle; width: fit-content; ';

    if ($variant === 'solid') {
        $badge_rules .= 'background: ' . $c['primary'] . '; color: #ffffff; ';
    } elseif ($variant === 'soft') {
        $badge_rules .= 'background: ' . $c['soft'] . '; color: ' . $c['primary'] . '; border: 1px solid ' . $c['border'] . '; ';
    } elseif ($variant === 'outline') {
        $badge_rules .= 'background: transparent; color: ' . $c['primary'] . '; border: 1.5px solid ' . $c['primary'] . '; ';
    } elseif ($variant === 'glass') {
        $badge_rules .= 'background: rgba(255,255,255,0.08); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px); ';
    } else {
        // Gradient mặc định
        $badge_rules .= 'background: linear-gradient(135deg, ' . $c['primary'] . ', ' . $c['secondary'] . '); color: #ffffff; box-shadow: 0 4px 15px ' . $c['border'] . '; ';
    }
    $badge_rules .= '} ';

    // Pulse dot effect
    if ($pulse) {
        $dot_color = ($variant === 'soft') ? $c['primary'] : '#ffffff';
        $badge_rules .= '.' . $unique_class . ' .vbc-badge-dot { position: relative; width: ' . $dot_size . '; height: ' . $dot_size . '; border-radius: 50%; background: ' . $dot_color . '; display: inline-block; flex-shrink: 0; } ';
        $badge_rules .= '.' . $unique_class . ' .vbc-badge-dot::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 50%; background: ' . $dot_color . '; animation: vbcBadgePulse 1.8s ease-out infinite; } ';
        $badge_rules .= '@keyframes vbcBadgePulse { 0% { transform: scale(1); opacity: 0.9; } 100% { transform: scale(2.8); opacity: 0; } } ';
    }

    $badge_rules = str_replace(array("\r", "\n"), ' ', $badge_rules);
    $badge_rules = preg_replace('/\s+/', ' ', $badge_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $badge_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $badge_rules;
        $default_css = '';
    }

    $text = !empty($atts['text']) ? esc_html($atts['text']) : do_shortcode(vbc_clean_inner_content($content));

    $icon_html = '';
    if (!empty($atts['icon'])) {
        $icon_html = do_shortcode('[vbc_icon name="' . esc_attr($atts['icon']) . '" size="1.1em" display="inline-flex"]');
    }

    $pulse_html = $pulse ? '<span class="vbc-badge-dot"></span>' : '';

    return '<span class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $default_css . $compiled_css . $pulse_html . $icon_html . '<span>' . $text . '</span></span>';
}
add_shortcode('vbc_badge', 'vbc_badge_shortcode');
