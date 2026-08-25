<?php
/**
 * Ultimate Flatsome VibeCode - Fullpage Component (fullPage.js)
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
 * Helper: Parse cấu hình danh sách trường và độ rộng cột cho vbc_post
 */
