<?php
/**
 * Plugin Name: Ultimate Flatsome VibeCode Elements
 * Plugin URI: https://github.com/tuend-work/ultimate-flatsome-vibecode
 * Description: Thêm các phần tử HTML cơ bản tích hợp sâu với Flatsome UX Builder, hỗ trợ responsive hoàn hảo, chèn dữ liệu động (Post Meta, ACF) và chỉnh sửa CSS nâng cao.
 * Version: 1.0.4
 * Author: Antigravity AI
 * Author URI: https://github.com/tuend-work
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * 1. ĐĂNG KÝ CẤU HÌNH PHẦN TỬ CHO UX BUILDER
 */
add_action('ux_builder_setup', 'vbc_register_ux_builder_elements');

function vbc_get_common_options($tag_type) {
    $options = array(
        // Layout & Styling Options Group
        'styling_group' => array(
            'type' => 'group',
            'heading' => 'Định dạng & CSS',
            'options' => array(
                'custom_class' => array(
                    'type' => 'textfield',
                    'heading' => 'CSS Class',
                    'default' => '',
                ),
                'custom_css' => array(
                    'type' => 'textarea',
                    'heading' => 'Custom CSS (Dùng "selector")',
                    'default' => '',
                    'description' => 'Ví dụ: selector { background: #eee; } selector:hover { opacity: 0.8; }',
                ),
                'custom_attributes' => array(
                    'type' => 'textfield',
                    'heading' => 'Thuộc tính HTML khác',
                    'default' => '',
                    'description' => 'Ví dụ: data-aos="fade-up" id="my-el"',
                ),
                'width' => array(
                    'type' => 'textfield',
                    'heading' => 'Độ rộng (Width)',
                    'responsive' => true,
                    'default' => '',
                ),
                'height' => array(
                    'type' => 'textfield',
                    'heading' => 'Chiều cao (Height)',
                    'responsive' => true,
                    'default' => '',
                ),
                'margin' => array(
                    'type' => 'textfield',
                    'heading' => 'Margin',
                    'responsive' => true,
                    'default' => '',
                    'description' => 'Ví dụ: 10px 0 20px 0',
                ),
                'padding' => array(
                    'type' => 'textfield',
                    'heading' => 'Padding',
                    'responsive' => true,
                    'default' => '',
                    'description' => 'Ví dụ: 15px',
                ),
                'font_size' => array(
                    'type' => 'textfield',
                    'heading' => 'Cỡ chữ (Font Size)',
                    'responsive' => true,
                    'default' => '',
                ),
                'text_align' => array(
                    'type' => 'select',
                    'heading' => 'Căn lề (Text Align)',
                    'responsive' => true,
                    'default' => '',
                    'options' => array(
                        '' => 'Mặc định',
                        'left' => 'Trái',
                        'center' => 'Giữa',
                        'right' => 'Phải',
                        'justify' => 'Đều 2 bên',
                    ),
                ),
                'display' => array(
                    'type' => 'select',
                    'heading' => 'Hiển thị (Display)',
                    'responsive' => true,
                    'default' => '',
                    'options' => array(
                        '' => 'Mặc định',
                        'block' => 'block',
                        'inline-block' => 'inline-block',
                        'inline' => 'inline',
                        'flex' => 'flex',
                        'grid' => 'grid',
                        'none' => 'none',
                    ),
                ),
                'background_color' => array(
                    'type' => 'colorpicker',
                    'heading' => 'Màu nền',
                    'responsive' => true,
                    'default' => '',
                ),
            ),
        ),
    );

    if ($tag_type === 'container') {
        // Content Options Group
        $options['content_group'] = array(
            'type' => 'group',
            'heading' => 'Nội dung & Dữ liệu',
            'options' => array(
                'content_source' => array(
                    'type' => 'select',
                    'heading' => 'Nguồn nội dung',
                    'default' => 'default',
                    'options' => array(
                        'default' => 'Mặc định (Dùng các phần tử con)',
                        'manual' => 'Nhập thủ công',
                        'post_meta' => 'WP Post Meta',
                        'acf' => 'ACF Field',
                    ),
                ),
                'content_manual' => array(
                    'type' => 'textarea',
                    'heading' => 'Văn bản thủ công',
                    'default' => '',
                    'conditions' => 'content_source === "manual"',
                ),
                'meta_key' => array(
                    'type' => 'textfield',
                    'heading' => 'Post Meta Key',
                    'default' => '',
                    'conditions' => 'content_source === "post_meta"',
                ),
                'acf_key' => array(
                    'type' => 'textfield',
                    'heading' => 'ACF Field Key',
                    'default' => '',
                    'conditions' => 'content_source === "acf"',
                ),
                'content_position' => array(
                    'type' => 'select',
                    'heading' => 'Vị trí chèn',
                    'default' => 'replace',
                    'options' => array(
                        'replace' => 'Thay thế hoàn toàn phần tử con',
                        'before' => 'Chèn trước phần tử con',
                        'after' => 'Chèn sau phần tử con',
                    ),
                    'conditions' => 'content_source !== "default"',
                ),
            ),
        );
    }

    return $options;
}

