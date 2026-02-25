<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface FieldGroupRegistrarInterface
{

	public function register( array $group ): void;
}
