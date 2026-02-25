<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use RuntimeException;

final class PhpProjectManifest implements ProjectManifestInterface
{
	private array $config;

	public function __construct( ProjectLocatorInterface $locator )
	{
		$path = rtrim( $locator->pluginRoot(), '/' ) . '/wp-mvc.php';

		if ( ! file_exists( $path ) ) {
			throw new RuntimeException( 'wp-mvc.php manifest not found in plugin root.' );
		}

		$config = require $path;

		if ( ! is_array( $config ) ) {
			throw new RuntimeException( 'Invalid wp-mvc.php manifest.' );
		}

		$this->config = $config;
	}

	public function namespace(): string
	{
		return $this->config['namespace'] ?? throw new RuntimeException( 'Manifest missing namespace.' );
	}

	public function path( string $key ): string
	{
		if ( ! isset( $this->config['paths'][$key] ) ) {
			throw new RuntimeException( "Manifest path '{$key}' not defined." );
		}

		return $this->config['paths'][$key];
	}

	/**
	 * @return string[]
	 */
	public function providers(): array
	{
		if ( ! array_key_exists( 'providers', $this->config ) ) {
			return [];
		}

		$providers = $this->config['providers'];

		if ( ! is_array( $providers ) ) {
			throw new RuntimeException( 'Manifest providers must be an array of class names.' );
		}

		foreach ( $providers as $provider ) {
			if ( ! is_string( $provider ) ) {
				throw new RuntimeException( 'Manifest providers must be an array of class names.' );
			}
		}

		return $providers;
	}
}
