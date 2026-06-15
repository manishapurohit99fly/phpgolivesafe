/**
 * Global Image Cropper Initializer
 * Intercepts every image <input type="file"> across the admin panel,
 * opens a Cropper.js modal, and injects the cropped file back.
 *
 * Storage policy (changed):
 *   The cropped image is held ONLY in memory for the current page
 *   session. A page refresh / back-forward / tab close discards it
 *   and the preview reverts to its server-rendered value. The image
 *   is only "kept" by virtue of being submitted with the form.
 *
 *   On every page load we proactively wipe any leftover IndexedDB /
 *   sessionStorage / localStorage entries written by previous
 *   versions of this script so the user is not haunted by a
 *   resurrected image after upgrading.
 *
 * Toolbar features (in the Cropper modal):
 *     • Zoom in / Zoom out
 *     • Rotate 90° left / right
 *     • Flip horizontal / vertical
 *     • Aspect ratio: Free | 1:1 | 4:3 | 16:9 | 3:4
 *     • Reset all changes
 *
 * Data attributes supported on the file input:
 *   data-crop-ratio="1"          → fixed aspect ratio  (e.g. 1 = square, "16/9")
 *                                   omit or "free" for free-form crop
 *   data-preview="#selector"    → CSS selector of <img> to update after crop
 *   data-placeholder-icon="#id" → icon element to hide once preview is set
 *
 * Public API (for AJAX forms like profile.js):
 *   window.cropperClearStored(inputElement)  – remove in-memory crop
 */

