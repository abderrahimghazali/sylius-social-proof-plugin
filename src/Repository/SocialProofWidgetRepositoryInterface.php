<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Repository;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface SocialProofWidgetRepositoryInterface extends RepositoryInterface
{
    public function findEnabledByType(WidgetType $type): ?SocialProofWidgetInterface;

    /** @return SocialProofWidgetInterface[] */
    public function findAllEnabled(): array;
}