function vbc_register_ux_builder_elements() {
    if (!function_exists('add_ux_builder_shortcode')) {
        return;
    }

    $tags = array(
        'div' => array('name' => 'VBC Div', 'type' => 'container'),
        'box' => array('name' => 'VBC Box (Div)', 'type' => 'container'),
        'block' => array('name' => 'VBC Block (Div)', 'type' => 'container'),
        'container' => array('name' => 'VBC Container (Div)', 'type' => 'container'),
        'p' => array('name' => 'VBC Paragraph', 'type' => 'container'),
        'i' => array('name' => 'VBC Italic', 'type' => 'container'),
        'span' => array('name' => 'VBC Span', 'type' => 'container'),
        'a' => array('name' => 'VBC Link', 'type' => 'container'),
        'h1' => array('name' => 'VBC H1', 'type' => 'container'),
        'h2' => array('name' => 'VBC H2', 'type' => 'container'),
        'h3' => array('name' => 'VBC H3', 'type' => 'container'),
        'h4' => array('name' => 'VBC H4', 'type' => 'container'),
        'h5' => array('name' => 'VBC H5', 'type' => 'container'),
        'h6' => array('name' => 'VBC H6', 'type' => 'container'),
        'li' => array('name' => 'VBC List Item', 'type' => 'container'),
        'ul' => array('name' => 'VBC Unordered List', 'type' => 'container'),
        'ol' => array('name' => 'VBC Ordered List', 'type' => 'container'),
        'table' => array('name' => 'VBC Table', 'type' => 'container'),
        'tr' => array('name' => 'VBC Table Row', 'type' => 'container'),
        'td' => array('name' => 'VBC Table Cell', 'type' => 'container'),
        'th' => array('name' => 'VBC Table Header', 'type' => 'container'),
        'b' => array('name' => 'VBC Bold', 'type' => 'container'),
        'strong' => array('name' => 'VBC Strong', 'type' => 'container'),
        'em' => array('name' => 'VBC Emphasis', 'type' => 'container'),
        'u' => array('name' => 'VBC Underline', 'type' => 'container'),
        'hr' => array('name' => 'VBC Horizontal Rule', 'type' => 'void'),
        'br' => array('name' => 'VBC Line Break', 'type' => 'void'),
        'img' => array('name' => 'VBC Image', 'type' => 'void'),
    );

    foreach ($tags as $tag => $config) {
        $options = vbc_get_common_options($config['type']);

        // Merge tag-specific options
        if ($tag === 'a') {
            $options['link_group'] = array(
                'type' => 'group',
                'heading' => 'Liên kết (Link Settings)',
                'options' => array(
                    'link_source' => array(
                        'type' => 'select',
                        'heading' => 'Nguồn URL',
                        'default' => 'manual',
                        'options' => array(
                            'manual' => 'Nhập thủ công',
                            'post_meta' => 'WP Post Meta',
                            'acf' => 'ACF Field',
                        ),
                    ),
                    'link_url' => array(
                        'type' => 'textfield',
                        'heading' => 'URL Liên kết',
                        'default' => '',
                        'conditions' => 'link_source === "manual"',
                    ),
                    'link_meta_key' => array(
                        'type' => 'textfield',
                        'heading' => 'Post Meta Key URL',
                        'default' => '',
                        'conditions' => 'link_source === "post_meta"',
                    ),
                    'link_acf_key' => array(
                        'type' => 'textfield',
                        'heading' => 'ACF Key URL',
                        'default' => '',
                        'conditions' => 'link_source === "acf"',
                    ),
                    'link_target' => array(
                        'type' => 'select',
                        'heading' => 'Mở liên kết',
                        'default' => '_self',
                        'options' => array(
                            '_self' => 'Cửa sổ hiện tại (_self)',
                            '_blank' => 'Cửa sổ mới (_blank)',
                        ),
                    ),
                    'link_rel' => array(
                        'type' => 'textfield',
                        'heading' => 'Rel Attribute',
                        'default' => '',
                    ),
                ),
            );
        } elseif ($tag === 'img') {
            $options['img_group'] = array(
                'type' => 'group',
                'heading' => 'Hình ảnh (Image Settings)',
                'options' => array(
                    'img_source' => array(
                        'type' => 'select',
                        'heading' => 'Nguồn Ảnh',
                        'default' => 'default',
                        'options' => array(
                            'default' => 'Thư viện (Media Library)',
                            'manual' => 'URL trực tiếp',
                            'post_meta' => 'WP Post Meta (ID/URL)',
                            'acf' => 'ACF Field (ID/URL)',
                        ),
                    ),
                    'img_attachment' => array(
                        'type' => 'image',
                        'heading' => 'Chọn ảnh',
                        'default' => '',
                        'conditions' => 'img_source === "default"',
                    ),
                    'img_url' => array(
                        'type' => 'textfield',
                        'heading' => 'URL ảnh',
                        'default' => '',
                        'conditions' => 'img_source === "manual"',
                    ),
                    'img_meta_key' => array(
                        'type' => 'textfield',
                        'heading' => 'Post Meta Key',
                        'default' => '',
                        'conditions' => 'img_source === "post_meta"',
                    ),
                    'img_acf_key' => array(
                        'type' => 'textfield',
                        'heading' => 'ACF Field Key',
                        'default' => '',
                        'conditions' => 'img_source === "acf"',
                    ),
                    'alt' => array(
                        'type' => 'textfield',
                        'heading' => 'Alt text',
                        'default' => '',
                    ),
                ),
            );
        } elseif ($tag === 'td' || $tag === 'th') {
            $options['table_cell_group'] = array(
                'type' => 'group',
                'heading' => 'Cấu hình Cell',
                'options' => array(
                    'colspan' => array(
                        'type' => 'textfield',
                        'heading' => 'Colspan',
                        'default' => '',
                    ),
                    'rowspan' => array(
                        'type' => 'textfield',
                        'heading' => 'Rowspan',
                        'default' => '',
                    ),
                ),
            );
        } elseif ($tag === 'ol') {
            $options['list_group'] = array(
                'type' => 'group',
                'heading' => 'Cấu hình Danh sách',
                'options' => array(
                    'ol_type' => array(
                        'type' => 'select',
                        'heading' => 'Kiểu đánh số',
                        'default' => '1',
                        'options' => array(
                            '1' => '1, 2, 3...',
                            'a' => 'a, b, c...',
                            'A' => 'A, B, C...',
                            'i' => 'i, ii, iii...',
                            'I' => 'I, II, III...',
                        ),
                    ),
                    'ol_start' => array(
                        'type' => 'textfield',
                        'heading' => 'Bắt đầu từ',
                        'default' => '',
                    ),
                ),
            );
        }

        $args = array(
            'name' => $config['name'],
            'category' => 'VibeCode HTML',
            'options' => $options,
        );

        if ($config['type'] === 'container') {
            $args['type'] = 'container';
        }

        add_ux_builder_shortcode('vbc_' . $tag, $args);
    }
}


