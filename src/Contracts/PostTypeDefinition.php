<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

use InvalidArgumentException;

final class PostTypeDefinition
{
	private string $slug;
	private array $args;

	public function __construct( string $slug, array $args )
	{
		$this->slug = $this->validateSlug( $slug );
		$this->args = $this->normalizeArgs( $args );
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function args(): array
	{
		return $this->args;
	}

	private function validateSlug( string $slug ): string
	{
		if ( ! preg_match( '/^[a-z0-9_]{1,20}$/', $slug ) ) {
			throw new InvalidArgumentException(
					"Invalid post type slug: {$slug}"
				);
		}

		return $slug;
	}

	private function normalizeArgs( array $args ): array
	{
		if ( ! isset( $args['label'] ) && ! isset( $args['labels'] ) ) {
			throw new InvalidArgumentException(
					"Post type '{$this->slug}' must define 'label' or 'labels'."
				);
		}

		return array_merge( [
			'public' => true,
			'show_in_rest' => true,
			'supports' => [ 'title', 'editor' ],
			], $args );
	}
}
