<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class PostDTO
{

	public function __construct(
		public int $ID,
		public string $post_type,
		public string $post_title,
		public string $post_content,
		public string $post_status,
		public array $raw = [],
		public array $fields = []
	)
	{
		
	}
}
