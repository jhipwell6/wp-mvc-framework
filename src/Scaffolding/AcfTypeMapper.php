<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding;

use Snowberry\WpMvc\Contracts\FieldDefinition;

final class AcfTypeMapper
{

	public function phpType( FieldDefinition $field ): string
	{
		return match ( $field->type ) {
			'text', 'textarea', 'wysiwyg', 'select', 'radio' => 'string',
			'number', 'range' => 'int',
			'true_false' => 'bool',
			'date_picker' => '\DateTimeImmutable',
			'image', 'file' => 'int', // store attachment ID
			'relationship' => $field->multiple ? 'array' : '?int',
			default => 'mixed',
		};
	}

	public function defaultValue( FieldDefinition $field ): string
	{
		if ( $field->default === null ) {
			return 'null';
		}

		return var_export( $field->default, true );
	}
}
