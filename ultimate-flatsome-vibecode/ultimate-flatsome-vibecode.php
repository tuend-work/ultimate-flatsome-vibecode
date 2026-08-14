<?php
/**
 * Plugin Name: Ultimate Flatsome VibeCode Elements
 * Plugin URI: https://github.com/tuend-work/ultimate-flatsome-vibecode
 * Description: Thêm các phần tử HTML cơ bản tích hợp sâu với Flatsome UX Builder, hỗ trợ responsive hoàn hảo, chèn dữ liệu động (Post Meta, ACF) và chỉnh sửa CSS nâng cao.
 * Version: 1.7.5
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
    $options = array();

    if ($tag_type === 'container') {
        $options['content'] = array(
            'type' => 'textarea',
            'heading' => 'Nội dung (Text/HTML/Shortcode)',
            'default' => '',
            'description' => 'Nhập chữ, HTML hoặc shortcode (như [vbc_icon]). Nhập ở đây sẽ hiển thị trực tiếp và không bị Flatsome tự ý bọc thẻ p/text.',
        );
    }

    $options['styling_group'] = array(
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
                'font_family' => array(
                    'type' => 'textfield',
                    'heading' => 'Font chữ (Google Font)',
                    'default' => '',
                    'description' => 'Ví dụ: Outfit, Inter, Montserrat. Sẽ tự động nạp từ Google Fonts.',
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

    // Đăng ký các Advanced Components vào UX Builder
    add_ux_builder_shortcode('vbc_card', array(
        'name' => 'VBC Card',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'content' => array(
                'type' => 'textarea',
                'heading' => 'Nội dung (Text/HTML/Shortcode)',
                'default' => '',
                'description' => 'Nhập chữ, HTML hoặc shortcode. Nhập ở đây sẽ hiển thị trực tiếp và không bị Flatsome tự ý bọc thẻ p/text.',
            ),
            'variant' => array(
                'type' => 'select',
                'heading' => 'Biến thể (Variant)',
                'default' => 'glass',
                'options' => array(
                    'glass' => 'Kính mờ (Glassmorphism)',
                    'custom' => 'Tùy chỉnh',
                ),
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding',
                'responsive' => true,
                'default' => '35px 30px',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin',
                'responsive' => true,
                'default' => '',
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
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền',
                'responsive' => true,
                'default' => '',
            ),
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'default' => '20px',
            ),
            'border_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu viền',
                'default' => 'rgba(255,255,255,0.08)',
            ),
            'glow_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu phát sáng (Hover Glow)',
                'default' => 'rgba(239, 68, 68, 0.2)',
            ),
            'font_family' => array(
                'type' => 'textfield',
                'heading' => 'Font chữ (Google Font)',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_testimonial', array(
        'name' => 'VBC Testimonial',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'content' => array(
                'type' => 'textarea',
                'heading' => 'Nội dung (Text/HTML/Shortcode)',
                'default' => '',
                'description' => 'Nhập chữ, HTML hoặc shortcode. Nhập ở đây sẽ hiển thị trực tiếp và không bị Flatsome tự ý bọc thẻ p/text.',
            ),
            'name' => array(
                'type' => 'textfield',
                'heading' => 'Tên khách hàng',
                'default' => 'Khách Hàng',
            ),
            'company' => array(
                'type' => 'textfield',
                'heading' => 'Công ty / Chức vụ',
                'default' => '',
            ),
            'stars' => array(
                'type' => 'select',
                'heading' => 'Đánh giá (Sao)',
                'default' => '5',
                'options' => array(
                    '1' => '1 Sao',
                    '2' => '2 Sao',
                    '3' => '3 Sao',
                    '4' => '4 Sao',
                    '5' => '5 Sao',
                ),
            ),
            'avatar_url' => array(
                'type' => 'textfield',
                'heading' => 'URL ảnh đại diện',
                'default' => '',
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding',
                'responsive' => true,
                'default' => '35px 28px',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin',
                'responsive' => true,
                'default' => '',
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
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền',
                'responsive' => true,
                'default' => '',
            ),
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'default' => '20px',
            ),
            'border_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu viền',
                'default' => 'rgba(255,255,255,0.08)',
            ),
            'text_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ quote',
                'default' => '#cbd5e1',
            ),
            'author_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ tác giả',
                'default' => '#ffffff',
            ),
            'font_family' => array(
                'type' => 'textfield',
                'heading' => 'Font chữ (Google Font)',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_accordion', array(
        'name' => 'VBC Accordion',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'content' => array(
                'type' => 'textarea',
                'heading' => 'Nội dung (Text/HTML/Shortcode)',
                'default' => '',
                'description' => 'Nhập chữ, HTML hoặc shortcode. Nhập ở đây sẽ hiển thị trực tiếp và không bị Flatsome tự ý bọc thẻ p/text.',
            ),
            'faq_schema' => array(
                'type' => 'select',
                'heading' => 'FAQ Schema (SEO)',
                'default' => 'true',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding',
                'responsive' => true,
                'default' => '35px 45px',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin',
                'responsive' => true,
                'default' => '',
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
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền',
                'responsive' => true,
                'default' => '',
            ),
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'default' => '24px',
            ),
            'border_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu viền',
                'default' => 'rgba(255,255,255,0.08)',
            ),
            'font_family' => array(
                'type' => 'textfield',
                'heading' => 'Font chữ (Google Font)',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_accordion_item', array(
        'name' => 'VBC Accordion Item',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'title' => array(
                'type' => 'textfield',
                'heading' => 'Tiêu đề câu hỏi',
                'default' => '',
            ),
            'open' => array(
                'type' => 'select',
                'heading' => 'Mặc định mở',
                'default' => 'false',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'title_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ tiêu đề',
                'default' => '',
            ),
            'content_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ nội dung',
                'default' => '',
            ),
            'font_size' => array(
                'type' => 'textfield',
                'heading' => 'Cỡ chữ tiêu đề',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_button', array(
        'name' => 'VBC Button',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'text' => array(
                'type' => 'textfield',
                'heading' => 'Chữ nút bấm',
                'default' => 'Click Here',
            ),
            'url' => array(
                'type' => 'textfield',
                'heading' => 'Liên kết (URL)',
                'default' => '#',
            ),
            'target' => array(
                'type' => 'select',
                'heading' => 'Mở liên kết',
                'default' => '_self',
                'options' => array(
                    '_self' => 'Cửa sổ hiện tại (_self)',
                    '_blank' => 'Cửa sổ mới (_blank)',
                ),
            ),
            'variant' => array(
                'type' => 'select',
                'heading' => 'Giao diện mẫu (Variant)',
                'default' => 'danger',
                'options' => array(
                    'danger' => 'Gradient Đỏ',
                    'glass' => 'Kính mờ (Glassmorphism)',
                    'custom' => 'Tùy chỉnh màu riêng',
                ),
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding',
                'responsive' => true,
                'default' => '16px 38px',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin',
                'responsive' => true,
                'default' => '',
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
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'default' => '30px',
            ),
            'font_size' => array(
                'type' => 'textfield',
                'heading' => 'Cỡ chữ',
                'responsive' => true,
                'default' => '15px',
            ),
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền (variant Custom)',
                'default' => '',
            ),
            'text_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ (variant Custom)',
                'default' => '',
            ),
            'font_family' => array(
                'type' => 'textfield',
                'heading' => 'Font chữ (Google Font)',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_slider', array(
        'name' => 'VBC Slider',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'per_page' => array(
                'type' => 'textfield',
                'heading' => 'Slides mỗi trang (Desktop)',
                'default' => '1',
            ),
            'speed' => array(
                'type' => 'textfield',
                'heading' => 'Tốc độ chuyển (ms)',
                'default' => '400',
            ),
            'autoplay' => array(
                'type' => 'select',
                'heading' => 'Tự động chạy (Autoplay)',
                'default' => 'false',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'arrows' => array(
                'type' => 'select',
                'heading' => 'Hiện nút mũi tên',
                'default' => 'true',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'pagination' => array(
                'type' => 'select',
                'heading' => 'Hiện dấu chấm chuyển trang',
                'default' => 'true',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'gap' => array(
                'type' => 'textfield',
                'heading' => 'Khoảng cách giữa các Slide',
                'default' => '20px',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_slide', array(
        'name' => 'VBC Slide Item',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'content' => array(
                'type' => 'textarea',
                'heading' => 'Nội dung (Text/HTML/Shortcode)',
                'default' => '',
                'description' => 'Nhập chữ, HTML hoặc shortcode. Nhập ở đây sẽ hiển thị trực tiếp và không bị Flatsome tự ý bọc thẻ p/text.',
            ),
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền Slide',
                'default' => '',
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS',
                'default' => '',
            ),
        ),
    ));

    add_ux_builder_shortcode('vbc_fullpage', array(
        'name' => 'VBC FullPage Wrapper',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'navigation' => array(
                'type' => 'select',
                'heading' => 'Hiện menu điều hướng bên cạnh',
                'default' => 'true',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'navigation_position' => array(
                'type' => 'select',
                'heading' => 'Vị trí menu điều hướng',
                'default' => 'right',
                'options' => array(
                    'left' => 'Bên trái',
                    'right' => 'Bên phải',
                ),
            ),
            'scroll_bar' => array(
                'type' => 'select',
                'heading' => 'Hiện thanh cuộn mặc định',
                'default' => 'false',
                'options' => array(
                    'true' => 'Có',
                    'false' => 'Không',
                ),
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class',
                'default' => '',
            ),
        ),
    ));
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
        add_shortcode('vbc_' . $tag . '_inner', 'vbc_shortcode_renderer');
        for ($i = 1; $i <= 5; $i++) {
            add_shortcode('vbc_' . $tag . '_inner_' . $i, 'vbc_shortcode_renderer');
        }
    }
}
add_action('init', 'vbc_register_shortcodes');

/**
 * Helper function to remove plain <p> and <br> tags auto-generated by wpautop.
 */
function vbc_clean_inner_content($content) {
    if (empty($content)) {
        return '';
    }
    // Remove attribute-less <p> and </p> tags
    $content = preg_replace('/<p\s*>/i', '', $content);
    $content = preg_replace('/<\/p>/i', '', $content);
    // Remove auto-generated <br /> and <br> tags
    $content = preg_replace('/<br\s*\/?>/i', '', $content);
    return $content;
}

