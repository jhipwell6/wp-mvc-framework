<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class HttpResponse
{

	public function __construct(
		public int $status,
		public string $body,
		public array $headers = []
	)
	{
		
	}
}
