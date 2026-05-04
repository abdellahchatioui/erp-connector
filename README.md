# Bagisto ERP Connector

A powerful integration package for [Bagisto](https://bagisto.com/) that enables seamless synchronization between your e-commerce store and an external ERP system.

## Features

- **Product Synchronization:** Receive product updates from your ERP via secure webhooks.
- **Order Synchronization:** Automatically send order details to your ERP when an order is placed.
- **Secure Communication:** Built-in middleware to verify API tokens for all incoming ERP requests.
- **Configurable:** Easy-to-use configuration file and environment variable support.

## Installation

### 1. Install via Composer

In your Bagisto project root, run:

```bash
composer require abdellahchatioui/erp-connector:dev-main
```

### 2. Configure Environment Variables

Add the following keys to your `.env` file:

```env
ERP_BASE_URL=http://your-erp-url.com
ERP_API_TOKEN=your_secure_token_here

# Recommended to avoid Algolia dependency errors if not using it
SCOUT_DRIVER=null
```

### 3. Publish Configuration

Publish the `erp.php` config file to your application:

```bash
php artisan vendor:publish --provider="Webkul\ErpConnector\Providers\ErpConnectorServiceProvider"
```

### 4. Run Migrations

Ensure your database is up to date:

```bash
php artisan migrate
```

## API Endpoints

The package exposes the following webhook endpoints (prefixed with `/api/erp`):

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/erp/webhook/product` | Sync product data from ERP |
| POST | `/api/erp/webhook/order` | Sync order data from ERP |

*Note: All requests must include the `Authorization: Bearer <your_token>` header or `X-ERP-TOKEN` header.*

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
