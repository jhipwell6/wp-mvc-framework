<?php

declare(strict_types=1);

namespace Snowberry\WpMvc;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
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
		// 1. Register services
		foreach ( $this->container->getProviders() as $provider ) {
			$provider->register( $this->container );
		}

		// 2. Boot services
		foreach ( $this->container->getProviders() as $provider ) {
			$provider->boot( $this->container );
		}

		// 3. Execute registration layer
		$this->container
			->get( RegistrationRegistryInterface::class )
			->registerAll();
	}

	public function container(): Container
	{
		return $this->container;
	}
}
