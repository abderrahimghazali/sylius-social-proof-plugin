<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusSocialProofPlugin\Unit\Entity;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidget;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use PHPUnit\Framework\TestCase;

final class SocialProofWidgetTest extends TestCase
{
    public function testDefaults(): void
    {
        $widget = new SocialProofWidget();

        self::assertNull($widget->getId());
        self::assertSame('', $widget->getCode());
        self::assertSame('', $widget->getName());
        self::assertSame(WidgetType::LiveViewers, $widget->getType());
        self::assertFalse($widget->isEnabled());
        self::assertSame(0, $widget->getPriority());
        self::assertSame([], $widget->getSettings());
    }

    public function testSettersAndGetters(): void
    {
        $widget = new SocialProofWidget();
        $widget->setCode('live_viewers');
        $widget->setName('Live Viewers');
        $widget->setType(WidgetType::RecentPurchases);
        $widget->setEnabled(true);
        $widget->setPriority(10);
        $widget->setSettings(['min_count' => 5]);

        self::assertSame('live_viewers', $widget->getCode());
        self::assertSame('Live Viewers', $widget->getName());
        self::assertSame(WidgetType::RecentPurchases, $widget->getType());
        self::assertTrue($widget->isEnabled());
        self::assertSame(10, $widget->getPriority());
        self::assertSame(['min_count' => 5], $widget->getSettings());
    }

    public function testGetSettingWithDefault(): void
    {
        $widget = new SocialProofWidget();
        $widget->setSettings(['threshold' => 3]);

        self::assertSame(3, $widget->getSetting('threshold'));
        self::assertSame(10, $widget->getSetting('missing_key', 10));
        self::assertNull($widget->getSetting('missing_key'));
    }

    public function testSetSettingMerges(): void
    {
        $widget = new SocialProofWidget();
        $widget->setSettings(['a' => 1]);
        $widget->setSetting('b', 2);

        self::assertSame(['a' => 1, 'b' => 2], $widget->getSettings());
    }
}
