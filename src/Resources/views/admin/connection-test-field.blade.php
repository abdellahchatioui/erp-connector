{{-- =====================================================================
     Part 1 — Connection Test
     ===================================================================== --}}
<div class="mb-4 last:!mb-0 mt-4">
    <div class="p-5 rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center gap-3 flex-wrap mb-5">
            <button type="button" id="erp-custom-save-btn"
                    class="primary-button px-5 py-2.5 text-sm font-semibold rounded-lg transition-all">
                Save Settings
            </button>
            <button type="button" id="erp-custom-test-btn"
                    class="px-5 py-2.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                Test Connection
            </button>
            <span id="erp-test-status" class="text-sm font-semibold"></span>
        </div>

        <div>
            <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                ERP Configuration Import
            </label>
            <div id="erp-drop-zone"
                 class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl px-6 py-8 text-center cursor-pointer bg-white dark:bg-gray-950 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-800 transition-all duration-200">
                <div class="text-4xl">📂</div>
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Drag &amp; Drop ERP Config JSON</div>
                <div class="text-xs text-gray-400">or click to upload</div>
                <input type="file" id="erp-config-file" accept=".json" class="hidden">
            </div>
            <div id="erp-upload-status" class="mt-3 text-sm font-semibold"></div>
        </div>
    </div>
</div>

{{-- Separator --}}
<div class="flex items-center gap-3 my-6 px-1">
    <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-300 dark:via-gray-700 to-transparent"></div>
    <div class="flex items-center gap-2 whitespace-nowrap">
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-600 text-white text-[10px] font-bold">1</span>
        <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Test Connection first</span>
        <span class="text-sm text-gray-300 dark:text-gray-600">→</span>
        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-bold">2</span>
        <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">Sync Products</span>
    </div>
    <div class="flex-1 h-px bg-gradient-to-r from-gray-300 dark:from-gray-700 to-transparent"></div>
</div>

