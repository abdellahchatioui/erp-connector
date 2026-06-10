<?php

return [
    'admin' => [
        'system' => [
            'erp-connector' => 'ERP Connector',
            'settings' => 'Settings',
            'general' => 'General',
            'backend-url' => 'ERP Backend URL',
            'erp-token' => 'ERP Token',
            'keycloak-token-url' => 'Keycloak Token URL',
            'keycloak-client-id' => 'Keycloak Client ID',
            'keycloak-username' => 'Keycloak Username',
            'keycloak-password' => 'Keycloak Password',
            'info' => 'Enter your ERP backend connection details.',
            'info_with_button' => '<span id="erp-info-text">Enter your ERP backend connection details.</span><br><br><div style="display: inline-flex; gap: 10px; align-items: center; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;"><button type="button" id="erp-custom-save-btn" style="padding: 8px 16px; background-color: #060c3b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Save Settings</button><button type="button" id="erp-custom-test-btn" style="padding: 8px 16px; background-color: white; color: #060c3b; border: 1px solid #060c3b; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">Test Connection</button><span id="erp-test-status" style="font-weight: 600; font-size: 14px; margin-left: 10px;"></span></div><script>setTimeout(function() { const txt = document.getElementById("erp-info-text"); if (txt && txt.parentNode) { txt.parentNode.childNodes.forEach(n => { if (n.nodeType === 3) { n.textContent = n.textContent.replace(/[{}]/g, ""); } }); } }, 100); document.addEventListener("click", function(e) { if (e.target && e.target.id === "erp-custom-save-btn") { const saveBtn = document.querySelector(".page-action button.btn-primary") || document.querySelector(".primary-button") || document.querySelector("button[type=\'submit\']"); if (saveBtn) { saveBtn.click(); } else { const form = document.querySelector("form"); if (form) form.dispatchEvent(new Event("submit", { cancelable: true, bubbles: true })); } } if (e.target && e.target.id === "erp-custom-test-btn") { const btn = e.target; const statusEl = document.getElementById("erp-test-status"); const originalText = btn.innerText; btn.innerText = "Testing..."; btn.disabled = true; if (statusEl) statusEl.innerText = ""; fetch("/admin/erp-connector/connection-test", { method: "POST", headers: { "X-CSRF-TOKEN": document.querySelector(\'meta[name="csrf-token"]\') ? document.querySelector(\'meta[name="csrf-token"]\').content : "", "Content-Type": "application/json", "Accept": "application/json" } }).then(response => response.json()).then(data => { alert(data.message); if (statusEl) { statusEl.innerText = (data.status === "success") ? "✅ Success" : "❌ Failed"; statusEl.style.color = (data.status === "success") ? "#28a745" : "#dc3545"; } btn.innerText = originalText; btn.disabled = false; }).catch(error => { alert("Error: " + error.message); if (statusEl) { statusEl.innerText = "⚠️ Error"; statusEl.style.color = "#ffc107"; } btn.innerText = originalText; btn.disabled = false; }); } });</script>',
            'auto-sync' => 'Auto Sync',
            'error-handling-mode' => 'Error Handling Mode',
            'log-and-continue' => 'Log & Continue',
            'abort-on-error' => 'Abort on Error',
        ],

        'connection' => [
            'title' => 'Test ERP Connection',
            'test' => 'Test Connection',
            'success' => 'Connection established successfully!',
            'failed' => 'Connection failed: :message',
            'checking' => 'Checking connection...',
        ],

        'menu' => [
            'title' => 'ERP Integration',
        ]
    ]
];
