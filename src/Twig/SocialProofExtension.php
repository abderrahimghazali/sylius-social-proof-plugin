<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SocialProofExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('social_proof_widgets', [SocialProofRuntime::class, 'getWidgets']),
            new TwigFunction('social_proof_recent_purchases', [SocialProofRuntime::class, 'getRecentPurchasesGlobal']),
            new TwigFunction('social_proof_widget_settings', [SocialProofRuntime::class, 'getWidgetSettings']),
        ];
    }
}
