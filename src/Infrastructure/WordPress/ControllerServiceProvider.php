<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Core\ControllerRegistry;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;

final class ControllerServiceProvider extends ServiceProvider
{

	public function register( Container $container ): void
	{
		$container->singleton( ControllerRegistry::class, fn() => new ControllerRegistry() );
	}

	public function boot( Container $container ): void
	{
		$discovery = new ControllerDiscovery(
			$container->get( ProjectLocatorInterface::class ),
			$container->get( ProjectManifestInterface::class ),
			$container->get( ControllerRegistry::class ),
			$container
		);

		$discovery->discover();

		$container->get( ControllerRegistry::class )->registerAll();
	}
}
