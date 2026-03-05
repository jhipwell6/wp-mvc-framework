<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Contracts\AssetManagerInterface;
use Snowberry\WpMvc\Support\Asset;
use Snowberry\WpMvc\Support\AssetContext;

class AssetManager implements AssetManagerInterface
{

	public function __construct(
		private AssetRegistry $registry
	)
	{
		
	}

	public function script(
		string $handle,
		string $src,
		array $deps = [],
		?string $version = null,
		bool $inFooter = true,
		array $contexts = []
	): void
	{

		$this->registry->register(
			new Asset(
				$handle,
				$src,
				$deps,
				$version,
				$inFooter,
				'script',
				$this->normalizeContexts( $contexts )
			)
		);
	}

	public function style(
		string $handle,
		string $src,
		array $deps = [],
		?string $version = null,
		array $contexts = []
	): void
	{

		$this->registry->register(
			new Asset(
				$handle,
				$src,
				$deps,
				$version,
				false,
				'style',
				$this->normalizeContexts( $contexts )
			)
		);
	}

	public function all(): array
	{
		return $this->registry->all();
	}

	/**
	 * @param string[] $contexts
	 * @return string[]
	 */
	private function normalizeContexts( array $contexts ): array
	{
		$contexts = array_values( array_filter( array_unique( $contexts ) ) );

		if ( $contexts === [] ) {
			return [ AssetContext::FRONTEND ];
		}

		return $contexts;
	}
}
