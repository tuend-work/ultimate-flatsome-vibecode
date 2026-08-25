<?php
/**
 * Ultimate Flatsome VibeCode - Accordion Component & FAQ Schema
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_accordion_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'faq_schema' => 'true',
        'dark' => 'false',
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
        'font_family' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $schema_attr = ($atts['faq_schema'] === 'true') ? ' itemscope itemtype="https://schema.org/FAQPage"' : '';

    $is_dark = in_array(strtolower($atts['dark']), array('true', 'yes', '1'), true);

    $padding = !empty($atts['padding']) ? $atts['padding'] : ($is_dark ? '35px 45px' : '10px 0');
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : ($is_dark ? '24px' : '0');
    $border = !empty($atts['border_color']) ? $atts['border_color'] : ($is_dark ? 'rgba(255,255,255,0.08)' : 'transparent');
    $bg = !empty($atts['background_color']) ? $atts['background_color'] : ($is_dark ? 'rgba(255,255,255,0.02)' : 'transparent');

    $res = vbc_compile_element_css($atts, 'vbc-acc');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $acc_rules = '.' . $unique_class . ' { ';
    if (!empty($bg) && $bg !== 'transparent') {
        $acc_rules .= 'background: ' . $bg . '; ';
    }
    if ($is_dark) {
        $acc_rules .= 'backdrop-filter: blur(16px); ';
        $acc_rules .= 'box-shadow: 0 15px 40px rgba(0,0,0,0.3); ';
    }
    if (!empty($border) && $border !== 'transparent') {
        $acc_rules .= 'border: 1px solid ' . $border . '; ';
    }
    if (!empty($radius) && $radius !== '0') {
        $acc_rules .= 'border-radius: ' . $radius . '; ';
    }
    if (!empty($padding) && $padding !== '0') {
        $acc_rules .= 'padding: ' . $padding . '; ';
    }
    $acc_rules .= '} ';

    $acc_rules = str_replace(array("\r", "\n"), ' ', $acc_rules);
    $acc_rules = preg_replace('/\s+/', ' ', $acc_rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $acc_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) {
            $vbc_accumulated_css = array();
        }
        $vbc_accumulated_css[] = $acc_rules;
        $default_css = '';
    }

    $inner_content = !empty($atts['content']) ? $atts['content'] : $content;
    $inner_content = vbc_clean_inner_content($inner_content);
    return '<div class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '"' . $schema_attr . '>' . $default_css . $compiled_css . do_shortcode($inner_content) . '</div>';
}

function vbc_accordion_item_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'title' => '',
        'open' => 'false',
        'title_color' => '',
        'content_color' => '',
        'icon_color' => '#F5568F',
        'border_color' => '#edf2f7',
        'bg_color' => '#ffffff',
        'font_size' => '17px',
    ), $atts);

    $open_attr = ($atts['open'] === 'true') ? ' open' : '';
    
    $title_color = !empty($atts['title_color']) ? $atts['title_color'] : '#1e1e3f';
    $content_color = !empty($atts['content_color']) ? $atts['content_color'] : '#4a4a6a';
    $font_size = !empty($atts['font_size']) ? $atts['font_size'] : '17px';
    $icon_color = !empty($atts['icon_color']) ? $atts['icon_color'] : '#F5568F';
    $border_color = !empty($atts['border_color']) ? $atts['border_color'] : '#edf2f7';

    $html = '<details class="vbc-faq-item" style="border: 1.5px solid ' . esc_attr($border_color) . '; border-radius: 14px; margin-bottom: 14px; overflow: hidden; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: all 0.2s;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"' . $open_attr . '>';
    $html .= '<summary style="color: ' . esc_attr($title_color) . '; font-size: ' . esc_attr($font_size) . '; font-weight: 800; cursor: pointer; outline: none; list-style: none; display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; user-select: none;" itemprop="name">';
    $html .= '<span>' . esc_html($atts['title']) . '</span><span style="font-size: 22px; font-weight: 900; color: ' . esc_attr($icon_color) . '; line-height: 1; flex-shrink: 0; margin-left: 12px;">+</span>';
    $html .= '</summary>';
    $html .= '<div style="color: ' . esc_attr($content_color) . '; font-size: 15px; line-height: 1.75; padding: 0 24px 20px 24px; background: #ffffff;" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
    $html .= '<div itemprop="text">' . do_shortcode(vbc_clean_inner_content($content)) . '</div>';
    $html .= '</div>';
    $html .= '</details>';

    return $html;
}

/**
 * Shortcode [vbc_tabs] & [vbc_tab]: Hệ thống Tab chuyển đổi nội dung tương tác linh hoạt
 */


function vbc_inject_accordion_faq_styles() {
    ?>
    <style id="vbc-accordion-faq-custom-css">
    .accordion,
    .accordion.vbc-accordion-faq,
    .vbc-accordion {
        border: none;
    }
    .accordion .accordion-title,
    .accordion.vbc-accordion-faq .accordion-title,
    .vbc-accordion .accordion-title {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        text-align: left !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .accordion .accordion-title > span,
    .accordion .accordion-title span,
    .accordion.vbc-accordion-faq .accordion-title > span,
    .accordion.vbc-accordion-faq .accordion-title span,
    .vbc-accordion .accordion-title > span,
    .vbc-accordion .accordion-title span {
        order: 1 !important;
        flex: 1 1 auto !important;
        text-align: left !important;
        margin: 0 !important;
        display: block !important;
    }
    .accordion .accordion-title > .toggle,
    .accordion .accordion-title > button,
    .accordion .accordion-title .toggle,
    .accordion .accordion-title button.toggle,
    .accordion.vbc-accordion-faq .accordion-title > .toggle,
    .accordion.vbc-accordion-faq .accordion-title > button,
    .accordion.vbc-accordion-faq .accordion-title .toggle,
    .vbc-accordion .accordion-title > .toggle,
    .vbc-accordion .accordion-title > button,
    .vbc-accordion .accordion-title .toggle {
        order: 2 !important;
        position: static !important;
        float: none !important;
        left: auto !important;
        right: auto !important;
        top: auto !important;
        bottom: auto !important;
        margin: 0 0 0 16px !important;
        color: #64748b !important;
        font-size: 16px !important;
        transition: transform 0.25s ease, color 0.25s ease !important;
        background: transparent !important;
        border: none !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 24px !important;
        height: 24px !important;
        padding: 0 !important;
        cursor: pointer !important;
        transform: none !important;
    }
    .accordion .accordion-title.active > .toggle,
    .accordion .accordion-title.active > button,
    .accordion .accordion-title.active .toggle,
    .accordion .accordion-title.active button.toggle,
    .accordion.vbc-accordion-faq .accordion-title.active > .toggle,
    .accordion.vbc-accordion-faq .accordion-title.active > button,
    .accordion.vbc-accordion-faq .accordion-title.active .toggle,
    .vbc-accordion .accordion-title.active > .toggle,
    .vbc-accordion .accordion-title.active > button,
    .vbc-accordion .accordion-title.active .toggle {
        transform: rotate(180deg) !important;
        color: #2563eb !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'vbc_inject_accordion_faq_styles', 99);

