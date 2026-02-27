<?php

declare(strict_types=1);

namespace Snowberry\WpMvc;

use RuntimeException;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Contracts\PolicyRegistryInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;
use Snowberry\WpMvc\Contracts\ViewRendererInterface;
use Snowberry\WpMvc\Contracts\AcfFieldServiceInterface;
use Snowberry\WpMvc\Controller\AbstractBlockController;
use Snowberry\WpMvc\Infrastructure\WordPress\WordPressServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\DiscoveryServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\ControllerServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\CliServiceProvider;
use Snowberry\WpMvc\Infrastructure\WordPress\BlockServiceProvider;

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

	private function registerControllerAndBlockProviders(): void
	{
		$this->container->addProvider(
			new ControllerServiceProvider()
		);

		$this->container->addProvider(
			new BlockServiceProvider()
		);
	}

	private function registerManifestPolicies(): void
	{
		$registry = $this->container->get( PolicyRegistryInterface::class );
		$manifest = $this->container->get( ProjectManifestInterface::class );

		foreach ( $manifest->policies() as $resourceClass => $policyClass ) {
			if ( ! class_exists( $resourceClass ) ) {
				throw new RuntimeException( "Manifest policy resource class [{$resourceClass}] does not exist." );
			}

			if ( ! class_exists( $policyClass ) ) {
				throw new RuntimeException( "Manifest policy class [{$policyClass}] does not exist." );
			}

			$registry->add( $resourceClass, $policyClass );
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
		$frameworkProviderCount = count( $this->container->getProviders() );

		// 1. Register framework services.
		for ( $index = 0; $index < $frameworkProviderCount; $index ++ ) {
			$this->container->getProviders()[$index]->register( $this->container );
		}

		// 2. Register manifest providers after framework providers.
		$this->registerManifestProviders();

		$manifestProviderEndIndex = count( $this->container->getProviders() );

		// 3. Register services for providers added from the manifest.
		for ( $index = $frameworkProviderCount; $index < $manifestProviderEndIndex; $index ++ ) {
			$this->container->getProviders()[$index]->register( $this->container );
		}

		// 4. Register policies declared in the manifest.
		$this->registerManifestPolicies();

		// 5. Register controller and block providers after policy registration.
		$this->registerControllerAndBlockProviders();

		$allProviderEndIndex = count( $this->container->getProviders() );

		// 6. Register services for controller and block providers.
		for ( $index = $manifestProviderEndIndex; $index < $allProviderEndIndex; $index ++ ) {
			$this->container->getProviders()[$index]->register( $this->container );
		}

		// 7. Boot services.
		foreach ( $this->container->getProviders() as $provider ) {
			$provider->boot( $this->container );
		}

		// 8. Execute registration layer.
		$this->container
			->get( RegistrationRegistryInterface::class )
			->registerAll();
	}

	public function container(): Container
	{
		return $this->container;
	}
}
