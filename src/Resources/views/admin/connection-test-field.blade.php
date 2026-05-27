<div class="mb-4 last:!mb-0 mt-4">
    <div class="p-5 rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-800">

        <!-- Buttons Row -->
        <div class="flex items-center gap-3 flex-wrap mb-5">

            <!-- Save -->
            <button 
                type="button" 
                id="erp-custom-save-btn" 
                class="primary-button px-5 py-2.5 text-sm font-semibold rounded-lg transition-all"
            >
                Save Settings
            </button>

            <!-- Test -->
            <button 
                type="button" 
                id="erp-custom-test-btn" 
                class="px-5 py-2.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
            >
                Test Connection
            </button>

            <!-- Status -->
            <span 
                id="erp-test-status" 
                class="text-sm font-semibold"
            ></span>

        </div>

        <!-- Upload Section -->
        <div>
            <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                ERP Configuration Import
            </label>

            <div 
                id="erp-drop-zone"
                class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl px-6 py-8 text-center cursor-pointer bg-white dark:bg-gray-950 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-gray-800 transition-all duration-200"
            >
                <div class="text-4xl">📂</div>

                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Drag & Drop ERP Config JSON
                </div>

                <div class="text-xs text-gray-400">
                    or click to upload
                </div>

                <input 
                    type="file" 
                    id="erp-config-file" 
                    accept=".json" 
                    class="hidden"
                >
            </div>

            <div 
                id="erp-upload-status" 
                class="mt-3 text-sm font-semibold"
            ></div>
        </div>

    </div>
</div>

<script>
(function () {
    // Using global event delegation so that Vue mounting does not destroy/unbind our event listeners
    document.addEventListener("click", function (event) {
        const dropZone = document.getElementById('erp-drop-zone');
const fileInput = document.getElementById('erp-config-file');
const uploadStatus = document.getElementById('erp-upload-status');

if (dropZone && fileInput) {

    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#2563eb';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#cbd5e1';
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#cbd5e1';

        const file = e.dataTransfer.files[0];

        if (file) {
            handleJsonFile(file);
        }
    });

    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];

        if (file) {
            handleJsonFile(file);
        }
    });
}

function handleJsonFile(file) {

    if (!file.name.endsWith('.json')) {
        uploadStatus.innerHTML = '❌ Invalid JSON file';
        uploadStatus.style.color = 'red';
        return;
    }

    const reader = new FileReader();

    reader.onload = function(event) {

        try {

            const config = JSON.parse(event.target.result);

            setField('backend_url', config.backend_url);
            setField('erp_token', config.erp_token);
            setField('keycloak_token_url', config.keycloak_token_url);
            setField('keycloak_client_id', config.keycloak_client_id);
            setField('keycloak_username', config.keycloak_username);
            setField('keycloak_password', config.keycloak_password);

            uploadStatus.innerHTML = '✅ Config imported';
            uploadStatus.style.color = 'green';

        } catch (e) {

            uploadStatus.innerHTML = '❌ Invalid JSON structure';
            uploadStatus.style.color = 'red';
        }
    };

    reader.readAsText(file);
}

function setField(fieldName, value) {

    const input = document.querySelector(`input[name*="${fieldName}"]`);

    if (input && value) {
        input.value = value;

        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
        const saveBtn = event.target.closest('#erp-custom-save-btn');
        const testBtn = event.target.closest('#erp-custom-test-btn');

        if (saveBtn) {
            event.preventDefault();
            // Find the native save/submit button inside the config page header
            const submitBtn = document.querySelector("form button[type='submit']") || document.querySelector(".primary-button[type='submit']");
            if (submitBtn) {
                submitBtn.click();
            } else {
                const form = document.querySelector("form");
                if (form) {
                    form.submit();
                }
            }
        }

        if (testBtn) {
            event.preventDefault();
            
            // Dynamically select the input values from the DOM
            const urlEl = document.querySelector('input[name*="backend_url"]') || document.getElementById('erp[settings][general][backend_url]');
            const tokenEl = document.querySelector('input[name*="erp_token"]') || document.getElementById('erp[settings][general][erp_token]');
            const keycloakUrlEl = document.querySelector('input[name*="keycloak_token_url"]');
            const keycloakClientIdEl = document.querySelector('input[name*="keycloak_client_id"]');
            const keycloakUsernameEl = document.querySelector('input[name*="keycloak_username"]');
            const keycloakPasswordEl = document.querySelector('input[name*="keycloak_password"]');
            
            const statusEl = document.getElementById("erp-test-status");

            const backendUrl = urlEl ? urlEl.value.trim() : '';
            const erpToken = tokenEl ? tokenEl.value.trim() : '';
            const keycloakTokenUrl = keycloakUrlEl ? keycloakUrlEl.value.trim() : '';
            const keycloakClientId = keycloakClientIdEl ? keycloakClientIdEl.value.trim() : '';
            const keycloakUsername = keycloakUsernameEl ? keycloakUsernameEl.value.trim() : '';
            const keycloakPassword = keycloakPasswordEl ? keycloakPasswordEl.value.trim() : '';

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
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    backend_url: backendUrl,
                    erp_token: erpToken,
                    keycloak_token_url: keycloakTokenUrl,
                    keycloak_client_id: keycloakClientId,
                    keycloak_username: keycloakUsername,
                    keycloak_password: keycloakPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (statusEl) {
                    statusEl.innerText = (data.status === "success") ? "✅ Success" : "❌ Failed";
                    statusEl.style.color = (data.status === "success") ? "#16a34a" : "#dc2626";
                }
                testBtn.innerText = originalText;
                testBtn.disabled = false;
            })
            .catch(error => {
                alert("Error: " + error.message);
                if (statusEl) {
                    statusEl.innerText = "⚠️ Error";
                    statusEl.style.color = "#d97706";
                }
                testBtn.innerText = originalText;
                testBtn.disabled = false;
            });
        }
    });
})();
</script>
