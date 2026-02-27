<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Closure;

final class Container
{
	private array $bindings = [];
	private array $instances = [];
	private array $providers = [];
	protected array $resolvers = [];

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
		if ( isset( $this->bindings[$abstract] ) ) {
			return ($this->bindings[$abstract])( $this );
		}

		if ( class_exists( $abstract ) ) {
			return $this->build( $abstract );
		}

		throw new \RuntimeException( "Cannot resolve [$abstract]." );
	}

	protected function build( string $class ): object
	{
		$reflection = new \ReflectionClass( $class );

		if ( ! $reflection->isInstantiable() ) {
			throw new \RuntimeException( "Class [$class] is not instantiable." );
		}

		$constructor = $reflection->getConstructor();

		if ( ! $constructor ) {
			return new $class();
		}

		$dependencies = [];

		foreach ( $constructor->getParameters() as $parameter ) {

			$type = $parameter->getType();

			if ( ! $type || $type->isBuiltin() ) {
				throw new RuntimeException(
						"Cannot resolve parameter \${$parameter->getName()} in [$class]"
					);
			}

			$typeName = $type->getName();

			try {
				$dependencies[] = $this->get( $typeName );
			} catch ( \RuntimeException $e ) {

				if ( $parameter->allowsNull() ) {
					$dependencies[] = null;
					continue;
				}

				throw $e;
			}
		}

		return $reflection->newInstanceArgs( $dependencies );
	}

	public function addProvider( ServiceProvider $provider ): void
	{
		$this->providers[] = $provider;
	}

	public function getProviders(): array
	{
		return $this->providers;
	}

	public function registerResolver( string $baseClass, callable $resolver ): void
	{
		$this->resolvers[$baseClass] = $resolver;
	}
}