/**
 * 2. ĐĂNG KÝ SHORTCODE HANDLERS VÀ BỘ RENDER CSS RESPONSIVE
 */
function vbc_register_shortcodes() {
    $tags = array(
        'div', 'box', 'block', 'container', 'p', 'i', 'span', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'li', 'ul', 'ol', 'table', 'tr', 'td', 'th', 'b', 'strong', 'em', 'u',
        'hr', 'br', 'img'
    );

    foreach ($tags as $tag) {
        add_shortcode('vbc_' . $tag, 'vbc_shortcode_renderer');
    }
}
add_action('init', 'vbc_register_shortcodes');

function vbc_shortcode_renderer($atts, $content = null, $tag = '') {
    $html_tag = str_replace('vbc_', '', $tag);

    // Map các alias của div về thẻ div thực tế để tránh trùng tên khi lồng nhau
    if (in_array($html_tag, array('box', 'block', 'container'))) {
        $html_tag = 'div';
    }

    $atts = shortcode_atts(array(
        // Common Styling Options
        'custom_class' => '',
        'custom_css' => '',
        'custom_attributes' => '',
        
        // Responsive Options (được tự động ánh xạ bằng __md và __sm)
        'width' => '', 'width__md' => '', 'width__sm' => '',
        'height' => '', 'height__md' => '', 'height__sm' => '',
        'margin' => '', 'margin__md' => '', 'margin__sm' => '',
        'padding' => '', 'padding__md' => '', 'padding__sm' => '',
        'font_size' => '', 'font_size__md' => '', 'font_size__sm' => '',
        'text_align' => '', 'text_align__md' => '', 'text_align__sm' => '',
        'display' => '', 'display__md' => '', 'display__sm' => '',
        'background_color' => '', 'background_color__md' => '', 'background_color__sm' => '',

        // Container-specific Options
        'content_source' => 'default',
        'content_manual' => '',
        'meta_key' => '',
        'acf_key' => '',
        'content_position' => 'replace',

        // a-specific
        'link_source' => 'manual',
        'link_url' => '',
        'link_meta_key' => '',
        'link_acf_key' => '',
        'link_target' => '_self',
        'link_rel' => '',

        // img-specific
        'img_source' => 'default',
        'img_attachment' => '',
        'img_url' => '',
        'img_meta_key' => '',
        'img_acf_key' => '',
        'alt' => '',

        // Cell-specific
        'colspan' => '',
        'rowspan' => '',

        // ol-specific
        'ol_type' => '1',
        'ol_start' => '',
    ), $atts, $tag);

    // Biên dịch Responsive CSS
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
        // Desktop
        if (isset($atts[$attr_key]) && $atts[$attr_key] !== '') {
            $val = $atts[$attr_key];
            if ($css_prop === 'background-color' && strpos($val, '#') !== 0 && !preg_match('/^(rgb|hsl)/', $val)) {
                // Colorpicker của Flatsome đôi khi trả về mã hexa trần
                $val = '#' . $val;
            }
            $styles_desktop[] = $css_prop . ': ' . $val . ';';
        }
        // Tablet (__md)
        $md_key = $attr_key . '__md';
        if (isset($atts[$md_key]) && $atts[$md_key] !== '') {
            $val = $atts[$md_key];
            if ($css_prop === 'background-color' && strpos($val, '#') !== 0 && !preg_match('/^(rgb|hsl)/', $val)) {
                $val = '#' . $val;
            }
            $styles_tablet[] = $css_prop . ': ' . $val . ';';
        }
        // Mobile (__sm)
        $sm_key = $attr_key . '__sm';
        if (isset($atts[$sm_key]) && $atts[$sm_key] !== '') {
            $val = $atts[$sm_key];
            if ($css_prop === 'background-color' && strpos($val, '#') !== 0 && !preg_match('/^(rgb|hsl)/', $val)) {
                $val = '#' . $val;
            }
            $styles_mobile[] = $css_prop . ': ' . $val . ';';
        }
    }

    $compiled_css = '';
    $class_attr = $atts['custom_class'];

    if (!empty($styles_desktop) || !empty($styles_tablet) || !empty($styles_mobile) || !empty($atts['custom_css'])) {
        $random_id = wp_generate_password(8, false);
        $unique_class = 'vbc-css-' . $random_id;
        
        $css_rules = '';
        
        // Desktop rules
        if (!empty($styles_desktop)) {
            $css_rules .= '.' . $unique_class . ' { ' . implode(' ', $styles_desktop) . ' }' . "\n";
        }
        
        // Tablet rules (max-width: 849px)
        if (!empty($styles_tablet)) {
            $css_rules .= '@media (max-width: 849px) { .' . $unique_class . ' { ' . implode(' ', $styles_tablet) . ' } }' . "\n";
        }
        
        // Mobile rules (max-width: 549px)
        if (!empty($styles_mobile)) {
            $css_rules .= '@media (max-width: 549px) { .' . $unique_class . ' { ' . implode(' ', $styles_mobile) . ' } }' . "\n";
        }
        
        // Custom CSS block
        if (!empty($atts['custom_css'])) {
            $raw_css = trim($atts['custom_css']);
            if (strpos($raw_css, '{') === false) {
                $css_rules .= '.' . $unique_class . ' { ' . $raw_css . ' }' . "\n";
            } else {
                $css_rules .= str_replace('selector', '.' . $unique_class, $raw_css) . "\n";
            }
        }
        
        $compiled_css = '<style>' . $css_rules . '</style>';
        $class_attr = trim($class_attr . ' ' . $unique_class);
    }

    $class_attr_str = !empty($class_attr) ? ' class="' . esc_attr($class_attr) . '"' : '';
    $custom_attrs = !empty($atts['custom_attributes']) ? ' ' . trim($atts['custom_attributes']) : '';

    // 3. Render các thẻ self-closing (void)
    $void_tags = array('hr', 'br', 'img');
    if (in_array($html_tag, $void_tags)) {
        if ($html_tag === 'br') {
            return '<br>';
        }
        if ($html_tag === 'hr') {
            return $compiled_css . '<hr' . $class_attr_str . $custom_attrs . '>';
        }
        if ($html_tag === 'img') {
            $img_url = '';
            $img_id = 0;
            if ($atts['img_source'] === 'default') {
                $img_id = intval($atts['img_attachment']);
                if ($img_id > 0) {
                    $img_url = wp_get_attachment_image_url($img_id, 'full');
                }
            } elseif ($atts['img_source'] === 'manual') {
                $img_url = $atts['img_url'];
            } elseif ($atts['img_source'] === 'post_meta') {
                $meta_key = $atts['img_meta_key'];
                if (!empty($meta_key)) {
                    $val = get_post_meta(get_the_ID(), $meta_key, true);
                    if (is_numeric($val)) {
                        $img_id = intval($val);
                        $img_url = wp_get_attachment_image_url($img_id, 'full');
                    } else {
                        $img_url = $val;
                    }
                }
            } elseif ($atts['img_source'] === 'acf') {
                $acf_key = $atts['img_acf_key'];
                if (!empty($acf_key)) {
                    if (function_exists('get_field')) {
                        $val = get_field($acf_key);
                        if (is_array($val) && isset($val['url'])) {
                            $img_url = $val['url'];
                            if (isset($val['id'])) {
                                $img_id = intval($val['id']);
                            }
                        } elseif (is_numeric($val)) {
                            $img_id = intval($val);
                            $img_url = wp_get_attachment_image_url($img_id, 'full');
                        } else {
                            $img_url = $val;
                        }
                    } else {
                        $val = get_post_meta(get_the_ID(), $acf_key, true);
                        if (is_numeric($val)) {
                            $img_id = intval($val);
                            $img_url = wp_get_attachment_image_url($img_id, 'full');
                        } else {
                            $img_url = $val;
                        }
                    }
                }
            }
            
            $img_url = esc_url($img_url);
            $alt = esc_attr($atts['alt']);
            if (empty($alt) && $img_id > 0) {
                $alt = esc_attr(get_post_meta($img_id, '_wp_attachment_image_alt', true));
            }
            
            if (empty($img_url)) {
                if (is_user_logged_in() && (is_admin() || is_customize_preview() || isset($_GET['uxb_iframe']))) {
                    return $compiled_css . '<div style="background:#f3f3f3;padding:15px;text-align:center;border:1px dashed #ccc;font-size:11px;"' . $class_attr_str . $custom_attrs . '>VBC Image (No Source Selected)</div>';
                }
                return '';
            }

            return $compiled_css . '<img src="' . $img_url . '" alt="' . $alt . '"' . $class_attr_str . $custom_attrs . '>';
        }
    }

    // 4. Render các thẻ Container
    // Xử lý dữ liệu động
    $dynamic_content = '';
    if ($atts['content_source'] === 'manual') {
        $dynamic_content = $atts['content_manual'];
    } elseif ($atts['content_source'] === 'post_meta') {
        $meta_key = $atts['meta_key'];
        if (!empty($meta_key)) {
            $dynamic_content = get_post_meta(get_the_ID(), $meta_key, true);
        }
    } elseif ($atts['content_source'] === 'acf') {
        $acf_key = $atts['acf_key'];
        if (!empty($acf_key)) {
            if (function_exists('get_field')) {
                $dynamic_content = get_field($acf_key);
            } else {
                $dynamic_content = get_post_meta(get_the_ID(), $acf_key, true);
            }
        }
    }

    if (is_array($dynamic_content) || is_object($dynamic_content)) {
        $dynamic_content = json_encode($dynamic_content);
    } else {
        $dynamic_content = strval($dynamic_content);
    }

    $children = do_shortcode($content);

    if ($atts['content_source'] === 'default') {
        $final_content = $children;
    } else {
        if ($atts['content_position'] === 'replace') {
            $final_content = $dynamic_content;
        } elseif ($atts['content_position'] === 'before') {
            $final_content = $dynamic_content . $children;
        } else {
            $final_content = $children . $dynamic_content;
        }
    }

    // Xử lý các thuộc tính HTML riêng biệt
    $tag_attrs = '';
    if ($html_tag === 'a') {
        $link_url = '';
        if ($atts['link_source'] === 'manual') {
            $link_url = $atts['link_url'];
        } elseif ($atts['link_source'] === 'post_meta') {
            $meta_key = $atts['link_meta_key'];
            if (!empty($meta_key)) {
                $link_url = get_post_meta(get_the_ID(), $meta_key, true);
            }
        } elseif ($atts['link_source'] === 'acf') {
            $acf_key = $atts['link_acf_key'];
            if (!empty($acf_key)) {
                if (function_exists('get_field')) {
                    $acf_value = get_field($acf_key);
                    if (is_array($acf_value) && isset($acf_value['url'])) {
                        $link_url = $acf_value['url'];
                    } else {
                        $link_url = $acf_value;
                    }
                } else {
                    $link_url = get_post_meta(get_the_ID(), $acf_key, true);
                }
            }
        }
        $link_url = esc_url($link_url);
        if (!empty($link_url)) {
            $tag_attrs .= ' href="' . $link_url . '"';
        }
        if (!empty($atts['link_target'])) {
            $tag_attrs .= ' target="' . esc_attr($atts['link_target']) . '"';
        }
        if (!empty($atts['link_rel'])) {
            $tag_attrs .= ' rel="' . esc_attr($atts['link_rel']) . '"';
        }
    } elseif ($html_tag === 'td' || $html_tag === 'th') {
        $colspan = intval($atts['colspan']);
        $rowspan = intval($atts['rowspan']);
        if ($colspan > 0) {
            $tag_attrs .= ' colspan="' . $colspan . '"';
        }
        if ($rowspan > 0) {
            $tag_attrs .= ' rowspan="' . $rowspan . '"';
        }
    } elseif ($html_tag === 'ol') {
        if (!empty($atts['ol_type'])) {
            $tag_attrs .= ' type="' . esc_attr($atts['ol_type']) . '"';
        }
        $ol_start = intval($atts['ol_start']);
        if ($ol_start > 0) {
            $tag_attrs .= ' start="' . $ol_start . '"';
        }
    }

    return $compiled_css . '<' . $html_tag . $class_attr_str . $tag_attrs . $custom_attrs . '>' . $final_content . '</' . $html_tag . '>';
}


