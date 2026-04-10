<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Service;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class LowStockChecker
{
    public function __construct(
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
    ) {
    }

    /**
     * Returns the lowest tracked stock count if below threshold, null otherwise.
     */
    public function getLowStockCount(ProductInterface $product): ?int
    {
        $widget = $this->widgetRepository->findEnabledByType(WidgetType::LowStock);

        if ($widget === null) {
            return null;
        }

        $threshold = (int) $widget->getSetting('threshold', 5);
        $lowestStock = null;

        foreach ($product->getVariants() as $variant) {
            if (!$variant instanceof ProductVariantInterface) {
                continue;
            }

            if (!$variant->isTracked()) {
                continue;
            }

            $available = $variant->getOnHand() - $variant->getOnHold();

            if ($available <= 0) {
                continue;
            }

            if ($lowestStock === null || $available < $lowestStock) {
                $lowestStock = $available;
            }
        }

        if ($lowestStock === null || $lowestStock > $threshold) {
            return null;
        }

        return $lowestStock;
    }
}
