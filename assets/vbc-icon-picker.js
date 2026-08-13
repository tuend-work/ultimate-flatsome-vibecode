(function($) {
    'use strict';

    console.log('[VBC Icon Picker v1.4.0] Loaded.');

    // Curated icon libraries
    const ICON_LIBRARIES = {
        lucide: [
            'home', 'user', 'mail', 'phone', 'search', 'settings', 'check', 'x', 'shield', 'shield-check',
            'shield-alert', 'zap', 'star', 'info', 'alert-triangle', 'trash-2', 'lock', 'key',
            'activity', 'file-text', 'code', 'smartphone', 'trending-up', 'credit-card',
            'refresh-cw', 'heart', 'message-square', 'sparkles', 'gauge', 'cloud-lightning',
            'hammer', 'monitor', 'laptop', 'server', 'globe', 'cloud', 'cpu', 'database',
            'terminal', 'arrow-right', 'arrow-left', 'download', 'upload', 'menu',
            'check-circle', 'x-circle', 'help-circle', 'alert-circle', 'clock', 'calendar',
            'map-pin', 'folder', 'file', 'link', 'external-link', 'eye', 'eye-off', 'layers',
            'package', 'box', 'rocket', 'headphones', 'wifi', 'bluetooth', 'battery', 'power'
        ],
        fontawesome: [
            'fa-solid fa-house', 'fa-solid fa-user', 'fa-solid fa-envelope', 'fa-solid fa-phone', 'fa-solid fa-magnifying-glass',
            'fa-solid fa-gear', 'fa-solid fa-check', 'fa-solid fa-xmark', 'fa-solid fa-shield-halved', 'fa-solid fa-bolt',
            'fa-solid fa-star', 'fa-solid fa-circle-info', 'fa-solid fa-circle-exclamation', 'fa-solid fa-trash', 'fa-solid fa-lock',
            'fa-solid fa-key', 'fa-solid fa-chart-line', 'fa-solid fa-server', 'fa-solid fa-globe', 'fa-solid fa-cloud',
            'fa-solid fa-cpu', 'fa-solid fa-database', 'fa-solid fa-terminal', 'fa-solid fa-code', 'fa-solid fa-heart',
            'fa-solid fa-comment', 'fa-solid fa-bell', 'fa-solid fa-bookmark', 'fa-solid fa-share', 'fa-solid fa-link',
            'fa-solid fa-arrow-right', 'fa-solid fa-arrow-left', 'fa-solid fa-download', 'fa-solid fa-upload', 'fa-solid fa-gauge',
            'fa-solid fa-screwdriver-wrench', 'fa-solid fa-arrows-rotate', 'fa-solid fa-credit-card', 'fa-solid fa-circle-check',
            'fa-brands fa-facebook', 'fa-brands fa-google', 'fa-brands fa-youtube', 'fa-brands fa-twitter', 'fa-brands fa-instagram',
            'fa-brands fa-github', 'fa-brands fa-tiktok', 'fa-brands fa-wordpress'
        ],
        remix: [
            'ri-home-line', 'ri-user-line', 'ri-mail-line', 'ri-phone-line', 'ri-search-line', 'ri-settings-line',
            'ri-check-line', 'ri-close-line', 'ri-shield-check-line', 'ri-flashlight-line', 'ri-star-line', 'ri-information-line',
            'ri-alert-line', 'ri-delete-bin-line', 'ri-lock-line', 'ri-key-line', 'ri-line-chart-line', 'ri-heart-line',
            'ri-chat-3-line', 'ri-notification-line', 'ri-server-line', 'ri-global-line', 'ri-cloud-line', 'ri-cpu-line',
            'ri-database-line', 'ri-terminal-line', 'ri-code-line', 'ri-arrow-right-line', 'ri-arrow-left-line',
            'ri-download-line', 'ri-upload-line', 'ri-facebook-fill', 'ri-google-fill', 'ri-youtube-fill',
            'ri-shield-cross-line', 'ri-spam-line', 'ri-database-2-line', 'ri-braces-line', 'ri-mac-line'
        ],
        phosphor: [
            'ph ph-house', 'ph ph-user', 'ph ph-envelope', 'ph ph-phone', 'ph ph-magnifying-glass',
            'ph ph-gear', 'ph ph-check', 'ph ph-x', 'ph ph-shield', 'ph ph-shield-check',
            'ph ph-lightning', 'ph ph-star', 'ph ph-info', 'ph ph-warning', 'ph ph-trash', 'ph ph-lock',
            'ph ph-key', 'ph ph-chart-line', 'ph ph-server', 'ph ph-globe', 'ph ph-cloud', 'ph ph-cpu',
            'ph ph-database', 'ph ph-terminal', 'ph ph-code', 'ph ph-heart', 'ph ph-chat', 'ph ph-bell',
            'ph ph-arrow-right', 'ph ph-arrow-left', 'ph ph-download', 'ph ph-upload'
        ],
        material: [
            'home', 'person', 'mail', 'call', 'search', 'settings', 'check', 'close', 'security', 'shield',
            'bolt', 'star', 'info', 'warning', 'delete', 'lock', 'vpn_key', 'trending_up', 'dns', 'language',
            'cloud', 'memory', 'storage', 'terminal', 'code', 'favorite', 'chat', 'notifications',
            'arrow_forward', 'arrow_back', 'download', 'upload', 'shield_heart', 'bug_report', 'build', 'cached'
        ]
    };

    let $activeInput = null;
    let currentTab = 'media';
    let tempSelectedValue = ''; // Stores temporary selection (img:URL or icon:class)
    let wpMediaFrame = null;

    // Core: Write value to input and trigger Flatsome update
    function writeValueToInput($input, value) {
        if (!$input || $input.length === 0) return false;
        console.log('[VBC Icon Picker] Saving value:', value);

        // Update value
        $input.val(value);

        // Dispatch React/HTML5 events
        var nativeInput = $input[0];
        var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');
        if (nativeInputValueSetter) {
            nativeInputValueSetter.set.call(nativeInput, value);
        }

        ['input', 'change', 'keyup', 'blur'].forEach(function(evtName) {
            nativeInput.dispatchEvent(new Event(evtName, { bubbles: true, cancelable: true }));
        });

        $input.trigger('input').trigger('change').trigger('keyup');
        $input.focus();
        setTimeout(function() { $input.blur(); }, 50);

        // Update inline preview in sidebar
        updateSidebarPreview($input);
        return true;
    }

    // Update the visual preview box next to the input in UX Builder sidebar
    function updateSidebarPreview($input) {
        var val = $input.val().trim();
        var $previewContainer = $input.parent().find('.vbc-preview-box');
        if ($previewContainer.length === 0) {
            $previewContainer = $('<div class="vbc-preview-box" style="margin-top:8px;padding:10px;background:#27272a;border-radius:6px;display:flex;align-items:center;gap:10px;border:1px solid #3f3f46;min-height:52px;"></div>');
            $input.after($previewContainer);
        }

        $previewContainer.empty();
        if (!val) {
            $previewContainer.html('<span style="color:#71717a;font-size:12px;">Chưa chọn Ảnh / Icon</span>');
            return;
        }

        if (val.indexOf('img:') === 0) {
            var url = val.substring(4);
            $previewContainer.html('<img src="' + url + '" style="width:32px;height:32px;object-fit:contain;background:#18181b;padding:2px;border-radius:4px;" />' +
                                   '<span style="color:#e4e4e7;font-size:12px;word-break:break-all;flex-grow:1;">Ảnh SVG/PNG</span>');
        } else if (val.indexOf('icon:') === 0) {
            var cls = val.substring(5);
            var iconHtml = '';
            // Render icon preview based on class format
            if (cls.indexOf('ri-') === 0) {
                iconHtml = '<i class="' + cls + '" style="font-size:24px;color:#fff;"></i>';
            } else if (cls.indexOf('fa-') >= 0) {
                iconHtml = '<i class="' + cls + '" style="font-size:20px;color:#fff;"></i>';
            } else if (cls.indexOf('ph-') >= 0 || cls.indexOf('ph ') === 0) {
                iconHtml = '<i class="' + cls + '" style="font-size:24px;color:#fff;"></i>';
            } else if (cls.indexOf('_') >= 0 && cls.indexOf('-') === -1) {
                iconHtml = '<span class="material-symbols-outlined" style="font-size:24px;color:#fff;">' + cls + '</span>';
            } else {
                // Lucide
                iconHtml = '<i data-lucide="' + cls + '" style="display:inline-block;width:24px;height:24px;color:#fff;"></i>';
            }
            $previewContainer.html('<div style="background:#18181b;padding:6px;border-radius:4px;display:flex;align-items:center;justify-content:center;">' + iconHtml + '</div>' +
                                   '<span style="color:#e4e4e7;font-size:12px;font-family:monospace;word-break:break-all;">' + cls + '</span>');
            
            if (val.indexOf('icon:') === 0 && typeof lucide !== 'undefined') {
                try { lucide.createIcons(); } catch(e) {}
            }
        } else {
            $previewContainer.html('<span style="color:#ef4444;font-size:12px;">Định dạng không hợp lệ: ' + val + '</span>');
        }
    }

    // Modal HTML & events setup
    function initModal() {
        if ($('#vbc-unified-picker').length > 0) return;

        var modalHtml = 
        '<div id="vbc-unified-picker" class="vbc-modal-overlay">' +
            '<div class="vbc-modal-box">' +
                '<div class="vbc-modal-header">' +
                    '<h3 class="vbc-modal-title">🎨 Bộ Chọn Ảnh / Icon VBC</h3>' +
                    '<div class="vbc-search-wrapper" style="display:none;">' +
                        '<span class="vbc-search-icon">🔍</span>' +
                        '<input type="text" class="vbc-search-input" placeholder="Tìm kiếm icon...">' +
                    '</div>' +
                    '<button class="vbc-modal-close" type="button">✕</button>' +
                '</div>' +
                '<div class="vbc-modal-nav">' +
                    '<button class="vbc-tab-btn active" data-tab="media" type="button">🖼️ Thư viện Ảnh (Upload SVG/PNG)</button>' +
                    '<button class="vbc-tab-btn" data-tab="lucide" type="button">Lucide</button>' +
                    '<button class="vbc-tab-btn" data-tab="fontawesome" type="button">Font Awesome 6</button>' +
                    '<button class="vbc-tab-btn" data-tab="remix" type="button">Remix Icon</button>' +
                    '<button class="vbc-tab-btn" data-tab="phosphor" type="button">Phosphor</button>' +
                    '<button class="vbc-tab-btn" data-tab="material" type="button">Material Symbols</button>' +
                '</div>' +
                '<div class="vbc-modal-body">' +
                    '<div class="vbc-media-view" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:15px;padding:40px 20px;">' +
                        '<div class="vbc-media-preview-box" style="width:96px;height:96px;border:2px dashed #3f3f46;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#18181b;padding:8px;">' +
                            '<span style="color:#71717a;font-size:32px;">🖼️</span>' +
                        '</div>' +
                        '<button type="button" class="vbc-open-wp-media-btn" style="background:#ef4444;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px;transition:all 0.2s;">Mở Thư Viện WordPress</button>' +
                        '<p style="color:#a1a1aa;font-size:12px;margin:0;text-align:center;">Bạn có thể tải lên file .svg, .png, .jpg từ máy tính hoặc chọn ảnh có sẵn.</p>' +
                    '</div>' +
                    '<div class="vbc-icon-grid" style="display:none;"></div>' +
                '</div>' +
                '<div class="vbc-modal-footer">' +
                    '<div class="vbc-selected-preview"><span>Đang chọn:</span><strong class="vbc-selected-name">None</strong></div>' +
                    '<button class="vbc-confirm-btn" type="button">✓ Xác Nhận Sử Dụng</button>' +
                '</div>' +
            '</div>' +
        '</div>';

        $('body').append(modalHtml);

        var $modal = $('#vbc-unified-picker');
        $modal.find('.vbc-modal-close').on('click', closeModal);
        $modal.on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });
        $modal.find('.vbc-tab-btn').on('click', function() { switchTab($(this).data('tab')); });
        $modal.find('.vbc-search-input').on('input', function() { filterIcons($(this).val()); });
        $modal.find('.vbc-confirm-btn').on('click', confirmSelection);
        $modal.find('.vbc-open-wp-media-btn').on('click', openWPMediaFrame);
    }

    function openModal($input) {
        $activeInput = $input;
        initModal();

        var $modal = $('#vbc-unified-picker');
        $modal.addClass('active');

        // Parse existing value
        var val = $input.val().trim();
        tempSelectedValue = val;

        if (val.indexOf('img:') === 0) {
            switchTab('media');
            showMediaPreview(val.substring(4));
        } else if (val.indexOf('icon:') === 0) {
            var iconClass = val.substring(5);
            // Auto detect tab
            var pack = 'lucide';
            if (iconClass.indexOf('fa-') >= 0) pack = 'fontawesome';
            else if (iconClass.indexOf('ri-') === 0) pack = 'remix';
            else if (iconClass.indexOf('ph-') >= 0 || iconClass.indexOf('ph ') === 0) pack = 'phosphor';
            else if (iconClass.indexOf('_') >= 0 && iconClass.indexOf('-') === -1) pack = 'material';
            switchTab(pack);
            selectIconItem(iconClass);
        } else {
            switchTab('media');
            clearMediaPreview();
        }
    }

    function closeModal() {
        $('#vbc-unified-picker').removeClass('active');
        $activeInput = null;
    }

    function switchTab(tab) {
        currentTab = tab;
        var $modal = $('#vbc-unified-picker');
        $modal.find('.vbc-tab-btn').removeClass('active');
        $modal.find('.vbc-tab-btn[data-tab="' + tab + '"]').addClass('active');

        if (tab === 'media') {
            $modal.find('.vbc-media-view').show();
            $modal.find('.vbc-icon-grid').hide();
            $modal.find('.vbc-search-wrapper').hide();
            $modal.find('.vbc-selected-name').text(tempSelectedValue.indexOf('img:') === 0 ? 'Ảnh: ' + tempSelectedValue.substring(4).split('/').pop() : 'None');
        } else {
            $modal.find('.vbc-media-view').hide();
            $modal.find('.vbc-icon-grid').show();
            $modal.find('.vbc-search-wrapper').show();
            $modal.find('.vbc-search-input').val('');
            renderIcons(ICON_LIBRARIES[tab]);
            
            var cleanIconClass = (tempSelectedValue.indexOf('icon:') === 0) ? tempSelectedValue.substring(5) : '';
            if (cleanIconClass) {
                selectIconItem(cleanIconClass);
            } else {
                $modal.find('.vbc-selected-name').text('None');
            }
        }
    }

    function openWPMediaFrame(e) {
        e.preventDefault();

        if (wpMediaFrame) {
            wpMediaFrame.open();
            return;
        }

        wpMediaFrame = wp.media({
            title: 'Chọn Ảnh SVG hoặc PNG / JPG làm Icon',
            button: { text: 'Use this image' },
            multiple: false,
            library: { type: ['image', 'image/svg+xml'] }
        });

        wpMediaFrame.on('select', function() {
            var attachment = wpMediaFrame.state().get('selection').first().toJSON();
            var url = attachment.url;
            console.log('[VBC Icon Picker] Selected WP image URL:', url);
            
            tempSelectedValue = 'img:' + url;
            showMediaPreview(url);
            $('#vbc-unified-picker .vbc-selected-name').text('Ảnh: ' + url.split('/').pop());
        });

        wpMediaFrame.open();
    }

    function showMediaPreview(url) {
        var $box = $('#vbc-unified-picker .vbc-media-preview-box');
        $box.css('border-style', 'solid').html('<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:contain;" />');
    }

    function clearMediaPreview() {
        var $box = $('#vbc-unified-picker .vbc-media-preview-box');
        $box.css('border-style', 'dashed').html('<span style="color:#71717a;font-size:32px;">🖼️</span>');
        $('#vbc-unified-picker .vbc-selected-name').text('None');
    }

    function renderIcons(icons) {
        var $grid = $('#vbc-unified-picker .vbc-icon-grid');
        $grid.empty();

        icons.forEach(function(icon) {
            var iconHtml = '';
            if (currentTab === 'fontawesome') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentTab === 'remix') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentTab === 'phosphor') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentTab === 'material') {
                iconHtml = '<span class="material-symbols-outlined">' + icon + '</span>';
            } else {
                iconHtml = '<i data-lucide="' + icon + '" style="display:inline-block;width:22px;height:22px;"></i>';
            }

            var cleanName = icon.replace('fa-solid ', '').replace('fa-brands ', '').replace('ri-', '').replace('ph ph-', '').replace('ph-', '');
            var $item = $('<div class="vbc-icon-item" data-icon="' + icon + '">' + iconHtml + '<div class="vbc-icon-name">' + cleanName + '</div></div>');

            $item.on('click', function() {
                tempSelectedValue = 'icon:' + icon;
                selectIconItem(icon);
            });

            $item.on('dblclick', function() {
                tempSelectedValue = 'icon:' + icon;
                selectIconItem(icon);
                confirmSelection();
            });

            $grid.append($item);
        });

        if (currentTab === 'lucide' && typeof lucide !== 'undefined') {
            try { lucide.createIcons(); } catch(e) {}
        }
    }

    function selectIconItem(iconClass) {
        var $modal = $('#vbc-unified-picker');
        $modal.find('.vbc-icon-item').removeClass('active');
        $modal.find('.vbc-icon-item[data-icon="' + iconClass + '"]').addClass('active');
        $modal.find('.vbc-selected-name').text(iconClass);
    }

    function filterIcons(query) {
        var term = query.toLowerCase().trim();
        $('#vbc-unified-picker .vbc-icon-item').each(function() {
            $(this).toggle($(this).data('icon').toLowerCase().includes(term));
        });
    }

    function confirmSelection() {
        if ($activeInput && tempSelectedValue) {
            writeValueToInput($activeInput, tempSelectedValue);
        }
        closeModal();
    }

    // Attach unified selection button to input
    function attachPickerButtons() {
        $('input[type="text"]').each(function() {
            var $input = $(this);
            var settingName = $input.data('setting') || '';
            if (settingName !== 'icon_value') return; // Target only our unified field

            // Avoid double insertion
            if ($input.parent().find('.vbc-unified-picker-btn').length > 0) return;

            // Hide the actual input to make UI super clean, showing only preview & button
            $input.css({
                'opacity': '0.7',
                'border-color': '#3f3f46',
                'background': '#18181b',
                'color': '#a1a1aa'
            });

            var $btn = $('<button type="button" class="vbc-unified-picker-btn" style="display:block;width:100%;margin-top:8px;background:#ef4444;color:#fff;border:none;border-radius:6px;padding:9px 15px;font-weight:700;cursor:pointer;font-size:13px;text-align:center;"><i class="fa-solid fa-icons" style="margin-right:6px;"></i> 🎨 Chọn Ảnh / Icon</button>');
            
            $btn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openModal($input);
            });

            $input.after($btn);
            
            // Render initial preview
            updateSidebarPreview($input);
        });
    }

    // Poller to hook into Flatsome dynamic React rendering
    $(document).ready(function() {
        setInterval(attachPickerButtons, 1000);
    });

})(jQuery);
