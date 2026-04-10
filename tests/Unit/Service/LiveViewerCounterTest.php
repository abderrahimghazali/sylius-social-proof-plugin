<?php

declare(strict_types=1);

namespace Tests\Abderrahim\SyliusSocialProofPlugin\Unit\Service;

use Abderrahim\SyliusSocialProofPlugin\Entity\SocialProofWidget;
use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Abderrahim\SyliusSocialProofPlugin\Repository\SocialProofWidgetRepositoryInterface;
use Abderrahim\SyliusSocialProofPlugin\Service\LiveViewerCounter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LiveViewerCounterTest extends TestCase
{
    private SocialProofWidgetRepositoryInterface&MockObject $widgetRepository;
    private LiveViewerCounter $counter;

    protected function setUp(): void
    {
        $this->widgetRepository = $this->createMock(SocialProofWidgetRepositoryInterface::class);
        $this->counter = new LiveViewerCounter($this->widgetRepository);
    }

    public function testReturnsZeroWhenWidgetDisabled(): void
    {
        $this->widgetRepository
            ->method('findEnabledByType')
            ->with(WidgetType::LiveViewers)
            ->willReturn(null);

        self::assertSame(0, $this->counter->getCount(1));
    }

    public function testReturnsCountWithinConfiguredRange(): void
    {
        $widget = new SocialProofWidget();
        $widget->setType(WidgetType::LiveViewers);
        $widget->setSettings(['min_count' => 10, 'max_count' => 20, 'refresh_interval' => 30]);

        $this->widgetRepository
            ->method('findEnabledByType')
            ->willReturn($widget);

        $count = $this->counter->getCount(42);

        self::assertGreaterThanOrEqual(10, $count);
        self::assertLessThanOrEqual(20, $count);
    }

    public function testReturnsSameCountForSameProductWithinInterval(): void
    {
        $widget = new SocialProofWidget();
        $widget->setType(WidgetType::LiveViewers);
        $widget->setSettings(['min_count' => 5, 'max_count' => 50, 'refresh_interval' => 60]);

        $this->widgetRepository
            ->method('findEnabledByType')
            ->willReturn($widget);

        $count1 = $this->counter->getCount(100);
        $count2 = $this->counter->getCount(100);

        self::assertSame($count1, $count2);
    }

    public function testReturnsDifferentCountForDifferentProducts(): void
    {
        $widget = new SocialProofWidget();
        $widget->setType(WidgetType::LiveViewers);
        $widget->setSettings(['min_count' => 1, 'max_count' => 1000, 'refresh_interval' => 30]);

        $this->widgetRepository
            ->method('findEnabledByType')
            ->willReturn($widget);

        $count1 = $this->counter->getCount(1);
        $count2 = $this->counter->getCount(999);

        // Extremely unlikely to be equal with range 1-1000
        self::assertNotSame($count1, $count2);
    }
}