/**
 * 2.5 VIBECODE ADVANCED COMPONENT SHORTCODES
 */
add_action('init', 'vbc_register_component_shortcodes');

function vbc_register_component_shortcodes() {
    add_shortcode('vbc_card', 'vbc_card_shortcode');
    add_shortcode('vbc_testimonial', 'vbc_testimonial_shortcode');
    add_shortcode('vbc_accordion', 'vbc_accordion_shortcode');
    add_shortcode('vbc_accordion_item', 'vbc_accordion_item_shortcode');
    add_shortcode('vbc_button', 'vbc_button_shortcode');
}

function vbc_card_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'variant' => 'glass',
        'custom_class' => '',
        'custom_css' => '',
        'padding' => '35px 30px',
        'border_radius' => '20px',
        'border_color' => 'rgba(255,255,255,0.08)',
        'glow_color' => 'rgba(239, 68, 68, 0.2)',
    ), $atts);

    $random_id = wp_generate_password(8, false);
    $unique_class = 'vbc-card-' . $random_id;

    $css = '.' . $unique_class . ' { ';
    $css .= 'background: rgba(255, 255, 255, 0.03); ';
    $css .= 'backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); ';
    $css .= 'border: 1px solid ' . esc_attr($atts['border_color']) . '; ';
    $css .= 'border-radius: ' . esc_attr($atts['border_radius']) . '; ';
    $css .= 'padding: ' . esc_attr($atts['padding']) . '; ';
    $css .= 'box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); ';
    $css .= 'transition: all 0.3s ease; ';
    $css .= '} ';
    $css .= '.' . $unique_class . ':hover { ';
    $css .= 'transform: translateY(-8px); ';
    $css .= 'border-color: ' . esc_attr($atts['glow_color']) . '; ';
    $css .= 'box-shadow: 0 20px 40px ' . esc_attr($atts['glow_color']) . '; ';
    $css .= '} ';

    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
        } else {
            $css .= str_replace('selector', '.' . $unique_class, $raw_css);
        }
    }

    $class_str = trim('vbc-component-card ' . $unique_class . ' ' . $atts['custom_class']);

    return '<style>' . $css . '</style><div class="' . esc_attr($class_str) . '">' . do_shortcode($content) . '</div>';
}

