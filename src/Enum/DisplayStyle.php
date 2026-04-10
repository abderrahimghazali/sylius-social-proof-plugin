<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Enum;

enum DisplayStyle: string
{
    case Toast = 'toast';
    case BottomBar = 'bottom_bar';
    case TopBar = 'top_bar';

    public function label(): string
    {
        return match ($this) {
            self::Toast => 'Toast (bottom-left)',
            self::BottomBar => 'Bottom bar (full-width)',
            self::TopBar => 'Top bar (full-width)',
        };
    }
}
