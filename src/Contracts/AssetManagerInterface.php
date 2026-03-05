<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

use Snowberry\WpMvc\Support\Asset;

interface AssetManagerInterface
{

	public function script(
		string $handle,
		string $src,
		array $deps = [],
		?string $version = null,
		bool $inFooter = true,
		array $contexts = []
	): void;

	public function style(
		string $handle,
		string $src,
		array $deps = [],
		?string $version = null,
		array $contexts = []
	): void;

	/**
	 * @return Asset[]
	 */
	public function all(): array;
}