(function () {
    'use strict';

    // ── Storage layer: in-memory only (refresh = clean slate) ─────────────
    // Keyed by `_skey(input)` — lives only as long as this page does.
    var _memStore = Object.create(null);

    function _skey(input) {
        var id = (input && (input.id || input.name)) || 'field';
        return 'cropper__' + window.location.pathname + '__' + id;
    }

    function _save(input, base64) {
        _memStore[_skey(input)] = base64;
        return Promise.resolve();
    }
    function _loadSync(input) {
        return _memStore[_skey(input)] || null;
    }
    function _loadAsync(input) {
        return Promise.resolve(_loadSync(input));
    }

    // Legacy storage scrubbers — older builds of this script wrote the
    // cropped image to IndexedDB / sessionStorage / localStorage so it
    // survived refreshes. We keep just enough cleanup logic to wipe any
    // such leftovers on every page load, after which the in-memory
    // store above is the single source of truth.

    var IDB_NAME = 'admin-cropper';
    var IDB_STORE = 'crops';
    var IDB_VERSION = 1;
    var _idbPromise = null;

    function _hasIDB() {
        try { return typeof indexedDB !== 'undefined' && indexedDB !== null; }
        catch (_) { return false; }
    }

    function _openIDB() {
        if (!_hasIDB()) return Promise.reject(new Error('no-idb'));
        if (_idbPromise) return _idbPromise;

        _idbPromise = new Promise(function (resolve, reject) {
            var req;
            try { req = indexedDB.open(IDB_NAME, IDB_VERSION); }
            catch (e) { return reject(e); }

            req.onupgradeneeded = function (e) {
                var db = e.target.result;
                if (!db.objectStoreNames.contains(IDB_STORE)) {
                    db.createObjectStore(IDB_STORE);
                }
            };
            req.onsuccess = function (e) { resolve(e.target.result); };
            req.onerror   = function (e) { reject(e.target.error); };
            req.onblocked = function ()  { reject(new Error('idb-blocked')); };
        });
        return _idbPromise;
    }

    /** Wipe one key from IndexedDB (best-effort, never rejects). */
    function _idbDel(key) {
        return _openIDB().then(function (db) {
            return new Promise(function (resolve) {
                var tx = db.transaction(IDB_STORE, 'readwrite');
                tx.oncomplete = function () { resolve(); };
                tx.objectStore(IDB_STORE).delete(key);
            });
        }).catch(function () {});
    }

    /** Wipe EVERY key in the legacy IDB store (used during boot scrub). */
    function _idbWipeAll() {
        return _openIDB().then(function (db) {
            return new Promise(function (resolve) {
                var tx = db.transaction(IDB_STORE, 'readwrite');
                tx.oncomplete = function () { resolve(); };
                tx.objectStore(IDB_STORE).clear();
            });
        }).catch(function () {});
    }

    /** Wipe sessionStorage + localStorage entries written by older versions. */
    function _wipeWebStorageLeftovers() {
        var stores = [];
        try { stores.push(sessionStorage); } catch (_) {}
        try { stores.push(localStorage);   } catch (_) {}

        stores.forEach(function (store) {
            try {
                var killKeys = [];
                for (var i = 0; i < store.length; i++) {
                    var k = store.key(i);
                    if (k && (k.indexOf('cropper__') === 0 ||
                              k.indexOf('cropper-deleted__') === 0)) {
                        killKeys.push(k);
                    }
                }
                killKeys.forEach(function (k) { store.removeItem(k); });
            } catch (_) { /* quota / privacy mode – ignore */ }
        });
    }

    /** Drop an entry from memory + scrub any legacy persisted copies. */
    function _clear(input) {
        var key = _skey(input);
        delete _memStore[key];
        try { sessionStorage.removeItem(key); } catch (_) {}
        try { localStorage.removeItem('cropper-deleted__' + key); } catch (_) {}
        return _idbDel(key);
    }

    /** Public: call from AJAX success handlers to discard the in-memory crop */
    window.cropperClearStored = function (input) {
        try { _clear(input); } catch (_) {}
    };

    // ── DOM helpers ────────────────────────────────────────────────────────

    function resolvePreview(input) {
        var sel = input.dataset.preview;
        if (sel) {
            var el = document.querySelector(sel);
            if (el) return el;
        }
        var container =
            input.closest('#profileImageContainer') ||
            input.closest('[data-img-container]')   ||
            input.closest('.img-upload-group');
        if (container) {
            var img = container.querySelector('img');
            if (img) return img;
        }
        return null;
    }

    function resolvePlaceholderIcon(input) {
        var sel = input.dataset.placeholderIcon;
        if (sel) return document.querySelector(sel);
        return null;
    }

    function parseAspectRatio(raw) {
        if (!raw || raw === 'free' || raw === '0' || raw === 'NaN') return NaN;
        if (raw.indexOf('/') !== -1) {
            var parts = raw.split('/');
            return parseFloat(parts[0]) / parseFloat(parts[1]);
        }
        return parseFloat(raw);
    }

    function parseIntOrNull(v) {
        var n = parseInt(v, 10);
        return isNaN(n) ? null : n;
    }

    function buildCropperOptions(input) {
        var raw = (input && input.dataset && input.dataset.cropRatio) || '';
        var aspectRatio = parseAspectRatio(raw);
        var minW = parseIntOrNull(input && input.dataset ? input.dataset.minCropWidth : null);
        var minH = parseIntOrNull(input && input.dataset ? input.dataset.minCropHeight : null);

        return {
            viewMode: 1,
            dragMode: 'move',
            aspectRatio: isNaN(aspectRatio) ? NaN : aspectRatio,
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            minCropBoxWidth:  minW || 120,
            minCropBoxHeight: minH || 120,
            toggleDragModeOnDblclick: false,
            responsive: true,
            zoomable: true,
            zoomOnTouch: true,
            zoomOnWheel: true,
            wheelZoomRatio: 0.1,
            background: true,
        };
    }

    /**
     * Reset a file input's preview back to its server-rendered original src
     * (captured into data-original-src on first paint). When there is no
     * original (e.g. Add User) the preview is hidden and the placeholder
     * icon — if any — is re-shown.
     */
    function resetPreviewToOriginal(input) {
        var previewEl = resolvePreview(input);
        if (previewEl) {
            var originalSrc = previewEl.getAttribute('data-original-src') || '';
            previewEl.src = originalSrc;
            if (!originalSrc || originalSrc === window.location.href) {
                previewEl.style.display = 'none';
            } else {
                previewEl.style.display = '';
            }
        }
        var iconEl = resolvePlaceholderIcon(input);
        if (iconEl) iconEl.style.display = '';
    }

    /** True when the input accepts image files. */
    function isImageInput(input) {
        if (!input || input.type !== 'file') return false;
        var accept = (input.getAttribute('accept') || '').toLowerCase();
        return accept.indexOf('image') !== -1 ||
            /\.(jpg|jpeg|png|gif|webp|svg)/.test(accept);
    }

    // ── On page load: ALWAYS start clean ─────────────────────────────────
    // A page refresh (or any fresh load) discards every cropped image. The
    // in-memory store is naturally empty here; we additionally wipe any
    // sessionStorage / localStorage / IndexedDB entries left behind by
    // older versions of this script so users coming from the persistent
    // build don't see a ghost image after upgrading.

    document.addEventListener('DOMContentLoaded', function () {

        _wipeWebStorageLeftovers();
        _idbWipeAll();

        document.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (!isImageInput(input)) return;

            // Capture the server-rendered preview src exactly once so that
            // form-reset / cancel can restore it cleanly later.
            var previewEl = resolvePreview(input);
            if (previewEl && !previewEl.hasAttribute('data-original-src')) {
                previewEl.setAttribute('data-original-src', previewEl.getAttribute('src') || '');
            }

            // Always reset on load — the file input is empty after a refresh
            // anyway; this just makes sure the visible preview matches.
            resetPreviewToOriginal(input);
        });

        // ── Clear cropped image when the form is reset ────────────────────
        // The Reset button on Add/Edit User forms calls form.reset(), which
        // dispatches a native `reset` event. We drop the in-memory crop AND
        // visually restore the preview to its original (server) state.
        document.addEventListener('reset', function (e) {
            var form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                if (!isImageInput(input)) return;
                _clear(input);
                resetPreviewToOriginal(input);
            });
        });

        // ── Boot the cropper modal ─────────────────────────────────────────

        var modalEl = document.getElementById('cropperModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var _cropperInstance   = null;
        var _cropperModal      = null;
        var _targetInput       = null;
        var _previewEl         = null;
        var _placeholderIconEl = null;
        var _originalSrc       = '';
        var _cropConfirmed     = false;

        // Track flip state per modal-open session
        var _flipH = false;
        var _flipV = false;

        _cropperModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false,
        });

        // ── Toolbar wiring ────────────────────────────────────────────────
        var toolbarEl    = modalEl.querySelector('#cropperToolbar');
        var aspectButtons = modalEl.querySelectorAll('.cropper-aspect');

        function setActiveAspectButton(rawValue) {
            aspectButtons.forEach(function (b) {
                var match = (b.dataset.cropperAspect === rawValue);
                b.classList.toggle('active', match);
            });
        }

        function syncToolbarToInput(input) {
            // Reset flips on every modal open
            _flipH = false;
            _flipV = false;
            modalEl.querySelectorAll('[data-cropper-action="flipHorizontal"], ' +
                                    '[data-cropper-action="flipVertical"]').forEach(function (b) {
                b.setAttribute('aria-pressed', 'false');
                b.classList.remove('active');
            });

            // Highlight the aspect ratio that matches the file input's preference
            var raw = (input && input.dataset && input.dataset.cropRatio) || '';
            if (!raw || raw === '0' || raw === 'free' || raw === 'NaN' ||
                isNaN(parseAspectRatio(raw))) {
                setActiveAspectButton('free');
            } else {
                // Normalise common values so the matching button is correctly highlit
                if (raw === '1' || raw === '1/1') setActiveAspectButton('1');
                else setActiveAspectButton(raw);
            }
        }

        if (toolbarEl) {
            toolbarEl.addEventListener('click', function (e) {
                var actionBtn = e.target.closest('[data-cropper-action]');
                if (actionBtn && _cropperInstance) {
                    e.preventDefault();
                    var action = actionBtn.dataset.cropperAction;
                    switch (action) {
                        case 'zoomIn':
                            _cropperInstance.zoom(0.1);
                            break;
                        case 'zoomOut':
                            _cropperInstance.zoom(-0.1);
                            break;
                        case 'rotateLeft':
                            _cropperInstance.rotate(-90);
                            break;
                        case 'rotateRight':
                            _cropperInstance.rotate(90);
                            break;
                        case 'flipHorizontal':
                            _flipH = !_flipH;
                            _cropperInstance.scaleX(_flipH ? -1 : 1);
                            actionBtn.setAttribute('aria-pressed', _flipH ? 'true' : 'false');
                            actionBtn.classList.toggle('active', _flipH);
                            break;
                        case 'flipVertical':
                            _flipV = !_flipV;
                            _cropperInstance.scaleY(_flipV ? -1 : 1);
                            actionBtn.setAttribute('aria-pressed', _flipV ? 'true' : 'false');
                            actionBtn.classList.toggle('active', _flipV);
                            break;
                        case 'reset':
                            _cropperInstance.reset();
                            _flipH = false;
                            _flipV = false;
                            modalEl.querySelectorAll(
                                '[data-cropper-action="flipHorizontal"], ' +
                                '[data-cropper-action="flipVertical"]'
                            ).forEach(function (b) {
                                b.setAttribute('aria-pressed', 'false');
                                b.classList.remove('active');
                            });
                            break;
                    }
                    return;
                }

                var aspectBtn = e.target.closest('[data-cropper-aspect]');
                if (aspectBtn && _cropperInstance) {
                    e.preventDefault();
                    var raw = aspectBtn.dataset.cropperAspect;
                    var ratio = parseAspectRatio(raw);
                    _cropperInstance.setAspectRatio(isNaN(ratio) ? NaN : ratio);
                    setActiveAspectButton(raw);
                }
            });
        }

        // Intercept all image file inputs (live / delegated)
        document.addEventListener('change', function (e) {
            var input = e.target;
            if (!input || input.type !== 'file') return;

            var accept   = (input.getAttribute('accept') || '').toLowerCase();
            var isImgInp = accept.indexOf('image') !== -1 ||
                /\.(jpg|jpeg|png|gif|webp|svg)/.test(accept);
            if (!isImgInp) return;

            var file = input.files[0];
            if (!file) return;

            // Block non-images even if the OS file picker lets them through.
            var isImageMime = (file.type || '').toLowerCase().indexOf('image/') === 0;
            if (!isImageMime) {
                if (typeof toastr !== 'undefined') toastr.error('Please select a valid image file (JPG/PNG/WebP).');
                resetPreviewToOriginal(input);
                input.value = '';
                return;
            }

            _targetInput       = input;
            _cropConfirmed     = false;
            _previewEl         = resolvePreview(input);
            _placeholderIconEl = resolvePlaceholderIcon(input);
            _originalSrc       = _previewEl ? (_previewEl.src || '') : '';

            syncToolbarToInput(input);

            var reader = new FileReader();
            reader.onload = function (ev) {
                document.getElementById('cropperModalImage').src = ev.target.result;
                _cropperModal.show();
            };
            reader.readAsDataURL(file);

            // Clear so the same file can be re-selected after cancel
            input.value = '';
        });

        // Init Cropper after modal is visually ready
        modalEl.addEventListener('shown.bs.modal', function () {
            var img = document.getElementById('cropperModalImage');
            if (_cropperInstance) { _cropperInstance.destroy(); _cropperInstance = null; }
            var opts = buildCropperOptions(_targetInput || {});

            // If the input provides a recommended crop size, initialise a sensible crop box.
            var recW = parseIntOrNull(_targetInput && _targetInput.dataset ? _targetInput.dataset.cropWidth : null);
            var recH = parseIntOrNull(_targetInput && _targetInput.dataset ? _targetInput.dataset.cropHeight : null);
            if (recW && recH) {
                var prevReady = opts.ready;
                opts.ready = function () {
                    if (typeof prevReady === 'function') prevReady.call(this);
                    try {
                        var cropper = this.cropper;
                        var container = cropper.getContainerData();
                        var boxW = Math.min(container.width * 0.72, container.height * 0.72);
                        var boxH = boxW;
                        if (!isNaN(opts.aspectRatio) && opts.aspectRatio !== 0) {
                            boxW = Math.min(container.width * 0.72, (container.height * 0.72) * opts.aspectRatio);
                            boxH = boxW / opts.aspectRatio;
                        }
                        cropper.setCropBoxData({
                            left: (container.width - boxW) / 2,
                            top:  (container.height - boxH) / 2,
                            width:  boxW,
                            height: boxH
                        });
                    } catch (_) {}
                };
            }

            _cropperInstance = new Cropper(img, opts);
        });

        // Cancel: restore the preview to whatever it was the moment the
        // modal opened — which is either the most recent in-memory crop
        // (if user already cropped once this session) or the server's
        // original src.
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (!_cropConfirmed && _previewEl) {
                var sync = _targetInput ? _loadSync(_targetInput) : null;
                if (sync) {
                    _previewEl.src           = sync;
                    _previewEl.style.display = '';
                } else if (_originalSrc) {
                    _previewEl.src = _originalSrc;
                }
            }
            if (_cropperInstance) { _cropperInstance.destroy(); _cropperInstance = null; }
        });

        // Confirm crop
        document.getElementById('cropperConfirmBtn').addEventListener('click', function () {
            if (!_cropperInstance || !_targetInput) return;

            var btn = this;
            btn.disabled  = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

            _cropperInstance.getCroppedCanvas({
                maxWidth: 1400,
                maxHeight: 1400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob(function (blob) {

                // 1. Inject file into input
                var croppedFile = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                _targetInput.files = dt.files;

                // 2. Update preview from blob URL (instant, no base64 conversion)
                var objectUrl = URL.createObjectURL(blob);
                if (_previewEl) {
                    _previewEl.src           = objectUrl;
                    _previewEl.style.display = '';
                }
                if (_placeholderIconEl) {
                    _placeholderIconEl.style.display = 'none';
                }

                // 3. Cache the cropped data URL in memory so re-opening the
                //    cropper modal in the SAME page session can restore the
                //    preview if the user cancels mid-edit. Discarded on
                //    refresh — see storage policy at top of file.
                var b64Reader = new FileReader();
                b64Reader.onload = function (ev) {
                    _save(_targetInput, ev.target.result);
                };
                b64Reader.readAsDataURL(blob);

                _cropConfirmed = true;
                _cropperModal.hide();

                btn.disabled  = false;
                btn.innerHTML = 'Crop &amp; Use';

                // 4. Notify downstream listeners (e.g. profile.js validation)
                _targetInput.dispatchEvent(new Event('cropped', { bubbles: true }));

            }, 'image/jpeg', 0.92);
        });
    });
}());
