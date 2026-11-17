<?php
/**
 * Ultimate Flatsome VibeCode - Real-time Countdown Component
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vbc_countdown_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'due_date'     => date('Y-m-d H:i:s', strtotime('+3 days')),
        'style'        => 'boxes', // boxes | pills | minimal
        'show_days'    => 'true',
        'show_hours'   => 'true',
        'show_minutes' => 'true',
        'show_seconds' => 'true',
        'box_bg'       => '#1e293b',
        'digit_color'  => '#ffffff',
        'label_color'  => '#94a3b8',
        'border_radius'=> '14px',
        'expired_text' => 'Ưu đãi đã kết thúc!',
        'class'        => '',
        'custom_class' => '',
        'custom_css'   => '',
        'margin'       => '',
    ), $atts);

    if (empty($atts['custom_class']) && !empty($atts['class'])) {
        $atts['custom_class'] = $atts['class'];
    }

    $res = vbc_compile_element_css($atts, 'vbc-countdown');
    $unique_class = $res['class'];
    $compiled_css = $res['css'];

    $target_time = strtotime($atts['due_date']);
    if (!$target_time) {
        $target_time = strtotime('+3 days');
    }
    $iso_target = date('c', $target_time);

    $box_bg = !empty($atts['box_bg']) ? $atts['box_bg'] : '#1e293b';
    $digit_color = !empty($atts['digit_color']) ? $atts['digit_color'] : '#ffffff';
    $label_color = !empty($atts['label_color']) ? $atts['label_color'] : '#94a3b8';
    $radius = !empty($atts['border_radius']) ? $atts['border_radius'] : '14px';

    $rules = '.' . $unique_class . ' { display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap; } ';
    $rules .= '.' . $unique_class . ' .vbc-cd-item { display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 68px; padding: 14px 10px; background: ' . $box_bg . '; border-radius: ' . $radius . '; box-shadow: 0 4px 15px rgba(0,0,0,0.06); } ';
    $rules .= '.' . $unique_class . ' .vbc-cd-num { font-size: 28px; font-weight: 900; color: ' . $digit_color . '; line-height: 1; font-variant-numeric: tabular-nums; } ';
    $rules .= '.' . $unique_class . ' .vbc-cd-label { font-size: 11px; font-weight: 700; color: ' . $label_color . '; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.5px; } ';
    $rules .= '.' . $unique_class . ' .vbc-cd-sep { font-size: 24px; font-weight: 800; color: ' . $digit_color . '; margin-top: -16px; opacity: 0.6; } ';
    $rules .= '@media (max-width: 549px) { .' . $unique_class . ' { gap: 8px; } .' . $unique_class . ' .vbc-cd-item { min-width: 54px; padding: 10px 6px; } .' . $unique_class . ' .vbc-cd-num { font-size: 22px; } .' . $unique_class . ' .vbc-cd-label { font-size: 9.5px; } } ';

    $rules = str_replace(array("\r", "\n"), ' ', $rules);
    $rules = preg_replace('/\s+/', ' ', $rules);

    if (vbc_should_inline_css()) {
        $default_css = '<style>' . $rules . '</style>';
    } else {
        global $vbc_accumulated_css;
        if (!is_array($vbc_accumulated_css)) $vbc_accumulated_css = array();
        $vbc_accumulated_css[] = $rules;
        $default_css = '';
    }

    $cid = 'cd_' . substr(md5($unique_class), 0, 8);

    $html = '<div id="' . esc_attr($cid) . '" class="vbc-countdown-wrap ' . esc_attr($unique_class . ' ' . $atts['custom_class']) . '" data-target="' . esc_attr($iso_target) . '" data-expired="' . esc_attr($atts['expired_text']) . '">';
    $html .= $default_css . $compiled_css;

    if ($atts['show_days'] === 'true') {
        $html .= '<div class="vbc-cd-item"><span class="vbc-cd-num vbc-cd-days">00</span><span class="vbc-cd-label">Ngày</span></div>';
        $html .= '<span class="vbc-cd-sep">:</span>';
    }
    if ($atts['show_hours'] === 'true') {
        $html .= '<div class="vbc-cd-item"><span class="vbc-cd-num vbc-cd-hours">00</span><span class="vbc-cd-label">Giờ</span></div>';
        $html .= '<span class="vbc-cd-sep">:</span>';
    }
    if ($atts['show_minutes'] === 'true') {
        $html .= '<div class="vbc-cd-item"><span class="vbc-cd-num vbc-cd-minutes">00</span><span class="vbc-cd-label">Phút</span></div>';
        if ($atts['show_seconds'] === 'true') {
            $html .= '<span class="vbc-cd-sep">:</span>';
        }
    }
    if ($atts['show_seconds'] === 'true') {
        $html .= '<div class="vbc-cd-item"><span class="vbc-cd-num vbc-cd-seconds">00</span><span class="vbc-cd-label">Giây</span></div>';
    }

    $html .= '<script>
    (function(){
        var el = document.getElementById("' . esc_js($cid) . '");
        if (!el) return;
        var target = new Date(el.getAttribute("data-target")).getTime();
        var expiredMsg = el.getAttribute("data-expired");
        function pad(n) { return n < 10 ? "0" + n : n; }
        function update() {
            var now = new Date().getTime();
            var diff = target - now;
            if (diff <= 0) {
                el.innerHTML = "<div class=\"vbc-cd-expired\" style=\"font-weight:800; font-size:18px;\">" + expiredMsg + "</div>";
                return;
            }
            var d = Math.floor(diff / (1000 * 60 * 60 * 24));
            var h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var s = Math.floor((diff % (1000 * 60)) / 1000);
            var elD = el.querySelector(".vbc-cd-days"); if (elD) elD.textContent = pad(d);
            var elH = el.querySelector(".vbc-cd-hours"); if (elH) elH.textContent = pad(h);
            var elM = el.querySelector(".vbc-cd-minutes"); if (elM) elM.textContent = pad(m);
            var elS = el.querySelector(".vbc-cd-seconds"); if (elS) elS.textContent = pad(s);
        }
        update();
        setInterval(update, 1000);
    })();
    </script>';

    $html .= '</div>';

    return $html;
}
add_shortcode('vbc_countdown', 'vbc_countdown_shortcode');
