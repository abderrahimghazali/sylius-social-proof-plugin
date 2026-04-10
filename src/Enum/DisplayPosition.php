<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Enum;

enum DisplayPosition: string
{
    case TopLeft = 'top_left';
    case TopRight = 'top_right';
    case BottomLeft = 'bottom_left';
    case BottomRight = 'bottom_right';

    public function label(): string
    {
        return match ($this) {
            self::TopLeft => 'Top left',
            self::TopRight => 'Top right',
            self::BottomLeft => 'Bottom left',
            self::BottomRight => 'Bottom right',
        };
    }
}
