<?php
/**
 * Ultimate Flatsome VibeCode - Infinite Marquee / Logo Ticker Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_marquee_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'speed'          => '25s',
        'direction'      => 'left', // left | right
        'pause_on_hover' => 'true',
        'gap'            => '40px',
        'gradient_edges' => 'true',
        'class'          => '',
        'custom_class'   => '',
        'custom_css'     => '',
        'padding'        => '20px 0',
        'margin'         => '',
        'width'          => '100%',
        'background_color' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-marquee');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $speed = !empty($atts['speed']) ? $atts['speed'] : '25s';
    $gap = !empty($atts['gap']) ? $atts['gap'] : '40px';
    $direction = (strtolower(trim($atts['direction'])) === 'right') ? 'reverse' : 'normal';
    $pause_on_hover = in_array(strtolower(trim($atts['pause_on_hover'])), array('true', '1', 'yes'), true);
    $gradient_edges = in_array(strtolower(trim($atts['gradient_edges'])), array('true', '1', 'yes'), true);

    $anim_id = 'vbcMarqueeAnim_' . substr(md5($unique_class), 0, 6);

    $css_rules = '.' . $unique_class . ' { ';
    $css_rules .= 'display: flex; overflow: hidden; user-select: none; position: relative; width: 100%; ';
    if ($gradient_edges) {
        $css_rules .= 'mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%); ';
        $css_rules .= '-webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%); ';
    }
    $css_rules .= '} ';

    $css_rules .= '.' . $unique_class . ' .vbc-marquee-track { ';
    $css_rules .= 'display: flex; flex-shrink: 0; align-items: center; justify-content: space-around; ';
    $css_rules .= 'min-width: 100%; gap: ' . $gap . '; ';
    $css_rules .= 'animation: ' . $anim_id . ' ' . $speed . ' linear infinite ' . $direction . '; ';
    $css_rules .= 'will-change: transform; ';
    $css_rules .= '} ';

    if ($pause_on_hover) {
        $css_rules .= '.' . $unique_class . ':hover .vbc-marquee-track { animation-play-state: paused !important; } ';
    }

    $css_rules .= '@keyframes ' . $anim_id . ' { ';
    $css_rules .= 'from { transform: translateX(0); } ';
    $css_rules .= 'to { transform: translateX(calc(-100% - ' . $gap . ')); } ';
    $css_rules .= '} ';

    $css_rules = str_replace(array("\r", "\n"), ' ', $css_rules);
    $css_rules = preg_replace('/\s+/', ' ', $css_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $css_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $css_rules;
        $default_css = '';
    }

    $inner_items = do_shortcode(vbc_clean_inner_content($content));

    $track_1 = '<div class="vbc-marquee-track">' . $inner_items . '</div>';
    $track_2 = '<div class="vbc-marquee-track" aria-hidden="true">' . $inner_items . '</div>';

    return '<div class="vbc-marquee-wrap ' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $default_css . $compiled_css . $track_1 . $track_2 . '</div>';
}
add_shortcode('vbc_marquee', 'vbc_marquee_shortcode');
