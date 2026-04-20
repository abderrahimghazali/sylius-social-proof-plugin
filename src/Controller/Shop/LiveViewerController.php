<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Controller\Shop;

use Abderrahim\SyliusSocialProofPlugin\Service\LiveViewerCounter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class LiveViewerController
{
    public function __construct(
        private readonly LiveViewerCounter $liveViewerCounter,
        private readonly RateLimiterFactory $socialProofApiLimiter,
    ) {
    }

    #[Cache(maxage: 10, public: true)]
    public function __invoke(int $productId): JsonResponse
    {
        $limiter = $this->socialProofApiLimiter->create('live_viewers_' . $productId);

        if (!$limiter->consume()->isAccepted()) {
            return new JsonResponse(['error' => 'Too many requests'], 429);
        }

        return new JsonResponse([
            'count' => $this->liveViewerCounter->getCount($productId),
        ]);
    }
}
