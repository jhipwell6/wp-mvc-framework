<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\TaxonomyRegistrarInterface;
use Snowberry\WpMvc\Contracts\TaxonomyDefinition;

final class TaxonomyRegistrar implements TaxonomyRegistrarInterface
{
	private const REGISTERED_TAXONOMIES_OPTION = 'snowberry_wp_mvc_registered_taxonomies';

	public function register( TaxonomyDefinition $definition ): void
	{
		$slug = $definition->slug();

		register_taxonomy(
			$slug,
			$definition->postTypes(),
			$definition->args()
		);

		$this->flushPermalinksIfNeeded( $slug );
	}

	private function flushPermalinksIfNeeded( string $slug ): void
	{
		$registeredTaxonomies = get_option( self::REGISTERED_TAXONOMIES_OPTION, [] );

		if ( ! is_array( $registeredTaxonomies ) ) {
			$registeredTaxonomies = [];
		}

		if ( in_array( $slug, $registeredTaxonomies, true ) ) {
			return;
		}

		$registeredTaxonomies[] = $slug;

		update_option( self::REGISTERED_TAXONOMIES_OPTION, array_values( array_unique( $registeredTaxonomies ) ), false );
		flush_rewrite_rules( false );
	}
}