function vbc_shortcode_renderer($atts, $content = null, $tag = '') {
    $html_tag = str_replace('vbc_', '', $tag);

    // Loại bỏ suffix lồng nhau như _inner, _inner_1, v.v. để lấy tag HTML gốc
    $html_tag = preg_replace('/_inner(_\d+)?$/', '', $html_tag);

    // Map các alias của div về thẻ div thực tế
    if (in_array($html_tag, array('box', 'block', 'container'))) {
        $html_tag = 'div';
    }

    $atts = shortcode_atts(array(
        'content' => '',
        // Common Styling Options
        'custom_class' => '',
        'custom_css' => '',
        'custom_attributes' => '',
        'font_family' => '',
        
        // Responsive Options (được tự động ánh xạ bằng __md và __sm)
        'width' => '', 'width__md' => '', 'width__sm' => '',
        'height' => '', 'height__md' => '', 'height__sm' => '',
        'margin' => '', 'margin__md' => '', 'margin__sm' => '',
        'padding' => '', 'padding__md' => '', 'padding__sm' => '',
        'font_size' => '', 'font_size__md' => '', 'font_size__sm' => '',
        'font_weight' => '',
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
        'font_weight' => 'font-weight',
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

    if (!empty($atts['font_family'])) {
        $styles_desktop[] = 'font-family: \'' . esc_attr($atts['font_family']) . '\', sans-serif;';
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
            }
            
            // Fallback tự động nhận diện URL
            if (empty($img_url)) {
                if (!empty($atts['img_url'])) {
                    $img_url = $atts['img_url'];
                } elseif (!empty($atts['img_src'])) {
                    $img_url = $atts['img_src'];
                } elseif (!empty($atts['src'])) {
                    $img_url = $atts['src'];
                }
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

    $inner_content_to_render = !empty($atts['content']) ? $atts['content'] : $content;
    $inner_content_to_render = vbc_clean_inner_content($inner_content_to_render);
    $children = do_shortcode($inner_content_to_render);

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
    }

    // Strip automatic <p> and <br> generated by wpautop on inline/text tags to prevent styling issues (like nested <p> inside headings)
    $text_like_tags = array(
        'span', 'i', 'b', 'strong', 'em', 'u', 'a', 
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 
        'p', 'li', 'button', 'td', 'th'
    );
    if (in_array($html_tag, $text_like_tags)) {
        $final_content = preg_replace('/<\/?p[^>]*>/i', '', $final_content);
        $final_content = preg_replace('/<\/?div[^>]*>/i', '', $final_content);
        $final_content = preg_replace('/^(<br\s*\/?>\s*)+|(<br\s*\/?>\s*)+$/i', '', $final_content);
        $final_content = str_replace(array('<br>', '<br />'), '', $final_content);
    }

    return '<' . $html_tag . $class_attr_str . $tag_attrs . $custom_attrs . '>' . $compiled_css . $final_content . '</' . $html_tag . '>';
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
    add_shortcode('vbc_slider', 'vbc_slider_shortcode');
    add_shortcode('vbc_slide', 'vbc_slide_shortcode');
    add_shortcode('vbc_fullpage', 'vbc_fullpage_shortcode');
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

function vbc_card_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'variant' => 'glass',
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

    $padding = !empty($atts['padding']) ? $atts['padding'] : '35px 30px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '20px';
    $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255,255,255,0.08)';
    $glow = !empty($atts['glow_color']) ? $atts['glow_color'] : 'rgba(239, 68, 68, 0.2)';
    $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'rgba(255, 255, 255, 0.03)';

    $res = vbc_compile_element_css($atts, 'vbc-card');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $card_rules = '.' . $unique_class . ' { ';
    $card_rules .= 'background: ' . $bg . '; ';
    $card_rules .= 'backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); ';
    $card_rules .= 'border: 1px solid ' . $border . '; ';
    $card_rules .= 'border-radius: ' . $radius . '; ';
    $card_rules .= 'padding: ' . $padding . '; ';
    $card_rules .= 'box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); ';
    $card_rules .= 'transition: all 0.3s ease; ';
    $card_rules .= '} ';
    $card_rules .= '.' . $unique_class . ':hover { ';
    $card_rules .= 'transform: translateY(-8px); ';
    $card_rules .= 'border-color: ' . $glow . '; ';
    $card_rules .= 'box-shadow: 0 20px 40px ' . $glow . '; ';
    $card_rules .= '} ';

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

function vbc_testimonial_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'name' => 'Khách Hàng',
        'company' => '',
        'stars' => '5',
        'avatar_url' => '',
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

function vbc_accordion_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'faq_schema' => 'true',
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

    $schema_attr = ($atts['faq_schema'] === 'true') ? ' itemscope itemtype="https://schema.org/FAQPage"' : '';

    $padding = !empty($atts['padding']) ? $atts['padding'] : '35px 45px';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '24px';
    $border = !empty($atts['border_color']) ? $atts['border_color'] : 'rgba(255,255,255,0.08)';
    $bg = !empty($atts['background_color']) ? $atts['background_color'] : 'rgba(255,255,255,0.02)';

    $res = vbc_compile_element_css($atts, 'vbc-acc');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $acc_rules = '.' . $unique_class . ' { ';
    $acc_rules .= 'background: ' . $bg . '; ';
    $acc_rules .= 'backdrop-filter: blur(16px); ';
    $acc_rules .= 'border: 1px solid ' . $border . '; ';
    $acc_rules .= 'border-radius: ' . $radius . '; ';
    $acc_rules .= 'padding: ' . $padding . '; ';
    $acc_rules .= 'box-shadow: 0 15px 40px rgba(0,0,0,0.3); ';
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
        'font_size' => '',
    ), $atts);

    $open_attr = ($atts['open'] === 'true') ? ' open' : '';
    
    $title_color = !empty($atts['title_color']) ? $atts['title_color'] : '#ffffff';
    $content_color = !empty($atts['content_color']) ? $atts['content_color'] : '#cbd5e1';
    $font_size = !empty($atts['font_size']) ? $atts['font_size'] : '18px';

    $html = '<details class="vbc-faq-item" style="border-bottom: 1px solid rgba(255,255,255,0.08); padding: 15px 0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"' . $open_attr . '>';
    $html .= '<summary style="color: ' . esc_attr($title_color) . '; font-size: ' . esc_attr($font_size) . '; font-weight: 700; cursor: pointer; outline: none; list-style: none; display: flex; justify-content: space-between; align-items: center;" itemprop="name">';
    $html .= '<span>' . esc_html($atts['title']) . '</span><span style="font-size: 20px; color: #f87171;">+</span>';
    $html .= '</summary>';
    $html .= '<div style="color: ' . esc_attr($content_color) . '; font-size: 15px; line-height: 1.7; margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
    $html .= '<div itemprop="text">' . do_shortcode(vbc_clean_inner_content($content)) . '</div>';
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

function vbc_slider_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'per_page' => '1',
        'speed' => '400',
        'autoplay' => 'false',
        'arrows' => 'true',
        'pagination' => 'true',
        'gap' => '20px',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    wp_enqueue_style('vbc-splide');
    wp_enqueue_script('vbc-splide');

    $res = vbc_compile_element_css($atts, 'vbc-sldr');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $random_id = wp_generate_password(8, false);
    $slider_id = 'vbc-splide-' . $random_id;

    $js_script = '<script>
    (function() {
        function initSplide() {
            if (typeof Splide !== "undefined" && document.getElementById("' . $slider_id . '")) {
                new Splide("#' . $slider_id . '", {
                    type: "slide",
                    perPage: ' . intval($atts['per_page']) . ',
                    speed: ' . intval($atts['speed']) . ',
                    autoplay: ' . ($atts['autoplay'] === 'true' ? 'true' : 'false') . ',
                    arrows: ' . ($atts['arrows'] === 'true' ? 'true' : 'false') . ',
                    pagination: ' . ($atts['pagination'] === 'true' ? 'true' : 'false') . ',
                    gap: "' . esc_js($atts['gap']) . '",
                    breakpoints: {
                        849: { perPage: 1 },
                        549: { perPage: 1 }
                    }
                }).mount();
            }
        }
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initSplide);
        } else {
            initSplide();
        }
    })();
    </script>';

    $class_str = esc_attr(trim('splide ' . $unique_class . ' ' . $atts['custom_class']));

    $html = '<div id="' . $slider_id . '" class="' . $class_str . '">';
    $html .= $compiled_css;
    $html .= '<div class="splide__track">';
    $html .= '<ul class="splide__list">';
    $html .= do_shortcode(vbc_clean_inner_content($content));
    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= $js_script;

    return $html;
}

function vbc_slide_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'content' => '',
        'background_color' => '',
        'padding' => '',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    $res = vbc_compile_element_css($atts, 'vbc-sld');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $class_str = esc_attr(trim('splide__slide ' . $unique_class . ' ' . $atts['custom_class']));

    $html = '<li class="' . $class_str . '">';
    $html .= $compiled_css;
    $inner_content = !empty($atts['content']) ? $atts['content'] : $content;
    $inner_content = vbc_clean_inner_content($inner_content);
    $html .= do_shortcode($inner_content);
    $html .= '</li>';

    return $html;
}

function vbc_fullpage_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'navigation' => 'true',
        'navigation_position' => 'right',
        'scroll_bar' => 'false',
        'custom_class' => '',
    ), $atts);

    wp_enqueue_style('vbc-fullpage');
    wp_enqueue_script('vbc-fullpage');

    $random_id = wp_generate_password(8, false);
    $fullpage_id = 'vbc-fullpage-' . $random_id;

    $js_script = '<script>
    (function() {
        function initFullPage() {
            var container = document.getElementById("' . $fullpage_id . '");
            if (typeof fullpage !== "undefined" && container) {
                // Di chuyen footer vao trong fullpage container
                var footer = document.getElementById("footer") || document.querySelector("footer") || document.querySelector(".footer-wrapper");
                if (footer) {
                    container.appendChild(footer);
                }

                // Them class section cho cac element con div (va footer), va xoa bo cac the p du thua
                Array.from(container.children).forEach(function(child) {
                    if (child.nodeType === 1) {
                        var tag = child.tagName.toLowerCase();
                        if (tag === "div" || tag === "footer") {
                            child.classList.add("section");
                            if (tag === "footer") {
                                child.classList.add("fp-auto-height");
                            }
                        } else if (tag === "p") {
                            child.remove();
                        }
                    }
                });
                new fullpage("#' . $fullpage_id . '", {
                    licenseKey: "gplv3-license",
                    navigation: ' . ($atts['navigation'] === 'true' ? 'true' : 'false') . ',
                    navigationPosition: "' . esc_js($atts['navigation_position']) . '",
                    scrollBar: ' . ($atts['scroll_bar'] === 'true' ? 'true' : 'false') . ',
                    autoScrolling: true,
                    fitToSection: true,
                    responsiveWidth: 768
                });
            }
        }
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initFullPage);
        } else {
            initFullPage();
        }
    })();
    </script>';

    $class_str = esc_attr(trim('vbc-fullpage-container ' . $atts['custom_class']));

    $html = '<div id="' . $fullpage_id . '" class="' . $class_str . '">';
    $html .= do_shortcode(vbc_clean_inner_content($content));
    $html .= '</div>';
    $html .= $js_script;

    return $html;
}


