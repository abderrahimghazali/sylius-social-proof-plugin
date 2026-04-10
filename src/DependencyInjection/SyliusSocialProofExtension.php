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

        // Twig hooks — must use prependExtensionConfig, not YAML loader
        $container->prependExtensionConfig('sylius_twig_hooks', [
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
                        'priority' => 0,
                    ],
                ],
            ],
        ]);
    }
}
