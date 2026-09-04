<?php
/**
 * Ultimate Flatsome VibeCode - REST API Endpoints & CF7 Handler
 *
 * @package UltimateFlatsomeVibeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'rest_api_init', 'vbc_register_rest_routes' );

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

    // 2. Unified Endpoint CRUD cho mọi wp_post (/vbc/v1/post, /vbc/v1/page, /vbc/v1/content)
    $post_endpoints = array('/post', '/page', '/content');
    foreach ($post_endpoints as $route) {
        register_rest_route('vbc/v1', $route, array(
            array(
                'methods' => 'GET',
                'callback' => 'vbc_api_get_post_handler',
                'permission_callback' => function($request) {
                    $user = vbc_authenticate_request($request);
                    if (is_wp_error($user)) {
                        return $user;
                    }
                    return user_can($user, 'edit_pages') || user_can($user, 'edit_posts');
                }
            ),
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
            )
        ));
    }

    // Endpoint kiểm tra nhanh sự tồn tại của ảnh trong Media Library (/vbc/v1/check-media)
    register_rest_route('vbc/v1', '/check-media', array(
        array(
            'methods' => array('GET', 'POST'),
            'callback' => 'vbc_api_check_media_handler',
            'permission_callback' => function($request) {
                $user = vbc_authenticate_request($request);
                if (is_wp_error($user)) {
                    return $user;
                }
                return user_can($user, 'upload_files') || user_can($user, 'edit_posts');
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
    // Cho phép upload SVG và các định dạng ảnh phổ biến
    add_filter('upload_mimes', function($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        $mimes['webp'] = 'image/webp';
        return $mimes;
    });
    
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    // 1. Nếu upload qua Multipart Form-Data
    if (!empty($_FILES['file'])) {
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
    
    // 2. Nếu upload qua Raw Binary Body kèm X-File-Name
    $raw_body = $request->get_body();
    if (empty($raw_body)) {
        $raw_body = file_get_contents('php://input');
    }
    
    if (!empty($raw_body)) {
        $filename = $request->get_header('X-File-Name');
        if (empty($filename)) {
            $filename = $request->get_param('filename');
        }
        if (empty($filename)) {
            $filename = 'vbc_upload_' . time() . '.png';
        }
        
        $upload = wp_upload_bits($filename, null, $raw_body);
        if (!empty($upload['error'])) {
            return new WP_Error('vbc_upload_failed', $upload['error'], array('status' => 500));
        }
        
        $file_path = $upload['file'];
        $file_type = wp_check_filetype(basename($file_path), null);
        
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($file_path)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $file_path);
        if (!is_wp_error($attachment_id)) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            
            return array(
                'success' => true,
                'id' => $attachment_id,
                'attachment_id' => $attachment_id,
                'url' => $upload['url'],
            );
        }
    }
    
    return new WP_Error('vbc_no_file', 'No file was uploaded.', array('status' => 400));
}

/**
 * Endpoint kiểm tra sự tồn tại của ảnh trong WordPress Media Library
 * Hỗ trợ:
 *   - GET ?filename=logo.svg
 *   - POST {"filenames": ["a.png", "b.jpg"], "urls": ["https://.../a.png"]}
 */
