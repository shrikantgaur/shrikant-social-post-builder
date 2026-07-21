/**
 * SK Social Post Builder - Admin JavaScript Actions
 *
 * @package SK\SocialPostBuilder
 */

jQuery(document).ready(function($) {
    'use strict';

    // Global App State
    const SPB_App = {
        selectedPosts: [], // Array of post objects {id, title, url, date, category}
        currentStep: 1,
        defaultEmoji: '🔥',
        ajaxUrl: ajaxurl, // WordPress global ajaxurl
        nonce: $('#shrikant_spb_nonce').val(),
        historyPage: 1,
        historyLimit: 10,
        tempEditorInstance: null
    };

    // Initialize the app
    function init() {
        bindEvents();
        loadDashboardStats();
        loadPostsForSelection();
        loadHistoryTable();
        loadTemplatesList();

        // Check if there is an active tab hash
        const hash = window.location.hash;
        if (hash && $('.shrikant-spb-nav-item[href="' + hash + '"]').length) {
            switchTab(hash);
        } else {
            const activePHP = $('.shrikant-spb-nav-item.active').attr('href');
            if (activePHP) {
                switchTab(activePHP);
            } else {
                switchTab('#dashboard');
            }
        }
    }

    // Helper: Show Toast Notification
    function showToast(message, type = 'success') {
        const container = $('.shrikant-spb-toast-container');
        const toast = $('<div class="shrikant-spb-toast ' + type + '"><span class="dashicons dashicons-' + 
            (type === 'success' ? 'yes' : 'warning') + '"></span> ' + message + '</div>');
        container.append(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Tab switcher
    function switchTab(targetId) {
        $('.shrikant-spb-nav-item').removeClass('active');
        $('.shrikant-spb-nav-item[href="' + targetId + '"]').addClass('active');
        $('.shrikant-spb-tab-content').hide();
        $(targetId).show();
        window.location.hash = targetId;

        // Special refreshes
        if (targetId === '#dashboard') {
            loadDashboardStats();
        } else if (targetId === '#history') {
            loadHistoryTable();
        } else if (targetId === '#templates') {
            loadTemplatesList();
        }
    }

    // Bind Event Listeners
    function bindEvents() {
        // Tab clicks
        $('.shrikant-spb-nav-item').on('click', function(e) {
            e.preventDefault();
            switchTab($(this).attr('href'));
        });

        // Quick action buttons
        $('.action-create-new').on('click', function() { switchTab('#create-post'); });
        $('.action-open-history').on('click', function() { switchTab('#history'); });
        $('.action-open-settings').on('click', function() { switchTab('#settings'); });

        // Wizard navigation
        $('.btn-next-step').on('click', function() {
            if (validateStep(SPB_App.currentStep)) {
                goToStep(SPB_App.currentStep + 1);
            }
        });

        $('.btn-prev-step').on('click', function() {
            goToStep(SPB_App.currentStep - 1);
        });

        // Post Selection - Filter triggers
        $('#spb-post-search, #spb-post-cat, #spb-post-tag, #spb-post-author, #spb-post-date').on('change keyup', function() {
            // Debounce search
            if ($(this).attr('id') === 'spb-post-search') {
                clearTimeout(SPB_App.searchTimeout);
                SPB_App.searchTimeout = setTimeout(loadPostsForSelection, 300);
            } else {
                loadPostsForSelection();
            }
        });

        // Custom date filter visibility toggling
        $('#spb-post-date').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.custom-date-range').show();
            } else {
                $('.custom-date-range').hide();
                $('#spb-post-start-date, #spb-post-end-date').val('');
            }
        });
        $('#spb-post-start-date, #spb-post-end-date').on('change', loadPostsForSelection);

        // Checkbox selections in Step 1
        $(document).on('change', '.spb-select-post-chk', function() {
            const postId = parseInt($(this).val(), 10);
            const row = $(this).closest('tr');
            const title = row.find('.post-title-cell').text();
            const url = row.data('url');
            const date = row.find('.post-date-cell').text();
            const cat = row.find('.post-cat-cell').text();

            if (this.checked) {
                // Add to list
                if (!SPB_App.selectedPosts.some(p => p.id === postId)) {
                    SPB_App.selectedPosts.push({ id: postId, title, url, date, category: cat });
                }
            } else {
                // Remove
                SPB_App.selectedPosts = SPB_App.selectedPosts.filter(p => p.id !== postId);
            }
            updateStep2List();
        });

        // Select All checkboxes
        $('#spb-select-all-posts').on('change', function() {
            const isChecked = this.checked;
            $('.spb-select-post-chk').each(function() {
                this.checked = isChecked;
                $(this).trigger('change');
            });
        });

        // Template card clicks in Step 3
        $(document).on('click', '.shrikant-spb-template-card', function() {
            $('.shrikant-spb-template-card').removeClass('selected');
            $(this).addClass('selected');
            SPB_App.selectedTemplateId = $(this).data('id');
            SPB_App.selectedTemplateContent = $(this).data('content');
            SPB_App.selectedTemplateName = $(this).data('name');
        });

        // Step 5 Actions
        $('#btn-generate-message').on('click', generateCompilation);
        $('#btn-copy-message').on('click', copyCompilationText);
        $('#btn-save-draft').on('click', function() { saveMessageToHistory('draft'); });
        $('#btn-save-history').on('click', function() { saveMessageToHistory('generated'); });
        $('#btn-clear-wizard').on('click', resetWizard);

        // History list: search & date filters
        $('#spb-history-search, #spb-history-date-filter, #spb-history-template').on('change keyup', function() {
            SPB_App.historyPage = 1;
            loadHistoryTable();
        });
        $('#spb-history-date-filter').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.history-custom-date').show();
            } else {
                $('.history-custom-date').hide();
                $('#spb-history-start, #spb-history-end').val('');
            }
        });
        $('#spb-history-start, #spb-history-end').on('change', function() {
            SPB_App.historyPage = 1;
            loadHistoryTable();
        });

        // History item actions
        $(document).on('click', '.btn-history-view', function() {
            const id = $(this).closest('tr').data('id');
            viewHistoryItem(id);
        });

        $(document).on('click', '.btn-history-copy', function() {
            const id = $(this).closest('tr').data('id');
            copyHistoryItemDirectly(id);
        });

        $(document).on('click', '.btn-history-delete', function() {
            const id = $(this).closest('tr').data('id');
            if (confirm(shrikant_spb_admin_opts.i18n.confirm_delete)) {
                deleteHistoryItem(id);
            }
        });

        $(document).on('click', '.btn-history-duplicate', function() {
            const id = $(this).closest('tr').data('id');
            duplicateHistoryItem(id);
        });

        // Pagination buttons
        $(document).on('click', '#history-prev', function() {
            if (SPB_App.historyPage > 1) {
                SPB_App.historyPage--;
                loadHistoryTable();
            }
        });
        $(document).on('click', '#history-next', function() {
            SPB_App.historyPage++;
            loadHistoryTable();
        });

        // Settings saving
        $('#shrikant-spb-settings-form').on('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });

        // Template custom Editor Actions
        $('.btn-add-template').on('click', function() {
            openTemplateModal();
        });

        $(document).on('click', '.btn-edit-template', function() {
            const id = $(this).data('id');
            openTemplateModal(id);
        });

        $(document).on('click', '.btn-delete-template', function() {
            const id = $(this).data('id');
            if (confirm(shrikant_spb_admin_opts.i18n.confirm_delete_template)) {
                deleteTemplate(id);
            }
        });

        $('#btn-save-template').on('click', saveCustomTemplate);

        // Placeholders clicks in Template Edit modal
        $(document).on('click', '.shrikant-spb-placeholder-badge', function() {
            const placeholder = $(this).text();
            const textarea = $('#template-editor-content')[0];
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + placeholder + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
        });

        // Modal close clicks
        $('.shrikant-spb-modal-close, .btn-modal-close').on('click', function() {
            $('.shrikant-spb-modal').removeClass('active');
        });
    }

    // Handle Wizard Step Changes
    function goToStep(stepNum) {
        if (stepNum < 1 || stepNum > 5) return;

        // Update active class in wizard timeline header
        $('.shrikant-spb-wizard-step').removeClass('active completed');
        $('.shrikant-spb-wizard-step').each(function(idx) {
            const stepIdx = idx + 1;
            if (stepIdx < stepNum) {
                $(this).addClass('completed');
            } else if (stepIdx === stepNum) {
                $(this).addClass('active');
            }
        });

        // Toggle panel divs
        $('.shrikant-spb-wizard-panel').removeClass('active');
        $('#step-' + stepNum).addClass('active');

        SPB_App.currentStep = stepNum;

        // Button visibility overrides
        if (stepNum === 1) {
            $('.btn-prev-step').attr('disabled', true);
        } else {
            $('.btn-prev-step').removeAttr('disabled');
        }

        if (stepNum === 5) {
            $('.btn-next-step').hide();
            // Automatically trigger generate if not already done, or prompt
            if (!$('#spb-generated-output').val()) {
                generateCompilation();
            }
        } else {
            $('.btn-next-step').show();
        }
    }

    // Step Validation before progressing
    function validateStep(step) {
        if (step === 1) {
            if (SPB_App.selectedPosts.length === 0) {
                alert(shrikant_spb_admin_opts.i18n.select_posts_err);
                return false;
            }
        }
        if (step === 3) {
            if (!SPB_App.selectedTemplateId) {
                alert(shrikant_spb_admin_opts.i18n.select_template_err);
                return false;
            }
        }
        return true;
    }

    // STEP 1: Load Posts from Database via AJAX
    function loadPostsForSelection() {
        const postsTableBody = $('#spb-posts-table-body');
        postsTableBody.html('<tr><td colspan="5" style="text-align:center;"><span class="spinner is-active" style="float:none;margin:10px auto;"></span> ' + shrikant_spb_admin_opts.i18n.loading + '</td></tr>');

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_fetch_posts',
                nonce: SPB_App.nonce,
                search: $('#spb-post-search').val(),
                category: $('#spb-post-cat').val(),
                tag: $('#spb-post-tag').val(),
                author: $('#spb-post-author').val(),
                date_filter: $('#spb-post-date').val(),
                start_date: $('#spb-post-start-date').val(),
                end_date: $('#spb-post-end-date').val()
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(post => {
                        const isChecked = SPB_App.selectedPosts.some(p => p.id === post.id);
                        html += `<tr data-url="${post.url}" data-id="${post.id}">
                            <td><input type="checkbox" class="spb-select-post-chk" value="${post.id}" ${isChecked ? 'checked' : ''}></td>
                            <td class="post-title-cell"><strong>${post.title}</strong></td>
                            <td class="post-cat-cell">${post.category}</td>
                            <td class="post-date-cell">${post.date}</td>
                        </tr>`;
                    });
                    postsTableBody.html(html);
                } else {
                    postsTableBody.html('<tr><td colspan="5" style="text-align:center;">' + shrikant_spb_admin_opts.i18n.no_posts_found + '</td></tr>');
                }
            },
            error: function() {
                postsTableBody.html('<tr><td colspan="5" style="text-align:center;color:red;">' + shrikant_spb_admin_opts.i18n.error_occurred + '</td></tr>');
            }
        });
    }

    // STEP 2: Update Selected Posts lists with Drag & Drop handles
    function updateStep2List() {
        const container = $('#spb-sortable-list');
        container.empty();

        if (SPB_App.selectedPosts.length === 0) {
            container.html('<div style="text-align:center;color:var(--shrikant-spb-text-muted);padding:20px;">' + shrikant_spb_admin_opts.i18n.no_posts_selected_step2 + '</div>');
            return;
        }

        SPB_App.selectedPosts.forEach((post, index) => {
            const item = $(`
                <div class="shrikant-spb-sortable-item" draggable="true" data-id="${post.id}">
                    <div class="shrikant-spb-sort-content">
                        <span class="shrikant-spb-sort-handle dashicons dashicons-menu"></span>
                        <div>
                            <div class="shrikant-spb-sort-title">${post.title}</div>
                            <div class="shrikant-spb-sort-meta">${post.category} &bull; ${post.date}</div>
                        </div>
                    </div>
                    <button class="shrikant-spb-btn shrikant-spb-btn-danger btn-remove-post" style="padding: 4px 8px; font-size:11px;" data-id="${post.id}"><span class="dashicons dashicons-trash"></span></button>
                </div>
            `);
            container.append(item);
        });

        // Re-bind remove buttons
        $('.btn-remove-post').on('click', function(e) {
            e.stopPropagation();
            const id = parseInt($(this).data('id'), 10);
            SPB_App.selectedPosts = SPB_App.selectedPosts.filter(p => p.id !== id);
            
            // Sync checkbox in step 1
            $(`.spb-select-post-chk[value="${id}"]`).prop('checked', false);

            updateStep2List();
        });

        // Initialize drag & drop sorting events
        initDragAndDrop();
    }

    // Drag & drop logic
    function initDragAndDrop() {
        const list = document.getElementById('spb-sortable-list');
        let dragEl = null;

        // Mouse Drag & Drop
        list.addEventListener('dragstart', function(e) {
            dragEl = e.target.closest('.shrikant-spb-sortable-item');
            if (dragEl) {
                dragEl.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', dragEl.innerHTML);
            }
        });

        list.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const overEl = e.target.closest('.shrikant-spb-sortable-item');
            if (overEl && overEl !== dragEl) {
                const bounding = overEl.getBoundingClientRect();
                const offset = bounding.y + (bounding.height / 2);
                if (e.clientY - offset > 0) {
                    overEl.after(dragEl);
                } else {
                    overEl.before(dragEl);
                }
            }
        });

        list.addEventListener('dragend', function() {
            if (dragEl) {
                dragEl.classList.remove('dragging');
                reconstructSelectedPostsOrder();
            }
        });

        // Touch Drag & Drop (Mobile support)
        let touchStartEl = null;

        list.addEventListener('touchstart', function(e) {
            const item = e.target.closest('.shrikant-spb-sortable-item');
            const handle = e.target.closest('.shrikant-spb-sort-handle');
            if (item && (handle || e.target.classList.contains('shrikant-spb-sortable-item') || e.target.closest('.shrikant-spb-sort-content'))) {
                touchStartEl = item;
                item.classList.add('dragging');
            }
        }, { passive: true });

        list.addEventListener('touchmove', function(e) {
            if (!touchStartEl) return;
            const touch = e.touches[0];
            const targetEl = document.elementFromPoint(touch.clientX, touch.clientY);
            if (!targetEl) return;

            const overEl = targetEl.closest('.shrikant-spb-sortable-item');
            if (overEl && overEl !== touchStartEl && overEl.parentNode === list) {
                const bounding = overEl.getBoundingClientRect();
                const offset = bounding.y + (bounding.height / 2);
                if (touch.clientY - offset > 0) {
                    overEl.after(touchStartEl);
                } else {
                    overEl.before(touchStartEl);
                }
            }
            if (e.cancelable) {
                e.preventDefault();
            }
        }, { passive: false });

        list.addEventListener('touchend', function() {
            if (touchStartEl) {
                touchStartEl.classList.remove('dragging');
                touchStartEl = null;
                reconstructSelectedPostsOrder();
            }
        });

        // Helper to reconstruct selectedPosts order
        function reconstructSelectedPostsOrder() {
            const newOrderIds = [];
            $('#spb-sortable-list .shrikant-spb-sortable-item').each(function() {
                newOrderIds.push(parseInt($(this).data('id'), 10));
            });

            const reorderedPosts = [];
            newOrderIds.forEach(id => {
                const postObj = SPB_App.selectedPosts.find(p => p.id === id);
                if (postObj) {
                    reorderedPosts.push(postObj);
                }
            });
            SPB_App.selectedPosts = reorderedPosts;
        }
    }

    // STEP 5: Generate the Social Post Compilation
    function generateCompilation() {
        const postIds = SPB_App.selectedPosts.map(p => p.id);
        if (postIds.length === 0) return;

        $('#spb-generated-output').val(shrikant_spb_admin_opts.i18n.compiling);

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_generate_message',
                nonce: SPB_App.nonce,
                post_ids: postIds,
                template_id: SPB_App.selectedTemplateId,
                options: {
                    number_posts: $('#chk-number-posts').is(':checked'),
                    show_emojis: $('#chk-show-emojis').is(':checked'),
                    add_footer: $('#chk-add-footer').is(':checked'),
                    add_website: $('#chk-add-website').is(':checked'),
                    add_hashtags: $('#chk-add-hashtags').is(':checked'),
                    remove_duplicates: $('#chk-remove-duplicates').is(':checked')
                }
            },
            success: function(response) {
                if (response.success) {
                    $('#spb-generated-output').val(response.data.message);
                    showToast(shrikant_spb_admin_opts.i18n.generation_success, 'success');

                    // If settings set auto copy, perform copy
                    if (parseInt(shrikant_spb_admin_opts.settings.auto_copy, 10) === 1) {
                        copyCompilationText();
                    }
                } else {
                    $('#spb-generated-output').val(response.data || shrikant_spb_admin_opts.i18n.error_occurred);
                    showToast(shrikant_spb_admin_opts.i18n.error_occurred, 'error');
                }
            },
            error: function() {
                $('#spb-generated-output').val(shrikant_spb_admin_opts.i18n.error_occurred);
                showToast(shrikant_spb_admin_opts.i18n.error_occurred, 'error');
            }
        });
    }

    // Step 5 Copy Action
    function copyCompilationText() {
        const textarea = document.getElementById('spb-generated-output');
        if (!textarea.value || textarea.value === shrikant_spb_admin_opts.i18n.compiling) return;

        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices

        try {
            navigator.clipboard.writeText(textarea.value).then(() => {
                showToast(shrikant_spb_admin_opts.i18n.copied_toast, 'success');
                // Automatically log to history with status "copied" or update log.
                saveMessageToHistory('copied');
            });
        } catch (err) {
            // Fallback copy
            document.execCommand('copy');
            showToast(shrikant_spb_admin_opts.i18n.copied_toast, 'success');
            saveMessageToHistory('copied');
        }
    }

    // Save logs to History table via AJAX
    function saveMessageToHistory(status = 'generated') {
        const text = $('#spb-generated-output').val();
        if (!text || text === shrikant_spb_admin_opts.i18n.compiling) return;

        const postItems = SPB_App.selectedPosts.map(p => {
            return {
                post_id: p.id,
                post_title: p.title,
                post_url: p.url
            };
        });

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_save_history',
                nonce: SPB_App.nonce,
                template: SPB_App.selectedTemplateName || 'Custom',
                generated_text: text,
                status: status,
                posts: postItems
            },
            success: function(response) {
                if (response.success) {
                    if (status === 'draft') {
                        showToast(shrikant_spb_admin_opts.i18n.draft_saved, 'success');
                    } else if (status === 'generated') {
                        showToast(shrikant_spb_admin_opts.i18n.history_saved, 'success');
                    }
                    loadDashboardStats();
                }
            }
        });
    }

    // Reset Wizard parameters
    function resetWizard() {
        SPB_App.selectedPosts = [];
        $('#spb-select-all-posts').prop('checked', false);
        $('.spb-select-post-chk').prop('checked', false);
        updateStep2List();

        $('#spb-generated-output').val('');
        goToStep(1);
    }

    // DASHBOARD: Load stats metrics
    function loadDashboardStats() {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_load_dashboard_stats',
                nonce: SPB_App.nonce
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#stat-today-posts').text(stats.today_posts);
                    $('#stat-today-generated').text(stats.today_generated);
                    $('#stat-total-history').text(stats.total_history);
                    $('#stat-most-used-template').text(stats.most_used_template || 'None');
                    $('#stat-last-time').text(stats.last_time || 'Never');
                }
            }
        });
    }

    // HISTORY: Load History logs list table
    function loadHistoryTable() {
        const body = $('#spb-history-table-body');
        body.html('<tr><td colspan="6" style="text-align:center;"><span class="spinner is-active" style="float:none;margin:10px auto;"></span></td></tr>');

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_fetch_history',
                nonce: SPB_App.nonce,
                page: SPB_App.historyPage,
                limit: SPB_App.historyLimit,
                search: $('#spb-history-search').val(),
                date_filter: $('#spb-history-date-filter').val(),
                start_date: $('#spb-history-start').val(),
                end_date: $('#spb-history-end').val(),
                template: $('#spb-history-template').val()
            },
            success: function(response) {
                if (response.success && response.data.items.length > 0) {
                    let html = '';
                    response.data.items.forEach(item => {
                        const dateFormatted = item.created_at;
                        html += `
                            <tr data-id="${item.id}">
                                <td>${dateFormatted}</td>
                                <td><strong>${item.template}</strong></td>
                                <td>${item.posts_count}</td>
                                <td><span class="shrikant-spb-badge shrikant-spb-badge-${item.status}">${item.status}</span></td>
                                <td>${item.user_name}</td>
                                <td>
                                    <div class="shrikant-spb-actions-group">
                                        <button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-history-view" style="padding:4px 8px; font-size:11px;">View</button>
                                        <button class="shrikant-spb-btn shrikant-spb-btn-primary btn-history-copy" style="padding:4px 8px; font-size:11px;">Copy</button>
                                        <button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-history-duplicate" style="padding:4px 8px; font-size:11px;">Duplicate</button>
                                        <button class="shrikant-spb-btn shrikant-spb-btn-danger btn-history-delete" style="padding:4px 8px; font-size:11px;">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    body.html(html);

                    // Pagination handles
                    $('#history-prev').prop('disabled', SPB_App.historyPage === 1);
                    const maxPage = Math.ceil(response.data.total / SPB_App.historyLimit);
                    $('#history-next').prop('disabled', SPB_App.historyPage >= maxPage || maxPage <= 1);
                    $('#history-page-info').text(`Page ${SPB_App.historyPage} of ${maxPage || 1}`);
                } else {
                    body.html('<tr><td colspan="6" style="text-align:center;">' + shrikant_spb_admin_opts.i18n.no_history + '</td></tr>');
                    $('#history-prev, #history-next').prop('disabled', true);
                    $('#history-page-info').text('Page 1 of 1');
                }
            }
        });
    }

    // View History Log details modal
    function viewHistoryItem(id) {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_get_history_item',
                nonce: SPB_App.nonce,
                history_id: id
            },
            success: function(response) {
                if (response.success) {
                    const item = response.data;
                    $('#modal-template-name').text(item.template);
                    $('#modal-creation-time').text(item.created_at);
                    
                    let postsHtml = '';
                    item.posts.forEach(p => {
                        postsHtml += `<li><a href="${p.post_url}" target="_blank">${p.post_title}</a></li>`;
                    });
                    $('#modal-posts-list').html(postsHtml);
                    $('#modal-text-content').val(item.generated_text);

                    // Attach Copy trigger on modal copy button
                    $('#btn-modal-copy').off('click').on('click', function() {
                        const txt = $('#modal-text-content');
                        txt.select();
                        navigator.clipboard.writeText(txt.val()).then(() => {
                            showToast(shrikant_spb_admin_opts.i18n.copied_toast, 'success');
                            $.ajax({
                                url: SPB_App.ajaxUrl,
                                type: 'POST',
                                data: {
                                    action: 'spb_copy_history',
                                    nonce: SPB_App.nonce,
                                    history_id: id
                                }
                            });
                        });
                    });

                    $('#history-view-modal').addClass('active');
                }
            }
        });
    }

    // Copy from Row directly
    function copyHistoryItemDirectly(id) {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_get_history_item',
                nonce: SPB_App.nonce,
                history_id: id
            },
            success: function(response) {
                if (response.success) {
                    navigator.clipboard.writeText(response.data.generated_text).then(() => {
                        showToast(shrikant_spb_admin_opts.i18n.copied_toast, 'success');
                        
                        // Mark copied on server
                        $.ajax({
                            url: SPB_App.ajaxUrl,
                            type: 'POST',
                            data: {
                                action: 'spb_copy_history',
                                nonce: SPB_App.nonce,
                                history_id: id
                            },
                            success: function() {
                                loadHistoryTable();
                            }
                        });
                    });
                }
            }
        });
    }

    // Delete History row
    function deleteHistoryItem(id) {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_delete_history',
                nonce: SPB_App.nonce,
                history_id: id
            },
            success: function(response) {
                if (response.success) {
                    showToast(shrikant_spb_admin_opts.i18n.deleted_toast, 'success');
                    loadHistoryTable();
                    loadDashboardStats();
                }
            }
        });
    }

    // Duplicate history items (Reload posts into create screen wizard)
    function duplicateHistoryItem(id) {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_duplicate_history',
                nonce: SPB_App.nonce,
                history_id: id
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Clear previous settings
                    SPB_App.selectedPosts = [];

                    // Data.posts contains array of IDs, so query them and populate.
                    // To do it cleanly, let's load all posts matching these IDs.
                    $.ajax({
                        url: SPB_App.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'spb_fetch_posts_by_ids',
                            nonce: SPB_App.nonce,
                            post_ids: data.posts
                        },
                        success: function(res) {
                            if (res.success) {
                                SPB_App.selectedPosts = res.data;
                                updateStep2List();
                                
                                // Select template
                                const templateCard = $(`.shrikant-spb-template-card[data-name="${data.template}"]`);
                                if (templateCard.length) {
                                    templateCard.trigger('click');
                                } else {
                                    // Click first as fallback
                                    $('.shrikant-spb-template-card').first().trigger('click');
                                }

                                // Open wizard tab
                                switchTab('#create-post');
                                // Move to step 2 to arrange order or step 5 directly
                                goToStep(2);
                                showToast(shrikant_spb_admin_opts.i18n.duplicated_toast, 'success');
                            }
                        }
                    });
                }
            }
        });
    }

    // TEMPLATES: Load CRUD table list
    function loadTemplatesList() {
        const body = $('#spb-templates-table-body');
        body.html('<tr><td colspan="4" style="text-align:center;"><span class="spinner is-active" style="float:none;margin:10px auto;"></span></td></tr>');

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_fetch_templates',
                nonce: SPB_App.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    let cardsHtml = '';
                    const defaultTemplateName = shrikant_spb_admin_opts.settings.default_template;

                    response.data.forEach(tpl => {
                        const isDefault = tpl.name === defaultTemplateName;
                        const isCustom = !['WhatsApp', 'Telegram', 'Facebook', 'LinkedIn', 'X (Twitter)'].includes(tpl.name);
                        
                        html += `
                            <tr>
                                <td><strong>${tpl.name}</strong> ${isDefault ? '<span class="shrikant-spb-badge shrikant-spb-badge-copied" style="font-size:10px;padding:2px 6px;">Default</span>' : ''}</td>
                                <td><pre style="margin:0;font-size:11px;max-height:80px;overflow:auto;background:#fafafa;padding:6px;border:1px solid #eee;">${escapeHtml(tpl.content)}</pre></td>
                                <td><span class="shrikant-spb-badge shrikant-spb-badge-${tpl.active ? 'copied' : 'draft'}">${tpl.active ? 'Active' : 'Inactive'}</span></td>
                                <td>
                                    <div class="shrikant-spb-actions-group">
                                        <button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-edit-template" style="padding:4px 8px; font-size:11px;" data-id="${tpl.id}">Edit</button>
                                        ${isCustom ? `<button class="shrikant-spb-btn shrikant-spb-btn-danger btn-delete-template" style="padding:4px 8px; font-size:11px;" data-id="${tpl.id}">Delete</button>` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;

                        // Rebuild step 3 cards grid
                        let iconHtml = '';
                        if (tpl.name.includes('WhatsApp')) {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#25D366" style="display:block;margin:0 auto;"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.413 9.863-9.864.001-2.63-1.023-5.101-2.885-6.963C16.48 1.958 14.015.931 11.39.931c-5.442 0-9.866 4.415-9.869 9.866-.001 1.839.486 3.635 1.412 5.239L1.87 21.13l5.223-1.37zM17.43 14.54c-.313-.156-1.854-.915-2.145-1.02-.29-.106-.503-.156-.713.156-.21.314-.813 1.02-.996 1.23-.183.21-.366.236-.679.08-1.745-.87-2.923-1.597-3.924-3.315-.262-.449.262-.418.75-1.393.082-.166.041-.313-.02-.47-.063-.156-.713-1.72-.977-2.35-.257-.618-.518-.534-.713-.544-.185-.01-.397-.01-.609-.01-.21 0-.555.08-.846.398-.29.313-1.11 1.084-1.11 2.644 0 1.56 1.135 3.07 1.293 3.28.156.21 2.233 3.411 5.41 4.78.756.326 1.345.52 1.803.666.76.242 1.452.208 2.001.127.61-.09 1.854-.758 2.116-1.452.261-.694.261-1.288.183-1.414-.078-.126-.29-.21-.603-.366z"/></svg>';
                        } else if (tpl.name.includes('Telegram')) {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#0088cc" style="display:block;margin:0 auto;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.11.02-1.93 1.23-5.46 3.62-.51.35-.98.53-1.39.51-.46-.01-1.35-.26-2.01-.48-.81-.27-1.46-.42-1.4-.88.03-.24.36-.49.99-.75 3.88-1.69 6.46-2.8 7.74-3.32 3.7-1.5 4.46-1.76 4.96-1.77.11 0 .36.03.52.16.13.1.17.24.19.34.02.11.02.26.01.39z"/></svg>';
                        } else if (tpl.name.includes('Facebook')) {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#1877F2" style="display:block;margin:0 auto;"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
                        } else if (tpl.name.includes('LinkedIn')) {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#0A66C2" style="display:block;margin:0 auto;"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
                        } else if (tpl.name.includes('X') || tpl.name.includes('Twitter')) {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#000000" style="display:block;margin:0 auto;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
                        } else {
                            iconHtml = '<svg viewBox="0 0 24 24" width="32" height="32" fill="#6B7280" style="display:block;margin:0 auto;"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>';
                        }

                        const isSelected = SPB_App.selectedTemplateId == tpl.id || (!SPB_App.selectedTemplateId && isDefault);
                        if (isSelected) {
                            SPB_App.selectedTemplateId = tpl.id;
                            SPB_App.selectedTemplateContent = tpl.content;
                            SPB_App.selectedTemplateName = tpl.name;
                        }

                        cardsHtml += `
                            <div class="shrikant-spb-template-card ${isSelected ? 'selected' : ''}" data-id="${tpl.id}" data-content="${escapeAttr(tpl.content)}" data-name="${tpl.name}">
                                <div class="shrikant-spb-template-icon">${iconHtml}</div>
                                <div class="shrikant-spb-template-name">${tpl.name}</div>
                            </div>
                        `;
                    });
                    body.html(html);
                    $('#spb-templates-step3-grid').html(cardsHtml);
                }
            }
        });
    }

    // Modal Editor for Custom Templates
    function openTemplateModal(id = 0) {
        $('#template-editor-id').val(id);
        if (id > 0) {
            // Fetch template
            $('#modal-template-editor-title').text(shrikant_spb_admin_opts.i18n.edit_template_title);
            $.ajax({
                url: SPB_App.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'spb_get_template',
                    nonce: SPB_App.nonce,
                    template_id: id
                },
                success: function(response) {
                    if (response.success) {
                        $('#template-editor-name').val(response.data.name);
                        $('#template-editor-content').val(response.data.content);
                        
                        // Disable name change for system templates
                        const systemTpls = ['WhatsApp', 'Telegram', 'Facebook', 'LinkedIn', 'X (Twitter)'];
                        if (systemTpls.includes(response.data.name)) {
                            $('#template-editor-name').attr('disabled', true);
                        } else {
                            $('#template-editor-name').removeAttr('disabled');
                        }

                        $('#template-editor-modal').addClass('active');
                    }
                }
            });
        } else {
            $('#modal-template-editor-title').text(shrikant_spb_admin_opts.i18n.add_template_title);
            $('#template-editor-name').val('').removeAttr('disabled');
            $('#template-editor-content').val('');
            $('#template-editor-modal').addClass('active');
        }
    }

    // Save custom template via AJAX
    function saveCustomTemplate() {
        const id = $('#template-editor-id').val();
        const name = $('#template-editor-name').val();
        const content = $('#template-editor-content').val();

        if (!name || !content) {
            alert(shrikant_spb_admin_opts.i18n.template_req_err);
            return;
        }

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_save_template',
                nonce: SPB_App.nonce,
                template_id: id,
                name: name,
                content: content
            },
            success: function(response) {
                if (response.success) {
                    showToast(shrikant_spb_admin_opts.i18n.template_saved, 'success');
                    $('#template-editor-modal').removeClass('active');
                    loadTemplatesList();
                } else {
                    alert(response.data || shrikant_spb_admin_opts.i18n.error_occurred);
                }
            }
        });
    }

    // Delete custom templates
    function deleteTemplate(id) {
        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_delete_template',
                nonce: SPB_App.nonce,
                template_id: id
            },
            success: function(response) {
                if (response.success) {
                    showToast(shrikant_spb_admin_opts.i18n.deleted_toast, 'success');
                    loadTemplatesList();
                }
            }
        });
    }

    // SETTINGS: Save settings options form
    function saveSettings() {
        const settings = {
            website_url: $('#settings-website-url').val(),
            footer_text: $('#settings-footer-text').val(),
            default_template: $('#settings-default-template').val(),
            default_emoji: $('#settings-default-emoji').val(),
            default_hashtags: $('#settings-default-hashtags').val(),
            auto_delete: $('#settings-auto-delete').val(),
            max_records: $('#settings-max-records').val(),
            auto_copy: $('#settings-auto-copy').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: SPB_App.ajaxUrl,
            type: 'POST',
            data: {
                action: 'spb_save_settings',
                nonce: SPB_App.nonce,
                settings: settings
            },
            success: function(response) {
                if (response.success) {
                    showToast(shrikant_spb_admin_opts.i18n.settings_saved, 'success');
                    // Sync values to client memory
                    shrikant_spb_admin_opts.settings = settings;
                } else {
                    showToast(shrikant_spb_admin_opts.i18n.error_occurred, 'error');
                }
            }
        });
    }

    // Utilities helpers
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeAttr(text) {
        return text.replace(/"/g, '&quot;');
    }

    // Trigger run.
    init();
});
