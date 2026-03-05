<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Support;

class Asset
{
    /**
     * @param string[] $contexts
     */
    public function __construct(
        private string $handle,
        private string $src,
        private array $deps = [],
        private ?string $version = null,
        private bool $inFooter = true,
        private string $type = 'script',
        private array $contexts = [AssetContext::FRONTEND]
    ) {}

    public function handle(): string
    {
        return $this->handle;
    }

    public function src(): string
    {
        return $this->src;
    }

    public function deps(): array
    {
        return $this->deps;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function inFooter(): bool
    {
        return $this->inFooter;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isScript(): bool
    {
        return $this->type === 'script';
    }

    public function isStyle(): bool
    {
        return $this->type === 'style';
    }

    /**
     * @return string[]
     */
    public function contexts(): array
    {
        return $this->contexts;
    }

    public function appliesTo(string $context): bool
    {
        return in_array($context, $this->contexts, true);
    }
}