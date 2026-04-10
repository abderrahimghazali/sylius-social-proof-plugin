<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Service;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;

final class LiveViewerCounter
{
    public function __construct(
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
    ) {
    }

    public function getCount(int $productId): int
    {
        $widget = $this->widgetRepository->findEnabledByType(WidgetType::LiveViewers);

        if ($widget === null) {
            return 0;
        }

        $min = (int) $widget->getSetting('min_count', 5);
        $max = (int) $widget->getSetting('max_count', 30);
        $interval = max(1, (int) $widget->getSetting('refresh_interval', 30));

        // Hash-based pseudo-random: stable within each refresh interval, varies per product
        // Does not affect global mt_rand state
        $timeBucket = (int) floor(time() / $interval);
        $hash = crc32($productId . ':' . $timeBucket);
        $range = max(1, $max - $min + 1);

        return $min + (abs($hash) % $range);
    }
}
