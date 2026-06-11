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

         <!-- Auto Sync Panel -->
<!-- Auto Sync Panel Component -->
<div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 mt-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-sm font-semibold">Auto Sync</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Automatically sync products at regular intervals</div>
        </div>
    </div>

    <div id="auto-sync-settings">
        <div class="text-xs uppercase tracking-wider text-gray-500 mb-2">Sync Every</div>
        <select id="auto-sync-interval" class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm">
            <!-- Simplified Intervals -->
            <option value="test-1">1 Minute (Testing)</option>
            <option value="1">Every 1 hour</option>
            <option value="3">Every 3 hours</option>
            <option value="6" selected>Every 6 hours</option>
            <option value="12">Every 12 hours</option>
            <option value="24">Every 24 hours (Daily)</option>
        </select>
        
        <button type="button" 
                id="save-auto-sync-btn" 
                onclick="executeAutoSyncSave()"
                class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors text-sm">
            Save Auto Sync Settings
        </button>
    </div>
    
    <!-- Visual Status & Timestamp Fields -->
    <div id="auto-sync-status" class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center"></div>
    <div id="last-sync-timestamp" class="mt-1 text-xs text-gray-400 dark:text-gray-500 text-center font-medium"></div>
    <div id="sync-countdown" class="mt-1 text-xs text-blue-500 text-center font-mono"></div>
</div>

<script>
    let activeSyncTimer = null;

    // 1. THE AUTOMATED TRIGGER ENGINE
    function triggerProductSync() {
        const realSyncBtn = document.getElementById('erp-sync-btn');
        const timestampEl = document.getElementById('last-sync-timestamp');
        
        if (!realSyncBtn) {
            console.error("❌ [Auto Sync Engine] Could not locate your manual sync target button: #erp-sync-btn");
            return;
        }

        console.log("🔄 [Auto Sync Engine] Timer expired! Unlocking and executing real sync pipeline...");

        // Remove the framework block attribute so the browser permits clicks
        realSyncBtn.disabled = false;
        
        // Simulate an authentic programmatic mouse click to wake up your system's real scripts
        realSyncBtn.click();

        // Capture and display the timestamp immediately
        if (timestampEl) {
            const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            timestampEl.innerHTML = `🕒 Last auto-sync: <span class="text-gray-700 dark:text-gray-300 font-semibold">${currentTime}</span>`;
            
            // Save last execution timestamp to storage so it survives page reloads
            localStorage.setItem('erp_last_sync_time', currentTime);
        }
    }

    // 2. TIMING MONITOR LOOP
    function startBackgroundEngine() {
        if (activeSyncTimer) clearInterval(activeSyncTimer);

        const saved = localStorage.getItem('erp_auto_sync');
        if (!saved) return;

        try {
            const data = JSON.parse(saved);
            if (!data.enabled || !data.interval_minutes) return;

            const intervalMs = data.interval_minutes * 60 * 1000;
            let timeRemainingMs = intervalMs;

            const countdownEl = document.getElementById('sync-countdown');

            activeSyncTimer = setInterval(() => {
                timeRemainingMs -= 1000;

                if (countdownEl) {
                    const totalSeconds = Math.max(0, Math.floor(timeRemainingMs / 1000));
                    const mins = Math.floor(totalSeconds / 60);
                    const secs = totalSeconds % 60;
                    countdownEl.innerText = `Next automatic sync in: ${mins}m ${secs}s`;
                }

                if (timeRemainingMs <= 0) {
                    triggerProductSync();
                    timeRemainingMs = intervalMs; // Reset and loop again
                }
            }, 1000);

        } catch (e) {
            console.error("Engine failed to loop properly:", e);
        }
    }

    // 3. PERSIST DESIGN CONFIGURATIONS
    function executeAutoSyncSave() {
        const intervalSelect = document.getElementById('auto-sync-interval');
        const statusEl = document.getElementById('auto-sync-status');

        if (!intervalSelect) return;

        const selectedValue = intervalSelect.value;
        let minutes = 360;
        let labelText = "";

        if (selectedValue === 'test-1') {
            minutes = 1;
            labelText = `1 minute (Testing Mode)`;
        } else {
            const hours = parseInt(selectedValue) || 6;
            minutes = hours * 60;
            labelText = `${hours} hour${hours > 1 ? 's' : ''}`;
        }

        const payload = {
            enabled: true,
            interval_minutes: minutes,
            raw_value: selectedValue,
            updated_at: new Date().toISOString()
        };

        localStorage.setItem('erp_auto_sync', JSON.stringify(payload));

        if (statusEl) {
            statusEl.innerHTML = `✅ Auto Sync linked: active every ${labelText}`;
            statusEl.style.color = '#16a34a';
        }

        startBackgroundEngine();
        alert(`✅ Auto Sync Saved & Linked!\n\nWill trigger your 'Sync Products Now' button script every ${labelText}.`);
    }

    // 4. WORKSPACE AUTOMATION ON STARTUP
    (function initOnLoad() {
        const saved = localStorage.getItem('erp_auto_sync');
        const lastSyncTime = localStorage.getItem('erp_last_sync_time');
        const timestampEl = document.getElementById('last-sync-timestamp');

        // Restore saved interval selection dropdown state
        if (saved) {
            try {
                const data = JSON.parse(saved);
                const intervalSelect = document.getElementById('auto-sync-interval');
                if (intervalSelect && data.raw_value) {
                    intervalSelect.value = data.raw_value;
                }
            } catch(e) {}
        }

        // Restore previous execution timestamp value on page reload
        if (lastSyncTime && timestampEl) {
            timestampEl.innerHTML = `🕒 Last auto-sync: <span class="text-gray-700 dark:text-gray-300 font-semibold">${lastSyncTime}</span>`;
        }
        
        startBackgroundEngine();
    })();
</script>




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