function vbc_testimonial_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'name' => 'Khách Hàng',
        'company' => '',
        'stars' => '5',
        'avatar_url' => '',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    $stars_count = intval($atts['stars']);
    $stars_html = str_repeat('★', $stars_count);

    $random_id = wp_generate_password(8, false);
    $unique_class = 'vbc-testi-' . $random_id;

    $css = '.' . $unique_class . ' { background: rgba(255,255,255,0.03); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.08); padding: 35px 28px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); transition: all 0.3s; } ';
    $css .= '.' . $unique_class . ':hover { transform: translateY(-5px); border-color: rgba(239,68,68,0.3); box-shadow: 0 20px 40px rgba(239,68,68,0.15); } ';
    $css .= '.' . $unique_class . ' .vbc-stars { color: #fbbf24; font-size: 18px; margin-bottom: 15px; letter-spacing: 2px; } ';
    $css .= '.' . $unique_class . ' .vbc-quote { color: #cbd5e1; font-size: 15px; line-height: 1.7; margin-bottom: 20px; font-style: italic; } ';
    $css .= '.' . $unique_class . ' .vbc-author { color: #ffffff; font-weight: 700; font-size: 16px; margin-bottom: 3px; } ';
    $css .= '.' . $unique_class . ' .vbc-company { color: #94a3b8; font-size: 13px; } ';

    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
        } else {
            $css .= str_replace('selector', '.' . $unique_class, $raw_css);
        }
    }

    $avatar_html = '';
    if (!empty($atts['avatar_url'])) {
        $avatar_html = '<img src="' . esc_url($atts['avatar_url']) . '" style="width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; border: 2px solid rgba(239,68,68,0.4); object-fit: cover;">';
    }

    $meta_html = '<div style="display: flex; align-items: center;">' . $avatar_html . '<div><div class="vbc-author">' . esc_html($atts['name']) . '</div><div class="vbc-company">' . esc_html($atts['company']) . '</div></div></div>';

    return '<style>' . $css . '</style><div class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '"><div class="vbc-stars">' . $stars_html . '</div><div class="vbc-quote">"' . do_shortcode($content) . '"</div>' . $meta_html . '</div>';
}

