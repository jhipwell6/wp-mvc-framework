<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\ControllerInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Core\ControllerRegistry;
use Snowberry\WpMvc\Core\Container;
use RuntimeException;

final class ControllerDiscovery
{

	public function __construct(
		private ProjectLocatorInterface $locator,
		private ProjectManifestInterface $manifest,
		private ControllerRegistry $registry,
		private Container $container,
	)
	{
		
	}

	public function discover(): void
	{
		$pluginRoot = rtrim( $this->locator->pluginRoot(), '/' );
		$srcRoot = $pluginRoot . '/' . $this->manifest->path( 'src' );

		$controllerDir = $srcRoot . '/Controllers';

		if ( ! is_dir( $controllerDir ) ) {
			return;
		}

		foreach ( glob( $controllerDir . '/*.php' ) ?: [] as $file ) {
			require_once $file;

			$class = $this->resolveClassFromFile( $file );

			if ( ! class_exists( $class ) ) {
				throw new RuntimeException( "Controller class not found in {$file}" );
			}

			$instance = new $class( $this->container );

			if ( ! $instance instanceof ControllerInterface ) {
				throw new RuntimeException( "Controller must implement ControllerInterface: {$class}" );
			}

			$this->registry->add( $instance );
		}
	}

	private function resolveClassFromFile( string $file ): string
	{
		$namespace = $this->manifest->namespace();
		$class = basename( $file, '.php' );

		return "{$namespace}\\Controllers\\{$class}";
	}
}
