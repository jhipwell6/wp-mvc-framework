<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

use InvalidArgumentException;

final class TaxonomyDefinition
{
	private string $slug;
	private array $postTypes;
	private array $args;

	public function __construct(
		string $slug,
		array|string $postTypes,
		array $args
	)
	{
		$this->slug = $this->validateSlug( $slug );
		$this->postTypes = $this->normalizePostTypes( $postTypes );
		$this->args = $this->normalizeArgs( $args );
	}

	public function slug(): string
	{
		return $this->slug;
	}

	public function postTypes(): array
	{
		return $this->postTypes;
	}

	public function args(): array
	{
		return $this->args;
	}

	private function validateSlug( string $slug ): string
	{
		if ( ! preg_match( '/^[a-z0-9_]{1,32}$/', $slug ) ) {
			throw new InvalidArgumentException(
					"Invalid taxonomy slug: {$slug}"
				);
		}

		return $slug;
	}

	private function normalizePostTypes( array|string $postTypes ): array
	{
		$postTypes = (array) $postTypes;

		if ( empty( $postTypes ) ) {
			throw new InvalidArgumentException(
					"Taxonomy '{$this->slug}' must be attached to at least one post type."
				);
		}

		return $postTypes;
	}

	private function normalizeArgs( array $args ): array
	{
		return array_merge( [
			'public' => true,
			'show_in_rest' => true,
			'hierarchical' => false,
			], $args );
	}
}
