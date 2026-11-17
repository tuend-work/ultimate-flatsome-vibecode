<?php
/**
 * Admin Tab View: Templates
 *
 * @package UltimateFlatsome
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

 = class_exists( 'Ultimate_Flatsome_Template_Builder' ) ? Ultimate_Flatsome_Template_Builder::get_template_rules() : array();
 = class_exists( 'Ultimate_Flatsome_Template_Builder' ) ? Ultimate_Flatsome_Template_Builder::get_ux_blocks_options() : array();

// Lấy danh sách Public Post Types
 = get_post_types( array( 'public' => true ), 'objects' );
unset( ['blocks'] ); // Bỏ UX Blocks ra khỏi danh sách

// Lấy danh sách Public Taxonomies
 = get_taxonomies( array( 'public' => true ), 'objects' );
?>
            <div class="vbc-info-box">
                <strong>🎨 Dynamic UX Block Templates Hub:</strong> Cho phép bạn thiết kế và gán bất kỳ <strong>UX Block</strong> nào làm giao diện hiển thị mặc định cho từng loại bài viết (Single Post Types) hoặc từng danh mục/thẻ (Taxonomies). Bạn có thể bấm <strong>"Sửa trong UX Builder"</strong> để kéo thả chỉnh sửa trực quan mọi thành phần giao diện động (Tiêu đề, Ảnh đại diện, Nội dung, Tác giả, Bình luận, Lưới bài viết)!
            </div>

            <form method="POST" action="">
                <?php wp_nonce_field( 'uf_save_templates_nonce', 'uf_templates_nonce' ); ?>
                <input type="hidden" name="vbc_action" value="save_template_rules" />
                <input type="hidden" name="current_tab" value="templates" />

                <!-- 1. Single Post Types Templates -->
                <div class="vbc-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2><span class="dashicons dashicons-admin-post"></span> 1. Giao Diện Chi Tiết (Single Post Types)</h2>
                            <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">
                                Gán UX Block template làm giao diện hiển thị cho từng loại bài viết chi tiết.
                            </p>
                        </div>
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ultimate-flatsome&uf_action=create_sample_template&type=single_post' ), 'uf_create_sample_template' ); ?>" class="button button-secondary" style="font-weight: 700; height: 38px; display: inline-flex; align-items: center; gap: 6px; border-color: #2563eb; color: #2563eb;">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('✨ Tạo Mẫu Bài Viết Chuẩn UX Builder 1-Click', 'vibecode'); ?>
                        </a>
                    </div>

                    <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th style="width: 220px;"><strong>Loại Bài Viết (Post Type)</strong></th>
                                <th><strong>UX Block Template Được Gán</strong></th>
                                <th style="width: 240px; text-align: right;"><strong>Hành Động</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $post_types as $pt_slug => $pt_obj ) : 
                                $rule_key = 'single_' . $pt_slug;
                                $selected_block_id = ! empty( $template_rules[ $rule_key ] ) ? intval( $template_rules[ $rule_key ] ) : '';
                            ?>
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <strong style="font-size: 14px; color: #0f172a;"><?php echo esc_html( $pt_obj->labels->singular_name ?: $pt_obj->label ); ?></strong>
                                        <div style="font-size: 12px; color: #64748b; font-family: monospace;">Slug: <?php echo esc_html( $pt_slug ); ?></div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <select name="uf_template_rules[<?php echo esc_attr( $rule_key ); ?>]" style="width: 100%; max-width: 450px; font-size: 13.5px; border-radius: 6px;">
                                            <?php foreach ( $ux_blocks as $b_id => $b_label ) : ?>
                                                <option value="<?php echo esc_attr( $b_id ); ?>" <?php selected( $selected_block_id, $b_id ); ?>>
                                                    <?php echo esc_html( $b_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="vertical-align: middle; text-align: right;">
                                        <?php if ( ! empty( $selected_block_id ) ) : ?>
                                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $selected_block_id . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #2563eb; border-color: #1d4ed8;">
                                                <span class="dashicons dashicons-edit" style="font-size: 14px; margin-top: 2px;"></span>
                                                <?php _e('Sửa trong UX Builder', 'vibecode'); ?>
                                            </a>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 12.5px;"><?php _e('Giao diện gốc theme', 'vibecode'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Taxonomy & Archive Templates -->
                <div class="vbc-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2><span class="dashicons dashicons-category"></span> 2. Giao Diện Danh Mục & Lưu Trữ (Taxonomies)</h2>
                            <p style="color: #64748b; font-size: 13px; margin: 4px 0 0 0;">
                                Gán UX Block template làm giao diện hiển thị cho các trang Chuyên mục (Category), Thẻ (Tag), Danh mục sản phẩm.
                            </p>
                        </div>
                        <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=ultimate-flatsome&uf_action=create_sample_template&type=category' ), 'uf_create_sample_template' ); ?>" class="button button-secondary" style="font-weight: 700; height: 38px; display: inline-flex; align-items: center; gap: 6px; border-color: #059669; color: #059669;">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('✨ Tạo Mẫu Danh Mục Chuẩn UX Builder 1-Click', 'vibecode'); ?>
                        </a>
                    </div>

                    <table class="widefat striped" style="border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr>
                                <th style="width: 220px;"><strong>Phân Loại (Taxonomy)</strong></th>
                                <th><strong>UX Block Template Được Gán</strong></th>
                                <th style="width: 240px; text-align: right;"><strong>Hành Động</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $taxonomies as $tax_slug => $tax_obj ) : 
                                $rule_key = 'taxonomy_' . $tax_slug;
                                $selected_block_id = ! empty( $template_rules[ $rule_key ] ) ? intval( $template_rules[ $rule_key ] ) : '';
                            ?>
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <strong style="font-size: 14px; color: #0f172a;"><?php echo esc_html( $tax_obj->labels->singular_name ?: $tax_obj->label ); ?></strong>
                                        <div style="font-size: 12px; color: #64748b; font-family: monospace;">Taxonomy: <?php echo esc_html( $tax_slug ); ?></div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <select name="uf_template_rules[<?php echo esc_attr( $rule_key ); ?>]" style="width: 100%; max-width: 450px; font-size: 13.5px; border-radius: 6px;">
                                            <?php foreach ( $ux_blocks as $b_id => $b_label ) : ?>
                                                <option value="<?php echo esc_attr( $b_id ); ?>" <?php selected( $selected_block_id, $b_id ); ?>>
                                                    <?php echo esc_html( $b_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="vertical-align: middle; text-align: right;">
                                        <?php if ( ! empty( $selected_block_id ) ) : ?>
                                            <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $selected_block_id . '&action=edit&app=uxbuilder' ) ); ?>" target="_blank" class="button button-primary" style="font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #2563eb; border-color: #1d4ed8;">
                                                <span class="dashicons dashicons-edit" style="font-size: 14px; margin-top: 2px;"></span>
                                                <?php _e('Sửa trong UX Builder', 'vibecode'); ?>
                                            </a>
                                        <?php else : ?>
                                            <span style="color: #94a3b8; font-size: 12.5px;"><?php _e('Giao diện gốc theme', 'vibecode'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-bottom: 30px;">
                    <button type="submit" class="vbc-btn-save" style="font-size: 16px; padding: 14px 36px;">
                        <span class="dashicons dashicons-saved" style="font-size: 18px;"></span>
                        <?php _e('Lưu Toàn Bộ Cấu Hình Templates', 'vibecode'); ?>
                    </button>
                </div>
            </form>

            <!-- 3. Cẩm Nang Dynamic Tags Cho UX Builder -->
            <div class="vbc-card">
                <h2><span class="dashicons dashicons-shortcode"></span> 3. Danh Sách Thẻ Động (Dynamic Tags) Cho UX Builder</h2>
                <p style="color: #64748b; font-size: 13px;">
                    Khi mở UX Builder để thiết kế UX Block template, bạn có thể kéo thả trực tiếp các element trong nhóm <strong>"Ultimate Flatsome Templates"</strong> hoặc chèn các shortcodes bên dưới:
                </p>

                <table class="widefat striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 220px;"><strong>Shortcode</strong></th>
                            <th style="width: 320px;"><strong>Cú Pháp Mẫu</strong></th>
                            <th><strong>Mô Tả Chức Năng</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[uf_post_title]</code></td>
                            <td><code>[uf_post_title tag="h1" font_size="36px" color="#0f172a"]</code></td>
                            <td>Hiển thị Tiêu đề bài viết / Tên chuyên mục tự động theo bài viết đang xem.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_content]</code></td>
                            <td><code>[uf_post_content]</code></td>
                            <td>Hiển thị toàn bộ nội dung bài viết gốc (<code>the_content</code>).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_thumbnail]</code></td>
                            <td><code>[uf_post_thumbnail border_radius="16px" aspect_ratio="16/9"]</code></td>
                            <td>Hiển thị Ảnh đại diện (Featured Image) bài viết với bo góc và tỷ lệ khung hình tùy biến.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_meta]</code></td>
                            <td><code>[uf_post_meta type="date|author|categories|comments_count"]</code></td>
                            <td>Hiển thị Ngày đăng, Tác giả, Chuyên mục hoặc Số bình luận.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_author]</code></td>
                            <td><code>[uf_post_author avatar_size="80" show_bio="yes"]</code></td>
                            <td>Hiển thị Box Tác Giả chuyên nghiệp (Avatar, Tên tác giả, Tiểu sử và link bài viết).</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_comments]</code></td>
                            <td><code>[uf_post_comments]</code></td>
                            <td>Hiển thị Khung bình luận và Form thảo luận chuẩn WordPress/Flatsome.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_navigation]</code></td>
                            <td><code>[uf_post_navigation]</code></td>
                            <td>Khối điều hướng Bài trước / Bài kế tiếp với giao diện thẻ card hiện đại.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_post_terms]</code></td>
                            <td><code>[uf_post_terms taxonomy="category" bg_color="#eff6ff"]</code></td>
                            <td>Hiển thị danh sách các chuyên mục dạng badge pills bo góc.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_breadcrumb]</code></td>
                            <td><code>[uf_breadcrumb]</code></td>
                            <td>Thanh điều hướng phân cấp Breadcrumbs chuẩn Flatsome.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_archive_title]</code></td>
                            <td><code>[uf_archive_title tag="h1" font_size="38px"]</code></td>
                            <td>Tiêu đề trang Category & Mô tả Chuyên mục.</td>
                        </tr>
                        <tr>
                            <td><code>[uf_archive_posts]</code></td>
                            <td><code>[uf_archive_posts columns="3" image_height="220px"]</code></td>
                            <td>Lưới danh sách bài viết thuộc Category đang xem kèm Phân trang Flatsome chuẩn.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

