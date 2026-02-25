<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;
use Snowberry\WpMvc\Contracts\PostTypeDefinition;
use Snowberry\WpMvc\Contracts\TaxonomyDefinition;
use Snowberry\WpMvc\Contracts\PostTypeRegistrarInterface;
use Snowberry\WpMvc\Contracts\TaxonomyRegistrarInterface;
use Snowberry\WpMvc\Contracts\FieldGroupRegistrarInterface;
use InvalidArgumentException;

final class RegistrationRegistry implements RegistrationRegistryInterface
{
	private array $postTypes = [];
	private array $taxonomies = [];
	private array $fieldGroups = [];

	public function __construct(
		private PostTypeRegistrarInterface $postTypeRegistrar,
		private TaxonomyRegistrarInterface $taxonomyRegistrar,
		private FieldGroupRegistrarInterface $fieldGroupRegistrar
	)
	{
		
	}

	public function addPostType( PostTypeDefinition $definition ): void
	{
		$slug = $definition->slug();

		if ( isset( $this->postTypes[$slug] ) ) {
			throw new InvalidArgumentException( "Duplicate post type: {$slug}" );
		}

		$this->postTypes[$slug] = $definition;
	}

	public function addTaxonomy( TaxonomyDefinition $definition ): void
	{
		$slug = $definition->slug();

		if ( isset( $this->taxonomies[$slug] ) ) {
			throw new InvalidArgumentException( "Duplicate taxonomy: {$slug}" );
		}

		$this->taxonomies[$slug] = $definition;
	}

	public function addFieldGroup( array $group ): void
	{
		if ( ! isset( $group['key'] ) ) {
			throw new InvalidArgumentException( 'ACF field group must have a key.' );
		}

		if ( isset( $this->fieldGroups[$group['key']] ) ) {
			throw new InvalidArgumentException( "Duplicate ACF field group: {$group['key']}" );
		}

		$this->fieldGroups[$group['key']] = $group;
	}

	public function registerAll(): void
	{
		add_action( 'init', function () {
			foreach ( $this->postTypes as $definition ) {
				$this->postTypeRegistrar->register( $definition );
			}

			foreach ( $this->taxonomies as $definition ) {
				$this->taxonomyRegistrar->register( $definition );
			}
		} );

		foreach ( $this->fieldGroups as $group ) {
			$this->fieldGroupRegistrar->register( $group );
		}
	}
}
