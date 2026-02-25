<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface ProjectManifestInterface
{

	public function namespace(): string;

	public function path( string $key ): string;

	/**
	 * @return string[]
	 */
	public function providers(): array;
}
