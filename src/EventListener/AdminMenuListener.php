<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\EventListener;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'sylius.menu.admin.main', method: 'addAdminMenuItems')]
final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $marketingMenu = $menu->getChild('marketing');

        if ($marketingMenu === null) {
            return;
        }

        $marketingMenu
            ->addChild('social_proof', [
                'route' => 'social_proof_admin_widget_index',
            ])
            ->setLabel('social_proof.ui.social_proof')
            ->setLabelAttribute('icon', 'fire')
        ;
    }
}
