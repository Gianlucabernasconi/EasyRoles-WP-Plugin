/**
 * Easy Roles – Admin JavaScript
 *
 * Vanilla JS — no external dependencies.
 * Only loaded on the plugin's admin page.
 *
 * @package EasyRoles
 */

(function () {
    'use strict';

    /* ─── Globals ───────────────────────────────────────────────── */
    var data = window.easyRolesData || {};
    var ajaxUrl = data.ajaxUrl || '';
    var nonce = data.nonce || '';
    var strings = data.strings || {};

    /* ─── DOM ready ─────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initTabs();
        initGroupToggles();
        initSearch();
        initBulkActions();
        initCardActions();
        initFormCreate();
        initFormEdit();
        initBackButton();
        initDashboardFilters();
        initOnboarding();
        initToolbarActions();
        initModals();
        updateAllGroupCounts();
        initUsersPanel();
    }

    /* ================================================================
       TABS
    ================================================================ */
    function initTabs() {
        var tabs = document.querySelectorAll('.easy-roles-tab');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                switchTab(tab.id);
            });
        });

        /* Header Create Button */
        var headerBtn = document.getElementById('er-header-create');
        if (headerBtn) {
            headerBtn.addEventListener('click', function () {
                var createTab = document.getElementById('er-tab-create');
                if (createTab) {
                    /* Trigger click on the tab to use existing switch logic */
                    createTab.click();
                }
            });
        }

        /* Ghost Card Create Button (Grid) */
        var ghostCard = document.getElementById('er-card-create');
        if (ghostCard) {
            ghostCard.addEventListener('click', function () {
                var createTab = document.getElementById('er-tab-create');
                if (createTab) {
                    createTab.click();
                }
            });
            // Accessibility: Enter key support
            ghostCard.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var createTab = document.getElementById('er-tab-create');
                    if (createTab) createTab.click();
                }
            });
        }
    }

    function switchTab(tabId) {
        /* Deactivate all */
        document.querySelectorAll('.easy-roles-tab').forEach(function (t) {
            t.classList.remove('easy-roles-tab--active');
            t.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.easy-roles-panel').forEach(function (p) {
            p.classList.remove('easy-roles-panel--active');
        });

        /* Activate target */
        var tab = document.getElementById(tabId);
        var panel = document.getElementById(tab.getAttribute('aria-controls'));

        tab.classList.remove('easy-roles-tab--hidden');
        tab.style.display = ''; /* Limpiar inline display:none (ej. tab de edición) */
        tab.classList.add('easy-roles-tab--active');
        tab.setAttribute('aria-selected', 'true');
        panel.classList.add('easy-roles-panel--active');

        /* Load Guide Content if needed */
        if (tabId === 'er-tab-guide') {
            loadGuideContent();
        }

        /* Manage Toolbar visibility */
        var toolbar = document.querySelector('.easy-roles-toolbar');
        if (toolbar) {
            if (tabId === 'er-tab-roles') {
                toolbar.style.display = 'flex';
                /* Re-apply filters just in case */
                if (typeof applyDashboardFilters === 'function') applyDashboardFilters();
            } else {
                toolbar.style.display = 'none';
            }
        }
    }

    /* ================================================================
       GROUP TOGGLES (Collapse/Expand)
    ================================================================ */
    function initGroupToggles() {
        document.querySelectorAll('.easy-roles-group__toggle').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var group = btn.closest('.easy-roles-group');
                var expanded = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', !expanded);
                group.classList.toggle('easy-roles-group--collapsed');
            });
        });
    }

    /* ================================================================
       SEARCH / FILTER (with debounce)
    ================================================================ */
    function initSearch() {
        document.querySelectorAll('.easy-roles-search__input').forEach(function (input) {
            var debounceTimer;
            input.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    filterCapabilities(input);
                }, 200);
            });
        });
    }

    function filterCapabilities(input) {
        var query = input.value.toLowerCase().trim();
        var panel = input.closest('.easy-roles-panel');
        var container = panel.querySelector('.easy-roles-cap-groups');
        var groups = container.querySelectorAll('.easy-roles-group');
        var noResults = container.querySelector('.easy-roles-no-results');
        var anyVisible = false;

        groups.forEach(function (group) {
            var caps = group.querySelectorAll('.easy-roles-cap');
            var groupVisible = false;

            caps.forEach(function (cap) {
                var capName = (cap.getAttribute('data-cap') || '').toLowerCase();
                var capDesc = (cap.querySelector('.easy-roles-cap__desc') || {}).textContent || '';
                capDesc = capDesc.toLowerCase();

                if (!query || capName.indexOf(query) !== -1 || capDesc.indexOf(query) !== -1) {
                    cap.classList.remove('easy-roles-cap--hidden');
                    groupVisible = true;
                } else {
                    cap.classList.add('easy-roles-cap--hidden');
                }
            });

            group.classList.toggle('easy-roles-group--hidden', !groupVisible);
            if (groupVisible) anyVisible = true;
        });

        if (noResults) {
            noResults.style.display = anyVisible ? 'none' : 'block';
        }
    }

    /* ================================================================
       BULK ACTIONS (Select/Deselect All + Group)
    ================================================================ */
    function initBulkActions() {
        /* Global select/deselect */
        document.querySelectorAll('.easy-roles-btn--select-all').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var formId = btn.getAttribute('data-form');
                toggleAllCaps(formId, true);
            });
        });

        document.querySelectorAll('.easy-roles-btn--deselect-all').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var formId = btn.getAttribute('data-form');
                toggleAllCaps(formId, false);
            });
        });

        /* Group select/deselect */
        document.querySelectorAll('.easy-roles-btn--group-select').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var group = btn.closest('.easy-roles-group');
                toggleGroupCaps(group, true);
            });
        });

        document.querySelectorAll('.easy-roles-btn--group-deselect').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var group = btn.closest('.easy-roles-group');
                toggleGroupCaps(group, false);
            });
        });

        /* Update counts on checkbox change */
        document.querySelectorAll('.easy-roles-cap__check').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var group = cb.closest('.easy-roles-group');
                updateGroupCount(group);
            });
        });
    }

    function toggleAllCaps(formId, checked) {
        var form = document.getElementById(formId);
        if (!form) return;
        form.querySelectorAll('.easy-roles-cap__check').forEach(function (cb) {
            if (!cb.closest('.easy-roles-cap--hidden')) {
                cb.checked = checked;
            }
        });
        form.querySelectorAll('.easy-roles-group').forEach(updateGroupCount);
    }

    function toggleGroupCaps(group, checked) {
        group.querySelectorAll('.easy-roles-cap__check').forEach(function (cb) {
            if (!cb.closest('.easy-roles-cap--hidden')) {
                cb.checked = checked;
            }
        });
        updateGroupCount(group);
    }

    function updateGroupCount(group) {
        var checked = group.querySelectorAll('.easy-roles-cap__check:checked').length;
        var numEl = group.querySelector('.easy-roles-group__checked-num');
        if (numEl) {
            numEl.textContent = checked;
        }
    }

    function updateAllGroupCounts() {
        document.querySelectorAll('.easy-roles-group').forEach(updateGroupCount);
    }

    /* ================================================================
       CARD ACTIONS (Edit, Clone, Delete)
    ================================================================ */
    function initCardActions() {
        document.querySelectorAll('.easy-roles-btn--edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                loadRoleForEdit(btn.getAttribute('data-role'));
            });
        });

        document.querySelectorAll('.easy-roles-btn--clone').forEach(function (btn) {
            btn.addEventListener('click', function () {
                showCloneModal(btn.getAttribute('data-role'));
            });
        });

        document.querySelectorAll('.easy-roles-btn--delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                deleteRole(btn.getAttribute('data-role'));
            });
        });
    }

    /* ================================================================
       LOAD ROLE FOR EDITING
    ================================================================ */
    function loadRoleForEdit(slug) {
        var form = document.getElementById('er-form-edit');
        form.classList.add('easy-roles-loading');

        ajaxPost('easy_roles_get_role_caps', { role_slug: slug }, function (res) {
            form.classList.remove('easy-roles-loading');

            if (!res.success) {
                showToast(res.data.message || strings.errorOccurred, 'error');
                return;
            }

            var d = res.data;

            /* Populate header */
            document.getElementById('er-edit-slug').value = d.slug;
            document.getElementById('er-edit-name').value = d.name;
            document.getElementById('er-edit-title').textContent = d.name;

            /* Badge */
            var badgeEl = document.getElementById('er-edit-badge');
            if (d.is_protected) {
                badgeEl.innerHTML = '<span class="easy-roles-badge easy-roles-badge--prot"><span class="dashicons dashicons-lock"></span> ' + strings.protected + '</span>';
            } else {
                badgeEl.innerHTML = '<span class="easy-roles-badge easy-roles-badge--custom">' + strings.custom + '</span>';
            }

            /* User count */
            document.getElementById('er-edit-user-count').textContent = d.user_count + ' ' + strings.users;

            /* Uncheck all, then check matching */
            var editPanel = document.getElementById('er-panel-edit');
            editPanel.querySelectorAll('.easy-roles-cap__check').forEach(function (cb) {
                cb.checked = false;
            });

            var caps = d.capabilities || {};
            Object.keys(caps).forEach(function (cap) {
                if (caps[cap]) {
                    var cb = editPanel.querySelector('.easy-roles-cap[data-cap="' + cap + '"] .easy-roles-cap__check');
                    if (cb) cb.checked = true;
                }
            });

            /* Update group counts */
            editPanel.querySelectorAll('.easy-roles-group').forEach(updateGroupCount);

            /* Switch to edit tab */
            switchTab('er-tab-edit');
        });
    }

    /* ================================================================
       CREATE ROLE
    ================================================================ */
    function initFormCreate() {
        var form = document.getElementById('er-form-create');
        if (!form) return;

        /* Auto-generate slug from name */
        var nameInput = document.getElementById('er-create-name');
        var slugInput = document.getElementById('er-create-slug');

        nameInput.addEventListener('input', function () {
            var slug = nameInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s_]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 60);
            slugInput.value = slug;
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var name = slugInput.closest('form').querySelector('#er-create-name').value.trim();
            var slug = slugInput.value.trim();

            if (!name) { showToast(strings.nameRequired, 'error'); return; }
            if (!slug) { showToast(strings.slugRequired, 'error'); return; }

            var caps = {};
            form.querySelectorAll('.easy-roles-cap__check:checked').forEach(function (cb) {
                var capName = cb.closest('.easy-roles-cap').getAttribute('data-cap');
                caps[capName] = 1;
            });

            /* Validate visibility requirements */
            checkVisibilityAndSubmit(caps, function () {
                form.classList.add('easy-roles-loading');

                ajaxPost('easy_roles_create_role', {
                    role_slug: slug,
                    role_name: name,
                    capabilities: caps
                }, function (res) {
                    form.classList.remove('easy-roles-loading');

                    if (res.success) {
                        showToast(strings.roleCreated, 'success');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        showToast(res.data.message || strings.errorOccurred, 'error');
                    }
                });
            });
        });
    }

    /* ================================================================
       UPDATE ROLE
    ================================================================ */
    function initFormEdit() {
        var form = document.getElementById('er-form-edit');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            showConfirm(strings.save_changes, strings.confirmUpdate || 'Are you sure you want to update this role?', function () {
                submitEditForm(form);
            });
        });

        function submitEditForm(form) {
            var slug = document.getElementById('er-edit-slug').value;
            var name = document.getElementById('er-edit-name').value.trim();

            var caps = {};
            form.querySelectorAll('.easy-roles-cap__check:checked').forEach(function (cb) {
                var capName = cb.closest('.easy-roles-cap').getAttribute('data-cap');
                caps[capName] = 1;
            });

            /* Validate visibility requirements */
            checkVisibilityAndSubmit(caps, function () {
                var btn = form.querySelector('.easy-roles-btn--submit');
                if (btn) btn.disabled = true;

                ajaxPost('easy_roles_update_role', {
                    role_slug: slug,
                    role_name: name,
                    capabilities: caps
                }, function (res) {
                    if (btn) btn.disabled = false;

                    if (res.success) {
                        showToast(strings.roleUpdated, 'success');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        showToast(res.data.message || strings.errorOccurred, 'error');
                    }
                });
            });
        }
    }

    /* ================================================================
       DELETE ROLE
    ================================================================ */
    function deleteRole(slug) {
        var card = document.querySelector('.easy-roles-card[data-role="' + slug + '"]');
        var roleName = card ? card.querySelector('.easy-roles-card__name').textContent.trim() : slug;
        var title = (strings.delete_btn || 'Delete') + ': ' + roleName;

        showConfirm(title, strings.confirmDelete || 'Are you sure you want to delete this role?', function () {
            performDeleteRole(slug);
        }, true);
    }

    function performDeleteRole(slug) {
        var card = document.querySelector('.easy-roles-card[data-role="' + slug + '"]');
        if (card) card.classList.add('easy-roles-loading');

        ajaxPost('easy_roles_delete_role', { role_slug: slug }, function (res) {
            if (card) card.classList.remove('easy-roles-loading');

            if (res.success) {
                showToast(strings.roleDeleted || 'Role deleted', 'success');
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(function () { card.remove(); }, 300);
                }
            } else {
                showToast(res.data.message || strings.errorOccurred, 'error');
            }
        });
    }

    /* ================================================================
       CLONE ROLE (Modal)
    ================================================================ */
    function showCloneModal(sourceSlug) {
        var card = document.querySelector('.easy-roles-card[data-role="' + sourceSlug + '"]');
        var sourceName = card ? card.querySelector('.easy-roles-card__name').textContent.trim() : sourceSlug;

        var html =
            '<label for="er-clone-name">' + (strings.role_name || 'Role Name') + '</label>' +
            '<input type="text" id="er-clone-name" class="regular-text" maxlength="100" autofocus>' +
            '<label for="er-clone-slug">' + (strings.role_slug || 'Role Slug') + '</label>' +
            '<input type="text" id="er-clone-slug" class="regular-text" maxlength="60" pattern="[a-z0-9_]+">';

        openModal({
            title: (strings.clone_btn || 'Clone') + ': ' + sourceName,
            html: html,
            icon: 'dashicons-admin-page',
            onConfirm: function () {
                var name = document.getElementById('er-clone-name').value.trim();
                var slug = document.getElementById('er-clone-slug').value.trim();

                if (!name || !slug) {
                    showToast(strings.errorOccurred || 'Please fill required fields', 'error');
                    return;
                }

                performClone(sourceSlug, name, slug);
            }
        });

        /* Auto slug from name */
        setTimeout(function () {
            var nameIn = document.getElementById('er-clone-name');
            var slugIn = document.getElementById('er-clone-slug');
            if (nameIn && slugIn) {
                nameIn.focus();
                nameIn.addEventListener('input', function () {
                    slugIn.value = nameIn.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s_]/g, '')
                        .replace(/\s+/g, '_')
                        .substring(0, 60);
                });
            }
        }, 100);
    }

    function performClone(sourceSlug, name, slug) {
        ajaxPost('easy_roles_clone_role', {
            source_slug: sourceSlug,
            new_name: name,
            new_slug: slug
        }, function (res) {
            if (res.success) {
                showToast(strings.roleCloned || 'Role cloned', 'success');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                showToast(res.data.message || strings.errorOccurred, 'error');
            }
        });
    }

    /* ================================================================
       BACK BUTTON
    ================================================================ */
    function initBackButton() {
        document.querySelectorAll('.easy-roles-btn--back').forEach(function (btn) {
            btn.addEventListener('click', function () {
                switchTab('er-tab-roles');
                /* Hide edit tab again if it was open */
                var editTab = document.getElementById('er-tab-edit');
                if (editTab) {
                    editTab.classList.add('easy-roles-tab--hidden');
                }
            });
        });
    }

    /* ================================================================
       AJAX HELPER
    ================================================================ */
    function ajaxPost(action, params, callback) {
        var formData = new FormData();
        formData.append('action', action);
        formData.append('_nonce', nonce);

        /* Flatten params — handle nested objects (capabilities) */
        Object.keys(params).forEach(function (key) {
            var val = params[key];
            if (typeof val === 'object' && val !== null) {
                Object.keys(val).forEach(function (subKey) {
                    formData.append(key + '[' + subKey + ']', val[subKey]);
                });
            } else {
                formData.append(key, val);
            }
        });

        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;

            var res;
            try {
                res = JSON.parse(xhr.responseText);
            } catch (e) {
                res = { success: false, data: { message: strings.errorOccurred } };
            }
            callback(res);
        };
        xhr.send(formData);
    }

    /* ================================================================
       TOAST NOTIFICATIONS
    ================================================================ */
    function showToast(message, type) {
        var container = document.getElementById('er-toast');
        if (!container) return;

        var icon = type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning';
        var toast = document.createElement('div');
        toast.className = 'easy-roles-toast__item easy-roles-toast__item--' + type;
        toast.innerHTML = '<span class="dashicons ' + icon + '"></span> ' + escapeHtml(message);

        container.appendChild(toast);

        /* Remove after animation */
        setTimeout(function () {
            if (toast.parentNode) toast.remove();
        }, 3000);
    }

    /* ================================================================
       UTILITY
    ================================================================ */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    /* ================================================================
       DASHBOARD FILTERS (Search + Toggles)
    ================================================================ */
    function initDashboardFilters() {
        var searchInput = document.getElementById('er-role-search');
        var filterBtns = document.querySelectorAll('.easy-roles-filter-btn');

        if (!searchInput && filterBtns.length === 0) return;

        /* Text Search */
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyDashboardFilters();
            });
        }

        /* Category Filter */
        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                /* Toggle active class */
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                applyDashboardFilters();
            });
        });
    }

    function applyDashboardFilters() {
        var searchInput = document.getElementById('er-role-search');
        var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        var activeBtn = document.querySelector('.easy-roles-filter-btn.active');
        var catFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';

        var cards = document.querySelectorAll('.easy-roles-card');

        cards.forEach(function (card) {
            var roleName = (card.querySelector('.easy-roles-card__name') || {}).textContent || '';
            var roleSlug = card.getAttribute('data-role') || '';
            var roleType = card.getAttribute('data-type') || 'custom';

            /* Text Match */
            var matchText = !query || roleName.toLowerCase().indexOf(query) !== -1 || roleSlug.toLowerCase().indexOf(query) !== -1;

            /* Category Match */
            var matchCat = (catFilter === 'all') || (roleType === catFilter);

            /* Special case: 'native' includes native WP + WC protected */
            if (catFilter === 'native' && roleType === 'protected') matchCat = true;

            if (matchText && matchCat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    /* ================================================================
       ONBOARDING DISMISS
    ================================================================ */
    function initOnboarding() {
        var container = document.getElementById('er-onboarding');
        var closeBtn = document.querySelector('.easy-roles-close-onboarding');

        if (!container || !closeBtn) return;

        /* Check local storage */
        if (localStorage.getItem('er_onboarding_dismissed') === 'true') {
            container.style.display = 'none';
        }

        closeBtn.addEventListener('click', function () {
            container.style.transition = 'opacity 0.3s, margin 0.3s';
            container.style.opacity = '0';
            container.style.marginBottom = '0';
            setTimeout(function () { container.style.display = 'none'; }, 300);

            localStorage.setItem('er_onboarding_dismissed', 'true');
        });
    }

    /* ================================================================
       TOOLBAR ACTIONS
    ================================================================ */
    function initToolbarActions() {
        var createBtn = document.getElementById('er-create-role-trigger');
        if (createBtn) {
            createBtn.addEventListener('click', function () {
                switchTab('er-tab-create');
            });
        }
    }

    /* ================================================================
       MODAL SYSTEM
    ================================================================ */
    var modalOverlay, modalTitle, modalMsg, modalIcon, btnCancel, btnConfirm;
    var onConfirmCallback = null;

    function initModals() {
        modalOverlay = document.getElementById('er-modal-overlay');
        if (!modalOverlay) return;

        modalTitle = document.getElementById('er-modal-title');
        modalMsg = document.getElementById('er-modal-message');
        modalIcon = document.getElementById('er-modal-icon');
        btnCancel = document.getElementById('er-modal-cancel');
        btnConfirm = document.getElementById('er-modal-confirm');

        /* Close on X or Cancel */
        document.querySelectorAll('.easy-roles-modal__close, #er-modal-cancel').forEach(function (btn) {
            btn.addEventListener('click', closeModal);
        });

        /* Close on outside click */
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) closeModal();
        });

        /* Confirm Action */
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function () {
                if (typeof onConfirmCallback === 'function') {
                    onConfirmCallback();
                }
                /* If callback returned false, don't close? usually no return value. */
                // closeModal(); // Moved closeModal to be called explicitly or handled? 
                // Wait, if it's async? 
                // Standard confirm dialog closes immediately.
                // But specifically for form submit or delete, we might want to keep it open?
                // The current deleteRole logic does not wait.
                // But wait, `performDeleteRole` is async.

                // Let's close modal immediately to mimic 'confirm' feel, unless callback handles it.
                // But for delete role, we might want to show loading?
                // Modals usually close.
                closeModal();
            });
        }
    }

    function openModal(opts) {
        if (!modalOverlay) return;

        modalTitle.textContent = opts.title || '';
        if (opts.html) {
            document.querySelector('.easy-roles-modal__body').innerHTML = opts.html;
        } else {
            document.querySelector('.easy-roles-modal__body').innerHTML = '<p id="er-modal-message"></p>';
            document.getElementById('er-modal-message').innerHTML = opts.message || '';
        }

        modalIcon.className = 'dashicons ' + (opts.icon || 'dashicons-info');

        if (btnConfirm) {
            btnConfirm.textContent = opts.confirmText || strings.confirm_btn || 'Confirm';
            // Reset class then add specific
            btnConfirm.className = 'easy-roles-btn ' + (opts.confirmClass || 'easy-roles-btn--primary');
            btnConfirm.style.display = opts.hideConfirm ? 'none' : 'inline-flex';
        }
        if (btnCancel) {
            btnCancel.textContent = opts.cancelText || strings.cancel_btn || 'Cancel';
            btnCancel.style.display = opts.hideCancel ? 'none' : 'inline-flex';
        }

        onConfirmCallback = opts.onConfirm;

        modalOverlay.style.display = 'flex';
        modalOverlay.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        if (!modalOverlay) return;
        modalOverlay.style.display = 'none';
        modalOverlay.setAttribute('aria-hidden', 'true');
        onConfirmCallback = null;
    }

    function showConfirm(title, message, callback, destructive) {
        openModal({
            title: title,
            message: message,
            icon: 'dashicons-warning',
            confirmClass: destructive ? 'easy-roles-btn--delete' : 'easy-roles-btn--primary',
            onConfirm: callback
        });
    }

    function showAlert(title, message) {
        openModal({
            title: title,
            message: message,
            icon: 'dashicons-info',
            hideCancel: true,
            confirmText: 'OK'
        });
    }

    /**
     * Check if 'read' and 'edit_posts' are present.
     * If not, show warning modal.
     */
    function checkVisibilityAndSubmit(caps, submitCallback) {
        var hasRead = caps['read'] || false;
        var hasEdit = caps['edit_posts'] || false;

        if (hasRead && hasEdit) {
            submitCallback();
            return;
        }

        var mainMsg = '';
        if (!hasRead && !hasEdit) {
            mainMsg = strings.visibility_warn_both;
        } else if (!hasRead) {
            mainMsg = strings.visibility_warn_read;
        } else {
            mainMsg = strings.visibility_warn_edit;
        }

        var fullHtml = `
            <div style="text-align:center;">
                <p style="font-size:1.1rem; margin-bottom:1rem;">${mainMsg}</p>
                <div style="background:#fff5f5; border:1px solid #f8d7da; padding:1rem; border-radius:4px; color:#721c24;">
                    ${strings.visibility_warn_desc}
                </div>
            </div>
        `;

        showConfirm(
            strings.visibility_warn_title,
            fullHtml,
            submitCallback,
            false // not destructive style, just warning
        );
    }

    /* ================================================================
       GUIDE CONTENT LOADER (Didactic)
    ================================================================ */
    function loadGuideContent() {
        var container = document.getElementById('er-guide-content');
        if (!container || container.innerHTML.trim().length > 50) return; // Already loaded

        var isEs = strings.is_es;

        var t = {
            title: isEs ? '¿Confundido con los Permisos?' : 'Confused by Permissions?',
            intro: isEs ? 'WordPress tiene cientos de "capacidades", pero solo necesitas entender <strong>3 conceptos clave</strong>. Imagina que tu sitio web es un edificio de oficinas con muchas habitaciones.'
                : 'WordPress has hundreds of "capabilities", but you only need to understand <strong>3 key concepts</strong>. Imagine your website is an office building with many rooms.',
            key_label: isEs ? 'LLAVE' : 'KEY',
            key_title: isEs ? 'La Puerta Principal' : 'The Main Entrance',
            key_desc: isEs ? 'Son los permisos esenciales. Sin ellos, el usuario <strong>no puede ni entrar</strong> al edificio.' : 'Essential permissions. Without them, the user <strong>cannot even enter</strong> the building.',
            read_desc: isEs ? 'Permite hacer login. <br><em style="color:#d63638;">Solo con esto verán un perfil vacío.</em>' : 'Allows login. <br><em style="color:#d63638;">With only this, they will see an empty profile.</em>',
            edit_posts_desc: isEs ? '<strong style="color:#d63638;">VITAL:</strong> Sin esto, WordPress suele ocultar todo el menú lateral.' : '<strong style="color:#d63638;">VITAL:</strong> Without this, WordPress usually hides the entire sidebar menu.',
            wc_desc: isEs ? 'La llave maestra de la Tienda. Sin ella, no hay ventas ni productos.' : 'The store master key. Without it, there are no sales or products.',
            anchor_label: isEs ? 'ANCLA' : 'ANCHOR',
            anchor_title: isEs ? 'Las Habitaciones' : 'The Rooms',
            anchor_desc: isEs ? 'Imagina que cada menú (Medios, Plugins) es una habitación dentro del edificio.' : 'Imagine that each menu (Media, Plugins) is a room inside the building.',
            media_desc: isEs ? 'Abre la "Biblioteca de Medios".' : 'Opens the "Media Library".',
            plugins_room: isEs ? 'Abre la sala de máquinas "Plugins".' : 'Opens the "Plugins" engine room.',
            pages_room: isEs ? 'Abre la sección de "Páginas".' : 'Opens the "Pages" section.',
            action_label: isEs ? 'ACCIÓN' : 'ACTION',
            action_title: isEs ? 'Tareas Específicas' : 'Specific Tasks',
            action_desc: isEs ? 'Una vez dentro de una habitación, ¿qué puedes hacer? ¿Solo mirar o también romper cosas?' : 'Once inside a room, what can you do? Just look around or also break things?',
            delete_others: isEs ? '¿Borrar trabajo de otros? (Peligroso).' : "Delete others' work? (Dangerous).",
            publish_posts: isEs ? '¿Publicar en la web o solo guardar borradores?' : 'Publish on the web or just save drafts?',
            install_plugins: isEs ? '¿Instalar software nuevo?' : 'Install new software?',
            recipes_title: isEs ? 'Ejemplos: ¿Qué necesito para...?' : 'Examples: What do I need for...?',
            blog_editor: isEs ? 'Un Editor de Blog' : 'A Blog Editor',
            blog_editor_desc: isEs ? 'Alguien que escriba y publique, pero no toque la web.' : 'Someone who writes and publishes, but doesn\'t touch the site setup.',
            needs: isEs ? 'Necesita' : 'Needs',
            optional: isEs ? 'Opcional' : 'Optional',
            trust: isEs ? '(si confías en él)' : '(if you trust them)',
            shop_manager: isEs ? 'Un Gestor de Tienda' : 'A Store Manager',
            shop_manager_desc: isEs ? 'Alguien que gestione pedidos y productos.' : 'Someone who manages orders and products.',
            careful: isEs ? 'Cuidado' : 'Careful',
            avoid_tax: isEs ? 'Evita <code>manage_woocommerce_settings</code> si no quieres que cambie los impuestos.' : 'Avoid <code>manage_woocommerce_settings</code> if you don\'t want them changing tax settings.',
            gold_rule: isEs ? 'Cuidado! 🚨' : 'Careful! 🚨',
            gold_rule_text: isEs ? 'Nunca des a nadie <code>activate_plugins</code>, <code>edit_theme_options</code> o <code>switch_themes</code> a menos que sea un Administrador de confianza. Son las llaves que pueden romper el sitio.'
                : 'Never give anyone <code>activate_plugins</code>, <code>edit_theme_options</code>, or <code>switch_themes</code> unless they are a trusted Administrator. These are the keys that can break the site.',
            footer_text: isEs ? '¿Listo para empezar? Vuelve atrás y crea tu primer rol.' : 'Ready to start? Go back and create your first role.',
            footer_btn: isEs ? 'Crear un Rol Ahora' : 'Create a Role Now'
        };

        var html = `
        <div class="easy-roles-guide-container" style="max-width: 900px; margin: 0 auto; line-height: 1.6;">
            
            <div class="easy-roles-intro" style="margin-bottom: 2.5rem; text-align: center; padding: 3rem 2rem;">
                <div class="easy-roles-intro__title" style="justify-content: center; margin-bottom: 1rem; font-size: 1.5rem;">
                    <span class="dashicons dashicons-welcome-learn-more" style="font-size: 2rem; width: 2rem; height: 2rem;"></span>
                    ${t.title}
                </div>
                <div class="easy-roles-intro__text" style="max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
                    <p>${t.intro}</p>
                </div>
            </div>

            <div class="easy-roles-legend__grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                
                <div class="easy-roles-legend__item" style="background:#fff; border:1px solid #e2e4e7; padding: 2rem; position: relative; overflow: visible;">
                    <div style="position: absolute; top: -1rem; left: 50%; transform: translateX(-50%); background: #fcefdc; color: #946c00; border: 1px solid #f0c33c; padding: 0.25rem 1rem; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">${t.key_label}</div>
                    <h4 style="margin: 1rem 0 1rem; font-size: 1.2rem; text-align: center;">${t.key_title}</h4>
                    <p style="font-size: 0.95rem; margin-bottom: 1.5rem; text-align: center;">${t.key_desc}</p>
                    <ul style="list-style: none; margin: 0; padding: 0; font-size: 0.9rem; color: #50575e;">
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">read</strong><br>
                            ${t.read_desc}
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">edit_posts</strong><br>
                            ${t.edit_posts_desc}
                        </li>
                        <li>
                            <strong style="color: #1d2327;">manage_woocommerce</strong><br>
                            ${t.wc_desc}
                        </li>
                    </ul>
                </div>

                <div class="easy-roles-legend__item" style="background:#fff; border:1px solid #e2e4e7; padding: 2rem; position: relative; overflow: visible;">
                    <div style="position: absolute; top: -1rem; left: 50%; transform: translateX(-50%); background: #e5f5fa; color: #135e96; border: 1px solid #72aee6; padding: 0.25rem 1rem; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">${t.anchor_label}</div>
                    <h4 style="margin: 1rem 0 1rem; font-size: 1.2rem; text-align: center;">${t.anchor_title}</h4>
                    <p style="font-size: 0.95rem; margin-bottom: 1.5rem; text-align: center;">${t.anchor_desc}</p>
                    <ul style="list-style: none; margin: 0; padding: 0; font-size: 0.9rem; color: #50575e;">
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">upload_files</strong><br>
                            ${t.media_desc}
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">activate_plugins</strong><br>
                            ${t.plugins_room}
                        </li>
                        <li>
                            <strong style="color: #1d2327;">edit_pages</strong><br>
                            ${t.pages_room}
                        </li>
                    </ul>
                </div>

                <div class="easy-roles-legend__item" style="background:#fff; border:1px solid #e2e4e7; padding: 2rem; position: relative; overflow: visible;">
                    <div style="position: absolute; top: -1rem; left: 50%; transform: translateX(-50%); background: #f0f0f1; color: #646970; border: 1px solid #c3c4c7; padding: 0.25rem 1rem; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">${t.action_label}</div>
                    <h4 style="margin: 1rem 0 1rem; font-size: 1.2rem; text-align: center;">${t.action_title}</h4>
                    <p style="font-size: 0.95rem; margin-bottom: 1.5rem; text-align: center;">${t.action_desc}</p>
                    <ul style="list-style: none; margin: 0; padding: 0; font-size: 0.9rem; color: #50575e;">
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">delete_others_posts</strong><br>
                            ${t.delete_others}
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f1;">
                            <strong style="color: #1d2327;">publish_posts</strong><br>
                            ${t.publish_posts}
                        </li>
                        <li>
                            <strong style="color: #1d2327;">install_plugins</strong><br>
                            ${t.install_plugins}
                        </li>
                    </ul>
                </div>
            </div>

            <h3 style="margin: 0 0 1.5rem; font-size: 1.3rem; color: #1d2327; text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 1rem;">${t.recipes_title}</h3>
            
            <div style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
                
                <div style="display:flex; align-items: flex-start; gap:1.25rem; padding:1.5rem; background:#fff; border:1px solid #c3c4c7; border-left:4px solid #2271b1; border-radius:4px;">
                    <div style="background: #f0f6fc; color:#2271b1; padding: 10px; border-radius: 50%;"><span class="dashicons dashicons-welcome-write-blog" style="font-size:24px; width:24px; height:24px;"></span></div>
                    <div>
                        <h5 style="margin:0 0 0.5rem; font-size:1.1rem; color: #1d2327;">${t.blog_editor}</h5>
                        <p style="margin:0 0 1rem; font-size:0.95rem; color:#50575e; line-height: 1.5;">${t.blog_editor_desc}</p>
                        <div style="font-size: 0.85rem; background: #f6f7f7; padding: 0.75rem; border-radius: 4px;">
                            <strong>${t.needs}:</strong> <code>read</code> + <code>edit_posts</code> + <code>upload_files</code><br>
                            <strong>${t.optional}:</strong> <code>publish_posts</code> ${t.trust}
                        </div>
                    </div>
                </div>

                <div style="display:flex; align-items: flex-start; gap:1.25rem; padding:1.5rem; background:#fff; border:1px solid #c3c4c7; border-left:4px solid #96588a; border-radius:4px;">
                    <div style="background: #fdf2f8; color:#96588a; padding: 10px; border-radius: 50%;"><span class="dashicons dashicons-store" style="font-size:24px; width:24px; height:24px;"></span></div>
                    <div>
                        <h5 style="margin:0 0 0.5rem; font-size:1.1rem; color: #1d2327;">${t.shop_manager}</h5>
                        <p style="margin:0 0 1rem; font-size:0.95rem; color:#50575e; line-height: 1.5;">${t.shop_manager_desc}</p>
                        <div style="font-size: 0.85rem; background: #f6f7f7; padding: 0.75rem; border-radius: 4px;">
                            <strong>${t.needs}:</strong> <code>manage_woocommerce</code> + <code>edit_products</code><br>
                            <strong>${t.careful}:</strong> ${t.avoid_tax}
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; display:flex; gap:1rem; padding:1.5rem; background:#fcf0f1; border:1px solid #d63638; border-radius:4px; align-items: center;">
                <span class="dashicons dashicons-warning" style="color:#d63638; font-size:32px; width:32px; height:32px;"></span>
                <div>
                    <h5 style="margin:0 0 0.25rem; font-size:1rem; color: #d63638;">${t.gold_rule}</h5>
                    <p style="margin:0; font-size:0.95rem; color:#1d2327;">${t.gold_rule_text}</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem; color: #646970; font-size: 0.9rem;">
                <p>${t.footer_text}</p>
                <button type="button" class="easy-roles-btn easy-roles-btn--primary" onclick="document.getElementById('er-tab-create').click()" style="margin-top: 0.5rem;">
                    ${t.footer_btn}
                </button>
            </div>

        </div>
        `;

        container.innerHTML = html;
    }

    /* ================================================================
       USER MANAGEMENT PANEL
       Aquí va toda la lógica del panel de usuarios jeje.
       Dos puntos de entrada:
       1. Tab permanente "Users" con dropdown de roles.
       2. Click contextual en el user-count de cada tarjeta de rol.
    ================================================================ */
    function initUsersPanel() {
        var allRoles = data.allRoles || {};
        var currentUserId = parseInt(data.currentUserId, 10) || 0;

        /* DOM References */
        var roleSelect = document.getElementById('er-users-role-select');
        var searchWrap = document.getElementById('er-users-search-wrap');
        var searchInput = document.getElementById('er-users-search');
        var listContainer = document.getElementById('er-users-list');
        var emptyState = document.getElementById('er-users-empty');
        var pagination = document.getElementById('er-users-pagination');
        var prevBtn = document.getElementById('er-users-prev');
        var nextBtn = document.getElementById('er-users-next');
        var pageInfo = document.getElementById('er-users-page-info');
        var roleBadge = document.getElementById('er-users-role-badge');
        var backBtn = document.getElementById('er-users-back');

        /* State */
        var currentRole = '';
        var currentPage = 1;
        var totalPages = 1;
        var searchTimer = null;

        if (!roleSelect || !listContainer) return;

        /* ─── Entry point 1: Dropdown change ─── */
        roleSelect.addEventListener('change', function () {
            var selectedRole = roleSelect.value;
            if (selectedRole) {
                currentRole = selectedRole;
                currentPage = 1;
                loadUsers();
            } else {
                resetPanel();
            }
        });

        /* ─── Entry point 2: Click en el user-count de las tarjetas ─── */
        document.querySelectorAll('.easy-roles-card__user-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var role = link.getAttribute('data-role');
                if (!role) return;

                /* Cambiar a la tab de usuarios */
                switchTab('er-tab-users');

                /* Seleccionar el rol en el dropdown */
                roleSelect.value = role;
                currentRole = role;
                currentPage = 1;
                loadUsers();
            });

            /* Keyboard support */
            link.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    link.click();
                }
            });
        });

        /* ─── Back button ─── */
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                switchTab('er-tab-roles');
            });
        }

        /* ─── Search (debounced, filtra client-side) ─── */
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    filterUsersClientSide();
                }, 300);
            });
        }

        /* ─── Pagination ─── */
        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    loadUsers();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadUsers();
                }
            });
        }

        /* ─── Load users via AJAX ─── */
        function loadUsers() {
            showLoadingSkeleton();
            updateRoleBadge();
            showSearchBar();

            ajaxPost('easy_roles_get_users', {
                role_slug: currentRole,
                page: currentPage
            }, function (res) {
                if (!res.success) {
                    showToast(res.data.message || strings.errorOccurred, 'error');
                    resetPanel();
                    return;
                }

                var usersData = res.data.users || [];
                totalPages = res.data.total_pages || 1;
                currentPage = res.data.current_page || 1;

                renderUsers(usersData);
                updatePagination(res.data.total);

                /* Limpiar búsqueda al cambiar de rol */
                if (searchInput) {
                    searchInput.value = '';
                }
            });
        }

        /* ─── Render user rows ─── */
        function renderUsers(users) {
            /* Limpiar el contenedor */
            listContainer.innerHTML = '';

            if (users.length === 0) {
                listContainer.innerHTML = '<div class="easy-roles-users-empty">' +
                    '<span class="dashicons dashicons-admin-users" style="font-size: 3rem; width: 3rem; height: 3rem; color: var(--er-border); margin-bottom: 1rem;"></span>' +
                    '<p>' + escapeHtml(strings.no_users) + '</p>' +
                    '</div>';
                return;
            }

            users.forEach(function (user) {
                var isSelf = (user.id === currentUserId);
                var row = document.createElement('div');
                row.className = 'easy-roles-user-row' + (isSelf ? ' easy-roles-user-row--self' : '');
                row.setAttribute('data-user-id', user.id);
                row.setAttribute('data-name', (user.display_name || '').toLowerCase());
                row.setAttribute('data-email', (user.email || '').toLowerCase());

                /* Avatar */
                var avatarHtml = '<img class="easy-roles-user-row__avatar" ' +
                    'src="' + escapeHtml(user.avatar_url) + '" ' +
                    'alt="' + escapeHtml(user.display_name) + '" ' +
                    'loading="lazy" width="40" height="40">';

                /* Info */
                var infoHtml = '<div class="easy-roles-user-row__info">' +
                    '<span class="easy-roles-user-row__name">' + escapeHtml(user.display_name) + '</span>' +
                    '<span class="easy-roles-user-row__email">' + escapeHtml(user.email) + '</span>' +
                    '</div>';

                /* Role dropdown */
                var selectHtml = '<div class="easy-roles-user-row__role-change">';
                selectHtml += '<select class="easy-roles-user-row__role-select" data-user-id="' + user.id + '"';
                selectHtml += ' aria-label="' + escapeHtml(strings.change_role + ': ' + user.display_name) + '"';
                if (isSelf) {
                    selectHtml += ' disabled title="' + escapeHtml(strings.self_demote_warn) + '"';
                }
                selectHtml += '>';

                /* Opciones del dropdown */
                Object.keys(allRoles).forEach(function (roleSlug) {
                    var roleName = allRoles[roleSlug];
                    var selected = (roleSlug === currentRole) ? ' selected' : '';
                    selectHtml += '<option value="' + escapeHtml(roleSlug) + '"' + selected + '>';
                    selectHtml += escapeHtml(roleName);
                    selectHtml += '</option>';
                });

                selectHtml += '</select></div>';

                row.innerHTML = avatarHtml + infoHtml + selectHtml;
                listContainer.appendChild(row);
            });

            /* Attach role change listeners */
            listContainer.querySelectorAll('.easy-roles-user-row__role-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    handleRoleChange(select);
                });
            });
        }

        /* ─── Handle role change ─── */
        function handleRoleChange(selectEl) {
            var userId = parseInt(selectEl.getAttribute('data-user-id'), 10);
            var newRole = selectEl.value;

            /* Si es el mismo rol, no hacer nada */
            if (newRole === currentRole) {
                return;
            }

            var targetRoleName = allRoles[newRole] || newRole;
            var row = selectEl.closest('.easy-roles-user-row');
            var userName = '';
            if (row) {
                var nameEl = row.querySelector('.easy-roles-user-row__name');
                userName = nameEl ? nameEl.textContent : '';
            }

            /* Confirmación con modal */
            showConfirm(
                strings.change_role,
                strings.confirm_change_role + '\n\n' + userName + ' → ' + targetRoleName,
                function () {
                    /* Loading state */
                    if (row) row.classList.add('easy-roles-loading');

                    ajaxPost('easy_roles_change_user_role', {
                        user_id: userId,
                        new_role: newRole
                    }, function (res) {
                        if (row) row.classList.remove('easy-roles-loading');

                        if (res.success) {
                            showToast(strings.role_changed, 'success');
                            /* Recargar la lista porque el usuario ya no está en este rol */
                            loadUsers();
                        } else {
                            showToast(res.data.message || strings.errorOccurred, 'error');
                            /* Revertir el select al valor original */
                            selectEl.value = currentRole;
                        }
                    });
                },
                false
            );

            /* Si el usuario cancela el modal, revertir el select */
            var cancelBtn2 = document.getElementById('er-modal-cancel');
            if (cancelBtn2) {
                var handler = function () {
                    selectEl.value = currentRole;
                    cancelBtn2.removeEventListener('click', handler);
                };
                cancelBtn2.addEventListener('click', handler);
            }
        }

        /* ─── Client-side search filter ─── */
        function filterUsersClientSide() {
            if (!searchInput) return;

            var query = searchInput.value.toLowerCase().trim();
            var rows = listContainer.querySelectorAll('.easy-roles-user-row');
            var visibleCount = 0;

            rows.forEach(function (row) {
                var name = row.getAttribute('data-name') || '';
                var email = row.getAttribute('data-email') || '';
                var match = !query || name.indexOf(query) !== -1 || email.indexOf(query) !== -1;

                row.style.display = match ? 'flex' : 'none';
                if (match) visibleCount++;
            });

            /* Mostrar empty state si no hay resultados */
            var existingNoResults = listContainer.querySelector('.easy-roles-users-no-results');
            if (existingNoResults) existingNoResults.remove();

            if (visibleCount === 0 && rows.length > 0) {
                var noResults = document.createElement('div');
                noResults.className = 'easy-roles-users-empty easy-roles-users-no-results';
                noResults.innerHTML = '<p>' + escapeHtml(strings.no_users) + '</p>';
                listContainer.appendChild(noResults);
            }
        }

        /* ─── Skeleton loading ─── */
        function showLoadingSkeleton() {
            var skeletonHtml = '<div class="easy-roles-users-loading">';
            for (var i = 0; i < 5; i++) {
                skeletonHtml += '<div class="easy-roles-users-skeleton">' +
                    '<div class="easy-roles-users-skeleton__avatar"></div>' +
                    '<div class="easy-roles-users-skeleton__text">' +
                    '<div class="easy-roles-users-skeleton__line easy-roles-users-skeleton__line--long"></div>' +
                    '<div class="easy-roles-users-skeleton__line easy-roles-users-skeleton__line--short"></div>' +
                    '</div></div>';
            }
            skeletonHtml += '</div>';
            listContainer.innerHTML = skeletonHtml;
        }

        /* ─── Update role badge ─── */
        function updateRoleBadge() {
            if (!roleBadge) return;

            if (currentRole && allRoles[currentRole]) {
                roleBadge.textContent = allRoles[currentRole];
                roleBadge.style.display = 'inline-flex';
            } else {
                roleBadge.style.display = 'none';
            }
        }

        /* ─── Show/hide search bar ─── */
        function showSearchBar() {
            if (searchWrap) {
                searchWrap.style.display = currentRole ? 'flex' : 'none';
            }
        }

        /* ─── Pagination controls ─── */
        function updatePagination(totalUsers) {
            if (!pagination) return;

            if (totalPages <= 1) {
                pagination.style.display = 'none';
                return;
            }

            pagination.style.display = 'flex';

            /* Page info text */
            var pageText = strings.page_of
                .replace('%1', currentPage)
                .replace('%2', totalPages);
            if (pageInfo) {
                pageInfo.textContent = pageText + ' (' + totalUsers + ' total)';
            }

            /* Button states */
            if (prevBtn) prevBtn.disabled = (currentPage <= 1);
            if (nextBtn) nextBtn.disabled = (currentPage >= totalPages);
        }

        /* ─── Reset panel (no role selected) ─── */
        function resetPanel() {
            currentRole = '';
            currentPage = 1;
            totalPages = 1;

            listContainer.innerHTML = '<div class="easy-roles-users-empty" id="er-users-empty">' +
                '<span class="dashicons dashicons-groups" style="font-size: 3rem; width: 3rem; height: 3rem; color: var(--er-border); margin-bottom: 1rem;"></span>' +
                '<p>' + escapeHtml(strings.select_role_prompt) + '</p>' +
                '</div>';

            if (pagination) pagination.style.display = 'none';
            if (searchWrap) searchWrap.style.display = 'none';
            if (roleBadge) roleBadge.style.display = 'none';
            if (roleSelect) roleSelect.value = '';
        }
    }

})();