function vbc_accordion_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'faq_schema' => 'true',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    $schema_attr = ($atts['faq_schema'] === 'true') ? ' itemscope itemtype="https://schema.org/FAQPage"' : '';
    
    $random_id = wp_generate_password(8, false);
    $unique_class = 'vbc-acc-' . $random_id;

    $css = '.' . $unique_class . ' { background: rgba(255,255,255,0.02); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 35px 45px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); } ';

    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
        } else {
            $css .= str_replace('selector', '.' . $unique_class, $raw_css);
        }
    }

    return '<style>' . $css . '</style><div class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '"' . $schema_attr . '>' . do_shortcode($content) . '</div>';
}

function vbc_accordion_item_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'title' => '',
        'open' => 'false',
    ), $atts);

    $open_attr = ($atts['open'] === 'true') ? ' open' : '';

    $html = '<details class="vbc-faq-item" style="border-bottom: 1px solid rgba(255,255,255,0.08); padding: 15px 0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"' . $open_attr . '>';
    $html .= '<summary style="color: #ffffff; font-size: 18px; font-weight: 700; cursor: pointer; outline: none; list-style: none; display: flex; justify-content: space-between; align-items: center;" itemprop="name">';
    $html .= '<span>' . esc_html($atts['title']) . '</span><span style="font-size: 20px; color: #f87171;">+</span>';
    $html .= '</summary>';
    $html .= '<div style="color: #cbd5e1; font-size: 15px; line-height: 1.7; margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
    $html .= '<div itemprop="text">' . do_shortcode($content) . '</div>';
    $html .= '</div>';
    $html .= '</details>';

    return $html;
}

