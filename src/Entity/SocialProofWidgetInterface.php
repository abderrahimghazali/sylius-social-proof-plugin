<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Entity;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\TimestampableInterface;

interface SocialProofWidgetInterface extends ResourceInterface, TimestampableInterface
{
    public function getCode(): string;

    public function setCode(string $code): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getType(): WidgetType;

    public function setType(WidgetType $type): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getPriority(): int;

    public function setPriority(int $priority): void;

    public function getSettings(): array;

    public function setSettings(array $settings): void;

    public function getSetting(string $key, mixed $default = null): mixed;

    public function setSetting(string $key, mixed $value): void;
}
