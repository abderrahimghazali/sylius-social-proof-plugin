<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SyliusSocialProofExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        // Sylius resource config
        $loader->load('resources/social_proof_widget.yaml');

        // Grid config
        $loader->load('grids/admin/social_proof_widget.yaml');

        // Twig template paths
        $container->prependExtensionConfig('twig', [
            'paths' => [
                __DIR__ . '/../../templates' => 'SyliusSocialProofPlugin',
            ],
        ]);

        // Rate limiter for shop API endpoints
        $container->prependExtensionConfig('framework', [
            'rate_limiter' => [
                'social_proof_api' => [
                    'policy' => 'sliding_window',
                    'limit' => 30,
                    'interval' => '60 seconds',
                ],
            ],
        ]);

        // Twig hooks — loadFromExtension merges with existing config instead of replacing
        $container->loadFromExtension('sylius_twig_hooks', [
            'hooks' => [
                'sylius_shop.product.show.content.info.summary' => [
                    'social_proof_product_badge' => [
                        'template' => '@SyliusSocialProofPlugin/shop/product_badge.html.twig',
                        'priority' => 450,
                    ],
                ],
                'sylius_shop.base.footer' => [
                    'social_proof_toasts' => [
                        'template' => '@SyliusSocialProofPlugin/shop/widget/purchase_notification.html.twig',
                        'priority' => -100,
                    ],
                ],
            ],
        ]);
    }
}
