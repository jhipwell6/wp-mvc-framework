<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\FieldProviderInterface;
use Snowberry\WpMvc\Contracts\FieldDefinition;

final class AcfFieldProvider implements FieldProviderInterface
{

	public function fieldsForPostType( string $postType ): array
	{
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}

		$groups = acf_get_field_groups( [
			'post_type' => $postType,
			] );

		$fields = [];

		foreach ( $groups as $group ) {
			$groupFields = acf_get_fields( $group['key'] ) ?: [];

			foreach ( $groupFields as $field ) {
				if ( $field['type'] === 'relationship' || $field['type'] === 'post_object' ) {
					$fields[] = new FieldDefinition(
						name: $field['name'],
						type: 'relationship',
						required: (bool) ($field['required'] ?? false),
						default: null,
						multiple: ($field['type'] === 'relationship'),
						relatedPostType: $field['post_type'][0] ?? null,
					);
				} else {
					$fields[] = new FieldDefinition(
						name: $field['name'],
						type: $field['type'],
						required: (bool) ($field['required'] ?? false),
						default: $field['default_value'] ?? null,
					);
				}
			}
		}

		return $fields;
	}
}
