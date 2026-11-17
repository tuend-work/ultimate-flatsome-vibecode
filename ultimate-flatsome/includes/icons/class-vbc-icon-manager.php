<?php
/**
 * Ultimate Flatsome VibeCode - Icon Libraries & Picker Modal
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    if (isset($_GET['uxb_iframe']) || isset($_GET['ux-builder']) || isset($_GET['app']) || (is_admin() && isset($_GET['page']) && $_GET['page'] === 'uxbuilder')) {
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
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                try {
                    // 1. Kiểm tra các svg[data-lucide] có tên icon không khớp với class lucide-{name}
                    var svgs = document.querySelectorAll('svg[data-lucide]');
                    for (var i = 0; i < svgs.length; i++) {
                        var svg = svgs[i];
                        var iconName = svg.getAttribute('data-lucide');
                        if (iconName) {
                            iconName = iconName.replace(/^icon:/, '').replace(/^svg:/, '').trim();
                            if (!svg.classList.contains('lucide-' + iconName)) {
                                var newI = document.createElement('i');
                                newI.setAttribute('data-lucide', iconName);
                                if (svg.className) newI.className = svg.className;
                                if (svg.parentNode) {
                                    svg.parentNode.replaceChild(newI, svg);
                                }
                            }
                        }
                    }
                } catch(e) {}

                // 2. Render tất cả thẻ i[data-lucide]
                try {
                    var icons = document.querySelectorAll('i[data-lucide]');
                    if (icons.length > 0) {
                        lucide.createIcons();
                    }
                } catch(e) {}
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
                clearTimeout(lucideTimeout);
                lucideTimeout = setTimeout(triggerLucide, 30);
            });
            var targetNode = document.body || document.documentElement;
            if (targetNode) {
                observer.observe(targetNode, { 
                    childList: true, 
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['data-lucide', 'class']
                });
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    if (document.body) {
                        observer.observe(document.body, { 
                            childList: true, 
                            subtree: true,
                            attributes: true,
                            attributeFilter: ['data-lucide', 'class']
                        });
                    }
                });
            }
        }

        // Lắng nghe postMessage từ builder top window để refresh ngay lập tức
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'vbc_refresh_icons') {
                triggerLucide();
            }
        });
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
    if (did_action('ux_builder_enqueue_scripts') || (is_admin() && (isset($_GET['post']) || isset($_GET['page'])))) {
        $is_ux = true;
    }
    
    if (!$is_ux) {
        return;
    }

    // Đảm bảo nạp wp.media
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
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

    $assets_url = defined('VBC_PLUGIN_URL') ? VBC_PLUGIN_URL . 'assets/' : plugins_url('../../assets/', __FILE__);
    $picker_ver = defined('VBC_VERSION') ? VBC_VERSION : time();
    wp_enqueue_script('vbc-icon-picker', $assets_url . 'vbc-icon-picker.js', array('jquery', 'media-views', 'media-models'), $picker_ver, true);
    wp_enqueue_style('vbc-icon-picker', $assets_url . 'vbc-icon-picker.css', array(), $picker_ver);
}

// Đăng ký UX Builder & Shortcode handler cho [vbc_icon]
add_action('ux_builder_setup', 'vbc_register_icon_ux_builder');
function vbc_register_icon_ux_builder() {
    if (!function_exists('add_ux_builder_shortcode')) {
        return;
    }

    add_ux_builder_shortcode('vbc_icon', array(
        'name'     => 'VBC Icon (Vector SVG)',
        'category' => 'VibeCode HTML',
        'wrap'     => false,
        'template' => '<span id="{{ shortcode.options.id || shortcode.$id }}" class="vbc-icon-wrap {{ shortcode.options.custom_class }}" ng-style="{\'display\': shortcode.options.display || \'inline-flex\', \'align-items\': \'center\', \'justify-content\': \'center\', \'color\': shortcode.options.color, \'background-color\': (shortcode.options.background_color || shortcode.options.bg_color || shortcode.options.bg), \'width\': shortcode.options.width, \'height\': shortcode.options.height, \'min-width\': (shortcode.options.min_width || shortcode.options.minWidth), \'padding\': shortcode.options.padding, \'margin\': shortcode.options.margin, \'border\': shortcode.options.border, \'border-radius\': (shortcode.options.border_radius || shortcode.options.borderRadius), \'box-shadow\': (shortcode.options.box_shadow || shortcode.options.boxShadow), \'font-size\': (shortcode.options.size || \'24px\')}"><span ng-if="shortcode.options.raw_svg || shortcode.options.svg" ng-bind-html="(shortcode.options.raw_svg || shortcode.options.svg) | html"></span><img ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && (shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0))" ng-src="{{ shortcode.options.icon }}" style="width:100%;height:100%;object-fit:contain;" /><span ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && !(shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0)) && (shortcode.options.icon_type === \'material\' || (shortcode.options.name && shortcode.options.name.indexOf(\'_\') !== -1 && shortcode.options.name.indexOf(\'-\') === -1))" class="material-symbols-outlined" style="font-size:inherit;line-height:1;">{{ shortcode.options.name || shortcode.options.icon || \'security\' }}</span><i ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && !(shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0)) && (shortcode.options.icon_type === \'fontawesome\' || (shortcode.options.name && shortcode.options.name.indexOf(\'fa-\') !== -1))" class="{{ (shortcode.options.name && shortcode.options.name.indexOf(\'fa-\') !== -1) ? shortcode.options.name : (\'fa-solid fa-\' + (shortcode.options.name || shortcode.options.icon || \'shield-halved\')) }}" style="font-size:inherit;line-height:1;"></i><i ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && !(shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0)) && (shortcode.options.icon_type === \'remix\' || (shortcode.options.name && shortcode.options.name.indexOf(\'ri-\') !== -1))" class="{{ (shortcode.options.name && shortcode.options.name.indexOf(\'ri-\') !== -1) ? shortcode.options.name : (\'ri-\' + (shortcode.options.name || shortcode.options.icon || \'shield-check-line\')) }}" style="font-size:inherit;line-height:1;"></i><i ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && !(shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0)) && (shortcode.options.icon_type === \'phosphor\' || (shortcode.options.name && shortcode.options.name.indexOf(\'ph\') !== -1))" class="{{ (shortcode.options.name && shortcode.options.name.indexOf(\'ph\') !== -1) ? shortcode.options.name : (\'ph ph-\' + (shortcode.options.name || shortcode.options.icon || \'shield-check\')) }}" style="font-size:inherit;line-height:1;"></i><i ng-if="!shortcode.options.raw_svg && !shortcode.options.svg && !(shortcode.options.icon && (shortcode.options.icon.indexOf(\'http\') === 0 || shortcode.options.icon.indexOf(\'data:\') === 0 || shortcode.options.icon.indexOf(\'/\') === 0)) && (shortcode.options.icon_type === \'lucide\' || (!shortcode.options.icon_type && (!shortcode.options.name || (shortcode.options.name.indexOf(\'fa-\') === -1 && shortcode.options.name.indexOf(\'ri-\') === -1 && shortcode.options.name.indexOf(\'ph\') === -1))))" data-lucide="{{ shortcode.options.name || shortcode.options.icon || \'shield-check\' }}" style="width:1em;height:1em;display:inline-block;"></i></span>',
            'media_group' => array(
                'type' => 'group',
                'heading' => 'Chọn Icon Vector & SVG',
                'options' => array(
                    'name' => array(
                        'type' => 'textfield',
                        'heading' => 'Tên Icon Vector (Lucide / FA)',
                        'default' => 'shield-check',
                        'description' => 'Lucide: globe, ticket-percent, sparkles, smartphone, shield-check, heart, star, phone, map-pin, credit-card... (Hoặc FontAwesome: fa-solid fa-star)',
                    ),
                    'icon_type' => array(
                        'type' => 'select',
                        'heading' => 'Bộ Icon (Pack)',
                        'default' => 'lucide',
                        'options' => array(
                            'lucide' => 'Lucide Icons (Vector SVG sạch, hiện đại)',
                            'fontawesome' => 'FontAwesome 6',
                            'remix' => 'Remix Icon',
                            'material' => 'Material Symbols',
                            'phosphor' => 'Phosphor Icons',
                        ),
                    ),
                    'icon' => array(
                        'type' => 'image',
                        'heading' => 'Hoặc chọn Ảnh / SVG từ Media Library',
                        'default' => '',
                        'description' => 'Nhấp vào nút để mở Thư viện: Tải file từ máy, Thư viện có sẵn hoặc chọn từ kho SVG Icon trực quan.',
                    ),
                    'raw_svg' => array(
                        'type' => 'textarea',
                        'heading' => 'Mã Inline SVG Tùy Biến (Tùy chọn)',
                        'default' => '',
                        'description' => 'Dán mã <svg>...</svg> trực tiếp nếu có icon SVG thương hiệu riêng biệt.',
                    ),
                ),
            ),
            'styling_group' => array(
                'type' => 'group',
                'heading' => 'Định Dạng & Hiệu Ứng (Design)',
                'options' => array(
                    'size' => array(
                        'type' => 'textfield',
                        'heading' => 'Kích thước Icon (Size)',
                        'responsive' => true,
                        'default' => '24px',
                        'description' => 'Ví dụ: 20px, 24px, 32px, 48px...',
                    ),
                    'color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu sắc Icon',
                        'default' => '#ff5b00',
                    ),
                    'hover_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu Icon khi Hover',
                        'default' => '',
                    ),
                    'background_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu nền huy hiệu (Badge BG)',
                        'default' => '',
                    ),
                    'bg_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu nền (bg_color)',
                        'default' => '',
                    ),
                    'width' => array(
                        'type' => 'textfield',
                        'heading' => 'Độ rộng badge (Width)',
                        'default' => '',
                        'description' => 'Ví dụ: 48px, 56px, auto',
                    ),
                    'height' => array(
                        'type' => 'textfield',
                        'heading' => 'Chiều cao badge (Height)',
                        'default' => '',
                        'description' => 'Ví dụ: 48px, 56px, auto',
                    ),
                    'min_width' => array(
                        'type' => 'textfield',
                        'heading' => 'Độ rộng tối thiểu (Min Width)',
                        'default' => '',
                    ),
                    'padding' => array(
                        'type' => 'textfield',
                        'heading' => 'Padding huy hiệu',
                        'responsive' => true,
                        'default' => '',
                        'description' => 'Ví dụ: 12px hoặc 10px 16px.',
                    ),
                    'border_radius' => array(
                        'type' => 'textfield',
                        'heading' => 'Bo góc huy hiệu (Border Radius)',
                        'default' => '',
                        'description' => 'Ví dụ: 50% (tròn), 12px, 16px...',
                    ),
                    'border' => array(
                        'type' => 'textfield',
                        'heading' => 'Viền huy hiệu (Border)',
                        'default' => '',
                        'description' => 'Ví dụ: 1px solid #e2e8f0',
                    ),
                    'border_color' => array(
                        'type' => 'colorpicker',
                        'heading' => 'Màu viền huy hiệu',
                        'default' => '',
                    ),
                    'box_shadow' => array(
                        'type' => 'textfield',
                        'heading' => 'Đổ bóng (Box Shadow)',
                        'default' => '',
                    ),
                    'display' => array(
                        'type' => 'select',
                        'heading' => 'Kiểu hiển thị (Display)',
                        'default' => 'inline-flex',
                        'options' => array(
                            'inline-flex' => 'Inline Flex (Canh giữa tự động)',
                            'flex' => 'Flex Block',
                            'inline-block' => 'Inline Block',
                            'block' => 'Block',
                        ),
                    ),
                    'margin' => array(
                        'type' => 'textfield',
                        'heading' => 'Margin',
                        'responsive' => true,
                        'default' => '',
                    ),
                    'custom_class' => array(
                        'type' => 'textfield',
                        'heading' => 'CSS Class',
                        'default' => '',
                    ),
                    'custom_css' => array(
                        'type' => 'textarea',
                        'heading' => 'Custom CSS (Dùng "selector")',
                        'default' => '',
                    ),
                ),
            ),
        ),
    ));
}

/**
 * Từ điển các biểu tượng Lucide phổ biến nhất được nạp sẵn SVG trực tiếp từ Server-side.
 * Giúp hiển thị tức thì 0ms, không nháy hình (FOIT) và đạt điểm Google PageSpeed tối đa.
 */
