# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-10

### Added
- Live Viewers widget — simulated real-time viewer count per product with configurable range and refresh interval
- Recent Purchases notification — toast, bottom bar, or top bar showing recent buyer name and city
- Sales Counter widget — shows units sold in configurable lookback period with animated count-up
- Low Stock Alert widget — urgency indicator when stock falls below threshold
- Single entity design with JSON settings per widget type
- Admin CRUD under Marketing > Social Proof with grid, create, update, delete, toggle
- Install command to seed default widget configurations
- Shop API endpoints for live viewer polling and recent purchase data
- Stimulus controllers for live polling, toast cycling, count animation, and pulse effect
- Twig hooks for product page widgets and footer notifications
- Lazy-loaded Twig extension via RuntimeExtensionInterface
- EN/FR translations
- PHPStan level 5, CI across PHP 8.2/8.3/8.4
