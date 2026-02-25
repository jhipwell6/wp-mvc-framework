<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PostTypeRegistrarInterface
{

	public function register( PostTypeDefinition $definition ): void;
}
