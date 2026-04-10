<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Repository;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidgetInterface;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class SocialProofWidgetRepository extends EntityRepository implements SocialProofWidgetRepositoryInterface
{
    public function findEnabledByType(WidgetType $type): ?SocialProofWidgetInterface
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.enabled = :enabled')
            ->andWhere('w.type = :type')
            ->setParameter('enabled', true)
            ->setParameter('type', $type->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return SocialProofWidgetInterface[] */
    public function findAllEnabled(): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('w.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
