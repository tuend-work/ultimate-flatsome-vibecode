<?php
/**
 * Ultimate Flatsome VibeCode - UX Builder Elements Registration
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('ux_builder_setup', 'vbc_register_ux_builder_elements');

function vbc_get_common_options($tag_type) {
    $options = array();

    if ($tag_type === 'text') {
        $options['text'] = array(
            'type' => 'textarea',
            'heading' => 'Nội dung (Text / HTML)',
            'default' => '',
            'description' => 'Nhập chữ, HTML hoặc shortcode (như [vbc_icon]).',
        );
    } elseif ($tag_type === 'container') {
        $options['content'] = array(
            'type' => 'textarea',
            'heading' => 'Nội dung trực tiếp (Content)',
            'default' => '',
            'description' => 'Nhập chữ, HTML hoặc shortcode nếu không dùng các khối con kéo thả.',
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
            'color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ (Color)',
                'responsive' => true,
                'default' => '',
            ),
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền (Background Color)',
                'responsive' => true,
                'default' => '',
            ),
            'font_family' => array(
                'type' => 'textfield',
                'heading' => 'Font chữ (Google Font)',
                'default' => '',
                'description' => 'Ví dụ: Outfit, Inter, Montserrat. Tự động nạp từ Google Fonts.',
            ),
            'font_size' => array(
                'type' => 'textfield',
                'heading' => 'Cỡ chữ (Font Size)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 16px, 1.25rem, 32px',
            ),
            'font_weight' => array(
                'type' => 'select',
                'heading' => 'Độ đậm chữ (Font Weight)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    '300' => '300 (Light)',
                    '400' => '400 (Regular)',
                    '500' => '500 (Medium)',
                    '600' => '600 (Semi Bold)',
                    '700' => '700 (Bold)',
                    '800' => '800 (Extra Bold)',
                    '900' => '900 (Black)',
                ),
            ),
            'line_height' => array(
                'type' => 'textfield',
                'heading' => 'Chiều cao dòng (Line Height)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 1.5, 1.7, 28px',
            ),
            'letter_spacing' => array(
                'type' => 'textfield',
                'heading' => 'Khoảng cách chữ (Letter Spacing)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 0.5px, 1px, -0.5px',
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
            'text_transform' => array(
                'type' => 'select',
                'heading' => 'Kiểu chữ hoa/thường',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    'none' => 'Bình thường (none)',
                    'uppercase' => 'CHỮ HOA (UPPERCASE)',
                    'lowercase' => 'chữ thường (lowercase)',
                    'capitalize' => 'Viết Hoa Đầu Từ',
                ),
            ),
        ),
    );

    $options['layout_group'] = array(
        'type' => 'group',
        'heading' => 'Bố cục & Kích thước (Layout & Flex/Grid)',
        'options' => array(
            'display' => array(
                'type' => 'select',
                'heading' => 'Hiển thị (Display)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    'block' => 'block',
                    'flex' => 'flex',
                    'inline-flex' => 'inline-flex',
                    'grid' => 'grid',
                    'inline-block' => 'inline-block',
                    'inline' => 'inline',
                    'none' => 'none',
                ),
            ),
            'width' => array(
                'type' => 'textfield',
                'heading' => 'Độ rộng (Width)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 100%, 350px, auto',
            ),
            'max_width' => array(
                'type' => 'textfield',
                'heading' => 'Độ rộng tối đa (Max Width)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 1200px, 600px',
            ),
            'height' => array(
                'type' => 'textfield',
                'heading' => 'Chiều cao (Height)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 100%, 400px, auto',
            ),
            'min_height' => array(
                'type' => 'textfield',
                'heading' => 'Chiều cao tối thiểu (Min Height)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 300px, 100vh',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin (Lề ngoài)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 10px 0 20px 0, 0 auto',
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding (Lề trong)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 24px, 15px 30px',
            ),
            'flex_direction' => array(
                'type' => 'select',
                'heading' => 'Hướng Flex (Flex Direction)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định (row)',
                    'row' => 'Hàng ngang (row)',
                    'column' => 'Cột dọc (column)',
                    'row-reverse' => 'Đảo hàng ngang (row-reverse)',
                    'column-reverse' => 'Đảo cột dọc (column-reverse)',
                ),
            ),
            'justify_content' => array(
                'type' => 'select',
                'heading' => 'Canh trục chính (Justify Content)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    'flex-start' => 'Bắt đầu (flex-start)',
                    'center' => 'Căn giữa (center)',
                    'flex-end' => 'Cuối (flex-end)',
                    'space-between' => 'Giãn đều 2 đầu (space-between)',
                    'space-around' => 'Giãn đều xung quanh (space-around)',
                    'space-evenly' => 'Giãn đều bằng nhau (space-evenly)',
                ),
            ),
            'align_items' => array(
                'type' => 'select',
                'heading' => 'Canh trục phụ (Align Items)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    'flex-start' => 'Trên cùng (flex-start)',
                    'center' => 'Căn giữa (center)',
                    'flex-end' => 'Dưới cùng (flex-end)',
                    'stretch' => 'Kéo giãn (stretch)',
                    'baseline' => 'Đường cơ sở (baseline)',
                ),
            ),
            'gap' => array(
                'type' => 'textfield',
                'heading' => 'Khoảng cách item (Gap)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 20px, 1.5rem, 16px 24px',
            ),
            'grid_template_columns' => array(
                'type' => 'textfield',
                'heading' => 'Cột Grid (Grid Columns)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: repeat(3, 1fr), 1fr 2fr, repeat(auto-fit, minmax(250px, 1fr))',
            ),
        ),
    );

    $options['effects_group'] = array(
        'type' => 'group',
        'heading' => 'Viền, Bo góc & Hiệu ứng (Borders & Effects)',
        'options' => array(
            'border' => array(
                'type' => 'textfield',
                'heading' => 'Đường viền (Border)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 1px solid #e2e8f0, 2px dashed #2563eb',
            ),
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 8px, 16px, 50%, 9999px',
            ),
            'box_shadow' => array(
                'type' => 'textfield',
                'heading' => 'Đổ bóng (Box Shadow)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 0 10px 25px rgba(0,0,0,0.1), 0 4px 6px -1px rgba(0,0,0,0.05)',
            ),
            'position' => array(
                'type' => 'select',
                'heading' => 'Vị trí (Position)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định (static)',
                    'relative' => 'Tương đối (relative)',
                    'absolute' => 'Tuyệt đối (absolute)',
                    'fixed' => 'Cố định (fixed)',
                    'sticky' => 'Dính (sticky)',
                ),
            ),
            'z_index' => array(
                'type' => 'textfield',
                'heading' => 'Thứ tự lớp (Z-Index)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 10, 99, 999',
            ),
            'overflow' => array(
                'type' => 'select',
                'heading' => 'Tràn viền (Overflow)',
                'responsive' => true,
                'default' => '',
                'options' => array(
                    '' => 'Mặc định',
                    'hidden' => 'Ẩn tràn (hidden)',
                    'visible' => 'Hiện tràn (visible)',
                    'auto' => 'Tự động (auto)',
                    'scroll' => 'Thanh cuộn (scroll)',
                ),
            ),
            'opacity' => array(
                'type' => 'textfield',
                'heading' => 'Độ mờ (Opacity)',
                'responsive' => true,
                'default' => '',
                'description' => 'Ví dụ: 0.9, 0.5, 1',
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
                    'heading' => 'Nguồn nội dung (Dynamic Data)',
                    'default' => 'default',
                    'options' => array(
                        'default' => 'Mặc định (Nhập chữ hoặc dùng {{post_title}})',
                        'post_title' => 'Tiêu đề bài viết (Post Title)',
                        'post_excerpt' => 'Mô tả ngắn (Post Excerpt)',
                        'post_date' => 'Ngày đăng (Post Date)',
                        'post_author' => 'Tác giả (Author)',
                        'manual' => 'Nhập thủ công',
                        'post_meta' => 'WP Post Meta Key',
                        'acf' => 'ACF Field Key',
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
                    'description' => 'Hỗ trợ Custom Field hoặc các trường WP mặc định: post_title, post_excerpt, post_date, post_author, permalink, ID...',
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
        'p' => array('name' => 'VBC Paragraph', 'type' => 'text'),
        'i' => array('name' => 'VBC Italic', 'type' => 'text'),
        'span' => array('name' => 'VBC Span', 'type' => 'text'),
        'a' => array('name' => 'VBC Link', 'type' => 'text'),
        'h1' => array('name' => 'VBC H1', 'type' => 'text'),
        'h2' => array('name' => 'VBC H2', 'type' => 'text'),
        'h3' => array('name' => 'VBC H3', 'type' => 'text'),
        'h4' => array('name' => 'VBC H4', 'type' => 'text'),
        'h5' => array('name' => 'VBC H5', 'type' => 'text'),
        'h6' => array('name' => 'VBC H6', 'type' => 'text'),
        'li' => array('name' => 'VBC List Item', 'type' => 'container'),
        'ul' => array('name' => 'VBC Unordered List', 'type' => 'container'),
        'ol' => array('name' => 'VBC Ordered List', 'type' => 'container'),
        'table' => array('name' => 'VBC Table', 'type' => 'container'),
        'tr' => array('name' => 'VBC Table Row', 'type' => 'container'),
        'td' => array('name' => 'VBC Table Cell', 'type' => 'container'),
        'th' => array('name' => 'VBC Table Header', 'type' => 'container'),
        'b' => array('name' => 'VBC Bold', 'type' => 'text'),
        'strong' => array('name' => 'VBC Strong', 'type' => 'text'),
        'em' => array('name' => 'VBC Emphasis', 'type' => 'text'),
        'u' => array('name' => 'VBC Underline', 'type' => 'text'),
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

    add_ux_builder_shortcode('vbc_tabs', array(
        'name' => 'VBC Tabs',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'style' => array(
                'type' => 'select',
                'heading' => 'Kiểu Tab (Style)',
                'default' => 'pills',
                'options' => array(
                    'pills' => 'Viên thuốc (Pills)',
                    'underline' => 'Gạch chân (Underline)',
                    'cards' => 'Thẻ bài (Cards)',
                    'glass' => 'Kính mờ (Glassmorphism)',
                ),
            ),
            'align' => array(
                'type' => 'select',
                'heading' => 'Căn lề Tabs (Align)',
                'default' => 'left',
                'options' => array(
                    'left' => 'Trái (Left)',
                    'center' => 'Giữa (Center)',
                    'right' => 'Phải (Right)',
                    'justify' => 'Đều 2 bên (Justify)',
                ),
            ),
            'active_tab' => array(
                'type' => 'textfield',
                'heading' => 'Tab mặc định kích hoạt (Số thứ tự)',
                'default' => '1',
            ),
            'tab_bg' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền Tab thường',
                'default' => '',
            ),
            'tab_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ Tab thường',
                'default' => '',
            ),
            'tab_active_bg' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền Tab kích hoạt (Active)',
                'default' => '',
            ),
            'tab_active_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu chữ Tab kích hoạt (Active)',
                'default' => '',
            ),
            'padding' => array(
                'type' => 'textfield',
                'heading' => 'Padding Khung Tab',
                'responsive' => true,
                'default' => '',
            ),
            'margin' => array(
                'type' => 'textfield',
                'heading' => 'Margin Khung Tab',
                'responsive' => true,
                'default' => '',
            ),
            'width' => array(
                'type' => 'textfield',
                'heading' => 'Độ rộng (Width)',
                'responsive' => true,
                'default' => '',
            ),
            'background_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu nền cả khung Tab',
                'responsive' => true,
                'default' => '',
            ),
            'border_radius' => array(
                'type' => 'textfield',
                'heading' => 'Bo góc (Border Radius)',
                'default' => '',
            ),
            'border_color' => array(
                'type' => 'colorpicker',
                'heading' => 'Màu viền',
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

    add_ux_builder_shortcode('vbc_tab', array(
        'name' => 'VBC Tab Item',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'title' => array(
                'type' => 'textfield',
                'heading' => 'Tiêu đề Tab',
                'default' => 'Tab Title',
            ),
            'icon' => array(
                'type' => 'textfield',
                'heading' => 'Icon Class (vd: fa fa-star hoặc dashicons-admin-post)',
                'default' => '',
            ),
            'tab_id' => array(
                'type' => 'textfield',
                'heading' => 'Custom ID cho Pane (Tùy chọn)',
                'default' => '',
            ),
            'custom_class' => array(
                'type' => 'textfield',
                'heading' => 'CSS Class cho Pane',
                'default' => '',
            ),
            'custom_css' => array(
                'type' => 'textarea',
                'heading' => 'Custom CSS cho Pane',
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

    add_ux_builder_shortcode('vbc_post', array(
        'name' => 'VBC Post & Product Grid',
        'category' => 'VibeCode Components',
        'type' => 'container',
        'options' => array(
            'query_group' => array(
                'type' => 'group',
                'heading' => 'Nguồn Dữ Liệu (Query)',
                'options' => array(
                    'post_type' => array(
                        'type' => 'select',
                        'heading' => 'Loại bài viết (Post Type)',
                        'default' => 'post',
                        'options' => array(
                            'post' => 'Bài viết (Posts)',
                            'product' => 'Sản phẩm (WooCommerce)',
                            'page' => 'Trang (Pages)',
                            'any' => 'Tất cả (Any Post Type)',
                            'custom' => 'Tùy chỉnh CPT',
                        ),
                    ),
                    'custom_post_type' => array(
                        'type' => 'textfield',
                        'heading' => 'Tên CPT tùy chỉnh',
                        'default' => '',
                        'conditions' => 'post_type === "custom"',
                    ),
                    'ids' => array(
                        'type' => 'textfield',
                        'heading' => 'Danh sách Post ID (cách nhau dấu phẩy)',
                        'default' => '',
                        'description' => 'Nhập các ID bài viết/sản phẩm cần hiển thị chính xác (Ví dụ: 12, 45, 78).',
                    ),
                    'taxonomy' => array(
                        'type' => 'textfield',
                        'heading' => 'Tên Taxonomy',
                        'default' => '',
                        'description' => 'Ví dụ: category, product_cat, post_tag, linh-vuc...',
                    ),
                    'terms' => array(
                        'type' => 'textfield',
                        'heading' => 'Danh sách Terms (Slug hoặc ID)',
                        'default' => '',
                        'description' => 'Nhập slug hoặc ID chuyên mục cách nhau dấu phẩy (Ví dụ: ban-hang, dich-vu hoặc 12, 34).',
                    ),
                    'operator' => array(
                        'type' => 'select',
                        'heading' => 'Điều kiện lọc Term',
                        'default' => 'IN',
                        'options' => array(
                            'IN' => 'Thuộc một trong các term (IN)',
                            'AND' => 'Thuộc tất cả các term (AND)',
                            'NOT IN' => 'Không thuộc các term (NOT IN)',
                        ),
                    ),
                    'posts_per_page' => array(
                        'type' => 'textfield',
                        'heading' => 'Số lượng bài hiển thị',
                        'default' => '8',
                        'description' => 'Nhập số bài muốn hiển thị. Nhập -1 để hiển thị tất cả.',
                    ),
                    'offset' => array(
                        'type' => 'textfield',
                        'heading' => 'Bỏ qua n bài đầu (Offset)',
                        'default' => '',
                    ),
                    'orderby' => array(
                        'type' => 'select',
                        'heading' => 'Sắp xếp theo',
                        'default' => 'date',
                        'options' => array(
                            'date' => 'Ngày đăng (Mới nhất)',
                            'title' => 'Tiêu đề (A-Z)',
                            'menu_order' => 'Thứ tự sắp xếp (Menu Order)',
                            'rand' => 'Ngẫu nhiên (Random)',
                            'post__in' => 'Theo thứ tự ID nhập vào',
                            'modified' => 'Ngày cập nhật',
                            'comment_count' => 'Lượt bình luận',
                            'meta_value_num' => 'Giá trị Custom Field (Số)',
                            'meta_value' => 'Giá trị Custom Field (Chữ)',
                            'ID' => 'ID bài viết',
                        ),
                    ),
                    'order' => array(
                        'type' => 'select',
                        'heading' => 'Thứ tự',
                        'default' => 'DESC',
                        'options' => array(
                            'DESC' => 'Giảm dần (Mới nhất / Lớn nhất)',
                            'ASC' => 'Tăng dần (Cũ nhất / Nhỏ nhất)',
                        ),
                    ),
                    'meta_key' => array(
                        'type' => 'textfield',
                        'heading' => 'Meta Key (Lọc / Sắp xếp)',
                        'default' => '',
                    ),
                    'meta_value' => array(
                        'type' => 'textfield',
                        'heading' => 'Meta Value (Giá trị cần lọc)',
                        'default' => '',
                    ),
                ),
            ),
            'layout_group' => array(
                'type' => 'group',
                'heading' => 'Bố Cục & Lưới (Layout)',
                'options' => array(
                    'layout' => array(
                        'type' => 'select',
                        'heading' => 'Kiểu hiển thị',
                        'default' => 'grid',
                        'options' => array(
                            'grid' => 'Dạng lưới (Card Grid)',
                            'list' => 'Dạng danh sách ngang (Horizontal List)',
                            'table' => 'Dạng bảng (Table List)',
                        ),
                    ),
                    'columns' => array(
                        'type' => 'select',
                        'heading' => 'Số cột hiển thị',
                        'responsive' => true,
                        'default' => '3',
                        'options' => array(
                            '1' => '1 Cột',
                            '2' => '2 Cột',
                            '3' => '3 Cột',
                            '4' => '4 Cột',
                            '5' => '5 Cột',
                            '6' => '6 Cột',
                        ),
                    ),
                    'gap' => array(
                        'type' => 'textfield',
                        'heading' => 'Khoảng cách giữa các bài (Gap)',
                        'responsive' => true,
                        'default' => '24px',
                    ),
                    'pagination' => array(
                        'type' => 'select',
                        'heading' => 'Phân trang (Pagination)',
                        'default' => 'none',
                        'options' => array(
                            'none' => 'Không phân trang',
                            'numeric' => 'Phân trang số (1, 2, 3...)',
                        ),
                    ),
                ),
            ),
            'fields_group' => array(
                'type' => 'group',
                'heading' => 'Trường Xuất Ra & Độ Rộng (Fields)',
                'options' => array(
                    'fields' => array(
                        'type' => 'textarea',
                        'heading' => 'Danh sách trường & Độ rộng',
                        'default' => 'thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, button:50%',
                        'description' => 'Nhập các trường theo thứ tự hiển thị, định dạng: field_name:width (Ví dụ: thumbnail:100%, title:100%, price:50%, button:50%, meta:dien_tich:50%, acf:phone:100%). Hỗ trợ: thumbnail, title, price, excerpt, content, date, author, categories, tags, button, rating, sku, stock, meta:key, acf:key.',
                    ),
                    'image_size' => array(
                        'type' => 'select',
                        'heading' => 'Kích thước ảnh thumbnail',
                        'default' => 'large',
                        'options' => array(
                            'large' => 'Lớn (Large)',
                            'medium' => 'Vừa (Medium)',
                            'full' => 'Gốc (Full)',
                            'thumbnail' => 'Nhỏ (Thumbnail)',
                            'woocommerce_thumbnail' => 'WooCommerce Thumbnail',
                        ),
                    ),
                    'image_height' => array(
                        'type' => 'textfield',
                        'heading' => 'Chiều cao ảnh',
                        'default' => '220px',
                    ),
                    'image_fit' => array(
                        'type' => 'select',
                        'heading' => 'Cắt ảnh (Object Fit)',
                        'default' => 'cover',
                        'options' => array(
                            'cover' => 'Cắt vừa khung (Cover)',
                            'contain' => 'Hiển thị trọn vẹn (Contain)',
                        ),
                    ),
                    'title_tag' => array(
                        'type' => 'select',
                        'heading' => 'Thẻ tiêu đề',
                        'default' => 'h3',
                        'options' => array(
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'p' => 'Paragraph',
                        ),
                    ),
                    'title_size' => array(
                        'type' => 'textfield',
                        'heading' => 'Cỡ chữ tiêu đề',
                        'default' => '18px',
                    ),
                    'title_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu tiêu đề',
                        'default' => '#0f172a',
                    ),
                    'title_lines' => array(
                        'type' => 'textfield',
                        'heading' => 'Số dòng tiêu đề tối đa',
                        'default' => '2',
                    ),
                    'excerpt_length' => array(
                        'type' => 'textfield',
                        'heading' => 'Số từ tóm tắt (Excerpt)',
                        'default' => '20',
                    ),
                    'price_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu giá sản phẩm',
                        'default' => '#2563eb',
                    ),
                    'price_size' => array(
                        'type' => 'textfield',
                        'heading' => 'Cỡ chữ giá',
                        'default' => '18px',
                    ),
                    'button_text' => array(
                        'type' => 'textfield',
                        'heading' => 'Chữ nút bấm (Button Text)',
                        'default' => 'Xem Chi Tiết',
                    ),
                    'button_variant' => array(
                        'type' => 'select',
                        'heading' => 'Kiểu nút bấm',
                        'default' => 'primary',
                        'options' => array(
                            'primary' => 'Chính (Primary Blue)',
                            'secondary' => 'Phụ (Secondary)',
                            'alert' => 'Nổi bật (Alert Red)',
                            'success' => 'Thành công (Success Green)',
                            'outline' => 'Viền mỏng (Outline)',
                            'dark' => 'Tối (Dark)',
                        ),
                    ),
                ),
            ),
            'card_group' => array(
                'type' => 'group',
                'heading' => 'Định Dạng Thẻ Card',
                'options' => array(
                    'card_bg' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu nền Card',
                        'default' => '#ffffff',
                    ),
                    'card_padding' => array(
                        'type' => 'textfield',
                        'heading' => 'Padding Card',
                        'default' => '20px',
                    ),
                    'card_radius' => array(
                        'type' => 'textfield',
                        'heading' => 'Bo góc Card (Border Radius)',
                        'default' => '16px',
                    ),
                    'card_border' => array(
                        'type' => 'textfield',
                        'heading' => 'Viền Card (Border)',
                        'default' => '1px solid #e2e8f0',
                    ),
                    'card_shadow' => array(
                        'type' => 'textfield',
                        'heading' => 'Đổ bóng Card (Box Shadow)',
                        'default' => '0 4px 15px rgba(0,0,0,0.03)',
                    ),
                    'card_hover' => array(
                        'type' => 'select',
                        'heading' => 'Hiệu ứng khi Hover',
                        'default' => 'translateY',
                        'options' => array(
                            'translateY' => 'Nâng lên nhẹ (TranslateY)',
                            'scale' => 'Phóng to nhẹ (Scale)',
                            'shadow' => 'Tăng đổ bóng (Shadow Only)',
                            'none' => 'Không hiệu ứng',
                        ),
                    ),
                    'custom_class' => array(
                        'type' => 'textfield',
                        'heading' => 'CSS Class',
                        'default' => '',
                    ),
                    'custom_css' => array(
                        'type' => 'textarea',
                        'heading' => 'Custom CSS (Dùng selector)',
                        'default' => '',
                    ),
                ),
            ),
        ),
    ));
}
