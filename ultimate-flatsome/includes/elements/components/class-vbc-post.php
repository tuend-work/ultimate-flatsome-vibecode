<?php
/**
 * Ultimate Flatsome VibeCode - Dynamic Post Query & Grid Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_parse_post_fields_config($fields_str, $post_type = 'post') {
    if (empty(trim($fields_str))) {
        if ($post_type === 'product') {
            $fields_str = 'thumbnail:100%, categories:100%, title:100%, price:50%, button:50%';
        } else {
            $fields_str = 'thumbnail:100%, categories:100%, title:100%, excerpt:100%, date:50%, button:50%';
        }
    }

    $raw_items = preg_split('/[\r\n,]+/', $fields_str);
    $parsed_fields = array();

    foreach ($raw_items as $item) {
        $item = trim($item);
        if (empty($item)) continue;

        $parts = explode(':', $item);
        $type = strtolower(trim($parts[0]));
        $width = '100%';
        $meta_key = '';
        $extra = '';

        if (in_array($type, array('meta', 'custom_field', 'acf'))) {
            $meta_key = isset($parts[1]) ? trim($parts[1]) : '';
            $width = isset($parts[2]) ? trim($parts[2]) : '100%';
            $extra = isset($parts[3]) ? trim($parts[3]) : '';
        } else {
            $width = isset($parts[1]) ? trim($parts[1]) : '100%';
            $extra = isset($parts[2]) ? trim($parts[2]) : '';
        }

        $parsed_fields[] = array(
            'raw' => $item,
            'type' => $type,
            'meta_key' => $meta_key,
            'width' => $width,
            'extra' => $extra,
        );
    }

    return $parsed_fields;
}

/**
 * Shortcode [vbc_post]: In danh sách bài viết / sản phẩm / CPT linh hoạt với trường tùy chọn & độ rộng cột
 */
