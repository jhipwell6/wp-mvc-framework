<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\PostTypeRegistrarInterface;
use Snowberry\WpMvc\Contracts\PostTypeDefinition;

final class PostTypeRegistrar implements PostTypeRegistrarInterface
{
	private const REGISTERED_POST_TYPES_OPTION = 'snowberry_wp_mvc_registered_post_types';

	public function register( PostTypeDefinition $definition ): void
	{
		$slug = $definition->slug();

		register_post_type(
			$slug,
			$definition->args()
		);

		$this->flushPermalinksIfNeeded( $slug );
	}

	private function flushPermalinksIfNeeded( string $slug ): void
	{
		$registeredPostTypes = get_option( self::REGISTERED_POST_TYPES_OPTION, [] );

		if ( ! is_array( $registeredPostTypes ) ) {
			$registeredPostTypes = [];
		}

		if ( in_array( $slug, $registeredPostTypes, true ) ) {
			return;
		}

		$registeredPostTypes[] = $slug;

		update_option( self::REGISTERED_POST_TYPES_OPTION, array_values( array_unique( $registeredPostTypes ) ), false );
		flush_rewrite_rules( false );
	}
}
