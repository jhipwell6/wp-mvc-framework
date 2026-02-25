<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface ViewRendererInterface
{
	public function render( string $view, array $data = [] ): string;
}
