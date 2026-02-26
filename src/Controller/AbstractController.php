<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Controller;

use Snowberry\WpMvc\Contracts\ControllerInterface;
use Snowberry\WpMvc\Contracts\PermissionCheckerInterface;
use Snowberry\WpMvc\Contracts\PolicyRegistryInterface;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Exceptions\AuthorizationException;

abstract class AbstractController implements ControllerInterface
{
	private ?PermissionCheckerInterface $permissionChecker;
	private ?PolicyRegistryInterface $policyRegistry;

	public function __construct(
		protected Container $container,
		?PermissionCheckerInterface $permissionChecker = null,
		?PolicyRegistryInterface $policyRegistry = null
	)
	{
		$this->permissionChecker = $permissionChecker;
		$this->policyRegistry = $policyRegistry;
	}

	protected function action(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $args = 1
	): void
	{
		add_action( $hook, $callback, $priority, $args );
	}

	protected function filter(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $args = 1
	): void
	{
		add_filter( $hook, $callback, $priority, $args );
	}

	protected function rest(
		string $namespace,
		string $route,
		array $args
	): void
	{
		add_action( 'rest_api_init', function () use ( $namespace, $route, $args ) {
			register_rest_route( $namespace, $route, $args );
		} );
	}

	protected function authorize( string $capability, int $userId = 0 ): void
	{
		if ( ! $this->can( $capability, $userId ) ) {
			throw new AuthorizationException( "User is not authorized for capability [{$capability}]" );
		}
	}

	protected function authorizeFor( string $ability, object $resource, int $userId = 0 ): void
	{
		if ( ! $this->canFor( $ability, $resource, $userId ) ) {
			throw new AuthorizationException( "User is not authorized for ability [{$ability}]" );
		}
	}

	protected function can( string $capability, int $userId = 0 ): bool
	{
		return $this->getPermissionChecker()->can( $capability, $userId );
	}

	protected function canFor( string $ability, object $resource, int $userId = 0 ): bool
	{
		return $this->getPolicyRegistry()->can( $ability, $resource, $userId );
	}

	private function getPermissionChecker(): PermissionCheckerInterface
	{
		if ( $this->permissionChecker === null ) {
			$this->permissionChecker = $this->container->get( PermissionCheckerInterface::class );
		}

		return $this->permissionChecker;
	}

	private function getPolicyRegistry(): PolicyRegistryInterface
	{
		if ( $this->policyRegistry === null ) {
			$this->policyRegistry = $this->container->get( PolicyRegistryInterface::class );
		}

		return $this->policyRegistry;
	}
}
