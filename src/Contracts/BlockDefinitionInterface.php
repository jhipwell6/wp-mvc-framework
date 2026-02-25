<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface BlockDefinitionInterface
{
	public function name(): string;

	public function settings(): array;

	public function render( array $block, string $content = '', bool $isPreview = false, int $postId = 0 ): void;
}
