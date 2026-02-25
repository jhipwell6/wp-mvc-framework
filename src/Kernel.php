<?php

declare(strict_types=1);

namespace Snowberry\WpMvc;

use RuntimeException;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;
use Snowberry\WpMvc\Infrastructure\WordPress\WordPressServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\DiscoveryServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\ControllerServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\CliServiceProvider;

final class Kernel
{
	private Container $container;

	public function __construct( ?Container $container = null )
	{
		$this->container = $container ?? new Container();

		$this->registerFrameworkProviders();
	}

	/**
	 * Register internal framework providers.
	 */
	private function registerFrameworkProviders(): void
	{
		$this->container->addProvider(
			new WordPressServiceProvider()
		);

		$this->container->addProvider(
			new DiscoveryServiceProvider()
		);

		$this->container->addProvider(
			new ControllerServiceProvider()
		);

		$this->container->addProvider(
			new CliServiceProvider()
		);
	}

	private function registerManifestProviders(): void
	{
		$manifest = $this->container->get( ProjectManifestInterface::class );

		foreach ( $manifest->providers() as $providerClass ) {
			if ( ! class_exists( $providerClass ) ) {
				throw new RuntimeException( "Manifest provider class [{$providerClass}] does not exist." );
			}

			$provider = new $providerClass();

			if ( ! $provider instanceof ServiceProvider ) {
				throw new RuntimeException( "Manifest provider [{$providerClass}] must extend " . ServiceProvider::class . '.' );
			}

			$this->container->addProvider( $provider );
		}
	}

	/**
	 * Allow client apps to register additional providers.
	 */
	public function register( ServiceProvider $provider ): void
	{
		$this->container->addProvider( $provider );
	}

	/**
	 * Boot the kernel.
	 */
	public function boot(): void
	{
		$initialProviderCount = count( $this->container->getProviders() );

		// 1. Register framework services.
		for ( $index = 0; $index < $initialProviderCount; $index++ ) {
			$this->container->getProviders()[$index]->register( $this->container );
		}

		// 2. Register manifest providers after framework providers.
		$this->registerManifestProviders();

		// 3. Register services for providers added from the manifest.
		$allProviders = $this->container->getProviders();
		for ( $index = $initialProviderCount; $index < count( $allProviders ); $index++ ) {
			$allProviders[$index]->register( $this->container );
		}

		// 4. Boot services.
		foreach ( $this->container->getProviders() as $provider ) {
			$provider->boot( $this->container );
		}

		// 5. Execute registration layer.
		$this->container
			->get( RegistrationRegistryInterface::class )
			->registerAll();
	}

	public function container(): Container
	{
		return $this->container;
	}
}