/**
 * 3. HỆ THỐNG TRANG QUẢN TRỊ VIBECODE & XUẤT DỰ ÁN CHO ANTIGRAVITY
 */

// Đăng ký Menu Quản Trị trong WordPress Admin
add_action('admin_menu', 'vbc_register_admin_menu');
function vbc_register_admin_menu() {
    // Menu chính cấp 1 trên thanh Admin Sidebar
    add_menu_page(
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page',
        'dashicons-layout',
        30
    );

    // Menu phụ dưới Flatsome Panel nếu có
    add_submenu_page(
        'flatsome-panel',
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode Settings', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page'
    );

    // Menu phụ dưới Cài đặt (Settings) dự phòng
    add_options_page(
        __('VibeCode Flatsome', 'vibecode'),
        __('VibeCode', 'vibecode'),
        'manage_options',
        'vibecode-settings',
        'vbc_render_admin_settings_page'
    );
}

// Xử lý Request Xuất Dự Án Antigravity (.zip)
add_action('admin_init', 'vbc_handle_export_project_request');
function vbc_handle_export_project_request() {
    if (!isset($_POST['vbc_action']) || $_POST['vbc_action'] !== 'export_antigravity_project') {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền thực hiện hành động này.', 'vibecode'));
    }

    check_admin_referer('vbc_export_project_nonce', 'vbc_export_nonce');

    // Lưu cấu hình nếu người dùng tích chọn
    if (!empty($_POST['vbc_save_config_data'])) {
        if (isset($_POST['ftp_host'])) update_option('vbc_ftp_host', sanitize_text_field($_POST['ftp_host']));
        if (isset($_POST['ftp_user'])) update_option('vbc_ftp_user', sanitize_text_field($_POST['ftp_user']));
        if (isset($_POST['ftp_password'])) update_option('vbc_ftp_password', sanitize_text_field($_POST['ftp_password']));
        if (isset($_POST['ftp_path'])) update_option('vbc_ftp_path', sanitize_text_field($_POST['ftp_path']));
        if (isset($_POST['brand_phone'])) update_option('vbc_brand_phone', sanitize_text_field($_POST['brand_phone']));
        if (isset($_POST['brand_email'])) update_option('vbc_brand_email', sanitize_email($_POST['brand_email']));
        if (isset($_POST['brand_address'])) update_option('vbc_brand_address', sanitize_text_field($_POST['brand_address']));
        if (isset($_POST['brand_zalo'])) update_option('vbc_brand_zalo', sanitize_text_field($_POST['brand_zalo']));
        if (isset($_POST['brand_hours'])) update_option('vbc_brand_hours', sanitize_text_field($_POST['brand_hours']));
    }

    // 1. API URL & Token
    $api_url = get_rest_url(null, '');
    $user_id = get_current_user_id();
    $token = get_user_meta($user_id, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $token);
    }

    // 2. FTP Info (Fallback <none> & Auto detect root path)
    $detected_root_path = defined('ABSPATH') ? wp_normalize_path(untrailingslashit(ABSPATH)) : (defined('WP_CONTENT_DIR') ? wp_normalize_path(dirname(WP_CONTENT_DIR)) : '/public_html');
    $detected_plugin_path = defined('WP_PLUGIN_DIR') ? wp_normalize_path(WP_PLUGIN_DIR) : $detected_root_path . '/wp-content/plugins';
    $saved_ftp_path = get_option('vbc_ftp_path', '');
    $default_ftp_path = !empty($saved_ftp_path) ? $saved_ftp_path : $detected_root_path;

    $ftp_host = !empty($_POST['ftp_host']) ? trim($_POST['ftp_host']) : (get_option('vbc_ftp_host') ?: '<none>');
    $ftp_user = !empty($_POST['ftp_user']) ? trim($_POST['ftp_user']) : (get_option('vbc_ftp_user') ?: '<none>');
    $ftp_password = !empty($_POST['ftp_password']) ? trim($_POST['ftp_password']) : (get_option('vbc_ftp_password') ?: '<none>');
    $ftp_path = !empty($_POST['ftp_path']) ? trim($_POST['ftp_path']) : ($default_ftp_path ?: '<none>');

    // 3. Website Context & Brand Info
    $site_url = get_site_url();
    $parsed_url = parse_url($site_url);
    $domain_host = !empty($parsed_url['host']) ? $parsed_url['host'] : 'website';
    $clean_domain = sanitize_file_name(preg_replace('/[^a-zA-Z0-9\.\-]/', '-', $domain_host));

    $site_name = isset($_POST['include_site_name']) ? (get_bloginfo('name') ?: '<none>') : '<none>';
    $site_tagline = isset($_POST['include_site_tagline']) ? (get_bloginfo('description') ?: '<none>') : '<none>';
    $language = get_locale() ?: '<none>';
    $timezone = wp_timezone_string() ?: '<none>';

    $contact = array(
        'phone' => isset($_POST['include_contact']) ? (!empty($_POST['brand_phone']) ? trim($_POST['brand_phone']) : (get_option('vbc_brand_phone') ?: '<none>')) : '<none>',
        'email' => isset($_POST['include_contact']) ? (!empty($_POST['brand_email']) ? trim($_POST['brand_email']) : (get_option('vbc_brand_email') ?: (get_option('admin_email') ?: '<none>'))) : '<none>',
        'address' => isset($_POST['include_contact']) ? (!empty($_POST['brand_address']) ? trim($_POST['brand_address']) : (get_option('vbc_brand_address') ?: '<none>')) : '<none>',
        'zalo' => isset($_POST['include_contact']) ? (!empty($_POST['brand_zalo']) ? trim($_POST['brand_zalo']) : (get_option('vbc_brand_zalo') ?: '<none>')) : '<none>',
        'working_hours' => isset($_POST['include_contact']) ? (!empty($_POST['brand_hours']) ? trim($_POST['brand_hours']) : (get_option('vbc_brand_hours') ?: '<none>')) : '<none>',
    );

    // Toàn bộ cài đặt Flatsome (Customizer theme_mods + các options Flatsome trong wp_options)
    $flatsome_settings = '<none>';
    $brand_styles = '<none>';
    if (isset($_POST['include_styles'])) {
        global $wpdb;
        $theme_mods = get_theme_mods();

        // 1. Trích xuất các trường cơ bản cho brand_styles
        $brand_styles = array(
            'primary_color' => isset($theme_mods['color_primary']) ? $theme_mods['color_primary'] : '#2563eb',
            'secondary_color' => isset($theme_mods['color_secondary']) ? $theme_mods['color_secondary'] : '#f8fafc',
            'success_color' => isset($theme_mods['color_success']) ? $theme_mods['color_success'] : '#10b981',
            'alert_color' => isset($theme_mods['color_alert']) ? $theme_mods['color_alert'] : '#ef4444',
            'font_heading' => isset($theme_mods['type_headings_font']) ? $theme_mods['type_headings_font'] : '<none>',
            'font_body' => isset($theme_mods['type_texts_font']) ? $theme_mods['type_texts_font'] : '<none>',
            'font_nav' => isset($theme_mods['type_nav_font']) ? $theme_mods['type_nav_font'] : '<none>',
            'font_alt' => isset($theme_mods['type_alt_font']) ? $theme_mods['type_alt_font'] : '<none>',
        );

        // 2. Lấy toàn bộ các option liên quan đến flatsome trong database wp_options
        $db_options_raw = $wpdb->get_results("
            SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE 'flatsome%' 
               OR option_name LIKE 'theme_mods_flatsome%'
        ", ARRAY_A);

        $flatsome_db_options = array();
        if (!empty($db_options_raw)) {
            foreach ($db_options_raw as $row) {
                $opt_key = $row['option_name'];
                $opt_val = maybe_unserialize($row['option_value']);
                $flatsome_db_options[$opt_key] = $opt_val;
            }
        }

        // 3. Làm sạch và chuẩn hóa theme_mods (loại bỏ các object không thể serialize)
        $clean_theme_mods = array();
        if (is_array($theme_mods)) {
            foreach ($theme_mods as $k => $v) {
                if (is_scalar($v) || is_array($v) || is_null($v)) {
                    $clean_theme_mods[$k] = $v;
                }
            }
        }

        // 4. Tổ chức đối tượng flatsome_settings theo cấu trúc chuyên nghiệp
        $flatsome_settings = array(
            'colors' => array(
                'primary' => isset($theme_mods['color_primary']) ? $theme_mods['color_primary'] : '<none>',
                'secondary' => isset($theme_mods['color_secondary']) ? $theme_mods['color_secondary'] : '<none>',
                'success' => isset($theme_mods['color_success']) ? $theme_mods['color_success'] : '<none>',
                'alert' => isset($theme_mods['color_alert']) ? $theme_mods['color_alert'] : '<none>',
                'links' => isset($theme_mods['color_links']) ? $theme_mods['color_links'] : '<none>',
                'texts' => isset($theme_mods['color_texts']) ? $theme_mods['color_texts'] : '<none>',
                'body_bg' => isset($theme_mods['body_bg']) ? $theme_mods['body_bg'] : '<none>',
            ),
            'typography' => array(
                'font_headings' => isset($theme_mods['type_headings_font']) ? $theme_mods['type_headings_font'] : '<none>',
                'font_body' => isset($theme_mods['type_texts_font']) ? $theme_mods['type_texts_font'] : '<none>',
                'font_nav' => isset($theme_mods['type_nav_font']) ? $theme_mods['type_nav_font'] : '<none>',
                'font_alt' => isset($theme_mods['type_alt_font']) ? $theme_mods['type_alt_font'] : '<none>',
                'font_headings_weight' => isset($theme_mods['type_headings_weight']) ? $theme_mods['type_headings_weight'] : '<none>',
                'font_body_weight' => isset($theme_mods['type_texts_weight']) ? $theme_mods['type_texts_weight'] : '<none>',
            ),
            'layout' => array(
                'site_width' => isset($theme_mods['site_width']) ? $theme_mods['site_width'] : '<none>',
                'layout' => isset($theme_mods['layout']) ? $theme_mods['layout'] : '<none>',
                'box_shadow' => isset($theme_mods['box_shadow']) ? $theme_mods['box_shadow'] : '<none>',
                'content_bg' => isset($theme_mods['content_bg']) ? $theme_mods['content_bg'] : '<none>',
                'container_padding' => isset($theme_mods['container_padding']) ? $theme_mods['container_padding'] : '<none>',
            ),
            'header' => array(
                'site_logo' => isset($theme_mods['site_logo']) ? (is_numeric($theme_mods['site_logo']) ? wp_get_attachment_url($theme_mods['site_logo']) : $theme_mods['site_logo']) : '<none>',
                'site_logo_dark' => isset($theme_mods['site_logo_dark']) ? (is_numeric($theme_mods['site_logo_dark']) ? wp_get_attachment_url($theme_mods['site_logo_dark']) : $theme_mods['site_logo_dark']) : '<none>',
                'header_height' => isset($theme_mods['header_height']) ? $theme_mods['header_height'] : '<none>',
                'header_bg' => isset($theme_mods['header_bg']) ? $theme_mods['header_bg'] : '<none>',
            ),
            'footer' => array(
                'footer_1_color' => isset($theme_mods['footer_1_color']) ? $theme_mods['footer_1_color'] : '<none>',
                'footer_2_color' => isset($theme_mods['footer_2_color']) ? $theme_mods['footer_2_color'] : '<none>',
                'footer_bottom_text' => isset($theme_mods['footer_bottom_text']) ? $theme_mods['footer_bottom_text'] : '<none>',
            ),
            'social_links' => array(
                'facebook' => isset($theme_mods['facebook_url']) ? $theme_mods['facebook_url'] : '<none>',
                'twitter' => isset($theme_mods['twitter_url']) ? $theme_mods['twitter_url'] : '<none>',
                'instagram' => isset($theme_mods['instagram_url']) ? $theme_mods['instagram_url'] : '<none>',
                'youtube' => isset($theme_mods['youtube_url']) ? $theme_mods['youtube_url'] : '<none>',
                'zalo' => isset($theme_mods['zalo_url']) ? $theme_mods['zalo_url'] : (!empty($_POST['brand_zalo']) ? trim($_POST['brand_zalo']) : '<none>'),
                'phone' => isset($theme_mods['phone_url']) ? $theme_mods['phone_url'] : (!empty($_POST['brand_phone']) ? trim($_POST['brand_phone']) : '<none>'),
                'email' => isset($theme_mods['email_url']) ? $theme_mods['email_url'] : (!empty($_POST['brand_email']) ? trim($_POST['brand_email']) : '<none>'),
            ),
            'custom_css' => array(
                'all' => isset($theme_mods['custom_css']) ? $theme_mods['custom_css'] : '<none>',
                'tablet' => isset($theme_mods['custom_css_tablet']) ? $theme_mods['custom_css_tablet'] : '<none>',
                'mobile' => isset($theme_mods['custom_css_mobile']) ? $theme_mods['custom_css_mobile'] : '<none>',
            ),
            'theme_mods' => !empty($clean_theme_mods) ? $clean_theme_mods : '<none>',
            'db_options' => !empty($flatsome_db_options) ? $flatsome_db_options : '<none>',
        );
    }

    $products_data = '<none>';
    if (isset($_POST['include_products'])) {
        $products_count = !empty($_POST['products_count']) ? min(max(1, intval($_POST['products_count'])), 50) : 10;
        $products = array();
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $products_count,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pid = get_the_ID();
                $price = '';
                $regular_price = '';
                if (function_exists('wc_get_product')) {
                    $wc_prod = wc_get_product($pid);
                    if ($wc_prod) {
                        $price = $wc_prod->get_price();
                        $regular_price = $wc_prod->get_regular_price();
                    }
                }
                $terms = get_the_terms($pid, 'product_cat');
                $cat_names = array();
                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $t) $cat_names[] = $t->name;
                }
                $thumb = wp_get_attachment_url(get_post_thumbnail_id($pid));
                $products[] = array(
                    'title' => get_the_title(),
                    'price' => $price ?: '<none>',
                    'regular_price' => $regular_price ?: '<none>',
                    'categories' => !empty($cat_names) ? implode(', ', $cat_names) : '<none>',
                    'url' => get_permalink(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>',
                    'image' => $thumb ?: '<none>'
                );
            }
            wp_reset_postdata();
        }
        $products_data = !empty($products) ? $products : '<none>';
    }

    $services_data = '<none>';
    if (isset($_POST['include_services'])) {
        $pages = array();
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 15,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $pages[] = array(
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'slug' => get_post_field('post_name'),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>'
                );
            }
            wp_reset_postdata();
        }
        $services_data = !empty($pages) ? $pages : '<none>';
    }

    $posts_data = '<none>';
    if (isset($_POST['include_posts'])) {
        $posts_count = !empty($_POST['posts_count']) ? min(max(1, intval($_POST['posts_count'])), 30) : 10;
        $posts = array();
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_count,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $categories = get_the_category();
                $cat_list = array();
                if (!empty($categories)) {
                    foreach ($categories as $c) $cat_list[] = $c->name;
                }
                $posts[] = array(
                    'title' => get_the_title(),
                    'categories' => !empty($cat_list) ? implode(', ', $cat_list) : '<none>',
                    'url' => get_permalink(),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt()) ?: '<none>',
                    'date' => get_the_date('Y-m-d')
                );
            }
            wp_reset_postdata();
        }
        $posts_data = !empty($posts) ? $posts : '<none>';
    }

    $custom_note = !empty($_POST['custom_instructions']) ? sanitize_textarea_field($_POST['custom_instructions']) : '<none>';

    // Cấu trúc chuẩn file vbc-config.json
    $config_array = array(
        'api-url' => $api_url,
        'token' => $token,
        'ftp' => array(
            'host' => $ftp_host,
            'user' => $ftp_user,
            'password' => $ftp_password,
            'path' => $ftp_path,
            'root_path' => $ftp_path,
            'plugins_path' => $detected_plugin_path,
        ),
        'website_context' => array(
            'site_name' => $site_name,
            'site_tagline' => $site_tagline,
            'site_url' => $site_url,
            'language' => $language,
            'timezone' => $timezone,
            'contact' => $contact,
            'brand_styles' => $brand_styles,
            'flatsome_settings' => $flatsome_settings,
            'products' => $products_data,
            'services' => $services_data,
            'posts' => $posts_data,
            'custom_instructions' => $custom_note,
        )
    );

    $json_content = json_encode($config_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Tên file ZIP: {domain}-vibecode-project.zip
    $zip_filename = $clean_domain . '-vibecode-project.zip';
    $skills_dir = plugin_dir_path(__FILE__) . 'skills/';

    // Tạo ZIP bằng ZipArchive
    if (class_exists('ZipArchive')) {
        $temp_zip = wp_tempnam($zip_filename);
        $zip = new ZipArchive();
        if ($zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('vbc-config.json', $json_content);

            if (is_dir($skills_dir)) {
                $files = scandir($skills_dir);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $file_path = $skills_dir . $file;
                        if (is_file($file_path)) {
                            $zip->addFile($file_path, 'skills/' . $file);
                        }
                    }
                }
            }

            $zip->close();

            if (file_exists($temp_zip) && filesize($temp_zip) > 0) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
                header('Content-Length: ' . filesize($temp_zip));
                header('Pragma: no-cache');
                header('Expires: 0');
                readfile($temp_zip);
                @unlink($temp_zip);
                exit;
            }
        }
    }

    // Fallback PclZip
    require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    $temp_zip = wp_tempnam($zip_filename);
    $archive = new PclZip($temp_zip);

    $temp_config_file = wp_tempnam('vbc-config.json');
    file_put_contents($temp_config_file, $json_content);

    $files_to_add = array($temp_config_file);
    if (is_dir($skills_dir)) {
        $skills_files = scandir($skills_dir);
        foreach ($skills_files as $f) {
            if ($f !== '.' && $f !== '..') {
                $files_to_add[] = $skills_dir . $f;
            }
        }
    }

    $archive->create($files_to_add, PCLZIP_OPT_REMOVE_ALL_PATH);
    @unlink($temp_config_file);

    if (file_exists($temp_zip)) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($temp_zip));
        readfile($temp_zip);
        @unlink($temp_zip);
        exit;
    }

    wp_die(__('Không thể tạo tệp nén ZIP trên máy chủ.', 'vibecode'));
}

