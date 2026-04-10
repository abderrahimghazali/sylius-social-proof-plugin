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

        // Twig hooks
        $loader->load('twig_hooks/shop/product.yaml');
        $loader->load('twig_hooks/shop/footer.yaml');

        // Twig template paths
        $container->prependExtensionConfig('twig', [
            'paths' => [
                __DIR__ . '/../../templates' => 'SyliusSocialProofPlugin',
            ],
        ]);
    }
}
