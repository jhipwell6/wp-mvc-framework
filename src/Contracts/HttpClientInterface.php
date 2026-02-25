<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface HttpClientInterface
{

	public function get( string $url, array $args = [] ): HttpResponse;
}
