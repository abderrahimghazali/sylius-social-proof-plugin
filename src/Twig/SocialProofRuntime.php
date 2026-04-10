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

        // Live viewers
        $liveWidget = $this->widgetRepository->findEnabledByType(WidgetType::LiveViewers);
        if ($liveWidget !== null && $productId !== null) {
            $widgets['live_viewers'] = [
                'count' => $this->liveViewerCounter->getCount($productId),
                'settings' => $liveWidget->getSettings(),
            ];
        }

        // Sales counter
        if ($productId !== null) {
            $soldCount = $this->salesCounterProvider->getSoldCount($productId);
            if ($soldCount !== null) {
                $salesWidget = $this->widgetRepository->findEnabledByType(WidgetType::SalesCounter);
                $widgets['sales_counter'] = [
                    'count' => $soldCount,
                    'lookback_hours' => (int) ($salesWidget?->getSetting('lookback_hours', 24)),
                ];
            }
        }

        // Low stock
        $lowStockCount = $this->lowStockChecker->getLowStockCount($product);
        if ($lowStockCount !== null) {
            $widgets['low_stock'] = [
                'count' => $lowStockCount,
            ];
        }

        return $widgets;
    }

    /**
     * @return list<array{first_name: string, city: string, product_name: string, product_image: ?string, purchased_at: string}>
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
