<?php
/**
 * Ultimate Flatsome VibeCode - REST API Endpoints & CF7 Handler
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

    // Endpoint tạo & quản lý Contact Form 7
    register_rest_route('vbc/v1', '/cf7', array(
        'methods' => 'POST',
        'callback' => 'vbc_api_cf7_handler',
        'permission_callback' => function($request) {
            $user = vbc_authenticate_request($request);
            if (is_wp_error($user)) {
                return $user;
            }
            return user_can($user, 'edit_posts') || user_can($user, 'manage_options');
        }
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
    if (!user_can($user, 'manage_options') && !user_can($user, 'administrator')) {
        return new WP_Error('vbc_forbidden', 'Chỉ tài khoản Administrator mới có quyền truy cập API.', array('status' => 403));
    }
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

/**
 * Tự động sửa lỗi UTF-8 Double Encoding (Mojibake)
 * Ví dụ: "MÃ¡y Chá»§ Váºt LÃ½" -> "Máy Chủ Vật Lý"
 */
function vbc_fix_utf8_mojibake($str) {
    if (empty($str) || !is_string($str)) return $str;
    
    // Thử đảo ngược UTF-8 -> ISO-8859-1
    $test1 = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $str);
    if ($test1 && $test1 !== $str && mb_check_encoding($test1, 'UTF-8')) {
        if (preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]/u', $test1)) {
            return $test1;
        }
    }
    
    // Thử đảo ngược UTF-8 -> Windows-1252
    $test2 = @iconv('UTF-8', 'WINDOWS-1252//IGNORE', $str);
    if ($test2 && $test2 !== $str && mb_check_encoding($test2, 'UTF-8')) {
        if (preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]/u', $test2)) {
            return $test2;
        }
    }
    
    return $str;
}

function vbc_api_page_handler($request) {
    $params = $request->get_params();
    $post_id = !empty($params['post_id']) ? intval($params['post_id']) : 0;
    $action_type = !empty($params['action_type']) ? sanitize_key($params['action_type']) : '';
    $title = !empty($params['title']) ? sanitize_text_field($params['title']) : '';
    $title = vbc_fix_utf8_mojibake($title);
    
    $content = !empty($params['content']) ? $params['content'] : ''; 
    $content = vbc_fix_utf8_mojibake($content);
    
    // 1. Tự động trích xuất toàn bộ CSS trong <style>...</style> để lưu riêng vào Custom CSS Post Meta
    $extracted_css = '';
    if (!empty($params['custom_css'])) {
        $extracted_css .= ' ' . $params['custom_css'];
    }
    $content = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/is', function($matches) use (&$extracted_css) {
        $minified_css = str_replace(array("\r\n", "\r", "\n"), ' ', $matches[1]);
        $minified_css = preg_replace('/\s+/', ' ', $minified_css);
        $extracted_css .= ' ' . trim($minified_css);
        return ''; // XÓA HOÀN TOÀN THẺ <style> RA KHỎI post_content
    }, $content);

    // 2. Xóa hoàn toàn tất cả comment HTML <!-- ... --> ra khỏi post_content
    $content = preg_replace('/<!--[\s\S]*?-->/', '', $content);
    $content = trim(preg_replace('/\n{3,}/', "\n\n", $content));
    
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
        update_post_meta($post_id, '_wp_page_template', $target_template);

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
        if (!empty($extracted_css)) {
            $extracted_css = trim($extracted_css);
            update_post_meta($updated_id, '_custom_css', $extracted_css);
            update_post_meta($updated_id, 'vbc_page_css', $extracted_css);
        }
        
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
        if (!empty($extracted_css)) {
            $extracted_css = trim($extracted_css);
            update_post_meta($new_id, '_custom_css', $extracted_css);
            update_post_meta($new_id, 'vbc_page_css', $extracted_css);
        }
        
        return array(
            'success' => true,
            'post_id' => $new_id,
            'url' => get_permalink($new_id),
            'action' => 'create',
        );
    }
}

/**
 * Tự động nạp Custom CSS vào thẻ <head> của trang
 */
