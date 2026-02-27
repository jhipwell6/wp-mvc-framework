<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Controller;

use Snowberry\WpMvc\Contracts\AcfFieldServiceInterface;
use Snowberry\WpMvc\Contracts\BlockDefinitionInterface;
use Snowberry\WpMvc\Contracts\PermissionCheckerInterface;
use Snowberry\WpMvc\Contracts\PolicyRegistryInterface;
use Snowberry\WpMvc\Contracts\ViewRendererInterface;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Exceptions\AuthorizationException;

abstract class AbstractBlockController implements BlockDefinitionInterface
{
	private ?PermissionCheckerInterface $permissionChecker;
	private ?PolicyRegistryInterface $policyRegistry;

	public function __construct(
		protected ViewRendererInterface $viewRenderer,
		protected AcfFieldServiceInterface $acf,
		?PermissionCheckerInterface $permissionChecker = null,
		?PolicyRegistryInterface $policyRegistry = null
	)
	{
		$this->permissionChecker = $permissionChecker;
		$this->policyRegistry = $policyRegistry;
	}

	protected function view( string $view, array $data = [] ): void
	{
		echo $this->viewRenderer->render( $view, $data );
	}

	protected function field( string $key, int|string|null $context = null ): mixed
	{
		return $this->acf->get( $key, $context );
	}

	protected function option( string $key ): mixed
	{
		return $this->acf->get( $key, 'option' );
	}

	protected function updateField( string $key, mixed $value, int|string|null $context = null ): void
	{
		$this->acf->update( $key, $value, $context );
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

	abstract public function name(): string;

	abstract public function settings(): array;

	abstract public function render( array $block, string $content = '', bool $isPreview = false, int $postId = 0 ): void;
}