// Xử lý lưu cấu hình chung & tái tạo token
add_action('admin_init', 'vbc_handle_save_settings_request');
function vbc_handle_save_settings_request() {
    if (isset($_POST['vbc_action']) && $_POST['vbc_action'] === 'save_general_settings') {
        if (!current_user_can('manage_options')) return;
        check_admin_referer('vbc_save_settings_nonce', 'vbc_settings_nonce');

        if (isset($_POST['ftp_host'])) update_option('vbc_ftp_host', sanitize_text_field($_POST['ftp_host']));
        if (isset($_POST['ftp_user'])) update_option('vbc_ftp_user', sanitize_text_field($_POST['ftp_user']));
        if (isset($_POST['ftp_password'])) update_option('vbc_ftp_password', sanitize_text_field($_POST['ftp_password']));
        if (isset($_POST['ftp_path'])) update_option('vbc_ftp_path', sanitize_text_field($_POST['ftp_path']));

        if (isset($_POST['brand_phone'])) update_option('vbc_brand_phone', sanitize_text_field($_POST['brand_phone']));
        if (isset($_POST['brand_email'])) update_option('vbc_brand_email', sanitize_email($_POST['brand_email']));
        if (isset($_POST['brand_address'])) update_option('vbc_brand_address', sanitize_text_field($_POST['brand_address']));
        if (isset($_POST['brand_zalo'])) update_option('vbc_brand_zalo', sanitize_text_field($_POST['brand_zalo']));
        if (isset($_POST['brand_hours'])) update_option('vbc_brand_hours', sanitize_text_field($_POST['brand_hours']));

        if (!empty($_POST['vbc_regenerate_token'])) {
            $user_id = get_current_user_id();
            $new_token = bin2hex(random_bytes(20));
            update_user_meta($user_id, 'vbc_api_token', $new_token);
        }

        $tab = !empty($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'export';
        wp_redirect(add_query_arg(array('page' => 'vibecode-settings', 'tab' => $tab, 'saved' => '1'), admin_url('admin.php')));
        exit;
    }
}

// Giao diện Trang Quản Trị VibeCode
function vbc_render_admin_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'export';
    $user_id = get_current_user_id();
    $token = get_user_meta($user_id, 'vbc_api_token', true);
    if (empty($token)) {
        $token = bin2hex(random_bytes(20));
        update_user_meta($user_id, 'vbc_api_token', $token);
    }

    $api_url = get_rest_url(null, '');
    $site_url = get_site_url();
    $parsed_url = parse_url($site_url);
    $domain_host = !empty($parsed_url['host']) ? $parsed_url['host'] : 'website';
    $clean_domain = sanitize_file_name(preg_replace('/[^a-zA-Z0-9\.\-]/', '-', $domain_host));

    // Tự động nhận diện đường dẫn thư mục gốc website (Root Path chứa wp-config.php)
    $detected_root_path = defined('ABSPATH') ? wp_normalize_path(untrailingslashit(ABSPATH)) : (defined('WP_CONTENT_DIR') ? wp_normalize_path(dirname(WP_CONTENT_DIR)) : '/public_html');
    $detected_plugin_path = defined('WP_PLUGIN_DIR') ? wp_normalize_path(WP_PLUGIN_DIR) : $detected_root_path . '/wp-content/plugins';
    $ftp_host = get_option('vbc_ftp_host', '');
    $ftp_user = get_option('vbc_ftp_user', '');
    $ftp_password = get_option('vbc_ftp_password', '');
    $ftp_path = get_option('vbc_ftp_path', '');
    if (empty($ftp_path)) {
        $ftp_path = $detected_root_path;
    }

    $brand_phone = get_option('vbc_brand_phone', '');
    $brand_email = get_option('vbc_brand_email', get_option('admin_email'));
    $brand_address = get_option('vbc_brand_address', '');
    $brand_zalo = get_option('vbc_brand_zalo', '');
    $brand_hours = get_option('vbc_brand_hours', '8:00 - 18:00 (T2 - T7)');

    $has_wc = class_exists('WooCommerce');
    $product_count = $has_wc ? wp_count_posts('product')->publish : 0;
    $post_count = wp_count_posts('post')->publish;
    $page_count = wp_count_posts('page')->publish;

    ?>
    <div class="wrap vbc-admin-wrap" style="max-width: 1100px; margin-top: 20px;">
        <style>
            .vbc-admin-header {
                background: linear-gradient(135deg, #090d16 0%, #1e293b 100%);
                padding: 25px 30px;
                border-radius: 12px;
                color: #ffffff;
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            }
            .vbc-admin-header h1 {
                color: #ffffff;
                font-size: 24px;
                font-weight: 800;
                margin: 0 0 6px 0;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .vbc-admin-header p {
                color: #94a3b8;
                margin: 0;
                font-size: 14px;
            }
            .vbc-badge {
                display: inline-block;
                background: rgba(37,99,235,0.25);
                color: #60a5fa;
                border: 1px solid rgba(59,130,246,0.35);
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
            }
            .vbc-nav-tab-wrapper {
                margin-bottom: 25px;
                border-bottom: 2px solid #e2e8f0;
                display: flex;
                gap: 8px;
            }
            .vbc-nav-tab {
                padding: 12px 20px;
                text-decoration: none;
                font-weight: 700;
                font-size: 14px;
                color: #64748b;
                border-radius: 8px 8px 0 0;
                background: #f1f5f9;
                transition: all 0.2s;
                border: 1px solid #e2e8f0;
                border-bottom: none;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .vbc-nav-tab:hover {
                color: #2563eb;
                background: #ffffff;
            }
            .vbc-nav-tab.active {
                color: #2563eb;
                background: #ffffff;
                border-top: 3px solid #2563eb;
                margin-bottom: -2px;
                padding-bottom: 13px;
            }
            .vbc-card {
                background: #ffffff;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                padding: 30px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.03);
                margin-bottom: 25px;
            }
            .vbc-card h2 {
                font-size: 18px;
                font-weight: 800;
                color: #0f172a;
                margin-top: 0;
                margin-bottom: 15px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .vbc-grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            @media (max-width: 782px) {
                .vbc-grid-2 { grid-template-columns: 1fr; }
            }
            .vbc-form-group {
                margin-bottom: 18px;
            }
            .vbc-form-group label {
                display: block;
                font-weight: 700;
                font-size: 13px;
                color: #334155;
                margin-bottom: 6px;
            }
            .vbc-form-group input[type="text"],
            .vbc-form-group input[type="password"],
            .vbc-form-group input[type="email"],
            .vbc-form-group textarea,
            .vbc-form-group select {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                padding: 8px 12px;
                font-size: 14px;
            }
            .vbc-checkbox-item {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 14px 18px;
                margin-bottom: 12px;
                display: flex;
                align-items: flex-start;
                gap: 14px;
                transition: all 0.2s;
            }
            .vbc-checkbox-item:hover {
                border-color: #93c5fd;
                background: #eff6ff;
            }
            .vbc-checkbox-item input[type="checkbox"] {
                margin-top: 3px;
                width: 18px;
                height: 18px;
            }
            .vbc-checkbox-item .vbc-chk-title {
                font-weight: 700;
                color: #0f172a;
                font-size: 14px;
                margin-bottom: 3px;
            }
            .vbc-checkbox-item .vbc-chk-desc {
                font-size: 12px;
                color: #64748b;
                line-height: 1.5;
            }
            .vbc-btn-export {
                background: linear-gradient(135deg, #2563eb, #1d4ed8);
                color: #ffffff !important;
                border: none;
                padding: 14px 32px;
                font-size: 16px;
                font-weight: 800;
                border-radius: 8px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 4px 15px rgba(37,99,235,0.35);
                transition: all 0.2s;
            }
            .vbc-btn-export:hover {
                background: linear-gradient(135deg, #1d4ed8, #1e40af);
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37,99,235,0.45);
            }
            .vbc-info-box {
                background: #eff6ff;
                border-left: 4px solid #3b82f6;
                padding: 15px 20px;
                border-radius: 0 8px 8px 0;
                margin-bottom: 20px;
                color: #1e3a8a;
                font-size: 13px;
                line-height: 1.6;
            }
        </style>

        <!-- Header -->
        <div class="vbc-admin-header">
            <div>
                <h1><span class="dashicons dashicons-superhero-alt" style="font-size: 28px; width: 28px; height: 28px;"></span> Ultimate Flatsome VibeCode</h1>
                <p><?php _e('Trình mở rộng chuyên sâu UX Builder & Cổng tự động hóa Landing Page chuẩn CRO', 'vibecode'); ?></p>
            </div>
            <div style="text-align: right;">
                <span class="vbc-badge">Phiên bản 1.7.0</span>
            </div>
        </div>

        <?php if (isset($_GET['saved']) && $_GET['saved'] === '1'): ?>
            <div class="notice notice-success is-dismissible" style="margin-bottom: 20px; border-left-color: #10b981;">
                <p><strong><?php _e('✓ Đã lưu cài đặt cấu hình thành công!', 'vibecode'); ?></strong></p>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="vbc-nav-tab-wrapper">
            <a href="?page=vibecode-settings&tab=export" class="vbc-nav-tab <?php echo $current_tab === 'export' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-download"></span> <?php _e('Xuất Dự Án Antigravity', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=api" class="vbc-nav-tab <?php echo $current_tab === 'api' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-rest-api"></span> <?php _e('API & Xác Thực', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=config" class="vbc-nav-tab <?php echo $current_tab === 'config' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-settings"></span> <?php _e('FTP & Thương Hiệu', 'vibecode'); ?>
            </a>
            <a href="?page=vibecode-settings&tab=docs" class="vbc-nav-tab <?php echo $current_tab === 'docs' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-book"></span> <?php _e('Hướng Dẫn & Shortcodes', 'vibecode'); ?>
            </a>
        </div>

        <!-- TAB 1: EXPORT TO ANTIGRAVITY PROJECT -->
        <?php if ($current_tab === 'export'): ?>
            <div class="vbc-info-box">
                <strong>💡 Antigravity Project Package:</strong> Khi xuất file ZIP, hệ thống sẽ đóng gói toàn bộ thư mục <code>skills/</code> (gồm các script clone và tạo landing page tự động) cùng tệp <code>vbc-config.json</code> chứa dữ liệu ngữ cảnh website. Antigravity AI sẽ đọc hiểu toàn bộ thông tin này để tạo Landing Page chuẩn xác theo ngữ cảnh của website bạn.
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field('vbc_export_project_nonce', 'vbc_export_nonce'); ?>
                <input type="hidden" name="vbc_action" value="export_antigravity_project" />

                <!-- 1. Kết Nối API & Token -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-admin-network"></span> 1. Thông Tin Kết Nối API</h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('WordPress REST API Endpoint', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr($api_url); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#2563eb;" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('VibeCode API Token (User Admin)', 'vibecode'); ?></label>
                            <input type="text" value="<?php echo esc_attr($token); ?>" readonly style="background:#f8fafc; font-family:monospace; color:#059669;" />
                        </div>
                    </div>
                </div>

                <!-- 2. Thông Tin FTP Hosting -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-cloud"></span> 2. Cấu Hình FTP Hosting (Tùy chọn)</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -5px; margin-bottom: 15px;">
                        Thông tin FTP giúp Antigravity có thể tự động tải file chỉnh sửa lên hosting nếu bạn cần can thiệp code trực tiếp. (Nếu để trống, giá trị sẽ là <code>&lt;none&gt;</code>).
                    </p>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Host', 'vibecode'); ?></label>
                            <input type="text" name="ftp_host" value="<?php echo esc_attr($ftp_host); ?>" placeholder="Ví dụ: 103.161.172.211 hoặc ftp.domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP User', 'vibecode'); ?></label>
                            <input type="text" name="ftp_user" value="<?php echo esc_attr($ftp_user); ?>" placeholder="Ví dụ: myuser@domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Password', 'vibecode'); ?></label>
                            <input type="password" name="ftp_password" value="<?php echo esc_attr($ftp_password); ?>" placeholder="••••••••••••" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Đường dẫn thư mục gốc Website trên Hosting (Root Path)', 'vibecode'); ?></label>
                            <input type="text" name="ftp_path" value="<?php echo esc_attr($ftp_path); ?>" placeholder="<?php echo esc_attr($detected_root_path); ?>" />
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                <?php printf(__('Tự động nhận diện thư mục gốc chứa wp-config.php: <code>%s</code>', 'vibecode'), esc_html($detected_root_path)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Lựa Chọn Dữ Liệu Ngữ Cảnh Xuất Ra JSON -->
                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-database"></span> 3. Chọn Dữ Liệu Ngữ Cảnh Website Đóng Gói Vào vbc-config.json</h2>
                    <p style="color: #64748b; font-size: 13px; margin-top: -5px; margin-bottom: 15px;">
                        Tích chọn những dữ liệu bạn muốn đưa vào file cấu hình. Bất kỳ trường nào không chọn hoặc trống dữ liệu sẽ tự động được gán là <code>&lt;none&gt;</code> để AI nhận diện và yêu cầu nhập lại nếu cần.
                    </p>

                    <!-- Checkbox Site Name & Tagline -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_site_name" id="chk_site_name" value="1" checked />
                        <div>
                            <label for="chk_site_name" class="vbc-chk-title"><?php _e('Tên Website & Khẩu Hiệu (Site Name & Tagline)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Hiện tại: <strong><?php echo esc_html(get_bloginfo('name')); ?></strong> — <em><?php echo esc_html(get_bloginfo('description')); ?></em>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Contact Info -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_contact" id="chk_contact" value="1" checked />
                        <div style="width: 100%;">
                            <label for="chk_contact" class="vbc-chk-title"><?php _e('Thông Tin Thương Hiệu & Liên Hệ (Phone, Email, Địa chỉ, Zalo)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc" style="margin-bottom: 10px;">
                                Antigravity AI sẽ tự động điền các thông tin này vào Hero Banner, Nút Hotline và Footer của Landing Page.
                            </div>
                            <div class="vbc-grid-2" style="margin-top: 10px;">
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Hotline / Số điện thoại', 'vibecode'); ?></label>
                                    <input type="text" name="brand_phone" value="<?php echo esc_attr($brand_phone); ?>" placeholder="0912 345 678" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Email Liên Hệ', 'vibecode'); ?></label>
                                    <input type="email" name="brand_email" value="<?php echo esc_attr($brand_email); ?>" placeholder="contact@domain.com" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Địa chỉ Doanh Nghiệp', 'vibecode'); ?></label>
                                    <input type="text" name="brand_address" value="<?php echo esc_attr($brand_address); ?>" placeholder="Số 123 Đường ABC, Hà Nội" style="width:100%;" />
                                </div>
                                <div>
                                    <label style="font-size: 12px; font-weight: 700;"><?php _e('Link Zalo / Chat Hotline', 'vibecode'); ?></label>
                                    <input type="text" name="brand_zalo" value="<?php echo esc_attr($brand_zalo); ?>" placeholder="https://zalo.me/0912345678" style="width:100%;" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Styles & Flatsome Options -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_styles" id="chk_styles" value="1" checked />
                        <div>
                            <label for="chk_styles" class="vbc-chk-title"><?php _e('Toàn Bộ Cài Đặt Flatsome Theme (Customizer & wp_options)', 'vibecode'); ?></label>
                            <div class="vbc-chk-desc">
                                Trích xuất toàn bộ cấu hình Flatsome trong bảng <code>wp_options</code> và Customizer (Bảng màu Primary/Secondary/Success/Alert, Typography, Header, Footer, Site Width, Layout, Custom CSS, Social Links...). Antigravity AI sẽ đồng bộ 100% style của Landing Page với giao diện tổng thể website.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Products -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_products" id="chk_products" value="1" <?php echo $product_count > 0 ? 'checked' : ''; ?> />
                        <div>
                            <label for="chk_products" class="vbc-chk-title"><?php _e('Danh Sách Sản Phẩm (WooCommerce Products)', 'vibecode'); ?> <?php echo $product_count > 0 ? "($product_count sản phẩm)" : '(Chưa có sản phẩm)'; ?></label>
                            <div class="vbc-chk-desc">
                                Xuất tên sản phẩm, giá bán, danh mục, link chi tiết và ảnh đại diện để AI chèn bảng giá/card sản phẩm.
                            </div>
                            <?php if ($product_count > 0): ?>
                                <div style="margin-top: 8px;">
                                    <label style="font-size: 12px; font-weight: 600;"><?php _e('Số lượng sản phẩm cần lấy:', 'vibecode'); ?></label>
                                    <select name="products_count" style="width: 120px; font-size: 12px;">
                                        <option value="5">5 sản phẩm</option>
                                        <option value="10" selected>10 sản phẩm</option>
                                        <option value="20">20 sản phẩm</option>
                                        <option value="50">50 sản phẩm</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Checkbox Services / Pages -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_services" id="chk_services" value="1" checked />
                        <div>
                            <label for="chk_services" class="vbc-chk-title"><?php _e('Danh Sách Dịch Vụ & Trang Quan Trọng', 'vibecode'); ?> (<?php echo $page_count; ?> trang)</label>
                            <div class="vbc-chk-desc">
                                Xuất danh sách trang tĩnh, tiêu đề, tóm tắt và đường dẫn để AI hiểu các dịch vụ trọng tâm của website.
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Blog Posts -->
                    <div class="vbc-checkbox-item">
                        <input type="checkbox" name="include_posts" id="chk_posts" value="1" <?php echo $post_count > 0 ? 'checked' : ''; ?> />
                        <div>
                            <label for="chk_posts" class="vbc-chk-title"><?php _e('Danh Sách Bài Viết Mới Nhất (Blog Posts)', 'vibecode'); ?> (<?php echo $post_count; ?> bài viết)</label>
                            <div class="vbc-chk-desc">
                                Xuất tiêu đề bài viết, chuyên mục, link và tóm tắt.
                            </div>
                            <?php if ($post_count > 0): ?>
                                <div style="margin-top: 8px;">
                                    <label style="font-size: 12px; font-weight: 600;"><?php _e('Số lượng bài viết cần lấy:', 'vibecode'); ?></label>
                                    <select name="posts_count" style="width: 120px; font-size: 12px;">
                                        <option value="5">5 bài viết</option>
                                        <option value="10" selected>10 bài viết</option>
                                        <option value="20">20 bài viết</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Custom Prompt Instruction -->
                    <div class="vbc-form-group" style="margin-top: 20px;">
                        <label><?php _e('Ghi Chú / Yêu Cầu Đặc Biệt Cho Antigravity AI (Tùy chọn)', 'vibecode'); ?></label>
                        <textarea name="custom_instructions" rows="3" placeholder="Ví dụ: Trang web chuyên cung cấp dịch vụ máy chủ và hosting tốc độ cao, tone màu ưu tiên Dark Tech Sleek..."></textarea>
                    </div>

                    <div style="margin-top: 15px;">
                        <label style="font-weight: 600; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="vbc_save_config_data" value="1" checked />
                            <?php _e('Lưu lại thông tin liên hệ và FTP ở trên vào website để tiện cho các lần xuất sau', 'vibecode'); ?>
                        </label>
                    </div>
                </div>

                <!-- Export Action Button -->
                <div style="margin-top: 20px; text-align: center; padding: 20px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <button type="submit" class="vbc-btn-export">
                        <span class="dashicons dashicons-archive" style="font-size: 20px;"></span>
                        <?php printf(__('Xuất File Dự Án %s-vibecode-project.zip', 'vibecode'), esc_html($clean_domain)); ?>
                    </button>
                    <p style="color: #64748b; font-size: 13px; margin-top: 10px; margin-bottom: 0;">
                        Tệp ZIP xuất ra chứa toàn bộ thư mục <code>skills/</code> và tệp <code>vbc-config.json</code> đã được cấu hình đầy đủ.
                    </p>
                </div>
            </form>

        <!-- TAB 2: API & TOKEN -->
        <?php elseif ($current_tab === 'api'): ?>
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-rest-api"></span> <?php _e('Thông Tin Xác Thực API', 'vibecode'); ?></h2>
                <p style="color: #64748b; font-size: 13px;">
                    <?php _e('API Token được cấp riêng cho tài khoản quản trị viên hiện tại để bảo vệ các thao tác đăng trang và upload media từ bên ngoài.', 'vibecode'); ?>
                </p>

                <form method="POST" action="">
                    <?php wp_nonce_field('vbc_save_settings_nonce', 'vbc_settings_nonce'); ?>
                    <input type="hidden" name="vbc_action" value="save_general_settings" />
                    <input type="hidden" name="current_tab" value="api" />

                    <div class="vbc-form-group">
                        <label><?php _e('WordPress REST API Base URL', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr($api_url); ?>" readonly style="background:#f8fafc; font-family:monospace;" />
                    </div>

                    <div class="vbc-form-group">
                        <label><?php _e('API Token Hiện Tại', 'vibecode'); ?></label>
                        <input type="text" value="<?php echo esc_attr($token); ?>" readonly style="background:#f8fafc; font-family:monospace; font-size:16px; font-weight:700; color:#2563eb;" />
                    </div>

                    <div class="vbc-form-group">
                        <label>
                            <input type="checkbox" name="vbc_regenerate_token" value="1" />
                            <strong><?php _e('Tạo lại Token mới (Lưu ý: Token cũ sẽ bị hủy hiệu lực ngay lập tức)', 'vibecode'); ?></strong>
                        </label>
                    </div>

                    <button type="submit" class="button button-primary"><?php _e('Lưu Thay Đổi & Cập Nhật Token', 'vibecode'); ?></button>
                </form>

                <hr style="margin: 25px 0; border: none; border-top: 1px solid #f1f5f9;">

                <h3><?php _e('Kiểm Tra REST API Endpoints', 'vibecode'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px; color: #475569; font-size: 13px; line-height: 1.8;">
                    <li><strong>Upload Media:</strong> <code>POST <?php echo esc_html($api_url); ?>/vbc/v1/upload</code> (Header: <code>X-VBC-Token: <?php echo esc_html(substr($token, 0, 8)); ?>...</code>)</li>
                    <li><strong>Đăng / Cập nhật Trang:</strong> <code>POST <?php echo esc_html($api_url); ?>/vbc/v1/page</code></li>
                    <li><strong>Lấy nội dung Trang:</strong> <code>GET <?php echo esc_html($api_url); ?>/vbc/v1/page?slug=trang-mau</code></li>
                </ul>
            </div>

        <!-- TAB 3: FTP & BRAND CONFIG -->
        <?php elseif ($current_tab === 'config'): ?>
            <form method="POST" action="">
                <?php wp_nonce_field('vbc_save_settings_nonce', 'vbc_settings_nonce'); ?>
                <input type="hidden" name="vbc_action" value="save_general_settings" />
                <input type="hidden" name="current_tab" value="config" />

                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-cloud"></span> <?php _e('Cài Đặt FTP Hosting Mặc Định', 'vibecode'); ?></h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Host', 'vibecode'); ?></label>
                            <input type="text" name="ftp_host" value="<?php echo esc_attr($ftp_host); ?>" placeholder="103.161.172.211" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP User', 'vibecode'); ?></label>
                            <input type="text" name="ftp_user" value="<?php echo esc_attr($ftp_user); ?>" placeholder="user@domain.com" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('FTP Password', 'vibecode'); ?></label>
                            <input type="password" name="ftp_password" value="<?php echo esc_attr($ftp_password); ?>" placeholder="••••••••••••" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Đường dẫn thư mục gốc Website trên Hosting (Root Path)', 'vibecode'); ?></label>
                            <input type="text" name="ftp_path" value="<?php echo esc_attr($ftp_path); ?>" placeholder="<?php echo esc_attr($detected_root_path); ?>" />
                            <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                                <?php printf(__('Tự động nhận diện thư mục gốc chứa wp-config.php: <code>%s</code>', 'vibecode'), esc_html($detected_root_path)); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="vbc-card">
                    <h2><span class="dashicons dashicons-businessperson"></span> <?php _e('Thông Tin Thương Hiệu Doanh Nghiệp', 'vibecode'); ?></h2>
                    <div class="vbc-grid-2">
                        <div class="vbc-form-group">
                            <label><?php _e('Số điện thoại / Hotline', 'vibecode'); ?></label>
                            <input type="text" name="brand_phone" value="<?php echo esc_attr($brand_phone); ?>" placeholder="0912 345 678" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Email liên hệ', 'vibecode'); ?></label>
                            <input type="email" name="brand_email" value="<?php echo esc_attr($brand_email); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Địa chỉ công ty', 'vibecode'); ?></label>
                            <input type="text" name="brand_address" value="<?php echo esc_attr($brand_address); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Link Zalo / Tư vấn', 'vibecode'); ?></label>
                            <input type="text" name="brand_zalo" value="<?php echo esc_attr($brand_zalo); ?>" />
                        </div>
                        <div class="vbc-form-group">
                            <label><?php _e('Giờ làm việc', 'vibecode'); ?></label>
                            <input type="text" name="brand_hours" value="<?php echo esc_attr($brand_hours); ?>" />
                        </div>
                    </div>

                    <button type="submit" class="button button-primary" style="margin-top: 10px;"><?php _e('Lưu Cấu Hình Mặc Định', 'vibecode'); ?></button>
                </div>
            </form>

        <!-- TAB 4: DOCS -->
        <?php elseif ($current_tab === 'docs'): ?>
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-book"></span> <?php _e('Danh Sách Shortcode VibeCode Phổ Biến', 'vibecode'); ?></h2>
                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 200px;"><strong>Shortcode</strong></th>
                            <th><strong>Mô Tả & Cú Pháp Mẫu</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[vbc_icon]</code></td>
                            <td>Thư viện vector icon thông minh: <code>[vbc_icon pack="lucide" name="shield-check" color="#2563eb" size="24px"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_card]</code></td>
                            <td>Khối thẻ card kính mờ: <code>[vbc_card variant="glass" border_radius="20px"] ... [/vbc_card]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_testimonial]</code></td>
                            <td>Đánh giá khách hàng: <code>[vbc_testimonial name="Nguyễn Văn A" stars="5" avatar_url="..."] Nhận xét... [/vbc_testimonial]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_accordion]</code></td>
                            <td>Khối hỏi đáp SEO FAQ: <code>[vbc_accordion][vbc_accordion_item title="Câu hỏi?"]Trả lời...[/vbc_accordion_item][/vbc_accordion]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_button]</code></td>
                            <td>Nút bấm chuyển đổi: <code>[vbc_button text="Xem Thêm" url="#link" variant="danger"]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_slider]</code></td>
                            <td>Khối trượt Splide: <code>[vbc_slider per_page="1"][vbc_slide]...[/vbc_slide][/vbc_slider]</code></td>
                        </tr>
                        <tr>
                            <td><code>[vbc_fullpage]</code></td>
                            <td>Cuộn full-screen từng màn hình: <code>[vbc_fullpage]...[/vbc_fullpage]</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Giữ lại trường hiển thị Token trong User Profile cá nhân
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
        array(
            'methods' => 'POST',
            'callback' => 'vbc_api_page_handler',
            'permission_callback' => function($request) {
                $user = vbc_authenticate_request($request);
                if (is_wp_error($user)) {
                    return $user;
                }
                return user_can($user, 'edit_pages') || user_can($user, 'edit_posts');
            }
        ),
        array(
            'methods' => 'GET',
            'callback' => 'vbc_api_get_page_handler',
            'permission_callback' => function($request) {
                $user = vbc_authenticate_request($request);
                if (is_wp_error($user)) {
                    return $user;
                }
                return user_can($user, 'edit_pages') || user_can($user, 'edit_posts');
            }
        )
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
    
    // Cho phép upload SVG an toàn
    add_filter('upload_mimes', function($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        return $mimes;
    });
    
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
        'id' => $attachment_id,
        'attachment_id' => $attachment_id,
        'url' => $url,
    );
}

function vbc_api_page_handler($request) {
    $params = $request->get_params();
    $post_id = !empty($params['post_id']) ? intval($params['post_id']) : 0;
    $action_type = !empty($params['action_type']) ? sanitize_key($params['action_type']) : '';
    $title = !empty($params['title']) ? sanitize_text_field($params['title']) : '';
    $content = !empty($params['content']) ? $params['content'] : ''; 
    $status = !empty($params['status']) ? sanitize_key($params['status']) : 'publish';
    $slug = !empty($params['slug']) ? sanitize_title($params['slug']) : '';
    $post_type = !empty($params['post_type']) ? sanitize_key($params['post_type']) : 'page';

    if ($action_type === 'delete') {
        if ($post_id <= 0) {
            return new WP_Error('vbc_invalid_id', 'Post ID is required for deletion.', array('status' => 400));
        }
        if (!current_user_can('delete_post', $post_id)) {
            return new WP_Error('vbc_forbidden', 'You do not have permission to delete this post.', array('status' => 403));
        }
        $deleted = wp_delete_post($post_id, true);
        if (!$deleted) {
            return new WP_Error('vbc_delete_failed', 'Failed to delete post.', array('status' => 500));
        }
        return array(
            'success' => true,
            'deleted_id' => $post_id,
            'action' => 'delete',
        );
    }
    
    if ($post_id <= 0 && !empty($slug)) {
        $existing = get_page_by_path($slug, OBJECT, $post_type);
        if ($existing) {
            $post_id = $existing->ID;
        }
    }
    
    if ($post_id > 0) {
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('vbc_not_found', 'Page not found.', array('status' => 404));
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return new WP_Error('vbc_forbidden', 'You do not have permission to edit this page.', array('status' => 403));
        }
        
        $target_template = !empty($params['template']) ? sanitize_text_field($params['template']) : 'page-blank.php';
        $current_template = get_post_meta($post_id, '_wp_page_template', true);
        $valid_templates = array_keys(wp_get_theme()->get_page_templates());
        if ($current_template && !in_array($current_template, $valid_templates) && $current_template !== 'default') {
            update_post_meta($post_id, '_wp_page_template', 'default');
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
        
        update_post_meta($updated_id, '_wp_page_template', $target_template);
        
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
        
        $target_template = !empty($params['template']) ? sanitize_text_field($params['template']) : 'page-blank.php';
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
        
        update_post_meta($new_id, '_wp_page_template', $target_template);
        
        return array(
            'success' => true,
            'post_id' => $new_id,
            'url' => get_permalink($new_id),
            'action' => 'create',
        );
    }
}

function vbc_api_get_page_handler($request) {
    $post_id = intval($request->get_param('post_id'));
    $slug = sanitize_title($request->get_param('slug'));
    
    if ($post_id > 0) {
        $post = get_post($post_id);
    } elseif (!empty($slug)) {
        $posts = get_posts(array(
            'name' => $slug,
            'post_type' => 'any',
            'posts_per_page' => 1
        ));
        $post = !empty($posts) ? $posts[0] : null;
    } else {
        return new WP_Error('vbc_missing_param', 'Post ID or Slug is required.', array('status' => 400));
    }
    
    if (!$post) {
        return new WP_Error('vbc_not_found', 'Page not found.', array('status' => 404));
    }
    
    return array(
        'success' => true,
        'post_id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'slug' => $post->post_name,
        'status' => $post->post_status,
        'post_type' => $post->post_type,
    );
}

/**
 * 5. QUẢN LÝ THƯ VIỆN ICON THÔNG MINH (CONDITIONAL ICON LOADING)
 */
add_action('wp_enqueue_scripts', 'vbc_register_icon_libraries');
add_action('admin_enqueue_scripts', 'vbc_register_icon_libraries');

function vbc_register_icon_libraries() {
    // Đăng ký danh sách các CDN thư viện icon (Chỉ đăng ký, chưa nạp vào trang)
    wp_register_style('vbc-fontawesome6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
    wp_register_style('vbc-remixicon', 'https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css', array(), '4.2.0');
    wp_register_style('vbc-material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', array(), '1.0');
    wp_register_script('vbc-lucide', 'https://unpkg.com/lucide@latest', array(), 'latest', false);
    wp_register_script('vbc-phosphor', 'https://unpkg.com/@phosphor-icons/web', array(), 'latest', false);

    // Đăng ký Splide.js & fullpage.js
    wp_register_style('vbc-splide', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css', array(), '4.1.4');
    wp_register_script('vbc-splide', 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js', array(), '4.1.4', true);
    wp_register_style('vbc-fullpage', 'https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.20/fullpage.min.css', array(), '4.0.20');
    wp_register_script('vbc-fullpage', 'https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/4.0.20/fullpage.min.js', array(), '4.0.20', true);

    // Nếu đang trong trình chỉnh sửa UX Builder hoặc customizer preview, nạp sẵn tất cả thư viện để hiển thị xem trước
    $is_ux_builder = false;
    if (function_exists('ux_builder_is_active') && ux_builder_is_active()) {
        $is_ux_builder = true;
    }
    if (isset($_GET['uxb_iframe']) || isset($_GET['ux-builder']) || (is_admin() && isset($_GET['page']) && $_GET['page'] === 'uxbuilder')) {
        $is_ux_builder = true;
    }

    if ($is_ux_builder) {
        wp_enqueue_style('vbc-fontawesome6');
        wp_enqueue_style('vbc-remixicon');
        wp_enqueue_style('vbc-material-symbols');
        wp_enqueue_script('vbc-lucide');
        wp_enqueue_script('vbc-phosphor');
        
        add_action('wp_footer', 'vbc_lucide_trigger_script', 99);
        add_action('admin_footer', 'vbc_lucide_trigger_script', 99);
    }
}

function vbc_enqueue_icon_pack($pack) {
    $pack = strtolower(trim($pack));
    if ($pack === 'fontawesome' || $pack === 'fa') {
        wp_enqueue_style('vbc-fontawesome6');
    } elseif ($pack === 'remix' || $pack === 'ri') {
        wp_enqueue_style('vbc-remixicon');
    } elseif ($pack === 'material' || $pack === 'google') {
        wp_enqueue_style('vbc-material-symbols');
    } elseif ($pack === 'lucide') {
        wp_enqueue_script('vbc-lucide');
        add_action('wp_footer', 'vbc_lucide_trigger_script', 99);
    } elseif ($pack === 'phosphor' || $pack === 'ph') {
        wp_enqueue_script('vbc-phosphor');
    }
}

function vbc_lucide_trigger_script() {
    ?>
    <script>
    (function() {
        function triggerLucide() {
            if (typeof lucide !== 'undefined') {
                var icons = document.querySelectorAll('i[data-lucide]');
                if (icons.length > 0) {
                    lucide.createIcons();
                }
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', triggerLucide);
        } else {
            triggerLucide();
        }

        if (typeof MutationObserver !== 'undefined') {
            var lucideTimeout;
            var observer = new MutationObserver(function(mutations) {
                // Debounce Lucide trigger to prevent freezing the browser thread on rapid updates in UX Builder
                clearTimeout(lucideTimeout);
                lucideTimeout = setTimeout(triggerLucide, 100);
            });
            observer.observe(document.body, { 
                childList: true, 
                subtree: true,
                attributes: true,
                attributeFilter: ['data-lucide']
            });
        }
    })();
    </script>
    <?php
}

add_action('ux_builder_enqueue_scripts', 'vbc_editor_scripts');
add_action('admin_enqueue_scripts', 'vbc_editor_scripts');
function vbc_editor_scripts() {
    $is_ux = false;
    if (isset($_GET['app']) && $_GET['app'] === 'uxbuilder') {
        $is_ux = true;
    }
    if (did_action('ux_builder_enqueue_scripts')) {
        $is_ux = true;
    }
    
    if (!$is_ux) {
        return;
    }

    // Make sure libraries are registered
    wp_register_style('vbc-fontawesome6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1');
    wp_register_style('vbc-remixicon', 'https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css', array(), '4.2.0');
    wp_register_style('vbc-material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', array(), '1.0');
    wp_register_script('vbc-lucide', 'https://unpkg.com/lucide@latest', array(), 'latest', false);
    wp_register_script('vbc-phosphor', 'https://unpkg.com/@phosphor-icons/web', array(), 'latest', false);

    wp_enqueue_style('vbc-fontawesome6');
    wp_enqueue_style('vbc-remixicon');
    wp_enqueue_style('vbc-material-symbols');
    wp_enqueue_script('vbc-lucide');
    wp_enqueue_script('vbc-phosphor');

    wp_enqueue_script('vbc-icon-picker', plugins_url('assets/vbc-icon-picker.js', __FILE__), array('jquery'), '1.4.3', true);
    wp_enqueue_style('vbc-icon-picker', plugins_url('assets/vbc-icon-picker.css', __FILE__), array(), '1.4.3');
}

// Đăng ký UX Builder & Shortcode handler cho [vbc_icon]
add_action('ux_builder_setup', 'vbc_register_icon_ux_builder');
function vbc_register_icon_ux_builder() {
    if (function_exists('add_ux_builder_shortcode')) {
        add_ux_builder_shortcode('vbc_icon', array(
            'name'     => 'VBC Icon Pack',
            'category' => 'VibeCode HTML',
            'options'  => array(
                'icon_value' => array(
                    'type'        => 'textfield',
                    'heading'     => 'Ảnh / Icon đã chọn',
                    'default'     => 'icon:shield-check',
                    'description' => 'Nhấn nút bên dưới để mở bộ chọn.',
                ),
                'color' => array(
                    'type'    => 'colorpicker',
                    'heading' => 'Màu sắc',
                    'default' => '',
                ),
                'size' => array(
                    'type'    => 'textfield',
                    'heading' => 'Kích thước (Size)',
                    'default' => '32px',
                ),
                'custom_class' => array(
                    'type'    => 'textfield',
                    'heading' => 'Custom Class',
                    'default' => '',
                ),
                'custom_css' => array(
                    'type'    => 'textarea',
                    'heading' => 'Custom CSS',
                    'default' => '',
                ),
            ),
        ));
    }
}

add_action('init', function() {
    add_shortcode('vbc_icon', 'vbc_icon_shortcode_renderer');
});

function vbc_icon_shortcode_renderer($atts) {
    $atts = shortcode_atts(array(
        'icon_value'    => '',
        'mode'          => 'icon',
        'svg_url'       => '',
        'pack'          => 'lucide',
        'name'          => '',
        'name_fa'       => '',
        'name_ri'       => '',
        'name_lucide'   => '',
        'name_phosphor' => '',
        'name_material' => '',
        'name_custom'   => '',
        'color'         => '',
        'size'          => '32px',
        'custom_class'  => '',
        'custom_css'    => '',
    ), $atts);

    $random_id    = wp_generate_password(8, false);
    $unique_class = 'vbc-icn-' . $random_id;
    $styles       = array();
    if (!empty($atts['color'])) $styles[] = 'color: ' . esc_attr($atts['color']) . ';';

    $css_rule = '';
    if (!empty($styles) || !empty($atts['custom_css'])) {
        $css_rule .= '.' . $unique_class . ' { ' . implode(' ', $styles) . ' } ';
        if (!empty($atts['custom_css'])) {
            $raw_css = trim($atts['custom_css']);
            if (strpos($raw_css, '{') === false) {
                $css_rule .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
            } else {
                $css_rule .= str_replace('selector', '.' . $unique_class, $raw_css);
            }
        }
    }
    $style_tag = '';
    if (!empty($css_rule)) {
        $css_rule = str_replace(array("\r", "\n"), ' ', $css_rule);
        $css_rule = preg_replace('/\s+/', ' ', $css_rule);
        if (vbc_should_inline_css()) {
            $style_tag = '<style>' . $css_rule . '</style>';
        } else {
            global $vbc_accumulated_css;
            if (!is_array($vbc_accumulated_css)) $vbc_accumulated_css = array();
            $vbc_accumulated_css[] = $css_rule;
        }
    }
    $class_str = esc_attr(trim($unique_class . ' ' . $atts['custom_class']));
    $sz = !empty($atts['size']) ? $atts['size'] : '32px';

    // === 1. UNIFIED icon_value: img:URL hoac icon:classname ===
    $iv = trim($atts['icon_value']);
    if (!empty($iv)) {
        if (strpos($iv, 'img:') === 0) {
            $img_url = substr($iv, 4);
            $img_style = 'width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';object-fit:contain;display:inline-block;';
            return $style_tag . '<img src="' . esc_url($img_url) . '" class="' . $class_str . '" style="' . $img_style . '" alt="icon" loading="lazy">';
        } elseif (strpos($iv, 'icon:') === 0) {
            $icon_class = substr($iv, 5);
            if (strpos($icon_class, 'ri-') !== false) {
                vbc_enqueue_icon_pack('remix');
                return $style_tag . '<i class="' . esc_attr($icon_class) . ' ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';"></i>';
            } elseif (strpos($icon_class, 'ph ') !== false || strpos($icon_class, 'ph-') !== false) {
                vbc_enqueue_icon_pack('phosphor');
                return $style_tag . '<i class="' . esc_attr($icon_class) . ' ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';"></i>';
            } elseif (strpos($icon_class, 'fa-') !== false) {
                vbc_enqueue_icon_pack('fontawesome');
                return $style_tag . '<i class="' . esc_attr($icon_class) . ' ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';"></i>';
            } elseif (strpos($icon_class, '_') !== false && strpos($icon_class, '-') === false) {
                vbc_enqueue_icon_pack('material');
                return $style_tag . '<span class="material-symbols-outlined ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';">' . esc_html($icon_class) . '</span>';
            } else {
                vbc_enqueue_icon_pack('lucide');
                return $style_tag . '<i data-lucide="' . esc_attr($icon_class) . '" class="' . $class_str . '" style="width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';display:inline-block;"></i>';
            }
        }
    }

    // === 2. BACKWARD COMPAT: shortcodes cu ===
    if ($atts['mode'] === 'svg' && !empty($atts['svg_url'])) {
        $svg_val = trim($atts['svg_url']);
        $img_url = is_numeric($svg_val) ? wp_get_attachment_url(intval($svg_val)) : $svg_val;
        if (!empty($img_url)) {
            $img_style = 'width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';object-fit:contain;display:inline-block;';
            return $style_tag . '<img src="' . esc_url($img_url) . '" class="' . $class_str . '" style="' . $img_style . '" alt="icon" loading="lazy">';
        }
    }

    $pack = strtolower(trim($atts['pack']));
    if (empty($pack) || $pack === 'fontawesome') {
        if (!empty($atts['name_lucide']) && $atts['name_lucide'] !== 'custom')         $pack = 'lucide';
        elseif (!empty($atts['name_ri']) && $atts['name_ri'] !== 'custom')             $pack = 'remix';
        elseif (!empty($atts['name_phosphor']) && $atts['name_phosphor'] !== 'custom') $pack = 'phosphor';
        elseif (!empty($atts['name_material']) && $atts['name_material'] !== 'custom') $pack = 'material';
        elseif (!empty($atts['name_fa']) && $atts['name_fa'] !== 'custom')             $pack = 'fontawesome';
    }
    $name = '';
    if ($pack === 'fontawesome')  $name = !empty($atts['name_fa'])       && $atts['name_fa']       !== 'custom' ? $atts['name_fa']       : $atts['name_custom'];
    elseif ($pack === 'remix')    $name = !empty($atts['name_ri'])       && $atts['name_ri']       !== 'custom' ? $atts['name_ri']       : $atts['name_custom'];
    elseif ($pack === 'lucide')   $name = !empty($atts['name_lucide'])   && $atts['name_lucide']   !== 'custom' ? $atts['name_lucide']   : $atts['name_custom'];
    elseif ($pack === 'phosphor') $name = !empty($atts['name_phosphor']) && $atts['name_phosphor'] !== 'custom' ? $atts['name_phosphor'] : $atts['name_custom'];
    elseif ($pack === 'material') $name = !empty($atts['name_material']) && $atts['name_material'] !== 'custom' ? $atts['name_material'] : $atts['name_custom'];
    if (empty($name)) $name = trim($atts['name']);
    if (strpos($name, 'ri-') !== false) $pack = 'remix';
    elseif (strpos($name, 'ph-') !== false) $pack = 'phosphor';

    vbc_enqueue_icon_pack($pack);

    if ($pack === 'lucide') {
        return $style_tag . '<i data-lucide="' . esc_attr($name) . '" class="' . $class_str . '" style="width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';display:inline-block;"></i>';
    } elseif ($pack === 'material') {
        return $style_tag . '<span class="material-symbols-outlined ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';">' . esc_html($name) . '</span>';
    } else {
        return $style_tag . '<i class="' . esc_attr($name) . ' ' . $class_str . '" style="font-size:' . esc_attr($sz) . ';"></i>';
    }
}

/**
 * 6. VÔ HIỆU HÓA TỰ ĐỘNG CHUYỂN EMOJI THÀNH ẢNH S.W.ORG CỦA WORDPRESS CORE
 */
add_action('init', 'vbc_disable_wp_emojis');

function vbc_disable_wp_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'vbc_disable_emojis_tinymce');
    add_filter('wp_resource_hints', 'vbc_disable_emojis_dns_prefetch', 10, 2);
}

function vbc_disable_emojis_tinymce($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    }
    return array();
}

function vbc_disable_emojis_dns_prefetch($urls, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/');
        $urls = array_diff($urls, array($emoji_svg_url));
    }
    return $urls;
}

/**
 * 7. LOẠI BỎ ĐĂNG KÝ TRÙNG LẶP ĐỂ BẢO VỆ CẤU HÌNH NÂNG CAO
 * (Các phần tử đã được đăng ký đầy đủ và tối ưu trong vbc_register_ux_builder_elements)
 */

// Filter dọn dẹp các thẻ p và br tự động sinh quanh các shortcodes
function vbc_clean_shortcode_html($content) {
    $array = array(
        '<p>[' => '[',
        ']</p>' => ']',
        ']<br />' => ']',
        ']<br>' => ']',
        '<br />[' => '[',
        '<br>[' => '['
    );
    return strtr($content, $array);
}
add_filter('the_content', 'vbc_clean_shortcode_html', 100);
