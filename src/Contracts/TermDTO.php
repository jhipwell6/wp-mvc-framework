<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class TermDTO
{
	public function __construct(
		public int $term_id,
		public string $name,
		public string $slug,
		public string $taxonomy,
		public string $description,
		public int $parent
	)
	{
	}
}
