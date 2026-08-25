<?php
/**
 * Ultimate Flatsome - Dynamic UX Block Template Builder for Post Types & Taxonomies
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ultimate_Flatsome_Template_Builder {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Flag to prevent recursion in post content rendering
     */
    private static $is_rendering_content = false;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // 1. Template Interception
        add_filter( 'template_include', array( $this, 'intercept_template' ), 999 );

        // 2. Register Dynamic Shortcodes
        add_action( 'init', array( $this, 'register_dynamic_shortcodes' ) );

        // 3. Register UX Builder Elements for Dynamic Tags
        add_action( 'ux_builder_setup', array( $this, 'register_ux_builder_elements' ) );

        // 4. Admin Save Settings & Actions
        add_action( 'admin_init', array( $this, 'handle_template_admin_actions' ) );

        // 5. Post & Term Meta Boxes
        add_action( 'add_meta_boxes', array( $this, 'register_post_metabox' ) );
        add_action( 'save_post', array( $this, 'save_post_metabox' ) );

        // Taxonomy Term Fields
        add_action( 'init', array( $this, 'register_taxonomy_term_fields' ), 20 );
    }

    /**
     * Lấy toàn bộ danh sách UX Blocks hiện có trong website
     */
    public static function get_ux_blocks_options() {
        $blocks = get_posts( array(
            'post_type'      => 'blocks',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        $options = array(
            '' => __( '-- Sử dụng Giao diện Flatsome Mặc định --', 'vibecode' ),
        );

        if ( ! empty( $blocks ) ) {
            foreach ( $blocks as $block ) {
                $options[ $block->ID ] = esc_html( $block->post_title ) . ' (ID: ' . $block->ID . ')';
            }
        }

        return $options;
    }

    /**
     * Lấy các quy tắc template đã lưu
     */
    public static function get_template_rules() {
        $rules = get_option( 'uf_template_rules', array() );
        return is_array( $rules ) ? $rules : array();
    }

    /**
     * 1. TEMPLATE INTERCEPTION ENGINE
     * Bắt filter template_include để render UX Block tùy biến nếu có gán template
     */
    public function intercept_template( $template ) {
        // Không can thiệp nếu đang ở trong WP Admin, REST API hoặc Flatsome UX Builder editor
        if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( isset( $_GET['uxb_iframe'] ) || isset( $_GET['app'] ) && $_GET['app'] === 'uxbuilder' ) ) {
            return $template;
        }

        $block_id = null;
        $context = '';

        // A. Xử lý Single Post Types (Bài viết, Trang, Sản phẩm, CPTs)
        if ( is_singular() ) {
            global $post;
            if ( ! $post ) return $template;

            // 1. Kiểm tra Post Meta Override trước
            $meta_block = get_post_meta( $post->ID, '_uf_custom_uxblock_template', true );
            if ( ! empty( $meta_block ) ) {
                $block_id = $meta_block;
                $context = 'single_post_meta';
            } else {
                // 2. Kiểm tra Primary Category Term Meta
                $post_type = $post->post_type;
                if ( $post_type === 'post' ) {
                    $cats = get_the_category( $post->ID );
                    if ( ! empty( $cats ) ) {
                        $cat_block = get_term_meta( $cats[0]->term_id, '_uf_custom_uxblock_template', true );
                        if ( ! empty( $cat_block ) ) {
                            $block_id = $cat_block;
                            $context = 'category_term_meta';
                        }
                    }
                }

                // 3. Kiểm tra Global Rule cho Post Type
                if ( empty( $block_id ) ) {
                    $rules = self::get_template_rules();
                    $rule_key = 'single_' . $post_type;
                    if ( ! empty( $rules[ $rule_key ] ) ) {
                        $block_id = $rules[ $rule_key ];
                        $context = 'single_' . $post_type;
                    }
                }
            }
        }
        // B. Xử lý Taxonomy & Archive Pages (Category, Tag, Product Category, Custom Taxonomies)
        elseif ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term && isset( $term->term_id ) ) {
                // 1. Kiểm tra Term Meta Override
                $term_block = get_term_meta( $term->term_id, '_uf_custom_uxblock_template', true );
                if ( ! empty( $term_block ) ) {
                    $block_id = $term_block;
                    $context = 'term_meta';
                } else {
                    // 2. Kiểm tra Global Rule cho Taxonomy
                    $rules = self::get_template_rules();
                    $tax_key = 'taxonomy_' . $term->taxonomy;
                    if ( ! empty( $rules[ $tax_key ] ) ) {
                        $block_id = $rules[ $tax_key ];
                        $context = 'taxonomy_' . $term->taxonomy;
                    }
                }
            }
        }

        // Nếu tìm thấy Block ID hợp lệ và UX Block tồn tại
        if ( ! empty( $block_id ) && get_post_status( $block_id ) === 'publish' ) {
            $this->render_custom_template_page( $block_id, $context );
            exit;
        }

        return $template;
    }

    /**
     * Xuất trang template chứa UX Block với Header & Footer chuẩn Flatsome
     */
    private function render_custom_template_page( $block_id, $context = '' ) {
        get_header();
        ?>
        <div id="uf-custom-template-wrapper" class="uf-template-wrapper uf-template-<?php echo esc_attr( $context ); ?>" style="width: 100%; min-height: 50vh;">
            <?php
            // Render UX Block nội dung template
            echo do_shortcode( '[block id="' . intval( $block_id ) . '"]' );
            ?>
        </div>
        <?php
        get_footer();
    }

    /**
     * 2. ĐĂNG KÝ HỆ THỐNG SHORTCODES ĐỘNG CHO UX BUILDER
     */
    public function register_dynamic_shortcodes() {
        add_shortcode( 'uf_post_title', array( $this, 'render_post_title' ) );
        add_shortcode( 'uf_post_content', array( $this, 'render_post_content' ) );
        add_shortcode( 'uf_post_excerpt', array( $this, 'render_post_excerpt' ) );
        add_shortcode( 'uf_post_thumbnail', array( $this, 'render_post_thumbnail' ) );
        add_shortcode( 'uf_post_meta', array( $this, 'render_post_meta' ) );
        add_shortcode( 'uf_post_author', array( $this, 'render_post_author' ) );
        add_shortcode( 'uf_post_comments', array( $this, 'render_post_comments' ) );
        add_shortcode( 'uf_post_navigation', array( $this, 'render_post_navigation' ) );
        add_shortcode( 'uf_post_terms', array( $this, 'render_post_terms' ) );
        add_shortcode( 'uf_breadcrumb', array( $this, 'render_breadcrumb' ) );
        add_shortcode( 'uf_archive_title', array( $this, 'render_archive_title' ) );
        add_shortcode( 'uf_archive_posts', array( $this, 'render_archive_posts' ) );
    }

    /**
     * Shortcode: [uf_post_title]
     */
    public function render_post_title( $atts ) {
        $atts = shortcode_atts( array(
            'tag'         => 'h1',
            'link'        => 'false',
            'color'       => '',
            'font_size'   => '',
            'font_weight' => '',
            'text_align'  => '',
            'margin'      => '',
            'class'       => '',
        ), $atts );

        $title = '';
        if ( is_singular() ) {
            $title = get_the_title();
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $title = single_term_title( '', false );
        } elseif ( is_archive() ) {
            $title = get_the_archive_title();
        } else {
            $title = get_the_title();
        }

        if ( empty( $title ) ) return '';

        $styles = array();
        if ( ! empty( $atts['color'] ) ) $styles[] = 'color:' . esc_attr( $atts['color'] );
        if ( ! empty( $atts['font_size'] ) ) $styles[] = 'font-size:' . esc_attr( $atts['font_size'] );
        if ( ! empty( $atts['font_weight'] ) ) $styles[] = 'font-weight:' . esc_attr( $atts['font_weight'] );
        if ( ! empty( $atts['text_align'] ) ) $styles[] = 'text-align:' . esc_attr( $atts['text_align'] );
        if ( ! empty( $atts['margin'] ) ) $styles[] = 'margin:' . esc_attr( $atts['margin'] );

        $style_attr = ! empty( $styles ) ? ' style="' . implode( ';', $styles ) . '"' : '';
        $class_attr = ! empty( $atts['class'] ) ? ' class="uf-post-title ' . esc_attr( $atts['class'] ) . '"' : ' class="uf-post-title"';
        $tag = in_array( strtolower( $atts['tag'] ), array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ), true ) ? strtolower( $atts['tag'] ) : 'h1';

        $inner_title = esc_html( $title );
        if ( in_array( strtolower( $atts['link'] ), array( 'true', 'yes', '1' ), true ) && is_singular() ) {
            $inner_title = '<a href="' . esc_url( get_permalink() ) . '">' . $inner_title . '</a>';
        }

        return '<' . $tag . $class_attr . $style_attr . '>' . $inner_title . '</' . $tag . '>';
    }

    /**
     * Shortcode: [uf_post_content]
     */
    public function render_post_content( $atts ) {
        if ( self::$is_rendering_content ) {
            return ''; // Ngăn chặn đệ quy vô hạn
        }

        self::$is_rendering_content = true;

        $atts = shortcode_atts( array(
            'class' => '',
        ), $atts );

        global $post;
        $content = '';

        if ( is_singular() && $post ) {
            $content = apply_filters( 'the_content', $post->post_content );
        } elseif ( is_admin() || ( isset( $_GET['app'] ) && $_GET['app'] === 'uxbuilder' ) ) {
            $content = '<div class="uf-preview-content" style="padding: 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; color: #64748b; line-height: 1.8;">'
                . '<p><strong>[Nội dung bài viết gốc (the_content) sẽ tự động hiển thị tại đây khi ra trang xem bài viết thực tế.]</strong></p>'
                . '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam...</p>'
                . '</div>';
        }

        self::$is_rendering_content = false;

        $class_attr = ! empty( $atts['class'] ) ? ' class="uf-post-content entry-content ' . esc_attr( $atts['class'] ) . '"' : ' class="uf-post-content entry-content"';
        return '<div' . $class_attr . '>' . $content . '</div>';
    }

    /**
     * Shortcode: [uf_post_excerpt]
     */
    public function render_post_excerpt( $atts ) {
        $atts = shortcode_atts( array(
            'length'    => '35',
            'color'     => '#64748b',
            'font_size' => '15px',
            'class'     => '',
        ), $atts );

        $excerpt = '';
        if ( is_singular() ) {
            $excerpt = get_the_excerpt();
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $excerpt = term_description();
        }

        if ( empty( $excerpt ) ) return '';

        return '<div class="uf-post-excerpt ' . esc_attr( $atts['class'] ) . '" style="color:' . esc_attr( $atts['color'] ) . '; font-size:' . esc_attr( $atts['font_size'] ) . '; line-height: 1.7;">'
            . wpautop( wp_strip_all_tags( $excerpt ) )
            . '</div>';
    }

    /**
     * Shortcode: [uf_post_thumbnail]
     */
    public function render_post_thumbnail( $atts ) {
        $atts = shortcode_atts( array(
            'size'          => 'large',
            'border_radius' => '16px',
            'box_shadow'    => '0 10px 30px rgba(0,0,0,0.08)',
            'aspect_ratio'  => '',
            'height'        => '',
            'fit'           => 'cover',
            'link'          => 'false',
            'class'         => '',
        ), $atts );

        $thumb_url = '';
        $alt_text = '';

        if ( is_singular() && has_post_thumbnail() ) {
            $thumb_id = get_post_thumbnail_id();
            $thumb_url = wp_get_attachment_image_url( $thumb_id, $atts['size'] );
            $alt_text = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) ?: get_the_title();
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            // Lấy ảnh danh mục Flatsome / WooCommerce nếu có
            $thumb_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
            if ( $thumb_id ) {
                $thumb_url = wp_get_attachment_image_url( $thumb_id, $atts['size'] );
                $alt_text = $term->name;
            }
        }

        // Placeholder cho UX Builder preview nếu chưa có ảnh
        if ( empty( $thumb_url ) && ( is_admin() || ( isset( $_GET['app'] ) && $_GET['app'] === 'uxbuilder' ) ) ) {
            $thumb_url = 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&h=630&fit=crop';
            $alt_text = 'Featured Image Preview';
        }

        if ( empty( $thumb_url ) ) return '';

        $styles = array(
            'display: block',
            'width: 100%',
            'border-radius: ' . esc_attr( $atts['border_radius'] ),
            'box-shadow: ' . esc_attr( $atts['box_shadow'] ),
            'object-fit: ' . esc_attr( $atts['fit'] ),
        );

        if ( ! empty( $atts['height'] ) ) $styles[] = 'height:' . esc_attr( $atts['height'] );
        if ( ! empty( $atts['aspect_ratio'] ) ) $styles[] = 'aspect-ratio:' . esc_attr( $atts['aspect_ratio'] );

        $style_attr = ' style="' . implode( ';', $styles ) . '"';
        $img_html = '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $alt_text ) . '"' . $style_attr . ' class="uf-featured-image ' . esc_attr( $atts['class'] ) . '" />';

        if ( in_array( strtolower( $atts['link'] ), array( 'true', 'yes', '1' ), true ) && is_singular() ) {
            return '<a href="' . esc_url( get_permalink() ) . '">' . $img_html . '</a>';
        }

        return $img_html;
    }

    /**
     * Shortcode: [uf_post_meta]
     */
    public function render_post_meta( $atts ) {
        $atts = shortcode_atts( array(
            'type'        => 'date',
            'field'       => '',
            'icon'        => 'yes',
            'color'       => '#64748b',
            'font_size'   => '13.5px',
            'class'       => '',
            'date_format' => get_option( 'date_format' ),
        ), $atts );

        $type = sanitize_key( $atts['type'] );
        $val = '';
        $icon_html = '';

        if ( $type === 'date' ) {
            $val = get_the_date( $atts['date_format'] );
            $icon_html = '<span class="dashicons dashicons-calendar-alt" style="font-size:15px; width:15px; height:15px; margin-right:4px;"></span>';
        } elseif ( $type === 'author' ) {
            $val = get_the_author();
            $icon_html = '<span class="dashicons dashicons-admin-users" style="font-size:15px; width:15px; height:15px; margin-right:4px;"></span>';
        } elseif ( $type === 'categories' || $type === 'category' ) {
            $categories = get_the_category();
            if ( ! empty( $categories ) ) {
                $cat_links = array();
                foreach ( $categories as $cat ) {
                    $cat_links[] = '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '" style="color:inherit; font-weight:600;">' . esc_html( $cat->name ) . '</a>';
                }
                $val = implode( ', ', $cat_links );
            }
            $icon_html = '<span class="dashicons dashicons-category" style="font-size:15px; width:15px; height:15px; margin-right:4px;"></span>';
        } elseif ( $type === 'comments_count' ) {
            $val = sprintf( _n( '%s bình luận', '%s bình luận', get_comments_number(), 'vibecode' ), number_format_i18n( get_comments_number() ) );
            $icon_html = '<span class="dashicons dashicons-admin-comments" style="font-size:15px; width:15px; height:15px; margin-right:4px;"></span>';
        } elseif ( $type === 'custom' && ! empty( $atts['field'] ) ) {
            $val = get_post_meta( get_the_ID(), sanitize_key( $atts['field'] ), true );
        }

        if ( empty( $val ) ) return '';

        $show_icon = in_array( strtolower( $atts['icon'] ), array( 'true', 'yes', '1' ), true );

        return '<span class="uf-post-meta-item ' . esc_attr( $atts['class'] ) . '" style="display:inline-flex; align-items:center; color:' . esc_attr( $atts['color'] ) . '; font-size:' . esc_attr( $atts['font_size'] ) . '; margin-right:16px;">'
            . ( $show_icon ? $icon_html : '' ) . $val
            . '</span>';
    }

    /**
     * Shortcode: [uf_post_author]
     */
    public function render_post_author( $atts ) {
        $atts = shortcode_atts( array(
            'avatar_size'   => '70',
            'show_bio'      => 'yes',
            'bg_color'      => '#f8fafc',
            'border_radius' => '16px',
            'padding'       => '24px',
            'box_shadow'    => '0 4px 15px rgba(0,0,0,0.03)',
            'class'         => '',
        ), $atts );

        $author_id = get_the_author_meta( 'ID' );
        if ( ! $author_id ) {
            $author_id = 1;
        }

        $author_name = get_the_author_meta( 'display_name', $author_id );
        $author_bio = get_the_author_meta( 'description', $author_id );
        $author_avatar = get_avatar( $author_id, intval( $atts['avatar_size'] ), '', $author_name, array( 'class' => 'uf-author-avatar-img' ) );
        $author_posts_url = get_author_posts_url( $author_id );

        if ( empty( $author_bio ) ) {
            $author_bio = __( 'Tác giả chuyên mục và biên tập viên nội dung website.', 'vibecode' );
        }

        ob_start();
        ?>
        <div class="uf-author-box <?php echo esc_attr( $atts['class'] ); ?>" style="background: <?php echo esc_attr( $atts['bg_color'] ); ?>; border-radius: <?php echo esc_attr( $atts['border_radius'] ); ?>; padding: <?php echo esc_attr( $atts['padding'] ); ?>; box-shadow: <?php echo esc_attr( $atts['box_shadow'] ); ?>; display: flex; gap: 20px; align-items: center; margin: 30px 0;">
            <div style="flex-shrink: 0; border-radius: 50%; overflow: hidden; width: <?php echo intval( $atts['avatar_size'] ); ?>px; height: <?php echo intval( $atts['avatar_size'] ); ?>px;">
                <?php echo $author_avatar; ?>
            </div>
            <div>
                <div style="font-size: 13px; text-transform: uppercase; font-weight: 700; color: #2563eb; letter-spacing: 0.5px;"><?php _e('Tác Giả', 'vibecode'); ?></div>
                <h4 style="margin: 4px 0 8px 0; font-size: 18px; font-weight: 800; color: #0f172a;">
                    <a href="<?php echo esc_url( $author_posts_url ); ?>" style="color: inherit; text-decoration: none;"><?php echo esc_html( $author_name ); ?></a>
                </h4>
                <?php if ( in_array( strtolower( $atts['show_bio'] ), array( 'yes', 'true', '1' ), true ) ) : ?>
                    <p style="margin: 0; font-size: 13.5px; color: #64748b; line-height: 1.6;"><?php echo esc_html( $author_bio ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [uf_post_comments]
     */
    public function render_post_comments( $atts ) {
        if ( ! is_singular() ) {
            return '';
        }

        ob_start();
        echo '<div class="uf-comments-wrapper" style="margin-top: 40px; padding-top: 30px; border-top: 1px solid #e2e8f0;">';
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Shortcode: [uf_post_navigation]
     */
    public function render_post_navigation( $atts ) {
        if ( ! is_singular( 'post' ) ) return '';

        $prev_post = get_previous_post();
        $next_post = get_next_post();

        if ( ! $prev_post && ! $next_post ) return '';

        ob_start();
        ?>
        <div class="uf-post-nav-wrapper" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 40px 0;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px;">
                <?php if ( $prev_post ) : ?>
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">← <?php _e('Bài Trước', 'vibecode'); ?></div>
                    <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" style="font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; display: block; margin-top: 4px;">
                        <?php echo esc_html( $prev_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; text-align: right;">
                <?php if ( $next_post ) : ?>
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php _e('Bài Kế Tiếp', 'vibecode'); ?> →</div>
                    <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" style="font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; display: block; margin-top: 4px;">
                        <?php echo esc_html( $next_post->post_title ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Shortcode: [uf_post_terms]
     */
    public function render_post_terms( $atts ) {
        $atts = shortcode_atts( array(
            'taxonomy'      => 'category',
            'bg_color'      => '#eff6ff',
            'color'         => '#2563eb',
            'border_radius' => '20px',
            'padding'       => '4px 14px',
            'font_size'     => '12.5px',
            'class'         => '',
        ), $atts );

        $terms = get_the_terms( get_the_ID(), sanitize_key( $atts['taxonomy'] ) );
        if ( empty( $terms ) || is_wp_error( $terms ) ) return '';

        $html = '<div class="uf-post-terms-list ' . esc_attr( $atts['class'] ) . '" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">';
        foreach ( $terms as $term ) {
            $link = get_term_link( $term );
            $html .= '<a href="' . esc_url( $link ) . '" style="background:' . esc_attr( $atts['bg_color'] ) . '; color:' . esc_attr( $atts['color'] ) . '; border-radius:' . esc_attr( $atts['border_radius'] ) . '; padding:' . esc_attr( $atts['padding'] ) . '; font-size:' . esc_attr( $atts['font_size'] ) . '; font-weight:700; text-decoration:none; display:inline-block; transition:all 0.2s;">' . esc_html( $term->name ) . '</a>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Shortcode: [uf_breadcrumb]
     */
    public function render_breadcrumb( $atts ) {
        if ( function_exists( 'flatsome_breadcrumb' ) ) {
            ob_start();
            flatsome_breadcrumb();
            return '<div class="uf-breadcrumb-wrapper" style="margin-bottom: 20px;">' . ob_get_clean() . '</div>';
        }
        return '';
    }

    /**
     * Shortcode: [uf_archive_title]
     */
    public function render_archive_title( $atts ) {
        $atts = shortcode_atts( array(
            'tag'              => 'h1',
            'show_description' => 'yes',
            'color'            => '#0f172a',
            'font_size'        => '32px',
            'text_align'       => 'left',
            'class'            => '',
        ), $atts );

        $title = '';
        $desc = '';

        if ( is_category() || is_tag() || is_tax() ) {
            $title = single_term_title( '', false );
            $desc = term_description();
        } elseif ( is_archive() ) {
            $title = get_the_archive_title();
            $desc = get_the_archive_description();
        } else {
            $title = __( 'Chuyên Mục Bài Viết', 'vibecode' );
            $desc = __( 'Danh sách các bài viết mới nhất và kiến thức chuyên sâu.', 'vibecode' );
        }

        $tag = in_array( strtolower( $atts['tag'] ), array( 'h1', 'h2', 'h3', 'div' ), true ) ? strtolower( $atts['tag'] ) : 'h1';

        $html = '<div class="uf-archive-header ' . esc_attr( $atts['class'] ) . '" style="text-align:' . esc_attr( $atts['text_align'] ) . '; margin-bottom: 30px;">';
        $html .= '<' . $tag . ' style="color:' . esc_attr( $atts['color'] ) . '; font-size:' . esc_attr( $atts['font_size'] ) . '; font-weight:800; margin:0 0 10px 0;">' . esc_html( $title ) . '</' . $tag . '>';

        if ( in_array( strtolower( $atts['show_description'] ), array( 'yes', 'true', '1' ), true ) && ! empty( $desc ) ) {
            $html .= '<div style="color:#64748b; font-size:15px; line-height:1.6; max-width:800px;">' . wpautop( $desc ) . '</div>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Shortcode: [uf_archive_posts]
     * Hiển thị danh sách bài viết / sản phẩm thuộc Category / Taxonomy đang xem kèm phân trang Flatsome
     */
    public function render_archive_posts( $atts ) {
        $atts = shortcode_atts( array(
            'columns'      => '3',
            'columns__md'  => '2',
            'columns__sm'  => '1',
            'image_height' => '220px',
            'card_radius'  => '16px',
            'class'        => '',
        ), $atts );

        global $wp_query;

        // Nếu trong UX Builder preview và không có query
        if ( ! have_posts() && ( is_admin() || ( isset( $_GET['app'] ) && $_GET['app'] === 'uxbuilder' ) ) ) {
            return do_shortcode( '[vbc_post post_type="post" posts_per_page="6" columns="' . esc_attr( $atts['columns'] ) . '" image_height="' . esc_attr( $atts['image_height'] ) . '" card_radius="' . esc_attr( $atts['card_radius'] ) . '"]' );
        }

        ob_start();
        ?>
        <div class="uf-archive-posts-container <?php echo esc_attr( $atts['class'] ); ?>">
            <div class="row row-small" style="display: flex; flex-wrap: wrap;">
                <?php
                if ( have_posts() ) :
                    while ( have_posts() ) : the_post();
                        $col_span = 12 / intval( $atts['columns'] );
                        $thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
                        ?>
                        <div class="col medium-6 small-12 large-<?php echo esc_attr( $col_span ); ?>" style="margin-bottom: 30px;">
                            <div class="uf-post-card" style="background: #ffffff; border-radius: <?php echo esc_attr( $atts['card_radius'] ); ?>; overflow: hidden; border: 1px solid #e2e8f0; height: 100%; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: all 0.2s;">
                                <?php if ( $thumb_url ) : ?>
                                    <div style="height: <?php echo esc_attr( $atts['image_height'] ); ?>; overflow: hidden;">
                                        <a href="<?php the_permalink(); ?>">
                                            <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div style="padding: 22px; flex-grow: 1; display: flex; flex-direction: column;">
                                    <div style="font-size: 12.5px; color: #64748b; margin-bottom: 8px;">
                                        <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php echo get_the_date(); ?>
                                    </div>
                                    <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 700; line-height: 1.4;">
                                        <a href="<?php the_permalink(); ?>" style="color: #0f172a; text-decoration: none;"><?php the_title(); ?></a>
                                    </h3>
                                    <div style="font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 16px; flex-grow: 1;">
                                        <?php echo wp_trim_words( get_the_excerpt(), 18, '...' ); ?>
                                    </div>
                                    <div>
                                        <a href="<?php the_permalink(); ?>" style="color: #2563eb; font-weight: 700; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                            <?php _e('Đọc Tiếp', 'vibecode'); ?> →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                else :
                    echo '<p style="padding: 20px; color: #64748b;">' . __( 'Không tìm thấy bài viết nào trong danh mục này.', 'vibecode' ) . '</p>';
                endif;
                ?>
            </div>

            <!-- Flatsome Standard Pagination -->
            <div style="margin-top: 30px; text-align: center;">
                <?php
                echo paginate_links( array(
                    'total'     => $wp_query->max_num_pages,
                    'prev_text' => '← ' . __( 'Trang trước', 'vibecode' ),
                    'next_text' => __( 'Trang sau', 'vibecode' ) . ' →',
                ) );
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * 3. ĐĂNG KÝ UX BUILDER ELEMENTS CHO DYNAMIC TEMPLATE TAGS
     */
    public function register_ux_builder_elements() {
        if ( ! function_exists( 'add_ux_builder_post_type' ) ) return;

        // Cho phép UX Builder kích hoạt trên post_type 'blocks'
        add_ux_builder_post_type( 'blocks' );

        if ( ! function_exists( 'ux_builder_category' ) || ! function_exists( 'ux_builder_element' ) ) return;

        // Tạo Category riêng trong UX Builder
        ux_builder_category( 'ultimate-flatsome-templates', array(
            'title'    => __( 'Ultimate Flatsome Templates', 'vibecode' ),
            'priority' => 1,
        ) );

        // 1. Element: Dynamic Post Title
        ux_builder_element( 'uf_post_title', array(
            'name'       => __( 'Post Title (Tiêu Đề)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'info'       => '{{ tag }}',
            'options'    => array(
                'tag' => array(
                    'type'    => 'select',
                    'heading' => 'HTML Tag',
                    'default' => 'h1',
                    'options' => array(
                        'h1' => 'H1',
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                    ),
                ),
                'color' => array(
                    'type'    => 'colorpicker',
                    'heading' => 'Màu chữ',
                    'default' => '',
                ),
                'font_size' => array(
                    'type'    => 'textfield',
                    'heading' => 'Cỡ chữ',
                    'default' => '32px',
                ),
                'text_align' => array(
                    'type'    => 'select',
                    'heading' => 'Canh lề',
                    'default' => 'left',
                    'options' => array(
                        'left'   => 'Trái',
                        'center' => 'Giữa',
                        'right'  => 'Phải',
                    ),
                ),
            ),
        ) );

        // 2. Element: Dynamic Post Content
        ux_builder_element( 'uf_post_content', array(
            'name'       => __( 'Post Content (Nội Dung)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'options'    => array(),
        ) );

        // 3. Element: Dynamic Post Thumbnail
        ux_builder_element( 'uf_post_thumbnail', array(
            'name'       => __( 'Featured Image (Ảnh Đại Diện)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'options'    => array(
                'border_radius' => array(
                    'type'    => 'textfield',
                    'heading' => 'Bo góc (border-radius)',
                    'default' => '16px',
                ),
                'aspect_ratio' => array(
                    'type'    => 'textfield',
                    'heading' => 'Tỷ lệ khung hình (vd: 16/9, 4/3)',
                    'default' => '16/9',
                ),
            ),
        ) );

        // 4. Element: Dynamic Author Box
        ux_builder_element( 'uf_post_author', array(
            'name'       => __( 'Author Box (Tác Giả)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'options'    => array(
                'bg_color' => array(
                    'type'    => 'colorpicker',
                    'heading' => 'Màu nền',
                    'default' => '#f8fafc',
                ),
                'border_radius' => array(
                    'type'    => 'textfield',
                    'heading' => 'Bo góc',
                    'default' => '16px',
                ),
            ),
        ) );

        // 5. Element: Dynamic Comments Box
        ux_builder_element( 'uf_post_comments', array(
            'name'       => __( 'Comments Box (Bình Luận)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'options'    => array(),
        ) );

        // 6. Element: Archive Category Posts Grid
        ux_builder_element( 'uf_archive_posts', array(
            'name'       => __( 'Archive Posts Grid (Lưới Bài Viết Category)', 'vibecode' ),
            'category'   => 'ultimate-flatsome-templates',
            'thumbnail'  => '',
            'options'    => array(
                'columns' => array(
                    'type'    => 'slider',
                    'heading' => 'Số cột',
                    'default' => 3,
                    'min'     => 1,
                    'max'     => 4,
                ),
                'image_height' => array(
                    'type'    => 'textfield',
                    'heading' => 'Chiều cao ảnh',
                    'default' => '220px',
                ),
            ),
        ) );
    }

    /**
     * 4. XỬ LÝ LƯU SETTINGS & TẠO SAMPLE TEMPLATES TỪ ADMIN
     */
    public function handle_template_admin_actions() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        // A. Lưu bảng quy tắc Template Rules
        if ( isset( $_POST['vbc_action'] ) && $_POST['vbc_action'] === 'save_template_rules' ) {
            check_admin_referer( 'uf_save_templates_nonce', 'uf_templates_nonce' );

            $rules = array();
            if ( ! empty( $_POST['uf_template_rules'] ) && is_array( $_POST['uf_template_rules'] ) ) {
                foreach ( $_POST['uf_template_rules'] as $key => $val ) {
                    $rules[ sanitize_key( $key ) ] = intval( $val );
                }
            }

            update_option( 'uf_template_rules', $rules );
            wp_redirect( add_query_arg( array( 'page' => 'ultimate-flatsome', 'tab' => 'templates', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        // B. Tạo Template Mẫu 1-Click (Sample Template Generator)
        if ( isset( $_GET['uf_action'] ) && $_GET['uf_action'] === 'create_sample_template' ) {
            check_admin_referer( 'uf_create_sample_template' );
            $type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'single_post';

            $new_block_id = $this->create_sample_ux_block( $type );
            if ( $new_block_id ) {
                // Tự động gán làm template mặc định
                $rules = self::get_template_rules();
                if ( $type === 'single_post' ) $rules['single_post'] = $new_block_id;
                if ( $type === 'category' ) $rules['taxonomy_category'] = $new_block_id;
                update_option( 'uf_template_rules', $rules );

                // Chuyển thẳng vào UX Builder để chỉnh sửa ngay lập tức!
                $ux_builder_url = admin_url( 'post.php?post=' . $new_block_id . '&action=edit&app=uxbuilder' );
                wp_redirect( $ux_builder_url );
                exit;
            }
        }
    }

    /**
     * Tạo UX Block mẫu với bố cục chuẩn UX Builder
     */
    public function create_sample_ux_block( $type = 'single_post' ) {
        if ( $type === 'single_post' ) {
            $title = 'Template Mẫu - Bài Viết Chuẩn UX Builder';
            $content = '[section bg_color="#f8fafc" padding="50px" padding__sm="30px"]'
                . '[row width="custom" custom_width="1140px"]'
                . '[col span="12" align="center"]'
                . '[uf_post_terms taxonomy="category" bg_color="#eff6ff" color="#2563eb" border_radius="20px"]'
                . '[uf_post_title tag="h1" font_size="36px" font_weight="800" color="#0f172a" text_align="center" margin="0 0 16px 0"]'
                . '[div align="center" style="display:flex; justify-content:center; gap:16px; margin-bottom:20px;"]'
                . '[uf_post_meta type="author" icon="yes"]'
                . '[uf_post_meta type="date" icon="yes"]'
                . '[uf_post_meta type="comments_count" icon="yes"]'
                . '[/div]'
                . '[/col]'
                . '[/row]'
                . '[/section]'
                . '[section padding="60px" padding__sm="30px"]'
                . '[row width="custom" custom_width="1140px"]'
                . '[col span="8" span__md="12" span__sm="12"]'
                . '[uf_post_thumbnail border_radius="16px" aspect_ratio="16/9" margin="0 0 30px 0"]'
                . '[uf_post_content]'
                . '[uf_post_author avatar_size="80" bg_color="#f8fafc" border_radius="16px" padding="24px"]'
                . '[uf_post_navigation]'
                . '[uf_post_comments]'
                . '[/col]'
                . '[col span="4" span__md="12" span__sm="12"]'
                . '[vbc_card variant="glass" border_radius="16px" padding="24px" margin="0 0 25px 0"]'
                . '[vbc_h4 font_size="18px" font_weight="700" margin="0 0 15px 0"]Bài Viết Nổi Bật[/vbc_h4]'
                . '[vbc_post post_type="post" posts_per_page="4" columns="1" layout="list" image_height="70px"]'
                . '[/vbc_card]'
                . '[/col]'
                . '[/row]'
                . '[/section]';
        } else {
            $title = 'Template Mẫu - Chuyên Mục Category Chuẩn UX Builder';
            $content = '[section bg_color="#090d16" dark="true" padding="60px" padding__sm="40px"]'
                . '[row width="custom" custom_width="1140px"]'
                . '[col span="12" align="center"]'
                . '[uf_breadcrumb]'
                . '[uf_archive_title tag="h1" font_size="38px" color="#ffffff" text_align="center"]'
                . '[/col]'
                . '[/row]'
                . '[/section]'
                . '[section padding="60px" padding__sm="30px"]'
                . '[row width="custom" custom_width="1140px"]'
                . '[col span="12"]'
                . '[uf_archive_posts columns="3" image_height="220px" card_radius="16px"]'
                . '[/col]'
                . '[/row]'
                . '[/section]';
        }

        $block_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'blocks',
        ) );

        return $block_id;
    }

    /**
     * 5. POST & TERM METABOX OVERRIDES
     */
    public function register_post_metabox() {
        $post_types = get_post_types( array( 'public' => true ), 'names' );
        foreach ( $post_types as $pt ) {
            if ( $pt === 'blocks' ) continue;
            add_meta_box(
                'uf_template_override_metabox',
                __( 'Ultimate Flatsome - Layout Template (UX Block)', 'vibecode' ),
                array( $this, 'render_post_metabox_html' ),
                $pt,
                'side',
                'high'
            );
        }
    }

    public function render_post_metabox_html( $post ) {
        wp_nonce_field( 'uf_save_post_metabox', 'uf_post_metabox_nonce' );
        $current_val = get_post_meta( $post->ID, '_uf_custom_uxblock_template', true );
        $options = self::get_ux_blocks_options();
        ?>
        <p style="font-size: 12px; color: #64748b; margin-top: 0;">
            <?php _e('Ghi đè UX Block Template riêng cho bài viết này:', 'vibecode'); ?>
        </p>
        <select name="_uf_custom_uxblock_template" style="width: 100%;">
            <?php foreach ( $options as $val => $lbl ) : ?>
                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_val, $val ); ?>>
                    <?php echo esc_html( $lbl ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ( ! empty( $current_val ) ) : ?>
            <p style="margin-top: 8px;">
                <a href="<?php echo esc_url( admin_url( 'post.php?post=' . intval( $current_val ) . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-small" style="display: inline-flex; align-items: center; gap: 4px;">
                    <span class="dashicons dashicons-edit" style="font-size: 14px; margin-top: 2px;"></span>
                    <?php _e('Sửa UX Block trong UX Builder', 'vibecode'); ?>
                </a>
            </p>
        <?php endif; ?>
        <?php
    }

    public function save_post_metabox( $post_id ) {
        if ( ! isset( $_POST['uf_post_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['uf_post_metabox_nonce'], 'uf_save_post_metabox' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['_uf_custom_uxblock_template'] ) ) {
            $val = intval( $_POST['_uf_custom_uxblock_template'] );
            if ( $val > 0 ) {
                update_post_meta( $post_id, '_uf_custom_uxblock_template', $val );
            } else {
                delete_post_meta( $post_id, '_uf_custom_uxblock_template' );
            }
        }
    }

    /**
     * Đăng ký trường chọn Template trong Taxonomy Term Edit Screen
     */
    public function register_taxonomy_term_fields() {
        $taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
        foreach ( $taxonomies as $tax ) {
            add_action( $tax . '_edit_form_fields', array( $this, 'render_term_edit_field' ), 10, 2 );
            add_action( 'edited_' . $tax, array( $this, 'save_term_edit_field' ), 10, 2 );
        }
    }

    public function render_term_edit_field( $term, $taxonomy ) {
        $current_val = get_term_meta( $term->term_id, '_uf_custom_uxblock_template', true );
        $options = self::get_ux_blocks_options();
        ?>
        <tr class="form-field">
            <th scope="row"><label for="_uf_custom_uxblock_template"><?php _e('UX Block Template (Ultimate Flatsome)', 'vibecode'); ?></label></th>
            <td>
                <select name="_uf_custom_uxblock_template" id="_uf_custom_uxblock_template" style="max-width: 400px;">
                    <?php foreach ( $options as $val => $lbl ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_val, $val ); ?>>
                            <?php echo esc_html( $lbl ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php _e('Chọn UX Block làm giao diện hiển thị cho danh mục này. Có thể chỉnh sửa kéo thả trong Flatsome UX Builder.', 'vibecode'); ?></p>
                <?php if ( ! empty( $current_val ) ) : ?>
                    <p style="margin-top: 8px;">
                        <a href="<?php echo esc_url( admin_url( 'post.php?post=' . intval( $current_val ) . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-small">
                            <?php _e('✏️ Sửa UX Block trong UX Builder', 'vibecode'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    public function save_term_edit_field( $term_id, $tt_id ) {
        if ( ! current_user_can( 'edit_terms' ) ) return;
        if ( isset( $_POST['_uf_custom_uxblock_template'] ) ) {
            $val = intval( $_POST['_uf_custom_uxblock_template'] );
            if ( $val > 0 ) {
                update_term_meta( $term_id, '_uf_custom_uxblock_template', $val );
            } else {
                delete_term_meta( $term_id, '_uf_custom_uxblock_template' );
            }
        }
    }
}

// Khởi tạo Singleton
Ultimate_Flatsome_Template_Builder::instance();
