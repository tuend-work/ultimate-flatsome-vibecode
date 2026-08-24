<?php
/**
 * Ultimate Flatsome VibeCode - CSS Engine & Responsive Compiler
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_should_inline_css() {
    // If inside UX Builder editor page or iframe, always inline CSS
    if (isset($_GET['uxb_iframe']) || isset($_GET['ux-builder'])) {
        return true;
    }
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'uxbuilder') {
        return true;
    }
    if (function_exists('ux_builder_is_active') && ux_builder_is_active()) {
        return true;
    }
    // If doing AJAX or REST API (dynamic preview rendering)
    if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return true;
    }
    // Fallback if footer has already run
    if (did_action('wp_footer') || did_action('admin_footer')) {
        return true;
    }
    return false;
}

add_action('wp_footer', 'vbc_print_accumulated_styles', 10);
add_action('admin_footer', 'vbc_print_accumulated_styles', 10);

function vbc_print_accumulated_styles() {
    global $vbc_accumulated_css, $vbc_accumulated_fonts;
    
    // In ra Google Fonts nếu có
    if (!empty($vbc_accumulated_fonts) && is_array($vbc_accumulated_fonts)) {
        $font_families = array_keys($vbc_accumulated_fonts);
        $family_query = '';
        foreach ($font_families as $family) {
            $family_query .= 'family=' . str_replace(' ', '+', trim($family)) . ':wght@300;400;500;600;700;800;900&';
        }
        $font_url = "https://fonts.googleapis.com/css2?" . $family_query . "display=swap";
        echo '<link rel="stylesheet" id="vbc-accumulated-fonts" href="' . esc_url($font_url) . '" type="text/css" media="all" />' . "\n";
    }
    
    // In ra Style CSS chung
    if (!empty($vbc_accumulated_css) && is_array($vbc_accumulated_css)) {
        $all_css = implode(' ', $vbc_accumulated_css);
        $all_css = str_replace(array("\r", "\n"), ' ', $all_css);
        $all_css = preg_replace('/\s+/', ' ', $all_css);
        echo '<style id="vbc-accumulated-elements-css">' . $all_css . '</style>' . "\n";
    }
}



function vbc_compile_element_css(&$atts, $base_class = 'vbc-comp') {
    $random_id = wp_generate_password(8, false);
    $unique_class = $base_class . '-' . $random_id;

    $styles_desktop = array();
    $styles_tablet = array();
    $styles_mobile = array();

    $responsive_props = array(
        'width' => 'width',
        'height' => 'height',
        'margin' => 'margin',
        'padding' => 'padding',
        'font_size' => 'font-size',
        'text_align' => 'text-align',
        'display' => 'display',
        'background_color' => 'background-color',
    );

    foreach ($responsive_props as $attr_key => $css_prop) {
        if (isset($atts[$attr_key]) && $atts[$attr_key] !== '') {
            $val = $atts[$attr_key];
            if ($css_prop === 'background-color' && preg_match('/^[0-9a-fA-F]{3,8}$/', $val)) {
                $val = '#' . $val;
            }
            $styles_desktop[] = $css_prop . ': ' . $val . ';';
        }
        $md_key = $attr_key . '__md';
        if (isset($atts[$md_key]) && $atts[$md_key] !== '') {
            $val = $atts[$md_key];
            if ($css_prop === 'background-color' && preg_match('/^[0-9a-fA-F]{3,8}$/', $val)) {
                $val = '#' . $val;
            }
            $styles_tablet[] = $css_prop . ': ' . $val . ';';
        }
        $sm_key = $attr_key . '__sm';
        if (isset($atts[$sm_key]) && $atts[$sm_key] !== '') {
            $val = $atts[$sm_key];
            if ($css_prop === 'background-color' && preg_match('/^[0-9a-fA-F]{3,8}$/', $val)) {
                $val = '#' . $val;
            }
            $styles_mobile[] = $css_prop . ': ' . $val . ';';
        }
    }

    if (!empty($atts['font_family'])) {
        $font_family = trim($atts['font_family']);
        if (vbc_should_inline_css()) {
            $font_slug = 'vbc-font-' . sanitize_title($font_family);
            $font_url = "https://fonts.googleapis.com/css2?family=" . str_replace(' ', '+', $font_family) . ":wght@300;400;500;600;700;800;900&display=swap";
            wp_enqueue_style($font_slug, $font_url, array(), null);
        } else {
            global $vbc_accumulated_fonts;
            if (!is_array($vbc_accumulated_fonts)) {
                $vbc_accumulated_fonts = array();
            }
            $vbc_accumulated_fonts[$font_family] = true;
        }

        $styles_desktop[] = "font-family: '" . esc_attr($font_family) . "', sans-serif;";
    }

    $compiled_css = '';
    if (!empty($styles_desktop) || !empty($styles_tablet) || !empty($styles_mobile) || !empty($atts['custom_css'])) {
        $css_rules = '';
        if (!empty($styles_desktop)) {
            $css_rules .= '.' . $unique_class . ' { ' . implode(' ', $styles_desktop) . ' }' . "\n";
        }
        if (!empty($styles_tablet)) {
            $css_rules .= '@media (max-width: 849px) { .' . $unique_class . ' { ' . implode(' ', $styles_tablet) . ' } }' . "\n";
        }
        if (!empty($styles_mobile)) {
            $css_rules .= '@media (max-width: 549px) { .' . $unique_class . ' { ' . implode(' ', $styles_mobile) . ' } }' . "\n";
        }
        if (!empty($atts['custom_css'])) {
            $raw_css = trim($atts['custom_css']);
            if (strpos($raw_css, '{') === false) {
                $css_rules .= '.' . $unique_class . ' { ' . $raw_css . ' }' . "\n";
            } else {
                $css_rules .= str_replace('selector', '.' . $unique_class, $raw_css) . "\n";
            }
        }
        
        // Compress CSS to prevent wpautop from adding <br> tags
        $css_rules = str_replace(array("\r", "\n"), ' ', $css_rules);
        $css_rules = preg_replace('/\s+/', ' ', $css_rules);
        
        if (vbc_should_inline_css()) {
            $compiled_css = '<style>' . $css_rules . '</style>';
        } else {
            global $vbc_accumulated_css;
            if (!is_array($vbc_accumulated_css)) {
                $vbc_accumulated_css = array();
            }
            $vbc_accumulated_css[] = $css_rules;
            $compiled_css = '';
        }
    }

    return array(
        'class' => $unique_class,
        'css' => $compiled_css
    );
}
