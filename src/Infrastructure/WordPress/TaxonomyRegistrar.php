<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\TaxonomyRegistrarInterface;
use Snowberry\WpMvc\Contracts\TaxonomyDefinition;

final class TaxonomyRegistrar implements TaxonomyRegistrarInterface
{

	public function register( TaxonomyDefinition $definition ): void
	{
		register_taxonomy(
			$definition->slug(),
			$definition->postTypes(),
			$definition->args()
		);
	}
}
