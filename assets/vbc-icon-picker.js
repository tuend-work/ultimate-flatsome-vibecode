(function($) {
    'use strict';

    console.log('[VBC Icon Picker] Script loaded successfully.');

    // Curated list of popular icons for each library pack
    const ICON_LIBRARIES = {
        lucide: [
            'home', 'user', 'mail', 'phone', 'search', 'settings', 'check', 'x', 'shield', 'shield-check',
            'shield-alert', 'zap', 'zap-off', 'star', 'info', 'alert-triangle', 'trash-2', 'lock', 'key',
            'activity', 'file-text', 'code', 'smartphone', 'trending-up', 'credit-card', 'database-backup',
            'refresh-cw', 'git-commit', 'heart', 'message-square', 'sparkles', 'gauge', 'cloud-lightning',
            'hammer', 'monitor', 'laptop', 'server', 'globe', 'cloud', 'droplet', 'cpu', 'database',
            'terminal', 'arrow-right', 'arrow-left', 'play', 'pause', 'download', 'upload', 'menu',
            'check-circle', 'x-circle', 'info-circle', 'help-circle', 'alert-circle', 'clock', 'calendar',
            'map-pin', 'folder', 'file', 'image', 'video', 'music', 'link', 'external-link', 'eye', 'eye-off'
        ],
        fontawesome: [
            'fa-solid fa-house', 'fa-solid fa-user', 'fa-solid fa-envelope', 'fa-solid fa-phone', 'fa-solid fa-magnifying-glass',
            'fa-solid fa-gear', 'fa-solid fa-check', 'fa-solid fa-xmark', 'fa-solid fa-shield-halved', 'fa-solid fa-bolt',
            'fa-solid fa-star', 'fa-solid fa-circle-info', 'fa-solid fa-circle-exclamation', 'fa-solid fa-trash', 'fa-solid fa-lock',
            'fa-solid fa-key', 'fa-solid fa-chart-line', 'fa-solid fa-server', 'fa-solid fa-globe', 'fa-solid fa-cloud',
            'fa-solid fa-cpu', 'fa-solid fa-database', 'fa-solid fa-terminal', 'fa-solid fa-code', 'fa-solid fa-heart',
            'fa-solid fa-comment', 'fa-solid fa-bell', 'fa-solid fa-bookmark', 'fa-solid fa-share', 'fa-solid fa-link',
            'fa-solid fa-arrow-right', 'fa-solid fa-arrow-left', 'fa-solid fa-download', 'fa-solid fa-upload', 'fa-solid fa-gauge',
            'fa-solid fa-screwdriver-wrench', 'fa-solid fa-arrows-rotate', 'fa-solid fa-credit-card', 'fa-solid fa-circle-check', 'fa-solid fa-circle-xmark',
            'fa-brands fa-facebook', 'fa-brands fa-google', 'fa-brands fa-youtube', 'fa-brands fa-twitter', 'fa-brands fa-instagram',
            'fa-brands fa-github', 'fa-brands fa-tiktok', 'fa-brands fa-wordpress'
        ],
        remix: [
            'ri-home-line', 'ri-user-line', 'ri-mail-line', 'ri-phone-line', 'ri-search-line', 'ri-settings-line',
            'ri-check-line', 'ri-close-line', 'ri-shield-check-line', 'ri-flashlight-line', 'ri-star-line', 'ri-information-line',
            'ri-alert-line', 'ri-delete-bin-line', 'ri-lock-line', 'ri-key-line', 'ri-line-chart-line', 'ri-heart-line',
            'ri-chat-3-line', 'ri-notification-line', 'ri-server-line', 'ri-global-line', 'ri-cloud-line', 'ri-cpu-line',
            'ri-database-line', 'ri-terminal-line', 'ri-code-line', 'ri-arrow-right-line', 'ri-arrow-left-line', 'ri-download-line',
            'ri-upload-line', 'ri-facebook-fill', 'ri-google-fill', 'ri-youtube-fill', 'ri-twitter-x-fill', 'ri-instagram-fill',
            'ri-shield-cross-line', 'ri-spam-line', 'ri-database-2-line', 'ri-braces-line', 'ri-mac-line'
        ],
        phosphor: [
            'ph-house', 'ph-user', 'ph-envelope', 'ph-phone', 'ph-magnifying-glass', 'ph-gear', 'ph-check', 'ph-x',
            'ph-shield', 'ph-shield-check', 'ph-lightning', 'ph-star', 'ph-info', 'ph-warning', 'ph-trash', 'ph-lock',
            'ph-key', 'ph-chart-line', 'ph-server', 'ph-globe', 'ph-cloud', 'ph-cpu', 'ph-database', 'ph-terminal',
            'ph-code', 'ph-heart', 'ph-chat', 'ph-bell', 'ph-arrow-right', 'ph-arrow-left', 'ph-download', 'ph-upload',
            'ph-facebook-logo', 'ph-instagram-logo', 'ph-youtube-logo', 'ph-twitter-logo', 'ph-shield-warning', 'ph-activity'
        ],
        material: [
            'home', 'person', 'mail', 'call', 'search', 'settings', 'check', 'close', 'security', 'shield',
            'bolt', 'star', 'info', 'warning', 'delete', 'lock', 'vpn_key', 'trending_up', 'dns', 'language',
            'cloud', 'memory', 'storage', 'terminal', 'code', 'favorite', 'chat', 'notifications', 'arrow_forward',
            'arrow_back', 'download', 'upload', 'shield_heart', 'bug_report', 'build', 'cached'
        ]
    };

    let $activeInput = null;
    let $activeMediaInput = null;
    let currentPack = 'lucide';
    let selectedIcon = '';

    // =========================================================================
    // 1. SIDEBAR POPUP ICON PICKER
    // =========================================================================

    function initModal() {
        if ($('#vbc-icon-picker').length > 0) return;

        const modalHtml = `
            <div id="vbc-icon-picker" class="vbc-modal-overlay">
                <div class="vbc-modal-box">
                    <div class="vbc-modal-header">
                        <h3 class="vbc-modal-title">Visual Icon Browser</h3>
                        <div class="vbc-search-wrapper">
                            <span class="vbc-search-icon">🔍</span>
                            <input type="text" class="vbc-search-input" placeholder="Search icons...">
                        </div>
                        <button class="vbc-modal-close" type="button">✕</button>
                    </div>
                    <div class="vbc-modal-nav">
                        <button class="vbc-tab-btn" data-tab="lucide" type="button">Lucide</button>
                        <button class="vbc-tab-btn" data-tab="fontawesome" type="button">Font Awesome 6</button>
                        <button class="vbc-tab-btn" data-tab="remix" type="button">Remix Icon</button>
                        <button class="vbc-tab-btn" data-tab="phosphor" type="button">Phosphor</button>
                        <button class="vbc-tab-btn" data-tab="material" type="button">Material Symbols</button>
                    </div>
                    <div class="vbc-modal-body">
                        <div class="vbc-icon-grid"></div>
                    </div>
                    <div class="vbc-modal-footer">
                        <div class="vbc-selected-preview">
                            <span>Selected:</span>
                            <strong class="vbc-selected-name">None</strong>
                        </div>
                        <button class="vbc-confirm-btn" type="button">Select Icon</button>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);

        const $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-modal-close').on('click', closeModal);
        $modal.on('click', function(e) {
            if ($(e.target).is($modal)) closeModal();
        });

        $modal.find('.vbc-tab-btn').on('click', function() {
            switchTab($(this).data('tab'));
        });

        $modal.find('.vbc-search-input').on('input', function() {
            filterIcons($(this).val());
        });

        $modal.find('.vbc-confirm-btn').on('click', confirmSelection);
    }

    function openModal($input) {
        $activeInput = $input;
        initModal();

        const $modal = $('#vbc-icon-picker');
        $modal.addClass('active');

        let pack = 'lucide';
        const $sidebar = $input.closest('.uxbuilder-sidebar, .uxb-sidebar, .yoast, .uxbuilder-sidebar-content');
        if ($sidebar.length > 0) {
            const $packSelect = $sidebar.find('select[data-setting="pack"], select[name="pack"], [data-option="pack"] select');
            if ($packSelect.length > 0) {
                pack = $packSelect.val();
                if (pack === 'google') pack = 'material';
                if (pack === 'fa') pack = 'fontawesome';
                if (pack === 'ri') pack = 'remix';
                if (pack === 'ph') pack = 'phosphor';
            }
        }

        switchTab(pack);
        
        const currentVal = $input.val().trim();
        if (currentVal) {
            selectedIcon = currentVal;
            $modal.find('.vbc-selected-name').text(currentVal);
        } else {
            selectedIcon = '';
            $modal.find('.vbc-selected-name').text('None');
        }
    }

    function closeModal() {
        $('#vbc-icon-picker').removeClass('active');
        $activeInput = null;
    }

    function switchTab(tab) {
        currentPack = tab;
        const $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-tab-btn').removeClass('active');
        $modal.find(`.vbc-tab-btn[data-tab="${tab}"]`).addClass('active');
        
        renderIcons(ICON_LIBRARIES[tab], $modal.find('.vbc-icon-grid'), selectIcon);
        $modal.find('.vbc-search-input').val('');
    }

    function renderIcons(icons, $grid, onSelect) {
        $grid.empty();

        icons.forEach(icon => {
            let iconHtml = '';
            if (currentPack === 'fontawesome') {
                iconHtml = `<i class="${icon}"></i>`;
            } else if (currentPack === 'remix') {
                iconHtml = `<i class="${icon}"></i>`;
            } else if (currentPack === 'phosphor') {
                iconHtml = `<i class="${icon}"></i>`;
            } else if (currentPack === 'material') {
                iconHtml = `<span class="material-symbols-outlined">${icon}</span>`;
            } else {
                iconHtml = `<i class="vbc-lucide-ph" data-lucide="${icon}" style="display:inline-block;width:24px;height:24px;border:1px dashed rgba(255,255,255,0.1);border-radius:4px;text-align:center;line-height:22px;font-size:10px;">L</i>`;
            }

            const cleanName = icon.replace('fa-solid ', '').replace('fa-brands ', '').replace('ri-', '').replace('ph-', '');
            const $item = $(`
                <div class="vbc-icon-item" data-icon="${icon}">
                    ${iconHtml}
                    <div class="vbc-icon-name">${cleanName}</div>
                </div>
            `);

            $item.on('click', function() {
                onSelect(icon);
            });
            $item.on('dblclick', function() {
                onSelect(icon);
                confirmSelection();
            });

            $grid.append($item);
        });

        if (currentPack === 'lucide' && typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function filterIcons(query) {
        const term = query.toLowerCase().trim();
        const $items = $('#vbc-icon-picker').find('.vbc-icon-item');
        $items.each(function() {
            const iconName = $(this).data('icon').toLowerCase();
            $(this).toggle(iconName.includes(term));
        });
    }

    function selectIcon(icon) {
        selectedIcon = icon;
        const $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-icon-item').removeClass('active').css('border-color', '');
        $modal.find(`.vbc-icon-item[data-icon="${icon}"]`).addClass('active').css('border-color', '#ef4444');
        $modal.find('.vbc-selected-name').text(icon);
    }

    function confirmSelection() {
        if ($activeInput && selectedIcon) {
            $activeInput.val(selectedIcon);
            $activeInput[0].dispatchEvent(new Event('input', { bubbles: true }));
            $activeInput[0].dispatchEvent(new Event('change', { bubbles: true }));
        }
        closeModal();
    }

    // =========================================================================
    // 2. WORDPRESS MEDIA LIBRARY "ICON" TAB INTEGRATION
    // =========================================================================

    // Keep track of the active setting input when media library opens
    $(document).on('click', '.uxbuilder-sidebar button, .uxb-sidebar button, .ux-builder-sidebar button, button.select-media, button.uxb-select-image, .ux-builder button', function() {
        const $row = $(this).closest('.uxb-field, .ux-builder-option, .uxb-option, [class*="option"], [class*="field"], div');
        $activeMediaInput = $row.find('input[type="text"], input[type="hidden"], input[data-setting], input.uxb-image-input');
        console.log('[VBC Icon Picker] Registered active media input:', $activeMediaInput);
    });

    function integrateMediaLibraryTab() {
        const $modal = $('.media-modal');
        if ($modal.length === 0) return;

        const $router = $modal.find('.media-router');
        if ($router.length > 0 && $router.find('.vbc-media-tab').length === 0) {
            console.log('[VBC Icon Picker] Media library popup found. Injecting "Icon Library" tab.');
            
            const $tab = $('<button type="button" class="media-menu-item vbc-media-tab">Icon Library</button>');
            $router.append($tab);
            
            $tab.on('click', function(e) {
                e.preventDefault();
                openMediaIconTab();
            });
        }
    }

    // When the user clicks other tabs, ensure we clean up the custom Icon view
    $(document).on('click', '.media-modal .media-router .media-menu-item:not(.vbc-media-tab)', function() {
        closeMediaIconTab();
    });

    function openMediaIconTab() {
        const $modal = $('.media-modal');
        $modal.find('.media-router .media-menu-item').removeClass('active');
        $modal.find('.vbc-media-tab').addClass('active');

        // Hide default Backbone.js elements
        $modal.find('.media-frame-content').children(':not(.vbc-media-picker-container)').hide();

        let $picker = $modal.find('.media-frame-content .vbc-media-picker-container');
        if ($picker.length === 0) {
            $picker = $(`
                <div class="vbc-media-picker-container" style="display:flex; flex-direction:column; height:100%; background:#18181b; color:#f4f4f5; padding:20px; box-sizing:border-box;">
                    <div class="vbc-media-picker-header" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:15px; gap: 20px;">
                        <h3 style="margin:0; font-size:16px; font-weight:800; color:#fff; font-family:sans-serif;">Select Icon</h3>
                        <div class="vbc-media-search-wrapper" style="position:relative; flex-grow:1; max-width:300px;">
                            <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#71717a; font-size:13px;">🔍</span>
                            <input type="text" class="vbc-media-search-input" placeholder="Search icons..." style="width:100%; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:6px; padding:6px 12px 6px 30px; color:#fff; font-size:13px; outline:none; box-sizing:border-box;">
                        </div>
                    </div>
                    <div class="vbc-media-tabs" style="display:flex; gap:10px; margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:5px;">
                        <button class="vbc-media-tab-btn active" data-tab="lucide" type="button" style="background:transparent; border:none; color:#ef4444; font-weight:600; cursor:pointer; padding:8px 12px; font-size:13px;">Lucide</button>
                        <button class="vbc-media-tab-btn" data-tab="fontawesome" type="button" style="background:transparent; border:none; color:#a1a1aa; font-weight:600; cursor:pointer; padding:8px 12px; font-size:13px;">Font Awesome</button>
                        <button class="vbc-media-tab-btn" data-tab="remix" type="button" style="background:transparent; border:none; color:#a1a1aa; font-weight:600; cursor:pointer; padding:8px 12px; font-size:13px;">Remix</button>
                        <button class="vbc-media-tab-btn" data-tab="phosphor" type="button" style="background:transparent; border:none; color:#a1a1aa; font-weight:600; cursor:pointer; padding:8px 12px; font-size:13px;">Phosphor</button>
                        <button class="vbc-media-tab-btn" data-tab="material" type="button" style="background:transparent; border:none; color:#a1a1aa; font-weight:600; cursor:pointer; padding:8px 12px; font-size:13px;">Material</button>
                    </div>
                    <div class="vbc-media-grid-wrapper" style="flex-grow:1; overflow-y:auto; padding-right:5px;">
                        <div class="vbc-media-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:10px;"></div>
                    </div>
                    <div class="vbc-media-footer" style="display:flex; align-items:center; justify-content:space-between; border-top:1px solid rgba(255,255,255,0.06); padding-top:15px; margin-top:15px;">
                        <div style="font-size:13px; color:#a1a1aa; font-family:sans-serif;">Selected: <strong class="vbc-media-selected-name" style="color:#fff;">None</strong></div>
                        <button class="vbc-media-confirm-btn" type="button" style="background:#ef4444; color:#fff; border:none; border-radius:4px; padding:8px 16px; font-weight:700; cursor:pointer; font-size:13px;">Confirm Icon</button>
                    </div>
                </div>
            `);
            $modal.find('.media-frame-content').append($picker);
            
            $picker.find('.vbc-media-search-input').on('input', function() {
                filterMediaIcons($(this).val());
            });
            
            $picker.find('.vbc-media-tab-btn').on('click', function() {
                $picker.find('.vbc-media-tab-btn').removeClass('active').css('color', '#a1a1aa');
                $(this).addClass('active').css('color', '#ef4444');
                switchMediaTab($(this).data('tab'));
            });
            
            $picker.find('.vbc-media-confirm-btn').on('click', confirmMediaSelection);
        } else {
            $picker.show();
        }
        
        switchMediaTab('lucide');
        $picker.find('.vbc-media-search-input').val('');
        $picker.find('.vbc-media-selected-name').text('None');
        selectedIcon = '';
    }

    function closeMediaIconTab() {
        const $modal = $('.media-modal');
        $modal.find('.media-frame-content .vbc-media-picker-container').hide();
        $modal.find('.media-frame-content').children(':not(.vbc-media-picker-container)').show();
    }

    function switchMediaTab(tab) {
        currentPack = tab;
        const $modal = $('.media-modal');
        const $grid = $modal.find('.vbc-media-grid');
        
        renderIcons(ICON_LIBRARIES[tab], $grid, selectMediaIcon);
        $modal.find('.vbc-media-search-input').val('');
    }

    function filterMediaIcons(query) {
        const term = query.toLowerCase().trim();
        const $items = $('.media-modal').find('.vbc-icon-item');
        $items.each(function() {
            const iconName = $(this).data('icon').toLowerCase();
            $(this).toggle(iconName.includes(term));
        });
    }

    function selectMediaIcon(icon) {
        selectedIcon = icon;
        const $modal = $('.media-modal');
        $modal.find('.vbc-icon-item').removeClass('active').css('border-color', '');
        $modal.find(`.vbc-icon-item[data-icon="${icon}"]`).addClass('active').css('border-color', '#ef4444');
        $modal.find('.vbc-media-selected-name').text(icon);
    }

    function confirmMediaSelection() {
        if ($activeMediaInput && selectedIcon) {
            console.log('[VBC Icon Picker] Setting media input to:', selectedIcon);
            $activeMediaInput.val(selectedIcon);
            $activeMediaInput[0].dispatchEvent(new Event('input', { bubbles: true }));
            $activeMediaInput[0].dispatchEvent(new Event('change', { bubbles: true }));
        }
        $('.media-modal .media-modal-close').click();
    }

    // =========================================================================
    // 3. SCAN AND INJECT SIDEBAR BUTTONS
    // =========================================================================

    function attachPickerButtons() {
        // Find input text fields anywhere on the page
        const $inputs = $('input[type="text"]');
        
        $inputs.each(function() {
            const $input = $(this);
            const $row = $input.closest('.uxb-field, .ux-builder-option, .uxb-option, .uxbuilder-sidebar-content > div, [class*="option"], [class*="field"], div');
            if ($row.length === 0) return;

            const labelText = ($row.find('label').text() || '').toLowerCase();
            const placeholder = ($input.attr('placeholder') || '').toLowerCase();
            const dataSetting = ($input.data('setting') || '').toLowerCase();
            const nameAttr = ($input.attr('name') || '').toLowerCase();

            const isIconField = labelText.includes('icon') || 
                               labelText.includes('legacy') || 
                               labelText.includes('tự do') || 
                               placeholder.includes('fa-') || 
                               dataSetting.includes('icon') || 
                               nameAttr.includes('icon');

            if (isIconField) {
                if ($input.parent().find('.vbc-picker-btn').length === 0) {
                    console.log('[VBC Icon Picker] Attaching button to field:', labelText);
                    const $btn = $('<button type="button" class="vbc-picker-btn"><i class="fa-solid fa-icons"></i> Browse Icons</button>');
                    $btn.on('click', function(e) {
                        e.preventDefault();
                        openModal($input);
                    });
                    $input.after($btn);
                }
            }
        });
    }

    // Periodically run search loops
    $(document).ready(function() {
        setInterval(attachPickerButtons, 1000);
        setInterval(integrateMediaLibraryTab, 1000);
    });

})(jQuery);
