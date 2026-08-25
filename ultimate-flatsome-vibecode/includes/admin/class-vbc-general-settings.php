<?php
/**
 * Ultimate Flatsome - General Website Settings & Shortcodes Management
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ultimate_Flatsome_General_Settings {

    /**
     * Singleton instance
     */
    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register shortcodes for both frontend and admin
        add_action( 'init', array( $this, 'register_shortcodes' ) );

        // Inject tracking scripts & custom code
        add_action( 'wp_head', array( $this, 'inject_header_scripts' ), 99 );
        add_action( 'wp_footer', array( $this, 'inject_footer_scripts' ), 99 );
    }

    /**
     * Danh sách định nghĩa các trường cấu hình và mapping với wp_options
     */
    public static function get_fields_config() {
        return array(
            // 1. Thông Tin Cơ Bản Website
            'site_title' => array(
                'label'       => __( 'Tên Website', 'vibecode' ),
                'option_key'  => 'blogname',
                'is_wp_core'  => true,
                'type'        => 'text',
                'group'       => 'general',
                'placeholder' => get_bloginfo( 'name' ),
                'shortcode'   => '[uf_info field="site_name"]',
                'description' => __( 'Đồng bộ 2 chiều với Tên website trong Cài đặt > Tổng quan (wp_options: blogname).', 'vibecode' ),
            ),
            'tagline' => array(
                'label'       => __( 'Khẩu Hiệu / Slogan', 'vibecode' ),
                'option_key'  => 'blogdescription',
                'is_wp_core'  => true,
                'type'        => 'text',
                'group'       => 'general',
                'placeholder' => get_bloginfo( 'description' ),
                'shortcode'   => '[uf_info field="tagline"]',
                'description' => __( 'Đồng bộ 2 chiều với Khẩu hiệu trong Cài đặt > Tổng quan (wp_options: blogdescription).', 'vibecode' ),
            ),
            'admin_email' => array(
                'label'       => __( 'Email Quản Trị Website', 'vibecode' ),
                'option_key'  => 'admin_email',
                'is_wp_core'  => true,
                'type'        => 'email',
                'group'       => 'general',
                'placeholder' => get_option( 'admin_email' ),
                'shortcode'   => '[uf_info field="admin_email"]',
                'description' => __( 'Đồng bộ 2 chiều với Địa chỉ email quản trị trong Cài đặt > Tổng quan (wp_options: admin_email).', 'vibecode' ),
            ),

            // 2. Thông Tin Liên Hệ & Thương Hiệu
            'company_name' => array(
                'label'       => __( 'Tên Công Ty / Doanh Nghiệp', 'vibecode' ),
                'option_key'  => 'uf_company_name',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => 'CÔNG TY CỔ PHẦN XYZ VIỆT NAM',
                'shortcode'   => '[uf_info field="company"]',
                'alias'       => '[uf_company]',
                'description' => __( 'Hiển thị trong chân trang, điều khoản pháp lý hoặc header.', 'vibecode' ),
            ),
            'phone' => array(
                'label'       => __( 'Hotline / Số Điện Thoại Chính', 'vibecode' ),
                'option_key'  => 'uf_phone',
                'legacy_key'  => 'vbc_brand_phone',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '1900 6364 09 hoặc 0912 345 678',
                'shortcode'   => '[uf_info field="phone"]',
                'alias'       => '[uf_phone link="true"]',
                'description' => __( 'Hỗ trợ thuộc tính link="true" để tạo thẻ bấm gọi <a href="tel:...">.', 'vibecode' ),
            ),
            'phone_2' => array(
                'label'       => __( 'Hotline Phụ / Kỹ Thuật', 'vibecode' ),
                'option_key'  => 'uf_phone_2',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '0987 654 321',
                'shortcode'   => '[uf_info field="phone_2"]',
                'alias'       => '[uf_phone_2]',
                'description' => __( 'Số điện thoại phụ hoặc hỗ trợ khẩn cấp.', 'vibecode' ),
            ),
            'zalo' => array(
                'label'       => __( 'Số Zalo / Link Zalo OA', 'vibecode' ),
                'option_key'  => 'uf_zalo',
                'legacy_key'  => 'vbc_brand_zalo',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '0912 345 678 hoặc https://zalo.me/0912345678',
                'shortcode'   => '[uf_info field="zalo"]',
                'alias'       => '[uf_zalo link="true"]',
                'description' => __( 'Hỗ trợ thuộc tính link="true" để tự sinh đường dẫn https://zalo.me/...', 'vibecode' ),
            ),
            'email' => array(
                'label'       => __( 'Email Liên Hệ / CSKH', 'vibecode' ),
                'option_key'  => 'uf_email',
                'legacy_key'  => 'vbc_brand_email',
                'type'        => 'email',
                'group'       => 'contact',
                'placeholder' => 'contact@domain.com',
                'shortcode'   => '[uf_info field="email"]',
                'alias'       => '[uf_email link="true"]',
                'description' => __( 'Hỗ trợ thuộc tính link="true" để tạo link <a href="mailto:...">.', 'vibecode' ),
            ),
            'address' => array(
                'label'       => __( 'Địa Chỉ Trụ Sở Chính', 'vibecode' ),
                'option_key'  => 'uf_address',
                'legacy_key'  => 'vbc_brand_address',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => 'P903, Tầng 9, Tòa nhà Diamond Plaza, 34 Lê Duẩn, Q.1, TP.HCM',
                'shortcode'   => '[uf_info field="address"]',
                'alias'       => '[uf_address]',
                'description' => __( 'Địa chỉ chính của doanh nghiệp hiển thị tại footer và liên hệ.', 'vibecode' ),
            ),
            'address_branch' => array(
                'label'       => __( 'Địa Chỉ Chi Nhánh / Văn Phòng 2', 'vibecode' ),
                'option_key'  => 'uf_address_branch',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => 'Tầng 5, Tòa nhà Keangnam Landmark 72, Nam Từ Liêm, Hà Nội',
                'shortcode'   => '[uf_info field="address_branch"]',
                'description' => __( 'Địa chỉ văn phòng đại diện hoặc chi nhánh miền Bắc/Trung.', 'vibecode' ),
            ),
            'working_hours' => array(
                'label'       => __( 'Thời Gian Làm Việc', 'vibecode' ),
                'option_key'  => 'uf_working_hours',
                'legacy_key'  => 'vbc_brand_hours',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '8:00 - 18:00 (Thứ 2 - Thứ 7)',
                'shortcode'   => '[uf_info field="working_hours"]',
                'description' => __( 'Khung thời gian tiếp khách hoặc phục vụ.', 'vibecode' ),
            ),
            'tax_code' => array(
                'label'       => __( 'Mã Số Thuế / Giấy Phép ĐKKD', 'vibecode' ),
                'option_key'  => 'uf_tax_code',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '0313589030 do Sở KH&ĐT TP.HCM cấp',
                'shortcode'   => '[uf_info field="tax_code"]',
                'description' => __( 'Mã số doanh nghiệp đăng ký tại Bộ Công Thương.', 'vibecode' ),
            ),
            'copyright' => array(
                'label'       => __( 'Bản Quyền Chân Trang (Copyright)', 'vibecode' ),
                'option_key'  => 'uf_copyright',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => '© {year} {site_name}. Bản quyền thuộc về Công ty.',
                'shortcode'   => '[uf_info field="copyright"]',
                'alias'       => '[uf_copyright]',
                'description' => __( 'Tự động thay thế {year} bằng năm hiện tại và {site_name} bằng tên website.', 'vibecode' ),
            ),
            'google_maps' => array(
                'label'       => __( 'Link Bản Đồ Google Maps (URL hoặc iframe embed)', 'vibecode' ),
                'option_key'  => 'uf_google_maps',
                'legacy_key'  => '',
                'type'        => 'text',
                'group'       => 'contact',
                'placeholder' => 'https://maps.google.com/?q=...',
                'shortcode'   => '[uf_info field="google_maps"]',
                'description' => __( 'Đường dẫn vị trí hoặc link chia sẻ bản đồ.', 'vibecode' ),
            ),

            // 3. Mạng Xã Hội (Social Media Links)
            'facebook' => array(
                'label'       => __( 'Facebook Fanpage / Profile URL', 'vibecode' ),
                'option_key'  => 'uf_facebook',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://facebook.com/kynaenglish',
                'shortcode'   => '[uf_info field="facebook"]',
                'description' => __( 'Link trang Facebook chính thức.', 'vibecode' ),
            ),
            'youtube' => array(
                'label'       => __( 'Kênh YouTube URL', 'vibecode' ),
                'option_key'  => 'uf_youtube',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://youtube.com/@kynaenglish',
                'shortcode'   => '[uf_info field="youtube"]',
                'description' => __( 'Link kênh video YouTube.', 'vibecode' ),
            ),
            'tiktok' => array(
                'label'       => __( 'Kênh TikTok URL', 'vibecode' ),
                'option_key'  => 'uf_tiktok',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://tiktok.com/@kynaenglish',
                'shortcode'   => '[uf_info field="tiktok"]',
                'description' => __( 'Link kênh TikTok.', 'vibecode' ),
            ),
            'instagram' => array(
                'label'       => __( 'Instagram URL', 'vibecode' ),
                'option_key'  => 'uf_instagram',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://instagram.com/kynaenglish',
                'shortcode'   => '[uf_info field="instagram"]',
                'description' => __( 'Link tài khoản Instagram.', 'vibecode' ),
            ),
            'messenger' => array(
                'label'       => __( 'Facebook Messenger URL', 'vibecode' ),
                'option_key'  => 'uf_messenger',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://m.me/kynaenglish',
                'shortcode'   => '[uf_info field="messenger"]',
                'description' => __( 'Link chat trực tiếp Facebook Messenger.', 'vibecode' ),
            ),
            'telegram' => array(
                'label'       => __( 'Kênh / Nhóm Telegram URL', 'vibecode' ),
                'option_key'  => 'uf_telegram',
                'type'        => 'text',
                'group'       => 'social',
                'placeholder' => 'https://t.me/kynaenglish',
                'shortcode'   => '[uf_info field="telegram"]',
                'description' => __( 'Link kênh hoặc chat Telegram.', 'vibecode' ),
            ),

            // 4. Mã Nhúng Scripts & Tracking (Header / Footer)
            'ga_id' => array(
                'label'       => __( 'Google Analytics (GA4 ID / GTM ID)', 'vibecode' ),
                'option_key'  => 'uf_ga_id',
                'type'        => 'text',
                'group'       => 'scripts',
                'placeholder' => 'G-XXXXXXXXXX hoặc GTM-XXXXXXX',
                'shortcode'   => '[uf_info field="ga_id"]',
                'description' => __( 'Tự động chèn mã đo lường Google Analytics / Tag Manager vào thẻ <head>.', 'vibecode' ),
            ),
            'fb_pixel_id' => array(
                'label'       => __( 'Facebook Pixel ID (Meta Pixel)', 'vibecode' ),
                'option_key'  => 'uf_fb_pixel_id',
                'type'        => 'text',
                'group'       => 'scripts',
                'placeholder' => 'Ví dụ: 123456789012345',
                'shortcode'   => '[uf_info field="fb_pixel_id"]',
                'description' => __( 'Tự động kích hoạt Meta Pixel theo dõi chuyển đổi.', 'vibecode' ),
            ),
            'header_scripts' => array(
                'label'       => __( 'Mã Nhúng Đầu Trang (Chèn vào trước </head>)', 'vibecode' ),
                'option_key'  => 'uf_header_scripts',
                'type'        => 'textarea',
                'group'       => 'scripts',
                'placeholder' => '<script> /* Code Javascript / CSS chèn vào Head */ </script>',
                'shortcode'   => '',
                'description' => __( 'Chèn mã xác minh Google Search Console, CSS tùy chỉnh hoặc Script bên thứ ba vào <head>.', 'vibecode' ),
            ),
            'footer_scripts' => array(
                'label'       => __( 'Mã Nhúng Cuối Trang (Chèn vào trước </body>)', 'vibecode' ),
                'option_key'  => 'uf_footer_scripts',
                'type'        => 'textarea',
                'group'       => 'scripts',
                'placeholder' => '<script> /* Code Chatbot, Livechat, Script thống kê */ </script>',
                'shortcode'   => '',
                'description' => __( 'Chèn mã Live Chat, Zalo Widget hoặc Script theo dõi sự kiện trước </body>.', 'vibecode' ),
            ),
        );
    }

    /**
     * Lấy giá trị cấu hình theo field key
     */
    public static function get_field_value( $key ) {
        $fields = self::get_fields_config();
        if ( ! isset( $fields[ $key ] ) ) {
            return get_option( $key, '' );
        }

        $config = $fields[ $key ];
        $val = get_option( $config['option_key'], '' );

        // Nếu trống, thử lấy từ legacy_key nếu có
        if ( empty( $val ) && ! empty( $config['legacy_key'] ) ) {
            $val = get_option( $config['legacy_key'], '' );
        }

        // Giá trị mặc định cho copyright nếu chưa cấu hình
        if ( $key === 'copyright' && empty( $val ) ) {
            $val = '© {year} ' . get_bloginfo( 'name' ) . '. All rights reserved.';
        }

        return $val;
    }

    /**
     * Đăng ký hệ thống Shortcodes [uf_info], [uf_phone], [uf_email], [uf_address], [uf_zalo], [uf_company], [uf_copyright], [uf_option]
     */
    public function register_shortcodes() {
        add_shortcode( 'uf_info', array( $this, 'render_info_shortcode' ) );
        add_shortcode( 'uf_field', array( $this, 'render_info_shortcode' ) );

        // Shortcodes tắt tiện lợi (Quick Aliases)
        add_shortcode( 'uf_phone', array( $this, 'render_phone_shortcode' ) );
        add_shortcode( 'uf_phone_2', array( $this, 'render_phone_2_shortcode' ) );
        add_shortcode( 'uf_email', array( $this, 'render_email_shortcode' ) );
        add_shortcode( 'uf_address', array( $this, 'render_address_shortcode' ) );
        add_shortcode( 'uf_zalo', array( $this, 'render_zalo_shortcode' ) );
        add_shortcode( 'uf_company', array( $this, 'render_company_shortcode' ) );
        add_shortcode( 'uf_copyright', array( $this, 'render_copyright_shortcode' ) );
        add_shortcode( 'uf_option', array( $this, 'render_option_shortcode' ) );
    }

    /**
     * Xử lý Shortcode chính [uf_info field="..."]
     */
    public function render_info_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'field'   => 'phone',
            'name'    => '',
            'link'    => 'false',
            'target'  => '_self',
            'class'   => '',
            'prefix'  => '',
            'suffix'  => '',
            'default' => '',
        ), $atts );

        $field = ! empty( $atts['name'] ) ? $atts['name'] : $atts['field'];
        $field = sanitize_key( $field );

        // Aliases mapping
        if ( $field === 'site_title' || $field === 'sitename' || $field === 'site' ) $field = 'site_title';
        if ( $field === 'tagline' || $field === 'slogan' || $field === 'description' ) $field = 'tagline';
        if ( $field === 'hotline' || $field === 'tel' ) $field = 'phone';
        if ( $field === 'company' ) $field = 'company_name';
        if ( $field === 'hours' ) $field = 'working_hours';
        if ( $field === 'maps' || $field === 'map' ) $field = 'google_maps';

        $val = self::get_field_value( $field );

        if ( empty( $val ) ) {
            $val = ! empty( $atts['default'] ) ? $atts['default'] : '';
        }

        if ( empty( $val ) ) {
            return '';
        }

        // Xử lý động cho copyright
        if ( $field === 'copyright' ) {
            $val = str_replace( '{year}', date( 'Y' ), $val );
            $val = str_replace( '{site_name}', get_bloginfo( 'name' ), $val );
        }

        $is_link = in_array( strtolower( $atts['link'] ), array( 'true', 'yes', '1' ), true );
        $class_attr = ! empty( $atts['class'] ) ? ' class="' . esc_attr( $atts['class'] ) . '"' : '';

        // Tự động định dạng liên kết nếu link="true"
        if ( $is_link ) {
            if ( $field === 'phone' || $field === 'phone_2' ) {
                $clean_num = preg_replace( '/[^0-9\+]/', '', $val );
                return $atts['prefix'] . '<a href="tel:' . esc_attr( $clean_num ) . '"' . $class_attr . '>' . esc_html( $val ) . '</a>' . $atts['suffix'];
            }
            if ( $field === 'email' || $field === 'admin_email' ) {
                return $atts['prefix'] . '<a href="mailto:' . esc_attr( $val ) . '"' . $class_attr . '>' . esc_html( $val ) . '</a>' . $atts['suffix'];
            }
            if ( $field === 'zalo' ) {
                $zalo_url = ( strpos( $val, 'http' ) === 0 ) ? $val : 'https://zalo.me/' . preg_replace( '/[^0-9]/', '', $val );
                return $atts['prefix'] . '<a href="' . esc_url( $zalo_url ) . '" target="_blank" rel="noopener noreferrer"' . $class_attr . '>' . esc_html( $val ) . '</a>' . $atts['suffix'];
            }
            if ( in_array( $field, array( 'facebook', 'youtube', 'tiktok', 'instagram', 'messenger', 'telegram', 'google_maps' ), true ) ) {
                $target = ! empty( $atts['target'] ) ? $atts['target'] : '_blank';
                return $atts['prefix'] . '<a href="' . esc_url( $val ) . '" target="' . esc_attr( $target ) . '" rel="noopener noreferrer"' . $class_attr . '>' . esc_html( $val ) . '</a>' . $atts['suffix'];
            }
        }

        return $atts['prefix'] . esc_html( $val ) . $atts['suffix'];
    }

    public function render_phone_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'phone';
        return $this->render_info_shortcode( $atts );
    }

    public function render_phone_2_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'phone_2';
        return $this->render_info_shortcode( $atts );
    }

    public function render_email_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'email';
        return $this->render_info_shortcode( $atts );
    }

    public function render_address_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'address';
        return $this->render_info_shortcode( $atts );
    }

    public function render_zalo_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'zalo';
        return $this->render_info_shortcode( $atts );
    }

    public function render_company_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'company_name';
        return $this->render_info_shortcode( $atts );
    }

    public function render_copyright_shortcode( $atts ) {
        if ( ! is_array( $atts ) ) $atts = array();
        $atts['field'] = 'copyright';
        return $this->render_info_shortcode( $atts );
    }

    public function render_option_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'key'     => 'blogname',
            'default' => '',
        ), $atts );

        $key = sanitize_key( $atts['key'] );
        // Danh sách đen không được xuất qua shortcode vì lý do bảo mật
        $blacklist = array( 'vbc_ftp_password', 'vbc_api_token', 'user_pass', 'auth_key', 'sec_salt' );
        if ( in_array( $key, $blacklist, true ) ) {
            return '';
        }

        $val = get_option( $key, $atts['default'] );
        if ( is_array( $val ) || is_object( $val ) ) {
            return '';
        }

        return esc_html( (string) $val );
    }

    /**
     * Tự động inject Google Analytics / GTM, Facebook Pixel & Custom Header Scripts
     */
    public function inject_header_scripts() {
        if ( is_admin() ) return;

        $ga_id = get_option( 'uf_ga_id', '' );
        if ( ! empty( $ga_id ) ) {
            if ( strpos( $ga_id, 'GTM-' ) === 0 ) {
                ?>
                <!-- Google Tag Manager (Ultimate Flatsome) -->
                <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                })(window,document,'script','dataLayer','<?php echo esc_js( $ga_id ); ?>');</script>
                <!-- End Google Tag Manager -->
                <?php
            } else {
                ?>
                <!-- Google Analytics GA4 (Ultimate Flatsome) -->
                <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
                <script>
                  window.dataLayer = window.dataLayer || [];
                  function gtag(){dataLayer.push(arguments);}
                  gtag('js', new Date());
                  gtag('config', '<?php echo esc_js( $ga_id ); ?>');
                </script>
                <!-- End Google Analytics GA4 -->
                <?php
            }
        }

        $fb_pixel = get_option( 'uf_fb_pixel_id', '' );
        if ( ! empty( $fb_pixel ) ) {
            ?>
            <!-- Meta Pixel Code (Ultimate Flatsome) -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js( $fb_pixel ); ?>');
            fbq('track', 'PageView');
            </script>
            <!-- End Meta Pixel Code -->
            <?php
        }

        $header_scripts = get_option( 'uf_header_scripts', '' );
        if ( ! empty( $header_scripts ) ) {
            echo "\n<!-- Ultimate Flatsome Custom Header Scripts -->\n" . $header_scripts . "\n<!-- End Custom Header Scripts -->\n";
        }
    }

    /**
     * Tự động inject Custom Footer Scripts
     */
    public function inject_footer_scripts() {
        if ( is_admin() ) return;

        $footer_scripts = get_option( 'uf_footer_scripts', '' );
        if ( ! empty( $footer_scripts ) ) {
            echo "\n<!-- Ultimate Flatsome Custom Footer Scripts -->\n" . $footer_scripts . "\n<!-- End Custom Footer Scripts -->\n";
        }
    }
}

// Khởi tạo Singleton
Ultimate_Flatsome_General_Settings::instance();
