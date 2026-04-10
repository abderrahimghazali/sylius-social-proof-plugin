<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
            <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
            <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
            <img alt="Sylius Logo" src="https://media.sylius.com/sylius-logo-800.png" width="300"/>
        </picture>
    </a>
</p>

<h1 align="center">Sylius Social Proof Plugin</h1>

<p align="center">
    Social proof and FOMO widgets for <a href="https://sylius.com">Sylius 2.x</a> — live viewer counts, recent purchase notifications, sales counters, low stock alerts, and custom messages.
</p>

<p align="center">
    <a href="https://github.com/abderrahimghazali/sylius-social-proof-plugin/actions/workflows/ci.yaml"><img src="https://github.com/abderrahimghazali/sylius-social-proof-plugin/actions/workflows/ci.yaml/badge.svg" alt="CI"/></a>
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-social-proof-plugin"><img src="https://img.shields.io/packagist/v/abderrahimghazali/sylius-social-proof-plugin.svg" alt="Latest Version"/></a>
    <a href="https://packagist.org/packages/abderrahimghazali/sylius-social-proof-plugin"><img src="https://img.shields.io/packagist/php-v/abderrahimghazali/sylius-social-proof-plugin.svg" alt="PHP Version"/></a>
    <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"/></a>
    <a href="https://sylius.com"><img src="https://img.shields.io/badge/sylius-2.x-green.svg" alt="Sylius 2.x"/></a>
    <a href="https://symfony.com"><img src="https://img.shields.io/badge/symfony-7.x-black.svg" alt="Symfony 7.x"/></a>
    <img src="https://img.shields.io/badge/PHPStan-level%205-brightgreen.svg" alt="PHPStan Level 5"/>
</p>

---

## Features

### Live Viewers
Displays "X people are viewing this right now" on product pages. Uses a seeded pseudo-random count within a configurable range, refreshed via lightweight polling.

### Recent Purchases
Shows "Jean from Paris just bought this" notifications with clickable product links. Queries real Sylius order data with buyer first name and city from billing address. Shows once per session to avoid spam.

### Sales Counter
Displays "47 sold in the last 24h" on product pages with an animated count-up effect. Configurable lookback period and minimum threshold.

### Low Stock Alert
Shows "Only 3 left in stock!" when product variant stock falls below a configurable threshold. Includes a subtle pulse animation for urgency.

### Custom Message
Display any custom message with an emoji icon, optional link, and dismissible close button. Perfect for announcements, promotions, or seasonal campaigns. Limited to 120 characters.

### Independent Positioning
Each widget has its own position setting — place them in any of the 4 screen corners independently:
- Top left
- Top right
- Bottom left
- Bottom right

---

## Requirements

| Dependency | Version |
|-----------|---------|
| PHP | ^8.2 |
| Sylius | ^2.1 |
| Symfony | ^7.0 |

## Installation

### 1. Install via Composer

```bash
composer require abderrahimghazali/sylius-social-proof-plugin
```

### 2. Register the bundle

```php
// config/bundles.php
return [
    // ...
    Abderrahim\SyliusSocialProofPlugin\SyliusSocialProofPlugin::class => ['all' => true],
];
```

### 3. Import routes

```yaml
# config/routes/sylius_social_proof.yaml
sylius_social_proof:
    resource: '@SyliusSocialProofPlugin/config/routes.yaml'
```

### 4. Run database migration

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

### 5. Seed default widget configurations

```bash
bin/console social-proof:install
```

This creates the default widgets (all disabled). Enable them from the admin panel.

### 6. Register Stimulus controllers

```json
// assets/controllers.json
{
    "@abderrahimghazali/sylius-social-proof-plugin": {
        "live-viewers": { "enabled": true, "fetch": "eager" },
        "purchase-toast": { "enabled": true, "fetch": "eager" },
        "sales-counter": { "enabled": true, "fetch": "lazy" },
        "low-stock": { "enabled": true, "fetch": "lazy" }
    }
}
```

### 7. Rebuild assets

```bash
npm run build
```

---

## Admin Usage

Navigate to **Marketing > Social Proof** in the Sylius admin panel.

Each widget can be independently:
- **Enabled/disabled** via toggle
- **Configured** with type-specific settings
- **Positioned** in any screen corner

### Widget Settings

| Widget | Settings |
|--------|----------|
| Live Viewers | Min count, max count, refresh interval, position |
| Recent Purchases | Max notifications, display interval, show city, lookback period, position |
| Sales Counter | Lookback period (hours), minimum threshold, position |
| Low Stock | Stock threshold, show exact count, position |
| Custom Message | Message (120 chars max), icon (emoji), link URL, link text, dismissible, position |

---

## Architecture

```
SyliusSocialProofPlugin/
├── src/
│   ├── Command/InstallCommand.php          # Seeds default widgets
│   ├── Controller/
│   │   ├── Admin/SocialProofWidgetController.php
│   │   └── Shop/
│   │       ├── LiveViewerController.php    # Polling endpoint
│   │       └── RecentPurchaseController.php # Recent purchases API
│   ├── Entity/SocialProofWidget.php        # Single entity with JSON settings
│   ├── Enum/
│   │   ├── WidgetType.php                  # 5 widget types
│   │   └── DisplayPosition.php             # 4 corner positions
│   ├── Service/
│   │   ├── LiveViewerCounter.php           # Seeded pseudo-random count
│   │   ├── RecentPurchaseProvider.php      # Queries Sylius orders, cached 5min
│   │   ├── SalesCounterProvider.php        # SUM(quantity) query, cached 10min
│   │   └── LowStockChecker.php            # Reads variant onHand - onHold
│   └── Twig/
│       ├── SocialProofExtension.php        # Registers functions
│       └── SocialProofRuntime.php          # Lazy-loaded runtime
├── assets/controllers/                     # Stimulus controllers
├── templates/
│   ├── admin/                              # CRUD forms
│   └── shop/                               # Product page + footer widgets
└── translations/                           # EN + FR
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v2/shop/social-proof/{productId}/live-viewers` | GET | Returns `{"count": 17}` |
| `/api/v2/shop/social-proof/recent-purchases` | GET | Returns array of recent purchases |
| `/api/v2/shop/social-proof/recent-purchases?productId=X` | GET | Filtered by product |

---

## Testing

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

## License

[MIT](LICENSE)
