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
        updateAllGroupCounts();
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
        tab.classList.add('easy-roles-tab--active');
        tab.setAttribute('aria-selected', 'true');
        panel.classList.add('easy-roles-panel--active');
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
    }

    /* ================================================================
       UPDATE ROLE
    ================================================================ */
    function initFormEdit() {
        var form = document.getElementById('er-form-edit');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!confirm(strings.confirmUpdate)) return;

            var slug = document.getElementById('er-edit-slug').value;
            var name = document.getElementById('er-edit-name').value.trim();

            var caps = {};
            form.querySelectorAll('.easy-roles-cap__check:checked').forEach(function (cb) {
                var capName = cb.closest('.easy-roles-cap').getAttribute('data-cap');
                caps[capName] = 1;
            });

            form.classList.add('easy-roles-loading');

            ajaxPost('easy_roles_update_role', {
                role_slug: slug,
                role_name: name,
                capabilities: caps
            }, function (res) {
                form.classList.remove('easy-roles-loading');

                if (res.success) {
                    showToast(strings.roleUpdated, 'success');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    showToast(res.data.message || strings.errorOccurred, 'error');
                }
            });
        });
    }

    /* ================================================================
       DELETE ROLE
    ================================================================ */
    function deleteRole(slug) {
        if (!confirm(strings.confirmDelete)) return;

        var card = document.querySelector('.easy-roles-card[data-role="' + slug + '"]');
        if (card) card.classList.add('easy-roles-loading');

        ajaxPost('easy_roles_delete_role', { role_slug: slug }, function (res) {
            if (card) card.classList.remove('easy-roles-loading');

            if (res.success) {
                showToast(strings.roleDeleted, 'success');
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
        /* Create modal overlay */
        var overlay = document.createElement('div');
        overlay.className = 'easy-roles-modal-overlay';
        overlay.innerHTML =
            '<div class="easy-roles-modal">' +
            '<h3>' + escapeHtml(strings.cloneName || 'Clone Role') + '</h3>' +
            '<label for="er-clone-name">' + escapeHtml(strings.cloneName || 'Name') + '</label>' +
            '<input type="text" id="er-clone-name" class="regular-text" maxlength="100" autofocus>' +
            '<label for="er-clone-slug">' + escapeHtml(strings.cloneSlug || 'Slug') + '</label>' +
            '<input type="text" id="er-clone-slug" class="regular-text" maxlength="60" pattern="[a-z0-9_]+">' +
            '<div class="easy-roles-modal__actions">' +
            '<button type="button" class="button er-clone-cancel">' + escapeHtml('Cancel') + '</button>' +
            '<button type="button" class="button button-primary er-clone-confirm">' + escapeHtml('Clone') + '</button>' +
            '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        /* Auto slug from name */
        var nameIn = overlay.querySelector('#er-clone-name');
        var slugIn = overlay.querySelector('#er-clone-slug');

        nameIn.addEventListener('input', function () {
            slugIn.value = nameIn.value
                .toLowerCase()
                .replace(/[^a-z0-9\s_]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 60);
        });

        /* Cancel */
        overlay.querySelector('.er-clone-cancel').addEventListener('click', function () {
            overlay.remove();
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });

        /* Confirm */
        overlay.querySelector('.er-clone-confirm').addEventListener('click', function () {
            var newName = nameIn.value.trim();
            var newSlug = slugIn.value.trim();

            if (!newName || !newSlug) {
                showToast(strings.nameRequired, 'error');
                return;
            }

            overlay.querySelector('.easy-roles-modal').classList.add('easy-roles-loading');

            ajaxPost('easy_roles_clone_role', {
                source_slug: sourceSlug,
                new_slug: newSlug,
                new_name: newName
            }, function (res) {
                overlay.remove();

                if (res.success) {
                    showToast(strings.roleCloned, 'success');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    showToast(res.data.message || strings.errorOccurred, 'error');
                }
            });
        });

        /* Focus name input */
        setTimeout(function () { nameIn.focus(); }, 100);
    }

    /* ================================================================
       BACK BUTTON
    ================================================================ */
    function initBackButton() {
        var btn = document.getElementById('er-edit-back');
        if (btn) {
            btn.addEventListener('click', function () {
                switchTab('er-tab-roles');
                /* Hide edit tab again */
                document.getElementById('er-tab-edit').classList.add('easy-roles-tab--hidden');
            });
        }
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

})();
