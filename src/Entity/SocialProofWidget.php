<?php

declare(strict_types=1);

namespace Abderrahim\SyliusSocialProofPlugin\Entity;

use Abderrahim\SyliusSocialProofPlugin\Enum\WidgetType;
use Sylius\Resource\Model\TimestampableTrait;

class SocialProofWidget implements SocialProofWidgetInterface
{
    use TimestampableTrait;

    protected ?int $id = null;

    protected string $code = '';

    protected string $name = '';

    protected WidgetType $type = WidgetType::LiveViewers;

    protected bool $enabled = false;

    protected int $priority = 0;

    protected array $settings = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): WidgetType
    {
        return $this->type;
    }

    public function setType(WidgetType $type): void
    {
        $this->type = $type;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }
}
