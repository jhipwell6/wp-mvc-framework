<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\BlockRegistry;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;

final class BlockServiceProvider extends ServiceProvider
{
	public function register( Container $container ): void
	{
		$container->singleton( BlockRegistry::class, fn() => new BlockRegistry() );
	}

	public function boot( Container $container ): void
	{
		$container->get( BlockRegistry::class )->registerAll();
	}
}