function vbc_get_inline_svg_icon($name, $sz = '24px') {
    $clean_name = strtolower(trim(str_replace(array('lucide:', 'lucide-'), '', $name)));

    static $svg_map = array(
        'check'          => '<polyline points="20 6 9 17 4 12"/>',
        'check-circle'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'shield-check'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/>',
        'arrow-right'    => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'arrow-left'     => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'chevron-right'  => '<path d="m9 18 6-6-6-6"/>',
        'chevron-left'   => '<path d="m15 18-6-6 6-6"/>',
        'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
        'chevron-up'     => '<path d="m18 15-6-6-6 6"/>',
        'phone'          => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'           => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'star'           => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'heart'          => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'shopping-cart'  => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        'shopping-bag'   => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'clock'          => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'map-pin'        => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
        'calendar'       => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
        'user'           => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'search'         => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'menu'           => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
        'x'              => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'plus'           => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'minus'          => '<path d="M5 12h14"/>',
        'zap'            => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'flame'          => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'sparkles'       => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
        'award'          => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
        'gift'           => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>',
        'lock'           => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'eye'            => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'play'           => '<polygon points="6 3 20 12 6 21 6 3"/>',
        'external-link'  => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
    );

    if (isset($svg_map[$clean_name])) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . esc_attr($sz) . '" height="' . esc_attr($sz) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-' . esc_attr($clean_name) . '" style="display:inline-block;vertical-align:middle;">' . $svg_map[$clean_name] . '</svg>';
    }

    return false;
}

