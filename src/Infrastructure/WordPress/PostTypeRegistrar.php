<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\PostTypeRegistrarInterface;
use Snowberry\WpMvc\Contracts\PostTypeDefinition;

final class PostTypeRegistrar implements PostTypeRegistrarInterface
{

	public function register( PostTypeDefinition $definition ): void
	{
		register_post_type(
			$definition->slug(),
			$definition->args()
		);
	}
}
