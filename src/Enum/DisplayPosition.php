<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Enum;

enum DisplayPosition: string
{
    case TopLeft = 'top_left';
    case TopRight = 'top_right';
    case BottomLeft = 'bottom_left';
    case BottomRight = 'bottom_right';
    case Top = 'top';
    case Bottom = 'bottom';

    public function label(): string
    {
        return match ($this) {
            self::TopLeft => 'Top left',
            self::TopRight => 'Top right',
            self::BottomLeft => 'Bottom left',
            self::BottomRight => 'Bottom right',
            self::Top => 'Top',
            self::Bottom => 'Bottom',
        };
    }

    /**
     * @return self[]
     */
    public static function forStyle(DisplayStyle $style): array
    {
        return match ($style) {
            DisplayStyle::Toast => [self::TopLeft, self::TopRight, self::BottomLeft, self::BottomRight],
            DisplayStyle::Section => [self::Top, self::Bottom],
        };
    }
}