function vbc_icon_shortcode_renderer($atts) {
    $atts = shortcode_atts(array(
        'icon'          => '',
        'icon_type'     => '',
        'image_id'      => '',
        'img_attachment'=> '',
        'image_url'     => '',
        'img_url'       => '',
        'svg_url'       => '',
        'icon_value'    => '',
        'name'          => '',
        'name_lucide'   => '',
        'name_fa'       => '',
        'name_ri'       => '',
        'name_material' => '',
        'name_phosphor' => '',
        'name_custom'   => '',
        'pack'          => '',
        'mode'          => '',

        // Styling
        'size'          => '24px',
        'size__md'      => '',
        'size__sm'      => '',
        'color'         => '',
        'hover_color'   => '',
        'background_color' => '',
        'bg_color'      => '',
        'bg'            => '',
        'width'         => '',
        'width__md'     => '',
        'width__sm'     => '',
        'height'        => '',
        'height__md'    => '',
        'height__sm'    => '',
        'min_width'     => '',
        'max_width'     => '',
        'padding'       => '',
        'padding__md'   => '',
        'padding__sm'   => '',
        'border_radius' => '',
        'border'        => '',
        'border_color'  => '',
        'box_shadow'    => '',
        'display'       => 'inline-flex',
        'margin'        => '',
        'margin__md'    => '',
        'margin__sm'    => '',
        'raw_svg'       => '',
        'svg'           => '',
        'class'         => '',
        'custom_class'  => '',
        'custom_css'    => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    // 1. Nhận diện nguồn icon/ảnh
    $raw_icon = '';
    // Nếu truyền ảnh URL hoặc attachment ID từ WordPress Media
    if (!empty($atts['icon']) && (is_numeric($atts['icon']) || strpos($atts['icon'], 'http') === 0 || strpos($atts['icon'], 'data:') === 0 || strpos($atts['icon'], '/') === 0)) {
        $raw_icon = trim($atts['icon']);
    } elseif (!empty($atts['image_id'])) {
        $raw_icon = trim($atts['image_id']);
    } elseif (!empty($atts['image_url'])) {
        $raw_icon = trim($atts['image_url']);
    } elseif (!empty($atts['img_attachment'])) {
        $raw_icon = trim($atts['img_attachment']);
    } elseif (!empty($atts['img_url'])) {
        $raw_icon = trim($atts['img_url']);
    } elseif (!empty($atts['name'])) {
        $raw_icon = trim($atts['name']);
    } elseif (!empty($atts['icon'])) {
        $raw_icon = trim($atts['icon']);
    } elseif (!empty($atts['svg_url'])) {
        $raw_icon = trim($atts['svg_url']);
    } elseif (!empty($atts['icon_value'])) {
        $raw_icon = trim($atts['icon_value']);
    } elseif (!empty($atts['name_lucide'])) {
        $raw_icon = trim($atts['name_lucide']);
    } elseif (!empty($atts['name_fa'])) {
        $raw_icon = trim($atts['name_fa']);
    } elseif (!empty($atts['name_ri'])) {
        $raw_icon = trim($atts['name_ri']);
    } elseif (!empty($atts['name_material'])) {
        $raw_icon = trim($atts['name_material']);
    } elseif (!empty($atts['name_phosphor'])) {
        $raw_icon = trim($atts['name_phosphor']);
    } elseif (!empty($atts['name_custom'])) {
        $raw_icon = trim($atts['name_custom']);
    }

    if (empty($raw_icon)) {
        $raw_icon = 'shield-check';
    }

    $image_src = '';
    $icon_name = '';
    $icon_pack = !empty($atts['icon_type']) ? strtolower(trim($atts['icon_type'])) : 'lucide';
    $is_image = false;

    if (is_numeric($raw_icon)) {
        // Attachment ID từ Media Library
        $image_src = wp_get_attachment_url(intval($raw_icon));
        $is_image = true;
    } elseif (strpos($raw_icon, 'http://') === 0 || strpos($raw_icon, 'https://') === 0 || strpos($raw_icon, 'data:') === 0 || strpos($raw_icon, '//') === 0 || strpos($raw_icon, '/wp-content/') !== false) {
        // URL ảnh trực tiếp
        $image_src = $raw_icon;
        $is_image = true;
    } elseif (strpos($raw_icon, 'img:') === 0) {
        $image_src = substr($raw_icon, 4);
        $is_image = true;
    } else {
        // Vector SVG Icon
        $clean_str = preg_replace('/^(icon|svg):/', '', $raw_icon);
        $icon_name = trim($clean_str);

        if (!empty($atts['icon_type'])) {
            $icon_pack = strtolower(trim($atts['icon_type']));
        } elseif (strpos($icon_name, 'fa-') !== false) {
            $icon_pack = 'fontawesome';
        } elseif (strpos($icon_name, 'ri-') !== false) {
            $icon_pack = 'remix';
        } elseif (strpos($icon_name, 'ph') !== false) {
            $icon_pack = 'phosphor';
        } elseif (strpos($icon_name, '_') !== false && strpos($icon_name, '-') === false) {
            $icon_pack = 'material';
        } else {
            $icon_pack = 'lucide';
            $icon_name = str_replace(array('lucide:', 'lucide-'), '', $icon_name);
        }

        vbc_enqueue_icon_pack($icon_pack);
    }

    // 2. Biên dịch CSS Styling
    $random_id = wp_generate_password(8, false);
    $unique_class = 'vbc-icn-' . $random_id;

    $sz = !empty($atts['size']) ? $atts['size'] : '32px';
    $sz_md = !empty($atts['size__md']) ? $atts['size__md'] : '';
    $sz_sm = !empty($atts['size__sm']) ? $atts['size__sm'] : '';

    $display = !empty($atts['display']) ? $atts['display'] : 'inline-flex';
    $color = !empty($atts['color']) ? $atts['color'] : '';
    $hover_color = !empty($atts['hover_color']) ? $atts['hover_color'] : '';
    $bg_color = !empty($atts['background_color']) ? $atts['background_color'] : (!empty($atts['bg_color']) ? $atts['bg_color'] : (!empty($atts['bg']) ? $atts['bg'] : ''));
    $width = !empty($atts['width']) ? $atts['width'] : '';
    $height = !empty($atts['height']) ? $atts['height'] : '';
    $min_width = !empty($atts['min_width']) ? $atts['min_width'] : '';
    $padding = !empty($atts['padding']) ? $atts['padding'] : '';
    $padding_md = !empty($atts['padding__md']) ? $atts['padding__md'] : '';
    $padding_sm = !empty($atts['padding__sm']) ? $atts['padding__sm'] : '';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '';
    $border = !empty($atts['border']) ? $atts['border'] : (!empty($atts['border_color']) ? '1px solid ' . $atts['border_color'] : '');
    $shadow = !empty($atts['box_shadow']) ? $atts['box_shadow'] : '';
    $margin = !empty($atts['margin']) ? $atts['margin'] : '';
    $margin_md = !empty($atts['margin__md']) ? $atts['margin__md'] : '';
    $margin_sm = !empty($atts['margin__sm']) ? $atts['margin__sm'] : '';

    $css_rules = '';

    // Desktop
    $desktop_css = array();
    $desktop_css[] = 'display: ' . esc_attr($display) . ';';
    $desktop_css[] = 'align-items: center;';
    $desktop_css[] = 'justify-content: center;';
    $desktop_css[] = 'line-height: 1;';
    $desktop_css[] = 'transition: all 0.25s ease;';
    $desktop_css[] = 'box-sizing: border-box;';

    if ($color) $desktop_css[] = 'color: ' . esc_attr($color) . ';';
    if ($bg_color) $desktop_css[] = 'background-color: ' . esc_attr($bg_color) . ';';
    if ($width) $desktop_css[] = 'width: ' . esc_attr($width) . ';';
    if ($height) $desktop_css[] = 'height: ' . esc_attr($height) . ';';
    if ($min_width) $desktop_css[] = 'min-width: ' . esc_attr($min_width) . ';';
    if ($padding) $desktop_css[] = 'padding: ' . esc_attr($padding) . ';';
    if ($radius) $desktop_css[] = 'border-radius: ' . esc_attr($radius) . ';';
    if ($border) $desktop_css[] = 'border: ' . esc_attr($border) . ';';
    if ($shadow) $desktop_css[] = 'box-shadow: ' . esc_attr($shadow) . ';';
    if ($margin) $desktop_css[] = 'margin: ' . esc_attr($margin) . ';';

    $css_rules .= '.' . $unique_class . ' { ' . implode(' ', $desktop_css) . ' } ';

    // Hover
    if ($hover_color) {
        $css_rules .= '.' . $unique_class . ':hover { color: ' . esc_attr($hover_color) . ' !important; } ';
    }

    // Tablet
    $tablet_css = array();
    if ($padding_md) $tablet_css[] = 'padding: ' . esc_attr($padding_md) . ';';
    if ($margin_md) $tablet_css[] = 'margin: ' . esc_attr($margin_md) . ';';
    if (!empty($tablet_css)) {
        $css_rules .= '@media (max-width: 849px) { .' . $unique_class . ' { ' . implode(' ', $tablet_css) . ' } } ';
    }

    // Mobile
    $mobile_css = array();
    if ($padding_sm) $mobile_css[] = 'padding: ' . esc_attr($padding_sm) . ';';
    if ($margin_sm) $mobile_css[] = 'margin: ' . esc_attr($margin_sm) . ';';
    if (!empty($mobile_css)) {
        $css_rules .= '@media (max-width: 549px) { .' . $unique_class . ' { ' . implode(' ', $mobile_css) . ' } } ';
    }

    // Custom CSS
    if (!empty($atts['custom_css'])) {
        $raw_css = trim($atts['custom_css']);
        if (strpos($raw_css, '{') === false) {
            $css_rules .= '.' . $unique_class . ' { ' . $raw_css . ' } ';
        } else {
            $css_rules .= str_replace('selector', '.' . $unique_class, $raw_css) . ' ';
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

    // 3. Render HTML phần tử
    $inner_html = '';
    $wrap_class = esc_attr(trim('vbc-icon-wrap ' . $unique_class . ' ' . $atts['custom_class']));

    if (!empty($atts['raw_svg']) || !empty($atts['svg'])) {
        $inner_html = !empty($atts['raw_svg']) ? $atts['raw_svg'] : $atts['svg'];
    } elseif ($is_image) {
        if (!empty($image_src)) {
            $img_style = 'width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';object-fit:contain;display:block;border-radius:inherit;';
            $inner_html = '<img src="' . esc_url($image_src) . '" class="vbc-icon-img" style="' . $img_style . '" alt="icon" loading="lazy">';
        } else {
            $inner_html = '<div style="width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i data-lucide="image" style="width:50%;height:50%;color:#94a3b8;"></i></div>';
            vbc_enqueue_icon_pack('lucide');
        }
    } else {
        if ($icon_pack === 'lucide') {
            $inline_svg = vbc_get_inline_svg_icon($icon_name, $sz);
            if ($inline_svg !== false) {
                $inner_html = $inline_svg;
            } else {
                $inner_html = '<i data-lucide="' . esc_attr($icon_name) . '" style="width:' . esc_attr($sz) . ';height:' . esc_attr($sz) . ';display:inline-block;"></i>';
                vbc_enqueue_icon_pack('lucide');
            }
        } elseif ($icon_pack === 'fontawesome') {
            $clean_fa = $icon_name;
            if (strpos($clean_fa, 'fa-') === false) $clean_fa = 'fa-solid fa-' . $clean_fa;
            elseif (strpos($clean_fa, 'fa-solid') === false && strpos($clean_fa, 'fa-brands') === false && strpos($clean_fa, 'fa-regular') === false) {
                $clean_fa = 'fa-solid ' . $clean_fa;
            }
            $inner_html = '<i class="' . esc_attr($clean_fa) . '" style="font-size:' . esc_attr($sz) . ';line-height:1;"></i>';
        } elseif ($icon_pack === 'remix') {
            $clean_ri = $icon_name;
            if (strpos($clean_ri, 'ri-') === false) $clean_ri = 'ri-' . $clean_ri;
            $inner_html = '<i class="' . esc_attr($clean_ri) . '" style="font-size:' . esc_attr($sz) . ';line-height:1;"></i>';
        } elseif ($icon_pack === 'material') {
            $clean_mat = str_replace('-', '_', $icon_name);
            $inner_html = '<span class="material-symbols-outlined" style="font-size:' . esc_attr($sz) . ';line-height:1;">' . esc_html($clean_mat) . '</span>';
        } elseif ($icon_pack === 'phosphor') {
            $clean_ph = $icon_name;
            if (strpos($clean_ph, 'ph') === false) $clean_ph = 'ph ph-' . $clean_ph;
            $inner_html = '<i class="' . esc_attr($clean_ph) . '" style="font-size:' . esc_attr($sz) . ';line-height:1;"></i>';
        }
    }

    return $style_tag . '<span class="' . $wrap_class . '">' . $inner_html . '</span>';
}

/**
 * 6. VÔ HIỆU HÓA TỰ ĐỘNG CHUYỂN EMOJI THÀNH ẢNH S.W.ORG CỦA WORDPRESS CORE
 */
add_action('init', 'vbc_disable_wp_emojis');
