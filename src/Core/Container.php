<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Closure;

final class Container
{
	private array $bindings = [];
	private array $instances = [];
	private array $providers = [];

	public function bind( string $abstract, Closure $factory ): void
	{
		$this->bindings[$abstract] = $factory;
	}

	public function singleton( string $abstract, Closure $factory ): void
	{
		$this->bindings[$abstract] = function ( $container ) use ( $abstract, $factory ) {
			if ( ! isset( $this->instances[$abstract] ) ) {
				$this->instances[$abstract] = $factory( $container );
			}
			return $this->instances[$abstract];
		};
	}

	public function get( string $abstract ): mixed
	{
		if ( ! isset( $this->bindings[$abstract] ) ) {
			if ( ! class_exists( $abstract ) ) {
				throw new \RuntimeException( "Cannot resolve [$abstract]" );
			}

			return new $abstract();
		}

		return $this->bindings[$abstract]( $this );
	}

	public function addProvider( ServiceProvider $provider ): void
	{
		$this->providers[] = $provider;
	}

	public function getProviders(): array
	{
		return $this->providers;
	}
}
