<?php
/**
 * Ultimate Flatsome VibeCode - Testimonial Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_testimonial_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'name' => 'Khách Hàng',
        'company' => '',
        'stars' => '5',
        'avatar_url' => '',
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
        'text_color' => '',
        'author_color' => '',
        'font_family' => '',
    ), $atts);

    $stars_count = intval($atts['stars']);
    $stars_html = str_repeat('★', $stars_count);

    $padding = !empty($atts['padding']) ? $atts['padding'] : '35px 28px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '20px';
    $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255,255,255,0.08)';
    $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'rgba(255,255,255,0.03)';
    $text_color = !empty($atts['text_color']) ? $atts['text_color'] : '#cbd5e1';
    $author_color = !empty($atts['author_color']) ? $atts['author_color'] : '#ffffff';

    $res = vbc_compile_element_css($atts, 'vbc-testi');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $testi_rules = '.' . $unique_class . ' { ';
    $testi_rules .= 'background: ' . $bg . '; ';
    $testi_rules .= 'backdrop-filter: blur(16px); ';
    $testi_rules .= 'border: 1px solid ' . $border . '; ';
    $testi_rules .= 'padding: ' . $padding . '; ';
    $testi_rules .= 'border-radius: ' . $radius . '; ';
    $testi_rules .= 'box-shadow: 0 15px 35px rgba(0,0,0,0.3); ';
    $testi_rules .= 'transition: all 0.3s; ';
    $testi_rules .= '} ';
    $testi_rules .= '.' . $unique_class . ':hover { ';
    $testi_rules .= 'transform: translateY(-5px); ';
    $testi_rules .= 'border-color: rgba(239,68,68,0.3); ';
    $testi_rules .= 'box-shadow: 0 20px 40px rgba(239,68,68,0.15); ';
    $testi_rules .= '} ';
    $testi_rules .= '.' . $unique_class . ' .vbc-stars { color: #fbbf24; font-size: 18px; margin-bottom: 15px; letter-spacing: 2px; } ';
    $testi_rules .= '.' . $unique_class . ' .vbc-quote { color: ' . $text_color . '; font-size: 15px; line-height: 1.7; margin-bottom: 20px; font-style: italic; } ';
    $testi_rules .= '.' . $unique_class . ' .vbc-author { color: ' . $author_color . '; font-weight: 700; font-size: 16px; margin-bottom: 3px; } ';
    $testi_rules .= '.' . $unique_class . ' .vbc-company { color: #94a3b8; font-size: 13px; } ';

    $testi_rules = str_replace(array("\r", "\n"), ' ', $testi_rules);
    $testi_rules = preg_replace('/\s+/', ' ', $testi_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $testi_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $testi_rules;
        $default_css = '';
    }

    $avatar_html = '';
    if (!empty($atts['avatar_url'])) {
        $avatar_html = '<img src="' . esc_url($atts['avatar_url']) . '" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; border: 2px solid rgba(239,68,68,0.4); object-fit: cover;">';
    }

    $meta_html = '<div style="display: flex; align-items: center;">' . $avatar_html . '<div><div class="vbc-author">' . esc_html($atts['name']) . '</div><div class="vbc-company">' . esc_html($atts['company']) . '</div></div></div>';

    $inner_content = !empty($atts['content']) ? $atts['content'] : $content;
    $inner_content = vbc_clean_inner_content($inner_content);
    return '<div class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $default_css . $compiled_css . '<div class="vbc-stars">' . $stars_html . '</div><div class="vbc-quote">"' . do_shortcode($inner_content) . '"</div>' . $meta_html . '</div>';
}