function vbc_post_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        // Query Options
        'post_type' => 'post',
        'custom_post_type' => '',
        'ids' => '',
        'taxonomy' => '',
        'terms' => '',
        'operator' => 'IN',
        'posts_per_page' => '8',
        'limit' => '',
        'total' => '',
        'offset' => '',
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_key' => '',
        'meta_value' => '',
        'meta_compare' => '=',
        'post_status' => 'publish',

        // Layout Options
        'layout' => 'grid', // grid, list, table
        'columns' => '3', 'columns__md' => '2', 'columns__sm' => '1',
        'gap' => '24px', 'gap__md' => '', 'gap__sm' => '',
        'pagination' => 'none', // none, numeric

        // Fields Configuration
        'fields' => '',
        'image_size' => 'large',
        'image_height' => '220px',
        'image_fit' => 'cover',
        'title_tag' => 'h3',
        'title_size' => '18px',
        'title_color' => '',
        'title_hover_color' => '',
        'title_lines' => '2',
        'excerpt_length' => '20',
        'excerpt_color' => '#64748b',
        'price_color' => '#2563eb',
        'price_size' => '18px',
        'button_text' => 'Xem Chi Tiết',
        'button_variant' => 'primary', // primary, secondary, alert, success, outline, dark
        'button_icon' => 'lucide:arrow-right',
        'button_radius' => '8px',

        // Card Styling
        'card_bg' => '#ffffff',
        'card_padding' => '20px',
        'card_radius' => '16px',
        'card_border' => '1px solid #e2e8f0',
        'card_shadow' => '0 4px 15px rgba(0,0,0,0.03)',
        'card_hover' => 'translateY', // translateY, scale, shadow, none
        'class' => '',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    // 1. Chuẩn bị tham số Query
    $post_type = trim($atts['post_type']);
    if ($post_type === 'custom' && !empty($atts['custom_post_type'])) {
        $post_type = trim($atts['custom_post_type']);
    }

    $posts_per_page = intval($atts['posts_per_page']);
    if (!empty($atts['limit'])) $posts_per_page = intval($atts['limit']);
    if (!empty($atts['total'])) $posts_per_page = intval($atts['total']);
    if ($posts_per_page === 0) $posts_per_page = 8;

    $query_args = array(
        'post_type' => $post_type === 'any' ? 'any' : (strpos($post_type, ',') !== false ? array_map('trim', explode(',', $post_type)) : $post_type),
        'posts_per_page' => $posts_per_page,
        'post_status' => !empty($atts['post_status']) ? $atts['post_status'] : 'publish',
        'ignore_sticky_posts' => true,
    );

    // Loại trừ bài viết hiện tại nếu đang ở trang xem chi tiết
    if (is_singular() && empty($atts['ids'])) {
        $curr_id = get_the_ID();
        if ($curr_id > 0) {
            $query_args['post__not_in'] = array($curr_id);
        }
    }

    // Lọc theo IDs cụ thể
    if (!empty($atts['ids'])) {
        $id_list = array_filter(array_map('intval', explode(',', $atts['ids'])));
        if (!empty($id_list)) {
            $query_args['post__in'] = $id_list;
            if ($atts['orderby'] === 'date' || empty($atts['orderby'])) {
                $query_args['orderby'] = 'post__in';
            }
        }
    }

    // Sắp xếp
    if (!empty($atts['orderby'])) {
        $query_args['orderby'] = $atts['orderby'];
    }
    if (!empty($atts['order'])) {
        $query_args['order'] = strtoupper($atts['order']);
    }

    // Offset
    if (!empty($atts['offset']) && is_numeric($atts['offset'])) {
        $query_args['offset'] = intval($atts['offset']);
    }

    // Lọc theo Taxonomy
    if (!empty($atts['taxonomy']) && !empty($atts['terms'])) {
        $term_items = array_map('trim', explode(',', $atts['terms']));
        $tax_field = is_numeric($term_items[0]) ? 'term_id' : 'slug';
        $operator = in_array(strtoupper($atts['operator']), array('IN', 'AND', 'NOT IN')) ? strtoupper($atts['operator']) : 'IN';

        $query_args['tax_query'] = array(
            array(
                'taxonomy' => trim($atts['taxonomy']),
                'field' => $tax_field,
                'terms' => $term_items,
                'operator' => $operator,
            )
        );
    }

    // Lọc theo Meta Key / Meta Value
    if (!empty($atts['meta_key'])) {
        $query_args['meta_key'] = trim($atts['meta_key']);
        if ($atts['meta_value'] !== '') {
            $query_args['meta_value'] = trim($atts['meta_value']);
            $query_args['meta_compare'] = !empty($atts['meta_compare']) ? $atts['meta_compare'] : '=';
        }
    }

    // Phân trang
    if ($atts['pagination'] === 'numeric') {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);
        $query_args['paged'] = $paged;
    }

    $posts_query = new WP_Query($query_args);

    if (!$posts_query->have_posts()) {
        wp_reset_postdata();
        if (is_user_logged_in() && (is_admin() || is_customize_preview() || isset($_GET['uxb_iframe']))) {
            return '<div style="background:#f8fafc;padding:25px;text-align:center;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;font-size:14px;"><strong>[VBC Post]</strong> Không tìm thấy bài viết hoặc sản phẩm nào phù hợp với bộ lọc query.</div>';
        }
        return '';
    }

    // 2. Biên dịch CSS Grid & Card Responsive
    $random_id = wp_generate_password(8, false);
    $grid_class = 'vbc-pgrid-' . $random_id;

    $columns_desktop = !empty($atts['columns']) ? intval($atts['columns']) : 3;
    $columns_tablet = !empty($atts['columns__md']) ? intval($atts['columns__md']) : min(2, $columns_desktop);
    $columns_mobile = !empty($atts['columns__sm']) ? intval($atts['columns__sm']) : 1;
    $gap = !empty($atts['gap']) ? $atts['gap'] : '24px';

    $card_bg = !empty($atts['card_bg']) ? $atts['card_bg'] : '#ffffff';
    $card_padding = !empty($atts['card_padding']) ? $atts['card_padding'] : '20px';
    $card_radius = !empty($atts['card_radius']) ? $atts['card_radius'] : '16px';
    $card_border = !empty($atts['card_border']) ? $atts['card_border'] : '1px solid #e2e8f0';
    $card_shadow = !empty($atts['card_shadow']) ? $atts['card_shadow'] : '0 4px 15px rgba(0,0,0,0.03)';
    $card_hover = $atts['card_hover'];

    $css_rules = '';

    if ($atts['layout'] === 'grid') {
        $css_rules .= '.' . $grid_class . ' { display: grid; grid-template-columns: repeat(' . $columns_desktop . ', 1fr); gap: ' . $gap . '; width: 100%; box-sizing: border-box; } ';
        $css_rules .= '@media (max-width: 849px) { .' . $grid_class . ' { grid-template-columns: repeat(' . $columns_tablet . ', 1fr); } } ';
        $css_rules .= '@media (max-width: 549px) { .' . $grid_class . ' { grid-template-columns: repeat(' . $columns_mobile . ', 1fr); } } ';
        
        // Card styling for Grid
        $css_rules .= '.' . $grid_class . ' .vbc-post-card { ';
        $css_rules .= 'background: ' . $card_bg . '; ';
        $css_rules .= 'padding: ' . $card_padding . '; ';
        $css_rules .= 'border-radius: ' . $card_radius . '; ';
        $css_rules .= 'border: ' . $card_border . '; ';
        $css_rules .= 'box-shadow: ' . $card_shadow . '; ';
        $css_rules .= 'display: flex; flex-wrap: wrap; align-items: center; align-content: flex-start; gap: 8px 12px; ';
        $css_rules .= 'position: relative; overflow: hidden; box-sizing: border-box; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); ';
        $css_rules .= '} ';
    } elseif ($atts['layout'] === 'list') {
        $css_rules .= '.' . $grid_class . ' { display: flex; flex-direction: column; gap: ' . $gap . '; width: 100%; } ';
        
        // Card styling for List (Horizontal)
        $css_rules .= '.' . $grid_class . ' .vbc-post-card { ';
        $css_rules .= 'background: ' . $card_bg . '; ';
        $css_rules .= 'padding: ' . $card_padding . '; ';
        $css_rules .= 'border-radius: ' . $card_radius . '; ';
        $css_rules .= 'border: ' . $card_border . '; ';
        $css_rules .= 'box-shadow: ' . $card_shadow . '; ';
        $css_rules .= 'display: flex; flex-direction: row; align-items: center; gap: 20px; ';
        $css_rules .= 'position: relative; overflow: hidden; box-sizing: border-box; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); ';
        $css_rules .= '} ';
        $css_rules .= '@media (max-width: 680px) { .' . $grid_class . ' .vbc-post-card { flex-direction: column; align-items: stretch; } } ';
        $css_rules .= '.' . $grid_class . ' .vbc-post-list-body { flex: 1; display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; width: 100%; } ';
    }

    if ($card_hover === 'translateY') {
        $css_rules .= '.' . $grid_class . ' .vbc-post-card:hover { transform: translateY(-6px); box-shadow: 0 18px 35px rgba(0,0,0,0.09); } ';
    } elseif ($card_hover === 'scale') {
        $css_rules .= '.' . $grid_class . ' .vbc-post-card:hover { transform: scale(1.02); } ';
    } elseif ($card_hover === 'shadow') {
        $css_rules .= '.' . $grid_class . ' .vbc-post-card:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.12); } ';
    }

    // Hover zoom thumbnail image
    $css_rules .= '.' . $grid_class . ' .vbc-post-card:hover .vbc-post-thumb-img { transform: scale(1.06); } ';

    // Title styling
    $title_color_css = !empty($atts['title_color']) ? 'color: ' . esc_attr($atts['title_color']) . ' !important;' : 'color: #0f172a !important;';
    $title_hover_css = !empty($atts['title_hover_color']) ? 'color: ' . esc_attr($atts['title_hover_color']) . ' !important;' : 'color: #2563eb !important;';
    $css_rules .= '.' . $grid_class . ' .vbc-post-title-link { ' . $title_color_css . ' text-decoration: none; transition: color 0.2s; } ';
    $css_rules .= '.' . $grid_class . ' .vbc-post-title-link:hover { ' . $title_hover_css . ' } ';

    // Custom CSS
    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css_rules .= '.' . $grid_class . ' { ' . $raw_css . ' } ';
        } else {
            $css_rules .= str_replace('selector', '.' . $grid_class, $raw_css) . ' ';
        }
    }

    $css_rules = str_replace(array("\r", "\n"), ' ', $css_rules);
    $css_rules = preg_replace('/\s+/', ' ', $css_rules);

    $style_tag = '';
    if (vbc_should_inline_css()) {
        $style_tag = '<style>' . $css_rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) $vbc_accumulated_css = array();
        $vbc_accumulated_css[] = $css_rules;
    }

    // 3. Phân tích cấu hình các trường hiển thị (Fields Parser)
    $parsed_fields = vbc_parse_post_fields_config($atts['fields'], $post_type);

    // 4. Render danh sách bài viết
    $output = '';
    $class_attr_str = esc_attr(trim('vbc-posts-wrapper ' . $grid_class . ' ' . $atts['custom_class']));

    $title_tag = in_array($atts['title_tag'], array('h2', 'h3', 'h4', 'p', 'div')) ? $atts['title_tag'] : 'h3';
    $title_size = !empty($atts['title_size']) ? $atts['title_size'] : '18px';
    $title_lines = intval($atts['title_lines']);
    $price_color = !empty($atts['price_color']) ? $atts['price_color'] : '#2563eb';
    $price_size = !empty($atts['price_size']) ? $atts['price_size'] : '18px';
    $btn_variant = !empty($atts['button_variant']) ? $atts['button_variant'] : 'primary';
    $btn_radius = !empty($atts['button_radius']) ? $atts['button_radius'] : '8px';
    $btn_text_default = !empty($atts['button_text']) ? $atts['button_text'] : 'Xem Chi Tiết';
    $image_height = !empty($atts['image_height']) ? $atts['image_height'] : '220px';
    $image_fit = !empty($atts['image_fit']) ? $atts['image_fit'] : 'cover';

    // Kiểm tra xem người dùng có truyền template tùy chỉnh hợp lệ có placeholder không
    $trimmed_content = trim($content ?: '');
    $has_custom_template = !empty($trimmed_content) && (strpos($trimmed_content, '{{') !== false || strpos($trimmed_content, '[vbc_') !== false);

    if ($atts['layout'] === 'table') {
        // Render Dạng Bảng (Table List)
        $output .= '<div class="' . $class_attr_str . '" style="overflow-x: auto; width: 100%;">';
        $output .= '<table class="vbc-post-table" style="width: 100%; border-collapse: collapse; background: ' . $card_bg . '; border-radius: ' . $card_radius . '; overflow: hidden; box-shadow: ' . $card_shadow . ';">';
        $output .= '<thead><tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">';
        foreach ($parsed_fields as $f) {
            $col_name = ucfirst($f['type']);
            if ($f['type'] === 'thumbnail' || $f['type'] === 'image') $col_name = 'Hình ảnh';
            elseif ($f['type'] === 'title') $col_name = 'Tiêu đề';
            elseif ($f['type'] === 'price') $col_name = 'Giá';
            elseif ($f['type'] === 'excerpt' || $f['type'] === 'desc') $col_name = 'Mô tả';
            elseif ($f['type'] === 'date') $col_name = 'Ngày đăng';
            elseif ($f['type'] === 'author') $col_name = 'Tác giả';
            elseif ($f['type'] === 'categories' || $f['type'] === 'terms') $col_name = 'Danh mục';
            elseif ($f['type'] === 'button' || $f['type'] === 'read_more') $col_name = 'Thao tác';
            elseif (!empty($f['meta_key'])) $col_name = $f['meta_key'];

            $output .= '<th style="padding: 14px 18px; font-weight: 700; font-size: 13px; color: #475569; width: ' . esc_attr($f['width']) . ';">' . esc_html($col_name) . '</th>';
        }
        $output .= '</tr></thead><tbody>';

        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);

            $output .= '<tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">';

            foreach ($parsed_fields as $f) {
                $output .= '<td style="padding: 14px 18px; vertical-align: middle; width: ' . esc_attr($f['width']) . ';">';

                if ($f['type'] === 'thumbnail' || $f['type'] === 'image') {
                    $thumb_url = get_the_post_thumbnail_url($post_id, $atts['image_size']);
                    if ($thumb_url) {
                        $output .= '<a href="' . esc_url($permalink) . '"><img src="' . esc_url($thumb_url) . '" alt="' . esc_attr(get_the_title()) . '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; display: block;"></a>';
                    } else {
                        $output .= '<div style="width: 60px; height: 60px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i data-lucide="file-text" style="width: 22px; height: 22px; color: #94a3b8;"></i></div>';
                    }
                } elseif ($f['type'] === 'title') {
                    $output .= '<a href="' . esc_url($permalink) . '" style="font-weight: 700; color: #0f172a; text-decoration: none; font-size: 15px;">' . get_the_title() . '</a>';
                } elseif ($f['type'] === 'price') {
                    $price_html = '';
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($post_id);
                        if ($product) $price_html = $product->get_price_html();
                    }
                    if (empty($price_html)) {
                        $raw_price = get_post_meta($post_id, '_price', true) ?: (get_post_meta($post_id, 'price', true) ?: get_post_meta($post_id, 'gia', true));
                        if (is_numeric($raw_price)) $price_html = number_format($raw_price, 0, ',', '.') . ' ₫';
                        elseif (!empty($raw_price)) $price_html = esc_html($raw_price);
                    }
                    $output .= '<div style="font-weight: 700; color: ' . esc_attr($price_color) . ';">' . ($price_html ?: '-') . '</div>';
                } elseif ($f['type'] === 'date') {
                    $output .= '<span style="font-size: 13px; color: #64748b;">' . get_the_date('d/m/Y') . '</span>';
                } elseif ($f['type'] === 'author') {
                    $output .= '<span style="font-size: 13px; color: #64748b;">' . get_the_author() . '</span>';
                } elseif ($f['type'] === 'categories' || $f['type'] === 'terms') {
                    $tax = !empty($atts['taxonomy']) ? $atts['taxonomy'] : ($post_type === 'product' ? 'product_cat' : 'category');
                    $terms_list = get_the_terms($post_id, $tax);
                    if ($terms_list && !is_wp_error($terms_list)) {
                        $output .= '<span style="background: rgba(37,99,235,0.08); color: #2563eb; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">' . esc_html($terms_list[0]->name) . '</span>';
                    }
                } elseif ($f['type'] === 'button' || $f['type'] === 'read_more') {
                    $output .= '<a href="' . esc_url($permalink) . '" class="vbc-btn vbc-btn-' . esc_attr($btn_variant) . '" style="padding: 6px 14px; font-size: 13px; border-radius: ' . esc_attr($btn_radius) . '; text-decoration: none; display: inline-block;">' . esc_html($btn_text_default) . '</a>';
                } elseif ($f['type'] === 'meta' || $f['type'] === 'custom_field') {
                    $val = get_post_meta($post_id, $f['meta_key'], true);
                    $output .= '<span>' . esc_html($val . (!empty($f['extra']) ? ' ' . $f['extra'] : '')) . '</span>';
                }

                $output .= '</td>';
            }

            $output .= '</tr>';
        }

        $output .= '</tbody></table></div>';
    } else {
        // Render Dạng Lưới Grid hoặc Dạng List
        $output .= '<div class="' . $class_attr_str . '">';

        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            $post_id = get_the_ID();
            $permalink = get_permalink($post_id);
            $post_title = get_the_title();

            // Nếu người dùng cung cấp template tùy chỉnh có chứa placeholders
            if ($has_custom_template) {
                $raw_tpl = $trimmed_content;
                $thumb_url = get_the_post_thumbnail_url($post_id, $atts['image_size']) ?: '';
                $excerpt = get_the_excerpt();

                $placeholders = array(
                    '{{id}}' => $post_id,
                    '{{title}}' => $post_title,
                    '{{permalink}}' => $permalink,
                    '{{thumbnail}}' => $thumb_url,
                    '{{excerpt}}' => $excerpt,
                    '{{date}}' => get_the_date('d/m/Y'),
                    '{{author}}' => get_the_author(),
                );

                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($post_id);
                    if ($product) {
                        $placeholders['{{price}}'] = $product->get_price_html();
                    }
                }

                $item_content = strtr($raw_tpl, $placeholders);
                $item_content = preg_replace_callback('/\{\{meta:([a-zA-Z0-9_\-]+)\}\}/', function($m) use ($post_id) {
                    return get_post_meta($post_id, $m[1], true);
                }, $item_content);

                $output .= '<div class="vbc-post-card">' . do_shortcode($item_content) . '</div>';
                continue;
            }

            // Render theo Fields Config
            $output .= '<div class="vbc-post-card">';

            // Phân loại thumbnail và các field còn lại nếu ở chế độ list layout
            $is_list_layout = ($atts['layout'] === 'list');
            $thumb_rendered_in_list = false;

            if ($is_list_layout) {
                // Render Thumbnail bên trái
                foreach ($parsed_fields as $f) {
                    if ($f['type'] === 'thumbnail' || $f['type'] === 'image') {
                        $thumb_url = get_the_post_thumbnail_url($post_id, $atts['image_size']);
                        $thumb_w = !empty($f['width']) && $f['width'] !== '100%' ? $f['width'] : '240px';

                        $output .= '<div class="vbc-post-list-thumb" style="width: ' . esc_attr($thumb_w) . '; flex: 0 0 ' . esc_attr($thumb_w) . '; height: ' . esc_attr($image_height) . '; overflow: hidden; border-radius: ' . esc_attr($card_radius) . '; position: relative;">';
                        if ($thumb_url) {
                            $output .= '<a href="' . esc_url($permalink) . '" style="display: block; width: 100%; height: 100%;"><img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($post_title) . '" class="vbc-post-thumb-img" style="width: 100%; height: 100%; object-fit: ' . esc_attr($image_fit) . '; display: block; transition: transform 0.4s ease;"></a>';
                        } else {
                            $output .= '<div style="width: 100%; height: 100%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;"><i data-lucide="file-text" style="width: 40px; height: 40px; color: #cbd5e1;"></i></div>';
                        }
                        $output .= '</div>';
                        $thumb_rendered_in_list = true;
                        break;
                    }
                }
                $output .= '<div class="vbc-post-list-body">';
            }

            foreach ($parsed_fields as $f) {
                $f_type = $f['type'];
                $f_width = $f['width'];

                // Bỏ qua thumbnail nếu đã render ở list mode
                if ($is_list_layout && ($f_type === 'thumbnail' || $f_type === 'image') && $thumb_rendered_in_list) {
                    continue;
                }

                // Tính toán flex styling dựa theo độ rộng
                $flex_css = 'width: 100%; flex: 0 0 100%; box-sizing: border-box;';
                if ($f_width === '50%') {
                    $flex_css = 'flex: 1 1 calc(50% - 6px); max-width: calc(50% - 6px); min-width: 130px; box-sizing: border-box;';
                } elseif ($f_width === '33.33%' || $f_width === '33%') {
                    $flex_css = 'flex: 1 1 calc(33.33% - 8px); max-width: calc(33.33% - 8px); min-width: 100px; box-sizing: border-box;';
                } elseif ($f_width === '25%') {
                    $flex_css = 'flex: 1 1 calc(25% - 9px); max-width: calc(25% - 9px); min-width: 80px; box-sizing: border-box;';
                } elseif ($f_width === 'auto') {
                    $flex_css = 'flex: 0 0 auto; width: auto; box-sizing: border-box;';
                } elseif ($f_width !== '100%') {
                    $flex_css = 'width: ' . esc_attr($f_width) . '; flex: 0 0 ' . esc_attr($f_width) . '; box-sizing: border-box;';
                }

                if ($f_type === 'thumbnail' || $f_type === 'image') {
                    $thumb_url = get_the_post_thumbnail_url($post_id, $atts['image_size']);
                    $sale_badge_html = '';

                    // Kiểm tra WooCommerce Sale Badge
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($post_id);
                        if ($product && method_exists($product, 'is_on_sale') && $product->is_on_sale()) {
                            $sale_badge_html = '<span style="position: absolute; top: 12px; left: 12px; z-index: 2; background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 999px; box-shadow: 0 4px 10px rgba(239,68,68,0.3);">SALE</span>';
                        }
                    }

                    $output .= '<div class="vbc-post-field vbc-post-field-thumb" style="' . $flex_css . ' margin-bottom: 6px;">';
                    $output .= '<div class="vbc-post-thumb-wrap" style="position: relative; width: 100%; height: ' . esc_attr($image_height) . '; overflow: hidden; border-radius: ' . esc_attr($card_radius) . ';">';
                    $output .= $sale_badge_html;
                    if (empty($thumb_url)) {
                        $thumb_url = 'https://vieduenglish.com/wp-content/uploads/2026/08/mau-giao-1.png';
                    }
                    $output .= '<a href="' . esc_url($permalink) . '" style="display: block; width: 100%; height: 100%;"><img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($post_title) . '" class="vbc-post-thumb-img" style="width: 100%; height: 100%; object-fit: ' . esc_attr($image_fit) . '; display: block; transition: transform 0.4s ease;"></a>';
                    $output .= '</div></div>';
                } elseif ($f_type === 'title') {
                    $clamp_css = $title_lines > 0 ? 'display: -webkit-box; -webkit-line-clamp: ' . $title_lines . '; -webkit-box-orient: vertical; overflow: hidden;' : '';
                    $output .= '<div class="vbc-post-field vbc-post-field-title" style="' . $flex_css . ' margin: 4px 0 6px 0;">';
                    $output .= '<' . $title_tag . ' class="vbc-post-title" style="margin: 0; font-size: ' . esc_attr($title_size) . '; font-weight: 700; line-height: 1.4; ' . $clamp_css . '">';
                    $output .= '<a href="' . esc_url($permalink) . '" class="vbc-post-title-link">' . esc_html($post_title) . '</a>';
                    $output .= '</' . $title_tag . '></div>';
                } elseif ($f_type === 'price') {
                    $price_html = '';
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($post_id);
                        if ($product) {
                            $price_html = $product->get_price_html();
                        }
                    }
                    if (empty($price_html)) {
                        $raw_price = get_post_meta($post_id, '_price', true) ?: (get_post_meta($post_id, 'price', true) ?: get_post_meta($post_id, 'gia', true));
                        if (is_numeric($raw_price)) {
                            $price_html = number_format($raw_price, 0, ',', '.') . ' ₫';
                        } elseif (!empty($raw_price)) {
                            $price_html = esc_html($raw_price);
                        }
                    }

                    if (!empty($price_html)) {
                        $output .= '<div class="vbc-post-field vbc-post-field-price" style="' . $flex_css . '">';
                        $output .= '<div style="font-size: ' . esc_attr($price_size) . '; font-weight: 800; color: ' . esc_attr($price_color) . ';">' . $price_html . '</div>';
                        $output .= '</div>';
                    }
                } elseif ($f_type === 'excerpt' || $f_type === 'desc' || $f_type === 'description') {
                    $raw_excerpt = get_the_excerpt($post_id);
                    if (empty($raw_excerpt) || strpos($raw_excerpt, '[') !== false) {
                        $post_body = get_post_field('post_content', $post_id);
                        $clean_body = strip_shortcodes($post_body);
                        $clean_body = wp_strip_all_tags($clean_body);
                        $clean_body = preg_replace('/\s+/', ' ', trim($clean_body));
                        if (!empty($clean_body)) {
                            $raw_excerpt = $clean_body;
                        }
                    }
                    $trimmed_excerpt = wp_trim_words($raw_excerpt, intval($atts['excerpt_length']), '...');
                    if (!empty($trimmed_excerpt)) {
                        $output .= '<div class="vbc-post-field vbc-post-field-excerpt" style="' . $flex_css . '">';
                        $output .= '<p style="margin: 0 0 6px 0; font-size: 14px; line-height: 1.6; color: ' . esc_attr($atts['excerpt_color']) . ';">' . esc_html($trimmed_excerpt) . '</p>';
                        $output .= '</div>';
                    }
                } elseif ($f_type === 'date') {
                    $output .= '<div class="vbc-post-field vbc-post-field-date" style="' . $flex_css . '">';
                    $output .= '<div style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #94a3b8;"><i data-lucide="calendar" style="width: 14px; height: 14px;"></i> <span>' . get_the_date('d/m/Y') . '</span></div>';
                    $output .= '</div>';
                } elseif ($f_type === 'author') {
                    $output .= '<div class="vbc-post-field vbc-post-field-author" style="' . $flex_css . '">';
                    $output .= '<div style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #64748b;"><i data-lucide="user" style="width: 14px; height: 14px;"></i> <span>' . get_the_author() . '</span></div>';
                    $output .= '</div>';
                } elseif ($f_type === 'categories' || $f_type === 'terms' || $f_type === 'category') {
                    $tax = !empty($atts['taxonomy']) ? $atts['taxonomy'] : ($post_type === 'product' ? 'product_cat' : 'category');
                    $terms_list = get_the_terms($post_id, $tax);
                    if ($terms_list && !is_wp_error($terms_list)) {
                        $output .= '<div class="vbc-post-field vbc-post-field-terms" style="' . $flex_css . ' margin-bottom: 4px;">';
                        $output .= '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                        foreach (array_slice($terms_list, 0, 2) as $t) {
                            $output .= '<span style="background: rgba(37,99,235,0.08); color: #2563eb; font-size: 12px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;">' . esc_html($t->name) . '</span>';
                        }
                        $output .= '</div></div>';
                    }
                } elseif ($f_type === 'tags') {
                    $tag_tax = $post_type === 'product' ? 'product_tag' : 'post_tag';
                    $tags_list = get_the_terms($post_id, $tag_tax);
                    if ($tags_list && !is_wp_error($tags_list)) {
                        $output .= '<div class="vbc-post-field vbc-post-field-tags" style="' . $flex_css . '">';
                        $output .= '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                        foreach (array_slice($tags_list, 0, 3) as $tg) {
                            $output .= '<span style="background: #f1f5f9; color: #475569; font-size: 11px; padding: 2px 7px; border-radius: 4px;">#' . esc_html($tg->name) . '</span>';
                        }
                        $output .= '</div></div>';
                    }
                } elseif ($f_type === 'button' || $f_type === 'read_more' || $f_type === 'buy_now') {
                    $btn_label = !empty($f['extra']) ? $f['extra'] : $btn_text_default;
                    $output .= '<div class="vbc-post-field vbc-post-field-button" style="' . $flex_css . ' text-align: right;">';
                    $output .= '<a href="' . esc_url($permalink) . '" class="vbc-btn vbc-btn-' . esc_attr($btn_variant) . '" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 18px; font-size: 13px; font-weight: 700; border-radius: ' . esc_attr($btn_radius) . '; text-decoration: none; transition: all 0.2s;">';
                    $output .= '<span>' . esc_html($btn_label) . '</span>';
                    $output .= '<i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>';
                    $output .= '</a></div>';
                } elseif ($f_type === 'rating') {
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($post_id);
                        if ($product) {
                            $rating_html = wc_get_rating_html($product->get_average_rating());
                            if ($rating_html) {
                                $output .= '<div class="vbc-post-field vbc-post-field-rating" style="' . $flex_css . '">' . $rating_html . '</div>';
                            }
                        }
                    }
                } elseif ($f_type === 'sku') {
                    if (function_exists('wc_get_product')) {
                        $product = wc_get_product($post_id);
                        if ($product && method_exists($product, 'get_sku') && $product->get_sku()) {
                            $output .= '<div class="vbc-post-field vbc-post-field-sku" style="' . $flex_css . ' font-size: 12px; color: #94a3b8;">SKU: ' . esc_html($product->get_sku()) . '</div>';
                        }
                    }
                } elseif ($f_type === 'meta' || $f_type === 'custom_field') {
                    $meta_k = $f['meta_key'];
                    if (!empty($meta_k)) {
                        $val = get_post_meta($post_id, $meta_k, true);
                        if ($val !== '') {
                            $display_val = is_array($val) ? implode(', ', $val) : $val;
                            $suffix = !empty($f['extra']) ? ' ' . $f['extra'] : '';
                            $output .= '<div class="vbc-post-field vbc-post-field-meta vbc-meta-' . esc_attr($meta_k) . '" style="' . $flex_css . ' font-size: 14px; color: #334155;">';
                            $output .= '<strong>' . esc_html($meta_k) . ':</strong> ' . esc_html($display_val . $suffix);
                            $output .= '</div>';
                        }
                    }
                } elseif ($f_type === 'acf') {
                    $acf_k = $f['meta_key'];
                    if (!empty($acf_k)) {
                        $val = function_exists('get_field') ? get_field($acf_k, $post_id) : get_post_meta($post_id, $acf_k, true);
                        if ($val !== false && $val !== null && $val !== '') {
                            $display_val = is_array($val) ? (isset($val['url']) ? '<img src="' . esc_url($val['url']) . '" style="max-width: 100%; border-radius: 8px;">' : implode(', ', $val)) : esc_html($val);
                            $suffix = !empty($f['extra']) ? ' ' . $f['extra'] : '';
                            $output .= '<div class="vbc-post-field vbc-post-field-acf vbc-acf-' . esc_attr($acf_k) . '" style="' . $flex_css . ' font-size: 14px; color: #334155;">';
                            $output .= '<strong>' . esc_html($acf_k) . ':</strong> ' . $display_val . $suffix;
                            $output .= '</div>';
                        }
                    }
                }
            }

            if ($is_list_layout) {
                $output .= '</div>'; // End list-body
            }

            $output .= '</div>'; // End Card
        }

        $output .= '</div>'; // End Grid/List
    }

    // 5. Phân trang nếu được bật
    if ($atts['pagination'] === 'numeric' && $posts_query->max_num_pages > 1) {
        $big = 999999999;
        $pagination_html = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $posts_query->max_num_pages,
            'type' => 'list',
            'prev_text' => '&laquo; Trước',
            'next_text' => 'Sau &raquo;',
        ));
        $output .= '<div class="vbc-pagination-wrap" style="margin-top: 30px; text-align: center;">' . $pagination_html . '</div>';
    }

    wp_reset_postdata();

    // Nạp Lucide icons nếu có
    if (function_exists('vbc_enqueue_icon_pack')) {
        vbc_enqueue_icon_pack('lucide');
    }

    return $style_tag . $output;
}

/**
 * 3. HỆ THỐNG TRANG QUẢN TRỊ VIBECODE & XUẤT DỰ ÁN CHO ANTIGRAVITY
 */

// Đăng ký Menu Quản Trị trong WordPress Admin
add_action('admin_menu', 'vbc_register_admin_menu');