add_action('wp_head', 'vbc_render_page_custom_css', 99);
function vbc_render_page_custom_css() {
    $post_id = get_the_ID();
    if (!$post_id) {
        if (isset($GLOBALS['post']->ID)) {
            $post_id = $GLOBALS['post']->ID;
        }
    }
    if (!$post_id) return;
    
    $css = get_post_meta($post_id, '_custom_css', true);
    if (empty($css)) {
        $css = get_post_meta($post_id, 'vbc_page_css', true);
    }
    if (!empty($css)) {
        echo "\n<!-- VibeCode / Flatsome Page Custom CSS -->\n";
        echo '<style id="vbc-page-custom-css">' . trim($css) . '</style>' . "\n";
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
 * REST API Handler cho Contact Form 7 (/vbc/v1/cf7)
 */
function vbc_api_cf7_handler($request) {
    $params = $request->get_params();
    $title = !empty($params['title']) ? sanitize_text_field($params['title']) : 'Form Liên Hệ ' . date('Y-m-d H:i');
    $title = vbc_fix_utf8_mojibake($title);
    
    $form_content = !empty($params['form']) ? $params['form'] : '';
    $form_content = vbc_fix_utf8_mojibake($form_content);
    
    $mail_recipient = !empty($params['mail_recipient']) ? sanitize_email($params['mail_recipient']) : get_option('admin_email');
    $mail_subject = !empty($params['mail_subject']) ? sanitize_text_field($params['mail_subject']) : '[' . get_bloginfo('name') . '] Liên hệ mới từ ' . $title;
    
    $post_id = !empty($params['id']) ? intval($params['id']) : 0;
    
    $post_data = array(
        'post_title' => $title,
        'post_content' => $form_content,
        'post_status' => 'publish',
        'post_type' => 'wpcf7_contact_form',
    );
    
    if ($post_id > 0) {
        $post_data['ID'] = $post_id;
        $saved_id = wp_update_post($post_data, true);
    } else {
        $saved_id = wp_insert_post($post_data, true);
    }
    
    if (is_wp_error($saved_id)) {
        return new WP_Error('vbc_cf7_save_failed', $saved_id->get_error_message(), array('status' => 500));
    }
    
    // Lưu metadata chuẩn Contact Form 7
    update_post_meta($saved_id, '_form', $form_content);
    
    $mail_meta = array(
        'active' => true,
        'subject' => $mail_subject,
        'sender' => '[_site_title] <wordpress@' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . '>',
        'recipient' => $mail_recipient,
        'body' => "Từ: [your-name] <[your-email]>\nSố điện thoại: [your-phone]\nTiêu đề: [your-subject]\n\nNội dung:\n[your-message]\n\n--\nThư này được gửi từ form liên hệ trên " . get_bloginfo('name') . " (" . home_url() . ")",
        'additional_headers' => "Reply-To: [your-email]",
        'attachments' => "",
        'use_html' => false,
        'exclude_empty' => false,
    );
    update_post_meta($saved_id, '_mail', $mail_meta);
    
    $messages_meta = array(
        'mail_sent_ok' => 'Cảm ơn bạn. Yêu cầu của bạn đã được gửi thành công.',
        'mail_sent_ng' => 'Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại sau.',
        'validation_error' => 'Một hoặc nhiều trường có lỗi. Vui lòng kiểm tra lại.',
        'spam' => 'Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại sau.',
        'accept_terms' => 'Bạn phải chấp nhận các điều khoản trước khi gửi tin nhắn.',
        'invalid_required' => 'Trường này là bắt buộc.',
        'invalid_too_long' => 'Trường quá dài.',
        'invalid_too_short' => 'Trường quá ngắn.',
    );
    update_post_meta($saved_id, '_messages', $messages_meta);
    
    $shortcode = '[contact-form-7 id="' . $saved_id . '" title="' . esc_attr($title) . '"]';
    
    return array(
        'success' => true,
        'id' => $saved_id,
        'title' => $title,
        'shortcode' => $shortcode,
        'form' => $form_content,
    );
}

/**
 * Fallback Shortcode Renderer cho [contact-form-7] khi plugin CF7 chưa được kích hoạt
 */
if (!shortcode_exists('contact-form-7')) {
    add_shortcode('contact-form-7', 'vbc_cf7_fallback_renderer');
}

function vbc_cf7_fallback_renderer($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
        'title' => '',
        'html_class' => '',
    ), $atts, 'contact-form-7');
    
    $form_id = intval($atts['id']);
    if ($form_id <= 0) return '';
    
    $form_post = get_post($form_id);
    if (!$form_post) return '';
    
    $raw_form = get_post_meta($form_id, '_form', true);
    if (empty($raw_form)) {
        $raw_form = $form_post->post_content;
    }
    
    // Parse các tag cơ bản của Contact Form 7 thành HTML
    $html = $raw_form;
    
    // [text* your-name placeholder "..."]
    $html = preg_replace_callback('/\[text(\*?)\s+([a-zA-Z0-9_\-]+)([^\]]*)\]/', function($m) {
        $req = !empty($m[1]) ? ' required' : '';
        $name = esc_attr($m[2]);
        $extra = $m[3];
        $placeholder = '';
        if (preg_match('/placeholder\s+[\'"]([^\'"]+)[\'"]/', $extra, $pm)) {
            $placeholder = ' placeholder="' . esc_attr($pm[1]) . '"';
        }
        return '<input type="text" name="' . $name . '"' . $placeholder . $req . ' class="wpcf7-form-control wpcf7-text" />';
    }, $html);
    
    // [tel* your-phone placeholder "..."]
    $html = preg_replace_callback('/\[tel(\*?)\s+([a-zA-Z0-9_\-]+)([^\]]*)\]/', function($m) {
        $req = !empty($m[1]) ? ' required' : '';
        $name = esc_attr($m[2]);
        $extra = $m[3];
        $placeholder = '';
        if (preg_match('/placeholder\s+[\'"]([^\'"]+)[\'"]/', $extra, $pm)) {
            $placeholder = ' placeholder="' . esc_attr($pm[1]) . '"';
        }
        return '<input type="tel" name="' . $name . '"' . $placeholder . $req . ' class="wpcf7-form-control wpcf7-tel" />';
    }, $html);

    // [email* your-email placeholder "..."]
    $html = preg_replace_callback('/\[email(\*?)\s+([a-zA-Z0-9_\-]+)([^\]]*)\]/', function($m) {
        $req = !empty($m[1]) ? ' required' : '';
        $name = esc_attr($m[2]);
        $extra = $m[3];
        $placeholder = '';
        if (preg_match('/placeholder\s+[\'"]([^\'"]+)[\'"]/', $extra, $pm)) {
            $placeholder = ' placeholder="' . esc_attr($pm[1]) . '"';
        }
        return '<input type="email" name="' . $name . '"' . $placeholder . $req . ' class="wpcf7-form-control wpcf7-email" />';
    }, $html);

    // [date departure-date]
    $html = preg_replace_callback('/\[date(\*?)\s+([a-zA-Z0-9_\-]+)([^\]]*)\]/', function($m) {
        $req = !empty($m[1]) ? ' required' : '';
        $name = esc_attr($m[2]);
        return '<input type="date" name="' . $name . '"' . $req . ' class="wpcf7-form-control wpcf7-date" />';
    }, $html);

    // [select your-select "Option 1" "Option 2"]
    $html = preg_replace_callback('/\[select(\*?)\s+([a-zA-Z0-9_\-]+)\s+([^\]]+)\]/', function($m) {
        $name = esc_attr($m[2]);
        $options_raw = $m[3];
        $options_html = '';
        if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $options_raw, $om)) {
            foreach ($om[1] as $opt) {
                $options_html .= '<option value="' . esc_attr($opt) . '">' . esc_html($opt) . '</option>';
            }
        }
        return '<select name="' . $name . '" class="wpcf7-form-control wpcf7-select">' . $options_html . '</select>';
    }, $html);

    // [textarea your-message placeholder "..."]
    $html = preg_replace_callback('/\[textarea(\*?)\s+([a-zA-Z0-9_\-]+)([^\]]*)\]/', function($m) {
        $name = esc_attr($m[2]);
        $extra = $m[3];
        $placeholder = '';
        if (preg_match('/placeholder\s+[\'"]([^\'"]+)[\'"]/', $extra, $pm)) {
            $placeholder = ' placeholder="' . esc_attr($pm[1]) . '"';
        }
        return '<textarea name="' . $name . '"' . $placeholder . ' class="wpcf7-form-control wpcf7-textarea"></textarea>';
    }, $html);

    // [submit class:my-class "Button Text"]
    $html = preg_replace_callback('/\[submit(?:\s+class:([^\s\]]+))?\s+[\'"]([^\'"]+)[\'"]\]/', function($m) {
        $cls = !empty($m[1]) ? ' ' . esc_attr($m[1]) : '';
        $text = esc_html($m[2]);
        return '<button type="submit" class="wpcf7-form-control wpcf7-submit' . $cls . '">' . $text . '</button>';
    }, $html);

    return '<div class="wpcf7 js" id="wpcf7-f' . $form_id . '-p0-o1" lang="vi" dir="ltr"><form action="#" method="post" class="wpcf7-form init ' . esc_attr($atts['html_class']) . '">' . $html . '</form></div>';
}

/**
 * 5. QUẢN LÝ THƯ VIỆN ICON THÔNG MINH (CONDITIONAL ICON LOADING)
 */
add_action('wp_enqueue_scripts', 'vbc_register_icon_libraries');
add_action('admin_enqueue_scripts', 'vbc_register_icon_libraries');
