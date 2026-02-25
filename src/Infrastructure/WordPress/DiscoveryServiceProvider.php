<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Contracts\DefinitionDiscoveryInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;

final class DiscoveryServiceProvider extends ServiceProvider
{

	public function register( Container $container ): void
	{
		$container->singleton( DefinitionDiscoveryInterface::class, function ( Container $c ) {
			return new DefinitionDiscovery(
				$c->get( ProjectLocatorInterface::class ),
				$c->get( ProjectManifestInterface::class ),
				$c->get( RegistrationRegistryInterface::class ),
			);
		} );
	}

	public function boot( Container $container ): void
	{
		// Do nothing if we can't locate a project manifest; let CLI still work in framework contexts.
		try {
			$container->get( DefinitionDiscoveryInterface::class )->discover();
		} catch ( \Throwable $e ) {
			// In production, I'd prefer failing loud.
			// But during early adoption, you may want to no-op if no manifest exists.
			// If you want "fail loud always", remove this try/catch.
		}
	}
}
