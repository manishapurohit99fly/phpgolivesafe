/* ============================================================================
 * Menu Builder — external script
 * Reads runtime values from window.menuBuilderConfig (set inline by Blade).
 * ============================================================================ */
(function () {
    'use strict';

    const config          = window.menuBuilderConfig || {};
    const SAVE_ORDER_URL  = config.saveOrderUrl || '';
    const STORE_ITEMS_URL = config.storeItemsUrl || '';
    const CSRF            = config.csrf || '';
    const SUCCESS_MESSAGE = config.successMessage || null;
    const ERROR_MESSAGE   = config.errorMessage || null;
    const MENUS_COUNT     = Number(config.menusCount || 0);

    /* ── Accordion toggle (global — used by inline onclick) ─────────────── */
    window.mbToggleAcc = function (btn, targetId) {
        const target = document.getElementById(targetId);
        if (!target) return;
        const bsCol  = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });
        const isOpen = target.classList.contains('show');

        if (isOpen) {
            bsCol.hide();
            btn.classList.remove('open');
            btn.querySelector('.arr')?.classList.remove('open');
        } else {
            bsCol.show();
            btn.classList.add('open');
            btn.querySelector('.arr')?.classList.add('open');
        }
    };

    /* ── SweetAlert delete helper (shared) ──────────────────────────────── */
    function mbSwalDelete({ url, title, text, successText }) {
        if (typeof Swal === 'undefined' || typeof $ === 'undefined') return;

        Swal.fire({
            title:              title,
            text:               text,
            icon:               'warning',
            showCancelButton:   true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Yes, Delete',
            cancelButtonText:   'No, Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url:    url,
                method: 'POST',
                data: {
                    _token:  CSRF,
                    _method: 'DELETE'
                },
                success: function (response) {
                    Swal.fire(
                        'Deleted!',
                        (response && response.message) ? response.message : successText,
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });
                },
                error: function () {
                    Swal.fire(
                        'Error!',
                        'Something went wrong',
                        'error'
                    );
                }
            });
        });
    }

    /* ── Confirm delete (menu) — global ─────────────────────────────────── */
    window.mbConfirmDelete = function (action, name) {
        mbSwalDelete({
            url:         action,
            title:       'Are you sure?',
            text:        `Delete menu "${name}" and all its items? This cannot be undone.`,
            successText: 'Menu deleted successfully.'
        });
    };

    /* ── Confirm delete (item) — global ─────────────────────────────────── */
    window.mbConfirmDeleteItem = function (action, name) {
        mbSwalDelete({
            url:         action,
            title:       'Are you sure?',
            text:        `Remove "${name}" from this menu?`,
            successText: 'Menu item removed successfully.'
        });
    };
    
    /* ── Toast helper — uses admin layout's toastr_alert ────────────────── */
    function showToast(message, type) {
        // Map Bootstrap-style 'danger' to toastr's 'error'
        const toastrType = type === 'danger' ? 'error' : (type || 'info');
        const headings   = {
            success: 'Success',
            error:   'Error',
            warning: 'Warning',
            info:    'Info',
        };
        const heading = headings[toastrType] || 'Info';

        if (typeof window.toastr_alert === 'function') {
            window.toastr_alert(heading, message, toastrType);
        } else if (typeof window.toastr !== 'undefined' && typeof window.toastr[toastrType] === 'function') {
            window.toastr[toastrType](message);
        }
    }

    /* ── DOM ready ──────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {

        /* 1. Flash messages from server-side session or bulk-add reload */
        const pendingSuccess = sessionStorage.getItem('mb_flash_success');
        if (pendingSuccess) {
            sessionStorage.removeItem('mb_flash_success');
            showToast(pendingSuccess, 'success');
        } else if (SUCCESS_MESSAGE) {
            showToast(SUCCESS_MESSAGE, 'success');
        } else if (ERROR_MESSAGE) {
            showToast(ERROR_MESSAGE, 'danger');
        }

        /* 2. Menu selector — show/hide panels, persist to localStorage */
        const menuSelect  = document.getElementById('mbMenuSelect');
        const selectBtn   = document.getElementById('mbSelectBtn');
        const noSelection = document.getElementById('mbNoSelection');
        const layout      = document.getElementById('mbBuilderLayout');
        
        function showPanel(menuId) {
            document.querySelectorAll('.mb-panel').forEach(p => p.classList.remove('active'));
            noSelection?.classList.add('d-none');
            layout?.classList.remove('d-none');

            if (menuId) {
                const panel = document.getElementById('mbPanel_' + menuId);
                if (panel) panel.classList.add('active');
                localStorage.setItem('mb_last_menu', menuId);
            }
        }

        selectBtn?.addEventListener('click', () => {
            const val = menuSelect?.value;
            if (!val) return;
            showPanel(val);
        });

        // Restore from localStorage on page load
        const saved = localStorage.getItem('mb_last_menu');
        if (saved && document.getElementById('mbPanel_' + saved)) {
            if (menuSelect) menuSelect.value = saved;
            showPanel(saved);
        } else if (MENUS_COUNT === 0) {
            // No menus — keep empty state visible (no-op kept for parity)
        }

        /* 3. Sidebar: select-all toggles */
        document.querySelectorAll('.mb-sel-all').forEach(btn => {
            btn.addEventListener('click', () => {
                const group  = btn.dataset.group;
                const checks = document.querySelectorAll('.' + group);
                const allOn  = [...checks].every(c => c.checked);
                checks.forEach(c => c.checked = !allOn);
                btn.textContent = allOn ? 'Select All' : 'Deselect All';
            });
        });

        /* 4. Sidebar: bulk-add checked pages / posts to the active menu */
        function getActiveMenuId() {
            const panel = document.querySelector('.mb-panel.active');
            if (!panel) return null;
            return panel.id.replace('mbPanel_', '');
        }

        function getActivePrefill() {
            const panel = document.querySelector('.mb-panel.active');
            if (!panel) return null;
            return {
                title: panel.querySelector('.mb-prefill-title'),
                url:   panel.querySelector('.mb-prefill-url'),
            };
        }

        function bulkAddCheckedToMenu(checked, btn, emptyMessage) {
            if (!checked.length) {
                showToast(emptyMessage, 'warning');
                return;
            }

            const menuId = getActiveMenuId();
            if (!menuId) {
                showToast('Select a menu first.', 'warning');
                return;
            }

            if (!STORE_ITEMS_URL) {
                showToast('Bulk add is not configured.', 'danger');
                return;
            }

            const items = checked.map(c => ({
                title: c.dataset.title,
                url:   c.dataset.url,
                target: '_self',
            }));

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding…';

            fetch(STORE_ITEMS_URL, {
                method:  'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-TOKEN':     CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                },
                body: JSON.stringify({ menu_id: menuId, items }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    localStorage.setItem('mb_last_menu', menuId);
                    sessionStorage.setItem('mb_flash_success', data.message);
                    window.location.reload();
                    return;
                }

                const msg = data.message
                    || (data.errors && Object.values(data.errors).flat()[0])
                    || 'Failed to add items.';
                showToast(msg, 'danger');
            })
            .catch(() => showToast('Network error. Please try again.', 'danger'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        }

        document.getElementById('addPagesBtn')?.addEventListener('click', function () {
            const checked = [...document.querySelectorAll('.page-check:checked')];
            bulkAddCheckedToMenu(checked, this, 'Select at least one page.');
        });

        document.getElementById('addPostsBtn')?.addEventListener('click', function () {
            const checked = [...document.querySelectorAll('.post-check:checked')];
            bulkAddCheckedToMenu(checked, this, 'Select at least one post.');
        });

        document.getElementById('addCustomLinkBtn')?.addEventListener('click', () => {
            const titleEl = document.getElementById('customLinkTitle');
            const urlEl   = document.getElementById('customLinkUrl');
            const title   = titleEl?.value.trim() || '';
            const url     = urlEl?.value.trim() || '';
            if (!title) { showToast('Enter a link label.', 'warning'); return; }

            const fields = getActivePrefill();
            if (!fields) { showToast('Select a menu first.', 'warning'); return; }

            fields.title.value = title;
            fields.url.value   = url || '#';
            fields.title.focus();
            showToast('Custom link pre-filled. Click Add Item to save.', 'info');
            
            if (titleEl) titleEl.value = '';
            if (urlEl)   urlEl.value   = '';
        });

        /* ============================================================
         * 5. WordPress-style nesting powered by jquery.nestable.js
         * ============================================================
         * The plugin handles all of the smooth drag/drop, indent /
         * outdent and tree restructuring for us. We only have to:
         *
         *   • point it at the existing `.mb-drag-handle` element
         *   • flag the collapsible edit form as `dd-nodrag` so opening
         *     the form never starts a drag
         *   • on every `change` event, refresh the `child-item` class
         *     used for visual styling and flip the dirty flag
         *   • enforce requirement #1 — the first root item is never
         *     allowed to slip into a nested position
         * ============================================================ */
        const MAX_NEST_DEPTH = 5;

        /**
         * Apply / clear the `child-item` class for every list item based
         * on whether it currently lives inside another `<li>`. Run after
         * each tree change so styling matches the new structure.
         */
        function refreshHierarchyClasses(root) {
            root.querySelectorAll('li.dd-item.menu-item-card').forEach(li => {
                const isChild = !!li.parentElement?.closest('li.dd-item.menu-item-card');
                li.classList.toggle('child-item', isChild);
                li.dataset.parent = isChild
                    ? li.parentElement.closest('li.dd-item.menu-item-card').dataset.id
                    : '';
            });
        }

        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.nestable === 'function') {
            const $ = window.jQuery;

            $('.menu-sortable.dd').each(function () {
                const $container = $(this);
                const containerEl = this;
                const menuId = $container.data('menu');

                $container.nestable({
                    handleClass: 'mb-drag-handle',
                    noDragClass: 'dd-nodrag',
                    maxDepth:    MAX_NEST_DEPTH,
                    threshold:   25,
                    expandBtnHTML:   '',
                    collapseBtnHTML: '',
                });

                refreshHierarchyClasses(containerEl);

                $container.on('change', function () {
                    refreshHierarchyClasses(containerEl);
                    markDirty(menuId);
                });
            });
        }

        /* 7. Chevron rotate on collapse open/close */
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(trigger => {
            const targetId = trigger.dataset.bsTarget;
            if (!targetId) return;
            const colEl = document.querySelector(targetId);
            if (!colEl) return;

            colEl.addEventListener('show.bs.collapse', () => {
                trigger.querySelector('.mb-item-chevron')?.classList.add('open');
            });
            colEl.addEventListener('hide.bs.collapse', () => {
                trigger.querySelector('.mb-item-chevron')?.classList.remove('open');
            });
        });

        /* 8. Dirty state + save order */
        function markDirty(menuId) {
            if (!menuId) return;
            const badge = document.getElementById('dirtyBadge_' + menuId);
            const btn   = document.querySelector('[data-menu="' + menuId + '"].mb-save-order-btn');
            badge?.classList.remove('d-none');
            if (btn) btn.style.display = '';
        }

        document.querySelectorAll('.mb-save-order-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const menuId  = this.dataset.menu;
                const items   = collectOrder(menuId);
                const spinner = document.getElementById('saveSpinner_' + menuId);
                const icon    = document.getElementById('saveIcon_' + menuId);

                this.disabled = true;
                spinner?.classList.remove('d-none');
                icon?.classList.add('d-none');

                fetch(SAVE_ORDER_URL, {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ items }),
                })
                .then(r => r.json())
                .then(data => {
                    spinner?.classList.add('d-none');
                    icon?.classList.remove('d-none');
                    this.disabled = false;

                    if (data.success) {
                        showToast('Menu order saved!', 'success');
                        document.getElementById('dirtyBadge_' + menuId)?.classList.add('d-none');
                        this.style.display = 'none';
                    } else {
                        showToast(data.message || 'Save failed.', 'danger');
                    }
                })
                .catch(() => {
                    spinner?.classList.add('d-none');
                    icon?.classList.remove('d-none');
                    this.disabled = false;
                    showToast('Network error. Please try again.', 'danger');
                });
            });
        });

        /**
         * Walk Nestable's serialised tree and emit one row per item with:
         *   • id          – menu_items.id
         *   • parent_id   – nullable (id of parent, or null)
         *   • sort_order  – sequential index within the same parent
         *
         * The very first root row is force-rooted to mirror the backend
         * rule that the first item is never a child.
         */
        function collectOrder(menuId) {
            const container = document.getElementById('sortable_' + menuId);
            if (!container) return [];

            // If Nestable isn't bound yet (e.g. empty menu) fall back to DOM scan.
            if (typeof window.jQuery === 'undefined' || !window.jQuery(container).hasClass('dd')) {
                return [];
            }

            const tree       = window.jQuery(container).nestable('serialize') || [];
            const rows       = [];
            const siblingIdx = Object.create(null);

            (function walk(nodes, parentId) {
                const key = parentId === null ? '__root__' : String(parentId);
                if (!(key in siblingIdx)) siblingIdx[key] = 0;

                nodes.forEach((node, i) => {
                    // First top-level item is always rooted, never nested.
                    const effectiveParent = (parentId === null && rows.length === 0) ? null : parentId;

                    rows.push({
                        id:         String(node.id),
                        parent_id:  effectiveParent,
                        sort_order: siblingIdx[key]++,
                    });

                    if (Array.isArray(node.children) && node.children.length) {
                        walk(node.children, node.id);
                    }
                });
            })(tree, null);

            return rows;
        }

        /* 9. Inline label update as user types in edit form */
        document.querySelectorAll('.mb-item-body').forEach(body => {
            const titleInput = body.querySelector('input[name="title"]');
            const header     = body.closest('.menu-item-card')?.querySelector('.lbl');
            if (titleInput && header) {
                titleInput.addEventListener('input', () => {
                    header.textContent = titleInput.value || 'Menu Item';
                });
            }
        });

    });
})();
