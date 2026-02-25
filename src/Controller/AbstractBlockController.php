<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Controller;

use Snowberry\WpMvc\Contracts\AcfFieldServiceInterface;
use Snowberry\WpMvc\Contracts\BlockDefinitionInterface;
use Snowberry\WpMvc\Core\Container;

abstract class AbstractBlockController implements BlockDefinitionInterface
{

	public function __construct(
		protected Container $container
	)
	{
	}

	protected function field( string $key, int|string|null $context = null ): mixed
	{
		return $this->acfFieldService()->get( $key, $context );
	}

	protected function updateField( string $key, mixed $value, int|string|null $context = null ): void
	{
		$this->acfFieldService()->update( $key, $value, $context );
	}

	private function acfFieldService(): AcfFieldServiceInterface
	{
		return $this->container->get( AcfFieldServiceInterface::class );
	}

	abstract public function name(): string;

	abstract public function settings(): array;

	abstract public function render( array $block, string $content = '', bool $isPreview = false, int $postId = 0 ): void;
}
