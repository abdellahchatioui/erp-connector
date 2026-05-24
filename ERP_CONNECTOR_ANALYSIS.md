# ErpConnector Package Analysis V-1.4.3

This document provides a comprehensive breakdown of each file and its functionality within the `Webkul\ErpConnector` package. This package facilitates a two-way integration between Bagisto and a Spring Boot ERP system.

## 1. Core Integration Architecture
The integration follows two primary models:
- **Push Model (Order Sync):** Bagisto pushes order data to the ERP whenever a customer completes a checkout.
- **Pull Model (Product Sync):** The ERP notifies Bagisto of product updates via a webhook. Bagisto then "pulls" the full product data from the ERP asynchronously.

---

## 2. File-by-File Explanation

### Entry Points & Registration
| File | Description |
| :--- | :--- |
| **`composer.json`** | Defines the package name, version, and PSR-4 autoloading. It ensures the `Webkul\ErpConnector\` namespace is mapped to the `src/` directory. |
| **`src/Providers/ErpConnectorServiceProvider.php`** | The heart of the package. It registers all components (routes, configs, translations) into the Laravel/Bagisto container. It also includes a security hook to encrypt the ERP token before it hits the database. |
| **`src/Providers/EventServiceProvider.php`** | Manages event-listener mappings. It specifically links the `checkout.order.save.after` event to the `SendOrderToErp` listener. |

### Configuration & Settings
| File | Description |
| :--- | :--- |
| **`src/Config/system.php`** | Defines the "Configuration" menu fields in the Bagisto Admin Panel. It creates the UI for entering the `Backend URL` and `ERP Token`. |
| **`src/Config/erp.php`** | Provides default configuration values and allows overrides via `.env` variables (`ERP_BASE_URL` and `ERP_API_TOKEN`). |
| **`src/Helpers/Config.php`** | A utility class used throughout the package to retrieve settings. It handles the automatic decryption of the ERP token so other classes can use it in plain text for API calls. |

### Routing & Security
| File | Description |
| :--- | :--- |
| **`routes/api.php`** | Defines the external API endpoints that the ERP calls (e.g., `/api/erp/webhook/product`). |
| **`src/Routes/admin.php`** | Defines internal routes used by the Bagisto Admin panel for testing the ERP connection. |
| **`src/Http/Middleware/VerifyErpToken.php`** | A security layer that intercepts incoming API calls from the ERP. It validates the request using the `X-ERP-TOKEN` header to prevent unauthorized access. |

### Business Logic (Controllers & Listeners)
| File | Description |
| :--- | :--- |
| **`src/Http/Controllers/WebhookController.php`** | Handles incoming ERP notifications. When a product sync notification arrives, it validates the SKU and dispatches a background job. |
| **`src/Http/Controllers/Admin/ConnectionController.php`** | Handles the "Test Connection" logic in the admin panel. It attempts to ping the ERP to confirm the settings are correct. |
| **`src/Listeners/SendOrderToErp.php`** | A silent observer that waits for an order to be saved. Once saved, it hands off the order to a background Job. |

### Background Tasks (Jobs)
| File | Description |
| :--- | :--- |
| **`src/Jobs/PushOrderToErpJob.php`** | Transmits order data to the ERP. It maps complex Bagisto order objects (including items and addresses) into a simplified JSON structure for the Spring Boot ERP. |
| **`src/Jobs/SyncProductFromErpJob.php`** | Fetches detailed product data from the ERP using a SKU. It then uses Bagisto's `ProductRepository` to either create a new product or update an existing one. |

### Localization & UI
| File | Description |
| :--- | :--- |
| **`src/Resources/lang/en/app.php`** | Contains all text strings. Notably, it contains an advanced implementation where JavaScript is embedded to dynamically add "Save" and "Test" buttons to the Bagisto Configuration page. |

---

## 3. Workflow Summary

### Order Flow (Bagisto -> ERP)
1. User places order.
2. `EventServiceProvider` catches `checkout.order.save.after`.
3. `SendOrderToErp` listener is triggered.
4. `PushOrderToErpJob` is dispatched to the queue.
5. Job maps data and POSTs to `ERP_URL/api/erp/orders`.

### Product Flow (ERP -> Bagisto)
1. ERP sends POST to `/api/erp/webhook/product` with a SKU.
2. `VerifyErpToken` middleware validates the request.
3. `WebhookController` receives the SKU.
4. `SyncProductFromErpJob` is dispatched.
5. Job fetches data from `ERP_URL/erp/products/sku/{sku}`.
6. Job creates/updates product in Bagisto database.


