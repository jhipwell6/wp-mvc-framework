<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\FieldGroupRegistrarInterface;

final class AcfFieldGroupRegistrar implements FieldGroupRegistrarInterface
{

	public function register( array $group ): void
	{
		if ( ! isset( $group['key'], $group['title'], $group['fields'] ) ) {
			throw new \InvalidArgumentException(
					'Invalid ACF field group definition.'
				);
		}

		add_action( 'acf/init', function () use ( $group ) {
			if ( function_exists( 'acf_add_local_field_group' ) ) {
				acf_add_local_field_group( $group );
			}
		} );
	}
}
