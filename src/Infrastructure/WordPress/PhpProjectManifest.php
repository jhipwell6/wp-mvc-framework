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
		if ( ! isset( $this->config['namespace'] ) ) {
			throw new RuntimeException( 'Manifest missing namespace.' );
		}
		
		return $this->config['namespace'];
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

	/**
	 * @return string[]
	 */
	public function blocks(): array
	{
		if ( ! array_key_exists( 'blocks', $this->config ) ) {
			return [];
		}

		$blocks = $this->config['blocks'];

		if ( ! is_array( $blocks ) ) {
			throw new RuntimeException( 'Manifest blocks must be an array of class names.' );
		}

		foreach ( $blocks as $block ) {
			if ( ! is_string( $block ) ) {
				throw new RuntimeException( 'Manifest blocks must be an array of class names.' );
			}
		}

		return $blocks;
	}

	/**
	 * @return array<string,string>
	 */
	public function policies(): array
	{
		if ( ! array_key_exists( 'policies', $this->config ) ) {
			return [];
		}

		$policies = $this->config['policies'];

		if ( ! is_array( $policies ) ) {
			throw new RuntimeException( 'Manifest policies must be an array mapping resource class names to policy class names.' );
		}

		foreach ( $policies as $resourceClass => $policyClass ) {
			if ( ! is_string( $resourceClass ) || ! is_string( $policyClass ) ) {
				throw new RuntimeException( 'Manifest policies must be an array mapping resource class names to policy class names.' );
			}
		}

		return $policies;
	}
}
