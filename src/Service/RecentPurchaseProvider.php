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
        $productTransTable = $em->getClassMetadata(\Sylius\Component\Core\Model\ProductTranslation::class)->getTableName();
        $productImageTable = $em->getClassMetadata(\Sylius\Component\Core\Model\ProductImage::class)->getTableName();

        $productFilter = '';
        $params = [
            'since' => (new \DateTime(sprintf('-%d hours', $lookbackHours)))->format('Y-m-d H:i:s'),
            'limit' => $limit,
        ];
        $types = [
            'limit' => \Doctrine\DBAL\ParameterType::INTEGER,
        ];

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
                pt.slug AS product_slug,
                o.locale_code,
                o.checkout_completed_at AS purchased_at
            FROM {$orderItemTable} oi
            INNER JOIN {$orderTable} o ON o.id = oi.order_id
            INNER JOIN {$variantTable} pv ON pv.id = oi.variant_id
            INNER JOIN {$productTable} p ON p.id = pv.product_id
            LEFT JOIN {$productTransTable} pt ON pt.translatable_id = p.id
            LEFT JOIN {$addressTable} a ON a.id = o.billing_address_id
            WHERE o.state != 'cancelled'
              AND o.checkout_completed_at >= :since
              {$productFilter}
            GROUP BY oi.id
            ORDER BY o.checkout_completed_at DESC
            LIMIT :limit
        SQL;

        $results = $conn->executeQuery($sql, $params, $types)->fetchAllAssociative();

        return array_map(function (array $row) use ($showCity): array {
            // Anonymize: show only first name initial + "." (e.g., "Jean" -> "J.")
            $firstName = $row['first_name'] ?? '';
            $displayName = $firstName !== '' ? mb_substr($firstName, 0, 1) . '.' : 'Someone';

            return [
                'first_name' => $displayName,
                'city' => $showCity ? ($row['city'] ?? '') : '',
                'product_name' => $row['product_name'] ?? '',
                'product_slug' => $row['product_slug'] ?? '',
                'locale' => $row['locale_code'] ?? 'en_US',
                'purchased_at' => $row['purchased_at'] ?? '',
            ];
        }, $results);
    }
}
