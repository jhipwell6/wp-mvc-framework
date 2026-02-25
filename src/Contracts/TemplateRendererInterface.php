<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface TemplateRendererInterface
{

	/**
	 * @param array<string, mixed> $context
	 */
	public function render( string $template, array $context = [] ): string;
}
