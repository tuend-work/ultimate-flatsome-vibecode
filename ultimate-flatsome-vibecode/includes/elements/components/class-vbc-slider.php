<?php
/**
 * Ultimate Flatsome VibeCode - Slider Component (Splide.js)
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_slider_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'per_page' => '1',
        'speed' => '400',
        'autoplay' => 'false',
        'arrows' => 'true',
        'pagination' => 'true',
        'gap' => '20px',
        'class' => '',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

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
        'class' => '',
        'custom_class' => '',
        'custom_css' => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

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