{{-- Part 2 — Sync Panel --}}
<div class="mb-4 last:!mb-0 mt-4">
    <div id="erp-sync-panel" class="p-5 rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-800">
        <div class="flex items-center justify-between gap-4 flex-wrap border-b border-gray-200 dark:border-gray-800 pb-4 mb-5">
            <div class="flex items-center gap-3">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Product Synchronization Engine
                </label>
                <span id="sync-lock-status" 
                      class="text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 font-medium">
                    🔒 Locked
                </span>
            </div>
            <span id="erp-sync-badge" class="px-3 py-1 text-xs font-semibold rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">Idle</span>
        </div>

        <div>
            <div class="flex items-center gap-3 flex-wrap mb-4">
                <button type="button" id="erp-sync-btn"
                        class="primary-button px-5 py-2.5 text-sm font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <span id="erp-sync-btn-txt">Sync Products Now</span>
                </button>
                <button type="button" id="erp-sync-cancel-btn"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 hover:border-red-200 dark:hover:border-red-900 transition-all hidden">
                    Cancel Sync
                </button>
                <span id="erp-sync-pct" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hidden"></span>
            </div>

            <div id="erp-sync-progress-wrap" class="w-full bg-gray-200 dark:bg-gray-800 h-2 rounded-full overflow-hidden mb-3 hidden">
                <div id="erp-sync-progress-bar" class="bg-blue-600 dark:bg-blue-500 h-full rounded-full transition-all duration-300 w-0"></div>
            </div>

            <div id="erp-sync-ticker" class="text-xs font-mono text-gray-400 dark:text-gray-500 min-h-[16px] mb-4 hidden"></div>

            <div id="erp-sync-stats" class="grid grid-cols-2 sm:grid-cols-5 gap-4 mt-4 hidden">
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-center shadow-sm">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Total</div>
                    <div id="sp-total" class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">0</div>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-center shadow-sm">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Created</div>
                    <div id="sp-created" class="text-2xl font-bold text-green-600 dark:text-green-400 tracking-tight">0</div>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-center shadow-sm">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Updated</div>
                    <div id="sp-updated" class="text-2xl font-bold text-blue-600 dark:text-blue-400 tracking-tight">0</div>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-center shadow-sm">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Disabled</div>
                    <div id="sp-disabled" class="text-2xl font-bold text-amber-600 dark:text-amber-400 tracking-tight">0</div>
                </div>
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-center shadow-sm">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Failed</div>
                    <div id="sp-failed" class="text-2xl font-bold text-red-600 dark:text-red-400 tracking-tight">0</div>
                </div>
            </div>
            <div id="erp-auto-sync-live-panel" class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 mt-4">
                <div class="flex items-center justify-between gap-3 flex-wrap mb-4 border-b border-gray-100 dark:border-gray-900/30 pb-3">
                    <div>
                        <div class="text-sm font-semibold">Auto Sync Status</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Live scheduler status and latest automatic report</div>
                    </div>
                    <span id="erp-auto-sync-status-badge" class="text-xs px-2.5 py-1 rounded-full font-semibold bg-gray-100 text-gray-600 dark:bg-gray-850 dark:text-gray-400">
                        Loading
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="p-3 border border-gray-100 dark:border-gray-900 rounded-lg bg-gray-50/50 dark:bg-gray-900/20">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Auto Sync</div>
                        <div id="erp-auto-enabled" class="text-sm font-bold text-gray-800 dark:text-gray-200">Loading</div>
                    </div>
                    <div class="p-3 border border-gray-100 dark:border-gray-900 rounded-lg bg-gray-50/50 dark:bg-gray-900/20">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Selected Interval</div>
                        <div id="erp-auto-interval" class="text-sm font-bold text-gray-800 dark:text-gray-200">Loading</div>
                    </div>
                    <div class="p-3 border border-gray-100 dark:border-gray-900 rounded-lg bg-gray-50/50 dark:bg-gray-900/20">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Last Sync Time</div>
                        <div id="erp-auto-last-sync" class="text-sm font-bold text-gray-800 dark:text-gray-200">Never</div>
                    </div>
                    <div class="p-3 border border-gray-100 dark:border-gray-900 rounded-lg bg-gray-50/50 dark:bg-gray-900/20">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Next Sync Time</div>
                        <div id="erp-auto-next-sync" class="text-sm font-bold text-gray-800 dark:text-gray-200">Unknown</div>
                    </div>
                    <div class="p-3 border border-gray-100 dark:border-gray-900 rounded-lg bg-gray-50/50 dark:bg-gray-900/20">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Countdown</div>
                        <div id="erp-auto-countdown" class="text-sm font-bold text-blue-600 dark:text-blue-400">--:--</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-4">
                    <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-900 bg-gray-50/50 dark:bg-gray-900/20 text-center">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Total</div>
                        <div id="erp-auto-total" class="text-lg font-bold text-gray-800 dark:text-gray-200">0</div>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-900 bg-gray-50/50 dark:bg-gray-900/20 text-center">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Created</div>
                        <div id="erp-auto-created" class="text-lg font-bold text-green-600 dark:text-green-500">0</div>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-900 bg-gray-50/50 dark:bg-gray-900/20 text-center">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Updated</div>
                        <div id="erp-auto-updated" class="text-lg font-bold text-blue-600 dark:text-blue-500">0</div>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-900 bg-gray-50/50 dark:bg-gray-900/20 text-center">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Disabled</div>
                        <div id="erp-auto-disabled" class="text-lg font-bold text-amber-600 dark:text-amber-500">0</div>
                    </div>
                    <div class="p-3 rounded-lg border border-gray-100 dark:border-gray-900 bg-gray-50/50 dark:bg-gray-900/20 text-center">
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-medium">Failed</div>
                        <div id="erp-auto-failed" class="text-lg font-bold text-red-600 dark:text-red-500">0</div>
                    </div>
                </div>

                <div id="erp-auto-message" class="mt-3 text-xs text-gray-500 dark:text-gray-400 hidden"></div>
                <div id="erp-auto-errors" class="mt-3 max-h-[120px] overflow-y-auto text-[11px] font-mono border border-red-200/50 dark:border-red-950/50 bg-red-50/20 dark:bg-red-950/5 p-2 rounded-lg text-red-600 dark:text-red-400 hidden"></div>
            </div>
            <div class="mt-3 flex items-center justify-end gap-3">
                <span id="erp-auto-save-status" class="text-sm font-semibold"></span>
                <button type="button" id="erp-auto-save-btn"
                        class="primary-button px-5 py-2.5 text-sm font-semibold rounded-lg transition-all">
                    Save Auto Sync Settings
                </button>
            </div>

                 <!-- Auto Sync Background Job Logs -->
        @php
            $lastSyncJson = core()->getConfigData('erp.settings.general.last_sync_info');
            $lastSyncInfo = $lastSyncJson ? json_decode($lastSyncJson, true) : null;
        @endphp


            <div id="erp-sync-errors" class="mt-4 max-h-[160px] overflow-y-auto rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50/50 dark:bg-red-950/10 p-3 hidden"></div>
            <div id="erp-sync-result" class="mt-4 text-sm font-semibold hidden"></div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const $ = id => document.getElementById(id);

    let isConnected = false;

    // Helpers
    const csrf = () => {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.content : '{{ csrf_token() }}';
    };

    const jsonHeaders = () => ({
        'X-CSRF-TOKEN': csrf(),
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    });

    function updateSyncPanelState(success) {
        isConnected = success;
        const syncBtn = $('erp-sync-btn');
        const lockStatus = $('sync-lock-status');

        if (success) {
            syncBtn.disabled = false;
            lockStatus.innerHTML = '✅ Unlocked';
            lockStatus.className = 'text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 font-medium';
            localStorage.setItem('erp_connection_success', 'true');
        } else {
            syncBtn.disabled = true;
            lockStatus.innerHTML = '🔒 Locked';
            lockStatus.className = 'text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 font-medium';
        }
    }

    // Load saved state
    if (localStorage.getItem('erp_connection_success') === 'true') {
        updateSyncPanelState(true);
    }

    let nextAutoSyncAt = null;
    let lastAutoSyncTargetKey = null;

    function formatDateTime(value) {
        if (!value) return 'Never';

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) return 'Never';

        return date.toLocaleString();
    }

    function setText(id, value) {
        const el = $(id);
        if (el) el.textContent = value;
    }

    function setAutoStatusBadge(status) {
        const badge = $('erp-auto-sync-status-badge');
        if (!badge) return;

        const normalized = status || 'idle';
        const labels = {
            running: 'Running',
            success: 'Success',
            error: 'Error',
            idle: 'Idle',
        };

        badge.textContent = labels[normalized] || normalized;

        const classes = {
            running: 'text-xs px-2.5 py-1 rounded-full font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            success: 'text-xs px-2.5 py-1 rounded-full font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            error: 'text-xs px-2.5 py-1 rounded-full font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            idle: 'text-xs px-2.5 py-1 rounded-full font-semibold bg-gray-100 text-gray-600 dark:bg-gray-850 dark:text-gray-400',
        };

        badge.className = classes[normalized] || classes.idle;
    }

    function estimateNextSyncAt(interval) {
        const date = new Date();

        switch (String(interval || '6')) {
            case 'test-1':
                date.setMinutes(date.getMinutes() + 1);
                break;

            case 'test-2':
                date.setMinutes(date.getMinutes() + 2);
                break;

            case '1':
                date.setHours(date.getHours() + 1);
                break;

            case '3':
                date.setHours(date.getHours() + 3);
                break;

            case '12':
                date.setHours(date.getHours() + 12);
                break;

            case '24':
                date.setDate(date.getDate() + 1);
                break;

            case '6':
            default:
                date.setHours(date.getHours() + 6);
                break;
        }

        return date;
    }

    function updateAutoCountdown() {
        const countdown = $('erp-auto-countdown');
        if (!countdown) return;

        if (!nextAutoSyncAt) {
            countdown.textContent = '--:--';
            return;
        }

        const diff = nextAutoSyncAt.getTime() - Date.now();

        if (diff <= 0) {
            countdown.textContent = 'Due now';
            return;
        }

        const totalSeconds = Math.floor(diff / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        countdown.textContent = hours > 0
            ? `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
            : `${minutes}:${String(seconds).padStart(2, '0')}`;
    }

    function updateAutoErrors(errors) {
        const errorsEl = $('erp-auto-errors');
        if (!errorsEl) return;

        errorsEl.innerHTML = '';

        if (!Array.isArray(errors) || errors.length === 0) {
            errorsEl.classList.add('hidden');
            return;
        }

        errors.forEach(error => {
            const row = document.createElement('div');
            row.textContent = typeof error === 'string'
                ? error
                : `${error.sku || 'SKU'}: ${error.error || error.message || 'Unknown error'}`;
            errorsEl.appendChild(row);
        });

        errorsEl.classList.remove('hidden');
    }

    function updateAutoSyncPanel(data) {
        setAutoStatusBadge(data.status);
        setText('erp-auto-enabled', data.auto_sync_enabled ? 'Enabled' : 'Disabled');
        setText('erp-auto-interval', data.interval_label || data.interval || 'Not selected');
        setText('erp-auto-last-sync', formatDateTime(data.last_sync_at || data.finished_at || data.timestamp));
        setText('erp-auto-total', data.total || 0);
        setText('erp-auto-created', data.created || 0);
        setText('erp-auto-updated', data.updated || 0);
        setText('erp-auto-disabled', data.disabled || 0);
        setText('erp-auto-failed', data.failed || 0);

        if (!data.auto_sync_enabled) {
            nextAutoSyncAt = null;
            lastAutoSyncTargetKey = null;
            setText('erp-auto-next-sync', 'Disabled');
        } else if (data.next_sync_at) {
            const targetKey = `${data.next_sync_at}|${data.status || ''}|${data.finished_at || ''}|${data.last_sync_at || ''}`;

            if (targetKey !== lastAutoSyncTargetKey) {
                nextAutoSyncAt = new Date(data.next_sync_at);
                lastAutoSyncTargetKey = targetKey;
            }

            setText('erp-auto-next-sync', formatDateTime(nextAutoSyncAt));
        } else {
            const targetKey = `local-estimate|${data.interval || '6'}`;

            if (!nextAutoSyncAt || targetKey !== lastAutoSyncTargetKey) {
                nextAutoSyncAt = estimateNextSyncAt(data.interval);
                lastAutoSyncTargetKey = targetKey;
            }

            setText('erp-auto-next-sync', formatDateTime(nextAutoSyncAt));
        }

        const messageEl = $('erp-auto-message');
        if (messageEl) {
            if (data.message) {
                messageEl.textContent = data.message;
                messageEl.classList.remove('hidden');
            } else {
                messageEl.textContent = '';
                messageEl.classList.add('hidden');
            }
        }

        updateAutoErrors(data.errors || []);
        updateAutoCountdown();
    }

    function getAutoSyncEnabledValue() {
        const checkbox = document.querySelector('input[type="checkbox"][name*="auto_sync_enabled"]');

        if (checkbox) {
            return checkbox.checked ? 1 : 0;
        }

        const input = document.querySelector('input[name*="auto_sync_enabled"]');

        return input && ['1', 'true', 'on'].includes(String(input.value).toLowerCase()) ? 1 : 0;
    }

    function getAutoSyncIntervalValue() {
        const select = document.querySelector('select[name*="auto_sync_interval"]');
        const input = select || document.querySelector('input[name*="auto_sync_interval"]');

        return input ? input.value : '6';
    }

    async function refreshAutoSyncStatus() {
        try {
            const response = await fetch('{{ route("admin.erp.sync.status") }}', {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) return;

            updateAutoSyncPanel(await response.json());
        } catch (error) {
            setAutoStatusBadge('error');
        }
    }

    refreshAutoSyncStatus();
    setInterval(refreshAutoSyncStatus, 10000);
    setInterval(updateAutoCountdown, 1000);

    // Connection Panel
    (function setupConnectionPanel() {
        const dropZone = $('erp-drop-zone');
        const fileInput = $('erp-config-file');
        const uploadStatus = $('erp-upload-status');

        if (dropZone && fileInput) {
            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.style.borderColor = '#2563eb';
            });
            dropZone.addEventListener('dragleave', () => dropZone.style.borderColor = '');
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.style.borderColor = '';
                const file = e.dataTransfer.files[0];
                if (file) handleJsonFile(file);
            });
            fileInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) handleJsonFile(file);
            });
        }

        function handleJsonFile(file) {
            if (!file.name.endsWith('.json')) {
                if (uploadStatus) uploadStatus.innerHTML = '❌ Invalid JSON file';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (event) {
                try {
                    const config = JSON.parse(event.target.result);
                    setField('backend_url', config.backend_url);
                    setField('erp_token', config.erp_token);
                    setField('keycloak_token_url', config.keycloak_token_url);
                    setField('keycloak_client_id', config.keycloak_client_id);
                    setField('keycloak_username', config.keycloak_username);
                    setField('keycloak_password', config.keycloak_password);
                    if (uploadStatus) uploadStatus.innerHTML = '✅ Config imported';
                } catch (e) {
                    if (uploadStatus) uploadStatus.innerHTML = '❌ Invalid JSON structure';
                }
            };
            reader.readAsText(file);
        }

        function setField(name, value) {
            const input = document.querySelector(`input[name*="${name}"]`);
            if (input && value !== undefined) {
                input.value = value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    })();

    // Save Button
    document.addEventListener('click', function (event) {
        if (event.target.closest('#erp-custom-save-btn')) {
            event.preventDefault();
            const submitBtn = document.querySelector("form button[type='submit']") || document.querySelector(".primary-button[type='submit']");
            if (submitBtn) submitBtn.click();
            else document.querySelector("form")?.submit();
        }
    });

    document.addEventListener('click', async function (event) {
        const saveBtn = event.target.closest('#erp-auto-save-btn');
        if (!saveBtn) return;

        event.preventDefault();

        const status = $('erp-auto-save-status');
        const originalText = saveBtn.textContent;

        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        if (status) {
            status.textContent = 'Saving auto sync settings...';
            status.style.color = '#4b5563';
        }

        try {
            const response = await fetch(`{{ route("admin.erp.sync.auto-settings.save") }}${window.location.search || ''}`, {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({
                    auto_sync_enabled: getAutoSyncEnabledValue(),
                    auto_sync_interval: getAutoSyncIntervalValue(),
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save auto sync settings.');
            }

            nextAutoSyncAt = null;
            lastAutoSyncTargetKey = null;

            if (status) {
                status.textContent = data.message || 'Auto sync settings saved.';
                status.style.color = '#16a34a';
            }

            await refreshAutoSyncStatus();
        } catch (error) {
            if (status) {
                status.textContent = error.message;
                status.style.color = '#dc2626';
            }
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    });

    // ==================== TEST BUTTON (FIXED) ====================
    document.addEventListener('click', function (event) {
        const testBtn = event.target.closest('#erp-custom-test-btn');
        if (!testBtn) return;

        event.preventDefault();

        const urlEl           = document.querySelector('input[name*="backend_url"]') || document.getElementById('erp[settings][general][backend_url]');
        const tokenEl         = document.querySelector('input[name*="erp_token"]') || document.getElementById('erp[settings][general][erp_token]');
        const keycloakUrlEl   = document.querySelector('input[name*="keycloak_token_url"]');
        const keycloakClientEl= document.querySelector('input[name*="keycloak_client_id"]');
        const keycloakUserEl  = document.querySelector('input[name*="keycloak_username"]');
        const keycloakPassEl  = document.querySelector('input[name*="keycloak_password"]');

        const statusEl = $('erp-test-status');

        const backendUrl       = urlEl ? urlEl.value.trim() : '';
        const erpToken         = tokenEl ? tokenEl.value.trim() : '';
        const keycloakTokenUrl = keycloakUrlEl ? keycloakUrlEl.value.trim() : '';
        const keycloakClientId = keycloakClientEl ? keycloakClientEl.value.trim() : '';
        const keycloakUsername = keycloakUserEl ? keycloakUserEl.value.trim() : '';
        const keycloakPassword = keycloakPassEl ? keycloakPassEl.value.trim() : '';

        if (!backendUrl || !erpToken || !keycloakTokenUrl || !keycloakClientId || !keycloakUsername || !keycloakPassword) {
            alert("Please fill out all the connection details first.");
            if (statusEl) {
                statusEl.innerText = "⚠️ Missing Inputs";
                statusEl.style.color = "#d97706";
            }
            return;
        }

        const originalText = testBtn.innerText;
        testBtn.innerText = "Testing...";
        testBtn.disabled = true;

        if (statusEl) {
            statusEl.innerText = "⏳ Connecting...";
            statusEl.style.color = "#4b5563";
        }

        fetch("{{ route('admin.erp.connection.run') }}", {
            method: "POST",
            headers: jsonHeaders(),
            body: JSON.stringify({
                backend_url: backendUrl,
                erp_token: erpToken,
                keycloak_token_url: keycloakTokenUrl,
                keycloak_client_id: keycloakClientId,
                keycloak_username: keycloakUsername,
                keycloak_password: keycloakPassword,
            }),
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (statusEl) {
                statusEl.innerText = (data.status === "success") ? "✅ Success" : "❌ Failed";
                statusEl.style.color = (data.status === "success") ? "#16a34a" : "#dc2626";
            }

            // Unlock sync panel if test is successful
            updateSyncPanelState(data.status === "success");

            testBtn.innerText = originalText;
            testBtn.disabled = false;
        })
        .catch(error => {
            alert("Error: " + error.message);
            if (statusEl) {
                statusEl.innerText = "⚠️ Error";
                statusEl.style.color = "#d97706";
            }
            updateSyncPanelState(false);
            testBtn.innerText = originalText;
            testBtn.disabled = false;
        });
    });
  // ── Sync Panel Logic ───────────────────────────────────────────────────
    let syncRunning   = false;
    let cancelRequest = false;
    let counts        = { total: 0, created: 0, updated: 0, failed: 0, disabled: 0 };

    function setBadge(text, cls) {
        const b = $('erp-sync-badge');
        if (!b) return;
        b.textContent = text;
        b.className   = cls;
    }

    function setProgress(done, total) {
        const pct = total > 0 ? Math.round((done / total) * 100) : 100;
        const bar = $('erp-sync-progress-bar');
        if (bar) bar.style.width = pct + '%';
        const p = $('erp-sync-pct');
        if (p) p.textContent = pct + '%';
    }

    function resetCounts(total) {
        counts = { total, created: 0, updated: 0, failed: 0, disabled: 0 };
        ['total','created','updated','failed','disabled'].forEach(k => {
            const el = $('sp-' + k);
            if (el) el.textContent = (k === 'total') ? total : 0;
        });
    }

    function incCount(key) {
        counts[key] = (counts[key] || 0) + 1;
        const el = $('sp-' + key);
        if (el) el.textContent = counts[key];
    }

    function setDisabledCount(count) {
        counts.disabled = count;
        const el = $('sp-disabled');
        if (el) el.textContent = count;
    }

    function addError(sku, msg) {
        const list = $('erp-sync-errors');
        if (!list) return;
        list.classList.remove('hidden');
        const row = document.createElement('div');
        row.className = 'sp-error-item text-red-600 dark:text-red-400 py-1';
        row.textContent = `✗ ${sku}: ${msg}`;
        list.appendChild(row);
    }

    function showEl(id) {
        const e = $('erp-sync-' + id);
        if (e) e.classList.remove('hidden');
    }

    function hideEl(id) {
        const e = $('erp-sync-' + id);
        if (e) e.classList.add('hidden');
    }

    function setTicker(t) {
        const e = $('erp-sync-ticker');
        if (e) e.textContent = t;
    }

    // Cancel
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#erp-sync-cancel-btn')) return;
        cancelRequest = true;
        setBadge('Cancelling…', 'sp-badge-warn');
    });

    // Sync Button
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#erp-sync-btn')) return;
        e.preventDefault();
        if (!syncRunning) startSync();
    });

    async function startSync() {
        syncRunning   = true;
        cancelRequest = false;

        const btn = $('erp-sync-btn');
        if (btn) btn.disabled = true;
        const btnTxt = $('erp-sync-btn-txt');
        if (btnTxt) btnTxt.textContent = '⏳ Syncing…';

        ['progress-wrap','pct','ticker','stats'].forEach(showEl);
        hideEl('result');

        const errorsEl = $('erp-sync-errors');
        if (errorsEl) {
            errorsEl.classList.add('hidden');
            errorsEl.innerHTML = '';
        }

        setProgress(0, 1);
        setBadge('Running', 'sp-badge-running');
        setTicker('Initializing…');

        try {
            const initRes = await fetch('{{ route("admin.erp.sync.init") }}', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({})
            });

            if (!initRes.ok) throw new Error('Init failed');
            const initData = await initRes.json();

            const toSync    = initData.to_sync    || [];
            const toDisable = initData.to_disable || [];
            const total     = toSync.length;

            resetCounts(total);

            // === IMPROVED HANDLING FOR NO PRODUCTS CASE ===
            if (total === 0) {
                setProgress(100, 100);
                setTicker('Finalizing…');

                // Still run finalize (important for disabling products)
                const fRes = await fetch('{{ route("admin.erp.sync.finalize") }}', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({
                        processed_skus: [],
                        to_disable: toDisable
                    })
                });

                const fData = await fRes.json();
                const disabledCount = fData.disabled_count || 0;

                setDisabledCount(disabledCount);

                if (disabledCount > 0) {
                    finishSync(`✅ Done! ${disabledCount} product(s) disabled.`, 'sp-badge-success');
                } else {
                    finishSync('✅ Everything is up to date. No changes needed.', 'sp-badge-success');
                }
                return;
            }

            // Normal case: products to sync
            const processed = [];
            for (let i = 0; i < toSync.length; i++) {
                if (cancelRequest) break;

                const sku = toSync[i];
                setTicker(`Processing: ${sku} (${i + 1} / ${total})`);
                setProgress(i, total);

                try {
                    const r = await fetch('{{ route("admin.erp.sync.sku") }}', {
                        method: 'POST',
                        headers: jsonHeaders(),
                        body: JSON.stringify({ sku })
                    });

                    const data = await r.json();

                    if (data.success) {
                        processed.push(sku);
                        incCount(data.status || 'updated');
                    } else {
                        incCount('failed');
                        addError(sku, data.message || 'Unknown error');
                    }
                } catch (err) {
                    incCount('failed');
                    addError(sku, err.message);
                }

                await new Promise(r => setTimeout(r, 200)); // prevent server overload
            }

            setProgress(total, total);
            setTicker('Finalizing…');

            // Finalize
            if (!cancelRequest) {
                const fRes = await fetch('{{ route("admin.erp.sync.finalize") }}', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ processed_skus: processed, to_disable: toDisable })
                });
                const fData = await fRes.json();
                const disabledCount = fData.disabled_count || 0;
                setDisabledCount(disabledCount);
            }

            // Final Status
            if (cancelRequest) {
                finishSync('⚠️ Sync was cancelled.', 'sp-badge-warn');
            } else if (counts.failed > 0) {
                finishSync(`⚠️ Partial sync — ${counts.failed} error(s).`, 'sp-badge-warn');
            } else {
                finishSync(`✅ Sync complete! ${counts.created} created, ${counts.updated} updated.`, 'sp-badge-success');
            }

        } catch (err) {
            finishSync('❌ ' + err.message, 'sp-badge-error');
        } finally {
            syncRunning = false;
            const btn = $('erp-sync-btn');
            if (btn) btn.disabled = false;
            const btnTxt = $('erp-sync-btn-txt');
            if (btnTxt) btnTxt.textContent = '🚀 Sync Products Now';
        }
    }

    function finishSync(msg, badgeCls) {
        const btn = $('erp-sync-btn');
        if (btn) btn.disabled = false;
        const btnTxt = $('erp-sync-btn-txt');
        if (btnTxt) btnTxt.textContent = '🚀 Sync Products Now';

        const cancelBtn = $('erp-sync-cancel-btn');
        if (cancelBtn) cancelBtn.style.display = 'none';

        setBadge(
            badgeCls === 'sp-badge-success' ? 'Done ✓' :
            badgeCls === 'sp-badge-error'   ? 'Error'  : 'Partial',
            badgeCls
        );

        const result = $('erp-sync-result');
        if (result) {
            result.textContent = msg;
            result.style.color = badgeCls === 'sp-badge-success' ? '#15803d' :
                                badgeCls === 'sp-badge-error' ? '#dc2626' : '#b45309';
            result.style.display = '';
        }
    }

        // ==================== FINAL AUTO SYNC FIX ====================
  

    

})();
</script>
