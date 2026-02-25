<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\CacheInterface;

final class TransientCache implements CacheInterface
{

	public function get( string $key ): mixed
	{
		$value = get_transient( $key );
		return $value === false ? null : $value;
	}

	public function set( string $key, mixed $value, int $ttl ): void
	{
		set_transient( $key, $value, $ttl );
	}

	public function delete( string $key ): void
	{
		delete_transient( $key );
	}
}
