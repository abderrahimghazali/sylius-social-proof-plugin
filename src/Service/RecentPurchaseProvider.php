<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Service;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class RecentPurchaseProvider
{
    public function __construct(
        private readonly SocialProofWidgetRepositoryInterface $widgetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @return list<array{first_name: string, city: string, product_name: string, product_image: ?string, purchased_at: string}>
     */
    public function getRecentPurchases(?int $productId = null, int $limit = 5): array
    {
        $widget = $this->widgetRepository->findEnabledByType(WidgetType::RecentPurchases);

        if ($widget === null) {
            return [];
        }

        $lookbackHours = (int) $widget->getSetting('lookback_hours', 24);
        $showCity = (bool) $widget->getSetting('show_city', true);
        $cacheKey = sprintf('social_proof.recent_purchases.%s', $productId ?? 'global');

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($productId, $limit, $lookbackHours, $showCity): array {
            $item->expiresAfter(300); // 5 minutes

            return $this->queryRecentPurchases($productId, $limit, $lookbackHours, $showCity);
        });
    }

    /**
     * @return list<array{first_name: string, city: string, product_name: string, product_image: ?string, purchased_at: string}>
     */
    private function queryRecentPurchases(?int $productId, int $limit, int $lookbackHours, bool $showCity): array
    {
        $conn = $this->entityManager->getConnection();

        $em = $this->entityManager;
        $orderTable = $em->getClassMetadata(\Sylius\Component\Core\Model\Order::class)->getTableName();
        $orderItemTable = $em->getClassMetadata(\Sylius\Component\Core\Model\OrderItem::class)->getTableName();
        $variantTable = $em->getClassMetadata(\Sylius\Component\Core\Model\ProductVariant::class)->getTableName();
        $productTable = $em->getClassMetadata(\Sylius\Component\Core\Model\Product::class)->getTableName();
        $addressTable = $em->getClassMetadata(\Sylius\Component\Core\Model\Address::class)->getTableName();
        $productTransTable = 'sylius_product_translation';

        $productFilter = '';
        $params = [
            'since' => (new \DateTime(sprintf('-%d hours', $lookbackHours)))->format('Y-m-d H:i:s'),
        ];
        $types = [];

        if ($productId !== null) {
            $productFilter = 'AND pv.product_id = :productId';
            $params['productId'] = $productId;
            $types['productId'] = \Doctrine\DBAL\ParameterType::INTEGER;
        }

        $sql = <<<SQL
            SELECT
                a.first_name,
                a.city,
                pt.name AS product_name,
                (SELECT pi.path FROM sylius_product_image pi
                 INNER JOIN sylius_product_image_product_variants pipv ON pi.id = pipv.image_id
                 WHERE pipv.variant_id = pv.id LIMIT 1) AS product_image,
                o.checkout_completed_at AS purchased_at
            FROM {$orderItemTable} oi
            INNER JOIN {$orderTable} o ON o.id = oi.order_id
            INNER JOIN {$variantTable} pv ON pv.id = oi.variant_id
            INNER JOIN {$productTable} p ON p.id = pv.product_id
            LEFT JOIN sylius_product_translation pt ON pt.translatable_id = p.id
            LEFT JOIN {$addressTable} a ON a.id = o.billing_address_id
            WHERE o.state != 'cancelled'
              AND o.checkout_completed_at >= :since
              {$productFilter}
            GROUP BY oi.id
            ORDER BY o.checkout_completed_at DESC
            LIMIT {$limit}
        SQL;

        $results = $conn->executeQuery($sql, $params, $types)->fetchAllAssociative();

        return array_map(fn(array $row) => [
            'first_name' => $row['first_name'] ?? 'Someone',
            'city' => $showCity ? ($row['city'] ?? '') : '',
            'product_name' => $row['product_name'] ?? '',
            'product_image' => $row['product_image'],
            'purchased_at' => $row['purchased_at'] ?? '',
        ], $results);
    }
}
