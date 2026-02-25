<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure;

use Snowberry\WpMvc\Contracts\TemplateRendererInterface;
use RuntimeException;

final class SimpleTemplateRenderer implements TemplateRendererInterface
{

	/**
	 * @param array<string, mixed> $context
	 */
	public function render( string $template, array $context = [] ): string
	{
		return preg_replace_callback( '/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function ( $m ) use ( $context ) {
				$key = $m[1];
				$val = $this->get( $context, $key );

				if ( $val === null ) {
					return '';
				}
				if ( is_array( $val ) || is_object( $val ) ) {
					throw new RuntimeException( "Template value for '{$key}' must be scalar." );
				}
				return (string) $val;
			}, $template ) ?? $template;
	}

	/**
	 * @param array<string, mixed> $context
	 */
	private function get( array $context, string $key ): mixed
	{
		// dot-notation support
		if ( ! str_contains( $key, '.' ) ) {
			return $context[$key] ?? null;
		}

		$parts = explode( '.', $key );
		$cur = $context;

		foreach ( $parts as $part ) {
			if ( is_array( $cur ) && array_key_exists( $part, $cur ) ) {
				$cur = $cur[$part];
				continue;
			}
			return null;
		}

		return $cur;
	}
}
