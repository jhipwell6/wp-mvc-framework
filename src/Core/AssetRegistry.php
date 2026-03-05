<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Support\Asset;

class AssetRegistry
{
	/**
	 * @var array<string, Asset>
	 */
	private array $assets = [];

	public function register( Asset $asset ): void
	{
		$this->assets[$asset->handle()] = $asset;
	}

	public function get( string $handle ): ?Asset
	{
		return $this->assets[$handle] ?? null;
	}

	/**
	 * @return Asset[]
	 */
	public function all(): array
	{
		return array_values( $this->assets );
	}
}
