<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface DefinitionDiscoveryInterface
{

	public function discover(): void;
}
