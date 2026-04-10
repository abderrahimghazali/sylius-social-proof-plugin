<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Enum;

enum WidgetType: string
{
    case LiveViewers = 'live_viewers';
    case RecentPurchases = 'recent_purchases';
    case SalesCounter = 'sales_counter';
    case LowStock = 'low_stock';

    public function label(): string
    {
        return match ($this) {
            self::LiveViewers => 'Live Viewers',
            self::RecentPurchases => 'Recent Purchases',
            self::SalesCounter => 'Sales Counter',
            self::LowStock => 'Low Stock Alert',
        };
    }
}
