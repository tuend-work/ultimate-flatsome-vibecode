(function($) {
    'use strict';

    console.log('[VBC Icon Picker v1.3.1] Script loaded.');

    // =========================================================================
    // ICON LIBRARIES
    // =========================================================================
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

    let $activeInput = null;        // for sidebar popup
    let $lastFocusedIconInput = null; // last icon input that was focused — used by Media Library tab
    let currentPack = 'lucide';
    let selectedIcon = '';

    // =========================================================================
    // CORE: Write value to input and force UX Builder to detect the change
    // =========================================================================
    function writeIconToInput($input, iconClass) {
        if (!$input || $input.length === 0) {
            console.warn('[VBC Icon Picker] No target input to write to.');
            return false;
        }
        console.log('[VBC Icon Picker] Writing icon to input:', iconClass, $input[0]);

        // 1. Set value via jQuery
        $input.val(iconClass);

        // 2. Native input event (React, Vue, Alpine)
        var nativeInput = $input[0];
        var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');
        if (nativeInputValueSetter) {
            nativeInputValueSetter.set.call(nativeInput, iconClass);
        }

        // 3. Dispatch all relevant events
        ['input', 'change', 'keyup', 'blur'].forEach(function(evtName) {
            nativeInput.dispatchEvent(new Event(evtName, { bubbles: true, cancelable: true }));
        });

        // 4. jQuery trigger (Knockout.js / legacy jQuery bindings)
        $input.trigger('input').trigger('change').trigger('keyup');

        // 5. Focus then blur to force UX Builder dirty detection
        $input.focus();
        setTimeout(function() { $input.blur(); }, 50);

        return true;
    }

    // =========================================================================
    // 1. SIDEBAR POPUP ICON PICKER
    // =========================================================================
    function initModal() {
        if ($('#vbc-icon-picker').length > 0) return;
        var modalHtml = '<div id="vbc-icon-picker" class="vbc-modal-overlay">' +
            '<div class="vbc-modal-box">' +
                '<div class="vbc-modal-header">' +
                    '<h3 class="vbc-modal-title">Visual Icon Browser</h3>' +
                    '<div class="vbc-search-wrapper">' +
                        '<span class="vbc-search-icon">🔍</span>' +
                        '<input type="text" class="vbc-search-input" placeholder="Search icons...">' +
                    '</div>' +
                    '<button class="vbc-modal-close" type="button">✕</button>' +
                '</div>' +
                '<div class="vbc-modal-nav">' +
                    '<button class="vbc-tab-btn" data-tab="lucide" type="button">Lucide</button>' +
                    '<button class="vbc-tab-btn" data-tab="fontawesome" type="button">Font Awesome 6</button>' +
                    '<button class="vbc-tab-btn" data-tab="remix" type="button">Remix Icon</button>' +
                    '<button class="vbc-tab-btn" data-tab="phosphor" type="button">Phosphor</button>' +
                    '<button class="vbc-tab-btn" data-tab="material" type="button">Material Symbols</button>' +
                '</div>' +
                '<div class="vbc-modal-body"><div class="vbc-icon-grid"></div></div>' +
                '<div class="vbc-modal-footer">' +
                    '<div class="vbc-selected-preview"><span>Selected:</span><strong class="vbc-selected-name">None</strong></div>' +
                    '<button class="vbc-confirm-btn" type="button">✓ Select Icon</button>' +
                '</div>' +
            '</div>' +
        '</div>';
        $('body').append(modalHtml);

        var $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-modal-close').on('click', closeModal);
        $modal.on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });
        $modal.find('.vbc-tab-btn').on('click', function() { switchTab($(this).data('tab')); });
        $modal.find('.vbc-search-input').on('input', function() { filterIcons($(this).val(), '#vbc-icon-picker'); });
        $modal.find('.vbc-confirm-btn').on('click', confirmSidebarSelection);
    }

    function openModal($input) {
        $activeInput = $input;
        initModal();
        var $modal = $('#vbc-icon-picker');
        $modal.addClass('active');

        // Auto-detect pack from sibling select
        var pack = 'lucide';
        var $row = $input.closest('[class]');
        var $packSelect = $row.find('select');
        if ($packSelect.length > 0) {
            var v = $packSelect.val();
            if (v === 'google' || v === 'material') pack = 'material';
            else if (v === 'fa') pack = 'fontawesome';
            else if (v === 'ri') pack = 'remix';
            else if (v === 'ph') pack = 'phosphor';
            else if (v === 'lucide') pack = 'lucide';
        }

        switchTab(pack);
        var currentVal = $input.val().trim();
        selectedIcon = currentVal || '';
        $modal.find('.vbc-selected-name').text(selectedIcon || 'None');
        $modal.find('.vbc-search-input').val('');
    }

    function closeModal() {
        $('#vbc-icon-picker').removeClass('active');
        $activeInput = null;
    }

    function switchTab(tab) {
        currentPack = tab;
        var $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-tab-btn').removeClass('active');
        $modal.find('.vbc-tab-btn[data-tab="' + tab + '"]').addClass('active');
        renderIcons(ICON_LIBRARIES[tab], $modal.find('.vbc-icon-grid'), selectIcon);
        $modal.find('.vbc-search-input').val('');
    }

    function renderIcons(icons, $grid, onSelect) {
        $grid.empty();
        icons.forEach(function(icon) {
            var iconHtml = '';
            if (currentPack === 'fontawesome') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentPack === 'remix') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentPack === 'phosphor') {
                iconHtml = '<i class="' + icon + '"></i>';
            } else if (currentPack === 'material') {
                iconHtml = '<span class="material-symbols-outlined">' + icon + '</span>';
            } else {
                iconHtml = '<i data-lucide="' + icon + '" style="display:inline-block;width:22px;height:22px;"></i>';
            }

            var cleanName = icon.replace('fa-solid ', '').replace('fa-brands ', '').replace('ri-', '').replace('ph ph-', '').replace('ph-', '');
            var $item = $('<div class="vbc-icon-item" data-icon="' + icon + '">' + iconHtml + '<div class="vbc-icon-name">' + cleanName + '</div></div>');

            $item.on('click', function() { onSelect(icon); });
            $item.on('dblclick', function() { onSelect(icon); confirmSidebarSelection(); });
            $grid.append($item);
        });

        if (currentPack === 'lucide' && typeof lucide !== 'undefined') {
            try { lucide.createIcons(); } catch(e) {}
        }
    }

    function filterIcons(query, modalSelector) {
        var term = query.toLowerCase().trim();
        $(modalSelector).find('.vbc-icon-item').each(function() {
            $(this).toggle($(this).data('icon').toLowerCase().includes(term));
        });
    }

    function selectIcon(icon) {
        selectedIcon = icon;
        var $modal = $('#vbc-icon-picker');
        $modal.find('.vbc-icon-item').removeClass('active');
        $modal.find('.vbc-icon-item[data-icon="' + icon + '"]').addClass('active');
        $modal.find('.vbc-selected-name').text(icon);
    }

    function confirmSidebarSelection() {
        if ($activeInput && selectedIcon) {
            writeIconToInput($activeInput, selectedIcon);
        }
        closeModal();
    }

    // =========================================================================
    // 2. MEDIA LIBRARY "ICON LIBRARY" TAB
    // =========================================================================

    // Track the last icon input that received focus — this is our write target
    $(document).on('focusin', 'input[type="text"]', function() {
        var $input = $(this);
        var labelText = ($input.closest('[class]').find('label').text() || '').toLowerCase();
        var placeholder = ($input.attr('placeholder') || '').toLowerCase();
        var dataSetting = ($input.data('setting') || '').toLowerCase();

        var isIconField = labelText.includes('icon') || labelText.includes('legacy') ||
                          labelText.includes('tự do') || placeholder.includes('fa-') ||
                          dataSetting.includes('icon');
        if (isIconField) {
            $lastFocusedIconInput = $input;
            console.log('[VBC Icon Picker] Tracked icon input:', labelText || dataSetting);
        }
    });

    // Inject "Icon Library" tab into media modal when it appears
    var mediaModalObserver = new MutationObserver(function() {
        var $modal = $('.media-modal');
        if ($modal.length === 0) return;
        var $router = $modal.find('.media-router');
        if ($router.length > 0 && $router.find('.vbc-media-tab').length === 0) {
            console.log('[VBC Icon Picker] Injecting Icon Library tab into Media Library.');
            var $tab = $('<button type="button" class="media-menu-item vbc-media-tab">🎨 Icon Library</button>');
            $router.append($tab);
            $tab.on('click', function(e) {
                e.preventDefault();
                openMediaIconTab();
            });
        }
    });
    mediaModalObserver.observe(document.body, { childList: true, subtree: true });

    // When other tabs are clicked, hide our panel
    $(document).on('click', '.media-modal .media-router .media-menu-item:not(.vbc-media-tab)', function() {
        closeMediaIconTab();
    });

    var mediaPack = 'lucide';
    var mediaSelectedIcon = '';

    function openMediaIconTab() {
        var $modal = $('.media-modal');
        $modal.find('.media-router .media-menu-item').removeClass('active');
        $modal.find('.vbc-media-tab').addClass('active');
        $modal.find('.media-frame-content').children(':not(.vbc-media-picker-container)').hide();

        var $picker = $modal.find('.vbc-media-picker-container');
        if ($picker.length === 0) {
            $picker = buildMediaPicker();
            $modal.find('.media-frame-content').append($picker);
        } else {
            $picker.show();
        }

        switchMediaTab(mediaPack);
        $picker.find('.vbc-media-search-input').val('');
        $picker.find('.vbc-media-selected-name').text('None');
        mediaSelectedIcon = '';
    }

    function buildMediaPicker() {
        var $picker = $('<div class="vbc-media-picker-container"></div>').css({
            display: 'flex', flexDirection: 'column', height: '100%',
            background: '#18181b', color: '#f4f4f5', padding: '20px', boxSizing: 'border-box'
        });

        var tabBtns = ['lucide','fontawesome','remix','phosphor','material'].map(function(t, i) {
            var label = ['Lucide','Font Awesome','Remix','Phosphor','Material'][i];
            return '<button class="vbc-media-tab-btn' + (i===0?' active':'') + '" data-tab="' + t + '" type="button" style="background:transparent;border:none;color:' + (i===0?'#ef4444':'#a1a1aa') + ';font-weight:600;cursor:pointer;padding:8px 12px;font-size:13px;">' + label + '</button>';
        }).join('');

        $picker.html(
            '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;gap:20px;">' +
                '<h3 style="margin:0;font-size:16px;font-weight:800;color:#fff;">🎨 Icon Library</h3>' +
                '<input type="text" class="vbc-media-search-input" placeholder="Search icons..." style="flex-grow:1;max-width:280px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:6px;padding:7px 12px;color:#fff;font-size:13px;outline:none;">' +
            '</div>' +
            '<div style="display:flex;gap:5px;margin-bottom:15px;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:5px;">' + tabBtns + '</div>' +
            '<div style="flex-grow:1;overflow-y:auto;">' +
                '<div class="vbc-media-grid vbc-icon-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px;"></div>' +
            '</div>' +
            '<div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.06);padding-top:15px;margin-top:15px;">' +
                '<div style="font-size:13px;color:#a1a1aa;">Selected: <strong class="vbc-media-selected-name" style="color:#fff;margin-left:6px;">None</strong></div>' +
                '<button class="vbc-media-confirm-btn" type="button" style="background:#ef4444;color:#fff;border:none;border-radius:6px;padding:9px 20px;font-weight:700;cursor:pointer;font-size:13px;">✓ Confirm Icon</button>' +
            '</div>'
        );

        $picker.find('.vbc-media-search-input').on('input', function() {
            filterIcons($(this).val(), '.vbc-media-picker-container');
        });

        $picker.find('.vbc-media-tab-btn').on('click', function() {
            $picker.find('.vbc-media-tab-btn').css('color', '#a1a1aa').removeClass('active');
            $(this).css('color', '#ef4444').addClass('active');
            switchMediaTab($(this).data('tab'));
        });

        $picker.find('.vbc-media-confirm-btn').on('click', confirmMediaSelection);

        return $picker;
    }

    function switchMediaTab(tab) {
        mediaPack = tab;
        currentPack = tab; // shared for renderIcons
        var $grid = $('.vbc-media-picker-container .vbc-media-grid');
        renderIcons(ICON_LIBRARIES[tab], $grid, selectMediaIcon);
    }

    function selectMediaIcon(icon) {
        mediaSelectedIcon = icon;
        $('.vbc-media-picker-container .vbc-icon-item').removeClass('active');
        $('.vbc-media-picker-container .vbc-icon-item[data-icon="' + icon + '"]').addClass('active');
        $('.vbc-media-selected-name').text(icon);
    }

    function confirmMediaSelection() {
        if (!mediaSelectedIcon) {
            alert('Vui lòng chọn một icon trước.');
            return;
        }

        // Strategy 1: write to last focused icon input
        var written = false;
        if ($lastFocusedIconInput && $lastFocusedIconInput.length > 0 && $.contains(document, $lastFocusedIconInput[0])) {
            written = writeIconToInput($lastFocusedIconInput, mediaSelectedIcon);
        }

        // Strategy 2: fallback — find any visible icon input in sidebar
        if (!written) {
            $('input[type="text"]').each(function() {
                var $inp = $(this);
                var label = ($inp.closest('[class]').find('label').text() || '').toLowerCase();
                var setting = ($inp.data('setting') || '').toLowerCase();
                if (label.includes('icon') || label.includes('legacy') || label.includes('tự do') || setting.includes('icon')) {
                    if ($inp.is(':visible')) {
                        writeIconToInput($inp, mediaSelectedIcon);
                        written = true;
                        return false; // break
                    }
                }
            });
        }

        if (!written) {
            // Strategy 3: copy to clipboard as last resort
            if (navigator.clipboard) {
                navigator.clipboard.writeText(mediaSelectedIcon);
                alert('Đã copy "' + mediaSelectedIcon + '" vào clipboard. Vui lòng paste vào trường icon.');
            } else {
                alert('Icon: ' + mediaSelectedIcon + '\nVui lòng copy và paste vào trường icon.');
            }
        }

        // Close the media modal
        $('.media-modal .media-modal-close, .media-modal button.button-link').first().trigger('click');
    }

    function closeMediaIconTab() {
        var $modal = $('.media-modal');
        $modal.find('.vbc-media-picker-container').hide();
        $modal.find('.media-frame-content').children(':not(.vbc-media-picker-container)').show();
    }

    // =========================================================================
    // 3. INJECT "BROWSE ICONS" BUTTON NEXT TO ICON TEXT INPUTS IN SIDEBAR
    // =========================================================================
    function attachPickerButtons() {
        $('input[type="text"]').each(function() {
            var $input = $(this);
            // Skip if already has button, or is inside our own modal
            if ($input.closest('#vbc-icon-picker, .vbc-media-picker-container, .media-modal').length > 0) return;
            if ($input.parent().find('.vbc-picker-btn').length > 0) return;

            var $row = $input.closest('[class]');
            var labelText = ($row.find('label').first().text() || '').toLowerCase();
            var placeholder = ($input.attr('placeholder') || '').toLowerCase();
            var dataSetting = ($input.data('setting') || '').toLowerCase();
            var nameAttr = ($input.attr('name') || '').toLowerCase();

            var isIconField = labelText.includes('icon') ||
                              labelText.includes('legacy') ||
                              labelText.includes('tự do') ||
                              placeholder.includes('fa-') ||
                              dataSetting.includes('icon') ||
                              nameAttr.includes('icon');

            if (isIconField) {
                console.log('[VBC Icon Picker] Attaching Browse Icons button to:', labelText || dataSetting || nameAttr);
                var $btn = $('<button type="button" class="vbc-picker-btn"><i class="fa-solid fa-icons"></i> Browse Icons</button>');
                $btn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openModal($input);
                });
                $input.after($btn);
            }
        });
    }

    // Run periodically to catch dynamically rendered sidebar panels
    $(document).ready(function() {
        setInterval(attachPickerButtons, 1200);
    });

})(jQuery);
