<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Controller\Shop;

use Abderrahim\SyliusSocialProofPlugin\Service\RecentPurchaseProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class RecentPurchaseController
{
    public function __construct(
        private readonly RecentPurchaseProvider $recentPurchaseProvider,
        private readonly RateLimiterFactory $socialProofApiLimiter,
    ) {
    }

    #[Cache(maxage: 30, public: true)]
    public function __invoke(Request $request): JsonResponse
    {
        $limiter = $this->socialProofApiLimiter->create('recent_purchases_' . $request->getClientIp());

        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Too many requests'], 429);
        }

        $productId = $request->query->getInt('productId', 0);

        $purchases = $this->recentPurchaseProvider->getRecentPurchases(
            $productId > 0 ? $productId : null,
        );

        return new JsonResponse($purchases);
    }
}
