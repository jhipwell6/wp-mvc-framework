<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface FieldProviderInterface
{

	/**
	 * @return FieldDefinition[]
	 */
	public function fieldsForPostType( string $postType ): array;
}
