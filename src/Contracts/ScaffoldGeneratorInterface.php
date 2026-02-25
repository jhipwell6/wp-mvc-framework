<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface ScaffoldGeneratorInterface
{

	/**
	 * @param array<string, mixed> $options
	 */
	public function generate( string $name, array $options = [] ): ScaffoldResult;
}