function vbc_button_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'text' => 'Click Here',
        'url' => '#',
        'target' => '_self',
        'variant' => 'danger',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    $random_id = wp_generate_password(8, false);
    $unique_class = 'vbc-btn-' . $random_id;

    $css = '.' . $unique_class . ' { display: inline-block; padding: 16px 38px; border-radius: 30px; font-weight: 800; font-size: 15px; text-decoration: none; text-align: center; transition: all 0.3s ease; letter-spacing: 0.5px; } ';
    
    if ($atts['variant'] === 'danger' || $atts['variant'] === 'primary') {
        $css .= '.' . $unique_class . ' { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; box-shadow: 0 4px 25px rgba(239, 68, 68, 0.5); } ';
        $css .= '.' . $unique_class . ':hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(239, 68, 68, 0.7); color: #ffffff; } ';
    } elseif ($atts['variant'] === 'glass') {
        $css .= '.' . $unique_class . ' { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; backdrop-filter: blur(8px); } ';
        $css .= '.' . $unique_class . ':hover { background: rgba(255, 255, 255, 0.15); border-color: #ffffff; transform: translateY(-3px); color: #ffffff; } ';
    }

    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
        } else {
            $css .= str_replace('selector', '.' . $unique_class, $raw_css);
        }
    }

    $btn_text = !empty($atts['text']) ? esc_html($atts['text']) : do_shortcode($content);

    return '<style>' . $css . '</style><a href="' . esc_url($atts['url']) . '" target="' . esc_attr($atts['target']) . '" class="' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">' . $btn_text . '</a>';
}


/**
 * 3. HỆ THỐNG QUẢN LÝ API TOKEN CHO NGƯỜI DÙNG ADMIN
 */
add_action('show_user_profile', 'vbc_user_profile_fields');
add_action('edit_user_profile', 'vbc_user_profile_fields');

function vbc_user_profile_fields($user) {
    $token = get_user_meta($user->ID, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user->ID, 'vbc_api_token', $token);
    }
    ?>
    <h3><?php _e('VibeCode API Settings', 'vibecode'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="vbc_api_token"><?php _e('API Token', 'vibecode'); ?></label></th>
            <td>
                <input type="text" name="vbc_api_token" id="vbc_api_token" value="<?php echo esc_attr($token); ?>" class="regular-text" readonly style="background-color: #f0f0f0; font-family: monospace;" />
                <p class="description"><?php _e('Token này được sử dụng để xác thực các yêu cầu API bên ngoài (ví dụ: Antigravity skill để tạo Landing Page).', 'vibecode'); ?></p>
                <br>
                <label>
                    <input type="checkbox" name="vbc_regenerate_token" value="1" />
                    <?php _e('Tạo lại token mới (Regenerate API Token)', 'vibecode'); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'vbc_save_user_profile_fields');
add_action('edit_user_profile_update', 'vbc_save_user_profile_fields');

