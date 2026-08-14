/**
 * ============================================================================
 * ULTIMATE FLATSOME VIBECODE - WORDPRESS MEDIA MODAL SVG ICON EXTENSION
 * ============================================================================
 * Tích hợp tab thứ 3 "SVG Icon" vào WordPress Media Library Modal chuẩn (wp.media).
 * Cho phép người dùng chọn Icon trực quan theo hình ảnh/danh mục mà không cần nhớ tên.
 * ============================================================================
 */

(function($) {
    'use strict';

    // 1. KHO ICON DỮ LIỆU ĐA DẠNG ĐƯỢC PHÂN THEO DANH MỤC TRỰC QUAN
    var VBC_ICON_CATEGORIES = [
        { id: 'all', name: '✨ Tất cả Icons' },
        { id: 'security', name: '🛡️ Bảo mật & Uy tín' },
        { id: 'business', name: '🚀 Kinh doanh & Tăng trưởng' },
        { id: 'ecommerce', name: '🛒 Bán hàng & E-commerce' },
        { id: 'contact', name: '📞 Liên hệ & Hỗ trợ' },
        { id: 'tech', name: '💻 Công nghệ & Website' },
        { id: 'interface', name: '⚙️ Giao diện & Tiện ích' },
        { id: 'arrows', name: '➡️ Mũi tên & Điều hướng' },
        { id: 'social', name: '💖 Tương tác & Người dùng' },
        { id: 'brands', name: '🌐 Thương hiệu & Mạng XH' }
    ];

    var VBC_ICONS_DATA = [
        // Security & Trust
        { id: 'shield-check', name: 'Khiên bảo vệ hoàn thành', cat: 'security', pack: 'lucide', keywords: 'shield check verified protect security uy tin' },
        { id: 'shield', name: 'Khiên bảo vệ', cat: 'security', pack: 'lucide', keywords: 'shield security protect khien' },
        { id: 'shield-alert', name: 'Cảnh báo bảo mật', cat: 'security', pack: 'lucide', keywords: 'shield alert warning danger' },
        { id: 'award', name: 'Huy chương danh dự', cat: 'security', pack: 'lucide', keywords: 'award medal badge winner reward huy chuong' },
        { id: 'lock', name: 'Khóa bảo mật', cat: 'security', pack: 'lucide', keywords: 'lock security private password khoa' },
        { id: 'key', name: 'Chìa khóa', cat: 'security', pack: 'lucide', keywords: 'key security access password chia khoa' },
        { id: 'check-circle', name: 'Tích tròn thành công', cat: 'security', pack: 'lucide', keywords: 'check circle success done verified hoan thanh' },
        { id: 'check-check', name: 'Đã xác thực kép', cat: 'security', pack: 'lucide', keywords: 'check double verified xac thuc' },
        { id: 'badge-check', name: 'Huy hiệu tích xanh', cat: 'security', pack: 'lucide', keywords: 'badge check verified official tich xanh' },
        { id: 'fingerprint', name: 'Vân tay bảo mật', cat: 'security', pack: 'lucide', keywords: 'fingerprint biometric security van tay' },

        // Business & Growth
        { id: 'trending-up', name: 'Biểu đồ tăng trưởng', cat: 'business', pack: 'lucide', keywords: 'trending up growth success chart stock tang truong' },
        { id: 'rocket', name: 'Tên lửa tăng tốc', cat: 'business', pack: 'lucide', keywords: 'rocket launch startup boost fast ten lua' },
        { id: 'target', name: 'Mục tiêu chiến lược', cat: 'business', pack: 'lucide', keywords: 'target goal focus aim muc tieu' },
        { id: 'briefcase', name: 'Cặp doanh nhân / Dự án', cat: 'business', pack: 'lucide', keywords: 'briefcase business job work bag du an' },
        { id: 'dollar-sign', name: 'Đô la / Doanh thu', cat: 'business', pack: 'lucide', keywords: 'dollar money cash finance price tien' },
        { id: 'coins', name: 'Tiền xu / Tài chính', cat: 'business', pack: 'lucide', keywords: 'coins money wealth finance gold tai chinh' },
        { id: 'pie-chart', name: 'Biểu đồ tròn', cat: 'business', pack: 'lucide', keywords: 'chart pie analytics stats bieu do' },
        { id: 'bar-chart-3', name: 'Biểu đồ cột', cat: 'business', pack: 'lucide', keywords: 'chart bar analytics report bieu do' },
        { id: 'percent', name: 'Phần trăm ưu đãi', cat: 'business', pack: 'lucide', keywords: 'percent discount sale offer giam gia' },
        { id: 'sparkles', name: 'Lấp lánh / AI Thông minh', cat: 'business', pack: 'lucide', keywords: 'sparkles magic ai star new premium thong minh' },
        { id: 'zap', name: 'Tia sét / Nhanh chóng', cat: 'business', pack: 'lucide', keywords: 'zap lightning speed fast power energy set' },

        // E-commerce & Shopping
        { id: 'shopping-cart', name: 'Giỏ hàng mua sắm', cat: 'ecommerce', pack: 'lucide', keywords: 'cart shopping store buy ecommerce gio hang' },
        { id: 'shopping-bag', name: 'Túi mua sắm', cat: 'ecommerce', pack: 'lucide', keywords: 'bag shopping store market tui' },
        { id: 'credit-card', name: 'Thẻ thanh toán', cat: 'ecommerce', pack: 'lucide', keywords: 'card credit payment bank visa master the' },
        { id: 'package', name: 'Kiện hàng / Đóng gói', cat: 'ecommerce', pack: 'lucide', keywords: 'package box delivery parcel shipping hang' },
        { id: 'truck', name: 'Xe giao hàng', cat: 'ecommerce', pack: 'lucide', keywords: 'truck delivery shipping express transport van chuyen' },
        { id: 'tag', name: 'Thẻ giá / Khuyến mãi', cat: 'ecommerce', pack: 'lucide', keywords: 'tag price label discount sale gia' },
        { id: 'gift', name: 'Hộp quà tặng', cat: 'ecommerce', pack: 'lucide', keywords: 'gift present reward bonus qua' },
        { id: 'store', name: 'Cửa hàng', cat: 'ecommerce', pack: 'lucide', keywords: 'store shop market boutique cua hang' },
        { id: 'receipt', name: 'Hóa đơn thanh toán', cat: 'ecommerce', pack: 'lucide', keywords: 'receipt bill invoice paper payment hoa don' },

        // Contact & Support
        { id: 'phone', name: 'Điện thoại liên hệ', cat: 'contact', pack: 'lucide', keywords: 'phone call hotline contact mobile dien thoai' },
        { id: 'phone-call', name: 'Đang gọi điện', cat: 'contact', pack: 'lucide', keywords: 'phone call dial ring ring goi dien' },
        { id: 'mail', name: 'Hòm thư điện tử', cat: 'contact', pack: 'lucide', keywords: 'mail email message envelope inbox thu' },
        { id: 'message-square', name: 'Tin nhắn / Trao đổi', cat: 'contact', pack: 'lucide', keywords: 'message chat comment talk sms tin nhan' },
        { id: 'message-circle', name: 'Hội thoại tròn', cat: 'contact', pack: 'lucide', keywords: 'chat speech bubble message hoi thoai' },
        { id: 'send', name: 'Gửi tin nhắn', cat: 'contact', pack: 'lucide', keywords: 'send message fly paper aircraft gui' },
        { id: 'headphones', name: 'Tai nghe hỗ trợ viên', cat: 'contact', pack: 'lucide', keywords: 'headphones support agent customer audio tai nghe' },
        { id: 'map-pin', name: 'Địa chỉ vị trí', cat: 'contact', pack: 'lucide', keywords: 'map pin location address marker place dia chi' },
        { id: 'clock', name: 'Đồng hồ thời gian', cat: 'contact', pack: 'lucide', keywords: 'clock time hour minute schedule watch dong ho' },
        { id: 'calendar', name: 'Lịch hẹn ngày', cat: 'contact', pack: 'lucide', keywords: 'calendar date schedule event month lich' },

        // Tech & Website
        { id: 'globe', name: 'Quả địa cầu / Website', cat: 'tech', pack: 'lucide', keywords: 'globe web internet world domain online website' },
        { id: 'monitor', name: 'Màn hình máy tính', cat: 'tech', pack: 'lucide', keywords: 'monitor screen desktop display computer man hinh' },
        { id: 'laptop', name: 'Máy tính xách tay', cat: 'tech', pack: 'lucide', keywords: 'laptop computer macbook pc notebook may tinh' },
        { id: 'smartphone', name: 'Điện thoại di động', cat: 'tech', pack: 'lucide', keywords: 'smartphone phone mobile iphone android di dong' },
        { id: 'server', name: 'Máy chủ Server', cat: 'tech', pack: 'lucide', keywords: 'server cloud host hosting rack database may chu' },
        { id: 'database', name: 'Cơ sở dữ liệu', cat: 'tech', pack: 'lucide', keywords: 'database sql storage data du lieu' },
        { id: 'cpu', name: 'Vi xử lý CPU', cat: 'tech', pack: 'lucide', keywords: 'cpu chip processor hardware vi xu ly' },
        { id: 'code', name: 'Mã nguồn / Lập trình', cat: 'tech', pack: 'lucide', keywords: 'code programming html dev software lap trinh' },
        { id: 'terminal', name: 'Dòng lệnh Terminal', cat: 'tech', pack: 'lucide', keywords: 'terminal console command prompt cli' },
        { id: 'wifi', name: 'Sóng WiFi mạng', cat: 'tech', pack: 'lucide', keywords: 'wifi internet connection wireless signal mang' },
        { id: 'cloud', name: 'Điện toán đám mây', cat: 'tech', pack: 'lucide', keywords: 'cloud hosting storage drive sync dam may' },
        { id: 'cloud-lightning', name: 'Máy chủ tốc độ cao', cat: 'tech', pack: 'lucide', keywords: 'cloud lightning fast speed server' },
        { id: 'activity', name: 'Hiệu năng nhịp tim', cat: 'tech', pack: 'lucide', keywords: 'activity pulse heart rate performance monitor hieu nang' },

        // Interface & General UI
        { id: 'home', name: 'Trang chủ Home', cat: 'interface', pack: 'lucide', keywords: 'home house main page dashboard trang chu' },
        { id: 'search', name: 'Kính lúp tìm kiếm', cat: 'interface', pack: 'lucide', keywords: 'search find magnifying glass tim kiem' },
        { id: 'settings', name: 'Bánh răng cài đặt', cat: 'interface', pack: 'lucide', keywords: 'settings gear options preferences tools cai dat' },
        { id: 'menu', name: 'Menu điều hướng', cat: 'interface', pack: 'lucide', keywords: 'menu hamburger nav navigation list menu' },
        { id: 'check', name: 'Dấu tích kiểm', cat: 'interface', pack: 'lucide', keywords: 'check tick ok yes approve tich' },
        { id: 'x', name: 'Dấu đóng / Xóa', cat: 'interface', pack: 'lucide', keywords: 'x close cancel delete remove xoa' },
        { id: 'plus', name: 'Dấu cộng thêm', cat: 'interface', pack: 'lucide', keywords: 'plus add new create cong' },
        { id: 'minus', name: 'Dấu trừ', cat: 'interface', pack: 'lucide', keywords: 'minus remove subtract tru' },
        { id: 'eye', name: 'Con mắt / Xem trước', cat: 'interface', pack: 'lucide', keywords: 'eye view preview watch visible xem' },
        { id: 'eye-off', name: 'Ẩn nội dung', cat: 'interface', pack: 'lucide', keywords: 'eye off hide hidden invisible an' },
        { id: 'refresh-cw', name: 'Làm mới / Đồng bộ', cat: 'interface', pack: 'lucide', keywords: 'refresh sync reload update rotate lam moi' },
        { id: 'download', name: 'Tải xuống', cat: 'interface', pack: 'lucide', keywords: 'download save get export tai xuong' },
        { id: 'upload', name: 'Tải lên', cat: 'interface', pack: 'lucide', keywords: 'upload send import file tai len' },
        { id: 'trash-2', name: 'Thùng rác xóa', cat: 'interface', pack: 'lucide', keywords: 'trash delete remove bin thung rac' },
        { id: 'edit-3', name: 'Chỉnh sửa bút', cat: 'interface', pack: 'lucide', keywords: 'edit pen write modify pencil sua' },
        { id: 'file-text', name: 'Tài liệu văn bản', cat: 'interface', pack: 'lucide', keywords: 'file text document paper doc page tai lieu' },
        { id: 'folder', name: 'Thư mục tệp', cat: 'interface', pack: 'lucide', keywords: 'folder directory file storage thu muc' },
        { id: 'link', name: 'Liên kết URL', cat: 'interface', pack: 'lucide', keywords: 'link url href anchor chain lien ket' },
        { id: 'external-link', name: 'Mở liên kết ngoài', cat: 'interface', pack: 'lucide', keywords: 'external link open new window tab lien ket ngoai' },
        { id: 'help-circle', name: 'Hỏi đáp hỗ trợ', cat: 'interface', pack: 'lucide', keywords: 'help question circle faq info hoi dap' },
        { id: 'alert-triangle', name: 'Cảnh báo tam giác', cat: 'interface', pack: 'lucide', keywords: 'alert warning caution triangle canh bao' },
        { id: 'info', name: 'Thông tin chi tiết', cat: 'interface', pack: 'lucide', keywords: 'info information detail about thong tin' },

        // Arrows & Navigation
        { id: 'arrow-right', name: 'Mũi tên sang phải', cat: 'arrows', pack: 'lucide', keywords: 'arrow right next forward direction mui ten phai' },
        { id: 'arrow-left', name: 'Mũi tên sang trái', cat: 'arrows', pack: 'lucide', keywords: 'arrow left back previous direction mui ten trai' },
        { id: 'arrow-up', name: 'Mũi tên lên trên', cat: 'arrows', pack: 'lucide', keywords: 'arrow up top direction mui ten len' },
        { id: 'arrow-down', name: 'Mũi tên xuống dưới', cat: 'arrows', pack: 'lucide', keywords: 'arrow down bottom direction mui ten xuong' },
        { id: 'chevron-right', name: 'Dấu nhọn phải', cat: 'arrows', pack: 'lucide', keywords: 'chevron right angle next' },
        { id: 'chevron-left', name: 'Dấu nhọn trái', cat: 'arrows', pack: 'lucide', keywords: 'chevron left angle prev' },
        { id: 'chevron-down', name: 'Dấu nhọn xuống', cat: 'arrows', pack: 'lucide', keywords: 'chevron down angle dropdown' },
        { id: 'corner-down-right', name: 'Rẽ nhánh sang phải', cat: 'arrows', pack: 'lucide', keywords: 'corner down right sub reply' },

        // Social & Users
        { id: 'user', name: 'Tài khoản người dùng', cat: 'social', pack: 'lucide', keywords: 'user account person profile avatar nguoi dung' },
        { id: 'users', name: 'Đội ngũ / Nhóm khách hàng', cat: 'social', pack: 'lucide', keywords: 'users group team people community doi ngu' },
        { id: 'user-check', name: 'Người dùng xác thực', cat: 'social', pack: 'lucide', keywords: 'user check verified member' },
        { id: 'user-plus', name: 'Thêm thành viên', cat: 'social', pack: 'lucide', keywords: 'user plus add member register them' },
        { id: 'heart', name: 'Trái tim yêu thích', cat: 'social', pack: 'lucide', keywords: 'heart love like favorite health trai tim' },
        { id: 'star', name: 'Ngôi sao đánh giá', cat: 'social', pack: 'lucide', keywords: 'star rating favorite review bookmark ngoi sao' },
        { id: 'thumbs-up', name: 'Thích / Đánh giá tốt', cat: 'social', pack: 'lucide', keywords: 'thumbs up like good approve praise thich' },
        { id: 'share-2', name: 'Chia sẻ liên kết', cat: 'social', pack: 'lucide', keywords: 'share link social network send chia se' },

        // Brands & Networks
        { id: 'fa-brands fa-facebook', name: 'Facebook Logo', cat: 'brands', pack: 'fontawesome', keywords: 'facebook social network meta' },
        { id: 'fa-brands fa-google', name: 'Google Logo', cat: 'brands', pack: 'fontawesome', keywords: 'google search gsuite' },
        { id: 'fa-brands fa-youtube', name: 'YouTube Logo', cat: 'brands', pack: 'fontawesome', keywords: 'youtube video stream media' },
        { id: 'fa-brands fa-tiktok', name: 'TikTok Logo', cat: 'brands', pack: 'fontawesome', keywords: 'tiktok video social trend' },
        { id: 'fa-brands fa-instagram', name: 'Instagram Logo', cat: 'brands', pack: 'fontawesome', keywords: 'instagram photo social story' },
        { id: 'fa-brands fa-twitter', name: 'Twitter / X Logo', cat: 'brands', pack: 'fontawesome', keywords: 'twitter x social tweet' },
        { id: 'fa-brands fa-github', name: 'GitHub Logo', cat: 'brands', pack: 'fontawesome', keywords: 'github code repo git developer' },
        { id: 'fa-brands fa-wordpress', name: 'WordPress Logo', cat: 'brands', pack: 'fontawesome', keywords: 'wordpress cms blog web' }
    ];

    // 2. TÍCH HỢP TAB "SVG ICON" VÀO WORDPRESS MEDIA MODAL (wp.media)
    function setupWordPressMediaModalExtension() {
        if (typeof wp === 'undefined' || !wp.media || !wp.media.view || !wp.media.view.MediaFrame || !wp.media.view.MediaFrame.Select) {
            return;
        }

        var MediaFrameSelect = wp.media.view.MediaFrame.Select;

        // A. Thêm Router Tab "SVG Icon"
        var originalBrowseRouter = MediaFrameSelect.prototype.browseRouter;
        MediaFrameSelect.prototype.browseRouter = function(routerView, state) {
            originalBrowseRouter.apply(this, arguments);
            routerView.set({
                'vbc_svg_icons': {
                    text: 'SVG Icon',
                    priority: 50
                }
            });
        };

        // B. Bind event khi tab SVG Icon được kích hoạt
        var originalBindHandlers = MediaFrameSelect.prototype.bindHandlers;
        MediaFrameSelect.prototype.bindHandlers = function() {
            originalBindHandlers.apply(this, arguments);
            this.on('content:create:vbc_svg_icons', this.vbcRenderSvgIconTab, this);
            this.on('content:render:vbc_svg_icons', this.vbcRenderSvgIconTab, this);
        };

        // C. Renderer View cho Tab SVG Icon
        MediaFrameSelect.prototype.vbcRenderSvgIconTab = function(contentRegion) {
            var state = this.state();
            if (this.$el) {
                this.$el.removeClass('hide-toolbar');
            }
            var browserView = new wp.media.view.VbcSvgIconBrowser({
                controller: this,
                model: state
            });

            if (contentRegion) {
                contentRegion.view = browserView;
                if (typeof contentRegion.set === 'function') {
                    try { contentRegion.set(browserView); } catch(e) {}
                }
            }
            if (this.content && typeof this.content.set === 'function') {
                try { this.content.set(browserView); } catch(e) {}
            }
        };

        // D. Khởi tạo Backbone View cho giao diện kho Icon
        wp.media.view.VbcSvgIconBrowser = wp.media.View.extend({
            className: 'vbc-svg-media-browser-container media-core-ui',
            selectedIcon: null,

            initialize: function(options) {
                wp.media.View.prototype.initialize.apply(this, arguments);
                this.currentCategory = 'all';
                this.searchQuery = '';
            },

            render: function() {
                var self = this;
                var html = '';

                // Header Toolbar (Tìm kiếm & Danh mục)
                html += '<div class="vbc-svg-toolbar">';
                html += '  <div class="vbc-search-box">';
                html += '    <i data-lucide="search" style="width:16px;height:16px;color:#94a3b8;position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;"></i>';
                html += '    <input type="text" class="vbc-search-input" placeholder="🔍 Tìm kiếm icon nhanh theo chủ đề (shield, check, phone, rocket, star, cart...)" value="' + this.searchQuery + '" />';
                html += '  </div>';

                html += '  <div class="vbc-categories-wrap">';
                VBC_ICON_CATEGORIES.forEach(function(cat) {
                    var activeCls = (cat.id === self.currentCategory) ? 'active' : '';
                    html += '<button type="button" class="vbc-category-tab ' + activeCls + '" data-cat="' + cat.id + '">' + cat.name + '</button>';
                });
                html += '  </div>';
                html += '</div>';

                // Main Content (Grid Gallery + Details Sidebar)
                html += '<div class="vbc-svg-content-layout">';
                html += '  <div class="vbc-svg-grid-scroll">';
                html += '    <div class="vbc-svg-grid-inner">';
                html +=        this.renderIconCardsHtml();
                html += '    </div>';
                html += '  </div>';

                // Sidebar Info Panel
                html += '  <div class="vbc-svg-sidebar-panel">';
                html +=      this.renderSidebarHtml();
                html += '  </div>';
                html += '</div>';

                this.$el.html(html);

                // Gắn Event Listeners trực tiếp lên DOM container của View
                this.bindViewDomEvents();

                // Kích hoạt render Lucide icons
                setTimeout(function() {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 50);

                return this;
            },

            bindViewDomEvents: function() {
                var self = this;

                // 1. Search Input
                this.$el.off('input', '.vbc-search-input').on('input', '.vbc-search-input', function(e) {
                    self.searchQuery = $(this).val();
                    self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });

                // 2. Category Tab Click
                this.$el.off('click', '.vbc-category-tab').on('click', '.vbc-category-tab', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $btn = $(this);
                    self.$el.find('.vbc-category-tab').removeClass('active');
                    $btn.addClass('active');
                    self.currentCategory = $btn.attr('data-cat');
                    self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                });

                // 3. Icon Card Click
                this.$el.off('click', '.vbc-icon-card').on('click', '.vbc-icon-card', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.selectIconCard($(this));
                });

                // 4. Icon Card Double Click
                this.$el.off('dblclick', '.vbc-icon-card').on('dblclick', '.vbc-icon-card', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.selectIconCard($(this));
                    self.applySelectionAndClose();
                });
            },

            selectIconCard: function($card) {
                var iconId = $card.attr('data-icon-id');
                var pack = $card.attr('data-pack');
                var name = $card.attr('data-name');

                this.selectedIcon = { id: iconId, pack: pack, name: name };

                this.$el.find('.vbc-icon-card').removeClass('selected');
                $card.addClass('selected');

                this.$el.find('.vbc-svg-sidebar-panel').html(this.renderSidebarHtml());
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Tạo đối tượng Attachment Model tương thích WordPress Media Selection
                var attachment = new wp.media.model.Attachment({
                    id: 'icon:' + iconId,
                    title: name,
                    filename: iconId + '.svg',
                    url: 'icon:' + iconId,
                    type: 'image',
                    subtype: 'svg+xml',
                    sizes: {
                        full: { url: 'icon:' + iconId }
                    }
                });

                // Cập nhật selection của Media Frame
                if (this.controller && this.controller.state) {
                    var state = this.controller.state();
                    if (state && state.get('selection')) {
                        state.get('selection').reset([attachment]);
                    }
                }

                // Kích hoạt ngay nút "Select" / "Use this image" ở góc dưới phải
                $('.media-button-select, .media-frame-toolbar .button-primary')
                    .prop('disabled', false)
                    .removeAttr('disabled')
                    .removeClass('disabled');
            },

            applySelectionAndClose: function() {
                var $selectBtn = $('.media-button-select, .media-frame-toolbar .button-primary');
                if ($selectBtn.length > 0) {
                    $selectBtn.trigger('click');
                }
            },

            renderIconCardsHtml: function() {
                var self = this;
                var query = this.searchQuery.toLowerCase().trim();
                var cat = this.currentCategory;
                var count = 0;
                var cardsHtml = '';

                VBC_ICONS_DATA.forEach(function(icon) {
                    // Lọc theo Category
                    if (cat !== 'all' && icon.cat !== cat) {
                        return;
                    }
                    // Lọc theo Search Query
                    if (query) {
                        var match = (icon.id.toLowerCase().indexOf(query) !== -1) ||
                                    (icon.name.toLowerCase().indexOf(query) !== -1) ||
                                    (icon.keywords && icon.keywords.toLowerCase().indexOf(query) !== -1);
                        if (!match) return;
                    }

                    count++;
                    var isSelected = self.selectedIcon && self.selectedIcon.id === icon.id;
                    var selectedClass = isSelected ? 'selected' : '';

                    cardsHtml += '<div class="vbc-icon-card ' + selectedClass + '" data-icon-id="' + icon.id + '" data-pack="' + icon.pack + '" data-name="' + icon.name + '">';
                    cardsHtml += '  <div class="vbc-icon-render-box">';
                    if (icon.pack === 'fontawesome') {
                        cardsHtml += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
                    } else if (icon.pack === 'remix') {
                        cardsHtml += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
                    } else {
                        cardsHtml += '    <i data-lucide="' + icon.id + '" style="width:32px;height:32px;"></i>';
                    }
                    cardsHtml += '  </div>';
                    cardsHtml += '  <div class="vbc-icon-title-label">' + icon.name + '</div>';
                    cardsHtml += '  <div class="vbc-check-badge">✓</div>';
                    cardsHtml += '</div>';
                });

                if (count === 0) {
                    return '<div class="vbc-no-results"><div style="font-size:36px;margin-bottom:10px;">🔍</div>Không tìm thấy icon nào phù hợp với từ khóa "<strong>' + this.searchQuery + '</strong>".</div>';
                }

                return cardsHtml;
            },

            renderSidebarHtml: function() {
                if (!this.selectedIcon) {
                    return '<div class="vbc-sidebar-empty">' +
                           '  <div style="font-size:42px;margin-bottom:12px;opacity:0.6;">🎨</div>' +
                           '  <div style="font-weight:700;font-size:14px;color:#334155;margin-bottom:6px;">Chưa chọn Icon</div>' +
                           '  <div style="font-size:12px;color:#64748b;">Nhấp vào một icon bất kỳ trong danh sách bên trái để xem trước và chèn vào trang.</div>' +
                           '</div>';
                }

                var icon = this.selectedIcon;
                var previewHtml = '';
                if (icon.pack === 'fontawesome') {
                    previewHtml = '<i class="' + icon.id + '" style="font-size:56px;color:#2563eb;"></i>';
                } else if (icon.pack === 'remix') {
                    previewHtml = '<i class="' + icon.id + '" style="font-size:56px;color:#2563eb;"></i>';
                } else {
                    previewHtml = '<i data-lucide="' + icon.id + '" style="width:56px;height:56px;color:#2563eb;"></i>';
                }

                var html = '<div class="vbc-sidebar-detail">';
                html += '  <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">Chi Tiết Icon Đã Chọn</h3>';
                html += '  <div class="vbc-sidebar-preview-canvas">' + previewHtml + '</div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Tên:</strong> <span>' + icon.name + '</span></div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Định dạng:</strong> <span>SVG Vector (' + icon.pack.toUpperCase() + ')</span></div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Mã Icon:</strong> <code>' + icon.id + '</code></div>';
                html += '  <div class="vbc-sidebar-tips">✓ Tự động hiển thị sắc nét 100% trên mọi màn hình Retina / 4K và tương thích responsive Flatsome UX Builder.</div>';
                html += '</div>';

                return html;
            }
        });

        console.log('[VBC Media Modal] Đã tích hợp thành công tab "SVG Icon" vào WordPress Media Library.');
    }

    // 3. GLOBAL DELEGATED FALLBACK (Đảm bảo bắt 100% sự kiện click)
    $(document).on('click', '.vbc-category-tab', function(e) {
        var $btn = $(this);
        var $container = $btn.closest('.vbc-svg-media-browser-container');
        if ($container.length === 0) return;

        $container.find('.vbc-category-tab').removeClass('active');
        $btn.addClass('active');

        var cat = $btn.attr('data-cat');
        var query = $container.find('.vbc-search-input').val() || '';

        var html = '';
        var count = 0;
        VBC_ICONS_DATA.forEach(function(icon) {
            if (cat !== 'all' && icon.cat !== cat) return;
            if (query) {
                var q = query.toLowerCase().trim();
                var match = (icon.id.toLowerCase().indexOf(q) !== -1) ||
                            (icon.name.toLowerCase().indexOf(q) !== -1) ||
                            (icon.keywords && icon.keywords.toLowerCase().indexOf(q) !== -1);
                if (!match) return;
            }
            count++;
            html += '<div class="vbc-icon-card" data-icon-id="' + icon.id + '" data-pack="' + icon.pack + '" data-name="' + icon.name + '">';
            html += '  <div class="vbc-icon-render-box">';
            if (icon.pack === 'fontawesome') {
                html += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
            } else if (icon.pack === 'remix') {
                html += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
            } else {
                html += '    <i data-lucide="' + icon.id + '" style="width:32px;height:32px;"></i>';
            }
            html += '  </div>';
            html += '  <div class="vbc-icon-title-label">' + icon.name + '</div>';
            html += '  <div class="vbc-check-badge">✓</div>';
            html += '</div>';
        });

        if (count === 0) {
            html = '<div class="vbc-no-results"><div style="font-size:36px;margin-bottom:10px;">🔍</div>Không tìm thấy icon nào phù hợp.</div>';
        }

        $container.find('.vbc-svg-grid-inner').html(html);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    $(document).on('click', '.vbc-icon-card', function(e) {
        var $card = $(this);
        var $container = $card.closest('.vbc-svg-media-browser-container');
        if ($container.length === 0) return;

        var iconId = $card.attr('data-icon-id');
        var pack = $card.attr('data-pack');
        var name = $card.attr('data-name');

        $container.find('.vbc-icon-card').removeClass('selected');
        $card.addClass('selected');

        // Render preview sidebar
        var previewHtml = '';
        if (pack === 'fontawesome') {
            previewHtml = '<i class="' + iconId + '" style="font-size:56px;color:#2563eb;"></i>';
        } else if (pack === 'remix') {
            previewHtml = '<i class="' + iconId + '" style="font-size:56px;color:#2563eb;"></i>';
        } else {
            previewHtml = '<i data-lucide="' + iconId + '" style="width:56px;height:56px;color:#2563eb;"></i>';
        }

        var sidebarHtml = '<div class="vbc-sidebar-detail">' +
                          '  <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">Chi Tiết Icon Đã Chọn</h3>' +
                          '  <div class="vbc-sidebar-preview-canvas">' + previewHtml + '</div>' +
                          '  <div class="vbc-sidebar-info-row"><strong>Tên:</strong> <span>' + name + '</span></div>' +
                          '  <div class="vbc-sidebar-info-row"><strong>Định dạng:</strong> <span>SVG Vector (' + pack.toUpperCase() + ')</span></div>' +
                          '  <div class="vbc-sidebar-info-row"><strong>Mã Icon:</strong> <code>' + iconId + '</code></div>' +
                          '  <div class="vbc-sidebar-tips">✓ Tự động hiển thị sắc nét 100% trên mọi màn hình Retina / 4K và tương thích responsive Flatsome UX Builder.</div>' +
                          '</div>';

        $container.find('.vbc-svg-sidebar-panel').html(sidebarHtml);
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Tạo attachment model cho WordPress media frame
        if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
            var attachment = new wp.media.model.Attachment({
                id: 'icon:' + iconId,
                title: name,
                filename: iconId + '.svg',
                url: 'icon:' + iconId,
                type: 'image',
                subtype: 'svg+xml',
                sizes: { full: { url: 'icon:' + iconId } }
            });
            var state = wp.media.frame.state();
            if (state && state.get('selection')) {
                state.get('selection').reset([attachment]);
            }
        }

        // Kích hoạt ngay nút "Use this image" ở góc dưới phải
        $('.media-button-select, .media-frame-toolbar .button-primary')
            .prop('disabled', false)
            .removeAttr('disabled')
            .removeClass('disabled');
    });

    $(document).on('dblclick', '.vbc-icon-card', function(e) {
        $('.media-button-select, .media-frame-toolbar .button-primary').trigger('click');
    });

    // 4. TỰ ĐỘNG KHỞI CHẠY KHI DOM SẴN SÀNG
    $(document).ready(function() {
        setupWordPressMediaModalExtension();
    });

    if (typeof wp !== 'undefined' && wp.media) {
        setupWordPressMediaModalExtension();
    }

})(jQuery);
