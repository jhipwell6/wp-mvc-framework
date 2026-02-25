<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class FieldDefinition
{

	public function __construct(
		public string $name, // meta key
		public string $type, // text, number, date, etc
		public bool $required = false,
		public bool $multiple = false,
		public ?string $relatedPostType = null,
		public mixed $default = null,
	)
	{
		
	}
}
