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

/**
 * Trình biên dịch CSS Responsive & Input ngắn gọn cho mọi phần tử VBC
 */
function vbc_compile_element_css(&$atts, $base_class = 'vbc-css') {
    $random_id = wp_generate_password(8, false);
    $unique_class = $base_class . '-' . $random_id;

    $styles_desktop = array();
    $styles_tablet = array();
    $styles_mobile = array();

    // Bảng ánh xạ thuộc tính shortcode sang CSS Property
    $property_map = array(
        // Kích thước & Khung (Dimensions & Sizing)
        'width'                  => 'width',
        'height'                 => 'height',
        'max_width'              => 'max-width',
        'min_width'              => 'min-width',
        'max_height'             => 'max-height',
        'min_height'             => 'min-height',
        'margin'                 => 'margin',
        'padding'                => 'padding',
        'display'                => 'display',
        'overflow'               => 'overflow',

        // Màu sắc & Nền (Colors & Backgrounds)
        'color'                  => 'color',
        'background_color'       => 'background-color',
        'bg_color'               => 'background-color',
        'background'             => 'background',
        'bg_gradient'            => 'background',
        'bg_image'               => 'background-image',

        // Kiểu chữ & Canh lề (Typography & Alignment)
        'font_size'              => 'font-size',
        'font_weight'            => 'font-weight',
        'line_height'            => 'line-height',
        'letter_spacing'         => 'letter-spacing',
        'text_align'             => 'text-align',
        'text_transform'         => 'text-transform',
        'text_decoration'        => 'text-decoration',

        // Flexbox & CSS Grid Layout
        'flex_direction'         => 'flex-direction',
        'justify_content'        => 'justify-content',
        'align_items'            => 'align-items',
        'flex_wrap'              => 'flex-wrap',
        'gap'                    => 'gap',
        'grid_template_columns'  => 'grid-template-columns',
        'grid_columns'           => 'grid-template-columns',
        'grid_gap'               => 'gap',

        // Viền & Đổ bóng (Borders, Radii & Shadows)
        'border'                 => 'border',
        'border_radius'          => 'border-radius',
        'border_color'           => 'border-color',
        'border_width'           => 'border-width',
        'border_style'           => 'border-style',
        'box_shadow'             => 'box-shadow',

        // Vị trí & Hiệu ứng (Positioning, Opacity & Effects)
        'position'               => 'position',
        'top'                    => 'top',
        'bottom'                 => 'bottom',
        'left'                   => 'left',
        'right'                  => 'right',
        'z_index'                => 'z-index',
        'opacity'                => 'opacity',
        'cursor'                 => 'cursor',
        'transition'             => 'transition',
        'transform'              => 'transform',
    );

    // Danh sách các thuộc tính dạng màu sắc cần tự động thêm tiền tố # nếu thiếu
    $color_props = array('color', 'background-color', 'border-color');

    // Hàm phụ trợ chuẩn hóa giá trị thuộc tính
    $format_val = function($css_prop, $raw_val) use ($color_props) {
        $val = trim($raw_val);
        if (in_array($css_prop, $color_props)) {
            // Nếu người dùng nhập mã hex trần không có # (ví dụ: fff, 2563eb, f0493e)
            if (preg_match('/^[0-9a-fA-F]{3,8}$/', $val)) {
                $val = '#' . $val;
            }
        } elseif ($css_prop === 'background-image' && !preg_match('/^url\(/i', $val) && !empty($val)) {
            $val = 'url(' . esc_url($val) . ')';
        }
        return $val;
    };

    foreach ($property_map as $attr_key => $css_prop) {
        // Desktop
        if (isset($atts[$attr_key]) && $atts[$attr_key] !== '') {
            $val = $format_val($css_prop, $atts[$attr_key]);
            $styles_desktop[] = $css_prop . ': ' . $val . ';';
        }
        // Tablet (__md) - Max Width 849px
        $md_key = $attr_key . '__md';
        if (isset($atts[$md_key]) && $atts[$md_key] !== '') {
            $val = $format_val($css_prop, $atts[$md_key]);
            $styles_tablet[] = $css_prop . ': ' . $val . ';';
        }
        // Mobile (__sm) - Max Width 549px
        $sm_key = $attr_key . '__sm';
        if (isset($atts[$sm_key]) && $atts[$sm_key] !== '') {
            $val = $format_val($css_prop, $atts[$sm_key]);
            $styles_mobile[] = $css_prop . ': ' . $val . ';';
        }
    }

    // Xử lý Google Font tự động
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
