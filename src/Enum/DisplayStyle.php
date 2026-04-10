<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Enum;

enum DisplayStyle: string
{
    case Toast = 'toast';
    case Section = 'section';

    public function label(): string
    {
        return match ($this) {
            self::Toast => 'Toast (floating popup)',
            self::Section => 'Section (full-width bar)',
        };
    }
}
