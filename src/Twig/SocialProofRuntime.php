<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Twig;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Abderrahim\SyliusSocialProofPlugin\Service\LiveViewerCounter;
use Abderrahim\SyliusSocialProofPlugin\Service\LowStockChecker;
use Abderrahim\SyliusSocialProofPlugin\Service\RecentPurchaseProvider;
use Abderrahim\SyliusSocialProofPlugin\Service\SalesCounterProvider;
use Sylius\Component\Core\Model\ProductInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class SocialProofRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
        private readonly LiveViewerCounter $liveViewerCounter,
        private readonly RecentPurchaseProvider $recentPurchaseProvider,
        private readonly SalesCounterProvider $salesCounterProvider,
        private readonly LowStockChecker $lowStockChecker,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgets(ProductInterface $product): array
    {
        $widgets = [];
        $productId = $product->getId();

        // Single query for all enabled widgets
        $allEnabled = $this->widgetRepository->findAllEnabled();
        $byType = [];
        foreach ($allEnabled as $w) {
            $byType[$w->getType()->value] = $w;
        }

        // Live viewers
        $liveWidget = $byType['live_viewers'] ?? null;
        if ($liveWidget !== null && $productId !== null) {
            $widgets['live_viewers'] = [
                'count' => $this->liveViewerCounter->getCount($productId),
                'position' => $liveWidget->getSetting('display_position', 'bottom_right'),
                'settings' => $liveWidget->getSettings(),
            ];
        }

        // Sales counter
        $salesWidget = $byType['sales_counter'] ?? null;
        if ($salesWidget !== null && $productId !== null) {
            $soldCount = $this->salesCounterProvider->getSoldCount($productId);
            if ($soldCount !== null) {
                $widgets['sales_counter'] = [
                    'count' => $soldCount,
                    'position' => $salesWidget->getSetting('display_position', 'bottom_right'),
                    'lookback_hours' => (int) $salesWidget->getSetting('lookback_hours', 24),
                ];
            }
        }

        // Low stock
        if (isset($byType['low_stock'])) {
            $lowStockCount = $this->lowStockChecker->getLowStockCount($product);
            if ($lowStockCount !== null) {
                $widgets['low_stock'] = [
                    'count' => $lowStockCount,
                    'position' => $byType['low_stock']->getSetting('display_position', 'bottom_right'),
                ];
            }
        }

        // Custom message
        $customWidget = $byType['custom_message'] ?? null;
        if ($customWidget !== null && ($customWidget->getSetting('message', '') !== '')) {
            $widgets['custom_message'] = [
                'message' => $customWidget->getSetting('message', ''),
                'icon' => $customWidget->getSetting('icon', '📢'),
                'link_url' => $customWidget->getSetting('link_url', ''),
                'link_text' => $customWidget->getSetting('link_text', ''),
                'dismissible' => $customWidget->getSetting('dismissible', true),
                'position' => $customWidget->getSetting('display_position', 'bottom_right'),
            ];
        }

        return $widgets;
    }

    /**
     * @return list<array{first_name: string, city: string, product_name: string, product_slug: string, locale: string, purchased_at: string}>
     */
    public function getRecentPurchasesGlobal(): array
    {
        return $this->recentPurchaseProvider->getRecentPurchases();
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetSettings(string $type): array
    {
        $widgetType = WidgetType::tryFrom($type);
        if ($widgetType === null) {
            return [];
        }

        $widget = $this->widgetRepository->findEnabledByType($widgetType);

        return $widget?->getSettings() ?? [];
    }

    /**
     * Get the display position from any enabled widget. All widgets share the same position.
     * Returns the position from the highest-priority enabled widget that has one set.
     */
    public function getGlobalPosition(): string
    {
        $widgets = $this->widgetRepository->findAllEnabled();

        foreach ($widgets as $widget) {
            $position = $widget->getSetting('display_position');
            if ($position !== null && $position !== '') {
                return $position;
            }
        }

        return 'bottom_right';
    }
}
