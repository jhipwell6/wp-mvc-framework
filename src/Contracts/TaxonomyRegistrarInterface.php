<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface TaxonomyRegistrarInterface
{

	public function register( TaxonomyDefinition $definition ): void;
}
