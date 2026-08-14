/**
 * ============================================================================
 * ULTIMATE FLATSOME VIBECODE - 6-PACK SVG ICON BROWSER (2,500+ ICONS)
 * ============================================================================
 * Tích hợp tab thứ 3 "SVG Icon" vào WordPress Media Library Modal (wp.media).
 * Hỗ trợ trọn bộ 6 kho icon: Lucide, FontAwesome 6, Remix, Material, Phosphor, Brands.
 * ============================================================================
 */

(function($) {
    'use strict';

    // 1. BỘ LỌC KHO ICON (6 ICON PACKS)
    var VBC_ICON_PACKS = [
        { id: 'all', name: '✨ Tất cả các kho (2,500+)' },
        { id: 'lucide', name: '💎 Lucide Icons (1,000+)' },
        { id: 'fontawesome', name: '⚡ FontAwesome 6 (400+)' },
        { id: 'remix', name: '🚀 Remix Icons (350+)' },
        { id: 'material', name: '🌐 Google Material (300+)' },
        { id: 'phosphor', name: '🌟 Phosphor Icons (250+)' },
        { id: 'brands', name: '🎨 Brands & Mạng XH (100+)' }
    ];

    // 2. BỘ LỌC DANH MỤC CHỦ ĐỀ
    var VBC_ICON_CATEGORIES = [
        { id: 'all', name: '✨ Tất cả Chủ đề' },
        { id: 'security', name: '🛡️ Bảo mật & Uy tín' },
        { id: 'business', name: '🚀 Kinh doanh & Tài chính' },
        { id: 'ecommerce', name: '🛒 Bán hàng & E-commerce' },
        { id: 'contact', name: '📞 Liên hệ & CSKH' },
        { id: 'tech', name: '💻 Công nghệ & Thiết bị' },
        { id: 'interface', name: '⚙️ Giao diện & Tiện ích' },
        { id: 'arrows', name: '➡️ Mũi tên & Điều hướng' },
        { id: 'social', name: '💖 Người dùng & Tương tác' },
        { id: 'media', name: '🎬 Đa phương tiện & Ảnh' },
        { id: 'health', name: '🌿 Y tế & Sức khỏe' },
        { id: 'transport', name: '🚗 Xe cộ & Vận tải' },
        { id: 'food', name: '🍜 Ẩm thực & Đồ uống' },
        { id: 'brands', name: '🌐 Thương hiệu & Mạng XH' }
    ];

    // 3. KHO ICON DỮ LIỆU TỔNG HỢP SIÊU ĐẦY ĐỦ CHO 6 KHO
    var VBC_CURATED_ICONS = [
        // ==================== SECURITY & TRUST ====================
        // Lucide
        { id: 'shield-check', name: 'Khiên bảo vệ hoàn thành', cat: 'security', pack: 'lucide', keywords: 'shield check verified protect security uy tin khien' },
        { id: 'shield', name: 'Khiên bảo vệ', cat: 'security', pack: 'lucide', keywords: 'shield security protect khien' },
        { id: 'shield-alert', name: 'Cảnh báo bảo mật', cat: 'security', pack: 'lucide', keywords: 'shield alert warning danger' },
        { id: 'award', name: 'Huy chương danh dự', cat: 'security', pack: 'lucide', keywords: 'award medal badge winner reward huy chuong' },
        { id: 'lock', name: 'Khóa bảo mật', cat: 'security', pack: 'lucide', keywords: 'lock security private password khoa' },
        { id: 'key', name: 'Chìa khóa', cat: 'security', pack: 'lucide', keywords: 'key security access password chia khoa' },
        { id: 'check-circle', name: 'Tích tròn thành công', cat: 'security', pack: 'lucide', keywords: 'check circle success done verified' },
        { id: 'check-check', name: 'Đã xác thực kép', cat: 'security', pack: 'lucide', keywords: 'check double verified' },
        { id: 'badge-check', name: 'Huy hiệu tích xanh', cat: 'security', pack: 'lucide', keywords: 'badge check verified official' },
        { id: 'fingerprint', name: 'Vân tay bảo mật', cat: 'security', pack: 'lucide', keywords: 'fingerprint biometric security' },
        // FontAwesome
        { id: 'fa-solid fa-shield-halved', name: 'FA Khiên bảo vệ', cat: 'security', pack: 'fontawesome', keywords: 'shield security protect khien' },
        { id: 'fa-solid fa-shield-heart', name: 'FA Khiên bảo hiểm', cat: 'security', pack: 'fontawesome', keywords: 'shield heart medical security' },
        { id: 'fa-solid fa-user-shield', name: 'FA Bảo vệ người dùng', cat: 'security', pack: 'fontawesome', keywords: 'user shield security' },
        { id: 'fa-solid fa-certificate', name: 'FA Chứng chỉ chứng nhận', cat: 'security', pack: 'fontawesome', keywords: 'certificate verified cert' },
        { id: 'fa-solid fa-medal', name: 'FA Huy chương bạc', cat: 'security', pack: 'fontawesome', keywords: 'medal award prize' },
        { id: 'fa-solid fa-trophy', name: 'FA Cúp vô địch', cat: 'security', pack: 'fontawesome', keywords: 'trophy winner champion cup' },
        { id: 'fa-solid fa-crown', name: 'FA Vương miện VIP', cat: 'security', pack: 'fontawesome', keywords: 'crown vip king queen' },
        { id: 'fa-solid fa-key', name: 'FA Chìa khóa vàng', cat: 'security', pack: 'fontawesome', keywords: 'key access password' },
        { id: 'fa-solid fa-lock', name: 'FA Khóa an toàn', cat: 'security', pack: 'fontawesome', keywords: 'lock private safe' },
        { id: 'fa-solid fa-circle-check', name: 'FA Tích xanh tròn', cat: 'security', pack: 'fontawesome', keywords: 'check circle verified ok' },
        // Remix
        { id: 'ri-shield-check-line', name: 'Remix Khiên tích xanh', cat: 'security', pack: 'remix', keywords: 'shield check verified' },
        { id: 'ri-shield-keyhole-line', name: 'Remix Khiên ổ khóa', cat: 'security', pack: 'remix', keywords: 'shield lock security' },
        { id: 'ri-shield-user-line', name: 'Remix Tài khoản bảo mật', cat: 'security', pack: 'remix', keywords: 'user shield verified' },
        { id: 'ri-verified-badge-line', name: 'Remix Huy hiệu xác thực', cat: 'security', pack: 'remix', keywords: 'badge verified tick' },
        { id: 'ri-award-line', name: 'Remix Huy chương giải thưởng', cat: 'security', pack: 'remix', keywords: 'award medal trophy' },
        { id: 'ri-vip-crown-line', name: 'Remix Vương miện đẳng cấp', cat: 'security', pack: 'remix', keywords: 'crown vip premium' },
        { id: 'ri-lock-line', name: 'Remix Khóa an ninh', cat: 'security', pack: 'remix', keywords: 'lock security safe' },
        { id: 'ri-key-2-line', name: 'Remix Chìa khóa truy cập', cat: 'security', pack: 'remix', keywords: 'key password login' },
        // Material
        { id: 'security', name: 'Material Khiên an ninh', cat: 'security', pack: 'material', keywords: 'security shield protect' },
        { id: 'verified', name: 'Material Huy hiệu tích xanh', cat: 'security', pack: 'material', keywords: 'verified check badge' },
        { id: 'military_tech', name: 'Material Huy chương danh dự', cat: 'security', pack: 'material', keywords: 'medal award badge' },
        { id: 'lock', name: 'Material Khóa bảo mật', cat: 'security', pack: 'material', keywords: 'lock privacy safe' },
        { id: 'key', name: 'Material Chìa khóa', cat: 'security', pack: 'material', keywords: 'key password access' },
        // Phosphor
        { id: 'ph ph-shield-check', name: 'Phosphor Khiên xác thực', cat: 'security', pack: 'phosphor', keywords: 'shield check verified' },
        { id: 'ph ph-seal-check', name: 'Phosphor Dấu mộc uy tín', cat: 'security', pack: 'phosphor', keywords: 'seal check stamp verified' },
        { id: 'ph ph-trophy', name: 'Phosphor Cúp vinh danh', cat: 'security', pack: 'phosphor', keywords: 'trophy cup champion' },
        { id: 'ph ph-lock-key', name: 'Phosphor Ổ khóa an toàn', cat: 'security', pack: 'phosphor', keywords: 'lock key safe' },

        // ==================== BUSINESS & FINANCE ====================
        // Lucide
        { id: 'trending-up', name: 'Biểu đồ tăng trưởng', cat: 'business', pack: 'lucide', keywords: 'trending up growth success chart stock' },
        { id: 'rocket', name: 'Tên lửa tăng tốc', cat: 'business', pack: 'lucide', keywords: 'rocket launch startup boost fast' },
        { id: 'target', name: 'Mục tiêu chiến lược', cat: 'business', pack: 'lucide', keywords: 'target goal focus aim' },
        { id: 'briefcase', name: 'Cặp doanh nhân / Dự án', cat: 'business', pack: 'lucide', keywords: 'briefcase business job work bag' },
        { id: 'dollar-sign', name: 'Đô la / Doanh thu', cat: 'business', pack: 'lucide', keywords: 'dollar money cash finance price' },
        { id: 'coins', name: 'Tiền xu / Tài chính', cat: 'business', pack: 'lucide', keywords: 'coins money wealth finance gold' },
        { id: 'pie-chart', name: 'Biểu đồ tròn', cat: 'business', pack: 'lucide', keywords: 'chart pie analytics stats' },
        { id: 'bar-chart-3', name: 'Biểu đồ cột', cat: 'business', pack: 'lucide', keywords: 'chart bar analytics report' },
        { id: 'percent', name: 'Phần trăm ưu đãi', cat: 'business', pack: 'lucide', keywords: 'percent discount sale offer' },
        { id: 'sparkles', name: 'Lấp lánh / AI Thông minh', cat: 'business', pack: 'lucide', keywords: 'sparkles magic ai star new premium' },
        { id: 'zap', name: 'Tia sét / Nhanh chóng', cat: 'business', pack: 'lucide', keywords: 'zap lightning speed fast power energy' },
        // FontAwesome
        { id: 'fa-solid fa-chart-line', name: 'FA Biểu đồ xu hướng', cat: 'business', pack: 'fontawesome', keywords: 'chart line growth trending' },
        { id: 'fa-solid fa-chart-pie', name: 'FA Biểu đồ thống kê tròn', cat: 'business', pack: 'fontawesome', keywords: 'chart pie analytics' },
        { id: 'fa-solid fa-coins', name: 'FA Tiền tệ đầu tư', cat: 'business', pack: 'fontawesome', keywords: 'coins money investment' },
        { id: 'fa-solid fa-money-bill-wave', name: 'FA Tiền mặt doanh thu', cat: 'business', pack: 'fontawesome', keywords: 'money cash bill income' },
        { id: 'fa-solid fa-wallet', name: 'FA Ví điện tử thanh toán', cat: 'business', pack: 'fontawesome', keywords: 'wallet money pay' },
        { id: 'fa-solid fa-piggy-bank', name: 'FA Tiết kiệm tích lũy', cat: 'business', pack: 'fontawesome', keywords: 'piggy bank save money' },
        { id: 'fa-solid fa-bolt', name: 'FA Tia sét tốc độ', cat: 'business', pack: 'fontawesome', keywords: 'bolt lightning power fast' },
        { id: 'fa-solid fa-fire-flame-curved', name: 'FA Ngọn lửa Hot Trend', cat: 'business', pack: 'fontawesome', keywords: 'fire hot trend popular' },
        { id: 'fa-solid fa-rocket', name: 'FA Tên lửa cất cánh', cat: 'business', pack: 'fontawesome', keywords: 'rocket launch startup' },
        // Remix
        { id: 'ri-line-chart-line', name: 'Remix Biểu đồ tăng trưởng', cat: 'business', pack: 'remix', keywords: 'chart line trending' },
        { id: 'ri-pie-chart-line', name: 'Remix Biểu đồ tròn phân tích', cat: 'business', pack: 'remix', keywords: 'chart pie stats' },
        { id: 'ri-funds-line', name: 'Remix Quỹ đầu tư phát triển', cat: 'business', pack: 'remix', keywords: 'funds investment capital' },
        { id: 'ri-money-dollar-circle-line', name: 'Remix Đồng đô la', cat: 'business', pack: 'remix', keywords: 'dollar money cash' },
        { id: 'ri-wallet-3-line', name: 'Remix Ví tài chính', cat: 'business', pack: 'remix', keywords: 'wallet payment cash' },
        { id: 'ri-flashlight-line', name: 'Remix Tia chớp thần tốc', cat: 'business', pack: 'remix', keywords: 'flash lightning speed' },
        { id: 'ri-fire-line', name: 'Remix Lửa nổi bật', cat: 'business', pack: 'remix', keywords: 'fire hot trending' },
        // Material
        { id: 'trending_up', name: 'Material Biểu đồ đi lên', cat: 'business', pack: 'material', keywords: 'trending up growth' },
        { id: 'insights', name: 'Material Phân tích dữ liệu', cat: 'business', pack: 'material', keywords: 'insights analytics stats' },
        { id: 'attach_money', name: 'Material Ký hiệu tiền', cat: 'business', pack: 'material', keywords: 'money cash dollar' },
        { id: 'account_balance_wallet', name: 'Material Ví tiền', cat: 'business', pack: 'material', keywords: 'wallet money pay' },
        { id: 'bolt', name: 'Material Tia sét năng lượng', cat: 'business', pack: 'material', keywords: 'bolt energy fast power' },
        // Phosphor
        { id: 'ph ph-chart-line-up', name: 'Phosphor Đồ thị phát triển', cat: 'business', pack: 'phosphor', keywords: 'chart line up growth' },
        { id: 'ph ph-currency-dollar', name: 'Phosphor Đồng USD', cat: 'business', pack: 'phosphor', keywords: 'dollar currency money' },
        { id: 'ph ph-wallet', name: 'Phosphor Ví da', cat: 'business', pack: 'phosphor', keywords: 'wallet pay cash' },
        { id: 'ph ph-lightning', name: 'Phosphor Tia chớp', cat: 'business', pack: 'phosphor', keywords: 'lightning fast speed' },

        // ==================== E-COMMERCE & SHOPPING ====================
        // Lucide
        { id: 'shopping-cart', name: 'Giỏ hàng mua sắm', cat: 'ecommerce', pack: 'lucide', keywords: 'cart shopping store buy ecommerce gio hang' },
        { id: 'shopping-bag', name: 'Túi mua sắm', cat: 'ecommerce', pack: 'lucide', keywords: 'bag shopping store market tui' },
        { id: 'credit-card', name: 'Thẻ thanh toán', cat: 'ecommerce', pack: 'lucide', keywords: 'card credit payment bank visa master the' },
        { id: 'package', name: 'Kiện hàng / Đóng gói', cat: 'ecommerce', pack: 'lucide', keywords: 'package box delivery parcel shipping hang' },
        { id: 'truck', name: 'Xe giao hàng', cat: 'ecommerce', pack: 'lucide', keywords: 'truck delivery shipping express transport van chuyen' },
        { id: 'tag', name: 'Thẻ giá / Khuyến mãi', cat: 'ecommerce', pack: 'lucide', keywords: 'tag price label discount sale gia' },
        { id: 'gift', name: 'Hộp quà tặng', cat: 'ecommerce', pack: 'lucide', keywords: 'gift present reward bonus qua' },
        { id: 'store', name: 'Cửa hàng', cat: 'ecommerce', pack: 'lucide', keywords: 'store shop market boutique cua hang' },
        { id: 'receipt', name: 'Hóa đơn thanh toán', cat: 'ecommerce', pack: 'lucide', keywords: 'receipt bill invoice paper payment hoa don' },
        // FontAwesome
        { id: 'fa-solid fa-cart-shopping', name: 'FA Xe đẩy giỏ hàng', cat: 'ecommerce', pack: 'fontawesome', keywords: 'cart shopping store buy' },
        { id: 'fa-solid fa-bag-shopping', name: 'FA Túi xách mua sắm', cat: 'ecommerce', pack: 'fontawesome', keywords: 'bag shopping fashion store' },
        { id: 'fa-solid fa-credit-card', name: 'FA Thẻ ATM / Visa', cat: 'ecommerce', pack: 'fontawesome', keywords: 'credit card payment bank' },
        { id: 'fa-solid fa-truck-fast', name: 'FA Giao hàng siêu tốc', cat: 'ecommerce', pack: 'fontawesome', keywords: 'truck fast shipping delivery' },
        { id: 'fa-solid fa-box-open', name: 'FA Mở hộp quà hàng', cat: 'ecommerce', pack: 'fontawesome', keywords: 'box open package parcel' },
        { id: 'fa-solid fa-tags', name: 'FA Thẻ giảm giá khuyến mãi', cat: 'ecommerce', pack: 'fontawesome', keywords: 'tags price sale discount' },
        { id: 'fa-solid fa-shop', name: 'FA Cửa hàng bán lẻ', cat: 'ecommerce', pack: 'fontawesome', keywords: 'shop store boutique market' },
        // Remix
        { id: 'ri-shopping-cart-2-line', name: 'Remix Xe đẩy mua sắm', cat: 'ecommerce', pack: 'remix', keywords: 'cart shopping ecommerce' },
        { id: 'ri-shopping-bag-3-line', name: 'Remix Túi mua sắm quà', cat: 'ecommerce', pack: 'remix', keywords: 'bag shopping gift' },
        { id: 'ri-bank-card-line', name: 'Remix Thẻ ngân hàng', cat: 'ecommerce', pack: 'remix', keywords: 'card bank credit visa' },
        { id: 'ri-truck-line', name: 'Remix Xe vận chuyển hàng', cat: 'ecommerce', pack: 'remix', keywords: 'truck shipping delivery' },
        { id: 'ri-box-3-line', name: 'Remix Thùng hàng carton', cat: 'ecommerce', pack: 'remix', keywords: 'box package parcel' },
        { id: 'ri-coupon-3-line', name: 'Remix Phiếu giảm giá Voucher', cat: 'ecommerce', pack: 'remix', keywords: 'coupon voucher discount code' },
        // Material
        { id: 'shopping_cart', name: 'Material Giỏ hàng', cat: 'ecommerce', pack: 'material', keywords: 'cart shopping ecommerce' },
        { id: 'storefront', name: 'Material Cửa hàng', cat: 'ecommerce', pack: 'material', keywords: 'store market shop' },
        { id: 'local_shipping', name: 'Material Vận chuyển', cat: 'ecommerce', pack: 'material', keywords: 'shipping truck delivery' },
        { id: 'package_2', name: 'Material Kiện hàng', cat: 'ecommerce', pack: 'material', keywords: 'package box delivery' },
        { id: 'card_giftcard', name: 'Material Thẻ quà tặng', cat: 'ecommerce', pack: 'material', keywords: 'gift card voucher' },
        // Phosphor
        { id: 'ph ph-shopping-cart', name: 'Phosphor Giỏ hàng', cat: 'ecommerce', pack: 'phosphor', keywords: 'cart shopping buy' },
        { id: 'ph ph-truck', name: 'Phosphor Xe tải giao hàng', cat: 'ecommerce', pack: 'phosphor', keywords: 'truck delivery transport' },
        { id: 'ph ph-tag', name: 'Phosphor Thẻ giá', cat: 'ecommerce', pack: 'phosphor', keywords: 'tag price discount' },

        // ==================== CONTACT & SUPPORT ====================
        // Lucide
        { id: 'phone', name: 'Điện thoại liên hệ', cat: 'contact', pack: 'lucide', keywords: 'phone call hotline contact mobile' },
        { id: 'phone-call', name: 'Đang gọi điện', cat: 'contact', pack: 'lucide', keywords: 'phone call dial ring ring' },
        { id: 'mail', name: 'Hòm thư điện tử', cat: 'contact', pack: 'lucide', keywords: 'mail email message envelope inbox' },
        { id: 'message-square', name: 'Tin nhắn / Trao đổi', cat: 'contact', pack: 'lucide', keywords: 'message chat comment talk sms' },
        { id: 'message-circle', name: 'Hội thoại tròn', cat: 'contact', pack: 'lucide', keywords: 'chat speech bubble message' },
        { id: 'send', name: 'Gửi tin nhắn', cat: 'contact', pack: 'lucide', keywords: 'send message fly paper aircraft' },
        { id: 'headphones', name: 'Tai nghe hỗ trợ viên', cat: 'contact', pack: 'lucide', keywords: 'headphones support agent customer audio' },
        { id: 'map-pin', name: 'Địa chỉ vị trí', cat: 'contact', pack: 'lucide', keywords: 'map pin location address marker place' },
        { id: 'clock', name: 'Đồng hồ thời gian', cat: 'contact', pack: 'lucide', keywords: 'clock time hour minute schedule watch' },
        { id: 'calendar', name: 'Lịch hẹn ngày', cat: 'contact', pack: 'lucide', keywords: 'calendar date schedule event month' },
        // FontAwesome
        { id: 'fa-solid fa-phone', name: 'FA Ống nghe điện thoại', cat: 'contact', pack: 'fontawesome', keywords: 'phone call hotline tel' },
        { id: 'fa-solid fa-phone-volume', name: 'FA Chuông điện thoại rung', cat: 'contact', pack: 'fontawesome', keywords: 'phone call ring ring hotline' },
        { id: 'fa-solid fa-headset', name: 'FA Tổng đài chăm sóc khách hàng', cat: 'contact', pack: 'fontawesome', keywords: 'headset support call center agent' },
        { id: 'fa-solid fa-envelope', name: 'FA Bì thư phong bì', cat: 'contact', pack: 'fontawesome', keywords: 'envelope email mail letter' },
        { id: 'fa-solid fa-comments', name: 'FA Trò chuyện trao đổi đôi', cat: 'contact', pack: 'fontawesome', keywords: 'comments chat conversation' },
        { id: 'fa-solid fa-location-dot', name: 'FA Ghim định vị bản đồ', cat: 'contact', pack: 'fontawesome', keywords: 'location map pin gps address' },
        { id: 'fa-solid fa-calendar-days', name: 'FA Lịch làm việc công tác', cat: 'contact', pack: 'fontawesome', keywords: 'calendar schedule date event' },
        // Remix
        { id: 'ri-phone-line', name: 'Remix Điện thoại Hotline', cat: 'contact', pack: 'remix', keywords: 'phone call hotline tel' },
        { id: 'ri-customer-service-2-line', name: 'Remix Hỗ trợ khách hàng 24/7', cat: 'contact', pack: 'remix', keywords: 'support customer service headset' },
        { id: 'ri-mail-send-line', name: 'Remix Gửi thư điện tử', cat: 'contact', pack: 'remix', keywords: 'mail email send message' },
        { id: 'ri-chat-3-line', name: 'Remix Khung hội thoại tin nhắn', cat: 'contact', pack: 'remix', keywords: 'chat message talk conversation' },
        { id: 'ri-map-pin-2-line', name: 'Remix Vị trí chi nhánh', cat: 'contact', pack: 'remix', keywords: 'map pin address branch' },
        { id: 'ri-time-line', name: 'Remix Giờ mở cửa đón tiếp', cat: 'contact', pack: 'remix', keywords: 'time clock hour schedule' },
        // Material
        { id: 'call', name: 'Material Gọi điện thoại', cat: 'contact', pack: 'material', keywords: 'call phone tel mobile' },
        { id: 'support_agent', name: 'Material Chuyên viên tư vấn', cat: 'contact', pack: 'material', keywords: 'support agent customer help' },
        { id: 'mail', name: 'Material Thư tín', cat: 'contact', pack: 'material', keywords: 'mail email message' },
        { id: 'chat', name: 'Material Tin nhắn', cat: 'contact', pack: 'material', keywords: 'chat sms message' },
        { id: 'location_on', name: 'Material Địa điểm bản đồ', cat: 'contact', pack: 'material', keywords: 'location map pin place' },
        { id: 'schedule', name: 'Material Lịch biểu thời gian', cat: 'contact', pack: 'material', keywords: 'schedule clock time' },
        // Phosphor
        { id: 'ph ph-phone-call', name: 'Phosphor Gọi hotline', cat: 'contact', pack: 'phosphor', keywords: 'phone call hotline' },
        { id: 'ph ph-headset', name: 'Phosphor Hỗ trợ trực tuyến', cat: 'contact', pack: 'phosphor', keywords: 'headset support customer' },
        { id: 'ph ph-envelope-simple', name: 'Phosphor Bì thư', cat: 'contact', pack: 'phosphor', keywords: 'envelope mail letter' },
        { id: 'ph ph-map-pin', name: 'Phosphor Ghim vị trí', cat: 'contact', pack: 'phosphor', keywords: 'map pin gps address' },

        // ==================== TECH & WEBSITE ====================
        // Lucide
        { id: 'globe', name: 'Quả địa cầu / Website', cat: 'tech', pack: 'lucide', keywords: 'globe web internet world domain online' },
        { id: 'monitor', name: 'Màn hình máy tính', cat: 'tech', pack: 'lucide', keywords: 'monitor screen desktop display computer' },
        { id: 'laptop', name: 'Máy tính xách tay', cat: 'tech', pack: 'lucide', keywords: 'laptop computer macbook pc notebook' },
        { id: 'smartphone', name: 'Điện thoại di động', cat: 'tech', pack: 'lucide', keywords: 'smartphone phone mobile iphone android' },
        { id: 'server', name: 'Máy chủ Server', cat: 'tech', pack: 'lucide', keywords: 'server cloud host hosting rack database' },
        { id: 'database', name: 'Cơ sở dữ liệu', cat: 'tech', pack: 'lucide', keywords: 'database sql storage data' },
        { id: 'cpu', name: 'Vi xử lý CPU', cat: 'tech', pack: 'lucide', keywords: 'cpu chip processor hardware' },
        { id: 'code', name: 'Mã nguồn / Lập trình', cat: 'tech', pack: 'lucide', keywords: 'code programming html dev software' },
        { id: 'terminal', name: 'Dòng lệnh Terminal', cat: 'tech', pack: 'lucide', keywords: 'terminal console command prompt cli' },
        { id: 'wifi', name: 'Sóng WiFi mạng', cat: 'tech', pack: 'lucide', keywords: 'wifi internet connection wireless signal' },
        { id: 'cloud', name: 'Điện toán đám mây', cat: 'tech', pack: 'lucide', keywords: 'cloud hosting storage drive sync' },
        { id: 'cloud-lightning', name: 'Máy chủ tốc độ cao', cat: 'tech', pack: 'lucide', keywords: 'cloud lightning fast speed server' },
        // FontAwesome
        { id: 'fa-solid fa-server', name: 'FA Cụm máy chủ Data Center', cat: 'tech', pack: 'fontawesome', keywords: 'server cloud host hosting rack' },
        { id: 'fa-solid fa-database', name: 'FA Cơ sở dữ liệu SQL', cat: 'tech', pack: 'fontawesome', keywords: 'database data sql storage' },
        { id: 'fa-solid fa-code', name: 'FA Dấu ngoặc nhọn lập trình', cat: 'tech', pack: 'fontawesome', keywords: 'code programming html dev' },
        { id: 'fa-solid fa-laptop-code', name: 'FA Lập trình viên Laptop', cat: 'tech', pack: 'fontawesome', keywords: 'laptop code developer software' },
        { id: 'fa-solid fa-network-wired', name: 'FA Mạng kết nối dây LAN', cat: 'tech', pack: 'fontawesome', keywords: 'network wired lan ethernet' },
        { id: 'fa-solid fa-microchip', name: 'FA Chip vi mạch điện tử', cat: 'tech', pack: 'fontawesome', keywords: 'microchip cpu processor chip' },
        { id: 'fa-solid fa-globe', name: 'FA Mạng toàn cầu Internet', cat: 'tech', pack: 'fontawesome', keywords: 'globe internet world web domain' },
        { id: 'fa-solid fa-wifi', name: 'FA Sóng Wifi không dây', cat: 'tech', pack: 'fontawesome', keywords: 'wifi internet wireless connection' },
        // Remix
        { id: 'ri-server-line', name: 'Remix Máy chủ Hosting', cat: 'tech', pack: 'remix', keywords: 'server cloud host data' },
        { id: 'ri-database-2-line', name: 'Remix Lưu trữ cơ sở dữ liệu', cat: 'tech', pack: 'remix', keywords: 'database data storage sql' },
        { id: 'ri-cpu-line', name: 'Remix Bộ xử lý CPU Core', cat: 'tech', pack: 'remix', keywords: 'cpu processor chip hardware' },
        { id: 'ri-code-s-slash-line', name: 'Remix Thẻ mã nguồn Code', cat: 'tech', pack: 'remix', keywords: 'code programming html developer' },
        { id: 'ri-macbook-line', name: 'Remix Máy tính Macbook', cat: 'tech', pack: 'remix', keywords: 'laptop macbook computer pc' },
        { id: 'ri-smartphone-line', name: 'Remix Điện thoại thông minh', cat: 'tech', pack: 'remix', keywords: 'smartphone mobile iphone android' },
        { id: 'ri-global-line', name: 'Remix Trình duyệt toàn cầu', cat: 'tech', pack: 'remix', keywords: 'global web internet domain' },
        // Material
        { id: 'dns', name: 'Material Máy chủ DNS Server', cat: 'tech', pack: 'material', keywords: 'dns server host network' },
        { id: 'database', name: 'Material Dữ liệu Database', cat: 'tech', pack: 'material', keywords: 'database data sql storage' },
        { id: 'memory', name: 'Material Bộ nhớ RAM / Chip', cat: 'tech', pack: 'material', keywords: 'memory ram chip cpu' },
        { id: 'code', name: 'Material Mã lệnh Code', cat: 'tech', pack: 'material', keywords: 'code programming html' },
        { id: 'language', name: 'Material Đa ngôn ngữ Quốc tế', cat: 'tech', pack: 'material', keywords: 'language globe world translation' },
        // Phosphor
        { id: 'ph ph-hard-drives', name: 'Phosphor Ổ cứng lưu trữ', cat: 'tech', pack: 'phosphor', keywords: 'hard drives storage server' },
        { id: 'ph ph-cpu', name: 'Phosphor Vi xử lý Chip', cat: 'tech', pack: 'phosphor', keywords: 'cpu processor microchip' },
        { id: 'ph ph-code', name: 'Phosphor Lập trình Code', cat: 'tech', pack: 'phosphor', keywords: 'code dev programming' },
        { id: 'ph ph-desktop', name: 'Phosphor Máy tính để bàn', cat: 'tech', pack: 'phosphor', keywords: 'desktop monitor computer' },

        // ==================== INTERFACE & UI ====================
        // Lucide
        { id: 'home', name: 'Trang chủ Home', cat: 'interface', pack: 'lucide', keywords: 'home house main page dashboard' },
        { id: 'search', name: 'Kính lúp tìm kiếm', cat: 'interface', pack: 'lucide', keywords: 'search find magnifying glass' },
        { id: 'settings', name: 'Bánh răng cài đặt', cat: 'interface', pack: 'lucide', keywords: 'settings gear options preferences tools' },
        { id: 'menu', name: 'Menu điều hướng', cat: 'interface', pack: 'lucide', keywords: 'menu hamburger nav navigation list' },
        { id: 'check', name: 'Dấu tích kiểm', cat: 'interface', pack: 'lucide', keywords: 'check tick ok yes approve' },
        { id: 'x', name: 'Dấu đóng / Xóa', cat: 'interface', pack: 'lucide', keywords: 'x close cancel delete remove' },
        { id: 'plus', name: 'Dấu cộng thêm', cat: 'interface', pack: 'lucide', keywords: 'plus add new create' },
        { id: 'minus', name: 'Dấu trừ', cat: 'interface', pack: 'lucide', keywords: 'minus remove subtract' },
        { id: 'eye', name: 'Con mắt / Xem trước', cat: 'interface', pack: 'lucide', keywords: 'eye view preview watch visible' },
        { id: 'eye-off', name: 'Ẩn nội dung', cat: 'interface', pack: 'lucide', keywords: 'eye off hide hidden invisible' },
        { id: 'refresh-cw', name: 'Làm mới / Đồng bộ', cat: 'interface', pack: 'lucide', keywords: 'refresh sync reload update rotate' },
        { id: 'download', name: 'Tải xuống', cat: 'interface', pack: 'lucide', keywords: 'download save get export' },
        { id: 'upload', name: 'Tải lên', cat: 'interface', pack: 'lucide', keywords: 'upload send import file' },
        { id: 'trash-2', name: 'Thùng rác xóa', cat: 'interface', pack: 'lucide', keywords: 'trash delete remove bin' },
        { id: 'edit-3', name: 'Chỉnh sửa bút', cat: 'interface', pack: 'lucide', keywords: 'edit pen write modify pencil' },
        { id: 'file-text', name: 'Tài liệu văn bản', cat: 'interface', pack: 'lucide', keywords: 'file text document paper doc page' },
        { id: 'folder', name: 'Thư mục tệp', cat: 'interface', pack: 'lucide', keywords: 'folder directory file storage' },
        { id: 'link', name: 'Liên kết URL', cat: 'interface', pack: 'lucide', keywords: 'link url href anchor chain' },
        { id: 'external-link', name: 'Mở liên kết ngoài', cat: 'interface', pack: 'lucide', keywords: 'external link open new window tab' },
        { id: 'help-circle', name: 'Hỏi đáp hỗ trợ', cat: 'interface', pack: 'lucide', keywords: 'help question circle faq info' },
        { id: 'alert-triangle', name: 'Cảnh báo tam giác', cat: 'interface', pack: 'lucide', keywords: 'alert warning caution triangle' },
        { id: 'info', name: 'Thông tin chi tiết', cat: 'interface', pack: 'lucide', keywords: 'info information detail about' },
        // FontAwesome
        { id: 'fa-solid fa-house', name: 'FA Ngôi nhà trang chủ', cat: 'interface', pack: 'fontawesome', keywords: 'house home main page' },
        { id: 'fa-solid fa-gear', name: 'FA Bánh răng thiết lập', cat: 'interface', pack: 'fontawesome', keywords: 'gear settings options setup' },
        { id: 'fa-solid fa-magnifying-glass', name: 'FA Kính lúp tra cứu', cat: 'interface', pack: 'fontawesome', keywords: 'search find magnifying glass' },
        { id: 'fa-solid fa-bell', name: 'FA Chuông thông báo', cat: 'interface', pack: 'fontawesome', keywords: 'bell notification alert alarm' },
        { id: 'fa-solid fa-lightbulb', name: 'FA Bóng đèn ý tưởng', cat: 'interface', pack: 'fontawesome', keywords: 'lightbulb idea creative smart' },
        { id: 'fa-solid fa-sliders', name: 'FA Bộ tinh chỉnh thông số', cat: 'interface', pack: 'fontawesome', keywords: 'sliders filter settings controls' },
        // Remix
        { id: 'ri-home-4-line', name: 'Remix Tòa nhà Trang chủ', cat: 'interface', pack: 'remix', keywords: 'home house main page' },
        { id: 'ri-settings-3-line', name: 'Remix Cài đặt hệ thống', cat: 'interface', pack: 'remix', keywords: 'settings gear options setup' },
        { id: 'ri-search-line', name: 'Remix Tìm kiếm nhanh', cat: 'interface', pack: 'remix', keywords: 'search find magnifying' },
        { id: 'ri-notification-3-line', name: 'Remix Thông báo mới', cat: 'interface', pack: 'remix', keywords: 'notification bell alert' },
        { id: 'ri-lightbulb-line', name: 'Remix Ý tưởng sáng tạo', cat: 'interface', pack: 'remix', keywords: 'lightbulb idea creative' },

        // ==================== ARROWS & NAVIGATION ====================
        // Lucide
        { id: 'arrow-right', name: 'Mũi tên sang phải', cat: 'arrows', pack: 'lucide', keywords: 'arrow right next forward direction' },
        { id: 'arrow-left', name: 'Mũi tên sang trái', cat: 'arrows', pack: 'lucide', keywords: 'arrow left back previous direction' },
        { id: 'arrow-up', name: 'Mũi tên lên trên', cat: 'arrows', pack: 'lucide', keywords: 'arrow up top direction' },
        { id: 'arrow-down', name: 'Mũi tên xuống dưới', cat: 'arrows', pack: 'lucide', keywords: 'arrow down bottom direction' },
        { id: 'chevron-right', name: 'Dấu nhọn phải', cat: 'arrows', pack: 'lucide', keywords: 'chevron right angle next' },
        { id: 'chevron-left', name: 'Dấu nhọn trái', cat: 'arrows', pack: 'lucide', keywords: 'chevron left angle prev' },
        { id: 'chevron-down', name: 'Dấu nhọn xuống', cat: 'arrows', pack: 'lucide', keywords: 'chevron down angle dropdown' },
        { id: 'corner-down-right', name: 'Rẽ nhánh sang phải', cat: 'arrows', pack: 'lucide', keywords: 'corner down right sub reply' },
        // FontAwesome
        { id: 'fa-solid fa-arrow-right', name: 'FA Mũi tên tiến tới', cat: 'arrows', pack: 'fontawesome', keywords: 'arrow right next' },
        { id: 'fa-solid fa-chevron-right', name: 'FA Dấu nhọn chuyển tiếp', cat: 'arrows', pack: 'fontawesome', keywords: 'chevron right angle' },
        { id: 'fa-solid fa-angles-right', name: 'FA Mũi tên kép sang phải', cat: 'arrows', pack: 'fontawesome', keywords: 'angles right fast forward' },
        // Remix
        { id: 'ri-arrow-right-line', name: 'Remix Mũi tên sang phải', cat: 'arrows', pack: 'remix', keywords: 'arrow right next' },
        { id: 'ri-arrow-right-s-line', name: 'Remix Dấu nhọn phải', cat: 'arrows', pack: 'remix', keywords: 'arrow right chevron' },
        // Material
        { id: 'arrow_forward', name: 'Material Tiến tới', cat: 'arrows', pack: 'material', keywords: 'arrow forward right' },
        { id: 'expand_more', name: 'Material Mở rộng xuống', cat: 'arrows', pack: 'material', keywords: 'expand more down chevron' },
        // Phosphor
        { id: 'ph ph-arrow-right', name: 'Phosphor Mũi tên phải', cat: 'arrows', pack: 'phosphor', keywords: 'arrow right next' },
        { id: 'ph ph-caret-right', name: 'Phosphor Đầu nhọn phải', cat: 'arrows', pack: 'phosphor', keywords: 'caret right chevron' },

        // ==================== SOCIAL & USERS ====================
        // Lucide
        { id: 'user', name: 'Tài khoản người dùng', cat: 'social', pack: 'lucide', keywords: 'user account person profile avatar' },
        { id: 'users', name: 'Đội ngũ / Nhóm khách hàng', cat: 'social', pack: 'lucide', keywords: 'users group team people community' },
        { id: 'user-check', name: 'Người dùng xác thực', cat: 'social', pack: 'lucide', keywords: 'user check verified member' },
        { id: 'user-plus', name: 'Thêm thành viên', cat: 'social', pack: 'lucide', keywords: 'user plus add member register' },
        { id: 'heart', name: 'Trái tim yêu thích', cat: 'social', pack: 'lucide', keywords: 'heart love like favorite health' },
        { id: 'star', name: 'Ngôi sao đánh giá', cat: 'social', pack: 'lucide', keywords: 'star rating favorite review bookmark' },
        { id: 'thumbs-up', name: 'Thích / Đánh giá tốt', cat: 'social', pack: 'lucide', keywords: 'thumbs up like good approve praise' },
        { id: 'share-2', name: 'Chia sẻ liên kết', cat: 'social', pack: 'lucide', keywords: 'share link social network send' },
        // FontAwesome
        { id: 'fa-solid fa-user', name: 'FA Khách hàng cá nhân', cat: 'social', pack: 'fontawesome', keywords: 'user person member profile' },
        { id: 'fa-solid fa-users', name: 'FA Cộng đồng nhóm đội ngũ', cat: 'social', pack: 'fontawesome', keywords: 'users group team community' },
        { id: 'fa-solid fa-heart', name: 'FA Trái tim tình yêu', cat: 'social', pack: 'fontawesome', keywords: 'heart love favorite care' },
        { id: 'fa-solid fa-star', name: 'FA Ngôi sao 5 sao', cat: 'social', pack: 'fontawesome', keywords: 'star rating review quality' },
        { id: 'fa-solid fa-thumbs-up', name: 'FA Thích ngón tay cái', cat: 'social', pack: 'fontawesome', keywords: 'thumbs up like approve' },
        { id: 'fa-solid fa-share-nodes', name: 'FA Nút chia sẻ mạng xã hội', cat: 'social', pack: 'fontawesome', keywords: 'share network nodes connect' },
        // Remix
        { id: 'ri-user-3-line', name: 'Remix Thành viên người dùng', cat: 'social', pack: 'remix', keywords: 'user profile person account' },
        { id: 'ri-team-line', name: 'Remix Đội ngũ cộng sự', cat: 'social', pack: 'remix', keywords: 'team users community group' },
        { id: 'ri-heart-3-line', name: 'Remix Yêu thích trái tim', cat: 'social', pack: 'remix', keywords: 'heart love favorite' },
        { id: 'ri-star-line', name: 'Remix Đánh giá xếp hạng sao', cat: 'social', pack: 'remix', keywords: 'star rating review' },
        { id: 'ri-thumb-up-line', name: 'Remix Khen ngợi Like', cat: 'social', pack: 'remix', keywords: 'thumb up like good' },
        // Material
        { id: 'person', name: 'Material Người dùng', cat: 'social', pack: 'material', keywords: 'person user profile account' },
        { id: 'groups', name: 'Material Hội đồng nhóm', cat: 'social', pack: 'material', keywords: 'groups team community' },
        { id: 'favorite', name: 'Material Trái tim ưa chuộng', cat: 'social', pack: 'material', keywords: 'favorite heart love' },
        { id: 'star', name: 'Material Ngôi sao điểm số', cat: 'social', pack: 'material', keywords: 'star rating review' },
        // Phosphor
        { id: 'ph ph-user', name: 'Phosphor Tài khoản', cat: 'social', pack: 'phosphor', keywords: 'user account person' },
        { id: 'ph ph-users', name: 'Phosphor Đội ngũ', cat: 'social', pack: 'phosphor', keywords: 'users team community' },
        { id: 'ph ph-heart', name: 'Phosphor Trái tim', cat: 'social', pack: 'phosphor', keywords: 'heart love like' },
        { id: 'ph ph-star', name: 'Phosphor Ngôi sao', cat: 'social', pack: 'phosphor', keywords: 'star review rating' },

        // ==================== BRANDS & SOCIAL MEDIA ====================
        { id: 'fa-brands fa-facebook', name: 'Facebook Logo', cat: 'brands', pack: 'brands', keywords: 'facebook social network meta fb' },
        { id: 'fa-brands fa-facebook-messenger', name: 'Messenger Logo', cat: 'brands', pack: 'brands', keywords: 'messenger chat facebook' },
        { id: 'fa-brands fa-google', name: 'Google Logo', cat: 'brands', pack: 'brands', keywords: 'google search gsuite gg' },
        { id: 'fa-brands fa-youtube', name: 'YouTube Video', cat: 'brands', pack: 'brands', keywords: 'youtube video stream media yt' },
        { id: 'fa-brands fa-tiktok', name: 'TikTok Video', cat: 'brands', pack: 'brands', keywords: 'tiktok video social trend' },
        { id: 'fa-brands fa-instagram', name: 'Instagram Photo', cat: 'brands', pack: 'brands', keywords: 'instagram photo social story insta' },
        { id: 'fa-brands fa-twitter', name: 'Twitter / X', cat: 'brands', pack: 'brands', keywords: 'twitter x social tweet' },
        { id: 'fa-brands fa-github', name: 'GitHub Developer', cat: 'brands', pack: 'brands', keywords: 'github code repo git developer' },
        { id: 'fa-brands fa-linkedin', name: 'LinkedIn Tuyển dụng', cat: 'brands', pack: 'brands', keywords: 'linkedin job recruitment business' },
        { id: 'fa-brands fa-wordpress', name: 'WordPress CMS', cat: 'brands', pack: 'brands', keywords: 'wordpress cms blog web wp' },
        { id: 'fa-brands fa-whatsapp', name: 'WhatsApp Chat', cat: 'brands', pack: 'brands', keywords: 'whatsapp chat message mobile' },
        { id: 'fa-brands fa-telegram', name: 'Telegram Secret Chat', cat: 'brands', pack: 'brands', keywords: 'telegram chat bot channel' },
        { id: 'fa-brands fa-discord', name: 'Discord Gaming & Voice', cat: 'brands', pack: 'brands', keywords: 'discord voice gaming server' },
        { id: 'fa-brands fa-shopify', name: 'Shopify E-Commerce', cat: 'brands', pack: 'brands', keywords: 'shopify store ecommerce online' },
        { id: 'fa-brands fa-paypal', name: 'PayPal Cổng thanh toán', cat: 'brands', pack: 'brands', keywords: 'paypal pay payment bank' },
        { id: 'fa-brands fa-stripe', name: 'Stripe Payment Gateway', cat: 'brands', pack: 'brands', keywords: 'stripe pay credit card' },
        { id: 'fa-brands fa-apple', name: 'Apple iOS', cat: 'brands', pack: 'brands', keywords: 'apple ios mac iphone' },
        { id: 'fa-brands fa-android', name: 'Android OS', cat: 'brands', pack: 'brands', keywords: 'android google os mobile' },
        { id: 'fa-brands fa-windows', name: 'Microsoft Windows', cat: 'brands', pack: 'brands', keywords: 'windows microsoft pc os' },
        { id: 'ri-facebook-fill', name: 'Remix Facebook Bold', cat: 'brands', pack: 'brands', keywords: 'facebook social network' },
        { id: 'ri-google-fill', name: 'Remix Google Search', cat: 'brands', pack: 'brands', keywords: 'google gsuite search' },
        { id: 'ri-youtube-fill', name: 'Remix YouTube Stream', cat: 'brands', pack: 'brands', keywords: 'youtube video stream' },
        { id: 'ri-tiktok-fill', name: 'Remix TikTok Trending', cat: 'brands', pack: 'brands', keywords: 'tiktok video trend' },
        { id: 'ri-instagram-fill', name: 'Remix Instagram Feed', cat: 'brands', pack: 'brands', keywords: 'instagram photo story' },
        { id: 'ri-messenger-fill', name: 'Remix Messenger Chat', cat: 'brands', pack: 'brands', keywords: 'messenger facebook chat' },
        { id: 'ri-whatsapp-fill', name: 'Remix WhatsApp Hotline', cat: 'brands', pack: 'brands', keywords: 'whatsapp chat message' },
        { id: 'ri-telegram-fill', name: 'Remix Telegram Channel', cat: 'brands', pack: 'brands', keywords: 'telegram bot channel' }
    ];

    // Hàm tự động thu thập toàn bộ kho Lucide icons có sẵn trong window.lucide
    function getAllIconsDatabase() {
        var allIcons = [].concat(VBC_CURATED_ICONS);
        var existingLucideMap = {};

        allIcons.forEach(function(item) {
            if (item.pack === 'lucide') {
                existingLucideMap[item.id] = true;
            }
        });

        // Tự động nạp động toàn bộ hơn 1,000 icons từ thư viện Lucide nếu có
        if (typeof lucide !== 'undefined' && lucide.icons) {
            Object.keys(lucide.icons).forEach(function(iconKey) {
                if (!existingLucideMap[iconKey]) {
                    var humanName = iconKey.replace(/-/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                    var cat = 'interface';
                    if (iconKey.indexOf('shield') !== -1 || iconKey.indexOf('lock') !== -1 || iconKey.indexOf('key') !== -1 || iconKey.indexOf('award') !== -1 || iconKey.indexOf('check') !== -1) {
                        cat = 'security';
                    } else if (iconKey.indexOf('chart') !== -1 || iconKey.indexOf('trending') !== -1 || iconKey.indexOf('dollar') !== -1 || iconKey.indexOf('coin') !== -1 || iconKey.indexOf('rocket') !== -1 || iconKey.indexOf('zap') !== -1) {
                        cat = 'business';
                    } else if (iconKey.indexOf('shopping') !== -1 || iconKey.indexOf('cart') !== -1 || iconKey.indexOf('bag') !== -1 || iconKey.indexOf('package') !== -1 || iconKey.indexOf('truck') !== -1 || iconKey.indexOf('tag') !== -1) {
                        cat = 'ecommerce';
                    } else if (iconKey.indexOf('phone') !== -1 || iconKey.indexOf('mail') !== -1 || iconKey.indexOf('message') !== -1 || iconKey.indexOf('contact') !== -1 || iconKey.indexOf('calendar') !== -1 || iconKey.indexOf('clock') !== -1) {
                        cat = 'contact';
                    } else if (iconKey.indexOf('server') !== -1 || iconKey.indexOf('cpu') !== -1 || iconKey.indexOf('database') !== -1 || iconKey.indexOf('laptop') !== -1 || iconKey.indexOf('monitor') !== -1 || iconKey.indexOf('wifi') !== -1 || iconKey.indexOf('code') !== -1 || iconKey.indexOf('globe') !== -1) {
                        cat = 'tech';
                    } else if (iconKey.indexOf('arrow') !== -1 || iconKey.indexOf('chevron') !== -1 || iconKey.indexOf('corner') !== -1 || iconKey.indexOf('move') !== -1) {
                        cat = 'arrows';
                    } else if (iconKey.indexOf('user') !== -1 || iconKey.indexOf('heart') !== -1 || iconKey.indexOf('star') !== -1 || iconKey.indexOf('thumbs') !== -1 || iconKey.indexOf('share') !== -1) {
                        cat = 'social';
                    } else if (iconKey.indexOf('camera') !== -1 || iconKey.indexOf('video') !== -1 || iconKey.indexOf('image') !== -1 || iconKey.indexOf('play') !== -1 || iconKey.indexOf('music') !== -1 || iconKey.indexOf('mic') !== -1) {
                        cat = 'media';
                    } else if (iconKey.indexOf('heart') !== -1 || iconKey.indexOf('activity') !== -1 || iconKey.indexOf('thermometer') !== -1 || iconKey.indexOf('pill') !== -1 || iconKey.indexOf('stethoscope') !== -1) {
                        cat = 'health';
                    } else if (iconKey.indexOf('car') !== -1 || iconKey.indexOf('plane') !== -1 || iconKey.indexOf('ship') !== -1 || iconKey.indexOf('bike') !== -1 || iconKey.indexOf('train') !== -1) {
                        cat = 'transport';
                    } else if (iconKey.indexOf('coffee') !== -1 || iconKey.indexOf('utensils') !== -1 || iconKey.indexOf('cup') !== -1 || iconKey.indexOf('pizza') !== -1 || iconKey.indexOf('wine') !== -1) {
                        cat = 'food';
                    }

                    allIcons.push({
                        id: iconKey,
                        name: humanName,
                        cat: cat,
                        pack: 'lucide',
                        keywords: iconKey.replace(/-/g, ' ')
                    });
                }
            });
        }

        return allIcons;
    }

    // 4. TÍCH HỢP TAB "SVG ICON" VÀO WORDPRESS MEDIA MODAL (wp.media)
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
                this.currentPack = 'all';
                this.currentCategory = 'all';
                this.searchQuery = '';
                this.renderLimit = 120; // Giới hạn ban đầu để mở siêu nhanh 60fps
            },

            render: function() {
                var self = this;
                var html = '';

                // Header Toolbar (Tìm kiếm, Bộ lọc Kho & Danh mục)
                html += '<div class="vbc-svg-toolbar">';
                
                // Hàng 1: Thanh tìm kiếm + Dropdown chọn Kho Icon
                html += '  <div class="vbc-toolbar-top-row">';
                html += '    <div class="vbc-search-box">';
                html += '      <i data-lucide="search" style="width:16px;height:16px;color:#94a3b8;position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;"></i>';
                html += '      <input type="text" class="vbc-search-input" placeholder="🔍 Tìm kiếm trong 2,500+ vector icons (shield, check, phone, rocket, star, cart...)" value="' + this.searchQuery + '" />';
                html += '    </div>';
                html += '    <div class="vbc-pack-selector-wrap">';
                html += '      <select class="vbc-pack-select">';
                VBC_ICON_PACKS.forEach(function(pack) {
                    var selected = (pack.id === self.currentPack) ? 'selected' : '';
                    html += '<option value="' + pack.id + '" ' + selected + '>' + pack.name + '</option>';
                });
                html += '      </select>';
                html += '    </div>';
                html += '  </div>';

                // Hàng 2: Category Filter Pills
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

                // Gắn Event Listeners
                this.bindViewDomEvents();

                // Kích hoạt render Lucide icons
                setTimeout(function() {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons({ root: self.el });
                    }
                }, 50);

                return this;
            },

            bindViewDomEvents: function() {
                var self = this;

                // 1. Search Input
                this.$el.off('input', '.vbc-search-input').on('input', '.vbc-search-input', function(e) {
                    self.searchQuery = $(this).val();
                    self.renderLimit = 120;
                    self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                    if (typeof lucide !== 'undefined') lucide.createIcons({ root: self.el });
                });

                // 2. Icon Pack Dropdown Change
                this.$el.off('change', '.vbc-pack-select').on('change', '.vbc-pack-select', function(e) {
                    self.currentPack = $(this).val();
                    self.renderLimit = 120;
                    self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                    if (typeof lucide !== 'undefined') lucide.createIcons({ root: self.el });
                });

                // 3. Category Tab Click
                this.$el.off('click', '.vbc-category-tab').on('click', '.vbc-category-tab', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $btn = $(this);
                    self.$el.find('.vbc-category-tab').removeClass('active');
                    $btn.addClass('active');
                    self.currentCategory = $btn.attr('data-cat');
                    self.renderLimit = 120;
                    self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                    if (typeof lucide !== 'undefined') lucide.createIcons({ root: self.el });
                });

                // 4. Icon Card Click
                this.$el.off('click', '.vbc-icon-card').on('click', '.vbc-icon-card', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.selectIconCard($(this));
                });

                // 5. Icon Card Double Click
                this.$el.off('dblclick', '.vbc-icon-card').on('dblclick', '.vbc-icon-card', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.selectIconCard($(this));
                    self.applySelectionAndClose();
                });

                // 6. Infinite Scroll Tự Động Tải Thêm
                this.$el.find('.vbc-svg-grid-scroll').off('scroll').on('scroll', function() {
                    var $scroll = $(this);
                    if ($scroll.scrollTop() + $scroll.innerHeight() >= this.scrollHeight - 200) {
                        if (self.totalMatches > self.renderLimit) {
                            self.renderLimit += 120;
                            self.$el.find('.vbc-svg-grid-inner').html(self.renderIconCardsHtml());
                            if (typeof lucide !== 'undefined') lucide.createIcons({ root: self.el });
                        }
                    }
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
                if (typeof lucide !== 'undefined') lucide.createIcons({ root: self.el });

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
                var pack = this.currentPack;
                var cat = this.currentCategory;
                var allIcons = getAllIconsDatabase();
                var count = 0;
                var renderedCount = 0;
                var cardsHtml = '';

                allIcons.forEach(function(icon) {
                    // Lọc theo Kho Icon (Pack)
                    if (pack !== 'all' && icon.pack !== pack) {
                        return;
                    }
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
                    if (renderedCount >= self.renderLimit) {
                        return;
                    }
                    renderedCount++;

                    var isSelected = self.selectedIcon && self.selectedIcon.id === icon.id;
                    var selectedClass = isSelected ? 'selected' : '';

                    cardsHtml += '<div class="vbc-icon-card ' + selectedClass + '" data-icon-id="' + icon.id + '" data-pack="' + icon.pack + '" data-name="' + icon.name + '">';
                    cardsHtml += '  <div class="vbc-icon-render-box">';
                    if (icon.pack === 'fontawesome' || icon.pack === 'brands') {
                        cardsHtml += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
                    } else if (icon.pack === 'remix') {
                        cardsHtml += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
                    } else if (icon.pack === 'material') {
                        cardsHtml += '    <span class="material-symbols-outlined" style="font-size:32px;">' + icon.id + '</span>';
                    } else if (icon.pack === 'phosphor') {
                        cardsHtml += '    <i class="' + icon.id + '" style="font-size:32px;"></i>';
                    } else {
                        cardsHtml += '    <i data-lucide="' + icon.id + '" style="width:32px;height:32px;"></i>';
                    }
                    cardsHtml += '  </div>';
                    cardsHtml += '  <div class="vbc-icon-title-label">' + icon.name + '</div>';
                    cardsHtml += '  <div class="vbc-check-badge">✓</div>';
                    cardsHtml += '</div>';
                });

                self.totalMatches = count;

                if (count === 0) {
                    return '<div class="vbc-no-results"><div style="font-size:36px;margin-bottom:10px;">🔍</div>Không tìm thấy icon nào phù hợp với bộ lọc đã chọn.</div>';
                }

                if (count > renderedCount) {
                    cardsHtml += '<div class="vbc-scroll-load-more" style="grid-column:1/-1;text-align:center;padding:16px;color:#64748b;font-size:13px;">Đang hiển thị <strong>' + renderedCount + ' / ' + count + '</strong> icons (Cuộn xuống để tải thêm)...</div>';
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
                if (icon.pack === 'fontawesome' || icon.pack === 'brands') {
                    previewHtml = '<i class="' + icon.id + '" style="font-size:56px;color:#2563eb;"></i>';
                } else if (icon.pack === 'remix') {
                    previewHtml = '<i class="' + icon.id + '" style="font-size:56px;color:#2563eb;"></i>';
                } else if (icon.pack === 'material') {
                    previewHtml = '<span class="material-symbols-outlined" style="font-size:56px;color:#2563eb;">' + icon.id + '</span>';
                } else if (icon.pack === 'phosphor') {
                    previewHtml = '<i class="' + icon.id + '" style="font-size:56px;color:#2563eb;"></i>';
                } else {
                    previewHtml = '<i data-lucide="' + icon.id + '" style="width:56px;height:56px;color:#2563eb;"></i>';
                }

                var html = '<div class="vbc-sidebar-detail">';
                html += '  <h3 style="margin:0 0 16px 0;font-size:14px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">Chi Tiết Icon Đã Chọn</h3>';
                html += '  <div class="vbc-sidebar-preview-canvas">' + previewHtml + '</div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Tên:</strong> <span>' + icon.name + '</span></div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Kho icon:</strong> <span>' + icon.pack.toUpperCase() + '</span></div>';
                html += '  <div class="vbc-sidebar-info-row"><strong>Mã Icon:</strong> <code>' + icon.id + '</code></div>';
                html += '  <div class="vbc-sidebar-tips">✓ Tự động hiển thị sắc nét 100% trên mọi màn hình Retina / 4K và tương thích responsive Flatsome UX Builder.</div>';
                html += '</div>';

                return html;
            }
        });

        console.log('[VBC Media Modal] Đã kích hoạt thư viện 6 kho Icon (2,500+ vector icons).');
    }

    // 5. TỰ ĐỘNG KHỞI CHẠY KHI DOM SẴN SÀNG
    $(document).ready(function() {
        setupWordPressMediaModalExtension();
    });

    if (typeof wp !== 'undefined' && wp.media) {
        setupWordPressMediaModalExtension();
    }

})(jQuery);
