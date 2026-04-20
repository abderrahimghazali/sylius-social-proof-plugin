<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\EventListener;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Cache\CacheInterface;

#[AsEventListener(event: 'workflow.sylius_order_checkout.completed.complete', method: 'invalidate', priority: -100)]
#[AsEventListener(event: 'sylius.order.post_complete', method: 'invalidate')]
final class OrderCompleteCacheInvalidator
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
    ) {
    }

    public function invalidate(object $event): void
    {
        $order = $event->getSubject();

        if (!$order instanceof OrderInterface) {
            return;
        }

        $productIds = [];

        /** @var OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            $variant = $item->getVariant();
            $product = $variant?->getProduct();

            if ($product !== null && $product->getId() !== null) {
                $productIds[$product->getId()] = true;
            }
        }

        if (empty($productIds)) {
            return;
        }

        $keysToDelete = [];

        // Sales counter cache keys
        $salesWidget = $this->widgetRepository->findEnabledByType(WidgetType::SalesCounter);
        if ($salesWidget !== null) {
            $lookbackHours = (int) $salesWidget->getSetting('lookback_hours', 24);
            foreach (array_keys($productIds) as $productId) {
                $keysToDelete[] = sprintf('social_proof.sales_counter.%d.%d', $productId, $lookbackHours);
            }
        }

        // Recent purchases cache keys
        $rpWidget = $this->widgetRepository->findEnabledByType(WidgetType::RecentPurchases);
        if ($rpWidget !== null) {
            $lookbackHours = (int) $rpWidget->getSetting('lookback_hours', 24);
            $showCity = (int) (bool) $rpWidget->getSetting('show_city', true);
            foreach (array_keys($productIds) as $productId) {
                $keysToDelete[] = sprintf('social_proof.recent_purchases.%s.%d.%d', $productId, $lookbackHours, $showCity);
            }
            $keysToDelete[] = sprintf('social_proof.recent_purchases.global.%d.%d', $lookbackHours, $showCity);
        }

        foreach ($keysToDelete as $key) {
            $this->cache->delete($key);
        }
    }
}
