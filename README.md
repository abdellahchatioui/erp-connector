# Bagisto ERP Connector

A powerful, secure integration package for [Bagisto](https://bagisto.com/) that enables seamless synchronization between your e-commerce store and an external ERP system.

## Features

- **Admin UI Configuration:** Configure your ERP connection directly from the Bagisto Admin Panel.
- **Secure Token Storage:** API tokens are securely encrypted before being stored in the database.
- **Connectivity Testing:** Built-in "Test Connection" tool to verify your ERP backend is reachable.
- **Product Synchronization:** Receive product updates from your ERP via secure webhooks.
- **Order Synchronization:** Automatically push order details to your ERP via event listeners when an order is placed.
- **Secure Webhooks:** Built-in middleware to verify API tokens for all incoming requests.

## Installation

### 1. Install via Composer

In your Bagisto project root, run:

```bash
composer require abdellahchatioui/erp-connector:dev-main
```

### 2. Clear Cache

Clear the application cache so Bagisto auto-discovers the package and loads the new configuration UI:

```bash
php artisan optimize:clear
php artisan config:cache
```

## Configuration

We have removed the need for `.env` variables! All configuration is now securely managed via the Admin Panel.

1. Log into your Bagisto **Admin Panel**.
2. Navigate to **Settings > Configuration > ERP Connector**.
3. Enter your **ERP Backend URL** (e.g., `http://localhost:8080`).
4. Enter your **ERP Token**.
5. Click **Save Settings**.
6. Click **Test Connection** to immediately verify that Bagisto can communicate with your ERP system.

## API Endpoints

The package exposes the following webhook endpoints in Bagisto (prefixed with `/api/erp`):

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/erp/webhook/product` | Sync product data from ERP |
| POST | `/api/erp/webhook/order` | Sync order data from ERP |

*Note: All requests sent to Bagisto must include the `X-ERP-TOKEN` header matching the token saved in your Admin settings.*

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
