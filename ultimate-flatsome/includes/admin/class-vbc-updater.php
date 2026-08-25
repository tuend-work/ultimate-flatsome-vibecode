<?php
/**
 * Ultimate Flatsome - Automatic GitHub Plugin Updater
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ultimate_Flatsome_Updater {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Default GitHub Repository Configuration
     */
    private $repo_owner = 'tuend-work';
    private $repo_name  = 'ultimate-flatsome-vibecode';
    private $branch     = 'main';
    private $subfolder  = 'ultimate-flatsome';

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Handle direct update request from Admin
        add_action( 'admin_init', array( $this, 'handle_manual_update_request' ) );

        // AJAX update check & execute handler
        add_action( 'wp_ajax_vbc_check_plugin_update', array( $this, 'ajax_check_update' ) );
        add_action( 'wp_ajax_vbc_execute_plugin_update', array( $this, 'ajax_execute_update' ) );

        // WordPress Core Plugins Update Transients integration
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_wp_plugin_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugins_api_info' ), 20, 3 );
    }

    /**
     * Lấy GitHub Token từ wp_options nếu có cấu hình
     */
    public function get_github_token() {
        return get_option( 'uf_github_token', '' );
    }

    /**
     * Kiểm tra phiên bản mới nhất từ GitHub
     */
    public function check_remote_version() {
        $token = $this->get_github_token();

        // 1. Thử lấy qua GitHub API (hỗ trợ cả repo Public & Private có Token)
        $api_url = "https://api.github.com/repos/{$this->repo_owner}/{$this->repo_name}/contents/{$this->subfolder}/ultimate-flatsome.php?ref={$this->branch}";
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'WordPress/UltimateFlatsome-Updater',
                'Accept'     => 'application/vnd.github.v3+json',
            ),
        );

        if ( ! empty( $token ) ) {
            $args['headers']['Authorization'] = 'token ' . trim( $token );
        }

        $response = wp_remote_get( $api_url, $args );
        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body['content'] ) ) {
                $file_content = base64_decode( $body['content'] );
                if ( preg_match( '/Version:\s*([0-9\.]+)/i', $file_content, $matches ) ) {
                    return trim( $matches[1] );
                }
            }
        }

        // 2. Thử lấy qua Raw Content URL (cho public repo)
        $raw_url = "https://raw.githubusercontent.com/{$this->repo_owner}/{$this->repo_name}/{$this->branch}/{$this->subfolder}/ultimate-flatsome.php";
        $raw_response = wp_remote_get( $raw_url, array( 'timeout' => 15, 'headers' => array( 'User-Agent' => 'WordPress/UltimateFlatsome-Updater' ) ) );
        if ( ! is_wp_error( $raw_response ) && wp_remote_retrieve_response_code( $raw_response ) === 200 ) {
            $raw_content = wp_remote_retrieve_body( $raw_response );
            if ( preg_match( '/Version:\s*([0-9\.]+)/i', $raw_content, $matches ) ) {
                return trim( $matches[1] );
            }
        }

        return false;
    }

    /**
     * Thực hiện tải về và ghi đè cập nhật plugin từ GitHub
     */
    public function perform_update() {
        if ( ! current_user_can( 'update_plugins' ) ) {
            return new WP_Error( 'forbidden', __( 'Bạn không có quyền cập nhật plugin.', 'vibecode' ) );
        }

        $token = $this->get_github_token();

        // 1. Chuẩn bị URL tải file ZIP
        $zip_url = "https://api.github.com/repos/{$this->repo_owner}/{$this->repo_name}/zipball/{$this->branch}";

        $args = array(
            'timeout'  => 300,
            'stream'   => false,
            'headers'  => array(
                'User-Agent' => 'WordPress/UltimateFlatsome-Updater',
                'Accept'     => 'application/vnd.github.v3+json',
            ),
        );

        if ( ! empty( $token ) ) {
            $args['headers']['Authorization'] = 'token ' . trim( $token );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
        global $wp_filesystem;

        // 2. Tải file ZIP về thư mục tạm
        $response = wp_remote_get( $zip_url, $args );
        if ( is_wp_error( $response ) ) {
            // Fallback direct URL nếu không có token
            $fallback_url = "https://github.com/{$this->repo_owner}/{$this->repo_name}/archive/refs/heads/{$this->branch}.zip";
            $response = wp_remote_get( $fallback_url, array( 'timeout' => 300, 'headers' => array( 'User-Agent' => 'WordPress/UltimateFlatsome-Updater' ) ) );
            if ( is_wp_error( $response ) ) {
                return $response;
            }
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        if ( $status_code === 404 && empty( $token ) ) {
            return new WP_Error( 'private_repo_token_required', __( 'Không thể tải bản cập nhật (Mã lỗi 404). Nếu Repository GitHub ở chế độ Riêng Tư (Private), vui lòng nhập GitHub Token trong Cài Đặt Chung > Cập Nhật Plugin.', 'vibecode' ) );
        }
        if ( $status_code !== 200 ) {
            return new WP_Error( 'http_error', sprintf( __( 'Lỗi tải về từ GitHub (HTTP %d).', 'vibecode' ), $status_code ) );
        }

        $zip_content = wp_remote_retrieve_body( $response );
        if ( empty( $zip_content ) ) {
            return new WP_Error( 'empty_download', __( 'Nội dung tải về bị rỗng.', 'vibecode' ) );
        }

        $temp_file = wp_tempnam( 'uf_update_' );
        if ( ! $wp_filesystem->put_contents( $temp_file, $zip_content ) ) {
            @unlink( $temp_file );
            return new WP_Error( 'write_error', __( 'Không thể ghi file ZIP tạm vào hosting.', 'vibecode' ) );
        }

        // 3. Giải nén vào thư mục tạm
        $temp_dir = trailingslashit( get_temp_dir() ) . 'uf_extracted_' . wp_generate_password( 8, false );
        wp_mkdir_p( $temp_dir );

        $unzip_result = unzip_file( $temp_file, $temp_dir );
        @unlink( $temp_file );

        if ( is_wp_error( $unzip_result ) ) {
            $wp_filesystem->delete( $temp_dir, true );
            return $unzip_result;
        }

        // 4. Tìm thư mục plugin 'ultimate-flatsome' bên trong thư mục giải nén
        $extracted_items = glob( $temp_dir . '/*' );
        $source_plugin_dir = '';

        if ( ! empty( $extracted_items ) ) {
            $root_extracted = $extracted_items[0]; // e.g., tuend-work-ultimate-flatsome-vibecode-xxxx
            $candidate = trailingslashit( $root_extracted ) . $this->subfolder;
            if ( is_dir( $candidate ) ) {
                $source_plugin_dir = $candidate;
            } elseif ( file_exists( trailingslashit( $root_extracted ) . 'ultimate-flatsome.php' ) ) {
                $source_plugin_dir = $root_extracted;
            }
        }

        if ( empty( $source_plugin_dir ) || ! is_dir( $source_plugin_dir ) ) {
            $wp_filesystem->delete( $temp_dir, true );
            return new WP_Error( 'subfolder_not_found', sprintf( __( 'Không tìm thấy thư mục plugin <code>%s</code> bên trong file ZIP tải về từ GitHub.', 'vibecode' ), esc_html( $this->subfolder ) ) );
        }

        // 5. Thư mục đích cài đặt plugin trên WordPress
        $dest_plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . 'ultimate-flatsome';

        if ( ! is_dir( $dest_plugin_dir ) ) {
            wp_mkdir_p( $dest_plugin_dir );
        }

        // 6. Sao chép đè toàn bộ file mới vào thư mục plugin
        $copy_result = copy_dir( $source_plugin_dir, $dest_plugin_dir );

        // 7. Xóa sạch thư mục tạm
        $wp_filesystem->delete( $temp_dir, true );

        if ( is_wp_error( $copy_result ) ) {
            return $copy_result;
        }

        // 8. Reset OPcache nếu có
        if ( function_exists( 'opcache_reset' ) ) {
            @opcache_reset();
        }

        // 9. Đọc phiên bản mới sau khi cập nhật
        $new_main_file = trailingslashit( $dest_plugin_dir ) . 'ultimate-flatsome.php';
        $new_version = defined( 'VBC_VERSION' ) ? VBC_VERSION : '2.5.33';
        if ( file_exists( $new_main_file ) ) {
            $data = get_file_data( $new_main_file, array( 'Version' => 'Version' ) );
            if ( ! empty( $data['Version'] ) ) {
                $new_version = $data['Version'];
            }
        }

        return array(
            'success'     => true,
            'new_version' => $new_version,
            'message'     => sprintf( __( 'Cập nhật thành công lên phiên bản %s từ GitHub!', 'vibecode' ), $new_version ),
        );
    }

    /**
     * Xử lý Request Cập Nhật Thủ Công từ Form Admin
     */
    public function handle_manual_update_request() {
        if ( isset( $_POST['vbc_action'] ) && $_POST['vbc_action'] === 'update_plugin_from_github' ) {
            if ( ! current_user_can( 'update_plugins' ) ) {
                return;
            }
            check_admin_referer( 'vbc_update_plugin_nonce', 'vbc_update_nonce' );

            // Lưu GitHub Token nếu có cập nhật
            if ( isset( $_POST['uf_github_token'] ) ) {
                update_option( 'uf_github_token', sanitize_text_field( $_POST['uf_github_token'] ) );
            }

            $result = $this->perform_update();

            if ( is_wp_error( $result ) ) {
                wp_redirect( add_query_arg( array(
                    'page'         => 'ultimate-flatsome',
                    'update_error' => urlencode( $result->get_error_message() ),
                ), admin_url( 'admin.php' ) ) );
                exit;
            }

            wp_redirect( add_query_arg( array(
                'page'           => 'ultimate-flatsome',
                'update_success' => '1',
                'new_version'    => ! empty( $result['new_version'] ) ? $result['new_version'] : '',
            ), admin_url( 'admin.php' ) ) );
            exit;
        }
    }

    /**
     * AJAX Check Update
     */
    public function ajax_check_update() {
        check_ajax_referer( 'vbc_ajax_nonce', 'security' );
        if ( ! current_user_can( 'update_plugins' ) ) {
            wp_send_json_error( array( 'message' => __( 'Không có quyền.', 'vibecode' ) ) );
        }

        $remote_version = $this->check_remote_version();
        $current_version = defined( 'VBC_VERSION' ) ? VBC_VERSION : '2.5.33';

        if ( ! $remote_version ) {
            wp_send_json_success( array(
                'has_update'      => false,
                'current_version' => $current_version,
                'message'         => __( 'Không thể kiểm tra phiên bản từ GitHub (Kiểm tra kết nối hoặc cấu hình GitHub Token nếu là Private Repo).', 'vibecode' ),
            ) );
        }

        $has_update = version_compare( $remote_version, $current_version, '>' );

        wp_send_json_success( array(
            'has_update'      => $has_update,
            'current_version' => $current_version,
            'remote_version'  => $remote_version,
            'message'         => $has_update
                ? sprintf( __( 'Đã có phiên bản mới: v%s (Hiện tại: v%s)', 'vibecode' ), $remote_version, $current_version )
                : sprintf( __( 'Bạn đang sử dụng phiên bản mới nhất (v%s).', 'vibecode' ), $current_version ),
        ) );
    }

    /**
     * AJAX Execute Update
     */
    public function ajax_execute_update() {
        check_ajax_referer( 'vbc_ajax_nonce', 'security' );
        if ( ! current_user_can( 'update_plugins' ) ) {
            wp_send_json_error( array( 'message' => __( 'Không có quyền.', 'vibecode' ) ) );
        }

        $result = $this->perform_update();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Tích hợp vào WordPress Plugins Update Checker
     */
    public function check_for_wp_plugin_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $plugin_slug = 'ultimate-flatsome/ultimate-flatsome.php';
        $remote_version = $this->check_remote_version();
        $current_version = defined( 'VBC_VERSION' ) ? VBC_VERSION : '2.5.33';

        if ( $remote_version && version_compare( $remote_version, $current_version, '>' ) ) {
            $obj = new stdClass();
            $obj->slug = 'ultimate-flatsome';
            $obj->new_version = $remote_version;
            $obj->url = "https://github.com/{$this->repo_owner}/{$this->repo_name}/tree/{$this->branch}/{$this->subfolder}";
            $obj->package = "https://api.github.com/repos/{$this->repo_owner}/{$this->repo_name}/zipball/{$this->branch}";
            $transient->response[ $plugin_slug ] = $obj;
        }

        return $transient;
    }

    /**
     * Plugin API Info Popup
     */
    public function plugins_api_info( $result, $action, $args ) {
        if ( isset( $args->slug ) && $args->slug === 'ultimate-flatsome' ) {
            $remote_version = $this->check_remote_version();
            $res = new stdClass();
            $res->name = 'Ultimate Flatsome';
            $res->slug = 'ultimate-flatsome';
            $res->version = $remote_version ?: ( defined( 'VBC_VERSION' ) ? VBC_VERSION : '2.5.33' );
            $res->author = '<a href="https://github.com/tuend-work">Antigravity AI</a>';
            $res->homepage = "https://github.com/{$this->repo_owner}/{$this->repo_name}";
            $res->sections = array(
                'description' => 'Tiện ích mở rộng cao cấp cho theme Flatsome: Quản lý thông tin website tập trung, hệ thống HTML & UX Builder Elements đa năng, hỗ trợ responsive hoàn hảo, chèn dữ liệu động và tối ưu hóa chuyển đổi.',
                'changelog'   => "Bản cập nhật tự động từ nhánh {$this->branch} của GitHub Repository.",
            );
            return $res;
        }
        return $result;
    }
}

// Khởi tạo Updater Singleton
Ultimate_Flatsome_Updater::instance();
