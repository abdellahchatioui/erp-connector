<div class="mb-4 last:!mb-0 mt-4">
    <div class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-900 dark:border-gray-800">
        <!-- Save Settings Button -->
        <button 
            type="button" 
            id="erp-custom-save-btn" 
            class="primary-button px-4 py-2 text-sm font-semibold rounded-md transition-all"
        >
            Save Settings
        </button>

        <!-- Test Connection Button -->
        <button 
            type="button" 
            id="erp-custom-test-btn" 
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800 px-4 py-2 text-sm font-semibold rounded-md border border-gray-300 dark:border-gray-700 transition-all"
        >
            Test Connection
        </button>

        <!-- Test Status Message -->
        <span 
            id="erp-test-status" 
            class="text-sm font-semibold"
        ></span>
    </div>
</div>

<script>
(function () {
    // Using global event delegation so that Vue mounting does not destroy/unbind our event listeners
    document.addEventListener("click", function (event) {
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
            const statusEl = document.getElementById("erp-test-status");

            const backendUrl = urlEl ? urlEl.value.trim() : '';
            const erpToken = tokenEl ? tokenEl.value.trim() : '';

            if (!backendUrl || !erpToken) {
                alert("Please enter both the ERP Backend URL and Token first.");
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
                    erp_token: erpToken
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
