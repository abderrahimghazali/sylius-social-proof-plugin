<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Controller\Shop;

use Abderrahim\SyliusSocialProofPlugin\Service\LiveViewerCounter;
use Symfony\Component\HttpFoundation\JsonResponse;

final class LiveViewerController
{
    public function __construct(
        private readonly LiveViewerCounter $liveViewerCounter,
    ) {
    }

    public function __invoke(int $productId): JsonResponse
    {
        return new JsonResponse([
            'count' => $this->liveViewerCounter->getCount($productId),
        ]);
    }
}
