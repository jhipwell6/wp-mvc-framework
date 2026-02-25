<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ViewRendererInterface;

final class PhpViewRenderer implements ViewRendererInterface
{
	private string $viewsRoot;

	public function __construct(
		private ProjectLocatorInterface $locator,
		private ProjectManifestInterface $manifest
	)
	{
		$this->viewsRoot = $this->resolveViewsRoot();
	}

	public function render( string $view, array $data = [] ): string
	{
		$viewPath = $this->resolveViewPath( $view );

		if ( ! is_file( $viewPath ) ) {
			throw new RuntimeException( "View [{$view}] not found at [{$viewPath}]." );
		}

		extract( $data, EXTR_SKIP );

		ob_start();

		require $viewPath;

		return (string) ob_get_clean();
	}

	private function resolveViewsRoot(): string
	{
		$pluginRoot = rtrim( $this->locator->pluginRoot(), '/' );

		try {
			$resourcesPath = trim( $this->manifest->path( 'resources' ), '/' );
		} catch ( RuntimeException ) {
			$resourcesPath = 'resources';
		}

		if ( $resourcesPath === '' ) {
			$resourcesPath = 'resources';
		}

		return $pluginRoot . '/' . $resourcesPath . '/views';
	}

	private function resolveViewPath( string $view ): string
	{
		$relativePath = str_replace( '.', '/', trim( $view ) );

		return $this->viewsRoot . '/' . $relativePath . '.php';
	}
}
