<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use RuntimeException;

final class ActivePluginProjectLocator implements ProjectLocatorInterface
{

	public function pluginRoot(): string
	{
		// If framework is in vendor/, we want the *parent plugin directory*.
		// __DIR__ points to .../vendor/snowberry/wp-mvc-framework/src/Infrastructure/WordPress
		$path = __DIR__;

		// Walk up until we find wp-content/plugins/<plugin-slug>/
		// We stop when we see a directory that contains a plugin main file with "Plugin Name:" header,
		// or we hit wp-content/plugins.
		$dir = realpath( $path ) ?: $path;

		for ( $i = 0; $i < 12; $i ++  ) {
			$dir = dirname( $dir );
			if ( $dir === '/' || $dir === '.' || $dir === '' ) {
				break;
			}

			// heuristic: plugin root typically contains a *.php with Plugin Name header.
			$phpFiles = glob( $dir . '/*.php' ) ?: [];
			foreach ( $phpFiles as $file ) {
				$contents = @file_get_contents( $file );
				if ( $contents !== false && str_contains( $contents, 'Plugin Name:' ) ) {
					return $dir;
				}
			}

			// also stop at wp-content/plugins and assume previous dir was plugin root (fallback)
			if ( str_ends_with( str_replace( '\\', '/', $dir ), '/wp-content/plugins' ) ) {
				// we went too far
				break;
			}
		}

		throw new RuntimeException( 'Unable to detect active plugin root for scaffolding.' );
	}
}
