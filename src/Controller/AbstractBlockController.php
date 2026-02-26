<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Controller;

use Snowberry\WpMvc\Contracts\AcfFieldServiceInterface;
use Snowberry\WpMvc\Contracts\BlockDefinitionInterface;
use Snowberry\WpMvc\Contracts\ViewRendererInterface;
use Snowberry\WpMvc\Core\Container;

abstract class AbstractBlockController implements BlockDefinitionInterface
{
	protected AcfFieldServiceInterface $acf;

	public function __construct(
		protected Container $container,
		protected ViewRendererInterface $viewRenderer
	)
	{
		$this->acf = $this->container->get( AcfFieldServiceInterface::class );
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

	abstract public function name(): string;

	abstract public function settings(): array;

	abstract public function render( array $block, string $content = '', bool $isPreview = false, int $postId = 0 ): void;
}
