<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Controller\Shop;

use Abderrahim\SyliusSocialProofPlugin\Service\RecentPurchaseProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class RecentPurchaseController
{
    public function __construct(
        private readonly RecentPurchaseProvider $recentPurchaseProvider,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $productId = $request->query->getInt('productId', 0);

        $purchases = $this->recentPurchaseProvider->getRecentPurchases(
            $productId > 0 ? $productId : null,
        );

        return new JsonResponse($purchases);
    }
}