function vbc_save_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }
    if (!empty($_POST['vbc_regenerate_token'])) {
        $new_token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $new_token);
    }
}


/**
 * 4. CỔNG REST API (UPLOAD & PAGE CREATOR)
 */
add_action('rest_api_init', 'vbc_register_rest_routes');

function vbc_register_rest_routes() {
    register_rest_route('vbc/v1', '/upload', array(
        'methods' => 'POST',
        'callback' => 'vbc_api_upload_handler',
        'permission_callback' => function($request) {
            $user = vbc_authenticate_request($request);
            if (is_wp_error($user)) {
                return $user;
            }
            return user_can($user, 'upload_files');
        }
    ));

    register_rest_route('vbc/v1', '/page', array(
        'methods' => 'POST',
        'callback' => 'vbc_api_page_handler',
        'permission_callback' => function($request) {
            $user = vbc_authenticate_request($request);
            if (is_wp_error($user)) {
                return $user;
            }
            return user_can($user, 'edit_pages') || user_can($user, 'edit_posts');
        }
    ));
}

function vbc_authenticate_request($request) {
    $token = $request->get_header('X-VBC-Token');
    if (!$token) {
        $auth_header = $request->get_header('Authorization');
        if ($auth_header && preg_match('/Bearer\s+(.+)/i', $auth_header, $matches)) {
            $token = $matches[1];
        }
    }
    if (!$token) {
        $token = $request->get_param('token');
    }
    
    if (empty($token)) {
        return new WP_Error('vbc_unauthorized', 'Missing API token.', array('status' => 401));
    }
    
    $users = get_users(array(
        'meta_key' => 'vbc_api_token',
        'meta_value' => $token,
        'number' => 1,
        'count_total' => false,
    ));
    
    if (empty($users)) {
        return new WP_Error('vbc_unauthorized', 'Invalid API token.', array('status' => 401));
    }
    
    $user = $users[0];
    wp_set_current_user($user->ID);
    return $user;
}

function vbc_api_upload_handler($request) {
    if (empty($_FILES['file'])) {
        return new WP_Error('vbc_no_file', 'No file was uploaded.', array('status' => 400));
    }
    
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    $attachment_id = media_handle_upload('file', 0);
    
    if (is_wp_error($attachment_id)) {
        return new WP_Error('vbc_upload_failed', $attachment_id->get_error_message(), array('status' => 500));
    }
    
    $url = wp_get_attachment_url($attachment_id);
    
    return array(
        'success' => true,
        'attachment_id' => $attachment_id,
        'url' => $url,
    );
}

function vbc_api_page_handler($request) {
    $params = $request->get_params();
    $post_id = !empty($params['post_id']) ? intval($params['post_id']) : 0;
    $title = !empty($params['title']) ? sanitize_text_field($params['title']) : '';
    $content = !empty($params['content']) ? $params['content'] : ''; 
    $status = !empty($params['status']) ? sanitize_key($params['status']) : 'publish';
    $slug = !empty($params['slug']) ? sanitize_title($params['slug']) : '';
    $post_type = !empty($params['post_type']) ? sanitize_key($params['post_type']) : 'page';
    
    if ($post_id > 0) {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('vbc_not_found', 'Page not found.', array('status' => 404));
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('vbc_forbidden', 'You do not have permission to edit this page.', array('status' => 403));
        }
        
        $post_data = array(
            'ID' => $post_id,
            'post_content' => $content,
        );
        if (!empty($title)) {
            $post_data['post_title'] = $title;
        }
        if (!empty($slug)) {
            $post_data['post_name'] = $slug;
        }
        if (!empty($status)) {
            $post_data['post_status'] = $status;
        }
        
        $updated_id = wp_update_post($post_data, true);
        if (is_wp_error($updated_id)) {
            return new WP_Error('vbc_save_failed', $updated_id->get_error_message(), array('status' => 500));
        }
        
        return array(
            'success' => true,
            'post_id' => $updated_id,
            'url' => get_permalink($updated_id),
            'action' => 'update',
        );
    } else {
        if (!current_user_can('edit_pages')) {
            return new WP_Error('vbc_forbidden', 'You do not have permission to create pages.', array('status' => 403));
        }
        
        $post_data = array(
            'post_title' => !empty($title) ? $title : 'Untitled Page',
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => $post_type,
        );
        if (!empty($slug)) {
            $post_data['post_name'] = $slug;
        }
        
        $new_id = wp_insert_post($post_data, true);
        if (is_wp_error($new_id)) {
            return new WP_Error('vbc_save_failed', $new_id->get_error_message(), array('status' => 500));
        }
        
        return array(
            'success' => true,
            'post_id' => $new_id,
            'url' => get_permalink($new_id),
            'action' => 'create',
        );
    }
}
