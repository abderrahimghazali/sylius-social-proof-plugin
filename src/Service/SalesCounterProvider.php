<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Service;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SalesCounterProvider
{
    public function __construct(
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getSoldCount(int $productId): ?int
    {
        $widget = $this->widgetRepository->findEnabledByType(WidgetType::SalesCounter);

        if ($widget === null) {
            return null;
        }

        $lookbackHours = (int) $widget->getSetting('lookback_hours', 24);
        $minThreshold = (int) $widget->getSetting('min_threshold', 5);

        $cacheKey = sprintf('social_proof.sales_counter.%d', $productId);

        $count = $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $lookbackHours): int {
            $item->expiresAfter(600); // 10 minutes

            return $this->querySoldCount($productId, $lookbackHours);
        });

        return $count >= $minThreshold ? $count : null;
    }

    private function querySoldCount(int $productId, int $lookbackHours): int
    {
        $conn = $this->entityManager->getConnection();

        $em = $this->entityManager;
        $orderTable = $em->getClassMetadata(\Sylius\Component\Core\Model\Order::class)->getTableName();
        $orderItemTable = $em->getClassMetadata(\Sylius\Component\Core\Model\OrderItem::class)->getTableName();
        $variantTable = $em->getClassMetadata(\Sylius\Component\Core\Model\ProductVariant::class)->getTableName();

        $sql = <<<SQL
            SELECT COALESCE(SUM(oi.quantity), 0) AS total_sold
            FROM {$orderItemTable} oi
            INNER JOIN {$orderTable} o ON o.id = oi.order_id
            INNER JOIN {$variantTable} pv ON pv.id = oi.variant_id
            WHERE pv.product_id = :productId
              AND o.state != 'cancelled'
              AND o.checkout_completed_at >= :since
        SQL;

        $result = $conn->executeQuery($sql, [
            'productId' => $productId,
            'since' => (new \DateTime(sprintf('-%d hours', $lookbackHours)))->format('Y-m-d H:i:s'),
        ], [
            'productId' => \Doctrine\DBAL\ParameterType::INTEGER,
        ])->fetchOne();

        return (int) $result;
    }
}
