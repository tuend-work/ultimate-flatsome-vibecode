<?php
/**
 * Ultimate Flatsome VibeCode - Tabs Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_tabs_shortcode($atts, $content = null, $tag = 'vbc_tabs') {
    $atts = shortcode_atts(array(
        'style' => 'pills', // pills, underline, cards, glass
        'align' => 'left',  // left, center, right, justify
        'active_tab' => '1',
        'tab_bg' => '',
        'tab_active_bg' => '',
        'tab_color' => '',
        'tab_active_color' => '',
        'class' => '',
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

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-tabs');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $raw_content = vbc_clean_inner_content($content);

    // Tìm tất cả shortcodes con của tab
    $tab_pattern = '/\[(vbc_tab(?:_inner(?:_\d+)?)?|tab)(?:\s+([^\]]*))?\](.*?)\[\/\1\]/is';
    preg_match_all($tab_pattern, $raw_content, $matches, PREG_SET_ORDER);

    $tabs_data = array();
    $active_idx = intval($atts['active_tab']) - 1;
    if ($active_idx < 0) $active_idx = 0;

    $tab_group_id = 'vbc-tab-grp-' . wp_generate_password(6, false);

    foreach ($matches as $i => $m) {
        $tab_atts_raw = isset($m[2]) ? $m[2] : '';
        $tab_inner_content = isset($m[3]) ? $m[3] : '';
        
        $parsed_atts = shortcode_parse_atts($tab_atts_raw);
        if (!is_array($parsed_atts)) {
            $parsed_atts = array();
        }

        $title = isset($parsed_atts['title']) ? $parsed_atts['title'] : 'Tab ' . ($i + 1);
        $icon = isset($parsed_atts['icon']) ? $parsed_atts['icon'] : '';
        $custom_class = isset($parsed_atts['custom_class']) ? $parsed_atts['custom_class'] : '';
        $pane_id = isset($parsed_atts['tab_id']) && !empty($parsed_atts['tab_id']) ? $parsed_atts['tab_id'] : $tab_group_id . '-pane-' . $i;

        $tabs_data[] = array(
            'id' => $pane_id,
            'title' => $title,
            'icon' => $icon,
            'custom_class' => $custom_class,
            'content' => $tab_inner_content,
            'is_active' => ($i === $active_idx)
        );
    }

    // Default styles for tab navigation
    $align_css = 'flex-start';
    if ($atts['align'] === 'center') $align_css = 'center';
    elseif ($atts['align'] === 'right') $align_css = 'flex-end';
    elseif ($atts['align'] === 'justify') $align_css = 'space-between';

    $tab_bg = !empty($atts['tab_bg']) ? $atts['tab_bg'] : '#f1f5f9';
    $tab_color = !empty($atts['tab_color']) ? $atts['tab_color'] : '#475569';
    $tab_active_bg = !empty($atts['tab_active_bg']) ? $atts['tab_active_bg'] : '#003366';
    $tab_active_color = !empty($atts['tab_active_color']) ? $atts['tab_active_color'] : '#ffffff';

    $tab_nav_css = '.' . $unique_class . ' .vbc-tabs-nav { display: flex; flex-wrap: wrap; gap: 10px; justify-content: ' . $align_css . '; list-style: none; margin: 0 0 24px 0; padding: 0; } ';
    $tab_nav_css .= '.' . $unique_class . ' .vbc-tab-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; font-weight: 700; font-size: 15px; border-radius: 30px; cursor: pointer; border: none; transition: all 0.25s ease; background: ' . $tab_bg . '; color: ' . $tab_color . '; } ';
    $tab_nav_css .= '.' . $unique_class . ' .vbc-tab-btn:hover { opacity: 0.9; transform: translateY(-1px); } ';
    $tab_nav_css .= '.' . $unique_class . ' .vbc-tab-btn.active { background: ' . $tab_active_bg . ' !important; color: ' . $tab_active_color . ' !important; box-shadow: 0 4px 15px rgba(0,0,0,0.1); } ';
    $tab_nav_css .= '.' . $unique_class . ' .vbc-tab-pane { display: none; opacity: 0; transition: opacity 0.3s ease; } ';
    $tab_nav_css .= '.' . $unique_class . ' .vbc-tab-pane.active { display: block; opacity: 1; animation: vbcFadeIn 0.3s ease; } ';

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $tab_nav_css . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) $vbc_accumulated_css = array();
        $vbc_accumulated_css[] = $tab_nav_css;
        $default_css = '';
    }

    $html = '<div id="' . esc_attr($tab_group_id) . '" class="vbc-tabs-wrapper ' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '">';
    $html .= $default_css . $compiled_css;

    // 1. Navigation Nav Header
    $html .= '<ul class="vbc-tabs-nav">';
    foreach ($tabs_data as $tab) {
        $act_cls = $tab['is_active'] ? ' active' : '';
        $icon_html = !empty($tab['icon']) ? '<i class="' . esc_attr($tab['icon']) . '"></i> ' : '';
        $html .= '<li class="vbc-tab-item">';
        $html .= '<button type="button" class="vbc-tab-btn' . $act_cls . '" data-target="#' . esc_attr($tab['id']) . '" onclick="vbcSwitchTab(this, \'' . esc_attr($tab_group_id) . '\')">';
        $html .= $icon_html . esc_html($tab['title']);
        $html .= '</button>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    // 2. Panes
    $html .= '<div class="vbc-tabs-content">';
    foreach ($tabs_data as $tab) {
        $act_cls = $tab['is_active'] ? ' active' : '';
        $html .= '<div id="' . esc_attr($tab['id']) . '" class="vbc-tab-pane' . $act_cls . ' ' . esc_attr($tab['custom_class']) . '">';
        $html .= do_shortcode($tab['content']);
        $html .= '</div>';
    }
    $html .= '</div>';

    // 3. Tab switching inline script
    $html .= '<script>
    if (typeof window.vbcSwitchTab === "undefined") {
        window.vbcSwitchTab = function(btn, grpId) {
            var grp = document.getElementById(grpId);
            if (!grp) return;
            var targetId = btn.getAttribute("data-target");
            var btns = grp.querySelectorAll(".vbc-tab-btn");
            var panes = grp.querySelectorAll(".vbc-tab-pane");
            btns.forEach(function(b) { b.classList.remove("active"); });
            panes.forEach(function(p) { p.classList.remove("active"); });
            btn.classList.add("active");
            var targetPane = grp.querySelector(targetId);
            if (targetPane) targetPane.classList.add("active");
        };
    }
    </script>';

    $html .= '</div>';
    return $html;
}

function vbc_tab_shortcode($atts, $content = null) {
    return do_shortcode(vbc_clean_inner_content($content));
}