function vbc_api_check_media_handler($request) {
    global $wpdb;
    
    $filename = $request->get_param('filename');
    $filenames = $request->get_param('filenames');
    $urls = $request->get_param('urls');
    
    if (empty($filenames) && !empty($filename)) {
        $filenames = array($filename);
    }
    if (!empty($urls) && is_array($urls)) {
        if (!is_array($filenames)) {
            $filenames = array();
        }
        foreach ($urls as $u) {
            $path = parse_url($u, PHP_URL_PATH);
            if ($path) {
                $filenames[] = basename($path);
            }
        }
    }
    
    if (empty($filenames) || !is_array($filenames)) {
        return new WP_Error('vbc_invalid_param', 'Vui lòng cung cấp filename hoặc danh sách filenames.', array('status' => 400));
    }
    
    $results = array();
    $filenames = array_unique(array_filter($filenames));
    
    foreach ($filenames as $fname) {
        $clean_name = sanitize_file_name($fname);
        if (empty($clean_name)) continue;
        
        $base_name = pathinfo($clean_name, PATHINFO_FILENAME);
        $ext = pathinfo($clean_name, PATHINFO_EXTENSION);
        
        // 1. Kiểm tra chính xác đuôi file trong meta _wp_attached_file
        $sql = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND (meta_value = %s OR meta_value LIKE %s) ORDER BY post_id DESC LIMIT 1",
            $clean_name,
            '%' . $wpdb->esc_like('/' . $clean_name)
        );
        $post_id = $wpdb->get_var($sql);
        
        // 2. Nếu chưa thấy, kiểm tra theo tên gốc nếu WordPress đã đổi tên thành filename-1.ext, filename-2.ext
        if (!$post_id && !empty($base_name) && !empty($ext)) {
            $sql_fuzzy = $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
                '%' . $wpdb->esc_like('/' . $base_name) . '%' . $wpdb->esc_like('.' . $ext)
            );
            $post_id = $wpdb->get_var($sql_fuzzy);
        }
        
        // 3. Nếu chưa thấy, kiểm tra trong bảng wp_posts (guid)
        if (!$post_id) {
            $sql_guid = $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE %s ORDER BY ID DESC LIMIT 1",
                '%' . $wpdb->esc_like($clean_name)
            );
            $post_id = $wpdb->get_var($sql_guid);
        }
        
        if ($post_id) {
            $url = wp_get_attachment_url($post_id);
            $results[$fname] = array(
                'exists' => true,
                'id' => intval($post_id),
                'url' => $url,
                'filename' => $clean_name,
            );
        } else {
            $results[$fname] = array(
                'exists' => false,
                'filename' => $clean_name,
            );
        }
    }
    
    // Nếu request đơn lẻ qua param filename
    if (!empty($filename) && count($filenames) === 1) {
        $res = isset($results[$filename]) ? $results[$filename] : array('exists' => false);
        return $res;
    }
    
    $found_count = 0;
    foreach ($results as $r) {
        if (!empty($r['exists'])) $found_count++;
    }
    
    return array(
        'success' => true,
        'total_checked' => count($filenames),
        'found_count' => $found_count,
        'results' => $results,
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
        
        $default_tpl = ($post_type === 'post') ? 'default' : 'page-blank.php';
        $target_template = !empty($params['template']) ? sanitize_text_field($params['template']) : $default_tpl;
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
        if (!empty($params['excerpt'])) {
            $post_data['post_excerpt'] = sanitize_textarea_field($params['excerpt']);
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

        if (!empty($params['thumbnail_id'])) {
            set_post_thumbnail($updated_id, intval($params['thumbnail_id']));
        }
        if (!empty($params['categories'])) {
            wp_set_post_categories($updated_id, array_map('intval', (array)$params['categories']));
        } elseif (!empty($params['category_names'])) {
            $cat_ids = array();
            foreach ((array)$params['category_names'] as $cname) {
                $term = term_exists($cname, 'category');
                if ($term) {
                    $cat_ids[] = (int)$term['term_id'];
                } else {
                    $new_cat = wp_insert_term($cname, 'category');
                    if (!is_wp_error($new_cat)) {
                        $cat_ids[] = (int)$new_cat['term_id'];
                    }
                }
            }
            if (!empty($cat_ids)) {
                wp_set_post_categories($updated_id, $cat_ids);
            }
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
        
        $default_tpl = ($post_type === 'post') ? 'default' : 'page-blank.php';
        $target_template = !empty($params['template']) ? sanitize_text_field($params['template']) : $default_tpl;
        $post_data = array(
            'post_title' => !empty($title) ? $title : 'Untitled Page',
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => $post_type,
        );
        if (!empty($slug)) {
            $post_data['post_name'] = $slug;
        }
        if (!empty($params['excerpt'])) {
            $post_data['post_excerpt'] = sanitize_textarea_field($params['excerpt']);
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

        if (!empty($params['thumbnail_id'])) {
            set_post_thumbnail($new_id, intval($params['thumbnail_id']));
        }
        if (!empty($params['categories'])) {
            wp_set_post_categories($new_id, array_map('intval', (array)$params['categories']));
        } elseif (!empty($params['category_names'])) {
            $cat_ids = array();
            foreach ((array)$params['category_names'] as $cname) {
                $term = term_exists($cname, 'category');
                if ($term) {
                    $cat_ids[] = (int)$term['term_id'];
                } else {
                    $new_cat = wp_insert_term($cname, 'category');
                    if (!is_wp_error($new_cat)) {
                        $cat_ids[] = (int)$new_cat['term_id'];
                    }
                }
            }
            if (!empty($cat_ids)) {
                wp_set_post_categories($new_id, $cat_ids);
            }
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
 * Xử lý truy vấn GET nội dung của bất kỳ wp_post nào (Post, Page, UX Block, Product...) qua ID hoặc Slug
 */
function vbc_api_get_post_handler($request) {
    $params = $request->get_params();
    $id = !empty($params['id']) ? intval($params['id']) : (!empty($params['post_id']) ? intval($params['post_id']) : 0);
    $slug = !empty($params['slug']) ? sanitize_title($params['slug']) : '';

    if ($id <= 0 && !empty($slug)) {
        $found_posts = get_posts(array(
            'name'           => $slug,
            'post_type'      => 'any',
            'posts_per_page' => 1,
            'post_status'    => 'any',
        ));
        if (!empty($found_posts)) {
            $id = $found_posts[0]->ID;
        }
    }

    if ($id <= 0) {
        return new WP_Error('vbc_invalid_id', 'Vui lòng cung cấp id hoặc post_id hợp lệ.', array('status' => 400));
    }

    $post = get_post($id);
    if (!$post) {
        return new WP_Error('vbc_not_found', 'Không tìm thấy wp_post với ID: ' . $id, array('status' => 404));
    }

    $custom_css = get_post_meta($post->ID, '_custom_css', true);
    if (empty($custom_css)) {
        $custom_css = get_post_meta($post->ID, 'vbc_page_css', true);
    }

    $template = get_post_meta($post->ID, '_wp_page_template', true);

    // Thống kê nhanh nội dung
    $raw_content = $post->post_content;
    preg_match_all('/\[vbc_section\b/i', $raw_content, $sections);
    preg_match_all('/<img\b|\[vbc_img\b/i', $raw_content, $imgs);
    preg_match_all('/\[contact-form-7\b/i', $raw_content, $forms);
    preg_match_all('/\[\/?vbc_[a-zA-Z0-9_\-]+[^\]]*\]/i', $raw_content, $vbc_tags);

    return array(
        'success'        => true,
        'id'             => $post->ID,
        'post_id'        => $post->ID,
        'title'          => $post->post_title,
        'slug'           => $post->post_name,
        'status'         => $post->post_status,
        'post_type'      => $post->post_type,
        'url'            => get_permalink($post->ID),
        'ux_builder_url' => admin_url('post.php?post=' . $post->ID . '&action=edit&app=uxbuilder'),
        'template'       => $template ? $template : 'default',
        'post_content'   => $raw_content,
        'custom_css'     => $custom_css ? $custom_css : '',
        'author'         => $post->post_author,
        'date'           => $post->post_date,
        'modified'       => $post->post_modified,
        'stats'          => array(
            'content_length' => strlen($raw_content),
            'vbc_tags_count' => count($vbc_tags[0]),
            'sections_count' => count($sections[0]),
            'images_count'   => count($imgs[0]),
            'has_cf7'        => count($forms[0]) > 0,
        )
    );
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
